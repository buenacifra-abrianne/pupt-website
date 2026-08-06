<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\NewsImage;
use App\Support\EventAnnouncementValidation;
use App\Support\PlainText;
use App\Support\RichText;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AnnouncementController extends Controller
{
    private function notifySystem(string $title, string $message, string $type = 'INFO'): void
    {
        $allowed = ['INFO', 'WARNING', 'DANGER', 'PRIMARY'];
        $type = strtoupper($type);
        if (!in_array($type, $allowed, true)) {
            $type = 'INFO';
        }

        DB::table('notifications')->insert([
            'channel' => 'SYSTEM',
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'created_at' => now(),
        ]);
    }

    private function logActivity(string $action, string $module, ?int $targetId, string $description): void
    {
        AuditLog::record($action, $module, $description, $targetId);
    }

    public function index()
    {
        $hasAnnouncementLinkColumn = Schema::hasColumn('announcements', 'link');
        $hasNewsLinkColumn = Schema::hasColumn('news', 'link');
        $hasNewsFeaturedColumn = Schema::hasColumn('news', 'is_featured');
        $hasNewsHiddenColumn = Schema::hasColumn('news', 'is_hidden_from_public');

        $announcements_all = DB::table('announcements as a')
            ->leftJoin('users as u', 'a.created_by', '=', 'u.user_id')
            ->select(
                'a.*',
                DB::raw("\n                    COALESCE(\n                        NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,''))), ''),\n                        NULLIF(TRIM((\n                            SELECT al.user_name\n                            FROM activity_logs al\n                            WHERE al.module = 'ANNOUNCEMENT'\n                              AND al.action = 'CREATED'\n                              AND (\n                                al.target_id = a.announcement_id\n                                OR al.description LIKE CONCAT('Created announcement: ', a.title, '%')\n                              )\n                            ORDER BY CASE WHEN al.target_id = a.announcement_id THEN 0 ELSE 1 END, al.id DESC\n                            LIMIT 1\n                        )), ''),\n                        NULLIF(TRIM((\n                            SELECT ar.requester_name\n                            FROM approval_requests ar\n                            WHERE ar.type = 'ANNOUNCEMENT_CREATE'\n                              AND (\n                                CAST(JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.announcement_id')) AS UNSIGNED) = a.announcement_id\n                                OR JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.title')) = a.title\n                              )\n                            ORDER BY CASE WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.announcement_id')) AS UNSIGNED) = a.announcement_id THEN 0 ELSE 1 END, ar.id DESC\n                            LIMIT 1\n                        )), ''),\n                        NULLIF(TRIM(CAST(a.created_by AS CHAR)), ''),\n                        'Unknown'\n                    ) as created_by_name\n                ")
            )
            ->orderByRaw("CASE WHEN a.status = 'ENABLED' THEN 0 ELSE 1 END")
            ->orderBy('a.created_at', 'desc')
            ->get()
            ->map(function ($announcement) {
                $announcement->title = PlainText::normalize($announcement->title ?? '');

                return $announcement;
            });

        $news_list = DB::table('news')
            ->when($hasNewsHiddenColumn, function ($query) {
                $query->orderBy('is_hidden_from_public');
            })
            ->when($hasNewsFeaturedColumn, function ($query) {
                $query->orderByDesc('is_featured');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($news) {
                $news->title = PlainText::normalize($news->title ?? '');
                $news->category = PlainText::normalize($news->category ?? '');
                $news->location = PlainText::normalize($news->location ?? '');

                return $news;
            });

        $oneMonthAgo = now()->subMonth();

        $active_news = $news_list->filter(function($news) use ($oneMonthAgo) {
            $date = \Carbon\Carbon::parse($news->date_published ?? $news->created_at);
            return $date >= $oneMonthAgo;
        })->values();

        $expired_news = $news_list->filter(function($news) use ($oneMonthAgo) {
            $date = \Carbon\Carbon::parse($news->date_published ?? $news->created_at);
            return $date < $oneMonthAgo;
        })->values();

        $active_announcements = $announcements_all;

        return view('superadmin.announcements', compact(
            'active_announcements',
            'active_news',
            'expired_news',
            'hasAnnouncementLinkColumn',
            'hasNewsLinkColumn',
            'hasNewsFeaturedColumn',
            'hasNewsHiddenColumn'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:60',
            'content' => 'required|string',
            'link' => 'nullable|url|max:255',
            'priority' => 'required|string',
            'status' => 'required|string',
            'announcement_id' => 'nullable|integer',
        ]);

        $title = PlainText::normalize($request->input('title'));
        $content = RichText::sanitize($request->input('content'));
        $link = trim((string) $request->input('link'));
        $priority = strtoupper(trim((string) $request->input('priority')));
        $status = strtoupper(trim((string) $request->input('status')));
        $hasLinkColumn = Schema::hasColumn('announcements', 'link');

        $data = [
            'title' => $title,
            'content' => $content,
            'priority' => $priority,
            'status' => $status,
        ];

        if ($hasLinkColumn) {
            $data['link'] = $link !== '' ? $link : null;
        }

        if ($request->filled('announcement_id')) {
            $announcementId = (int) $request->announcement_id;
            $existing = DB::table('announcements')
                ->where('announcement_id', $announcementId)
                ->first();

            if (!$existing) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Announcement not found.',
                    ], 404);
                }

                return back()->with('error', 'Announcement not found.');
            }

            $isNoChange = trim((string) ($existing->title ?? '')) === $title
                && trim((string) ($existing->content ?? '')) === $content
                && strtoupper(trim((string) ($existing->priority ?? ''))) === $priority
                && strtoupper(trim((string) ($existing->status ?? ''))) === $status;

            if ($hasLinkColumn) {
                $isNoChange = $isNoChange
                    && trim((string) ($existing->link ?? '')) === ($link !== '' ? $link : '');
            }

            if ($isNoChange) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'ok' => true,
                        'no_changes' => true,
                        'message' => 'No changes detected.',
                    ]);
                }

                return back()->with('info', 'No changes detected.');
            }

            DB::table('announcements')
                ->where('announcement_id', $announcementId)
                ->update($data);

            $this->logActivity(
                'UPDATED',
                'ANNOUNCEMENT',
                $announcementId,
                'Updated announcement: '.$title
            );

            $this->notifySystem(
                'Announcement Edited',
                'Announcement '.$title.' was edited.',
                'PRIMARY'
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Announcement updated.',
                ]);
            }

            return back()->with('success', 'Announcement updated.');
        }

        $data['created_at'] = now();
        $data['created_by'] = (int) (session('user_id') ?? 0);

        $id = DB::table('announcements')->insertGetId($data, 'announcement_id');

        $this->notifySystem(
            'Announcement Created',
            'Announcement '.$title.' was created.',
            'INFO'
        );

        $this->logActivity(
            'CREATED',
            'ANNOUNCEMENT',
            (int) $id,
            'Created announcement: '.$title
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Announcement created.',
            ]);
        }

        return back()->with('success', 'Announcement created.');
    }

    public function saveNews(Request $request)
    {
        if (!$request->hasFile('image')) {
            $request->request->remove('image');
        }

        $hasNewsLinkColumn = Schema::hasColumn('news', 'link');
        $hasNewsFeaturedColumn = Schema::hasColumn('news', 'is_featured');
        $hasNewsHiddenColumn = Schema::hasColumn('news', 'is_hidden_from_public');

        $rules = [
            'news_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:60'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:60'],
        ];

        if ($hasNewsLinkColumn) {
            $rules['link'] = ['nullable', 'url', 'max:255'];
        }

        $request->validate($rules);

        if ($message = NewsImage::validationError($request->file('image'))) {
            throw ValidationException::withMessages(['image' => $message]);
        }

        $newsId = (int) $request->input('news_id', 0);

        $existing = null;
        if ($newsId > 0) {
            $existing = DB::table('news')->where('news_id', $newsId)->first();
            if (!$existing) {
                return response()->json(['ok' => false, 'error' => 'News not found.'], 404);
            }
        }

        EventAnnouncementValidation::validate($request, $existing);

        $incomingTitle = PlainText::normalize($request->input('title'));
        $incomingContent = RichText::sanitize($request->input('content'));
        $incomingCategory = PlainText::normalize($request->input('category'));
        $incomingLocation = PlainText::normalize($request->input('location'));
        $incomingLink = trim((string) $request->input('link'));
        $hasNewImage = $request->hasFile('image');

        if ($newsId > 0) {
            $removeImage = (string) $request->input('remove_image', '0') === '1';

            $isNoChange = !$hasNewImage
                && !$removeImage
                && trim((string) ($existing->title ?? '')) === $incomingTitle
                && trim((string) ($existing->content ?? '')) === $incomingContent
                && trim((string) ($existing->category ?? '')) === $incomingCategory
                && trim((string) ($existing->location ?? '')) === $incomingLocation;

            if ($hasNewsLinkColumn) {
                $isNoChange = $isNoChange
                    && trim((string) ($existing->link ?? '')) === ($incomingLink !== '' ? $incomingLink : '');
            }

            if ($isNoChange) {
                return response()->json([
                    'ok' => true,
                    'no_changes' => true,
                    'message' => 'No changes detected.',
                ]);
            }
        }

        $imagePath = $existing?->image_path;
        $removeImage = (string) $request->input('remove_image', '0') === '1';

        if ($removeImage && $imagePath) {
            NewsImage::delete($imagePath);
            $imagePath = null;
        }

        if ($hasNewImage) {
            $oldImagePath = $imagePath;

            $uploadedPath = NewsImage::store($request->file('image'));

            if (!$uploadedPath) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Image upload failed.',
                ], 500);
            }

            $imagePath = $uploadedPath;

            if ($oldImagePath) {
                NewsImage::delete($oldImagePath);
            }
        }

        $data = [
            'title' => $incomingTitle,
            'content' => $incomingContent,
            'category' => $incomingCategory,
            'location' => $incomingLocation,
            'image_path' => $imagePath,
            'date_published' => now(),
        ];

        if ($hasNewsLinkColumn) {
            $data['link'] = $incomingLink !== '' ? $incomingLink : null;
        }

        if ($hasNewsFeaturedColumn) {
            $data['is_featured'] = $request->boolean('is_featured');
        }

        if ($hasNewsHiddenColumn) {
            $data['is_hidden_from_public'] = $request->boolean('is_hidden_from_public');
        }

        if ($newsId > 0) {
            DB::table('news')->where('news_id', $newsId)->update($data);
            $this->logActivity('UPDATED', 'NEWS', $newsId, 'Updated news: '.$incomingTitle);
            $savedNewsId = $newsId;
        } else {
            $data['created_at'] = now();
            $data['priority'] = 'MEDIUM';
            $data['status'] = 'APPROVED';
            $data['created_by'] = (int) (session('user_id') ?? 0);

            $newId = DB::table('news')->insertGetId($data, 'news_id');
            $this->logActivity('CREATED', 'NEWS', (int) $newId, 'Created news: '.$incomingTitle);
            $savedNewsId = (int) $newId;
        }

        $savedNews = DB::table('news')->where('news_id', $savedNewsId)->first();

        return response()->json([
            'ok' => true,
            'message' => $newsId > 0 ? 'News updated successfully.' : 'News created successfully.',
            'news' => $this->formatNewsPayload($savedNews),
        ]);
    }

    private function formatNewsPayload(?object $news): ?array
    {
        if (!$news) {
            return null;
        }

        return [
            'news_id' => (int) $news->news_id,
            'title' => PlainText::normalize($news->title ?? ''),
            'content' => (string) ($news->content ?? ''),
            'category' => PlainText::normalize($news->category ?? ''),
            'location' => PlainText::normalize($news->location ?? ''),
            'link' => (string) ($news->link ?? ''),
            'image_path' => (string) ($news->image_path ?? ''),
            'image_url' => NewsImage::url($news->image_path),
            'is_featured' => (bool) ($news->is_featured ?? false),
            'is_hidden_from_public' => (bool) ($news->is_hidden_from_public ?? false),
        ];
    }

    public function delete(Request $request)
    {
        $id = (int) $request->id;

        $row = DB::table('announcements')->where('announcement_id', $id)->first();

        if (!$row) {
            return response()->json(['ok' => false, 'error' => 'Announcement not found.'], 404);
        }

        $this->deleteAnnouncementRecord($id);

        $this->notifySystem(
            'Announcement Deleted',
            'Announcement '.$row->title.' was deleted.',
            'DANGER'
        );
        $this->logActivity(
            'DELETED',
            'ANNOUNCEMENT',
            $id,
            'Deleted announcement: '.$row->title
        );

        return response()->json(['ok' => true]);
    }

    public function bulkAnnouncements(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'gt:0'],
        ]);

        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['ok' => false, 'error' => 'No announcements selected.'], 422);
        }

        $items = DB::table('announcements')
            ->whereIn('announcement_id', $ids->all())
            ->get();

        foreach ($items as $item) {
            $this->deleteAnnouncementRecord((int) $item->announcement_id);
            $this->logActivity(
                'DELETED',
                'ANNOUNCEMENT',
                (int) $item->announcement_id,
                'Deleted announcement: '.$item->title
            );
        }

        $count = $items->count();

        $this->notifySystem(
            'Announcements Deleted',
            $count.' announcement(s) were deleted.',
            'DANGER'
        );

        return response()->json([
            'ok' => true,
            'message' => $count.' announcement(s) deleted.',
        ]);
    }

    public function deleteNews(Request $request)
    {
        $id = $request->id;

        $news = DB::table('news')->where('news_id', $id)->first();
        if (!$news) {
            return response()->json(['ok' => false, 'error' => 'News not found']);
        }

        $this->deleteNewsRecord((int) $id, $news);

        $this->notifySystem(
            'News Deleted',
            'News article '.$news->title.' was deleted.',
            'DANGER'
        );
        $this->logActivity(
            'DELETED',
            'NEWS',
            (int) $id,
            'Deleted news: '.$news->title
        );

        return response()->json(['ok' => true]);
    }

    public function bulkNews(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:delete,hide,show'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'gt:0'],
        ]);

        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['ok' => false, 'error' => 'No news items selected.'], 422);
        }

        $action = (string) $request->input('action');

        if ($action === 'delete') {
            $items = DB::table('news')
                ->whereIn('news_id', $ids->all())
                ->get();

            foreach ($items as $item) {
                $this->deleteNewsRecord((int) $item->news_id, $item);
                $this->logActivity('DELETED', 'NEWS', (int) $item->news_id, 'Deleted news: '.$item->title);
            }

            $count = $items->count();

            $this->notifySystem(
                'News Deleted',
                $count.' news article(s) were deleted.',
                'DANGER'
            );

            return response()->json([
                'ok' => true,
                'message' => $count.' news article(s) deleted.',
            ]);
        }

        if (!Schema::hasColumn('news', 'is_hidden_from_public')) {
            return response()->json([
                'ok' => false,
                'error' => 'Hide from public is unavailable until the latest news migration is run.',
            ], 422);
        }

        $hidden = $action === 'hide';

        DB::table('news')
            ->whereIn('news_id', $ids->all())
            ->update(['is_hidden_from_public' => $hidden ? 1 : 0]);

        foreach ($ids as $id) {
            $this->logActivity(
                $hidden ? 'HIDDEN' : 'SHOWN',
                'NEWS',
                $id,
                ($hidden ? 'Hidden' : 'Restored').' news from public view'
            );
        }

        $this->notifySystem(
            $hidden ? 'News Hidden' : 'News Restored',
            $ids->count().' news article(s) were '.($hidden ? 'hidden from' : 'restored to').' public view.',
            $hidden ? 'WARNING' : 'INFO'
        );

        return response()->json([
            'ok' => true,
            'message' => $ids->count().' news article(s) '.($hidden ? 'hidden from' : 'restored to').' public view.',
        ]);
    }

    private function deleteNewsRecord(int $id, ?object $news = null): void
    {
        $news ??= DB::table('news')->where('news_id', $id)->first();

        if (!$news) {
            return;
        }

        if (!empty($news->image_path)) {
            NewsImage::delete($news->image_path);
        }

        DB::table('news')->where('news_id', $id)->delete();
    }

    private function deleteAnnouncementRecord(int $id): void
    {
        DB::table('announcements')->where('announcement_id', $id)->delete();
    }

    public function toggle(Request $request)
    {
        $announcement = DB::table('announcements')
            ->where('announcement_id', $request->id)
            ->first();

        if (!$announcement) {
            return response()->json(['ok' => false]);
        }

        $newStatus = $announcement->status === 'ENABLED' ? 'DISABLED' : 'ENABLED';

        DB::table('announcements')
            ->where('announcement_id', $request->id)
            ->update(['status' => $newStatus]);

        if ($newStatus === 'ENABLED') {
            $this->notifySystem(
                'Announcement Enabled',
                'Announcement '.$announcement->title.' was enabled.',
                'INFO'
            );
            $this->logActivity(
                'ENABLED',
                'ANNOUNCEMENT',
                (int) $announcement->announcement_id,
                'Enabled announcement: '.$announcement->title
            );
        } else {
            $this->notifySystem(
                'Announcement Disabled',
                'Announcement '.$announcement->title.' was disabled.',
                'WARNING'
            );
            $this->logActivity(
                'DISABLED',
                'ANNOUNCEMENT',
                (int) $announcement->announcement_id,
                'Disabled announcement: '.$announcement->title
            );
        }

        return response()->json(['ok' => true]);
    }
}

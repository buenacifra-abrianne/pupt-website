<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\NewsImage;
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

        $announcements = DB::table('announcements as a')
            ->leftJoin('users as u', 'a.created_by', '=', 'u.user_id')
            ->select(
                'a.*',
                DB::raw("\n                    COALESCE(\n                        NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,''))), ''),\n                        NULLIF(TRIM((\n                            SELECT al.user_name\n                            FROM activity_logs al\n                            WHERE al.module = 'ANNOUNCEMENT'\n                              AND al.action = 'CREATED'\n                              AND (\n                                al.target_id = a.announcement_id\n                                OR al.description LIKE CONCAT('Created announcement: ', a.title, '%')\n                              )\n                            ORDER BY CASE WHEN al.target_id = a.announcement_id THEN 0 ELSE 1 END, al.id DESC\n                            LIMIT 1\n                        )), ''),\n                        NULLIF(TRIM((\n                            SELECT ar.requester_name\n                            FROM approval_requests ar\n                            WHERE ar.type = 'ANNOUNCEMENT_CREATE'\n                              AND (\n                                CAST(JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.announcement_id')) AS UNSIGNED) = a.announcement_id\n                                OR JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.title')) = a.title\n                              )\n                            ORDER BY CASE WHEN CAST(JSON_UNQUOTE(JSON_EXTRACT(ar.details, '$.announcement_id')) AS UNSIGNED) = a.announcement_id THEN 0 ELSE 1 END, ar.id DESC\n                            LIMIT 1\n                        )), ''),\n                        NULLIF(TRIM(CAST(a.created_by AS CHAR)), ''),\n                        'Unknown'\n                    ) as created_by_name\n                ")
            )
            ->orderByRaw("CASE WHEN a.status = 'ENABLED' THEN 0 ELSE 1 END")
            ->orderBy('a.created_at', 'desc')
            ->get();

        $news_list = DB::table('news')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.announcements', compact('announcements', 'news_list', 'hasAnnouncementLinkColumn'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'link' => 'nullable|url|max:255',
            'priority' => 'required|string',
            'status' => 'required|string',
            'announcement_id' => 'nullable|integer',
        ]);

        $title = trim((string) $request->input('title'));
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

        $rules = [
            'news_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
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

        $incomingTitle = trim((string) $request->input('title'));
        $incomingContent = RichText::sanitize($request->input('content'));
        $incomingCategory = trim((string) $request->input('category'));
        $incomingLocation = trim((string) $request->input('location'));
        $incomingLink = trim((string) $request->input('link'));
        $hasNewImage = $request->hasFile('image');

        if ($newsId > 0) {
            $isNoChange = !$hasNewImage
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

        if ($newsId > 0) {
            DB::table('news')->where('news_id', $newsId)->update($data);
            $this->logActivity('UPDATED', 'NEWS', $newsId, 'Updated news: '.$incomingTitle);
        } else {
            $data['created_at'] = now();
            $data['priority'] = 'MEDIUM';
            $data['status'] = 'APPROVED';
            $data['created_by'] = (int) (session('user_id') ?? 0);

            $newId = DB::table('news')->insertGetId($data, 'news_id');
            $this->logActivity('CREATED', 'NEWS', (int) $newId, 'Created news: '.$incomingTitle);
        }

        return response()->json([
            'ok' => true,
            'message' => $newsId > 0 ? 'News updated successfully.' : 'News created successfully.',
        ]);
    }

    public function delete(Request $request)
    {
        $id = (int) $request->id;

        $row = DB::table('announcements')->where('announcement_id', $id)->first();

        DB::table('announcements')
            ->where('announcement_id', $id)
            ->delete();

        $this->notifySystem(
            'Announcement Deleted',
            'Announcement '.($row->title ?? "#{$id}").' was deleted.',
            'DANGER'
        );
        $this->logActivity(
            'DELETED',
            'ANNOUNCEMENT',
            $id,
            'Deleted announcement: '.($row->title ?? "#{$id}")
        );

        return response()->json(['ok' => true]);
    }

    public function deleteNews(Request $request)
    {
        $id = $request->id;

        $news = DB::table('news')->where('news_id', $id)->first();
        if (!$news) {
            return response()->json(['ok' => false, 'error' => 'News not found']);
        }

        if (!empty($news->image_path)) {
            NewsImage::delete($news->image_path);
        }

        DB::table('news')->where('news_id', $id)->delete();

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

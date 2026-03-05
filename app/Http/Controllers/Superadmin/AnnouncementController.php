<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{

    private function notifySystem(string $title, string $message, string $type = 'INFO'): void
    {
        $allowed = ['INFO','WARNING','DANGER','PRIMARY'];
        $type = strtoupper($type);
        if (!in_array($type, $allowed, true)) $type = 'INFO';

        DB::table('notifications')->insert([
            'channel'    => 'SYSTEM',   // IMPORTANT
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'created_at' => now(),
            // add 'updated_at' => now() only if your notifications table has updated_at
        ]);
    }

    private function logActivity(string $action, string $module, ?int $targetId, string $description): void
    {
        DB::table('activity_logs')->insert([
            'user_id'    => (int) session('user_id'),
            'user_name'  => trim((string) session('user_first_name', '') . ' ' . (string) session('user_last_name', '')),
            'action'     => strtoupper($action),   // CREATED / UPDATED / DELETED / APPROVED / REJECTED / DISABLED etc.
            'module'     => strtoupper($module),   // ANNOUNCEMENT / NEWS / ...
            'target_id'  => $targetId,
            'description'=> $description,
            'created_at' => now(),
        ]);
    }

    public function index()
    {
        $announcements = DB::table('announcements')
            ->orderByRaw("CASE WHEN status = 'ENABLED' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        $news_list = \DB::table('news')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.announcements', compact(
            'announcements',
            'news_list'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'title'    => 'required|string',
            'content'  => 'required|string',
            'priority' => 'required|string',
            'status'   => 'required|string',
            'announcement_id' => 'nullable|integer',
        ]);

        $title = $request->input('title');

        $data = [
            'title'    => $title,
            'content'  => $request->input('content'),
            'priority' => strtoupper($request->input('priority')),
            'status'   => strtoupper($request->input('status')), // ENABLED/DISABLED
        ];

        if ($request->filled('announcement_id')) {
            DB::table('announcements')
                ->where('announcement_id', $request->announcement_id)
                ->update($data);

            // ✅ NOTIF: edited/updated
            $this->notifySystem(
                'Announcement Edited',
                'Announcement '.$title.' was edited.',
                'PRIMARY'
            );

            return back()->with('success', 'Announcement updated.');
        }

        $data['created_at'] = now();
        $data['created_by'] = session('admin_last_name');

        $id = DB::table('announcements')->insertGetId($data, 'announcement_id');

        // ✅ NOTIF: created
        $this->notifySystem(
            'Announcement Created',
            'Announcement '.$title.' was created.',
            'INFO'
        );
        
        $this->logActivity(
            'CREATED', 
            'ANNOUNCEMENT', 
            (int)$id, 
            'Created announcement: '.$title.''
            );

        return back()->with('success', 'Announcement created.');
    }

    public function saveNews(Request $request)
{
    $request->validate([
        'news_id'   => ['nullable','integer'],
        'title'     => ['required','string','max:255'],
        'content'   => ['required','string'],
        'category'  => ['required','string','max:100'],
        'location'  => ['nullable','string','max:255'],
        'image'     => ['nullable','image','max:5120'], // 5MB
    ]);

    $newsId = (int) $request->input('news_id', 0);

    // ✅ Keep existing image_path on edit
    $existing = null;
    if ($newsId > 0) {
        $existing = DB::table('news')->where('news_id', $newsId)->first();
        if (!$existing) {
            return response()->json(['ok' => false, 'error' => 'News not found.'], 404);
        }
    }

    $imagePath = $existing->image_path ?? null;

    // ✅ Save new upload (public disk -> storage/app/public/news)
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('news', 'public'); // returns "news/xxx.jpg"
    }

    $data = [
        'title'        => $request->title,
        'content'      => $request->content,
        'category'     => $request->category,
        'location'     => $request->location,
        'image_path'   => $imagePath,
        'date_published' => now(),
    ];

    if ($newsId > 0) {
        DB::table('news')->where('news_id', $newsId)->update($data);
    } else {
        // optional fields if meron kayo in table
        $data['created_at'] = now();
        $data['priority']   = 'MEDIUM';
        $data['status']     = 'APPROVED';
        $data['created_by'] = (int) (session('user_id') ?? 0);

        DB::table('news')->insert($data);
    }

    return response()->json(['ok' => true]);
}

    public function delete(Request $request)
    {
        $id = (int) $request->id;

        $row = DB::table('announcements')->where('announcement_id', $id)->first();

        DB::table('announcements')
            ->where('announcement_id', $id)
            ->delete();

        // ✅ NOTIF: deleted (optional)
        $this->notifySystem(
            'Announcement Deleted',
            'Announcement '.($row->title ?? "#{$id}").' was deleted.',
            'DANGER'
        );
        $this->logActivity(
            'DELETED',
            'ANNOUNCEMENT',
            (int) $id,
            'Deleted announcement: '.($row->title ?? "#{$id}").''
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

        // delete image if exists
        if (!empty($news->image_path)) {
            $path = public_path('assets/uploads/'.$news->image_path);
            if (file_exists($path)) @unlink($path);
        }

        DB::table('news')->where('news_id', $id)->delete();

        // ✅ NOTIFICATION: News Deleted
        $this->notifySystem(
            'News Deleted',
            'News article '.$news->title.' was deleted.',
            'DANGER'
        );
        $this->logActivity(
            'DELETED',
            'NEWS',
            (int) $id,
            'Deleted news: '.$news->title.''
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

        // ✅ NOTIF: approved/rejected style (enabled/disabled)
        if ($newStatus === 'ENABLED') {
            $this->notifySystem(
                'Announcement Enabled',
                'Announcement '.$announcement->title.' was enabled.',
                'INFO'
            );
            $this->logActivity(
                'ENABLED', 
                'ANNOUNCEMENT', 
                (int)$announcement->announcement_id, 
                'Enabled announcement: '.$announcement->title.''
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
                (int)$announcement->announcement_id, 
                'Disabled announcement: '.$announcement->title.''
            );
        }

        return response()->json(['ok' => true]);
    }
}
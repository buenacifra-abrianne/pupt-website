<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index()
    {
        $email = (string) session('admin_email');
        $name  = trim((string)session('admin_first_name').' '.(string)session('admin_last_name'));

        // Staff sees THEIR requests only (Announcements + News)
        $myRequests = DB::table('approval_requests')
            ->where('requester_email', $email)
            ->whereIn('type', [
                'ANNOUNCEMENT_CREATE', 'ANNOUNCEMENT_UPDATE', 'ANNOUNCEMENT_DELETE',
                'NEWS_CREATE', 'NEWS_UPDATE', 'NEWS_DELETE'
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('staff.announcements', compact('myRequests', 'email', 'name'));
    }

    // -------------------------
    // ANNOUNCEMENTS (requests)
    // -------------------------

    public function requestCreateAnnouncement(Request $request)
    {
        $request->validate([
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createRequest(
            'ANNOUNCEMENT_CREATE',
            $request->input('title'),
            [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'priority' => $request->input('priority'),
            ]
        );
    }

    public function requestUpdateAnnouncement(Request $request)
    {
        $request->validate([
            'announcement_id' => ['required','integer'],
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createRequest(
            'ANNOUNCEMENT_UPDATE',
            $request->input('title'),
            [
                'announcement_id' => (int)$request->announcement_id,
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'priority' => $request->input('priority'),
            ]
        );
    }

    public function requestDeleteAnnouncement(Request $request)
    {
        $request->validate([
            'announcement_id' => ['required','integer'],
            'title' => ['nullable','string','max:255'], // optional display
        ]);

        $title = $request->title ?: 'Delete Announcement';

        return $this->createRequest(
            'ANNOUNCEMENT_DELETE',
            $title,
            [
                'announcement_id' => (int)$request->announcement_id,
            ]
        );
    }

        public function requestEnableAnnouncement(Request $request)
    {
        $request->validate([
            'announcement_id' => ['required', 'integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Enable Announcement';

        return $this->createRequest(
            'ANNOUNCEMENT_ENABLE',
            $title,
            [
                'announcement_id' => (int) $request->announcement_id,
            ]
        );
    }

    public function requestDisableAnnouncement(Request $request)
    {
        $request->validate([
            'announcement_id' => ['required', 'integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Disable Announcement';

        return $this->createRequest(
            'ANNOUNCEMENT_DISABLE',
            $title,
            [
                'announcement_id' => (int) $request->announcement_id,
            ]
        );
    }

    // -------------------------
    // NEWS (requests)
    // -------------------------

    public function requestCreateNews(Request $request)
    {
        $request->validate([
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'category' => ['required','string','max:100'],
            'location' => ['nullable','string','max:255'],
            // image later (we’ll handle upload request properly next)
        ]);

        return $this->createRequest(
            'NEWS_CREATE',
            $request->input('title'),
            [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
                // image_path handled later
            ]
        );
    }

    public function requestUpdateNews(Request $request)
    {
        $request->validate([
            'news_id' => ['required','integer'],
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'category' => ['required','string','max:100'],
            'location' => ['nullable','string','max:255'],
        ]);

        return $this->createRequest(
            'NEWS_UPDATE',
            $request->input('title'),
            [
                'news_id' => (int)$request->input('news_id'),
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
            ]
        );
    }

    public function requestDeleteNews(Request $request)
    {
        $request->validate([
            'news_id' => ['required','integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Delete News';

        return $this->createRequest(
            'NEWS_DELETE',
            $title,
            [
                'news_id' => (int)$request->news_id,
            ]
        );
    }

    // -------------------------
    // helper
    // -------------------------

    private function createRequest(string $type, string $title, array $payload)
    {
        $email = (string) session('admin_email');
        $name  = trim((string)session('admin_first_name').' '.(string)session('admin_last_name'));

        DB::table('approval_requests')->insert([
            'type' => $type,
            'title' => $title,
            'details' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'requester_name' => $name ?: 'Staff',
            'requester_email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
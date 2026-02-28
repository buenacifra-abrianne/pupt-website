<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index()
    {
        $email = (string) session('user_email');
        $name  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));

        // Staff sees THEIR requests only (Announcements + News)
        $myRequests = DB::table('approval_requests')
            ->where('requester_email', $email)
            ->whereIn('type', [
                'ANNOUNCEMENT_CREATE', 'ANNOUNCEMENT_UPDATE', 'ANNOUNCEMENT_DELETE',
                'NEWS_CREATE', 'NEWS_UPDATE', 'ANNOUNCEMENT_ENABLE', 'ANNOUNCEMENT_DISABLE', 'NEWS_DELETE'
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
        $email = (string) session('user_email');
        $name  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));

        if (!$email) {
            return response()->json(['ok' => false, 'error' => 'Missing session email. Please re-login.'], 422);
        }

        $request->validate([
            'request_id' => ['nullable','integer'], // ✅ for resubmitting/editing existing request (no duplicates)
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'announcement_id' => ['required','integer'],
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'announcement_id' => ['required','integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Delete Announcement';

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'announcement_id' => ['required', 'integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Enable Announcement';

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'announcement_id' => ['required', 'integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Disable Announcement';

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'category' => ['required','string','max:100'],
            'location' => ['nullable','string','max:255'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
            'NEWS_CREATE',
            $request->input('title'),
            [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'category' => $request->input('category'),
                'location' => $request->input('location'),
            ]
        );
    }

    public function requestUpdateNews(Request $request)
    {
        $request->validate([
            'request_id' => ['nullable','integer'], // ✅
            'news_id' => ['required','integer'],
            'title' => ['required','string','max:255'],
            'content' => ['required','string'],
            'category' => ['required','string','max:100'],
            'location' => ['nullable','string','max:255'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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
            'request_id' => ['nullable','integer'], // ✅
            'news_id' => ['required','integer'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = $request->title ?: 'Delete News';

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
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

    /**
     * ✅ If request_id exists, UPDATE that approval_requests row (resubmit / edit rejected)
     * ✅ Else INSERT new row
     */
    private function createOrUpdateRequest(?int $requestId, string $type, string $title, array $payload)
    {
        $email = (string) session('user_email');
        $name  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));

        if (!$email) {
            return response()->json(['ok' => false, 'error' => 'Missing session email. Please re-login.'], 422);
        }

        $data = [
            'type' => $type,
            'title' => $title,
            'details' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'requester_name' => $name ?: 'Staff',
            'requester_email' => $email,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'updated_at' => now(),
        ];

        // ✅ update existing request row (prevents duplicates)
        if ($requestId) {
            DB::table('approval_requests')
                ->where('id', $requestId)
                ->where('requester_email', $email)
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('approval_requests')->insert($data);
        }

        return response()->json(['ok' => true]);
    }
}
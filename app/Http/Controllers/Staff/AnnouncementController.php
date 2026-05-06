<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\NewsImage;
use App\Support\EventAnnouncementValidation;
use App\Support\PlainText;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\ApprovalRequest;

class AnnouncementController extends Controller
{
    public function index()
{
    $email  = (string) session('user_email');
    $normalizedEmail = strtolower(trim($email));
    $name   = trim((string)session('user_first_name').' '.(string)session('user_last_name'));
    $userId = (int) (session('user_id') ?? 0);

    // Staff sees THEIR requests only (Announcements + News)
    $myRequests = DB::table('approval_requests')
        ->whereRaw('LOWER(requester_email) = ?', [$normalizedEmail])
        ->whereIn('type', [
            'ANNOUNCEMENT_CREATE', 'ANNOUNCEMENT_UPDATE', 'ANNOUNCEMENT_DELETE',
            'ANNOUNCEMENT_ENABLE', 'ANNOUNCEMENT_DISABLE',
            'NEWS_CREATE', 'NEWS_UPDATE', 'NEWS_DELETE'
        ])
        ->orderByDesc('created_at')
        ->get();

    // ✅ Get announcement_ids that currently have PENDING requests (so we hide them from LIVE)
    $pendingAnnIds = DB::table('approval_requests')
        ->whereRaw('LOWER(requester_email) = ?', [$normalizedEmail])
        ->whereRaw('LOWER(status) = ?', ['pending'])
        ->whereIn('type', ['ANNOUNCEMENT_UPDATE','ANNOUNCEMENT_DELETE','ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE'])
        ->get()
        ->map(function ($r) {
            $p = json_decode($r->details ?? '{}', true) ?: [];
            return (int)($p['announcement_id'] ?? 0);
        })
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    // ✅ LIVE = approved announcements created by this admin,
    // minus those with pending changes
    $approvedAnnouncementIds = DB::table('approval_requests')
        ->whereRaw('LOWER(requester_email) = ?', [$normalizedEmail])
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereIn('type', ['ANNOUNCEMENT_CREATE', 'ANNOUNCEMENT_UPDATE', 'ANNOUNCEMENT_ENABLE', 'ANNOUNCEMENT_DISABLE'])
        ->get()
        ->map(function ($r) {
            $p = json_decode($r->details ?? '{}', true) ?: [];
            return (int)($p['announcement_id'] ?? 0);
        })
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    $myAnnouncements = DB::table('announcements')
        ->where(function ($q) use ($userId, $approvedAnnouncementIds) {
            if ($userId > 0) {
                $q->where('created_by', $userId);
            }

            if ($approvedAnnouncementIds !== []) {
                $method = $userId > 0 ? 'orWhereIn' : 'whereIn';
                $q->{$method}('announcement_id', $approvedAnnouncementIds);
            }
        })
        ->when($pendingAnnIds !== [], function ($q) use ($pendingAnnIds) {
            $q->whereNotIn('announcement_id', $pendingAnnIds);
        })
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($announcement) {
            $announcement->title = PlainText::normalize($announcement->title ?? '');

            return $announcement;
        });

// ✅ Get news_ids that currently have PENDING requests (hide from LIVE)
$pendingNewsIds = DB::table('approval_requests')
    ->whereRaw('LOWER(requester_email) = ?', [$normalizedEmail])
    ->whereRaw('LOWER(status) = ?', ['pending'])
    ->whereIn('type', ['NEWS_UPDATE','NEWS_DELETE'])
    ->get()
    ->map(function ($r) {
        $p = json_decode($r->details ?? '{}', true) ?: [];
        return (int)($p['news_id'] ?? 0);
    })
    ->filter(fn($id) => $id > 0)
    ->unique()
    ->values()
    ->all();

// ✅ LIVE = approved news created by this admin,
// minus those with pending changes
    $approvedNewsIds = DB::table('approval_requests')
        ->whereRaw('LOWER(requester_email) = ?', [$normalizedEmail])
        ->whereRaw('LOWER(status) = ?', ['approved'])
        ->whereIn('type', ['NEWS_CREATE', 'NEWS_UPDATE'])
        ->get()
        ->map(function ($r) {
            $p = json_decode($r->details ?? '{}', true) ?: [];
            return (int)($p['news_id'] ?? 0);
        })
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

 $myNews = DB::table('news')
    ->where(function ($q) use ($userId, $approvedNewsIds) {
        if ($userId > 0) {
            $q->where('created_by', $userId);
        }

        if ($approvedNewsIds !== []) {
            $method = $userId > 0 ? 'orWhereIn' : 'whereIn';
            $q->{$method}('news_id', $approvedNewsIds);
        }
    })
    ->where('status', 'APPROVED') // keep it clean for public consistency
    ->when($pendingNewsIds !== [], function ($q) use ($pendingNewsIds) {
        $q->whereNotIn('news_id', $pendingNewsIds);
    })
    ->orderByDesc('created_at')
    ->get()
    ->map(function ($news) {
        $news->title = PlainText::normalize($news->title ?? '');
        $news->category = PlainText::normalize($news->category ?? '');
        $news->location = PlainText::normalize($news->location ?? '');

        return $news;
    });

    return view('staff.announcements', compact(
    'myRequests', 'myAnnouncements', 'myNews', 'email', 'name'
));
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
            'link' => ['nullable','string','max:255'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
            'ANNOUNCEMENT_CREATE',
            PlainText::normalize($request->input('title')),
            [
                'title' => PlainText::normalize($request->input('title')),
                'content' => RichText::sanitize($request->input('content')),
                'priority' => $request->input('priority'),
                'link' => $request->input('link'),
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
            'link' => ['nullable','string','max:255'],
            'priority' => ['required','in:HIGH,MEDIUM,LOW'],
        ]);

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int)$request->input('request_id') : null,
            'ANNOUNCEMENT_UPDATE',
            PlainText::normalize($request->input('title')),
            [
                'announcement_id' => (int)$request->announcement_id,
                'title' => PlainText::normalize($request->input('title')),
                'content' => RichText::sanitize($request->input('content')),
                'link' => $request->input('link'),
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

        $title = PlainText::normalize($request->title ?: 'Delete Announcement');

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

        $title = PlainText::normalize($request->title ?: 'Enable Announcement');

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

        $title = PlainText::normalize($request->title ?: 'Disable Announcement');

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
    if (!$request->hasFile('image')) {
        $request->request->remove('image');
    }

    $request->validate([
        'request_id' => ['nullable','integer'],
        'title' => ['required','string','max:255'],
        'content' => ['required','string'],
        'category' => ['required','string','max:100'],
        'link' => ['nullable','string','max:255'],
        'location' => ['nullable','string','max:255'],
        'existing_image_path' => ['nullable','string'],
        'remove_image' => ['nullable','in:0,1'],
    ]);

    if ($message = NewsImage::validationError($request->file('image'))) {
        throw ValidationException::withMessages(['image' => $message]);
    }

    EventAnnouncementValidation::validate($request);

    $requestId = $request->input('request_id') ? (int) $request->input('request_id') : null;
    $removeImage = (string) $request->input('remove_image', '0') === '1';

    $imagePath = $request->input('existing_image_path') ?: null;

    if ($requestId && !$imagePath) {
        $old = DB::table('approval_requests')
            ->where('id', $requestId)
            ->where('requester_email', (string) session('user_email'))
            ->first();

        if ($old) {
            $oldPayload = json_decode($old->details ?? '{}', true) ?: [];
            $imagePath = $oldPayload['image_path'] ?? null;
        }
    }

    if ($removeImage) {
        $imagePath = null;
    }

    if ($request->hasFile('image')) {
        $uploadedPath = NewsImage::store($request->file('image'));

        if (!$uploadedPath) {
            return response()->json([
                'ok' => false,
                'error' => 'Image upload failed.',
            ], 500);
        }

        $imagePath = $uploadedPath;
    }

    return $this->createOrUpdateRequest(
        $requestId,
        'NEWS_CREATE',
        PlainText::normalize($request->input('title')),
        [
            'title' => PlainText::normalize($request->input('title')),
            'content' => RichText::sanitize($request->input('content')),
            'category' => PlainText::normalize($request->input('category')),
            'link' => $request->input('link'),
            'location' => PlainText::normalize($request->input('location')),
            'image_path' => $imagePath,
        ]
    );
}

    public function requestUpdateNews(Request $request)
{
    if (!$request->hasFile('image')) {
        $request->request->remove('image');
    }

    $request->validate([
        'request_id' => ['nullable','integer'],
        'news_id' => ['required','integer','gt:0'],
        'title' => ['required','string','max:255'],
        'content' => ['required','string'],
        'category' => ['required','string','max:100'],
        'link' => ['nullable','string','max:255'],
        'location' => ['nullable','string','max:255'],
        'existing_image_path' => ['nullable','string'],
        'remove_image' => ['nullable','in:0,1'],
    ]);

    if ($message = NewsImage::validationError($request->file('image'))) {
        throw ValidationException::withMessages(['image' => $message]);
    }

    $existing = DB::table('news')->where('news_id', (int) $request->input('news_id'))->first();
    EventAnnouncementValidation::validate($request, $existing);

    $removeImage = (string) $request->input('remove_image', '0') === '1';
    $imagePath = $request->input('existing_image_path') ?: null;

    if ($removeImage) {
        $imagePath = null;
    }

    if ($request->hasFile('image')) {
        $uploadedPath = NewsImage::store($request->file('image'));

        if (!$uploadedPath) {
            return response()->json([
                'ok' => false,
                'error' => 'Image upload failed.',
            ], 500);
        }

        $imagePath = $uploadedPath;
    }

    $payload = [
        'news_id' => (int) $request->input('news_id'),
        'title' => PlainText::normalize($request->input('title')),
        'content' => RichText::sanitize($request->input('content')),
        'category' => PlainText::normalize($request->input('category')),
        'link' => $request->input('link'),
        'location' => PlainText::normalize($request->input('location')),
        'image_path' => $imagePath,
    ];

    return $this->createOrUpdateRequest(
        $request->input('request_id') ? (int) $request->input('request_id') : null,
        'NEWS_UPDATE',
        PlainText::normalize($request->input('title')),
        $payload
    );
}

    public function requestDeleteNews(Request $request)
    {
        $request->validate([
            'request_id' => ['nullable','integer'], // ✅
            'news_id' => ['required','integer','gt:0'],
            'title' => ['nullable','string','max:255'],
        ]);

        $title = PlainText::normalize($request->title ?: 'Delete News');

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

    $this->pushApproverNotifications(
        'INFO',
        'New Approval Request',
        "{$name} re-submitted a request."
    );

    $userId = (int)(session('user_id') ?? 0);

    // STAFF notif (FIXED)
    $this->pushSystemNotif(
        'INFO',
        'Request Submitted',
        "Your request is now PENDING.",
        'STAFF',
        $userId
    );

} else {

    $data['created_at'] = now();
    DB::table('approval_requests')->insert($data);

    $this->pushApproverNotifications(
        'INFO',
        'New Approval Request',
        "{$name} submitted a request."
    );

    $userId = (int)(session('user_id') ?? 0);

    // STAFF notif (FIXED)
    $this->pushSystemNotif(
        'INFO',
        'Request Submitted',
        "Your request is now PENDING.",
        'STAFF',
        $userId
    );
}

        return response()->json(['ok' => true]);
    }

    public function deleteRequestOnly($id)
{
    $req = ApprovalRequest::findOrFail($id);

    // OPTIONAL but recommended: only allow deleting own requests
    $userEmail = session('user_email') ?? null; // adjust if iba session key mo
    if ($userEmail && strtolower($req->requester_email) !== strtolower($userEmail)) {
        return response()->json(['message' => 'Not allowed.'], 403);
    }

    // Recommended: only allow delete if NOT approved (para di mabura approved history)
    $status = strtolower(trim((string)$req->status));
    if ($status === 'approved') {
        return response()->json(['message' => 'Approved requests cannot be deleted.'], 422);
    }

    // Delete REQUEST ONLY (does not touch announcements table)
    $req->delete();

    return response()->json(['ok' => true]);
}

    private function pushSystemNotif(string $type, string $title, string $message, ?string $targetRole, ?int $targetUserId = null): void
    {
        DB::table('notifications')->insert([
            'title'          => $title,
            'message'        => $message,
            'type'           => strtoupper($type),
            'channel'        => 'SYSTEM',
            'target_role'    => $targetRole,
            'target_user_id' => $targetUserId,
            'created_at'     => now(),
        ]);
    }

    private function pushApproverNotifications(string $type, string $title, string $message): void
    {
        foreach (['ADMIN', 'SUPERADMIN'] as $role) {
            $this->pushSystemNotif($type, $title, $message, $role, null);
        }
    }
}

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
use App\Services\ResendEmailService;

class AnnouncementController extends Controller
{
    public function index()
{
    $email  = strtolower(trim((string) session('user_email')));
    $normalizedEmail = $email;
    $name   = trim((string)session('user_first_name').' '.(string)session('user_last_name'));
    $userId = (int) (session('user_id') ?? 0);

    // Staff sees THEIR requests only (Announcements + News)
    $myRequests = DB::table('approval_requests')
        ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$normalizedEmail])
        ->whereIn('type', [
            'ANNOUNCEMENT_CREATE', 'ANNOUNCEMENT_UPDATE', 'ANNOUNCEMENT_DELETE',
            'ANNOUNCEMENT_ENABLE', 'ANNOUNCEMENT_DISABLE',
            'NEWS_CREATE', 'NEWS_UPDATE', 'NEWS_DELETE'
        ])
        ->orderByDesc('created_at')
        ->get();

    // ✅ Get announcement_ids that currently have PENDING requests (so we hide them from LIVE)
    $pendingAnnIds = DB::table('approval_requests')
        ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$normalizedEmail])
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
        ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$normalizedEmail])
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
    ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$normalizedEmail])
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
        ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$normalizedEmail])
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
        $email = strtolower(trim((string) session('user_email')));
        $name  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));

        if (!$email) {
            return response()->json(['ok' => false, 'error' => 'Missing session email. Please re-login.'], 422);
        }

        $request->validate([
            'request_id' => ['nullable','integer'], // ✅ for resubmitting/editing existing request (no duplicates)
            'title' => ['required','string','max:60'],
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
            'title' => ['required','string','max:60'],
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

    public function requestBulkDeleteAnnouncements(Request $request)
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

        $email = strtolower(trim((string) session('user_email')));
        $name = trim((string) session('user_first_name').' '.(string) session('user_last_name'));

        if (!$email) {
            return response()->json(['ok' => false, 'error' => 'Missing session email. Please re-login.'], 422);
        }

        $existingPendingIds = DB::table('approval_requests')
            ->where('type', 'ANNOUNCEMENT_DELETE')
            ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$email])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->get()
            ->map(function ($request) {
                $details = json_decode($request->details ?? '{}', true) ?: [];
                return (int) ($details['announcement_id'] ?? 0);
            })
            ->filter(fn ($id) => $id > 0)
            ->all();

        $targetIds = $ids->diff($existingPendingIds)->values();

        if ($targetIds->isEmpty()) {
            return response()->json([
                'ok' => true,
                'message' => 'Delete request(s) already pending for the selected announcement(s).',
            ]);
        }

        $announcements = DB::table('announcements')
            ->whereIn('announcement_id', $targetIds->all())
            ->get(['announcement_id', 'title']);

        foreach ($announcements as $announcement) {
            DB::table('approval_requests')->insert([
                'type' => 'ANNOUNCEMENT_DELETE',
                'title' => PlainText::normalize($announcement->title ?? 'Delete Announcement'),
                'details' => json_encode([
                    'announcement_id' => (int) $announcement->announcement_id,
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'pending',
                'requester_name' => $name ?: 'Staff',
                'requester_email' => $email,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $count = $announcements->count();

        if ($count > 0) {
            // Notify active admins/superadmins via Resend
            $adminEmails = DB::table('users')
                ->whereIn('role', ['Admin', 'Superadmin'])
                ->where('status', 'Active')
                ->pluck('email')
                ->toArray();

            if (!empty($adminEmails)) {
                $emailService = app(ResendEmailService::class);
                $emailService->sendPendingApprovalNotification($adminEmails, [
                    'type' => 'BULK_ANNOUNCEMENT_DELETE',
                    'title' => "$count Announcement(s) requested for deletion",
                    'requester_name' => $name ?: 'Staff',
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return response()->json([
            'ok' => true,
            'message' => $count.' delete request(s) submitted. Please wait for admin approval.',
        ]);
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
        'title' => ['required','string','max:60'],
        'content' => ['required','string'],
        'category' => ['required','string','max:100'],
        'link' => ['nullable','string','max:255'],
        'location' => ['nullable','string','max:60'],
        'expiration_date' => ['nullable','date'],
        'existing_image_path' => ['nullable','string'],
        'remove_image' => ['nullable','in:0,1'],
    ]);

    if ($message = NewsImage::validationError($request->file('image'))) {
        throw ValidationException::withMessages(['image' => $message]);
    }

    EventAnnouncementValidation::validate($request);

    $requestId = $request->input('request_id') ? (int) $request->input('request_id') : null;
    $removeImage = (string) $request->input('remove_image', '0') === '1';
    $email = strtolower(trim((string) session('user_email')));

    $imagePath = $request->input('existing_image_path') ?: null;

    if ($requestId && !$imagePath) {
        $old = DB::table('approval_requests')
            ->where('id', $requestId)
            ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$email])
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
            'expiration_date' => $request->input('expiration_date'),
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
        'title' => ['required','string','max:60'],
        'content' => ['required','string'],
        'category' => ['required','string','max:100'],
        'link' => ['nullable','string','max:255'],
        'location' => ['nullable','string','max:60'],
        'expiration_date' => ['nullable','date'],
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
        'expiration_date' => $request->input('expiration_date'),
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
        $email = strtolower(trim((string) session('user_email')));
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
        ->whereRaw('LOWER(TRIM(requester_email)) = ?', [$email])
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

        // Notify active admins/superadmins via Resend
        $adminEmails = DB::table('users')
            ->whereIn('role', ['Admin', 'Superadmin'])
            ->where('status', 'Active')
            ->pluck('email')
            ->toArray();

        if (!empty($adminEmails)) {
            $emailService = app(ResendEmailService::class);
            $emailService->sendPendingApprovalNotification($adminEmails, $data);
        }

        $response = ['ok' => true];

        if (str_starts_with($type, 'NEWS_')) {
            $response['news'] = [
                'news_id' => (int) ($payload['news_id'] ?? 0),
                'title' => PlainText::normalize($payload['title'] ?? ''),
                'content' => (string) ($payload['content'] ?? ''),
                'category' => PlainText::normalize($payload['category'] ?? ''),
                'location' => PlainText::normalize($payload['location'] ?? ''),
                'link' => (string) ($payload['link'] ?? ''),
                'image_path' => (string) ($payload['image_path'] ?? ''),
                'image_url' => NewsImage::url($payload['image_path'] ?? null),
            ];
        }

        return response()->json($response);
    }

    public function deleteRequestOnly($id)
{
    $req = ApprovalRequest::findOrFail($id);

    // OPTIONAL but recommended: only allow deleting own requests
    $userEmail = strtolower(trim((string) (session('user_email') ?? '')));
    if ($userEmail !== '' && strtolower(trim((string) $req->requester_email)) !== $userEmail) {
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

public function showRequestChanges($id)
{
    $req = ApprovalRequest::findOrFail($id);
    $userEmail = strtolower(trim((string) (session('user_email') ?? '')));

    if ($userEmail === '' || strtolower(trim((string) $req->requester_email)) !== $userEmail) {
        return response()->json(['ok' => false, 'message' => 'Not allowed.'], 403);
    }

    $payload = json_decode($req->details ?? '{}', true) ?: [];
    $type = strtoupper((string) $req->type);
    $status = strtolower(trim((string) $req->status));

    return response()->json([
        'ok' => true,
        'request' => [
            'id' => (int) $req->id,
            'type' => $type,
            'type_label' => $this->approvalTypeLabel($type),
            'status' => $status,
            'status_label' => $this->approvalStatusLabel($status),
            'needs_revision' => $status === 'rejected',
            'title' => PlainText::normalize($payload['title'] ?? $req->title ?? 'Request'),
            'submitted_at' => optional($req->created_at)->format('M d, Y h:i A'),
            'updated_at' => optional($req->updated_at)->format('M d, Y h:i A'),
            'rejection_reason' => (string) ($req->rejection_reason ?? ''),
        ],
        'fields' => $this->approvalChangeFields($type, $payload),
    ]);
}

private function approvalChangeFields(string $type, array $payload): array
{
    $original = $this->approvalOriginalValues($type, $payload);
    $updated = $this->approvalSubmittedValues($type, $payload, $original);
    $fields = [];

    foreach ($updated as $key => $field) {
        $originalValue = $original[$key]['value'] ?? null;
        $updatedValue = $field['value'] ?? null;
        $fieldType = (string) ($field['type'] ?? 'text');

        $fields[] = [
            'key' => $key,
            'label' => (string) ($field['label'] ?? $this->humanizeApprovalField($key)),
            'type' => $fieldType,
            'original' => $this->formatApprovalFieldValue($originalValue, $fieldType),
            'updated' => $this->formatApprovalFieldValue($updatedValue, $fieldType),
            'changed' => $this->approvalFieldChanged($originalValue, $updatedValue, $fieldType),
        ];
    }

    return $fields;
}

private function approvalSubmittedValues(string $type, array $payload, array $original): array
{
    if (str_starts_with($type, 'ANNOUNCEMENT_')) {
        if (in_array($type, ['ANNOUNCEMENT_DELETE', 'ANNOUNCEMENT_ENABLE', 'ANNOUNCEMENT_DISABLE'], true)) {
            return array_merge($original, [
                'requested_action' => ['label' => 'Requested Action', 'value' => $this->approvalTypeLabel($type), 'type' => 'text'],
            ]);
        }

        return [
            'title' => ['label' => 'Title', 'value' => $payload['title'] ?? '', 'type' => 'text'],
            'content' => ['label' => 'Description', 'value' => RichText::sanitize($payload['content'] ?? ''), 'type' => 'html'],
            'priority' => ['label' => 'Priority', 'value' => strtoupper((string) ($payload['priority'] ?? '')), 'type' => 'text'],
            'link' => ['label' => 'Link', 'value' => $payload['link'] ?? '', 'type' => 'text'],
        ];
    }

    if (str_starts_with($type, 'NEWS_')) {
        if ($type === 'NEWS_DELETE') {
            return array_merge($original, [
                'requested_action' => ['label' => 'Requested Action', 'value' => $this->approvalTypeLabel($type), 'type' => 'text'],
            ]);
        }

        return [
            'title' => ['label' => 'Title', 'value' => $payload['title'] ?? '', 'type' => 'text'],
            'content' => ['label' => 'Content', 'value' => RichText::sanitize($payload['content'] ?? ''), 'type' => 'html'],
            'category' => ['label' => 'Category', 'value' => $payload['category'] ?? '', 'type' => 'text'],
            'location' => ['label' => 'Venue / Location', 'value' => $payload['location'] ?? '', 'type' => 'text'],
            'link' => ['label' => 'Link', 'value' => $payload['link'] ?? '', 'type' => 'text'],
            'image_path' => ['label' => 'Uploaded Image', 'value' => $payload['image_path'] ?? '', 'type' => 'image'],
        ];
    }

    return [
        'details' => ['label' => 'Submitted Details', 'value' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'type' => 'text'],
    ];
}

private function approvalOriginalValues(string $type, array $payload): array
{
    if (str_starts_with($type, 'ANNOUNCEMENT_')) {
        $id = (int) ($payload['announcement_id'] ?? 0);
        if ($id <= 0) {
            return [];
        }

        $row = DB::table('announcements')->where('announcement_id', $id)->first();
        if (!$row) {
            return [];
        }

        return [
            'title' => ['label' => 'Title', 'value' => $row->title ?? '', 'type' => 'text'],
            'content' => ['label' => 'Description', 'value' => RichText::sanitize($row->content ?? ''), 'type' => 'html'],
            'priority' => ['label' => 'Priority', 'value' => strtoupper((string) ($row->priority ?? '')), 'type' => 'text'],
            'link' => ['label' => 'Link', 'value' => $row->link ?? '', 'type' => 'text'],
        ];
    }

    if (str_starts_with($type, 'NEWS_')) {
        $id = (int) ($payload['news_id'] ?? 0);
        if ($id <= 0) {
            return [];
        }

        $row = DB::table('news')->where('news_id', $id)->first();
        if (!$row) {
            return [];
        }

        return [
            'title' => ['label' => 'Title', 'value' => $row->title ?? '', 'type' => 'text'],
            'content' => ['label' => 'Content', 'value' => RichText::sanitize($row->content ?? ''), 'type' => 'html'],
            'category' => ['label' => 'Category', 'value' => $row->category ?? '', 'type' => 'text'],
            'location' => ['label' => 'Venue / Location', 'value' => $row->location ?? '', 'type' => 'text'],
            'link' => ['label' => 'Link', 'value' => $row->link ?? '', 'type' => 'text'],
            'image_path' => ['label' => 'Uploaded Image', 'value' => $row->image_path ?? '', 'type' => 'image'],
        ];
    }

    return [];
}

private function approvalFieldChanged(mixed $original, mixed $updated, string $type): bool
{
    if ($type === 'html') {
        return RichText::plainText((string) $original) !== RichText::plainText((string) $updated);
    }

    return trim((string) ($original ?? '')) !== trim((string) ($updated ?? ''));
}

private function formatApprovalFieldValue(mixed $value, string $type): array
{
    $raw = trim((string) ($value ?? ''));

    if ($type === 'image') {
        return [
            'raw' => $raw,
            'url' => NewsImage::url($raw),
        ];
    }

    return [
        'raw' => $raw,
    ];
}

private function approvalTypeLabel(string $type): string
{
    return match ($type) {
        'ANNOUNCEMENT_CREATE' => 'Create Announcement',
        'ANNOUNCEMENT_UPDATE' => 'Edit Announcement',
        'ANNOUNCEMENT_DELETE' => 'Delete Announcement',
        'ANNOUNCEMENT_ENABLE' => 'Enable Announcement',
        'ANNOUNCEMENT_DISABLE' => 'Disable Announcement',
        'NEWS_CREATE' => 'Create Article / Event',
        'NEWS_UPDATE' => 'Edit Article / Event',
        'NEWS_DELETE' => 'Delete Article / Event',
        default => 'Approval Request',
    };
}

private function approvalStatusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default => 'Needs Revision',
    };
}

private function humanizeApprovalField(string $key): string
{
    return ucwords(str_replace(['_', '-'], ' ', $key));
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

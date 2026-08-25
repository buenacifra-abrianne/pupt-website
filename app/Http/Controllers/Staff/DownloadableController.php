<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Support\DownloadableFile;
use App\Support\RichText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\ResendEmailService;

class DownloadableController extends Controller
{
    public function index()
    {
        $rawRole = session('role') ?? session('user_role') ?? '';
        $role = strtolower(trim((string) $rawRole));
        $normalizedRole = str_replace(' ', '_', $role);

        $email = (string) session('user_email');
        $userId = (int) (session('user_id') ?? 0);
        $name = trim((string) session('user_first_name') . ' ' . (string) session('user_last_name'));

        $downloadables = DB::table('downloadables')
            ->orderByDesc('created_at')
            ->get();

        $isFacultyPro = ($normalizedRole === 'faculty_pro');

        $myRequests = collect();
        $myApprovedDownloadables = collect();

        if ($isFacultyPro && $email !== '') {
            $myRequests = DB::table('approval_requests')
                ->where('requester_email', $email)
                ->whereIn('type', [
                    'DOWNLOADABLE_CREATE',
                    'DOWNLOADABLE_UPDATE',
                    'DOWNLOADABLE_DELETE',
                    'DOWNLOADABLE_CREATE',
                    'DOWNLOADABLE_UPDATE',
                    'DOWNLOADABLE_DELETE',
                ])
                ->orderByDesc('created_at')
                ->get();

            $pendingDownloadableIds = DB::table('approval_requests')
                ->where('requester_email', $email)
                ->where('status', 'pending')
                ->whereIn('type', [
                    'DOWNLOADABLE_UPDATE',
                    'DOWNLOADABLE_DELETE',
                    'DOWNLOADABLE_UPDATE',
                    'DOWNLOADABLE_DELETE',
                ])
                ->get()
                ->map(function ($row) {
                    $payload = json_decode($row->details ?? '{}', true) ?: [];
                    return (int) ($payload['downloadable_id'] ?? 0);
                })
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $myApprovedDownloadables = DB::table('downloadables')
                ->where('created_by', $userId)
                ->when(count($pendingDownloadableIds) > 0, function ($query) use ($pendingDownloadableIds) {
                    $query->whereNotIn('downloadable_id', $pendingDownloadableIds);
                })
                ->orderByDesc('created_at')
                ->get();
        }

        $readDownloadableIds = DB::table('downloadable_user_reads')
            ->where('user_id', $userId)
            ->pluck('downloadable_id')
            ->toArray();

        foreach ($downloadables as $downloadable) {
            $downloadable->is_read = in_array($downloadable->downloadable_id, $readDownloadableIds);
        }

        return view('staff.downloadables', [
            'isFacultyPro' => $isFacultyPro,
            'downloadables' => $downloadables,
            'myRequests' => $myRequests,
            'myApprovedDownloadables' => $myApprovedDownloadables,
            'email' => $email,
            'name' => $name,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $userId = session('user_id');

        if ($id && $userId) {
            DB::table('downloadable_user_reads')->updateOrInsert(
                ['downloadable_id' => $id, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function requestCreate(Request $request)
    {
        $email = (string) session('user_email');

        if ($email === '') {
            return response()->json([
                'ok' => false,
                'error' => 'Missing session email. Please re-login.',
            ], 422);
        }

        $request->validate([
            'request_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'file' => ['nullable', 'file', 'mimes:pdf,docx,doc'],
        ]);

        if ($message = DownloadableFile::validationError($request->file('file'))) {
            throw ValidationException::withMessages(['file' => $message]);
        }

        $requestId = $request->input('request_id') ? (int) $request->input('request_id') : null;

        $filePath = null;
        $originalFilename = null;

        if ($requestId) {
            $oldRequest = DB::table('approval_requests')
                ->where('id', $requestId)
                ->where('requester_email', $email)
                ->first();

            if ($oldRequest) {
                $oldPayload = json_decode($oldRequest->details ?? '{}', true) ?: [];
                $filePath = $oldPayload['file_path'] ?? null;
                $originalFilename = $oldPayload['original_filename'] ?? null;
            }
        }

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            $storedPath = DownloadableFile::store($uploadedFile, 'downloadables');
            if (!$storedPath) {
                return response()->json([
                    'ok' => false,
                    'error' => 'File upload failed.',
                ], 500);
            }

            if ($filePath) {
                DownloadableFile::delete($filePath);
            }

            $filePath = $storedPath;
            $originalFilename = $uploadedFile->getClientOriginalName();
        }

        if (!$filePath || !$originalFilename) {
            return response()->json([
                'ok' => false,
                'error' => 'A file is required.',
            ], 422);
        }

        return $this->createOrUpdateRequest(
            $requestId,
            'DOWNLOADABLE_CREATE',
            $request->input('title'),
            [
                'title' => $request->input('title'),
                'description' => RichText::sanitize($request->input('description')),
                'file_path' => $filePath,
                'original_filename' => $originalFilename,
            ]
        );
    }

    public function requestUpdate(Request $request)
    {
        $email = (string) session('user_email');

        if ($email === '') {
            return response()->json([
                'ok' => false,
                'error' => 'Missing session email. Please re-login.',
            ], 422);
        }

        $request->validate([
            'request_id' => ['nullable', 'integer'],
            'downloadable_id' => ['required', 'integer', 'gt:0'],
            'title' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:256'],
            'existing_file_path' => ['nullable', 'string'],
            'existing_original_filename' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,docx,doc'],
        ]);

        if ($message = DownloadableFile::validationError($request->file('file'))) {
            throw ValidationException::withMessages(['file' => $message]);
        }

        $filePath = $request->input('existing_file_path') ?: null;
        $originalFilename = $request->input('existing_original_filename') ?: null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $storedPath = DownloadableFile::store($uploadedFile, 'downloadables');

            if (!$storedPath) {
                return response()->json([
                    'ok' => false,
                    'error' => 'File upload failed.',
                ], 500);
            }

            $filePath = $storedPath;
            $originalFilename = $uploadedFile->getClientOriginalName();
        }

        return $this->createOrUpdateRequest(
            $request->input('request_id') ? (int) $request->input('request_id') : null,
            'DOWNLOADABLE_UPDATE',
            $request->input('title'),
            [
                'downloadable_id' => (int) $request->input('downloadable_id'),
                'title' => $request->input('title'),
                'description' => RichText::sanitize($request->input('description')),
                'file_path' => $filePath,
                'original_filename' => $originalFilename,
            ]
        );
    }

    public function requestDelete(Request $request)
{
    $request->validate([
        'request_id' => ['nullable', 'integer'],
        'downloadable_id' => ['required', 'integer', 'gt:0'],
        'title' => ['nullable', 'string', 'max:255'],
    ]);

    $downloadableId = (int) $request->input('downloadable_id');

    $row = DB::table('downloadables')
        ->where('downloadable_id', $downloadableId)
        ->first();

    if (!$row) {
        return response()->json([
            'ok' => false,
            'error' => 'Downloadable not found.',
        ], 404);
    }

    $title = $request->input('title') ?: ($row->title ?? 'Delete Downloadable');

    return $this->createOrUpdateRequest(
        $request->input('request_id') ? (int) $request->input('request_id') : null,
        'DOWNLOADABLE_DELETE',
        $title,
        [
            'downloadable_id' => $downloadableId,
            'title' => $row->title ?? $title,
            'description' => RichText::sanitize($row->description ?? ''),
            'file_path' => $row->file_path ?? null,
            'original_filename' => $row->original_filename ?? null,
        ]
    );
}

    public function deleteRequestOnly($id)
    {
        $req = ApprovalRequest::findOrFail($id);

        $userEmail = session('user_email') ?? null;
        if ($userEmail && strtolower((string) $req->requester_email) !== strtolower((string) $userEmail)) {
            return response()->json(['message' => 'Not allowed.'], 403);
        }

        $status = strtolower(trim((string) $req->status));
        if ($status === 'approved') {
            return response()->json(['message' => 'Approved requests cannot be deleted.'], 422);
        }

        $payload = json_decode($req->details ?? '{}', true) ?: [];
        $type = strtoupper(trim((string) $req->type));

        if (in_array($type, ['DOWNLOADABLE_CREATE', 'DOWNLOAD_CREATE'], true)) {
            $filePath = trim((string) ($payload['file_path'] ?? ''));
            if ($filePath !== '') {
                DownloadableFile::delete($filePath);
            }
        }

        $req->delete();

        return response()->json(['ok' => true]);
    }

    private function createOrUpdateRequest(?int $requestId, string $type, string $title, array $payload)
    {
        $email = (string) session('user_email');
        $name = trim((string) session('user_first_name') . ' ' . (string) session('user_last_name'));

        if ($email === '') {
            return response()->json([
                'ok' => false,
                'error' => 'Missing session email. Please re-login.',
            ], 422);
        }

        $data = [
            'type' => $type,
            'title' => $title,
            'details' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'requester_name' => $name !== '' ? $name : 'Staff',
            'requester_email' => $email,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'updated_at' => now(),
        ];

        if ($requestId) {
            DB::table('approval_requests')
                ->where('id', $requestId)
                ->where('requester_email', $email)
                ->update($data);

            $this->pushApproverNotifications(
                'INFO',
                'New Approval Request',
                "{$name} re-submitted a downloadable request."
            );
        } else {
            $data['created_at'] = now();

            DB::table('approval_requests')->insert($data);

            $this->pushApproverNotifications(
                'INFO',
                'New Approval Request',
                "{$name} submitted a downloadable request."
            );
        }

        $userId = (int) (session('user_id') ?? 0);

        $this->pushSystemNotif(
            'INFO',
            'Request Submitted',
            'Your downloadable request is now PENDING.',
            'STAFF',
            $userId > 0 ? $userId : null
        );

        // Notify active admins/superadmins via Resend
        $adminEmails = DB::table('users')
            ->whereIn('role', ['Admin', 'Superadmin', 'admin', 'superadmin'])
            ->where('status', 'Active')
            ->pluck('email')
            ->toArray();

        if (!empty($adminEmails)) {
            $emailService = app(ResendEmailService::class);
            $emailService->sendPendingApprovalNotification($adminEmails, $data);
        }

        return response()->json(['ok' => true]);
    }

    private function pushSystemNotif(string $type, string $title, string $message, ?string $targetRole, ?int $targetUserId = null): void
    {
        DB::table('notifications')->insert([
            'title' => $title,
            'message' => $message,
            'type' => strtoupper($type),
            'channel' => 'SYSTEM',
            'target_role' => $targetRole,
            'target_user_id' => $targetUserId,
            'created_at' => now(),
        ]);
    }

    private function pushApproverNotifications(string $type, string $title, string $message): void
    {
        foreach (['ADMIN', 'SUPERADMIN'] as $role) {
            $this->pushSystemNotif($type, $title, $message, $role, null);
        }
    }
}

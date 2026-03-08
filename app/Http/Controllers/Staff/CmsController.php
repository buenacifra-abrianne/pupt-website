<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\AuditLog;
use App\Support\CmsSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    public function index()
    {
        $role = CmsSections::normalizeRole((string) session('user_role'));
        $allowedTabs = CmsSections::tabsForRole($role);

        if (empty($allowedTabs)) {
            abort(403, 'No CMS tabs are assigned to your role.');
        }

        $tabDefs = CmsSections::tabDefinitions($allowedTabs);
        $contentsByTab = $this->loadContents($allowedTabs);
        $requestDraftsByTab = $this->loadLatestDraftRequests($allowedTabs);

        $pendingCount = collect($requestDraftsByTab)->filter(function ($row) {
            return strtolower((string) ($row['status'] ?? '')) === 'pending';
        })->count();

        return view('staff.content', [
            'tabDefs' => $tabDefs,
            'allowedTabs' => $allowedTabs,
            'contentsByTab' => $contentsByTab,
            'requestDraftsByTab' => $requestDraftsByTab,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function requestEdit(Request $request)
    {
        $role = CmsSections::normalizeRole((string) session('user_role'));
        $allowedTabs = CmsSections::tabsForRole($role);

        if (empty($allowedTabs)) {
            return response()->json([
                'ok' => false,
                'message' => 'No CMS tabs are assigned to your role.',
            ], 403);
        }

        $data = $request->validate([
            'tab_key' => ['required', Rule::in($allowedTabs)],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'request_id' => ['nullable', 'integer'],
        ]);

        $email = trim((string) session('user_email'));
        $name = trim((string) session('user_first_name').' '.(string) session('user_last_name'));
        if ($email === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Missing session email. Please re-login.',
            ], 422);
        }

        $tabKey = (string) $data['tab_key'];
        $tabLabel = CmsSections::labelForTab($tabKey);
        $type = CmsSections::requestTypeForTab($tabKey);

        if ($type === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Unsupported content section selected.',
            ], 422);
        }

        $live = $this->loadSingleContent($tabKey);
        $title = trim((string) ($data['title'] ?? ''));
        $content = (string) ($data['content'] ?? '');

        if ($title === '') {
            $title = $tabLabel.' Content';
        }

        $payload = [
            'tab_key' => $tabKey,
            'tab_label' => $tabLabel,
            'title' => $title,
            'content' => $content,
            'previous_title' => (string) ($live['title'] ?? ''),
            'previous_content' => (string) ($live['content'] ?? ''),
        ];

        $rowData = [
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

        $requestId = $this->storeOrUpdateRequest(
            $email,
            $type,
            $rowData,
            $data['request_id'] ?? null
        );

        $this->pushSystemNotif(
            'INFO',
            'New CMS Approval Request',
            ($name !== '' ? $name : $email).' submitted a content update for '.$tabLabel.'.',
            'ADMIN',
            null
        );

        $this->pushSystemNotif(
            'INFO',
            'CMS Request Submitted',
            'Your '.$tabLabel.' content update is now pending admin approval.',
            'STAFF',
            (int) (session('user_id') ?? 0)
        );

        AuditLog::record(
            'UPDATED',
            'CONTENT',
            'Submitted CMS edit request for '.$tabLabel,
            (int) (session('user_id') ?? 0)
        );

        return response()->json([
            'ok' => true,
            'request_id' => $requestId,
            'message' => 'Content request submitted for admin approval.',
        ]);
    }

    private function loadContents(array $tabKeys): array
    {
        $defaults = [];
        foreach ($tabKeys as $tabKey) {
            $defaults[$tabKey] = [
                'title' => CmsSections::labelForTab($tabKey).' Content',
                'content' => '',
            ];
        }

        if (!Schema::hasTable('cms_contents')) {
            return $defaults;
        }

        $rows = DB::table('cms_contents')
            ->whereIn('tab_key', $tabKeys)
            ->get()
            ->keyBy('tab_key');

        foreach ($tabKeys as $tabKey) {
            $row = $rows->get($tabKey);
            if (!$row) {
                continue;
            }

            $defaults[$tabKey] = [
                'title' => trim((string) ($row->title ?? '')) !== ''
                    ? (string) $row->title
                    : CmsSections::labelForTab($tabKey).' Content',
                'content' => (string) ($row->content ?? ''),
            ];
        }

        return $defaults;
    }

    private function loadSingleContent(string $tabKey): array
    {
        return $this->loadContents([$tabKey])[$tabKey] ?? [
            'title' => CmsSections::labelForTab($tabKey).' Content',
            'content' => '',
        ];
    }

    private function loadLatestDraftRequests(array $tabKeys): array
    {
        $email = trim((string) session('user_email'));
        if ($email === '') {
            return [];
        }

        $types = [];
        foreach ($tabKeys as $tabKey) {
            $type = CmsSections::requestTypeForTab($tabKey);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        if (empty($types)) {
            return [];
        }

        $rows = DB::table('approval_requests')
            ->where('requester_email', $email)
            ->whereIn('type', $types)
            ->whereIn('status', ['pending', 'rejected'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $tabKey = CmsSections::tabForRequestType((string) ($row->type ?? ''));
            if ($tabKey === null || isset($out[$tabKey])) {
                continue;
            }

            $payload = json_decode((string) ($row->details ?? '{}'), true) ?: [];
            $out[$tabKey] = [
                'id' => (int) $row->id,
                'status' => (string) ($row->status ?? ''),
                'title' => (string) ($payload['title'] ?? $row->title ?? ''),
                'content' => (string) ($payload['content'] ?? ''),
                'rejection_reason' => (string) ($row->rejection_reason ?? ''),
                'updated_at' => $row->updated_at,
            ];
        }

        return $out;
    }

    private function storeOrUpdateRequest(
        string $email,
        string $type,
        array $rowData,
        mixed $requestIdFromInput
    ): int {
        $requestId = is_numeric($requestIdFromInput) ? (int) $requestIdFromInput : 0;

        if ($requestId > 0) {
            $updated = DB::table('approval_requests')
                ->where('id', $requestId)
                ->where('requester_email', $email)
                ->where('type', $type)
                ->update($rowData);

            if ($updated > 0) {
                return $requestId;
            }
        }

        $existing = DB::table('approval_requests')
            ->where('requester_email', $email)
            ->where('type', $type)
            ->whereIn('status', ['pending', 'rejected'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            DB::table('approval_requests')
                ->where('id', (int) $existing->id)
                ->update($rowData);

            return (int) $existing->id;
        }

        $rowData['created_at'] = now();

        return (int) DB::table('approval_requests')->insertGetId($rowData);
    }

    private function pushSystemNotif(
        string $type,
        string $title,
        string $message,
        ?string $targetRole = null,
        ?int $targetUserId = null
    ): void {
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
}

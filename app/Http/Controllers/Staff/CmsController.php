<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\AuditLog;
use App\Support\CmsSections;
use App\Support\HomeCmsContent;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CmsController extends Controller
{
    public function index()
    {
        $roles = session('user_roles', [session('user_role')]);
        $allowedTabs = CmsSections::tabsForRoles($roles);

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
        $roles = session('user_roles', [session('user_role')]);
        $allowedTabs = CmsSections::tabsForRoles($roles);

        if (empty($allowedTabs)) {
            return response()->json([
                'ok' => false,
                'message' => 'No CMS tabs are assigned to your role.',
            ], 403);
        }

        $data = $request->validate([
            'tab_key' => ['required', Rule::in($allowedTabs)],
            'section_key' => ['nullable', Rule::in(['description', 'carousel'])],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'request_id' => ['nullable', 'integer'],
            'home' => ['nullable', 'array'],
            'home.campus_description' => ['nullable', 'string'],
            'home.campus_image' => ['nullable', 'string', 'max:2048'],
            'home.campus_image_file' => ['nullable', 'image', 'max:5120'],
            'home.carousel' => ['nullable', 'array'],
            'home.carousel.*.title' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.subtitle' => ['nullable', 'string', 'max:255'],
            'home.carousel.*.image' => ['nullable', 'string', 'max:2048'],
            'home.carousel.*.image_file' => ['nullable', 'image', 'max:5120'],
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
        $sectionKey = $tabKey === 'home'
            ? strtolower(trim((string) ($data['section_key'] ?? '')))
            : '';
        $sectionLabel = $this->homeSectionLabel($sectionKey);
        $type = CmsSections::requestTypeForTab($tabKey);

        if ($type === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Unsupported content section selected.',
            ], 422);
        }

        $live = $this->loadSingleContent($tabKey);
        $editableRequest = $this->loadEditableRequest($email, $type, $data['request_id'] ?? null);
        $editablePayload = $this->extractRequestPayload($editableRequest);

        $baseTitle = trim((string) ($editablePayload['title'] ?? $live['title'] ?? ''));
        if ($baseTitle === '') {
            $baseTitle = $tabLabel.' Content';
        }

        $baseContent = (string) ($editablePayload['content'] ?? $live['content'] ?? '');
        $title = trim((string) ($data['title'] ?? ''));
        $content = (string) ($data['content'] ?? '');

        if ($tabKey === 'home') {
            $baseHome = HomeCmsContent::fromStored($baseContent);
            $baseHomeEncoded = HomeCmsContent::encode($baseHome);
            $homeInput = is_array($data['home'] ?? null) ? $data['home'] : [];

            if ($sectionKey === 'description') {
                unset($homeInput['carousel'], $homeInput['carousel_slides']);
            } elseif ($sectionKey === 'carousel') {
                unset($homeInput['campus_description'], $homeInput['campus_image'], $homeInput['campus_title']);
            }

            if ($sectionKey !== 'carousel') {
                $campusImageUpload = $request->file('home.campus_image_file');
                if ($campusImageUpload instanceof UploadedFile) {
                    $homeInput['campus_image'] = $campusImageUpload->store('home/description', 'public');
                }
            }

            if ($sectionKey !== 'description') {
                $carouselUploads = $request->file('home.carousel', []);

                if (is_array($carouselUploads)) {
                    foreach ($carouselUploads as $index => $slideUpload) {
                        $upload = is_array($slideUpload) ? ($slideUpload['image_file'] ?? null) : null;
                        if (!$upload instanceof UploadedFile) {
                            continue;
                        }

                        $homeInput['carousel'][$index]['image'] = $upload->store('home/carousel', 'public');
                    }
                }
            }

            $content = HomeCmsContent::encode(
                HomeCmsContent::fromInput($homeInput, $baseHomeEncoded)
            );
            $title = $baseTitle;
            $baseContent = $baseHomeEncoded;
        } elseif ($title === '') {
            $title = $tabLabel.' Content';
        }

        if ($title === $baseTitle && $content === $baseContent) {
            return response()->json([
                'ok' => true,
                'no_changes' => true,
                'message' => 'No changes detected.',
            ]);
        }

        $payload = [
            'tab_key' => $tabKey,
            'tab_label' => $tabLabel,
            'title' => $title,
            'content' => $content,
            'previous_title' => (string) ($live['title'] ?? ''),
            'previous_content' => (string) ($live['content'] ?? ''),
            'section_key' => $sectionKey !== '' ? $sectionKey : null,
            'section_label' => $sectionLabel,
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
            $tabKey === 'home' && $sectionLabel !== ''
                ? 'Submitted CMS edit request for Home ('.$sectionLabel.')'
                : 'Submitted CMS edit request for '.$tabLabel,
            (int) (session('user_id') ?? 0)
        );

        $successMessage = 'Content request submitted for admin approval.';
        if ($tabKey === 'home' && $sectionLabel !== '') {
            $successMessage = 'Home '.$sectionLabel.' request submitted for approval.';
        }

        return response()->json([
            'ok' => true,
            'request_id' => $requestId,
            'message' => $successMessage,
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
                ->whereIn('status', ['pending', 'rejected'])
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

    private function loadEditableRequest(string $email, string $type, mixed $requestIdFromInput): ?object
    {
        $requestId = is_numeric($requestIdFromInput) ? (int) $requestIdFromInput : 0;

        if ($requestId > 0) {
            $row = DB::table('approval_requests')
                ->where('id', $requestId)
                ->where('requester_email', $email)
                ->where('type', $type)
                ->whereIn('status', ['pending', 'rejected'])
                ->first();

            if ($row) {
                return $row;
            }
        }

        return DB::table('approval_requests')
            ->where('requester_email', $email)
            ->where('type', $type)
            ->whereIn('status', ['pending', 'rejected'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    private function extractRequestPayload(?object $requestRow): array
    {
        if (!$requestRow) {
            return [];
        }

        $payload = json_decode((string) ($requestRow->details ?? '{}'), true);

        return is_array($payload) ? $payload : [];
    }

    private function homeSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'description' => 'Description',
            'carousel' => 'Carousel',
            default => '',
        };
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

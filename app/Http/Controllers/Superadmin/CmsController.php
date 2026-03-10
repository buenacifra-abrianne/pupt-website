<?php

namespace App\Http\Controllers\Superadmin;

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
    public function page()
    {
        $role = CmsSections::normalizeRole((string) session('user_role'));
        $allowedTabs = CmsSections::tabsForRole($role);

        if (empty($allowedTabs)) {
            abort(403, 'No CMS tabs are assigned to your role.');
        }

        $tabDefs = CmsSections::tabDefinitions($allowedTabs);
        $contentsByTab = $this->loadContents($allowedTabs);
        $pendingByTab = $this->loadPendingCountsByTab();
        $totalPending = array_sum($pendingByTab);
        $totalLiveContents = $this->countTotalContents($allowedTabs);

        return view('superadmin.content', [
            'tabDefs' => $tabDefs,
            'allowedTabs' => $allowedTabs,
            'contentsByTab' => $contentsByTab,
            'pendingByTab' => $pendingByTab,
            'totalPending' => $totalPending,
            'totalLiveContents' => $totalLiveContents,
        ]);
    }

    public function save(Request $request)
    {
        $role = CmsSections::normalizeRole((string) session('user_role'));
        $allowedTabs = CmsSections::tabsForRole($role);

        $data = $request->validate([
            'tab_key' => ['required', Rule::in($allowedTabs)],
            'section_key' => ['nullable', Rule::in(['description', 'carousel'])],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
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

        if (!Schema::hasTable('cms_contents')) {
            return response()->json([
                'ok' => false,
                'message' => 'cms_contents table not found. Please run migrations first.',
            ], 422);
        }

        $tabKey = (string) $data['tab_key'];
        $tabLabel = CmsSections::labelForTab($tabKey);
        $sectionKey = $tabKey === 'home'
            ? strtolower(trim((string) ($data['section_key'] ?? '')))
            : '';
        $sectionLabel = $this->homeSectionLabel($sectionKey);

        $existing = DB::table('cms_contents')
            ->where('tab_key', $tabKey)
            ->first();

        $currentTitle = trim((string) ($existing->title ?? ''));
        if ($currentTitle === '') {
            $currentTitle = $tabLabel.' Content';
        }

        $currentContent = (string) ($existing->content ?? '');
        $title = trim((string) ($data['title'] ?? ''));
        $content = (string) ($data['content'] ?? '');

        if ($tabKey === 'home') {
            $baseHome = HomeCmsContent::fromStored((string) ($existing->content ?? ''));
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
            $title = $currentTitle;
            $currentContent = $baseHomeEncoded;
        } elseif ($title === '') {
            $title = $tabLabel.' Content';
        }

        if ($title === $currentTitle && $content === $currentContent) {
            return response()->json([
                'ok' => true,
                'no_changes' => true,
                'message' => 'No changes detected.',
            ]);
        }

        if ($existing) {
            DB::table('cms_contents')
                ->where('tab_key', $tabKey)
                ->update([
                    'title' => $title,
                    'content' => $content,
                    'updated_by' => (int) (session('user_id') ?? 0),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('cms_contents')->insert([
                'tab_key' => $tabKey,
                'title' => $title,
                'content' => $content,
                'updated_by' => (int) (session('user_id') ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $auditMessage = 'Updated '.$tabLabel.' content directly as superadmin.';
        if ($tabKey === 'home' && $sectionLabel !== '') {
            $auditMessage = 'Updated Home content ('.$sectionLabel.') directly as superadmin.';
        }

        AuditLog::record(
            'UPDATED',
            'CONTENT',
            $auditMessage,
            (int) (session('user_id') ?? 0)
        );

        $successMessage = $tabLabel.' content saved successfully.';
        if ($tabKey === 'home' && $sectionLabel !== '') {
            $successMessage = 'Home '.$sectionLabel.' saved successfully.';
        }

        return response()->json([
            'ok' => true,
            'message' => $successMessage,
        ]);
    }

    private function homeSectionLabel(string $sectionKey): string
    {
        return match ($sectionKey) {
            'description' => 'Description',
            'carousel' => 'Carousel',
            default => '',
        };
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

    private function loadPendingCountsByTab(): array
    {
        $out = [];
        foreach (CmsSections::allTabKeys() as $tabKey) {
            $out[$tabKey] = 0;
        }

        $types = [];
        foreach (CmsSections::allTabKeys() as $tabKey) {
            $type = CmsSections::requestTypeForTab($tabKey);
            if ($type !== null) {
                $types[] = $type;
            }
        }

        if (empty($types)) {
            return $out;
        }

        $rows = DB::table('approval_requests')
            ->select('type', DB::raw('COUNT(*) as total'))
            ->where('status', 'pending')
            ->whereIn('type', $types)
            ->groupBy('type')
            ->get();

        foreach ($rows as $row) {
            $tabKey = CmsSections::tabForRequestType((string) ($row->type ?? ''));
            if ($tabKey === null) {
                continue;
            }

            $out[$tabKey] = (int) ($row->total ?? 0);
        }

        return $out;
    }

    private function countTotalContents(array $tabKeys): int
    {
        return count($tabKeys);
    }
}

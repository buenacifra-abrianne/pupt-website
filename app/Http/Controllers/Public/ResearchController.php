<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\ResearchCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResearchController extends Controller
{
    public function index(Request $request)
    {
        $researchCms = $this->loadResearchCms();

        return view('public.research', [
            'researchCms' => $researchCms,
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    public function strategicPlan(Request $request)
    {
        $researchCms = $this->loadResearchCms();

        return view('public.research_sdp', [
            'researchCms' => $researchCms,
            'sdp' => $researchCms['strategic_development_plan'] ?? ResearchCmsContent::defaults()['strategic_development_plan'],
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }

    private function loadResearchCms(): array
    {
        $researchCms = ResearchCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $row = DB::table('cms_contents')
                ->where('tab_key', 'research_extension')
                ->first();

            if ($row) {
                $researchCms = ResearchCmsContent::fromStored((string) ($row->content ?? ''));
            }
        }

        return $researchCms;
    }
}

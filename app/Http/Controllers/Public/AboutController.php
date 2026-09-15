<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\AboutCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class AboutController extends Controller
{
    public function index(Request $request)
    {
        return $this->renderPage($request);
    }

    public function show(Request $request, string $section)
    {
        return $this->renderPage($request, $section);
    }

    private function renderPage(Request $request, ?string $section = null)
    {
        $aboutCms = Cache::remember('public_about_cms', 300, function () {
            $cms = AboutCmsContent::defaults();
            if (Schema::hasTable('cms_contents')) {
                $aboutRow = DB::table('cms_contents')->where('tab_key', 'about')->first();
                if ($aboutRow) {
                    $cms = AboutCmsContent::fromStored((string) ($aboutRow->content ?? ''));
                }
            }
            return $cms;
        });

        $sections = $aboutCms['sections'] ?? [];
        $selectedSection = null;

        if ($section !== null) {
            abort_unless(isset($sections[$section]), 404);
            $selectedSection = $sections[$section];
        }

        return view('public.about', [
            'aboutCms' => $aboutCms,
            'sections' => $sections,
            'selectedSection' => $selectedSection,
            'cmsPreview' => $request->boolean('cms_preview'),
        ]);
    }
}

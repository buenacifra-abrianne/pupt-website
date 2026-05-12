<?php

namespace Tests\Unit;

use App\Support\AboutCmsContent;
use App\Support\CmsApprovalPreview;
use Tests\TestCase;

class CmsApprovalPreviewTest extends TestCase
{
    public function test_about_contents_image_change_renders_previous_and_requested_images(): void
    {
        $previous = AboutCmsContent::fromInput([
            'sections' => [
                'history' => [
                    'label' => 'History',
                    'summary' => 'Old summary',
                    'image' => 'assets/static_img/old-history.png',
                ],
            ],
        ]);

        $requested = AboutCmsContent::fromInput([
            'sections' => [
                'history' => [
                    'label' => 'History',
                    'summary' => 'Old summary',
                    'image' => 'about/sections/new-history.png',
                ],
            ],
        ], AboutCmsContent::encode($previous));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'about',
            'section_key' => 'contents',
            'section_label' => 'Contents',
            'previous_content' => AboutCmsContent::encode($previous),
            'content' => AboutCmsContent::encode($requested),
        ], 'CMS_ABOUT_EDIT');

        $this->assertStringContainsString('Previous', $html);
        $this->assertStringContainsString('Requested Update', $html);
        $this->assertStringContainsString('old-history.png', $html);
        $this->assertStringContainsString('new-history.png', $html);
        $this->assertStringContainsString('<img', $html);
    }

    public function test_missing_requested_image_renders_fallback_state(): void
    {
        $content = AboutCmsContent::encode(AboutCmsContent::fromInput([
            'overview' => [
                'story_image' => '',
            ],
        ]));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'about',
            'section_key' => 'intro',
            'section_label' => 'Intro',
            'content' => $content,
        ], 'CMS_ABOUT_EDIT');

        $this->assertStringContainsString('Story Image', $html);
        $this->assertStringContainsString('No image provided.', $html);
    }
}

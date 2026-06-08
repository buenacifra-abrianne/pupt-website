<?php

namespace Tests\Unit;

use App\Support\AboutCmsContent;
use App\Support\CmsApprovalPreview;
use App\Support\HomeCmsContent;
use Tests\TestCase;

class CmsApprovalPreviewTest extends TestCase
{
    public function test_text_only_change_hides_unchanged_image_fields(): void
    {
        $previous = AboutCmsContent::fromInput([
            'overview' => [
                'story_title' => 'Old title',
                'story_description' => 'Shared description',
                'story_image' => 'assets/static_img/story.png',
            ],
        ]);

        $requested = AboutCmsContent::fromInput([
            'overview' => [
                'story_title' => 'New title',
                'story_description' => 'Shared description',
                'story_image' => 'assets/static_img/story.png',
            ],
        ], AboutCmsContent::encode($previous));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'about',
            'section_key' => 'intro',
            'section_label' => 'Intro',
            'previous_content' => AboutCmsContent::encode($previous),
            'content' => AboutCmsContent::encode($requested),
        ], 'CMS_ABOUT_EDIT');

        $this->assertStringContainsString('Story Title', $html);
        $this->assertStringContainsString('New title', $html);
        $this->assertStringContainsString('Old title', $html);
        $this->assertStringContainsString('Current', $html);
        $this->assertStringContainsString('Requested Update', $html);
        $this->assertStringContainsString('approval-change-split', $html);
        $this->assertStringNotContainsString('story.png', $html);
        $this->assertStringNotContainsString('Story Image', $html);
    }

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

        $this->assertStringContainsString('Current', $html);
        $this->assertStringContainsString('Requested Update', $html);
        $this->assertStringContainsString('old-history.png', $html);
        $this->assertStringContainsString('new-history.png', $html);
        $this->assertStringContainsString('<img', $html);
    }

    public function test_campus_tour_video_change_renders_video_preview_and_hides_unchanged_facilities(): void
    {
        $previous = HomeCmsContent::fromInput([
            'campus_tour' => [
                'avp_video' => 'home/campus-tour/video/old-tour.mp4',
                'facilities' => [
                    ['name' => 'Library', 'image' => 'assets/static_img/library.png'],
                ],
            ],
        ]);

        $requested = HomeCmsContent::fromInput([
            'campus_tour' => [
                'avp_video' => 'home/campus-tour/video/new-tour.mp4',
                'facilities' => [
                    ['name' => 'Library', 'image' => 'assets/static_img/library.png'],
                ],
            ],
        ], HomeCmsContent::encode($previous));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'home',
            'section_key' => 'campus_tour_video',
            'section_label' => 'Campus Tour AVP',
            'previous_content' => HomeCmsContent::encode($previous),
            'content' => HomeCmsContent::encode($requested),
        ], 'CMS_HOME_EDIT');

        $this->assertStringContainsString('<video controls', $html);
        $this->assertStringContainsString('old-tour.mp4', $html);
        $this->assertStringContainsString('new-tour.mp4', $html);
        $this->assertStringContainsString('Current', $html);
        $this->assertStringContainsString('Requested Update', $html);
        $this->assertStringNotContainsString('Library', $html);
    }

    public function test_link_only_change_renders_clickable_link_without_unchanged_copy(): void
    {
        $previous = HomeCmsContent::fromInput([
            'quick_links' => [
                'tag' => 'Explore',
                'title' => 'Navigate the campus experience.',
                'description' => 'Shared description',
                'items' => [
                    [
                        'label' => 'About',
                        'title' => 'Know the campus',
                        'body' => 'Shared body copy',
                        'href' => 'https://example.com/original',
                    ],
                ],
            ],
        ]);

        $requested = HomeCmsContent::fromInput([
            'quick_links' => [
                'tag' => 'Explore',
                'title' => 'Navigate the campus experience.',
                'description' => 'Shared description',
                'items' => [
                    [
                        'label' => 'About',
                        'title' => 'Know the campus',
                        'body' => 'Shared body copy',
                        'href' => 'https://example.com/updated',
                    ],
                ],
            ],
        ], HomeCmsContent::encode($previous));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'home',
            'section_key' => 'quick_links',
            'section_label' => 'Explore Section',
            'previous_content' => HomeCmsContent::encode($previous),
            'content' => HomeCmsContent::encode($requested),
        ], 'CMS_HOME_EDIT');

        $this->assertStringContainsString('Href', $html);
        $this->assertStringContainsString('Open link', $html);
        $this->assertStringContainsString('https://example.com/original', $html);
        $this->assertStringContainsString('https://example.com/updated', $html);
        $this->assertStringContainsString('Current', $html);
        $this->assertStringContainsString('Requested Update', $html);
        $this->assertStringNotContainsString('Shared body copy', $html);
    }

    public function test_multiple_changed_fields_render_split_screen_blocks_for_each_change(): void
    {
        $previous = AboutCmsContent::fromInput([
            'overview' => [
                'story_title' => 'Before title',
                'story_description' => 'Before description',
                'story_image' => 'assets/static_img/story.png',
            ],
        ]);

        $requested = AboutCmsContent::fromInput([
            'overview' => [
                'story_title' => 'After title',
                'story_description' => 'After description',
                'story_image' => 'assets/static_img/story.png',
            ],
        ], AboutCmsContent::encode($previous));

        $html = CmsApprovalPreview::htmlForRequest([
            'tab_key' => 'about',
            'section_key' => 'intro',
            'section_label' => 'Intro',
            'previous_content' => AboutCmsContent::encode($previous),
            'content' => AboutCmsContent::encode($requested),
        ], 'CMS_ABOUT_EDIT');

        $this->assertStringContainsString('Before title', $html);
        $this->assertStringContainsString('After title', $html);
        $this->assertStringContainsString('Before description', $html);
        $this->assertStringContainsString('After description', $html);
        $this->assertStringContainsString('Story Title', $html);
        $this->assertStringContainsString('Story Description', $html);
        $this->assertSame(2, substr_count($html, 'approval-change-split'));
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

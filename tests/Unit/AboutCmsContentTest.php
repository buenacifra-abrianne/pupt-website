<?php

namespace Tests\Unit;

use App\Support\AboutCmsContent;
use PHPUnit\Framework\TestCase;

class AboutCmsContentTest extends TestCase
{
    public function test_history_title_edit_changes_normalized_content(): void
    {
        $base = $this->storedDefaults();
        $input = $this->historyInput([
            'title' => 'Updated school-site reservation title',
        ]);

        $content = AboutCmsContent::encode(AboutCmsContent::fromInput($input, $base));

        $this->assertNotSame($base, $content);
        $this->assertSame(
            'Updated school-site reservation title',
            AboutCmsContent::fromStored($content)['sections']['history']['timeline'][0]['title']
        );
    }

    public function test_history_period_edit_changes_normalized_content(): void
    {
        $base = $this->storedDefaults();
        $input = $this->historyInput([
            'period' => 'January 1960 - February 1961',
        ]);

        $content = AboutCmsContent::encode(AboutCmsContent::fromInput($input, $base));

        $this->assertNotSame($base, $content);
        $this->assertSame(
            'January 1960 - February 1961',
            AboutCmsContent::fromStored($content)['sections']['history']['timeline'][0]['period']
        );
    }

    public function test_history_body_paragraph_edit_changes_normalized_content(): void
    {
        $base = $this->storedDefaults();
        $input = $this->historyInput([
            'body_text' => "First updated paragraph.\r\n\r\nSecond updated paragraph.",
        ]);

        $content = AboutCmsContent::encode(AboutCmsContent::fromInput($input, $base));

        $this->assertNotSame($base, $content);
        $this->assertSame(
            ['First updated paragraph.', 'Second updated paragraph.'],
            AboutCmsContent::fromStored($content)['sections']['history']['timeline'][0]['body']
        );
    }

    public function test_empty_history_edit_has_no_normalized_changes(): void
    {
        $base = $this->storedDefaults();
        $content = AboutCmsContent::encode(AboutCmsContent::fromInput($this->historyInput([]), $base));

        $this->assertSame(
            AboutCmsContent::fromStored($base)['sections']['history'],
            AboutCmsContent::fromStored($content)['sections']['history']
        );
    }

    public function test_history_save_snapshot_resets_after_successful_save(): void
    {
        $base = $this->storedDefaults();
        $input = $this->historyInput([
            'title' => 'Updated school-site reservation title',
            'period' => 'January 1960 - February 1961',
            'body_text' => 'Updated paragraph after save.',
        ]);

        $saved = AboutCmsContent::encode(AboutCmsContent::fromInput($input, $base));
        $repeat = AboutCmsContent::encode(AboutCmsContent::fromInput($input, $saved));

        $this->assertNotSame($base, $saved);
        $this->assertSame($saved, $repeat);
    }

    public function test_clearing_official_name_and_body_changes_normalized_content(): void
    {
        $stored = AboutCmsContent::encode(AboutCmsContent::fromInput([
            'sections' => [
                'campus-officials' => [
                    'official_groups' => [
                        [
                            'title' => 'Campus Director',
                            'name' => 'Existing Official',
                            'body' => 'Existing official description.',
                            'image' => '',
                        ],
                    ],
                ],
            ],
        ]));

        $content = AboutCmsContent::encode(AboutCmsContent::fromInput([
            'sections' => [
                'campus-officials' => [
                    'official_groups' => [
                        [
                            'title' => 'Campus Director',
                            'name' => null,
                            'body' => null,
                            'image' => '',
                        ],
                    ],
                ],
            ],
        ], $stored));

        $official = AboutCmsContent::fromStored($content)['sections']['campus-officials']['official_groups'][0];

        $this->assertNotSame($stored, $content);
        $this->assertSame('', $official['name']);
        $this->assertSame('', $official['body']);
    }

    public function test_logo_seal_links_and_descriptions_are_normalized(): void
    {
        $base = $this->storedDefaults();

        $content = AboutCmsContent::encode(AboutCmsContent::fromInput([
            'sections' => [
                'logo-and-symbols' => [
                    'seals' => [
                        [
                            'id' => 'custom-seal',
                            'label' => 'Custom Seal',
                            'tag' => 'Record Seal',
                            'highlights_text' => "First highlight\nSecond highlight",
                            'information' => [
                                'title' => 'Informations about the Seal',
                                'description' => '<p>Custom info</p>',
                            ],
                            'reports' => [
                                'title' => 'Reports and Records',
                                'description' => '<p>Custom reports</p>',
                            ],
                            'links' => [
                                ['label' => 'Reference A', 'url' => 'https://example.com/a'],
                                ['label' => '', 'url' => ''],
                            ],
                        ],
                    ],
                ],
            ],
        ], $base));

        $seal = AboutCmsContent::fromStored($content)['sections']['logo-and-symbols']['seals'][0];

        $this->assertSame('custom-seal', $seal['id']);
        $this->assertSame('Custom Seal', $seal['label']);
        $this->assertSame(['First highlight', 'Second highlight'], $seal['highlights']);
        $this->assertSame('Informations about the Seal', $seal['information']['title']);
        $this->assertSame('<p>Custom info</p>', $seal['information']['description']);
        $this->assertSame('Reports and Records', $seal['reports']['title']);
        $this->assertSame('<p>Custom reports</p>', $seal['reports']['description']);
        $this->assertCount(1, $seal['links']);
        $this->assertSame('Reference A', $seal['links'][0]['label']);
        $this->assertSame('https://example.com/a', $seal['links'][0]['url']);
    }

    private function storedDefaults(): string
    {
        return AboutCmsContent::encode(AboutCmsContent::defaults());
    }

    private function historyInput(array $firstMilestoneOverrides): array
    {
        $history = AboutCmsContent::defaults()['sections']['history'];
        $timeline = $history['timeline'];
        $timeline[0] = array_merge($timeline[0], $firstMilestoneOverrides);
        if (array_key_exists('body_text', $firstMilestoneOverrides)) {
            unset($timeline[0]['body']);
        }

        return [
            'sections' => [
                'history' => [
                    'label' => $history['label'],
                    'summary' => $history['summary'],
                    'page_kicker' => $history['page_kicker'],
                    'page_title' => $history['page_title'],
                    'timeline' => $timeline,
                ],
            ],
        ];
    }
}

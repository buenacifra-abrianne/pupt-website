<?php

namespace Tests\Unit;

use App\Support\StudentsCmsContent;
use PHPUnit\Framework\TestCase;

class StudentsCmsContentTest extends TestCase
{
    public function test_from_cards_input_replaces_existing_cards_without_reusing_deleted_positions(): void
    {
        $stored = StudentsCmsContent::encode([
            'page' => [
                'eyebrow' => 'Student Services',
                'title' => 'Students',
                'description' => '',
                'hero_image' => 'assets/static_img/about_header_image.png',
            ],
            'cards' => [
                [
                    'title' => 'Student Handbook',
                    'description' => 'Existing handbook link',
                    'link' => 'https://example.com/handbook',
                ],
                [
                    'title' => 'Removed Card',
                    'description' => 'Should be removed',
                    'link' => 'https://example.com/removed',
                ],
            ],
        ]);

        $result = StudentsCmsContent::fromCardsInput([
            [
                'title' => 'Student Handbook',
                'description' => 'Existing handbook link',
                'link' => 'https://example.com/handbook',
            ],
        ], $stored);

        $this->assertSame('', $result['page']['description']);
        $this->assertCount(1, $result['cards']);
        $this->assertSame('Student Handbook', $result['cards'][0]['title']);
        $this->assertSame('https://example.com/handbook', $result['cards'][0]['link']);
    }

    public function test_from_organizations_input_replaces_existing_items_and_preserves_other_sections(): void
    {
        $stored = StudentsCmsContent::encode([
            'page' => [
                'eyebrow' => 'Student Services',
                'title' => 'Students',
                'description' => 'Original intro',
                'hero_image' => 'assets/static_img/about_header_image.png',
            ],
            'cards' => [
                [
                    'title' => 'Student Handbook',
                    'description' => 'Existing handbook link',
                    'link' => 'https://example.com/handbook',
                ],
            ],
            'organization_sections' => [
                [
                    'key' => 'academic',
                    'title' => 'Academic Organizations',
                    'items' => [
                        [
                            'title' => 'Old Organization',
                            'abbr' => 'OLD',
                            'link' => 'https://example.com/old',
                            'image' => 'assets/static_img/students/studentorgs/org1.png',
                        ],
                    ],
                ],
            ],
        ]);

        $result = StudentsCmsContent::fromOrganizationsInput([
            [
                'key' => 'academic',
                'title' => 'Academic Organizations',
                'items' => [
                    [
                        'title' => 'Computer Society',
                        'abbr' => 'CSS',
                        'link' => 'https://example.com/css',
                        'image' => 'assets/static_img/students/studentorgs/org2.png',
                    ],
                ],
            ],
        ], $stored);

        $this->assertSame('Original intro', $result['page']['description']);
        $this->assertCount(1, $result['cards']);
        $this->assertCount(2, $result['organization_sections']);
        $this->assertSame('Computer Society', $result['organization_sections'][0]['items'][0]['title']);
        $this->assertSame('CSS', $result['organization_sections'][0]['items'][0]['abbr']);
        $this->assertSame('Non-Academic Student Organizations', $result['organization_sections'][1]['title']);
    }
}

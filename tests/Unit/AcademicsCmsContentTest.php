<?php

namespace Tests\Unit;

use App\Support\AcademicsCmsContent;
use PHPUnit\Framework\TestCase;

class AcademicsCmsContentTest extends TestCase
{
    public function test_graduate_programs_are_removed_from_defaults_and_stored_content(): void
    {
        $defaults = AcademicsCmsContent::defaults();

        $this->assertNotContains(
            'public.graduate-programs',
            array_column($defaults['contents']['items'], 'route')
        );
        $this->assertArrayNotHasKey('graduate-programs', $defaults['pages']);

        $stored = AcademicsCmsContent::encode([
            'contents' => [
                'items' => [
                    [
                        'label' => 'Graduate Programs',
                        'summary' => 'Old graduate program card.',
                        'image' => 'assets/static_img/pupillar.jpeg',
                        'route' => 'public.graduate-programs',
                    ],
                    [
                        'label' => 'Degree Programs',
                        'summary' => 'Degree program card.',
                        'image' => 'assets/static_img/pupillar.jpeg',
                        'route' => 'public.degree-programs',
                    ],
                ],
            ],
            'pages' => [
                'graduate-programs' => [
                    'hero' => [
                        'title' => 'Graduate Programs',
                    ],
                ],
            ],
        ]);

        $content = AcademicsCmsContent::fromStored($stored);

        $this->assertSame(['public.degree-programs'], array_column($content['contents']['items'], 'route'));
        $this->assertArrayNotHasKey('graduate-programs', $content['pages']);
    }
}

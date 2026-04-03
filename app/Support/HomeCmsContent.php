<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class HomeCmsContent
{
    private const DEFAULTS = [
        'campus_title' => 'PUP TAGUIG CAMPUS',
        'campus_description' => "Quality and relevant education. These are the key words and\nthe main objective for the establishment of the Polytechnic\nUniversity of the Philippines Taguig Campus.",
        'campus_image' => 'assets/static_img/pupillar.jpeg',
        'announcements' => [],
        'hero' => [
            'crest_heading' => "A LEADING\nCOMPREHENSIVE\nPOLYTECHNIC\nUNIVERSITY IN\nASIA",
            'crest_year' => '2026',
        ],
        'updates' => [
            'tag' => 'Home',
            'title' => 'Campus Updates',
            'description' => 'Check out the latest events, news and updates of our Sintang Paaralan!',
        ],
        'quick_links' => [
            'tag' => 'Explore',
            'title' => 'Navigate the campus experience.',
            'items' => [
                [
                    'label' => 'About',
                    'title' => 'Know the campus',
                    'body' => 'Explore the campus profile, identity, and institutional story.',
                    'href' => '/about',
                ],
                [
                    'label' => 'Academics',
                    'title' => 'Browse programs',
                    'body' => 'See the academic offerings and learning environment available to students.',
                    'href' => '/academics',
                ],
                [
                    'label' => 'Students',
                    'title' => 'Student services',
                    'body' => 'Access student-centered information, updates, and support channels.',
                    'href' => '/students',
                ],
                [
                    'label' => 'Events',
                    'title' => 'Events',
                    'body' => 'View all Events from Upcoming and Incoming events of the Campus.',
                    'href' => '/events',
                ],
                [
                    'label' => 'Research & Extension',
                    'title' => 'Research Tools',
                    'body' => 'Open the PUP research and extension portals, tools, and institutional resources.',
                    'href' => '/research',
                ],
            ],
        ],
        'feedback' => [
            'tag' => 'Feedback',
            'title' => 'Help improve the public experience',
            'description' => 'Share questions, issues, or suggestions through the campus feedback form.',
            'button_label' => 'Open Feedback Form',
        ],
        'carousel_slides' => [
            [
                'title' => 'Welcome to PUP Taguig Campus',
                'subtitle' => 'Excellence in Technical Education',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'Academic Excellence',
                'subtitle' => 'Preparing Leaders for Tomorrow',
                'image' => 'assets/static_img/graduates.jpg',
            ],
            [
                'title' => 'Student Life',
                'subtitle' => 'Building Community and Character',
                'image' => 'assets/static_img/studentbody.jpg',
            ],
        ],
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function fromStored(?string $raw): array
    {
        $defaults = self::defaults();
        $content = trim((string) $raw);

        if ($content === '') {
            return $defaults;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            $defaults['campus_description'] = $content;

            return $defaults;
        }

        if (isset($decoded['home']) && is_array($decoded['home'])) {
            $decoded = $decoded['home'];
        }

        return self::normalize($decoded, $defaults);
    }

    public static function fromInput(mixed $input, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);
        $source = is_array($input) ? $input : [];

        return self::normalize($source, $base);
    }

    public static function encode(array $data): string
    {
        return (string) json_encode(
            self::normalize($data, self::defaults()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public static function resolveImagePath(?string $path, string $fallbackPath): string
    {
        $value = trim((string) $path);
        if ($value === '') {
            return asset(ltrim($fallbackPath, '/'));
        }

        if (preg_match('/^(https?:)?\/\//i', $value) === 1 || str_starts_with($value, 'data:')) {
            return $value;
        }

        $normalized = ltrim($value, '/');

        if (str_starts_with($normalized, 'assets/') || str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        if (Storage::disk('public')->exists($normalized)) {
            return asset('storage/'.$normalized);
        }

        return asset($normalized);
    }

    private static function normalize(array $source, array $base): array
    {
        $defaults = self::defaults();

        $normalized = [
            'campus_title' => self::sanitizeString(
                $source['campus_title'] ?? $base['campus_title'] ?? $defaults['campus_title'],
                255,
                $defaults['campus_title']
            ),
            'campus_description' => self::sanitizeString(
                $source['campus_description'] ?? $base['campus_description'] ?? $defaults['campus_description'],
                5000,
                $defaults['campus_description']
            ),
            'campus_image' => self::sanitizeString(
                $source['campus_image'] ?? $base['campus_image'] ?? $defaults['campus_image'],
                2048,
                $defaults['campus_image']
            ),
            'announcements' => self::normalizeAnnouncements(
                $source['announcements'] ?? ($base['announcements'] ?? [])
            ),
            'hero' => self::normalizeHero(
                $source['hero'] ?? ($base['hero'] ?? [])
            ),
            'updates' => self::normalizeUpdates(
                $source['updates'] ?? ($base['updates'] ?? [])
            ),
            'quick_links' => self::normalizeQuickLinks(
                $source['quick_links'] ?? ($base['quick_links'] ?? [])
            ),
            'feedback' => self::normalizeFeedback(
                $source['feedback'] ?? ($base['feedback'] ?? [])
            ),
            'carousel_slides' => self::normalizeSlides(
                $source['carousel_slides'] ?? $source['carousel'] ?? null,
                $base['carousel_slides'] ?? $defaults['carousel_slides']
            ),
        ];

        return $normalized;
    }

    private static function normalizeAnnouncements(mixed $input): array
    {
        $items = is_array($input) ? array_values($input) : [];
        $out = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $content = trim((string) ($item['content'] ?? ''));
            $date = self::sanitizeAnnouncementDate($item['date'] ?? null);

            if ($title === '' && $content === '') {
                continue;
            }

            $out[] = [
                'title' => self::sanitizeString($title, 255, ''),
                'content' => self::sanitizeString($content, 5000, ''),
                'date' => $date,
            ];

            if (count($out) >= 10) {
                break;
            }
        }

        return $out;
    }

    private static function sanitizeAnnouncementDate(mixed $value): string
    {
        $date = trim((string) $value);

        if ($date === '') {
            return '';
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : '';
    }

    private static function normalizeHero(mixed $input): array
    {
        $defaults = self::defaults()['hero'];
        $source = is_array($input) ? $input : [];

        return [
            'crest_heading' => self::sanitizeString(
                $source['crest_heading'] ?? $defaults['crest_heading'],
                255,
                $defaults['crest_heading']
            ),
            'crest_year' => self::sanitizeString(
                $source['crest_year'] ?? $defaults['crest_year'],
                50,
                $defaults['crest_year']
            ),
        ];
    }

    private static function normalizeUpdates(mixed $input): array
    {
        $defaults = self::defaults()['updates'];
        $source = is_array($input) ? $input : [];

        return [
            'tag' => self::sanitizeString(
                $source['tag'] ?? $defaults['tag'],
                80,
                $defaults['tag']
            ),
            'title' => self::sanitizeString(
                $source['title'] ?? $defaults['title'],
                255,
                $defaults['title']
            ),
            'description' => self::sanitizeString(
                $source['description'] ?? $defaults['description'],
                5000,
                $defaults['description']
            ),
        ];
    }

    private static function normalizeQuickLinks(mixed $input): array
    {
        $defaults = self::defaults()['quick_links'];
        $source = is_array($input) ? $input : [];
        $baseItems = is_array($source['items'] ?? null) ? array_values($source['items']) : [];
        $defaultItems = $defaults['items'];
        $items = [];

        foreach ($defaultItems as $index => $defaultItem) {
            $current = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : [];

            $items[] = [
                'label' => self::sanitizeString(
                    $current['label'] ?? $defaultItem['label'],
                    255,
                    $defaultItem['label']
                ),
                'title' => self::sanitizeString(
                    $current['title'] ?? $defaultItem['title'],
                    255,
                    $defaultItem['title']
                ),
                'body' => self::sanitizeString(
                    $current['body'] ?? $defaultItem['body'],
                    5000,
                    $defaultItem['body']
                ),
                'href' => self::sanitizeString(
                    $current['href'] ?? $defaultItem['href'],
                    2048,
                    $defaultItem['href']
                ),
            ];
        }

        return [
            'tag' => self::sanitizeString(
                $source['tag'] ?? $defaults['tag'],
                80,
                $defaults['tag']
            ),
            'title' => self::sanitizeString(
                $source['title'] ?? $defaults['title'],
                255,
                $defaults['title']
            ),
            'items' => $items,
        ];
    }

    private static function normalizeFeedback(mixed $input): array
    {
        $defaults = self::defaults()['feedback'];
        $source = is_array($input) ? $input : [];

        return [
            'tag' => self::sanitizeString(
                $source['tag'] ?? $defaults['tag'],
                80,
                $defaults['tag']
            ),
            'title' => self::sanitizeString(
                $source['title'] ?? $defaults['title'],
                255,
                $defaults['title']
            ),
            'description' => self::sanitizeString(
                $source['description'] ?? $defaults['description'],
                5000,
                $defaults['description']
            ),
            'button_label' => self::sanitizeString(
                $source['button_label'] ?? $defaults['button_label'],
                120,
                $defaults['button_label']
            ),
        ];
    }

    private static function normalizeSlides(mixed $input, array $baseSlides): array
    {
        $defaults = self::defaults()['carousel_slides'];
        $inputSlides = is_array($input) ? array_values($input) : [];
        $normalized = [];

        for ($i = 0; $i < 3; $i++) {
            $defaultSlide = $defaults[$i] ?? ['title' => '', 'subtitle' => '', 'image' => ''];
            $baseSlide = $baseSlides[$i] ?? $defaultSlide;
            $current = is_array($inputSlides[$i] ?? null) ? $inputSlides[$i] : [];

            $normalized[] = [
                'title' => self::sanitizeString(
                    $current['title'] ?? $baseSlide['title'] ?? $defaultSlide['title'],
                    255,
                    $defaultSlide['title']
                ),
                'subtitle' => self::sanitizeString(
                    $current['subtitle'] ?? $baseSlide['subtitle'] ?? $defaultSlide['subtitle'],
                    255,
                    $defaultSlide['subtitle']
                ),
                'image' => self::sanitizeString(
                    $current['image'] ?? $baseSlide['image'] ?? $defaultSlide['image'],
                    2048,
                    $defaultSlide['image']
                ),
            ];
        }

        return $normalized;
    }

    private static function sanitizeString(mixed $value, int $maxLen, string $fallback): string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return $fallback;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class EventsCmsContent
{
    private const DEFAULTS = [
        'page' => [
            'eyebrow' => 'Campus Calendar',
            'title' => 'Events',
            'description' => 'Explore upcoming academic, research, campus, and student life activities happening in PUP Taguig.',
        ],
        'cards' => [
            [
                'title' => 'Student Leadership Assembly',
                'summary' => 'A campus-wide gathering for student leaders to align on student programs, service projects, and the academic term ahead.',
                'content' => '<p>Student organizations and class officers are invited to the <strong>Student Leadership Assembly</strong> for updates on campus priorities, collaboration opportunities, and upcoming student programs.</p>',
                'image' => 'assets/static_img/pupillar.jpeg',
                'location' => 'Main Auditorium',
                'event_date' => '2026-04-20',
                'start_time' => '09:00',
                'end_time' => '12:00',
                'category' => 'student-life',
                'featured' => true,
            ],
            [
                'title' => 'Research Colloquium',
                'summary' => 'Faculty and student researchers present current studies, prototypes, and extension work with the campus community.',
                'content' => '<p>The <strong>Research Colloquium</strong> highlights selected studies, innovations, and extension initiatives from faculty and student researchers.</p>',
                'image' => 'assets/static_img/pupillar.jpeg',
                'location' => 'Research Center',
                'event_date' => '2026-04-24',
                'start_time' => '13:00',
                'end_time' => '16:00',
                'category' => 'research',
                'featured' => false,
            ],
            [
                'title' => 'Academic Advising Week',
                'summary' => 'Students can meet with advisers to review academic plans, enrollment concerns, and readiness for the next term.',
                'content' => '<p><strong>Academic Advising Week</strong> gives students time to review course plans, graduation timelines, and enrollment concerns with faculty advisers.</p>',
                'image' => 'assets/static_img/pupillar.jpeg',
                'location' => 'Academic Affairs Office',
                'event_date' => '2026-04-28',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'category' => 'academic',
                'featured' => false,
            ],
        ],
    ];

    private const CATEGORY_OPTIONS = [
        'academic' => 'Academic',
        'events' => 'Events',
        'research' => 'Research',
        'student-life' => 'Student Life',
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    public static function categoryOptions(): array
    {
        return self::CATEGORY_OPTIONS;
    }

    public static function categoryLabel(?string $value): string
    {
        $key = strtolower(trim((string) $value));

        return self::CATEGORY_OPTIONS[$key] ?? self::CATEGORY_OPTIONS['events'];
    }

    public static function fromStored(?string $raw): array
    {
        $content = trim((string) $raw);

        if ($content === '') {
            return self::defaults();
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return self::defaults();
        }

        if (isset($decoded['events']) && is_array($decoded['events'])) {
            $decoded = $decoded['events'];
        }

        return self::normalize($decoded, self::defaults());
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

        return [
            'page' => self::normalizePage(
                is_array($source['page'] ?? null) ? $source['page'] : [],
                is_array($base['page'] ?? null) ? $base['page'] : $defaults['page'],
                $defaults['page']
            ),
            'cards' => self::normalizeCards(
                $source['cards'] ?? [],
                $base['cards'] ?? $defaults['cards'],
                $defaults['cards']
            ),
        ];
    }

    private static function normalizePage(array $source, array $base, array $defaults): array
    {
        return [
            'eyebrow' => self::pickString($source, $base, $defaults, 'eyebrow', 120),
            'title' => self::pickString($source, $base, $defaults, 'title'),
            'description' => self::pickString($source, $base, $defaults, 'description', 5000),
        ];
    }

    private static function normalizeCards(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = is_array($base) ? array_values($base) : [];
        $cards = [];

        foreach ($sourceItems as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $baseItem = is_array($baseItems[$index] ?? null)
                ? $baseItems[$index]
                : [
                    'title' => '',
                    'summary' => '',
                    'content' => '',
                    'image' => '',
                    'location' => '',
                    'event_date' => '',
                    'start_time' => '',
                    'end_time' => '',
                    'category' => 'events',
                    'featured' => false,
                ];

            $normalized = [
                'title' => self::sanitizeString((string) ($item['title'] ?? ($baseItem['title'] ?? '')), 255, ''),
                'summary' => self::sanitizeString((string) ($item['summary'] ?? ($baseItem['summary'] ?? '')), 3000, ''),
                'content' => self::sanitizeString((string) ($item['content'] ?? ($baseItem['content'] ?? '')), 20000, ''),
                'image' => self::sanitizeString((string) ($item['image'] ?? ($baseItem['image'] ?? '')), 2048, ''),
                'location' => self::sanitizeString((string) ($item['location'] ?? ($baseItem['location'] ?? '')), 255, ''),
                'event_date' => self::sanitizeDate($item['event_date'] ?? ($baseItem['event_date'] ?? '')),
                'start_time' => self::sanitizeTime($item['start_time'] ?? ($baseItem['start_time'] ?? '')),
                'end_time' => self::sanitizeTime($item['end_time'] ?? ($baseItem['end_time'] ?? '')),
                'category' => self::sanitizeCategory($item['category'] ?? ($baseItem['category'] ?? 'events')),
                'featured' => self::toBool($item['featured'] ?? ($baseItem['featured'] ?? false)),
            ];

            if (
                $normalized['title'] === ''
                && $normalized['summary'] === ''
                && $normalized['content'] === ''
                && $normalized['location'] === ''
                && $normalized['event_date'] === ''
            ) {
                continue;
            }

            $cards[] = $normalized;

            if (count($cards) >= 24) {
                break;
            }
        }

        if (empty($cards) && !empty($defaults)) {
            return array_values($defaults);
        }

        return $cards;
    }

    private static function sanitizeCategory(mixed $value): string
    {
        $key = strtolower(trim((string) $value));

        return array_key_exists($key, self::CATEGORY_OPTIONS) ? $key : 'events';
    }

    private static function sanitizeDate(mixed $value): string
    {
        $date = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : '';
    }

    private static function sanitizeTime(mixed $value): string
    {
        $time = trim((string) $value);

        return preg_match('/^\d{2}:\d{2}$/', $time) === 1 ? $time : '';
    }

    private static function pickString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        $value = $source[$key] ?? ($base[$key] ?? ($defaults[$key] ?? ''));

        return self::sanitizeString((string) $value, $maxLen, (string) ($defaults[$key] ?? ''));
    }

    private static function sanitizeString(string $value, int $maxLen, string $fallback): string
    {
        $text = trim($value);

        if ($text === '') {
            $text = trim($fallback);
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

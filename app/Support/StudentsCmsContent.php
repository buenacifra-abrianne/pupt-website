<?php

namespace App\Support;

class StudentsCmsContent
{
    private const DEFAULTS = [
        'page' => [
            'eyebrow' => 'Student Services',
            'title' => 'Students',
            'description' => '',
            'contents_tag' => 'Contents',
            'contents_title' => 'Student Services',
            'contents_description' => '',
            'hero_image' => 'assets/static_img/about_header_image.png',
        ],
        'cards' => [
            [
                'title' => 'Student Handbook',
                'description' => 'Guidelines, policies, and procedures that govern student life at PUP Taguig Campus.',
                'link' => 'https://drive.google.com/file/d/0B1BuDAuN0r8SX1BWX2NSN3FURzg/view?resourcekey=0-oi8lUy9PCFysh0FDyL5ipw',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'PUPSIS',
                'description' => 'Access the PUP Student Information System for enrollment, grades, and academic records.',
                'link' => 'https://sis.pup.edu.ph/',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'ODRS',
                'description' => 'Request official documents and records online through the PUP Online Document Request System.',
                'link' => 'https://odrs.pup.edu.ph/',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'Downloadable Forms',
                'description' => 'Access and download official forms needed for various student transactions and requests.',
                'link' => '#',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
        ],
        'organization_sections' => [
            [
                'key' => 'academic',
                'title' => 'Academic Student Organizations',
                'items' => [
                    [
                        'title' => 'Association of Electronics Engineering Students',
                        'abbr' => 'AEES',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Computer Society PUP Taguig',
                        'abbr' => 'CS - PUPT',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Junior Marketing Association',
                        'abbr' => 'PUPT JMA',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Junior Philippine Institute of Accountants',
                        'abbr' => 'JPIA - PUP Taguig',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Junior People Management Association of the Philippines',
                        'abbr' => 'PMAP Junior - PUPT',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Wisdom Values Education',
                        'abbr' => 'WVE',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Philippine Association of Students in Office Administration',
                        'abbr' => 'Dura Lex Sed Lex',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'Philippine Society of Mechanical Engineers',
                        'abbr' => 'PSME - PUPT Student Unit',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                ],
            ],
            [
                'key' => 'non_academic',
                'title' => 'Non-Academic Student Organizations',
                'items' => [
                    [
                        'title' => 'Emergency Response Group',
                        'abbr' => 'ERG - "Serving with a Purpose"',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'iROCK Campus',
                        'abbr' => 'Established 2015',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'PUP UKAW',
                        'abbr' => 'UKAW',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                    [
                        'title' => 'PUP-REC',
                        'abbr' => 'REC',
                        'link' => '#',
                        'image' => 'assets/static_img/pupillar.jpeg',
                    ],
                ],
            ],
        ],
    ];

    public static function defaults(): array
    {
        return self::DEFAULTS;
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

        if (isset($decoded['students']) && is_array($decoded['students'])) {
            $decoded = $decoded['students'];
        }

        return self::normalize($decoded, self::defaults());
    }

    public static function fromInput(mixed $input, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);
        $source = is_array($input) ? $input : [];

        return self::normalize($source, $base);
    }

    public static function fromCardsInput(mixed $cardsInput, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);
        $source = is_array($cardsInput) ? $cardsInput : [];
        $pageInput = is_array($source['page'] ?? null) ? $source['page'] : ($base['page'] ?? self::defaults()['page']);
        $normalizedCardsInput = array_key_exists('cards', $source)
            ? ($source['cards'] ?? [])
            : $source;

        return self::normalize([
            'page' => is_array($pageInput) ? $pageInput : ($base['page'] ?? self::defaults()['page']),
            'cards' => is_array($normalizedCardsInput) ? $normalizedCardsInput : [],
            'organization_sections' => $base['organization_sections'] ?? self::defaults()['organization_sections'],
        ], $base);
    }

    public static function fromOrganizationsInput(mixed $sectionsInput, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);

        return self::normalize([
            'page' => $base['page'] ?? self::defaults()['page'],
            'cards' => $base['cards'] ?? self::defaults()['cards'],
            'organization_sections' => is_array($sectionsInput) ? $sectionsInput : [],
        ], $base);
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
        return (string) (ImageStorage::url($path, $fallbackPath) ?? asset(ltrim($fallbackPath, '/')));
    }

    private static function normalize(array $source, array $base): array
    {
        $defaults = self::defaults();
        $pageSource = is_array($source['page'] ?? null)
            ? $source['page']
            : (is_array($base['page'] ?? null) ? $base['page'] : $defaults['page']);
        $cardsSource = array_key_exists('cards', $source)
            ? ($source['cards'] ?? [])
            : ($base['cards'] ?? $defaults['cards']);
        $organizationSectionsSource = array_key_exists('organization_sections', $source)
            ? ($source['organization_sections'] ?? [])
            : ($base['organization_sections'] ?? $defaults['organization_sections']);

        return [
            'page' => self::normalizePage(
                is_array($pageSource) ? $pageSource : [],
                is_array($base['page'] ?? null) ? $base['page'] : $defaults['page'],
                $defaults['page']
            ),
            'cards' => self::normalizeCards(
                $cardsSource,
                is_array($base['cards'] ?? null) ? $base['cards'] : $defaults['cards'],
                $defaults['cards']
            ),
            'organization_sections' => self::normalizeOrganizationSections(
                $organizationSectionsSource,
                is_array($base['organization_sections'] ?? null) ? $base['organization_sections'] : $defaults['organization_sections'],
                $defaults['organization_sections']
            ),
        ];
    }

    private static function normalizePage(array $source, array $base, array $defaults): array
    {
        return [
            'eyebrow' => self::pickString($source, $base, $defaults, 'eyebrow', 120),
            'title' => self::pickString($source, $base, $defaults, 'title'),
            'description' => self::pickString($source, $base, $defaults, 'description', 5000),
            'contents_tag' => self::pickString($source, $base, $defaults, 'contents_tag', 120),
            'contents_title' => self::pickString($source, $base, $defaults, 'contents_title'),
            'contents_description' => self::pickString($source, $base, $defaults, 'contents_description', 5000),
            'hero_image' => self::pickOptionalString($source, $base, $defaults, 'hero_image', 2048),
        ];
    }

    private static function normalizeCards(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = is_array($base) ? array_values($base) : [];
        $defaultItems = is_array($defaults) ? array_values($defaults) : [];
        $cards = [];

        foreach ($sourceItems as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $defaultItem = is_array($defaultItems[$index] ?? null)
                ? $defaultItems[$index]
                : ['title' => '', 'description' => '', 'link' => '', 'image' => 'assets/static_img/pupillar.jpeg'];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $normalized = [
                'title' => self::sanitizeString((string) ($item['title'] ?? ($baseItem['title'] ?? '')), 255, ''),
                'description' => self::sanitizeString((string) ($item['description'] ?? ($baseItem['description'] ?? '')), 5000, ''),
                'link' => self::sanitizeString((string) ($item['link'] ?? ($baseItem['link'] ?? '')), 2048, ''),
                'image' => array_key_exists('image', $item)
                    ? self::sanitizeOptionalString((string) $item['image'], 2048)
                    : self::sanitizeString((string) ($baseItem['image'] ?? 'assets/static_img/pupillar.jpeg'), 2048, 'assets/static_img/pupillar.jpeg'),
            ];
            $hasExplicitImage = trim((string) ($item['image'] ?? '')) !== '';

            if (
                $normalized['title'] === ''
                && $normalized['description'] === ''
                && $normalized['link'] === ''
                && !$hasExplicitImage
            ) {
                continue;
            }

            $cards[] = $normalized;

            if (count($cards) >= 24) {
                break;
            }
        }

        return $cards;
    }

    private static function normalizeOrganizationSections(mixed $input, array $base, array $defaults): array
    {
        $sourceSections = is_array($input) ? array_values($input) : [];
        $baseSections = is_array($base) ? array_values($base) : [];
        $defaultSections = is_array($defaults) ? array_values($defaults) : [];
        $sections = [];

        foreach ($defaultSections as $index => $defaultSection) {
            if (!is_array($defaultSection)) {
                continue;
            }

            $sourceSection = is_array($sourceSections[$index] ?? null) ? $sourceSections[$index] : [];
            $baseSection = is_array($baseSections[$index] ?? null) ? $baseSections[$index] : $defaultSection;
            $defaultItems = is_array($defaultSection['items'] ?? null) ? array_values($defaultSection['items']) : [];
            $sourceItems = is_array($sourceSection['items'] ?? null) ? array_values($sourceSection['items']) : [];
            $baseItems = is_array($baseSection['items'] ?? null) ? array_values($baseSection['items']) : $defaultItems;
            $items = [];

            foreach ($sourceItems as $itemIndex => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $defaultItem = is_array($defaultItems[$itemIndex] ?? null)
                    ? $defaultItems[$itemIndex]
                    : ['title' => '', 'abbr' => '', 'link' => '#', 'image' => 'assets/static_img/pupillar.jpeg'];
                $baseItem = is_array($baseItems[$itemIndex] ?? null) ? $baseItems[$itemIndex] : $defaultItem;

                $normalizedItem = [
                    'title' => self::sanitizeString((string) ($item['title'] ?? ($baseItem['title'] ?? '')), 255, ''),
                    'abbr' => self::sanitizeString((string) ($item['abbr'] ?? ($baseItem['abbr'] ?? '')), 255, ''),
                    'link' => self::sanitizeString((string) ($item['link'] ?? ($baseItem['link'] ?? '#')), 2048, '#'),
                    'image' => array_key_exists('image', $item)
                        ? self::sanitizeOptionalString((string) $item['image'], 2048)
                        : self::sanitizeString((string) ($baseItem['image'] ?? 'assets/static_img/pupillar.jpeg'), 2048, 'assets/static_img/pupillar.jpeg'),
                ];

                if (
                    $normalizedItem['title'] === ''
                    && $normalizedItem['abbr'] === ''
                    && $normalizedItem['link'] === ''
                ) {
                    continue;
                }

                $items[] = $normalizedItem;

                if (count($items) >= 24) {
                    break;
                }
            }

            $sections[] = [
                'key' => self::sanitizeString((string) ($defaultSection['key'] ?? ($baseSection['key'] ?? 'section_'.$index)), 80, 'section_'.$index),
                'title' => self::sanitizeString((string) ($sourceSection['title'] ?? ($baseSection['title'] ?? ($defaultSection['title'] ?? ''))), 255, (string) ($defaultSection['title'] ?? '')),
                'items' => $items,
            ];
        }

        return $sections;
    }

    private static function pickString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        $value = $source[$key] ?? ($base[$key] ?? ($defaults[$key] ?? ''));

        return self::sanitizeString((string) $value, $maxLen, (string) ($defaults[$key] ?? ''));
    }

    private static function pickOptionalString(array $source, array $base, array $defaults, string $key, int $maxLen = 255): string
    {
        if (array_key_exists($key, $source)) {
            return self::sanitizeOptionalString((string) $source[$key], $maxLen);
        }

        return self::pickString($source, $base, $defaults, $key, $maxLen);
    }

    private static function sanitizeString(string $value, int $maxLen, string $fallback): string
    {
        $text = trim(HtmlEntities::decode($value));

        if ($text === '') {
            $text = trim(HtmlEntities::decode($fallback));
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }

    private static function sanitizeOptionalString(string $value, int $maxLen): string
    {
        $text = trim(HtmlEntities::decode($value));

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }
}

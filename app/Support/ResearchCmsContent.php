<?php

namespace App\Support;

class ResearchCmsContent
{
    private const DEFAULTS = [
        'page' => [
            'eyebrow' => '',
            'title' => 'Research and Extension',
            'description' => 'Discover the campus initiatives, scholarly work, and community-centered extension programs that connect PUP Taguig with industry, partner institutions, and the wider public.',
            'hero_image' => 'assets/static_img/pupillar.jpeg',
        ],
        'cards' => [
            [
                'title' => 'Research',
                'description' => 'Explore research initiatives, publications, and scholarly works conducted at PUP Taguig Campus.',
                'link' => '/research',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'Extension',
                'description' => 'Community outreach and extension programs that connect PUP Taguig with the wider community.',
                'link' => '/research',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
            [
                'title' => 'Strategic Development Plan',
                'description' => 'Discover the campus strategic development plan aligning academic priorities and growth.',
                'link' => '/research/strategic-development-plan',
                'image' => 'assets/static_img/pupillar.jpeg',
            ],
        ],
        'strategic_development_plan' => [
            'label' => 'Strategic Development Plan',
            'lead' => 'The campus strategic development plan aligns academic priorities, student support, facilities, and partnerships toward sustainable institutional growth.',
            'development_priorities' => [
                [
                    'title' => 'Instructional Excellence',
                    'body' => 'Continue improving program delivery, learning outcomes, and faculty support systems.',
                ],
                [
                    'title' => 'Student Success',
                    'body' => 'Expand services that improve access, retention, wellbeing, and holistic student development.',
                ],
                [
                    'title' => 'Infrastructure and Digital Readiness',
                    'body' => 'Upgrade classrooms, laboratories, connectivity, and campus systems that support learning and operations.',
                ],
                [
                    'title' => 'Research and Community Engagement',
                    'body' => 'Strengthen initiatives that connect scholarship with community needs and industry collaboration.',
                ],
                [
                    'title' => 'Good Governance',
                    'body' => 'Promote data-informed planning, transparent processes, and continuous quality improvement.',
                ],
            ],
            'plan_principles' => [
                'Set measurable targets for instruction, services, and campus operations.',
                'Use feedback and evidence to improve policies, programs, and resource allocation.',
                'Build partnerships that extend learning opportunities and community impact.',
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

        if (isset($decoded['research']) && is_array($decoded['research'])) {
            $decoded = $decoded['research'];
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

        return self::normalize([
            'page' => $base['page'] ?? self::defaults()['page'],
            'cards' => is_array($cardsInput) ? $cardsInput : [],
            'strategic_development_plan' => $base['strategic_development_plan'] ?? self::defaults()['strategic_development_plan'],
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
        $sdpSource = is_array($source['strategic_development_plan'] ?? null)
            ? $source['strategic_development_plan']
            : (is_array($base['strategic_development_plan'] ?? null) ? $base['strategic_development_plan'] : $defaults['strategic_development_plan']);

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
            'strategic_development_plan' => self::normalizeSdp(
                is_array($sdpSource) ? $sdpSource : [],
                is_array($base['strategic_development_plan'] ?? null) ? $base['strategic_development_plan'] : $defaults['strategic_development_plan'],
                $defaults['strategic_development_plan']
            ),
        ];
    }

    private static function normalizePage(array $source, array $base, array $defaults): array
    {
        return [
            'eyebrow' => self::pickString($source, $base, $defaults, 'eyebrow', 120),
            'title' => self::pickString($source, $base, $defaults, 'title'),
            'description' => self::pickString($source, $base, $defaults, 'description', 5000),
            'hero_image' => self::pickOptionalString($source, $base, $defaults, 'hero_image', 2048),
        ];
    }

    private static function normalizeSdp(array $source, array $base, array $defaults): array
    {
        $prioritiesSource = array_key_exists('development_priorities', $source)
            ? ($source['development_priorities'] ?? [])
            : ($base['development_priorities'] ?? $defaults['development_priorities']);

        $priorities = [];
        foreach ((is_array($prioritiesSource) ? array_values($prioritiesSource) : []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim(HtmlEntities::decode((string) ($item['title'] ?? '')));
            $body = trim((string) ($item['body'] ?? ''));
            if ($title === '' && $body === '') {
                continue;
            }
            $priorities[] = [
                'title' => mb_substr($title, 0, 255),
                'body' => mb_substr($body, 0, 5000),
            ];
            if (count($priorities) >= 24) {
                break;
            }
        }

        $principlesSource = array_key_exists('plan_principles', $source)
            ? ($source['plan_principles'] ?? [])
            : ($base['plan_principles'] ?? $defaults['plan_principles']);
        $principles = [];
        foreach ((is_array($principlesSource) ? array_values($principlesSource) : []) as $item) {
            $text = trim(HtmlEntities::decode((string) $item));
            if ($text !== '') {
                $principles[] = mb_substr($text, 0, 500);
            }
        }

        return [
            'label' => self::pickString($source, $base, $defaults, 'label'),
            'lead' => self::pickString($source, $base, $defaults, 'lead', 5000),
            'development_priorities' => $priorities !== [] ? $priorities : ($base['development_priorities'] ?? $defaults['development_priorities']),
            'plan_principles' => $principles !== [] ? $principles : ($base['plan_principles'] ?? $defaults['plan_principles']),
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

        if (array_key_exists($key, $base)) {
            return self::sanitizeOptionalString((string) $base[$key], $maxLen);
        }

        return self::sanitizeOptionalString((string) ($defaults[$key] ?? ''), $maxLen);
    }

    private static function sanitizeOptionalString(string $value, int $maxLen): string
    {
        $text = trim(HtmlEntities::decode($value));

        if ($text === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
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
}

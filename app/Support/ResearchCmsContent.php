<?php

namespace App\Support;

class ResearchCmsContent
{
    private const DEFAULTS = [
        'page' => [
            'eyebrow' => 'Research & Extension',
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
        ], $base);
    }

    public static function encode(array $data): string
    {
        return (string) json_encode(
            self::normalize($data, self::defaults()),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
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

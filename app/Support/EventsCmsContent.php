<?php

namespace App\Support;

class EventsCmsContent
{
    private const DEFAULTS = [
        'page' => [
            'eyebrow' => 'Campus Calendar',
            'title' => 'Events',
            'description' => 'Explore upcoming academic, research, campus, and student life activities happening in PUP Taguig.',
        ],
        'cards' => [],
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

    public static function fromCardsInput(mixed $cardsInput, ?string $fallbackStored = null): array
    {
        $base = self::fromStored($fallbackStored);
        $sourceCards = is_array($cardsInput) ? $cardsInput : [];

        return [
            'page' => self::normalizePage(
                [],
                is_array($base['page'] ?? null) ? $base['page'] : self::defaults()['page'],
                self::defaults()['page']
            ),
            'cards' => self::normalizeCards($sourceCards, [], self::defaults()['cards']),
        ];
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

    public static function displayCollections(iterable $cards, ?string $today = null): array
    {
        $sortedCards = collect($cards)
            ->filter(fn ($card) => is_array($card))
            ->sortBy(fn (array $card) => self::cardSortKey($card))
            ->values();

        $todayKey = self::sanitizeDate($today ?? '') ?: now()->toDateString();

        $activeCards = $sortedCards
            ->reject(fn (array $card) => self::isExpiredCard($card, $todayKey))
            ->values();

        $expiredCards = $sortedCards
            ->filter(fn (array $card) => self::isExpiredCard($card, $todayKey))
            ->sortByDesc(fn (array $card) => self::cardSortKey($card))
            ->values();

        return [
            'all' => $sortedCards,
            'active' => $activeCards,
            'expired' => $expiredCards,
            'featured' => $activeCards->first(fn (array $card) => self::toBool($card['featured'] ?? false)),
            'ongoing' => $activeCards
                ->filter(fn (array $card) => ($card['event_date'] ?? '') === $todayKey)
                ->values(),
            'upcoming' => $activeCards
                ->filter(fn (array $card) => ($card['event_date'] ?? '') > $todayKey)
                ->values(),
        ];
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

        if (empty($cards)) {
            return [];
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

    private static function cardSortKey(array $card): string
    {
        return ($card['event_date'] ?? '9999-12-31')
            .'|'.($card['start_time'] ?? '99:99')
            .'|'.($card['title'] ?? '');
    }

    private static function isExpiredCard(array $card, string $today): bool
    {
        $eventDate = self::sanitizeDate($card['event_date'] ?? '');

        return $eventDate !== '' && $eventDate < $today;
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

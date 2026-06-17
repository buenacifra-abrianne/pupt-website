<?php

namespace App\Support;

class EventsCmsCardValidation
{
    public const FEATURED_CONFLICT_MESSAGE = 'A featured event already exists. Only one featured event is allowed at a time.';

    public static function validateCardsSubmission(mixed $cardsInput, ?int $activeCardIndex = null): array
    {
        $cards = is_array($cardsInput) ? $cardsInput : [];
        $errors = [];
        $featuredIndexes = [];

        foreach ($cards as $index => $card) {
            if (!is_array($card)) {
                continue;
            }

            if (self::toBool($card['featured'] ?? false)) {
                $featuredIndexes[] = (string) $index;
            }
        }

        if (count($featuredIndexes) > 1) {
            foreach (array_slice($featuredIndexes, 1) as $index) {
                $errors["events.cards.{$index}.featured"][] = self::FEATURED_CONFLICT_MESSAGE;
            }
        }

        if ($activeCardIndex === null) {
            return $errors;
        }

        $activeCard = $cards[$activeCardIndex] ?? $cards[(string) $activeCardIndex] ?? null;
        if (!is_array($activeCard) || !self::cardHasAnyValue($activeCard)) {
            return $errors;
        }

        $requiredFields = [
            'title' => 'Event Title is required.',
            'category' => 'Category is required.',
            'event_date' => 'Event Date is required.',
            'location' => 'Location is required.',
            'start_time' => 'Start Time is required.',
            'end_time' => 'End Time is required.',
            'content' => 'Event Details is required.',
        ];

        foreach ($requiredFields as $field => $message) {
            if (self::fieldValue($activeCard, $field) === '') {
                $errors["events.cards.{$activeCardIndex}.{$field}"][] = $message;
            }
        }

        return $errors;
    }

    private static function cardHasAnyValue(array $card): bool
    {
        $fields = [
            'title',
            'category',
            'location',
            'event_date',
            'start_time',
            'end_time',
            'content',
            'summary',
            'image',
            'featured',
        ];

        foreach ($fields as $field) {
            if (self::fieldValue($card, $field) !== '') {
                return true;
            }
        }

        return false;
    }

    private static function fieldValue(array $card, string $field): string
    {
        $value = $card[$field] ?? '';

        if ($field === 'content' || $field === 'summary') {
            return self::plainText((string) $value);
        }

        if ($field === 'featured') {
            return self::toBool($value) ? '1' : '';
        }

        return trim((string) $value);
    }

    private static function plainText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\x{00a0}/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}

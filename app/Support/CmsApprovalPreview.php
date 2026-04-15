<?php

namespace App\Support;

class CmsApprovalPreview
{
    public static function titleForRequest(array $payload, string $type): string
    {
        $tabKey = (string) ($payload['tab_key'] ?? CmsSections::tabForRequestType($type));
        $tabLabel = CmsSections::labelForTab($tabKey);
        $sectionKey = trim((string) ($payload['section_key'] ?? ''));
        $sectionLabel = trim((string) ($payload['section_label'] ?? self::sectionLabelFor($tabKey, $sectionKey)));

        if ($sectionLabel !== '') {
            return $tabLabel.' - '.$sectionLabel;
        }

        $title = trim((string) ($payload['title'] ?? ''));

        return $title !== '' ? $title : $tabLabel.' Content';
    }

    public static function htmlForRequest(array $payload, string $type): string
    {
        $tabKey = (string) ($payload['tab_key'] ?? CmsSections::tabForRequestType($type));
        $sectionKey = trim((string) ($payload['section_key'] ?? ''));
        $sectionLabel = trim((string) ($payload['section_label'] ?? self::sectionLabelFor($tabKey, $sectionKey)));

        $requested = self::extractSectionData($tabKey, $sectionKey, (string) ($payload['content'] ?? ''));
        $previous = self::extractSectionData($tabKey, $sectionKey, (string) ($payload['previous_content'] ?? ''));

        $normalizedRequested = self::normalizeComparable($requested);
        $normalizedPrevious = self::normalizeComparable($previous);

        if ($normalizedPrevious === '' || $normalizedPrevious === $normalizedRequested) {
            return self::renderPanel('Requested Section', $sectionLabel, $requested);
        }

        return implode('', [
            self::renderSectionHeading($sectionLabel),
            '<div style="display:grid; gap:14px;">',
            self::renderValuePanel('Previous', $previous),
            self::renderValuePanel('Requested Update', $requested),
            '</div>',
        ]);
    }

    private static function extractSectionData(string $tabKey, string $sectionKey, string $encoded): mixed
    {
        $encoded = trim($encoded);
        if ($encoded === '') {
            return null;
        }

        try {
            $decoded = match ($tabKey) {
                'home' => HomeCmsContent::fromStored($encoded),
                'about' => AboutCmsContent::fromStored($encoded),
                'academics' => AcademicsCmsContent::fromStored($encoded),
                'students' => StudentsCmsContent::fromStored($encoded),
                'research_extension' => ResearchCmsContent::fromStored($encoded),
                'events' => EventsCmsContent::fromStored($encoded),
                default => json_decode($encoded, true),
            };
        } catch (\Throwable) {
            $decoded = json_decode($encoded, true);
        }

        if (!is_array($decoded)) {
            return $encoded;
        }

        return self::sliceSection($tabKey, $sectionKey, $decoded);
    }

    private static function sliceSection(string $tabKey, string $sectionKey, array $decoded): mixed
    {
        if ($sectionKey === '') {
            return $decoded;
        }

        return match ($tabKey) {
            'home' => match ($sectionKey) {
                'description' => self::onlyKeys($decoded, ['campus_title', 'campus_description', 'campus_image']),
                'carousel' => [
                    'hero' => $decoded['hero'] ?? [],
                    'carousel_slides' => $decoded['carousel_slides'] ?? ($decoded['carousel'] ?? []),
                ],
                'updates' => $decoded['updates'] ?? [],
                'quick_links' => $decoded['quick_links'] ?? [],
                'feedback' => $decoded['feedback'] ?? [],
                default => $decoded,
            },
            'about' => self::sliceAboutSection($sectionKey, $decoded),
            'academics' => match ($sectionKey) {
                'hero' => ['hero' => self::onlyKeys($decoded['hero'] ?? [], ['title', 'image'])],
                'contents' => $decoded['contents'] ?? [],
                'intro' => $decoded['intro'] ?? [],
                'features' => $decoded['features'] ?? [],
                default => $decoded,
            },
            'students' => match ($sectionKey) {
                'page' => $decoded['page'] ?? [],
                'cards' => $decoded['cards'] ?? [],
                'organizations' => $decoded['organization_sections'] ?? [],
                default => $decoded,
            },
            'research_extension' => match ($sectionKey) {
                'page' => $decoded['page'] ?? [],
                'cards' => $decoded['cards'] ?? [],
                default => $decoded,
            },
            'events' => match ($sectionKey) {
                'page' => $decoded['page'] ?? [],
                'cards' => $decoded['cards'] ?? [],
                default => $decoded,
            },
            default => $decoded,
        };
    }

    private static function sliceAboutSection(string $sectionKey, array $decoded): mixed
    {
        $overview = is_array($decoded['overview'] ?? null) ? $decoded['overview'] : [];
        $sections = is_array($decoded['sections'] ?? null) ? $decoded['sections'] : [];
        $vision = is_array($sections['vision-and-mission'] ?? null) ? $sections['vision-and-mission'] : [];

        return match ($sectionKey) {
            'hero' => ['overview' => self::onlyKeys($overview, ['hero_image', 'hero_title_default', 'hero_title_history', 'hero_title_vision', 'section_header_image'])],
            'intro' => ['overview' => self::onlyKeys($overview, ['story_tag', 'story_title', 'story_description'])],
            'contents' => [
                'overview' => self::onlyKeys($overview, ['contents_tag', 'contents_title']),
                'sections' => array_map(static fn ($section) => self::onlyKeys(is_array($section) ? $section : [], ['label', 'summary', 'visible_in_contents']), $sections),
            ],
            'vision-mission-header' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['page_kicker', 'page_title'])]],
            'vision-mission-statements' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['vision', 'mission'])]],
            'strategic-goals' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['strategic_goals'])]],
            'core-values' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['core_values'])]],
            default => ['sections' => [$sectionKey => is_array($sections[$sectionKey] ?? null) ? $sections[$sectionKey] : []]],
        };
    }

    private static function renderPanel(string $label, string $sectionLabel, mixed $value): string
    {
        return implode('', [
            self::renderSectionHeading($sectionLabel),
            self::renderValuePanel($label, $value),
        ]);
    }

    private static function renderSectionHeading(string $sectionLabel): string
    {
        if ($sectionLabel === '') {
            return '';
        }

        return '<div style="margin-bottom:12px;">'
            .'<div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#8f7d74; font-weight:700;">Requested CMS Section</div>'
            .'<div style="font-size:18px; font-weight:700; color:#5c0000;">'.e($sectionLabel).'</div>'
            .'</div>';
    }

    private static function renderValuePanel(string $label, mixed $value): string
    {
        return '<div style="border:1px solid rgba(128,0,0,.08); border-radius:16px; background:#fbfbfb; padding:14px 16px;">'
            .'<div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#8f7d74; font-weight:700; margin-bottom:10px;">'.e($label).'</div>'
            .self::renderValue($value)
            .'</div>';
    }

    private static function renderValue(mixed $value): string
    {
        if ($value === null) {
            return self::emptyState();
        }

        if (is_bool($value)) {
            return self::renderScalar($value ? 'Yes' : 'No');
        }

        if (is_scalar($value)) {
            $text = trim((string) $value);
            if ($text === '') {
                return self::emptyState();
            }

            return self::renderScalar($text);
        }

        if (!is_array($value)) {
            return self::renderScalar((string) $value);
        }

        if ($value === []) {
            return self::emptyState();
        }

        if (self::isAssoc($value)) {
            $parts = [];
            foreach ($value as $key => $item) {
                $parts[] = '<div style="margin-top:10px;">'
                    .'<div style="font-size:12px; color:#8f7d74; font-weight:700; margin-bottom:6px;">'.e(self::humanizeKey((string) $key)).'</div>'
                    .self::renderNestedValue($item)
                    .'</div>';
            }

            return implode('', $parts);
        }

        $items = [];
        foreach ($value as $index => $item) {
            $items[] = '<div style="margin-top:10px; padding:12px; border-radius:12px; background:#fff; border:1px solid rgba(128,0,0,.06);">'
                .'<div style="font-size:12px; color:#8f7d74; font-weight:700; margin-bottom:6px;">Item '.($index + 1).'</div>'
                .self::renderNestedValue($item)
                .'</div>';
        }

        return implode('', $items);
    }

    private static function renderNestedValue(mixed $value): string
    {
        if (is_array($value)) {
            return self::renderValue($value);
        }

        if (is_bool($value)) {
            return self::renderScalar($value ? 'Yes' : 'No');
        }

        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return self::emptyState();
        }

        return self::renderScalar($text);
    }

    private static function renderScalar(string $text): string
    {
        if (preg_match('/<\/?[a-z][\s\S]*>/i', $text) === 1) {
            return '<div class="rich-text-content">'.RichText::sanitize($text).'</div>';
        }

        return '<div style="white-space:pre-wrap; color:#2d2d2d;">'.nl2br(e($text)).'</div>';
    }

    private static function emptyState(): string
    {
        return '<div style="color:#8f7d74; font-style:italic;">No content provided.</div>';
    }

    private static function normalizeComparable(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private static function isAssoc(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    private static function humanizeKey(string $key): string
    {
        $value = trim($key);
        if ($value === '') {
            return 'Value';
        }

        return ucwords(str_replace(['-', '_'], ' ', $value));
    }

    private static function onlyKeys(array $source, array $keys): array
    {
        return array_intersect_key($source, array_flip($keys));
    }

    private static function sectionLabelFor(string $tabKey, string $sectionKey): string
    {
        return match ($tabKey) {
            'home' => match ($sectionKey) {
                'description' => 'Description',
                'carousel' => 'Hero Carousel',
                'updates' => 'Campus Updates',
                'quick_links' => 'Explore Section',
                'feedback' => 'Feedback Banner',
                default => '',
            },
            'about' => match ($sectionKey) {
                'hero' => 'Hero',
                'intro' => 'Intro',
                'contents' => 'Contents',
                'history' => 'History',
                'vision-mission-header' => 'Vision and Mission Header',
                'vision-mission-statements' => 'Vision and Mission Statements',
                'strategic-goals' => 'Strategic Goals',
                'core-values' => 'Core Values',
                'vision-and-mission' => 'Vision and Mission',
                'logo-and-symbols' => 'Logo and Symbols',
                'hymn' => 'Hymn',
                'maps' => 'Maps',
                'campus-officials' => 'Campus Officials',
                'strategic-development-plan' => 'Strategic Development Plan',
                default => '',
            },
            'academics' => match ($sectionKey) {
                'hero' => 'Hero',
                'contents' => 'Contents',
                'intro' => 'Intro',
                'features' => 'What We Offer',
                default => '',
            },
            'students' => match ($sectionKey) {
                'page' => 'Page Header',
                'cards' => 'Cards',
                'organizations' => 'Organizations',
                default => '',
            },
            'research_extension' => match ($sectionKey) {
                'page' => 'Page Header',
                'cards' => 'Cards',
                default => '',
            },
            'events' => match ($sectionKey) {
                'page' => 'Page Header',
                'cards' => 'Event Listings',
                default => '',
            },
            default => '',
        };
    }
}

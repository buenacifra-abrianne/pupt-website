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
            return self::renderPanel('Requested Update', $sectionLabel, $requested);
        }

        return implode('', [
            self::renderSectionHeading($sectionLabel),
            self::renderValuePanel('Changed Content', self::diffChangedValue($requested, $previous)),
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
                'campus_tour_video' => [
                    'campus_tour' => self::onlyKeys($decoded['campus_tour'] ?? [], ['avp_video']),
                ],
                'campus_tour_facilities' => [
                    'campus_tour' => self::onlyKeys($decoded['campus_tour'] ?? [], ['facilities']),
                ],
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
            'intro' => ['overview' => self::onlyKeys($overview, ['story_tag', 'story_title', 'story_description', 'story_image'])],
            'contents' => [
                'overview' => self::onlyKeys($overview, ['contents_tag', 'contents_title']),
                'sections' => array_map(static fn ($section) => self::onlyKeys(is_array($section) ? $section : [], ['label', 'summary', 'image', 'visible_in_contents']), $sections),
            ],
            'vision-mission-header' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['page_kicker', 'page_title'])]],
            'vision-statement' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['vision'])]],
            'mission-statement' => ['sections' => ['vision-and-mission' => self::onlyKeys($vision, ['mission'])]],
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
            .'<div style="font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:#8f7d74; font-weight:700;">CMS Section</div>'
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

        if (self::isChangedScalar($value)) {
            return self::renderChangedScalar($value);
        }

        if (self::isAssoc($value)) {
            $parts = [];
            foreach ($value as $key => $item) {
                $parts[] = '<div style="margin-top:10px;">'
                    .'<div style="font-size:12px; color:#8f7d74; font-weight:700; margin-bottom:6px;">'.e(self::humanizeKey((string) $key)).'</div>'
                    .self::renderNestedValue($item, (string) $key)
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

    private static function renderNestedValueForKey(mixed $value, string $key): string
    {
        if (is_array($value) && self::isChangedScalar($value)) {
            return self::renderChangedScalar($value, $key);
        }

        if (is_array($value)) {
            return self::renderValue($value);
        }

        if (is_bool($value)) {
            return self::renderScalar($value ? 'Yes' : 'No');
        }

        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return self::isImageKey($key)
                ? self::emptyImageState()
                : (self::isVideoKey($key)
                    ? self::emptyVideoState()
                    : (self::isLinkLikeKey($key)
                        ? self::emptyLinkState()
                        : self::emptyState()));
        }

        if (self::isImageKey($key)) {
            return self::renderImageValue($text);
        }

        if (self::isVideoKey($key) || self::looksLikeVideoReference($text)) {
            return self::renderVideoValue($text);
        }

        if (self::isLinkLikeKey($key)) {
            return self::renderLinkValue($text);
        }

        if (self::looksLikeAbsoluteUrl($text)) {
            if (self::looksLikeVideoReference($text)) {
                return self::renderVideoValue($text);
            }

            return self::renderLinkValue($text);
        }

        return self::renderScalar($text);
    }

    private static function renderNestedValue(mixed $value, string $key = ''): string
    {
        return self::renderNestedValueForKey($value, $key);
    }

    private static function renderChangedScalar(array $value, string $key = ''): string
    {
        return '<div class="approval-change-split">'
            .'<div class="approval-change-column">'
            .'<div class="approval-change-column-title">Current</div>'
            .self::renderNestedValue($value['previous'] ?? null, $key)
            .'</div>'
            .'<div class="approval-change-column">'
            .'<div class="approval-change-column-title">Requested Update</div>'
            .self::renderNestedValue($value['requested'] ?? null, $key)
            .'</div>'
            .'</div>';
    }

    private static function diffChangedValue(mixed $requested, mixed $previous): mixed
    {
        if (self::normalizeComparable($requested) === self::normalizeComparable($previous)) {
            return [];
        }

        if (is_array($requested) && is_array($previous)) {
            if (self::isAssoc($requested) || self::isAssoc($previous)) {
                return self::diffChangedAssoc($requested, $previous);
            }

            return self::diffChangedList($requested, $previous);
        }

        return [
            '__cms_changed_scalar' => true,
            'previous' => $previous,
            'requested' => $requested,
        ];
    }

    private static function diffChangedAssoc(array $requested, array $previous): array
    {
        $out = [];
        $keys = array_values(array_unique(array_merge(array_keys($requested), array_keys($previous))));

        foreach ($keys as $key) {
            $next = $requested[$key] ?? null;
            $old = $previous[$key] ?? null;

            if (self::normalizeComparable($next) === self::normalizeComparable($old)) {
                continue;
            }

            $out[$key] = self::diffChangedValue($next, $old);
        }

        return $out;
    }

    private static function diffChangedList(array $requested, array $previous): array
    {
        $out = [];
        $max = max(count($requested), count($previous));

        for ($index = 0; $index < $max; $index++) {
            $hasNext = array_key_exists($index, $requested);
            $hasOld = array_key_exists($index, $previous);
            $next = $hasNext ? $requested[$index] : null;
            $old = $hasOld ? $previous[$index] : null;

            if (self::normalizeComparable($next) === self::normalizeComparable($old)) {
                continue;
            }

            $out['item_'.($index + 1)] = self::diffChangedValue($next, $old);
        }

        return $out;
    }

    private static function isChangedScalar(array $value): bool
    {
        return ($value['__cms_changed_scalar'] ?? false) === true
            && array_key_exists('previous', $value)
            && array_key_exists('requested', $value);
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

    private static function emptyImageState(): string
    {
        return '<div style="border:1px dashed rgba(128,0,0,.22); border-radius:12px; padding:14px; color:#8f7d74; font-style:italic; background:#fff;">No image provided.</div>';
    }

    private static function emptyVideoState(): string
    {
        return '<div style="border:1px dashed rgba(128,0,0,.22); border-radius:12px; padding:14px; color:#8f7d74; font-style:italic; background:#fff;">No video provided.</div>';
    }

    private static function emptyLinkState(): string
    {
        return '<div style="border:1px dashed rgba(128,0,0,.22); border-radius:12px; padding:14px; color:#8f7d74; font-style:italic; background:#fff;">No link provided.</div>';
    }

    private static function renderImageValue(string $path): string
    {
        $url = ImageStorage::url($path);

        if (!$url) {
            return self::emptyImageState();
        }

        return '<figure style="margin:0;">'
            .'<img src="'.e($url).'" alt="Image preview" style="display:block; max-width:100%; max-height:320px; object-fit:contain; border-radius:12px; border:1px solid rgba(0,0,0,.08); background:#fff;">'
            .'<figcaption style="margin-top:6px; font-size:12px; color:#8f7d74; word-break:break-all;">'.e($path).'</figcaption>'
            .'</figure>';
    }

    private static function renderVideoValue(string $path): string
    {
        $embedUrl = self::resolveVideoEmbedUrl($path);
        if ($embedUrl !== null) {
            return '<figure style="margin:0;">'
                .'<div style="position:relative; width:100%; padding-top:56.25%; overflow:hidden; border-radius:12px; border:1px solid rgba(0,0,0,.08); background:#140f0f;">'
                .'<iframe src="'.e($embedUrl).'" title="Video preview" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy" style="position:absolute; inset:0; width:100%; height:100%; border:0;"></iframe>'
                .'</div>'
                .'<figcaption style="margin-top:8px; font-size:12px; color:#8f7d74; word-break:break-all;">'
                .self::renderInlineAnchor($path, $path)
                .'</figcaption>'
                .'</figure>';
        }

        $url = self::resolveAssetUrl($path);
        if ($url !== null && self::isDirectVideoPath($url)) {
            return '<figure style="margin:0;">'
                .'<video controls preload="metadata" style="display:block; width:100%; max-height:360px; border-radius:12px; border:1px solid rgba(0,0,0,.08); background:#140f0f;">'
                .'<source src="'.e($url).'">'
                .'Your browser does not support the video tag.'
                .'</video>'
                .'<figcaption style="margin-top:8px; font-size:12px; color:#8f7d74; word-break:break-all;">'.e($path).'</figcaption>'
                .'</figure>';
        }

        return self::renderLinkValue($path, 'Open video');
    }

    private static function renderLinkValue(string $value, string $label = 'Open link'): string
    {
        $url = self::resolveLinkUrl($value);
        if ($url === null) {
            return self::renderScalar($value);
        }

        return '<div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; padding:12px 14px; border-radius:12px; border:1px solid rgba(128,0,0,.12); background:#fff7f0;">'
            .'<span style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:999px; background:rgba(128,0,0,.08); color:#800000; flex:0 0 auto;">'
            .'<i class="fas fa-link" aria-hidden="true"></i>'
            .'</span>'
            .'<div style="min-width:0; flex:1 1 220px;">'
            .'<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" style="color:#800000; font-weight:700; text-decoration:none;">'.e($label).'</a>'
            .'<div style="margin-top:4px; font-size:12px; color:#8f7d74; word-break:break-all;">'.e($value).'</div>'
            .'</div>'
            .'</div>';
    }

    private static function renderInlineAnchor(string $url, string $label): string
    {
        $resolvedUrl = self::resolveLinkUrl($url);
        if ($resolvedUrl === null) {
            return e($label);
        }

        return '<a href="'.e($resolvedUrl).'" target="_blank" rel="noopener noreferrer" style="color:#800000; text-decoration:none; font-weight:600;">'.e($label).'</a>';
    }

    private static function isImageKey(string $key): bool
    {
        $normalized = strtolower(trim($key));

        return $normalized === 'image'
            || $normalized === 'image_path'
            || str_ends_with($normalized, '_image')
            || str_ends_with($normalized, '_image_path');
    }

    private static function isVideoKey(string $key): bool
    {
        $normalized = strtolower(trim($key));

        return $normalized === 'video'
            || $normalized === 'video_url'
            || $normalized === 'avp_video'
            || str_contains($normalized, 'video');
    }

    private static function isLinkLikeKey(string $key): bool
    {
        $normalized = strtolower(trim($key));

        return in_array($normalized, ['link', 'href', 'url', 'file', 'file_path', 'download_url'], true)
            || str_ends_with($normalized, '_link')
            || str_ends_with($normalized, '_href')
            || str_ends_with($normalized, '_url')
            || str_ends_with($normalized, '_file')
            || str_ends_with($normalized, '_file_path');
    }

    private static function looksLikeVideoReference(string $value): bool
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return false;
        }

        return self::resolveVideoEmbedUrl($value) !== null
            || self::isDirectVideoPath($normalized);
    }

    private static function isDirectVideoPath(string $value): bool
    {
        $path = preg_replace('/[?#].*$/', '', strtolower(trim($value)));

        return preg_match('/\.(mp4|webm|ogg|mov|m4v)$/', $path) === 1;
    }

    private static function resolveVideoEmbedUrl(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtube\.com/embed/|youtu\.be/|youtube\.com/shorts/)([A-Za-z0-9_-]{6,})~i', $trimmed, $matches) === 1) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('~(?:player\.)?vimeo\.com/(?:video/)?(\d+)~i', $trimmed, $matches) === 1) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    private static function resolveAssetUrl(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (self::looksLikeAbsoluteUrl($trimmed) || str_starts_with($trimmed, 'data:')) {
            return $trimmed;
        }

        return ImageStorage::url($trimmed);
    }

    private static function resolveLinkUrl(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (self::isSafeDirectLink($trimmed) || str_starts_with($trimmed, '/')) {
            return $trimmed;
        }

        return ImageStorage::url($trimmed);
    }

    private static function looksLikeAbsoluteUrl(string $value): bool
    {
        return preg_match('/^(https?:)?\/\//i', trim($value)) === 1;
    }

    private static function isSafeDirectLink(string $value): bool
    {
        $trimmed = trim($value);

        return self::looksLikeAbsoluteUrl($trimmed)
            || preg_match('/^(mailto|tel):/i', $trimmed) === 1;
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
                'campus_tour_video' => 'Campus Tour AVP',
                'campus_tour_facilities' => 'Campus Tour Facilities',
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
                'vision-statement' => 'Vision Statement',
                'mission-statement' => 'Mission Statement',
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

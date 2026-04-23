<?php

namespace App\Support;

class AcademicsCmsContent
{
    private const DEFAULTS = [
        'hero' => [
            'image' => 'assets/static_img/about_header_image.png',
            'title' => 'ACADEMICS',
        ],
        'contents' => [
            'tag' => 'Contents',
            'items' => [
                [
                    'label' => 'Degree Programs',
                    'summary' => 'Discover a wide range of undergraduate majors and minors designed to prepare you for professional success.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.degree-programs',
                ],
                [
                    'label' => 'Diploma Programs',
                    'summary' => 'Gain practical skills and specialized knowledge through diploma courses tailored for career readiness.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.diploma-programs',
                ],
                [
                    'label' => 'Graduate Programs',
                    'summary' => "Advance your expertise with master's and doctoral programs that foster research, leadership, and innovation.",
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.graduate-programs',
                ],
                [
                    'label' => 'PUP iApply',
                    'summary' => "Easily access the university's online application portal to start your academic journey.",
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.pup-iapply',
                ],
                [
                    'label' => 'University Calendar',
                    'summary' => 'Stay updated with important academic schedules, events, and deadlines throughout the school year.',
                    'image' => 'assets/static_img/pupillar.jpeg',
                    'route' => 'public.university-calendar',
                ],
            ],
        ],
        'intro' => [
            'body' => '<p><strong>Quality and relevant education</strong> that responds to the call of present times in building the <strong>foundations of the future.</strong></p><p>Ranging from high school to doctoral courses, traditional to nontraditional education system, <strong>the University makes it possible</strong> that <strong>deserving individuals can have access</strong> to these academic resources.</p><p>The University has always been making <strong>initiatives to enrich its academic programs</strong> in various fields of study and <strong>implement an educational strategy</strong> designed to provide our students with highly employable, managerial, and entrepreneurial skills in order to make them exceedingly <strong>creative, productive, competitive, and self-reliant</strong>.</p>',
        ],
        'features' => [
            'eyebrow' => 'What we offer',
            'items' => [
                [
                    'title' => 'QUALITY',
                    'body' => '<p><strong>Academic Excellence</strong> Being one of the reputable universities in the country, we always make it to a point that the education given to our students meets the standards of quality and excellence.</p>',
                    'wide' => false,
                ],
                [
                    'title' => 'RELEVANT',
                    'body' => '<p><strong>Responsive Learning</strong> The University, through its various programs, equips its students with learning and skills that are significant and responsive, enabling students to be competitive and very resourceful.</p>',
                    'wide' => false,
                ],
                [
                    'title' => 'FLEXIBLE',
                    'body' => "<p><strong>Accessible Study Paths</strong> Programs that adapt to a student's living condition, especially for the working class. Our Open University and distance learning method goes beyond the physical restrictions of a campus.</p>",
                    'wide' => false,
                ],
                [
                    'title' => 'ACCREDITED',
                    'body' => '<p><strong>Recognized Standards</strong> Most of our academic courses are accredited by the Accrediting Agency of Chartered Colleges and Universities in the Philippines (AACCUP).</p>',
                    'wide' => false,
                ],
                [
                    'title' => 'AFFORDABLE',
                    'body' => '<p><strong>Low-Cost Education</strong> Practicality without sacrificing quality in education. Having the lowest tuition and fees among universities in the Philippines, one can enroll for less than PHP 500 per semester in an undergraduate program.</p>',
                    'wide' => true,
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

        if (isset($decoded['academics']) && is_array($decoded['academics'])) {
            $decoded = $decoded['academics'];
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
        return (string) (ImageStorage::url($path, $fallbackPath) ?? asset(ltrim($fallbackPath, '/')));
    }

    private static function normalize(array $source, array $base): array
    {
        $defaults = self::defaults();

        return [
            'hero' => self::normalizeHero(
                is_array($source['hero'] ?? null) ? $source['hero'] : [],
                is_array($base['hero'] ?? null) ? $base['hero'] : $defaults['hero'],
                $defaults['hero']
            ),
            'contents' => self::normalizeContents(
                is_array($source['contents'] ?? null) ? $source['contents'] : [],
                is_array($base['contents'] ?? null) ? $base['contents'] : $defaults['contents'],
                $defaults['contents']
            ),
            'intro' => self::normalizeIntro(
                is_array($source['intro'] ?? null) ? $source['intro'] : [],
                is_array($base['intro'] ?? null) ? $base['intro'] : $defaults['intro'],
                $defaults['intro']
            ),
            'features' => self::normalizeFeatures(
                is_array($source['features'] ?? null) ? $source['features'] : [],
                is_array($base['features'] ?? null) ? $base['features'] : $defaults['features'],
                $defaults['features']
            ),
        ];
    }

    private static function normalizeHero(array $source, array $base, array $defaults): array
    {
        return [
            'image' => self::pickOptionalString($source, $base, $defaults, 'image', 2048),
            'title' => self::pickString($source, $base, $defaults, 'title'),
        ];
    }

    private static function normalizeContents(array $source, array $base, array $defaults): array
    {
        return [
            'tag' => self::pickString($source, $base, $defaults, 'tag', 80),
            'items' => self::normalizeContentsItems(
                $source['items'] ?? [],
                $base['items'] ?? $defaults['items'],
                $defaults['items']
            ),
        ];
    }

    private static function normalizeIntro(array $source, array $base, array $defaults): array
    {
        return [
            'body' => self::pickString($source, $base, $defaults, 'body', 20000),
        ];
    }

    private static function normalizeFeatures(array $source, array $base, array $defaults): array
    {
        return [
            'eyebrow' => self::pickString($source, $base, $defaults, 'eyebrow', 120),
            'items' => self::normalizeFeatureItems(
                $source['items'] ?? [],
                $base['items'] ?? $defaults['items'],
                $defaults['items']
            ),
        ];
    }

    private static function normalizeContentsItems(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values(is_array($base) ? $base : []);
        $defaultItems = array_values(is_array($defaults) ? $defaults : []);
        $itemsSource = $sourceItems !== [] ? $sourceItems : $baseItems;
        $items = [];

        foreach ($itemsSource as $index => $source) {
            if (!is_array($source)) {
                continue;
            }

            $defaultItem = is_array($defaultItems[$index] ?? null)
                ? $defaultItems[$index]
                : ['label' => '', 'summary' => '', 'image' => '', 'route' => 'public.academics'];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $normalized = [
                'label' => self::pickString($source, $baseItem, $defaultItem, 'label'),
                'summary' => self::pickString($source, $baseItem, $defaultItem, 'summary', 4000),
                'image' => self::pickOptionalString($source, $baseItem, $defaultItem, 'image', 2048),
                'route' => self::pickString($source, $baseItem, $defaultItem, 'route', 255),
            ];

            if (
                trim($normalized['label']) === ''
                && trim($normalized['summary']) === ''
                && trim($normalized['image']) === ''
            ) {
                continue;
            }

            $items[] = $normalized;

            if (count($items) >= 24) {
                break;
            }
        }

        return $items;
    }

    private static function normalizeFeatureItems(mixed $input, array $base, array $defaults): array
    {
        $sourceItems = is_array($input) ? array_values($input) : [];
        $baseItems = array_values(is_array($base) ? $base : []);
        $defaultItems = array_values(is_array($defaults) ? $defaults : []);
        $itemsSource = $sourceItems !== [] ? $sourceItems : $baseItems;
        $items = [];

        foreach ($itemsSource as $index => $source) {
            if (!is_array($source)) {
                continue;
            }

            $defaultItem = is_array($defaultItems[$index] ?? null)
                ? $defaultItems[$index]
                : ['title' => '', 'body' => '', 'wide' => false];
            $baseItem = is_array($baseItems[$index] ?? null) ? $baseItems[$index] : $defaultItem;

            $normalized = [
                'title' => self::pickString($source, $baseItem, $defaultItem, 'title'),
                'body' => self::pickString($source, $baseItem, $defaultItem, 'body', 12000),
                'wide' => filter_var(
                    $source['wide'] ?? ($baseItem['wide'] ?? ($defaultItem['wide'] ?? false)),
                    FILTER_VALIDATE_BOOL,
                    FILTER_NULL_ON_FAILURE
                ) ?? (bool) ($defaultItem['wide'] ?? false),
            ];

            if (
                trim($normalized['title']) === ''
                && trim($normalized['body']) === ''
            ) {
                continue;
            }

            $items[] = $normalized;

            if (count($items) >= 24) {
                break;
            }
        }

        return $items;
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
        $text = trim($value);

        if ($text === '') {
            $text = trim($fallback);
        }

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }

    private static function sanitizeOptionalString(string $value, int $maxLen): string
    {
        $text = trim($value);

        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLen);
        }

        return substr($text, 0, $maxLen);
    }
}

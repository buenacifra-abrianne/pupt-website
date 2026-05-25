<?php

namespace App\Services\KnowledgeSync;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class WebsiteLinkDiscoveryService
{
    public function __construct(
        private readonly LinkExtractor $linkExtractor,
        private readonly UrlNormalizer $urlNormalizer,
    ) {
    }

    /**
     * @return string[]
     */
    public function discoverKnowledgeCandidateUrls(): array
    {
        $baseUrl = (string) config('knowledge_sync.base_url', config('app.url'));
        $baseHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
        $stripParams = (array) config('knowledge_sync.url.strip_query_parameters', []);

        $allDiscovered = [];

        foreach ($this->discoverFromCms($baseUrl) as $rawUrl) {
            $normalized = $this->urlNormalizer->normalize($rawUrl, $baseUrl, $stripParams);
            if (is_string($normalized)) {
                $allDiscovered[] = $normalized;
            }
        }

        foreach ($this->discoverFromTemplates($baseUrl) as $rawUrl) {
            $normalized = $this->urlNormalizer->normalize($rawUrl, $baseUrl, $stripParams);
            if (is_string($normalized)) {
                $allDiscovered[] = $normalized;
            }
        }

        $internalPages = $this->crawlInternalPages($baseUrl);
        foreach ($internalPages as $pageUrl => $html) {
            foreach ($this->linkExtractor->extractFromHtml($html, $pageUrl) as $row) {
                $normalized = $this->urlNormalizer->normalize((string) $row['url'], $pageUrl, $stripParams);
                if (is_string($normalized)) {
                    $allDiscovered[] = $normalized;
                }
            }
        }

        foreach ($this->discoverFromSitemap($baseUrl) as $rawUrl) {
            $normalized = $this->urlNormalizer->normalize($rawUrl, $baseUrl, $stripParams);
            if (is_string($normalized)) {
                $allDiscovered[] = $normalized;
            }
        }

        foreach ((array) config('knowledge_sync.manual_urls', []) as $manualUrl) {
            if (!is_string($manualUrl)) {
                continue;
            }

            $normalized = $this->urlNormalizer->normalize($manualUrl, $baseUrl, $stripParams);
            if (is_string($normalized)) {
                $allDiscovered[] = $normalized;
            }
        }

        $unique = array_values(array_unique($allDiscovered));

        return array_values(array_filter($unique, function (string $url) use ($baseHost) {
            if (!$this->isKnowledgeCandidateUrl($url)) {
                return false;
            }

            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $isInternal = $host !== '' && $host === $baseHost;

            return !$isInternal || $this->urlNormalizer->isDocumentUrl($url);
        }));
    }

    /**
     * @return string[]
     */
    private function discoverFromCms(string $baseUrl): array
    {
        $urls = [];

        $tableFields = [
            'cms_contents' => ['title', 'content'],
            'announcements' => ['title', 'content', 'link'],
            'news' => ['title', 'content', 'link', 'location'],
            'downloadables' => ['title', 'description', 'file_path', 'original_filename'],
        ];

        foreach ($tableFields as $table => $fields) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $selectable = array_values(array_filter($fields, static fn (string $field) => Schema::hasColumn($table, $field)));
            if ($selectable === []) {
                continue;
            }

            $primaryKey = $this->primaryKeyForTable($table);
            if (!in_array($primaryKey, $selectable, true) && Schema::hasColumn($table, $primaryKey)) {
                $selectable[] = $primaryKey;
            }

            DB::table($table)
                ->select($selectable)
                ->orderBy($primaryKey)
                ->chunkById(100, function ($rows) use (&$urls): void {
                    foreach ($rows as $row) {
                        foreach ((array) $row as $value) {
                            if (is_string($value) && trim($value) !== '') {
                                $urls = array_merge($urls, $this->extractFromUnknownContent($value));
                            }
                        }
                    }
                }, $primaryKey);
        }

        foreach ($this->discoverPublicRouteUrls($baseUrl) as $routeUrl) {
            $urls[] = $routeUrl;
        }

        return array_values(array_unique($urls));
    }

    /**
     * @return string[]
     */
    private function discoverFromTemplates(string $baseUrl): array
    {
        $urls = [];
        $allowedExtensions = (array) config('knowledge_sync.template_scan.extensions', []);
        $maxBytes = (int) config('knowledge_sync.template_scan.max_file_bytes', 1_500_000);

        foreach ((array) config('knowledge_sync.template_scan.paths', []) as $path) {
            if (!is_string($path) || !File::exists($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                $filename = $file->getFilename();

                if (!$this->hasAllowedTemplateExtension($filename, $allowedExtensions)) {
                    continue;
                }

                if ($file->getSize() > $maxBytes) {
                    continue;
                }

                $content = File::get($file->getRealPath());

                foreach ($this->linkExtractor->extractFromText($content) as $rawUrl) {
                    $urls[] = $rawUrl;
                }

                foreach ($this->linkExtractor->extractFromHtml($content, $file->getRealPath()) as $row) {
                    $urls[] = (string) ($row['url'] ?? '');
                }
            }
        }

        foreach ($this->discoverPublicRouteUrls($baseUrl) as $routeUrl) {
            $urls[] = $routeUrl;
        }

        return array_values(array_filter(array_unique($urls)));
    }

    /**
     * @return array<string, string>
     */
    private function crawlInternalPages(string $baseUrl): array
    {
        $maxDepth = (int) config('knowledge_sync.crawl.max_depth', 2);
        $maxPages = (int) config('knowledge_sync.crawl.max_pages', 80);
        $baseHost = strtolower((string) parse_url($baseUrl, PHP_URL_HOST));

        $queue = [];
        $visited = [];
        $htmlByUrl = [];

        $seedUrls = array_unique(array_merge([$baseUrl], $this->discoverPublicRouteUrls($baseUrl), $this->discoverFromSitemap($baseUrl)));

        foreach ($seedUrls as $seedUrl) {
            $queue[] = ['url' => $seedUrl, 'depth' => 0];
        }

        while ($queue !== [] && count($visited) < $maxPages) {
            $item = array_shift($queue);
            if (!is_array($item)) {
                continue;
            }

            $url = (string) ($item['url'] ?? '');
            $depth = (int) ($item['depth'] ?? 0);

            if ($url === '' || isset($visited[$url])) {
                continue;
            }

            $visited[$url] = true;

            try {
                $response = Http::timeout((int) config('knowledge_sync.crawl.timeout_seconds', 10))->get($url);
            } catch (\Throwable) {
                continue;
            }

            if (!$response->successful()) {
                continue;
            }

            $contentType = strtolower((string) $response->header('Content-Type', ''));
            if (!str_starts_with($contentType, 'text/html')) {
                continue;
            }

            $html = (string) $response->body();
            $htmlByUrl[$url] = $html;

            if ($depth >= $maxDepth) {
                continue;
            }

            foreach ($this->linkExtractor->extractFromHtml($html, $url) as $row) {
                $normalized = $this->urlNormalizer->normalize((string) $row['url'], $url, (array) config('knowledge_sync.url.strip_query_parameters', []));
                if (!is_string($normalized)) {
                    continue;
                }

                $host = strtolower((string) parse_url($normalized, PHP_URL_HOST));
                if ($host !== $baseHost) {
                    continue;
                }

                $queue[] = ['url' => $normalized, 'depth' => $depth + 1];
            }
        }

        return $htmlByUrl;
    }

    /**
     * @return string[]
     */
    private function discoverFromSitemap(string $baseUrl): array
    {
        $sitemapUrl = rtrim($baseUrl, '/').'/sitemap.xml';

        try {
            $response = Http::timeout((int) config('knowledge_sync.crawl.timeout_seconds', 10))->get($sitemapUrl);
        } catch (\Throwable) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        return $this->linkExtractor->extractFromSitemapXml((string) $response->body());
    }

    /**
     * @return string[]
     */
    private function discoverPublicRouteUrls(string $baseUrl): array
    {
        $urls = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = ltrim((string) $route->uri(), '/');
            if ($uri === '' || str_contains($uri, '{')) {
                continue;
            }

            $absolute = rtrim($baseUrl, '/').'/'.$uri;
            $urls[] = $absolute;
        }

        $urls[] = rtrim($baseUrl, '/').'/';

        return array_values(array_unique($urls));
    }

    /**
     * @return string[]
     */
    private function extractFromUnknownContent(string $value): array
    {
        $urls = $this->linkExtractor->extractFromText($value);

        if (str_contains($value, '<')) {
            foreach ($this->linkExtractor->extractFromHtml($value) as $row) {
                $urls[] = (string) ($row['url'] ?? '');
            }
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            $urls = array_merge($urls, $this->extractFromArray($decoded));
        }

        return array_values(array_unique(array_filter($urls)));
    }

    /**
     * @param array<mixed> $payload
     * @return string[]
     */
    private function extractFromArray(array $payload): array
    {
        $urls = [];

        foreach ($payload as $value) {
            if (is_array($value)) {
                $urls = array_merge($urls, $this->extractFromArray($value));
                continue;
            }

            if (is_string($value) && trim($value) !== '') {
                $urls = array_merge($urls, $this->extractFromUnknownContent($value));
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param string[] $allowedExtensions
     */
    private function hasAllowedTemplateExtension(string $filename, array $allowedExtensions): bool
    {
        $filename = strtolower($filename);

        foreach ($allowedExtensions as $extension) {
            $normalized = strtolower((string) $extension);
            if ($normalized !== '' && str_ends_with($filename, '.'.$normalized)) {
                return true;
            }
        }

        return false;
    }

    private function primaryKeyForTable(string $table): string
    {
        return match ($table) {
            'announcements' => 'announcement_id',
            'news' => 'news_id',
            'downloadables' => 'downloadable_id',
            default => 'id',
        };
    }

    private function isKnowledgeCandidateUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return true;
        }

        if (in_array($extension, ['pdf', 'docx', 'html', 'htm', 'txt'], true)) {
            return true;
        }

        $nonKnowledgeExtensions = [
            'css', 'js', 'mjs', 'map', 'xml', 'json',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico',
            'woff', 'woff2', 'ttf', 'otf', 'eot',
            'mp4', 'webm', 'mp3', 'wav', 'zip', 'rar',
        ];

        return !in_array($extension, $nonKnowledgeExtensions, true);
    }
}

<?php

namespace App\Services;

use App\Services\KnowledgeSync\LinkExtractor;
use App\Services\KnowledgeSync\UrlNormalizer;
use App\Services\KnowledgeSync\UrlSafetyValidator;
use App\Support\AboutCmsContent;
use App\Support\DownloadableFile;
use App\Support\RichText;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class LinkMonitoringService
{
    public function __construct(
        private readonly LinkExtractor $extractor,
        private readonly UrlNormalizer $normalizer,
        private readonly UrlSafetyValidator $safety,
    ) {}

    public function start(): array
    {
        $scan = ['base_url' => rtrim(config('app.url'), '/').'/', 'links' => [], 'started_at' => now()->toIso8601String(), 'completed_at' => null];

        foreach (Route::getRoutes() as $route) {
            if (str_starts_with((string) $route->getName(), 'public.') && in_array('GET', $route->methods(), true) && $route->parameterNames() === [] && ! str_contains($route->uri(), 'callback')) {
                $this->add($scan, '/'.ltrim($route->uri(), '/'), $scan['base_url'], 'Page');
            }
        }

        $about = AboutCmsContent::defaults();
        if (Schema::hasTable('cms_contents')) {
            $content = DB::table('cms_contents')->where('tab_key', 'about')->value('content');
            if (is_string($content)) {
                $about = AboutCmsContent::fromStored($content);
            }
        }
        foreach (array_keys($about['sections'] ?? []) as $section) {
            $this->add($scan, '/about/'.$section, $scan['base_url'], 'Page');
        }
        $this->discoverPublishedLinks($scan);

        return $scan;
    }

    private function discoverPublishedLinks(array &$scan): void
    {
        foreach (['announcements' => 'ENABLED', 'news' => 'APPROVED', 'downloadables' => null] as $table => $status) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $query = DB::table($table);
            if ($status !== null) {
                $query->whereRaw('UPPER(TRIM(status)) = ?', [$status]);
            }
            if ($table === 'news' && Schema::hasColumn($table, 'is_hidden_from_public')) {
                $query->where(fn ($scope) => $scope->whereNull('is_hidden_from_public')->orWhere('is_hidden_from_public', 0));
            }
            $source = $table === 'downloadables'
                ? route((strtoupper(trim((string) session('user_role'))) === 'ADMIN' ? 'admin' : 'superadmin').'.downloadables', [], false)
                : '/home';
            $page = $this->normalizer->normalize($source, $scan['base_url']);
            foreach ($query->cursor() as $row) {
                $label = ($table === 'downloadables' ? 'Campus Memorandum' : ($table === 'news' ? 'News' : 'Announcement')).': '.($row->title ?? '');
                $url = $table === 'downloadables' ? DownloadableFile::url($row->file_path ?? null) : ($row->link ?? null);
                if (is_string($url)) {
                    $this->add($scan, $url, $scan['base_url'], $label, $page);
                }
                $html = RichText::sanitize($row->content ?? $row->description ?? '');
                $this->discover($scan, $html, $page, $page);
                foreach ($this->extractor->extractFromText(strip_tags($html)) as $url) {
                    $this->add($scan, $url, $page, $label, $page);
                }
            }
        }
    }

    public function step(array $scan): array
    {
        foreach ($scan['links'] as $key => $link) {
            if ($link['status'] !== 'pending') {
                continue;
            }

            $result = $this->check($link['url'], $scan['base_url']);
            $scan['links'][$key] = array_merge($link, array_diff_key($result, ['html' => true, 'final_url' => true]));
            if ($result['html'] !== null) {
                $this->discover($scan, $result['html'], $result['final_url'], $link['url']);
            }
            break;
        }

        if (! array_filter($scan['links'], fn ($link) => $link['status'] === 'pending')) {
            $scan['completed_at'] ??= now()->toIso8601String();
        }

        return $scan;
    }

    private function discover(array &$scan, string $html, string $base, string $page): void
    {
        if (trim($html) === '') {
            return;
        }
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        $baseHref = $xpath->query('//base[@href]')->item(0)?->getAttribute('href');
        $base = $baseHref ? ($this->normalizer->normalize($baseHref, $base) ?? $base) : $base;

        foreach ($xpath->query('//a[@href] | //area[@href] | //*[@data-link or @data-href]') as $node) {
            foreach (['href', 'data-link', 'data-href'] as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $this->add($scan, $node->getAttribute($attribute), $base, trim($node->textContent) ?: 'Link', $page);
                }
            }
        }
        foreach ($xpath->query('//*[@data-content-html or @data-summary-html]') as $node) {
            foreach (['data-content-html', 'data-summary-html'] as $attribute) {
                if ($node->hasAttribute($attribute)) {
                    $this->discover($scan, RichText::sanitize($node->getAttribute($attribute)), $base, $page);
                }
            }
        }
        foreach ($xpath->query('//*[@data-home or @data-about or @data-academics or @data-students or @data-news-events or @data-research or @data-oneportal]') as $node) {
            foreach (['home', 'about', 'academics', 'students', 'news-events', 'research', 'oneportal'] as $attribute) {
                if ($node->hasAttribute('data-'.$attribute)) {
                    $this->add($scan, $node->getAttribute('data-'.$attribute), $base, 'Header / Footer', $page);
                }
            }
        }
        // Shared navigation is rendered by this web component in the browser.
        if (str_contains($html, '<pup-header') || str_contains($html, '<pup-footer')) {
            $components = file_get_contents(public_path('assets/js/pup-components.js'));
            preg_match_all('/const\s+(\w+)\s*=\s*(?:this\.dataset\.\w+\s*\|\|\s*)?["\'](https?:\/\/[^"\']+)["\']/', $components, $constants, PREG_SET_ORDER);
            foreach ($constants as $constant) {
                $components = str_replace('${'.$constant[1].'}', $constant[2], $components);
            }
            preg_match_all('/\bhref\s*=\s*(["\'])(.*?)\1/s', $components, $links);
            foreach ($links[2] as $url) {
                $this->add($scan, $url, $base, 'Header / Footer', $page);
            }
        }
    }

    private function add(array &$scan, string $raw, string $base, string $label, ?string $page = null): void
    {
        $raw = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($raw === '' || str_contains($raw, '${') || str_contains($raw, '{{') || str_starts_with($raw, '#')) {
            return;
        }
        $url = $this->normalizer->normalize($raw, $base);
        if ($url === null) {
            return;
        }
        if (preg_match('/\.(?:css|m?js|map|png|jpe?g|gif|webp|svg|ico|avif|bmp|tiff?|mp4|webm|mov|mp3|wav|ogg|woff2?|ttf|otf|eot)$/i', rawurldecode((string) parse_url($url, PHP_URL_PATH)))) {
            return;
        }
        // Keep signed URLs and repeated query parameters exactly as published.
        if (str_contains($raw, '?')) {
            $url = (string) (new Uri($url))->withQuery((string) parse_url($raw, PHP_URL_QUERY));
        }
        $key = hash('sha256', $url);
        $page ??= $url;
        if (! isset($scan['links'][$key])) {
            $scan['links'][$key] = [
                'url' => $url,
                'label' => mb_substr(preg_replace('/\s+/', ' ', trim($label)), 0, 160),
                'pages' => [],
                'status' => 'pending',
                'http_status' => null,
                'detail' => 'Waiting to be checked',
            ];
        }
        if (! in_array($page, $scan['links'][$key]['pages'], true)) {
            $scan['links'][$key]['pages'][] = $page;
        }
    }

    private function check(string $url, string $base): array
    {
        $result = ['status' => 'not_working', 'http_status' => null, 'detail' => '', 'html' => null, 'final_url' => $url];
        $origin = fn (string $value) => strtolower((string) parse_url($value, PHP_URL_SCHEME).'://'.(string) parse_url($value, PHP_URL_HOST)).':'.(parse_url($value, PHP_URL_PORT) ?? (parse_url($value, PHP_URL_SCHEME) === 'https' ? 443 : 80));

        try {
            for ($redirect = 0; $redirect <= 3; $redirect++) {
                $internal = $origin($url) === $origin($base);
                $path = (string) parse_url($url, PHP_URL_PATH);
                if (parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_PASS) !== null) {
                    $result['detail'] = 'URLs containing credentials are not checked.';

                    return $result;
                }
                if ($internal && preg_match('#^/(superadmin|admin|staff|auth|sso|logout|profile|cms|api|debug-height)(/|$)#i', $path)) {
                    $result['status'] = 'not_checked';
                    $result['detail'] = 'Protected or action endpoint; excluded from public-page crawling.';

                    return $result;
                }
                if (! $internal) {
                    $safe = $this->safety->validate($url);
                    if (! $safe['allowed']) {
                        $result['detail'] = $safe['reason'];

                        return $result;
                    }
                }
                $response = $internal && app()->environment('local')
                    ? $this->localResponse($url)
                    : Http::withHeaders(['User-Agent' => 'PUP-LinkMonitor/1.0', 'Accept' => '*/*'])
                        ->connectTimeout(3)->timeout(5)->withOptions(['allow_redirects' => false, 'stream' => true, 'read_timeout' => 5])->get($url);
                $result['http_status'] = $response->status();
                $stream = $response->toPsrResponse()->getBody();
                if ($response->redirect()) {
                    $next = $this->normalizer->normalize((string) $response->header('Location'), $url);
                    if ($next !== null && str_contains((string) $response->header('Location'), '?')) {
                        $next = (string) (new Uri($next))->withQuery((string) parse_url($response->header('Location'), PHP_URL_QUERY));
                    }
                    $stream->close();
                    if ($next === null || $redirect === 3) {
                        $result['detail'] = 'Invalid redirect or too many redirects.';

                        return $result;
                    }
                    $url = $next;

                    continue;
                }
                $result['status'] = $response->successful() ? 'working' : 'not_working';
                $result['detail'] = 'HTTP '.$response->status();
                $result['final_url'] = $url;
                if ($internal && $response->successful() && str_contains(strtolower((string) $response->header('Content-Type')), 'text/html')) {
                    $html = '';
                    while (! $stream->eof() && strlen($html) <= 5_000_000) {
                        $html .= $stream->read(8192);
                    }
                    if (strlen($html) > 5_000_000) {
                        $result['status'] = 'not_checked';
                        $result['detail'] = 'Page exceeds 5 MB; its links could not all be read.';
                    } else {
                        $result['html'] = $html;
                    }
                }
                $stream->close();

                return $result;
            }
        } catch (\Throwable) {
            $result['status'] = 'not_working';
            $result['html'] = null;
            $result['detail'] = 'Connection failed, timed out, or TLS certificate could not be verified.';
        }

        return $result;
    }

    private function localResponse(string $url): \Illuminate\Http\Client\Response
    {
        // PHP's local development server cannot serve an HTTP request to itself while scanning.
        $original = app('request');
        $request = Request::create($url);
        $request->setLaravelSession($original->session());
        try {
            $file = realpath(public_path(ltrim(rawurldecode((string) parse_url($url, PHP_URL_PATH)), '/')));
            if ($file && is_file($file) && (str_starts_with($file, realpath(public_path()).DIRECTORY_SEPARATOR) || str_starts_with($file, storage_path('app/public').DIRECTORY_SEPARATOR))) {
                return new \Illuminate\Http\Client\Response(new Response(200, ['Content-Type' => mime_content_type($file) ?: 'application/octet-stream'], fopen($file, 'rb')));
            }

            $route = Route::getRoutes()->match($request);
            if (! str_starts_with((string) $route->getName(), 'public.') || str_contains($route->uri(), 'callback')) {
                return new \Illuminate\Http\Client\Response(new Response(403));
            }
            $request->setRouteResolver(fn () => $route);
            app()->instance('request', $request);
            $response = Router::toResponse($request, $route->run());

            return new \Illuminate\Http\Client\Response(new Response($response->getStatusCode(), $response->headers->all(), $response->getContent()));
        } catch (HttpExceptionInterface $e) {
            return new \Illuminate\Http\Client\Response(new Response($e->getStatusCode()));
        } finally {
            app()->instance('request', $original);
        }
    }
}

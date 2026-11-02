<?php

namespace Tests\Feature;

use App\Http\Middleware\ActiveSecurityTracker;
use App\Http\Middleware\FirewallMiddleware;
use App\Http\Middleware\TrackPublicAnalytics;
use App\Services\KnowledgeSync\UrlSafetyValidator;
use App\Services\LinkMonitoringService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class LinkMonitoringTest extends TestCase
{
    public function test_only_admin_and_superadmin_can_read_or_run_scans(): void
    {
        $this->getJson('/cms/link-monitoring')->assertStatus(419);
        foreach (['STAFF', 'FACULTY', 'REGISTRAR', ''] as $role) {
            $this->withSession(['user_logged_in' => true, 'user_role' => $role, 'terms_accepted' => true]);
            session()->save();
            $this->getJson('/cms/link-monitoring')->assertForbidden();
            $this->postJson('/cms/link-monitoring', ['action' => 'start'])->assertForbidden();
        }
        foreach (['ADMIN', 'SUPERADMIN'] as $role) {
            $this->withSession(['user_logged_in' => true, 'user_role' => $role, 'terms_accepted' => true]);
            session()->save();
            $this->postJson('/cms/link-monitoring', ['action' => 'start'])->assertOk()->assertJsonPath('scan.completed_at', null);
            $this->getJson('/cms/link-monitoring')->assertOk()->assertJsonStructure(['scan' => ['base_url', 'links', 'started_at']]);
        }
        Http::assertNothingSent();
    }

    public function test_scan_validates_actions_requires_terms_and_handles_missing_scan(): void
    {
        $this->withSession(['user_logged_in' => true, 'user_role' => 'ADMIN', 'terms_accepted' => false]);
        session()->save();
        $this->postJson('/cms/link-monitoring', ['action' => 'start'])->assertStatus(423);
        $this->withSession(['terms_accepted' => true]);
        session()->save();
        $this->postJson('/cms/link-monitoring', ['action' => 'arbitrary-url'])->assertUnprocessable();
        $this->postJson('/cms/link-monitoring', ['action' => 'step'])->assertStatus(410);
        $this->getJson('/cms/link-monitoring')->assertExactJson(['scan' => null]);
    }

    public function test_scan_can_be_advanced_and_resumed_through_the_endpoint(): void
    {
        Http::fake(['*' => Http::response('<a href="/nested">Nested</a>', 200, ['Content-Type' => 'text/html'])]);
        $this->withSession(['user_logged_in' => true, 'user_role' => 'ADMIN', 'terms_accepted' => true]);
        session()->save();
        $this->postJson('/cms/link-monitoring', ['action' => 'start'])->assertOk();
        $scan = $this->postJson('/cms/link-monitoring', ['action' => 'step'])->assertOk()->json('scan');
        $this->assertSame('working', $this->find($scan, 'https://campus.test/')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://campus.test/nested')['status']);
        $this->getJson('/cms/link-monitoring')->assertJsonPath('scan', $scan);
        Http::assertSentCount(1);
    }

    public function test_scan_seeds_every_public_page_and_stored_about_subpages(): void
    {
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('tab_key');
            $table->text('content');
        });
        DB::table('cms_contents')->insert(['tab_key' => 'about', 'content' => json_encode(['sections' => ['history' => ['label' => 'History']]])]);
        $scan = app(LinkMonitoringService::class)->start();
        $urls = array_column($scan['links'], 'url');
        foreach (['/', '/home', '/about', '/about/history', '/academics', '/academics/degree-programs', '/students/document-requests', '/research/strategic-development-plan', '/feedback'] as $path) {
            $this->assertContains('https://campus.test'.$path, $urls);
        }
        $this->assertNotContains('https://campus.test/home/callback', $urls);
        $this->assertNotContains('https://campus.test/admin/dashboard', $urls);
    }

    public function test_scan_discovers_nested_links_footer_downloads_and_page_sources_progressively(): void
    {
        $safety = Mockery::mock(UrlSafetyValidator::class);
        $safety->shouldReceive('validate')->with('https://external.test/good')->andReturn(['allowed' => true, 'reason' => null]);
        $this->app->instance(UrlSafetyValidator::class, $safety);
        Http::fake([
            'https://campus.test/' => Http::response('<a href="/nested">Nested</a><footer><a href="https://external.test/good">External</a><a href="/missing.pdf">Download</a></footer><a href="mailto:office@campus.test">Email</a><a href="#">Placeholder</a>', 200, ['Content-Type' => 'text/html']),
            'https://campus.test/nested' => Http::response('<a href="/deep">Deep</a><a href="https://external.test/good">Shared external</a><img src="/image.jpg" srcset="/small.jpg 300w, /large.jpg 800w"><video poster="/poster.jpg"></video>', 200, ['Content-Type' => 'text/html']),
            'https://campus.test/missing.pdf' => Http::response('', 404),
            'https://campus.test/deep' => Http::response('<a href="/signed?key=a%20b&key=c">Signed</a>', 200, ['Content-Type' => 'text/html']),
            '*' => Http::response('', 200),
        ]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $this->rootOnly($monitor->start());
        $scan = $monitor->step($scan);
        $this->assertNull($scan['completed_at']);
        $this->assertSame('working', $this->find($scan, 'https://campus.test/')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://external.test/good')['status']);
        for ($i = 0; $scan['completed_at'] === null && $i < 30; $i++) {
            $scan = $monitor->step($scan);
        }
        $this->assertNotNull($scan['completed_at']);
        $this->assertSame('not_working', $this->find($scan, 'https://campus.test/missing.pdf')['status']);
        $this->assertSame(404, $this->find($scan, 'https://campus.test/missing.pdf')['http_status']);
        $this->assertSame(['https://campus.test/', 'https://campus.test/nested'], $this->find($scan, 'https://external.test/good')['pages']);
        $this->assertNotContains('mailto:office@campus.test', array_column($scan['links'], 'url'));
        $this->assertNotContains('https://campus.test/#', array_column($scan['links'], 'url'));
        foreach (['/deep', '/signed?key=a%20b&key=c'] as $path) {
            $this->assertSame('working', $this->find($scan, 'https://campus.test'.$path)['status']);
        }
        foreach (['/image.jpg', '/small.jpg', '/large.jpg', '/poster.jpg'] as $path) {
            $this->assertNotContains('https://campus.test'.$path, array_column($scan['links'], 'url'));
        }
        Http::assertSentCount(6);
    }

    public function test_only_navigation_and_published_modal_links_are_discovered_not_page_resources(): void
    {
        $html = <<<'HTML'
            <link rel="stylesheet" href="https://fonts.example.test/css2?family=Example">
            <link rel="icon" href="/favicon.ico">
            <script src="https://cdn.example.test/widget.js"></script>
            <script>const example = 'https://script.example.test/not-a-link';</script>
            <!-- <a href="https://comment.example.test/not-published">Comment</a> -->
            <img src="/photo.jpg" data-src="/lazy-photo.jpg" srcset="/small.jpg 1x, /large.jpg 2x">
            <video src="/video.mp4" poster="/poster.jpg"></video>
            <iframe src="https://embed.example.test/player"></iframe>
            <div style="background-image: url(https://images.example.test/background)"></div>
            <a href="/assets/linked-photo.png?version=1">Image resource</a>
            <a href="https://cdn.example.test/style.css">Stylesheet resource</a>
            <a href="/about">About</a>
            <a href="https://external.test/page">External</a>
            <button data-link="/published-news-link" data-image="/news-image.jpg">News</button>
            <button data-content-html="&lt;a href=&quot;/cms-modal-link&quot;&gt;CMS link&lt;/a&gt;">Read more</button>
            <button data-summary-html="&lt;a href=&quot;/cms-summary-link&quot;&gt;Summary link&lt;/a&gt;">Summary</button>
            HTML;
        Http::fake(['*' => Http::response($html, 200, ['Content-Type' => 'text/html'])]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $monitor->step($this->rootOnly($monitor->start()));
        $this->assertEqualsCanonicalizing([
            'https://campus.test/',
            'https://campus.test/about',
            'https://external.test/page',
            'https://campus.test/published-news-link',
            'https://campus.test/cms-modal-link',
            'https://campus.test/cms-summary-link',
        ], array_column($scan['links'], 'url'));
        Http::assertSentCount(1);
    }

    public function test_all_published_news_announcement_and_memorandum_links_are_included_without_drafts_or_resources(): void
    {
        foreach (['news', 'announcements'] as $table) {
            Schema::create($table, function (Blueprint $schema) {
                $schema->id();
                $schema->string('title');
                $schema->text('content')->nullable();
                $schema->string('link')->nullable();
                $schema->string('status');
                $schema->boolean('is_hidden_from_public')->nullable();
                $schema->string('image_path')->nullable();
            });
        }
        for ($i = 1; $i <= 12; $i++) {
            DB::table('news')->insert(['title' => 'Published News '.$i, 'link' => 'https://news.test/published-'.$i, 'status' => ' approved ', 'image_path' => 'https://images.test/news-'.$i.'.jpg']);
        }
        DB::table('news')->insert([
            ['title' => 'Draft', 'link' => 'https://news.test/draft', 'status' => 'PENDING', 'is_hidden_from_public' => 0],
            ['title' => 'Hidden', 'link' => 'https://news.test/hidden', 'status' => 'APPROVED', 'is_hidden_from_public' => 1],
        ]);
        DB::table('announcements')->insert([
            ['title' => 'Published announcement', 'link' => 'https://announcements.test/live', 'content' => '<a href="/announcement-document.pdf">Document</a><img src="https://images.test/announcement.jpg">', 'status' => 'ENABLED'],
            ['title' => 'Disabled', 'link' => 'https://announcements.test/disabled', 'content' => '<a href="https://announcements.test/unpublished-body">Draft body</a>', 'status' => 'DISABLED'],
        ]);
        Schema::create('downloadables', function (Blueprint $schema) {
            $schema->id();
            $schema->string('title');
            $schema->text('description')->nullable();
            $schema->string('file_path')->nullable();
        });
        DB::table('downloadables')->insert(['title' => 'Campus notice', 'description' => '<a href="https://memo.test/reference">Reference</a> https://memo.test/plain-link <img src="https://images.test/memo.jpg">', 'file_path' => 'https://memo.test/notice.pdf']);
        $this->withSession(['user_role' => 'ADMIN']);
        $scan = app(LinkMonitoringService::class)->start();
        $urls = array_column($scan['links'], 'url');
        foreach (range(1, 12) as $i) {
            $this->assertContains('https://news.test/published-'.$i, $urls);
        }
        foreach (['https://announcements.test/live', 'https://campus.test/announcement-document.pdf', 'https://memo.test/notice.pdf', 'https://memo.test/reference', 'https://memo.test/plain-link'] as $url) {
            $this->assertContains($url, $urls);
        }
        foreach (['https://news.test/draft', 'https://news.test/hidden', 'https://announcements.test/disabled', 'https://announcements.test/unpublished-body', 'https://images.test/announcement.jpg', 'https://images.test/memo.jpg'] as $url) {
            $this->assertNotContains($url, $urls);
        }
        $this->assertSame(['https://campus.test/admin/downloadables'], $this->find($scan, 'https://memo.test/notice.pdf')['pages']);
        $this->assertSame('Campus Memorandum: Campus notice', $this->find($scan, 'https://memo.test/notice.pdf')['label']);
        Http::assertNothingSent();
    }

    public function test_shared_javascript_navigation_is_included_for_its_source_page(): void
    {
        Http::fake(['*' => Http::response('<pup-header data-news-events="/events"></pup-header><pup-footer></pup-footer>', 200, ['Content-Type' => 'text/html'])]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $monitor->step($this->rootOnly($monitor->start()));
        $this->assertSame(['https://campus.test/'], $this->find($scan, 'https://outlook.office.com/mail/')['pages']);
        $this->assertSame('pending', $this->find($scan, 'https://campus.test/events')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://www.pup.edu.ph/privacy/')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://one-portal.isaxbsit2027.com/landing')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://drive.google.com/file/d/1Ef-hJnBux5Bn9Z3L4xUNeOETT-11f_7l/view?usp=drive_link')['status']);
        $this->assertNotContains('http://www.w3.org/2000/svg', array_column($scan['links'], 'url'));
        foreach ($scan['links'] as $link) {
            $this->assertStringNotContainsString('${', $link['url']);
            $this->assertDoesNotMatchRegularExpression('/\.(?:js|css|png|jpg|woff2?)(?:\?|$)/i', $link['url']);
        }
    }

    public function test_private_destinations_are_blocked_before_requesting_redirects_and_protected_pages_are_skipped(): void
    {
        Http::fake([
            'https://campus.test/' => Http::response('<a href="/redirect">Redirect</a><a href="/admin/dashboard">CMS</a><a href="http://169.254.169.254/secret">Private</a>', 200, ['Content-Type' => 'text/html']),
            'https://campus.test/redirect' => Http::response('', 302, ['Location' => 'http://127.0.0.1/secret']),
        ]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $this->rootOnly($monitor->start());
        for ($i = 0; $scan['completed_at'] === null && $i < 10; $i++) {
            $scan = $monitor->step($scan);
        }
        $this->assertSame('not_checked', $this->find($scan, 'https://campus.test/admin/dashboard')['status']);
        $this->assertSame('not_working', $this->find($scan, 'https://campus.test/redirect')['status']);
        $this->assertSame('not_working', $this->find($scan, 'http://169.254.169.254/secret')['status']);
        Http::assertSentCount(2);
    }

    public function test_redirects_are_followed_and_connection_errors_do_not_abort_the_scan(): void
    {
        Http::fake([
            'https://campus.test/' => Http::response('', 301, ['Location' => '/new']),
            'https://campus.test/new' => Http::response('<a href="/offline">Offline</a>', 200, ['Content-Type' => 'text/html']),
            'https://campus.test/offline' => Http::failedConnection(),
        ]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $monitor->step($this->rootOnly($monitor->start()));
        $this->assertSame('working', $this->find($scan, 'https://campus.test/')['status']);
        $scan = $monitor->step($scan);
        $this->assertNotNull($scan['completed_at']);
        $this->assertSame('not_working', $this->find($scan, 'https://campus.test/offline')['status']);
    }

    public function test_monitor_requests_do_not_inflate_public_analytics(): void
    {
        Schema::shouldReceive('hasTable')->never();
        $request = Request::create('https://campus.test/home', 'GET', server: ['HTTP_USER_AGENT' => 'PUP-LinkMonitor/1.0']);
        $response = (new TrackPublicAnalytics)->handle($request, fn () => response('<html></html>'));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_local_scans_render_public_routes_without_requesting_the_single_worker_server_or_page_resources(): void
    {
        $this->app['env'] = 'local';
        $original = app('request');
        $original->setLaravelSession(app('session')->driver());
        Route::get('/monitor-fixture', fn () => response('<a href="/monitor-child">Child</a><a href="/monitor-missing">Missing</a><img src="/assets/static_img/logo.png"><a href="/monitor-action">Action</a>', 200, ['Content-Type' => 'text/html']))->name('public.monitor-fixture');
        Route::get('/monitor-child', fn () => response('<p>Child</p>', 200, ['Content-Type' => 'text/html']))->name('public.monitor-child');
        Route::get('/monitor-action', function () {
            $this->fail('Non-public routes must never be executed.');
        })->name('private.monitor-action');
        $monitor = app(LinkMonitoringService::class);
        $scan = $monitor->start();
        $scan['links'] = array_filter($scan['links'], fn ($link) => $link['url'] === 'https://campus.test/monitor-fixture');
        for ($i = 0; $scan['completed_at'] === null && $i < 10; $i++) {
            $scan = $monitor->step($scan);
        }
        $this->assertNotNull($scan['completed_at']);
        $this->assertSame('working', $this->find($scan, 'https://campus.test/monitor-child')['status']);
        $this->assertNotContains('https://campus.test/assets/static_img/logo.png', array_column($scan['links'], 'url'));
        $this->assertSame(404, $this->find($scan, 'https://campus.test/monitor-missing')['http_status']);
        $this->assertSame(403, $this->find($scan, 'https://campus.test/monitor-action')['http_status']);
        $this->assertSame($original, app('request'));
        Http::assertNothingSent();
    }

    private function rootOnly(array $scan): array
    {
        $scan['links'] = array_filter($scan['links'], fn ($link) => $link['url'] === 'https://campus.test/');

        return $scan;
    }

    private function find(array $scan, string $url): array
    {
        $rows = array_values(array_filter($scan['links'], fn ($link) => $link['url'] === $url));
        $this->assertCount(1, $rows, $url);

        return $rows[0];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([FirewallMiddleware::class, ActiveSecurityTracker::class]);
        $this->startSession();
        $this->withCredentials()->withCookie(config('session.cookie'), session()->getId());
        config(['app.url' => 'https://campus.test']);
        Http::preventStrayRequests();
    }
}

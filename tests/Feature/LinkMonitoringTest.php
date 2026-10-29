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
        $this->assertSame('not_checked', $this->find($scan, 'mailto:office@campus.test')['status']);
        $this->assertSame('not_working', $this->find($scan, 'https://campus.test/#')['status']);
        foreach (['/deep', '/small.jpg', '/large.jpg', '/poster.jpg', '/signed?key=a%20b&key=c'] as $path) {
            $this->assertSame('working', $this->find($scan, 'https://campus.test'.$path)['status']);
        }
        Http::assertSentCount(10);
    }

    public function test_shared_javascript_navigation_is_included_for_its_source_page(): void
    {
        Http::fake(['*' => Http::response('<pup-header data-news-events="/events"></pup-header><pup-footer></pup-footer>', 200, ['Content-Type' => 'text/html'])]);
        $monitor = app(LinkMonitoringService::class);
        $scan = $monitor->step($this->rootOnly($monitor->start()));
        $this->assertSame(['https://campus.test/'], $this->find($scan, 'https://outlook.office.com/mail/')['pages']);
        $this->assertSame('pending', $this->find($scan, 'https://campus.test/events')['status']);
        $this->assertSame('pending', $this->find($scan, 'https://www.pup.edu.ph/privacy/')['status']);
        $this->assertNotContains('http://www.w3.org/2000/svg', array_column($scan['links'], 'url'));
        foreach ($scan['links'] as $link) {
            $this->assertStringNotContainsString('${', $link['url']);
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

    public function test_local_scans_render_public_routes_and_assets_without_requesting_the_single_worker_server(): void
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
        $this->assertSame('working', $this->find($scan, 'https://campus.test/assets/static_img/logo.png')['status']);
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

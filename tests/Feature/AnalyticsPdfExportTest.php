<?php

namespace Tests\Feature;

use App\Services\CloudWatchService;
use App\Support\AnalyticsPdfBuilder;
use Mockery;
use Tests\TestCase;

class AnalyticsPdfExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $cloudWatch = Mockery::mock(CloudWatchService::class);
        $cloudWatch->shouldNotReceive('getServerHealth');
        $this->app->instance(CloudWatchService::class, $cloudWatch);
    }

    private function payload(): array
    {
        return [
            'download_pdf' => 1,
            'start' => '2026-11-01',
            'end' => '2026-11-02',
            'payload' => json_encode([
                'kpis' => ['total_visitors' => '30,336', 'avg_duration' => '0m 38s', 'bounce_rate' => '94.47%'],
                'user_engagement' => ['sessions' => '32,176', 'pageviews' => '40,862', 'pages_per_session' => '1.27'],
                'feedback_results' => ['total_responses' => 39],
                'upload_analytics' => ['roles' => [['role' => 'Clinic', 'count' => 3, 'percentage' => 2.5]]],
                'server_health' => ['status' => 'Healthy', 'cpu_usage' => 1, 'memory_usage' => 41],
                'announcement_reach' => ['views' => 999],
            ]),
        ];
    }

    public function test_default_pdf_has_all_supported_sections_and_no_announcement_reach(): void
    {
        $response = $this->post('/superadmin/analytics/export/pdf', $this->payload());

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        foreach (array_values(AnalyticsPdfBuilder::SECTIONS) as $index => $label) {
            $this->assertStringContainsString('Part '.($index + 1).': '.$label, $pdf);
        }
        $this->assertStringContainsString('Total Visitors: 30,336', $pdf);
        $this->assertStringContainsString('Date Range: 2026-11-01 to 2026-11-02', $pdf);
        $this->assertStringNotContainsString('Announcement Reach', $pdf);
        $this->assertStringNotContainsString('Unique Viewers', $pdf);
    }

    public function test_modal_offers_all_supported_sections_checked_by_default(): void
    {
        $html = view('superadmin.analytics.pdf_export_modal')->render();

        $this->assertStringContainsString('<dialog id="analyticsPdfExportModal"', $html);
        $this->assertSame(5, substr_count($html, 'type="checkbox"'));
        foreach (AnalyticsPdfBuilder::SECTIONS as $key => $label) {
            $this->assertStringContainsString('value="'.$key.'" form="exportPdfForm" checked', $html);
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringNotContainsString('Announcement Reach', $html);
    }

    public function test_pdf_exports_only_the_selected_sections(): void
    {
        foreach (AnalyticsPdfBuilder::SECTIONS as $section => $label) {
            $response = $this->post('/superadmin/analytics/export/pdf', [
                ...$this->payload(),
                'sections' => [$section],
            ]);

            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $pdf = $response->getContent();
            $this->assertStringContainsString('Part 1: '.$label, $pdf);
            $this->assertStringNotContainsString('Part 2:', $pdf);
            foreach (AnalyticsPdfBuilder::SECTIONS as $otherSection => $otherLabel) {
                if ($otherSection !== $section) {
                    $this->assertStringNotContainsString($otherLabel, $pdf);
                }
            }
        }
    }

    public function test_print_preview_and_its_download_form_preserve_selected_sections(): void
    {
        $response = $this->post('/superadmin/analytics/export/pdf', [
            ...$this->payload(),
            'download_pdf' => 0,
            'sections' => ['user_engagement', 'server_health'],
        ]);

        $response->assertOk()
            ->assertSee('Part 1: User Engagement')
            ->assertSee('Part 2: Server Health')
            ->assertSee('name="sections[]" value="user_engagement"', false)
            ->assertSee('name="sections[]" value="server_health"', false)
            ->assertDontSee('Monitoring Overview')
            ->assertDontSee('Feedback Result')
            ->assertDontSee('Upload Percentage')
            ->assertDontSee('Announcement Reach');
    }

    public function test_empty_or_unsupported_section_selections_are_rejected(): void
    {
        foreach ([[], ['announcement_reach'], ['unsupported'], 'server_health'] as $sections) {
            $this->postJson('/superadmin/analytics/export/pdf', [
                ...$this->payload(),
                'sections' => $sections,
            ])->assertUnprocessable();
        }
    }

    public function test_excluding_server_health_does_not_fetch_cloudwatch_metrics(): void
    {
        $this->post('/superadmin/analytics/export/pdf', [
            'download_pdf' => 1,
            'sections' => ['monitoring_overview'],
            'payload' => json_encode(['kpis' => ['total_visitors' => 42]]),
        ])->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }
}

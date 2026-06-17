<?php

namespace App\Support;

class AnalyticsPdfBuilder
{
    private const PAGE_WIDTH = 595.28;
    private const PAGE_HEIGHT = 841.89;
    private const MARGIN_LEFT = 40.0;
    private const MARGIN_RIGHT = 40.0;
    private const MARGIN_TOP = 40.0;
    private const MARGIN_BOTTOM = 72.0;
    private const FOOTER_GAP = 18.0;

    private array $pages = [];

    private array $currentPage = [];

    private float $cursorY = 0.0;

    public function build(array $report): string
    {
        $this->pages = [];
        $this->currentPage = [];
        $this->cursorY = 0.0;

        $this->startPage();
        $this->addTitle('PUP Taguig Website Analytics Report');
        $this->addLine('Polytechnic University of the Philippines - Taguig Campus', 11.0);
        $this->addGap(6.0);
        $this->addKeyValueLine('Date Range', sprintf(
            '%s to %s',
            $report['start'] !== '' ? $report['start'] : 'All Dates',
            $report['end'] !== '' ? $report['end'] : 'All Dates'
        ));
        $this->addKeyValueLine('Generated', $report['generatedAt']);
        $this->addGap();

        $this->addSection('Part 1: Monitoring Overview');
        $this->addMetricRows([
            ['Total Visitors', (string) ($report['data']['total_visitors'] ?? 0)],
            ['Avg. Session Duration', (string) ($report['data']['avg_duration'] ?? '0m 0s')],
            ['Bounce Rate', (string) ($report['data']['bounce_rate'] ?? '0%')],
            ['Total Uploads', (string) data_get($report, 'uploads.total_uploads', $report['data']['total_uploads'] ?? 0)],
        ]);

        $this->addSection('Part 2: User Engagement');
        $this->addMetricRows([
            ['Sessions', (string) ($report['data']['sessions'] ?? 0)],
            ['Page views', (string) ($report['data']['pageviews'] ?? 0)],
            ['Pages / Session', (string) ($report['data']['pages_per_session'] ?? 0)],
        ]);

        $feedback = $report['feedback'] ?? [];
        $this->addSection('Part 3: Feedback Result');
        for ($index = 1; $index <= 10; $index++) {
            $this->addKeyValueLine(
                'Q'.$index,
                number_format((float) data_get($feedback, 'question_'.$index.'_avg', 0), 2).' / 4'
            );
        }
        $this->addKeyValueLine(
            'Total Average',
            number_format((float) data_get($feedback, 'overall_average', 0), 2).' / 4'
        );
        $this->addKeyValueLine('Final Result', (string) data_get($feedback, 'final_rating', 'No Data'));
        $this->addGap(6.0);
        $this->addMetricRows([
            ['Outstanding', number_format((int) data_get($feedback, 'outstanding', 0))],
            ['Very Satisfactory', number_format((int) data_get($feedback, 'very_satisfactory', 0))],
            ['Satisfactory', number_format((int) data_get($feedback, 'satisfactory', 0))],
            ['Unsatisfactory', number_format((int) data_get($feedback, 'unsatisfactory', 0))],
            ['Total Responses', number_format((int) data_get($feedback, 'total_responses', 0))],
        ]);

        $this->addSection('Part 4: Upload Percentage');
        $roles = data_get($report, 'uploads.roles', []);
        if ($roles === []) {
            $this->addLine('No role upload data found.', 10.0, 'regular', 12.0);
        } else {
            foreach ($roles as $row) {
                $this->addLine(sprintf(
                    '%s: %s uploads (%s%%)',
                    data_get($row, 'role', 'Unknown'),
                    number_format((int) data_get($row, 'count', 0)),
                    number_format((float) data_get($row, 'percentage', 0), 2)
                ), 10.0, 'regular', 12.0);
            }
        }
        $this->addGap(6.0);
        $sources = data_get($report, 'uploads.sources', []);
        if ($sources === []) {
            $this->addLine('No uploads found.', 10.0, 'regular', 12.0);
        } else {
            foreach ($sources as $row) {
                $this->addLine(sprintf(
                    '%s: %s',
                    data_get($row, 'source', 'Uploads'),
                    number_format((int) data_get($row, 'count', 0))
                ), 10.0, 'regular', 12.0);
            }
        }

        $announcementReach = $report['announcementReach'] ?? [];
        $this->addSection('Part 5: Announcement Reach');
        $this->addMetricRows([
            ['Views', number_format((int) data_get($announcementReach, 'views', 0))],
            ['Unique Viewers', number_format((int) data_get($announcementReach, 'unique_viewers', 0))],
            ['Clicks', number_format((int) data_get($announcementReach, 'clicks', 0))],
            ['CTR', number_format((float) data_get($announcementReach, 'ctr_pct', 0), 2).'%'],
        ]);

        $serverHealth = $report['serverHealth'] ?? [];
        $this->addSection('Part 6: Server Health');
        $this->addMetricRows([
            ['Server Status', (string) data_get($serverHealth, 'status', 'Unavailable')],
            ['CPU Usage', (string) data_get($serverHealth, 'cpu_usage', '--')],
            ['Memory Usage', (string) data_get($serverHealth, 'memory_usage', '--')],
            ['Last Updated', (string) data_get($serverHealth, 'last_updated', '--')],
        ]);

        if ((string) data_get($serverHealth, 'status', 'Unavailable') === 'Unavailable') {
            $this->addLine((string) data_get(
                $serverHealth,
                'message',
                'Server health data is temporarily unavailable.'
            ), 10.0, 'regular', 12.0);
        }

        $this->finishCurrentPage();

        return $this->compilePdf();
    }

    private function addSection(string $title): void
    {
        $this->ensureVerticalSpace(34.0);
        $this->addGap(10.0);
        $this->addLine($title, 14.0, 'bold');
        $this->addGap(4.0);
    }

    private function addTitle(string $title): void
    {
        $this->ensureVerticalSpace(28.0);
        $this->addLine($title, 18.0, 'bold');
    }

    private function addMetricRows(array $rows): void
    {
        foreach ($rows as [$label, $value]) {
            $this->addKeyValueLine($label, $value);
        }
    }

    private function addKeyValueLine(string $label, string $value): void
    {
        $this->addLine($label.': '.$value, 10.5, 'regular', 12.0);
    }

    private function addLine(string $text, float $size = 11.0, string $font = 'regular', float $indent = 0.0): void
    {
        $lineHeight = max(14.0, $size + 4.0);
        $maxChars = max(24, (int) floor(($this->usableWidth() - $indent) / max(4.6, $size * 0.52)));
        $segments = $this->wrapText($text, $maxChars);

        foreach ($segments as $segment) {
            $this->ensureVerticalSpace($lineHeight);
            $this->currentPage[] = [
                'text' => $segment,
                'size' => $size,
                'font' => $font,
                'x' => self::MARGIN_LEFT + $indent,
                'y' => $this->cursorY,
            ];
            $this->cursorY -= $lineHeight;
        }
    }

    private function addGap(float $size = 10.0): void
    {
        $this->ensureVerticalSpace($size);
        $this->cursorY -= $size;
    }

    private function ensureVerticalSpace(float $height): void
    {
        if ($this->cursorY - $height >= self::MARGIN_BOTTOM) {
            return;
        }

        $this->startPage();
    }

    private function startPage(): void
    {
        if ($this->currentPage !== []) {
            $this->pages[] = $this->currentPage;
        }

        $this->currentPage = [];
        $this->cursorY = self::PAGE_HEIGHT - self::MARGIN_TOP;
    }

    private function finishCurrentPage(): void
    {
        if ($this->currentPage !== []) {
            $this->pages[] = $this->currentPage;
            $this->currentPage = [];
        }
    }

    private function compilePdf(): string
    {
        $objects = [];
        $fontRegularId = 3;
        $fontBoldId = 4;
        $pageIds = [];
        $contentIds = [];
        $pageCount = count($this->pages);

        for ($index = 0; $index < $pageCount; $index++) {
            $pageIds[$index] = 5 + ($index * 2);
            $contentIds[$index] = 6 + ($index * 2);
        }

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        $kids = implode(' ', array_map(fn (int $id) => $id.' 0 R', $pageIds));
        $objects[2] = sprintf('<< /Type /Pages /Count %d /Kids [%s] >>', $pageCount, $kids);

        $objects[$fontRegularId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objects[$fontBoldId] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";

        foreach ($this->pages as $index => $items) {
            $content = $this->compilePageContent($items);
            $pageId = $pageIds[$index];
            $contentId = $contentIds[$index];

            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $fontRegularId,
                $fontBoldId,
                $contentId
            );

            $objects[$contentId] = sprintf(
                "<< /Length %d >>\nstream\n%s\nendstream",
                strlen($content),
                $content
            );
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$body."\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $maxObjectId = max(array_keys($objects));

        $pdf .= "xref\n0 ".($maxObjectId + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= $maxObjectId; $id++) {
            $offset = $offsets[$id] ?? 0;
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n";
        $pdf .= sprintf("<< /Size %d /Root 1 0 R >>\n", $maxObjectId + 1);
        $pdf .= "startxref\n".$xrefPosition."\n%%EOF";

        return $pdf;
    }

    private function compilePageContent(array $items): string
    {
        $commands = [];

        foreach ($items as $item) {
            $font = $item['font'] === 'bold' ? 'F2' : 'F1';
            $commands[] = sprintf(
                "BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
                $font,
                $item['size'],
                $item['x'],
                $item['y'],
                $this->escapePdfText($item['text'])
            );
        }

        foreach ($this->footerLines() as $footer) {
            $commands[] = sprintf(
                "BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET",
                $footer['font'],
                $footer['size'],
                $footer['x'],
                $footer['y'],
                $this->escapePdfText($footer['text'])
            );
        }

        return implode("\n", $commands);
    }

    private function footerLines(): array
    {
        $y = self::MARGIN_BOTTOM - self::FOOTER_GAP;

        return [
            [
                'font' => 'F1',
                'size' => 9.0,
                'x' => self::MARGIN_LEFT,
                'y' => $y + 20.0,
                'text' => 'This is system-generated, signature is not required.',
            ],
            [
                'font' => 'F2',
                'size' => 9.0,
                'x' => self::MARGIN_LEFT,
                'y' => $y,
                'text' => 'This document contains personal-identifiable information that is subject to Data Privacy.',
            ],
            [
                'font' => 'F2',
                'size' => 9.0,
                'x' => self::MARGIN_LEFT,
                'y' => $y - 12.0,
                'text' => 'Please keep this document protected and in a safe place.',
            ],
        ];
    }

    private function wrapText(string $text, int $maxChars): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if ($text === '') {
            return [''];
        }

        return preg_split('/\R/', wordwrap($text, $maxChars, "\n", true)) ?: [$text];
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);

        return preg_replace('/[^\x20-\x7E]/', '?', $text) ?? '';
    }

    private function usableWidth(): float
    {
        return self::PAGE_WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT;
    }
}

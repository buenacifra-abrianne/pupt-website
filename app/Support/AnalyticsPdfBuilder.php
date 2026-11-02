<?php

namespace App\Support;

class AnalyticsPdfBuilder
{
    public const SECTIONS = [
        'monitoring_overview' => 'Monitoring Overview',
        'user_engagement' => 'User Engagement',
        'feedback_results' => 'Feedback Result',
        'upload_percentage' => 'Upload Percentage',
        'server_health' => 'Server Health',
    ];

    private const PAGE_WIDTH = 595.28;

    private const PAGE_HEIGHT = 841.89;

    private const MARGIN_LEFT = 40.0;

    private const MARGIN_RIGHT = 40.0;

    private const MARGIN_TOP = 40.0;

    private const MARGIN_BOTTOM = 90.0;

    private const MAROON = '0.4824 0 0';

    private const INK = '0.1333 0.1333 0.1333';

    private const BORDER = '0.8 0.8 0.8';

    private array $pages = [];

    private array $currentPage = [];

    private float $cursorY = 0.0;

    private ?array $logo = null;

    public function build(array $report): string
    {
        $this->pages = [];
        $this->currentPage = [];
        $this->cursorY = 0.0;
        $this->logo = $this->loadLogo();

        $this->startPage();
        $this->addReportHeader($report);

        $sections = $report['sections'] ?? array_keys(self::SECTIONS);
        $part = 0;

        if (in_array('monitoring_overview', $sections, true)) {
            $this->addSection('Part '.(++$part).': '.self::SECTIONS['monitoring_overview'], 70.0);
            $this->addSummaryCards([
                ['Total Visitors', (string) ($report['data']['total_visitors'] ?? 0)],
                ['Avg. Session Duration', (string) ($report['data']['avg_duration'] ?? '0m 0s')],
                ['Bounce Rate', (string) ($report['data']['bounce_rate'] ?? '0%')],
                ['Total Uploads', (string) data_get($report, 'uploads.total_uploads', $report['data']['total_uploads'] ?? 0)],
            ]);
        }

        if (in_array('user_engagement', $sections, true)) {
            $this->addSection('Part '.(++$part).': '.self::SECTIONS['user_engagement'], 100.0);
            $this->addMetricRows([
                ['Sessions', (string) ($report['data']['sessions'] ?? 0)],
                ['Page views', (string) ($report['data']['pageviews'] ?? 0)],
                ['Pages / Session', (string) ($report['data']['pages_per_session'] ?? 0)],
            ]);
        }

        if (in_array('feedback_results', $sections, true)) {
            $feedback = $report['feedback'] ?? [];
            $feedbackRows = [];
            for ($index = 1; $index <= 10; $index++) {
                $feedbackRows[] = [
                    'Q'.$index,
                    number_format((float) data_get($feedback, 'question_'.$index.'_avg', 0), 2).' / 4',
                ];
            }
            $feedbackRows[] = [
                'Total Average',
                number_format((float) data_get($feedback, 'overall_average', 0), 2).' / 4',
            ];
            $feedbackRows[] = ['Final Result', (string) data_get($feedback, 'final_rating', 'No Data')];
            $ratingRows = [
                ['Outstanding', number_format((int) data_get($feedback, 'outstanding', 0))],
                ['Very Satisfactory', number_format((int) data_get($feedback, 'very_satisfactory', 0))],
                ['Satisfactory', number_format((int) data_get($feedback, 'satisfactory', 0))],
                ['Unsatisfactory', number_format((int) data_get($feedback, 'unsatisfactory', 0))],
                ['Total Responses', number_format((int) data_get($feedback, 'total_responses', 0))],
            ];
            $tables = [
                ['headers' => ['Question', 'Average Score'], 'rows' => $feedbackRows, 'columns' => [0.5, 0.5]],
                ['headers' => ['Rating', 'Responses'], 'rows' => $ratingRows, 'columns' => [0.65, 0.35]],
            ];
            $this->addSection('Part '.(++$part).': '.self::SECTIONS['feedback_results'], $this->tablesHeight($tables));
            $this->addTables($tables);
        }

        if (in_array('upload_percentage', $sections, true)) {
            $roles = array_map(fn ($row) => [
                (string) data_get($row, 'role', 'Unknown'),
                number_format((int) data_get($row, 'count', 0)),
                number_format((float) data_get($row, 'percentage', 0), 2).'%',
            ], data_get($report, 'uploads.roles', []));
            $sources = array_map(fn ($row) => [
                (string) data_get($row, 'source', 'Uploads'),
                number_format((int) data_get($row, 'count', 0)),
            ], data_get($report, 'uploads.sources', []));
            $tables = [
                ['headers' => ['Uploader Role', 'Uploads', 'Percentage'], 'rows' => $roles ?: [['No role upload data found.', '', '']], 'columns' => [0.45, 0.25, 0.3]],
                ['headers' => ['Upload Source', 'Uploads'], 'rows' => $sources ?: [['No uploads found.', '']], 'columns' => [0.7, 0.3]],
            ];
            $this->addSection('Part '.(++$part).': '.self::SECTIONS['upload_percentage'], $this->tablesHeight($tables));
            $this->addTables($tables);
        }

        if (in_array('server_health', $sections, true)) {
            $serverHealth = $report['serverHealth'] ?? [];
            $this->addSection('Part '.(++$part).': '.self::SECTIONS['server_health'], 145.0);
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
        }

        $this->finishCurrentPage();

        return $this->compilePdf();
    }

    private function loadLogo(): ?array
    {
        $path = public_path('assets/static_img/logo.png');
        if (! is_file($path) || ! function_exists('imagecreatefrompng')) {
            return null;
        }

        $source = imagecreatefrompng($path);
        if ($source === false) {
            return null;
        }

        $width = 160;
        $height = max(1, (int) round(imagesy($source) * $width / imagesx($source)));
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
        ob_start();
        imagejpeg($image, null, 90);
        $data = ob_get_clean();

        return ['width' => $width, 'height' => $height, 'data' => $data];
    }

    private function addReportHeader(array $report): void
    {
        $top = $this->cursorY;
        $left = self::MARGIN_LEFT;
        if ($this->logo !== null) {
            $height = 52.0 * $this->logo['height'] / $this->logo['width'];
            $this->currentPage[] = ['command' => sprintf('q 52 0 0 %.2F %.2F %.2F cm /Logo Do Q', $height, $left, $top - $height)];
            $left += 66.0;
        }

        $this->drawText('PUP Taguig Website Analytics Report', $left, $top - 17.0, 17.0, 'bold', self::MAROON);
        $this->drawText('Polytechnic University of the Philippines - Taguig Campus', $left, $top - 35.0, 9.0);
        $this->drawText('Date Range: '.($report['start'] ?: 'All Dates').' to '.($report['end'] ?: 'All Dates'), $left, $top - 51.0, 9.0);
        $this->drawText('Generated: '.$report['generatedAt'], $left, $top - 65.0, 9.0);
        $this->drawBox(self::MARGIN_LEFT, $top - 77.0, $this->usableWidth(), 3.0, self::MAROON);
        $this->cursorY = $top - 94.0;
    }

    private function addSection(string $title, float $contentHeight = 30.0): void
    {
        // Keep a section together when it fits on a page, and reserve its first row otherwise.
        $pageHeight = self::PAGE_HEIGHT - self::MARGIN_TOP - self::MARGIN_BOTTOM;
        $requiredHeight = $contentHeight + 42.0;
        $this->ensureVerticalSpace($requiredHeight <= $pageHeight ? $requiredHeight : 90.0);
        $this->cursorY -= 20.0;
        $this->drawText($title, self::MARGIN_LEFT, $this->cursorY, 14.0, 'bold', self::MAROON);
        $this->drawBox(self::MARGIN_LEFT, $this->cursorY - 7.0, $this->usableWidth(), 1.0, '0.8667 0.8667 0.8667');
        $this->cursorY -= 18.0;
    }

    private function addSummaryCards(array $metrics): void
    {
        $gap = 8.0;
        $width = ($this->usableWidth() - $gap * (count($metrics) - 1)) / count($metrics);
        $height = 68.0;
        foreach ($metrics as $index => [$label, $value]) {
            $left = self::MARGIN_LEFT + $index * ($width + $gap);
            $this->drawBox($left, $this->cursorY, $width, $height, '0.9804 0.9804 0.9804', self::BORDER);
            $this->drawText(strtoupper($label), $left + 8.0, $this->cursorY - 18.0, 7.5, 'regular', '0.3333 0.3333 0.3333');
            $lines = $this->wrapText((string) $value, max(1, (int) (($width - 16.0) / 9.0)));
            foreach ($lines as $lineIndex => $line) {
                $this->drawText($line, $left + 8.0, $this->cursorY - 39.0 - $lineIndex * 15.0, 15.0, 'bold', self::MAROON);
            }
        }
        $this->cursorY -= $height;
    }

    private function addMetricRows(array $rows): void
    {
        $this->addTables([['headers' => ['Metric', 'Value'], 'rows' => $rows, 'columns' => [0.55, 0.45]]]);
    }

    private function tableCells(array $values, array $columns, float $width): array
    {
        return array_map(fn ($value, $fraction) => $this->wrapText(
            (string) $value,
            max(1, (int) floor(($width * $fraction - 14.0) / 5.0))
        ), $values, $columns);
    }

    private function tableRowHeight(array $cells): float
    {
        return 12.0 + 12.0 * max(array_map('count', $cells));
    }

    private function tablesHeight(array $tables): float
    {
        $width = ($this->usableWidth() - 12.0 * (count($tables) - 1)) / count($tables);
        $height = 0.0;
        foreach ($tables as $table) {
            $tableHeight = 0.0;
            foreach ([$table['headers'], ...$table['rows']] as $row) {
                $tableHeight += $this->tableRowHeight($this->tableCells($row, $table['columns'], $width));
            }
            $height = max($height, $tableHeight);
        }

        return $height;
    }

    private function drawTableRow(array $cells, array $columns, float $left, float $width, float $height, bool $header, bool $shaded): void
    {
        foreach ($cells as $index => $lines) {
            $cellWidth = $width * $columns[$index];
            $this->drawBox($left, $this->cursorY, $cellWidth, $height, $header ? self::MAROON : ($shaded ? '0.9608 0.9608 0.9608' : '1 1 1'), self::BORDER);
            foreach ($lines as $lineIndex => $line) {
                $this->drawText($line, $left + 7.0, $this->cursorY - 16.0 - $lineIndex * 12.0, 9.0, $header ? 'bold' : 'regular', $header ? '1 1 1' : self::INK);
            }
            $left += $cellWidth;
        }
    }

    private function addTables(array $tables): void
    {
        $width = ($this->usableWidth() - 12.0 * (count($tables) - 1)) / count($tables);
        $headers = array_map(fn ($table) => $this->tableCells($table['headers'], $table['columns'], $width), $tables);
        $headerHeight = max(array_map(fn ($cells) => $this->tableRowHeight($cells), $headers));
        $drawHeaders = function () use ($tables, $headers, $width, $headerHeight): void {
            foreach ($tables as $index => $table) {
                $this->drawTableRow($headers[$index], $table['columns'], self::MARGIN_LEFT + $index * ($width + 12.0), $width, $headerHeight, true, false);
            }
            $this->cursorY -= $headerHeight;
        };
        $drawHeaders();

        $rowCount = max(array_map(fn ($table) => count($table['rows']), $tables));
        for ($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++) {
            $cells = [];
            foreach ($tables as $index => $table) {
                if (isset($table['rows'][$rowIndex])) {
                    $cells[$index] = $this->tableCells($table['rows'][$rowIndex], $table['columns'], $width);
                }
            }

            // Split very long cells across pages without drawing into the footer.
            while ($cells !== []) {
                $height = max(array_map(fn ($row) => $this->tableRowHeight($row), $cells));
                if ($this->cursorY - min($height, 24.0) < self::MARGIN_BOTTOM) {
                    $this->startPage();
                    $drawHeaders();
                }
                $lineLimit = max(1, (int) floor(($this->cursorY - self::MARGIN_BOTTOM - 12.0) / 12.0));
                $height = min($height, 12.0 + 12.0 * $lineLimit);
                foreach ($cells as $index => $row) {
                    $visible = array_map(fn ($lines) => array_slice($lines, 0, $lineLimit), $row);
                    $this->drawTableRow($visible, $tables[$index]['columns'], self::MARGIN_LEFT + $index * ($width + 12.0), $width, $height, false, $rowIndex % 2 === 1);
                    $remaining = array_map(fn ($lines) => array_slice($lines, $lineLimit), $row);
                    if (max(array_map('count', $remaining)) === 0) {
                        unset($cells[$index]);
                    } else {
                        $cells[$index] = $remaining;
                    }
                }
                $this->cursorY -= $height;
            }
        }
    }

    private function drawText(string $text, float $x, float $y, float $size, string $font = 'regular', string $color = self::INK): void
    {
        $this->currentPage[] = compact('text', 'x', 'y', 'size', 'font', 'color');
    }

    private function drawBox(float $left, float $top, float $width, float $height, string $fill, ?string $stroke = null): void
    {
        $this->currentPage[] = ['command' => sprintf(
            'q %s rg %s RG 0.5 w %.2F %.2F %.2F %.2F re %s Q',
            $fill,
            $stroke ?? $fill,
            $left,
            $top - $height,
            $width,
            $height,
            $stroke === null ? 'f' : 'B'
        )];
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
        $firstPageId = $this->logo === null ? 5 : 6;

        for ($index = 0; $index < $pageCount; $index++) {
            $pageIds[$index] = $firstPageId + ($index * 2);
            $contentIds[$index] = $firstPageId + 1 + ($index * 2);
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = implode(' ', array_map(fn (int $id) => $id.' 0 R', $pageIds));
        $objects[2] = sprintf('<< /Type /Pages /Count %d /Kids [%s] >>', $pageCount, $kids);

        $objects[$fontRegularId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[$fontBoldId] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        if ($this->logo !== null) {
            $objects[5] = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length %d >>\nstream\n%s\nendstream",
                $this->logo['width'],
                $this->logo['height'],
                strlen($this->logo['data']),
                $this->logo['data']
            );
        }

        foreach ($this->pages as $index => $items) {
            $content = $this->compilePageContent($items);
            $pageId = $pageIds[$index];
            $contentId = $contentIds[$index];

            $objects[$pageId] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 %d 0 R /F2 %d 0 R >> %s >> /Contents %d 0 R >>',
                self::PAGE_WIDTH,
                self::PAGE_HEIGHT,
                $fontRegularId,
                $fontBoldId,
                $this->logo === null ? '' : '/XObject << /Logo 5 0 R >>',
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
            if (isset($item['command'])) {
                $commands[] = $item['command'];

                continue;
            }
            $font = $item['font'] === 'bold' ? 'F2' : 'F1';
            $commands[] = sprintf(
                'q %s rg BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET Q',
                $item['color'] ?? self::INK,
                $font,
                $item['size'],
                $item['x'],
                $item['y'],
                $this->escapePdfText($item['text'])
            );
        }

        $commands[] = sprintf('q %s RG 0.75 w %.2F 55 m %.2F 55 l S Q', self::MAROON, self::MARGIN_LEFT, self::PAGE_WIDTH - self::MARGIN_RIGHT);
        foreach ($this->footerLines() as $footer) {
            $commands[] = sprintf(
                'q %s rg BT /%s %.2F Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET Q',
                $footer['color'],
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
        return [
            [
                'font' => 'F1',
                'size' => 8.0,
                'x' => self::PAGE_WIDTH - self::MARGIN_RIGHT - 186.0,
                'y' => 66.0,
                'color' => self::INK,
                'text' => 'This is system-generated, signature is not required.',
            ],
            [
                'font' => 'F2',
                'size' => 8.0,
                'x' => self::MARGIN_LEFT,
                'y' => 42.0,
                'color' => self::MAROON,
                'text' => 'This document contains personal-identifiable information that is subject to Data Privacy.',
            ],
            [
                'font' => 'F2',
                'size' => 8.0,
                'x' => self::MARGIN_LEFT + 72.0,
                'y' => 30.0,
                'color' => self::MAROON,
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

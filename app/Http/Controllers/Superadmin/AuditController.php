<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\AuditLog;
use App\Support\Avatar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditController extends Controller
{
    public function index()
    {
        $auditLogs = [];
        $auditStats = [
            'total' => 0,
            'account' => 0,
            'content' => 0,
        ];

        if (!Schema::hasTable('activity_logs')) {
            return view('superadmin.audit', compact('auditLogs', 'auditStats'));
        }

        $columns = Schema::getColumnListing('activity_logs');
        $idColumn = $this->resolveIdColumn($columns);

        $select = array_values(array_filter([
            $idColumn,
            in_array('user_id', $columns, true) ? 'user_id' : null,
            in_array('user_name', $columns, true) ? 'user_name' : null,
            in_array('action', $columns, true) ? 'action' : null,
            in_array('module', $columns, true) ? 'module' : null,
            in_array('description', $columns, true) ? 'description' : null,
            in_array('ip_address', $columns, true) ? 'ip_address' : null,
            in_array('created_at', $columns, true) ? 'created_at' : null,
        ]));

        $rows = DB::table('activity_logs')
            ->select($select)
            ->orderByDesc(in_array('created_at', $columns, true) ? 'created_at' : $idColumn)
            ->limit(1000)
            ->get();

        $allActions = DB::table('activity_logs')
            ->select([
                in_array('action', $columns, true) ? 'action' : DB::raw("'' as action"),
                in_array('module', $columns, true) ? 'module' : DB::raw("'' as module"),
                in_array('created_at', $columns, true) ? 'created_at' : DB::raw("null as created_at"),
            ])
            ->get();

        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sixtyDaysAgo = $now->copy()->subDays(60);

        $sparklineBuckets = [];
        for ($i = 9; $i >= 0; $i--) {
            $sparklineBuckets[$now->copy()->subDays($i)->format('Y-m-d')] = ['total' => 0, 'account' => 0, 'content' => 0];
        }
        $trends = [
            'total_curr' => 0, 'total_prev' => 0,
            'account_curr' => 0, 'account_prev' => 0,
            'content_curr' => 0, 'content_prev' => 0,
        ];

        foreach ($allActions as $event) {
            $action = (string) ($event->action ?? '');
            $module = (string) ($event->module ?? '');
            $createdAt = !empty($event->created_at) ? Carbon::parse($event->created_at) : null;

            if (!AuditLog::includeInAudit($action, $module)) {
                continue;
            }

            $isAccount = AuditLog::isAccountEvent($action, $module);
            $isContent = AuditLog::isContentEvent($action, $module);

            $auditStats['total']++;
            if ($isAccount) $auditStats['account']++;
            if ($isContent) $auditStats['content']++;

            if ($createdAt) {
                if ($createdAt >= $thirtyDaysAgo) {
                    $trends['total_curr']++;
                    if ($isAccount) $trends['account_curr']++;
                    if ($isContent) $trends['content_curr']++;

                    $dateStr = $createdAt->format('Y-m-d');
                    if (isset($sparklineBuckets[$dateStr])) {
                        $sparklineBuckets[$dateStr]['total']++;
                        if ($isAccount) $sparklineBuckets[$dateStr]['account']++;
                        if ($isContent) $sparklineBuckets[$dateStr]['content']++;
                    }
                } elseif ($createdAt >= $sixtyDaysAgo) {
                    $trends['total_prev']++;
                    if ($isAccount) $trends['account_prev']++;
                    if ($isContent) $trends['content_prev']++;
                }
            }
        }

        $calcTrend = function($curr, $prev) {
            if ($prev == 0) return $curr > 0 ? 100 : 0;
            return round((($curr - $prev) / $prev) * 100, 1);
        };
        $auditStats['total_trend'] = $calcTrend($trends['total_curr'], $trends['total_prev']);
        $auditStats['account_trend'] = $calcTrend($trends['account_curr'], $trends['account_prev']);
        $auditStats['content_trend'] = $calcTrend($trends['content_curr'], $trends['content_prev']);

        $buildSvg = function($key) use ($sparklineBuckets) {
            $vals = array_column($sparklineBuckets, $key);
            $max = max($vals);
            $min = min($vals);
            $range = $max - $min;
            if ($range == 0) $range = 1;

            $pts = [];
            foreach ($vals as $i => $v) {
                $x = round($i * (100 / 9), 1);
                $y = round(40 - (($v - $min) / $range) * 30, 1);
                $pts[] = "{$x},{$y}";
            }
            return 'M' . implode(' L', $pts);
        };

        $auditStats['total_svg'] = $buildSvg('total');
        $auditStats['account_svg'] = $buildSvg('account');
        $auditStats['content_svg'] = $buildSvg('content');

        $userMap = [];
        if (in_array('user_id', $columns, true)) {
            $userIds = $rows
                ->pluck('user_id')
                ->filter(fn ($id) => (int) $id > 0)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (!empty($userIds) && Schema::hasTable('users')) {
                $userPk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
                $userSelect = array_values(array_filter([
                    'users.' . $userPk,
                    'users.first_name',
                    'users.last_name',
                    'users.name',
                    Schema::hasColumn('users', 'role') ? 'users.role as legacy_role' : null,
                    Schema::hasColumn('users', 'profile_picture') ? 'users.profile_picture' : null,
                ]));
                $userRows = DB::table('users')
                    ->whereIn('users.' . $userPk, $userIds)
                    ->select($userSelect)
                    ->get();

                $rolesByUser = collect();
                if (Schema::hasTable('user_roles')) {
                    $rolesByUser = DB::table('user_roles')
                        ->whereIn('user_id', $userIds)
                        ->orderByDesc('is_primary')
                        ->orderBy('id')
                        ->get()
                        ->groupBy('user_id')
                        ->map(fn ($roles) => strtoupper((string) ($roles->first()->role_code ?? '')));
                }

                foreach ($userRows as $user) {
                    $name = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));
                    if ($name === '') {
                        $name = (string) ($user->name ?? 'Unknown');
                    }

                    $userId = (int) $user->{$userPk};
                    $userMap[(int) $user->{$userPk}] = [
                        'name' => $name,
                        'role' => (string) ($rolesByUser->get($userId)
                            ?: strtoupper((string) ($user->legacy_role ?? ''))),
                        'avatar_url' => Avatar::resolveUrl((string) ($user->profile_picture ?? '')),
                        'avatar_initials' => Avatar::initials(
                            $name,
                            (string) ($user->first_name ?? ''),
                            (string) ($user->last_name ?? '')
                        ),
                    ];
                }
            }
        }

        foreach ($rows as $idx => $row) {
            $userId = (int) ($row->user_id ?? 0);
            $userName = trim((string) ($row->user_name ?? ''));
            if ($userName === '' && $userId > 0 && isset($userMap[$userId])) {
                $userName = $userMap[$userId]['name'];
            }

            $role = $userId > 0 && isset($userMap[$userId]) ? $userMap[$userId]['role'] : 'SYSTEM';
            $action = strtoupper((string) ($row->action ?? 'SYSTEM'));
            $module = strtoupper((string) ($row->module ?? 'SYSTEM'));

            if (!AuditLog::includeInAudit($action, $module)) {
                continue;
            }

            $timestamp = (string) ($row->created_at ?? now()->toDateTimeString());
            try {
                $parsedTs = Carbon::parse($timestamp);
            } catch (\Throwable $e) {
                $parsedTs = now();
            }

            $auditLogs[] = [
                'id' => (int) ($row->{$idColumn} ?? ($idx + 1)),
                'user' => $userName !== '' ? $userName : 'System',
                'role' => $role !== '' ? $role : 'SYSTEM',
                'action' => $action,
                'module' => $module,
                'desc' => (string) ($row->description ?? ''),
                'ip' => (string) ($row->ip_address ?? '-'),
                'ts' => $parsedTs->toIso8601String(),
                'av' => 'av-0',
                'avatar_url' => $userId > 0 && isset($userMap[$userId]) ? $userMap[$userId]['avatar_url'] : '',
                'avatar_initials' => $userId > 0 && isset($userMap[$userId])
                    ? $userMap[$userId]['avatar_initials']
                    : Avatar::initials($userName !== '' ? $userName : 'System'),
            ];
        }

        return view('superadmin.audit', compact('auditLogs', 'auditStats'));
    }

    private function resolveIdColumn(array $columns): string
    {
        foreach (['id', 'activity_log_id', 'log_id'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return $columns[0] ?? 'id';
    }

    public function exportExcel(\Illuminate\Http\Request $request)
    {
        $payload = json_decode((string) $request->input('payload', ''), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Audit Trail');

        $headers = ['#', 'User', 'Role', 'Action', 'Module', 'Description', 'IP Address', 'Timestamp'];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '1', $header);
            $colIndex++;
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7F1113'],
            ],
        ];

        $zebraStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8EFE8'],
            ],
        ];

        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->freezePane('A2');

        $rowNum = 2;
        foreach ($payload as $idx => $log) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $log['user'] ?? '');
            $sheet->setCellValue('C' . $rowNum, $log['role'] ?? '');
            $sheet->setCellValue('D' . $rowNum, $log['action'] ?? '');
            $sheet->setCellValue('E' . $rowNum, $log['module'] ?? '');
            $sheet->setCellValue('F' . $rowNum, $log['desc'] ?? '');
            $sheet->setCellValue('G' . $rowNum, $log['ip'] ?? '');
            
            // Handle timestamp cleanly
            $ts = $log['ts'] ?? '';
            if ($ts !== '') {
                try {
                    $ts = \Carbon\Carbon::parse($ts)->format('Y-m-d H:i:s');
                } catch (\Throwable $e) {}
            }
            $sheet->setCellValue('H' . $rowNum, $ts);

            if ($rowNum % 2 === 0) {
                $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->applyFromArray($zebraStyle);
            }

            $rowNum++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'audit_trail_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

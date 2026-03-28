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
            ])
            ->get();

        foreach ($allActions as $event) {
            $action = (string) ($event->action ?? '');
            $module = (string) ($event->module ?? '');

            if (!AuditLog::includeInAudit($action, $module)) {
                continue;
            }

            $auditStats['total']++;
            if (AuditLog::isAccountEvent($action, $module)) {
                $auditStats['account']++;
            }
            if (AuditLog::isContentEvent($action, $module)) {
                $auditStats['content']++;
            }
        }

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
                    'roles.code as role_code',
                    Schema::hasColumn('users', 'profile_picture') ? 'users.profile_picture' : null,
                ]));
                $userRows = DB::table('users')
                    ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                    ->whereIn('users.' . $userPk, $userIds)
                    ->select($userSelect)
                    ->get();

                foreach ($userRows as $user) {
                    $name = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));
                    if ($name === '') {
                        $name = (string) ($user->name ?? 'Unknown');
                    }

                    $userMap[(int) $user->{$userPk}] = [
                        'name' => $name,
                        'role' => strtoupper((string) ($user->role_code ?? '')),
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
}

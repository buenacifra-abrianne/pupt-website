<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuditLog
{
    private const ACCOUNT_ACTIONS = [
        'LOGIN',
        'LOGOUT',
        'SECURITY',
        'FAILED_LOGIN',
        'UNAUTHORIZED',
        'LOCKED',
    ];

    private const ACCOUNT_MODULES = [
        'AUTHENTICATION',
        'SECURITY',
    ];

    private const CMS_MODULES = [
        'ACCOUNT',
        'ACCOUNTS',
        'ANNOUNCEMENT',
        'ANNOUNCEMENTS',
        'NEWS',
        'CONTENT',
        'CMS',
    ];

    private const CHANGE_ACTIONS = [
        'CREATED',
        'UPDATED',
        'DELETED',
        'ENABLED',
        'DISABLED',
        'APPROVED',
        'REJECTED',
        'MARK_READ',
        'DISMISSED',
    ];

    private static ?array $columns = null;

    public static function record(
        string $action,
        string $module,
        string $description,
        ?int $targetId = null,
        array $context = []
    ): void {
        if (!self::tableReady()) {
            return;
        }

        $action = strtoupper(trim($action));
        $module = strtoupper(trim($module));

        $userId = $context['user_id'] ?? (int) session('user_id');
        $userName = $context['user_name'] ?? self::sessionUserName();
        $ipAddress = $context['ip_address'] ?? request()?->ip();

        $row = [];

        if (self::hasColumn('user_id')) {
            $row['user_id'] = $userId > 0 ? $userId : null;
        }
        if (self::hasColumn('user_name')) {
            $row['user_name'] = $userName !== '' ? $userName : 'System';
        }
        if (self::hasColumn('action')) {
            $row['action'] = $action;
        }
        if (self::hasColumn('module')) {
            $row['module'] = $module;
        }
        if (self::hasColumn('target_id')) {
            $row['target_id'] = $targetId;
        }
        if (self::hasColumn('description')) {
            $row['description'] = $description;
        }
        if (self::hasColumn('ip_address')) {
            $row['ip_address'] = $ipAddress;
        }
        if (self::hasColumn('created_at')) {
            $row['created_at'] = now();
        }
        if (self::hasColumn('updated_at')) {
            $row['updated_at'] = now();
        }

        if (empty($row)) {
            return;
        }

        try {
            DB::table('activity_logs')->insert($row);
        } catch (\Throwable $e) {
            Log::warning('Failed to write activity log', ['error' => $e->getMessage()]);
        }
    }

    public static function includeInAudit(?string $action, ?string $module): bool
    {
        return self::isAccountEvent($action, $module) || self::isContentEvent($action, $module);
    }

    public static function isAccountEvent(?string $action, ?string $module): bool
    {
        $action = strtoupper(trim((string) $action));
        $module = strtoupper(trim((string) $module));

        if (in_array($action, self::ACCOUNT_ACTIONS, true)) {
            return true;
        }

        if (in_array($module, self::ACCOUNT_MODULES, true)) {
            return true;
        }

        if ($module === 'ACCOUNT' || $module === 'ACCOUNTS') {
            return self::isChangeAction($action);
        }

        return false;
    }

    public static function isContentEvent(?string $action, ?string $module): bool
    {
        $action = strtoupper(trim((string) $action));
        $module = strtoupper(trim((string) $module));

        if (!in_array($module, self::CMS_MODULES, true)) {
            return false;
        }

        return self::isChangeAction($action);
    }

    public static function isChangeAction(?string $action): bool
    {
        return in_array(strtoupper(trim((string) $action)), self::CHANGE_ACTIONS, true);
    }

    private static function tableReady(): bool
    {
        if (self::$columns !== null) {
            return true;
        }

        if (!Schema::hasTable('activity_logs')) {
            return false;
        }

        self::$columns = Schema::getColumnListing('activity_logs');
        return true;
    }

    private static function hasColumn(string $column): bool
    {
        return in_array($column, self::$columns ?? [], true);
    }

    private static function sessionUserName(): string
    {
        $fullName = trim(
            (string) session('user_first_name', '') . ' ' . (string) session('user_last_name', '')
        );

        if ($fullName !== '') {
            return $fullName;
        }

        return (string) session('user_name', '');
    }
}

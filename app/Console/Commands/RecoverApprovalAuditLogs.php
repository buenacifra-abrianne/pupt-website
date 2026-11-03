<?php

namespace App\Console\Commands;

use App\Support\ApprovalAudit;
use App\Support\CmsSections;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecoverApprovalAuditLogs extends Command
{
    protected $signature = 'audit:recover-approvals {--write : Insert recovered events; otherwise preview only}';

    protected $description = 'Recover missing APPROVED events from retained approval history, preserving review times.';

    public function handle(): int
    {
        if (! Schema::hasTable('approval_requests') || ! Schema::hasTable('activity_logs')
            || ! Schema::hasColumn('activity_logs', 'approval_request_id')) {
            $this->error('Run migrations first; approval history and the approval audit column are required.');

            return self::FAILURE;
        }

        $write = (bool) $this->option('write');
        $counts = ['recoverable' => 0, 'existing' => 0, 'legacy' => 0, 'skipped' => 0];
        $columns = Schema::getColumnListing('activity_logs');

        DB::table('approval_requests')
            ->whereRaw("LOWER(TRIM(status)) = 'approved'")
            ->chunkById(100, function ($requests) use ($write, $columns, &$counts) {
                foreach ($requests as $candidate) {
                    DB::transaction(function () use ($candidate, $write, $columns, &$counts) {
                        $request = DB::table('approval_requests')->where('id', $candidate->id)->lockForUpdate()->first();
                        if (! $request || strtolower(trim((string) $request->status)) !== 'approved') {
                            return;
                        }

                        if (DB::table('activity_logs')->where('approval_request_id', $request->id)->exists()) {
                            $counts['existing']++;

                            return;
                        }

                        if (empty($request->reviewed_at)) {
                            $counts['skipped']++;
                            $this->warn("Request #{$request->id}: no review timestamp; skipped.");

                            return;
                        }

                        if ($this->hasLegacyEvent($request)) {
                            $counts['legacy']++;

                            return;
                        }

                        $this->line("Request #{$request->id}: {$request->type}, reviewed {$request->reviewed_at}");
                        $counts['recoverable']++;
                        if (! $write) {
                            return;
                        }

                        $row = [
                            'approval_request_id' => $request->id,
                            'action' => 'APPROVED',
                            'module' => ApprovalAudit::module($request->type),
                            'target_id' => $request->id,
                            'description' => 'Recovered from approval history. '.ApprovalAudit::description($request),
                            'user_id' => (int) $request->reviewed_by > 0 ? (int) $request->reviewed_by : null,
                            'user_name' => $this->reviewerName((int) $request->reviewed_by),
                            // The original IP was not saved in approval_requests. Legacy schemas require a string.
                            'ip_address' => '',
                            'created_at' => $request->reviewed_at,
                            'updated_at' => now(),
                        ];
                        DB::table('activity_logs')->insert(array_intersect_key($row, array_flip($columns)));
                    });
                }
            });

        $verb = $write ? 'Recovered' : 'Would recover';
        $this->info("{$verb} {$counts['recoverable']} approval event(s). Existing: {$counts['existing']}; legacy matches: {$counts['legacy']}; missing timestamps: {$counts['skipped']}.");
        if (! $write) {
            $this->comment('Preview only. Run with --write to insert the recovered events.');
        }

        return self::SUCCESS;
    }

    private function hasLegacyEvent(object $request): bool
    {
        $type = strtoupper(trim((string) $request->type));
        if (! str_starts_with($type, 'CMS_') || ! str_ends_with($type, '_EDIT')) {
            return false;
        }

        $payload = json_decode($request->details ?? '{}', true) ?: [];
        $tabKey = $payload['tab_key'] ?? CmsSections::tabForRequestType($type);
        $reviewedAt = Carbon::parse($request->reviewed_at);

        // Old CMS events have no request link. Conservatively skip nearby matches
        // rather than count an already recorded decision twice.
        return DB::table('activity_logs')
            ->whereNull('approval_request_id')
            ->where('action', 'APPROVED')
            ->where('module', 'CONTENT')
            ->where('user_id', (int) $request->reviewed_by)
            ->where('description', 'Approved CMS content edit for '.CmsSections::labelForTab((string) $tabKey))
            ->whereBetween('created_at', [
                $reviewedAt->copy()->subSeconds(5),
                $reviewedAt->copy()->addSeconds(5),
            ])
            ->exists();
    }

    private function reviewerName(int $userId): string
    {
        if ($userId > 0 && Schema::hasTable('users')) {
            $key = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
            $user = DB::table('users')->where($key, $userId)->first();
            if ($user) {
                $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

                return $name !== '' ? $name : ($user->name ?? 'Unknown reviewer');
            }
        }

        return $userId > 0 ? 'Unknown reviewer #'.$userId : 'Unknown reviewer';
    }
}

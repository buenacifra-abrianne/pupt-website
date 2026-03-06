<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAccountTempPasswordMail;
use App\Support\AuditLog;

class AccountsController extends Controller
{
    public function index()
{
    $roles = $this->fetchRolesForUi();

    // ✅ ensure FACULTY exists for dropdown display
    if (!$roles->contains('code', 'FACULTY')) {
        $roles->push((object)[
            'code' => 'FACULTY',
            'name' => 'Faculty',
            'level' => 0,
        ]);
    }

    // (optional) same idea if you want Student dropdown mapping later
    // if (!$roles->contains('code', 'STUDENT')) {
    //     $roles->push((object)['code'=>'STUDENT','name'=>'Student','level'=>0]);
    // }

    $acceptedCodes = $roles->pluck('code')->map(fn($c) => (string)$c)->all();

    // ✅ allow users stored as base role codes
    if (in_array('FACULTY', $acceptedCodes, true)) $acceptedCodes[] = 'pupt:faculty';
    if (in_array('STUDENT', $acceptedCodes, true)) $acceptedCodes[] = 'pupt:student';

    $rows = DB::table('users')
    ->select('user_id','first_name','last_name','email','role','status','last_login_at')
    ->whereIn('role', $acceptedCodes)
    ->orderBy('user_id', 'desc')
    ->get();

$userIds = $rows->pluck('user_id')->map(fn($id) => (int) $id)->all();

$rolesByUser = collect();
if (Schema::hasTable('user_roles') && !empty($userIds)) {
    $rolesByUser = DB::table('user_roles')
        ->select('user_id', 'role_code', 'is_primary')
        ->whereIn('user_id', $userIds)
        ->orderByDesc('is_primary')
        ->orderBy('id')
        ->get()
        ->groupBy('user_id');
}

$mapped = $rows->map(function ($u) use ($rolesByUser) {
    $userRoleRows = $rolesByUser->get((int) $u->user_id, collect());

    $roleCodes = $userRoleRows->pluck('role_code')
        ->map(fn($r) => (string) $r)
        ->values()
        ->all();

    if (empty($roleCodes) && !empty($u->role)) {
        $roleCodes = [(string) $u->role];
    }

    return [
        'id'    => (int) $u->user_id,
        'fn'    => (string) $u->first_name,
        'ln'    => (string) $u->last_name,
        'em'    => (string) $u->email,
        'rl'    => (string) ($roleCodes[0] ?? $u->role),
        'roles' => $roleCodes,
        'st'    => (string) $u->status,
        'll'    => $u->last_login_at ? (string) $u->last_login_at : 'Never',
        'av'    => 'av-0',
    ];
});

    return view('superadmin.accounts', [
        'usersJson' => $mapped->toJson(),
        'rolesJson' => $roles->toJson(),
    ]);
}

private function fetchRolesForUi()
{
    if (Schema::hasTable('roles')) {
        return DB::table('roles')
            ->select('code', 'name', 'level')
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('scope', 'CMS')
                  ->orWhereIn('code', ['GLOBAL_SUPERADMIN', 'SYSTEM_SUPERADMIN']);
            })
            ->orderByDesc('level')
            ->get();
    }

    $fallbackCodes = [];
    if (Schema::hasTable('users')) {
        $fallbackCodes = DB::table('users')
            ->whereNotNull('role')
            ->pluck('role')
            ->map(fn ($r) => $this->normalizeRoleCode((string) $r))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    if (empty($fallbackCodes)) {
        $fallbackCodes = ['FACULTY', 'SYSTEM_SUPERADMIN', 'GLOBAL_SUPERADMIN'];
    }

    return collect($fallbackCodes)
        ->map(fn ($code) => (object) [
            'code' => $code,
            'name' => Str::headline(str_replace('_', ' ', str_replace(':', ' ', strtolower($code)))),
            'level' => 0,
        ])
        ->values();
}

private function denyIfSystemTouchesGlobal(string $action, string $targetRole): ?\Illuminate\Http\JsonResponse
{
    $actorRole = strtoupper(trim((string) session('user_role')));
    $targetRole = strtoupper(trim($targetRole));

    // RULE: SYSTEM_SUPERADMIN cannot create/edit/suspend GLOBAL_SUPERADMIN
    if ($actorRole === 'SYSTEM_SUPERADMIN' && $targetRole === 'GLOBAL_SUPERADMIN') {
        $msg = match ($action) {
            'create' => 'You are not allowed to create a Global Superadmin account.',
            'edit'   => 'You are not allowed to edit a Global Superadmin account.',
            'suspend'=> 'You are not allowed to suspend a Global Superadmin account.',
            default  => 'You are not allowed to perform this action on a Global Superadmin account.',
        };

        return response()->json(['ok' => false, 'message' => $msg], 403);
    }

    return null;
}

private function normalizeRoleCode(string $raw): string
{
    $raw = trim($raw);

    if (str_contains($raw, ':')) {
        return $raw;
    }

    return strtoupper(preg_replace('/\s+/', '_', $raw));
}

private function normalizeRoleCodesFromRequest(Request $request): array
{
    $roles = $request->input('roles', []);

    if (!is_array($roles) || empty($roles)) {
        $single = (string) $request->input('role', '');
        if ($single !== '') {
            $roles = [$single];
        }
    }

    $roles = array_map(fn($r) => $this->normalizeRoleCode((string) $r), $roles);
    $roles = array_values(array_unique(array_filter($roles)));

    return $roles;
}

private function allowedRoleCodesForAccounts(): array
{
    if (Schema::hasTable('roles')) {
        $allowedCodes = DB::table('roles')
            ->where('is_active', 1)
            ->where('scope', 'CMS')
            ->pluck('code')
            ->map(fn($c) => (string) $c)
            ->all();
    } else {
        $allowedCodes = [];
        if (Schema::hasTable('users')) {
            $allowedCodes = DB::table('users')
                ->whereNotNull('role')
                ->pluck('role')
                ->map(fn ($r) => $this->normalizeRoleCode((string) $r))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }
    }

    if (in_array('FACULTY', $allowedCodes, true)) {
        $allowedCodes[] = 'pupt:faculty';
    }

    return array_values(array_unique($allowedCodes));
}

private function saveUserRoles(int $userId, array $roleCodes): void
{
    if (!Schema::hasTable('user_roles')) {
        return;
    }

    DB::table('user_roles')->where('user_id', $userId)->delete();

    $rows = [];
    foreach ($roleCodes as $i => $code) {
        $rows[] = [
            'user_id'     => $userId,
            'role_code'   => $code,
            'is_primary'  => $i === 0 ? 1 : 0,
            'assigned_by' => session('user_id') ? (int) session('user_id') : null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }

    if (!empty($rows)) {
        DB::table('user_roles')->insert($rows);
    }
}

private function logAccountEvent(string $action, int $targetUserId, string $description): void
{
    AuditLog::record($action, 'ACCOUNTS', $description, $targetUserId);
}

    public function store(Request $request)
{
    $validStatus = ['Active','Inactive','Suspended'];

    $data = $request->validate([
        'first_name' => ['required','string','max:80'],
        'last_name'  => ['required','string','max:80'],
        'email'      => ['required','email','max:190', Rule::unique('users','email')],
        'status'     => ['required', Rule::in($validStatus)],
        'roles'      => ['nullable','array'],
        'roles.*'    => ['string','max:80'],
        'role'       => ['nullable','string','max:80'],
    ]);

    $creatorRole = strtoupper(trim((string) session('user_role')));
    $requestedRoleCodes = $this->normalizeRoleCodesFromRequest($request);

    if (empty($requestedRoleCodes)) {
        return response()->json([
            'ok' => false,
            'message' => 'Please select at least one role.'
        ], 422);
    }

    if (in_array('GLOBAL_SUPERADMIN', $requestedRoleCodes, true)) {
        return response()->json([
            'ok' => false,
            'message' => 'Global Superadmin cannot be created from this page.'
        ], 403);
    }

    if ($creatorRole === 'SYSTEM_SUPERADMIN' && in_array('SYSTEM_SUPERADMIN', $requestedRoleCodes, true)) {
        return response()->json([
            'ok' => false,
            'message' => 'Only Global Superadmin can create a System Superadmin account.'
        ], 403);
    }

    $allowedCodes = $this->allowedRoleCodesForAccounts();

    foreach ($requestedRoleCodes as $code) {
        if ($code === 'SYSTEM_SUPERADMIN') {
            if ($creatorRole !== 'GLOBAL_SUPERADMIN') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Only Global Superadmin can create a System Superadmin account.'
                ], 403);
            }
            continue;
        }

        if (!in_array($code, $allowedCodes, true)) {
            return response()->json([
                'ok' => false,
                'message' => "Invalid role selected: {$code}"
            ], 422);
        }
    }

    $primaryRole = $requestedRoleCodes[0];
    $name = trim($data['first_name'] . ' ' . $data['last_name']);

    $insert = [
        'first_name'    => $data['first_name'],
        'last_name'     => $data['last_name'],
        'name'          => $name,
        'email'         => $data['email'],
        'role'          => $primaryRole,
        'status'        => $data['status'],
        'last_login_at' => null,
        'created_at'    => now(),
        'updated_at'    => now(),
    ];

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
    $newUserId = DB::table('users')->insertGetId($insert, $pk);

    $this->saveUserRoles((int) $newUserId, $requestedRoleCodes);

    $this->logAccountEvent(
        'CREATED',
        (int) $newUserId,
        'Created account for '.$data['email'].' with role '.$primaryRole
    );

    $roleLabel = $primaryRole;
    if (Schema::hasTable('roles')) {
        $roleRow = DB::table('roles')->where('code', $primaryRole)->first();
        if ($roleRow && !empty($roleRow->name)) {
            $roleLabel = (string) $roleRow->name;
        }
    }

    $emailSent = false;
    try {
        Mail::to($data['email'])->queue(
            new NewAccountTempPasswordMail(
                $name,
                $data['email'],
                $roleLabel,
                $tempPassword
            )
        );
        $emailSent = true;
    } catch (\Throwable $e) {
        \Log::error('Temp password email failed: '.$e->getMessage(), ['email' => $data['email']]);
    }

    return response()->json([
    'ok' => true,
    'user' => [
        'id' => (int) $newUserId,
        'fn' => $data['first_name'],
        'ln' => $data['last_name'],
        'em' => $data['email'],
        'rl' => $primaryRole,
        'st' => $data['status'],
        'll' => 'Never',
    ]
    ]);
}

public function setStatus(Request $request, $id)
{
    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    $target = DB::table('users')->select($pk.' as id', 'role')->where($pk, (int)$id)->first();
    if (!$target) {
        return response()->json(['ok' => false, 'message' => 'User not found.'], 404);
    }

    $newStatus = (string) $request->input('status', '');
    $newStatusUpper = strtoupper(trim($newStatus));

    // Only block if they are trying to SUSPEND a GLOBAL_SUPERADMIN
    if ($newStatusUpper === 'SUSPENDED') {
        if ($resp = $this->denyIfSystemTouchesGlobal('suspend', (string)$target->role)) {
            return $resp;
        }
    }

    // validate allowed statuses
    $validStatus = ['Active','Inactive','Suspended'];
    $request->validate([
        'status' => ['required', Rule::in($validStatus)],
    ]);

    DB::table('users')->where($pk, (int)$id)->update([
        'status' => $newStatus,
        'updated_at' => now(),
    ]);

    if (strtoupper($newStatus) === 'SUSPENDED') {
        AuditLog::record(
            'SECURITY',
            'SECURITY',
            'Suspended account ID '.$id,
            (int) $id
        );
    } else {
        $this->logAccountEvent('UPDATED', (int) $id, 'Updated account status to '.$newStatus);
    }

    return response()->json(['ok' => true]);
}

private function blockIfSystemTargetsGlobal(int $targetId, string $action)
{
    $actorRole = strtoupper(trim((string) session('user_role')));
    if ($actorRole !== 'SYSTEM_SUPERADMIN') return null;

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    $target = DB::table('users')->select($pk.' as id', 'role')->where($pk, $targetId)->first();
    if (!$target) return response()->json(['ok'=>false,'message'=>'User not found.'], 404);

    if (strtoupper(trim((string)$target->role)) === 'GLOBAL_SUPERADMIN') {
        $msg = $action === 'edit'
            ? 'You are not allowed to edit a Global Superadmin account.'
            : 'You are not allowed to suspend a Global Superadmin account.';
        return response()->json(['ok'=>false,'message'=>$msg], 403);
    }

    return null;
}

public function update(Request $request, $id)
{
    $id = (int) $id;
    if ($resp = $this->blockIfSystemTargetsGlobal($id, 'edit')) return $resp;

    $validStatus = ['Active','Inactive','Suspended'];
    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    $data = $request->validate([
        'first_name' => ['required','string','max:80'],
        'last_name'  => ['required','string','max:80'],
        'email'      => ['required','email','max:190', Rule::unique('users','email')->ignore($id, $pk)],
        'status'     => ['required', Rule::in($validStatus)],
        'roles'      => ['nullable','array'],
        'roles.*'    => ['string','max:80'],
        'role'       => ['nullable','string','max:80'],
    ]);

    $requestedRoleCodes = $this->normalizeRoleCodesFromRequest($request);

    if (empty($requestedRoleCodes)) {
        return response()->json([
            'ok' => false,
            'message' => 'Please select at least one role.'
        ], 422);
    }

    if (in_array('GLOBAL_SUPERADMIN', $requestedRoleCodes, true)) {
        return response()->json([
            'ok' => false,
            'message' => 'Global Superadmin cannot be set from this page.'
        ], 403);
    }

    $actorRole = strtoupper(trim((string) session('user_role')));
    if (in_array('SYSTEM_SUPERADMIN', $requestedRoleCodes, true) && $actorRole !== 'GLOBAL_SUPERADMIN') {
        return response()->json([
            'ok' => false,
            'message' => 'Only Global Superadmin can assign the System Superadmin role.'
        ], 403);
    }

    $allowedCodes = $this->allowedRoleCodesForAccounts();

    foreach ($requestedRoleCodes as $code) {
        if ($code === 'SYSTEM_SUPERADMIN') {
            continue;
        }

        if (!in_array($code, $allowedCodes, true)) {
            return response()->json([
                'ok' => false,
                'message' => "Invalid role selected: {$code}"
            ], 422);
        }
    }

    $primaryRole = $requestedRoleCodes[0];

    DB::table('users')->where($pk, $id)->update([
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'name'       => trim($data['first_name'].' '.$data['last_name']),
        'email'      => $data['email'],
        'role'       => $primaryRole,
        'status'     => $data['status'],
        'updated_at' => now(),
    ]);

    $this->saveUserRoles($id, $requestedRoleCodes);

    $this->logAccountEvent(
        'UPDATED',
        $id,
        'Updated account '.$data['email'].' (roles: '.implode(', ', $requestedRoleCodes).')'
    );

    return response()->json([
        'ok' => true,
        'user' => [
            'id'    => $id,
            'fn'    => $data['first_name'],
            'ln'    => $data['last_name'],
            'em'    => $data['email'],
            'rl'    => $primaryRole,
            'roles' => $requestedRoleCodes,
            'st'    => $data['status'],
        ]
    ]);
}

public function updateStatus(Request $request, $id)
{
    $id = (int) $id;

    $data = $request->validate([
        'status' => ['required', Rule::in(['Active','Inactive','Suspended'])],
    ]);

    if (strtolower($data['status']) === 'suspended') {
        if ($resp = $this->blockIfSystemTargetsGlobal($id, 'suspend')) return $resp;
    }

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    DB::table('users')->where($pk, $id)->update([
        'status' => $data['status'],
        'updated_at' => now(),
    ]);

    if (strtoupper($data['status']) === 'SUSPENDED') {
        AuditLog::record(
            'SECURITY',
            'SECURITY',
            'Suspended account ID '.$id,
            $id
        );
    } else {
        $this->logAccountEvent('UPDATED', $id, 'Updated account status to '.$data['status']);
    }

    return response()->json(['ok'=>true,'status'=>$data['status']]);
}
}

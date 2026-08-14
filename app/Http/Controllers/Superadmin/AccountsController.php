<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\NewAccountTempPasswordMail;
use App\Support\AuditLog;
use App\Support\Avatar;
use App\Services\Idp\IdpDirectoryService;

class AccountsController extends Controller
{
    public function index(
        Request $request,
        IdpDirectoryService $idpDirectoryService
    )
    {
        $roles = $this->fetchRolesForUi();

        if (!$roles->contains('code', 'FACULTY')) {
            $roles->push((object)[
                'code' => 'FACULTY',
                'name' => 'Faculty',
                'level' => 0,
            ]);
        }

        $acceptedCodes = $roles->pluck('code')->map(fn($c) => (string)$c)->all();

        if (in_array('FACULTY', $acceptedCodes, true)) $acceptedCodes[] = 'pupt:faculty';
        if (in_array('STUDENT', $acceptedCodes, true)) $acceptedCodes[] = 'pupt:student';

        $userSelect = [
            'users.user_id',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.role_id',
            'users.status',
            'users.last_login_at',
            'roles.code as role_code',
            'roles.name as role_name',
        ];
        if (Schema::hasColumn('users', 'profile_picture')) {
            $userSelect[] = 'users.profile_picture';
        }

        $rows = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select($userSelect)
            ->whereIn('roles.code', $acceptedCodes)
            ->orderBy('users.user_id', 'desc')
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

        $accessToken = (string) ($request->cookie('access_token') ?: session('access_token') ?: '');
        
        $idpDirectoryResponse = $idpDirectoryService->getActiveUsersForDropdown($accessToken);
        $idpDirectory = $idpDirectoryResponse['data'] ?? [];
        $isIdpCached = $idpDirectoryResponse['is_cached'] ?? false;
        $idpCacheTimestamp = $idpDirectoryResponse['cached_at'] ?? null;

        $combinedDirectory = collect($idpDirectory)
            ->filter(function ($person) {
                return trim((string) ($person['oneportal_id'] ?? '')) !== '' || trim((string) ($person['email'] ?? '')) !== '';
            })
            ->groupBy(function ($person) {
                return !empty($person['oneportal_id']) ? $person['oneportal_id'] : strtolower(trim((string) ($person['email'] ?? '')));
            })
            ->map(function ($group) {
                return collect($group)->first();
            })
            ->sortBy([
                ['first_name', 'asc'],
                ['last_name', 'asc'],
                ['middle_name', 'asc'],
            ])
            ->values()
            ->all();

        $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

        $mapped = $rows->map(function ($u) use ($rolesByUser, $combinedDirectory, $pk) {
            $userRoleRows = $rolesByUser->get((int) $u->user_id, collect());

            $roleCodes = $userRoleRows->pluck('role_code')
                ->map(fn($r) => (string) $r)
                ->values()
                ->all();

            if (empty($roleCodes) && !empty($u->role_code)) {
                $roleCodes = [(string) $u->role_code];
            }

            $userOneportalId = $u->oneportal_id ?? null;
            $userFirstName = strtolower(trim((string) $u->first_name));
            $userLastName = strtolower(trim((string) $u->last_name));
            $currentEmail = strtolower(trim((string) $u->email));

            $matchedPerson = null;

            if ($userOneportalId) {
                $matchedPerson = collect($combinedDirectory)->firstWhere('oneportal_id', $userOneportalId);
            }

            if (!$matchedPerson && $currentEmail !== '') {
                $matchedPerson = collect($combinedDirectory)->first(function ($person) use ($currentEmail) {
                    return strtolower(trim((string) ($person['email'] ?? ''))) === $currentEmail;
                });
            }

            if (!$matchedPerson && $userFirstName !== '' && $userLastName !== '') {
                $matchedPerson = collect($combinedDirectory)->first(function ($person) use ($userFirstName, $userLastName) {
                    return strtolower(trim((string) ($person['first_name'] ?? ''))) === $userFirstName
                        && strtolower(trim((string) ($person['last_name'] ?? ''))) === $userLastName;
                });
            }

            if ($matchedPerson) {
                $updates = [];
                
                if (!empty($matchedPerson['oneportal_id']) && $userOneportalId !== $matchedPerson['oneportal_id']) {
                    $updates['oneportal_id'] = $matchedPerson['oneportal_id'];
                    $u->oneportal_id = $matchedPerson['oneportal_id'];
                }

                if (!empty($matchedPerson['email'])) {
                    $dirEmail = strtolower(trim($matchedPerson['email']));
                    if ($dirEmail !== $currentEmail) {
                        $emailExists = DB::table('users')->where('email', $dirEmail)->where($pk, '!=', $u->user_id)->exists();
                        if (!$emailExists) {
                            $updates['email'] = $dirEmail;
                            $currentEmail = $dirEmail;
                            $u->email = $dirEmail;
                        }
                    }
                }
                
                if (!empty($updates)) {
                    DB::table('users')->where($pk, $u->user_id)->update($updates);
                }
            }

            $fullName = trim((string) $u->first_name . ' ' . (string) $u->last_name);
            if ($fullName === '') {
                $fullName = (string) ($u->email ?? 'User');
            }

            return [
                'id'    => (int) $u->user_id,
                'fn'    => (string) $u->first_name,
                'ln'    => (string) $u->last_name,
                'em'    => $currentEmail,
                'rl'    => (string) ($roleCodes[0] ?? $u->role_code ?? ''),
                'roles' => $roleCodes,
                'st'    => (string) $u->status,
                'll'    => $u->last_login_at ? (string) $u->last_login_at : 'Never',
                'av'    => 'av-0',
                'avatar_url' => Avatar::resolveUrl((string) ($u->profile_picture ?? '')),
                'avatar_initials' => Avatar::initials(
                    $fullName,
                    (string) $u->first_name,
                    (string) $u->last_name
                ),
            ];
        });

        return view('superadmin.accounts', [
            'usersJson' => $mapped->toJson(),
            'rolesJson' => $roles->toJson(),
            'facultyDirectoryJson' => json_encode($combinedDirectory),
            'isFacultyCached' => $isIdpCached,
            'facultyCacheTimestamp' => $idpCacheTimestamp,
            'isAdminCached' => false,
            'adminCacheTimestamp' => null,
        ]);
    }

private function fetchRolesForUi()
{
    return DB::table('roles')
        ->select('code', 'name', 'level')
        ->where('is_active', 1)
        ->where(function ($q) {
            $q->where('scope', 'CMS')
              ->orWhereIn('code', ['SUPERADMIN', 'ADMIN']);
        })
        ->orderByDesc('level')
        ->get();
}

private function defaultCmsRolesForFallback(): array
{
    return [
        'REGISTRAR' => 'Registrar',
        'HAP' => 'HAP',
        'STUDENT_SERVICES' => 'Student Services',
        'RESEARCH_EXTENSION' => 'Research and Extension',
        'FACULTY' => 'Faculty',
        'SUPERADMIN' => 'Superadmin',
        'ADMIN' => 'Admin',
    ];
}

private function denyIfNonSuperadminTargetsSuperadmin(string $action, string $targetRole): ?\Illuminate\Http\JsonResponse
{
    $actorRole = strtoupper(trim((string) session('user_role')));
    $targetRole = strtoupper(trim($targetRole));

    if ($actorRole !== 'SUPERADMIN' && $targetRole === 'SUPERADMIN') {
        $msg = match ($action) {
            'create' => 'You are not allowed to create a Superadmin account.',
            'edit'   => 'You are not allowed to edit a Superadmin account.',
            'suspend'=> 'You are not allowed to suspend a Superadmin account.',
            default  => 'You are not allowed to perform this action on a Superadmin account.',
        };

        return response()->json(['ok' => false, 'message' => $msg], 403);
    }

    return null;
}

private function normalizeRoleCode(string $raw): string
{
    $raw = trim($raw);

    if ($raw === '') {
        return '';
    }

    $lower = strtolower($raw);

    if ($lower === 'pupt:faculty') {
        return 'FACULTY';
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

private function validateTopLevelRoleMix(array $requestedRoleCodes): ?\Illuminate\Http\JsonResponse
{
    $hasSuperadmin = in_array('SUPERADMIN', $requestedRoleCodes, true);
    $hasAdmin = in_array('ADMIN', $requestedRoleCodes, true);

    $staffRoles = array_diff($requestedRoleCodes, ['SUPERADMIN', 'ADMIN']);
    $hasStaffRoles = !empty($staffRoles);

    if ($hasSuperadmin && count($requestedRoleCodes) > 1) {
        return response()->json([
            'ok' => false,
            'message' => 'Superadmin cannot be combined with staff roles.'
        ], 422);
    }

    if ($hasAdmin && count($requestedRoleCodes) > 1) {
        return response()->json([
            'ok' => false,
            'message' => 'Admin cannot be combined with staff roles.'
        ], 422);
    }

    if (($hasSuperadmin || $hasAdmin) && $hasStaffRoles) {
        return response()->json([
            'ok' => false,
            'message' => 'Admin or Superadmin accounts cannot be assigned staff roles.'
        ], 422);
    }

    return null;
}

private function actorIsSuperadmin(): bool
{
    return strtoupper(trim((string) session('user_role'))) === 'SUPERADMIN';
}

private function allowedRoleCodesForAccounts(): array
{
    $allowedCodes = DB::table('roles')
        ->where('is_active', 1)
        ->where(function ($q) {
            $q->where('scope', 'CMS')
              ->orWhere('code', 'SUPERADMIN');
        })
        ->pluck('code')
        ->map(fn($c) => (string) $c)
        ->all();

    if (in_array('FACULTY', $allowedCodes, true)) {
        $allowedCodes[] = 'pupt:faculty';
    }

    if (!$this->actorIsSuperadmin()) {
        $allowedCodes = array_values(array_filter($allowedCodes, fn ($code) => $code !== 'SUPERADMIN'));
    }

    return array_values(array_unique($allowedCodes));
}

private function saveUserRoles(int $userId, array $roleCodes): void
{
    if (!Schema::hasTable('user_roles')) {
        return;
    }

    DB::table('user_roles')->where('user_id', $userId)->delete();

    $hasAssignedBy = Schema::hasColumn('user_roles', 'assigned_by');
    $hasCreatedAt = Schema::hasColumn('user_roles', 'created_at');
    $hasUpdatedAt = Schema::hasColumn('user_roles', 'updated_at');

    $rows = [];
    foreach ($roleCodes as $i => $code) {
        $row = [
            'user_id'     => $userId,
            'role_code'   => $code,
            'is_primary'  => $i === 0 ? 1 : 0,
        ];

        if ($hasAssignedBy) {
            $row['assigned_by'] = session('user_id') ? (int) session('user_id') : null;
        }
        if ($hasCreatedAt) {
            $row['created_at'] = now();
        }
        if ($hasUpdatedAt) {
            $row['updated_at'] = now();
        }

        $rows[] = $row;
    }

    if (!empty($rows)) {
        DB::table('user_roles')->insert($rows);
    }
}

private function logAccountEvent(string $action, int $targetUserId, string $description): void
{
    AuditLog::record($action, 'ACCOUNTS', $description, $targetUserId);
}

private function usersColumnExists(string $column): bool
{
    return Schema::hasTable('users') && Schema::hasColumn('users', $column);
}

private function addUsersInsertTimestamps(array $payload): array
{
    if ($this->usersColumnExists('created_at')) {
        $payload['created_at'] = now();
    }

    if ($this->usersColumnExists('updated_at')) {
        $payload['updated_at'] = now();
    }

    return $payload;
}

private function addUsersUpdatedAt(array $payload): array
{
    if ($this->usersColumnExists('updated_at')) {
        $payload['updated_at'] = now();
    }

    return $payload;
}

private function filterUsersPayload(array $payload): array
{
    if (!Schema::hasTable('users')) {
        return $payload;
    }

    $columns = array_flip(Schema::getColumnListing('users'));

    return array_intersect_key($payload, $columns);
}

    public function store(Request $request)
{
    $validStatus = ['Active','Inactive','Suspended'];

    $data = $request->validate([
        'first_name' => ['required','string','max:80'],
        'last_name'  => ['required','string','max:80'],
        'email'      => ['required','email','max:190'],
        'status'     => ['required', Rule::in($validStatus)],
        'roles'      => ['nullable','array'],
        'roles.*'    => ['string','max:80'],
        'role'       => ['nullable','string','max:80'],
    ]);

    $normalizedEmail = strtolower(trim((string) $data['email']));

    $existingUser = DB::table('users')
        ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
        ->first();

    if ($existingUser) {
        return response()->json([
            'ok' => false,
            'message' => 'This user already has CMS access.'
        ], 422);
    }

    $requestedRoleCodes = $this->normalizeRoleCodesFromRequest($request);

    if (empty($requestedRoleCodes)) {
        return response()->json([
            'ok' => false,
            'message' => 'Please select at least one role.'
        ], 422);
    }

    if ($resp = $this->validateTopLevelRoleMix($requestedRoleCodes)) {
        return $resp;
    }

    if (in_array('SUPERADMIN', $requestedRoleCodes, true) && !$this->actorIsSuperadmin()) {
        return response()->json([
            'ok' => false,
            'message' => 'You are not allowed to create a Superadmin account.'
        ], 403);
    }

    $allowedCodes = $this->allowedRoleCodesForAccounts();

    foreach ($requestedRoleCodes as $code) {
        if (!in_array($code, $allowedCodes, true)) {
            return response()->json([
                'ok' => false,
                'message' => "Invalid role selected: {$code}"
            ], 422);
        }
    }

    $primaryRole = $requestedRoleCodes[0];
    $primaryRoleRow = DB::table('roles')->where('code', $primaryRole)->first();

    if (!$primaryRoleRow) {
        return response()->json([
            'ok' => false,
            'message' => "Role not found: {$primaryRole}"
        ], 422);
    }

    $name = trim($data['first_name'] . ' ' . $data['last_name']);
    $tempPassword = null;

    $insert = [
        'first_name'    => $data['first_name'],
        'last_name'     => $data['last_name'],
        'name'          => $name,
        'email'         => $normalizedEmail,
        'role_id'       => $primaryRoleRow->id,
        'status'        => $data['status'],
        'last_login_at' => null,
    ];

    if ($this->usersColumnExists('password')) {
        $tempPassword = Str::upper(Str::random(10));
        $insert['password'] = Hash::make($tempPassword);
    }

    $insert = $this->addUsersInsertTimestamps($insert);
    $insert = $this->filterUsersPayload($insert);

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
    $newUserId = DB::table('users')->insertGetId($insert, $pk);

    $this->saveUserRoles((int) $newUserId, $requestedRoleCodes);

    $this->logAccountEvent(
        'CREATED',
        (int) $newUserId,
        'Created account for '.$normalizedEmail.' with role '.$primaryRole
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
        if ($tempPassword !== null) {
            Mail::to($normalizedEmail)->queue(
                new NewAccountTempPasswordMail(
                    $name,
                    $normalizedEmail,
                    $roleLabel,
                    $tempPassword
                )
            );
            $emailSent = true;
        }
    } catch (\Throwable $e) {
        \Log::error('Temp password email failed: '.$e->getMessage(), ['email' => $normalizedEmail]);
    }

    return response()->json([
        'ok' => true,
        'user' => [
            'id'    => (int) $newUserId,
            'fn'    => $data['first_name'],
            'ln'    => $data['last_name'],
            'em'    => $normalizedEmail,
            'rl'    => $primaryRole,
            'roles' => $requestedRoleCodes,
            'st'    => $data['status'],
            'll'    => 'Never',
            'avatar_url' => '',
            'avatar_initials' => Avatar::initials(
                trim($data['first_name'].' '.$data['last_name']),
                $data['first_name'],
                $data['last_name']
            ),
        ]
    ]);
}

public function setStatus(Request $request, $id)
{
    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    $target = DB::table('users')
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
        ->select('users.'.$pk.' as id', 'roles.code as role_code')
        ->where('users.'.$pk, (int) $id)
        ->first();
    if (!$target) {
        return response()->json(['ok' => false, 'message' => 'User not found.'], 404);
    }

    $newStatus = (string) $request->input('status', '');
    $newStatusUpper = strtoupper(trim($newStatus));

    // Only block if they are trying to SUSPEND a SUPERADMIN
    if ($newStatusUpper === 'SUSPENDED') {
    if ($resp = $this->denyIfNonSuperadminTargetsSuperadmin('suspend', (string) ($target->role_code ?? ''))) {
        return $resp;
    }
}

    // validate allowed statuses
    $validStatus = ['Active','Inactive','Suspended'];
    $request->validate([
        'status' => ['required', Rule::in($validStatus)],
    ]);

    DB::table('users')->where($pk, (int)$id)->update($this->filterUsersPayload($this->addUsersUpdatedAt([
        'status' => $newStatus,
    ])));

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

private function blockIfNonSuperadminTargetsSuperadmin(int $targetId, string $action)
{
    $actorRole = strtoupper(trim((string) session('user_role')));
    if ($actorRole === 'SUPERADMIN') {
        return null;
    }

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    $target = DB::table('users')
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
        ->select('users.'.$pk.' as id', 'roles.code as role_code')
        ->where('users.'.$pk, $targetId)
        ->first();

    if (!$target) {
        return response()->json(['ok' => false, 'message' => 'User not found.'], 404);
    }

    if (strtoupper(trim((string) ($target->role_code ?? ''))) === 'SUPERADMIN') {
        $msg = $action === 'edit'
            ? 'You are not allowed to edit a Superadmin account.'
            : 'You are not allowed to suspend a Superadmin account.';

        return response()->json(['ok' => false, 'message' => $msg], 403);
    }

    return null;
}

public function update(Request $request, $id)
{
    $id = (int) $id;
if ($resp = $this->blockIfNonSuperadminTargetsSuperadmin($id, 'edit')) return $resp;

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

if ($resp = $this->validateTopLevelRoleMix($requestedRoleCodes)) {
    return $resp;
}

if (in_array('SUPERADMIN', $requestedRoleCodes, true) && !$this->actorIsSuperadmin()) {
    return response()->json([
        'ok' => false,
        'message' => 'You are not allowed to assign the Superadmin role.'
    ], 403);
}

$allowedCodes = $this->allowedRoleCodesForAccounts();

foreach ($requestedRoleCodes as $code) {
    if (!in_array($code, $allowedCodes, true)) {
        return response()->json([
            'ok' => false,
            'message' => "Invalid role selected: {$code}"
        ], 422);
    }
}

    $primaryRole = $requestedRoleCodes[0];
    $primaryRoleRow = DB::table('roles')->where('code', $primaryRole)->first();

    if (!$primaryRoleRow) {
        return response()->json([
            'ok' => false,
            'message' => "Role not found: {$primaryRole}"
        ], 422);
    }

    DB::table('users')->where($pk, $id)->update($this->filterUsersPayload($this->addUsersUpdatedAt([
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'name'       => trim($data['first_name'].' '.$data['last_name']),
        'email'      => $data['email'],
        'role_id'    => $primaryRoleRow->id,
        'status'     => $data['status'],
    ])));

    $this->saveUserRoles($id, $requestedRoleCodes);

    if ((int) session('user_id') === $id) {
        session([
            'user_role' => $primaryRole,
            'user_roles' => $requestedRoleCodes,
        ]);
    }

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
            'avatar_initials' => Avatar::initials(
                trim($data['first_name'].' '.$data['last_name']),
                $data['first_name'],
                $data['last_name']
            ),
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
    if ($resp = $this->blockIfNonSuperadminTargetsSuperadmin($id, 'suspend')) return $resp;
}

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

    DB::table('users')->where($pk, $id)->update($this->filterUsersPayload($this->addUsersUpdatedAt([
        'status' => $data['status'],
    ])));

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

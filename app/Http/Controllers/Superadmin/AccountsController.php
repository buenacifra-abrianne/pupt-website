<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AccountsController extends Controller
{
    public function index()
{
    $roles = DB::table('roles')
        ->select('code', 'name', 'level')
        ->where('is_active', 1)
        ->where(function ($q) {
            $q->where('scope', 'CMS')
              ->orWhereIn('code', ['GLOBAL_SUPERADMIN', 'SYSTEM_SUPERADMIN']);
        })
        ->orderByDesc('level')
        ->get();

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

    $mapped = $rows->map(function ($u) {
        return [
            'id' => (int) $u->user_id,
            'fn' => (string) $u->first_name,
            'ln' => (string) $u->last_name,
            'em' => (string) $u->email,
            'rl' => (string) $u->role,
            'st' => (string) $u->status,
            'll' => $u->last_login_at ? (string) $u->last_login_at : 'Never',
            'av' => 'av-0',
        ];
    });

    return view('superadmin.accounts', [
        'usersJson' => $mapped->toJson(),
        'rolesJson' => $roles->toJson(),
    ]);
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

    public function store(Request $request)
{
    $validStatus = ['Active','Inactive','Suspended'];

    $data = $request->validate([
        'first_name' => ['required','string','max:80'],
        'last_name'  => ['required','string','max:80'],
        'email'      => ['required','email','max:190', Rule::unique('users','email')],
        'role'       => ['required','string','max:80'],
        'status'     => ['required', Rule::in($validStatus)],
    ]);

    $creatorRole = strtoupper(trim((string) session('user_role')));
    $requestedRoleRaw = trim((string) $data['role']);

    // Normalize: "Global Superadmin" => GLOBAL_SUPERADMIN (optional safety)
    $requestedRoleCode = str_contains($requestedRoleRaw, ':')
        ? $requestedRoleRaw
        : strtoupper(preg_replace('/\s+/', '_', $requestedRoleRaw));

    // ✅ RULE: system_superadmin bawal gumawa ng global_superadmin
    if ($resp = $this->denyIfSystemTouchesGlobal('create', $requestedRoleCode)) {
    return $resp;
}

    // ✅ Never allow creating GLOBAL_SUPERADMIN at all (UI rule mo)
    if ($requestedRoleCode === 'GLOBAL_SUPERADMIN') {
        return response()->json([
            'ok' => false,
            'message' => 'Global Superadmin cannot be created from this page.'
        ], 403);
    }

    // ✅ Allowed roles (DB driven): scope CMS + active
    $allowedCodes = DB::table('roles')
    ->where('is_active', 1)
    ->where('scope', 'CMS')
    ->pluck('code')
    ->map(fn($c) => (string) $c)
    ->all();

// allow base equivalents for saving
if (in_array('FACULTY', $allowedCodes, true)) {
    $allowedCodes[] = 'pupt:faculty';
}

    // Optional: allow creating SYSTEM_SUPERADMIN only if creator is GLOBAL_SUPERADMIN
    // (remove this whole block if ayaw mo)
    if ($requestedRoleCode === 'SYSTEM_SUPERADMIN' && $creatorRole !== 'GLOBAL_SUPERADMIN') {
        return response()->json([
            'ok' => false,
            'message' => 'Only Global Superadmin can create a System Superadmin account.'
        ], 403);
    }

    if (!in_array($requestedRoleCode, $allowedCodes, true) && $requestedRoleCode !== 'SYSTEM_SUPERADMIN') {
        return response()->json([
            'ok' => false,
            'message' => 'Invalid role selected.'
        ], 422);
    }

    $name = trim($data['first_name'] . ' ' . $data['last_name']);

    $tempPassword = Str::random(10);

    $insert = [
        'first_name'    => $data['first_name'],
        'last_name'     => $data['last_name'],
        'name'          => $name,
        'email'         => $data['email'],
        'role'          => $requestedRoleCode,          // ✅ store CODE
        'status'        => $data['status'],
        'password'      => Hash::make($tempPassword),
        'last_login_at' => null,
        'created_at'    => now(),
        'updated_at'    => now(),
    ];

    $pk = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
    $newUserId = DB::table('users')->insertGetId($insert, $pk);

    return response()->json([
        'ok' => true,
        'user' => [
            'id' => (int) $newUserId,
            'fn' => $data['first_name'],
            'ln' => $data['last_name'],
            'em' => $data['email'],
            'rl' => $requestedRoleCode,  // ✅ return stored role
            'st' => $data['status'],
            'll' => 'Never',
        ],
        'temp_password' => $tempPassword,
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
        'role'       => ['required','string','max:80'],
        'status'     => ['required', Rule::in($validStatus)],
    ]);

    $requestedRoleRaw = trim((string) $data['role']);
    $requestedRoleCode = str_contains($requestedRoleRaw, ':')
        ? $requestedRoleRaw
        : strtoupper(preg_replace('/\s+/', '_', $requestedRoleRaw));

    // optional: never allow setting global superadmin here
    if ($requestedRoleCode === 'GLOBAL_SUPERADMIN') {
        return response()->json(['ok'=>false,'message'=>'Global Superadmin cannot be set from this page.'], 403);
    }

    DB::table('users')->where($pk, $id)->update([
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'name'       => trim($data['first_name'].' '.$data['last_name']),
        'email'      => $data['email'],
        'role'       => $requestedRoleCode,
        'status'     => $data['status'],
        'updated_at' => now(),
    ]);

    return response()->json([
        'ok' => true,
        'user' => [
            'id' => $id,
            'fn' => $data['first_name'],
            'ln' => $data['last_name'],
            'em' => $data['email'],
            'rl' => $requestedRoleCode,
            'st' => $data['status'],
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

    return response()->json(['ok'=>true,'status'=>$data['status']]);
}
}
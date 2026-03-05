<?php

namespace App\Http\Controllers\Faculty;

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
    $rows = DB::table('users')
        ->select('user_id','first_name','last_name','email','role','status','last_login_at')
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

    return view('faculty.accounts', [
        'usersJson' => $mapped->toJson(),
    ]);
}

    public function store(Request $request)
{
    $validRoles  = ['Admin','Registrar','HAP','Faculty','Student Services'];
    $validStatus = ['Active','Inactive','Suspended'];
    $name = trim($request->first_name . ' ' . $request->last_name);

    $data = $request->validate([
        'first_name' => ['required','string','max:80'],
        'last_name'  => ['required','string','max:80'],
        'email'      => ['required','email','max:190', Rule::unique('users','email')],
        'role'       => ['required', Rule::in($validRoles)],
        'status'     => ['required', Rule::in($validStatus)],
    ]);

    // ✅ create temp password (min 8 chars)
    $tempPassword = Str::random(10);

    $insert = [
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'email'      => $data['email'],
        'role'       => $data['role'],
        'status'     => $data['status'],
        'password'   => Hash::make($tempPassword),  // ✅ stop 500
        'last_login_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
        'name'          => $name,
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
            'rl' => $data['role'],
            'st' => $data['status'],
            'll' => 'Never',
        ],
        'temp_password' => $tempPassword, // so frontend can show it
    ]);
}
}
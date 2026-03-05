<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        // adjust table name/columns based sa users table mo
        $rows = DB::table('users')
            ->select([
                'user_id',        // if wala, palitan mo to with 'id'
                'first_name',
                'last_name',
                'name',
                'email',
                'role',
                'status',
                'last_login_at',
            ])
            ->orderByDesc('user_id') // if wala, palitan with 'id'
            ->get();

        // map to the fields your JS expects
        $users = $rows->map(function ($u) {
            $fn = $u->first_name ?? '';
            $ln = $u->last_name ?? '';
            $full = trim(($u->name ?? '') ?: ($fn . ' ' . $ln));

            return [
                'id' => (int)($u->user_id ?? 0),
                'fn' => $fn,
                'ln' => $ln,
                'em' => $u->email ?? '',
                'uid' => (string)($u->user_id ?? ''), // if may separate user_id/student_no column, dito ilagay
                'rl' => $u->role ?? 'Student',
                'dp' => $u->role ?? 'Student',        // sabi mo department = role
                'st' => 'Active',                      // wala pa status column, default muna
                'll' => '—',                            // wala pa last_login column, default muna
                'ph' => '',
                'pos' => '',
                'un' => '',
                'nt' => '',
                'av' => 'av-0',
            ];
        })->values();

        return view('faculty.accounts', [
            'usersJson' => $users->toJson(JSON_UNESCAPED_UNICODE),
        ]);
    }
}
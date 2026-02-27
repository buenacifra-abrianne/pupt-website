<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function show()
    {
        return view('faculty.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Match your current system: table 'admins', plain password check
        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('password', $request->password)
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['login' => 'Invalid email or password'])
                ->withInput();
        }

        session([
            'user_logged_in' => true,
            'user_id' => (int) ($user->user_id ?? 0),
            'user_email' => $user->email ?? '',
            'user_first_name' => $user->first_name ?? '',
            'user_last_name'  => $user->last_name ?? '',
            'user_role' => $user->role ?? '',
            'user_name' => $user->name ?? '',
        ]);

        if (($user->role ?? '') === 'ADMIN') {
            return redirect()->route('faculty.dashboard');   // admin side
        }

        return redirect()->route('staff.dashboard');        // non-admin side
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('faculty.login');
    }
}
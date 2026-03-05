<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

        $user = DB::table('users')
            ->where('email', $request->email)
            ->first();

        if (!$user || !$this->passwordMatches((string) $request->password, (string) ($user->password ?? ''))) {
            return back()
                ->withErrors(['login' => 'Invalid email or password'])
                ->withInput();
        }

        // Auto-upgrade legacy plain-text passwords to hashed passwords after successful login.
        $storedPassword = (string) ($user->password ?? '');
        if ($storedPassword !== '' && hash_equals($storedPassword, (string) $request->password)) {
            $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
            DB::table('users')
    ->where($idColumn, data_get($user, $idColumn))
    ->update([
        'last_login_at' => now(),
        'password'      => Hash::make((string) $request->password)
    ]);
        }

        session([
            'user_logged_in' => true,
            'user_id' => (int) ($user->user_id ?? $user->id ?? 0),
            'user_email' => $user->email ?? '',
            'user_first_name' => $user->first_name ?? '',
            'user_middle_name' => $user->middle_name ?? '',
            'user_last_name'  => $user->last_name ?? '',
            'user_role' => $user->role ?? '',
            'user_name' => $user->name ?? '',
            'user_profile_picture' => $user->profile_picture ?? '',
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

    public function updateProfile(Request $request)
    {
        $request->validateWithBag('profile', [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $userId = (int) session('user_id', 0);
        $userEmail = (string) session('user_email', '');

        if ($userId <= 0 && $userEmail === '') {
            return back()->withErrors(['current_password' => 'Session expired. Please login again.'], 'profile');
        }

        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $userQuery = DB::table('users');
        if ($userEmail !== '') {
            $userQuery->where('email', $userEmail);
        } else {
            $userQuery->where($idColumn, $userId);
        }
        $user = $userQuery->first();

        if (!$user) {
            return back()->withErrors(['current_password' => 'Account not found.'], 'profile');
        }

        if (!$this->passwordMatches((string) $request->input('current_password'), (string) ($user->password ?? ''))) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'], 'profile');
        }

        $updates = [
            'password' => Hash::make((string) $request->input('new_password')),
        ];

        if ($request->hasFile('profile_picture') && Schema::hasColumn('users', 'profile_picture')) {
            $storedPath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $updates['profile_picture'] = 'storage/' . $storedPath;
        }

        DB::table('users')->where($idColumn, data_get($user, $idColumn))->update($updates);

        if (!empty($updates['profile_picture'])) {
            session(['user_profile_picture' => $updates['profile_picture']]);
        }

        return back()->with('profile_success', 'Profile updated successfully.');
    }

    private function passwordMatches(string $input, string $stored): bool
    {
        if ($stored === '') {
            return false;
        }

        if (hash_equals($stored, $input)) {
            return true;
        }

        $passwordInfo = password_get_info($stored);
        if (($passwordInfo['algo'] ?? 0) !== 0) {
            return password_verify($input, $stored);
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\AuditLog;
use App\Support\ImageStorage;

class AuthController extends Controller
{
    public function show()
    {
        return view('superadmin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ]);

        $user = $this->findUserByEmail((string) $request->email);

        if (!$user) {
            AuditLog::record(
                'SECURITY',
                'SECURITY',
                'Failed login attempt for email: ' . (string) $request->email,
                null,
                ['user_name' => 'Unknown']
            );
            \App\Services\IncidentResponseService::recordFailure($request->ip(), (string) $request->email, 'FAILED_LOGIN', 'Failed superadmin login: unknown user');
            return back()
                ->withErrors(['login' => 'Invalid login attempt'])
                ->withInput();
        }



        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';

        $updates = [
            'last_login_at' => now(),
        ];

        DB::table('users')
            ->where($idColumn, data_get($user, $idColumn))
            ->update($updates);

        $userId = (int) ($user->user_id ?? $user->id ?? 0);
        session(['mfa_pending_user_id' => $userId]);



        if (empty($user->mfa_secret)) {
            return redirect()->route('superadmin.mfa.setup');
        }

        return redirect()->route('superadmin.mfa.challenge');
    }

    public function completeLogin($user)
    {
        $dbRole = $this->normalizeDbRole((string) ($user->role_code ?? $user->role ?? ''));
        $userId = (int) ($user->user_id ?? $user->id ?? 0);

        $assignedRoles = [];
        if (Schema::hasTable('user_roles') && $userId > 0) {
            $assignedRoles = DB::table('user_roles')
                ->where('user_id', $userId)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->pluck('role_code')
                ->map(function ($role) {
                    return strtoupper(trim((string) $role));
                })
                ->values()
                ->all();
        }

        if (empty($assignedRoles) && !empty($dbRole)) {
            $assignedRoles = [$dbRole];
        }

        session([
            'user_logged_in' => true,
            'user_id' => $userId,
            'user_email' => $user->email ?? '',
            'user_first_name' => $user->first_name ?? '',
            'user_middle_name' => $user->middle_name ?? '',
            'user_last_name'  => $user->last_name ?? '',
            'user_suffix' => $user->suffix ?? '',
            'user_role' => $dbRole ?? '',
            'user_roles' => $assignedRoles,
            'user_name' => $user->name ?? '',
            'user_profile_picture' => $user->profile_picture ?? '',
            'terms_accepted' => false,
        ]);
        session()->forget('mfa_pending_user_id');

        AuditLog::record(
            'LOGIN',
            'AUTHENTICATION',
            'User logged in successfully: ' . (string) ($user->email ?? ''),
            (int) ($user->user_id ?? $user->id ?? 0),
            [
                'user_id' => (int) ($user->user_id ?? $user->id ?? 0),
                'user_name' => trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? '')),
            ]
        );

        if ($dbRole === 'SUPERADMIN') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($dbRole === 'ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        if (
            $dbRole === 'REGISTRAR' ||
            $dbRole === 'HAP' ||
            $dbRole === 'STUDENT_SERVICES' ||
            $dbRole === 'RESEARCH_EXTENSION' ||
            $dbRole === 'FACULTY' ||
            $dbRole === 'FACULTY_PRO' ||
            $dbRole === 'DENTAL' ||
            $dbRole === 'GUIDANCE' ||
            $dbRole === 'CLINIC' ||
            $dbRole === 'ACCREDITATION' ||
            $dbRole === 'ADMISSIONS' ||
            $dbRole === 'LIBRARY' ||
            $dbRole === 'OJT' ||
            $dbRole === 'CWTS' ||
            $dbRole === 'DIRECTOR_OFFICE' ||
            $dbRole === 'ADMINISTRATION'
        ) {
            return redirect()->route('staff.dashboard');
        }

        session()->flush();

        return redirect()->route('superadmin.login')->withErrors([
            'login' => 'Unauthorized role: ' . $dbRole
        ])->withInput();
    }

    public function logout(Request $request)
    {
        AuditLog::record(
            'LOGOUT',
            'AUTHENTICATION',
            'User logged out.',
            (int) session('user_id', 0)
        );

        // clear local session
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('public.landing')
        ->withoutCookie('access_token')
        ->withoutCookie('refresh_token');
        
    }

    public function updateProfile(Request $request)
    {
        $request->validateWithBag('profileInfo', [
            'first_name' => ['nullable', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:30'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'avatar_image_data' => ['nullable', 'string'],
            'reset_avatar' => ['nullable', 'boolean'],
        ]);

        [$user, $idColumn] = $this->resolveSessionUser();

        if (!$user) {
            return back()->withErrors(['profile' => 'Account not found or session expired.'], 'profileInfo');
        }

        $updates = [];
        $incomingFirstName = $this->normalizeProfileValue((string) $request->input('first_name', ''));
        $incomingMiddleName = $this->normalizeProfileValue((string) $request->input('middle_name', ''));
        $incomingLastName = $this->normalizeProfileValue((string) $request->input('last_name', ''));
        $incomingSuffix = $this->normalizeProfileValue((string) $request->input('suffix', ''));

        $currentFirstName = $this->normalizeProfileValue((string) data_get($user, 'first_name', ''));
        $currentMiddleName = $this->normalizeProfileValue((string) data_get($user, 'middle_name', ''));
        $currentLastName = $this->normalizeProfileValue((string) data_get($user, 'last_name', ''));
        $currentSuffix = $this->normalizeProfileValue((string) data_get($user, 'suffix', ''));

        if (Schema::hasColumn('users', 'first_name')) {
            if ($incomingFirstName !== $currentFirstName) {
                $updates['first_name'] = $incomingFirstName;
            }
        }
        if (Schema::hasColumn('users', 'middle_name')) {
            if ($incomingMiddleName !== $currentMiddleName) {
                $updates['middle_name'] = $incomingMiddleName;
            }
        }
        if (Schema::hasColumn('users', 'last_name')) {
            if ($incomingLastName !== $currentLastName) {
                $updates['last_name'] = $incomingLastName;
            }
        }
        if (Schema::hasColumn('users', 'suffix')) {
            if ($incomingSuffix !== $currentSuffix) {
                $updates['suffix'] = $incomingSuffix;
            }
        }

        if (Schema::hasColumn('users', 'name')) {
            $nameFirst = array_key_exists('first_name', $updates) ? $updates['first_name'] : $currentFirstName;
            $nameMiddle = array_key_exists('middle_name', $updates) ? $updates['middle_name'] : $currentMiddleName;
            $nameLast = array_key_exists('last_name', $updates) ? $updates['last_name'] : $currentLastName;
            // Keep suffix visible in full name even if `suffix` column is unavailable in some environments.
            $nameSuffix = Schema::hasColumn('users', 'suffix')
                ? (array_key_exists('suffix', $updates) ? $updates['suffix'] : $currentSuffix)
                : $incomingSuffix;

            $fullName = trim(implode(' ', array_filter([$nameFirst, $nameMiddle, $nameLast, $nameSuffix], fn ($part) => trim((string) $part) !== '')));
            $normalizedCurrentName = $this->normalizeProfileValue((string) data_get($user, 'name', ''));
            $normalizedNextName = $this->normalizeProfileValue($fullName);

            if ($normalizedNextName !== $normalizedCurrentName) {
                $updates['name'] = $normalizedNextName;
            }
        }

        $avatarImageData = (string) $request->input('avatar_image_data', '');
        $shouldResetAvatar = $request->boolean('reset_avatar');
        $oldProfilePicture = (string) data_get($user, 'profile_picture', '');

        if ($shouldResetAvatar && Schema::hasColumn('users', 'profile_picture')) {
            $updates['profile_picture'] = null;
        }

        if (!$shouldResetAvatar && $avatarImageData !== '') {
            $storedAvatarPath = $this->storeAvatarFromDataUrl($avatarImageData);
            if ($storedAvatarPath === null) {
                return back()->withErrors(['profile_picture' => 'Unable to process avatar image. Please try another image.'], 'profileInfo');
            }
            if (Schema::hasColumn('users', 'profile_picture')) {
                $updates['profile_picture'] = $storedAvatarPath;
            }
        }

        if (!$shouldResetAvatar && !isset($updates['profile_picture']) && $request->hasFile('profile_picture') && Schema::hasColumn('users', 'profile_picture')) {
            $storedPath = ImageStorage::store($request->file('profile_picture'), 'profile_pictures');
            if ($storedPath === false) {
                return back()->withErrors(['profile_picture' => 'Unable to upload profile picture. Please try again.'], 'profileInfo');
            }

            $updates['profile_picture'] = $storedPath;
        }

        if ($updates === []) {
            return back()->with('profile_info_notice', 'No profile changes to save.');
        }

        DB::table('users')->where($idColumn, data_get($user, $idColumn))->update($updates);

        if (array_key_exists('profile_picture', $updates)) {
            $newProfilePicture = (string) ($updates['profile_picture'] ?? '');
            if ($newProfilePicture !== $oldProfilePicture) {
                ImageStorage::delete($oldProfilePicture);
            }
        }

        if (array_key_exists('first_name', $updates)) {
            session(['user_first_name' => $updates['first_name'] ?? '']);
        }
        if (array_key_exists('middle_name', $updates)) {
            session(['user_middle_name' => $updates['middle_name'] ?? '']);
        }
        if (array_key_exists('last_name', $updates)) {
            session(['user_last_name' => $updates['last_name'] ?? '']);
        }
        if (array_key_exists('suffix', $updates)) {
            session(['user_suffix' => $updates['suffix'] ?? '']);
        } elseif (!Schema::hasColumn('users', 'suffix')) {
            session(['user_suffix' => $incomingSuffix ?? '']);
        }
        if (array_key_exists('name', $updates)) {
            session(['user_name' => $updates['name'] ?? '']);
        }
        if (array_key_exists('profile_picture', $updates)) {
            session(['user_profile_picture' => (string) ($updates['profile_picture'] ?? '')]);
        }

        AuditLog::record(
            'UPDATED',
            'ACCOUNT',
            'User updated profile information.',
            (int) data_get($user, $idColumn)
        );

        return back()->with('profile_info_success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validateWithBag('profilePassword', [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        [$user, $idColumn] = $this->resolveSessionUser();

        if (!$user) {
            return back()->withErrors(['current_password' => 'Session expired. Please login again.'], 'profilePassword');
        }

        if (!$this->passwordMatches((string) $request->input('current_password'), (string) ($user->password ?? ''))) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'], 'profilePassword');
        }

        DB::table('users')
            ->where($idColumn, data_get($user, $idColumn))
            ->update([
                'password' => Hash::make((string) $request->input('new_password')),
            ]);

        AuditLog::record(
            'UPDATED',
            'ACCOUNT',
            'User updated account password.',
            (int) data_get($user, $idColumn)
        );

        return back()->with('profile_password_success', 'Password changed successfully.');
    }


    private function resolveSessionUser(): array
    {
        $userId = (int) session('user_id', 0);
        $userEmail = (string) session('user_email', '');

        if ($userId <= 0 && $userEmail === '') {
            return [null, Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id'];
        }

        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $query = DB::table('users');

        if ($userEmail !== '') {
            $query->where('email', $userEmail);
        } else {
            $query->where($idColumn, $userId);
        }

        return [$query->first(), $idColumn];
    }

    private function normalizeProfileValue(string $value): ?string
    {
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function storeAvatarFromDataUrl(string $dataUrl): ?string
    {
        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,/', $dataUrl, $matches)) {
            return null;
        }

        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($binary === false || strlen($binary) === 0 || strlen($binary) > (6 * 1024 * 1024)) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($binary);
        if ($imageInfo === false) {
            return null;
        }

        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));
        if (!in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return null;
        }

        $extension = strtolower((string) $matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $filePath = 'profile_pictures/' . now()->format('Ymd_His') . '_' . Str::random(10) . '.' . $extension;
        if (!ImageStorage::put($filePath, $binary)) {
            return null;
        }

        return $filePath;
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

        public function callback(Request $request)
{
    $token = $request->query('token') ?? $request->input('token');

    \Log::info('IDP callback received', [
        'query' => $request->query(),
        'input' => $request->all(),
        'ip' => $request->ip(),
    ]);

    if (!$token) {
        return redirect('/')
            ->with('error', 'No authentication token received.');
    }

    try {
        /**
         * STEP 1:
         * Decode / validate the token here based on your IDP spec.
         * You need to extract at least the user's email.
         *
         * Example only:
         * $payload = YourIdpService::decodeToken($token);
         * $email = strtolower(trim((string) ($payload['email'] ?? '')));
         */

        $payload = $this->decodeIdpToken($token); // create this helper/service
        $email = strtolower(trim((string) ($payload['email'] ?? '')));

        if ($email === '') {
            $this->clearAuthState($request);

            return redirect('/')
                ->with('error', 'Unable to identify account from identity provider.');
        }

        $user = $this->findUserByEmail($email, true);

        if (!$user) {
            $this->clearAuthState($request);

            return redirect('/')
                ->with('error', 'You have no role in this system. Please check with the superadmin.');
        }

        $dbRole = $this->normalizeDbRole((string) ($user->role_code ?? $user->role ?? ''));

        $userId = (int) ($user->user_id ?? $user->id ?? 0);

        $assignedRoles = [];
        if (Schema::hasTable('user_roles') && $userId > 0) {
            $assignedRoles = DB::table('user_roles')
                ->where('user_id', $userId)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->pluck('role_code')
                ->map(function ($role) {
                    return strtoupper(trim((string) $role));
                })
                ->filter()
                ->values()
                ->all();
        }

        if (empty($assignedRoles) && !empty($dbRole)) {
            $assignedRoles = [$dbRole];
        }

        $allowedRoles = [
            'SUPERADMIN',
            'ADMIN',
            'REGISTRAR',
            'HAP',
            'STUDENT_SERVICES',
            'RESEARCH_EXTENSION',
            'FACULTY',
            'FACULTY_PRO',
            'DENTAL',
            'GUIDANCE',
            'CLINIC',
            'ACCREDITATION',
            'ADMISSIONS',
            'LIBRARY',
            'OJT',
            'CWTS',
            'DIRECTOR_OFFICE',
            'ADMINISTRATION'
        ];

        $validRoles = array_values(array_intersect($assignedRoles, $allowedRoles));

        if (empty($validRoles)) {
            $this->clearAuthState($request);

            return redirect('/')
                ->with('error', 'You have no role in this system. Please check with the superadmin.');
        }

        DB::table('users')
            ->where(Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id', $userId)
            ->update([
                'last_login_at' => now(),
            ]);

        session([
            'user_logged_in' => true,
            'user_id' => $userId,
            'user_email' => $user->email ?? '',
            'user_first_name' => $user->first_name ?? '',
            'user_middle_name' => $user->middle_name ?? '',
            'user_last_name' => $user->last_name ?? '',
            'user_suffix' => $user->suffix ?? '',
            'user_role' => $validRoles[0],
            'user_roles' => $validRoles,
            'user_name' => $user->name ?? '',
            'user_profile_picture' => $user->profile_picture ?? '',
            'terms_accepted' => false,
        ]);

        AuditLog::record(
            'LOGIN',
            'AUTHENTICATION',
            'User logged in successfully via IDP: ' . (string) ($user->email ?? ''),
            $userId,
            [
                'user_id' => $userId,
                'user_name' => trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? '')),
            ]
        );

        $primaryRole = $validRoles[0];

        if ($primaryRole === 'SUPERADMIN') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($primaryRole === 'ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('staff.dashboard');

    } catch (\Throwable $e) {
        \Log::error('IDP callback failed', [
            'message' => $e->getMessage(),
        ]);

        $this->clearAuthState($request);

        return redirect('/')
            ->with('error', 'Authentication failed. Please try again.');
    }
}

private function clearAuthState(Request $request): void
{
    $request->session()->forget([
        'user_logged_in',
        'user_id',
        'user_email',
        'user_first_name',
        'user_middle_name',
        'user_last_name',
        'user_suffix',
        'user_role',
        'user_roles',
        'user_name',
        'user_profile_picture',
        'terms_accepted',
        'idp_token',
        'access_token',
        'refresh_token',
    ]);

    $request->session()->invalidate();
    $request->session()->regenerateToken();
}

private function findUserByEmail(string $email, bool $caseInsensitive = false): ?object
{
    $query = DB::table('users')->select('users.*');

    if (Schema::hasTable('roles') && Schema::hasColumn('users', 'role_id')) {
        $query->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->addSelect(
                'roles.code as role_code',
                'roles.name as role_name',
                'roles.level as role_level'
            );
    } elseif (Schema::hasColumn('users', 'role')) {
        $query->addSelect(
            DB::raw('users.role as role_code'),
            DB::raw('users.role as role_name'),
            DB::raw('null as role_level')
        );
    }

    if ($caseInsensitive) {
        $query->whereRaw('LOWER(users.email) = ?', [strtolower(trim($email))]);
    } else {
        $query->where('users.email', $email);
    }

    return $query->first();
}

private function normalizeDbRole(string $role): string
{
    $normalizedRole = strtoupper(trim($role));

    return preg_replace('/\s+/', '_', $normalizedRole) ?? $normalizedRole;
}
}

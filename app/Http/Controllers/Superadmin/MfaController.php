<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Support\AuditLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MfaController extends Controller
{
    public function setup(Request $request)
    {
        $userId = session('mfa_pending_user_id');
        if (!$userId) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'Session expired.']);
        }

        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $user = DB::table('users')->where($idColumn, $userId)->first();

        if (!$user) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'User not found.']);
        }

        if (!empty($user->mfa_secret)) {
            return redirect()->route('superadmin.mfa.challenge');
        }

        $google2fa = app(Google2FA::class);
        $secret = $google2fa->generateSecretKey();
        
        session(['mfa_setup_secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(256),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        $backupCodes = collect(range(1, 8))->map(fn() => strtoupper(Str::random(8)))->toArray();
        session(['mfa_setup_backup_codes' => $backupCodes]);

        return view('superadmin.mfa_setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
            'backupCodes' => $backupCodes
        ]);
    }

    public function challenge(Request $request)
    {
        $userId = session('mfa_pending_user_id');
        if (!$userId) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'Session expired.']);
        }

        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $user = DB::table('users')->where($idColumn, $userId)->first();

        if (!$user || empty($user->mfa_secret)) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'Invalid MFA state.']);
        }

        return view('superadmin.mfa_challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'nullable|string',
            'backup_code' => 'nullable|string',
            'is_setup' => 'nullable|boolean'
        ]);

        $userId = session('mfa_pending_user_id');
        if (!$userId) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'Session expired.']);
        }

        $idColumn = Schema::hasColumn('users', 'user_id') ? 'user_id' : 'id';
        $user = DB::table('users')->where($idColumn, $userId)->first();

        if (!$user) {
            return redirect()->route('superadmin.login')->withErrors(['login' => 'User not found.']);
        }

        $isSetup = $request->boolean('is_setup');

        if ($request->filled('backup_code') && !$isSetup) {
            $storedCodes = json_decode($user->backup_codes ?? '[]', true) ?: [];
            $inputCode = strtoupper(trim((string)$request->backup_code));
            
            $validCodeIndex = -1;
            foreach ($storedCodes as $index => $hashedCode) {
                if (Hash::check($inputCode, $hashedCode)) {
                    $validCodeIndex = $index;
                    break;
                }
            }

            if ($validCodeIndex !== -1) {
                unset($storedCodes[$validCodeIndex]);
                DB::table('users')->where($idColumn, $userId)->update([
                    'backup_codes' => json_encode(array_values($storedCodes))
                ]);
                
                AuditLog::record('SECURITY', 'ACCOUNT', 'User logged in using a backup code.', $userId);
                
                $authController = app(AuthController::class);
                return $authController->completeLogin($user);
            }

            return back()->withErrors(['backup_code' => 'Invalid backup code.']);
        }

        $secret = $isSetup ? session('mfa_setup_secret') : $user->mfa_secret;

        if (!$secret) {
            return back()->withErrors(['one_time_password' => 'MFA secret missing.']);
        }

        if (!$request->filled('one_time_password')) {
            return back()->withErrors(['one_time_password' => 'The authentication code is required.']);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        if ($valid) {
            if ($isSetup) {
                $rawBackupCodes = session('mfa_setup_backup_codes', []);
                $hashedBackupCodes = array_map(fn($code) => Hash::make($code), $rawBackupCodes);

                DB::table('users')->where($idColumn, $userId)->update([
                    'mfa_secret' => $secret,
                    'backup_codes' => json_encode($hashedBackupCodes)
                ]);
                session()->forget(['mfa_setup_secret', 'mfa_setup_backup_codes']);
                
                AuditLog::record(
                    'SECURITY',
                    'ACCOUNT',
                    'User set up MFA successfully.',
                    $userId
                );
            }

            $authController = app(AuthController::class);
            return $authController->completeLogin($user);
        }

        return back()->withErrors(['one_time_password' => 'Invalid code.']);
    }
}

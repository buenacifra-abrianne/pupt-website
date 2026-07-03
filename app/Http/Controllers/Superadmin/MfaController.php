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

        return view('superadmin.mfa_setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret
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
            'one_time_password' => 'required|string',
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
        $secret = $isSetup ? session('mfa_setup_secret') : $user->mfa_secret;

        if (!$secret) {
            return back()->withErrors(['one_time_password' => 'MFA secret missing.']);
        }

        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($secret, $request->one_time_password);

        if ($valid) {
            if ($isSetup) {
                DB::table('users')->where($idColumn, $userId)->update([
                    'mfa_secret' => $secret
                ]);
                session()->forget('mfa_setup_secret');
                
                AuditLog::record(
                    'SECURITY',
                    'ACCOUNT',
                    'User set up MFA successfully.',
                    $userId
                );
            }

            // Call the completeLogin method from AuthController to finalize the session
            $authController = app(AuthController::class);
            return $authController->completeLogin($user);
        }

        return back()->withErrors(['one_time_password' => 'Invalid code.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Support\CmsSections;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class CmsTermsController extends Controller
{
    public function accept(Request $request): RedirectResponse
    {
        $request->validate([
            'agree_terms' => ['accepted'],
        ]);

        session([
            'terms_accepted' => true,
            'terms_accepted_at' => now()->toDateTimeString(),
        ]);

        return redirect()->to($this->defaultCmsRoute());
    }

    public function blocked(): View|RedirectResponse
    {
        if ((bool) session('terms_accepted', false)) {
            return redirect()->to($this->defaultCmsRoute());
        }

        return view('cms.terms-blocked');
    }

    private function defaultCmsRoute(): string
    {
        $role = CmsSections::normalizeRole((string) session('user_role'));

        if ($role === 'SUPERADMIN') {
            return route('superadmin.dashboard');
        }

        if ($role === 'ADMIN') {
            return route('admin.dashboard');
        }

        $rawRoles = session('user_roles', [session('user_role')]);
        $roles = is_array($rawRoles) ? $rawRoles : [(string) $rawRoles];

        if (!empty(CmsSections::tabsForRoles($roles))) {
            return route('staff.dashboard');
        }

        return route('superadmin.login');
    }
}

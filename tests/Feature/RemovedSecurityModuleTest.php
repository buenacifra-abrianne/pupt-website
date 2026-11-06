<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RemovedSecurityModuleTest extends TestCase
{
    public function test_former_security_endpoints_are_not_accessible(): void
    {
        foreach ([false, true] as $loggedIn) {
            $this->withSession([
                'user_logged_in' => $loggedIn,
                'user_role' => 'SUPERADMIN',
                'user_roles' => ['SUPERADMIN'],
                'terms_accepted' => true,
            ]);

            $this->get('/superadmin/security/incidents')->assertNotFound();
            $this->post('/superadmin/security/block-ip', ['ip_address' => '192.0.2.1'])->assertNotFound();
            $this->delete('/superadmin/security/unblock-ip/1')->assertNotFound();
            $this->post('/superadmin/security/suspend/1')->assertNotFound();
            $this->post('/superadmin/security/unsuspend/1')->assertNotFound();
        }

        foreach (Route::getRoutes() as $route) {
            $this->assertFalse(str_starts_with((string) $route->getName(), 'superadmin.security.'));
        }
    }

    public function test_superadmin_sidebar_keeps_other_navigation_without_security_module(): void
    {
        $this->app->instance('request', Request::create('/superadmin/dashboard'));
        $this->withSession(['user_role' => 'SUPERADMIN', 'user_roles' => ['SUPERADMIN']]);

        $sidebar = view('components.app.sidebar')->render();

        $this->assertStringContainsString('Manage CMS Access', $sidebar);
        $this->assertStringContainsString('Audit Trails', $sidebar);
        $this->assertStringNotContainsString('Security &amp; Incidents', $sidebar);
        $this->assertStringNotContainsString('Security & Incidents', $sidebar);
        $this->assertStringNotContainsString('/superadmin/security/', $sidebar);
    }
}

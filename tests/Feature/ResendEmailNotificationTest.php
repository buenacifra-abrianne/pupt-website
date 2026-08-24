<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ApprovalRequest;
use App\Services\ResendEmailService;
use Mockery\MockInterface;

class ResendEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware();

        // Ensure roles and statuses exist for testing
        // Creating active and inactive admins and superadmins
        User::factory()->create(['email' => 'active_admin@test.com', 'role' => 'Admin', 'status' => 'Active']);
        User::factory()->create(['email' => 'inactive_admin@test.com', 'role' => 'Admin', 'status' => 'Inactive']);
        User::factory()->create(['email' => 'active_superadmin@test.com', 'role' => 'Superadmin', 'status' => 'Active']);
        User::factory()->create(['email' => 'inactive_superadmin@test.com', 'role' => 'Superadmin', 'status' => 'Inactive']);
        User::factory()->create(['email' => 'staff1@test.com', 'role' => 'Staff', 'status' => 'Active']);
        User::factory()->create(['email' => 'staff2@test.com', 'role' => 'Staff', 'status' => 'Inactive']);
    }

    public function test_pending_request_notifies_active_admins_only()
    {
        $mock = $this->mock(ResendEmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendPendingApprovalNotification')
                 ->once()
                 ->withArgs(function ($emails, $data) {
                     return in_array('active_admin@test.com', $emails)
                         && in_array('active_superadmin@test.com', $emails)
                         && !in_array('inactive_admin@test.com', $emails)
                         && !in_array('inactive_superadmin@test.com', $emails)
                         && !in_array('staff1@test.com', $emails);
                 });
        });

        // Force Laravel to use the mocked service in the controller
        $this->app->instance(ResendEmailService::class, $mock);

        // We can test this by calling the Staff controller's request creation endpoint
        $response = $this->withSession([
            'user_email' => 'staff1@test.com',
            'user_first_name' => 'Staff',
            'user_last_name' => 'One',
            'user_id' => 999
        ])->postJson('/staff/announcements/request-create', [
            'title' => 'Test Announcement',
            'content' => 'Test Content',
            'priority' => 'LOW',
        ]); // Adjust route name if needed.

        // Assuming it's successful, the mock expectation will be asserted
        $response->assertStatus(200);
    }

    public function test_approved_request_notifies_active_staff_requester()
    {
        $mock = $this->mock(ResendEmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendApprovalResultNotification')
                 ->once()
                 ->withArgs(function ($email, $details, $status) {
                     return $email === 'staff1@test.com' && $status === 'approved';
                 });
        });

        $this->app->instance(ResendEmailService::class, $mock);

        $request = ApprovalRequest::create([
            'type' => 'ANNOUNCEMENT_CREATE',
            'title' => 'Test Title',
            'details' => json_encode(['title' => 'Test Title', 'content' => 'Test Content', 'priority' => 'LOW']),
            'status' => 'pending',
            'requester_email' => 'staff1@test.com',
            'requester_name' => 'Staff One'
        ]);

        $response = $this->withSession([
            'user_email' => 'active_admin@test.com',
            'user_first_name' => 'Admin',
            'user_last_name' => 'User',
            'user_id' => 1
        ])->postJson("/superadmin/approvals/{$request->id}/approve");

        $response->assertStatus(200);
    }
}

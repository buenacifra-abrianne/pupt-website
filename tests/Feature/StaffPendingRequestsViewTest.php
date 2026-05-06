<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class StaffPendingRequestsViewTest extends TestCase
{
    public function test_staff_pending_tab_renders_pending_announcement_and_event_requests(): void
    {
        $requests = new Collection([
            (object) [
                'id' => 101,
                'type' => 'ANNOUNCEMENT_CREATE',
                'title' => 'Pending Campus Advisory',
                'details' => json_encode([
                    'title' => 'Pending Campus Advisory',
                    'content' => '<p>Advisory details.</p>',
                    'priority' => 'MEDIUM',
                    'link' => '',
                ]),
                'status' => 'pending',
                'rejection_reason' => null,
            ],
            (object) [
                'id' => 102,
                'type' => 'NEWS_CREATE',
                'title' => 'Pending Foundation Day',
                'details' => json_encode([
                    'title' => 'Pending Foundation Day',
                    'content' => '<p>Event details.</p>',
                    'category' => 'Event',
                    'location' => 'Campus Gym',
                    'link' => '',
                    'image_path' => null,
                ]),
                'status' => 'pending',
                'rejection_reason' => null,
            ],
        ]);

        $html = view('staff.announcements', [
            'myRequests' => $requests,
            'myAnnouncements' => collect(),
            'myNews' => collect(),
            'email' => 'staff@example.test',
            'name' => 'Staff User',
        ])->render();

        $this->assertStringContainsString('My Pending Requests', $html);
        $this->assertStringContainsString('Pending Campus Advisory', $html);
        $this->assertStringContainsString('Pending Foundation Day', $html);
        $this->assertStringContainsString('Announcement Requests', $html);
        $this->assertStringContainsString('News Requests', $html);
        $this->assertStringContainsString('Event', $html);
    }

    public function test_staff_news_list_renders_own_pending_created_article_with_status(): void
    {
        $requests = new Collection([
            (object) [
                'id' => 201,
                'type' => 'NEWS_CREATE',
                'title' => 'Pending Student Services Article',
                'details' => json_encode([
                    'title' => 'Pending Student Services Article',
                    'content' => '<p>Submitted article details.</p>',
                    'category' => 'Event',
                    'location' => 'Student Center',
                    'link' => '',
                    'image_path' => null,
                ]),
                'status' => 'pending',
                'rejection_reason' => null,
            ],
        ]);

        $html = view('staff.announcements', [
            'myRequests' => $requests,
            'myAnnouncements' => collect(),
            'myNews' => collect(),
            'email' => 'studentservices@example.test',
            'name' => 'Student Services',
        ])->render();

        $this->assertStringContainsString('newsRequestsList', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'Pending Student Services Article'));
        $this->assertStringContainsString('Pending Approval', $html);
        $this->assertStringContainsString('View Changes', $html);
    }

    public function test_staff_can_fetch_own_pending_change_details_with_changed_fields(): void
    {
        $this->prepareApprovalTables();

        DB::table('news')->insert([
            'news_id' => 44,
            'title' => 'Original Title',
            'content' => '<p>Original body.</p>',
            'category' => 'Campus',
            'location' => 'Old Venue',
            'link' => '',
            'image_path' => 'news/original.jpg',
            'status' => 'APPROVED',
            'created_at' => now(),
        ]);

        $requestId = DB::table('approval_requests')->insertGetId([
            'title' => 'Updated Title',
            'type' => 'NEWS_UPDATE',
            'status' => 'pending',
            'requester_name' => 'Student Services',
            'requester_email' => 'studentservices@example.test',
            'details' => json_encode([
                'news_id' => 44,
                'title' => 'Updated Title',
                'content' => '<p>Updated body.</p>',
                'category' => 'Event',
                'location' => 'New Venue',
                'link' => '',
                'image_path' => 'news/updated.jpg',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withoutMiddleware()
            ->withSession(['user_email' => 'studentservices@example.test'])
            ->getJson(route('staff.requests.changes', ['id' => $requestId]));

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('request.status_label', 'Pending Approval')
            ->assertJsonPath('fields.0.label', 'Title')
            ->assertJsonPath('fields.0.original.raw', 'Original Title')
            ->assertJsonPath('fields.0.updated.raw', 'Updated Title')
            ->assertJsonPath('fields.0.changed', true);
    }

    public function test_staff_cannot_fetch_another_users_pending_change_details(): void
    {
        $this->prepareApprovalTables();

        $requestId = DB::table('approval_requests')->insertGetId([
            'title' => 'Other User Request',
            'type' => 'NEWS_CREATE',
            'status' => 'pending',
            'requester_name' => 'Other User',
            'requester_email' => 'other@example.test',
            'details' => json_encode(['title' => 'Other User Request']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this
            ->withoutMiddleware()
            ->withSession(['user_email' => 'studentservices@example.test'])
            ->getJson(route('staff.requests.changes', ['id' => $requestId]))
            ->assertForbidden();
    }

    private function prepareApprovalTables(): void
    {
        if (!Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('details')->nullable();
                $table->string('type')->nullable();
                $table->string('status')->default('pending');
                $table->string('requester_name')->nullable();
                $table->string('requester_email')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->string('rejection_reason')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->unsignedBigInteger('news_id')->primary();
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->string('category')->nullable();
                $table->string('location')->nullable();
                $table->string('link')->nullable();
                $table->string('image_path')->nullable();
                $table->string('priority')->nullable();
                $table->string('status')->nullable();
                $table->timestamp('date_published')->nullable();
                $table->timestamps();
            });
        }
    }
}

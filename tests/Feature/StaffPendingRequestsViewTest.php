<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
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
}

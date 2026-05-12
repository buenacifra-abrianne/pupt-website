<?php

namespace Tests\Unit;

use App\Support\EventAnnouncementValidation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EventAnnouncementValidationTest extends TestCase
{
    public function test_event_announcement_requires_complete_details(): void
    {
        $request = Request::create('/news/save', 'POST', [
            'title' => '   ',
            'content' => '<p><br></p>',
            'category' => 'Event',
            'location' => '   ',
        ]);

        try {
            EventAnnouncementValidation::validate($request);
            $this->fail('Expected event validation to fail.');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $this->assertArrayHasKey('title', $errors);
            $this->assertArrayHasKey('content', $errors);
            $this->assertArrayHasKey('location', $errors);
            $this->assertArrayHasKey('image', $errors);
        }
    }

    public function test_event_announcement_accepts_uploaded_image(): void
    {
        $request = Request::create('/news/save', 'POST', [
            'title' => 'Career Fair',
            'content' => '<p>Meet partner companies.</p>',
            'category' => 'Event',
            'location' => 'Campus Gym',
        ], [], [
            'image' => UploadedFile::fake()->create('event.jpg', 20, 'image/jpeg'),
        ]);

        EventAnnouncementValidation::validate($request);

        $this->assertTrue(true);
    }

    public function test_event_announcement_accepts_existing_image_when_not_removed(): void
    {
        $request = Request::create('/news/save', 'POST', [
            'title' => 'Career Fair',
            'content' => '<p>Meet partner companies.</p>',
            'category' => 'Event',
            'location' => 'Campus Gym',
            'remove_image' => '0',
        ]);

        $existing = (object) ['image_path' => 'news/existing-event.jpg'];

        EventAnnouncementValidation::validate($request, $existing);

        $this->assertTrue(true);
    }

    public function test_non_event_news_does_not_require_event_fields(): void
    {
        $request = Request::create('/news/save', 'POST', [
            'title' => 'General News',
            'content' => '<p>Update.</p>',
            'category' => 'Campus',
        ]);

        EventAnnouncementValidation::validate($request);

        $this->assertTrue(true);
    }
}

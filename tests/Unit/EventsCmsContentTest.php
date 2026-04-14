<?php

namespace Tests\Unit;

use App\Support\EventsCmsContent;
use PHPUnit\Framework\TestCase;

class EventsCmsContentTest extends TestCase
{
    public function test_from_cards_input_replaces_existing_cards_without_reusing_deleted_positions(): void
    {
        $stored = EventsCmsContent::encode([
            'page' => [
                'eyebrow' => 'Campus Calendar',
                'title' => 'Events',
                'description' => 'Existing intro',
            ],
            'cards' => [
                [
                    'title' => 'First Event',
                    'summary' => 'First summary',
                    'content' => 'First content',
                    'image' => 'events/cards/first.jpg',
                    'location' => 'Hall A',
                    'event_date' => '2026-04-20',
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'category' => 'events',
                    'featured' => false,
                ],
                [
                    'title' => 'Deleted Event',
                    'summary' => 'Should be removed',
                    'content' => 'Should be removed',
                    'image' => 'events/cards/deleted.jpg',
                    'location' => 'Hall B',
                    'event_date' => '2026-04-21',
                    'start_time' => '10:00',
                    'end_time' => '11:00',
                    'category' => 'academic',
                    'featured' => true,
                ],
            ],
        ]);

        $result = EventsCmsContent::fromCardsInput([
            [
                'title' => 'First Event',
                'summary' => 'First summary',
                'content' => 'First content',
                'image' => 'events/cards/first.jpg',
                'location' => 'Hall A',
                'event_date' => '2026-04-20',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'category' => 'events',
            ],
        ], $stored);

        $this->assertSame('Existing intro', $result['page']['description']);
        $this->assertCount(1, $result['cards']);
        $this->assertSame('First Event', $result['cards'][0]['title']);
        $this->assertSame('events/cards/first.jpg', $result['cards'][0]['image']);
        $this->assertFalse($result['cards'][0]['featured']);
    }
}

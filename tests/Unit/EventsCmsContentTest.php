<?php

namespace Tests\Unit;

use App\Support\EventsCmsCardValidation;
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

    public function test_display_collections_moves_past_events_into_expired_bucket(): void
    {
        $cards = [
            [
                'title' => 'Past Event',
                'event_date' => '2026-04-14',
                'start_time' => '09:00',
                'featured' => true,
            ],
            [
                'title' => 'Today Event',
                'event_date' => '2026-04-16',
                'start_time' => '10:00',
                'featured' => false,
            ],
            [
                'title' => 'Future Event',
                'event_date' => '2026-04-18',
                'start_time' => '08:00',
                'featured' => true,
            ],
        ];

        $result = EventsCmsContent::displayCollections($cards, '2026-04-16');

        $this->assertCount(2, $result['active']);
        $this->assertSame(['Today Event', 'Future Event'], $result['active']->pluck('title')->all());
        $this->assertCount(1, $result['expired']);
        $this->assertSame(['Past Event'], $result['expired']->pluck('title')->all());
        $this->assertSame('Future Event', $result['featured']['title']);
        $this->assertSame(['Today Event'], $result['ongoing']->pluck('title')->all());
        $this->assertSame(['Future Event'], $result['upcoming']->pluck('title')->all());
    }

    public function test_from_cards_input_keeps_only_the_first_featured_card(): void
    {
        $result = EventsCmsContent::fromCardsInput([
            [
                'title' => 'First Event',
                'content' => 'First content',
                'location' => 'Hall A',
                'event_date' => '2026-04-20',
                'start_time' => '08:00',
                'end_time' => '09:00',
                'category' => 'events',
                'featured' => '1',
            ],
            [
                'title' => 'Second Event',
                'content' => 'Second content',
                'location' => 'Hall B',
                'event_date' => '2026-04-21',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'category' => 'academic',
                'featured' => '1',
            ],
        ]);

        $this->assertTrue($result['cards'][0]['featured']);
        $this->assertFalse($result['cards'][1]['featured']);
    }

    public function test_events_card_validation_requires_active_card_fields_and_blocks_multiple_featured_cards(): void
    {
        $errors = EventsCmsCardValidation::validateCardsSubmission([
            4 => [
                'title' => '',
                'content' => '',
                'location' => '',
                'event_date' => '',
                'start_time' => '',
                'end_time' => '',
                'category' => 'events',
                'featured' => '1',
            ],
            9 => [
                'title' => 'Existing featured event',
                'content' => 'Details',
                'location' => 'Hall B',
                'event_date' => '2026-04-21',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'category' => 'events',
                'featured' => '1',
            ],
        ], 4);

        $this->assertSame(
            EventsCmsCardValidation::FEATURED_CONFLICT_MESSAGE,
            $errors['events.cards.9.featured'][0] ?? null
        );
        $this->assertSame('Event Title is required.', $errors['events.cards.4.title'][0] ?? null);
        $this->assertSame('Event Details is required.', $errors['events.cards.4.content'][0] ?? null);
        $this->assertSame('Location is required.', $errors['events.cards.4.location'][0] ?? null);
        $this->assertSame('Event Date is required.', $errors['events.cards.4.event_date'][0] ?? null);
        $this->assertSame('Start Time is required.', $errors['events.cards.4.start_time'][0] ?? null);
        $this->assertSame('End Time is required.', $errors['events.cards.4.end_time'][0] ?? null);
    }
}

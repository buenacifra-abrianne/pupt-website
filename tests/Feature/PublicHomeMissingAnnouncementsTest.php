<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicHomeMissingAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_when_announcements_and_news_tables_are_missing(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('news');

        $response = $this->get(route('public.home'));

        $response->assertOk();
        $response->assertSee('NO NEWS PUBLISHED');
        $response->assertSee('NO ANNOUNCEMENT PUBLISHED');
    }
}

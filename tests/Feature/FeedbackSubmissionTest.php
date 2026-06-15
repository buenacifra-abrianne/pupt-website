<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeedbackSubmissionTest extends TestCase
{
    public function test_feedback_submission_succeeds_when_only_first_six_score_columns_exist(): void
    {
        Schema::dropIfExists('feedback_submissions');

        Schema::create('feedback_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('q1_score');
            $table->unsignedTinyInteger('q2_score');
            $table->unsignedTinyInteger('q3_score');
            $table->unsignedTinyInteger('q4_score');
            $table->unsignedTinyInteger('q5_score');
            $table->unsignedTinyInteger('q6_score');
            $table->decimal('overall_score', 4, 2);
            $table->string('overall_rating', 40);
            $table->timestamps();
        });

        $response = $this
            ->withoutMiddleware()
            ->post(route('public.feedback.submit'), [
                'responses' => [
                    0 => 4,
                    1 => 4,
                    2 => 4,
                    3 => 4,
                    4 => 4,
                    5 => 4,
                    6 => 4,
                    7 => 4,
                    8 => 4,
                    9 => 4,
                ],
            ]);

        $response->assertRedirect(route('public.feedback'));
        $response->assertSessionHas('feedback_submitted', true);

        $this->assertSame(1, DB::table('feedback_submissions')->count());

        $row = DB::table('feedback_submissions')->first();

        $this->assertNotNull($row);
        $this->assertSame(4, (int) $row->q1_score);
        $this->assertSame(4, (int) $row->q2_score);
        $this->assertSame(4, (int) $row->q3_score);
        $this->assertSame(4, (int) $row->q4_score);
        $this->assertSame(4, (int) $row->q5_score);
        $this->assertSame(4, (int) $row->q6_score);
        $this->assertSame('4.00', number_format((float) $row->overall_score, 2, '.', ''));
        $this->assertSame('Outstanding', $row->overall_rating);

        $analyticsResponse = $this
            ->withoutMiddleware()
            ->postJson(route('superadmin.analytics.superadminApi'), [
                'start' => now()->subDay()->toDateString(),
                'end' => now()->addDay()->toDateString(),
            ]);

        $analyticsResponse->assertOk();
        $analyticsResponse->assertJsonPath('feedback_results.total_responses', 1);
        $analyticsResponse->assertJsonPath('feedback_results.final_rating', 'Outstanding');
        $analyticsResponse->assertJsonPath('feedback_results.outstanding', 1);
    }
}

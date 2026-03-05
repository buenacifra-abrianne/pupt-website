<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    private const QUESTION_FIELDS = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6'];

    public function index()
    {
        return view('public.feedback');
    }

    public function store(Request $request)
    {
        $rules = [];
        foreach (self::QUESTION_FIELDS as $field) {
            $rules[$field] = ['required', 'integer', 'between:1,4'];
        }

        $validated = $request->validate($rules);

        $scores = collect(self::QUESTION_FIELDS)
            ->map(fn (string $field) => (int) $validated[$field])
            ->all();

        $overallScore = round(array_sum($scores) / max(1, count($scores)), 2);

        DB::table('feedback_submissions')->insert([
            'q1_score' => $scores[0],
            'q2_score' => $scores[1],
            'q3_score' => $scores[2],
            'q4_score' => $scores[3],
            'q5_score' => $scores[4],
            'q6_score' => $scores[5],
            'overall_score' => $overallScore,
            'overall_rating' => $this->scoreToRating($overallScore),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('public.feedback')
            ->with('feedback_submitted', true);
    }

    private function scoreToRating(float $score): string
    {
        if ($score >= 3.5) {
            return 'Outstanding';
        }

        if ($score >= 2.5) {
            return 'Very Satisfactory';
        }

        if ($score >= 1.5) {
            return 'Satisfactory';
        }

        return 'Unsatisfactory';
    }
}

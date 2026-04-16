<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeCmsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbackContent = $this->resolveFeedbackContent();

        return view('public.feedback', [
            'homeFeedbackPreview' => $feedbackContent,
            'feedbackQuestions' => $this->resolveFeedbackQuestions($feedbackContent),
        ]);
    }

    public function store(Request $request)
    {
        $feedbackContent = $this->resolveFeedbackContent();
        $questions = $this->resolveFeedbackQuestions($feedbackContent);

        if ($questions === []) {
            return redirect()
                ->route('public.feedback')
                ->withErrors([
                    'feedback_form' => 'The feedback form is not available right now.',
                ]);
        }

        $rules = [];
        foreach (array_keys($questions) as $index) {
            $rules['responses.'.$index] = ['required', 'integer', 'between:1,4'];
        }

        $validated = $request->validate($rules);

        $scores = collect(array_keys($questions))
            ->map(fn (int|string $index) => (int) data_get($validated, 'responses.'.$index))
            ->all();

        $overallScore = round(array_sum($scores) / max(1, count($scores)), 2);
        $payload = [
            'overall_score' => $overallScore,
            'overall_rating' => $this->scoreToRating($overallScore),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach (range(1, 10) as $questionNumber) {
            $payload['q'.$questionNumber.'_score'] = $scores[$questionNumber - 1] ?? null;
        }

        DB::table('feedback_submissions')->insert($payload);

        return redirect()
            ->route('public.feedback')
            ->with('feedback_submitted', true);
    }

    private function resolveFeedbackContent(): array
    {
        $homeCms = HomeCmsContent::defaults();

        if (Schema::hasTable('cms_contents')) {
            $homeRow = DB::table('cms_contents')->where('tab_key', 'home')->first();
            if ($homeRow) {
                $homeCms = HomeCmsContent::fromStored((string) ($homeRow->content ?? ''));
            }
        }

        return is_array($homeCms['feedback'] ?? null)
            ? $homeCms['feedback']
            : (HomeCmsContent::defaults()['feedback'] ?? []);
    }

    private function resolveFeedbackQuestions(array $feedbackContent): array
    {
        return collect($feedbackContent['questions'] ?? [])
            ->filter(fn ($item) => is_array($item) && trim((string) ($item['question'] ?? '')) !== '')
            ->take(10)
            ->values()
            ->all();
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

<?php

namespace App\Http\Middleware;

use App\Services\HtmlEntityNormalizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizePlainTextEntities
{
    /**
     * @var array<int, string>
     */
    private array $plainTextKeys = [
        'title',
        'category',
        'location',
        'label',
        'name',
        'heading',
        'subtitle',
        'eyebrow',
        'tag',
        'original_filename',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethodSafe()) {
            $request->merge($this->normalizeInput($request->input()));
        }

        return $next($request);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function normalizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->normalizeInput($value);
                continue;
            }

            if (is_string($value) && $this->isPlainTextKey((string) $key)) {
                $input[$key] = HtmlEntityNormalizer::plain($value);
            }
        }

        return $input;
    }

    private function isPlainTextKey(string $key): bool
    {
        $normalized = strtolower($key);

        if (in_array($normalized, $this->plainTextKeys, true)) {
            return true;
        }

        return str_ends_with($normalized, '_title')
            || str_ends_with($normalized, '_name')
            || str_ends_with($normalized, '_label')
            || str_ends_with($normalized, '_location');
    }
}

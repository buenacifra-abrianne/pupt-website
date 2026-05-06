<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\PlainText;
use Illuminate\Http\JsonResponse;

class AnnouncementApiController extends Controller
{
    public function list(): JsonResponse
    {
        $announcements = Announcement::query()
            ->select([
                'announcement_id',
                'title',
                'content',
                'status',
                'link',
            ])
            ->whereRaw('UPPER(status) = ?', ['ENABLED'])
            ->orderByDesc('announcement_id')
            ->get()
            ->map(function ($announcement) {
                return [
                    'announcement_id' => $announcement->announcement_id,
                    'title' => PlainText::normalize($announcement->title),
                    'content' => $this->toPlainText($announcement->content),
                    'status' => $announcement->status,
                    'link' => $announcement->link,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Announcements retrieved successfully.',
            'data' => $announcements,
        ]);
    }

    public function show(int $announcement_id): JsonResponse
    {
        $announcement = Announcement::query()
            ->select([
                'announcement_id',
                'title',
                'content',
                'status',
                'link',
            ])
            ->whereRaw('UPPER(status) = ?', ['ENABLED'])
            ->where('announcement_id', $announcement_id)
            ->first();

        if (! $announcement) {
            return response()->json([
                'success' => false,
                'message' => 'Announcement not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Announcement retrieved successfully.',
            'data' => [
                'announcement_id' => $announcement->announcement_id,
                'title' => PlainText::normalize($announcement->title),
                'content' => $this->toPlainText($announcement->content),
                'status' => $announcement->status,
                'link' => $announcement->link,
            ],
        ]);
    }

    private function toPlainText(?string $html): string
    {
        $value = trim((string) $html);

        if ($value === '') {
            return '';
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/<br\s*\/?>/i', "\n", $value);
        $value = preg_replace('/<\/p>/i', "\n", $value);
        $value = preg_replace('/<\/div>/i', "\n", $value);
        $value = preg_replace('/<\/li>/i', "\n", $value);

        $text = strip_tags($value);
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);

        return trim($text);
    }
}

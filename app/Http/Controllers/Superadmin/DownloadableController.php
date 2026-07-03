<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Support\DownloadableFile;
use Illuminate\Validation\ValidationException;

class DownloadableController extends Controller
{
    private function notifySystem(string $title, string $message, string $type = 'INFO'): void
    {
        $allowed = ['INFO', 'WARNING', 'DANGER', 'PRIMARY'];
        $type = strtoupper($type);

        if (!in_array($type, $allowed, true)) {
            $type = 'INFO';
        }

        DB::table('notifications')->insert([
            'channel' => 'SYSTEM',
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'created_at' => now(),
        ]);
    }

    private function logActivity(string $action, ?int $targetId, string $description): void
    {
        AuditLog::record($action, 'DOWNLOADABLE', $description, $targetId);
    }

    public function index(Request $request)
    {
        $userId = session('user_id');

        $downloadables = DB::table('downloadables')
            ->orderBy('created_at', 'desc')
            ->get();

        $readDownloadableIds = DB::table('downloadable_user_reads')
            ->where('user_id', $userId)
            ->pluck('downloadable_id')
            ->toArray();

        foreach ($downloadables as $downloadable) {
            $downloadable->year = date('Y', strtotime($downloadable->created_at));
            $downloadable->is_read = in_array($downloadable->downloadable_id, $readDownloadableIds);
        }

        $groupedDownloadables = $downloadables->groupBy('year');

        return view('superadmin.downloadables', [
            'groupedDownloadables' => $groupedDownloadables,
            'downloadables' => $downloadables, // keep this for JS if needed
        ]);
    }

    public function markAsRead(Request $request)
    {
        $id = $request->input('id');
        $userId = session('user_id');

        if ($id && $userId) {
            DB::table('downloadable_user_reads')->updateOrInsert(
                ['downloadable_id' => $id, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function save(Request $request)
    {
        return response()->json([
            'ok' => false,
            'error' => 'Superadmin is not allowed to edit/delete memorandums.',
        ], 403);
    }

    public function delete(Request $request)
    {
        return response()->json([
            'ok' => false,
            'error' => 'Superadmin is not allowed to edit/delete memorandums.',
        ], 403);
    }

}
<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function index()
    {
        $downloadables = DB::table('downloadables')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.downloadables', compact('downloadables'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'downloadable_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:Memo,Form'],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $title = trim((string) $request->input('title'));
        $description = trim((string) $request->input('description'));
        $category = trim((string) $request->input('category'));
        $downloadableId = (int) $request->input('downloadable_id', 0);

        $existing = null;
        if ($downloadableId > 0) {
            $existing = DB::table('downloadables')
                ->where('downloadable_id', $downloadableId)
                ->first();

            if (!$existing) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Downloadable not found.',
                ], 404);
            }
        }

        $filePath = $existing->file_path ?? null;
        $originalFilename = $existing->original_filename ?? null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            $filePath = $uploadedFile->store('downloadables', 'public');
            $originalFilename = $uploadedFile->getClientOriginalName();

            if ($existing && !empty($existing->file_path) && Storage::disk('public')->exists($existing->file_path)) {
                Storage::disk('public')->delete($existing->file_path);
            }
        }

        if (!$filePath || !$originalFilename) {
            return response()->json([
                'ok' => false,
                'error' => 'A file is required.',
            ], 422);
        }

        $data = [
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'category' => $category,
            'file_path' => $filePath,
            'original_filename' => $originalFilename,
            'updated_at' => now(),
        ];

        if ($downloadableId > 0) {
            DB::table('downloadables')
                ->where('downloadable_id', $downloadableId)
                ->update($data);

            $this->logActivity(
                'UPDATED',
                $downloadableId,
                'Updated downloadable: ' . $title
            );

            $this->notifySystem(
                'Downloadable Updated',
                'Downloadable ' . $title . ' was updated.',
                'PRIMARY'
            );

            return response()->json([
                'ok' => true,
                'message' => 'Downloadable updated successfully.',
            ]);
        }

        $data['created_by'] = (int) (session('user_id') ?? 0);
        $data['created_at'] = now();

        $newId = DB::table('downloadables')->insertGetId($data, 'downloadable_id');

        $this->logActivity(
            'CREATED',
            (int) $newId,
            'Created downloadable: ' . $title
        );

        $this->notifySystem(
            'Downloadable Created',
            'Downloadable ' . $title . ' was created.',
            'INFO'
        );

        return response()->json([
            'ok' => true,
            'message' => 'Downloadable created successfully.',
        ]);
    }

    public function delete(Request $request)
    {
        $id = (int) $request->input('id');

        $row = DB::table('downloadables')
            ->where('downloadable_id', $id)
            ->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'error' => 'Downloadable not found.',
            ], 404);
        }

        if (!empty($row->file_path) && Storage::disk('public')->exists($row->file_path)) {
            Storage::disk('public')->delete($row->file_path);
        }

        DB::table('downloadables')
            ->where('downloadable_id', $id)
            ->delete();

        $this->logActivity(
            'DELETED',
            $id,
            'Deleted downloadable: ' . ($row->title ?? "#{$id}")
        );

        $this->notifySystem(
            'Downloadable Deleted',
            'Downloadable ' . ($row->title ?? "#{$id}") . ' was deleted.',
            'DANGER'
        );

        return response()->json(['ok' => true]);
    }

}
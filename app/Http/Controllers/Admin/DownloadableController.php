<?php

namespace App\Http\Controllers\Admin;

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

    public function index()
    {
        $downloadables = DB::table('downloadables')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.downloadables', compact('downloadables'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'downloadable_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:20480'],
        ]);

        $title = trim((string) $request->input('title'));
        $description = trim((string) $request->input('description'));
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

        if ($message = DownloadableFile::validationError($request->file('file'))) {
            throw ValidationException::withMessages(['file' => $message]);
        }

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            $storedPath = DownloadableFile::store($uploadedFile, 'downloadables');
            if (!$storedPath) {
                return response()->json([
                    'ok' => false,
                    'error' => 'File upload failed.',
                ], 500);
            }

            if ($existing && !empty($existing->file_path)) {
                DownloadableFile::delete($existing->file_path);
            }

            $filePath = $storedPath;
            $originalFilename = $uploadedFile->getClientOriginalName();
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

        if (!empty($row->file_path)) {
            DownloadableFile::delete($row->file_path);
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
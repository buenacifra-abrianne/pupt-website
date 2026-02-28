<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;

class ApprovalsController extends Controller
{
    public function pending(Request $request)
    {
        $query = ApprovalRequest::where('status', 'pending')->latest();

        // Search
        if ($request->filled('q')) {
            $q = $request->q;

            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('requester_name', 'like', "%{$q}%")
                    ->orWhere('requester_email', 'like', "%{$q}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $pending = $query->paginate(10)->withQueryString();

        $types = ApprovalRequest::select('type')
            ->distinct()
            ->pluck('type');

        return view('faculty.pendings', compact('pending', 'types'));
    }

    public function approve(Request $request, $id)
{
    // who approved (adjust depending on your session fields)
    $adminEmail = (string) session('user_email');
    $adminName  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));
    $reviewedBy = $adminName ?: $adminEmail ?: 'Admin';

    $row = DB::table('approval_requests')->where('id', (int)$id)->first();
    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'Request not found.'], 404);
    }
    if (strtolower(trim((string)$row->status)) !== 'pending') {
        return response()->json(['ok' => false, 'error' => 'Only pending requests can be approved.'], 422);
    }

    $type = strtoupper((string)$row->type);
    $payload = json_decode($row->details ?? '{}', true) ?: [];

    DB::beginTransaction();
    try {
        // -------------------------
        // ANNOUNCEMENTS
        // -------------------------
        if ($type === 'ANNOUNCEMENT_CREATE') {
            // created_by is INT in announcements table
$creatorId = 0;

// adjust table name if needed (users table)
$u = DB::table('users')->where('email', $row->requester_email)->first();
if ($u && isset($u->user_id)) {
    $creatorId = (int) $u->user_id;
} elseif ($u && isset($u->id)) {
    $creatorId = (int) $u->id;
}

DB::table('announcements')->insert([
    'title' => $payload['title'] ?? $row->title ?? 'Announcement',
    'content' => $payload['content'] ?? '',
    'priority' => strtoupper($payload['priority'] ?? 'LOW'),
    'created_at' => now(),
    'status' => 'ENABLED',
    'date_published' => now(),
    'created_by' => $creatorId,
]);
        }
        elseif ($type === 'ANNOUNCEMENT_UPDATE') {
            $aid = (int)($payload['announcement_id'] ?? 0);
            if (!$aid) throw new \Exception("Missing announcement_id in request details.");

            DB::table('announcements')
                ->where('announcement_id', $aid)
                ->update([
                    'title' => $payload['title'] ?? DB::raw('title'),
                    'content' => $payload['content'] ?? DB::raw('content'),
                    'priority' => strtoupper($payload['priority'] ?? 'LOW'),
                    // no updated_at column in your table (based on your schema)
                ]);
        }
        elseif ($type === 'ANNOUNCEMENT_DELETE') {
            $aid = (int)($payload['announcement_id'] ?? 0);
            if (!$aid) throw new \Exception("Missing announcement_id in request details.");

            DB::table('announcements')->where('announcement_id', $aid)->delete();
        }
        elseif ($type === 'ANNOUNCEMENT_ENABLE' || $type === 'ANNOUNCEMENT_DISABLE') {
            $aid = (int)($payload['announcement_id'] ?? 0);
            if (!$aid) throw new \Exception("Missing announcement_id in request details.");

            $newStatus = ($type === 'ANNOUNCEMENT_DISABLE') ? 'DISABLED' : 'ENABLED';

            DB::table('announcements')
                ->where('announcement_id', $aid)
                ->update(['status' => $newStatus]);
        }

        // -------------------------
        // NEWS
        // -------------------------
        elseif ($type === 'NEWS_CREATE') {
            DB::table('news')->insert([
                'title' => $payload['title'] ?? $row->title ?? 'News',
                'content' => $payload['content'] ?? '',
                'category' => $payload['category'] ?? 'Other',
                'location' => $payload['location'] ?? null,
                'image_path' => $payload['image_path'] ?? null,
                'date_published' => now(),
                'created_at' => now(),
            ]);
        }
        elseif ($type === 'NEWS_UPDATE') {
            $nid = (int)($payload['news_id'] ?? 0);
            if (!$nid) throw new \Exception("Missing news_id in request details.");

            DB::table('news')
                ->where('news_id', $nid)
                ->update([
                    'title' => $payload['title'] ?? DB::raw('title'),
                    'content' => $payload['content'] ?? DB::raw('content'),
                    'category' => $payload['category'] ?? DB::raw('category'),
                    'location' => $payload['location'] ?? DB::raw('location'),
                    // image_path update if you later support image requests
                ]);
        }
        elseif ($type === 'NEWS_DELETE') {
            $nid = (int)($payload['news_id'] ?? 0);
            if (!$nid) throw new \Exception("Missing news_id in request details.");

            DB::table('news')->where('news_id', $nid)->delete();
        }
        else {
            throw new \Exception("Unknown request type: {$type}");
        }

        // mark request approved
        DB::table('approval_requests')->where('id', (int)$id)->update([
            'status' => 'approved',
            'reviewed_by' => (int) (session('user_id') ?? 0),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        DB::commit();
        return response()->json(['ok' => true]);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
    }
}

    public function reject(Request $request, $id)
{
    $request->validate([
        'reason' => 'nullable|string|max:255'
    ]);

    // get the row by ID (same style as approve)
    $row = DB::table('approval_requests')->where('id', (int)$id)->first();
    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'Request not found.'], 404);
    }

    if (strtolower(trim((string)$row->status)) !== 'pending') {
        return response()->json(['ok' => false, 'error' => 'This request is no longer pending.'], 422);
    }

    DB::table('approval_requests')->where('id', (int)$id)->update([
        'status' => 'rejected',
        'reviewed_by' => (int) (session('user_id') ?? 0),  // INT column
        'reviewed_at' => now(),
        'rejection_reason' => $request->input('reason'),
        'updated_at' => now(),
    ]);

    return response()->json(['ok' => true]);
}
}
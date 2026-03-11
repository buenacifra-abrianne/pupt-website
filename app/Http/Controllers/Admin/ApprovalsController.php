<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Support\AuditLog;
use App\Support\CmsSections;

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
        $pending = $this->attachDisplayFields($pending);

        $historyQuery = ApprovalRequest::whereIn('status', ['approved', 'rejected'])->latest();

        // same search
        if ($request->filled('q')) {
            $q = $request->q;
            $historyQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('requester_name', 'like', "%{$q}%")
                    ->orWhere('requester_email', 'like', "%{$q}%");
            });
        }

        // same type filter
        if ($request->filled('type')) {
            $historyQuery->where('type', $request->type);
        }

// IMPORTANT: different page name so pagination won't conflict with pending list
$history = $historyQuery->paginate(10, ['*'], 'history_page')->withQueryString();
$history = $this->attachDisplayFields($history);

        // ✅ Attach display fields for modal "View" (so enable/disable/delete shows real announcement data)

        $types = ApprovalRequest::select('type')
            ->distinct()
            ->pluck('type');

        return view('admin.pendings', compact('pending', 'history', 'types'));
    }

    public function approve(Request $request, $id)
{
    // who approved (adjust depending on your session fields)
    $adminEmail = (string) session('user_email');
    $adminName  = trim((string)session('user_first_name').' '.(string)session('user_last_name'));
    $reviewedBy = $adminName ?: $adminEmail ?: 'Admin';

    $row = ApprovalRequest::find($id);
    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'Request not found.'], 404);
    }
    if (strtolower(trim((string)$row->status)) !== 'pending') {
        return response()->json(['ok' => false, 'error' => 'Only pending requests can be approved.'], 422);
    }

    $type = strtoupper((string)$row->type);
    $payload = json_decode($row->details ?? '{}', true) ?: [];
    $reqId = (int) $row->getKey(); // ✅ use real PK

    DB::beginTransaction();
    try {
        // -------------------------
        // ANNOUNCEMENTS
        // -------------------------
        if ($type === 'ANNOUNCEMENT_CREATE') {

    // created_by is INT in announcements table
    $creatorId = 0;
    $u = DB::table('users')->where('email', $row->requester_email)->first();
    if ($u && isset($u->user_id)) {
        $creatorId = (int) $u->user_id;
    } elseif ($u && isset($u->id)) {
        $creatorId = (int) $u->id;
    }

    // ✅ Insert and get the new announcement_id
    $newAnnouncementId = DB::table('announcements')->insertGetId([
        'title' => $payload['title'] ?? $row->title ?? 'Announcement',
        'content' => $payload['content'] ?? '',
        'priority' => strtoupper($payload['priority'] ?? 'LOW'),
        'created_at' => now(),
        'status' => 'ENABLED',
        'date_published' => now(),
        'created_by' => $creatorId,
    ], 'announcement_id');

    AuditLog::record(
        'CREATED',
        'ANNOUNCEMENT',
        'Created announcement: '.($payload['title'] ?? $row->title ?? 'Announcement').' (approved request)',
        (int) $newAnnouncementId,
        [
            'user_id' => $creatorId > 0 ? $creatorId : null,
            'user_name' => trim((string) ($row->requester_name ?? '')) !== ''
                ? trim((string) $row->requester_name)
                : 'Staff',
        ]
    );

    // ✅ Save announcement_id back into approval_requests.details (so future edits become UPDATE)
    $payload['announcement_id'] = (int) $newAnnouncementId;

    DB::table('approval_requests')->where('id', $reqId)->update([
        'details' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        'updated_at' => now(),
    ]);
}
        elseif ($type === 'NEWS_CREATE') {

    // created_by is INT in news table
    $creatorId = 0;
    $u = DB::table('users')->where('email', $row->requester_email)->first();
    if ($u && isset($u->user_id)) {
        $creatorId = (int) $u->user_id;
    } elseif ($u && isset($u->id)) {
        $creatorId = (int) $u->id;
    }

    // ✅ Insert and get the new news_id
    $newNewsId = DB::table('news')->insertGetId([
        'title' => $payload['title'] ?? $row->title ?? 'News',
        'content' => $payload['content'] ?? '',
        'category' => $payload['category'] ?? 'Other',
        'location' => $payload['location'] ?? null,
        'image_path' => $payload['image_path'] ?? null,
        'date_published' => now(),
        'created_at' => now(),
        'priority' => strtoupper($payload['priority'] ?? 'LOW'),
        'status' => 'APPROVED',
        'created_by' => $creatorId,
    ], 'news_id');

    AuditLog::record(
        'CREATED',
        'NEWS',
        'Created news: '.($payload['title'] ?? $row->title ?? 'News').' (approved request)',
        (int) $newNewsId,
        [
            'user_id' => $creatorId > 0 ? $creatorId : null,
            'user_name' => trim((string) ($row->requester_name ?? '')) !== ''
                ? trim((string) $row->requester_name)
                : 'Staff',
        ]
    );

    // ✅ Save news_id back into approval_requests.details
    $payload['news_id'] = (int) $newNewsId;

    DB::table('approval_requests')->where('id', $reqId)->update([
        'details' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        'updated_at' => now(),
    ]);
}
        elseif ($type === 'ANNOUNCEMENT_UPDATE') {
            $aid = (int)($payload['announcement_id'] ?? 0);
            if ($aid <= 0) {
                throw new \Exception("Missing announcement_id in request details.");
            }

            $announcementUpdate = [
                'title' => $payload['title'] ?? DB::raw('title'),
                'content' => $payload['content'] ?? DB::raw('content'),
                'priority' => isset($payload['priority']) ? strtoupper((string) $payload['priority']) : DB::raw('priority'),
            ];
            if (Schema::hasColumn('announcements', 'updated_at')) {
                $announcementUpdate['updated_at'] = now();
            }

            DB::table('announcements')
                ->where('announcement_id', $aid)
                ->update($announcementUpdate);
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
        elseif ($type === 'NEWS_UPDATE') {
            $nid = (int)($payload['news_id'] ?? 0);
        if ($nid <= 0) {
            throw new \Exception("Missing news_id in request details. Approve NEWS_CREATE first so it saves news_id into the request payload.");
        }

            DB::table('news')
                ->where('news_id', $nid)
                ->update([
                    'title' => $payload['title'] ?? DB::raw('title'),
                    'content' => $payload['content'] ?? DB::raw('content'),
                    'category' => $payload['category'] ?? DB::raw('category'),
                    'location' => $payload['location'] ?? DB::raw('location'),
                    'priority' => isset($payload['priority']) ? strtoupper($payload['priority']) : DB::raw('priority'),
                    // image_path update if you later support image requests
                ]);
        }
        elseif ($type === 'NEWS_DELETE') {
            $nid = (int)($payload['news_id'] ?? 0);
            if (!$nid) throw new \Exception("Missing news_id in request details.");

            DB::table('news')->where('news_id', $nid)->delete();
        }
        elseif (str_starts_with($type, 'CMS_') && str_ends_with($type, '_EDIT')) {
            if (!Schema::hasTable('cms_contents')) {
                throw new \Exception("cms_contents table not found. Run migrations first.");
            }

            $tabKey = (string) ($payload['tab_key'] ?? CmsSections::tabForRequestType($type));
            if ($tabKey === '' || !in_array($tabKey, CmsSections::allTabKeys(), true)) {
                throw new \Exception("Invalid CMS tab in request details.");
            }

            $tabLabel = CmsSections::labelForTab($tabKey);
            $title = trim((string) ($payload['title'] ?? ''));
            if ($title === '') {
                $title = $tabLabel.' Content';
            }

            $content = (string) ($payload['content'] ?? '');

            $exists = DB::table('cms_contents')->where('tab_key', $tabKey)->exists();
            if ($exists) {
                DB::table('cms_contents')
                    ->where('tab_key', $tabKey)
                    ->update([
                        'title' => $title,
                        'content' => $content,
                        'updated_by' => (int) (session('user_id') ?? 0),
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('cms_contents')->insert([
                    'tab_key' => $tabKey,
                    'title' => $title,
                    'content' => $content,
                    'updated_by' => (int) (session('user_id') ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            AuditLog::record(
                'APPROVED',
                'CONTENT',
                'Approved CMS content edit for '.$tabLabel,
                (int) (session('user_id') ?? 0)
            );
        }
        else {
            throw new \Exception("Unknown request type: {$type}");
        }

        // mark request approved
        DB::table('approval_requests')->where('id', $reqId)->update([
            'status' => 'approved',
            'reviewed_by' => (int) (session('user_id') ?? 0),
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔔 Notify ONLY the requester staff
        // 🔔 Notify ONLY the requester staff (robust user id lookup)
$reqEmail = strtolower(trim((string)($row->requester_email ?? '')));

// try user_id first (your app seems to use user_id)
$reqUserId = (int) DB::table('users')
    ->whereRaw('LOWER(email) = ?', [$reqEmail])
    ->value('user_id');

// fallback if the PK is 'id'
if ($reqUserId <= 0) {
    $reqUserId = (int) DB::table('users')
        ->whereRaw('LOWER(email) = ?', [$reqEmail])
        ->value('id');
}

// ✅ If still 0, do nothing (cannot target a staff user)
if ($reqUserId > 0) {
    $this->pushSystemNotif(
        'PRIMARY',
        'Request Approved',
        'Your request was approved.',
        'STAFF',
        $reqUserId
    );
}

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
    $row = ApprovalRequest::find($id);
    if (!$row) {
        return response()->json(['ok' => false, 'error' => 'Request not found.'], 404);
    }
    $reqId = (int) $row->getKey();

    if (strtolower(trim((string)$row->status)) !== 'pending') {
        return response()->json(['ok' => false, 'error' => 'This request is no longer pending.'], 422);
    }

    DB::table('approval_requests')->where('id', $reqId)->update([
        'status' => 'rejected',
        'reviewed_by' => (int) (session('user_id') ?? 0),  // INT column
        'reviewed_at' => now(),
        'rejection_reason' => $request->input('reason'),
        'updated_at' => now(),
    ]);

    $rawType = strtoupper(trim((string) ($row->type ?? '')));
    $requestTitle = trim((string) ($row->title ?? 'Request'));
    $reasonText = trim((string) $request->input('reason', ''));
    $reasonSuffix = $reasonText !== '' ? ' | Reason: '.$reasonText : '';

    if (str_starts_with($rawType, 'CMS_') && str_ends_with($rawType, '_EDIT')) {
        $tabKey = CmsSections::tabForRequestType($rawType);
        $label = $tabKey ? CmsSections::labelForTab($tabKey) : 'Content';

        AuditLog::record(
            'REJECTED',
            'CONTENT',
            'Rejected CMS content edit for '.$label.$reasonSuffix,
            (int) (session('user_id') ?? 0)
        );
    } else {
        $module = str_starts_with($rawType, 'NEWS_') ? 'NEWS' : 'ANNOUNCEMENT';

        AuditLog::record(
            'REJECTED',
            $module,
            'Rejected '.$requestTitle.$reasonSuffix,
            (int) (session('user_id') ?? 0)
        );
    }

    $reqEmail = strtolower(trim((string)($row->requester_email ?? '')));

$reqUserId = (int) DB::table('users')
    ->whereRaw('LOWER(email) = ?', [$reqEmail])
    ->value('user_id');

if ($reqUserId <= 0) {
    $reqUserId = (int) DB::table('users')
        ->whereRaw('LOWER(email) = ?', [$reqEmail])
        ->value('id');
}

if ($reqUserId > 0) {
    $this->pushSystemNotif(
        'DANGER',
        'Request Rejected',
        'Your request was rejected.',
        'STAFF',
        $reqUserId
    );
}

    $reason = trim((string)$request->input('reason'));
    $msg = ($row->requester_name ?: $row->requester_email) . " request was REJECTED: " . (strtoupper((string)$row->type)) . " — " . ($row->title ?? 'Request');
    if ($reason !== '') $msg .= " | Reason: {$reason}";

    return response()->json(['ok' => true]);
}

public function destroy($id)
{
    $req = \App\Models\ApprovalRequest::findOrFail($id);
    $req->delete(); // approval_requests ONLY

    return response()->json(['ok' => true]);
}

private function pushSystemNotif(
    string $type,
    string $title,
    string $message,
    ?string $targetRole = null,
    ?int $targetUserId = null
): void
{
    DB::table('notifications')->insert([
        'title'          => $title,
        'message'        => $message,
        'type'           => strtoupper($type),
        'channel'        => 'SYSTEM',
        'target_role'    => $targetRole,
        'target_user_id' => $targetUserId,
        'created_at'     => now(),
    ]);
}

private function attachDisplayFields($paginator)
{
    $paginator->getCollection()->transform(function ($item) {
        $type = strtoupper((string)($item->type ?? ''));
        $payload = json_decode($item->details ?? '{}', true) ?: [];

        // default: use request payload (create/update)
        $displayTitle = $payload['title'] ?? $item->title ?? 'Request';
        $displayPriority = strtoupper((string)($payload['priority'] ?? ''));
        $displayContent = $payload['content'] ?? ($payload['details'] ?? '');

        // ANNOUNCEMENT: enable/disable/delete -> show REAL announcement
        if (in_array($type, ['ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE','ANNOUNCEMENT_DELETE'], true)) {
            $aid = (int)($payload['announcement_id'] ?? 0);
            if ($aid > 0) {
                $a = DB::table('announcements')->where('announcement_id', $aid)->first();
                if ($a) {
                    $displayTitle = (string)($a->title ?? $displayTitle);
                    $displayPriority = strtoupper((string)($a->priority ?? $displayPriority));
                    $displayContent = (string)($a->content ?? $displayContent);
                }
            }
        }

        // NEWS_DELETE -> show REAL news + keep image/category/location
        if ($type === 'NEWS_DELETE') {
            $nid = (int)($payload['news_id'] ?? 0);
            if ($nid > 0) {
                $n = DB::table('news')->where('news_id', $nid)->first();
                if ($n) {
                    $displayTitle = (string)($n->title ?? $displayTitle);
                    $displayPriority = strtoupper((string)($n->priority ?? $displayPriority));
                    $displayContent = (string)($n->content ?? $displayContent);

                    $payload['image_path'] = $payload['image_path'] ?? ($n->image_path ?? null);
                    $payload['category']   = $payload['category']   ?? ($n->category ?? null);
                    $payload['location']   = $payload['location']   ?? ($n->location ?? null);
                }
            }
        }

        if (str_starts_with($type, 'CMS_') && str_ends_with($type, '_EDIT')) {
            $tabKey = (string) ($payload['tab_key'] ?? CmsSections::tabForRequestType($type));
            $tabLabel = CmsSections::labelForTab($tabKey);

            $displayTitle = trim((string) ($payload['title'] ?? ''));
            if ($displayTitle === '') {
                $displayTitle = $tabLabel.' Content';
            }

            $requested = (string) ($payload['content'] ?? '');
            $previous = (string) ($payload['previous_content'] ?? '');

            if (trim($previous) !== '' && trim($previous) !== trim($requested)) {
                $displayContent = "Previous:\n".$previous."\n\nRequested update:\n".$requested;
            } else {
                $displayContent = $requested;
            }
        }

        // attach to row for blade
        $item->display_title = $displayTitle;
        $item->display_priority = $displayPriority;
        $item->display_content = $displayContent;

        // image url for modal
        $imagePath = $payload['image_path'] ?? null;
        $item->display_image_url = $imagePath ? asset('storage/' . ltrim($imagePath, '/')) : null;

        // news meta for modal
        $item->display_category = $payload['category'] ?? null;
        $item->display_location = $payload['location'] ?? null;

        return $item;
    });

    return $paginator;
}
}

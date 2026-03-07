@php
  $payload = json_decode($row->details ?? '{}', true) ?: [];

  $reqId = (int) $row->id;
  $type  = strtoupper((string)$row->type);

  $typeLabel = match($type) {
    'ANNOUNCEMENT_UPDATE'  => 'Edit Request',
    'ANNOUNCEMENT_DELETE'  => 'Delete Request',
    'ANNOUNCEMENT_ENABLE'  => 'Enable Request',
    'ANNOUNCEMENT_DISABLE' => 'Disable Request',
    'ANNOUNCEMENT_CREATE'  => 'Create Request',
    'NEWS_UPDATE'          => 'Edit News Request',
    'NEWS_DELETE'          => 'Delete News Request',
    'NEWS_CREATE'          => 'Create News Request',
    default => 'Request',
  };

$requestTitle = $payload['title'] ?? $row->title ?? 'Request';
$requestContent = $payload['content'] ?? '';
$requestPriority = strtoupper((string)($payload['priority'] ?? 'LOW'));

$title = $requestTitle;
$content = $requestContent;
$priority = $requestPriority;

// ✅ If this request targets an existing announcement (enable/disable/delete/update),
// show LIVE announcement title/priority/content on the card.
$targetAnnId = (int)($payload['announcement_id'] ?? 0);

if ($targetAnnId > 0 && in_array($type, ['ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE','ANNOUNCEMENT_DELETE'], true)) {
  $live = \DB::table('announcements')->where('announcement_id', $targetAnnId)->first();
  if ($live) {
    $title = (string)($live->title ?? $title);
    $content = (string)($live->content ?? $content);
    $priority = strtoupper((string)($live->priority ?? $priority));
  }
}

$editTitle = $requestTitle ?: $title;
$editContent = $requestContent !== '' ? $requestContent : $content;
$editPriority = $requestPriority ?: $priority;

  $reqStatus = strtolower(trim((string)$row->status));
  $statusClass = $reqStatus;

  $db_status = null;
  $is_disabled = false;

  if ($type === 'ANNOUNCEMENT_ENABLE') {
    $db_status = 'ENABLED';
  } elseif ($type === 'ANNOUNCEMENT_DISABLE') {
    $db_status = 'DISABLED';
    $is_disabled = true;
  }

  $targetId = $payload['announcement_id'] ?? null;

  $searchHay = strtolower(
    ($title).' '.($content).' '.($priority).' '.($reqStatus).' '.($targetId ?? '').' '.(($row->rejection_reason ?? ''))
  );
@endphp

<div class="announcement-item {{ $is_disabled ? 'disabled' : '' }} {{ strtolower($priority) }}-priority"
     data-search="{{ e($searchHay) }}"
     style="margin-bottom: 14px;">

  <div class="announcement-header">
  <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">

    {{-- LEFT SIDE: Title + Priority inline --}}
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
      <h3 class="announcement-title" style="margin:0;">
        {{ e($title) }}
      </h3>

      <span class="priority-badge priority-{{ strtolower($priority) }}">
        {{ ucfirst(strtolower($priority)) }} Priority
      </span>
    </div>

    {{-- RIGHT SIDE: Request Type --}}
    <span style="
      font-size:12px;
      padding:6px 10px;
      border-radius:999px;
      background:#f2f3f5;
      color:#333;
      white-space:nowrap;
    ">
      {{ $typeLabel }}
    </span>

  </div>
</div>

  <p class="announcement-description">{{ e($content) }}</p>

  <div class="announcement-actions">
    <button type="button" class="btn btn-sm btn-primary"
      onclick="editAnnouncementRequest(
        {{ $reqId }},
        {{ \Illuminate\Support\Js::from($type) }},
        {{ $targetId ? (int)$targetId : 0 }},
        {{ \Illuminate\Support\Js::from($editTitle) }},
        {{ \Illuminate\Support\Js::from($editContent) }},
        {{ \Illuminate\Support\Js::from($editPriority) }}
      )">
      <i class="fas fa-edit"></i> Edit
    </button>

    {{-- Toggle button: ONLY show when request is APPROVED --}}
    @if($statusClass === 'approved' && !empty($db_status) && !empty($payload['announcement_id']))
    <button class="btn btn-sm {{ $is_disabled ? 'btn-success' : 'btn-warning' }}"
        type="button"
        onclick="toggleAnnouncementStatus({{ (int)$payload['announcement_id'] }}, '{{ $db_status }}')">
        <i class="fas {{ $is_disabled ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
        {{ $is_disabled ? 'Enable' : 'Disable' }}
    </button>
    @endif

    <button type="button" class="btn btn-sm btn-delete"
    data-delete-url="{{ route('admin.requests.delete', ['id' => $reqId]) }}"
    data-title="{{ e($title) }}"
    onclick="deleteApprovalRequestOnly(event, this)">
    <i class="fas fa-trash"></i>
    </button>
  </div>

  @if($statusClass === 'rejected' && !empty($row->rejection_reason))
    <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f;">
      <strong>Rejected reason:</strong> {{ e($row->rejection_reason) }}
    </div>
  @endif

  @if($type === 'ANNOUNCEMENT_UPDATE' && $statusClass === 'rejected')
    <div style="margin-top:8px;padding:10px;border-radius:10px;background:#fff7d6;color:#6b4e00;">
      <strong>Note:</strong> This was an <b>edit request</b>. Since it was <b>rejected</b>, the live announcement stays <b>unchanged</b>.
    </div>
  @endif
</div>

@php
  $payload = json_decode($row->details ?? '{}', true) ?: [];

  $reqId = (int) $row->id;
  $type  = strtoupper((string)$row->type);
  $title = $payload['title'] ?? $row->title ?? 'Request';
  $content = $payload['content'] ?? '';
  $priority = strtoupper($payload['priority'] ?? 'LOW');

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
    <div>
      <h3 class="announcement-title" style="margin:0;">
        {{ e($title) }}
      </h3>

      <div style="margin-top:6px;">
        <span class="priority-badge priority-{{ strtolower($priority) }}">
          {{ ucfirst(strtolower($priority)) }} Priority
        </span>

        @if(!empty($db_status))
          <span class="status-badge status-{{ strtolower($db_status) }}">
            {{ ucfirst(strtolower($db_status)) }}
          </span>
        @endif
      </div>
    </div>
  </div>
</div>

  <p class="announcement-description">{{ e($content) }}</p>

  <div class="announcement-actions">
    <button class="btn btn-sm btn-primary"
      onclick="editAnnouncementRequest(
        {{ $reqId }},
        '{{ $type }}',
        {{ $targetId ? (int)$targetId : 0 }},
        '{{ addslashes($title) }}',
        '{{ addslashes($content) }}',
        '{{ $priority }}'
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
    data-delete-url="{{ url('/staff/requests/'.$reqId) }}"
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
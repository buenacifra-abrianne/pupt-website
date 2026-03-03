@php
  $payload = json_decode($row->details ?? '{}', true) ?: [];

  $reqId = (int) $row->id;
  $type  = strtoupper((string)$row->type);

  $typeLabel = match($type) {
    'NEWS_UPDATE' => 'Edit Request',
    'NEWS_DELETE' => 'Delete Request',
    'NEWS_CREATE' => 'Create Request',
    default => 'Request',
  };

  $title = $payload['title'] ?? $row->title ?? 'Request';
  $content = $payload['content'] ?? '';
  $category = $payload['category'] ?? '';
  $location = $payload['location'] ?? '';
  $imagePath = $payload['image_path'] ?? null;
$imageUrl = $imagePath ? asset('storage/' . ltrim($imagePath,'/')) : null;

  $targetNewsId = (int)($payload['news_id'] ?? 0);
  $reqStatus = strtolower(trim((string)$row->status));

  $searchHay = strtolower($title.' '.$content.' '.$category.' '.$location.' '.$reqStatus.' '.(($row->rejection_reason ?? '')));
@endphp

<div class="announcement-item"
     data-search="{{ e($searchHay) }}"
     style="margin-bottom: 14px;">

  <div class="announcement-header">
    <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
      <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <h3 class="announcement-title" style="margin:0;">{{ e($title) }}</h3>
        <div style="display:flex; gap:14px; font-size:13px; opacity:.8; margin-top:6px; flex-wrap:wrap;">

  @if($category)
    <span>
      <i class="fas fa-tag" style="margin-right:4px;"></i>
      {{ e($category) }}
    </span>
  @endif

  @if($location)
    <span>
      <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>
      {{ e($location) }}
    </span>
  @endif

</div>
      </div>

      <span style="font-size:12px;padding:6px 10px;border-radius:999px;background:#f2f3f5;color:#333;white-space:nowrap;">
        {{ $typeLabel }}
      </span>
    </div>
  </div>

  @if($imageUrl)
  <div style="margin: 10px 0;">
    <img src="{{ $imageUrl }}" alt="news image"
         style="width:100%; max-height:220px; object-fit:cover; border-radius:12px; border:1px solid rgba(0,0,0,.08);">
  </div>
@endif

  <p class="announcement-description">{{ e($content) }}</p>

  <div class="announcement-actions">
    <button class="btn btn-sm btn-primary"
      type="button"
      onclick="editNewsRequest(
      {{ $reqId }},
      '{{ $type }}',
      {{ $targetNewsId }},
      '{{ addslashes($title) }}',
      '{{ addslashes($content) }}',
      '{{ addslashes($category) }}',
      '{{ addslashes($location) }}',
      '{{ $imagePath ? addslashes($imagePath) : '' }}',
      '{{ $imageUrl ? addslashes($imageUrl) : '' }}'
    )">
      <i class="fas fa-edit"></i> Edit
    </button>

    <button type="button" class="btn btn-sm btn-delete"
      data-delete-url="{{ url('/staff/requests/'.$reqId) }}"
      data-title="{{ e($title) }}"
      onclick="deleteApprovalRequestOnly(event, this)">
      <i class="fas fa-trash"></i>
    </button>
  </div>

  @if($reqStatus === 'rejected' && !empty($row->rejection_reason))
    <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f;">
      <strong>Rejected reason:</strong> {{ e($row->rejection_reason) }}
    </div>
  @endif
</div>
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
  $link = $payload['link'] ?? '';
  $imagePath = $payload['image_path'] ?? null;
$imageUrl = $imagePath ? asset('storage/' . ltrim($imagePath,'/')) : null;

  $targetNewsId = (int)($payload['news_id'] ?? 0);
  $reqStatus = strtolower(trim((string)$row->status));

  $searchHay = strtolower($title.' '.$content.' '.$category.' '.$location.' '.$reqStatus.' '.(($row->rejection_reason ?? '')));
@endphp

<div class="news-card news-request-card"
     data-search="{{ e($searchHay) }}">

  <div class="news-image">
    @if($imageUrl)
      <img src="{{ $imageUrl }}" alt="news image" style="width:100%; height:150px; object-fit:cover;">
    @else
      <i class="fas fa-newspaper"></i>
    @endif
  </div>

  <div class="news-content">
    <div class="news-card-badges">
      @if($category)
        <span class="news-category">{{ e($category) }}</span>
      @endif
      <span class="news-flag-badge news-flag-badge-hidden">{{ $typeLabel }}</span>
      <span class="news-flag-badge {{ $reqStatus === 'rejected' ? 'news-flag-badge-hidden' : 'news-flag-badge-featured' }}">
        {{ ucfirst($reqStatus) }}
      </span>
    </div>

    <h3 class="news-title">{{ e($title) }}</h3>

    <div class="news-meta">
      <span><i class="fas fa-map-marker-alt"></i> {{ e($location ?: 'No location') }}</span>
    </div>

    <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($content) !!}</div>

    <div class="news-actions">
      <button class="btn btn-sm btn-primary"
        type="button"
        onclick="editNewsRequest(
          {{ $reqId }},
          {{ \Illuminate\Support\Js::from($type) }},
          {{ $targetNewsId }},
          {{ \Illuminate\Support\Js::from($title) }},
          {{ \Illuminate\Support\Js::from($content) }},
          {{ \Illuminate\Support\Js::from($category) }},
          {{ \Illuminate\Support\Js::from($location) }},
          {{ \Illuminate\Support\Js::from($link) }},
          {{ \Illuminate\Support\Js::from($imagePath) }},
          {{ \Illuminate\Support\Js::from($imageUrl) }}
        )">
        <i class="fas fa-edit"></i>
      </button>

      <button type="button" class="btn btn-sm btn-delete"
        data-delete-url="{{ route('staff.requests.delete', ['id' => $reqId]) }}"
        data-title="{{ e($title) }}"
        onclick="deleteApprovalRequestOnly(event, this)">
        <i class="fas fa-trash"></i>
      </button>

      <button class="btn btn-sm btn-view-icon"
        type="button"
        title="View"
        onclick='openReadMoreModal(@json($title), @json($content))'>
        <i class="fas fa-eye"></i>
      </button>
    </div>

    @if($reqStatus === 'rejected' && !empty($row->rejection_reason))
      <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f;">
        <strong>Rejected reason:</strong> {{ e($row->rejection_reason) }}
      </div>
    @endif
  </div>
</div>

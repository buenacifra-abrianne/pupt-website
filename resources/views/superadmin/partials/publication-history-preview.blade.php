<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $label }} — backup preview</title>
    <style>
        body { margin:0; padding:24px; background:#f8fafc; color:#1e293b; font:14px/1.6 Arial,sans-serif; }
        main { max-width:1000px; margin:auto; } h1 { color:#800000; font-size:24px; } h2 { font-size:20px; }
        article { border:1px solid #e2e8f0; border-radius:10px; padding:20px; margin:16px 0; background:#fff; overflow-wrap:anywhere; }
        article h3 { font-size:18px; margin:0 0 10px; } .meta { color:#475569; font-size:12px; }
        .images { display:flex; gap:12px; flex-wrap:wrap; } img { max-width:100%; max-height:260px; object-fit:contain; }
        a { color:#800000; } @media(max-width:480px) { body { padding:12px; } article { padding:14px; } }
    </style>
</head>
<body><main>
    <h1>{{ $label }}</h1>
    <p class="meta">Read-only content from the selected S3 database backup.</p>
    @foreach($tables as $table => $rows)
        @if(count($tables) > 1)
            <h2>{{ match($table) { 'news' => 'News', 'announcements' => 'Announcements', 'downloadables' => 'Campus Memorandum' } }}</h2>
        @endif
        @forelse($rows as $id => $row)
            <article>
                <h3>{{ $row['title'] ?? '' }}</h3>
                <p class="meta">{{ implode(' · ', array_filter(['Record #'.$id, $row['status'] ?? '', $row['priority'] ?? '', $row['date_published'] ?? $row['created_at'] ?? ''])) }}</p>
                @if(!empty($row['is_hidden_from_public'])) <p class="meta">Hidden from the public page</p> @endif
                <div>{!! \App\Support\RichText::sanitize($row['content'] ?? $row['description'] ?? '') !!}</div>
                @if($table === 'news')
                    <div class="images">
                        @foreach(\App\Support\PublicationBackupContent::newsImages($row) as $imagePath)
                            <img src="{{ \App\Support\NewsImage::url($imagePath) }}" alt="{{ $row['title'] ?? 'News image' }}" loading="lazy">
                        @endforeach
                    </div>
                @endif
                @if($table === 'downloadables')
                    @php
                        $fileUrl = \App\Support\DownloadableFile::url($row['file_path'] ?? '');
                        $safeFileUrl = preg_match('/^(?:https?:\/\/|\/)/i', (string) $fileUrl) ? $fileUrl : null;
                    @endphp
                    <p>{{ $row['original_filename'] ?? '' }}</p>
                    @if($safeFileUrl) <a href="{{ $safeFileUrl }}">Open attached memorandum</a> @endif
                @endif
            </article>
        @empty
            <p>No records in this backup.</p>
        @endforelse
    @endforeach
</main></body>
</html>

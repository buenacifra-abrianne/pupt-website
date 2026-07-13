
{!! json_encode(($studentsPreviewPages ?? ['overview' => ($studentsPreviewHtml ?? '')]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}

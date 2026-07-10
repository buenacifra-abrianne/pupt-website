
{!! json_encode(($academicsPreviewPages ?? ['overview' => ($academicsPreviewHtml ?? '')]), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}

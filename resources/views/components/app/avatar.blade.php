@props([
    'name' => 'User',
    'firstName' => '',
    'lastName' => '',
    'src' => '',
    'alt' => null,
    'imageId' => null,
    'fallbackId' => null,
    'imageClass' => '',
    'fallbackClass' => '',
    'imageStyle' => '',
    'fallbackStyle' => '',
    'loading' => null,
])

@php
    $resolvedUrl = \App\Support\Avatar::resolveUrl((string) $src);
    $initials = \App\Support\Avatar::initials((string) $name, (string) $firstName, (string) $lastName);
    $altText = trim((string) ($alt ?? $name));
    if ($altText === '') {
        $altText = 'User';
    }
@endphp

@if($resolvedUrl !== '')
    <img
        @if($imageId) id="{{ $imageId }}" @endif
        class="{{ $imageClass }}"
        src="{{ $resolvedUrl }}"
        alt="{{ e($altText) }}"
        @if($loading) loading="{{ $loading }}" @endif
        @if(trim($imageStyle) !== '') style="{{ $imageStyle }}" @endif
        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
    >
    <span
        @if($fallbackId) id="{{ $fallbackId }}" @endif
        class="{{ $fallbackClass }}"
        style="{{ $fallbackStyle }}display:none;"
    >{{ $initials }}</span>
@else
    <img
        @if($imageId) id="{{ $imageId }}" @endif
        class="{{ $imageClass }}"
        src=""
        alt="{{ e($altText) }}"
        @if($loading) loading="{{ $loading }}" @endif
        style="{{ $imageStyle }}display:none;"
    >
    <span
        @if($fallbackId) id="{{ $fallbackId }}" @endif
        class="{{ $fallbackClass }}"
        @if(trim($fallbackStyle) !== '') style="{{ $fallbackStyle }}" @endif
    >{{ $initials }}</span>
@endif

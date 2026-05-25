<?php

namespace App\Services\KnowledgeSync;

class FetchResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $finalUrl = null,
        public readonly ?string $contentType = null,
        public readonly ?string $body = null,
        public readonly ?string $error = null,
    ) {
    }

    public static function failed(string $error): self
    {
        return new self(false, null, null, null, $error);
    }
}

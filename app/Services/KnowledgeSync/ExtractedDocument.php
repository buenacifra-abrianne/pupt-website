<?php

namespace App\Services\KnowledgeSync;

class ExtractedDocument
{
    public function __construct(
        public readonly string $title,
        public readonly string $text,
    ) {
    }
}

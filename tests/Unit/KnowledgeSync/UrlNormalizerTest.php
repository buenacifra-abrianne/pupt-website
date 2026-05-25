<?php

namespace Tests\Unit\KnowledgeSync;

use App\Services\KnowledgeSync\UrlNormalizer;
use PHPUnit\Framework\TestCase;

class UrlNormalizerTest extends TestCase
{
    public function test_url_normalization_resolves_relative_and_removes_tracking_params(): void
    {
        $normalizer = new UrlNormalizer();

        $url = $normalizer->normalize('/docs/file.pdf?utm_source=x&id=5', 'https://site.test/base', ['utm_source']);

        $this->assertSame('https://site.test/docs/file.pdf?id=5', $url);
    }

    public function test_url_normalization_rejects_javascript_scheme(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertNull($normalizer->normalize('javascript:alert(1)', 'https://site.test'));
    }

    public function test_document_url_detection(): void
    {
        $normalizer = new UrlNormalizer();

        $this->assertTrue($normalizer->isDocumentUrl('https://site.test/files/a.docx'));
        $this->assertTrue($normalizer->isDocumentUrl('https://site.test/files/a.pdf'));
        $this->assertFalse($normalizer->isDocumentUrl('https://site.test/news'));
    }
}

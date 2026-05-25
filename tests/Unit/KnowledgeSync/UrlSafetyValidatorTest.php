<?php

namespace Tests\Unit\KnowledgeSync;

use App\Services\KnowledgeSync\UrlSafetyValidator;
use PHPUnit\Framework\TestCase;

class UrlSafetyValidatorTest extends TestCase
{
    public function test_ssrf_blocking_for_localhost_and_private_ranges(): void
    {
        $validator = new UrlSafetyValidator();

        $localhost = $validator->validate('http://localhost/resource');
        $privateIp = $validator->validate('http://10.0.0.5/admin');
        $metadataIp = $validator->validate('http://169.254.169.254/latest/meta-data');

        $this->assertFalse($localhost['allowed']);
        $this->assertFalse($privateIp['allowed']);
        $this->assertFalse($metadataIp['allowed']);
    }

    public function test_allows_public_http_url(): void
    {
        $validator = new UrlSafetyValidator();

        $result = $validator->validate('https://example.com/docs');

        $this->assertTrue($result['allowed']);
    }
}

<?php

namespace Tests\Unit\KnowledgeSync;

use App\Services\KnowledgeSync\LinkExtractor;
use PHPUnit\Framework\TestCase;

class LinkExtractorTest extends TestCase
{
    public function test_extracting_links_from_rendered_footer_html(): void
    {
        $html = '<footer><a href="https://example.org/service">Service</a><a href="/students">Students</a></footer>';

        $rows = (new LinkExtractor())->extractFromHtml($html, 'https://site.test');
        $urls = array_column($rows, 'url');

        $this->assertContains('https://example.org/service', $urls);
        $this->assertContains('/students', $urls);
    }

    public function test_extracting_links_from_static_template_source(): void
    {
        $template = '<a href="https://example.org/footer-link">Footer</a> <script>const nav = { to: "https://example.org/nav" };</script>';

        $urls = (new LinkExtractor())->extractFromText($template);

        $this->assertContains('https://example.org/footer-link', $urls);
        $this->assertContains('https://example.org/nav', $urls);
    }

    public function test_extracting_links_from_cms_rich_text(): void
    {
        $rich = '<p>Visit <a href="https://example.org/admissions">admissions</a></p>';
        $rows = (new LinkExtractor())->extractFromHtml($rich);

        $this->assertSame('https://example.org/admissions', $rows[0]['url']);
    }

    public function test_extracting_links_from_sitemap_xml(): void
    {
        $xml = '<?xml version="1.0"?><urlset><url><loc>https://site.test/about</loc></url><url><loc>https://site.test/services</loc></url></urlset>';

        $urls = (new LinkExtractor())->extractFromSitemapXml($xml);

        $this->assertSame(['https://site.test/about', 'https://site.test/services'], $urls);
    }
}

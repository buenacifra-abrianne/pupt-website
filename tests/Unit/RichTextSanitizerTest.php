<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_sanitize_preserves_bold_and_color_inline_styles(): void
    {
        $html = '<span style="font-weight: bold; color: rgb(127, 17, 19); position: fixed;">Styled text</span>';

        $result = RichText::sanitize($html);

        $this->assertStringContainsString('font-weight: bold', $result);
        $this->assertStringContainsString('color: rgb(127, 17, 19)', $result);
        $this->assertStringNotContainsString('position: fixed', $result);
        $this->assertStringContainsString('Styled text', $result);
    }

    public function test_sanitize_preserves_common_text_formatting_styles(): void
    {
        $html = '<span style="font-style: italic; text-decoration: underline line-through; font-weight: 700;">Formatted</span>';

        $result = RichText::sanitize($html);

        $this->assertStringContainsString('font-style: italic', $result);
        $this->assertStringContainsString('text-decoration: underline line-through', $result);
        $this->assertStringContainsString('font-weight: 700', $result);
    }
}

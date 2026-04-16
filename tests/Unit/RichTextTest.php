a<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_sanitize_preserves_inline_color_on_formatted_tags(): void
    {
        $result = RichText::sanitize('<strong style="color:#ff0000">Highlighted</strong>');

        $this->assertSame('<strong style="color: #FF0000">Highlighted</strong>', $result);
    }

    public function test_sanitize_converts_legacy_font_tags_to_safe_spans(): void
    {
        $result = RichText::sanitize('<font color="#00aa00"><b>Legacy color</b></font>');

        $this->assertSame('<span style="color: #00AA00"><b>Legacy color</b></span>', $result);
    }
}

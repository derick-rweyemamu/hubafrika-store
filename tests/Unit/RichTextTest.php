<?php

namespace Tests\Unit;

use App\Support\RichText;
use PHPUnit\Framework\TestCase;

class RichTextTest extends TestCase
{
    public function test_plain_text_and_blank_values_normalize_cleanly(): void
    {
        $this->assertNull(RichText::sanitize(null));
        $this->assertNull(RichText::sanitize("   \n\t "));
        $this->assertSame('Monocrystalline 24V & 48V solar panel', RichText::sanitize('  Monocrystalline 24V & 48V solar panel  '));
        $this->assertSame('5 < 10 & > 2', RichText::sanitize('5 < 10 & > 2'));
    }

    public function test_empty_editor_markup_normalizes_to_null(): void
    {
        $this->assertNull(RichText::sanitize('<p><br></p>'));
        $this->assertNull(RichText::sanitize('<p>&nbsp;</p><div><br></div>'));
        $this->assertSame('', RichText::toHtml('<p><br></p>'));
    }

    public function test_safe_formatting_and_utf8_characters_are_preserved_while_dangerous_markup_is_stripped(): void
    {
        $dirty = '<script>alert("xss")</script>'
            .'<h1 onclick="evil()">Solar Kit — Tanzania</h1>'
            .'<p style="color:red"><strong>High efficiency</strong> <em>hybrid</em> <u>system</u></p>'
            .'<ul><li>24V controller</li><li>MPPT tracking</li></ul>'
            .'<p><a href="javascript:alert(1)">Bad link</a> and '
            .'<a href="https://hubafrika.test/shop" target="_blank" onclick="steal()">Shop now</a></p>'
            .'<img src="x" onerror="alert(2)">';

        $clean = RichText::sanitize($dirty);

        $this->assertNotNull($clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert(', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('<img', $clean);
        $this->assertStringContainsString('<h2>Solar Kit — Tanzania</h2>', $clean);
        $this->assertStringContainsString('<strong>High efficiency</strong>', $clean);
        $this->assertStringContainsString('<em>hybrid</em>', $clean);
        $this->assertStringContainsString('<u>system</u>', $clean);
        $this->assertStringContainsString('<ul><li>24V controller</li><li>MPPT tracking</li></ul>', $clean);
        $this->assertStringContainsString('<p>Bad link and <a', $clean);
        $this->assertStringNotContainsString('<a href="javascript:', $clean);
        $this->assertStringContainsString(
            '<a href="https://hubafrika.test/shop" target="_blank" rel="noopener noreferrer">Shop now</a>',
            $clean,
        );
    }

    public function test_to_html_converts_legacy_plain_newlines_and_preserves_rich_html(): void
    {
        $this->assertSame(
            "Line one &amp; two<br>\nLine three",
            RichText::toHtml("Line one & two\nLine three"),
        );

        $this->assertSame(
            '<p>Formatted <strong>content</strong></p>',
            RichText::toHtml('<p>Formatted <strong>content</strong></p>'),
        );
    }

    public function test_plain_text_separates_blocks_and_limits_cleanly(): void
    {
        $html = '<h2>Battery Storage</h2><p>LiFePO4 <strong>48V</strong></p><ul><li>Long cycle life</li><li>Smart BMS</li></ul>';

        $this->assertSame(
            'Battery Storage LiFePO4 48V Long cycle life Smart BMS',
            RichText::plainText($html),
        );

        $this->assertSame(
            'Battery Storage...',
            RichText::plainText($html, 15),
        );
    }

    public function test_safe_url_checks_schemes(): void
    {
        $this->assertTrue(RichText::isSafeUrl('https://hubafrika.test/products'));
        $this->assertTrue(RichText::isSafeUrl('http://hubafrika.test'));
        $this->assertTrue(RichText::isSafeUrl('/shop'));
        $this->assertTrue(RichText::isSafeUrl('#specifications'));
        $this->assertTrue(RichText::isSafeUrl('mailto:info@hubafrika.test'));
        $this->assertTrue(RichText::isSafeUrl('tel:+255712345678'));

        $this->assertFalse(RichText::isSafeUrl('javascript:alert(1)'));
        $this->assertFalse(RichText::isSafeUrl('data:text/html,<script>alert(1)</script>'));
        $this->assertFalse(RichText::isSafeUrl('//evil.example/phish'));
        $this->assertFalse(RichText::isSafeUrl(''));
    }
}

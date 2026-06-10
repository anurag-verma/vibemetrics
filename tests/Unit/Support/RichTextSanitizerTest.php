<?php

namespace Tests\Unit\Support;

use App\Support\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_sanitizer_keeps_allowed_formatting_tags(): void
    {
        $html = '<p><strong>Bold</strong> and <em>italic</em> with <u>underline</u></p>';

        $sanitized = RichTextSanitizer::sanitize($html);

        $this->assertStringContainsString('<strong>Bold</strong>', $sanitized);
        $this->assertStringContainsString('<em>italic</em>', $sanitized);
        $this->assertStringContainsString('<u>underline</u>', $sanitized);
    }

    public function test_sanitizer_strips_script_tags(): void
    {
        $html = '<p>Hello</p><script>alert("xss")</script>';

        $sanitized = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringContainsString('Hello', $sanitized);
    }

    public function test_sanitizer_allows_safe_links_only(): void
    {
        $safe = RichTextSanitizer::sanitize('<a href="https://example.com">Status</a>');
        $unsafe = RichTextSanitizer::sanitize('<a href="javascript:alert(1)">Bad</a>');

        $this->assertStringContainsString('href="https://example.com"', $safe);
        $this->assertStringContainsString('rel="noopener noreferrer"', $safe);
        $this->assertStringContainsString('Bad', $unsafe);
        $this->assertStringNotContainsString('javascript:', $unsafe);
    }

    public function test_sanitizer_removes_script_content_entirely(): void
    {
        $sanitized = RichTextSanitizer::sanitize('<script>alert(1)</script>');

        $this->assertSame('', $sanitized);
    }

    public function test_plain_text_is_escaped_when_no_html_tags_present(): void
    {
        $sanitized = RichTextSanitizer::sanitize('Maintenance at 10 PM');

        $this->assertSame('Maintenance at 10 PM', $sanitized);
    }
}

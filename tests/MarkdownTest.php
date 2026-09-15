<?php

use PHPUnit\Framework\TestCase;

final class MarkdownTest extends TestCase
{
    public function testRendersMarkdown(): void
    {
        $this->assertSame("<p>A <strong>bold</strong> claim.</p>\n", markdown('A **bold** claim.'));
    }

    public function testEscapesRawHtml(): void
    {
        $this->assertStringNotContainsString('<script>', markdown('<script>alert(1)</script>'));
    }

    public function testDropsJavascriptLinks(): void
    {
        $this->assertStringNotContainsString('javascript:', markdown('[x](javascript:alert(1))'));
    }
}

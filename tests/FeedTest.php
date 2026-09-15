<?php

use PHPUnit\Framework\TestCase;

final class FeedTest extends TestCase
{
    /** @return array<int, array<string, mixed>> */
    private function posts(): array
    {
        return [[
            'slug' => 'hello',
            'title' => 'Hello & <welcome>',
            'body' => 'A **bold** claim.',
            'published_at' => '2020-01-02 03:04:05+00',
        ]];
    }

    private function feed(): SimpleXMLElement
    {
        $xml = simplexml_load_string(feed_xml($this->posts(), 'https://example.com', 'My Blog'));

        $this->assertNotFalse($xml, 'the feed is not well-formed XML');

        return $xml;
    }

    public function testDescribesTheChannel(): void
    {
        $this->assertSame('My Blog', (string) $this->feed()->channel->title);
    }

    public function testCarriesEachPostWithAnAbsoluteUrl(): void
    {
        $item = $this->feed()->channel->item;

        $this->assertSame('Hello & <welcome>', (string) $item->title);
        $this->assertSame('https://example.com/posts/hello', (string) $item->link);
        $this->assertSame(
            strtotime('2020-01-02 03:04:05+00'),
            strtotime((string) $item->pubDate),
            'pubDate names the same instant, whatever the server timezone'
        );
    }

    public function testDescriptionSurvivesAsMarkup(): void
    {
        $this->assertSame(
            "<p>A <strong>bold</strong> claim.</p>\n",
            (string) $this->feed()->channel->item->description
        );
    }
}

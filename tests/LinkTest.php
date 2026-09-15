<?php

use PHPUnit\Framework\TestCase;

/**
 * Runs against the database in DATABASE_DSN (cms_test, see phpunit.xml).
 * Each test runs in a transaction that is rolled back, so rows do not leak.
 */
final class LinkTest extends TestCase
{
    protected function setUp(): void
    {
        Db::conn()->beginTransaction();
    }

    protected function tearDown(): void
    {
        Db::conn()->rollBack();
    }

    public function testAllOrdersByPosition(): void
    {
        Link::create('Last', '/last', 20);
        Link::create('First', '/first', 10);

        $this->assertSame(['First', 'Last'], array_column(Link::all(), 'label'));
    }

    public function testDeleteRemovesALink(): void
    {
        Link::create('Gone', '/gone', 0);
        $id = (int) Db::conn()->query("SELECT id FROM links WHERE label = 'Gone'")->fetchColumn();

        Link::delete($id);

        $this->assertNotContains('Gone', array_column(Link::all(), 'label'));
    }

    public function testValidUrlAcceptsSiteRelativeAndHttpUrls(): void
    {
        $this->assertTrue(Link::validUrl('/'));
        $this->assertTrue(Link::validUrl('/about'));
        $this->assertTrue(Link::validUrl('http://example.com'));
        $this->assertTrue(Link::validUrl('https://example.com/feed'));
    }

    public function testValidUrlRejectsScriptAndOffSiteSchemes(): void
    {
        $this->assertFalse(Link::validUrl('javascript:alert(1)'));
        $this->assertFalse(Link::validUrl('data:text/html,x'));
        $this->assertFalse(Link::validUrl('//example.com'));
        $this->assertFalse(Link::validUrl('about'));
    }
}

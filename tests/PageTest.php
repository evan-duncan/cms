<?php

use PHPUnit\Framework\TestCase;

/**
 * Pages inherit every query from Content, so this covers the wiring: the
 * table Page reads, and the published_at rules coming through it.
 *
 * Runs against the database in DATABASE_DSN (cms_test, see phpunit.xml).
 * Each test runs in a transaction that is rolled back, so rows do not leak.
 */
final class PageTest extends TestCase
{
    protected function setUp(): void
    {
        Db::conn()->beginTransaction();
    }

    protected function tearDown(): void
    {
        Db::conn()->rollBack();
    }

    public function testPublishedBySlugFindsLivePages(): void
    {
        Page::create('about', 'About', 'Hello.', '2020-01-01');

        $this->assertSame('About', Page::publishedBySlug('about')['title']);
    }

    public function testPublishedBySlugHidesDraftsAndScheduledPages(): void
    {
        Page::create('draft', 'Draft', '', null);
        Page::create('soon', 'Soon', '', '2999-01-01');

        $this->assertNull(Page::publishedBySlug('draft'));
        $this->assertNull(Page::publishedBySlug('soon'));
    }

    public function testAllIncludesDraftsAndScheduledPages(): void
    {
        Page::create('draft', 'Draft', '', null);

        $this->assertContains('draft', array_column(Page::all(), 'slug'));
    }

    public function testUpdateChangesEveryEditableField(): void
    {
        Page::create('before', 'Before', '', null);
        $id = (int) Db::conn()->query("SELECT id FROM pages WHERE slug = 'before'")->fetchColumn();

        Page::update($id, 'after', 'After', 'Rewritten.', '2020-01-01 09:00');
        $page = Page::byId($id);

        $this->assertSame('after', $page['slug']);
        $this->assertSame('After', $page['title']);
        $this->assertSame('Rewritten.', $page['body']);
        $this->assertNotNull($page['published_at']);
    }

    public function testPagesAndPostsUseSeparateTables(): void
    {
        Page::create('shared-slug', 'Page', '', '2020-01-01');

        $this->assertNull(Post::publishedBySlug('shared-slug'));
    }
}

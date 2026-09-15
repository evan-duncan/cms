<?php

use PHPUnit\Framework\TestCase;

/**
 * Runs against the database in DATABASE_DSN (cms_test, see phpunit.xml).
 * Each test runs in a transaction that is rolled back, so rows do not leak.
 */
final class PostTest extends TestCase
{
    protected function setUp(): void
    {
        Db::conn()->beginTransaction();
    }

    protected function tearDown(): void
    {
        Db::conn()->rollBack();
    }

    private function insert(string $slug, string $title, ?string $publishedAt): void
    {
        Db::conn()
            ->prepare('INSERT INTO posts (slug, title, body, published_at) VALUES (:slug, :title, :body, :published_at)')
            ->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':body' => 'body',
                ':published_at' => $publishedAt,
            ]);
    }

    public function testPublishedExcludesDraftsAndFuturePosts(): void
    {
        $this->insert('live', 'Live', '2020-01-01');
        $this->insert('draft', 'Draft', null);
        $this->insert('scheduled', 'Scheduled', '2999-01-01');

        $slugs = array_column(Post::published(), 'slug');

        $this->assertContains('live', $slugs);
        $this->assertNotContains('draft', $slugs);
        $this->assertNotContains('scheduled', $slugs);
    }

    public function testPublishedIsNewestFirst(): void
    {
        $this->insert('older', 'Older', '2020-01-01');
        $this->insert('newer', 'Newer', '2021-01-01');

        $slugs = array_column(Post::published(), 'slug');

        $this->assertLessThan(array_search('older', $slugs, true), array_search('newer', $slugs, true));
    }

    public function testBySlugReturnsNullWhenMissing(): void
    {
        $this->assertNull(Post::bySlug('does-not-exist'));
    }

    public function testBySlugFindsDrafts(): void
    {
        $this->insert('draft', 'Draft', null);

        $this->assertSame('Draft', Post::bySlug('draft')['title']);
    }
}

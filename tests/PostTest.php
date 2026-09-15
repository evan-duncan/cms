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

    private function insert(string $slug, string $title, ?string $publishedAt): int
    {
        $stmt = Db::conn()->prepare(
            'INSERT INTO posts (slug, title, body, published_at)
             VALUES (:slug, :title, :body, :published_at)
             RETURNING id'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':body' => 'body',
            ':published_at' => $publishedAt,
        ]);

        return (int) $stmt->fetchColumn();
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

    public function testPublishedBySlugFindsLivePosts(): void
    {
        $this->insert('live', 'Live', '2020-01-01');

        $this->assertSame('Live', Post::publishedBySlug('live')['title']);
    }

    public function testPublishedBySlugHidesDrafts(): void
    {
        $this->insert('draft', 'Draft', null);

        $this->assertNull(Post::publishedBySlug('draft'));
    }

    public function testPublishedBySlugHidesScheduledPosts(): void
    {
        $this->insert('scheduled', 'Scheduled', '2999-01-01');

        $this->assertNull(Post::publishedBySlug('scheduled'));
    }

    public function testPublishedBySlugReturnsNullWhenMissing(): void
    {
        $this->assertNull(Post::publishedBySlug('does-not-exist'));
    }

    public function testAllIncludesDraftsAndScheduledPosts(): void
    {
        $this->insert('live', 'Live', '2020-01-01');
        $this->insert('draft', 'Draft', null);
        $this->insert('scheduled', 'Scheduled', '2999-01-01');

        $slugs = array_column(Post::all(), 'slug');

        $this->assertContains('live', $slugs);
        $this->assertContains('draft', $slugs);
        $this->assertContains('scheduled', $slugs);
    }

    public function testCreateStoresADraftThatByIdCanRead(): void
    {
        Post::create('new-post', 'New Post', 'Some body.', null);

        $id = (int) Db::conn()->query("SELECT id FROM posts WHERE slug = 'new-post'")->fetchColumn();
        $post = Post::byId($id);

        $this->assertSame('New Post', $post['title']);
        $this->assertSame('Some body.', $post['body']);
        $this->assertNull($post['published_at']);
    }

    public function testUpdateChangesEveryEditableField(): void
    {
        $id = $this->insert('before', 'Before', null);

        Post::update($id, 'after', 'After', 'Rewritten.', '2020-01-01 09:00');

        $post = Post::byId($id);

        $this->assertSame('after', $post['slug']);
        $this->assertSame('After', $post['title']);
        $this->assertSame('Rewritten.', $post['body']);
        $this->assertNotNull($post['published_at']);
    }

    public function testDeleteRemovesThePost(): void
    {
        $id = $this->insert('gone', 'Gone', '2020-01-01');

        Post::delete($id);

        $this->assertNull(Post::byId($id));
    }

    public function testByIdReturnsNullWhenMissing(): void
    {
        $this->assertNull(Post::byId(0));
    }

    public function testSlugifyDerivesASlugFromATitle(): void
    {
        $this->assertSame('hello-there-world', Post::slugify('Hello, There  World!'));
    }
}

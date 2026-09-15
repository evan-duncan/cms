<?php

class Post
{
    /** @return array<int, array<string, mixed>> */
    public static function published(int $limit = 20): array
    {
        $stmt = Db::conn()->prepare(
            'SELECT slug, title, body, published_at
             FROM posts
             WHERE published_at IS NOT NULL AND published_at <= now()
             ORDER BY published_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function publishedBySlug(string $slug): ?array
    {
        $stmt = Db::conn()->prepare(
            'SELECT slug, title, body, published_at
             FROM posts
             WHERE slug = :slug AND published_at IS NOT NULL AND published_at <= now()'
        );
        $stmt->execute([':slug' => $slug]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Every post, drafts and scheduled included. Admin only.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return Db::conn()->query(
            'SELECT id, slug, title, body, published_at
             FROM posts
             ORDER BY COALESCE(published_at, created_at) DESC'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function byId(int $id): ?array
    {
        $stmt = Db::conn()->prepare(
            'SELECT id, slug, title, body, published_at FROM posts WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(string $slug, string $title, string $body, ?string $publishedAt): void
    {
        Db::conn()->prepare(
            'INSERT INTO posts (slug, title, body, published_at)
             VALUES (:slug, :title, :body, :published_at)'
        )->execute([
            ':slug' => $slug,
            ':title' => $title,
            ':body' => $body,
            ':published_at' => $publishedAt,
        ]);
    }

    public static function update(int $id, string $slug, string $title, string $body, ?string $publishedAt): void
    {
        Db::conn()->prepare(
            'UPDATE posts
             SET slug = :slug, title = :title, body = :body, published_at = :published_at
             WHERE id = :id'
        )->execute([
            ':id' => $id,
            ':slug' => $slug,
            ':title' => $title,
            ':body' => $body,
            ':published_at' => $publishedAt,
        ]);
    }

    public static function slugify(string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    }
}

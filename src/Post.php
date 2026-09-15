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
    public static function bySlug(string $slug): ?array
    {
        $stmt = Db::conn()->prepare(
            'SELECT slug, title, body, published_at FROM posts WHERE slug = :slug'
        );
        $stmt->execute([':slug' => $slug]);

        return $stmt->fetch() ?: null;
    }
}

<?php

/**
 * Shared queries for slug-addressed, Markdown-bodied content. Subclasses
 * name their table in TABLE; state lives entirely in published_at, so the
 * publish rules are the same for every subclass.
 *
 * TABLE is a class constant, never a value from a request, so the table
 * name is the one thing here that is interpolated rather than bound.
 */
abstract class Content
{
    protected const TABLE = '';

    /** @return array<int, array<string, mixed>> */
    public static function published(int $limit = 20): array
    {
        $stmt = Db::conn()->prepare(
            'SELECT slug, title, body, published_at
             FROM ' . static::TABLE . '
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
             FROM ' . static::TABLE . '
             WHERE slug = :slug AND published_at IS NOT NULL AND published_at <= now()'
        );
        $stmt->execute([':slug' => $slug]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Everything, drafts and scheduled included. Admin only.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return Db::conn()->query(
            'SELECT id, slug, title, body, published_at
             FROM ' . static::TABLE . '
             ORDER BY COALESCE(published_at, created_at) DESC'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public static function byId(int $id): ?array
    {
        $stmt = Db::conn()->prepare(
            'SELECT id, slug, title, body, published_at FROM ' . static::TABLE . ' WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function create(string $slug, string $title, string $body, ?string $publishedAt): void
    {
        Db::conn()->prepare(
            'INSERT INTO ' . static::TABLE . ' (slug, title, body, published_at)
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
            'UPDATE ' . static::TABLE . '
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

    public static function delete(int $id): void
    {
        Db::conn()->prepare('DELETE FROM ' . static::TABLE . ' WHERE id = :id')->execute([':id' => $id]);
    }

    public static function slugify(string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    }
}

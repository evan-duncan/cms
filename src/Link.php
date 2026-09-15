<?php

/**
 * Footer links. A link is a label and a URL: editing one means deleting it
 * and adding it again.
 */
class Link
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Db::conn()->query(
            'SELECT id, label, url, position FROM links ORDER BY position, id'
        )->fetchAll();
    }

    public static function create(string $label, string $url, int $position): void
    {
        Db::conn()->prepare(
            'INSERT INTO links (label, url, position) VALUES (:label, :url, :position)'
        )->execute([':label' => $label, ':url' => $url, ':position' => $position]);
    }

    public static function delete(int $id): void
    {
        Db::conn()->prepare('DELETE FROM links WHERE id = :id')->execute([':id' => $id]);
    }

    /**
     * Keeps javascript: and data: URLs out of the footer's href. Site-relative
     * paths and plain http(s) URLs only; "//host" is protocol-relative and
     * leaves the site, so it is not a site-relative path.
     */
    public static function validUrl(string $url): bool
    {
        return (bool) preg_match('#^(/(?!/)|https?://)#', $url);
    }
}

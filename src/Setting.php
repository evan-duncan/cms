<?php

class Setting
{
    /** @var array<string, string> Per-request cache; the layout reads site_name twice. */
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string
    {
        if (!isset(self::$cache[$key])) {
            $stmt = Db::conn()->prepare('SELECT value FROM settings WHERE key = :key');
            $stmt->execute([':key' => $key]);
            $value = $stmt->fetchColumn();
            self::$cache[$key] = $value === false ? $default : (string) $value;
        }

        return self::$cache[$key];
    }

    public static function set(string $key, string $value): void
    {
        Db::conn()->prepare(
            'INSERT INTO settings (key, value) VALUES (:key, :value)
             ON CONFLICT (key) DO UPDATE SET value = excluded.value'
        )->execute([':key' => $key, ':value' => $value]);

        self::$cache[$key] = $value;
    }
}

<?php

class Db
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $dsn = getenv('DATABASE_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=cms';
            self::$pdo = new PDO(
                $dsn,
                getenv('DATABASE_USER') ?: 'cms',
                getenv('DATABASE_PASSWORD') ?: 'cms',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}

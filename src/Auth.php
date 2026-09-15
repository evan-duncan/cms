<?php

class Auth
{
    private const SESSION_KEY = 'user_id';

    /**
     * Looks up a user by email and checks the password against the stored hash.
     * Touches no session state, so it is usable from tests and the CLI.
     */
    public static function verify(string $email, string $password): ?int
    {
        $stmt = Db::conn()->prepare('SELECT id, password_hash FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return (int) $user['id'];
    }

    /**
     * Started lazily rather than in the front controller, so readers of the
     * public blog never receive a session cookie.
     */
    private static function session(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
            session_start();
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        $id = self::verify($email, $password);

        if ($id === null) {
            return false;
        }

        self::session();
        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = $id;

        return true;
    }

    public static function check(): bool
    {
        // No session cookie means no login, so public readers never start a session.
        if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE[session_name()])) {
            return false;
        }

        self::session();

        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function logout(): void
    {
        self::session();
        $_SESSION = [];
        session_destroy();
    }

    /** Route middleware: sends a logged-out visitor to the login form. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/admin/login');
        }
    }

    public static function csrfToken(): string
    {
        self::session();

        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    /** Route middleware: rejects a POST whose form token does not match the session. */
    public static function requireCsrf(): void
    {
        self::session();
        $token = $_POST['csrf'] ?? null;

        if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(403);
            exit;
        }
    }
}

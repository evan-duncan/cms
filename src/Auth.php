<?php

class Auth
{
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
}

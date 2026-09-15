<?php

use PHPUnit\Framework\TestCase;

/**
 * Runs against the database in DATABASE_DSN (cms_test, see phpunit.xml).
 * Each test runs in a transaction that is rolled back, so rows do not leak.
 */
final class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        Db::conn()->beginTransaction();
    }

    protected function tearDown(): void
    {
        Db::conn()->rollBack();
    }

    private function createUser(string $email, string $password): void
    {
        Db::conn()
            ->prepare('INSERT INTO users (email, password_hash) VALUES (:email, :hash)')
            ->execute([':email' => $email, ':hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public function testVerifyReturnsUserIdForCorrectPassword(): void
    {
        $this->createUser('author@example.com', 'correct horse battery staple');

        $this->assertIsInt(Auth::verify('author@example.com', 'correct horse battery staple'));
    }

    public function testVerifyRejectsWrongPassword(): void
    {
        $this->createUser('author@example.com', 'correct horse battery staple');

        $this->assertNull(Auth::verify('author@example.com', 'wrong password'));
    }

    public function testVerifyRejectsUnknownEmail(): void
    {
        $this->assertNull(Auth::verify('nobody@example.com', 'whatever'));
    }
}

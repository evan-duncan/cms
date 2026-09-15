<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function router(): Router
    {
        $router = new Router();
        $noop = function (array $params = []): void {};

        $router->add('GET', '/', $noop);
        $router->add('GET', '/posts/{slug}', $noop);
        $router->add('POST', '/posts', $noop);

        return $router;
    }

    public function testMatchesRoot(): void
    {
        $this->assertNotNull($this->router()->match('GET', '/'));
    }

    public function testCapturesPlaceholder(): void
    {
        [, $params] = $this->router()->match('GET', '/posts/hello-world');

        $this->assertSame(['slug' => 'hello-world'], $params);
    }

    public function testIgnoresTrailingSlash(): void
    {
        $this->assertNotNull($this->router()->match('GET', '/posts/hello-world/'));
    }

    public function testMethodIsCaseInsensitive(): void
    {
        $this->assertNotNull($this->router()->match('get', '/'));
    }

    #[DataProvider('misses')]
    public function testMisses(string $method, string $path): void
    {
        $this->assertNull($this->router()->match($method, $path));
    }

    /** @return array<string, array{string, string}> */
    public static function misses(): array
    {
        return [
            'placeholder spans one segment' => ['GET', '/posts/a/b'],
            'method must match' => ['POST', '/posts/hello'],
            'unknown path' => ['GET', '/nope'],
        ];
    }
}

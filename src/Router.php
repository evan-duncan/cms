<?php

/**
 * Pattern syntax: literal path segments plus {name} placeholders,
 * e.g. "/posts/{slug}". A placeholder matches one segment.
 *
 * A route may carry middleware: callables run in order, before the handler,
 * each receiving the matched parameters. Middleware that ends the request
 * (a redirect, a 403) simply exits, so the handler never runs.
 */
class Router
{
    /** @var array<int, array{method: string, regex: string, names: string[], handler: callable, middleware: callable[]}> */
    private array $routes = [];

    /** @param callable[] $middleware */
    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $names = [];
        $regex = preg_replace_callback(
            '#\{(\w+)\}#',
            function (array $m) use (&$names): string {
                $names[] = $m[1];
                return '([^/]+)';
            },
            $pattern
        );

        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => '#^' . $regex . '$#',
            'names' => $names,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * @return array{0: callable, 1: array<string, string>}|null
     */
    public function match(string $method, string $path): ?array
    {
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            if (!preg_match($route['regex'], $path, $m)) {
                continue;
            }
            array_shift($m);
            return [self::pipeline($route), array_combine($route['names'], $m)];
        }

        return null;
    }

    /**
     * Wraps a route's middleware around its handler, so callers get one
     * callable and do not have to know whether a route has middleware.
     *
     * @param array{handler: callable, middleware: callable[]} $route
     */
    private static function pipeline(array $route): callable
    {
        if ($route['middleware'] === []) {
            return $route['handler'];
        }

        return function (array $params) use ($route): void {
            foreach ($route['middleware'] as $middleware) {
                $middleware($params);
            }

            ($route['handler'])($params);
        };
    }

    public function dispatch(string $method, string $path): void
    {
        $matched = $this->match($method, $path);

        if ($matched === null) {
            http_response_code(404);
            render('404');
            return;
        }

        [$handler, $params] = $matched;
        $handler($params);
    }
}

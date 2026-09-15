<?php

/**
 * Pattern syntax: literal path segments plus {name} placeholders,
 * e.g. "/posts/{slug}". A placeholder matches one segment.
 */
class Router
{
    /** @var array<int, array{method: string, regex: string, names: string[], handler: callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
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
            return [$route['handler'], array_combine($route['names'], $m)];
        }

        return null;
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

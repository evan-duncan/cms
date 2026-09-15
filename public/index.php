<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$router->add('GET', '/', function (): void {
    render('posts/index', ['posts' => Post::published()]);
});

$router->add('GET', '/posts/{slug}', function (array $params): void {
    $post = Post::bySlug($params['slug']);

    if ($post === null) {
        http_response_code(404);
        render('404');
        return;
    }

    render('posts/show', ['post' => $post]);
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

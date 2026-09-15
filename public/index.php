<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$router->add('GET', '/', function (): void {
    render('posts/index', ['posts' => Post::published()]);
});

$router->add('GET', '/posts/{slug}', function (array $params): void {
    $post = Post::publishedBySlug($params['slug']);

    if ($post === null) {
        http_response_code(404);
        render('404');
        return;
    }

    render('posts/show', ['post' => $post]);
});

$router->add('GET', '/admin/login', function (): void {
    render('admin/login', ['csrf' => Auth::csrfToken(), 'error' => null]);
});

$router->add('POST', '/admin/login', function (): void {
    Auth::verifyCsrf($_POST['csrf'] ?? null);

    if (!Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        http_response_code(422);
        render('admin/login', ['csrf' => Auth::csrfToken(), 'error' => 'Wrong email or password.']);
        return;
    }

    redirect('/admin');
});

$router->add('POST', '/admin/logout', function (): void {
    Auth::verifyCsrf($_POST['csrf'] ?? null);
    Auth::logout();
    redirect('/');
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

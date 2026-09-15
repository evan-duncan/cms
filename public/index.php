<?php

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Reads a submitted post from $_POST. An empty slug is derived from the
 * title; an empty date means draft.
 *
 * @return array<string, mixed>
 */
function post_input(?int $id): array
{
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $publishedAt = trim($_POST['published_at'] ?? '');

    return [
        'id' => $id,
        'title' => $title,
        'slug' => $slug !== '' ? $slug : Post::slugify($title),
        'body' => $_POST['body'] ?? '',
        'published_at' => $publishedAt !== '' ? $publishedAt : null,
    ];
}

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

$router->add('GET', '/admin', function (): void {
    render('admin/index', ['posts' => Post::all()]);
}, [Auth::requireLogin(...)]);

$router->add('GET', '/admin/login', function (): void {
    render('admin/login', ['csrf' => Auth::csrfToken(), 'error' => null]);
});

$router->add('POST', '/admin/login', function (): void {
    if (!Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        http_response_code(422);
        render('admin/login', ['csrf' => Auth::csrfToken(), 'error' => 'Wrong email or password.']);
        return;
    }

    redirect('/admin');
}, [Auth::requireCsrf(...)]);

$router->add('POST', '/admin/logout', function (): void {
    Auth::logout();
    redirect('/');
}, [Auth::requireCsrf(...)]);

$router->add('GET', '/admin/posts/new', function (): void {
    render('admin/form', [
        'post' => ['id' => null, 'slug' => '', 'title' => '', 'body' => '', 'published_at' => null],
        'csrf' => Auth::csrfToken(),
        'error' => null,
    ]);
}, [Auth::requireLogin(...)]);

$router->add('POST', '/admin/posts', function (): void {
    $post = post_input(null);

    if ($post['title'] === '') {
        http_response_code(422);
        render('admin/form', ['post' => $post, 'csrf' => Auth::csrfToken(), 'error' => 'Title is required.']);
        return;
    }

    try {
        Post::create($post['slug'], $post['title'], $post['body'], $post['published_at']);
    } catch (PDOException $e) {
        if ($e->getCode() !== '23505') {
            throw $e;
        }
        http_response_code(422);
        render('admin/form', ['post' => $post, 'csrf' => Auth::csrfToken(), 'error' => 'That slug is already taken.']);
        return;
    }

    redirect('/admin');
}, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

$router->add('GET', '/admin/posts/{id}/edit', function (array $params): void {

    $post = Post::byId((int) $params['id']);

    if ($post === null) {
        http_response_code(404);
        render('404');
        return;
    }

    render('admin/form', ['post' => $post, 'csrf' => Auth::csrfToken(), 'error' => null]);
}, [Auth::requireLogin(...)]);

$router->add('POST', '/admin/posts/{id}', function (array $params): void {
    $post = post_input((int) $params['id']);

    if ($post['title'] === '') {
        http_response_code(422);
        render('admin/form', ['post' => $post, 'csrf' => Auth::csrfToken(), 'error' => 'Title is required.']);
        return;
    }

    try {
        Post::update($post['id'], $post['slug'], $post['title'], $post['body'], $post['published_at']);
    } catch (PDOException $e) {
        if ($e->getCode() !== '23505') {
            throw $e;
        }
        http_response_code(422);
        render('admin/form', ['post' => $post, 'csrf' => Auth::csrfToken(), 'error' => 'That slug is already taken.']);
        return;
    }

    redirect('/admin');
}, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

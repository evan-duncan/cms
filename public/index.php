<?php

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Reads submitted content from $_POST. An empty slug is derived from the
 * title; an empty date means draft.
 *
 * @return array<string, mixed>
 */
function content_input(?int $id): array
{
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $publishedAt = trim($_POST['published_at'] ?? '');

    return [
        'id' => $id,
        'title' => $title,
        'slug' => $slug !== '' ? $slug : Content::slugify($title),
        'body' => $_POST['body'] ?? '',
        'published_at' => $publishedAt !== '' ? $publishedAt : null,
    ];
}

/**
 * Creates ($id null) or updates one piece of content, re-rendering the editor
 * with an error when the input is rejected.
 *
 * @param class-string<Content> $class
 */
function save_content(string $type, string $class, ?int $id): void
{
    $content = content_input($id);

    $reject = function (string $error) use ($type, $content): void {
        http_response_code(422);
        render('admin/form', [
            'type' => $type,
            'content' => $content,
            'csrf' => Auth::csrfToken(),
            'error' => $error,
        ]);
    };

    if ($content['title'] === '') {
        $reject('Title is required.');
        return;
    }

    try {
        if ($id === null) {
            $class::create($content['slug'], $content['title'], $content['body'], $content['published_at']);
        } else {
            $class::update($id, $content['slug'], $content['title'], $content['body'], $content['published_at']);
        }
    } catch (PDOException $e) {
        if ($e->getCode() !== '23505') {
            throw $e;
        }
        $reject('That slug is already taken.');
        return;
    }

    redirect('/admin');
}

/** @param array<string, mixed> $data */
function render_admin_index(array $data = []): void
{
    render('admin/index', $data + [
        'posts' => Post::all(),
        'pages' => Page::all(),
        'links' => Link::all(),
        'error' => null,
    ]);
}

$router = new Router();

$router->add('GET', '/', function (): void {
    $perPage = 20;
    $page = max(1, (int) ($_GET['page'] ?? 1));

    // Asking for one row past the page answers "is there an older page?"
    // without a second COUNT query.
    $posts = Post::published($perPage + 1, ($page - 1) * $perPage);

    render('posts/index', [
        'posts' => array_slice($posts, 0, $perPage),
        'page' => $page,
        'hasOlder' => count($posts) > $perPage,
    ]);
});

$router->add('GET', '/feed.xml', function (): void {
    header('Content-Type: application/rss+xml; charset=utf-8');

    echo feed_xml(Post::published(), site_url(), Setting::get('site_name', 'cms'));
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
    render_admin_index();
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

$router->add('POST', '/admin/settings', function (): void {
    $name = trim($_POST['site_name'] ?? '');

    if ($name !== '') {
        Setting::set('site_name', mb_substr($name, 0, 100));
    }

    redirect('/admin');
}, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

$router->add('POST', '/admin/links', function (): void {
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');

    if ($label === '' || !Link::validUrl($url)) {
        http_response_code(422);
        render_admin_index(['error' => 'A link needs a label and a URL starting with /, http:// or https://.']);
        return;
    }

    Link::create(mb_substr($label, 0, 100), $url, (int) ($_POST['position'] ?? 0));

    redirect('/admin');
}, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

$router->add('POST', '/admin/links/{id}/delete', function (array $params): void {
    Link::delete((int) $params['id']);

    redirect('/admin');
}, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

$router->add('POST', '/admin/logout', function (): void {
    Auth::logout();
    redirect('/');
}, [Auth::requireCsrf(...)]);

/**
 * Posts and pages differ only in their table and their URL prefix, so the
 * editor routes are registered once for each.
 */
foreach (['posts' => Post::class, 'pages' => Page::class] as $type => $class) {
    $router->add('GET', "/admin/{$type}/new", function () use ($type): void {
        render('admin/form', [
            'type' => $type,
            'content' => ['id' => null, 'slug' => '', 'title' => '', 'body' => '', 'published_at' => null],
            'csrf' => Auth::csrfToken(),
            'error' => null,
        ]);
    }, [Auth::requireLogin(...)]);

    $router->add('POST', "/admin/{$type}", function () use ($type, $class): void {
        save_content($type, $class, null);
    }, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

    $router->add('GET', "/admin/{$type}/{id}/edit", function (array $params) use ($type, $class): void {
        $content = $class::byId((int) $params['id']);

        if ($content === null) {
            http_response_code(404);
            render('404');
            return;
        }

        render('admin/form', [
            'type' => $type,
            'content' => $content,
            'csrf' => Auth::csrfToken(),
            'error' => null,
        ]);
    }, [Auth::requireLogin(...)]);

    $router->add('POST', "/admin/{$type}/{id}", function (array $params) use ($type, $class): void {
        save_content($type, $class, (int) $params['id']);
    }, [Auth::requireLogin(...), Auth::requireCsrf(...)]);

    $router->add('POST', "/admin/{$type}/{id}/delete", function (array $params) use ($class): void {
        $class::delete((int) $params['id']);

        redirect('/admin');
    }, [Auth::requireLogin(...), Auth::requireCsrf(...)]);
}

/**
 * Pages live at the site root, so this route matches any single segment and
 * is registered last: every literal route above wins first.
 */
$router->add('GET', '/{slug}', function (array $params): void {
    $page = Page::publishedBySlug($params['slug']);

    if ($page === null) {
        http_response_code(404);
        render('404');
        return;
    }

    render('pages/show', ['page' => $page]);
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

<?php

/**
 * Renders templates/<name>.php inside templates/layout.php.
 * $data keys become local variables in the template.
 *
 * @param array<string, mixed> $data
 */
function render(string $name, array $data = []): void
{
    $template = dirname(__DIR__) . '/templates/' . $name . '.php';

    extract($data, EXTR_SKIP);
    ob_start();
    require $template;
    $content = ob_get_clean();

    require dirname(__DIR__) . '/templates/layout.php';
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    http_response_code(302);
    exit;
}

/**
 * Formats a database timestamp for <input type="datetime-local">, which
 * accepts only "Y-m-d\TH:i". An empty value means the post is a draft.
 *
 * ponytail: server timezone only; add a per-user timezone if a second author appears.
 */
function datetime_local(?string $timestamp): string
{
    return $timestamp === null ? '' : date('Y-m-d\TH:i', strtotime($timestamp));
}

/** Formats a database timestamp for display. */
function post_date(?string $timestamp): string
{
    return $timestamp === null ? '' : date('F j, Y', strtotime($timestamp));
}

/**
 * The site's absolute URL, without a trailing slash, taken from the request
 * that is asking for it.
 *
 * ponytail: reads the request host, so a feed built off-request (a cron job,
 * a CLI export) has nothing to read; add a site_url setting if that happens.
 */
function site_url(): string
{
    $scheme = ($_SERVER['HTTPS'] ?? '') !== '' ? 'https' : 'http';

    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

/**
 * Renders published posts as an RSS 2.0 document. Bodies go out as rendered
 * Markdown: e() escapes that HTML so the reader's XML parser hands it back
 * as markup, while raw HTML markdown() already escaped stays text.
 *
 * @param array<int, array<string, mixed>> $posts
 */
function feed_xml(array $posts, string $base, string $siteName): string
{
    $items = '';

    foreach ($posts as $post) {
        $url = $base . '/posts/' . $post['slug'];

        $items .= "  <item>\n"
            . '    <title>' . e($post['title']) . "</title>\n"
            . '    <link>' . e($url) . "</link>\n"
            . '    <guid isPermaLink="true">' . e($url) . "</guid>\n"
            . '    <pubDate>' . date(DATE_RSS, strtotime($post['published_at'])) . "</pubDate>\n"
            . '    <description>' . e(markdown($post['body'])) . "</description>\n"
            . "  </item>\n";
    }

    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n"
        . "<channel>\n"
        . '  <title>' . e($siteName) . "</title>\n"
        . '  <link>' . e($base) . "/</link>\n"
        . '  <description>' . e($siteName) . "</description>\n"
        . '  <atom:link href="' . e($base . '/feed.xml') . '" rel="self" type="application/rss+xml"/>' . "\n"
        . $items
        . "</channel>\n"
        . "</rss>\n";
}

/**
 * Renders a post body as HTML. Raw HTML in the body is escaped rather than
 * passed through, so a body needs no e() around it.
 *
 * ponytail: converted per request; cache into a column if rendering shows up
 * in a profile.
 */
function markdown(string $body): string
{
    static $converter = null;

    $converter ??= new League\CommonMark\CommonMarkConverter([
        'html_input' => 'escape',
        'allow_unsafe_links' => false,
    ]);

    return (string) $converter->convert($body);
}

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

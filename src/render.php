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

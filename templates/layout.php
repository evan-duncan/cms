<?php $siteName = Setting::get('site_name', 'cms'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(isset($title) ? $title . ' — ' . $siteName : $siteName) ?></title>
    <link rel="stylesheet" href="/pico.css">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<header>
    <a href="/"><?= e($siteName) ?></a>
    <?php if (Auth::check()): ?><a href="/admin">Admin</a><?php endif; ?>
</header>
<main><?= $content ?></main>
<?php $links = Link::all(); ?>
<?php if ($links !== []): ?>
    <footer>
        <nav>
            <?php foreach ($links as $link): ?>
                <a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a>
            <?php endforeach; ?>
        </nav>
    </footer>
<?php endif; ?>
</body>
</html>

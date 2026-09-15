<article>
    <h1><?= e($post['title']) ?></h1>
    <time datetime="<?= e($post['published_at']) ?>"><?= e($post['published_at']) ?></time>
    <div><?= markdown($post['body']) ?></div>
</article>

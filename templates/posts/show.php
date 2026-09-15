<article>
    <h1><?= e($post['title']) ?></h1>
    <time datetime="<?= e($post['published_at']) ?>"><?= e($post['published_at']) ?></time>
    <div><?= nl2br(e($post['body'])) ?></div>
</article>

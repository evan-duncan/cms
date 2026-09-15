<?php if ($page > 1) { $title = 'Page ' . $page; } ?>
<h1>Posts</h1>
<ul class="feed">
    <?php foreach ($posts as $post): ?>
        <li>
            <a href="/posts/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a>
            <time datetime="<?= e($post['published_at']) ?>"><?= e(post_date($post['published_at'])) ?></time>
        </li>
    <?php endforeach; ?>
</ul>
<?php if ($page > 1 || $hasOlder): ?>
    <nav class="pager">
        <span><?php if ($page > 1): ?><a href="/?page=<?= e((string) ($page - 1)) ?>" rel="prev">Newer posts</a><?php endif; ?></span>
        <span><?php if ($hasOlder): ?><a href="/?page=<?= e((string) ($page + 1)) ?>" rel="next">Older posts</a><?php endif; ?></span>
    </nav>
<?php endif; ?>

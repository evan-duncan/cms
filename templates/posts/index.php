<h1>Posts</h1>
<ul class="feed">
    <?php foreach ($posts as $post): ?>
        <li>
            <a href="/posts/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a>
            <time datetime="<?= e($post['published_at']) ?>"><?= e(post_date($post['published_at'])) ?></time>
        </li>
    <?php endforeach; ?>
</ul>

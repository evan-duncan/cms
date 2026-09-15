<h1>Your posts</h1>
<p><a href="/admin/posts/new">New post</a></p>
<ul>
    <?php foreach ($posts as $post): ?>
        <li>
            <a href="/admin/posts/<?= e((string) $post['id']) ?>/edit"><?= e($post['title']) ?></a>
            <?php if ($post['published_at'] === null): ?>
                <span>Draft</span>
            <?php elseif (strtotime($post['published_at']) > time()): ?>
                <span>Scheduled for <time datetime="<?= e($post['published_at']) ?>"><?= e($post['published_at']) ?></time></span>
            <?php else: ?>
                <span>Published <time datetime="<?= e($post['published_at']) ?>"><?= e($post['published_at']) ?></time></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
<form method="post" action="/admin/logout">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <button type="submit">Log out</button>
</form>

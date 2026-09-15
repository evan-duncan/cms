<h1>Your posts</h1>
<p><a href="/admin/posts/new">New post</a></p>
<ul class="feed">
    <?php foreach ($posts as $post): ?>
        <li>
            <a href="/admin/posts/<?= e((string) $post['id']) ?>/edit"><?= e($post['title']) ?></a>
            <?php if ($post['published_at'] === null): ?>
                <span>Draft</span>
            <?php elseif (strtotime($post['published_at']) > time()): ?>
                <span>Scheduled for <time datetime="<?= e($post['published_at']) ?>"><?= e(post_date($post['published_at'])) ?></time></span>
            <?php else: ?>
                <span>Published <time datetime="<?= e($post['published_at']) ?>"><?= e(post_date($post['published_at'])) ?></time></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
<h2>Settings</h2>
<form method="post" action="/admin/settings">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <p><label>Site name <input name="site_name" value="<?= e(Setting::get('site_name')) ?>" maxlength="100" required></label></p>
    <button type="submit">Save settings</button>
</form>
<form method="post" action="/admin/logout">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <button type="submit">Log out</button>
</form>

<?php if ($error !== null): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>
<?php foreach (['posts' => $posts, 'pages' => $pages] as $type => $items): ?>
    <h1>Your <?= e($type) ?></h1>
    <p><a href="/admin/<?= e($type) ?>/new">New <?= $type === 'pages' ? 'page' : 'post' ?></a></p>
    <ul class="feed">
        <?php foreach ($items as $item): ?>
            <li>
                <a href="/admin/<?= e($type) ?>/<?= e((string) $item['id']) ?>/edit"><?= e($item['title']) ?></a>
                <?php if ($item['published_at'] === null): ?>
                    <span>Draft</span>
                <?php elseif (strtotime($item['published_at']) > time()): ?>
                    <span>Scheduled for <time datetime="<?= e($item['published_at']) ?>"><?= e(post_date($item['published_at'])) ?></time></span>
                <?php else: ?>
                    <span>Published <time datetime="<?= e($item['published_at']) ?>"><?= e(post_date($item['published_at'])) ?></time></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
<h2>Footer links</h2>
<ul class="feed">
    <?php foreach ($links as $link): ?>
        <li>
            <a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a>
            <form method="post" action="/admin/links/<?= e((string) $link['id']) ?>/delete">
                <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
                <button type="submit">Delete</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>
<form method="post" action="/admin/links">
    <input type="hidden" name="csrf" value="<?= e(Auth::csrfToken()) ?>">
    <p><label>Label <input name="label" maxlength="100" required></label></p>
    <p><label>URL <input name="url" placeholder="/about" required></label></p>
    <p><label>Position <input type="number" name="position" value="0"></label></p>
    <button type="submit">Add link</button>
</form>
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

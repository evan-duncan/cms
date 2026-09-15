<h1><?= $post['id'] === null ? 'New post' : 'Edit post' ?></h1>
<?php if ($error !== null): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="<?= $post['id'] === null ? '/admin/posts' : '/admin/posts/' . e((string) $post['id']) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <p><label>Title <input name="title" value="<?= e($post['title']) ?>" required autofocus></label></p>
    <p><label>Slug <input name="slug" value="<?= e($post['slug']) ?>" placeholder="derived from the title"></label></p>
    <p><label>Body<br><textarea name="body" rows="20" cols="80"><?= e($post['body']) ?></textarea></label></p>
    <p>
        <label>Publish at
            <input type="datetime-local" name="published_at" value="<?= e(datetime_local($post['published_at'])) ?>">
        </label>
    </p>
    <p>Leave the date empty to keep this a draft. A date in the future schedules it.</p>
    <button type="submit">Save</button>
    <a href="/admin">Cancel</a>
</form>

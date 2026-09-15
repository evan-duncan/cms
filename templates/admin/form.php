<?php $singular = $type === 'pages' ? 'page' : 'post'; ?>
<h1><?= $content['id'] === null ? 'New ' . $singular : 'Edit ' . $singular ?></h1>
<?php if ($error !== null): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="/admin/<?= e($type) ?><?= $content['id'] === null ? '' : '/' . e((string) $content['id']) ?>">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <p><label>Title <input name="title" value="<?= e($content['title']) ?>" required autofocus></label></p>
    <p><label>Slug <input name="slug" value="<?= e($content['slug']) ?>" placeholder="derived from the title"></label></p>
    <p><label>Body (Markdown)<br><textarea name="body" rows="20" cols="80"><?= e($content['body']) ?></textarea></label></p>
    <p>
        <label>Publish at
            <input type="datetime-local" name="published_at" value="<?= e(datetime_local($content['published_at'])) ?>">
        </label>
    </p>
    <p>Leave the date empty to keep this a draft. A date in the future schedules it.</p>
    <button type="submit">Save</button>
    <a href="/admin">Cancel</a>
</form>

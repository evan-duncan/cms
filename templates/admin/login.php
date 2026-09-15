<h1>Log in</h1>
<?php if ($error !== null): ?>
    <p><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="/admin/login">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <p><label>Email <input type="email" name="email" required autofocus></label></p>
    <p><label>Password <input type="password" name="password" required></label></p>
    <button type="submit">Log in</button>
</form>

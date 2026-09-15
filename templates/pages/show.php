<?php $title = $page['title']; ?>
<article>
    <h1><?= e($page['title']) ?></h1>
    <div><?= markdown($page['body']) ?></div>
</article>

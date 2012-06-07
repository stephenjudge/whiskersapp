<?php if (count($posts) > 1): ?>
<?php foreach ($posts as $key => $val): ?>

    <?php if (is_object($val)) : ?>
    <div class="post clearfix">
        <div class="text"><?php echo anchor('post/'.$key, $val->text); ?></div>
        <div class="date"><?php $val->time != '' ? print date('ga, F j', $val->time) : NULL ?></div>
    </div>
    <?php else: ?>
        <?php var_dump($key); ?>
    <?php endif; ?>

<?php endforeach; ?>
<?php else: ?>
<p>Make a post!</p>
<?php endif; ?>
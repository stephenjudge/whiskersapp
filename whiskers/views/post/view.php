
<div class="post clearfix">
  <div class="text"><?php echo $post->text; ?></div>
  <?php if (isset($post->twitter)): ?>
    <a href="http://twitter.com/#!/<?php echo $post->twitter->user->screen_name; ?>/status/<?php echo $post->twitter->id_str; ?>">Posted to Twitter &rarr;</a>
  <?php endif; ?>
  <div class="date"><?php echo (!empty($post->time)) ? date('ga, F j', $post->time) : NULL ?></div>
  <br>
  <?php if (isset($post->source_urls)) : ?>
    <div class="sources clearfix">
    <?php foreach ($post->source_urls as $source => $permalink) : ?>
    <a href="<?php echo $permalink ?>"><?php echo ucwords($source) ?></a>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<form id="post" class="remove" action="<?php echo site_url('post/remove'); ?>" method="post">
  <input type="hidden" name="key" value="<?php echo $key; ?>" />
  <input id="post-form-submit" onclick="document.getElementById('face').className = 'chesire'" name="op" type="submit" value="Delete" />
</form>
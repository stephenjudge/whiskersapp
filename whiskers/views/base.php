<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?php echo $title; ?></title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="<?php echo $base_url ?>theme/js/scripts.js<?php echo '?' . time(); ?>"></script>
    <link rel="stylesheet" href="<?php echo $base_url ?>theme/css/style.css<?php echo '?' . time(); ?>">
  </head>
  <body>
    <div id="header-container">
      <header class="wrapper">
        <h1><?php echo anchor('post', 'Whiskers'); ?></h1>
        <?php if ($authenticated): ?>
        <div id="user"><?php echo anchor('logout', 'Logout'); ?></div>
        <?php endif; ?>
      </header>
    </div>
    <div id="main" class="wrapper clearfix">
      <nav>
        <ul>
          <li>
          <?php 
            $att_current = (strstr(current_url(), 'post')) ? 'class="active"' : NULL;
            echo anchor('post', 'Post', $att_current); 
          ?>
          </li>
          <li>
          <?php 
            $att_current = (strstr(current_url(), 'history')) ? 'class="active"' : NULL;
            echo anchor('history', 'History', $att_current); 
          ?>
          <li>
          <?php 
            $att_current = (strstr(current_url(), 'admin')) ? 'class="active"' : NULL;
            echo anchor('admin', 'Admin', $att_current); 
          ?>
        </ul>
      </nav>
      <div id="content" class="curl">
<?php if (!empty($messages)): ?>
<?php foreach ($messages as $class_type => $class_msgs) :
  foreach ($class_msgs as $message) : ?>
        <div class="flash <?php print $class_type; ?>"><?php print $message; ?></div>
<?php 
  endforeach; 
endforeach; ?>
<?php endif; ?>

      <?php echo $content ?>
      </div>
    </div>
  <div id="footer-container">
    <footer class="wrapper">
      <img onmouseover="className = 'chesire'" id="face" src="<?php echo $base_url ?>theme/images/whiskers.png" width="47" height="22" title="Whiskers' face" alt="Whiskers' face" />
    </footer>
  </div>
<?php if (isset($scripts)) : ?>
<?php foreach ($scripts as $name) : ?>
<script src="<?php echo $base_url ?>theme/js/<?php echo $name ?>.js<?php echo '?' . time(); ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>
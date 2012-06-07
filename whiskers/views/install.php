<h2>Installing Whiskers</h2>
<ul class="install">
<?php foreach ($lines as $line) : ?>
    <li class="check"><?php echo $line ?></li>
<?php endforeach; ?>
</ul>

<hr>

<h2>Success! Now let's create your account.</h2>

<form action="<?php echo $base_url."install/signup"; ?>" method="POST">
    <input type="text" name="username" placeholder="Your username">
    <input type="password" name="password" placeholder="password">

    <p><input type="submit" value="Create account"></p>
</form>
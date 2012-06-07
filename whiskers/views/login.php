<form id="post" action="<?php echo $action_url ?>" method="post">
<div>
  <label for="username">Username</label>
  <input type="text" name="login[username]"  maxlength="50" value="" id="username">
</div>
<div class="form-item">
  <label for="password">Password</label>
  <input type="password" name="login[password]" value="" id="password">
</div>
<div class="form-item">
  <input id="post-form-submit" onclick="document.getElementById('face').className = 'chesire'" name="op" type="submit" value="Login" />
</div>
</form>
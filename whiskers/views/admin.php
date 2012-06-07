<h2>Accounts</h2>

<form id="add_service" action="<?php echo site_url("admin/account_connect"); ?>" method="post">
    <p>Add an a account for</p>

    <select name="add[driver]">
    <?php if (isset($available_drivers)) : foreach ($available_drivers as $driver) : ?>
        <option value="<?php echo $driver ?>"><?php echo ucwords($driver) ?></option>
    <?php endforeach; endif; ?>
    </select>

    <input type="submit" value="Add" />

</form>

<p>Depending on the account, you'll be asked for a user name and password, or sent to the account's website to sign in and authorize <em>Whiskers App</em>.</p>

<h3>Current Accounts</h3>
<?php if ($valid_drivers) : ?>
<?php foreach ($valid_drivers as $driver => $obj): ?>

    <?php if('twitter' === $driver) : ?>
        <h4><?php echo ucwords($driver) ?></h4> 
        <a href="http://twitter.com/<?php echo $obj->access_token->screen_name ?>"><?php echo $obj->access_token->screen_name ?></a>
    <?php endif; ?>

    <?php if('facebook' === $driver) : ?>
        <h4><?php echo ucwords($driver) ?></h4>
        <a href="<?php echo $obj->user->link ?>"><?php echo $obj->user->name ?></a>
    <?php endif; ?>


    <form id="remove_service" action="<?php echo site_url('admin'); ?>" method="post" style="float:right">
    <input type="hidden" name="rm[driver]" value="<?php echo $driver; ?>" />
    <input id="remove_service_submit" onclick="document.getElementById('face').className = 'chesire'" name="rm[op]" type="submit" value="Remove" />
    </form>

    <hr>
<?php endforeach; ?>
<?php else: ?>
    <p>None.</p>
<?php endif; ?>
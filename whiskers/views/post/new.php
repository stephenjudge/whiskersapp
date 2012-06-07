<form id="post" action="<?php echo site_url() ?>" method="post" data-endpoint="<?php echo $base_url ?>api/post">
    <div id="count">0 chars</div>
    <textarea id="text" name="text" rows="4" cols="60"></textarea>

    <h3>Post to &raquo;</h3>

    <div id="drivers">
        <?php if ($valid_drivers) : ?>
        <?php foreach ($valid_drivers as $driver => $obj) : ?>
        <div class="driver triangle-border left">
            <header>
                <h4>To <?php echo ucwords($driver) ?></h4>
                <a href="#" class="remove">x</a>
            </header>

            <textarea id="<?php echo $driver ?>_text" name="<?php echo $driver ?>_status" class="driver-text" data-driver="<?php echo $driver ?>" rows="4" cols="45"></textarea>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p>You have to add an account before you can post. Visit the admin page to <a href="<?php echo site_url("/admin") ?>">manage your accounts</a>.</p>
        <?php endif; ?>
    </div>

    <input id="post-form-submit" onclick="document.getElementById('face').className = 'chesire'" name="op" type="submit" value="Post all" />
</form>
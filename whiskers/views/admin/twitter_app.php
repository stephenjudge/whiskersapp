<h2>Twitter App</h2>

<form name="twitter_app" action="<?php echo site_url("admin/twitter_app"); ?>" method="post">

    <p>You need to create a Twitter app and add the details here to continue:</p>

    <ul>
        <li>Go to <a target="_blank" href="http://dev.twitter.com">http://dev.twitter.com</a></li>
        <li>In the bottom-right corner, click "Create an app".</li>
        <li>Sign in with your Twitter account if you're not already</li>
        <li>Fill in the Name field with something like "John's Whiskers", ignore the other fields.</li>
        <li>Add a description ("Whiskers for Twitter") and website url (the current url if you'd like).</li>
        <li>Enter your callback url: <strong><?php print site_url('admin/twitter_callback'); ?></strong></li>
        <li>Agree to the license, enter the captcha, and click "Create your Twitter application"</li>
        <li>Click the "Settings" tab, change the Access to "Read and Write", and click "Update this Twitter applications settings"</li>
        <li>Go back to the "Details" tab, and copy the "Consumer key" and "Consumer secret" to Whiskers. You should be good to go!</li>
    </ul>

    <p>Add your Twitter details:</p>

    <hr />

    <p>
    <label for="twitter_consumer_key">Consumer Key</label>
    <input name="twitter_consumer_key" type="text" value="<?php echo $old_consumer_key ?>">
    </p>

    <p>
    <label for="twitter_consumer_secret">Consumer Secret</label>
    <input name="twitter_consumer_secret" type="text" value="<?php echo $old_consumer_secret ?>">
    </p>

    <input type="submit" value="Save details" />

</form>

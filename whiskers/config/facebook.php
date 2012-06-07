<?php
/**
 * Goto https://developers.facebook.com/apps/
 *
 * From here you can manage your Facebook Apps
 * and obtain all values for the config items below.
 *
 * Make sure you "Select how your app integrates with Facebook"
 * by clicking "Website" and filling in your Site URL (where you're hosting Whiskers)
 */
$config['facebook_default_scope']	= 'offline_access,publish_stream'; // E.G 'read_stream,birthday,users_events,rsvp_event'
$config['facebook_api_url'] 		= 'https://graph.facebook.com/'; // Just in case it changes.
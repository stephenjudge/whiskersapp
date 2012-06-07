<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whiskers_post_twitter extends CI_Driver {

    protected   $CI,
                $connection,
                $screen_name;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();

        require_once BASEPATH.'../vendor/twitteroauth/twitteroauth/twitteroauth.php';
        // Load local config
        $twitter_settings = $this->CI->settings->get('twitter');
        $consumer_key = $twitter_settings->twitter_consumer_key;
        $consumer_secret = $twitter_settings->twitter_consumer_secret;
        $access_token = $twitter_settings->access_token;


        /* Create a TwitterOauth object with consumer/user tokens. */
        $this->connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token->oauth_token, $access_token->oauth_token_secret);

        /* If method is set change API call made. Test is called by default. */
        $content = $this->connection->get('account/verify_credentials');

        // for later
        $this->screen_name = $access_token->screen_name;
    }

    public function save_post($text)
    {
        if (empty($text))
        {
            $this->CI->session->setMessage('Text cannot be empty', 'error');
            return FALSE;
        }

        $tweet = $this->connection->post('statuses/update', array('status' => $text));

        if ( ! is_int($tweet))
        {
            $tweet = $tweet->id_str;
        }

        $time = time();

        // Tweet has been posted, save to DB
        $key = sha1($time.':'.$text);

        $saved = $this->CI->posts->update($key, array(
            'type' => 'post',
            'text' => $text,
            'time' => $time,
            'source_urls' => array(
                'twitter' => 'http://twitter.com/#!/'.$this->screen_name.'/status/'.$tweet
            )
        ));

        return ( ! $saved) ? FALSE : $tweet;
    }

    public function remove_post($key)
    {
        $tweet = $this->CI->posts->get($key);
        $url_pieces = explode('/', $tweet->source_urls->twitter);
        $id = $url_pieces[count($url_pieces)-1];

        if (empty($id))
        {
            return $this->CI->posts->rm($key);
        }

        // remove from Twitter
        try {
            $tweet = $this->connection->post('statuses/destroy', array('id' => $id));
        } catch (Exception $e) {
            $this->session->setMessage($e, 'error');
            return FALSE;
        }

        if ( ! $tweet)
        {
            $this->session->setMessage('Could not delete post from Twitter', 'error');
            return FALSE;
        }

        if ( ! $this->CI->posts->rm($key))
        {
            $this->session->setMessage('Could not remove post from Database', 'error');
            return FALSE;
        }

        return $id;
    }
}

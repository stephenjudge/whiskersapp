<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whiskers_post_facebook extends CI_Driver {

    protected   $CI,
                $fb_settings,
                $fb;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->helper('url');
        $this->CI->load->library('whiskers_db', array('settings'), 'settings');
        $this->CI->load->config('facebook'); // Load local config
        $this->CI->load->file(FCPATH.'vendor/facebook-php-sdk/src/facebook.php');

        $app_id = $this->CI->config->item('facebook_app_id');
        $app_secret = $this->CI->config->item('facebook_api_secret');
        $app_scope = $this->CI->config->item('facebook_default_scope');

        $my_url = current_url();

        $this->fb_settings = $this->CI->settings->get('facebook');

        $this->fb = new Facebook(array(
            'appId'  => $app_id,
            'secret' => $app_secret,
        ));
    }

    public function save_post($text)
    {
        if (empty($text))
        {
            $this->CI->session->setMessage('Text cannot be empty', 'error');
            return FALSE;
        }

        $result = $this->fb->api(
            '/me/feed/',
            'post',
            array('access_token' => $this->fb_settings->token, 'message' => $text)
        );

        $long_id = explode('_', $result['id']);
        $id = $long_id[1];
        $time = time();

        // Tweet has been posted, save to DB
        $key = sha1($time.':'.$text);

        $saved = $this->CI->posts->update($key, array(
            'type' => 'post',
            'text' => $text,
            'time' => $time,
            'source_urls' => array(
                'facebook' => $this->fb_settings->user->link.'/posts/'.$id
            )
        ));

        return ( ! $saved) ? FALSE : $id;
    }

}

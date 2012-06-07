<?php

class Whiskers {

  public    $session,
            $user,
            $apis = array(),
            $db,
            $settings,
            $CI;

    /**
     * Setup session and current user.
     */
    function __construct() 
    {
        $CI =& get_instance();

        $this->db = $CI->whiskers_db->init($this, 'post');
        $this->settings = $CI->whiskers_db->init($this, 'settings');

        $this->CI = $CI;
        // $this->loadAPIs();
    }

    function process($post) 
    {
        if (isset($post['op'])) 
        {
            // This is a bad scene.
            if ($post['op'] == 'Post') 
            {
                foreach ($this->apis as $name => $api) 
                {
                    $service = 'save_post';

                    if (method_exists($api, $service)) 
                    {
                        $resp = call_user_func(
                              array($api, $service)
                            , NULL
                            , $post['text']
                        );
                    }
                }
            }

            if ($post['op'] == 'Delete') 
            {
                foreach ($this->apis as $name => $api) 
                {
                    $service = 'delete_post';

                    if (method_exists($api, $service)) 
                    {
                        $resp = call_user_func(
                            array($api, $service),
                            $post['key']
                        );
                    }
                }

                WhiskersHttp::redirect('history');
            }
        }
    }

    function requireAuth() 
    {
        $user = $this->CI->session->userdata('User');
        return isset($user['id']);
    }

    function loadAPIs() 
    {
        // Not ideal... maybe move into /inc/api.
        include_once(WHISKERS_ROOT . '/inc/class/WhiskersPost.class.php');

        $this->apis['WhiskersPost'] = new WhiskersPost($this);
        $apis = array('TwitterPost');

        foreach ($apis as $api) 
        {
            $file = WHISKERS_ROOT . '/inc/api/' . $api . '.php';

            if ( ! file_exists($file)) 
            {
                $error = 'API file no longer exists';
            } 
            else 
            {
                include_once($file);
                $this->apis[$api] = new $api($this, $api);
            }
        }
    }

}

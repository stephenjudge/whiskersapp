<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

    public  $data; // holds data to be passed to view

    public function __construct()
    {
        parent::__construct();

        // Dependecies
        $this->load->helper('url');
        $this->load->helper('whiskers');
        $this->load->library('session');
        $this->load->library('whiskers_user');
        $this->load->library('whiskers_db', array('settings'), 'settings');
        $this->load->library('whiskers_db', array('post'), 'db');
        $this->load->driver('whiskers_post');

        // setup template data
        $this->data['base_url'] = base_url();
        $this->data['title'] = 'Whiskers';
        $this->data['authenticated'] = false;

        if ( ! $var = $this->_logged_in())
        {
            // die(var_dump($this->session->userdata));
            redirect('/login', 301);
        }
    }

    public function index()
    {
        // Delete service credentials from settings.db
        if ($remove_service = $this->input->post('rm', TRUE))
        {
            $driver = 'whiskers_post_'.$remove_service['driver'];
            $handle = $this->settings->rm($remove_service['driver']);
            if ($handle !== false) {
                //$this->session->setMessage('Your ' . $remove_service['driver'] . ' account was removed successfully.', 'message');
                //return true;
            }
            else {
                //$this->session->setMessage('There was a problem removing your account.', 'message');
                //return true;
            }
        }

        $drivers = whiskers_get_drivers();
        $this->data['valid_drivers'] = $drivers['valid_drivers'];
        $this->data['available_drivers'] = $drivers['available_drivers'];

        $this->_parse_template('admin');
    }

    public function account_connect()
    {
        $add_service = $this->input->post('add', TRUE);

        if (method_exists($this, $add_service['driver'] . '_connect'))
        {
            $this->{$add_service['driver'] . '_connect'}(); // Maybe directly on the driver.
        }
    }


    /**
     * Twitter OAuth
     *
     */
    public function twitter_connect()
    {
        require_once BASEPATH.'../vendor/twitteroauth/twitteroauth/twitteroauth.php';
        
        $twitter_settings = $this->settings->get('twitter');
        
        if ( ! is_object($twitter_settings)
            && ! isset($twitter_settings->twitter_consumer_key)
            && ! isset($twitter_settings->twitter_consumer_secret))
        {
            redirect('/admin/twitter_app');
        }
        
        // Load local config
        $consumer_key = $twitter_settings->twitter_consumer_key;
        $consumer_secret = $twitter_settings->twitter_consumer_secret;

        /* Build TwitterOAuth object with client credentials. */
        $connection = new TwitterOAuth($consumer_key, $consumer_secret);

        /* Get temporary credentials. */
        // param is callback url
        $request_token = $connection->getRequestToken(site_url('admin/twitter_callback'));
        $token = $request_token['oauth_token'];

        /* Save temporary credentials to session. */
        $this->session->set_userdata('oauth_token', $token);
        $this->session->set_userdata('oauth_token_secret', $request_token['oauth_token_secret']);

        /* If last connection failed don't display authorization link. */
        switch ($connection->http_code)
        {
            case 200:
                /* Build authorize URL and redirect user to Twitter. */
                $url = $connection->getAuthorizeURL($request_token);
                header('Location: ' . $url);
            break;

            case 401:
                $this->session->setMessage('401: Authentication failed.', 'error');
            break;

            default:
                /* Show notification if something went wrong. */
                $this->session->setMessage('Could not connect to Twitter. Refresh the page or try again later.', 'error');
                redirect('/admin', 301);
        }
    }
    
    /**
     * Form for FB app info
     */
    public function twitter_app()
    {
        $twitter_app = $this->input->post(NULL, TRUE);
    
        if ($twitter_app)
        {
            $this->settings->update('twitter', $twitter_app);
            redirect('/admin/twitter_connect');
        }
    
        // Show form
        $this->data['old_consumer_key'] = isset($twitter_settings->twitter_consumer_key) ? $twitter_settings->$old_consumer_key : '';
        $this->data['old_consumer_secret'] = isset($twitter_settings->twitter_consumer_secret) ? $twitter_settings->twitter_consumer_secret : '';
    
        $this->_parse_template('admin/twitter_app');
    }


    /**
     * Take the user when they return from Twitter. Get access tokens.
     * Verify credentials and redirect to based on response from Twitter.
     */
    public function twitter_callback()
    {
        require_once BASEPATH.'../vendor/twitteroauth/twitteroauth/twitteroauth.php';

        // Load local config
        $twitter_settings = $this->settings->get('twitter');
        $consumer_key = $twitter_settings->twitter_consumer_key;
        $consumer_secret = $twitter_settings->twitter_consumer_secret;

        /* If the oauth_token is old redirect to the connect page. */
        if (isset($_REQUEST['oauth_token'])
        && $this->session->userdata('oauth_token') !== $_REQUEST['oauth_token'])
        {
            $this->session->set_userdata('oauth_status', 'oldtoken');
            $this->session->sess_destroy();
            redirect('/admin', 301);
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $this->session->userdata('oauth_token'), $this->session->userdata('oauth_token_secret'));

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $this->session->set_userdata('access_token', $access_token);

        /* Remove no longer needed request tokens */
        $this->session->unset_userdata('oauth_token');
        $this->session->unset_userdata('oauth_token_secret');

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 === $connection->http_code)
        {
            /* The user has been verified and the access tokens can be saved for future use */
            $this->session->set_userdata('status', 'verified');
            $this->settings->rm('twitter');
            $this->settings->set('twitter', array(
                'access_token' => $access_token,
                'twitter_consumer_key' => $consumer_key,
                'twitter_consumer_secret' => $consumer_secret
            ));

            $this->session->setMessage('Successfully authorized Twitter.', 'success');

            redirect('/admin');
        }
        else
        {
            /* Save HTTP status for error dialog on connnect page.*/
            $this->session->set_userdata('oauth_status', 'oldtoken');
            $this->session->sess_destroy();
            $this->session->setMessage('Could not connect to Twitter. Responded with: '.$connection->http_code, 'error');
            redirect('/admin', 301);
        }
    }

    public function facebook_connect()
    {
        $this->load->file(FCPATH.'vendor/facebook-php-sdk/src/facebook.php');

        // check db for fb app info
        $fb_settings = $this->settings->get('facebook');

        if ( ! is_object($fb_settings)
            && ! isset($fb_settings->facebook_app_id)
            && ! isset($fb_settings->facebook_api_secret))
        {
            redirect('/admin/facebook_app');
        }

        // cool we got fb app info
        $app_id = $fb_settings->facebook_app_id;
        $app_secret = $fb_settings->facebook_api_secret;

        $this->load->config('facebook'); // Load local config
        $app_scope = $this->config->item('facebook_default_scope');

        $facebook = new Facebook(array(
            'appId'  => $app_id,
            'secret' => $app_secret,
        ));

        $user = $facebook->getUser();

        if ($user)
        {
            try
            {
                // Proceed knowing you have a logged in user who's authenticated.
                $code = $_SESSION["fb_{$app_id}_code"];
                $token = $_SESSION["fb_{$app_id}_access_token"];

                $user_profile = $facebook->api('/me');

                $this->settings->rm('facebook');
                $this->settings->set('facebook', array(
                    'token' => $token,
                    'user' => $user_profile
                ));
                return redirect('admin');
            }
            catch (FacebookApiException $e)
            {
                show_error($e);
                $user = null;
            }
        }
        else
        {
            redirect($facebook->getLoginUrl(array(
                'scope' => $app_scope,
                'redirect_uri' => site_url('admin/facebook_connect')
            )));
        }
    }

    public function facebook_auth()
    {
        echo $code = $this->input->get('code');
    }

    /**
     * Form for FB app info
     */
    public function facebook_app()
    {
        $fb_app = $this->input->post(NULL, TRUE);

        if ($fb_app)
        {
            $this->settings->update('facebook', $fb_app);
            redirect('/admin/facebook_connect');
        }

        // Show form
        $this->data['old_api_secret'] = isset($fb_settings->facebook_api_secret) ? $fb_settings->facebook_api_secret : '';
        $this->data['old_app_id'] = isset($fb_settings->facebook_app_id) ? $fb_settings->facebook_app_id : '';

        $this->_parse_template('admin/facebook_app');
    }

    private function _logged_in()
    {
        $uri = uri_string();

        if ( strstr($uri, 'facebook'))
        {
            return TRUE;
        }

        // login token exists
        if ($this->input->post('token', TRUE))
        {
            return TRUE;
        }

        // Lastly, check for user id in the session
        $user = $this->session->userdata('User');

        return isset($user['id']);
    }

    private function _parse_template($template)
    {
        $file = APPPATH.'views/'.$template.'.php';

        if ( ! is_file($file))
        {
            show_error('Could not load template file: "'.$file.'"');
            return false;
        }

        $this->data['messages'] = $this->session->getMessages(NULL, TRUE);

        extract($this->data);

        ob_start();
        include $file;
        $out = ob_get_contents();
        ob_end_clean();

        $this->data['content'] = $out;

        $this->load->view('base', $this->data);
    }
}

/* End of file whiskers_frontend.php */
/* Location: ./application/controllers/whiskers_frontend.php */

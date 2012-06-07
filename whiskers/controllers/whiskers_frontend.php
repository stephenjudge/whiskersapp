<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whiskers_Frontend extends CI_Controller {

    public  $data; // holds data to be passed to view

    public function __construct()
    {
        parent::__construct();

        // Dependecies
        $this->load->helper('url');
        $this->load->helper('whiskers');
        $this->load->library('session');
        $this->load->library('whiskers_user');

        // Check for DB
        $db_file = BASEPATH.'../data/whiskers.db';

        if ( ! file_exists($db_file))
        {
            redirect('/install', 301);
        }

        $this->load->database();

        $this->load->library('whiskers_db', array('settings'), 'settings');
        $this->load->library('whiskers_db', array('posts'), 'posts');
        $this->load->driver('whiskers_post');

        // setup template data
        $this->data['base_url'] = base_url();
        $this->data['title'] = 'Whiskers';
        $this->data['authenticated'] = false;

        // Check logged in status 
        if ( ! $var = $this->_logged_in())
        {
            redirect('/login', 301);
        }
    }

	public function index()
	{
        $drivers = whiskers_get_drivers();
        
        $this->data['valid_drivers'] = $drivers['valid_drivers'];
        $this->data['scripts'] = array('posting');
        $this->data['messages'] = $this->session->getMessages(NULL, TRUE);

        $this->_parse_template('post/new');
	}

    public function post($action = "new")
    {
        $this->data['action_url'] = site_url('login');

        if ($action === 'new') 
        {
            $drivers = whiskers_get_drivers();
            
            $this->data['valid_drivers'] = $drivers['valid_drivers'];
            $this->data['scripts'] = array('posting');
            $this->data['messages'] = $this->session->getMessages(NULL, TRUE);

            $this->_parse_template('post/new');
        }
        elseif ($action === 'remove')
        {
            // REMOVE SOME POST!!!1
            if (($remove_post = $this->input->post(NULL, TRUE)) !== FALSE )
            {
                if ( ! $this->whiskers_post->delete_post($remove_post['key']))
                {
                    $this->data['messages'] = $this->session->flashdata('error');
                    $this->_parse_template('post/view/'.$remove_post['key']);
                }

                redirect('history');
            }
        }
        else
        {
            // SHOW DAT FORM!
            $this->data['post'] = $this->posts->get($action);
            $this->data['key'] = $action;
            $this->_parse_template('post/view');
        }

    }

    public function history()
    {
        // $this->data['posts'] = $this->whiskers_post->get_posts();
        $this->data['posts'] = $this->posts->get();
        $this->_parse_template('history'); 
    }

    /**
     * Show or handle login form
     */
    public function login()
    {
        $record = $this->settings->get('user');
        
        if ( ! is_object($record))
        {
            redirect('/install');
        }


        if ($login = $this->input->post('login', TRUE))
        {
            $login_attempt = $this->whiskers_user->authenticate($login['username'], $login['password']);

            if ( ! $login_attempt)
            {
                $this->session->setMessage('Could not log you in', 'error');
            }
            else
            {
                redirect('/post', 'refresh');
            }
        }

        $this->data['action_url'] = site_url('login');
        $this->data['messages'] = $this->session->getMessages(NULL, TRUE);
        $this->_parse_template('login');
    }

    private function _logged_in()
    {
        $uri = uri_string();

        if ( strstr($uri, 'login'))
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
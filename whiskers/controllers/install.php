<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Install extends CI_Controller {

    public  $data; // holds data to be passed to view

    public function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
        $this->load->helper('whiskers');
        $this->load->library('session');
        $this->load->library('whiskers_user');
        $this->load->library('whiskers_db', array('settings'), 'settings');
        $this->load->library('whiskers_db', array('posts'), 'posts');

        $this->data['base_url'] = base_url();
        $this->data['title'] = 'Whiskers Install';
        $this->data['authenticated'] = false;
    }

    public function index()
    {
        // Base URL is not properlly set
        $base_uri = str_replace('install', '', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $base_uri = (substr($base_uri, 0, 7) === 'http://') ? $base_uri : 'http://'.$base_uri;
        $this->data['base_url'] = $base_uri;

        // messages to frontend
        $this->data['lines'] = array();

        // Check db dir
        $db_dir = BASEPATH.'../data/';

        if ( ! is_writable($db_dir))
        {
            $this->data['lines'][] = "The directory \"{$db_dir}\" is not writeable. Please check your permissions.";
        }
        else
        {
            $this->data['lines'][] = "The directory \"{$db_dir}\" is writeable.";
        }

        // check db file
        $db_file = BASEPATH.'../data/whiskers.db';

        if ( ! file_exists($db_file))
        {
            $db_handle = fopen($db_file, 'w');
            fclose($db_handle);

            $this->data['lines'][] = "Created database file: \"{$db_file}\"";
        }
        else
        {
            $this->data['lines'][] = "Database file \"{$db_file}\" found.";
        }

        $this->load->database();

        // check if table exists
        // if not, create

        $create_posts = $this->db->simple_query("CREATE TABLE posts (
            key VARCHAR(100) PRIMARY KEY, 
            val BLOB, 
            modified DATETIME DEFAULT CURRENT_TIMESTAMP,
            created TIMESTAMP(20)
        )");

        if (FALSE !== $create_posts)
        {
            $this->data['lines'][] = "Created database table: \"posts\"";
        }
        else
        {
            $this->data['lines'][] = "Database table \"posts\" already exists.";
        }

        // create settings table
        $create_settings = $this->db->simple_query("CREATE TABLE settings (
            key VARCHAR(100) PRIMARY KEY, 
            val BLOB, 
            modified DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        if (FALSE !== $create_settings)
        {
            $this->data['lines'][] = "Created database table: \"settings\"";
        }
        else
        {
            $this->data['lines'][] = "Database table \"settings\" already exists.";
        }

        $this->data['lines'][] = "Whiskers is installed.";

        // Cool!
        $this->_parse_template('install');
    }

    public function signup()
    {
        $user = $this->input->post('username');
        $pass = $this->input->post('password');

        if (empty($user) || empty($pass))
        {
            redirect('/install', 301);
        }

        if ($this->whiskers_user->save($user, $pass))
        {
            $login_attempt = $this->whiskers_user->authenticate($user, $pass);

            if ( ! $login_attempt)
            {
                $this->session->setMessage('Could not log you in', 'error');
                redirect('/install', 301);
            }
            else
            {
                $this->session->setMessage('Account created! You&rsquo;re now logged in.', 'success');
                redirect('/admin', 'refresh');
            }
        }
        else
        {
            $this->session->setMessage('Error creating your account.', 'error');
            redirect('/install', 301);
        }
    }

    public function convert()
    {
        // $this->load->database();

        // $old_db_file = BASEPATH.'../data/settings.JSON';
        // $lines = file($old_db_file);

        // // Loop through our array, show HTML source as HTML source; and line numbers too.
        // foreach ($lines as $line_num => $line) 
        // {
        //     $json = json_decode($line);

        //     if ($json->val !== NULL)
        //     {
        //         $this->db->insert('settings', array(
        //               'key' => $json->key
        //             , 'val' => json_encode($json->val)
        //             // , 'created' => $json->val->time
        //         )); 
        //     }
        // }
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
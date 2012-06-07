<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

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
        $this->load->library('whiskers_db', array('posts'), 'posts');
        $this->load->driver('whiskers_post');
    }

    // return JSON format
    public function _respond($status = 200)
    {
        $this->data['status'] = $status;
        $this->load->view('json', $this->data);
    }

    // Ugh, RESTful?!
    public function _remap($method, $params = array())
    {
        $method = $_SERVER['REQUEST_METHOD'].'_'.$method;

        if (method_exists($this, $method))
        {
            return call_user_func_array(array($this, $method), $params);
        }

        show_404();
    }

    /**
     * Saves a whisker post.
     *
     * @param   string  $driver     name of the driver
     * @param   string  $text       text of the post
     * @return  object
     */
    function post_post()
    {
        $this->data['driver'] = $this->input->post('driver');
        $this->data['text'] = $this->input->post('text');

        $driver = $this->input->post('driver');

        if ( ! $data = $this->whiskers_post->$driver->save_post($this->data['text']))
        {
            $this->data['message'] = "Failed to post to {$this->data['driver']}.";
            $this->_respond(500);
        }
        
        $this->_respond(200);
    }

    function get_post($id)
    {
        echo "string get";
    }
}
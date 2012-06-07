<?php

class Whiskers_post extends CI_Driver_Library {

    public $CI;
      
    public $valid_drivers  = array(
        'whiskers_post_twitter',
        'whiskers_post_facebook'
    );

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('whiskers_db', array('post'), 'db');
    }

    public function delete_post($key) 
    {
        if ( ! $this->CI->db->rm($key)) 
        {
            $this->CI->session->set_flashdata('status', 'Failed to delete post');
            return FALSE;
        }

        $this->CI->session->set_flashdata('status', 'Post deleted');

        return TRUE;
    }

  public function save_post($key = NULL, $text, $time = NULL) 
  {
    // Sanity checks.
    if ($time == NULL) {
      $time = time();
    }
    if ($text == NULL) {
      $this->whiskers->session->setMessage('Please enter some text to post a message', 'error');
      return false;
    }
    if ($key == NULL) {
      $key = sha1(time() . ':' . $text);
    }
    // This should be a proper object and we just set the object, ie.: db->set($id, $this);
    // Might need to have a separate object, biggest problem is filtering out $this->whiskers.
    if ($this->whiskers->db->set($key, array('type' => 'post', 'text' => $text, 'time' => $time))) {
      $this->whiskers->session->setMessage('Post created. ' . l('post/' . $key, 'See it &rarr;'), 'status');
    }
  }

    public function get_posts($key = NULL)
    {
        return $this->CI->posts->get($key);
    }
  
  function admin_form() {
    print '';
  }

}
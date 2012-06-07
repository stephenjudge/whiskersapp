<?php

/**
 * User management functions
 */
class Whiskers_user {
  
    public $whiskers;
    public $user;
    public $prefs;
    public $db;

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }  

    function authenticate($username, $password) 
    {
        $record = $this->CI->settings->get('user');
        
        if ( ! is_object($record))
        {
            $this->CI->session->set_flashdata('error', 'Settings DB is empty');
            return FALSE;
        }

        if ($username == $record->username && sha1($password) == $record->password) 
        {
            $this->loadUser(array('id' => $record->username));
            return TRUE;
        }
        else 
        {
            // $this->whiskers->session->setMessage('Could not log you in', 'error');
            return FALSE;
        }
    }

    /**
     * TODO: Implement proper password hasing + salting
     */
    function save($username, $password)
    {
        return $this->CI->settings->set('user', array(
              'username' => $username
            , 'password' => sha1($password)
        ));
    }

  function loadUser($details) {
    $user = array(
      'id'  => $details['id'],
      'sig' => sha1(uniqid()),
    );
    $this->CI->session->set_userdata('User', $user);
  }
  
  function read($field) {
    return @$this->user[$field];
  }
  
  function signout() {
    $this->whiskers->session->destroy();
    WhiskersHttp::redirect(WHISKERS_URL);
  }
  
  function reload() {
    $user = $this->whiskers->session->read('User');
    $id  = $user['id'];
    $sig = $user['sig'];
    
    $res = $this->whiskers->db->query_rows('readUser', 1, $user['id'], $user['sig']);
    if ( ! empty($res) ) {
      $this->user = $res[0];
      $this->prefs = $this->whiskers->db->query_rows('getPreferences', -1, intval($this->read('id')));
    } else {
      # the users signature and id do not match with what we have so
      # force them off the system
      $this->signout();
    }
  }

  function update($prefs) {
    $user_fields = array_intersect_key($prefs, $this->user);
    
    # password change request
    if ( ! empty($prefs['password'])) {
      if ($prefs['password'] !== $prefs['confirm']) {
        $this->whiskers->session->setMessage("Passwords do not match", 'error');
        return FALSE;
      } else {
        $password = $this->whiskers->hash( 
                      sprintf('%s-%s-%s',
                            $this->whiskers->config['presalt'],
                            $prefs['password'],
                            $this->whiskers->config['postsalt']
        ));
        $this->whiskers->db->query('changePassword', $this->user['id'], $password);
      }
    }
    $this->whiskers->db->query('updateUser', $this->user['id'], 
      $user_fields
    );
  }
}

?>
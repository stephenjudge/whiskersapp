<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Session extends CI_Session {

    function setMessage($msg, $level) 
    {
        $msgs = $this->getMessages();
        $msgs[$level][] = $msg;
        $this->set_userdata("Message", $msgs);
    }

    function getMessages($level=NULL, $erase=FALSE) 
    {
        $msg = $this->userdata("Message");


        if ($erase) 
        {
            $this->unset_userdata("Message");
        }

        if ( ! empty($level)) 
        {
            return @$msg[$level];
        } 
        else 
        {
            return $msg;
        }
    }
}
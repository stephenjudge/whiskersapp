<?php
	
	function whiskers_get_drivers()
	{
		$ci =& get_instance();

		$ret = array();
		
        // Loop valid drivers, matched in settings db
        foreach ($ci->whiskers_post->valid_drivers as $driver)
        {
            $driver = str_replace('whiskers_post_', '', $driver);
            $handle = $ci->settings->get($driver);            

            if ( ! empty($handle))
            {
                $ret['valid_drivers'][$driver] = $handle;
            }
            else
            {
                $ret['available_drivers'][] = $driver;
            }
        }

        if ( ! isset($ret['available_drivers']))
        {
            $ret['available_drivers'] = false;
        }

        if ( ! isset($ret['valid_drivers']))
        {
            $ret['valid_drivers'] = false;
        }

        return $ret;
	}

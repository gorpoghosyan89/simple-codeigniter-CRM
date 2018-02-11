<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * fms_endpoint_helper
 */

// ------------------------------------------------------------------------

/** load local config
    returns config array (general, or db) for use in config files
    This is a bit clunky because CodeIgniter handles db settings a wee
    bit differently from other configs... so this returns the data to be
    used, rather than setting it directly. Hmm it was also horrible to get
    working because of aforementioned clunkiness.
*/

if ( ! function_exists('load_general_config')) {
	function load_general_config($config_type = '') {
        $conf_general_filename = BASEPATH . "../conf/general.yml";
        static $general_config_is_loaded = FALSE;
        static $general_config = array();
        static $general_db_config= array();
        if (! $general_config_is_loaded && file_exists($conf_general_filename)) {  
            require_once APPPATH . "libraries/spyc.php";
            $general_yml_data = Spyc::YAMLLoad($conf_general_filename);
            $general_config_is_loaded = TRUE;
            foreach ($general_yml_data as $key => $value) {      
                $k = strtolower(str_replace("FMSE_", "", trim($key)));
                switch ($k) {
                    //-----------------------------------------------------------------------
                    // map mySociety-specific config values to their CodeIgniter equivalents
                    case 'db_host':
                        $k = 'hostname';
                        break;
                    case 'db_name':
                        $k = 'database';
                        break;
                    case 'db_user':
                        $k = 'username';
                        break;
                    case 'db_pass':
                        $k = 'password';
                        break;
                    // end of mySociety-specific mappings
                    //-----------------------------------------------------------------------
                    default:                                
                        if (strpos($k, 'db_') === 0) {
                            $k = strtolower(str_replace("db_", "", $k));
                        } else {
                            if ($k=='base_url') {
                                $base_url = trim($value);
                                $len = strlen($base_url);
                                if ($len > 0) {
                                    if (strrpos($base_url, '/') != $len-1) {
                                        $base_url = $base_url . '/';
                                    }
                                } else {
                                    $k = '';
                                    break;  # don't set BASE_URL blank, because that's probably not what the admin meant
                                }
                            }
                            $general_config[$k] = trim($value);
                            log_message('debug',"general_config['" + $k + "'] is " . $general_config[$k]);
                            $k = '';
                            break;
                        }
                }
                if ($k) {
                  $general_db_config[$k] = trim($value);
                  log_message('debug', "\$general_db_config[$k] is " .  $general_db_config[$k]);
                }
            }
        }         
    	if ($config_type === 'db') {
    	    return $general_db_config;
    	} else {
    	    return $general_config;
    	}
	}
}


/**
 * is_config_true
 *
 * returns true if the config value (from the database's config_settings) is true
 *
 * @access	public
 * @param	string $raw_value the config setting's value (typically read from the database)
 * @return	boolean TRUE if the value represents a true/confirmed setting
 */	
if ( ! function_exists('is_config_true')) {
	function is_config_true($raw_value) {
		$config_value = strtolower(trim($raw_value));
		return ($config_value != '' && $config_value != '0' && $config_value != 'f' && $config_value != 'false' && $config_value != 'no');
	}
}

/**
 * open311_enabled_or_error
 *
 * returns true if the Open311 server is enabled (checks the config setting directly)
 * 
 * @access	public
 * @return	none, but throws error
 */	
if ( ! function_exists('open311_enabled_or_error')) {
	function open311_enabled_or_error() {
		$CI =& get_instance();
		$config_result = $CI->db->get_where('config_settings', array('name' => 'enable_open311_server'), 1);
		if (! is_config_true($config_result->row()->value)) {
			show_error('This Open311 server is currently disabled', 404 );
    	}
	}
}

/**
 * show_error_xml
 *
 * mimics show_error but with http status and simple error delivered in XML
 * This should be friendlier to FixMyStreet
 * Note: terminates execution
 * 
 * @access	public
 * @return	none, but terminates execution
 */	
if ( ! function_exists('show_error_xml')) {
	function show_error_xml($msg, $code) {
		$CI =& get_instance();
		$error['code'] = $code;
		$error['description'] = $msg;
		//$CI->output->set_content_type('xml');
		$CI->output->set_status_header($code);
		$CI->load->view('error_xml', $error);
		$CI->output->_display(); // explicit flush so we can exit
		exit();
	}
}


?>
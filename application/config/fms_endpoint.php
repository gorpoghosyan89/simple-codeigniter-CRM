<?php 

// set these values for your own installation

$config['cobrand_name'] = '';

// Important:
// if you've got a conf/general.yml file in place, any settings in
// that will be OVERRIDING the settings you see here!
// see ../../../documentation/ALTERNATIVE_CONFIG.md for details
//

$ci=& get_instance();
$ci->load->helper('fms_endpoint');
$config_array = load_general_config();
foreach ($config_array as $key => $value) {
    $config[$key] = $value;
}

?>
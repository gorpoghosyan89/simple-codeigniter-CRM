<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE',  0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE',   0755);
define('DIR_WRITE_MODE',  0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


define('OPEN311_SERVICE_BAD_API_KEY',    403);
define('OPEN311_SERVICE_ID_NOT_FOUND',   404);
define('OPEN311_SERVICE_ID_MISSING',     400);
define('OPEN311_GENERAL_SERVICE_ERROR',  400);
define('OPEN311_EXTERNAL_ID_MISSING',    400); // our own error: similar to Service ID missing 
define('OPEN311_EXTERNAL_ID_DUPLICATE',  400); // our own error 
define('OPEN311_ATTRIBUTE_EXTERNAL_ID',  'external_id'); // will be overrridden by config setting in db if present
define('REPORT_DEFAULT_STATUS',          'new'); // new Open311 requests into FMS-endpoint have this status
define('REPORT_UNKNOWN_STATUS_ID',       0); // used for incoming reports with unrecognised status 

define('FMSE_DEFAULT_REPORT_PRIORITY',   0); // default priority is id=0 which should map to "Normal"
/* End of file constants.php */
/* Location: ./system/application/config/constants.php */
<?php

define("CAMPAIGNER_LEON_ROOT", dirname(__FILE__) . '/');
define("CAMPAIGNER_SPATEL", CAMPAIGNER_LEON_ROOT . '../../../spatel/');
define("CAMPAIGNER_SUBCTR", CAMPAIGNER_LEON_ROOT . '../../../subctr.popularliving.com/');
define("CAMPAIGNER_ADMIN", CAMPAIGNER_LEON_ROOT . '../../../admin.popularliving.com/');

define('SERVER_RUN', false);
// Set the upload size limitation to 64M
//ini_set('post_max_size', '64M');
//ini_set('upload_max_filesize', '64M');
ini_set('memory_limit', '1024M');
//ini_set('max_input_time', '300');
//ini_set('max_execution_time', '300');
define('SOAP_RESPONSE_TRACK',false);
define('MAIL_RESULT', true);



date_default_timezone_set('America/Chicago');

// Include the DB config file
//require_once(CAMPAIGNER_SPATEL . 'config.php');

// Initialize the DB connection
global $db_link;
$db_link = mysql_connect ("8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com", "jingshi", "kendeji12306!");
mysql_select_db('arcamax',$db_link);


?>

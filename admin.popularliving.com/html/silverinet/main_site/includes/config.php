<?php

ini_set('arg_separator.output','&amp;');		// if php adds "&", use &amp;

// What is today?
//$today = date("m/d/Y"); 
define(TODAY,date("m/d/Y"));



define(HOST,'mydb01.amperemedia.com');
define(DBASE,'silverinet');
define(USER,'silverinet');
define(PASSWORD,'84hAp0');

$link = mysql_connect (HOST, USER, PASSWORD);  
if ( ! $link ) {
    error('Cannot Connect to DB config', $_SERVER[HTTP_HOST], "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
}

if ( ! mysql_select_db (DBASE) ) {
    error('Cannot Select DB', $_SERVER[HTTP_HOST], "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]");
}



$_CONFIG['domain_name'] = 'silverinet.com';
$_CONFIG['page_title'] = 'SilverINET';



$_CONFIG['test_ips'] = array(
			'67.162.119.180'
			);
		
?>

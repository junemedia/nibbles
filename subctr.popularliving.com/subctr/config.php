<?php

/*
while (list($key,$val) = each($_POST)) { $$key = $val; }
while (list($key,$val) = each($_GET)) { $$key = $val; }
*/


if (trim($_SERVER['HTTP_HOST']) == 'sf.popularliving.com') {
	$host = 'sf';
	$_SESSION['subcampid'] = '3507';
	$subcampid = '3507';	// keep this as quick fix
	$other_host = 'r4l';
	$google_analytics = "UA-44287917-1";
}
if (trim($_SERVER['HTTP_HOST']) == 'r4l.popularliving.com') {
	$host = 'r4l';
	$_SESSION['subcampid'] = '2767';
	$subcampid = '2767';	// keep this as quick fix
	$other_host = 'fitfab';
	$google_analytics = "UA-1200417-21";
}
if (trim($_SERVER['HTTP_HOST']) == 'fitfab.popularliving.com') {
	$host = 'fitfab';
	$_SESSION['subcampid'] = '2752';
	$subcampid = '2752';	// keep this as quick fix
	$other_host = 'r4l';
	$google_analytics = "UA-10900002-7";
}
if (trim($_SERVER['HTTP_HOST']) == 'wim.popularliving.com') {
	$host = 'wim';
	$_SESSION['subcampid'] = '3078';
	$subcampid = '3078';	// keep this as quick fix
	$other_host = 'r4l';
	$google_analytics = "UA-33504465-1";
}
if (trim($_SERVER['HTTP_HOST']) == 'br.popularliving.com') {
	$host = 'br';
	$_SESSION['subcampid'] = '4223';
	$subcampid = '4223';	// keep this as quick fix
	$other_host = 'r4l';
	$google_analytics = "UA-33504465-1";
}

$_SESSION['r4l_subcampid'] = '2767';
$_SESSION['fitfab_subcampid'] = '2752';
$_SESSION['wim_subcampid'] = '3078';
$_SESSION['sf_subcampid'] = '3507';
$_SESSION['br_subcampid'] = '4223';


$_SESSION['r4l_all_listid'] = array('393','396','395','394','511','539','554','574','502','501','503','500');
$_SESSION['fitfab_all_listid'] = array('411','410','448');
$_SESSION['wim_all_listid'] = array('553','558');
$_SESSION['sf_all_listid'] = array('583','508');
$_SESSION['br_all_listid'] = array('504','505');


$user_ip = trim($_SERVER['REMOTE_ADDR']);
$_SESSION['user_ip'] = $user_ip;

/* EMAIL REPORT RECIPIENTS*/
$process_bounce_log_email = "samirp@junemedia.com,leonz@junemedia.com,hillarym@junemedia.com";
$feed_back_loop_report = "samirp@junemedia.com,leonz@junemedia.com,hillarym@junemedia.com";
$bounce_out_report = "samirp@junemedia.com,leonz@junemedia.com,hillarym@junemedia.com";


mysql_pconnect ("192.168.51.33", "root", "5dsa234Y");
mysql_select_db ("arcamax");


// if user is listed in banned table, exit.
$banned_ip = "SELECT * FROM bannedIP WHERE ipaddr=\"$user_ip\" LIMIT 1";
$banned_ip_result = mysql_query($banned_ip);
if (mysql_num_rows($banned_ip_result) == 1) {
	echo '<!-- ip banned -->'.exit;
}

include_once("functions.php");

?>

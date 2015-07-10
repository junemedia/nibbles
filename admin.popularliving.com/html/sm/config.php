<?php

$host = "localhost" ;

$dbase = "myfree" ;

$user = "cory" ;

$pass = "#a!!yu5" ;

// DO NOT CHANGE THESE TWO LINES!
mysql_connect ('localhost', $user, $pass);
mysql_select_db ($dbase);

$webRoot = "/var/www/html/sm" ;
$showMeSiteRoot="http://cory.myfree.com/sm";
$adminPasswd = "showme";

// Write header and footer file name to include it.
$showMeHeader = "";
$showMeFooter = "";

// Choose Display Mode
//$displayMode = "email"; // OR
$displayMode = "popAndEmail";

// If user forced to click a button for each offer , fill YES
$forceShowMe = "YES";

// Script name to Proceed to 
$proceedTo = "http://www.myfree.com/index.php";

// set email from address
$emailFrom = "showme@myfree.com";

include("queryString.php");

function ascii_encode($string)  {
	for ($i=0; $i < strlen($string); $i++) {
		$encoded .= '&#'.ord(substr($string,$i)).';';
	}
	return $encoded;
}

?>
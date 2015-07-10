<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');

$PHPSESSID = $_GET['PHPSESSID'];
if(!($PHPSESSID))
	exit();

session_start();

// this script just decrement the page position in the flow.
// when user comes back to our site from 3rd party page, we increment the count and reload the page
// and reloading the page increments the count so we call this script to decrement the count.
// Samir Patel - 9*21*2006
//$stemp = "old: ".$_SESSION['iSesCurrentPositionInFlow'];


$_SESSION['iSesCurrentPositionInFlow']--;

//$stemp .= "new: ".$_SESSION['iSesCurrentPositionInFlow'];
//echo $stemp;

?>


<?php


/*********

Script for Pixel Tracking

*********/

include("../includes/paths.php");

if((!($src) && $s)) {
	$src=$s;
}

$sInsertQuery = "INSERT INTO edOfferPixelsTracking(openDate, offerCode, IPAddress)
				VALUES(CURRENT_DATE, '$src', '".$_SERVER['REMOTE_ADDR']."')";

$rResult = dbQuery($sInsertQuery);
//echo dbError();
?>
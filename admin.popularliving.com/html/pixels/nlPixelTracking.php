<?php


/*********

Script for Pixel Tracking

*********/

include("../includes/paths.php");

$sInsertQuery = "INSERT INTO nibbles.nlPixelsTracking(openDate, nlCode, ipAddress)
				VALUES(CURRENT_DATE, '$src', '".$_SERVER['REMOTE_ADDR']."')";

$rResult = dbQuery($sInsertQuery);
//echo dbError();
?>
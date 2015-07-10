<?php

//********* Script for Pixel Tracking   ***********

include("../includes/paths.php");

if((!($src) && $s)) {
	$src = $s;
}

$sRateQuery = "SELECT rate FROM links WHERE sourceCode='$src'";
$rRateResult = dbQuery($sRateQuery);
$sRateRow = mysql_fetch_object($rRateResult);
$iRate = $sRateRow->rate;

$sInsertQuery = "INSERT INTO nibbles.bdPixelsTracking(openDate, sourceCode, ipAddress, revenue)
				VALUES(CURRENT_DATE, '$src', '".$_SERVER['REMOTE_ADDR']."', '$iRate')";
$rResult = dbQuery($sInsertQuery);

?>

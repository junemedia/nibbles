<?php

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Froogle Datafeed";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	$imageFileUrl = $sGblMhlSiteRoot."/images/product";

	$productUrl = $sGblMhlSiteRoot."/detail.php";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_select_db ($dbase); 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View_Report: Froogle Datafeed\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	// end of track users' activity in nibbles		

	
$froogleDataQuery = "SELECT *
					 FROM   products
					 ORDER BY prName";
$froogleDataResult = dbQuery($froogleDataQuery);

$numRecords = dbNumRows($froogleDataResult);
		
		
			//$exportData = "<table>";			
			while ($row = dbFetchObject($froogleDataResult)) {
				$prUrl = $productUrl."/prID/$row->prID";
				$prImageUrl = $imageFileUrl."/$row->prImage";
				$exportData .= "\"".$prUrl."\",\"".$row->prName."\",\"".$row->prDescription."\",\"".$row->prOurPrice."\",\"".$prImageUrl."\"\n";				
			}
			$exportData .= "Total\t$totalOpens\t";
			//$exportData .= "</table>";
			header("Content-type: text/plain");
			header("Content-Disposition: attachment; filename=mhlData.csv");
			header("Content-Description: CSV Output");
			echo $exportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();

				
} 				
?>
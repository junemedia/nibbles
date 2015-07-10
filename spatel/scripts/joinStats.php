<?php

// script to make daily entry in join stats summary table

// make entry into cron script status table

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "joinStats.php" );


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");



// set date to count stat for yesterdsy's date

$sStatDate = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

$sDateTimeFrom = $sStatDate." 00:00:00";
$sDateTimeTo = $sStatDate." 23:59:59";

//get active list counts

$sReportQuery1 = "SELECT count(email) as listCount, count( distinct email) as uniqueListCount
	   			  FROM   joinEmailActive";
			
$rReportResult1 = dbQuery($sReportQuery1);
echo dbError();

while ($oReportRow = dbFetchObject($rReportResult1)) {	
	$iListCount = $oReportRow->listCount;
	$iUniqueListCount = $oReportRow->uniqueListCount;

}

// get sub counts
$sSubCountQuery = "SELECT count(email) as subCount, count( distinct email) as uniqueSubCount
				   FROM   joinEmailSub
				   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
$rSubCountResult = dbQuery($sSubCountQuery);
//echo "\n".$sSubCountQuery.dbError();
while ($oSubCountRow = dbFetchObject($rSubCountResult)) {
	$iSubCount = $oSubCountRow->subCount;
	$iUniqueSubCount = $oSubCountRow->uniqueSubCount;
}

// get confirm counts
$sConfirmCountQuery = "SELECT count(email) as confirmCount, count( distinct email) as uniqueConfirmCount
				   FROM   joinEmailConfirm
				   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
$rConfirmCountResult = dbQuery($sConfirmCountQuery);
//echo "\n".$sSubCountQuery.dbError();
while ($oConfirmCountRow = dbFetchObject($rConfirmCountResult)) {
	$iConfirmCount = $oConfirmCountRow->confirmCount;
	$iUniqueConfirmCount = $oConfirmCountRow->uniqueConfirmCount;
}


// get unsub counts
$sPurgeCountQuery = "SELECT count(email) as purgeCount, count( distinct email) as uniquePurgeCount
				   FROM   joinEmailUnsub
				   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
				   AND	  isPurge = '1'";
$rPurgeCountResult = dbQuery($sPurgeCountQuery);
//echo "\n".$sUnsubCountQuery.dbError();
while ($oPurgeCountRow = dbFetchObject($rPurgeCountResult)) {
	$iPurgeCount = $oPurgeCountRow->purgeCount;
	$iUniquePurgeCount = $oPurgeCountRow->uniqueUnsubCount;
}


// get unsub counts
$sUnsubCountQuery = "SELECT count(email) as unsubCount, count( distinct email) as uniqueUnsubCount
				   FROM   joinEmailUnsub
				   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
				   AND	  isPurge = ''";
$rUnsubCountResult = dbQuery($sUnsubCountQuery);
//echo "\n".$sUnsubCountQuery.dbError();
while ($oUnsubCountRow = dbFetchObject($rUnsubCountResult)) {
	$iUnsubCount = $oUnsubCountRow->unsubCount;
	$iUniqueUnsubCount = $oUnsubCountRow->uniqueUnsubCount;
}


// get confirm counts
$sHeldCountQuery = "SELECT count(email) as heldCount, count( distinct email) as uniqueHeldCount
				   FROM   joinEmailHeldJournal
				   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
$rHeldCountResult = dbQuery($sHeldCountQuery);
//echo "\n".$sConfirmCountQuery.dbError();
while ($oHeldCountRow = dbFetchObject($rHeldCountResult)) {
	$iHeldCount = $oHeldCountRow->heldCount;
	$iUniqueHeldCount = $oHeldCountRow->uniqueHeldCount;
}

// check if same data's record exists

$sCheckQuery = "SELECT * 
				FROM   joinEmailStats
				WHERE  dateAdded = '$sStatDate'";
$rCheckResult = dbQuery($sCheckQuery);
if (dbNumRows($rCheckResult) == 0) {
	$sInsertQuery = "INSERT INTO joinEmailStats(dateAdded, listCount, subCount, purgeCount, unsubCount, confirmCount, heldCount, 
									uniqueListCount, uniqueSubCount, uniquePurgeCount, uniqueUnsubCount, uniqueConfirmCount, uniqueHeldCount)
					 VALUES('$sStatDate', '$iListCount', '$iSubCount', '$iPurgeCount', '$iUnsubCount', '$iConfirmCount', '$iHeldCount', 
							'$iUniqueListCount', '$iUniqueSubCount', '$iUniquePurgeCount', '$iUniqueUnsubCount', '$iUniqueConfirmCount', '$iUniqueHeldCount')";
	$rInsertResult = dbQuery($sInsertQuery);
} else {
	$sUpdateQuery = "UPDATE joinEmailStats
					 SET    listCount = '$iListCount',
							subCount = '$iSubCount',
							purgeCount = '$iPurgeCount',
							unsubCount = '$iUnsubCount',
							confirmCount = '$iConfirmCount',
							heldCount = '$iHeldCount',
							uniqueListCount = '$iUniqueListCount',
							uniqueSubCount = '$iUniqueSubCount',
							uniquePurgeCount = '$iUniquePurgeCount',
							uniqueUnsubCount = '$iUniqueUnsubCount',
							uniqueConfirmCount = '$iUniqueConfirmCount',
							uniqueHeldCount = '$iUniqueHeldCount'	
					 WHERE  dateAdded = '$sStatDate'";
	$rUpdateResult = dbQuery($sUpdateQuery);
	
}

echo dbError();


cssLogFinish( $iScriptId );

?>

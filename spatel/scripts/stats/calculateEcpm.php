<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));


$sGetOfferStats = "SELECT offerCode, sum(displayCount) as displayCount
					FROM offerStats
					WHERE displayDate = '$sYesterday'
					GROUP BY offerCode
					ORDER BY offerCode ASC";
$rOfferStats = dbQuery($sGetOfferStats);
echo dbError();
while ($oTempRow = dbFetchObject($rOfferStats)) {
	$fOfferEcpm = 0;
	$iCount = 0;

	$sGetCount = "SELECT count(*) as count
				FROM  otDataHistory 
				WHERE processStatus != 'R'
				AND dateTimeAdded >= '$sYesterday 00:00:00'
				AND offerCode = '$oTempRow->offerCode'
				AND pageId !='238'";
	$rGetCount = dbQuery($sGetCount);
	while ($oCountRow = dbFetchObject($rGetCount)) {
		$iCount = $oCountRow->count;
	}
	
	// Get Revenue Per Lead
	$sGetRevPerLead = "SELECT revPerLead FROM offers WHERE offerCode = \"$oTempRow->offerCode\"";
	$rRevPerLead = dbQuery($sGetRevPerLead);
	$oRevRow = dbFetchObject($rRevPerLead);
	$iTotalRev = $oRevRow->revPerLead * $iCount;

	
	// Calculate ECPM
	$fOfferEcpm = ($iTotalRev * 1000) / $oTempRow->displayCount;
	$fOfferEcpm = sprintf("%10.2f",round($fOfferEcpm, 2));


	$sCheckQuery = "SELECT * FROM   offerStatsWorking
					WHERE  offerCode = \"$oTempRow->offerCode\"
					AND    displayDate = '$sYesterday'";
	$rCheckResult = dbQuery($sCheckQuery);

	if ( dbNumRows($rOfferStatCheckResult) == 0 ) {
		$sInsertQuery = "INSERT IGNORE INTO offerStatsWorking (offerCode,displayDate,displayCount,ecpmTotal)
				VALUES (\"$oTempRow->offerCode\",'$sYesterday','$oTempRow->displayCount','$fOfferEcpm')";
		$rInsertResult = dbQuery($sInsertQuery);
		echo dbError();
	} else {
		$sUpdateQuery = "UPDATE offerStatsWorking
						 SET    displayCount = displayCount + $oTempRow->displayCount,
						 ecpmTotal = ecpmTotal + $fOfferEcpm
						 WHERE  offerCode = \"$oTempRow->offerCode\"
						 AND    displayDate = '$sYesterday'";
		$rUpdateResult =  dbQuery($sUpdateQuery);
		echo dbError();
	}
}



?>

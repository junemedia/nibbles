<?php

include("/var/www/config.php");



// Make all the offers as Not Live which has mode other than active or the date is not between inactive date

$sUpdateQuery = "UPDATE offers
				 SET    isLive = '0'
				 WHERE  mode != 'A'
				 OR     CURRENT_TIME NOT BETWEEN activeDateTime AND inactiveDateTime";
$rUpdateResult = mysql_query($sUpdateQuery);


// Make offer Live or Not Live if it's in active mode and its within the date range

$sOffersQuery = "SELECT offers.isLive, offers.isCap, capCounts.*
				FROM   offers LEFT JOIN capCounts ON offers.offerCode=capCounts.offerCode
				WHERE  mode = 'A'
				AND    CURRENT_TIME BETWEEN activeDateTime AND inactiveDateTime";
$rOffersResult = mysql_query($sOffersQuery);
echo mysql_error();
while ($oOffersRow = mysql_fetch_object($rOffersResult)) {
	$sOfferCode = $oOffersRow->offerCode;
	//$sMode = $oOffersRow->mode;
	$iIsLive = $oOffersRow->isLive;
	$iIsCap = $oOffersRow->isCap;
	$sCapStartDate = $oOffersRow->capStartDate;
	$iMaxCap = $oOffersRow->maxCap;
	$sCap1PeriodType = $oOffersRow->cap1PeriodType;
	$iCap1PeriodInterval = $oOffersRow->cap1PeriodInterval;
	$iCap1Max = $oOffersRow->cap1Max;
	$sCap2PeriodType = $oOffersRow->cap2PeriodType;
	$iCap2Max = $oOffersRow->cap2Max;
	$iTotalCounts = $oOffersRow->totalCounts;
	$iCap1Counts = $oOffersRow->cap1Counts;
	$iCap2Counts = $oOffersRow->cap2Counts;
	
	
	//echo $iCap1PeriodInterval;
	if ($iIsCap == 0) {
		
		$sOfferUpdateQuery = "UPDATE offers
						 SET    isLive = '1'
						 WHERE  offerCode = '$sOfferCode'";		
		$rOfferUpdateResult = mysql_query($sOfferUpdateQuery);
		
	} else {
		
		// check cap counts
		
		//get counts from current table
		$sCurrentCountsQuery = "SELECT count(*) AS counts
							   FROM   otData
							   WHERE  offerCode = '$sOfferCode'";
		$rCurrentCountsResult = mysql_query($sCurrentCountsQuery);
		while ($oCurrentCountsRow = mysql_fetch_object($rCurrentCountsResult)) {
			$iCurrentCounts = $oCurrentCountsRow->counts;
		}
		
		// Add current count to all the counts
		$iTotalCounts += $iCurrentCounts;
		$iCap1Counts += $iCurrentCounts;
		$iCap2Counts += $iCurrentCounts;
		
		// make offer down if any of the cap limit is reached
		
		if (($iMaxCap !=0 && $iTotalCounts >= $iMaxCap) || 
			($iCap1PeriodInterval != 0 && $iCap1Max !=0 && $iCap1Counts >= $iCap1Max) ||
			($iCap2PeriodInterval != 0 && $iCap2Max !=0 && $iCap2Counts >= $iCap2Max) ) {
			
			$sOfferUpdateQuery = "UPDATE offers
								  SET    isLive = 0
								  WHERE  offerCode = '$sOfferCode'";
			$rOfferUpdateResult = mysql_query($sOfferUpdateQuery);
						
		} 
	}	
	
}

?>
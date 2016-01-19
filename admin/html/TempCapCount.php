<?php

// script to update cap counts
// script should be run immediately after postal verification process to update counts

$sOffersQuery = "SELECT offers.isLive, offers.isCap, capCounts.*
				FROM   offers LEFT JOIN capCounts ON offers.offerCode=capCounts.offerCode";

$rOffersResult = mysql_query($sOffersQuery);
while ($oOffersRow = mysql_fetch_object($rOffersResult)) {

	$sOfferCode = $oOffersRow->offerCode;
	$sCapStartDate = $oOffersRow->capStartDate;
	$iMaxCap = $oOffersRow->maxCap;
	$sCap1PeriodType = $oOffersRow->cap1PeriodType;
	$iCap1PeriodInterval = $oOffersRow->cap1PeriodInterval;
	$iCap1Max = $oOffersRow->cap1Max;
	$sCap2PeriodType = $oOffersRow->cap2PeriodType;
	$iCap2Max = $oOffersRow->cap2Max;
	
	
$sCheckMaxQuery = "SELECT count(*) counts
						  FROM   otData
						  WHERE  offerCode = '$sOfferCode'
						  AND  dateTimeAdded >= $capStartDate";
		$rCheckMaxResult = mysql_query($sCheckMaxQuery);
		while ($oCheckMaxRow = mysql_fetch_object($rCheckMaxResult)) {
			$iTotalCounts = $oCheckMaxRow->counts;
		}
		
		$sCheckMaxHistoryQuery = "SELECT count(*) counts
						  FROM   otDataHistory
						  WHERE  offerCode = '$sOfferCode'
						  AND    dateTimeAdded >= $capStartDate";
		$rCheckMaxHistoryResult = mysql_query($sCheckMaxHistoyrQuery);
		while ($oCheckMaxHistoryRow = mysql_fetch_object($rCheckMaxHistoryResult)) {
			$iTotalCounts += $oCheckMaxHistoryRow->counts;
		}
		
		if ($iCap1PeriodInterval != 0 && $iCap1Max != 0) {
			if ($sCap1PeriodType =='W') {
								
				$sCap1Condition = "AND WEEK(dateTimeProcessed,1) = WEEK(CURRENT_DATE,1)
							   AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else if ($sCap1PeriodType =='M') {
				
				$sCap1Condition = "AND  MONTH(dateTimeProcessed) = MONTH(CURRENT_DATE)
							   AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else if ($sCap1PeriodType =='Y') {
				
				$sCap1Condition = "AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else {				
				// if daily cap limit, check count only from current table and not from history table
			}
						
			
			$sCheckCap1CountsQuery = "SELECT count(*) counts
						  FROM   otDataHistory
						  WHERE  dateTimeAdded >= $capStartDate
						  AND    postalVerified = 'V' ";
			$sCheckCap1CountsQuery .= $sCap1Condition;
			
			$rCheckCap1CountsResult = mysql_query($sCheckCap1CountsQuery);
			while ($oCheckCap1CountsRow = mysql_fetch_object($rCheckCap1CountsResult)) {
				$iCap1TotalCounts = $oCheckCap1CountsRow->counts;
			}
						
		}
		
		if ($iCap2PeriodInterval != 0 && $iCap2Max != 0) {
		
		// get cap2 counts
			if ($sCap2PeriodType =='W') {
								
				$sCap2Condition = "AND WEEK(dateTimeProcessed,1) = WEEK(CURRENT_DATE,1)
							   AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else if ($sCap2PeriodType =='M') {
				
				$sCap2Condition = "AND  MONTH(dateTimeProcessed) = MONTH(CURRENT_DATE)
							   AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else if ($sCap2PeriodType =='Y') {
				$sCap2Condition = "AND  YEAR(dateTimeProcessed) = YEAR(CURRENT_DATE)";
				
			} else {				
				// if daily cap limit, check count only from current table and not from history table
			}
						
			
			$sCheckCap2CountsQuery = "SELECT count(*) counts
						  FROM   otDataHistory
						  WHERE  dateTimeAdded >= $capStartDate";
			$sCheckCap2CountsQuery .= $sCap2Condition;
			
			$rCheckCap2CountsResult = mysql_query($sCheckCap2CountsQuery);
			while ($oCheckCap2CountsRow = mysql_fetch_object($rCheckCap2CountsResult)) {
				$iCap2TotalCounts = $oCheckCap2CountsRow->counts;
			}
						
		}
		
		
		
		// update the capCounts for an offer
		
		$sCountsUpdateQuery = "UPDATE capCounts
							   SET    totalCounts = '$iTotalCounts',
									  cap1Counts = '$iCap1TotalCounts',
									  cap2Counts = '$iCap2TotalCounts'
							   WHERE  offerCode = '$sOfferCode'";
		$rCountsUpdateResult = mysql_query($sCountsUpdateQuery);
					
		
} // end of offer row while loop


?>
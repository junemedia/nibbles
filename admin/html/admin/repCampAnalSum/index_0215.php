<?php

/*********

Script to Display 

**********/
    
include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

set_time_limit(500);


$iScriptStartTime = getMicroTime();

$sPageTitle = "OT Campaign Analysis Summary Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	

		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

$sViewReport = stripslashes($sViewReport);

if (! $sViewReport) {
	$sViewReport = "Today's Report";
}

if ($sViewReport != "Today's Report") {
	/*
	if (!($iYearFrom)) {
	$iYearFrom = substr( $sYesterday, 0, 4);
		$iMonthFrom = substr( $sYesterday, 5, 2);
		$iDayFrom = "01";
		
		$iYearTo = $iYearFrom;
		$iMonthTo = $iMonthFrom;
		$iDayTo = substr($sYesterday,8,2);
	}
	*/
	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		}
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = "01";
		}
		
	
	
} else  {
	$iYearFrom = date('Y');
	$iMonthFrom = date('m');
	$iDayFrom = date('d');
		
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
	$iYearTo = $iYearFrom;
	/*if (!($iYearFrom)) {
		
	}*/
}

	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
			
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}
		
	
if ( checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
	
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		
	// initialize all valirables to 0;
	$iUniqueUsers = 0;
	$iUniqueUsersNonPv = 0;
	$iOffersTakenByPvUsers = 0;
	$fAvgOffersTakenPerUser = 0;
	$fGrossRevenue = 0;
	$fGrossPvRevenue = 0;
	$iTotalOffersTakenByUsers = 0;
	$fConversionRate = 0;
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";	
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";	
	
	if ($sPartnerCode != '') {
		
		// get partnerId
		$sPartnerQuery = "SELECT *
						  FROM   partnerCompanies
						  WHERE  code = '$sPartnerCode'";
		$rPartnerResult = dbQuery($sPartnerQuery) ;
		while ($oPartnerRow = dbFetchObject($rPartnerResult)) {
			$iPartnerId = $oPartnerRow->id;
		}
		
		$sUniqueUsersNonPvQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsersNonPv
							  FROM    otDataHistory, campaigns
							  WHERE    otDataHistory.sourceCode = campaigns.sourceCode
							  AND      campaigns.partnerId = '$iPartnerId'
							  AND      otDataHistory.postalVerified != 'V'
							  AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
	} else {
	
		$sUniqueUsersNonPvQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsersNonPv
						  FROM    otDataHistory
						  WHERE   otDataHistory.postalVerified != 'V'
						  AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom'
 						  AND 	'$sDateTimeTo'";
	}
	
	if ($sSourceCode != '') {
		$sUniqueUsersNonPvQuery .= " AND      sourceCode = '$sSourceCode'";
	}
	
	//$sUniqueUsersNonPvQuery .= " GROUP BY userDataHistory.email";
	//echo $sUniqueUsersNonPvQuery;
	$rUniqueUsersNonPvResult = dbQuery($sUniqueUsersNonPvQuery);
	
	while ($oUniqueUsersNonPvRow = dbFetchObject($rUniqueUsersNonPvResult)) {
		$iUniqueUsersNonPv = $oUniqueUsersNonPvRow->uniqueUsersNonPv;		
		
	}	
	if ($rUniqueUsersNonPvResult) {
		dbFreeResult($rUniqueUsersNonPvResult);
	}
	
	if ($sPartnerCode != '') {		
		if ($sViewReport != 'History Report') {
		$sUniqueUsersQuery = "SELECT count(distinct otData.email) AS uniqueUsers
    						  FROM    otData, userData, campaigns, partnerCompanies
    						  WHERE   otData.email = userData.email
		 					  AND	  otData.sourceCode = campaigns.sourceCode
							  AND     campaigns.partnerId  = '$iPartnerId'    	
							  AND	  address NOT LIKE \"3401 Dundee%\"
							  AND     otData.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		} else {
			$sUniqueUsersQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsers
    						  FROM    otDataHistory, campaigns
    						  WHERE   otDataHistory.sourceCode = campaigns.sourceCode
							  AND      campaigns.partnerId = '$iPartnerId'
    						  AND      otDataHistory.postalVerified = 'V'
							  AND      verified != 'I'
							  AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";			
		}
	} else {
		if ($sViewReport != 'History Report') {
		$sUniqueUsersQuery = "SELECT count(distinct otData.email) AS uniqueUsers
    						  FROM    otData, userData
    						  WHERE   otData.email = userData.email
							  AND	  address NOT LIKE \"3401 Dundee%\"
							  AND	  otData.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		} else {
		$sUniqueUsersQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsers
    						  FROM   otDataHistory
    						  WHERE  otDataHistory.postalVerified = 'V'
		 					  AND    verified != 'I'
							  AND    otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";	
		}
	}
	if ($sSourceCode != '') {
		$sUniqueUsersQuery .= " AND  sourceCode = '$sSourceCode'";
	}
		
	$rUniqueUsersResult = dbQuery($sUniqueUsersQuery);
		
	while ($oUniqueUsersRow = dbFetchObject($rUniqueUsersResult)) {
		//echo "<BR>".$oUniqueUsersRow->uniqueUsers;
		$iUniqueUsers = $oUniqueUsersRow->uniqueUsers;
	}
	
	if ($rUniqueUsersResult) {
		dbFreeResult($rUniqueUsersResult);
	}
	
	
	//echo "<BR>1 ".$sUniqueUsersQuery;
	
	//echo "uniq".$iUniqueUsers;
	$iGrossUniqueUsers = $iUniqueUsers + $iUniqueUsersNonPv;
	if ($iGrossUniqueUsers != 0 && $iGrossUniqueUsers != '') {
		$fPercentPv = ($iUniqueUsers * 100) / $iGrossUniqueUsers;
		$fPercentPv = sprintf("%6.2f",round($fPercentPv, 2));
	} else {
		$fPercentPv = "N/A";
	}
	if ($sViewReport == 'History Report') {
	if ($sPartnerCode != '') {
		//if ($sViewReport == 'History Report') {
	$sGrossRevenueQuery = "SELECT (count(otDataHistory.email) * otDataHistory.revPerLead) AS grossRevenue
      					   FROM    otDataHistory, campaigns
      					   WHERE   otDataHistory.sourceCode = campaigns.sourceCode
 						   AND      campaigns.partnerId = '$iPartnerId'
      					   AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND	'$sDateTimeTo'";
		//}
	} else {
		//if ($sViewReport == 'History Report') {
			$sGrossRevenueQuery = "SELECT   (count(otDataHistory.email) * otDataHistory.revPerLead) AS grossRevenue
      						   FROM    otDataHistory
      						   WHERE   otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		//}
	}
	if ($sSourceCode != '') {
		$sGrossRevenueQuery .= " AND  sourceCode = '$sSourceCode'";
	}
	
	$sGrossRevenueQuery .= " GROUP BY offerCode";	
	$rGrossRevenueResult = dbQuery($sGrossRevenueQuery);
	//echo $sGrossRevenueQuery;
	while ($oGrossRevenueRow = dbFetchObject($rGrossRevenueResult)) {		
		$fGrossRevenue += $oGrossRevenueRow->grossRevenue;
		//echo "<Br> ".$oGrossRevenueRow->offerCode." - ".$oGrossRevenueRow->grossRevenue;
	}
	
	//echo "<BR>2 ".$sGrossRevenueQuery;
	
	if ($rGrossRevenueResult) {
		dbFreeResult($rGrossRevenueResult);
	}
	
	}
	
	if ($sPartnerCode != '') {
		if ($sViewReport != 'History Report') {
			$sTotalOffersTakenQuery = "SELECT 	offerCode, (count(otData.email) * revPerLead) AS grossRevenue,
												count(otData.email) AS totalOffersTakenByUsers
      								 FROM    otData, userData, campaigns
      								 WHERE   otData.email = userData.email
									 AND	 address NOT LIKE \"3401 Dundee%\"
									 AND	 otData.sourceCode = campaigns.sourceCode									 
	 								 AND     campaigns.partnerId = '$iPartnerId'									
      								 AND     otData.dateTimeAdded BETWEEN '$sDateTimeFrom'
      								 AND '$sDateTimeTo'";
		} else {
				$sTotalOffersTakenQuery = "SELECT 	otDataHistory.offerCode, (count(otDataHistory.email) * otDataHistory.revPerLead) AS grossPvRevenue,
												count(otDataHistory.email) AS totalOffersTakenByPvUsers
      								 FROM    otDataHistory, campaigns
      								 WHERE    otDataHistory.sourceCode = campaigns.sourceCode
	 								 AND      campaigns.partnerId = '$iPartnerId'
									 AND       otDataHistory.postalVerified = 'V'									 
      								 AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom'
      								 AND '$sDateTimeTo'";
		}
	} else {
		if ($sViewReport != 'History Report') {
			$sTotalOffersTakenQuery = "SELECT offerCode, (count(otData.email) * otData.revPerLead) AS grossRevenue,
									 count(otData.email) AS totalOffersTakenByUsers
      							 FROM      otData, userData
      							 WHERE 	    otData.email = userData.email
								 AND		address NOT LIKE \"3401 Dundee%\"								 
								 AND		otData.dateTimeAdded BETWEEN '$sDateTimeFrom'
      							 AND '$sDateTimeTo'";
		} else {
		
		$sTotalOffersTakenQuery = 	"SELECT offerCode, (count(otDataHistory.email) * revPerLead) AS grossPvRevenue,
	        	  					 		count(otDataHistory.email) AS totalOffersTakenByPvUsers
      								 FROM    otDataHistory
      								 WHERE   otDataHistory.postalVerified = 'V'									 
	      							 AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom'  
    	  							 AND '$sDateTimeTo'";
		}
	}
	
	if ($sSourceCode != '') {
		$sTotalOffersTakenQuery .= " AND      sourceCode = '$sSourceCode'";
	}	
	
	$sTotalOffersTakenQuery .= " GROUP BY offerCode";
	
	$rTotalOffersTakenResult = dbQuery($sTotalOffersTakenQuery);
	
	$fGrossPvRevenue = 0;
	echo dbError();
	while ($oTotalOffersTakenRow = dbFetchObject($rTotalOffersTakenResult)) {		
		
		if ($sViewReport == 'History Report') {
			// if history data , get total offers taken by PV users
			$iTotalOffersTakenByPvUsers += $oTotalOffersTakenRow->totalOffersTakenByPvUsers;	
			
			$fGrossPvRevenue += $oTotalOffersTakenRow->grossPvRevenue;	
		} else {		
		// if today's data, get total offers taken by gross users 
			$iTotalOffersTakenByUsers += $oTotalOffersTakenRow->totalOffersTakenByUsers;		
			$fGrossRevenue += $oTotalOffersTakenRow->grossRevenue;
		}
		
		
		
	}
	
	//echo "<BR>3 ".$sTotalOffersTakenQuery;
	
	if ($rTotalOffersTakenResult) {
		dbFreeResult($rTotalOffersTakenResult);
	}
	
		
	if ($sPartnerCode != '') {		
		if ($sViewReport != 'History Report') {
		$sClickQuery = "SELECT count(bdRedirectsTracking.id) as clicks
    						  FROM    campaigns, bdRedirectsTracking
    						  WHERE   bdRedirectsTracking.sourceCode = campaigns.sourceCode
							  AND     campaigns.partnerId  = '$iPartnerId'    						 
							  AND     clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";
		} else {
			$sClickQuery = "SELECT sum(bdRedirectsTrackingHistorySum.clicks) as clicks
    						  FROM    bdRedirectsTrackingHistorySum, campaigns
    						  WHERE   bdRedirectsTrackingHistorySum.sourceCode = campaigns.sourceCode
							  AND      campaigns.partnerId = '$iPartnerId'
							  AND     clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";			
		}
	} else {
		if ($sViewReport != 'History Report') {
		$sClickQuery = "SELECT count(bdRedirectsTracking.id) AS clicks
    						  FROM    bdRedirectsTracking
    						  WHERE   clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";
		} else {
		$sClickQuery = "SELECT sum(bdRedirectsTrackingHistorySum.clicks) AS clicks
    						  FROM   bdRedirectsTrackingHistorySum
    						  WHERE  clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";	
		}
	}
	
	if ($sSourceCode != '') {
		$sClickQuery .= " AND      sourceCode = '$sSourceCode'";
	}
	
	$rClickResult = dbQuery($sClickQuery);
	//echo $sClickQuery.mysql_error();
	while ($oClickRow = dbFetchObject($rClickResult)) {
		$iClicks = $oClickRow->clicks;
	}
	
	//echo "<BR>4 ".$sClickQuery;
	if ($rClickResult) {
		dbFreeResult($rClickResult);
	}
	
	if ($sViewReport == 'History Report') {
	if ($sPartnerCode != '') {
		
		
			$sValidatedOffersQuery = "SELECT offerCode, (count(otDataHistory.email) * revPerLead) AS validatedOffersGrossPvRevenue,
	        		  				 		count(otDataHistory.email) AS validatedOffersTakenByPvUsers
      								 FROM    otDataHistory, campaigns
		      						 WHERE otDataHistory.sourceCode = campaigns.sourceCode
 									 AND      campaigns.partnerId = '$iPartnerId'
									 AND       otDataHistory.postalVerified = 'V' AND processStatus = 'P'									 
      								 AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom'
      								 AND '$sDateTimeTo'";
		
		
	} else {
		
				$sValidatedOffersQuery = "SELECT offerCode, (count(otDataHistory.email) * revPerLead) AS validatedOffersGrossPvRevenue,
	        	  					 		count(otDataHistory.email) AS validatedOffersTakenByPvUsers
      								 FROM    otDataHistory
      								 WHERE  otDataHistory.postalVerified = 'V'    AND processStatus = 'P'									 
	      							 AND     otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom'  
    	  							 AND '$sDateTimeTo'";
		
	}
	
	if ($sSourceCode != '') {
		$sValidatedOffersQuery .= " AND      sourceCode = '$sSourceCode'";
	}
	$sValidatedOffersQuery .= " GROUP BY offerCode";
	
	$rValidatedOffersResult = dbQuery($sValidatedOffersQuery);
	//echo $sGrossPvRevenueQuery;
	while ($oValidatedOffersRow = dbFetchObject($rValidatedOffersResult)) {
		
			$fValidatedOffersGrossPvRevenue += $oValidatedOffersRow->validatedOffersGrossPvRevenue;
			$iValidatedOffersTakenByPvUsers += $oValidatedOffersRow->validatedOffersTakenByPvUsers;
					
		//echo "<BR>$oGrossPvRevenueRow->offerCode $oGrossPvRevenueRow->offersTakenByPvUsers";
	}
	
	//echo "<BR>5 ".$sValidatedOffersQuery;
	if ($rValidatedOffersResult) {
		dbFreeResult($rValidatedOffersResult);
	}
	}	
	
	
	if ($iUniqueUsers !=0) {
		if ($sViewReport != 'History Report') {
			$fAvgOffersTakenPerUser = round($iTotalOffersTakenByUsers / $iUniqueUsers,2);
		} else {
			$fAvgOffersTakenPerUser = round($iTotalOffersTakenByPvUsers / $iUniqueUsers,2);
		}
		$fAvgValidatedOffersTakenPerUser = round($iValidatedOffersTakenByPvUsers / $iUniqueUsers,2);
	} else {
		$fAvgOffersTakenPerUser = 0;
		$fAvgValidatedOffersTakenPerUser = 0;
	}
	
	if ($sPartnerCode == '') {
		$sPartnerCodeDisplay = "All";
	} else {
		$sPartnerCodeDisplay = $sPartnerCode;
	}
	if ($sSourceCode == '') {
		$sSourceCodeDisplay = "All";
	} else {
		$sSourceCodeDisplay = $sSourceCode;
	}	
		
	if ($iClicks) {	
		if ($sViewReport == 'History Report') {
		
			$fConversionRate = ($iUniqueUsers / $iClicks) * 100;
			$fConversionRate = sprintf("%10.2f",round($fConversionRate, 2));
		} else {
		
			$fConversionRate = ($iUniqueUsers / $iClicks) * 100;
			$fConversionRate = sprintf("%10.2f",round($fConversionRate, 2));
		}		
	}

		
	$fGrossRevenue = sprintf("%10.2f",round($fGrossRevenue, 2));
	$fGrossPvRevenue = sprintf("%10.2f", round($fGrossPvRevenue,2));
	$fValidatedOffersGrossPvRevenue = sprintf("%10.2f", round($fValidatedOffersGrossPvRevenue,2));
	
	
	if ($sShowQueries == 'Y') {
		$sShowQueriesChecked = "checked";
	}
	
	if ($sViewReport != 'History Report') {
		if ($sShowQueries == 'Y') {
			$sQueries = "<b>Gross Unique Users Query:</b><BR>".$sUniqueUsersQuery;
			$sQueries .= "<br><br><b>Total Offers Taken By Gross Users AND Gross Revenue Query:</b><BR>".$sTotalOffersTakenQuery;
			$sQueries .= "<br><br><b>Clicks Query:</b><BR>".$sClickQuery;
		}
		
		$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
						<tr><td>
						<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
						<tr><td align=center class=bigHeader colspan=2><BR>OT Campaign Analysis Summary Report <BR>From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo<BR><BR><BR></td></tr>
						<tr><td class=header align=right width=50%>Run Date / Time:</td><td> $sRunDateAndTime</td></tr>
						<tr><td class=header align=right>Partner Code:</td><td> $sPartnerCodeDisplay</td></tr>
						<tr><td class=header align=right>Source Code:</td><td> $sSourceCodeDisplay</td></tr>
						<tr><td class=header align=right>Gross Unique Users:</td><td> $iUniqueUsers</td></tr>						
						<tr><td class=header align=right>Total Offers Taken By Gross Users:</td><td> $iTotalOffersTakenByUsers</td></tr>						
						<tr><td class=header align=right>Clicks:</td><td> $iClicks</td></tr>
						<tr><td class=header align=right>Conversion Rate:</td><td> $fConversionRate %</td></tr>
						<tr><td class=header align=right>Average Offers Taken Per User:</td><td> $fAvgOffersTakenPerUser</td></tr>						
						<tr><td class=header align=right>Gross Revenue:</td><td> \$$fGrossRevenue</td></tr>						
						<tr><td colspan=2><BR></td></tr>
						";
	} else {
		if ($sShowQueries == 'Y') {
			$sQueries = "<b>Unique Users (PV) Query:</b><BR>".$sUniqueUsersQuery;
			$sQueries .= "<br><br><b>Unique Users (Non PV) Query:</b><BR>".$sUniqueUsersNonPvQuery;
			
			$sQueries .= "<br><br><b>Total Offers Taken By PV Users AND PV Revenue Query<BR>(Without Regard To Taking Offer Validation Into Account):</b>
							<BR>".$sTotalOffersTakenQuery;
			$sQueries .= "<br><br><b>Total Validated Offers Taken By PV Users AND PV Revenue Query<BR>(Taking Offer Validation Into Account):</b>
							<BR>".$sValidatedOffersQuery;		
			$sQueries .= "<br><br><b>Clicks Query:</b><BR>".$sClickQuery;	
			$sQueries .= "<br><br><b>Gross Revenue Query:</b><BR>".$sGrossRevenueQuery;	
		}
		
	
	$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
						<tr><td>
						<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
						<tr><td align=center class=bigHeader colspan=2><BR>OT Campaign Analysis Summary Report <BR>From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo<BR><BR><BR></td></tr>
						<tr><td class=header align=right width=50%>Run Date / Time:</td><td> $sRunDateAndTime</td></tr>
						<tr><td class=header align=right>Partner Code:</td><td> $sPartnerCodeDisplay</td></tr>
						<tr><td class=header align=right>Source Code:</td><td> $sSourceCodeDisplay</td></tr>
						<tr><td class=header align=right>Gross Unique Users<BR>(Non PV And PV):</td><td> $iGrossUniqueUsers</td></tr>
						<tr><td class=header align=right>Unique Users PV:</td><td> $iUniqueUsers</td></tr>
						<tr><td class=header align=right>% PV:</td><td> $fPercentPv %</td></tr>
						<tr><td class=header align=right>Total Offers Taken By PV Users:</td><td> $iTotalOffersTakenByPvUsers</td></tr>
						<tr><td class=header align=right>Validated Offers Taken By PV Users:</td><td> $iValidatedOffersTakenByPvUsers</td></tr>
						<tr><td class=header align=right>Clicks:</td><td> $iClicks</td></tr>
						<tr><td class=header align=right>Conversion Rate:</td><td> $fConversionRate %</td></tr>
						<tr><td class=header align=right>Average Offers Taken Per PV User:</td><td> $fAvgOffersTakenPerUser</td></tr>
						<tr><td class=header align=right>Average Validated Offers Taken Per PV User:</td><td> $fAvgValidatedOffersTakenPerUser</td></tr>
						<tr><td class=header align=right>Gross Revenue:<BR>(Includes Non PV And PV)</td><td> \$$fGrossRevenue</td></tr>
						<tr><td class=header align=right>PV Revenue:<BR>(Without Regard To Taking Offer Validation Into Account)</td><td> \$".$fGrossPvRevenue."</td></tr>
						<tr><td class=header align=right>PV Revenue:<BR>(Taking Offer Validation Into Account)</td><td> \$".$fValidatedOffersGrossPvRevenue."</td></tr>
						<tr><td colspan=2><BR></td></tr>";
	}
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
	
	$sReportContent .= "<tr><td colspan=2 class=header><BR>Notes - </td></tr>
						<tr><td colspan=2>Counts will change as postal verification status changes.<BR>
							<BR>Today's Report omits any leads having address starting with '3401 Dundee' considering those as test leads. 
								<BR>Test leads are deleted after midnight and not included in history report.
							<br>Report includes all data even if PV is not attempted.<BR>
							<br>Conversion rate is unique users divided by clicks displayed as Percentage. For today's report it's total unique users, for history report it is unique PV users.<BR>
							<br>Gross Unique Users is count of unique users within selected date range who took one or more offers.
						<BR>Gross Unique Users in Source Analysis Report may be higher than gross unique users in Campaign Analysis Report
					because in Source Analysis Report user will be unique for a source code and same user may be unique user 
					for another source code also if he came up in our site through different source codes resulting the total unique user count higher than 
					Campaign Analysis Report<br>
							<br>Unique Users PV is count of unique users within selected date range who took one or more offers and passed Postal Verification.
							<BR><br>Validated offers taken by Pv users is the count of valid Pv offers after any type of custom verification.
							<BR><BR>Approximate time to run this report - $iScriptExecutionTime seconds
							</td></tr>
						
						<tr><td colspan=2><BR><BR></td></tr>";
	if ($sShowQueries) {
						
			$sReportContent .= "<tr><td colspan=2><b>Queries Used To Prepare This Report:</b><BR><BR>$sQueries</td></tr>
						<tr><td colspan=2><BR><BR></td></tr>";
	}
	$sReportContent .= "</table>
						</td></tr>
						</table>";
	
	if ($sPrintReport) {
		
		$sReportContentHtml = "<html>
						<head>
						<style>
			TD {
	FONT-SIZE: 8pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}

TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 8pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
		
TD.bigHeader {
	FONT-WEIGHT: bold; FONT-SIZE: 12pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
		</style>
		<head>
		<body>".$sReportContent."</body></html>";

		header("Content-type: text/html");
		header("Content-Disposition: attachment; filename=repCampAnalSum.html");
		header("Content-Description: Report output");
		echo $sReportContentHtml;
		// if didn't exit, all the html page content will be saved as excel file.
		exit();
	}
}
}
	
	
	
	include("../../includes/adminHeader.php");	
	
// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>
<script language=JavaScript>

function funcAllSource() {
	if (document.form1.sAllSourceCodes.checked) {
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
	} else {
		document.form1.sSourceCode.disabled = false;
	}
}

function funcAllPartner() {
	if (document.form1.sAllPartnerCodes.checked) {
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
	} else {
		document.form1.sPartnerCode.disabled = false;
	}
	
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'> &nbsp; Exact Match
	<tr><td>Partner Code</td><td colspan=3><input type=text name=sPartnerCode value='<?php echo $sPartnerCode;?>'> &nbsp; Exact Match
	<tr><td colspan=2><input type=button name=sSubmit value='History Report' onClick="funcReportClicked('history');">  &nbsp; &nbsp; 
	<input type=button name=sSubmit value="Today's Report" onClick="funcReportClicked('today');">  &nbsp; &nbsp; 
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td><td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>

<?php echo $sReportContent;?>

</td></tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
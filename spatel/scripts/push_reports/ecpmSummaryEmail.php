<?php

// make entry into cron script status table

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

include("$sGblLibsPath/dateFunctions.php");
$sScriptNotDoneMsg = '';
$sScriptNotDoneNotes = '';
$sTempMsg = '';
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sPageTitle = "ECPM Summary Report";
$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
$iYearTo = substr( $sYesterday, 0, 4);
$iMonthTo = substr( $sYesterday, 5, 2);
$iDayTo = substr( $sYesterday, 8, 2);
$iYearFrom = substr( $sYesterday, 0, 4);
$iMonthFrom = substr( $sYesterday, 5, 2);
$iDayFrom = "01";
		
// send leads sent count report for now
$sLeadsCountCol = "leadsSentCount";	

	
$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";
		

if ($iMonthTo =='1') {
	$iPrevMonthYear = $iYearTo-1;
	$iPrevMonthMonth = "12";
} else {
	$iPrevMonthYear = $iYearTo;
	$iPrevMonthMonth = $iMonthTo -1;
}
if ($iPrevMonthMonth < 10) {
	$iPrevMonthMonth = "0".$iPrevMonthMonth;			
}
		
// specify any date of last month as just place holder
$sPrevMonth = $iPrevMonthYear."-".$iPrevMonthMonth."-25";			
$iPrevMonthNum = substr($sPrevMonth,5,2);
$iPrevMonthNum = round($iPrevMonthNum) - 1;
$sPrevMonthName = $aGblMonthsArray[$iPrevMonthNum];

$sOffersQuery = "SELECT DISTINCT offerLeadsCountSum.offerCode, offers.mode, offers.isLive, 
						offerCompanies.creditStatus, offers.revPerLead, offerCompanies.repDesignated
				 FROM   offerLeadsCountSum, offers, offerCompanies
				 WHERE  offerLeadsCountSum.offerCode = offers.offerCode	
				 AND	offers.companyId = offerCompanies.id
				 AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
				 AND	offerLeadsCountSum.pageId != '238'
				 ORDER BY offerLeadsCountSum.offerCode";
$rOffersResult = dbQuery($sOffersQuery);
echo dbError();
$iRow = 0;
$aPageOffersRevenue = '';
$aCatOffersRevenue = '';
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	$iRow++;
	$sOfferCode = $oOffersRow->offerCode;
	$fRevPerLead = $oOffersRow->revPerLead;
	$sMode = $oOffersRow->mode;
	$iIsLive = $oOffersRow->isLive;
	$sCreditStatus = $oOffersRow->creditStatus;
	$sRepDesignated = $oOffersRow->repDesignated;
	$fOfferRevenueRowTotal = 0;
	$iOffersTakenCount = 0;
	$fPrevMonthOfferRevenueRowTotal = 0;
	$iPrevMonthOffersTakenCount = 0;
	$sTheyHostType = "&nbsp;";
	$sRestrictions = '';
	$sOfferName = '';
	$sOfferRep = '';
			
	$sGetResQuery = "SELECT * FROM offers WHERE offerCode='$sOfferCode'";
	$rGetResResult = dbQuery($sGetResQuery);
	while ($oResRow = dbFetchObject($rGetResResult)) {
		$sRestrictions = substr($oResRow->restrictions,0,25);
		$sOfferName = substr($oResRow->name,0,15);
		$sOfferType = $oResRow->offerType;
	}
			
	if ($sRestrictions == '') { $sRestrictions = "&nbsp;"; }
	if ($sOfferName == '') { $sOfferName = "&nbsp;"; }
	if ($sOfferType == 'OTH') { $sTheyHostType = 'O'; }
	if ($sOfferType == 'CTH') { $sTheyHostType = 'C'; }
	if ($sOfferType == 'OTH_CTH') { $sTheyHostType = 'OC'; }

	// get offer rep here
	if ($sRepDesignated != '') {
		$sRepQuery = "SELECT firstName, lastName
					  FROM   nbUsers
					  WHERE  id IN (".$sRepDesignated.")";
		$rRepResult = dbQuery($sRepQuery);
		echo dbError();
		while ($oRepRow = dbFetchObject($rRepResult)) {
			$sOfferRep .= substr($oRepRow->firstName,0,1).substr($oRepRow->lastName,0,1)." ";
		}
		dbFreeResult($rRepResult);
	}
	if ($sOfferRep == '') { $sOfferRep = "&nbsp;"; }

	// define header if iRow = 1
	if ($iRow == 1) {
		// get current month (last month of selected date range) details
		$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
						FROM   offerLeadsCountSum, offers
						WHERE  offerLeadsCountSum.offerCode = offers.offerCode										
						AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
						AND	offerLeadsCountSum.pageId != '238'";
		$rLeadsResult = dbQuery($sLeadsQuery);
		while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
			$iCurrMonthOffersTakenCount = $oLeadsRow->offersTaken;
			$fCurrMonthRevenue = $oLeadsRow->revenue;
		}
		dbFreeResult($rLeadsResult);
		$fCurrMonthRevenue = sprintf("%12.2f",round($fCurrMonthRevenue, 2));

		$sDisplayQuery = "SELECT sum(opens) AS displayCount
			  		  FROM   pageDisplayStatsSum
			  		  WHERE  openDate BETWEEN '$sDateFrom' AND '$sDateTo'";
		$rDisplayResult = dbQuery($sDisplayQuery);
		while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
			$iCurrMonthDisplayCount += $oDisplayRow->displayCount;
		}
		dbFreeResult($rDisplayResult);

		if ($iCurrMonthDisplayCount == 0) { $iCurrMonthDisplayCount = "&nbsp;"; }

		// get previous month (month before the last month of selected date range) details
		$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
						FROM   offerLeadsCountSum, offers
						WHERE  offerLeadsCountSum.offerCode = offers.offerCode										
						AND    date_format(dateAdded,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')
						AND	offerLeadsCountSum.pageId != '238'";
		$rLeadsResult = dbQuery($sLeadsQuery);
		while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
			$iPrevMonthOffersTakenCount = $oLeadsRow->offersTaken;
			$fPrevMonthRevenue = $oLeadsRow->revenue;
		}
		dbFreeResult($rLeadsResult);
						
		$fPrevMonthRevenueTotal = sprintf("%12.2f",round($fPrevMonthRevenueTotal, 2));
						
		$sDisplayQuery = "SELECT sum(opens) AS displayCount
			  		  FROM   pageDisplayStatsSum
			  		  WHERE  date_format(openDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
		$rDisplayResult = dbQuery($sDisplayQuery);
		while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
			$iPrevMonthDisplayCount += $oDisplayRow->displayCount;
		}
		dbFreeResult($rDisplayResult);
	}
					
	// get no of pages offer is on
	$iNoOfPagesOfferLiveOn = 0;
	$sOfferPagesQuery = "SELECT count(*) AS noOfPages
						 FROM   pageMap
						 WHERE  offerCode = '$sOfferCode'";
	$rOFferPagesResult = dbQuery($sOfferPagesQuery);
	while ($oOfferPagesRow = dbFetchObject($rOFferPagesResult)) {
		$iNoOfPagesOfferLiveOn = $oOfferPagesRow->noOfPages;
	}
					
	$fOfferRevenue = 0;
	$iOfferDisplayCount = 0;					
	$fOfferEcpm = 0;
	// get lead details
	$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
					FROM   offerLeadsCountSum, offers
					WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
					AND    offerLeadsCountSum.offerCode = offers.offerCode									
					AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
					AND	offerLeadsCountSum.pageId != '238'";
	$rLeadsResult = dbQuery($sLeadsQuery);
	while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
		$iOffersTakenCount += $oLeadsRow->offersTaken;
		$fOfferRevenue = $oLeadsRow->revenue;
	}
	dbFreeResult($rLeadsResult);
										
	$fOfferRevenue = sprintf("%10.2f",round($fOfferRevenue, 2));
			
	// get the display details and ecpm for the offer
	$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
				  FROM   offerStatsSum
				  WHERE  offerCode = '$sOfferCode'
				  AND    displayDate BETWEEN '$sDateFrom' AND '$sDateTo' ";
	$rDisplayResult = dbQuery($sDisplayQuery);
	while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
		$iOfferDisplayCount = $oDisplayRow->offerDisplayCount;
	}
			
	if ($iOfferDisplayCount) {
		$fOfferEcpm = ($fOfferRevenue * 1000 )/ $iOfferDisplayCount;
	} else {
		$fOfferEcpm = "0.0";
		$iOfferDisplayCount = "0";
	}
			
	$fOfferEcpm = trim(sprintf("%10.2f",round($fOfferEcpm, 2)));
	
	
	
	
	
	

	// start of getting yesterday's offer ecpm
	$fYesterdayRevenue = 0;
	$iYesterdayOfferDisplayCount = 0;
	$fYesterdayOfferEcpm = 0;
	
	// get lead details
	$sYesterdayLeadsQuery = "SELECT sum($sLeadsCountCol * offers.revPerLead) as revenue
					FROM   offerLeadsCountSum, offers
					WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
					AND    offerLeadsCountSum.offerCode = offers.offerCode									
					AND    dateAdded = '$sYesterday'
					AND	offerLeadsCountSum.pageId != '238'";
	$rYesterdayLeadsResult = dbQuery($sYesterdayLeadsQuery);
	while ($oYesLeadsRow = dbFetchObject($rYesterdayLeadsResult)) {
		$fYesterdayRevenue = $oYesLeadsRow->revenue;
	}
	dbFreeResult($rYesterdayLeadsResult);
	$fYesterdayRevenue = sprintf("%10.2f",round($fYesterdayRevenue, 2));

	// get the display details and ecpm for the offer
	$sYesterdayDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
				  FROM   offerStatsSum
				  WHERE  offerCode = '$sOfferCode'
				  AND    displayDate = '$sYesterday'";
	$rYesDisplayResult = dbQuery($sYesterdayDisplayQuery);
	while ($oYesDisplayRow = dbFetchObject($rYesDisplayResult)) {
		$iYesterdayOfferDisplayCount = $oYesDisplayRow->offerDisplayCount;
	}
			
	if ($iYesterdayOfferDisplayCount) {
		$fYesterdayOfferEcpm = ($fYesterdayRevenue * 1000 ) / $iYesterdayOfferDisplayCount;
	} else {
		$fYesterdayOfferEcpm = "0.0";
		$iYesterdayOfferDisplayCount = "0";
	}

	$fYesterdayOfferEcpm = trim(sprintf("%10.2f",round($fYesterdayOfferEcpm, 2)));
	// end of getting yesterday's offer ecpm
	
	
	
	
	


	// get the Prev month (the month before the last month of selected date range )
	// display details and ecpm for the offer
	// get prev month revenue
	$fPrevMonthOfferRevenue = 0;
	$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
				FROM   offerLeadsCountSum, offers
				WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
				AND    offerLeadsCountSum.offerCode = offers.offerCode									
				AND    date_format(dateAdded, '%Y-%m') = date_format('$sPrevMonth','%Y-%m')
				AND	offerLeadsCountSum.pageId != '238'";
	$rLeadsResult = dbQuery($sLeadsQuery);
	echo dbError();
	while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
		$iPrevMonthOffersTakenCount += $oLeadsRow->offersTaken;
		$fPrevMonthOfferRevenue += $oLeadsRow->revenue;
	}
			
	$fPrevMonthOfferRevenue = sprintf("%10.2f",round($fPrevMonthOfferRevenue, 2));
			
	// get display count
	$iPrevMonthOfferDisplayCount = 0;
	$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
					  FROM   offerStatsSum
					  WHERE  offerCode = '$sOfferCode'
					  AND    date_format(displayDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m') ";			
	$rDisplayResult = dbQuery($sDisplayQuery);
	echo dbError();
	while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
		$iPrevMonthOfferDisplayCount += $oDisplayRow->offerDisplayCount;
	}
			
	if ($iPrevMonthOfferDisplayCount) {
		$fPrevMonthOfferEcpm = ($fPrevMonthOfferRevenue * 1000 )/ $iPrevMonthOfferDisplayCount;
	} else {
		$fPrevMonthOfferEcpm = "0.0";
		$iPrevMonthOfferDisplayCount = "0";
	}
			
	$fPrevMonthOfferEcpm = trim(sprintf("%10.2f",round($fPrevMonthOfferEcpm, 2)));
	$fOfferRevenue = trim(sprintf("%10.2f",round($fOfferRevenue, 2)));
	$aReportArray['offerCode'][$iRow-1] = substr($sOfferCode,0,15);
	$aReportArray['offerRep'][$iRow-1] = $sOfferRep;
	$aReportArray['noOfPages'][$iRow-1] = $iNoOfPagesOfferLiveOn;
	$aReportArray['offersTakenCount'][$iRow-1] = $iOffersTakenCount;
	$aReportArray['offerDisplayCount'][$iRow-1] = $iOfferDisplayCount;
	$aReportArray['offerEcpm'][$iRow-1] = $fOfferEcpm;
	$aReportArray['yesterdayOfferEcpm'][$iRow-1] = $fYesterdayOfferEcpm;
	$aReportArray['prevMonthOfferEcpm'][$iRow-1] = $fPrevMonthOfferEcpm;
	$aReportArray['revPerLead'][$iRow-1] = $fRevPerLead;
	$aReportArray['offerRevenue'][$iRow-1] = $fOfferRevenue;
	$aReportArray['restrictions'][$iRow-1] = $sRestrictions;
	$aReportArray['offerName'][$iRow-1] = $sOfferName;
	$aReportArray['theyHostType'][$iRow-1] = $sTheyHostType;

	if (strtoupper($sMode) == 'A' && $iIsLive && strtoupper($sCreditStatus)=='OK') {
		$aReportArray['isLive'][$iRow-1] = "Y";
	} else {
		$aReportArray['isLive'][$iRow-1] = "N";
	}
} // end of offers while loop
dbFreeResult($rOffersResult);

$sReportContent = "<html><head>
<style =\"text/css\">
TD.small { 
	FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 9px; COLOR: #000000;
}
		
TD.bigHeader {
	FONT-WEIGHT: bold; FONT-SIZE: 12pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
		
TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 8pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
</style>
		</head><body><table cellpadding=0 cellspacing=0 bgcolor=#FFFFFF width=95% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td class=bigHeader align=center><BR>$sPageTitle<BR>
	From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo<BR><BR></td></tr>
	<tr><td class=header>Run Date / Time: $sRunDateAndTime</td></tr>
	</table></td></tr></table></td></tr>
	<tr><td>
		<table width=100% align=center border=1 bgcolor=#FFFFFF cellpaddiing=3 cellspacing=0 bordercolorlight=#000000>
			<tr><td>&nbsp;</td><td class=small>Rep.</td>
			<td class=small>Rate/PL</td><td class=small width=10%>OfferCode</td>
			<td class=small width=10%>Offer<br>Name</td><td class=small align=right>Offer<br>eCPM</td>
			<td class=small align=right>Offer<br>eCPM<br>(Yesterday)</td>
			<td class=small align=right>Offer<br>eCPM<br>($sPrevMonthName)</td>
			<td class=small align=right>No Of <br>Pages<br>Offer On</td>
			<td class=small align=right>Offer<br>Total</td><td class=small align=right>Display<br>Total</td>
			<td class=small align=right>Rate/PL</td><td class=small align=right>Offer<br>Rev</td>
			<td class=small align=right>Live</td><td class=small align=left>Restrictions</td>
			<td class=small align=left>They Host</td></tr>";

// sort array in descending order of offer ecpm only if array is not empty	
if (count($aReportArray['offerCode']) > 0 ) {
	array_multisort($aReportArray['offerEcpm'], SORT_DESC, $aReportArray['prevMonthOfferEcpm'], $aReportArray['offerCode'], $aReportArray['offerName'], $aReportArray['revPerLead'] ,$aReportArray['offerRep'], $aReportArray['noOfPages'], $aReportArray['offersTakenCount'], $aReportArray['offerDisplayCount'], $aReportArray['offerRevenue'], $aReportArray['isLive'], $aReportArray['restrictions'], $aReportArray['theyHostType'], $aReportArray['yesterdayOfferEcpm']);
}
		
for ($i = 0; $i < count($aReportArray['offerCode']); $i++) {
	$iRow = $i+1;
	$sReportContent .="<tr><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iRow</td><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>".$aReportArray['offerRep'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap>\$".$aReportArray['revPerLead'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>".$aReportArray['offerCode'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>".$aReportArray['offerName'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['offerEcpm'][$i]."</td>
			
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['yesterdayOfferEcpm'][$i]."</td>
			
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap align=right>\$".$aReportArray['prevMonthOfferEcpm'][$i]."</td>		
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['noOfPages'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['offersTakenCount'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['offerDisplayCount'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['revPerLead'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['offerRevenue'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>".$aReportArray['isLive'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=left nowrap>".$aReportArray['restrictions'][$i]."</td>
			<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=left nowrap>".$aReportArray['theyHostType'][$i]."</td>
			</tr>";
}



$sNewContent = "<html><head>
		<style =\"text/css\">
		TD.big {
			FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 12px; COLOR: #000000;
		}
		TD.header {
		FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
		}
		TD.small { 
			FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 9px; COLOR: #000000;
		}
		</style>
		</head>
		<body><br><br>
		<table width=400 border=1 bgcolor=#FFFFFF cellpaddiing=3 cellspacing=0 bordercolorlight=#000000>
		<tr><td class=big colspan=3 align=center>Offers With Zero Revenue or Zero Display Count</td></tr>
		<tr><td class=small><b>Offer Code</b></td>
		<td class=small><b>Revenue</b></td>
		<td class=small><b>Display Count</b></td></tr>";
$sGetYesterdayLiveOffers = "SELECT offerCode FROM liveOffers WHERE dateAdded = '$sYesterday'";
$sYesResult = dbQuery($sGetYesterdayLiveOffers);
echo dbError();
while ($oYesterdayRow = dbFetchObject($sYesResult)) {
	$iYesterdayRev = 0;
	$iYesterdayDisplay = 0;
	$sYesterdayRevQuery = "SELECT sum($sLeadsCountCol * offers.revPerLead) AS rev
					FROM   offerLeadsCountSum, offers
					WHERE  offerLeadsCountSum.offerCode = '$oYesterdayRow->offerCode'
					AND    offerLeadsCountSum.offerCode = offers.offerCode									
					AND    dateAdded = '$sYesterday'
					AND	offerLeadsCountSum.pageId != '238'";
	$rYesterdayRevResult = dbQuery($sYesterdayRevQuery);
	echo dbError();
	while ($oYesRevRow = dbFetchObject($rYesterdayRevResult)) {
		$iYesterdayRev = $oYesRevRow->rev;
	}
	dbFreeResult($rYesterdayRevResult);
	
	
	// get the display details and ecpm for the offer
	$sYesDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCt
				  FROM   offerStatsSum
				  WHERE  offerCode = '$oYesterdayRow->offerCode'
				  AND    displayDate = '$sYesterday'";
	$rYesDisplay = dbQuery($sYesDisplayQuery);
	echo dbError();
	while ($oYesDisplay = dbFetchObject($rYesDisplay)) {
		$iYesterdayDisplay = $oYesDisplay->offerDisplayCt;
	}
	dbFreeResult($rYesDisplay);
	
	
	//echo "oc: $oYesterdayRow->offerCode\trev: $iYesterdayRev\tdisplay: $iYesterdayDisplay\n";
	if ($iYesterdayDisplay <= 0 || $iYesterdayRev <= 0) {
		if ($iYesterdayRev == '') { $iYesterdayRev = '&nbsp;';}
		if ($iYesterdayRev == 0.00) { $iYesterdayRev = '&nbsp;';}
		if ($iYesterdayDisplay == '') { $iYesterdayDisplay = '&nbsp;';}
		$sNewContent .= "<tr><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$oYesterdayRow->offerCode</td>
				<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iYesterdayRev</td>
				<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iYesterdayDisplay</td></tr>";
	}
}
$sNewContent .= "</table><br><br></body></html>";







if ($sReportContent != '') {
	$fCurrMonthRevenue = sprintf("%10.2f",round($fCurrMonthRevenue, 2));
	if ($iCurrMonthDisplayCount) {
		$fCurrMonthEcpm = ($fCurrMonthRevenue * 1000) / $iCurrMonthDisplayCount;
		$fCurrMonthEcpm = sprintf("%10.2f",round($fCurrMonthEcpm, 2));
		$fCurrMonthEcpm = "\$".$fCurrMonthEcpm;
	} else {
		$fCurrMonthEcpm = "&nbsp;";
	}
			
			
	$fPrevMonthRevenue = sprintf("%10.2f",round($fPrevMonthRevenue, 2));
			
	if ($iPrevMonthDisplayCount) {
		$fPrevMonthEcpm = ($fPrevMonthRevenue * 1000) / $iPrevMonthDisplayCount;
		$fPrevMonthEcpm = sprintf("%10.2f",round($fPrevMonthEcpm, 2));
		$fPrevMonthEcpm = "\$".$fPrevMonthEcpm;
	} else {
		$fPrevMonthEcpm = "&nbsp;";
	}
			
			
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Total Rev</td><td>&nbsp;</td>
				<td align=right nowrap class=small><b>$fCurrMonthRevenue</b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Total Rev</td><td>&nbsp;</td>
				<td align=right nowrap class=small><b>$fPrevMonthRevenue</b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Total Display</td><td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td><td align=right nowrap class=small><b>$iCurrMonthDisplayCount</b></td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Total Display</td><td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td><td align=right nowrap class=small><b>$iPrevMonthDisplayCount</b></td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>eCPM</td><td>&nbsp;</td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fCurrMonthEcpm</b></td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
	$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>$sPrevMonthName eCPM</td><td>&nbsp;</td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fPrevMonthEcpm</b></td>
				<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
}

$sReportContent .= "</table></td></tr></table>
			$sNewContent
		<table width=80% align=center><tr><Td class=small>Notes:</td></tr>
	<tr><td class=small>Report is accurate as of midnight last night after today's leads are processed.</td></tr>
	<tr><td class=small>Only gross leads report is accurate for the offers which are not processed daily on a basis.</td></tr>
	<tr><td class=small>Report reflects counts for current month upto midnight last night.</td></tr>
	<tr><td class=small>Previous Month counts are of the month prior to the current month.</td></tr>
	<tr><td class=small>API Leads are excluded from this report.</td></tr>
	<tr><td class=small>They Host:  O = Open They Host, C = Close They Host, OC = Open/Close They Host</td></tr>
	<tr><td class=small>Only first 15 chars displayed for Offer Code and Page Name.</td></tr>
		</table></body></html>";

		
$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
$sHeaders .= "cc: ";

$sEmailQuery = "SELECT * FROM emailRecipients WHERE  purpose = 'ecpm summary report'";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
	//$sRecipients = 'spatel@amperemedia.com';
}

if (!($sEmailTo)) {
	$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
}

$sCcTo = substr($sRecipients,strlen($sEmailTo));
$sHeaders .= ", $sCcTo";
$sHeaders .= "\r\n";
$sSubject = "ECPM Summary Report - $sRunDateAndTime";
mail($sEmailTo, $sSubject, $sReportContent, $sHeaders);



?>

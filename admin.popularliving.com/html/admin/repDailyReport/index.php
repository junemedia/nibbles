<?php

/*********
Script to Display 
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

$iScriptStartTime = getMicroTime();

$sPageTitle = "Daily Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	

	
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
				&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iYearTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);

		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
	}


	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iMonthFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iMonthTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sMonthFromOptions .= "<option value='$iValue' $sFromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$iValue' $sToSel>$aGblMonthsArray[$i]";
	}

	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}
		if ($iValue == $iDayFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iDayTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sDayFromOptions .= "<option value='$iValue' $sFromSel>$i";
		$sDayToOptions .= "<option value='$iValue' $sToSel>$i";
	}

	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		if ($i == $iYearFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel ="";
		}
		if ($i == $iYearTo) {
			$sToSel = "selected";
		} else {
			$sToSel ="";
		}
		$sYearFromOptions .= "<option value='$i' $sFromSel>$i";
		$sYearToOptions .= "<option value='$i' $sToSel>$i";
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	$sSqlDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom 00:00:00";
	$sSqlDateTo = "$iYearTo-$iMonthTo-$iDayTo 23:59:59";

if ($sViewReport != "") {
	

	$sClickQuery = "SELECT substring(clickDate,1,10) as date, sum(clicks) AS clicks 
					FROM bdRedirectsTrackingHistorySum
					WHERE clickDate >= '$sSqlDateFrom' AND clickDate <= '$sSqlDateTo' 
					GROUP BY date";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sClickQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		
	
	
	$rClickResult = dbQuery($sClickQuery);
	$iClickCount = 0;
	while ($oClicksRow = dbFetchObject($rClickResult)) {
		$aReportArray['clicks'][$iClickCount] = $oClicksRow->clicks;
		$iClickCount++;
	}
	
	
	
	$sCountUserQuery = "SELECT substring(dateTimeAdded,1,10) as date, count(distinct email) as userCount
					FROM otDataHistory 
					WHERE dateTimeAdded >= '$sSqlDateFrom' AND dateTimeAdded <= '$sSqlDateTo'
					GROUP BY date";
	$rCountUserResult = dbQuery($sCountUserQuery);
	$iUserCount = 0;
	while ($oCountUserRow = dbFetchObject($rCountUserResult)) {
		$aReportArray['userCount'][$iUserCount] = $oCountUserRow->userCount;
		$iUserCount++;
		//echo $oCountUserRow->userCount."<br>";
	}
	
	
	
	
	$sRevenueQuery = "SELECT substring(dateTimeAdded,1,10) as date, sum(revPerLead) as verifiedRevenue
					FROM otDataHistory 
					WHERE dateTimeAdded >= '$sSqlDateFrom' AND dateTimeAdded <= '$sSqlDateTo' 
					AND postalVerified = 'V'
					GROUP BY date";
	$rRevenueResult = dbQuery($sRevenueQuery);
	$iRevCount = 0;
	while ($oRevenueRow = dbFetchObject($rRevenueResult)) {
		$aReportArray['verifiedRevenue'][$iRevCount] = round($oRevenueRow->verifiedRevenue,0);
		$iRevCount++;
	}

	$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as date, count(id) as leads, sum(revPerLead) as revenue
					FROM otDataHistory 
					WHERE dateTimeAdded >= '$sSqlDateFrom' AND dateTimeAdded <= '$sSqlDateTo' GROUP BY date";

	$rReportResult = dbQuery($sReportQuery);
	echo  dbError();
	$i=0;
	$sOneTimeDelivered = 0;
	while ($oReportRow = dbFetchObject($rReportResult)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN_WHITE";
		} else {
			$sBgcolorClass = "ODD";
		}

		$sDate = $oReportRow->date;
		$sLeads = $oReportRow->leads;
		$sRevenue = round($oReportRow->revenue,0);
		$sDay = date("D", mktime(0, 0, 0, substr($sDate,5,2), substr($sDate,8,2), substr($sDate,0,4)));
		//$sPvPercent = number_format(($aReportArray['revenue'][$i] / $sRevenue) * 100,1);
		$Ecpm = round(($sRevenue / $aReportArray['clicks'][$i]) * 1000,0);
		$sAvgRevPerLead = round($sRevenue / $sLeads,2);

			
		if ($sDay == 'Sun' || $sDay == 'Sat') {
			$sOneTimeDelivered = "";
		} elseif ($sDay == 'Mon') {
			$sGetCount = "SELECT count(dateTimeAdded) as mondayLeads
						FROM otDataHistory
						WHERE sendStatus = 'S'
						AND dateTimeAdded BETWEEN date_add('$sDate', INTERVAL -3 DAY) AND date_add('$sDate', INTERVAL -1 SECOND)";
			$rGetCountResult = dbQuery($sGetCount);
			$sMondayLeads = dbFetchObject($rGetCountResult);
			$sOneTimeDelivered = $sMondayLeads->mondayLeads;
		}

		if ($sDay == 'Tue' || $sDay == 'Wed' || $sDay == 'Thu' || $sDay == 'Fri') {
			$sGetCount = "SELECT count(dateTimeAdded) as mondayLeads
						FROM otDataHistory
						WHERE sendStatus = 'S'
						AND dateTimeAdded BETWEEN date_add('$sDate', INTERVAL -1 DAY) AND date_add('$sDate', INTERVAL -1 SECOND)";
			$rGetCountResult = dbQuery($sGetCount);
			$sMondayLeads = dbFetchObject($rGetCountResult);
			$sOneTimeDelivered = $sMondayLeads->mondayLeads;
		}
	
		
		$aReportArray['day'][$i] = $sDay;
		$aReportArray['date'][$i] = $sDate;
		$aReportArray['oneTimeCollected'][$i] = $sLeads;
		$aReportArray['grossRevenue'][$i] = $sRevenue;
		$aReportArray['eCPM'][$i] = $Ecpm;
		$aReportArray['avgPerUser'][$i] = round($sLeads / $aReportArray['userCount'][$i],2);
		$aReportArray['avgPerLead'][$i] = $sAvgRevPerLead;
		$aReportArray['oneTimeSent'][$i] = $sOneTimeDelivered;
		$i++;
	}


		$iNumRecords = count($aReportArray['date']);
		$iTotalPages = ceil(($iNumRecords)/$iRecPerPage);
		
		// If current page no. is greater than total pages move to the last available page no.
		if ($iPage > $iTotalPages) {
			$iPage = $iTotalPages;
		}
		
		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage -1;

		if ($iNumRecords > 0) {
			$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		}
		
		if ($iTotalPages > $iPage ) {
			$iNextPage = $iPage+1;
			$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
			$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
		}

		if ($iPage != 1) {
			$iPrevPage = $iPage-1;
			$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
			$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
		}
		
		$iCount = 0;
		$sPageLoop = 0;	
		for( $iLoop=0; $iLoop<$iNumRecords; $iLoop++ ) {
			$sPageLoop++;
			if (($sPageLoop > $iStartRec) && ($sPageLoop <= ($iStartRec + $iRecPerPage))) {
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sPvPercent = number_format(($aReportArray['verifiedRevenue'][$iLoop] / $aReportArray['grossRevenue'][$iLoop]) * 100,1);

				$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['day'][$iLoop]."</td>
							<td>".$aReportArray['date'][$iLoop]."</td>
							<td>".$aReportArray['oneTimeCollected'][$iLoop]."</td>
							<td>".$aReportArray['grossRevenue'][$iLoop]."</td>
							<td>".round($aReportArray['verifiedRevenue'][$iLoop],0)."</td>
							<td>$sPvPercent</td>
							<td>".$aReportArray['clicks'][$iLoop]."</td>
							<td>".$aReportArray['eCPM'][$iLoop]."</td>
							<td>".$aReportArray['avgPerUser'][$iLoop]."</td>
							<td>".$aReportArray['avgPerLead'][$iLoop]."</td>
							<td>".$aReportArray['oneTimeSent'][$iLoop]."</td>
							</tr>";
				$sOneTimeCollectedTotal += $aReportArray['oneTimeCollected'][$iLoop];
				$sGrossRevenueTotal += $aReportArray['grossRevenue'][$iLoop];
				$sValidatedRevenueTotal += $aReportArray['verifiedRevenue'][$iLoop];
				$sPvPercentTotal += $aReportArray['pvPercent'][$iLoop];
				$sClicksTotal += $aReportArray['clicks'][$iLoop];
				$sEcpmTotal += $aReportArray['eCPM'][$iLoop];
				$sAvgPerUserTotal += $aReportArray['avgPerUser'][$iLoop];
				$sAvgPerLeadTotal += $aReportArray['avgPerLead'][$iLoop];
				$sOneTimeSentTotal += $aReportArray['oneTimeSent'][$iLoop];
				$sTotalUsers += $aReportArray['userCount'][$iLoop];
				$iCount++;
			}
		}
		$sAvgPerLeadTotal = round($sGrossRevenueTotal / $sOneTimeCollectedTotal,2);
		$sAvgPerUserTotal = round($sOneTimeCollectedTotal / $sTotalUsers,2);
		
		$sReportContent .= "<tr><td colspan=11><hr color=#000000></td></tr>
					<tr><td><b>Total: </b></td>
					<td><b></b></td>
					<td><b>$sOneTimeCollectedTotal</b></td>
					<td><b>$sGrossRevenueTotal</b></td>
					<td><b>$sValidatedRevenueTotal</b></td>
					<td><b></b></td>
					<td><b>$sClicksTotal</b></td>
					<td><b>$sEcpmTotal</b></td>
					<td><b>$sAvgPerUserTotal</b></td>
					<td><b>$sAvgPerLeadTotal</b></td>
					<td><b>$sOneTimeSentTotal</b></td>
					</tr></tr>";
		//echo $iCount;
		// display averages...
		$sReportContent .= "<tr><td><b>Averages: </b></td>
					<td><b></b></td>
					<td><b>".round($sOneTimeCollectedTotal / $iCount,0)."</b></td>
					<td><b>".round($sGrossRevenueTotal / $iCount,2)."</b></td>
					<td><b>".round($sValidatedRevenueTotal / $iCount,2)."</b></td>
					<td><b></b></td>
					<td><b>".round($sClicksTotal / $iCount,2)."</b></td>
					<td><b>".round($sEcpmTotal / $iCount,2)."</b></td>
					<td><b></b></td>
					<td><b></b></td>
					<td><b>".round($sOneTimeSentTotal / $iCount,0)."</b></td>
					</tr></tr>";
}
	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	include("../../includes/adminHeader.php");	

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport value='ViewReport'>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>
	
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">	
	&nbsp; &nbsp;</td>
</tr>

<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> 
&nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> 
&nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; 
<?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>


</table>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=11 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=11 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
	<td valign="top" class=header>Day</a></td>
	<td valign="top" class=header>Date</a></td>
	<td valign="top" class=header>One Time Collected</a></td>
	<td valign="top" class=header>Gross Revenue</a></td>
	<td valign="top" class=header>Validated Revenue</a></td>
	<td valign="top" class=header>% PV</a></td>
	<td valign="top" class=header>BusDev Clicks</a></td>
	<td valign="top" class=header>Lead Gen eCPM</a></td>
	<td valign="top" class=header>Average Offers/User</a></td>
	<td valign="top" class=header>Avg Rev/Lead</a></td>
	<td valign="top" class=header>One Time Delivered</a></td>
	</tr>
	
		<?php echo $sReportContent;?>

	<tr><td colspan=11 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=11 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=11><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)<br>
	Today's data is not included on this report.<br>
	Total: This is the total for current page only, not for the entire report.<br>
	One Time Collected:  Number of gross leads collected for that date.<br>
	Gross Revenue:  This is sum of revenue for all leads.<br>
	Validated Revenue:  This is sum of revenue for leads that are postal verified.<br>
	PV %: The ratio of Validated Revenue versus Gross Revenue. This is the result of Validated Revenue divide by Gross Revenue. 
	BusDev Clicks: This is number of redirect clicks.<br>
	Lead Gen eCPM:  This is result of 'Gross Revenue' devide by 'BusDev Clicks' multiply by 1000.<br>
	Average Offers/User:  This is an average offers taken per user.<br>
	Avg Rev/Lead:  This is an average revenue per leads.<br>
	One Time Delivered:  This is number of leads delivered on this day.  Sat and Sun will be blank.  Monday will include Fri-Sun.
	
	</td></tr>
	
		</td></tr></table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>

</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Onetime Quick Count Report";

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
	
	if (!($sViewReport)) {
		$iYearFrom = $iCurrYear;
		$iMonthFrom = $iCurrMonth;
		$iDayFrom = $iCurrDay;
		
		$iYearTo = $iYearFrom;
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
	} else if ($sHistoryReport) {
		
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
	
	
	if ($sExcludeNonRevenue) {
		$sExcludeNonRevenueFilter = " AND offers.isNonRevenue != '1' ";	
	}
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		
		$sDeleteMemoryTableQuery = "DELETE FROM tempOtQuickCount";
		$rDeleteMemoryTableResult = dbQuery($sDeleteMemoryTableQuery);
		
		echo dbError();
		$sHistoryDataQuery = "INSERT INTO tempOtQuickCount(offerCode, name, totalLeads, totalRevenue)
						 SELECT offers.offerCode, offers.name, count(otDataHistory.email) totalLeads,
	          					count(otDataHistory.email) * offers.revPerLead AS totalRevenue						 
      					 FROM   otDataHistory, userDataHistory, offers
      					 WHERE  otDataHistory.email = userDataHistory.email
						 AND	otDataHistory.offerCode = offers.offerCode 
						 $sExcludeNonRevenueFilter           					 
      					 AND    date_format(otDataHistory.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sDateFrom'
      					 AND '$sDateTo'
						 GROUP BY otDataHistory.offerCode";
		$rHistoryDataResult = dbQuery($sHistoryDataQuery);
		echo  dbError();
		$sTodaysDataQuery = "INSERT INTO tempOtQuickCount(offerCode, name, totalLeads, totalRevenue)
						 SELECT offers.offerCode, offers.name, count(otData.email) totalLeads,
	          					count(otData.email) * offers.revPerLead AS totalRevenue
      					 FROM   otData, userData, offers
      					 WHERE  otData.email = userData.email
						 AND	otData.offerCode = offers.offerCode  
						 $sExcludeNonRevenueFilter
      					 AND    date_format(otData.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sDateFrom'
      					 AND '$sDateTo'
						 GROUP BY otData.offerCode";
		$rTodaysDataResult = dbQuery($sTodaysDataQuery);
		echo  dbError();
		// get rows from temp table
		$sMemoryTableSelectQuery = "SELECT offerCode, name, sum(totalLeads) AS totalLeads, sum(totalRevenue) AS totalRevenue
								FROM   tempOtQuickCount
								GROUP BY offerCode";
		$rMemoryTableSelectResult = dbQuery($sMemoryTableSelectQuery);
		
		$iTotalLeads = 0;
		$fTotalRevenue = 0;
		echo dbError();
		
		$count = 0;
		
		while ($oMemoryTableSelectRow = dbFetchObject($rMemoryTableSelectResult)) {
			
			
			$count++ ;
			
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN_WHITE";
			} else {
				$bgcolorClass = "ODD";
			}
			
			$sReportContent .= "<tr class=$bgcolorClass><td>$count $oMemoryTableSelectRow->offerCode</td><td>$oMemoryTableSelectRow->name</td><td align=right>$oMemoryTableSelectRow->totalLeads</td><td align=right>$oMemoryTableSelectRow->totalRevenue</td></tr>";
			$iTotalLeads += $oMemoryTableSelectRow->totalLeads;
			$fTotalRevenue += $oMemoryTableSelectRow->totalRevenue;
		}
		
		$rDeleteMemoryTableResult = dbQuery($sDeleteMemoryTableQuery);
		/*
		
		$rReportResult = mysql_query($sReportQuery);
		echo mysql_error();
		//echo mysql_num_rows($rReportResult);
		
		while ($oReportRow = mysql_fetch_object($rReportResult)) {
		
		
		$iTempTotalLeads = $oReportRow->totalHistoryLeads + $oReportRow->totalTodaysLeads;
		$sReportContent .= "<tr class=$bgcolorClass><td>$oReportRow->name</td><td align=right>$iTempTotalLeads</td><td align=right>$oReportRow->totalRevenue</td></tr>";
		$iTotalLeads += $oReportRow->totalLeads;
		$fTotalRevenue += $oReportRow->totalRevenue;
		
		}*/
		
		
		$fTotalRevenue = sprintf("%10.2f", round($fTotalRevenue,2));
		
		//$sReportContent = "
		
		//$sReportContent
		/*<tr><td colspan=4 align=left><hr color=#000000></td></tr>
		<tr><td class=header colspan=2>Total</td><td class=header align=right>$iTotalLeads</td><td class=header align=right>$fTotalRevenue</td></tr>
		<tr><td colspan=4 class=header><BR>Notes -</td></tr>
		<tr><td colspan=4>* Updated in real time.<BR>* Gross leads without regard to any type of verification.</td></tr>
		<tr><td colspan=4><BR><BR></td></tr>
		</td></tr></table></td></tr></table></td></tr>
		</table>";
		*/
	}
	
	$iHoursToday = date('H') ;
	if ($iHoursToday != 0 && $iHoursToday != '') {
		$iLeadsPerHour = $iTotalLeads / $iHoursToday ;
		
		$fRevenuePerHour = $fTotalRevenue / $iHoursToday ;
	} else {
		$iLeadsPerHour = $iTotalLeads;
		$fRevenuePerHour = $fTotalRevenue;
	}
	
	if ($sExcludeNonRevenue == 'Y') {
		$sExcludeNonRevenueChecked = "checked";
	}
	
	include("../../includes/adminHeader.php");
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
	
// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

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
	<tr><td></td><td><input type=checkbox name=sExcludeNonRevenue value='Y' <?php echo $sExcludeNonRevenueChecked;?>> Exclude Non-Revenue Offers</td></tr>
	<tr><td colspan=2><input type=button name=sSubmit value='View Report'  onClick="funcReportClicked('report');">	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
		<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
			<tr><td>
			<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
			<tr><td>
				<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime; ?></td></tr>
	<tr><td width=120 class=header>Offer Code</td><td width=250 class=header>Offer Name</td>
		<td class=header align=right width=100>Number Of Leads</td><td class=header align=right>Revenue</td>
		
	</tr>
	
		<?php echo $sReportContent;?>
			<tr><td colspan=4 align=left><hr color=#000000></td></tr>
	<tr><td class=header colspan=2>Total:</td><td class=header align=right><?php echo $iTotalLeads; ?></td>
			<td class=header align=right><?php echo $fTotalRevenue;?></td></tr>
			
			<tr><td colspan="4">&nbsp;</td></tr>
			
			<tr>
			<td class=header colspan=2>Leads & Revenue Per Hour:</td>
			<td class=header align=right><?php echo number_format($iLeadsPerHour,2); ?></td>
			<td class=header align=right><?php echo number_format($fRevenuePerHour,2);?></td>
			</tr>
			
	<tr><td colspan=4 class=header><BR>Notes -</td></tr>
	<tr><td colspan=4>- Updated in real time.</td></tr>
	<tr><td colspan=4>- Report omits any leads having address starting with '3401 Dundee' considering those as test leads.</td></tr>
	<tr><Td colspan=4>- Gross leads without regard to any type of verification.</td></tr>
	<tr><Td colspan=4>- Per hour figures only valid for current day.</td></tr>
	<tr><Td colspan=4>- Per hour figures based on number of hours since 00:00 rounded down.</td></tr>
	<tr><Td colspan=4>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
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



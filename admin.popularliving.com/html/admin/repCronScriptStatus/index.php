<?php



include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Cron Script Status Report";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = date('m')."-".date('d')."-".date('Y');

	$sViewReport = stripslashes($sViewReport);

	if (!( $sViewReport )) {
		$iYearFrom = date('Y');
		$iMonthFrom = date('m');
		$iDayFrom = date('d');

		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;

		$sShowQueries = "Y";
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
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {

			if (!($sOrderColumn)) {
			$sOrderColumn = "startDateTime";
			$sStartDateTimeOrder = "ASC";
		}
		
		switch ($sOrderColumn) {
			
			case "scriptName" :
			$sCurrOrder = $sScriptNameOrder;
			$sScriptNameOrder = ($sScriptNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "endDateTime" :
			$sCurrOrder = $sEndDateTimeOrder;
			$sEndDateTimeOrder = ($sEndDateTimeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "execTime" :
			$sCurrOrder = $sExecTimeOrder;
			$sExecTimeOrder = ($sExecTimeOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sStartDateTimeOrder;
			$sStartDateTimeOrder = ($sStartDateTimeOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&sViewReport=$sViewReport";
		
		$sReportQuery = "SELECT scriptName, startDateTime, endDateTime, unix_timestamp(endDateTime) - unix_timestamp(startDateTime) as execTime
						 FROM   cronScriptStatus
						 WHERE  startDateTime BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
						 ORDER BY ".$sOrderColumn." $sCurrOrder";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase); 

		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		


		
		
		$rReportResult = dbQuery($sReportQuery);
			echo dbError();
			while ($oReportRow = dbFetchObject($rReportResult)) {
				
				$sExecTime = $oReportRow->execTime;
				if ($sExecTime < 0 ) {
					$sExecTime = "N/A";
				}
				$sReportContent .=  "<tr><td>$oReportRow->scriptName</td>
										<td nowrap>$oReportRow->startDateTime</td>
										<td nowrap>$oReportRow->endDateTime</td>
										<td align=right>$sExecTime</td>
									</tr>";
			}

		}
	}

	
	
		

	if ($sShowQueries == "Y") {
		$sShowQueriesChecked = "checked";
	}

	if ($sShowQueries == 'Y') {
		$sQueries = "<b>Reportb Query:</b><BR>".$sReportQuery;		
	}


	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

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
		
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');"></td>
		<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR>
			<BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=scriptName&sScriptNameOrder=<?php echo $sScriptNameOrder;?>'>Script Name</a><BR>&nbsp;</td>		
		<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=startDateTime&sStartDateTimeOrder=<?php echo $sStartDateTimeOrder;?>'>Start Date Time</a><BR>&nbsp;</td>
		<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=endDateTime&sEndDateTimeOrder=<?php echo $sEndDateTimeOrder;?>'>End Date Time</a><BR>&nbsp;</td>
		<td class=header align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=execTime&sExecTimeOrder=<?php echo $sExecTimeOrder;?>'>Exec. Time</a> (Seconds)</td>
	</tr>
	<?php echo $sReportContent;?>
	<Tr><td colspan=4><hr color=#000000></td></tR>
	<tr><td colspan=4><BR><b>Notes -</b><BR>	
		<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
	<tr><td colspan=4><?php echo $sQueries;?></td></tr>
	
	</table></td></tr></table></td></tr>
	</table>
	
</form>

<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php
session_start();
include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$sPageTitle = "Nibbles II Clicks Report";
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

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;
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

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sSourceCode=$sSourceCode";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
				// start of track users' activity in nibbles
				mysql_connect ($host, $user, $pass);
				mysql_select_db ($dbase);
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
							  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Tried to generate $sPageTitle, but the server load was high.')";
				$rResult = dbQuery($sAddQuery);
				echo  dbError();
				mysql_connect ($reportingHost, $reportingUser, $reportingPass);
				mysql_select_db ($reportingDbase);
				// end of track users' activity in nibbles
			} else {
				mysql_select_db("nibbles_reporting");
				$sReportQuery = "SELECT * FROM nibbles2ClicksHistory
   					 	WHERE clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";

				if ($sSourceCode != '') {
					$sReportQuery .= " AND sourceCode LIKE '%$sSourceCode%' ";
				}
				$sReportQuery .= " ORDER BY clickDate ASC ";

				$rReportResult = dbQuery($sReportQuery);
				echo dbError();
				$iTotalCount = 0;
				while ($oReportRow = dbFetchObject($rReportResult)) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}

					$sReportContent .= "<tr class=$sBgcolorClass>
							<td>$oReportRow->clickDate</td>
							<td>$oReportRow->sourceCode</td>
							<td>$oReportRow->count</td>
							<td>$oReportRow->remoteIp</td>
							<td>$oReportRow->sessionId</td>
							</tr>";
					$iTotalCount += $oReportRow->count;
				}
					$sReportContent .= "<tr><td colspan=5><hr color=#000000></td></tr>
					<tr><td><b>Total: </b></td>
					<td><b></b></td>
					<td><b>$iTotalCount</b></td>
					<td><b></b></td>
					<td><b></b></td>
					</tr></tr>";
					$sExpReportContent .= "\t\t$iTotalCount\n";
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
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
	
	
<tr><td>Source Code</td>
<td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
</td></tr>


<td colspan=2></td>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
<td colspan=2></td>
</tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header>Date</td>
		<td class=header>Source Code</td>
		<td class=header>Gross Count</td>
		<td class=header>IP Address</td>	
		<td class=header>Session Id</td>	
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=5 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=5>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Today's data is not available.<br><br>
		<b>Query:</b><br>
		<?php echo $sReportQuery; ?>
		</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
		<?php echo $sQueries;?>
		</table></td></tr></table></td></tr>
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

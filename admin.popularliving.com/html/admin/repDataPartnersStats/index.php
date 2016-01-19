<?php
/*********
Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.
**********/
session_start();

include("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();



$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$sPageTitle = "Data Partners Stats Report";

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

		$iMonthTo = date('m');
		$iDayTo = date('d');
		$iYearTo = date('Y');

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
							&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sUserName=$sUserName&sPageName=$sPageName";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
					// start of track users' activity in nibbles
					mysql_connect ($host, $user, $pass);
					mysql_select_db ($dbase);
					$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
								  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Tried to generate Data Partners Stats report, but the server load was high.')";
					$rResult = dbQuery($sAddQuery);
					echo  dbError();
					mysql_connect ($reportingHost, $reportingUser, $reportingPass);
					mysql_select_db ($reportingDbase);
					// end of track users' activity in nibbles
			} else {
				$sFirstDate = $iYearFrom."-".$iMonthFrom."-01";
			
				$sReportQuery = "SELECT date, script, count
						FROM nibbles_datafeed.dataSentStats
   					 	WHERE date BETWEEN '$sDateFrom' AND '$sDateTo'";
					
				if ($sScript != "") {
					$sReportQuery .= " and script = '$sScript' ";
				}
				
				mysql_connect ($host, $user, $pass);
				mysql_select_db ($dbase);
				// start of track users' activity in nibbles
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
							VALUES(\"$sTrackingUser\", \"$PHP_SELF\", now(), \"Generated Data Partners Stats Report with this query:  $sReportQuery.\")";
				$rResult = mysql_query($sAddQuery);
				echo  dbError();
				// end of track users' activity in nibbles
				mysql_connect ($reportingHost, $reportingUser, $reportingPass);
				mysql_select_db ($reportingDbase);
				
				$rReportResult = dbQuery($sReportQuery);
				echo dbError();
				while ($oReportRow = dbFetchObject($rReportResult)) {
					$sEndDate = $oReportRow->date;
					$sMonthlyQuery = "SELECT sum(count) as mcount
							FROM nibbles_datafeed.dataSentStats
							WHERE date BETWEEN '$sFirstDate' AND '$sEndDate'
							AND script = '$oReportRow->script'";
					$rMonthlyResult = mysql_query($sMonthlyQuery);
					while ($oDataRow = dbFetchObject($rMonthlyResult)) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}

						$sReportContent .= "<tr class=$sBgcolorClass>
								<td>$oReportRow->date</td>
								<td>$oReportRow->script</td>
								<td>$oReportRow->count</td>
								<td>$oDataRow->mcount</td></tr>";
						$iTotalCount += $oReportRow->count;
						$sExpReportContent .= "$oReportRow->date\t$oReportRow->script\t$oReportRow->count\t$oDataRow->mcount\n";
					}
				}
					$sReportContent .= "<tr><td colspan=4><hr color=#000000></td></tr>
					<tr><td><b>Total: </b></td>
					<td><b></b></td>
					<td><b>$iTotalCount</b></td>
					<td><b></b></td>
					</tr></tr>";
					$sExpReportContent .= "\t\t$iTotalCount\n";
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}


	if ($sExportExcel && !$bDateRangeNotOk) {

		// start of track users' activity in nibbles
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Exported Data Partners Stats Report')";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		// end of track users' activity in nibbles
		
		$sExpReportContent = "Date\tVendor\tDaily Count\tMonthly Count\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";

		$sFileName = "DataSent_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExpReportContent, strlen($sExpReportContent));
			fclose($rFpFile);

			echo "<script language=JavaScript>
			void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
		  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	}

	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}

	
	
	// Get all user names
	$sScriptQuery = "SELECT distinct script
				FROM nibbles_datafeed.dataSentStats
      			ORDER BY script ASC";

	$rScriptResult = dbQuery($sScriptQuery);
	echo dbError();

	while ($oScriptRow = dbFetchObject($rScriptResult)) {
		$sTempScript = $oScriptRow->script;
		if ($sScript) {
			if ($sTempScript == $sScript) {
				$sScriptSelected = "selected";
			} else {
				$sScriptSelected = "";
			}
		} else {
			if ($sTempScript == $sScript && isset($sScript)) {
				$sScriptSelected = "selected";
			} else {
				$sScriptSelected = "";
			}
		}
		$sScriptOption .= "<option value='".$oScriptRow->script."' $sScriptSelected>$oScriptRow->script";
	}
	

	
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

	
	/*
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=date&sDateOrder=<?php echo $sDateOrder;?>" class=header>Date</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=script&sScriptOrder=<?php echo $sScriptOrder;?>" class=header>Vendor</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=count&sCountOrder=<?php echo $sCountOrder;?>" class=header>Daily Count</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=Mcount&sMCountOrder=<?php echo $sMCountOrder;?>" class=header>Monthly Count</a></td>
		*/
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
	
	
<tr><td>User Name:</td><td><select name=sScript>
<option value=''>All</option>
<?php echo $sScriptOption;?>
	</select></td></tr>


	<td colspan=2></td>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 &nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td>
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
		<td class=header>Vendor</td>
		<td class=header>Daily Count</td>
		<td class=header>Monthly Count (MTD)</td>	
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=4 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=4>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Daily Count:  Number of records sent for this date range and script.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		Monthly Count:  Monthly Count includes from <?php echo $sFirstDate ?> to <?php echo $sEndDate ?><br>
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

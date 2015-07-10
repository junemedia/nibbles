<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "R4L Log Report";

session_start();

$sSourceCodeOptions .= "<option value=''>All";
mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


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
	$sToday = date('m')."-".date('d')."-".date('Y');

	$sViewReport = stripslashes($sViewReport);


	if (!($sDateFrom || $sDateTo)) {
		$sDateFrom = $sToday;
		$sDateTo = $sToday;
		if (!($sViewReport)) {
			$sViewReport = "Get Report";
		}
	}


	if ($sViewReport) {

		$iMonthFrom = substr($sDateFrom,0,2);
		$iDayFrom = substr($sDateFrom,3,2);
		$iYearFrom = substr($sDateFrom, 6,4);

		$iMonthTo = substr($sDateTo,0,2);
		$iDayTo = substr($sDateTo,3,2);
		$iYearTo = substr($sDateTo, 6,4);

		$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
		$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";

	}


	if ($sViewReport) {

		$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo";

		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {
			$iGrandTotalUniqueUsers = 0;

			if ($sReportBy == 'sourceCode') {
				if ($sAllSourceCodes != '') {
                                        $sSourceCodeFilter = " AND sourceCode = '$sAllSourceCodes' ";
                                } else {
					$sSourceCodeFilter = '';
				}


				$sReportQuery = "SELECT date, sourceCode, grossPageViews, uniqueVisits, grossVisits
						 FROM  r4l.logging
						 WHERE  date BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
						$sSourceCodeFilter
						 ORDER BY sourceCode, date";
				$sColumnHeader = "Source Code";

			} else {

                                $sReportQuery = "SELECT date, sourceCode, grossPageViews, uniqueVisits, grossVisits
                                                 FROM  r4l.logging
                                                 WHERE  date BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
                                                 ORDER BY date, sourceCode";

				$sColumnHeader = "Date";
			}

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

			$iGrandTotalUniqueUsers = 0;
			$iGrandTotalGrossUsers = 0;
			$iGrandTotalGrossPageViews = 0;

			
			$rReportResult = dbQuery($sReportQuery);
			echo dbError();
			while ($oReportRow = dbFetchObject($rReportResult)) {

				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}

				$iUniqueUsers = $oReportRow->uniqueVisits;
				$iGrossUsers = $oReportRow->grossVisits;
				$sSourceCode = $oReportRow->sourceCode;
				$iGrossPageViews = $oReportRow->grossPageViews;
				$sDate = $oReportRow->date;

				$iGrandTotalUniqueUsers += $iUniqueUsers;
				$iGrandTotalGrossUsers += $iGrossUsers;
				$iGrandTotalGrossPageViews += $iGrossPageViews;
				if($sReportBy == 'sourceCode'){
					$sReportContent .= "<tr><td align=left>$sSourceCode</td><td align=right>$sDate</td><td align=right>$iGrossPageViews</td><td align=right>$iGrossUsers</td><td align=right>$iUniqueUsers</td></tr>";
				} else {
					$sReportContent .= "<tr><td align=left>$sDate</td><td align=right>$sSourceCode</td><td align=right>$iGrossPageViews</td><td align=right>$iGrossUsers</td><td align=right>$iUniqueUsers</td></tr>";
				}
			}



			$sReportContent .= "<tr><td colspan=5 align=left><hr color=#000000></td></tr>
								<tr><td><b>Summary</b></td>
								<td align=right></td>
								<td align=right><b>$iGrandTotalGrossPageViews</b></td>
								<td align=right><b>$iGrandTotalGrossUsers</b></td>
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>								
								
						</tr>";			
		}
	}


	if ($sShowQueries == 'Y') {
		$sShowQueriesChecked = "checked";
	}





	if ($sShowQueries == 'Y') {

		$sQueries .= "<b>Main Query To Get Offers/Pages/SourceCodes:</b><BR><BR> $sReportQuery
					  <BR><BR><b>Query To Get UniqueUsers Count For An Offer/Page/SourceCode</b>
					  <br><br>$sReportQuery	
						";
	}

	$sDateChecked = '';
	$sSourceCodeChecked = '';

	switch($sReportBy) {
		case 'date':
		default:
		$sDateChecked = "checked";
		break;
		case 'sourceCode':
		$sSourceCodeChecked = "checked";
		break;
	}

	/*
	if ($sExportExcel) {

		$sExpReportContent = "Offer Code\tGross Unique Users\tUnique Users PV\t% PV"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nReport includes data only where PV is attempted.";
		$sExpReportContent .= "\nCounts will change as postal verification status changes.";
		$sExpReportContent .= "\nReport omits any leads having address starting with '3401 Dundee' considering those as test leads.";
		$sExpReportContent .= "\nSummary will always different for Offery wise, Page wise and SourceCode wise Report because unique users are counted within different type of groups (group of offer, page or sourceCode)\n";

		$sFileName = "postalVerification_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
	*/



	$sSourceCodeQuery = "SELECT distinct sourceCode
							 FROM   otDataHistory
							 WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							 AND sourceCode != ''
							 ORDER BY sourceCode"; 
	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();
	while ($oReportRow = dbFetchObject($rSourceCodeResult)) {
		if( $sAllSourceCodes == $oReportRow->sourceCode ) {
			$sSelected = " selected ";
		} else {
			$sSelected = "";
		}
		$sSourceCodeOptions .= "<option value='$oReportRow->sourceCode' $sSelected>$oReportRow->sourceCode";
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

	<tr><td>Date From</td><td><input type=textbox name=sDateFrom Value='<?php echo $sDateFrom;?>'></td></tr>
	<tr><td>Date To</td><td><input type=textbox name=sDateTo Value='<?php echo $sDateTo;?>'></td></tr>		
	<tr><td>Report By</td><td>
			<input type=radio name=sReportBy value='date' <?php echo $sDateChecked;?>> Date &nbsp; &nbsp; 
			<input type=radio name=sReportBy value='sourceCode' <?php echo $sSourceCodeChecked;?>> SourceCode &nbsp; &nbsp; </td></tr>
			
			<tr><td>Source Code</td><td><select name=sAllSourceCodes>
			<?php echo $sSourceCodeOptions;?>
			</select></td></tr>
	<!--<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <php echo $sPvOnlyChecked;?>> PostalVerified
	</td></tr>-->	
	<tr><td colspan=2><input type=button name=sSubmit value='Get Report' onClick="funcReportClicked('report');">	
	  &nbsp; &nbsp; &nbsp; &nbsp; <input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	<!--<input type=submit name=sPrintReport value='Print This Report'></td></tr>-->
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header><?php echo $sColumnHeader;?></td>
		<td align=right class=header><?php echo ($sColumnHeader == 'Date' ? 'Source Code' : 'Date'); ?></td>				
		<td align=right class=header>Gross Page Views</td>
		<td align=right class=header>Gross Visits</td>
		<td align=right class=header>Unique Visits</td>		
	</tr>
	
<?php echo $sReportContent;?>

	<tr><td colspan=5 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	<tr><td colspan=5><b>Queries Used To Prepare This Report:</b><BR><BR><?php echo $sQueries; ?></td></tr>
						<tr><td colspan=2><BR><BR></td></tr>
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

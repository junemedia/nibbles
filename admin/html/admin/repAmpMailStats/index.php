<?php

/*********

Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.

**********/

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");


mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


$iScriptStartTime = getMicroTime();

$sPageTitle = "Ampere Mailing Statistics";

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

	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "dateSent";
		$sDateSentOrder = "DESC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "dateSent" :
			$sCurrOrder = $sDateSentOrder;
			$sDateSentOrder = ($sDateSentOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "messageId" :
			$sCurrOrder = $sMessageIdOrder;
			$sMessageIdOrder = ($sMessageIdOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "listName" :
			$sCurrOrder = $sListNameOrder;
			$sListNameOrder = ($sListNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "successes" :
			$sCurrOrder = $sSuccessOrder;
			$sSuccessOrder = ($sSuccessOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errors" :
			$sCurrOrder = $sErrorOrder;
			$sErrorOrder = ($sErrorOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "total" :
			$sCurrOrder = $sTotalOrder;
			$sTotalOrder = ($sTotalOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "countSent" :
			$sCurrOrder = $sCountSentOrder;
			$sCountSentOrder = ($sCountSentOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDbMailIdOrder;
			$sDateSentOrder = ($sDateSentOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}


	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sListName=$sListName&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";

	dbSelect( "ezmlm" );
	
	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				if ($iDisplayDateWise) {
					/*			$sReportQuery = "SELECT  dbMailsSentCount.*, dbMails.emailSub
					FROM   dbMailsSentCount, dbMails
					WHERE  dbMailsSentCount.dbMailId = dbMails.id
					AND	dateSent BETWEEN '$sDateFrom' AND '$sDateTo'";

					if ($iDbMailId) {
					$sReportQuery .= " AND dbMails.id = '$iDbMailId' ";
					}

					$sDateSentPlaceHolder = "<td></td>";
					$sExpDateSentPlaceHolder = "\t";
					$sDateSentHeader= "<td class=header><a href=\"$sSortLink&sOrderColumn=dateSent&sDateSentOrder=$sDateSentOrder\" class=header>DateSent</a></td>";
					*/
				} else {

					$sReportQuery = "SELECT MIN(dateTimeStarted) AS dateSent, mailLogSendStats.messageId, mailLogListNames.listName, successes, errors, (successes+errors) as total
					FROM mailLogSendStats LEFT JOIN mailLogListNames ON (mailLogSendStats.messageId = mailLogListNames.messageId)
					WHERE dateTimeStarted BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
					AND successes > 1";

					if ($sListName) {
						$sReportQuery .= " AND mailLogListNames.listName = '$sListName' ";
					}
				}

				$sReportQuery .= " GROUP BY mailLogSendStats.messageId, mailLogListNames.listName";
				$sReportQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

			//	echo $sReportQuery;

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				mysql_connect ($host, $user, $pass); 
				dbSelect( "nibbles" );
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
				$rResult = dbQuery($sAddQuery); 
				echo  dbError(); 
				mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
				dbSelect( "ezmlm" );

				// end of track users' activity in nibbles		
			
				$rReportResult = dbQuery($sReportQuery);
				echo dbError();
				$iNumRecords = dbNumRows($rReportResult);

				$iTotalPages = ceil($iNumRecords/$iRecPerPage);

				// If current page no. is greater than total pages move to the last available page no.
				if ($iPage > $iTotalPages) {
					$iPage = $iTotalPages;
				}

				$iStartRec = ($iPage-1) * $iRecPerPage;
				$iEndRec = $iStartRec + $iRecPerPage -1;

				if ($iNumRecords > 0) {
					$sCurrentPage = " Page $iPage "."/ $iTotalPages";
				}

				// use query to fetch only the rows of the page to be displayed
				$sReportQuery .= " LIMIT $iStartRec, $iRecPerPage";
				$rReportResult = dbQuery($sReportQuery);

				if ( dbNumRows($rReportResult) >0) {
					if ($iTotalPages > $iPage ) {
						$iNextPage = $iPage+1;
						$sNextPageLink = "<a href='".$sSortLink."&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
					}
					if ($iPage != 1) {
						$iPrevPage = $iPage-1;
						$sPrevPageLink = "<a href='".$sSortLink."&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
					}
					while ($oReportRow = dbFetchObject($rReportResult)) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}

						//				if ($iDisplayDateWise) {
						$sDateColumn = "<td>$oReportRow->dateSent</td>";
						$sExpDateColumn = "$oReportRow->dateSent\t";

						//				}
						$eTotal = $oReportRow->successes + $oReportRow->errors;
						$sReportContent .= "<tr class=$sBgcolorClass>$sDateColumn
									<td>$oReportRow->messageId</td>
									<td>$oReportRow->listName</td>
									<td>$oReportRow->successes</td>
									<td>$oReportRow->errors</td>
									<td align=right>$eTotal</td>
								</tr>";

						$sExpReportContent .= $sExpDateColumn . "$oReportRow->messageId\t$oReportRow->listName\t" .
						"$oReportRow->successes\t$oReportRow->errors\t$eTotal\n";

						$iPageTotalSuccessesCount += $oReportRow->successes;
						$iPageTotalErrorsCount += $oReportRow->errors;
						$iPageTotalTotalCount += $eTotal;

					}

					$sReportContent .= "<tr><td colspan=6><HR color=#000000></td></tr>
							<tr>$sDateSentPlaceHolder<td></td>
								<td class=header align=right>Total</td>
								<td class=header></td>
								<td class=header>$iPageTotalSuccessesCount</td>
								<td class=header>$iPageTotalErrorsCount</td>
								<td class=header>$iPageTotalTotalCount</td>
							</tr>";

					$sExpReportContent .= $sDateSentPlaceHolder . "\tTotal\t\t$iPageTotalSuccessesCount\t$iPageTotalErrorsCount\t$iPageTotalTotalCount\n";
				}
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}


	if ($sExportExcel && !$bDateRangeNotOk) {
		$sExpReportContent = "Date Sent\tMessage Id\tList Name\tSuccesses\tErrors\tTotal"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";

		$sFileName = "dbMailsSent_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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

	$sListNamesOptions = "<option value=''>All";
	$sListNamesOptions .= "<option value='bda' selected>BDA";
	$sListNamesOptions .= "<option value='bdatest'>BDA Test";


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
<tr><td>List Name</td>
	<td><select name=sListName><?php echo $sListNamesOptions;?>
		</select></td></tr>		
	
<!--<tr><td></td>
	<td><input type=checkbox name=iDisplayDateWise value='1' <?php //echo $sDisplayDateWiseChecked;?>> Display Datewise (nonfunct)</td></tr>		-->
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 &nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td>
	<td colspan=2><!--<input type=checkbox name=sShowQueries value='Y' <?php //echo $sShowQueriesChecked;?>> Show Queries--></td>
</tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
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
		<td class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=dateSent&sDateSentOrder=<?php echo $sDateSentOrder;?>" class=header>Date / Time</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=messageId&sMessageIdOrder=<?php echo $sMessageIdOrder;?>" class=header>Message Id</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=listName&sListNameOrder=<?php echo $sListNameOrder;?>" class=header>List Name</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=successes&sSuccessOrder=<?php echo $sSuccessOrder;?>" class=header>Sucesses</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=errors&sErrorOrder=<?php echo $sErrorOrder;?>" class=header>Errors</a></td>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=total&sTotalOrder=<?php echo $sTotalOrder;?>" class=header>Total</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=5>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).</td></tr>
	<tr><td colspan=5>
		<BR>Date / Time is the first occurance of either a bounce or success for the given list name / message ID.</td></tr>
	<tr><td colspan=5>
		<BR>The data from yesterday will be truncated if the mailing was sent late enough in the day that the processing did not complete until after midnight.</td></tr>
	<tr><td colspan=5>
		<BR>The oldest data in the report will contain only partial data if the previous day's email was sent late in the day and did not complete processing until after midnight.</td></tr>
	<tr><td colspan=5><BR><BR></td></tr>
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

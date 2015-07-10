<?php

/*********

Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Ampere Mailing Stats - Bounces Per Domain";

session_start();

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

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 0;
	$iDisplayTopX = 25;

	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	$sDomainNameOptions .= "<option value=''>All";

	if (!$sViewReport) {

		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, $sYesterday ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, $sYesterday ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, $sYesterday ), 8, 2);

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

	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "countBounced";
		$sCountBouncedOrder = "DESC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "countBounced" :
			$sCurrOrder = $sCountBouncedOrder;
			$sCountBouncedOrder = ($sCountBouncedOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sCountBouncedOrder;
			$sCountBouncedOrder = ($sCountBouncedOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 25;
	}
	if (!($iPage)) {
		$iPage = 1;
	}


	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sStatus=$sStatus&sListName=$sListName&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";

	dbSelect( "ezmlm" );

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {

			if ($iDisplayDateWise) {
				exit();
			} else {

				if ($sShowErrors) {
					$sErrorSelect = "errorNum, errorText, ";
					$sErrorNumPlaceHolder = "<td class=header>errorNum</td><td class=header>errorText</td>";
				}

				$sReportQuery = "select $sErrorSelect
								domain, count
							from mailLogErrorStats 
							where dateTimeStarted between '$sDateFrom 00:00:00' and '$sDateTo 23:59:59'";

				if ($sDomainName != "") {
					$sReportQuery .= "and domain = '$sDomainName' ";
				}

				if ($sShowErrors) {
					$sReportQuery .= "group by domain, errorNum ";
				} else {
					$sReportQuery .= "group by domain ";
				}

				$sReportQuery .= "order by count DESC";

			}

			//		echo $sReportQuery;

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

			//		echo "((".dbNumRows($rReportResult)."))";

			if ( dbNumRows($rReportResult) >0) {
				if ($iTotalPages > $iPage ) {
					$iNextPage = $iPage+1;
					$sNextPageLink = "<a href='".$sSortLink."&sStatus=$sStatus&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&sDomainName=$sDomainName&sShowErrors=$sShowErrors' class=header>Next</a>";
					$sLastPageLink = "<a href='".$sSortLink."&sStatus=$sStatus&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder&sDomainName=$sDomainName&sShowErrors=$sShowErrors' class=header>Last</a>";
				}
				if ($iPage != 1) {
					$iPrevPage = $iPage-1;
					$sPrevPageLink = "<a href='".$sSortLink."&sStatus=$sStatus&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage&sDomainName=$sDomainName&sShowErrors=$sShowErrors' class=header>Previous</a>";
					$sFirstPageLink = "<a href='".$sSortLink."&sStatus=$sStatus&sListName=$sListName&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage&sDomainName=$sDomainName&sShowErrors=$sShowErrors' class=header>First</a>";
				}
				while ($oReportRow = dbFetchObject($rReportResult)) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					$sDateColumn = "<td>$oReportRow->dateSent</td>";
					$sExpDateColumn = "$oReportRow->dateSent\t";

					$sReportContent .= "<tr class=$sBgcolorClass><td>$oReportRow->domain</td>";

					if ($sShowErrors) {
						$sReportContent .= "<td>$oReportRow->errorNum</td><td>";
						$sReportContent .= chunk_split($oReportRow->errorText, 100);
						$sReportContent .= "</td>";
						$sExpReportContent .= "$oReportRow->domain\t$oReportRow->errorNum\t$oReportRow->count\t";
						$sExpReportContent .= "$oReportRow->errorText\n";
					} else {
						$sExpReportContent .= "$oReportRow->domain\t$oReportRow->count\n";

					}

					$sReportContent .= "<td align='right'>$oReportRow->count</td></tr>";


					$iPageTotalBounce += $oReportRow->count;

				}

				if ($sShowErrors) {
					$sReportBuffer = "<td colspan=2>";
					$sReportColSpan = "4";
				} else {
					$sReportColSpan = "2";
				}

				$sReportContent .= "<tr><td colspan=$sReportColSpan><HR color=#000000></td></tr>
							<tr>
								<td class=header align=right>Page Total Bounce Count</td>";
				$sReportContent .= $sReportBuffer;
				$sReportContent .= "<td class=header align=right>$iPageTotalBounce</td>
							</tr>";

				$sExpReportContent .= "\tPage Total Bounce Count\t$iPageTotalBounce\n";

			}
		}
	} else {
		$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
	}

	if ($sExportExcel && !$bDateRangeNotOk) {
		$sExpReportContent = "Domain Name\tBounce Count"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";

		$sFileName = "dbMailsBounced_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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

	if ($sShowErrors) {
		$sShowErrorsChecked = "checked";
	}

	$sDomainQuery = "SELECT distinct domain 
	FROM mailLogErrorStats 
	WHERE dateTimeStarted between '$sDateFrom 00:00:00' and '$sDateTo 23:59:59'
	ORDER BY domain";
	$rDomainQuery = dbQuery($sDomainQuery);
	echo dbError();
	while ($oReportRow = dbFetchObject($rDomainQuery)) {
	
	if( $sDomainName == $oReportRow->domain ) {
		$sDomainNameSelected = " selected ";
	} else {
		$sDomainNameSelected = "";
	}

	$sDomainNameOptions .= "<option value='$oReportRow->domain' $sDomainNameSelected>$oReportRow->domain";
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
<tr><td>Domain Name</td>
	<td><select name=sDomainName><?php echo $sDomainNameOptions;?>
		</select></td></tr>		
<!--<tr><td></td>
	<td><input type=checkbox name=iDisplayDateWise value='1' <?php //echo $sDisplayDateWiseChecked;?>> Display Datewise (nonfunct)</td></tr>		-->
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 &nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
 &nbsp; &nbsp; <input type=checkbox name=sShowErrors value="Y" <?php echo $sShowErrorsChecked;?>> Show Errors</td>
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
	<tr>
		<td class=header>Domain Name</td>
		<?php echo $sErrorNumPlaceHolder; ?>
		<td class=header align="right">Bounce Count</td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=5 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=5>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
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
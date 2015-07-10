<?php
/*********
Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.
**********/
session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "OT Page Displays & Gross Leads Report";

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
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {

		$iMonthTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iYearTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 0, 4);

		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = 1;//substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
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
		$sOrderColumn = "openDate";
		$sDateSentOrder = "DESC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "opens" :
			$sCurrOrder = $sOpensOrder;
			$sOpensOrder = ($sOpensOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "A.leads" :
			$sCurrOrder = $sLeadsOrder;
			$sLeadsOrder = ($sLeadsOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sOpenDateOrder;
			$sOpenDateOrder = ($sOpenDateOrder != "DESC" ? "DESC" : "ASC");
		}
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
							&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";
	$sToday = date('Y')."-".date('m')."-".date('d');
	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk  && "$iYearTo-$iMonthTo-$iDayTo"<$sToday) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				if ($iDisplayDateWise) {
				} else {

					$sReportQuery = "SELECT openDate, sum(opens) as opens
					FROM pageDisplayStats
					WHERE openDate BETWEEN '$sDateFrom' AND '$sDateTo'
					group by pageDisplayStats.openDate
					ORDER BY openDate";
					//ORDER BY $sOrderColumn $sCurrOrder
		if ($orderColumn != "A.leads") {
		$sReportQuery .= " $sCurrOrder";
		}
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
					
					while ($oReportRow = dbFetchObject($rReportResult)) {
						$aReportContent[$oReportRow->openDate]['openDate'] = $oReportRow->openDate;
						$aReportContent[$oReportRow->openDate]['opens'] = $oReportRow->opens;
						
						$sOpens = $sOpens + $oReportRow->opens;
					}
					
								
					
					$sReportLeadsQuery = "SELECT count(email) as leads, substring(dateTimeAdded,1,10) as dateOnly
								FROM otDataHistory
								WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
								group by dateOnly";
								
								if ($orderColumn == "A.leads") {
									$sCurrOrder = $sLeadsOrder;
									if ($sCurrOrder == "DESC") {
										$sCurrOrder = "ASC";
									} else {
										$sCurrOrder = "DESC";
									}
									$sLeadsOrder = ($sLeadsOrder != "DESC" ? "DESC" : "ASC");
									$sReportLeadsQuery .= "ORDER BY leads $currOrder";
								}
								
					$rReportLeadsResult = dbQuery($sReportLeadsQuery);
					echo dbError();
					
					while ($oReportLeadsRow = dbFetchObject($rReportLeadsResult)) {
						$aReportContent[$oReportLeadsRow->dateOnly]['leads'] = $oReportLeadsRow->leads;
						$sLeads = $sLeads + $oReportLeadsRow->leads;
					}
					
					
					


					foreach ($aReportContent as $aReportRow) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}
						
						
					//	ksort($sReportContent);
						
						
						
						$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportRow['openDate']."</td>
								<td>".$aReportRow['opens']."</td>
								<td>".$aReportRow['leads']."</td>
							</tr>";
						
						$sExpReportContent .= "".$aReportRow['openDate']."\t".$aReportRow['opens']."\t" .
							"".$aReportRow['leads']."\n";
					}
							$sReportContent .= "<tr><td colspan=3><HR color=#000000></td></tr>
							<tr><td class=header>Total</td>
								<td class=header>$sOpens</td>
								<td class=header>$sLeads</td>
							</tr>";
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days) OR includes today.";
		}
	
	}

	if ($sExportExcel && !$bDateRangeNotOk) {
		$sExpReportContent = "Date\tPage Displayed\tLeads\n".$sExpReportContent;
		$sExpReportContent .= "Total:\t$sOpens\t$sLeads\n";
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
	
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	
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
	<tr><td colspan=3 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=3 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=openDate&sOpenDateOrder=<?php echo $sOpenDateOrder;?>" class=header>Date</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=opens&sOpensOrder=<?php echo $sOpensOrder;?>" class=header>Page Displayed</a></td>
		<!--<td class=header class=header>Leads</td>-->
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=A.leads&sLeadsOrder=<?php echo $sLeadsOrder;?>" class=header>Gross Leads</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=3 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=3 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=3>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).</td></tr>
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

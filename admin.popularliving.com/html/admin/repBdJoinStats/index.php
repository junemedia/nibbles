<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "BD Join Stats";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

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

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && isset($sSourceCode)) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {

			$sSubQuery = "SELECT links.sourceCode, links.rate, count(email) subCount, count(DISTINCT email) uniqueSubCount
				  FROM   links, joinEmailSub
				  WHERE  links.sourceCode = joinEmailSub.sourceCode
				  AND	 links.typeCode = 'J'
				  AND	 dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";


			if ($sSourceCode != '') {
				if ($sFilter == 'startsWith') {
					$sSubQuery .= " AND links.sourceCode LIKE '$sSourceCode%' ";
				} else {
					$sSubQuery .= " AND links.sourceCode = '$sSourceCode'";
				}
			}

			$sSubQuery .= " GROUP BY links.sourceCode";

			// start of track users' activity in nibbles 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sSubQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		
			
			
			$rSubResult = dbQuery($sSubQuery);
			echo dbError();
			$i=0;
			while ($oSubRow = dbFetchObject($rSubResult)) {
				$sTempSourceCode = $oSubRow->sourceCode;
				$fRate = $oSubRow->rate;
				$iSubCount = $oSubRow->subCount;
				$iUniqueSubCount = $oSubRow->uniqueSubCount;
				$aReportArray['sourceCode'][$i] = $sTempSourceCode;
				$aReportArray['rate'][$i] = $fRate;
				$aReportArray['subCount'][$i] = $iSubCount;
				$aReportArray['uniqueSubCount'][$i] = $iUniqueSubCount;

				$fRevenue = 0;
				// get revenue for this sourceCode
				if ($sDateFrom == date('Y')."-".date('m')."-".date('d')) {
					$sRevenueQuery = "SELECT sum(revPerLead) as revenue
							  FROM   otData
							  WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo' 
							  AND    sourceCode = '$sTempSourceCode'";
				} else {
					$sRevenueQuery = "SELECT sum(revPerLead) as revenue
							  FROM   otDataHistory
							  WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo' 
							  AND	 sourceCode = '$sTempSourceCode'
							  AND 	 postalVerified = 'V' AND processStatus = 'P'";
				}

				$rRevenueResult = dbQuery($sRevenueQuery);
				
				while ($oRevenueRow = dbFetchObject($rRevenueResult)) {
					$fRevenue = $oRevenueRow->revenue;
				}

				$fRevenue = sprintf("%12.2f",round($fRevenue, 2));
				$aReportArray['revenue'][$i] = $fRevenue;

				// initialize elements for confirm counts also, to keep array length same.
				$aReportArray['confirmCount'][$i] = '0';
				$aReportArray['uniqueConfirmCount'][$i] = '0';

				$i++;
			}


			$sConfirmQuery = "SELECT links.sourceCode, links.rate, count(email) confirmCount, count(DISTINCT email) uniqueConfirmCount, links.rate
				  FROM   links, joinEmailConfirm
				  WHERE  links.sourceCode = joinEmailConfirm.sourceCode
				  AND	 links.typeCode = 'J'
				  AND	 dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";

			if ($sSourceCode != '') {
				if ($sFilter == 'startsWith') {
					$sConfirmQuery .= " AND links.sourceCode LIKE '$sSourceCode%' ";
				} else {
					$sConfirmQuery .= " AND links.sourceCode = '$sSourceCode'";
				}
			}

			$sConfirmQuery .= " GROUP BY links.sourceCode";

			$rConfirmResult = dbQuery($sConfirmQuery);
			echo dbError();
			while ($oConfirmRow = dbFetchObject($rConfirmResult)) {
				$sTempSourceCode = $oConfirmRow->sourceCode;
				$fRate = $oConfirmRow->rate;
				$iConfirmCount = $oConfirmRow->confirmCount;
				$iUniqueConfirmCount = $oConfirmRow->uniqueConfirmCount;
				$iFound = 'false';
				for ($x=0; $x < count($aReportArray['sourceCode']); $x++) {
					if ($aReportArray['sourceCode'][$x] == $sTempSourceCode) {
						$iFound = "$x";
						break;
					}
				}

				
				if ($iFound == 'false') {
					$aReportArray['sourceCode'][$i] = $sTempSourceCode;
					$aReportArray['rate'][$i] = $fRate;
					$aReportArray['confirmCount'][$i] = $iConfirmCount;
					$aReportArray['uniqueConfirmCount'][$i] = $iUniqueConfirmCount;

					$fRevenue = 0;
					// get revenue for this sourceCode
					if ($sDateFrom == date('Y')."-".date('m')."-".date('d')) {
						$sRevenueQuery = "SELECT sum(revPerLead) as revenue
							  FROM   otData
							  WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo' 
							  AND    sourceCode = '$sTempSourceCode'";
					} else {
						$sRevenueQuery = "SELECT sum(revPerLead) as revenue
							  FROM   otDataHistory
							  WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo' 
							  AND	 sourceCode = '$sTempSourceCode'
							  AND 	 postalVerified = 'V' AND processStatus = 'P'";
					}

					$rRevenueResult = dbQuery($sRevenueQuery);
					echo dbError();
					while ($oRevenueRow = dbFetchObject($rRevenueResult)) {
						$fRevenue = $oRevenueRow->revenue;
					}

					$fRevenue = sprintf("%12.2f",round($fRevenue, 2));
					$aReportArray['revenue'][$i] = $fRevenue;

					// initialize elements for sub counts also, to keep array length same.
					$aReportArray['subCount'][$i] = '0';
					$aReportArray['uniqueSubCount'][$i] = '0';

					$i++;
				} else {
					
					$aReportArray['confirmCount'][$iFound] = $iConfirmCount;
					$aReportArray['uniqueConfirmCount'][$iFound] = $iUniqueConfirmCount;
				}
			}

		}
	}

	$fGrossRecovery = 0;
	$fGrossRevenue = 0;
	$fGrossCost = 0;
	
	$sExpReportContent = '';
	for ($i = 0; $i < count($aReportArray['sourceCode']); $i++) {
		$fCost = $aReportArray['rate'][$i] * $aReportArray['uniqueConfirmCount'][$i];
		$fCost = sprintf("%6.2f",round($fCost, 2));

		$fRecovery = 0;
		if ($fCost != 0) {
			$fRecovery = ($aReportArray['revenue'][$i] * 100) / $fCost ;
		}
		$fRecovery = sprintf("%6.2f",round($fRecovery, 2));
		
		if ($fCost == 0) {
			$fRecovery = "N/A";
		}
		$sReportContent .=  "<tr><td>".$aReportArray['sourceCode'][$i]."</td>
							<td align=right>".$aReportArray['subCount'][$i]."</td>
							<td align=right>".$aReportArray['uniqueSubCount'][$i]."</td>
							<td align=right>".$aReportArray['confirmCount'][$i]."</td>
							<td align=right>".$aReportArray['uniqueConfirmCount'][$i]."</td>
							<td align=right>".$aReportArray['rate'][$i]."</td>							
							<td align=right>$fCost</td>
							<td align=right>".$aReportArray['revenue'][$i]."</td>
							<td align=right>$fRecovery</td>
						</tr>";
		
		$sExpReportContent .= $aReportArray['sourceCode'][$i] . "\t".
							  $aReportArray['subCount'][$i] . "\t".
							  $aReportArray['uniqueSubCount'][$i] . "\t".
							  $aReportArray['confirmCount'][$i] . "\t".
							  $aReportArray['uniqueConfirmCount'][$i] . "\t".
							  $aReportArray['rate'][$i] . "\t".
							  $fCost . "\t".
							  $aReportArray['revenue'][$i] . "\t".
							  $fRecovery . "\n";
		
		$fGrossRevenue += $aReportArray['revenue'][$i];
		$fGrossCost += $fCost;
		
	}
	
	
	$fGrossCost = sprintf("%6.2f",round($fGrossCost, 2));
	$fGrossRecovery = sprintf("%6.2f",round($fGrossRecovery, 2));
	
	if ($fGrossCost != 0) {
		$fGrossRecovery = $fGrossRevenue / $fGrossCost;
	}
	$fGrossRecovery = sprintf("%6.2f",round($fGrossRecovery, 2));
			
	$sReportContent .= "<tr><td colspan=9><hr color=#000000></td></tr>
						<tr><td colspan=6></td>
							<td align=right>$fGrossCost</td>
							<td align=right>$fGrossRevenue</td>
							<td align=right>$fGrossRecovery</td></tr>";
	
	$sExpReportContent .= "\t\t\t\t\t\t$fGrossCost\t$fGrossRevenue\t$fGrossRecovery\n";
	
	$sStartsWithChecked = '';
	$sExactMatchChecked = '';

	if ($sFilter == 'startsWith') {
		$sStartsWithChecked = "checked";
	} else {
		$sExactMatchChecked = "checked";
	}

	if ($sShowQueries == "Y") {
		$sShowQueriesChecked = "checked";
	}

	if ($sShowQueries == 'Y') {
		$sQueries = "<b>Sub Query:</b><BR>".$sSubQuery;
		$sQueries .= "<br><br><b>Confirm Query:</b><BR>".$sConfirmQuery;
	}


	
if ($sExportExcel) {
		
		$sExpReportContent = "SourceCode\tGross Sub Count\tUnique Sub Count\tGross Confirm Count\tUnique Confirm Count\tRate\tCost\tRevenue"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nSourceCode: $sSourceCode ";
		if ($sFilter == 'startsWith') {
			$sExpReportContent .= " (Starts With)";
		}
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "bdJoinStats_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
	
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
		<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
	&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td>
		<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=9 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR>
			<BR><BR><BR></td></tr>
	<tr><td colspan=9 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>SourceCode</td>
		<td class=header align=right>Gross Sub Count</td>
		<td class=header align=right>Unique Sub Count</td>
		<td class=header align=right>Gross Confirm Count</td>
		<td class=header align=right>Unique Confirm Count</td>
		<td class=header align=right>Rate</td>
		<td class=header align=right>Cost</td>
		<td class=header align=right>Revenue</td>
		<td class=header align=right nowrap>% Recovery</td>
	</tr>
	<?php echo $sReportContent;?>
	<Tr><td colspan=9><hr color=#000000></td></tR>
	<tr><td colspan=9><BR><b>Notes -</b><BR>
	<BR>For history dates, revenue only reflects records where PV attempted. For today's date, report reflects gross revenue.
		<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=9><BR><BR></td></tr>
	<tr><td colspan=9><?php echo $sQueries;?></td></tr>
	
	</table></td></tr></table></td></tr>
	</table>
	
</form>

<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
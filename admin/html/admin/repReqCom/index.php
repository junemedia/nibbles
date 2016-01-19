<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$iScriptStartTime = getMicroTime();
$sPageTitle = "Requestors / Completors Report (Unique Email Counts)";

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
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$sExcludeApiLeads = 'Y';
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

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
			&sViewReport=$sViewReport&iCatId=$iCatId";

	if ($sViewReport != '') {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				$iTotalRequestors = 0;
				$iTotalCompletors = 0;
				$iGrandTotal = 0;
				$i = 0;
				$sFilter = '';
				$sDupTotalHtml = '';
				$sMoreNotes = '';
				
				
				if ($iCatId !='') {
					$sFilter = " AND categories.id = '$iCatId' ";
				}

				$sRequestorQuery = "SELECT categories.id as catId, categories.title, count(DISTINCT email) as requestors
							FROM xOutDataHistory, categoryMap, categories
							WHERE xOutDataHistory.dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
							AND categoryMap.categoryId = categories.id
							AND xOutDataHistory.offerCode = categoryMap.offerCode
							$sFilter
							GROUP BY categories.title";
				
				
				$rRequestorResult = dbQuery($sRequestorQuery);
				echo dbError();
				$aReportArray = array();
				while ($sRequestorRow = dbFetchObject($rRequestorResult)) {
					$sTitle = $sRequestorRow->title;
					$iCategoryId = $sRequestorRow->catId;
					$iRequestors = $sRequestorRow->requestors;
					
					$sCompletorQuery = "SELECT count(DISTINCT email) as completors
								FROM otDataHistory, categoryMap
								WHERE otDataHistory.dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
								AND categoryMap.offerCode = otDataHistory.offerCode
								AND categoryMap.categoryId = '$iCategoryId'
								AND otDataHistory.processStatus != 'R'
								GROUP BY categoryMap.categoryId";
					$rCompletorResult = dbQuery($sCompletorQuery);
					echo dbError();
					while ($sCompletorRow = dbFetchObject($rCompletorResult)) {
						$iCompletors = $sCompletorRow->completors;
					}
					
					$aReportArray['sTitle'][$i] = $sTitle;
					$aReportArray['iCompletors'][$i] = $iCompletors;
					$aReportArray['iRequestors'][$i] = $iRequestors;
					$aReportArray['iCategoryId'][$i] = $iCategoryId;
					$aReportArray['iCategoryTotal'][$i] = $iRequestors + $iCompletors;
					
					$i++;
				}
				
				
				for ($x = 0; $x < count($aReportArray['sTitle']); $x++) {
					$sTitle = $aReportArray['sTitle'][$x];
					$iCompletors = $aReportArray['iCompletors'][$x];
					$iRequestors = $aReportArray['iRequestors'][$x];
					$iCategoryId = $aReportArray['iCategoryId'][$x];
					$iCategoryTotal = $aReportArray['iCategoryTotal'][$x];
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sReportContent .= "<tr class=$sBgcolorClass>
								<td>$sTitle</td>
								<td>$iRequestors</td>
								<td>$iCompletors</td>
								<td>$iCategoryTotal</td>
								</tr>";
					
					$iTotalRequestors += $iRequestors;
					$iTotalCompletors += $iCompletors;
					$iGrandTotal += $iCategoryTotal;
				}
				
				
				if ($iCatId == '') {
					$sDupRequestorQuery = "SELECT count(DISTINCT email) AS dupRequestor FROM xOutDataHistory
								WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
					$rDupRequestorResult = dbQuery($sDupRequestorQuery);
					while ($sDupRequestorRow = dbFetchObject($rDupRequestorResult)) {
						$iDupRequestors = $sDupRequestorRow->dupRequestor;
					}
					
					
					$sDupCompletorQuery = "SELECT count(DISTINCT email) as dupCompletors
									FROM otDataHistory
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									AND processStatus != 'R'";
					$rDupCompletorResult = dbQuery($sDupCompletorQuery);
					echo dbError();
					while ($sDupCompletorRow = dbFetchObject($rDupCompletorResult)) {
						$iDupCompletors = $sDupCompletorRow->dupCompletors;
					}
					
					$iDupGrandTotal = $iDupCompletors + $iDupRequestors;
					
					$sDupTotalHtml = "<tr><td><b>Dedups Total: </b></td>
								<td>$iDupRequestors</td>
								<td>$iDupCompletors</td>
								<td>$iDupGrandTotal</td>
								</tr>";
					
					$sMoreNotes = "<b>Query 3 - [Total Completors unique email count]:</b><br>$sDupCompletorQuery<br><br>
					<b>Query 4 - [Total Requestors unique email count]:</b><br>$sDupRequestorQuery<br><br>";
				}
				

				$sReportContent .= "<tr><td colspan=4><hr color=#000000></td></tr>
								<tr><td><b>Grand Total: </b></td>
								<td>$iTotalRequestors</td>
								<td>$iTotalCompletors</td>
								<td>$iGrandTotal</td>
								</tr>
								$sDupTotalHtml";
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}



	$sCatQuery = "SELECT * FROM categories order by title";
	$rCatResult = mysql_query($sCatQuery);
	$sCatOption = "<option value=''>";
	while ($oCatRow = mysql_fetch_object($rCatResult)) {
		if ($oCatRow->id == $iCatId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sCatOption .= "<option value='$oCatRow->id' $sSelected>$oCatRow->title";
	}
	
	include("../../includes/adminHeader.php");
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
	echo $sReportJavaScript;	// display javascript from reportInclude.php which defined funcReportClicked() function
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
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?></select></td></tr>	
<tr><td>Category</td>
	<td><select name='iCatId'><?php echo $sCatOption;?></select></td>
</tr>
	
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 </td><td colspan=2></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
		<td class=header>Category</td>
		<td class=header>Requestors</td>
		<td class=header>Completors</td>
		<td class=header>Total</td>
	</tr>
<?php echo $sReportContent;?>
<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=4 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=4>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Grand Total:  Grand total should be higher because one offer can be in more than one category.<br>
		Requestors: Number of times the offer that belongs to this category was checked on the first page.<br>
		Completors: Number of offers taken that belongs to this category.<br>
		Total: Requestors plus Completors.<br><br>
		<b>Query 1 - [Get Categories and Count]: </b><br><?php echo $sRequestorQuery; ?><br><br>
		<b>Query 2 - [For each category returned from Query 1, get completors count]:</b><br><?php echo $sCompletorQuery; ?><br><br>
		<?php echo $sMoreNotes; ?>
		</td></tr>
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
<?php

/*********
Script to Display 
**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Click to Submit Conversion Rate";

session_start();

$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 7;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iMonthTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iYearTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
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


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	
	
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		$sQuery = '';
		
		$sTruncateTempTableQuery = "TRUNCATE TABLE tempRepClickToSubmitRatio";
		$rTruncateTempTableResult = dbQuery($sTruncateTempTableQuery);
		
		// start of track users' activity in nibbles 
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles

		$report = array();
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";	
		$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
		$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";	


		$sGetBdRedirectsQuery = "SELECT * FROM    bdRedirectsTrackingHistorySum
						  WHERE   clickDate BETWEEN '$sDateFrom'  AND 	'$sDateTo'";
		if ($sSourceCode != '') {
			$sGetBdRedirectsQuery .= " AND      sourceCode = '$sSourceCode'";
		}

		$sQuery .= $sGetBdRedirectsQuery."&nbsp;&nbsp;&nbsp;(Evertime the user comes to our site with source code
					we insert entry into bdRedirectsTracking table).<br><br>";
		$rGetBdRedirectsResult = dbQuery($sGetBdRedirectsQuery);
		echo dbError();
		while ($oRedirectRow = dbFetchObject($rGetBdRedirectsResult)) {
			$sInsertRedirectQuery = "INSERT INTO tempRepClickToSubmitRatio (sourceCode,redirectCount,submitCount)
				VALUES (\"$oRedirectRow->sourceCode\",\"$oRedirectRow->clicks\",'0')";
			$rInsertRedirectResult = dbQuery($sInsertRedirectQuery);
		}
		
	
		$sClickedSubmitQuery = "SELECT sourceCode, count(sourceCode) as counts FROM xOutDataHistory
	    				  WHERE   dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		if ($sSourceCode != '') {
			$sClickedSubmitQuery .= " AND  sourceCode = '$sSourceCode'";
		}
		$sClickedSubmitQuery .= " GROUP BY sourceCode ";
		
		$sQuery .= $sClickedSubmitQuery."&nbsp;&nbsp;&nbsp;(Everytime the user clicks on submit button, we insert entry
			into xOutData table with sourceCode.  If user clicks on submit and get error message, we don't count that.  We
			only count if user hit submit on the page and it sends user to 2nd page questions or next page).<br><br>";
		
		$rClickedSubmitResult = dbQuery($sClickedSubmitQuery);
		echo dbError();
		while ($oSubmitRow = dbFetchObject($rClickedSubmitResult)) {
			$sInsertRedirectQuery = "INSERT INTO tempRepClickToSubmitRatio (sourceCode,redirectCount,submitCount)
				VALUES (\"$oSubmitRow->sourceCode\",\"0\",\"$oSubmitRow->counts\")";
			$rInsertRedirectResult = dbQuery($sInsertRedirectQuery);
		}
		

		
		$sGetReportDataQuery = "SELECT sourceCode, sum(redirectCount) as redirects, sum(submitCount) as submits
			 		FROM tempRepClickToSubmitRatio WHERE sourceCode != '' GROUP BY sourceCode";
		$rGetReportDataResult = dbQuery($sGetReportDataQuery);
		echo dbError();
		while ($oReportRow = dbFetchObject($rGetReportDataResult)) {
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN_WHITE";
			} else {
				$bgcolorClass = "ODD";
			}
				
			if ($oReportRow->redirects > 0) {
				$sConversionRate = number_format((($oReportRow->submits / $oReportRow->redirects)*100),1);
			} else {
				$sConversionRate = 0;
			}
			$sReportContent .= "<tr class='$bgcolorClass'><td>$oReportRow->sourceCode</td>
							<td>$oReportRow->redirects</td>
							<td>$oReportRow->submits</td>
							<td>$sConversionRate</td></tr>";
			
			$iRedirectTotal += $oReportRow->redirects;
			$iSubmitTotal += $oReportRow->submits;
		}
		$sRateTotal = number_format((($iSubmitTotal / $iRedirectTotal)*100),1);
		$sReportContent .= "<tr><td colspan=5 align=center><hr color=#000000></td></tr>
							<tr><td><b>Total: </b></td>
							<td><b>$iRedirectTotal</b></td>
							<td><b>$iSubmitTotal</b></td>
							<td><b>$sRateTotal</b></td></tr>";
	}
	
	
	
	$sSourceCodeQuery = "SELECT sourceCode FROM links order by sourceCode";
	$rSourceCodeResult = mysql_query($sSourceCodeQuery);
	$sSourceCodeOption = "<option value=''>";
	while ($oSourceCodeRow = mysql_fetch_object($rSourceCodeResult)) {
		if ($oSourceCodeRow->sourceCode == $sSourceCode) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sSourceCodeOptions .= "<option value='$oSourceCodeRow->sourceCode' $sSelected>$oSourceCodeRow->sourceCode";
	}
	

	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
	include("../../includes/adminHeader.php");	

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
	
	<tr><td>Source Code</td><td><select name=sSourceCode>
			<option value=''>All
			<?php echo $sSourceCodeOptions;?>
	</select></td></tr>
	
<tr><td></td><td>
<input type=submit name='sSubmit' value='View Report' onClick="funcReportClicked('report');"></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
	<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $sDateFrom to $sDateTo"; ?>
	<BR><BR></td></tr>
	
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
	<tr>
		<td  class=header>Source Code</td>
		<td class=header>Redirects Clicked</td>
		<td class=header>Clicked Submit</td>
		<td class=header>% Submited</td>
	</tr>
	
			<?php echo $sReportContent;?>
			<tr><td colspan=5 align=center><hr color=#000000></td></tr>	
	
	<tr><td colspan=5><BR><BR></td></tr>
	
	<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	
	<tr><td colspan=5>Today's data is not included on this report.
				<br><br></td></tr>	
	<tr><td colspan=5>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<br><br>
	<tr><td colspan=5><b>Query:</b><br><?php echo $sQuery;?></td></tr>
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
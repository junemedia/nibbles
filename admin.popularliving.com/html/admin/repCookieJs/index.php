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
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
set_time_limit(50000);

$sPageTitle = "Cookie/JavaScript Disabled Report";

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sCookie == '') { $sCookie = 'N'; }
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";
	$sRedirectsTable = 'bdRedirectsTracking';
	$sReportQuery = '';
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

	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "email";
		$sSessionIdOrder = "ASC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "count" :
			$sCurrOrder = $sCountOrder;
			$sCountOrder = ($sCountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "email" :
			$sCurrOrder = $sEmailOrder;
			$sEmailOrder = ($sEmailOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "subSourceCode" :
			$sCurrOrder = $sSubSourceCodeOrder;
			$sSubSourceCodeOrder = ($sSubSourceCodeOrder != "DESC" ? "DESC" : "ASC");
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
	if (!($iLeadsPerEmail)) {
		$iLeadsPerEmail = 3;
	}

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sSourceCode=$sSourceCode&sRemoteIp=$sRemoteIp&sCookie=$sCookie";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				
				$sCheckFrom = $iMonthFrom.$iDayFrom.$iYearFrom;
				$sCheckTo = $iMonthTo.$iDayTo.$iYearTo;
				
				if (($sCheckFrom == $sCheckFrom) && ($sCheckFrom == date('mdY'))) {
					$sRedirectsTable = 'bdRedirectsTracking';
				} else {
					$sRedirectsTable = 'bdRedirectsTrackingHistory';
				}
				
				
				/*if ($sViewReport == 'History Report') {
					
				} else {
					
				}*/
				
				$sReportQuery = "SELECT * FROM $sRedirectsTable
						WHERE clickDate BETWEEN '$sDateFrom' AND '$sDateTo'";
				if ($sSourceCode != '') {
					$sReportQuery .= " AND sourceCode LIKE '%$sSourceCode%'";
				}
				if ($sRemoteIp !='') {
					$sReportQuery .= " AND ipAddress LIKE '%$sRemoteIp%'";
				}
				if ($sCookie != 'B') {
					$sReportQuery .= " AND cookieEnabled = '$sCookie'";
				}

				// start of track users' activity in nibbles
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sReportQuery) . "\")";
				$rResult = dbQuery($sAddQuery);
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
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}

					$sReportContent .= "<tr class=$sBgcolorClass>
							<td>$oReportRow->clickDate</td>
							<td>$oReportRow->sourceCode</td>
							<td>$oReportRow->subSourceCode</td>
							<td>$oReportRow->ipAddress</td>
							<td>$oReportRow->cookieEnabled</td>
							</tr>";
				}
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	// Get all sourceCode
	/*$sSourceCodeQuery = "SELECT DISTINCT sourceCode
				FROM $sRedirectsTable
      			ORDER BY sourceCode ASC";
	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();
	$sSourceCodeOption = '';
	while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
		$sTempSourceCode = $oSourceCodeRow->sourceCode;
		if ($sSourceCode) {
			if ($sTempSourceCode == $sSourceCode) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		} else {
			if ($sTempSourceCode == $sSourceCode && isset($sSourceCode)) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		}
		$sSourceCodeOption .= "<option value='".$oSourceCodeRow->sourceCode."' $sSourceCodeSelected>$oSourceCodeRow->sourceCode";
	}
	

	$sQuery1 = "SELECT DISTINCT ipAddress
				FROM $sRedirectsTable
      			ORDER BY ipAddress ASC";
	$rResult1 = dbQuery($sQuery1);
	echo dbError();
	$sIpOption = '';
	while ($oRow1 = dbFetchObject($rResult1)) {
		if ($oRow1->ipAddress == $sRemoteIp) {
			$sSelected = 'selected';
		} else {
			$sSelected = '';
		}
		$sIpOption .= "<option value='".$oRow1->ipAddress."' $sSelected>$oRow1->ipAddress";
	}
	*/
	

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
	

<tr><td>Source Code:</td><td>
<input type="text" name='sSourceCode' size="15" maxlength="20" value="<?php echo $sSourceCode; ?>">
</td></tr>

<tr><td>Remote IP:</td><td>
<input type="text" name='sRemoteIp' size="15" maxlength="16" value="<?php echo $sRemoteIp; ?>">
</td>
</tr>

<tr><td></td>
<td>
<input type="radio" name="sCookie" value="Y" <?php if($sCookie=='Y') { echo 'checked'; } ?>> &nbsp;Cookie Enabled
<input type="radio" name="sCookie" value="N" <?php if($sCookie=='N') { echo 'checked'; } ?>> &nbsp;Cookie Disabled
<input type="radio" name="sCookie" value="B" <?php if($sCookie=='B') { echo 'checked'; } ?>> &nbsp;All
</td>
</tr>

<tr><td colspan=2>
	<input type=button name=sSubmit value='View Report' onClick="funcReportClicked('history');"> 
</td></tr>

<tr><td colspan=4 align=right class=header>
	<input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; &nbsp; Go To Page 
	<input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> 
	&nbsp; <?php echo $sNextPageLink;?> 
	&nbsp; <?php echo $sLastPageLink;?> 
	&nbsp; <?php echo $sCurrentPage;?>
</td></tr>


</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=6 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=6 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
		<td class=header>Date</td>
		<td class=header>Source Code</td>
		<td class=header>SubSource Code</td>
		<td class=header>Remote Ip</td>
		<td class=header>Cookie Enabled</td>
	</tr>
<?php echo $sReportContent;?>
<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=6 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=6>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		View either history report or today's report.  You cannot combine history and today's report together.
		<br><b>Query:</b><br><?php echo $sReportQuery; ?>
		</td></tr>
	<tr><td colspan=6><BR><BR></td></tr>
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

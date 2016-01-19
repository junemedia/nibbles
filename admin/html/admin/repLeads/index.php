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


$sPageTitle = "Leads Report";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";
	$sReportQuery = '';

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 7;
	$iDefaultDaysToReport = 0;
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
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	if (!($iLeadsPerEmail)) {
		$iLeadsPerEmail = 3;	
	}

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sOfferCode=$sOfferCode&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sPageName=$sPageName&sSourceCode=$sSourceCode";
	
	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				$sSourceCodeFilter = '';
				$sOfferCodeFilter = '';
				
				if ($sSourceCode != '') {
					$sSourceCodeFilter = " AND sourceCode = '$sSourceCode'";
				}
				
				if ($sOfferCode !='') {
					$sOfferCodeFilter = " AND offerCode = '$sOfferCode'";
				}
				
				if ($sViewReport == 'History Report') {
					$sOtData = 'otDataHistory';
				} else {
					$sOtData = 'otData';
				}
				
				$sReportQuery = "SELECT * FROM $sOtData
						WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
						$sSourceCodeFilter
						$sOfferCodeFilter";


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
							<td>$oReportRow->dateTimeAdded</td>
							<td>$oReportRow->offerCode</td>
							<td>$oReportRow->email</td>
							<td>$oReportRow->sourceCode</td>
							<td>$oReportRow->remoteIp</td>
							<td>$oReportRow->sessionId</td>
							</tr>";
				}
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	// Get all sourceCode
	$sSourceCodeQuery = "SELECT sourceCode
				FROM links
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
	

	// Get all subSourceCode
	$sOfferCodeQuery = "SELECT offerCode
				FROM offers
      			ORDER BY offerCode ASC";
	$sOfferCodeResult = dbQuery($sOfferCodeQuery);
	echo dbError();
	$sOfferCodeOption = '';
	while ($oOfferCodeRow = dbFetchObject($sOfferCodeResult)) {
		$sTempVal = $oOfferCodeRow->offerCode;
		if ($sOfferCode) {
			if ($sTempVal == $sOfferCode) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		} else {
			if ($sTempVal == $sOfferCode && isset($sOfferCode)) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		}
		$sOfferCodeOption .= "<option value='".$oOfferCodeRow->offerCode."' $sSelected>$oOfferCodeRow->offerCode";
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
	


<tr><td>Source Code:</td><td>
<select name='sSourceCode'>
<option value="">All</option>
<?php echo $sSourceCodeOption ?>
</select>
</td></tr>

<tr><td>Offer Code:</td><td>
<select name='sOfferCode'>
<option value="">All</option>
<?php echo $sOfferCodeOption ?>
</select>
</td></tr>


<tr><td colspan=2>
	<input type=button name=sSubmit value='History Report' onClick="funcReportClicked('history');">  &nbsp; &nbsp; 
	<input type=button name=sSubmit value="Today's Report" onClick="funcReportClicked('today');">	
</td></tr>


<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
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
		<td class=header>Offer Code</td>
		<td class=header>Email</td>
		<td class=header>Source Code</td>
		<td class=header>Remote Ip</td>
		<td class=header>Session Id</td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=6 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=6>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
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

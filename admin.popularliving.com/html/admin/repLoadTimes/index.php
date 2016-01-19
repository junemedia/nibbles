<?php

// reporting login info
$reportingHost = "64.132.70.251";
$reportingDbase = "nibbles";
$reportingUser = "nibbles";
$reportingPass = "#a!!yu5";

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


session_start();
$iScriptStartTime = getMicroTime();
$sPageTitle = "Load Times Report";

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
$sUrlOptions .= "<option value=''>All";

if (!$sViewReport) {

	$iMonthTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 5, 2);
	$iDayTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 8, 2);
	$iYearTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 0, 4);

	$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
	$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
	$iDayFrom = 1;//substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
}

$aGblMonthsArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
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
	$sOrderColumn = "dateTime";
	$sDateSentOrder = "DESC";
}

// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
if (!($sCurrOrder)) {
	switch ($sOrderColumn) {
		case "url" :
		$sCurrOrder = $sUrlOrder;
		$sUrlOrder = ($sUrlOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "loadTime" :
		$sCurrOrder = $sLoadTimeOrder;
		$sLoadTimeOrder = ($sLoadTimeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "average" :
		$sCurrOrder = $sAverageOrder;
		$sAverageOrder = ($sAverageOrder != "DESC" ? "DESC" : "ASC");
		default:
		$sCurrOrder = $sDateTimeOrder;
		$sDateTimeOrder = ($sDateTimeOrder != "DESC" ? "DESC" : "ASC");
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
							&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";
$sToday = date('Y')."-".date('m')."-".date('d');

	//echo "test: ".$sViewReport;


if ($sViewReport != "") {

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
		if ($sAllowReport == 'N') {
			$sMessage .= "<br>Server Load Is High. Please check back soon...";
		} else {

			$sReportQuery = "SELECT *
							FROM loadTimes
							WHERE dateTime BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";

				if ($sUrl != "") {
					$sReportQuery .= "and url = '$sUrl' ";
				}
			
			$sReportQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
			$rResult = mysql_query($sAddQuery); 
			echo  mysql_error(); 
			// end of track users' activity in nibbles		
		
			$rReportResult = mysql_query($sReportQuery);
			echo mysql_error();
			
			$iAverage = 0;
			$iCount = 0;
			while ($oReportRow = mysql_fetch_object($rReportResult)) {
				$iCount ++;
				$iLoadTimeTotal += $oReportRow->loadTime;
			}
			
			
			$iNumRecords = mysql_num_rows($rReportResult);

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
			$rReportResult = mysql_query($sReportQuery);


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

			while ($oReportRow = mysql_fetch_object($rReportResult)) {
			//	$iCount ++;
				if ($sBgcolorClass == "white") {
					$sBgcolorClass = "c9c9c9";
				} else {
					$sBgcolorClass = "white";
				}

				$sReportContent .= "<tr bgcolor=$sBgcolorClass><td>$oReportRow->dateTime</td>
								<td>$oReportRow->url</td>
								<td>$oReportRow->loadTime</td>
							</tr>";
			//	$iLoadTimeTotal += $oReportRow->loadTime;
			}
			$iAverage = number_format($iLoadTimeTotal / $iCount, 2, '.', "");

			$sReportContent .= "<tr><td colspan=3><HR color=#000000></td></tr>
							<tr><td class=header>Average</td>
							<td class=header>&nbsp;</td>
							<td class=header>$iAverage</td></tr>";
		}
	} else {
		$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
	}
}


$sUrlQuery = "SELECT distinct url
				FROM loadTimes
				WHERE dateTime BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	ORDER BY url";
	$rDomainQuery = mysql_query($sUrlQuery);
	echo mysql_error();
	while ($oReportRow = mysql_fetch_object($rDomainQuery)) {
	
	if( $sUrl == $oReportRow->url ) {
		$sUrlSelected = " selected ";
	} else {
		$sUrlSelected = "";
	}

	$sUrlOptions .= "<option value='$oReportRow->url' $sUrlSelected>$oReportRow->url";
	}

$iScriptEndTime = getMicroTime();
$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);




$sReportJavaScript = "
<script language=JavaScript>

function funcReportClicked(btnClicked) {
	if (btnClicked == 'history') {
		document.form1.sViewReport.value = \"History Report\";
	} else if (btnClicked == 'today') {
		document.form1.sViewReport.value = \"Today's Report\";
	} else if (btnClicked == 'report') {
		document.form1.sViewReport.value = \"View Report\";
	} else if (btnClicked == 'export') {
		document.form1.sViewReport.value = \"Export Report\";
	} else {
		document.form1.sViewReport.value = btnClicked;
	}
	
	var repClicked = document.form1.reportClicked.value;
	if (repClicked == '') {
		document.form1.reportClicked.value = 'Y';
		
		document.form1.submit();
	} else {
		alert('Report is running... Please Wait');
	}
}

</script>";





// display javascript
echo $sReportJavaScript;

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";




// Pass Date in format yyyy-mm-dd returns yyyy-mm-dd
function DateAdd($intervalType,$interval,$date) {
	$year = substr($date,0,4);
	$month = substr($date,5,2);
	$day = substr($date,8,2);
	switch($intervalType) {
		case "y":
		$time = mktime(0,0,0, $month, $day, $year+$interval);
		break;
		case "m":
		$time = mktime(0,0,0,$month+$interval, $day, $year);
		break;
		case "d":
		$time = mktime(0,0,0,$month, $day+$interval, $year);
	}

	$date = getdate($time);
	if ($date["mon"] <10)
	$month = "0".$date["mon"];
	else
	$month = $date["mon"];
	if ($date["mday"] <10)
	$day = "0".$date["mday"];
	else
	$day = $date["mday"];
	return $date["year"]."-".$month."-".$day;

}

function getMicroTime() {
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}





?>
<html>
<head>
<title>Load Times Report</title>
</head>
<body>
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
<tr><td>URL</td>
	<td><select name=sUrl><?php echo $sUrlOptions;?>
		</select></td></tr>

<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
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
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=dateTime&sDateTimeOrder=<?php echo $sDateTimeOrder;?>" class=header>Date Time</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=url&sUrlOrder=<?php echo $sUrlOrder;?>" class=header>URL</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=loadTime&sLoadTimeOrder=<?php echo $sLoadTimeOrder;?>" class=header>Load Time</a></td>
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
</body>
</html>
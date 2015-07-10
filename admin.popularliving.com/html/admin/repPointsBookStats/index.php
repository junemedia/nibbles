<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Points Book Stats Report";

session_start();


mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin() || ($_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart')) {
	
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
	
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
	
	if (!($sViewReport)) {
		$iYearFrom = $iCurrYear;
		$iMonthFrom = $iCurrMonth;
		$iDayFrom = $iCurrDay;
		
		$iYearTo = $iYearFrom;
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
	} else if ($sHistoryReport) {
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		}
		
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = "01";
		}
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}
	
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sListName=$sListName&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sPageList=$sPageList";

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		$sDateTimeFrom = $sDateFrom." 00:00:00";
		$sDateTimeTo = $sDateTo." 23:59:59";
		
		if ($sPageList != "") {
			$sPageFilter = " AND page = '$sPageList'";
		}

		
		echo dbError();
		$sDataQuery = "SELECT * 
							FROM pointsBookStats
      					 WHERE    dateTimeTaken BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
      					 $sPageFilter
      					 ORDER BY dateTimeTaken";


		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase); 

		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sDataQuery\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		


		
		
		
		$rDataResult = dbQuery($sDataQuery);
		echo  dbError();
		

		$iTotalDisplayed = 0;
		$iTotalTaken = 0;
		$count = 0;
		
		while ($oRepRow = dbFetchObject($rDataResult)) {
			$count++ ;
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN_WHITE";
			} else {
				$bgcolorClass = "ODD";
			}
			$sReportContent .= "<tr class=$bgcolorClass>
								<td>$oRepRow->dateTimeTaken</td>
								<td>$oRepRow->offerTaken</td>
								<td>$oRepRow->pageDisplayed</td>
								<td>$oRepRow->page</td>";
				$iTotalDisplayed = $iTotalDisplayed + $oRepRow->pageDisplayed;
				$iTotalTaken = $iTotalTaken + $oRepRow->offerTaken;		
		}
		

	$sPageQuery = "SELECT distinct page 
					FROM pointsBookStats
					WHERE page != ''
					ORDER BY page";
	$sPageQuery = dbQuery($sPageQuery);
	echo dbError();
	while ($oPageRow = dbFetchObject($sPageQuery)) {
		$sTempPage = $oPageRow->page;
		if ($sPageList) {
			if ($sTempPage == $sPageList) {
				$sPageSelected = "selected";
			} else {
				$sPageSelected = "";
			}
		} else {
			if ($sTempPage == $sPageList && isset($sPageList)) {
				$sPageSelected = "selected";
			} else {
				$sPageSelected = "";
			}
		}
		$sPageOptions .= "<option value='$oPageRow->page' $sPageSelected>$oPageRow->page";
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
	</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Date To
	<select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>
	
	<tr><td>
	Page: </td><td><select name=sPageList>
	<option value='' selected>All</option>
	<?php echo $sPageOptions;?>
	</select>
	</td></tr>
	
	<tr><td colspan=2><input type=button name=sSubmit value='View Report'  onClick="funcReportClicked('report');">
	</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
		<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
			<tr><td>
			<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
			<tr><td>
				<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime; ?></td></tr>
	<tr><br><td width=120 class=header>Date</td><td width=120 class=header>Downloaded eBooks</td><td width=250 class=header>Page Displayed</td>
	<td width=250 class=header>Page</td>
	</tr>
		<?php echo $sReportContent;?>
			<tr><td colspan=4 align=left><hr color=#000000></td></tr>
	<tr><td class=header>Total:</td><td class=header><?php echo $iTotalTaken; ?></td>
			<td class=header><?php echo $iTotalDisplayed;?></td></tr>
			
			<tr><td colspan="4">&nbsp;</td></tr>
			
			
	<tr><td colspan=4 class=header><BR>Notes -</td></tr>
	<tr><Td colspan=4>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
	<tr><td colspan=12><?php echo $sQueries; ?></td></tr>
		</td></tr></table></td></tr></table></td></tr>
	</table>
</td></tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
}
	}
	}
else {
	echo "You are not authorized to access this page...";
}
?>
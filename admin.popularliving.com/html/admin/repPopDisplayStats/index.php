<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Popup Creative Display Stats Report";

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
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
	
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if (!($sViewReport)) {
		$iYearFrom = $iCurrYear;
		$iMonthFrom = $iCurrMonth;
		$iDayFrom = $iCurrDay;
		
		$iYearTo = $iYearFrom;
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
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
	
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		
						
		$sSelectQuery = "SELECT offerId, sum(counts) AS counts
						 FROM   popupCreativeDisplayStats		
						 WHERE  displayDate BETWEEN '$sDateFrom' AND '$sDateTo'
						 GROUP BY offerId";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $sSelectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery);
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		// end of track users' activity in nibbles		
		
		
		$rSelectResult = dbQuery($sSelectQuery);
		
		$iTotalLeads = 0;
		$fTotalRevenue = 0;
		echo dbError();
		
		$count = 0;
		
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
		
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sReportContent .= "<tr class=$sBgcolorClass><td>$oSelectRow->offerId</td>
									<td>$oSelectRow->counts</td></tr>";

		}
		
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
	<tr><td colspan=2><input type=button name=sSubmit value='View Report'  onClick="funcReportClicked('report');">	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
		<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
			<tr><td>
			<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
			<tr><td>
				<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=2 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR><BR></td></tr>
	
	<tr><td colspan=2 class=header>Run Date / Time: <?php echo $sRunDateAndTime; ?></td></tr>
	
	<tr><td width=120 class=header>OfferId</td><td class=header>Display Count</td>		
		
	</tr>
	
		<?php echo $sReportContent;?>
			<tr><td colspan=4 align=left><hr color=#000000></td></tr>
	
						
	<tr><td colspan=2 class=header><BR>Notes -</td></tr>	
	<tr><Td colspan=2>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=2><BR><BR></td></tr>
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



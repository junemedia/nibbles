<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Held Report";

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
	
	$sToday = $iCurrYear."-".$iCurrMonth."-".$iCurrDay;	
		
	if (!$sViewReport) {
		
		$iYearFrom = date('Y');
		$iMonthFrom = date('m');
		$iDayFrom = date('d');
		
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
		$sOrderColumn = "dateAdded";
		$sDateAddedOrder = "ASC";
	}			
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "joinListId" :
			$sCurrOrder = $sJoinListIdOrder;
			$sDbMailIdOrder = ($sDbMailIdOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "emailSub" :
			$sCurrOrder = $sEmailSubOrder;
			$sEmailSubOrder = ($sEmailSubOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDateAddedOrder;
			$sDateAddedOrder = ($sDateAddedOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 100;
	}
	
	if (!($iPage)) {
		$iPage = 1;
	}
	
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {				
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {
			
		// prepare date array for the selected date range		
	
		$d=0;
		for ($i=0; $i<1000; $i++) {
			$sReportDate = DateAdd("d", $i, $iYearFrom."-".$iMonthFrom."-".$iDayFrom);			
								
			$aDateArray[$d] = $sReportDate;			
			$d++;
			if ($sReportDate == $sDateTo) {
				break;
			}
		}
		//}
		
		// get joinLists
		/*$i=0;
		$sJoinListQuery = "SELECT *
						   FROM	  joinLists
						   ORDER BY title";
		$rJoinListResult = dbQuery($sJoinListQuery);
		//echo $sJoinListQuery.dbError().dbNumRows($rJoinListResult);
		while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
			
			$iJoinListId = $oJoinListRow->id;
			// date query
			
			$aReportArray['joinListId'][$i] = $iJoinListId;
			$aReportArray['title'][$i] = $oJoinListRow->title;
			
			//$sReportContent .= "<tr><td>$oJoinListRow->title</td>";
			reset($aDateArray);*/
			for ($d=0; $d < count($aDateArray); $d++) {
									
				$sReportDate = $aDateArray[$d];
				
				//$sColHeader .= "<td>$sReportDate</td>";
				//$aReportArray['dayTotalCount'][$d] = '0';
				
				$sReportQuery = "SELECT joinLists.id, joinLists.title, count(joinEmailHeld.id) as heldCount
								 FROM   joinLists LEFT JOIN joinEmailHeld ON joinLists.id = joinEmailHeld.joinListId
								 WHERE  date_format(dateTimeAdded,'%Y-%m-%d') = '$sReportDate'
								 GROUP BY joinLists.id";

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				mysql_connect ($host, $user, $pass); 
				mysql_select_db ($dbase); 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
				mysql_select_db ($reportingDbase); 
				// end of track users' activity in nibbles		

				
				$rReportResult = dbQuery($sReportQuery);
				//echo "<BR>".$sReportQuery.dbError().dbNumRows($rReportResult);
				$i = 0;
				
				if ($rReportResult) {
					while ($oReportRow = dbFetchObject($rReportResult)) {

						//if ($d == 0) {
							$aReportArray['joinListId'][$i] = $oReportRow->id;
							$aReportArray['title'][$i] = $oReportRow->title;
						//}
						
						$aReportArray[$d][$i] = $oReportRow->heldCount;

						//$iDayTotalCount += $oReportRow->heldCount;
						//$aReportArray['dayTotalCount'][$d] += $oReportRow->heldCount;
						
						$i++;
					}
									
					
					dbFreeResult($rReportResult);
				}
				//echo "<BR>".$aReportArray['dayTotalCount'][$d];

			}

			
		for ($i=0; $i< count($aReportArray['joinListId']);$i++) {			
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}				
				
			$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['title'][$i]."</td>";

			reset($aDateArray);

			for ($d=0; $d<count($aDateArray);$d++) {
				
				$sTempDate = $aDateArray[$d];

				// prepare date if first row of report
				if ($i == 0) {
					$sDateHeader .= "<Td class=header nowrap align=right>$sTempDate</td>";
				}
				
				//zero fill
				if ($aReportArray[$d][$i] == '') {
					$aReportArray[$d][$i] = '0';
				}
								
				$sReportContent .= "<td align=right>".$aReportArray[$d][$i]."</td>";
				
				$aReportArray['dayTotalCount'][$d] += $aReportArray[$d][$i];
				
			}
			$sReportContent .= "</tr>";
			
		}		
		
		// get row of day totals
		reset($aDateArray);
		
		$iTotalReportCols = count($aDateArray) + 1;
		
		
		$sReportTotalRow = "<tr><td colspan=$iTotalReportCols><HR color=#000000></td></tr>
							<tr><td class=header>Total Count</td>";
		
		for ($d=0; $d<count($aDateArray);$d++) {
			$sReportTotalRow .= "<td align=right class=header>".$aReportArray['dayTotalCount'][$d]."</td>";
		}

		$sReportContent .= $sReportTotalRow."</tr><tr><td colspan=$iTotalReportCols><HR color=#000000></td></tr>";			
		
		$sReportHeader .= "<tr><td class=header>Join List</td>$sDateHeader								
							</tr>";
	}
	}

	if ($sShowQueries == 'Y') {
		$sQueries = "<b>Report Query:</b><BR>".$sReportQuery;		
		$sShowQueriesChecked = "checked";
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
	
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
	</td>
	<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td>
</tr>
</table>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=<?php echo $iTotalReportCols;?> class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=<?php echo $iTotalReportCols;?> class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<?php echo $sReportHeader;?>

<?php echo $sReportContent;?>

	<tr><td colspan=<?php echo $iTotalReportCols;?> class=header><BR>Notes -</td></tR>
	<Tr><td colspan=<?php echo $iTotalReportCols;?>>Report shows datewise hold count per join list.</td></tR>
	<tr><td colspan=<?php echo $iTotalReportCols;?>>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=<?php echo $iTotalReportCols;?>><BR><BR></td></tr>
	<tr><td colspan=<?php echo $iTotalReportCols;?>><?php echo $sQueries;?></td></tr>
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
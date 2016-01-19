<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "E1 Reporting";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	//partner name, lead volume, total leads avg revenue per lead, total revenue
	//filter y date range or rep
// default to date range of yesterday and all reps
		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
$sToday = date('m')."-".date('d')."-".date('Y');


if (!($iYearFrom)) {
	
	/*
	$iYearFrom = substr( $sYesterday, 0, 4);
	$iMonthFrom = substr( $sYesterday, 5, 2);
	$iDayFrom = substr( $sYesterday, 8, 2);
		
	$iYearTo = substr( $sYesterday, 0, 4);
	$iMonthTo = substr( $sYesterday, 5, 2);
	$iDayTo = substr( $sYesterday, 8, 2);
	*/
	
	
	$iYearFrom = $iCurrYear;
	$iMonthFrom = $iCurrMonth;
	$iDayFrom = $iCurrDay;
		
	$iYearTo = $iYearFrom;
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
	
}	

if ($sViewReport ) {
	
	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) > 0 || $iYearTo=='') {
			$iYearTo = $iYearFrom;
			$iMonthTo = $iMonthFrom;
			$iDayTo = $iDayFrom;
		}

		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) > 0 || $iYearFrom=='') {
			$iYearFrom = $iCurrYear;
			$iMonthFrom = $iCurrMonth;
			$iDayFrom = $iCurrDay;
		}

	
}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
				
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "e1Page";
		$sE1PageOrder = "DESC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");			
			break;
			case "totalAttempts" :
			$sCurrOrder = $sTotalAttemptsOrder;
			$sTotalAttemptsOrder = ($sTotalAttemptsOrder != "DESC" ? "DESC" : "ASC");			
			break;
			case "totalRejects" :
			$sCurrOrder = $sTotalRejectsOrder;
			$sTotalRejectsOrder = ($sTotalRejectsOrder != "DESC" ? "DESC" : "ASC");
			break;		
			case "totalSubs" :
			$sCurrOrder = $sTotalSubsOrder;
			$sTotalSubsOrder = ($sTotalSubsOrder != "DESC" ? "DESC" : "ASC");
			break;	
			case "totalConfirms" :
			$sCurrOrder = $sTotalConfirmstOrder;
			$sTotalConfirmstOrder = ($sTotalConfirmstOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sE1PageOrder;
			$sE1PageOrder = ($sE1PageOrder != "DESC" ? "DESC" : "ASC");
		}
	}
			
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&sSourceCode=$sSourceCode&iExactMatch=$iExactMatch";
	
	if ( checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
			
		$sRepQuery = "SELECT sourceCode, sum(attempts) as totalAttempts, sum(rejects) as totalRejects,
							 sum(subs) as totalSubs, sum(confirms) as totalConfirms,
							 concat(otPages.pageName,'_e1') as e1Page
					  FROM   e1TrackingSum, otPages
					  WHERE  e1TrackingSum.pageId = otPages.id
					  AND	 submitDate BETWEEN '$sDateFrom' AND '$sDateTo'";
		
		if ($sSourceCode != '') {
					if ($iExactMatch) {
						$sRepQuery .= " AND e1TrackingSum.sourceCode ='$sSourceCode' ";
					} else {
						$sRepQuery .= " AND e1TrackingSum.sourceCode LIKE '$sSourceCode%' ";
					}
				}
				
		$sRepQuery .= " GROUP BY e1TrackingSum.pageId, sourceCode";
		
		$sRepQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
		
		$rRepResult = dbQuery($sRepQuery);
		echo dbError();
		while ($oRepRow = dbFetchObject($rRepResult)) {

			if ($sBgColorClass == "ODD") {
				$sBgColorClass = "EVEN_WHITE";
			} else {
				$sBgColorClass = "ODD";
			}
		
			$sReportContent .= "<tr class=$sBgColorClass><td>$oRepRow->e1Page</td>
									<td>$oRepRow->sourceCode</td>
									<td align=right>$oRepRow->totalAttempts</td>
									<td align=right>$oRepRow->totalRejects</td>
									<td align=right>$oRepRow->totalSubs</td>
									<td align=right>$oRepRow->totalConfirms</td></tr>";
			
			$iGrandTotalAttempts += $oRepRow->totalAttempts;
			$iGrandTotalRejects += $oRepRow->totalRejects;
			$iGrandTotalSubs += $oRepRow->totalSubs;
			$iGrandTotalConfirms += $oRepRow->totalConfirms;
			
		}
		
		$sReportContent .= "<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
								<tr><td colspan=2 class=header>Total</td>
								<td align=right class=header>$iGrandTotalAttempts</td>
								<td align=right class=header>$iGrandTotalRejects</td>
								<td align=right class=header>$iGrandTotalSubs</td>
								<td align=right class=header>$iGrandTotalConfirms</td></tr>";
			

	} else {
		$sMessage = "Please Select Valid Dates...";
	}
	

	include("../../includes/adminHeader.php");	
	
	if ($sShowQueries == 'Y') {
			
			$sShowQueriesChecked = "checked";

			$sQueries .= "<tr><td colspan=6><b>Queries Used To Prepare This Report:</b><BR><BR>";
			$sQueries .= "<b>Report Query:</b><BR>".$sRepQuery;			
			$sQueries .= "</td></tr><tr><td colspan=6><BR><BR></td></tr>";
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
	
	
	if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}
	
	 // Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From &nbsp; </td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To &nbsp; &nbsp; &nbsp; <select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	<input type='checkbox' name='iExactMatch' value='1' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>
	<tr><td colspan=2><input type=submit name=sViewReport value='View Report'>	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td>
		<td><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=6 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td><a href='<?php echo $sSortLink;?>&sOrderColumn=e1Page&sE1PageOrder=<?php echo $sE1PageOrder;?>' class=header>e1 Page</a></td>		
		<td><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>' class=header>SourceCode</a></td>
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalAttempts&sTotalAttemptsOrder=<?php echo $sTotalAttemptsOrder;?>' class=header>Attempts</a></td>				
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalRejects&sTotalRejectsOrder=<?php echo $sTotalRejectsOrder;?>' class=header>Rejects</a></td>		
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalSubs&sTotalSubsOrder=<?php echo $sTotalSubsOrder;?>' class=header>Subs</a></td>		
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalConfirms&sTotalConfirmsOrder=<?php echo $sTotalConfirmstOrder;?>' class=header>Confirms</a></td>		
	</tr>
	
	<?php echo $sReportContent;?>

	<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=6 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=6><BR>Attempts = Total submit attempts from e1 page.
					  <BR>Rejects = Total number of submits to e1 page that were rejected due to failed front end bounds checks.
					  <BR>Subs = Total subscriptions from e1 page.
					  <BR>Confirms = Total subscriptions from e1 page which confirmed.
					  <BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=6><BR><BR></td></tr>
		<?php echo $sQueries;?>
		</td></tr></table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>
</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>
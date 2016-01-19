<?php

//total leads posted, total accepted, total rejected, by source code and date - need report
// Report will be API Statistics



include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$iScriptStartTime = getMicroTime();

session_start();

$sPageTitle = "API Statistics Reporting";
	
if (hasAccessRight($iMenuId) || isAdmin()) {
		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	// set curr date values to be selected by default
	
	if (!($sViewReport)) {
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
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {
		// specify default order column
		if (!($sOrderColumn)) {
			$sOrderColumn = "sourceCode";
			$sSourceCodeOrder = "ASC";
		}
		
		// specify current order (ASC or DESC) and reverse the order in link of that column's header
		if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
				case "leadsPosted" :
				$sCurrOrder = $sLeadsPostedOrder;
				$sLeadsPostedOrder = ($sLeadsPostedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "leadsAccepted" :
				$sCurrOrder = $sLeadsAcceptedOrder;
				$sLeadsAcceptedOrder = ($sLeadsAcceptedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "leadsRejected" :
				$sCurrOrder = $sLeadsRejectedOrder;
				$sLeadsRejectedOrder = ($sLeadsRejectedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "datePosted" :
				$sCurrOrder = $sDatePostedOrder;
				$sDatePostedOrder = ($sDatePostedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "leadData" :
				$sCurrOrder = $sLeadDataOrder;
				$sLeadDataOrder = ($sLeadDataOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "reason" :
				$sCurrOrder = $sReasonOrder;
				$sReasonOrder = ($sReasonOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$sCurrOrder = $sSourceCodeOrder;
				$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
			}
		}

		/*// Specify Page no. settings
		if (!($iRecPerPage)) {
			$iRecPerPage = 10;
		}*/
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&sSourceCode=$sSourceCode";
		$sSortLink .= "&sFilter=$sFilter&sViewReport=View Report";
		
		
		if ($sSourceCode != '' && $sFilter == 'exactMatchWithError') {
			
			$sSelectQuery = "SELECT *
							 FROM   apiRejectionLog
							 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'
							 AND	sourceCode = '$sSourceCode'
			 				 ORDER BY $sOrderColumn $sCurrOrder";
		
		} else {
			
		$sSelectQuery = "SELECT sourceCode, sum(leadsPosted) AS leadsPosted, 
								sum(leadsAccepted) AS leadsAccepted, 
								sum(leadsRejected) AS leadsRejected
						 FROM   apiStats
						 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'";
		
		if ($sSourceCode != '') {
			if ($sFilter == 'exactMatch') {
				$sSelectQuery .= " AND sourceCode = '$sSourceCode'";
			} else if ($sFilter == 'startsWith') {
				$sSelectQuery .= " AND sourceCode LIKE '$sSourceCode%'";
			}
		}
		
		$sSelectQuery .= " GROUP BY sourceCode";
		$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
		
		if (!($sSourceCode != '' && $sFilter == 'exactMatch')) {
			$sSelectQuery .= ", datePosted";
		}
							
		}		 
		
		$rResult = dbQuery($sSelectQuery);
		echo  dbError();
		$iTotalLeadsPosted = 0;
		$iTotalLeadsAccepted = 0;
		$iTotalLeadsRejected = 0;
		
		if ($rResult) {
			
			if (dbNumRows($rResult) > 0) {
				
				
				while ($oRow = dbFetchObject($rResult)) {
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}		
					
					$iTotalLeadsPosted += $oRow->leadsPosted;
					$iTotalLeadsAccepted += $oRow->leadsAccepted;
					$iTotalLeadsRejected += $oRow->leadsRejected;
					
					// Prepare Next/Prev/First/Last links
					/*if ($iTotalPages > $iPage) {
						$iNextPage = $iPage + 1;
						$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrde' class=header>Last</a>";
					}
					if ($iPage != 1) {
						$iPrevPage = $iPage - 1;
						$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder' class=header>First</a>";
					}
					*/
					if ($sSourceCode != '' && $sFilter == 'exactMatchWithError') {
						$sReportData .= "<tr class=$sBgcolorClass><td nowrap>$oRow->sourceCode</td>
										  <td align=right nowrap>$oRow->datePosted</td>
										  <td >".nl2br($oRow->leadData)."</td>
										  <td >".nl2br($oRow->reason)."</td></tr>";
					} else {
					$sReportData .= "<tr class=$sBgcolorClass><td>$oRow->sourceCode</td>
										  <td align=right>$oRow->leadsPosted</td>
										  <td align=right>$oRow->leadsAccepted</td>
										  <td align=right>$oRow->leadsRejected</td></tr>";
					}
				}
				
				
			if ($sSourceCode != '' && $sFilter == 'exactMatchWithError') {
				$sReportHeader = "<tr><th align=left><a href='$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder'>Source Code</a></th>
									<th align=left><a href='$sSortLink&sOrderColumn=datePosted&sDatePostedOrder=$sDatePostedOrder'>Date Posted</a></th>
									<th align=left><a href='$sSortLink&sOrderColumn=leadData&sLeadDataOrder=$sLeadDataOrder'>Lead Data</a></th>
									<th align=left><a href='$sSortLink&sOrderColumn=reason&sReasonOrder=$sReasonOrder'>Reason</a></th>
								</tr>";
				$sReportData = $sReportHeader . $sReportData;
			} else {
				$sReportHeader = "<tr><th align=left><a href='$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder'>Source Code</a></th>
									<th align=right><a href='$sSortLink&sOrderColumn=leadsPosted&sLeadsPostedOrder=$sLeadsPostedOrder'>Leads Posted</a></th>
									<th align=right><a href='$sSortLink&sOrderColumn=leadsAccepted&sLeadsAcceptedOrder=$sLeadsAcceptedOrder'>Leads Accepted</a></th>
									<th align=right><a href='$sSortLink&sOrderColumn=leadsRejected&sLeadsRejectedOrder=$sLeadsRejectedOrder'>Leads Rejected</a></th>
								</tr>";
				$sReportData .= "<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
									<tr><td class=header>Total Counts</td>
										  <td align=right class=header>$iTotalLeadsPosted</td>
										  <td align=right class=header>$iTotalLeadsAccepted</td>
										  <td align=right class=header>$iTotalLeadsRejected</td></tr>
									<tr><td colspan=4 align=left><hr color=#000000></td></tr>";
				
				$sReportData = $sReportHeader . $sReportData;
				
			} 
				
			} else {
				$sMessage = "No Records Exist...";
			}
		}
	}
	
	if ($sFilter == 'startsWith') {
		$sStartsWithChecked = "checked";
	} else if ($sFilter == 'exactMatch') {
		$sExactMatchChecked = "checked";
	} else if ($sFilter == 'exactMatchWithError') {
		$sExactMatchWithErrorChecked = "checked";
	}
	
	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
	include("../../includes/adminHeader.php");
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
				document.form1.submit();
}

</script>				
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>
<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $sCampaignsLink;?></td></tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match
		&nbsp; <input type='radio' name='sFilter' value='exactMatchWithError' <?php echo $sExactMatchWithErrorChecked;?>> Exact Match With Error List
		</td>
		</tr>	
<tr>
<td colspan=2><br><input type=submit name=sViewReport value='View Report'></td></tr>

			</table>
	
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
<?php echo $sReportData;?>

<tr><td colspan=4 class=header><BR>Notes -</td></tr>
	<tr><td colspan=4>- Updated in real time.
		<BR>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
	
</table></td></tr></table></td></tr></table></td></tr></table>
</form>			

<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}

?>
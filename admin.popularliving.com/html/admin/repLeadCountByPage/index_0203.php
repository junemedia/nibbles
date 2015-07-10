<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Leads Count By OT Page Report";

session_start();

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
	
	
$sViewReport = stripslashes($sViewReport);

if (! $sViewReport) {
	$sViewReport = "Today's Report";
}


if ($sViewReport != "Today's Report") {
	
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
		
} else  {
	$iYearFrom = date('Y');
	$iMonthFrom = date('m');
	$iDayFrom = date('d');
		
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
	$iYearTo = $iYearFrom;
	
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
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "pageName";
		$sPageNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "leadsCount" :
			$sCurrOrder = $sLeadsCountOrder;
			$sLeadsCountOrder = ($sLeadsCountOrder != "DESC" ? "DESC" : "ASC");			
			break;	
			case "leadsRevenue" :
			$sCurrOrder = $sLeadsRevenueOrder;
			$sLeadsRevenueOrder = ($sLeadsRevenueOrder != "DESC" ? "DESC" : "ASC");			
			break;			
			default:
			$sCurrOrder = $sPageNameOrder;
			$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";	
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sOfferCode=$sOfferCode&iPostalVerified=$iPostalVerified&sDateFrom=$sDateFrom&sDateTo=$sDateTo&sViewReport=$sViewReport";

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {

	
		if ($sViewReport != 'History Report') {
			
			$iPostalVerified = '';
			
			$sReportQuery = "SELECT count(otData.id) AS leadsCount, pageName, sum(1 * revPerLead) as leadsRevenue
							 FROM   otPages LEFT JOIN otData, userData, offers
							 ON		otData.pageId = otPages.id							
							 WHERE  otData.offerCode = offers.offerCode
							 AND    otData.email = userData.email
							 AND    address NOT LIKE \"3401 dundee%\"
							 AND	date_format(otData.dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
			
			if ($sOfferCode) {
				$sReportQuery .= " AND offers.offerCode = '$sOfferCode' ";
			}
			
			$sReportQuery .= " GROUP BY pageId
						 	 ORDER BY $sOrderColumn $sCurrOrder";
			
			
		} else {
			
			if ($iPostalVerified) {
				$sReportQuery = "SELECT count(otDataHistory.id) AS leadsCount, pageName, sum(1 * revPerLead) as leadsRevenue
								 FROM   otPages LEFT JOIN otDataHistory, userDataHistory
								 ON		otDataHistory.pageId = otPages.id, offers
								 WHERE  otDataHistory.email = userDataHistory.email
								 AND	offers.offerCode = otDataHistory.offerCode								 
								 AND    userDataHistory.postalVerified = 'V'
								 AND	otDataHistory.verified != 'I'
								 AND    date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'	";
			if ($sOfferCode) {
				$sReportQuery .= " AND offers.offerCode = '$sOfferCode' ";
			}
			
			$sReportQuery .= "   GROUP BY pageId
								 ORDER BY $sOrderColumn $sCurrOrder";
			} else {
				$sReportQuery = "SELECT count(otDataHistory.id) AS leadsCount, pageName, sum(1 * revPerLead) as leadsRevenue
								 FROM   otPages LEFT JOIN otDataHistory, userDataHistory
								 ON		otDataHistory.pageId = otPages.id, offers
								 WHERE  otDataHistory.email = userDataHistory.email
								 AND	offers.offerCode = otDataHistory.offerCode
								 AND	date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'	";
				
				if ($sOfferCode) {
					$sReportQuery .= " AND offers.offerCode = '$sOfferCode' ";
				}
				
				$sReportQuery .= " GROUP BY pageId
								   ORDER BY $sOrderColumn $sCurrOrder";
			}		
		}
		
		
		$rReportResult = dbQuery($sReportQuery);
		echo dbError();
		
		while ($oReportRow = dbFetchObject($rReportResult)) {
			$fLeadsRevenue = $oReportRow->leadsRevenue;
			$fLeadsRevenue = sprintf("%8.2f",round($fLeadsRevenue, 2));
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}
		
			
			$sReportContent .= "<tr class=$sBgcolorClass><td>$oReportRow->pageName</td>
									<td align=right>$oReportRow->leadsCount</td>
									<td align=right>$oReportRow->leadsRevenue</td>
								</tr>";
			$iGrandTotalLeads += $oReportRow->leadsCount;
			$fGrandTotalRevenue += $oReportRow->leadsRevenue;
		}
		
				
		$fGrandTotalRevenue = sprintf("%8.2f",round($fGrandTotalRevenue, 2));
		$sReportContent .= "<tr><td colspan=3><HR color=#000000></td></tr>
							<tr><td class=header>Total Leads</td>
								<td class=header align=right>$iGrandTotalLeads</td>
								<td class=header align=right>$fGrandTotalRevenue</td>
							</tr>";
	}
	
	// prepare offers list
	
	$sOffersQuery = "SELECT *
				 	 FROM   offers					
				 	 ORDER BY offers.offerCode";
	$rOffersResult = dbQuery($sOffersQuery);
	
	$sOffersOptions = "<option value=''>All";
	while ($oOffersRow = dbFetchObject($rOffersResult)) {		
		$sTempOfferCode = $oOffersRow->offerCode;
			
		if ($sTempOfferCode == $sOfferCode ) {
			$sSelected = "selected";							
		} else {
			$sSelected = '';
		}
		
		$sOffersOptions .= "<option value='$sTempOfferCode' $sSelected>$sTempOfferCode";
	}		
	
	if ($iPostalVerified) {
		$sPostalVerfiedChecked = "checked";
	}
	
	if ($sShowQueries == 'Y') {
		
		$sQueries .= "<tr><td colspan=3><b>Queries Used To Prepare This Report:</b>";		
		$sQueries .= "<BR><BR><b>Report Query To Get Report Data:</b><BR>".$sReportQuery;
		$sQueries .= "</td></tr><tr><td colspan=2><BR><BR></td></tr>";
			
		$sShowQueriesChecked = "checked";
	}
		
	
	include("../../includes/adminHeader.php");
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	<tr><td>Select Offer</td>
		<td colspan=3><select name='sOfferCode'>
		<?php echo $sOffersOptions;?>
	</select></td></tr>	
	<tr><Td></td><td colspan=3><input type=checkbox name=iPostalVerified value='1' <?php echo $sPostalVerfiedChecked;?>> Postal Verified</td></tr>
	
<tr><td colspan=2><input type=submit name=sViewReport value='History Report'>  &nbsp; &nbsp; 
	<input type=submit name=sViewReport value="Today's Report">  &nbsp; &nbsp; 
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
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=3 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=3 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=pageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=leadsCount&sLeadsCountOrder=<?php echo $sLeadsCountOrder;?>" class=header>Leads Count</a></td>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=leadsRevenue&sLeadsRevenueOrder=<?php echo $sLeadsRevenueOrder;?>" class=header>Leads Revenue</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=3 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=3 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=3><BR>Today's Report omits any leads having address starting with '3401 Dundee' considering those as test leads.
					  <BR>Test leads are deleted after midnight and not included in history report.
					<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=3><BR><BR></td></tr>
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
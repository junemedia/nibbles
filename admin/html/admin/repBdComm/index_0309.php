<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();
$iScriptStartTime = getMicroTime();

$sPageTitle = "BD Commission Report";

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
		
		
		// previous day of current month's first day is the last day of previous month
		$sPrevMonthLastDay = DateAdd("d", -1, $iCurrYear."-".$iCurrMonth."-"."01");
		
		$iYearFrom = substr( $sPrevMonthLastDay, 0, 4);
		$iMonthFrom = substr( $sPrevMonthLastDay, 5, 2);
		$iDayFrom = "01";
		
		$iYearTo = $iYearFrom;
		$iMonthTo = $iMonthFrom;
		$iDayTo =  substr( $sPrevMonthLastDay, 8, 2);
		
		
	}
	
	if ($sViewReport ) {
		
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		}
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = substr( $sYesterday, 8, 2);
		}
		
	}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";
	
	if ($iRepDesignated == '') {
		
		if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'stuart' || $_SERVER['PHP_AUTH_USER'] == 'phil') {
			$iRepDesignated = 'all';
		} else if ($_SERVER['PHP_AUTH_USER'] !='') {
			$sUserQuery = "SELECT nbUsers.*
				  			FROM   nbUsers
				  			WHERE  userName = '".$_SERVER['PHP_AUTH_USER']."' ";
			
			$rUserResult = dbQuery($sUserQuery);
			while ($oUserRow = dbFetchObject($rUserResult)) {
				$iRepDesignated = $oUserRow->id;
			}
		}
	}
	
	$sRepQuery = "SELECT nbUsers.id, firstName, userName
				  FROM   nbUsers				  
				  ORDER BY firstName";
	
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	
	$sSelected = "";
	
	if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'stuart' || $_SERVER['PHP_AUTH_USER'] == 'phil') {
		if ($iRepDesignated == 'all') {
			$sSelected = "selected";
		}
		$sRepOptions = "<option value='all' $sSelected>All";
	}
	
	while ($oRepRow = dbFetchObject($rRepResult)) {
		
		if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'stuart' || $_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == $oRepRow->userName) {
			
			if ($oRepRow->id == $iRepDesignated) {
				$sSelected = "selected";
			} else {
				$sSelected = '';
			}
			
			//echo "<BR>$iRepDesignated"." - ".$_SERVER['PHP_AUTH_USER']." - ".$oRepRow->userName;
			
			$sRepOptions .= "<option value=$oRepRow->id $sSelected>$oRepRow->firstName";
			
		}
	}
	
	$sTempRepDesignated = "'".$iRepDesignated."'";
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "sourceCode";
		$sSourceCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "companyName" :
			$sCurrOrder = $sCompanyNameOrder;
			$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "leadsCount" :
			$sCurrOrder = $sLeadsCountOrder;
			$sLeadsCountOrder = ($sLeadsCountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "commission":
			$sCurrOrder = $sCommissionOrder;
			$sCommissionOrder = ($sCommissionOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iRepDesignated=$iRepDesignated&fCommissionRate=$fCommissionRate&sViewReport=View Report";
	
	if ($sViewReport) {
		
		
		if (trim($fCommissionRate) != '') {
			if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
				if ($sAllowReport == 'N') {
					$sMessage = "Server Load Is High. Please check back soon...";
				} else {
				$sReportQuery = "SELECT companyName, otDataHistory.sourceCode ,
								count( otDataHistory.email )   AS leadsCount,  
								count( otDataHistory.email ) * $fCommissionRate as commission,
								substring(otDataHistory.sourceCode,1,14) as tempSourceCode
						 FROM 	otDataHistory, offers, campaigns, partnerCompanies
						 WHERE  otDataHistory.offerCode = offers.offerCode
						 AND	substring(otDataHistory.sourceCode,1,14) = campaigns.sourceCode
						 AND	campaigns.partnerId = partnerCompanies.id						 
						 AND	dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
						 AND 	sendStatus = 'S' 
						 AND 	offers.isNonRevenue != '1'
						 AND	otDataHistory.sourceCode NOT  LIKE 'MY%'
						 AND 	otDataHistory.sourceCode NOT  LIKE 'AMP%'
						 AND 	otDataHistory.sourceCode NOT  LIKE 'b2b%'";
				
				if ($iRepDesignated != "all") {
					$sReportQuery .= " AND	FIND_IN_SET( \"$sTempRepDesignated\", partnerCompanies.repDesignated) > 0";
				}
				
				if ($sSourceCode != '') {
					if ($iExactMatch) {
						$sReportQuery .= " AND otDataHistory.sourceCode ='$sSourceCode' ";
					} else {
						$sReportQuery .= " AND otDataHistory.sourceCode LIKE '$sSourceCode%' ";
					}
				}
				
				$sReportQuery .= " GROUP BY tempSourceCode ORDER BY $sOrderColumn $sCurrOrder ";
				$rRepResult = dbQuery($sReportQuery);				
				echo dbError();
				$iTotalCount = 0;
				while ($oRepRow = dbFetchObject($rRepResult)) {
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					$sReportContent .= "<tr class=$sBgcolorClass><td>$oRepRow->companyName</td><td>$oRepRow->tempSourceCode</td>
									<td align=right>$oRepRow->leadsCount</td><td align=right>$oRepRow->commission</td></tr>";
					$sExportData .= "$oRepRow->companyName\t$oRepRow->tempSourceCode\t$oRepRow->leadsCount\t$oRepRow->commission\t\n";
					$iTotalCount += $oRepRow->leadsCount;
					$fTotalCommission += $oRepRow->commission;
				}
				
				$sExportData = "Partner Name\tSource code\tLead Counts\tCommission\n".$sExportData;
				
				$sExportData .= "\nTotal \t\t$iTotalCount\t$fTotalCommission\n";
				$sExportData .= "\nReport From $sDateFrom to $sDateTo\nCommission Rate: $fCommissionRate\nRun Date / Time: $sRunDateAndTime";
				
			}
			} else {
				$sMessage = "Please Select Valid Dates...";
			}
		} else {
			$sMessage = "Please Enter Commission Rate...";
		}
	}
	
	if ($sShowQueries == 'Y') {
		
		$sShowQueriesChecked = "checked";
		
		$sQueries .= "<tr><td colspan=3><b>Queries Used To Prepare This Report:</b>";
		$sQueries .= "<BR><BR><b>Query To Get Report Data:</b><BR>".$sReportQuery;
		$sQueries .= "</td></tr><tr><td colspan=3><BR><BR></td></tr>";
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
	
	
	$sExportChecked = '';
	
	if ($sExport) {
		$sExportChecked = "checked";
	}
	
	if ($sExport) {
			
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=bdCommission.xls");
			header("Content-Description: Excel output");
			echo $sExportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
	}
		
	
	if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}
	
	include("../../includes/adminHeader.php");	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
		// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
		
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
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	<input type='checkbox' name='iExactMatch' value='1' <?php echo $sExactMatchChecked;?>> Exact Match
		
	<tr><td>Rep.</td>
		<td colspan=3><select name=iRepDesignated><?php echo $sRepOptions;?></select></td>
	</tr>
	<tr><td>Commission Rate</td>
		<td colspan=3><input type=test name=fCommissionRate value='<?php echo $fCommissionRate;?>'></td>
	</tr>
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">	
	&nbsp; &nbsp; <input type=checkbox name=sExport value='Export To Excel' <?php echo $sExportChecked;?>> Export To Excel</td>
		<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=70% align=center border=0>
	<tr><td colspan=4 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td width=45%><a href='<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>' class=header>Partner Name</a></td>
		<td width=45%><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>' class=header>Source Code</a></td>
		<td width=30% align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=leadsCount&sLeadsCountOrder=<?php echo $sLeadsCountOrder;?>' class=header>Lead Counts</a></td>				
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=commission&sCommissionOrder=<?php echo $sCommissionOrder;?>' class=header>Commission</a></td>
	</tr>
	
	<?php echo $sReportContent;?>

	<tr><td colspan=4 align=left><hr color=#000000></td></tr>
	<tr><td class=header colspan=2>Total Leads Count</td><td align=right class=header><?php echo $iTotalCount;?></td>
		<td align=right class=header><?php echo $fTotalCommission;?></td></tr>
	<tr><td colspan=4 align=left><hr color=#000000></td></tr>
	<tr><td colspan=4 ><BR><b>Notes -</b>
						<BR><BR> Count reflects leads which are successfully delivered.
						<BR>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	</td></tr>
	<tr><td colspan=4></td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
		<?php echo $sQueries;?>
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
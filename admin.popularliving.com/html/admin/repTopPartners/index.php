<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Top BD Partners Report";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


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
	
	$iYearFrom = substr( $sYesterday, 0, 4);
	$iMonthFrom = substr( $sYesterday, 5, 2);
	$iDayFrom = substr( $sYesterday, 8, 2);
		
	$iYearTo = substr( $sYesterday, 0, 4);
	$iMonthTo = substr( $sYesterday, 5, 2);
	$iDayTo = substr( $sYesterday, 8, 2);
	
	
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

} else {
	// exclude non revenue offers by default
	$iExcludeNonRevenue = '1';
}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	//if ($sGetReport || $sExport) {
			
			
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "totalLeads";
		$sTotalLeadsOrder = "DESC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "companyName" :
			$sCurrOrder = $sCompanyNameOrder;
			$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");			
			break;
			case "totalRevenue" :
			$sCurrOrder = $sTotalRevenueOrder;
			$sTotalRevenueOrder = ($sTotalRevenueOrder != "DESC" ? "DESC" : "ASC");
			break;		
			case "avgRevPerLead" :
			$sCurrOrder = $sAvgRevPerLeadOrder;
			$sAvgRevPerLeadOrder = ($sAvgRevPerLeadOrder != "DESC" ? "DESC" : "ASC");
			break;	
			case "code" :
			$sCurrOrder = $sCodeOrder;
			$sCodeOrder = ($sCodeOrder != "DESC" ? "DESC" : "ASC");
			break;		
			case "percentOt" :
			$sCurrOrder = $sPercentOtOrder;
			$sPercentOtOrder = ($sPercentOtOrder != "DESC" ? "DESC" : "ASC");
			break;		
			default:
			$sCurrOrder = $sTotalLeadsOrder;
			$sTotalLeadsOrder = ($sTotalLeadsOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	
	if ($sCurrOrder == 'DESC') {
			$sCurrOrder = SORT_DESC;
		} else {
			$sCurrOrder = SORT_ASC;
		}
		
	$sSortLink = $PHP_SELF."?sViewReport=$sViewReport&iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iRepDesignated=$iRepDesignated&sPostalVerified=$sPostalVerified&iExcludeNonRevenue=$iExcludeNonRevenue";
	if ( $sViewReport ) {
	if ( checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
	//if ($sViewReport) {
		
	$sDateTimeFrom = $sDateFrom." 00:00:00";
	$sDateTimeTo = $sDateTo." 23:59:59";
	
		if ($iRepDesignated != '') {
			$sPartnerQuery = "SELECT *
						  FROM   partnerCompanies 
						  WHERE  repDesignated LIKE \"%'".$iRepDesignated."'%\"";
				
			$rPartnerResult = dbQuery($sPartnerQuery);
			//echo $sPartnerQuery. mysql_error()	;
			while ($oPartnerRow = dbFetchObject($rPartnerResult)) {
				$sPartnerCodesList .= "'".$oPartnerRow->code."',";
			}
			if ($sPartnerCodesList !='') {
				$sPartnerCodesList = substr($sPartnerCodesList, 0, strlen($sPartnerCodesList)-1);
			}
		}
		//echo $sPartnerCodesList;
		
			$sReportQuery = "SELECT count(otDataHistory.email) AS totalLeads, 
									sum(otDataHistory.revPerLead) AS totalRevenue, 
									sum(otDataHistory.revPerLead)/count(otDataHistory.email) AS avgRevPerLead,
									substring(sourceCode,1,3) AS partnerCode, 
									companyName
						 	FROM   otDataHistory LEFT JOIN partnerCompanies ON partnerCompanies.code = substring(sourceCode,1,3),
								   offers	
						 	WHERE  otDataHistory.offerCode = offers.offerCode 
							AND otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";

			if ($iPostalVerified) {
					$sReportQuery .= " AND    otDataHistory.postalVerified = 'V'
									   AND	  otDataHistory.processStatus = 'P'";
			}
 	
			if ($iExcludeNonRevenue) {
					$sReportQuery .= " AND    offers.isNonRevenue != '1'";
			}
			
			if ($iExcludeNonRevenue) {
				
			}
			if ($sPartnerCodesList != '') {
				$sReportQuery .= " AND substring(sourceCode,1,3) IN (".$sPartnerCodesList . ") ";
			}
			
			$sReportQuery .= " GROUP BY partnerCode 	";						   
							   //ORDER BY $sOrderColumn $sCurrOrder";
					

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
	
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")"; 
			$rResult = dbQuery($sAddQuery); 
			echo  dbError(); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		


							   
							   
		$rReportResult = dbQuery($sReportQuery);
		
		echo  dbError();
		//echo mysql_num_rows($rReportResult);
		$i = 0;
		while ($oReportRow = dbFetchObject($rReportResult)) {
			
			$iTotalLeads = $oReportRow->totalLeads;
			$fTotalRevenue = $oReportRow->totalRevenue;
			$sPartnerName = $oReportRow->companyName;
			$sPartnerCode = $oReportRow->partnerCode;
			$fAvgRevPerLead = $oReportRow->avgRevPerLead;
			$iGrandTotalLeads += $iTotalLeads;
			$fGrandTotalRevenue += $fTotalRevenue;
			
			$fAvgRevPerLead = sprintf("%10.2f",round($fAvgRevPerLead, 2));
			
			$aReportData["partnerName"][$i] = $sPartnerName;
			$aReportData["partnerCode"][$i] = $sPartnerCode;
			$aReportData["totalLeads"][$i] = $iTotalLeads;
			$aReportData["totalRevenue"][$i] = $fTotalRevenue;
			$aReportData["avgRevPerLead"][$i] = $fAvgRevPerLead;
			$i++;

		}
		
		if ($iGrandTotalLeads != 0) {
			$fGrandTotalAvgRevPerLead = $fGrandTotalRevenue / $iGrandTotalLeads ;
		}
		$fGrandTotalAvgRevPerLead = sprintf("%10.2f",round($fGrandTotalAvgRevPerLead, 2));
			
		$fGrandTotalRevenue = sprintf("%12.2f",round($fGrandTotalRevenue, 2));
						
		
		for ($i = 0; $i < count($aReportData["partnerCode"]); $i++) {	

			if ($fGrandTotalRevenue) {
				$fPercentOtTotal = ($aReportData["totalRevenue"][$i] * 100 )/ $fGrandTotalRevenue;
			
				$fPercentOtTotal = sprintf("%12.2f",round($fPercentOtTotal, 2));
				
				$aReportData["percentOtTotal"][$i] = $fPercentOtTotal;
			}
			
			
		} 
		
		
		if (count ($aReportData['partnerName']) > 0 ) {
			
			switch ($sOrderColumn) {
					case "companyName" :					
					array_multisort($aReportData['partnerName'], $sCurrOrder, $aReportData['totalRevenue'],  $aReportData['partnerCode'], $aReportData['totalLeads'] ,$aReportData['avgRevPerLead'],  $aReportData['percentOtTotal']);
					break;
					case "totalRevenue" :					
					array_multisort($aReportData['totalRevenue'], $sCurrOrder, $aReportData['partnerName'],  $aReportData['partnerCode'], $aReportData['totalLeads'] ,$aReportData['avgRevPerLead'],  $aReportData['percentOtTotal']);					
					break;
					case "avgRevPerLead" :					
					array_multisort($aReportData['avgRevPerLead'], $sCurrOrder, $aReportData['partnerName'],  $aReportData['partnerCode'], $aReportData['totalLeads'] ,$aReportData['totalRevenue'],  $aReportData['percentOtTotal']);					
					break;
					case "code" :					
					array_multisort($aReportData['partnerCode'], $sCurrOrder, $aReportData['partnerName'],  $aReportData['totalRevenue'], $aReportData['totalLeads'] ,$aReportData['avgRevPerLead'],  $aReportData['percentOtTotal']);
					break;
					case "percentOt" :					
					array_multisort($aReportData['percentOtTotal'], $sCurrOrder, $aReportData['partnerName'],  $aReportData['totalRevenue'], $aReportData['totalLeads'] ,$aReportData['avgRevPerLead'],  $aReportData['partnerCode']);
					break;
					default:
					array_multisort($aReportData['totalLeads'], $sCurrOrder, $aReportData['partnerName'],  $aReportData['totalRevenue'], $aReportData['percentOtTotal'] ,$aReportData['avgRevPerLead'],  $aReportData['partnerCode']);
				}
		}

		for ($i = 0; $i < count($aReportData["partnerCode"]); $i++) {

			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}

			$sReportContent .= "<tr class=$sBgcolorClass><td>" . $aReportData["partnerName"][$i] . "</td>
									<td>" . $aReportData["partnerCode"][$i] . "</td>
									<td align=right>" .$aReportData["totalLeads"][$i] . "</td>
									<td align=right>" . $aReportData["totalRevenue"][$i] . "</td>
									<td align=right>" . $aReportData["avgRevPerLead"][$i] . "</td>
									<td align=right>" . $aReportData["percentOtTotal"][$i] . "</td>
									</tr>";
			
			$sExpReportContent .= $aReportData["partnerName"][$i] . "\t".
								  $aReportData["partnerCode"][$i] . "\t".
								  $aReportData["totalLeads"][$i] . "\t".
								  $aReportData["totalRevenue"][$i] . "\t".
								  $aReportData["avgRevPerLead"][$i] . "\t".
								  $aReportData["percentOtTotal"][$i] . "\n";
								  
		}
				
		$sReportContent .= "<tr><td colspan=6 align=left><hr color=#000000></td></tr>
								<tr><td colspan=2><b>Summary</b></td>
								<td align=right><b>$iGrandTotalLeads</b></td>
								<td align=right><b>$fGrandTotalRevenue</b></td>
								<td align=right><b>$fGrandTotalAvgRevPerLead</b></td>
								<td></td>
							</tr>";
		
		$sExpReportContent .= "\tSummary\t".$iGrandTotalLeads."\t".$fGrandTotalRevenue."\t".$fGrandTotalAvgRevPerLead."\n";
			

	} else {
		$sMessage = "Please Select Valid Dates...";
	}
	}
	

	include("../../includes/adminHeader.php");	
	
	if ($sShowQueries == 'Y') {
			
			$sShowQueriesChecked = "checked";

			$sQueries .= "<tr><td colspan=6><b>Queries Used To Prepare This Report:</b><BR><BR>";
			$sQueries .= "<b>Report Query 1 To Get Partner codes if displayed Rep is not for all Rep:</b><BR>".$sPartnerQuery;
			$sQueries .= "<BR><BR><b>Report Query 2 To Get Report Data:</b><BR>".$sReportQuery;
			$sQueries .= "</td></tr><tr><td colspan=2><BR><BR></td></tr>";
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
		
		
	$sRepQuery = "SELECT nbUsers.id, firstName, userName
				  FROM   nbUsers				  
				  ORDER BY userName";
	
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	
	$sSelected = "";
	if ($iRepDesignated =='') {
		$sSelected = "selected";
	} 
	$sRepOptions = "<option value='' $sSelected>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
			
		if ($oRepRow->id == $iRepDesignated) {
			$sSelected = "selected";		
		} else {
			$sSelected = '';
		}
		
		$sRepOptions .= "<option value=$oRepRow->id $sSelected>$oRepRow->userName";
	}
	
	
	$sPostalVerfiedChecked = "";
	if ($iPostalVerified) {
		$sPostalVerfiedChecked = "checked";
	}
	
	$sExcludeNonrevenueChecked = "";
	if ($iExcludeNonRevenue) {
		$sExcludeNonrevenueChecked = "checked";
	}
	
	
	if ($sExportExcel) {
		
		$sExpReportContent = "Partner Company\tPartner Code\tTotal Leads\tTotal Revenue\tAvg. Rev. Per Lead\t% OT Total"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "topPartners_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExpReportContent, strlen($sExpReportContent));
			fclose($rFpFile);

			echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	}
	
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	
	 // Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	
	
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
	<tr><td>Rep.</td>
		<td><select name=iRepDesignated><?php echo $sRepOptions;?></select></td>
	</tr>
	<tr><td></td>
		<td><input type=checkbox name=iPostalVerified value='1' <?php echo $sPostalVerfiedChecked;?>> Postal Verified</td>
	</tr>
	<tr><td></td>
		<td><input type=checkbox name=iExcludeNonRevenue value='1' <?php echo $sExcludeNonrevenueChecked;?>> Exclude Non-Revenue Offers</td>
	</tr>
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">	
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td>
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
	<tr><td><a href='<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>' class=header>Partner Company</a></td>
		<td><a href='<?php echo $sSortLink;?>&sOrderColumn=code&sCodeOrder=<?php echo $sCodeOrder;?>' class=header>Partner Code</a></td>
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalLeads&sTotalLeadsOrder=<?php echo $sUniqueUsersOrder;?>' class=header>Total Leads</a></td>				
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=totalRevenue&sTotalRevenueOrder=<?php echo $sTotalRevenueOrder;?>' class=header>Total Revenue</a></td>		
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=avgRevPerLead&sAvgRevPerLeadOrder=<?php echo $sAvgRevPerLeadOrder;?>' class=header>Avg. Rev. Per Lead</a></td>		
		<td align=right><a href='<?php echo $sSortLink;?>&sOrderColumn=percentOt&sPercentOtOrder=<?php echo $sPercentOtOrder;?>' class=header>% OT Total</a></td>		
		<td></td>
	</tr>
	
	<?php echo $sReportContent;?>

	<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=6 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=6><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads.
					   <BR>If postal verified is checked, report includes only postal verified and successfully deliverable leads.
					<BR>%OT Total shows % of the total revenue.
					<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=6><BR><BR></td></tr>
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
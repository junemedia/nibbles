<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();
$sPageTitle = "Onetime Quick Count Report";
session_start();
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

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
	
	if ($sExportExcel && $sExportEmails) {
		$sMessage = "Please check only one export option...";
	} else if ($sAllowReport == 'N') {
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
	
	
	if ($sExcludeNonRevenue) {
		$sExcludeNonRevenueFilter = " AND offers.isNonRevenue != '1' ";	
	}
	
	if( $sIncludeTestLeads == "Y" ) {
			$sIncludeTestLeadsCheckedClause = "";
		} else {
			$sIncludeTestLeadsCheckedClauseHistory = " AND otDataHistory.mode!='T' ";
			$sIncludeTestLeadsCheckedClause = " AND otData.mode!='T' ";
		}
		
	if ($sPostalVerified=="pvOnly") {
		$sPvOnlyClause= "AND otDataHistory.postalVerified='V' ";
	}
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		$sDateTimeFrom = $sDateFrom." 00:00:00";
		$sDateTimeTo = $sDateTo." 23:59:59";
		
		
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		$sDeleteMemoryTableQuery = "DELETE FROM tempOtQuickCount";
		$rDeleteMemoryTableResult = dbQuery($sDeleteMemoryTableQuery);
		
		echo dbError();

		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: Date between $sDateTimeFrom and $sDateTimeTo. Rep: $iAccountRep.\")"; 
		$rResult = dbQuery($sAddQuery);
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		// end of track users' activity in nibbles			
		
		$sHistoryDataQuery = "SELECT offers.offerCode AS offerCode, 
							offers.name AS offerName, 
							count(otDataHistory.email) AS totalLeads,
	          					count(otDataHistory.email) * otDataHistory.revPerLead AS totalRevenue
      					 FROM   otDataHistory, offers
      					 WHERE  otDataHistory.offerCode = offers.offerCode 
						 $sExcludeNonRevenueFilter
						 $sIncludeTestLeadsCheckedClauseHistory
						 $sPvOnlyClause
      					 AND    otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
						 GROUP BY otDataHistory.offerCode";
		$rHistoryDataResult = dbQuery($sHistoryDataQuery);
		echo  dbError();
		
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		while ($oHistoryRow = dbFetchObject($rHistoryDataResult)) {
			$sInsertQuery = "INSERT INTO tempOtQuickCount(offerCode, name, totalLeads, totalRevenue)
						VALUES (\"$oHistoryRow->offerCode\",\"$oHistoryRow->offerName\",
						\"$oHistoryRow->totalLeads\",\"$oHistoryRow->totalRevenue\")";
			$asdf = dbQuery($sInsertQuery);
		}
		
		
		
		
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		
		$sTodaysDataQuery = "SELECT offers.offerCode AS offerCode, 
							offers.name AS offerName, 
							count(otData.email) AS totalLeads,
	          				count(otData.email) * otData.revPerLead AS totalRevenue
      					 FROM   otData, offers
      					 WHERE  otData.offerCode = offers.offerCode  
						 $sExcludeNonRevenueFilter
						 $sIncludeTestLeadsCheckedClause
      					 AND    otData.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
						 GROUP BY otData.offerCode";
		$rTodaysDataResult = dbQuery($sTodaysDataQuery);
		echo  dbError();
		
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		while ($oCurrRow = dbFetchObject($rTodaysDataResult)) {
			$sInsertQuery = "INSERT INTO tempOtQuickCount(offerCode, name, totalLeads, totalRevenue)
						VALUES (\"$oCurrRow->offerCode\",\"$oCurrRow->offerName\",
						\"$oCurrRow->totalLeads\",\"$oCurrRow->totalRevenue\")";
			$asdf = dbQuery($sInsertQuery);
		}
		
		
		
		

		if( $iAccountRep != "" ) {
			$sAccountRepClause = " AND nbUsers.id = $iAccountRep ";
		} else {
			$sAccountRepClause = "";
		}
		
		if ($sAESubTotal == 'Y') {
			$sSortAEClause = " ORDER BY nbUsers.userName ";
		} else {
			$sSortAEClause = "";	
		}
		
		
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		$sMemoryTableSelectQuery = "SELECT tempOtQuickCount.offerCode, tempOtQuickCount.name, sum(tempOtQuickCount.totalLeads) AS totalLeads, sum(tempOtQuickCount.totalRevenue) AS totalRevenue, nbUsers.userName as userName
									FROM   tempOtQuickCount, offers, offerCompanies, nbUsers
									WHERE tempOtQuickCount.offerCode = offers.offerCode
									AND offers.companyId=offerCompanies.id
									AND locate(concat(\"'\",nbUsers.id,\"'\"),offerCompanies.repDesignated)
									$sAccountRepClause
									GROUP BY tempOtQuickCount.offerCode
									$sSortAEClause";
		$rMemoryTableSelectResult = dbQuery($sMemoryTableSelectQuery);
		$iTotalLeads = 0;
		$fTotalRevenue = 0;
		echo dbError();
		$count = 0;
		$iAESubTotalLeads = 0;
		$iAESubTotalRev =0;
		while ($oMemoryTableSelectRow = dbFetchObject($rMemoryTableSelectResult)) {
			$count++ ;
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN_WHITE";
			} else {
				$bgcolorClass = "ODD";
			}

			if ($sAESubTotal == 'Y') {
				if ($iAccountRepPrevious != $oMemoryTableSelectRow->userName && isset($iAccountRepPrevious)) {
					$sReportContent .= "<tr class=$bgcolorClass>
								<td><b>$iAccountRepPrevious Total</b></td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td align=right>$iAESubTotalLeads</td>
								<td align=right>$iAESubTotalRev</td></tr>";	
					$sReportContent .= "<tr><td>&nbsp;</td></tr>";
					$iAESubTotalLeads = 0;
					$iAESubTotalRev =0;
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN_WHITE";
					} else {
						$bgcolorClass = "ODD";
					}
				}
			}

			$sReportContent .= "<tr class=$bgcolorClass>
								<td>$oMemoryTableSelectRow->userName</td>
								<td>$count $oMemoryTableSelectRow->offerCode</td>
								<td>$oMemoryTableSelectRow->name</td>
								<td align=right>$oMemoryTableSelectRow->totalLeads</td>
								<td align=right>$oMemoryTableSelectRow->totalRevenue</td></tr>";

			$sExpReportContent .= "$oMemoryTableSelectRow->userName\t$oMemoryTableSelectRow->offerCode\t$oMemoryTableSelectRow->name";
			$sExpReportContent .= "\t$oMemoryTableSelectRow->totalLeads\t$oMemoryTableSelectRow->totalRevenue\n";
					
			$iTotalLeads += $oMemoryTableSelectRow->totalLeads;
			$fTotalRevenue += $oMemoryTableSelectRow->totalRevenue;
			$iAccountRepPrevious = $oMemoryTableSelectRow->userName;
			$iAESubTotalLeads += $oMemoryTableSelectRow->totalLeads;
			$iAESubTotalRev += $oMemoryTableSelectRow->totalRevenue;
		}
		
		$rDeleteMemoryTableResult = dbQuery($sDeleteMemoryTableQuery);
		$fTotalRevenue = sprintf("%10.2f", round($fTotalRevenue,2));
	}
	
	$iHoursToday = date('H') ;
	if ($iHoursToday != 0 && $iHoursToday != '') {
		$fLeadsPerHour = $iTotalLeads / $iHoursToday ;
		
		$fRevenuePerHour = $fTotalRevenue / $iHoursToday ;
	} else {
		$fLeadsPerHour = $iTotalLeads;
		$fRevenuePerHour = $fTotalRevenue;
	}
	
	$fLeadsPerHour = sprintf("%10.2f", round($fLeadsPerHour,2));
	$fRevenuePerHour = sprintf("%10.2f", round($fRevenuePerHour,2));
		
	
	mysql_connect ($reportingHost, $reportingUser, $reportingPass);
	mysql_select_db ($reportingDbase);
	$sRepQuery = "SELECT id, firstName, userName
				 FROM   nbUsers
				 ORDER BY firstName";
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	$sAccountRepOptions = "<option value=''>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ($iAccountRep == $oRepRow->id) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		
		$sAccountRepOptions .= "<option value='$oRepRow->id' $sSelected>$oRepRow->userName";
	}
	
	
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	if ($sExportEmails) {
		$sExportEmailsChecked = "checked";
	}
	
	if ($sExcludeNonRevenue == 'Y') {
		$sExcludeNonRevenueChecked = "checked";
	}
	
	if ($sIncludeTestLeads == 'Y') {
		$sIncludeTestLeadsChecked = "checked";
	}
	

	
	if ($sPostalVerified == 'pvOnly') {
		$sPvOnlyChecked = "checked";
	} else if ($sPostalVerified == 'pvAndNonPv') {
		$sPvAndNonPvChecked = "checked";
	} else {
		$sPvOnlyChecked="checked";	
	}
		
	if ($sShowQueries == 'Y') {
		$sShowQueriesChecked = "checked";
	}
		
	if ($sAESubTotal == 'Y') {
		$sAESubTotalChecked = "checked";
	}
		
		
	if ($sExportEmails) {
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		$aTemp = array();

		$sEmailHistoryQuery = "SELECT DISTINCT otDataHistory.email AS email
   						FROM   otDataHistory, offers, offerCompanies
  						WHERE  otDataHistory.offerCode = offers.offerCode 
  						AND offers.companyId = offerCompanies.id
						$sExcludeNonRevenueFilter
						$sIncludeTestLeadsCheckedClauseHistory
						$sPvOnlyClause
						$sAccountRepClause
   						AND    otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		$rEmailHistoryQueryResult = dbQuery($sEmailHistoryQuery);
		echo  dbError();
		$x = 0;
		while ($oRow = dbFetchObject($rEmailHistoryQueryResult)) {
			$aTemp['email'][$x] = $oRow->email;
			$x++;
		}
			
			
		$sEmailQuery = "SELECT DISTINCT otData.email as email
   						FROM   otData, offers, offerCompanies
   						WHERE  otData.offerCode = offers.offerCode 
   						AND offers.companyId = offerCompanies.id
						$sExcludeNonRevenueFilter
						$sIncludeTestLeadsCheckedClause
						$sAccountRepClause
   						AND    otData.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'";
		$rEmailQueryResult = dbQuery($sEmailQuery);
		echo  dbError();
		while ($oRow = dbFetchObject($rEmailQueryResult)) {
			$aTemp['email'][$x] = $oRow->email;
			$x++;
		}
			
		$aTemp['email'] = array_unique($aTemp['email']);
		$sExportEmailData = '';
		
		foreach ($aTemp['email'] as $email) {
			$sExportEmailData .= "\"$email\"\r\n";
		}
	}

	if ($sExportExcel) {
		$sExpReportContent = "User Name\tOffer Code\tOffer Name\tNo Of Leads\tRevenue"."\n".$sExpReportContent;
		$sExpReportContent .= "\nTotal\t\t$iTotalLeads\t$fTotalRevenue";
		$sExpReportContent .= "\nLeads & Revenue Per Hour\t\t$fLeadsPerHour\t$fRevenuePerHour";
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nUpdated in real time.";
		$sExpReportContent .= "\nReport omits any leads having address starting with '3401 Dundee' considering those as test leads.";
		$sExpReportContent .= "\nGross leads without regard to any type of verification.";
		$sExpReportContent .= "\nPer hour figures only valid for current day.";
		$sExpReportContent .= "\nPer hour figures based on number of hours since 00:00 rounded down.\n";

		$sFileName = "otQuickCount_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
	} else if ($sExportEmails) {
			
			$sFileName = "oneTimeQuickEmails_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

			$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
			if ($rFpFile) {
				fputs($rFpFile, $sExportEmailData, strlen($sExportEmailData));
				fclose($rFpFile);

				echo "<script language=JavaScript>
					void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  	</script>";
			} else {
				$sMessage = "Error exporting data...";
			}
		}
	}
	
	if ($sShowQueries == 'Y') {	
		$sQueries = "<b>Queries Used To Prepare This Report:</b>".
					 "<BR><BR><b>History Query - Collects data from otDataHistory table:</b><BR>".$sHistoryDataQuery.
					 "<BR><BR><b>Current Query - Collects data from otData table (today's data):</b><BR>".$sTodaysDataQuery.
					 "<BR><BR><b>Temp Query - Collects data from table(s) and insert it into temp table:</b><BR>".$sMemoryTableSelectQuery.
					 "<BR><BR><b>Rep Query - Collects Account Executive's name from nbUsers table:</b><BR>".$sRepQuery;
				if ($sExportEmails=='Y') {
		$sQueries .= "<BR><BR><b>Temp Query - Collects email addresses from otDataHistory table and insert them into temp table:</b><BR>".$sEmailHistoryQuery.
					 "<BR><BR><b>Temp Query - Collects email addresses from otData table and insert them into temptable:</b><BR>".$sDistinctEmailQuery.
					 "<BR><BR><b>Email Query - Collects email addresses from temp table:</b><BR>".$sEmailQuery;
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
	</select>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Date To
	<select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	
	
	<tr><Td>Account Executive</td><td><select name=iAccountRep><?php echo $sAccountRepOptions;?></select> </td></tr>
	<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <?php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <?php echo $sPvOnlyChecked;?>> PostalVerified
				
	</td></tr>
	<tr><td></td><td><input type=checkbox name=sExcludeNonRevenue value='Y' <?php echo $sExcludeNonRevenueChecked;?>> Exclude Non-Revenue Offers</td></tr>
	<tr><td></td><td><input type=checkbox name=sIncludeTestLeads value='Y' <?php echo $sIncludeTestLeadsChecked;?>> Include Test Leads</td></tr>

	
	<tr><td colspan=2><input type=button name=sSubmit value='View Report'  onClick="funcReportClicked('report');">	
	&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
	&nbsp; &nbsp; &nbsp; <input type=checkbox name=sExportEmails value="Y"  <?php echo $sExportEmailsChecked;?>> Export Emails
	&nbsp; &nbsp; &nbsp; <input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries
	&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;
	<input type=checkbox name=sAESubTotal value='Y' <?php echo $sAESubTotalChecked;?>> Sort & Sub Total by AE
	</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
		<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
			<tr><td>
			<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
			<tr><td>
				<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=4 class=header>Run Date / Time: <?php echo $sRunDateAndTime; ?></td></tr>
	<tr><td width=120 class=header>Account<br>Executive</td><td width=120 class=header>Offer Code</td><td width=250 class=header>Offer Name</td>
		<td class=header align=right width=100>Number Of Leads</td><td class=header align=right>Revenue</td>
	</tr>
		<?php echo $sReportContent;?>
			<tr><td colspan=5 align=left><hr color=#000000></td></tr>
	<tr><td class=header colspan=3>Total:</td><td class=header align=right><?php echo $iTotalLeads; ?></td>
			<td class=header align=right><?php echo number_format($fTotalRevenue,2);?></td></tr>
			
			<tr><td colspan="4">&nbsp;</td></tr>
			
			<tr>
			<td class=header colspan=3>Leads & Revenue Per Hour:</td>
			<td class=header align=right><?php echo number_format($fLeadsPerHour,2); ?></td>
			<td class=header align=right><?php echo number_format($fRevenuePerHour,2);?></td>
			</tr>
			
	<tr><td colspan=4 class=header><BR>Notes -</td></tr>
	<tr><td colspan=4>- Updated in real time.</td></tr>
	<tr><td colspan=4>- Report omits any leads having address starting with '3401 Dundee' considering those as test leads.</td></tr>
	<tr><Td colspan=4>- Gross leads without regard to any type of verification.</td></tr>
	<tr><Td colspan=4>- Per hour figures only valid for current day.</td></tr>
	<tr><Td colspan=4>- Per hour figures based on number of hours since 00:00 rounded down.</td></tr>
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
} else {
	echo "You are not authorized to access this page...";
}
?>

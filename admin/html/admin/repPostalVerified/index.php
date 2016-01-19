<?php
/*********
Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.
**********/
ini_set("register_globals",0);

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/validationFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);



while (list($key,$val) = each($_GET)) {
	$sVarToPass .= "&$key=$val";
}

while (list($key,$val) = each($_POST)) {
	$sVarToPass .= "&$key=$val";
}


if (!isset($_GET['iPage']) || isInteger($_GET['iPage'])) {
	$iPage = $_GET['iPage'];
} else {
	$sMessage .= "iPage is invalid<br>";	
}

if (!isset($_GET['iRecPerPage']) || isInteger($_GET['iRecPerPage'])) {
	$iRecPerPage = $_GET['iRecPerPage'];
} else {
	$sMessage .= "iRecPerPage is invalid<br>";	
}

if (!isset($_GET['iYearTo']) || isInteger($_GET['iYearTo'])) {
	$iYearTo = $_GET['iYearTo'];
} else {
	$sMessage .= "iYearTo is invalid<br>";	
}

if (!isset($_GET['iDayTo']) || isInteger($_GET['iDayTo'])) {
	$iDayTo = $_GET['iDayTo'];
} else {
	$sMessage .= "Day To is invalid<br>";	
}

if (!isset($_GET['iMonthTo']) || isInteger($_GET['iMonthTo'])) {
	$iMonthTo = $_GET['iMonthTo'];
} else {
	$sMessage .= "Month To is invalid<br>";	
}

if (!isset($_GET['iYearFrom']) || isInteger($_GET['iYearFrom'])) {
	$iYearFrom = $_GET['iYearFrom'];
} else {
	$sMessage .= "Year From is invalid<br>";	
}

if (!isset($_GET['iDayFrom']) || isInteger($_GET['iDayFrom'])) {
	$iDayFrom = $_GET['iDayFrom'];
} else {
	$sMessage .= "Day From is invalid<br>";	
}

if (!isset($_GET['iMonthFrom']) || isInteger($_GET['iMonthFrom'])) {
	$iMonthFrom = $_GET['iMonthFrom'];
} else {
	$sMessage .= "Month From is invalid<br>";	
}

if (!isset($_GET['iMenuId']) || isInteger($_GET['iMenuId'])) {
	$iMenuId = $_GET['iMenuId'];
} else {
	$sMessage .= "You must come to this page from Nibbles Main Menu.<br>";	
}

/*if (!isset($_GET['iId']) || isInteger($_GET['iId'])) {
	$iId = $_GET['iId'];
} else {
	$sMessage .= "iId is invalid<br>";	
}*/

if (!isset($_GET['sViewReport']) || $_GET['sViewReport']=='View Report') {
	$sViewReport = $_GET['sViewReport'];
} else {
	$sMessage .= "View Report is invalid<br>";	
}

$iScriptStartTime = getMicroTime();

$sPageTitle = "Postal Verified Report";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	
	
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "dateTimeAdded";
		$sCurrOrder = "DESC";
	}
	
	//echo "<br>Before: $sCurrOrder";
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "percentVerified" :
			$sCurrOrder = $sPercentVerifiedOrder;
			$sPercentVerifiedOrder = ($sPercentVerifiedOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "grossLeads" :
			$sCurrOrder = $sGrossLeadsOrder;
			$sGrossLeadsOrder = ($sGrossLeadsOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "leadsSent" :
			$sCurrOrder = $sLeadsSentOrder;
			$sLeadsSentOrder = ($sLeadsSentOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "postalVerified" :
			$sCurrOrder = $sPostalVerifiedOrder;
			$sPostalVerifiedOrder = ($sPostalVerifiedOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "dateTimeAdded" :
			$sCurrOrder = $sDateOrder;
			$sDateOrder = ($sDateOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	if ($sCurrOrder == 'DESC') {
		$sCurrOrder = SORT_DESC;
	} else {
		$sCurrOrder = SORT_ASC;
	}
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";

	
	

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
	$sToday = date('Y')."-".date('m')."-".date('d');

	
	if (!$sViewReport) {

		$iMonthTo = date('m');
		$iDayTo = date('d');
		$iYearTo = date('Y');

		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
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



	
	if ($sViewReport != "" && $sMessage=="") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				
				$iPageTotalGrossLeadsCount = 0;
				$iPageTotalLeadsSentCount = 0;
				$iPageTotalPostalVerifiedCount = 0;

				
				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				mysql_connect ($host, $user, $pass); 
				mysql_select_db ($dbase); 
		
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'\")"; 
				$rResult = dbQuery($sAddQuery); 
				echo  dbError(); 
				mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
				mysql_select_db ($reportingDbase); 
				// end of track users' activity in nibbles		



				
				
				// collect data from otData (current) table if ending date of the report includes today.
				if ($sDateTo >=	$sToday) {
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as grossLeads
									FROM otData
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
							$aReportArray['dateTimeAdded'][$oReportRow->dateTimeAdded] = $oReportRow->dateTimeAdded;
							$aReportArray['grossLeads'][$oReportRow->dateTimeAdded] = $oReportRow->grossLeads;
					}
					
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as leadsSent
									FROM otData
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									AND sendStatus = 'S'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
							$aReportArray['leadsSent'][$oReportRow->dateTimeAdded] = $oReportRow->leadsSent;
					}
					
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as postalVerified
									FROM otData
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									AND postalVerified = 'V'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
							$aReportArray['postalVerified'][$oReportRow->dateTimeAdded] = $oReportRow->postalVerified;
							$aReportArray['percentVerified'][$oReportRow->dateTimeAdded] = number_format((($oReportRow->postalVerified/$aReportArray['grossLeads'][$oReportRow->dateTimeAdded] )*100), 2, '.', "");
					}
				}	//END: collect data from otData (current) table if ending date of the report includes today.
	
				
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as grossLeads
									FROM otDataHistory
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
						$aReportArray['dateTimeAdded'][$oReportRow->dateTimeAdded] = $oReportRow->dateTimeAdded;
						$aReportArray['grossLeads'][$oReportRow->dateTimeAdded] = $oReportRow->grossLeads;
					}
					
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as leadsSent
									FROM otDataHistory
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									AND sendStatus = 'S'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
						$aReportArray['leadsSent'][$oReportRow->dateTimeAdded] = $oReportRow->leadsSent;
					}
					
					$sReportQuery = "SELECT substring(dateTimeAdded,1,10) as dateTimeAdded, count(id) as postalVerified
									FROM otDataHistory
									WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
									AND postalVerified = 'V'
									GROUP BY dateTimeAdded";
					$rReportQueryResult = dbQuery($sReportQuery);
					echo dbError();
					
					
					while ($oReportRow = dbFetchObject($rReportQueryResult)) {
						$aReportArray['postalVerified'][$oReportRow->dateTimeAdded] = $oReportRow->postalVerified;
						$aReportArray['percentVerified'][$oReportRow->dateTimeAdded] = number_format((($oReportRow->postalVerified/$aReportArray['grossLeads'][$oReportRow->dateTimeAdded] )*100), 2, '.', "");
					}

					

					// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
						switch ($sOrderColumn) {
							case "grossLeads" :
							array_multisort($aReportArray['grossLeads'], $sCurrOrder, $aReportArray['dateTimeAdded'], $aReportArray['leadsSent'], $aReportArray['postalVerified'], $aReportArray['percentVerified']);
							break;
							case "leadsSent" :
							array_multisort($aReportArray['leadsSent'], $sCurrOrder, $aReportArray['grossLeads'], $aReportArray['dateTimeAdded'], $aReportArray['postalVerified'], $aReportArray['percentVerified']);
							break;
							case "postalVerified" :
							array_multisort($aReportArray['postalVerified'], $sCurrOrder, $aReportArray['grossLeads'], $aReportArray['leadsSent'], $aReportArray['dateTimeAdded'], $aReportArray['percentVerified']);
							break;
							case "percentVerified" :
							array_multisort($aReportArray['percentVerified'], $sCurrOrder, $aReportArray['grossLeads'], $aReportArray['leadsSent'], $aReportArray['postalVerified'], $aReportArray['dateTimeAdded']);
							break;
							default :
							array_multisort($aReportArray['dateTimeAdded'], $sCurrOrder, $aReportArray['grossLeads'], $aReportArray['leadsSent'], $aReportArray['postalVerified'], $aReportArray['percentVerified']);
						}
					
						
						
			$sReportContent = "";
					
			$iNumRecords = count($aReportArray['dateTimeAdded']);
			$iTotalPages = ceil(($iNumRecords)/$iRecPerPage);
			
			// If current page no. is greater than total pages move to the last available page no.
			if ($iPage > $iTotalPages) {
				$iPage = $iTotalPages;
			}
			
			$iStartRec = ($iPage-1) * $iRecPerPage;
			$iEndRec = $iStartRec + $iRecPerPage -1;

			if ($iNumRecords > 0) {
				$sCurrentPage = " Page $iPage "."/ $iTotalPages";
			}
		
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
			
			$sPageLoop = 0;	
			foreach ($aReportArray['dateTimeAdded'] as $sDate) {
				$sPageLoop++;
				if (($sPageLoop > $iStartRec) && ($sPageLoop <= ($iStartRec + $iRecPerPage))) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}

						$sReportContent .= "<tr class=$sBgcolorClass>$sDateColumn
									<td>".$aReportArray['dateTimeAdded'][$sDate]."</td>
									<td>".$aReportArray['grossLeads'][$sDate]."</td>
									<td>".$aReportArray['leadsSent'][$sDate]."</td>
									<td>".$aReportArray['postalVerified'][$sDate]."</td>
									<td>".$aReportArray['percentVerified'][$sDate]."</td>
								</tr>";
					
						$iPageTotalGrossLeadsCount += $aReportArray['grossLeads'][$sDate];
						$iPageTotalLeadsSentCount += $aReportArray['leadsSent'][$sDate];
						$iPageTotalPostalVerifiedCount += $aReportArray['postalVerified'][$sDate];
						
						$sExpReportContent .= $aReportArray['dateTimeAdded'][$sDate]."\t";
						$sExpReportContent .= $aReportArray['grossLeads'][$sDate]."\t";
						$sExpReportContent .= $aReportArray['leadsSent'][$sDate]."\t";
						$sExpReportContent .= $aReportArray['postalVerified'][$sDate]."\t";
						$sExpReportContent .= $aReportArray['percentVerified'][$sDate]."\n";
					}
			}
				$sReportContent .= "<tr><td colspan=5><HR color=#000000></td></tr>
							<tr><td class=header>Total</td>
								<td class=header>$iPageTotalGrossLeadsCount</td>
								<td class=header>$iPageTotalLeadsSentCount</td>
								<td class=header>$iPageTotalPostalVerifiedCount</td>
							</tr>";

				$sExpReportContent .= "Total\t$iPageTotalGrossLeadsCount\t$iPageTotalLeadsSentCount\t$iPageTotalPostalVerifiedCount\n";
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}


	if ($sExportExcel && !$bDateRangeNotOk) {
		$sExpReportContent = "Date\tGross Leads\tLeads Sent\tPostal Verified\tPercent Postal Verified"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";

		$sFileName = "dbMailsSent_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
 &nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td>
	<td colspan=2></td>
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
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=dateTimeAdded&sDateOrder=<?php echo $sDateOrder;?>" class=header>Date</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=grossLeads&sGrossLeadsOrder=<?php echo $sGrossLeadsOrder;?>" class=header>Gross Leads</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=leadsSent&sLeadsSentOrder=<?php echo $sLeadsSentOrder;?>" class=header>Leads Sent</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=postalVerified&sPostalVerifiedOrder=<?php echo $sPostalVerifiedOrder;?>" class=header>Postal Verified</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=percentVerified&sPercentVerifiedOrder=<?php echo $sPercentVerifiedOrder;?>" class=header>% Postal Verified</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=5>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Data for today is incomplete, includes from midnight to current time.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		Gross Leads: Number of leads we collected including non-postal verified.  
			This is the count of id in otDataHistory table for that date (otData table for today's entry).
			<br>
		Leads Sent: Number of leads we sent to our client.  This number will always be less than or equal to Postal Verified.
			This is the count of id in otDataHistory table for that date (otData table for today's entry) where sendStatus is "S".
			<br>
		Postal Verified: Number of leads that are postal verified.  This number will always be less than or equal to Gross Leads.
			This is the count of id in otDataHistory table for that date (otData table for today's entry) where postalVerified is "V".
			<br>
		Percent Postal Verified: The ratio of Postal Verified leads versus Gross Leads.
			This is the result of Postal Verified divide by Gross Leads.
			<br>
		</td></tr>
	<tr><td colspan=5><BR><BR></td></tr>
		<?php echo $sQueries;?>
		</table></td></tr></table></td></tr>
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

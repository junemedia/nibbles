<?php

/*********

Script to Display 

**********/
    
include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Postal Verification Report";

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
$sToday = date('m')."-".date('d')."-".date('Y');

$sViewReport = stripslashes($sViewReport);

	
if (!($sDateFrom || $sDateTo)) {
	$sDateFrom = $sToday;
	$sDateTo = $sToday;	
	if (!($sViewReport)) {
		$sViewReport = "Get Report";
	}
}


if ($sViewReport) {

	$iMonthFrom = substr($sDateFrom,0,2);
	$iDayFrom = substr($sDateFrom,3,2);
	$iYearFrom = substr($sDateFrom, 6,4);
	
	$iMonthTo = substr($sDateTo,0,2);
	$iDayTo = substr($sDateTo,3,2);
	$iYearTo = substr($sDateTo, 6,4);
	
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
		
		//if (!($sDateFrom)) {
			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
		//}
		//if (!($sDateTo)) {
			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";
		//}
	
} 
		
	
	if ($sViewReport) {
		
		$iGrandTotalUniqueUsers = 0;
		$iGrandTotalUniqueUsersPv = 0;
		
		if ($sReportBy == 'page') {
			$sReportQuery = "SELECT pageId, pageName, count(distinct otDataHistory.email) AS uniqueUsers
						 FROM  otPages LEFT JOIN otDataHistory ON otDataHistory.pageId = otPages.id, userDataHistory
						 WHERE  userDataHistory.email = otDataHistory.email	
						 AND	address NOT LIKE \"3401 Dundee%\"
						 AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 AND 	userDataHistory.postalVerified IS NOT NULL						
						 GROUP BY otPages.id ORDER BY otPages.id";
			$sColumnHeader = "Page Name";
			
		} else if ($sReportBy == 'sourceCode') {
			$sReportQuery = "SELECT sourceCode, count(distinct otDataHistory.email) AS uniqueUsers
						 FROM   otDataHistory, userDataHistory
						 WHERE  userDataHistory.email = otDataHistory.email	
						 AND	address NOT LIKE \"3401 Dundee%\"					 
						 AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 AND 	userDataHistory.postalVerified IS NOT NULL
						 GROUP BY sourceCode ORDER BY sourceCode";
			
			$sColumnHeader = "Source Code";
		} else {
			$sReportQuery = "SELECT offerCode, count(distinct otDataHistory.email) AS uniqueUsers
						 FROM   otDataHistory, userDataHistory
						 WHERE  userDataHistory.email = otDataHistory.email						 
						 AND	address NOT LIKE \"3401 Dundee%\"
						 AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 AND 	userDataHistory.postalVerified IS NOT NULL
						 GROUP BY offerCode ORDER BY offerCode";
			$sColumnHeader = "Offer Code";
		}
		$rReportResult = mysql_query($sReportQuery);
		echo mysql_error()		;
		while ($oReportRow = mysql_fetch_object($rReportResult)) {
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$iUniqueUsers = $oReportRow->uniqueUsers;
			
			$sReportContent .= "<tr class=$sBgcolorClass>";
			
			if ($sReportBy == 'page') {
				$iPageId = $oReportRow->pageId;
				$sPageName = $oReportRow->pageName;
				$sUniqueUsersPvQuery = "SELECT count(distinct otDataHistory.email) as uniqueUsersPv
										FROM   userDataHistory, otDataHistory
										WHERE  userDataHistory.email = otDataHistory.email						 
										AND	   address NOT LIKE \"3401 Dundee%\"
							 			AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 				AND    userDataHistory.postalVerified ='V' and verified != 'I'
										AND	   otDataHistory.pageId = '$iPageId'";
				
				$sReportContent .= "<td>$sPageName</td>";
				
			} else if ($sReportBy == 'sourceCode') {
				$sSourceCode = $oReportRow->sourceCode;
				
				$sUniqueUsersPvQuery = "SELECT count(distinct otDataHistory.email) as uniqueUsersPv
										FROM   userDataHistory, otDataHistory
										WHERE  userDataHistory.email = otDataHistory.email						 
										AND	   address NOT LIKE \"3401 Dundee%\"
							 			AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 				AND    userDataHistory.postalVerified ='V' and verified != 'I'
										AND	   otDataHistory.sourceCode = '$sSourceCode'";
				
				$sReportContent .= "<td>$sSourceCode</td>";
				
			} else {
				
				$sOfferCode = $oReportRow->offerCode;
				
				$sUniqueUsersPvQuery = "SELECT count(distinct otDataHistory.email) as uniqueUsersPv
										FROM   userDataHistory, otDataHistory
										WHERE  userDataHistory.email = otDataHistory.email						 
										AND	   address NOT LIKE \"3401 Dundee%\"
							 			AND    date_format(otDataHistory.dateTimeAdded,\"%m-%d-%Y\") BETWEEN '$sDateFrom' AND '$sDateTo'
						 				AND    userDataHistory.postalVerified ='V' and verified != 'I'
										AND	   otDataHistory.offerCode = '$sOfferCode'";
				
				$sReportContent .= "<td>$sOfferCode</td>";
			}
			
			$rUniqueUsersPvResult = mysql_query($sUniqueUsersPvQuery);
			echo mysql_error();
			while ($oUniqueUsersPvRow = mysql_fetch_object($rUniqueUsersPvResult)) {
				$iUniqueUsersPv = $oUniqueUsersPvRow->uniqueUsersPv;
			}
				
			if ($iUniqueUsers != 0 && $iUniqueUsers != '') {
				$fPercentPv = ($iUniqueUsersPv * 100 ) / $iUniqueUsers;		
			}
			
			$fPercentPv = sprintf("%5.2f",round($fPercentPv, 2));
			$iGrandTotalUniqueUsers += $iUniqueUsers;
			$iGrandTotalUniqueUsersPv += $iUniqueUsersPv;
		
			$sReportContent .= "<td align=right>$iUniqueUsers</td><td align=right>$iUniqueUsersPv</td><td align=right>$fPercentPv</td></tr>";
		}
		
		
		if ($iGrandTotalUniqueUsers != 0 && $iGrandTotalUniqueUsers != '') {
				$fGrandPercentPv = ($iGrandTotalUniqueUsersPv * 100 ) / $iGrandTotalUniqueUsers;		
			}
			
			$fGrandPercentPv = sprintf("%5.2f",round($fGrandPercentPv, 2));	
		
			
				
		$sReportContent .= "<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
								<tr><td><b>Summary</b></td>
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>								
								<td align=right><b>$iGrandTotalUniqueUsersPv</b></td>								
								<td align=right><b>$fGrandPercentPv</b></td>
							</tr>";			
	}
			
	
	if ($sShowQueries == 'Y') {
		$sShowQueriesChecked = "checked";
	}
	
	



	if ($sShowQueries == 'Y') {
		
		$sQueries .= "<b>Main Query To Get Offers/Pages/SourceCodes:</b><BR><BR> $sReportQuery
					  <BR><BR><b>Query To Get UniqueUsers Count For An Offer/Page/SourceCode</b>
					  <br><br>$sUniqueUsersPvQuery	
						";
	}
	
	$sPageChecked = '';
	$sOfferChecked = '';
	$sSourceCodeChecked = '';
	
	switch($sReportBy) {
		case 'page':
			$sPageChecked = "checked";
			break;
		case 'sourceCode':
			$sSourceCodeChecked = "checked";
			break;
		default:
			$sOfferChecked = "checked";
	}
	
	
	include("../../includes/adminHeader.php");	
		
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Date From</td><td><input type=textbox name=sDateFrom Value='<?php echo $sDateFrom;?>'></td></tr>
	<tr><td>Date To</td><td><input type=textbox name=sDateTo Value='<?php echo $sDateTo;?>'></td></tr>		
	<tr><td>Report By</td><td><input type=radio name=sReportBy value='offer' <?php echo $sOfferChecked;?>> Offer &nbsp; &nbsp; 
			<input type=radio name=sReportBy value='page' <?php echo $sPageChecked;?>> Page &nbsp; &nbsp; 
			<input type=radio name=sReportBy value='sourceCode' <?php echo $sSourceCodeChecked;?>> SourceCode &nbsp; &nbsp; </td></tr>
	<!--<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <php echo $sPvOnlyChecked;?>> PostalVerified
	</td></tr>-->	
	<tr><td colspan=2><input type=submit name=sViewReport value='Get Report'>  &nbsp; &nbsp; 
	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td><td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	<!--<input type=submit name=sPrintReport value='Print This Report'></td></tr>-->
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
	<tr><td class=header><?php echo $sColumnHeader;?></td>
		<td align=right class=header>Gross Unique Users</td>				
		<td align=right class=header>Unique User PV</td>
		<td align=right class=header>% PV</td></td>		
	</tr>
	
<?php echo $sReportContent;?>

	<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=4 class=header><BR>Notes -</td></tr>
	<tr><td colspan=4>Report includes data only where PV is attempted.
					 <BR>Counts will change as postal verification status changes.
					 <BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads.
					 <BR>Summary will always different for Offery wise, Page wise and SourceCode wise Report because 
					 	 unique users are counted within different type of groups (group of offer, page or sourceCode)					 	 
					 <BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)
					 	 
				</td></tr>	
	<tr><td colspan=4><BR><BR></td></tr>
	<tr><td colspan=4><b>Queries Used To Prepare This Report:</b><BR><BR><?php echo $sQueries; ?></td></tr>
						<tr><td colspan=2><BR><BR></td></tr>
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
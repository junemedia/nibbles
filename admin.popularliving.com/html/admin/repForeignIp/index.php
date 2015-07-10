<?php

/*********
Script to Display 
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

set_time_limit(5000);
$iScriptStartTime = getMicroTime();
$sPageTitle = "Foreign IP Report";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	

	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "sourceCode";
		$sCurrOrder = "ASC";
	}
	
	//echo "<br>Before: $sCurrOrder";
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "foreignCountPercent" :
			$sCurrOrder = $sForeignCountPercentOrder;
			$sForeignCountPercentOrder = ($sForeignCountPercentOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "usCount" :
			$sCurrOrder = $sUsCountOrder;
			$sUsCountOrder = ($sUsCountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "usCountPercent" :
			$sCurrOrder = $sUsCountPercentOrder;
			$sUsCountPercentOrder = ($sUsCountPercentOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "foreignCount" :
			$sCurrOrder = $sForeignCountOrder;
			$sForeignCountOrder = ($sForeignCountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "uniqueUsers" :
			$sCurrOrder = $sUniqueUsersOrder;
			$sUniqueUsersOrder = ($sUniqueUsersOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	if ($sCurrOrder == 'DESC') {
		$sCurrOrder = SORT_DESC;
	} else {
		$sCurrOrder = SORT_ASC;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
				&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sSourceCode=$sSourceCode";
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iYearTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
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
	
	$sDateTimeFrom = $sDateFrom." 00:00:00";
	$sDateTimeTo = $sDateTo." 23:59:59";
	
	if ($sViewReport != "") {
		if ($sSourceCode != '') {
			$sSourceCodeFilter = " AND sourceCode = '$sSourceCode' ";
		} else {
			$sSourceCodeFilter = '';
		}
	
		$sReportQuery = "SELECT sourceCode, remoteIp
					 FROM  otDataHistory
					 WHERE dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					 $sSourceCodeFilter ";
		$rReportResult = dbQuery($sReportQuery);
		echo  dbError();

		$i = 0;
		while ($oReportRow = dbFetchObject($rReportResult)) {
			if ($oReportRow->remoteIp == '') {
				$iIpNum = 0;
			} else {
				$iIpNum = split ("\.",$oReportRow->remoteIp);
				$iIpNum = ($iIpNum[3] + $iIpNum[2] * 256 + $iIpNum[1] * 256 * 256 + $iIpNum[0] * 256 * 256 * 256);
			}

			$sGetUniqueUserCount = "SELECT count(distinct email) as uniqueUsers FROM 
							otDataHistory WHERE dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							AND sourceCode=\"$oReportRow->sourceCode\" LIMIT 1";
			$rGetUniqueUserCount = dbQuery($sGetUniqueUserCount);
			while ($oCountRow = dbFetchObject($rGetUniqueUserCount)) {
				$aReportArray['uniqueUsers'][$i] = $oCountRow->uniqueUsers;
			}
			
			$sQuery = "SELECT  countrySHORT
				 FROM  ipcountry
				 WHERE ipFROM <=$iIpNum
				 AND ipTO >=$iIpNum";
			$rResult = dbQuery($sQuery);
			
			while ($oRow = dbFetchObject($rResult)) {
				$aReportArray['sourceCode'][$i] = $oReportRow->sourceCode;
				$aReportArray['country'][$i] = $oRow->countrySHORT;
				$i++;
			}
		}

		$iCountEntries = count($aReportArray['sourceCode']);
		if ($i>0) {
			array_multisort($aReportArray['sourceCode'], SORT_ASC, $aReportArray['country'], $aReportArray['uniqueUsers']);
			$sPrevious = $aReportArray['sourceCode'][0];
			$iUSCount = 0;
			$iTemp = 0;
			$iForeignCount = 0;
			$iNewCountArray = 0;
			for( $iLoop=0; $iLoop<$iCountEntries; $iLoop++ ) {	
				if( $sPrevious == $aReportArray['sourceCode'][$iLoop] ) {
					if ($aReportArray['country'][$iLoop] == "US") {
						$iUSCount ++;
					} else {
						$iForeignCount ++;
					}
				} else {
					$iTotal = $iUSCount + $iForeignCount;
					if ($iTotal > 0) {
						$iTempUS = ($iUSCount / $iTotal) * 100;
						$iTempOther = ($iForeignCount / $iTotal) * 100;
						$iTempUSPercent = number_format($iTempUS,1);
						$iTempOtherPercent = number_format($iTempOther,1);
					} else {
						$iTempUSPercent = 0;
						$iTempOtherPercent = 0;
					}
					
					$aFinalReportArray['sourceCode'][$iNewCountArray] = $aReportArray['sourceCode'][$iLoop-1];
					$aFinalReportArray['uniqueUsers'][$iNewCountArray] = $aReportArray['uniqueUsers'][$iLoop-1];
					$aFinalReportArray['usCount'][$iNewCountArray] = $iUSCount;
					$aFinalReportArray['usCountPercent'][$iNewCountArray] = $iTempUSPercent;
					$aFinalReportArray['foreignCount'][$iNewCountArray] = $iForeignCount;
					$aFinalReportArray['foreignCountPercent'][$iNewCountArray] = $iTempOtherPercent;
					$iNewCountArray++;
	
					$iUSCount = 0;
					$iForeignCount = 0;
					if ($aReportArray['country'][$iLoop] == "US") {
						$iUSCount ++;
					} else {
						$iForeignCount ++;
					}
				}
				$sPrevious = $aReportArray['sourceCode'][$iLoop];
			}
			
			$iLoop++;
			$iTotal = $iUSCount + $iForeignCount;
			if ($iTotal > 0) {
				$iTempUS = ($iUSCount / $iTotal) * 100;
				$iTempOtherPercent = ($iForeignCount / $iTotal) * 100;
				$iTempUSPercent = number_format($iTempUS,1);
				$iTempOtherPercent = number_format($iTempOtherPercent,1);
			} else {
				$iTempUSPercent = 0;
				$iTempOtherPercent = 0;
			}
						
			$aFinalReportArray['sourceCode'][$iNewCountArray] = $aReportArray['sourceCode'][$iCountEntries-1];
			$aFinalReportArray['uniqueUsers'][$iNewCountArray] = $aReportArray['uniqueUsers'][$iCountEntries-1];
			$aFinalReportArray['usCount'][$iNewCountArray] = $iUSCount;
			$aFinalReportArray['usCountPercent'][$iNewCountArray] = $iTempUSPercent;
			$aFinalReportArray['foreignCount'][$iNewCountArray] = $iForeignCount;
			$aFinalReportArray['foreignCountPercent'][$iNewCountArray] = $iTempOtherPercent;
		}
			
			
		if ($i>0) {
			// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
			switch ($sOrderColumn) {
				case "foreignCountPercent" :
					array_multisort($aFinalReportArray['foreignCountPercent'], $sCurrOrder,$aFinalReportArray['usCount'],$aFinalReportArray['usCountPercent'],$aFinalReportArray['foreignCount'],$aFinalReportArray['sourceCode'],$aFinalReportArray['uniqueUsers']);
					break;
				case "usCount" :
					array_multisort($aFinalReportArray['usCount'], $sCurrOrder,$aFinalReportArray['foreignCountPercent'],$aFinalReportArray['usCountPercent'],$aFinalReportArray['foreignCount'],$aFinalReportArray['sourceCode'],$aFinalReportArray['uniqueUsers']);
					break;
				case "usCountPercent" :
					array_multisort($aFinalReportArray['usCountPercent'], $sCurrOrder,$aFinalReportArray['foreignCountPercent'],$aFinalReportArray['usCount'],$aFinalReportArray['foreignCount'],$aFinalReportArray['sourceCode'],$aFinalReportArray['uniqueUsers']);
					break;
				case "foreignCount" :
					array_multisort($aFinalReportArray['foreignCount'], $sCurrOrder,$aFinalReportArray['foreignCountPercent'],$aFinalReportArray['usCount'],$aFinalReportArray['usCountPercent'],$aFinalReportArray['sourceCode'],$aFinalReportArray['uniqueUsers']);
					break;
				case "uniqueUsers" :
					array_multisort($aFinalReportArray['uniqueUsers'], $sCurrOrder,$aFinalReportArray['foreignCountPercent'],$aFinalReportArray['usCount'],$aFinalReportArray['usCountPercent'],$aFinalReportArray['sourceCode'],$aFinalReportArray['foreignCount']);
					break;
				default :
					array_multisort($aFinalReportArray['sourceCode'], $sCurrOrder,$aFinalReportArray['foreignCountPercent'],$aFinalReportArray['usCount'],$aFinalReportArray['usCountPercent'],$aFinalReportArray['foreignCount'],$aFinalReportArray['uniqueUsers']);
			}
		}
			
		
		$iGrandTotalUsCount = array_sum($aFinalReportArray['usCount']);
		$iGrandTotalForeignCount = array_sum($aFinalReportArray['foreignCount']);
		$iGrandTotalUniqueUserCount = array_sum($aFinalReportArray['uniqueUsers']);
			
		$iNumRecords = count($aFinalReportArray['sourceCode']);
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
			
		if ($i>0) {
			$sPageLoop = 0;	
			for( $iLoop=0; $iLoop<$iNumRecords; $iLoop++ ) {	
				$sPageLoop++;
				if (($sPageLoop > $iStartRec) && ($sPageLoop <= ($iStartRec + $iRecPerPage))) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sReportContent .= "<tr class=$sBgcolorClass>
								<td>".$aFinalReportArray['sourceCode'][$iLoop]."</td>
								<td>".$aFinalReportArray['uniqueUsers'][$iLoop]."</td>
								<td>".$aFinalReportArray['usCount'][$iLoop]."</td>
								<td>".$aFinalReportArray['usCountPercent'][$iLoop]."</td>
								<td>".$aFinalReportArray['foreignCount'][$iLoop]."</td>
								<td>".$aFinalReportArray['foreignCountPercent'][$iLoop]."</td>
								</tr>";
					$iTotalUsCount += $aFinalReportArray['usCount'][$iLoop];
					$iTotalForeignCount += $aFinalReportArray['foreignCount'][$iLoop];
					$iPageUniqueUsers += $aFinalReportArray['uniqueUsers'][$iLoop];
				}
			}
			$sTotal = $iTotalUsCount + $iTotalForeignCount;
			
			if ($sTotal > 0) {
				$sUsPercent = ($iTotalUsCount / $sTotal) * 100;
				$sForeignPercent = ($iTotalForeignCount / $sTotal) * 100;
				$sUsPercent = number_format($sUsPercent,1);
				$sForeignPercent = number_format($sForeignPercent,1);
			} else {
				$sUsPercent = 0;
				$sForeignPercent = 0;
			}
	
			$sReportContent .= "<tr><td colspan=6><hr color=#000000></td></tr>
						<tr><td><b>Total: </b></td>
						<td><b>$iPageUniqueUsers</b></td>
						<td><b>$iTotalUsCount</b></td>
						<td><b>$sUsPercent</b></td>
						<td><b>$iTotalForeignCount</b></td>
						<td><b>$sForeignPercent</b></td>
						</tr>";
	
			$sGrandTotal = $iGrandTotalUsCount + $iGrandTotalForeignCount;
			
			if ($sGrandTotal > 0) {
				$sGrandUsPercent = ($iGrandTotalUsCount / $sGrandTotal) * 100;
				$sGrandForeignPercent = ($iGrandTotalForeignCount / $sGrandTotal) * 100;
				$sGrandUsPercent = number_format($sGrandUsPercent,1);
				$sGrandForeignPercent = number_format($sGrandForeignPercent,1);
			} else {
				$sGrandUsPercent = 0;
				$sGrandForeignPercent = 0;
			}
			
			$sReportContent .= "<tr><td colspan=6><hr color=#000000></td></tr>
					<tr><td><b>Grand Total: </b></td>
					<td><b>$iGrandTotalUniqueUserCount</b></td>
					<td><b>$iGrandTotalUsCount</b></td>
					<td><b>$sGrandUsPercent</b></td>
					<td><b>$iGrandTotalForeignCount</b></td>
					<td><b>$sGrandForeignPercent</b></td>
					</tr>";
		}
	}
	

	$sSourceCodeQuery = "SELECT distinct sourceCode
				 FROM  otDataHistory
				 WHERE dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
				 AND sourceCode != ''
				 ORDER BY sourceCode ASC";
	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();
	while ($oRow = dbFetchObject($rSourceCodeResult)) {
		if ($sSourceCode) {
			if ($oRow->sourceCode == $sSourceCode) {
				$sSelected = 'selected';
			} else {
				$sSelected = '';
			}
		} else {
			if ($oRow->sourceCode == $sSourceCode && isset($sSourceCode)) {
				$sSelected = 'selected';
			} else {
				$sSelected = '';
			}
		}
		$sSourceCodeOptions .= "<option value=\"$oRow->sourceCode\" $sSelected>$oRow->sourceCode";
	}

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// start of track users' activity in nibbles
	mysql_connect ($host, $user, $pass);
	mysql_select_db ($dbase);
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")";
	$rResult = dbQuery($sAddQuery);
	mysql_connect ($reportingHost, $reportingUser, $reportingPass);
	mysql_select_db ($reportingDbase);
	// end of track users' activity in nibbles

	include("../../includes/adminHeader.php");	

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport value='ViewReport'>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>
		
	<tr><td>Source Code: </td><td><select name=sSourceCode>
	<option value='' selected>All
	<?php echo $sSourceCodeOptions;?>
	</select></td></tr>
	
	
	<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">	
	&nbsp; &nbsp;</td>
</tr>

<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> 
&nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> 
&nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; 
<?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>


</table>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=7 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=7 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=uniqueUsers&sUniqueUsersOrder=<?php echo $sUniqueUsersOrder;?>" class=header>Unique Users</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=usCount&sUsCountOrder=<?php echo $sUsCountOrder;?>" class=header>US Lead Count</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=usCountPercent&sUsCountPercentOrder=<?php echo $sUsCountPercentOrder;?>" class=header>US Lead Percent</a></td>	
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=foreignCount&sForeignCountOrder=<?php echo $sForeignCountOrder;?>" class=header>Foreign Lead Count</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=foreignCountPercent&sForeignCountPercentOrder=<?php echo $sForeignCountPercentOrder;?>" class=header>Foreign Lead Percent</a></td>
	</tr>
	
		<?php echo $sReportContent;?>

	<tr><td colspan=7 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=7 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=7><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)<br>
	Today's data is not included on this report.<br>
	Total: This is the total for current page only, not for the entire report.<br>
	Grand Total:  This is the total for entire report.<br>
	This report for visitors to otPages only.<br>
	US Count: This is count of ipAddresses ranged in US<br>
	US Percent: The ratio of US ipAddresses versus Total IpAddresses.  This is the result of 'US Count' divide by Total IpAddresses (US & Foreign).<br>
	Foreign Count: This is count of ipAddresses ranged out side of the US<br>
	Foreign Percent: The ratio of Foreign ipAddresses versus Total IpAddresses.  This is the result of 'Foreign Count' divide by Total IpAddresses (US & Foreign).<br>
	
	
	
	
	</td></tr>
		<?php //echo $sQueries;?>
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
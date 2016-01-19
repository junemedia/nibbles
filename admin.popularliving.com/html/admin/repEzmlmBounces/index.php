<?php

/*********

Script to Display Ezmlm Bounces
$Author: smita $
$Id: index.php,v 1.1 2005/04/05 21:19:42 smita Exp $

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Ezmlm Bounces Report";

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
	
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	
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
	
	
	if ($sSourceCode != '') {
		if ($sFilter == 'startsWith') {
			$sSourceCodeFilter = " AND sourceCode like \"$sSourceCode%\"";
		} else if ($sFilter == 'exactMatch') {
			$sSourceCodeFilter = " AND sourceCode = \"$sSourceCode\"";
		}
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
			default:
			$sCurrOrder = $sDateAddedOrder;
			$sDateAddedOrder = ($sDateAddedOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";
	
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
	
		
		
		
			$sReportQuery1 = "SELECT count(id) as counts
	   						  FROM   ezmlm.bounceLog";
							  //GROUP BY joinListId ORDER BY joinListId";
			
			$rReportResult1 = dbQuery($sReportQuery1);
			echo dbError();
			$i=0;
			while ($oReportRow = dbFetchObject($rReportResult1)) {
				$iJoinListId = $oReportRow->joinListId;
				$iBounceCount = $oReportRow->counts;
			
				$iJoinListId = '215';
				$aReportArray['bounceCount'][$iJoinListId] = $iBounceCount;

			}
			
			
			
			
			
			
			
			$sListQuery = "SELECT *
					  FROM   joinLists
					  ORDER BY id";
		
		$rListResult = dbQuery($sListQuery);
		
		while ($oListRow = dbFetchObject($rListResult)) {
			$iJoinListId = $oListRow->id;
			$sTitle = $oListRow->title;
			
			$iBounceCount = 0;
			
			$aReportArray['joinListId'][$iJoinListId] = $iJoinListId;
			$aReportArray['title'][$iJoinListId] = $sTitle;
			
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['joinListId'][$iJoinListId]."</td>
										<td>".$aReportArray['title'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['bounceCount'][$iJoinListId]."</td>
									</tr>";	
			
				$iPageTotalBounceCount += $aReportArray['bounceCount'][$iJoinListId];

			
		}
				
		
		

		$sReportContent .= "<tr><td colspan=3><HR color=#000000></td></tr>
								<tr><td></td>
								<td class=header>Page Total Counts</td>
								<td class=header align=right>$iPageTotalBounceCount</td>
							</tr>
						<tr><td colspan=3 align=left><hr color=#000000></td></tr>";
	}
	}


	if ($sShowQueries == 'Y') {
		//$sQueries = "<b>List Count Query:</b><BR>".$sReportQuery1;
		//$sQueries .= "<br><br>Subscription Count Query:</b><BR>".$sSubQuery;
		
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

	
	<tr><td >Source Code</td><td><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
			<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match
	</td></tr>
	
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
	<tr><td colspan=3 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=3 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>List Id</td>
		<td class=header>List Name</td>
		<td class=header align=right>Bounce Count</td>		
	</tr>

<?php echo $sReportContent;?>


	<tr><td colspan=3 class=header><BR>Notes -</td></tR>	
	<tr><td colspan=3>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=3><BR><BR></td></tr>
	<tr><td colspan=3><?php echo $sQueries;?></td></tr>
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
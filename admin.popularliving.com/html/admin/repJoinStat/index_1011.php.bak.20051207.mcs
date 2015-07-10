<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Join System Statistics Report";

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
			case "emailSub" :
			$sCurrOrder = $sEmailSubOrder;
			$sEmailSubOrder = ($sEmailSubOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDateAddedOrder;
			$sDateAddedOrder = ($sDateAddedOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 70;
	}
	
	if (!($iPage)) {
		$iPage = 1;
	}
	
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage";
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		/*
		SELECT date_format( joinEmailActive.dateTimeAdded, '%Y-%m-%d' ) AS dateAdded, joinLists.shortName, count( joinEmailActive.id ) AS subCount, count( joinEmailInactive.id ) AS unsubCount
		FROM joinLists
		LEFT JOIN joinEmailActive ON joinLists.id = joinEmailActive.joinListId
		LEFT JOIN joinEmailInactive ON joinLists.id = joinEmailInactive.joinListId
		WHERE date_format( joinEmailInactive.dateTimeAdded, '%Y-%m-%d' )
		BETWEEN '2004-08-20' AND '2004-08-20' AND date_format( joinEmailActive.dateTimeAdded, '%Y-%m-%d' )
		BETWEEN '2004-08-20' AND '2004-08-20'
		GROUP BY dateAdded, joinLists.id
		ORDER BY dateAdded ASC
		
		
		
		*/
		/*$sReportQuery = "SELECT date_format(joinEmailActive.dateTimeAdded,'%Y-%m-%d') AS dateAdded,
		joinLists.shortName, count(joinEmailActive.id) as subCount
		FROM   joinLists , joinEmailActive
		WHERE  joinLists.id = joinEmailActive.joinListId
		AND	date_format(joinEmailActive.dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
		GROUP  BY dateAdded, joinLists.id
		ORDER BY $sOrderColumn $sCurrOrder";
		*/
		
		
			$sReportQuery1 = "SELECT joinListId, count(joinEmailActive.id) as counts
	   						  FROM   joinEmailActive				   						  
							  GROUP BY joinListId ORDER BY joinListId";
			
			$rReportResult1 = dbQuery($sReportQuery1);
			echo dbError();
			$i=0;
			while ($oReportRow = dbFetchObject($rReportResult1)) {
				$iJoinListId = $oReportRow->joinListId;
				$iListCount = $oReportRow->counts;
			
				$aReportArray['listCount'][$iJoinListId] = $iListCount;

			}
			
			$sUnsubQuery = "SELECT joinListId, count(id) AS unsubCount
							   FROM	  joinEmailUnsub
							   WHERE  date_format(dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
							   $sSourceCodeFilter
							   GROUP BY joinListId ORDER BY joinListId";
			$rUnsubResult = dbQuery($sUnsubQuery);
			
			echo  dbError();
			while ($oUnsubRow = dbFetchObject($rUnsubResult)) {
				$iJoinListId = $oUnsubRow->joinListId;
				$iUnsubCount = $oUnsubRow->unsubCount;
				$aReportArray['unsubCount'][$iJoinListId] = $iUnsubCount;
			}							
		
			$sSubQuery = "SELECT joinListId,  count(id) AS subCount
							   FROM	  joinEmailSub
							   WHERE  date_format(dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
							   $sSourceCodeFilter
							   GROUP BY joinListId ORDER BY joinListId";
			$rSubResult = dbQuery($sSubQuery);			
			echo  dbError();
			
			while ($oSubRow = dbFetchObject($rSubResult)) {
				$iJoinListId = $oSubRow->joinListId;
				$iSubCount = $oSubRow->subCount;
				$aReportArray['subCount'][$iJoinListId] = $iSubCount;
			}
			
			$sConfirmQuery = "SELECT joinListId,  count(id) AS confirmCount
							   FROM	  joinEmailConfirm
							   WHERE  date_format(dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
							   $sSourceCodeFilter
							   GROUP BY joinListId ORDER BY joinListId";
			$rConfirmResult = dbQuery($sConfirmQuery);			
			echo  dbError();
			
			while ($oConfirmRow = dbFetchObject($rConfirmResult)) {
				$iJoinListId = $oConfirmRow->joinListId;
				$iConfirmCount = $oConfirmRow->confirmCount;
				$aReportArray['confirmCount'][$iJoinListId] = $iConfirmCount;
			}
			
			
			$sHeldQuery = "SELECT joinListId,  count(id) AS heldCount
							   FROM	  joinEmailHeldJournal
							   WHERE  date_format(dateTimeAdded,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
							   $sSourceCodeFilter
							   GROUP BY joinListId ORDER BY joinListId";
			$rHeldResult = dbQuery($sHeldQuery);			
			echo  dbError();
			
			while ($oHeldRow = dbFetchObject($rHeldResult)) {
				$iJoinListId = $oHeldRow->joinListId;
				$iHeldCount = $oHeldRow->heldCount;
				$aReportArray['heldCount'][$iJoinListId] = $iHeldCount;
			}
			
			
			$sListQuery = "SELECT *
					  FROM   joinLists
					  ORDER BY id";
		
		$rListResult = dbQuery($sListQuery);
		
		while ($oListRow = dbFetchObject($rListResult)) {
			$iJoinListId = $oListRow->id;
			$sTitle = $oListRow->title;
			
			$iActiveCount = 0;
			$iInactiveCount = 0;
			$aReportArray['joinListId'][$iJoinListId] = $iJoinListId;
			$aReportArray['title'][$iJoinListId] = $sTitle;
			
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['joinListId'][$iJoinListId]."</td>
										<td>".$aReportArray['title'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['listCount'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['subCount'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['confirmCount'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['unsubCount'][$iJoinListId]."</td>
										<td align=right>".$aReportArray['heldCount'][$iJoinListId]."</td>
									</tr>";	
			
				$iPageTotalListCount += $aReportArray['listCount'][$iJoinListId];
				$iPageTotalSubCount += $aReportArray['subCount'][$iJoinListId];
				$iPageTotalConfirmCount += $aReportArray['confirmCount'][$iJoinListId];
				$iPageTotalUnsubCount += $aReportArray['unsubCount'][$iJoinListId];
				$iPageTotalHeldCount += $aReportArray['heldCount'][$iJoinListId];
			
		}
				
		
		$sUniqueQuery = "SELECT uniqueListCount
						 FROM   joinEmailStats
						 WHERE  dateAdded = '$sYesterday'";
		$rUniqueResult = dbQuery($sUniqueQuery);
		while ($oUniqueRow = dbFetchObject($rUniqueResult)) {
			$iUniqueListCount = $oUniqueRow->uniqueListCount;
		
		} 
		
		$sUniqueQuery = "SELECT *
						 FROM   joinEmailStats
						 WHERE  dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
		$rUniqueResult = dbQuery($sUniqueQuery);
		while ($oUniqueRow = dbFetchObject($rUniqueResult)) {
			$iUniqueSubCount = $oUniqueRow->uniqueSubCount;
			$iUniqueConfirmCount = $oUniqueRow->uniqueConfirmCount;
			$iUniqueUnsubCount = $oUniqueRow->uniqueUnsubCount;
			$iUniqueHeldCount = $oUniqueRow->uniqueHeldCount;
		}

		$sReportContent .= "<tr><td colspan=7><HR color=#000000></td></tr>
								<tr><td></td>
								<td class=header>Page Total Counts</td>
								<td class=header align=right>$iPageTotalListCount</td>
								<td class=header align=right>$iPageTotalSubCount</td>
								<td class=header align=right>$iPageTotalConfirmCount</td>
								<td class=header align=right>$iPageTotalUnsubCount</td>
								<td class=header align=right>$iPageTotalHeldCount</td>
							</tr>
						<tr><td colspan=7 align=left><hr color=#000000></td></tr>";
	
		if (!($sDateFrom == $sToday && $sDateTo == $sToday)) {
				
				$sReportContent .= "<tr><td></td>
										<td class=header>Net Counts</td>
										<td class=header align=right>$iUniqueListCount</td>
										<td class=header align=right>$iUniqueSubCount</td>
										<td class=header align=right>$iUniqueConfirmCount</td>
										<td class=header align=right>$iUniqueUnsubCount</td>
										<td class=header align=right>$iUniqueHeldCount</td>
									</tr>
									<tr><td colspan=7><HR color=#000000></td></tr>";
		}
	}


	if ($sShowQueries == 'Y') {
		$sQueries = "<b>List Count Query:</b><BR>".$sReportQuery1;
		$sQueries .= "<br><br>Subscription Count Query:</b><BR>".$sSubQuery;
		$sQueries .= "<br><br>Subscription Count Query:</b><BR>".$sConfirmQuery;
		$sQueries .= "<br><br>Unsubscription Count Query:</b><BR>".$sUnsubQuery;
		$sQueries .= "<br><br>Held Count Query:</b><BR>".$sHeldQuery;
		$sQueries .= "<br><br>Join Lists Query:</b><BR>".$sListQuery;
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

	
	<tr><td >Source Code</td><td><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
			<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match
	</td></tr>
	
<tr><td colspan=2><input type=submit name=sViewReport value='View Report'>
	</td>
	<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td>
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
	<tr><td colspan=6 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>List Id</td>
		<td class=header>List Name</td>
		<td class=header align=right>List Count</td>
		<td class=header align=right>Subscription Count</a></td>
		<td class=header align=right>Confirmed Count</a></td>
		<td class=header align=right>Unsubscription Count</a></td>
		<td class=header align=right>Held Count</a></td>
	</tr>

<?php echo $sReportContent;?>


	<tr><td colspan=6 class=header><BR>Notes -</td></tR>
	<tr><td colspan=6><BR>Date/sourceCode filter doesn't apply to the list counts. Date/SourceCode filter only applies to the subscription/unsub/confirm/held counts.
	<tr><td colspan=6>Net counts based on nightly batch, not available if date range includes current date.
	</td></tr>
	<tr><td colspan=6>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=6><BR><BR></td></tr>
	<tr><td colspan=6><?php echo $sQueries;?></td></tr>
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
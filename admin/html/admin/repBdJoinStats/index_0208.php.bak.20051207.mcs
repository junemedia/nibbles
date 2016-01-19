<?php

/*

sallyjoinc: ok take a break from it. Need a report - call it "BD Join Stats"
sallyjoinc: use : SELECT  DISTINCT email
FROM  `joinEmailConfirm`
WHERE sourceCode
LIKE  'alijb%' AND dateTimeAdded >=  '2004-09-01 00:00:00' and dateTimeAdded <= '2004-09-30 23:59:59'
sallyjoinc: prompt for the dates and source code (drop downs)
sallyjoinc: in the notes show this query
 
couple of numbers for the source selected - gross subs, unique subs, gross confirms, unique confirms.

keith, chris, larry, nina, phil and stuart

put in sales and bd section 

*/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "BD Join Stats";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

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

if (!( $sViewReport )) {
	$iYearFrom = date('Y');
	$iMonthFrom = date('m');
	$iDayFrom = date('d');
		
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
	$iYearTo = $iYearFrom;
	
	$sShowQueries = "Y";
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
	
			
if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && isset($sSourceCode)) {
		
	
	$sSubQuery = "SELECT count(email) subCount, count(DISTINCT email) uniqueSubCount
				  FROM   joinEmailSub
				  WHERE  date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
	
	
	if ($sFilter == 'startsWith') {
		$sSubQuery .= " AND sourceCode LIKE '$sSourceCode%' ";
	} else {
		$sSubQuery .= " AND sourceCode = '$sSourceCode'";
	}
	
	$rSubResult = dbQuery($sSubQuery);
	while ($oSubRow = dbFetchObject($rSubResult)) {
		$iSubCount = $oSubRow->subCount;
		$iUniqueSubCount = $oSubRow->uniqueSubCount;		
	}
	
	
	$sConfirmQuery = "SELECT count(email) confirmCount, count(DISTINCT email) uniqueConfirmCount
				  FROM   joinEmailConfirm
				  WHERE  date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
	
	if ($sFilter == 'startsWith') {
		$sConfirmQuery .= " AND sourceCode LIKE '$sSourceCode%' ";
	} else {
		$sConfirmQuery .= " AND sourceCode = '$sSourceCode'";
	}
	
	
	$rConfirmResult = dbQuery($sConfirmQuery);
	while ($oConfirmRow = dbFetchObject($rConfirmResult)) {
		$iConfirmCount = $oConfirmRow->confirmCount;
		$iUniqueConfirmCount = $oConfirmRow->uniqueConfirmCount;
	}
	
	
}
		

$sStartsWithChecked = '';
$sExactMatchChecked = '';

if ($sFilter == 'startsWith') {
	$sStartsWithChecked = "checked";
} else {
	$sExactMatchChecked = "checked";
}

if ($sShowQueries == "Y") {
	$sShowQueriesChecked = "checked";
}

if ($sShowQueries == 'Y') {
		$sQueries = "<b>Sub Query:</b><BR>".$sSubQuery;
		$sQueries .= "<br><br><b>Confirm Query:</b><BR>".$sConfirmQuery;		
}
	
	
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

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
	
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
		<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>
	<tr><td colspan=2><input type=submit name=sViewReport value='View Report'></td>
		<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=7 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR>
			<BR><BR><BR></td></tr>
	<tr><td colspan=7 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>Gross Sub Count</td>
		<td class=header>Unique Sub Count</td>
		<td class=header>Gross Confirm Count</td>
		<td class=header>Unique Confirm Count</td>
	</tr>
	<tr><td><?php echo $iSubCount;?></td>
		<td><?php echo $iUniqueSubCount;?></td>
		<td><?php echo $iConfirmCount;?></td>
		<td><?php echo $iUniqueConfirmCount;?></td>
	</tr>
	<Tr><td colspan=4><hr color=#000000></td></tR>
	<tr><td colspan=4><BR><b>Notes -</b><BR>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
	<tr><td colspan=4><?php echo $sQueries;?></td></tr>
	
	</table></td></tr></table></td></tr>
	</table>
	
</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>
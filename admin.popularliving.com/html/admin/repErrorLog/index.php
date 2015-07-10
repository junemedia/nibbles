<?php

/*********

Script to Display 

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Error Log Report";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {

// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

if (!($iYearFrom)) {
	
	$iYearFrom = $iCurrYear;
	$iMonthFrom = $iCurrMonth;
	$iDayFrom = $iCurrDay;
	
	$iYearTo = $iYearFrom;
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
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
		
	
	
if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";
	
	// Set Default order column
	if (!($sOrderColumn)) {
		if ($sSumRejectionByValue) {
			$sOrderColumn = "errorDate";
			$sErrorDateOrder = "DESC";
		} else {
			$sOrderColumn = "errorDateTime";
			$sErrorDateTimeOrder = "DESC";
		}
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "valueInvalidated" :
			$sCurrOrder = $sValueInvalidatedOrder;
			$sValueInvalidatedOrder = ($sValueInvalidatedOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "function" :
			$sCurrOrder = $sFunctionOrder;
			$sFunctionOrder = ($sFunctionOrder != "DESC" ? "DESC" : "ASC");
			break;					
			case "ipAddress" :
			$sCurrOrder = $sIpAddressOrder;
			$sIpAddressOrder = ($sIpAddressOrder != "DESC" ? "DESC" : "ASC");
			break;	
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
			break;	
			case "errorDate" :
			$sCurrOrder = $sErrorDateOrder;
			$sErrorDateOrder = ($sErrorDateOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "noOfRejects" :
			$sCurrOrder = $sNoOfRejectsOrder;
			$sNoOfRejectsOrder = ($sNoOfRejectsOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sErrorDateTimeOrder;
			$sErrorDateTimeOrder = ($sErrorDateTimeOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		
		switch ($sSearchIn) {
			case "valueInvalidated" :
			$sFilterPart .= ($iExactMatch) ? "valueInvalidated = '$sFilter'" : "valueInvalidated like '%$sFilter%'";
			break;
			case "function" :
			$sFilterPart .= ($iExactMatch) ? "function = '$sFilter'" : "function like '%$sFilter%'";
			break;
			case "ipAddress" :
			$sFilterPart .= ($iExactMatch) ? "ipAddress = '$sFilter'" : "ipAddress like '%$sFilter%'";
			break;
			case "sourceCode" :
			$sFilterPart .= ($iExactMatch) ? "sourceCode = '$sFilter'" : "sourceCode like '%$sFilter%'";
			break;
			case "errorDateTime" :
			$sFilterPart .= ($iExactMatch) ? "errorDateTime = '$sFilter'" : "errorDateTime like '%$sFilter%'";
			break;
						
			default:
			$sFilterPart .= ($iExactMatch) ? "valueInvalidated = '$sFilter' || function = '$sFilter' || ipAddress = '$sFilter' || sourceCode = '$sFilter' || errorDateTime = '$sFilter'" : " valueInvalidated like '%$sFilter%' || function LIKE '%$sFilter%' || ipAddress like '%$sFilter%' || sourceCode like '%$sFilter%' || errorDateTime like '%$sFilter%'";
		}
		
		$sFilterPart .= ") ";
	}
	
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sExclude) {
			case "valueInvalidated" :
			$sFilterPart .= "valueInvalidated NOT LIKE '%$sExclude%'";
			break;
			case "function" :
			$sFilterPart .= "function NOT LIKE '%$sExclude%'";
			break;
			case "ipAddress" :
			$sFilterPart .= "ipAddress NOT LIKE '%$sExclude%'";
			break;
			case "sourceCode" :
			$sFilterPart .= "sourceCode NOT LIKE '%$sExclude%'";
			break;
			case "errorDateTime" :
			$sFilterPart .= "errorDateTime NOT LIKE '%$sExclude%'";
			break;
			//	case "dateLastUpdated" :
			//	$sFilterPart .= "dateLastUpdated NOT LIKE '%$sExclude%'";
			//	break;
			default:
			$sFilterPart .= "valueInvalidated NOT LIKE '%$sExclude%' && function NOT LIKE '%$sExclude%'  && ipAddress NOT LIKE '%$sExclude%' && sourceCode NOT LIKE '%$sExclude%' && errorDateTime NOT LIKE '%$sExclude%'" ;
		}
		$sFilterPart .= " ) ";
		
	}
	
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 50;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn
					&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
					&sExcludeBlankValues=$sExcludeBlankValues&iRecPerPage=$iRecPerPage&sSumRejectionByValue=$sSumRejectionByValue";

	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	
	if ($sSumRejectionByValue) {
		$sErrorLogQuery = "SELECT date_format(errorDateTime, '%Y-%m-%d') AS errorDate, valueInvalidated, count(id) as noOfRejects, function
						   FROM errorLog
						   WHERE errorDateTime BETWEEN '$sDateTimeFrom'
      					   AND '$sDateTimeTo'
						   $sFilterPart 	";
		if ($sExcludeBlankValues == 'Y') {
			$sErrorLogQuery .= " AND valueInvalidated != '' ";
		}
		
		$sErrorLogQuery .= "GROUP BY valueInvalidated";
		
	} else {
		$sErrorLogQuery = "SELECT *
						   FROM errorLog
						   WHERE errorDateTime BETWEEN '$sDateTimeFrom'
      					   AND '$sDateTimeTo'
						   $sFilterPart 	";
		if ($sExcludeBlankValues == 'Y') {
			$sErrorLogQuery .= " AND valueInvalidated != '' ";
		}
	}
	
	$sErrorLogQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sErrorLogQuery\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		

	
	$rErrorLogResult = dbQuery($sErrorLogQuery);
	//echo $sErrorLogQuery. mysql_error();

	$iNumRecords = dbNumRows($rErrorLogResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sErrorLogQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rErrorLogResult = dbQuery($sErrorLogQuery);
	echo dbError();
	if ( dbNumRows($rErrorLogResult) >0) {
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
			
		while ($oErrorLogRow = dbFetchObject($rErrorLogResult)) {
		
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}
		
			$sValueInvalidated = ascii_encode($oErrorLogRow->valueInvalidated);
			
			if ($sSumRejectionByValue) {
				$iGrossAttempts = 0;
				if ($sDateFrom != "$iCurrYear-$iCurrMonth-$iCurrDay" && $sDateTo != "$iCurrYear-$iCurrMonth-$iCurrDay") {
					$sGrossLeadsQuery = "SELECT count(*) AS grossLeads
										 FROM   otDataHistory
										 WHERE  dateTimeAdded 
												between '$sDateTimeFrom' and '$sDateTimeTo'";
					$rGrossLeadsResult = dbQuery($sGrossLeadsQuery);
					if ($rGrossLeadsResult) {
						while ($oGrossLeadsRow = dbFetchObject($rGrossLeadsResult)) {
							$iGrossLeads = $oGrossLeadsRow->grossLeads;
						}
					
						dbFreeResult($rValidatedOffersResul);
					}
				}
				
				$sGrossLeadsQuery = "SELECT count(*) AS grossLeads
									 FROM   otData
									 WHERE  dateTimeAdded 
											between '$sDateTimeFrom' and '$sDateTimeTo'";
				$rGrossLeadsResult = dbQuery($sGrossLeadsQuery);
				if ($rGrossLeadsResult) {
					while ($oGrossLeadsRow = dbFetchObject($rGrossLeadsResult)) {
						$iGrossLeads += $oGrossLeadsRow->grossLeads;
					}
					dbFreeResult($rGrossLeadsResult);
				}
				
				$iNoOfRejects = $oErrorLogRow->noOfRejects;
				$iGrossAttempts = $iGrossLeads + $iNoOfRejects;
				
				$fPercentRejects = (100 * $iNoOfRejects) / $iGrossAttempts;
				$fPercentRejects = sprintf("%6.2f",round($fPercentRejects, 2));
				
				/*$sReportData .= "<tr class=$sBgcolorClass><td>$oErrorLogRow->errorDate</td>
									<td>$sValueInvalidated</td><td>$iGrossAttempts</td>
									<td>$iNoOfRejects</td><td>$fPercentRejects</td>
									</tr>";		*/
				$sReportData .= "<tr class=$sBgcolorClass><td>$oErrorLogRow->errorDate</td>
									<td>$sValueInvalidated</td>
									<td>$iNoOfRejects</td></tr>";		
				
			} else {
				$sReportData .= "<tr class=$sBgcolorClass><td>$oErrorLogRow->errorDateTime</td>
						<td>$sValueInvalidated</td><td>$oErrorLogRow->function</td>
						<td>$oErrorLogRow->ipAddress</td><td>$oErrorLogRow->sourceCode</td></tr>";		
			}
		}								  		
	}
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
	$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR>$sPageTitle<BR>From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo<BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: $sRunDateAndTime</td></tr>";
	if ($sSumRejectionByValue) {
		$sReportContent .= "<tr class=$sBgcolorClass><td width=200><a href=\"$sSortLink&sOrderColumn=errorDate&sErrorDateOrder=$sErrorDateOrder\" class=header>Error Date/Time</a></td>
								<td width=260 class=header><a href=\"$sSortLink&sOrderColumn=valueInvalidated&sValueInvalidatedOrder=$sValueInvalidatedOrder\" class=header>Value</a></td>								
								<td><a href=\"$sSortLink&sOrderColumn=noOfRejects&sNoOfRejectsOrder=$sNoOfRejectsOrder\" class=header># Of Rejects</a></td>
									</tr>";
	} else {
		
	$sReportContent .= "<tr><td width=200><a href=\"$sSortLink&sOrderColumn=errorDateTime&sErrorDateTimeOrder=$sErrorDateTimeOrder\" class=header>Error Date/Time</a></td>
		<td width=260 class=header><a href=\"$sSortLink&sOrderColumn=valueInvalidated&sValueInvalidatedOrder=$sValueInvalidatedOrder\" class=header>Value Invalidated</a></td>
		<td width=180 class=header><a href=\"$sSortLink&sOrderColumn=function&sFunctionOrder=$sFunctionOrder\" class=header>Validation Function</a></td>
		<td width=100 class=header><a href=\"$sSortLink&sOrderColumn=ipAddress&sIpAddressOrder=$sIpAddressOrder\" class=header>IP Address</a></td>
		<td width=100 class=header><a href=\"$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder\" class=header>SourceCode</a></td>
		</tr>";
	}
	
	
	$sReportContent .= "$sReportData
	<tr><td colspan=5 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	<tr><td colspan=5>- Updated in real time.
		<BR>- Approximate time to run this report - $iScriptExecutionTime second(s)</td></tr>
	<tr><td colspan=5><BR><BR></td></tr>
		</td></tr></table></td></tr></table></td></tr>
	</table>";
}
}
		
if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}	
	
	switch ($sSearchIn) {
		case 'valudeInvalidated':
		$sValueInvalidatedSelected = "selected";
		break;
		case 'function':
		$sFunctionSelected = "selected";
		break;		
		case 'ipAddress':
		$sIpAddressSelected = "selected";
		break;
		case 'errorDateTime':
		$sErrorDateTimeSelected = "selected";
		break;	
		case 'sourceCode':
		$sSourceCodeSelected = "selected";
		break;	
		default:
		$sAllFieldsSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='' $sAllFieldsSelected>All Fields
						<option value='valueInvalidated' $sValueInvalidated>Value Invalidated
						<option value='function' $sFunctionSelected>Function
						<option value='ipAddress' $sIpAddressSelected>IP Address
						<option value='errorDateTime' $sErrorDateTimeSelected>Error Date & Time
						<option value='sourceCode' $sSourceCodeSelected>SourceCode";
	
	
	$sExcludeBlankValuesChecked = "";
	if ($sExcludeBlankValues == 'Y') {
		$sExcludeBlankValuesChecked = "checked";
	} 
	
	$sSumRejectionByValueChecked = "";
	if ($sSumRejectionByValue == 'Y') {
		$sSumRejectionByValueChecked = "checked";
	} 
	
	include("../../includes/adminHeader.php");	
		
	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
		
?>
<script language=JavaScript>
function funcRecPerPage(form1) {
					//document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}					
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center >

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	
<tr><td>Filter By</td><td colspan=3><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td colspan=3><input type=text name=exclude value='<?php echo $sExclude;?>'></td></tr>
<tr><td>Search In</td><td colspan=3><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></tr>
	<tr><td></td><td colspan=3><input type=checkbox name=sExcludeBlankValues value='Y' <?php echo $sExcludeBlankValuesChecked;?>> Exclude Blank Values 
		&nbsp; &nbsp; <input type=checkbox name=sSumRejectionByValue value='Y' <?php echo $sSumRejectionByValueChecked;?>> Rejection Summary By Value Invalidated</td>
			<td><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');"></td></tr>
	
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
	
			<?php echo $sReportContent;?>
			
</td></tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
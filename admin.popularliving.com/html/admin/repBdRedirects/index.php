<?php

/***********
Script to display Redirects Report
************/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$sPageTitle = "Redirects Reporting";

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


	
if (hasAccessRight($iMenuId) || isAdmin()) {

	// use stripslashes because value of $save contains '
	$sViewReport = stripslashes($sViewReport);
	
	// set curr date values to be selected by default
	$iCurrYear = date('Y');
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($sViewReport != "Today's Report") {
		$sReportTable = "bdRedirectsTrackingHistorySum";				
		
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
		$sReportTable = "bdRedirectsTracking";
		
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
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	
	// check if selected dates are valid dates
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {
			
		// specify default order column
		if (!($sOrderColumn)) {
			$sOrderColumn = "companyName";
			$sCompanyNameOrder = "ASC";
		}
		
		// specify current order (ASC or DESC) and reverse the order in link of that column's header
		if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
				case "companyCode" :
				$sCurrOrder = $sCompanyCodeOrder;
				$sCompanyCodeOrder = ($sCompanyCodeOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "sourceCode" :
				$sCurrOrder = $sSourceCodeOrder;
				$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "clicks" :
				$sCurrOrder = $sClicksOrder;
				$sClicksOrder = ($sClicksOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$sCurrOrder = $sCompanyNameOrder;
				$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		
		
		// Specify Page no. settings
		if (!($iRecPerPage)) {
			$iRecPerPage = 10;
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&iCompanyId=$iCompanyId&sSourceCode=$sSourceCode";
		$sSortLink .= "&sFilter=$sFilter&sSubsource=$sSubsource&sSsFilter=$sSsFilter&iRecPerPage=$iRecPerPage&sViewReport=".urlencode($sViewReport);
		
		if (!($iPage)) {
			$iPage = 1;
		}
		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage - 1;
		
		//Query to fetch only the rows to be displayed in current page
		if ($sReportTable == "bdRedirectsTracking") {
			$sSelectQuery = "SELECT companyName, $sReportTable.sourceCode, partnerCompanies.code, count(clickDate) AS clicks";
		} else {
			$sSelectQuery = "SELECT companyName, $sReportTable.sourceCode, partnerCompanies.code, ";
			
			$sSelectQuery .= "sum(clicks) AS clicks";
		}
		
		// get the subsourcecode to display if have expand subsource in report
		if ($sExpandSubsource) {
			$sSelectQuery .= ", $sReportTable.subSourceCode";
		}
		// select clickDate to be displayed if filtered for sourcecode/subsource exact match
		if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "bdRedirectsTrackingHistorySum")
		$sSelectQuery .= ", clickDate";
		
		$sSelectQuery .= " FROM $sReportTable, links, partnerCompanies
						WHERE links.sourceCode = $sReportTable.sourceCode
						AND  links.partnerId = partnerCompanies.id
						AND clickDate between '$sDateFrom' AND '$sDateTo' ";		
		
		if ($iCompanyId != '') {
			$sSelectQuery .=" AND links.partnerId = '$iCompanyId'";
		}
		if (trim($sSourceCode != '')) {
			if ($sFilter == 'startsWith') {
				$sSelectQuery .= " AND links.sourceCode LIKE '".$sSourceCode."%' ";
			} else if ($sFilter == 'exactMatch') {
				$sSelectQuery .= " AND links.sourceCode = '$sSourceCode' ";
			}
		}
		if (trim($sSsFilter !='')) {
			if ($sSsFilter == 'startsWith') {
				$sSelectQuery .= " AND $sReportTable.subSourceCode LIKE '".$sSubsource."%' ";
			} else if ($sSsFilter == 'exactMatch') {
				$sSelectQuery .= " AND $sRportTable.subSourceCode = '$sSubsource' ";
			}
		}
		// Current table is not summary table, so use group by in the query
		if ($sReportTable == "bdRedirectsTracking") {
			$sSelectQuery .= " GROUP BY companyName, links.sourceCode	";
			// If subsource display then group by subsource to get different rows according to subsource
			if ($sExpandSubsource) {
				$sSelectQuery .= ", $sReportTable.subSourceCode ";
			}
		} else {
			
			// If supress subsource with summary table, then sum clicks of different subsource
			$sSelectQuery .= " GROUP BY companyName, links.sourceCode ";
			if ($sExpandSubsource) {
				$sSelectQuery .= ", $sReportTable.subSourceCode ";
			}
			// Display datewise if it's for specific sourcecode/subsource
			if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch')
			$sSelectQuery .= ", clickDate ";
			
		}
		
		// if subsource display then allow order by sourcecode, subsource only
		if ($sExpandSubsource) {
			$sSelectQuery .= "ORDER BY $sReportTable.sourceCode, $sReportTable.subSourceCode";
		} else {
			$sSelectQuery .= " ORDER BY ".$sOrderColumn." $sCurrOrder";
		}

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase); 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sSelectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		
		
		
		// Get the total no of records and count total no of pages
		$rResult = dbQuery($sSelectQuery);
		echo dbError();
		$iNumRecords = dbNumRows($rResult);
		
		$iGrandTotalClicks = 0;
		$iTotalPages = ceil($iNumRecords/$iRecPerPage);
		if ($iNumRecords > 0) {
			$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		}
		while ($oTempRow = dbFetchObject($rResult)) {
			$iGrandTotalClicks += $oTempRow->clicks;
		}
		
		$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";		
		$rResult = dbQuery($sSelectQuery);
		//echo $sSelectQuery.mysql_error();
		$iPageTotalClicks = 0;
		if ($rResult) {
			
			if (dbNumRows($rResult) > 0) {
				
				$iPageTotalClicks = 0;
				while ($oRow = dbFetchObject($rResult)) {
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$iPageTotalClicks += $oRow->clicks;
					
					// Prepare Next/Prev/First/Last links
					if ($iTotalPages > $iPage) {
						$iNextPage = $iPage + 1;
						$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&sExpandSubsource=$sExpandSubsource' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrde&sExpandSubsource=$sExpandSubsource' class=header>Last</a>";
					}
					if ($iPage != 1) {
						$iPrevPage = $iPage - 1;
						$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&sExpandSubsource=$sExpandSubsource' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&sExpandSubsource=$sExpandSubsource' class=header>First</a>";
					}
					if ($sExpandSubsource) {
						$sSubsourceValue = "<td>$oRow->subSourceCode</td>";
					}
					// display datewise if filtered for sourcecode
					if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "bdRedirectsTrackingHistorySum")
					$sDateColumn = "<td>$oRow->clickDate</td>";
					
					if ($sOldSourceCode != $oRow->sourceCode || $sOldSourceCode == '') {
						$sReportData .= "<tr class=$sBgcolorClass><td>$oRow->companyName</td>
										<td>$oRow->code</td><td>$oRow->sourceCode</td>$sSubsourceValue
										<td>$oRow->clicks</td>$sDateColumn</tr>";										
					} else  {
						$sReportData .= "<tr class=$sBgcolorClass><tD colspan=3></td>$sSubsourceValue<td>$oRow->clicks</td>
										$sDateColumn</tr>";
					}
					$sOldSourceCode = $oRow->sourceCode;
				}
			} else {
				$sMessage = "No Records Exist...";
			}
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			if ($sExpandSubsource) {
				$iColspan = 2;
			} else {
				$iColspan = 1;
			}
			if ($sDateColumn) {
				$sDatePlaceHolder="<td></td>";
			}
			// pageTotal row
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=$iColspan></td><td colspan=2><b>Page Total Clicks</b></td><td><b>$iPageTotalClicks</b></td>$sDatePlaceHolder</tr>";
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			// grandTotal row
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=$iColspan></td><td colspan=2><b>Grand Total Clicks</b></td><td><b>$iGrandTotalClicks</b></td>$sDatePlaceHolder</tr>";
			
			dbFreeResult($rResult);
			
		} else {
			echo dbError();
		}
	}
	} else {
		$sMessage = "Please Select Valid Dates...";
	}
	
	if ($sFilter == 'startsWith') {
		$sStartsWithChecked = "CHECKED";
	} else if ($sFilter == 'exactMatch') {
		$sExactMatchChecked = "CHECKED";
	}
	
	if ($sSsFilter == 'startsWith') {
		$sSsStartsWithChecked = "CHECKED";
	} else if ($sSsFilter == 'exactMatch') {
		$sSsExactMatchChecked = "CHECKED";
	}
	
	// Prepare companyname options
	
		$sCompanyNameOptions .= "<tr><td>Company Name</td><td><select name=iCompanyId>";
		$sCompanyNameOptions .= "<option value='' selected>All";
		$sCompanyQuery = "SELECT id, companyName, code
					   FROM   partnerCompanies
					   ORDER BY companyName";
		$rCompanyResult = dbQuery($sCompanyQuery);
		
		while ( $oCompanyRow = dbFetchObject($rCompanyResult)) {
			if ($oCompanyRow->id == $iCompanyId) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
			$sCompanyNameOptions .= "<option value='".$oCompanyRow->id."' $sSelected>".$oCompanyRow->companyName." - ".$oCompanyRow->code;
		}
		$sCompanyNameOptions .= "</select></td></tr>";
	
	
	// Supress Date/Display Date  link
	if ($sExpandSubsource == '') {
		$sExpandSubsourceLink = "<a href='$sSortLink&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder&sExpandSubsource=Y'>Expand Subsource</a>";
		$sSubsourceHeader='';
	} else {
		$sExpandSubsourceLink = "<a href='$sSortLink&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>Suppress Subsource</a>";
		$sSubsourceHeader = "<td class=header>Subsource</td>";
	}
	
	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	$sLinksLink = "<a href='$sGblAdminSiteRoot/linksMgmnt/index.php?iMenuId=$iMenuId'>Links Management</a>";
	
	include("../../includes/adminHeader.php");
	
	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
				document.form1.submit();
}

</script>				
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $sLinksLink;?></td></tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	<?php echo $sCompanyNameOptions;?>
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match</td>
		</tr>
	<tr><td>Subsource</td><td colspan=3><input type=text name=sSubsource value='<?php echo $sSubsource;?>'>
	<input type='radio' name='sSsFilter' value='startsWith' <?php echo $sSsStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sSsFilter' value='exactMatch' <?php echo $sSsExactMatchChecked;?>> Exact Match</td>
		</tr>
	<tr>
<td colspan=2><br><input type=button name=sSave value='History Report' onClick="funcReportClicked('history');"> 
	&nbsp; &nbsp; &nbsp; <input type=button name=sSave value="Today's Report"  onClick="funcReportClicked('today');"> &nbsp; &nbsp; &nbsp; &nbsp; <?php echo $sExpandSubsourceLink;?></td></tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>'>Company Name</a></th>
<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=code&sCompanyCodeOrder=<?php echo $sCompanyCodeOrder;?>'>Code</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>'>Source Code</a></th>	
	<?php echo $sSubsourceHeader;?>
						<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=clicks&sClicksOrder=<?php echo $sClicksOrder;?>'>Clicks</a></th></tr>			
<?php echo $sReportData;?>
<tr><td colspan=5 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
<tr><td><?php echo $sRedirectLink;?></td></tr>
</table>
</form>			


<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}

?>
<?php

/***********
Script to display BD Pixels Report
************/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$pageTitle = "BD Pixel Reporting";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);



if (hasAccessRight($iMenuId) || isAdmin()) {
	
		if (!($sOrderColumn)) {
			$sOrderColumn = "companyName";
			$sCompanyNameOrder = "ASC";
		}
		
		switch ($sOrderColumn) {
			
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "opens" :
			$sCurrOrder = $sOpensOrder;
			$sOpensOrder = ($sOpensOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "revenue" :
			$sCurrOrder = $sRevenueOrder;
			$sRevenueOrder = ($sRevenueOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sCompanyNameOrder;
			$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&iCompanyId=$iCompanyId&sSourceCode=$sSourceCode&sFilter=$sFilter&sViewReport=".ascii_encode($sViewReport);
		
		
		
	// set curr date values to be selected by default
	$sViewReport = stripslashes($sViewReport);
	$iCurrYear = date('Y');
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($sViewReport != "Today's Report") {
		$sReportTable = "bdPixelsTrackingHistorySum";
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = substr( $sYesterday, 8, 2);
		}
				
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		}

	} else {
		$sReportTable = "bdPixelsTracking";
		
		$iYearFrom = date('Y');
		$iMonthFrom = date('m');
		$iDayFrom = date('d');

		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;
	}

	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$iValue = $i + 1;
		
		if ($iValue <10) {
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
	
	// check if the dates selected are valid dates
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {
			

		// Specify Page no. settings
		if (!($iRecPerPage)) {
			$iRecPerPage = 10;
		}
		
		if (!($iPage)) {
			$iPage = 1;
		}
		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage -1;
		
		
		// Prepare report data to display
		$sSelectQuery = "SELECT companyName, $sReportTable.sourceCode, partnerCompanies.code, sum(revenue) as revenue, ";
		if ($sReportTable == "bdPixelsTrackingHistorySum") {
			$sSelectQuery .= " sum(opens) as opens ";
		} else {
			$sSelectQuery .= " count(openDate) AS opens ";
		}
		
		// select clickDate to be displayed if filtered for sourcecode/subsource exact match
		if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "bdPixelsTrackingHistorySum")
		$sSelectQuery .= ", openDate";
		
		$sSelectQuery .= " FROM $sReportTable, links, partnerCompanies
						WHERE links.sourceCode = $sReportTable.sourceCode						
						AND  links.partnerId = partnerCompanies.id
						AND openDate between '$sDateFrom' AND '$sDateTo'";
		if ($iCompanyId != '') {
			$sSelectQuery .= " AND links.partnerId = '$iCompanyId'";
		}
		if (trim($sSourceCode != '')) {
			if ($sFilter == 'startsWith') {
				$sSelectQuery .= " AND links.sourceCode LIKE '".$sSourceCode."%' ";
			} else if ($sFilter == 'exactMatch') {
				$sSelectQuery .= " AND links.sourceCode = '$sSourceCode' ";
			}
		}
		
		$sSelectQuery .= " GROUP BY companyName, links.sourceCode	";
		// Display datewise if it's for specific sourcecode
		if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch')
			$sSelectQuery .= ", openDate ";
		$sSelectQuery .= " ORDER BY ".$sOrderColumn." $sCurrOrder";

		// start of track users' activity in nibbles 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase);
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sSelectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		
		
		
		// Get the total no of records and count total no of pages
		$rResult = mysql_query($sSelectQuery);
		echo mysql_error();
		$iNumRecords = mysql_num_rows($rResult);
		$iGrandTotalOpens = 0;
		$iGrandTotalRev = 0;
		$iTotalPages = ceil($iNumRecords/$iRecPerPage);
		if ($iNumRecords > 0)
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		while ($oTempRow = mysql_fetch_object($rResult)) {
			$iGrandTotalOpens += $oTempRow->opens;
			$iGrandTotalRev += $oTempRow->revenue;
		}
		
		$iPageTotalOpens = 0;
		$iPageTotalRev = 0;
		$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
		$rResult = mysql_query($sSelectQuery);
		echo dbError();
		if ($rResult) {
			if (mysql_num_rows($rResult) > 0) {
				
				$iTotalClicks = 0;
				while ($oRow = mysql_fetch_object($rResult)) {
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$iPageTotalOpens += $oRow->opens;
					$iPageTotalRev += $oRow->revenue;
					
					// Prepare Next/Prev/First/Last links
					if ($iTotalPages > $iPage) {
						$iNextPage = $iPage + 1;
						$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
					}
					if ($iPage != 1) {
						$iPrevPage = $iPage - 1;
						$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder' class=header>First</a>";
					}
					// display datewise if filtered for sourcecode
					if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "bdPixelsTrackingHistorySum")
						$sDateColumn = "<td>$oRow->openDate</td>";
					
					$sReportData .= "<tr class=$sBgcolorClass><td>$oRow->companyName</td>
								 <td>$oRow->code</td><td>$oRow->sourceCode</td>
								 <td>".number_format($oRow->revenue, 2)."</td>
								<td>$oRow->opens</td>$sDateColumn</tr>";										
				}
			} else {
				$sMessage = "No Records Exist...";
			}
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			if ($sDateColumn) {
				$sDatePlaceHolder="<td></td>";
			}
			
			if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "PixelsTrackingHistorySum")
				$colspan=2;
			else
				$colspan=1;
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=2></td><td><b>Page Total</b></td><td><b>".number_format($iPageTotalRev, 2)."</b></td><td><b>$iPageTotalOpens</b></td>$sDatePlaceHolder</tr>";
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=2></td><td><b>Grand Total</b></td><td><b>".number_format($iGrandTotalRev, 2)."</b></td><td><b>$iGrandTotalOpens</b></td>$sDatePlaceHolder</tr>";
			
			mysql_free_result($rResult);
			
		} else {
			echo mysql_error();
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
	
	// Prepare companyname options
	$sCompanyNameOptions .= "<option value='' selected>All";
	$sCompanyQuery = "SELECT id, companyName, code
					  FROM   partnerCompanies
					  ORDER BY companyName";
	$sCompanyResult = mysql_query($sCompanyQuery);
	
	while ( $oCompanyRow = mysql_fetch_object($sCompanyResult)) {
		if ($oCompanyRow->id == $iCompanyId) {
			$sSelected = "selected";
		} else {
			$sSelected ="";
		}
		$sCompanyNameOptions .="<option value='".$oCompanyRow->id."' $sSelected>".$oCompanyRow->companyName." - ".$oCompanyRow->code;
	}
	
	$sPartnerMgmntLink = "<a href='$sGblAdminSiteRoot/partnersMgmnt/index.php?iMenuId=14' class=header>Partner Management</a>";
	$sRedirectLink = "<a href='$sGblAdminSiteRoot/linksMgmnt/index.php?iMenuId=106' class=header>Links Management</a>";
	
	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
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

<table width=95% align=center bgcolor=c9c9c9>
<tr><td colspan=4 align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sRedirectLink;?></td></tr>
<tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?> 
	</select></td></tr>
	<tr><td>Company Name</td><td><select name=iCompanyId>
	<?php echo $sCompanyNameOptions;?>
	</select></td></tr><tr>
	<td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
	<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match</td>
		</tr><tr>
<td colspan=3><br><input type=button name=sSubmit value='History Report' onClick="funcReportClicked('history');"> 
	&nbsp; &nbsp; &nbsp; <input type=button name=sSubmit value="Today's Report" onClick="funcReportClicked('today');"></td></tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>'>Company Name</a></th>
<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=code&sCompanyCodeOrder=<?php echo $sCompanyCodeOrder;?>'>Code</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>'>Source Code</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=revenue&sRevenueOrder=<?php echo $sRevenueOrder;?>'>Revenue</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=opens&sOpensOrder=<?php echo $sOpensOrder;?>'>Opens</a></th></tr>			
<?php echo $sReportData;?>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
<tr><td align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sRedirectLink;?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><b>Query: </b></td></tr>
<tr><td><?php echo $sSelectQuery; ?></td></tr>
<tr><td><b>Notes: </b></td></tr>
<tr><td>Revenue:  Total revenue collected with this source code.</td></tr>
<tr><td>Open:  Number of times the pixel fired with this source code.</td></tr>
</table>
</form>


<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}
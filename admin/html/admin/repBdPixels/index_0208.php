<?php

/***********

Script to display BD Pixels Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$pageTitle = "BD Pixel Reporting";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	// set curr date values to be selected by default
	$sSave = stripslashes($sSave);
	$iCurrYear = date('Y');
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($sSave != "Today's Report") {
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
			default:
			$sCurrOrder = $sCompanyNameOrder;
			$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&iCompanyId=$iCompanyId&sSourceCode=$sSourceCode&sFilter=$sFilter&sSave=".ascii_encode($sSave);
		
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
		$sSelectQuery = "SELECT companyName, $sReportTable.sourceCode, partnerCompanies.code, ";
		if ($sReportTable == "bdPixelsTrackingHistorySum") {
			$sSelectQuery .= " sum(opens) as opens ";
		} else {
			$sSelectQuery .= " count(openDate) AS opens ";
		}
		
		// select clickDate to be displayed if filtered for sourcecode/subsource exact match
		if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch' && $sReportTable == "bdPixelsTrackingHistorySum")
		$sSelectQuery .= ", openDate";
		
		$sSelectQuery .= " FROM $sReportTable, campaigns, partnerCompanies
						WHERE campaigns.sourceCode = $sReportTable.sourceCode						
						AND  campaigns.partnerId = partnerCompanies.id
						AND openDate between '$sDateFrom' AND '$sDateTo'";
		if ($iCompanyId != '') {
			$sSelectQuery .= " AND campaigns.partnerId = '$iCompanyId'";
		}
		if (trim($sSourceCode != '')) {
			if ($sFilter == 'startsWith') {
				$sSelectQuery .= " AND campaigns.sourceCode LIKE '".$sSourceCode."%' ";
			} else if ($sFilter == 'exactMatch') {
				$sSelectQuery .= " AND campaigns.sourceCode = '$sSourceCode' ";
			}
		}
		
		$sSelectQuery .= " GROUP BY companyName, campaigns.sourceCode	";
		// Display datewise if it's for specific sourcecode
		if (($sSourceCode != '' || $sSubsource != '') && $sFilter == 'exactMatch')
			$sSelectQuery .= ", openDate ";
		$sSelectQuery .= " ORDER BY ".$sOrderColumn." $sCurrOrder";
		
		// Get the total no of records and count total no of pages
		$rResult = mysql_query($sSelectQuery);
		echo mysql_error();
		$iNumRecords = mysql_num_rows($rResult);
		$iGrandTotalOpens = 0;
		$iTotalPages = ceil($iNumRecords/$iRecPerPage);
		if ($iNumRecords > 0)
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		while ($oTempRow = mysql_fetch_object($rResult)) {
			$iGrandTotalOpens += $oTempRow->opens;
		}
		
		$iPageTotalOpens = 0;
		
		$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
		
		/****************
$ipQuery = "SELECT companyName, PixelsTrackingHistory.sourceCode, count( openDate ) AS opens, IPAddress
FROM PixelsTrackingHistory, RedirectsInfo, BDPartners
WHERE RedirectsInfo.sourceCode = PixelsTrackingHistory.sourceCode AND RedirectsInfo.partnerId = BDPartners.id AND openDate
BETWEEN '2003-10-21' AND '2003-10-29' AND RedirectsInfo.sourceCode = 'vcmfb102103001'
GROUP BY companyName, RedirectsInfo.sourceCode, IPAddress
ORDER BY opens LIMIT 130000, 65000";
$ipResult = mysql_query($ipQuery);

$numRecords = mysql_num_rows($ipResult);
		
		
			//$exportData = "<table>";			
			while ($oRow = mysql_fetch_object($ipResult)) {
				$exportData .= "$row->sourceCode\t$row->openDate\t$row->IPAddress\t$row->opens\t\n";
				$totalOpens += $row->opens;
			}
			$exportData .= "Total\t$totalOpens\t";
			//$exportData .= "</table>";
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=myExcelDoc.xls");
			header("Content-Description: Excel output");
			echo $exportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
					/**************************/
			
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
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=2></td><td><b>Page Total Opens</b></td><td><b>$iPageTotalOpens</b></td>$sDatePlaceHolder</tr>";
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sReportData .= "<tr class=$sBgcolorClass><td colspan=2></td><td><b>Grand Total Opens</b></td><td><b>$iGrandTotalOpens</b></td>$sDatePlaceHolder</tr>";
			
			mysql_free_result($rResult);
			
		} else {
			echo mysql_error();
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
	$sRedirectLink = "<a href='$sGblAdminSiteRoot/campaignsMgmnt/index.php?iMenuId=106' class=header>Campaigns Management</a>";
	
	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	include("../../includes/adminHeader.php");
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
				document.form1.submit();
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
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
<td colspan=3><br><input type=submit name=sSave value='History Report'> &nbsp; &nbsp; &nbsp; <input type=submit name=sSave value="Today's Report"></td></tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>'>Company Name</a></th>
<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=code&sCompanyCodeOrder=<?php echo $sCompanyCodeOrder;?>'>Code</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>'>Source Code</a></th>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=opens&sOpensOrder=<?php echo $sOpensOrder;?>'>Opens</a></th></tr>			
<?php echo $sReportData;?>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
<tr><td  align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sRedirectLink;?></td></tr>
</table>
</form>			


<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}
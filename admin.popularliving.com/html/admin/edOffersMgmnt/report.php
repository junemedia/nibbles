<?php

/***********

Script to display Offer Redirects Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Nibbles Offer Redirects Reporting";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// set curr date values to be selected by default
	$currYear = date('Y');
	$yesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	// Strip slashes because of ' in the value
	$save = stripslashes($save);
	
	$currYear = date('Y');
	$yesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($save != "Today's Report") {
		$reportTable = "edOfferRedirectsTrackingHistorySum";
		
		/*if ((date('Y')."-".date('m')."-".date('d') == $yearFrom."-".$monthFrom."-".$dayFrom) || $yearFrom =='') {
			$yearFrom = substr( $yesterday, 0, 4);
			$monthFrom = substr( $yesterday, 5, 2);
			$dayFrom = substr( $yesterday, 8, 2);
		}*/
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$monthFrom,$dayFrom,$yearFrom)) >= 0 || $yearFrom=='') {
			$yearFrom = substr( $yesterday, 0, 4);
			$monthFrom = substr( $yesterday, 5, 2);
			$dayFrom = substr( $yesterday, 8, 2);
		}
				
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$monthTo,$dayTo,$yearTo)) >= 0 || $yearTo=='') {
			$yearTo = substr( $yesterday, 0, 4);
			$monthTo = substr( $yesterday, 5, 2);
			$dayTo = substr( $yesterday, 8, 2);
		}
		
		
	} else {
		$reportTable = "edOfferRedirectsTracking";
		
		$yearFrom = date('Y');
		$monthFrom = date('m');
		$dayFrom = date('d');
		
		$monthTo = $monthFrom;
		$dayTo = $dayFrom;
		$yearTo = $yearFrom;
	}
	
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $monthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $monthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$monthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$monthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $dayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $dayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$dayFromOptions .= "<option value='$value' $fromSel>$i";
		$dayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	for ($i = $currYear; $i >= $currYear-5; $i--) {
		
		if ($i == $yearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $yearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$yearFromOptions .= "<option value='$i' $fromSel>$i";
		$yearToOptions .= "<option value='$i' $toSel>$i";
	}
	
	$dateFrom = "$yearFrom-$monthFrom-$dayFrom";
	$dateTo = "$yearTo-$monthTo-$dayTo";
	
	// check selected dates are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		if (!($orderColumn)) {
			$orderColumn = "companyName";
			$companyNameOrder = "ASC";
		}
		if (!($currOrder)) {
			switch ($orderColumn) {
				case "companyCode" :
				$currOrder = $companyCodeOrder;
				$companyCodeOrder = ($companyCodeOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "offerCode" :
				$currOrder = $offerCodeOrder;
				$offerCodeOrder = ($offerCodeOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "clicks" :
				$currOrder = $clicksOrder;
				$clicksOrder = ($clicksOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$currOrder = $companyNameOrder;
				$companyNameOrder = ($companyNameOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		
		// Specify Page no. settings
		if (!($recPerPage)) {
			$recPerPage = 10;
		}
		$sortLink = $PHP_SELF."?iMenuId=$iMenuId&reportMenuId=$reportMenuId&reportMenuFolder=$reportMenuFolder&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .= "&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&companyId=$companyId&offerCode=$offerCode&filter=$filter&expandSubsource=$expandSubsource&recPerPage=$recPerPage&save=".urlencode($save);
		
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage - 1;
		
		//Query to fetch only the rows to be displayed in current page
		if ($reportTable == "edOfferRedirectsTracking") {
			$selectQuery = "SELECT companyName, code, edOffers.headline, $reportTable.offerCode, count(clickDate) AS clicks  ";
		} else {
			$selectQuery = "SELECT companyName, code, edOffers.headline, $reportTable.offerCode, ";
			//if ($expandSubsource)
			//$selectQuery .= "sum(clicks) AS clicks";
			//else
			$selectQuery .= "sum(clicks) AS clicks";
		}
		
		// get the subsourcecode to display if have expand subsource in report
		if ($expandSubsource) {
			$selectQuery .= ", $reportTable.subsource";
		}
		// select clickDate to be displayed if filtered for sourcecode/subsource exact match
		if (($offerCode != '' || $subsource != '') && $filter == 'exactMatch' && $reportTable == "edOfferRedirectsTrackingHistorySum")
		$selectQuery .= ", clickDate";
		
		$selectQuery .= " FROM $reportTable, edOffers, edOfferCompanies
						WHERE edOffers.offerCode = $reportTable.offerCode						
						AND  edOffers.companyId = edOfferCompanies.id
						AND clickDate between '$dateFrom' AND '$dateTo' ";		
		
		if ($companyId != '') {
			$selectQuery .=" AND edOffers.companyId = '$companyId'";
		}
		
		if (trim($offerCode != '')) {
			if ($filter == 'startsWith') {
				$selectQuery .= " AND edOffers.offerCode LIKE '".$offerCode."%' ";
			} else if ($filter == 'exactMatch') {
				$selectQuery .= " AND edOffers.offerCode = '$offerCode' ";
			}
		}
		
		if (trim($ssFilter !='')) {
			if ($ssFilter == 'startsWith') {
				$selectQuery .= " AND $reportTable.subsource LIKE '".$subsource."%' ";
			} else if ($ssFilter == 'exactMatch') {
				$selectQuery .= " AND $reportTable.subsource = '$subsource' ";
			}
		}
		
		// Current table is not summary table, so use group by in the query
		if ($reportTable == "edOfferRedirectsTracking") {
			$selectQuery .= " GROUP BY companyName, edOffers.offerCode	";
			// If subsource display then group by subsource to get different rows according to subsource
			if ($expandSubsource) {
				$selectQuery .= ", $reportTable.subsource ";
			}
		} else {
			
			// If supress subsource with summary table, then sum clicks of different subsource
			$selectQuery .= " GROUP BY companyName, $reportTable.offerCode ";
			if ($expandSubsource) {
				$selectQuery .= ", $reportTable.subsource ";
			}
			// Display datewise if it's for specific offercode/subsource
			if (($offerCode != '' || $subsource != '') && $filter == 'exactMatch') {
				$selectQuery .= ", clickDate ";
			}
		}
		
		// if subsource display then allow order by sourcecode, subsource only
		if ($expandSubsource) {
			$selectQuery .= "ORDER BY substring(edOffers.offerCode,1,3) $currOrder, substring(edOffers.offerCode,4)+0 $currOrder, $reportTable.subsource";
		} else {
			if ($orderColumn == 'offerCode') {
				$selectQuery .= " ORDER BY substring(edOffers.offerCode,1,3) $currOrder, substring(edOffers.offerCode,4)+0 $currOrder ";
			} else {
				$selectQuery .= " ORDER BY $orderColumn $currOrder ";
			}
			//		$selectQuery .= " ORDER BY ".$orderColumn." $currOrder";
		}
		
		// Get the total no of records and count total no of pages

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		//echo $selectQuery;
		$result = mysql_query($selectQuery);
		$numRecords = mysql_num_rows($result);
		
		if ($export) {
			//$exportData = "<table>";			
			while ($row = mysql_fetch_object($result)) {
				$exportData .= "$row->offerCode\t$row->clicks\t$row->clickDate\n";
				$totalClicks += $row->clicks;
			}
			$exportData .= "Total\t$totalClicks\t";
			//$exportData .= "</table>";
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=myExcelDoc.xls");
			header("Content-Description: Excel output");
			echo $exportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
			
		}
		
		$grandTotalClicks = 0;
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0) {
			$currentPage = " Page $page "."/ $totalPages";
		}
		while ($tempRow = mysql_fetch_object($result)) {
			$grandTotalClicks += $tempRow->clicks;
		}
		
		$selectQuery .= " LIMIT $startRec, $recPerPage";
		$result = mysql_query($selectQuery);
		
		$pageTotalClicks = 0;
		
		if ($result) {
			
			if (mysql_num_rows($result) > 0) {
				
				// Prepare Next/Prev/First/Last links
				if ($totalPages > $page) {
					$nextPage = $page+1;
					$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
					$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
				}
				if ($page!=1) {
					$prevPage = $page-1;
					$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
					$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
				}
				
				
				$totalClicks = 0;
				while ($row = mysql_fetch_object($result)) {
					
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					
					$pageTotalClicks += $row->clicks;
					
					
					if ($expandSubsource) {
						$subsourceValue = "<td>$row->subsource</td>";
					}
					// display datewise if filtered for sourcecode
					if (($offerCode != '' || $subsource != '') && $filter == 'exactMatch' && $reportTable == "edOfferRedirectsTrackingHistorySum")
					$dateColumn = "<td>$row->clickDate</td>";
					
					if ($oldOfferCode != $row->offerCode || $oldOfferCode == '') {
						$reportData .= "<tr class=$bgcolorClass><td>$row->companyName</td>
										<td>$row->code</td><td>$row->offerCode</td>$subsourceValue										
										<td>$row->clicks</td>$dateColumn</tr>";										
					} else  {
						$reportData .= "<tr class=$bgcolorClass><tD colspan=3></td>$subsourceValue<td>$row->clicks</td>
										$dateColumn</tr>";
					}
					$oldOfferCode = $row->offerCode;
				}
			} else {
				$sMessage = "No Records Exist...";
			}
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			if ($expandSubsource) {
				$colspan = 2;
			} else {
				$colspan = 1;
			}
			if ($dateColumn) {
				$datePlaceHolder="<td></td>";
			}
			// pageTotal row
			$reportData .= "<tr class=$bgcolorClass><td colspan=$colspan></td><td colspan=2><b>Page Total Clicks</b></td><td><b>$pageTotalClicks</b></td>$datePlaceHolder</tr>";
			
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			// grandTotal row
			$reportData .= "<tr class=$bgcolorClass><td colspan=$colspan></td><td colspan=2><b>Grand Total Clicks</b></td><td><b>$grandTotalClicks</b></td>$datePlaceHolder</tr>";
			
			mysql_free_result($result);
			
		} else {
			echo mysql_error();
		}
	} else {
		$sMessage = "Please Select Valid Dates...";
	}
	
	if ($filter == 'startsWith') {
		$startsWithChecked = "CHECKED";
	} else if ($filter == 'exactMatch') {
		$exactMatchChecked = "CHECKED";
	}
	
	if ($ssFilter == 'startsWith') {
		$ssStartsWithChecked = "CHECKED";
	} else if ($ssFilter == 'exactMatch') {
		$ssExactMatchChecked = "CHECKED";
	}
	
	// Prepare companyname options
	$companyNameOptions .= "<option value='' selected>All";
	$companyQuery = "select id, companyName, code
				from edOfferCompanies
				order by companyName";
	$companyResult = mysql_query($companyQuery);
	
	while ( $companyRow = mysql_fetch_object($companyResult)) {
		if ($companyRow->id == $companyId) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$companyNameOptions .= "<option value='".$companyRow->id."' $selected>".$companyRow->companyName." - ".$companyRow->code;
	}
	// Supress Date/Display Date  link
	if ($expandSubsource == '') {
		$expandSubsourceLink = "<a href='$sortLink&expandSubsource=Y'>Expand Subsource</a>";
		$subsourceHeader='';
	} else {
		$expandSubsourceLink = "<a href='$sortLink'>Suppress Subsource</a>";
		$subsourceHeader = "<td class=header>Subsource</td>";
	}
	
	if ($reportMenuId) {
		$hidden .=  "<input type=hidden name=reportMenuId value='$reportMenuId'>
					 <input type=hidden name=reportMenuFolder value='$reportMenuFolder'>";
		$redirectsLink = "<a href='../$reportMenuFolder/index.php?iMenuId=$reportMenuId'>Back to Offer Reports Menu</a>";
	} else {
		
		$redirectsLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Offer Management</a>
					&nbsp; &nbsp;<a href='pixelReport.php?iMenuId=$iMenuId'>Offer Pixels Report</a>
					&nbsp; &nbsp;<a href='offersExpiringReport.php?iMenuId=$iMenuId'>Offers Expiring Report</a>
					&nbsp; &nbsp;<a href='orphanOffersReport.php?iMenuId=$iMenuId'>Orphan Offers Report</a>
					&nbsp; &nbsp;<a href='deactOffersReport.php?iMenuId=$iMenuId'>Deactivated Offers Report</a>
					&nbsp; &nbsp;<a href='JavaScript:void(window.open(\"frameMgmnt.php?iMenuId=$iMenuId\",\"\",\"\"))'>Frame Managemnt</a>";
	}
	
	$exportToExcelLink = "<a href='$sortLink&export=export'>Export To excel</a>";
	
	// Hidden variable to be passed with form submit
	$hidden .=  "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	include("../../includes/adminHeader.php");	
	
?>
<script language=JavaScript>
function funcRecPerPage(form1) {
				document.form1.submit();
}
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td colspan=5><?php echo $redirectsLink;?></td></tr>
	<td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>
	<tr><td>Company Name</td><td colspan=3><select name=companyId>
	<?php echo $companyNameOptions;?>
	</select></td></tr><tr>
	<td>Offer Code</td><td colspan=3><input type=text name=offerCode value='<?php echo $offerCode;?>'>
	<input type='radio' name='filter' value='startsWith' <?php echo $startsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='filter' value='exactMatch' <?php echo $exactMatchChecked;?>> Exact Match</td>
		</tr>
		<tr><td>Subsource</td><td colspan=3><input type=text name=subsource value='<?php echo $subsource;?>'>
	<input type='radio' name='ssFilter' value='startsWith' <?php echo $ssStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='ssFilter' value='exactMatch' <?php echo $ssExactMatchChecked;?>> Exact Match</td>
		</tr>
		<tr><td colspan=4><br><input type=submit name=save value='History Report'> &nbsp; &nbsp; &nbsp; <input type=submit name=save value="Today's Report"> &nbsp; &nbsp; &nbsp; &nbsp; <?php echo $expandSubsourceLink;?> &nbsp; &nbsp; &nbsp; <?php echo $exportToExcelLink;?></td></tr>

			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><th align=left><a href='<?php echo $sortLink;?>&orderColumn=companyName&companyNameOrder=<?php echo $companyNameOrder;?>'>Company Name</a></th>
<th align=left><a href='<?php echo $sortLink;?>}&orderColumn=code&companyCodeOrder=<?php echo $companyCodeOrder;?>'>Code</a></th>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>'>Offer Code</a></th>
		<?php echo $subsourceHeader;?>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=clicks&clicksOrder=<?php echo $clicksOrder;?>'>Clicks</a></th></tr>			
<?php echo $reportData;?>
<tr><td colspan=4 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><td colspan=5><?php echo $redirectsLink;?></td></tr>
</table>
</form>		

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
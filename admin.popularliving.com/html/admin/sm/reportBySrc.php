<?php

/***********

Script to display You Won Report

************/
include("../../library.php");
include("../../includes/template.php");

$pageTitle = "Show Me Reporting -  Report By SourceCode";

session_start();
if (session_is_registered("marsUserId") && ($marsAccessRights[$menuId]=='Y' || $marsLevel == 'admin')) {
	//&& $marsPermissions[$menuId]['perView']=='Y'
	// Create the template object
	$t = new Template($marsWebRoot,"comment");
	
	// Get the folder of this menu
	$menuFolder = $marsMenuFolder[$menuId]['link'];
	
	$t->set_file(array("main" => "main.phtml",
	"content" => "$menuFolder/reportBySrc.phtml"));
	
	
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($submit)) {
		$monthFrom = $currMonth;
		$monthTo = $currMonth;
		$dayFrom = "01";
		$dayTo = $currDay;
		$yearFrom = $currYear;
		$yearTo = $currYear;
	}
		
	// prepare month options for From and To date
	for ($i = 0; $i < count($monthArray); $i++) {
		if ($i < 10) {
			$value ="0".$i+1;
		} else {
			$value =$i+1;
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
		
		$monthFromOptions .= "<option value='$value' $fromSel>$monthArray[$i]";
		$monthToOptions .= "<option value='$value' $toSel>$monthArray[$i]";
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
	
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {		
		
		if (!($orderColumn)) {
			$orderColumn = "sourceCode";
			$sourceCodeOrder = "DESC";
		}
		switch ($orderColumn) {
			
			case "title" :
			$currOrder = $titleOrder;
			$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "displayCounts" :
			$currOrder = $displayCountsOrder;
			$displayCountsOrder = ($displayCountsOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "displayCounts" :
			$currOrder = $displayCountsOrder;
			$displayCountsOrder = ($displayCountsOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "pickCounts" :
			$currOrder = $pickCountsOrder;
			$pickCountsOrder = ($pickCountsOrder != "DESC" ? "DESC" : "ASC");
			break;						
			default:
			$currOrder = $sourceCodeOrder;
			$sourceCodeOrder = ($sourceCodeOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sortLink = $PHP_SELF."?monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .="&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&submit=ViewReport";
		
		// Specify Page no. settings
		$recPerPage = 5;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;		
		
		// Prepare report data to display
		$selectQuery = "SELECT b.sourceCode, offerId, sum(displayCounts) displayCounts,
							   sum(pickCounts) pickCounts, c.title
						FROM   ShowMeTracking b, ShowMeOffers c
						WHERE  b.offerId = c.id 
						AND    b.dateShown >= '$dateFrom'
						AND    b.dateShown <= '$dateTo'";		
		
		$selectQuery .= " GROUP BY b.sourceCode, offerId ";
		$selectQuery .= " ORDER BY b.sourceCode, title";
		
		// Get the total no of records and count total no of pages
		$result = mysql_query($selectQuery);
		echo mysql_error();
		$numRecords = mysql_num_rows($result);
		$grandTotalPageCounts = 0;
		$grandTotalDisplayCounts = 0;
		$grandTotalPickCounts = 0;
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0)
			$currentPage = " Page $page "."/ $totalPages";
		while ($tempRow = mysql_fetch_object($result)) {
			$grandTotalDisplayCounts += $tempRow->displayCounts;
			$grandTotalPickCounts += $tempRow->pickCounts;			
			//if ($prevSrc != $tempRow->sourceCode || $prevSrc == '') 
				$grandTotalPageCounts += $tempRow->pageCounts;
			//$prevSrc = $tempRow->sourceCode;
		}				
		
		$pageTotalPageCounts = 0;
		$pageTotalDisplayCounts = 0;
		$pageTotalPickCounts = 0;
		//$prevSrc = '';
		$selectQuery .= " LIMIT $startRec, $recPerPage";
		$result = mysql_query($selectQuery);
		
		if ($result) {
			
			$numRecords = mysql_num_rows($result);
			if ($numRecords > 0) {
				
				$totalCounts=0;
				while ($row = mysql_fetch_object($result)) {
					
					if ($bgcolorClass=="ODD") {
						$bgcolorClass="EVEN";
					} else {
						$bgcolorClass="ODD";
					}
					
					$pageTotalDisplayCounts += $row->displayCounts;
					$pageTotalPickCounts += $row->pickCounts;
					//if ($prevSrc != $row->sourceCode || $prevSrc == '') 
						$pageTotalPageCounts += $row->pageCounts;
					//$prevSrc = $row->sourceCode;
					
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
					$offerTitle = ascii_encode(substr($row->title,0,25))."...";						
															
					$reportData .="<tr class=$bgcolorClass><td>$row->sourceCode</td>
								 <td>$offerTitle</td><td>$row->displayCounts</td>
								<td>$row->pickCounts</td></tr>";										
				}
			} else {
				$message = "No records exist...";
			}
			if ($bgcolorClass=="ODD") {
				$bgcolorClass="EVEN";
			} else {
				$bgcolorClass="ODD";
			}			
			$reportData .="<tr class=$bgcolorClass><td><b>Page Total</b></td><td></td><td><b>$pageTotalDisplayCounts</b></td><td><b>$pageTotalPickCounts<b></td></tr>";
			
			if ($bgcolorClass=="ODD") {
				$bgcolorClass="EVEN";
			} else {
				$bgcolorClass="ODD";
			}
			$reportData .="<tr class=$bgcolorClass><td><b>Grand Total</b></td><td></td><td><b>$grandTotalDisplayCounts</b></td><td><b>$grandTotalPickCounts</b></td></tr>";
			
			mysql_free_result($result);
			
		} else {
			echo mysql_error();
		}
	} else {
		$message = "Please select valid dates...";
	}	
	
	$sortLink = $PHP_SELF."?monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
	$sortLink .="&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&submit=ViewReport";	
	
	$hidden = "<input type=hidden name=menuId value='$menuId'>";
	$showMeAdminLink = "<a href='index.php?menuId=$menuId'>Back To ShowMe Admin</a>";

	$t->set_var(array(  "ACTION" => $PHP_SELF,
		"HIDDEN" => "$hidden",	
		"SHOWME_ADMIN_LINK" => "$showMeAdminLink",
		"SOURCE_CODE_COLUMN" => "$sourceCodeColumn",
		"OFFER_COLUMN" => "$offerColumn",
		"EXPAND_SOURCE_LINK" => "$expandSourceLink",
		"EXPAND_OFFER_LINK" => "$expandOfferLink",
		"REPORT_DATA" => "$reportData",
		"MONTH_FROM_OPTIONS"=>"$monthFromOptions",
		"DAY_FROM_OPTIONS"=>"$dayFromOptions",
		"YEAR_FROM_OPTIONS"=>"$yearFromOptions",
		"MONTH_TO_OPTIONS"=>"$monthToOptions",
		"DAY_TO_OPTIONS"=>"$dayToOptions",
		"YEAR_TO_OPTIONS"=>"$yearToOptions"	
		));	
						
	include("../mainParse.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
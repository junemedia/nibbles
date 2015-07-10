<?php

/***********

Script to display Recovery Report

************/

include("../../library.php");
include("../../includes/template.php");

$pageTitle = "Recovery System Reporting";

session_start();

if (session_is_registered("marsUserId") && ($marsAccessRights[$menuId]=='Y' || $marsLevel == 'admin')) {
	
	// Create the template object
	$t = new Template($marsWebRoot,"comment");
	
	// Get the folder of this menu
	$menuFolder = $marsMenuFolder[$menuId]['link'];
	
	// set template files
	$t->set_file(array("main" => "main.phtml",
	"content" => "$menuFolder/index.phtml"));
	
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
		$value = $i + 1;
		if ($value < 10) {
			$value ="0".$value;
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
		
		$value  = $i;
		if ($i < 10) {
			$value = "0".$value;
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
	
	// prepare year options for From and To date
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
	
	// check if selected dates are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		if (!($orderColumn)) {
			$orderColumn = "dateAttempted";
			$dateAttemptedOrder = "DESC";
		}
		if (!($currOrder)) {
			
			switch ($orderColumn) {
				
				case "dateAttempted" :
				$currOrder = $dateAttemptedOrder;
				$dateAttemptedOrder = ($dateAttemptedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "totalAttempted" :
				$currOrder = $totalAttemptedOrder;
				$totalAttemptedOrder = ($totalAttemptedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "valid" :
				$currOrder = $validOrder;
				$validOrder = ($validOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "invalid" :
				$currOrder = $invalidOrder;
				$invalidOrder = ($invalidOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "unknown" :
				$currOrder = $unknownOrder;
				$unknownOrder = ($unknownOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$currOrder = $dateAttemptedOrder;
				$dateAttemptedOrder = ($dateAttemptedOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		$sortLink = $PHP_SELF."?menuId=$menuId&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .= "&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&submit=ViewReport";
		
		// Specify Page no. settings
		$recPerPage = 10;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;
		// Count no of remaining to be verified
		$countQuery = "SELECT count(*) AS totalRemaining
					   FROM   Recovery";
		$countResult = mysql_query($countQuery);
		
		while ($row =  mysql_fetch_object($countResult)) {
			$remainingToVerify = $row->totalRemaining;
		}
		
		// Prepare report data to display
		$selectQuery = "SELECT count(dateAttempted) totalRecords, sum(valid) grandTotalValid,
								sum(invalid) grandTotalInvalid, sum(unknown) grandTotalUnknown,
								sum(newAdded) grandTotalNewAdded							   
						FROM   RecoveryStats
						WHERE  dateAttempted >= '$dateFrom'
						AND    dateAttempted <= '$dateTo'";						
		// Get the total no of records and count total no of pages
		$result = mysql_query($selectQuery);
		
		$grandTotalValid = 0;
		$grandTotalInvalid = 0;
		$grandTotalUnknown = 0;
		$grandTotalAttempted = 0;
		$grandTotalNewAdded = 0;
		while ($tempRow = mysql_fetch_object($result)) {
			
			$numRecords = $tempRow->totalRecords;
			$grandTotalValid = $tempRow->grandTotalValid;
			$grandTotalInvalid = $tempRow->grandTotalInvalid;
			$grandTotalUnknown = $tempRow->grandTotalUnknown;
			$grandTotalAttempted = $grandTotalValid + $grandTotalInvalid + $grandTotalUnknown;
			$grandTotalNewAdded = $tempRow->grandTotalNewAdded;
		}
		
		$totalPages = ceil($numRecords/$recPerPage);
		$currentPage = " Page $page "."/ $totalPages";
				
		// get grandTotalResponses
		$selectQuery = "SELECT dateAttempted, valid, invalid, unknown, valid+invalid+unknown AS totalAttempted, startingRecords, newAdded
						  FROM   RecoveryStats
						  WHERE  dateAttempted >= '$dateFrom'
						  AND    dateAttempted <= '$dateTo'";				
		
		$selectQuery .= " ORDER BY ".$orderColumn." $currOrder
						 LIMIT $startRec, $recPerPage";
		$result = mysql_query($selectQuery);
		
		$pageTotalAttempted = 0;
		$pageTotalValid = 0;
		$pageTotalInvalid = 0;
		$pageTotalUnknown = 0;
		$pageTotalNewAdded = 0;
		
		
		if (mysql_num_rows($result) == 0) {
			$message = "No Records Exist...";
		} else {
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				// Prepare Next/Prev/First/Last links
				if ($totalPages > $page) {
					$nextPage = $page + 1;
					$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
					$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
				}
				if ($page != 1) {
					$prevPage = $page - 1;
					$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
					$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
				}
				
				$pageTotalValid += $row->valid;
				$pageTotalInvalid += $row->invalid;
				$pageTotalUnknown += $row->unknown;
				$pageTotalAttempted = $pageTotalValid + $pageTotalInvalid + $pageTotalUnknown;
				$pageTotalNewAdded += $row->newAdded;			
				$percentageValid = 0;
				$percentageInvalid = 0;
				$percentageUnknown = 0;
				
				$totalAttempted = $row->valid + $row->invalid + $row->unknown;
				if ($totalAttempted != 0) {
					$percentageValid = round((100 * $row->valid)/($totalAttempted),2);
					$percentageInvalid = round((100 * $row->invalid)/($totalAttempted),2);					
					$percentageUnknown = 100 - $percentageValid - $percentageInvalid;
				}
				
				$reportData .= "<tr class=$bgcolorClass><td>$row->dateAttempted</td>
								<td>$totalAttempted</td>
								 <td>$row->valid</td><td>$row->invalid</td>
								<td>$row->unknown</td><td>$percentageValid</td>
								<td>$percentageInvalid</td>
								<td>$percentageUnknown</td>
								<td>$row->newAdded</td></tr>";				
			}
		}
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
		
		if ($pageTotalAttempted != '0') {
			$percentageValid = round((100 * $pageTotalValid)/($pageTotalAttempted), 2);
			$percentageInvalid = round((100 * $pageTotalInvalid)/($pageTotalAttempted), 2);
			$percentageUnknown =  100 - $percentageValid - $percentageInvalid;
		}
		$reportData .= "<tr class=$bgcolorClass><td><b>Page Total</b></td><td><b>$pageTotalAttempted</b></td><td><b>$pageTotalValid</b></td>
							<td><b>$pageTotalInvalid</b></td><td><b>$pageTotalUnknown</b></td>
							<td><b>$percentageValid</b></td><td><b>$percentageInvalid<b></td>
							<td><b>$percentageUnknown<b></td><td><b>$pageTotalNewAdded</b></td></tr>";
		
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
		if ($grandTotalAttempted != '0') {
			$percentageValid = round((100 * $grandTotalValid)/($grandTotalAttempted), 2);
			$percentageInvalid = round((100 * $grandTotalInvalid)/($grandTotalAttempted), 2);
			$percentageUnknown = 100 - $percentageValid - $percentageInvalid;
		}
		$reportData .= "<tr class=$bgcolorClass><td><b>Grand Total</b></td><td><b>$grandTotalAttempted</b></td>
						<td><b>$grandTotalValid</b></td><td><b>$grandTotalInvalid</b></td>
						<td><b>$grandTotalUnknown</b></td><td><b>$percentageValid</b></td>
						<td><b>$percentageInvalid</b></td><td><b>$percentageUnknown</b></td>
						<td><b>$grandTotalNewAdded</b></td></tr>";
		
		mysql_free_result($result);
		
	} else {
		$message = "Please Select Valid Dates...";
	}
	
	
	$sortLink = $PHP_SELF."?menuId=$menuId&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
	$sortLink .= "&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&submit=ViewReport";
	
	// Hidden variable to be passed with form submit
	$hidden =  "<input type=hidden name=menuId value='$menuId'>";
	
	// Parse variables in Template
	$t->set_var(array(  "ACTION"=>"$PHP_SELF",
	"HIDDEN"=>"$hidden",
	"SORT_LINK" => "$sortLink",
	"NEXT_PAGE_LINK" => "$nextPageLink",
	"PREV_PAGE_LINK" => "$prevPageLink",
	"FIRST_PAGE_LINK" => "$firstPageLink",
	"LAST_PAGE_LINK" => "$lastPageLink",
	"CURRENT_PAGE" => "$currentPage",
	"MONTH_FROM_OPTIONS"=>"$monthFromOptions",
	"DAY_FROM_OPTIONS"=>"$dayFromOptions",
	"YEAR_FROM_OPTIONS"=>"$yearFromOptions",
	"MONTH_TO_OPTIONS"=>"$monthToOptions",
	"DAYS_TO_OPTIONS"=>"$dayToOptions",
	"YEAR_TO_OPTIONS"=>"$yearToOptions",
	"DATE_ATTEMPTED_ORDER"=>"$dateAttemptedOrder",
	"TOTAL_ATTEMPTED_ORDER"=>"$totalAttemptedOrder",
	"VALID_ORDER" => "$validOrder",
	"INVALID_ORDER" => "$invalidOrder",
	"REPORT_DATA"=>"$reportData",
	"REMAINING_TO_VERIFY" => "$remainingToVerify"
	));
	
	// Include common steps to parse common variables
	// and steps to display the final template
	include("../mainParse.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
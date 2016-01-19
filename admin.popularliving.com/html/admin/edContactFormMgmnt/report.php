<?php

/***********

Script to display Contact Form Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Editor's Contact Form Reporting";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($sSave)) {
		$monthFrom = $currMonth;
		$monthTo = $currMonth;
		$dayFrom = "01";
		$dayTo = $currDay;
		$yearFrom = $currYear;
		$yearTo = $currYear;
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$value = $i+1;
		if ($i < 10) {
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
	
	// check if dates are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		if (!($orderColumn)) {
			$orderColumn = "formName";
			$formNameOrder = "ASC";
		}
		if (!($currOrder)) {
			// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
			switch ($orderColumn) {
				case "dateSubmitted":
				$currOrder = $dateSubmittedOrder;
				$dateSubmittedOrder = ($dateSubmittedOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "counts":
				$currOrder = $countsOrder;
				$countsOrder = ($countsOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$currOrder = $formNameOrder;
				$formNameOrder = ($formNameOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		
		$sortLink = $PHP_SELF."?iMenuId=$iMenuId&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .= "&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&formId=$formId&submit=ViewReport";
		
		// Specify Page no. settings
		$recPerPage = 10;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage - 1;
		
		$selectQuery = "SELECT edContactFormStats.*, edContactForms.formName
						FROM edContactFormStats, edContactForms
						WHERE edContactFormStats.contactFormId = edContactForms.id
						AND dateSubmitted >= '$dateFrom'
						AND dateSubmitted <= '$dateTo'";
		
		if ($formId != '') {
			$selectQuery .=" AND edContactForms.id = '$formId'";
		}
		
		$selectQuery .= " ORDER BY ".$orderColumn." $currOrder";
		
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		
		// Get the total no of records and count total no of pages
		$result = mysql_query($selectQuery);
		$numRecords = mysql_num_rows($result);
		$grandTotalCounts = 0;
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0) {
			$currentPage = " Page $page "."/ $totalPages";
		}
		while ($tempRow = mysql_fetch_object($result)) {
			$grandTotalCounts += $tempRow->counts;
		}
		
		$selectQuery .= " LIMIT $startRec, $recPerPage";
		
		$pageTotalCounts = 0;				
		
		$result = mysql_query($selectQuery);
				
		if ($result) {					
			if (mysql_num_rows($result) > 0) {				
				while ($row = mysql_fetch_object($result)) {
					
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					
					$pageTotalCounts += $row->counts;
					
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

					$reportData .="<tr class=$bgcolorClass><td>$row->formName</td>
									<td>$row->dateSubmitted</td>
							<td>$row->counts</td></tr>";										
				}
			} else {
				$sMessage = "No Records Exist...";
			}
			
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			
			$reportData .= "<tr class=$bgcolorClass><td></td><td><b>Page Total Counts</b></td><td><b>$pageTotalCounts</b></td></tr>";
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			$reportData .= "<tr class=$bgcolorClass><td></td><td><b>Grand Total Counts</b></td><td><b>$grandTotalCounts</b></td></tr>";			
		} else {
			echo mysql_error();
		}
	} else {
		$sMessage = "Please Select Valid Dates...";
	}
	
	
	// Prepare formName options for selection box, to filter report
	$formNameOptions .= "<option value='' selected>All";
	$formQuery = "SELECT id, formName
				  FROM   edContactForms
				  ORDER BY formName";
	$formResult = mysql_query($formQuery);
	
	while ( $formRow = mysql_fetch_object($formResult)) {
		if ($formRow->id == $formId) {
			$selected = "selected";
		} else {
			$selected ="";
		}
		$formNameOptions .= "<option value='".$formRow->id."' $selected>".$formRow->formName;
	}
	
	// Hidden variable to be passed with form submit
	$hidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	$contactFormLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Contact Form Management</a>";
	
	
	include("../../includes/adminHeader.php");
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $contactFormLink;?></td></tr>
	<td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>
	<tr><td>Contact Form Name</td><td><select name=formId>
	<?php echo $formNameOptions;?>
	</select></td></tr>
	<tr>
		<td><input type=submit name=sSave value='View Report'></td></tr>
	<tr><td colspan=4 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><th align=left><a href='<?php echo $sortLink;?>&orderColumn=formName&formNameOrder=<?php echo $formNameOrder;?>'>Contact Form Name</a></th>
<th align=left><a href='<?php echo $sortLink;?>&orderColumn=dateSubmitted&dateSubmittedOrder=<?php echo $dateSubmittedOrder;?>'>Date Submitted</a></th>	
						<th align=left><a href='<?php echo $sortLink;?>&orderColumn=counts&countsOrder=<?php echo $countsOrder;?>'>Counts</a></th></tr>			
<?php echo $reportData;?>
<tr><td colspan=4 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><td><?php echo $contactFormLink;?></td></tr>
</table>
</form>			

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
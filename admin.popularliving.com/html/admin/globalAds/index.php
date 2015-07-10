<?php

/***********

Script to display Global Ads Counts Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Global Ads Counts";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// set Current Date values					

$currYear = date(Y);
$currMonth = date(m); //01 to 12
$currDay = date(d); // 01 to 31

// set curr date values to be selected by default
// to display report of current month
if (!($submit)) {
	$monthFrom = $currMonth;
	$monthTo = $currMonth;
	$dayFrom = "01";
	//$dayFrom = $currDay;
	$dayTo = $currDay;
	$yearFrom = $currYear;
	$yearTo = $currYear;
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

// Set the sortLink to use for all the links on this page
$sortLink = $PHP_SELF."?iMenuId=$iMenuId&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo";
$sortLink .= "&adType=$adType&displayPage=$displayPage&submit=ViewReport";
//$sortLink .= "&filter=$filter&exactMatch=$exactMatch&submit=ViewReport";


	// Check if the selected date is a valid date
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		// Set Order column as Current Order and set sorting order of it.
		// Don't change the order if Prev/Next/Last/First clicked, i.e. currOrder will be there
		if (!($currOrder)) {
			if (!($orderColumn)) {
				$orderColumn = "displayDate";
				$displayDateOrder = "DESC";
			}
			switch ($orderColumn) {		
				
				case "adType" :
					$currOrder = $adTypeOrder;		
					$adTypeOrder = ($adTypeOrder != "DESC" ? "DESC" : "ASC");				
					break;		
				case "displayPage" :
					$currOrder = $displayPageOrder;		
					$displayPageOrder = ($displayPageOrder != "DESC" ? "DESC" : "ASC");				
					break;								
				case "counts":
					$currOrder = $countsOrder;		
					$countsOrder = ($countsOrder != "DESC" ? "DESC" : "ASC");
					break;
				default :
					$currOrder = $displayDateOrder;		
					$displayDateOrder = ($displayDateOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		
		// Specify Page no. settings	
		$recPerPage = 50;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage - 1;
			
		// count records and GrandTotalCounts
		$countQuery = "SELECT  *
						FROM   AdsTracking
						WHERE  displayDate BETWEEN '$dateFrom' AND '$dateTo' ";

		if ($adType != '') {
			$filterPart = " AND adType LIKE '%$adType%'";
		}
		if ($displayPage != '') {
			$filterPart = " AND displayPage LIKE '%$displayPage%'";
		}
		
		$countQuery .= $filterPart;
		
		
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Clicked View Report - Query: $countQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
		
		
		
		
		$countResult = mysql_query($countQuery);			
		while ($countRow = mysql_fetch_object($countResult)) {			
			$grandTotalCounts += $countRow->counts;
		}		
			
		$totalCounts = 0;	
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0)
				$currentPage = " Page $page "."/ $totalPages";	
		// Prepare query to fetch the records
		$selectQuery = "SELECT *
						FROM   AdsTracking
						WHERE  displayDate BETWEEN '$dateFrom' AND '$dateTo' 
						$filterPart ";
		
		if ($sortPage != 'Y') {
			if ($orderColumn != '')
				$selectQuery .= " ORDER BY $orderColumn $currOrder";										 				
		}
		
		$selectQuery .= " LIMIT $startRec, $recPerPage";			
		$result = mysql_query($selectQuery);
		$numRecords = mysql_num_rows($result);
		if ($result) {			
			// Prepare Next/Prev/First/Last links
			if ($totalPages > $page) {
				$nextPage = $page + 1;
				$nextPageLink = "<a href='".$sortLink."&sortPage=$sortPage&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
				$lastPageLink = "<a href='".$sortLink."&sortPage=$sortPage&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";						
			}
			if ($page != 1) {
				$prevPage = $page - 1;
				$prevPageLink = "<a href='".$sortLink."&sortPage=$sortPage&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";			
				$firstPageLink = "<a href='".$sortLink."&sortPage=$sortPage&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";						
			}					
			
			if ($numRecords > 0) {	
				
				//if ($sortPage != 'Y' )	{	
					// If to sort all the records of result					
					while ($row = mysql_fetch_object($result)) {			

						if ($bgcolorClass == "ODD") {
							$bgcolorClass = "EVEN";
						} else {
							$bgcolorClass = "ODD";
						}			
										
						$totalCounts += $row->counts;
					
						$reportData .= "<tr class=$bgcolorClass>
										<td>$row->displayDate</a></td><td>$row->adType</td>
										<td>$row->displayPage</td><td>$row->counts</td></tr>";					
					}						
		} else {
			$sMessage = "No Records Exist...";
		}
		// display Page Total
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}			
						
		$reportData .= "<tr class=$bgcolorClass><td colspan=2></td><td><b>Page Total counts</b></td><td><b>$totalCounts</b></td></tr>";	

		// Display Grand total
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}			
							
		$reportData .= "<tr class=$bgcolorClass><td colspan=2></td><td><b>Grand Total Counts</b></td><td><b>$grandTotalCounts</b></td></tr>";
		
		mysql_free_result($result);

	} else {
		echo mysql_error();
	}
	//}
} else {
	$sMessage = "Please Select Valid Dates...";
}

// If exactMatch checked	
if ($exactMatch == 'Y')
	$exactMatchChecked = "checked";
else
	$exactMatchChecked = '';
	
$hidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
$AOLComplaintsLink = "<a href='index.php?iMenuId=$iMenuId'>Back To AOL Complaints Menu</a>";

include("../../includes/adminHeader.php");

?>	

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<table width=95% align=center bgcolor=c9c9c9><tr>

	<td>Date From</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>	
	<tr><td>Ad Type</td><td><input type=text name=adType value='<?php echo $adType;?>'></td></tr>
	<tr><td>Display Page</td><td colspan=3><input type=text name=displayPage value='<?php echo $displayPage;?>' size=40></td></tr>
	<tr>
<td><input type=submit name=submit value='View Report'></td></tr>
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=3 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><th align=left><a href='<?php echo $sortLink;?>&orderColumn=displayDate&displayDateOrder=<?php echo $displayDateOrder;?>'>Display Date</a></th>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=adType&adTypeOrder=<?php echo $adTypeOrder;?>'>Ad Type</a></th>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=displayPage&displayPageOrder=<?php echo $displayPageOrder;?>'>Display Page</a></th>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=counts&countsOrder=<?php echo $countsOrder;?>'>Counts</a></th></tr>			

<?php echo $reportData;?>
<tr><td colspan=3 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
</table>
</form>	


<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
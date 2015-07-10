<?php


/***********

Script to Display Project Display Report of HandCraftersVillage site

*************/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village Project Display Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);
		
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($sSave || $monthFrom) ) {
		$monthFrom = $currMonth;
		$monthTo = $currMonth;
		$dayFrom = "01";
		$dayTo = $currDay;
		$yearFrom = $currYear;
		$yearTo = $currYear;
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
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
		
		$monthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$monthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
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
	
	// Specify Page no. settings
		$recPerPage = 20;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;
			
		
	
		
	// to get grand total category display counts
	$catQuery = "SELECT sum(counts) AS counts
				 FROM   categoryDisplayCounts
				 WHERE  clickDate BETWEEN '$dateFrom' AND '$dateTo'";
	$sTempQuery2 .= "\n$catQuery";
	
	$catResult = dbQuery($catQuery);
		
	while ($catRow = dbFetchObject($catResult)) {
			$grandTotalCategoryCount += $catRow->counts;
	}
	
					
	$countQuery .= " SELECT clickDate, counts, title
					 FROM   craftProjects, projectDisplayCounts 
					 WHERE  craftProjects.id = projectDisplayCounts.projectId
					 AND    clickDate BETWEEN '$dateFrom' AND '$dateTo'";
	$sTempQuery2 .= "\n$countQuery";
	$countResult = dbQuery($countQuery);
	
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $sTempQuery2\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	$numRecords = dbNumRows($countResult);
	
	$totalPages = ceil($numRecords/$recPerPage);
	if ($totalPages > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	} else {
		$sMessage = "No Records Found...";
	}
		
	$countQuery .= " LIMIT  $startRec, $recPerPage";
	
	$countResult = dbQuery($countQuery);
	
	$sortLink = "$PHP_SELF?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
	
	echo dbError();
	while ($countRow = dbFetchObject($countResult)) {
		
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
								
		
		$clickDate = $countRow->clickDate;
		$title = $countRow->title;
		$projectCounts = $countRow->counts;		
		$totalProjectCounts += $projectCounts;		
				
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
				
		$reportData .= "<tr class=$bgcolorClass><td>$clickDate</td><td>$title</td>
						<td>$projectCounts</td></tr>";		
	}
	
	if (dbNumRows($countResult) >0) {
		$reportData .= "<tr><td colspan=2 align=right class=header>Page Total Count &nbsp; &nbsp; </td><td>$totalProjectCounts</td></tr>
						<tr><td colspan=2 align=right class=header>Grand Total Count &nbsp; &nbsp; </td><td>$grandTotalProjectCounts</td></tr>";
	}
	
	$reportLinks = "<a href='catDisplayReport.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Category Report</a>
					&nbsp; &nbsp;<a href='index.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>TAF Report</a>";
	
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>";

	include("$sGblIncludePath/adminHeader.php");
	?>
	
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=right><?php echo $reportLinks;?></td></tr>
<tr><td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td><td><input type=submit name=sSave value="View Report"></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=3 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
	<tr><td class=header>Click Date</a></td>
	<td class=header>Category</a></td>
	<td class=header>Display Counts</td>
	</tr>
<?php echo $reportData;?>	
<tr><td colspan=3 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
</table>


<?php 
	// include footer
	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	
?>
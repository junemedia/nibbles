<?php

include("../../../includes/paths.php");

$sPageTitle = "Fun Page Counts Report";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($save || $monthFrom) ) {
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
	
	
	if (!($orderColumn)) {
		$orderColumn = "title";
		$titleOrder = "ASC";
	}
	if (!($currOrder)) {
	switch ($orderColumn) {
		case "catTitle" :
		$currOrder = $catTitleOrder;
		$catTitleOrder = ($catTitleOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "clickDate" :
		$currOrder = $clickDateOrder;
		$clickDateOrder = ($clickDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "displayCounts" :
		$currOrder = $displayCountsOrder;
		$displayCountsOrder = ($displayCountsOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "TAFCounts" :
		$currOrder = $TAFCountsOrder;
		$TAFCountsOrder = ($TAFCountsOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
		
	}
	}
	
	// Specify Page no. settings
		// Specify Page no. settings<BR>

		if (!($recPerPage)) {
			$recPerPage = 10;
		}
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;
		
	$sortLink = "$PHP_SELF?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&recPerPage=$recPerPage";
	
	$countQuery = "SELECT funPages.id, funPageCategories.id catId, funPages.title, funPageCategories.title AS catTitle, fpDisplayCounts.clickDate, sum(fpDisplayCounts.counts) AS displayCounts
			   FROM funPages LEFT JOIN fpDisplayCounts ON funPages.id = fpDisplayCounts.fpId,
					 funPageCategories,	funPageCategoryInt
			   WHERE fpDisplayCounts.clickDate BETWEEN '$dateFrom' AND '$dateTo'			   
			   AND   funPages.id = funPageCategoryInt.pageId
			   AND   funPageCategories.id = funPageCategoryInt.CatId 
			   GROUP BY funPages.id, clickDate
			   ORDER BY $orderColumn $currOrder";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $countQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles

	
	$countResult = mysql_query($countQuery);
	echo dbError();
	$numRecords = mysql_num_rows($countResult);
	
	// get grand total counts
	while ($countRow = mysql_fetch_object($countResult)) {
		$displayCount = $countRow->displayCounts;
		$grandTotalDisplayCount += $displayCount;
	}
	// to get grand total TAF counts
	$TAFQuery = "SELECT sum(counts) AS counts
				 FROM   fpTAFCounts
				 WHERE  clickDate BETWEEN '$dateFrom' AND '$dateTo'"; 
		$TAFResult = mysql_query($TAFQuery);
		
		while ($TAFRow = mysql_fetch_object($TAFResult)) {
			$grandTotalTAFCount += $TAFRow->counts;
		}
		
	$countQuery .= "  LIMIT $startRec, $recPerPage";
	$countResult = mysql_query($countQuery);
	
	$totalPages = ceil($numRecords/$recPerPage);
	$currentPage = " Page $page "."/ $totalPages";
		
	echo mysql_error();
	while ($countRow = mysql_fetch_object($countResult)) {
		
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
								
		$id = $countRow->id;
		$catId = $countRow->catId;
		$title = $countRow->title;
		$catTitle = $countRow->catTitle;
		$clickDate = $countRow->clickDate;
		$displayCount = $countRow->displayCounts;
		$totalDisplayCount += $displayCount;
		$TAFCount = 0;
		// get TAF count
		$TAFQuery = "SELECT sum(counts) AS counts
				 FROM   fpTAFCounts
				 WHERE  clickDate = '$clickDate'
				 AND    fpId = '$id'"; 
		$TAFResult = mysql_query($TAFQuery);
		
		while ($TAFRow = mysql_fetch_object($TAFResult)) {
			$TAFCount = $TAFRow->counts;
		}
		$totalTAFCount += $TAFCount;
		
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
				
		$reportData .= "<tr class=$bgcolorClass><td><a href='JavaScript:void(open(\"$sGblFpSiteRoot/fp.php/id/$id\",\"\",\"\"));'>$title</a></td>
			<td><a href='JavaScript:void(open(\"$sGblFpSiteRoot/cat.php/catid/$catId\",\"\",\"\"));'>$catTitle</a></td><td>$clickDate</td><td>$displayCount</td><td>$TAFCount</td></tr>";		
	}
	
	if (mysql_num_rows($countResult) >0) {
		$reportData .= "<tr><td colspan=3 align=right><b>Page Total Count &nbsp; &nbsp; </b></td><td><b>$totalDisplayCount</b></td><td><b>$totalTAFCount</b></td></tr>
						<tr><td colspan=3 align=right><b>Grand Total Counts Within Selected Date Range &nbsp; &nbsp; </b></td><td><b>$grandTotalDisplayCount</b></td><td><b>$grandTotalTAFCount</b></td></tr>";
	}
	
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";
		
		
	include("$sGblIncludePath/adminHeader.php");
		
?>
	
	<script language=JavaScript>
function funcRecPerPage(form1) {
				document.form1.submit();
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
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
<tr><td colspan=5 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp;
 	Go To Page <input type=text name=page value='<?php echo $page;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp;  <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
	<tr><td class=header><A href="<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>">Fun Page</a></td>
	<td class=header><A href="<?php echo $sortLink;?>&orderColumn=catTitle&catTitleOrder=<?php echo $catTitleOrder;?>">FP Category</a></td>
	<td class=header><A href="<?php echo $sortLink;?>}&orderColumn=clickDate&clickDateOrder=<?php echo $clickDateOrder;?>">Click Date</a></td>
	<td class=header><A href="<?php echo $sortLink;?>&orderColumn=displayCounts&displayCountsOrder=<?php echo $displayCountsOrder;?>">Display Count</a></td>
	<td class=header>TAF Count</td></tr>
	<?php echo $reportData;?>	
<tr><td colspan=5 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
</table>


<?php
include("../../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
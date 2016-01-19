<?php

/***********

Script to display You Won Report

************/

include("../config.php");

$pageTitle = "Show Me Reporting -  Report By Date";
session_start();
if (session_is_registered("showMeAdmin")) {
	$monthArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	
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
	
		$sortLink = $PHP_SELF."?monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .="&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&submit=ViewReport";		
		
		// Specify Page no. settings
		$recPerPage = 10;
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;		
		
		// Prepare report data to display
		$selectPart = "SELECT a.dateShown,  sum(displayCounts) totalDisplayCounts,
							   sum(pickCounts) totalPickCounts ";
		
		$selectQuery = "FROM   ShowMePageCounts a, ShowMeTracking b, ShowMeOffers c
						WHERE  a.dateShown = b.dateShown	
						AND    a.sourceCode = b.sourceCode					
						AND    b.offerId = c.id 
						AND    a.dateShown >= '$dateFrom'
						AND    a.dateShown <= '$dateTo'";		
		$groupByPart = " GROUP BY a.dateShown ";
		$orderPart = " ORDER BY a.dateShown ";
				
		if ($expandSourceCode || $expandOffer) {			
			
			if ($expandSourceCode) {
				$groupByPart .= ", a.sourceCode ";
				$selectPart .= ", a.sourceCode ";
				$orderPart .= " , a.sourceCode, offerId ";
			} 
				
			if ($expandOffer) {
				$groupByPart .= ", offerId ";
				$selectPart .= ", offerId, c.title ";
				$orderPart .= " , offerId, a.sourceCode ";
			}			
		}
				

		$selectQuery = $selectPart . $selectQuery .$groupByPart . $orderPart;
		
		// Get the total no of records and count total no of pages
		$tempResult = mysql_query($selectQuery);		

		$numRecords = mysql_num_rows($tempResult);		
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0)
			$currentPage = " Page $page "."/ $totalPages";
		while ($tempRow = mysql_fetch_object($tempResult)) {
			echo $tempRow->displayCounts;
			$grandTotalDisplayCounts += $tempRow->totalDisplayCounts;
			$grandTotalPickCounts += $tempRow->totalPickCounts;						
		}		
		
		// Get page shown grand total
		
		$pageShownTotalQuery = "SELECT sum(counts) AS pageCounts
							FROM  ShowMePageCounts
							WHERE dateShown >= '$dateFrom'
							AND   dateShown <= '$dateTo'";
		$pageShownTotalResult = mysql_query($pageShownTotalQuery);
		while($pageShownTotalRow = mysql_fetch_object($pageShownTotalResult)) {
			$grandTotalPageCounts = $pageShownTotalRow->pageCounts;			
		}		
		
		$pageTotalPageCounts = 0;
		$pageTotalDisplayCounts = 0;
		$pageTotalPickCounts = 0;
		$prevDate = '';
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
					if($prevDate != $row->dateShown || $prevDate == '') {
						$dateShownDisplay = $row->dateShown;
						
						// calculate page shown counts for this date
						$pageCountQuery = "SELECT sum(counts)  AS pageCounts
										   FROM   ShowMePageCounts
										   WHERE dateShown = '".$row->dateShown."'
										   GROUP BY dateShown";
						$pageCountResult = mysql_query($pageCountQuery);
						
						while ($pageCountRow = mysql_fetch_object($pageCountResult)) {
							
							$pageShownCountDisplay = $pageCountRow->pageCounts;
							$pageTotalPageCounts += $pageCountRow->pageCounts;
						}		
						
					} else 	{
						// Don't display date and page CountDisplayAgain
						$dateShownDisplay = "";
						$pageShownCountDisplay = "";
					}
					
					$prevDate = $row->dateShown;
					
					$pageTotalDisplayCounts += $row->totalDisplayCounts;
					$pageTotalPickCounts += $row->totalPickCounts;					
								
					// Prepare Next/Prev/First/Last links
					if ($totalPages > $page) {
						$nextPage = $page+1;
						//$sortLink .= "&expandSourceCode=$expandSourceCode&expandOffer=$expandOffer";
						$nextPageLink = "<a href='".$sortLink."&expandSourceCode=$expandSourceCode&expandOffer=$expandOffer&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
						$lastPageLink = "<a href='".$sortLink."&expandSourceCode=$expandSourceCode&expandOffer=$expandOffer&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
					}
					if ($page!=1) {
						$prevPage = $page-1;
						$prevPageLink = "<a href='".$sortLink."&expandSourceCode=$expandSourceCode&expandOffer=$expandOffer&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
						$firstPageLink = "<a href='".$sortLink."&expandSourceCode=$expandSourceCode&expandOffer=$expandOffer&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
					}					
															
					$reportData .="<tr class=$bgcolorClass><td>$dateShownDisplay</td><td>$pageShownCountDisplay</td>";
					
					if ($expandSourceCode)
						$reportData .= "<td>$row->sourceCode</td>";
					if ($expandOffer)
					{
						$offerTitle = ascii_encode(substr($row->title,0,25))."...";
						$reportData .= "<td>$offerTitle</td>";
					}
					
					$reportData .= "<td>$row->totalDisplayCounts</td>
								<td>$row->totalPickCounts</td></tr>";										
				}
			} else {
				$message = "No records exist...";
			}
			if ($bgcolorClass=="ODD") {
				$bgcolorClass="EVEN";
			} else {
				$bgcolorClass="ODD";
			}			
			$reportData .="<tr class=$bgcolorClass><td><b>Page Total</b></td><td><b>$pageTotalPageCounts</b></td>";
			if($expandSourceCode)
				$reportData .= "<td></td>";
			if($expandOffer)
				$reportData .= "<td></td>";
			$reportData .= "<td><b>$pageTotalDisplayCounts</b></td><td><b>$pageTotalPickCounts<b></td></tr>";
			
			if ($bgcolorClass=="ODD") {
				$bgcolorClass="EVEN";
			} else {
				$bgcolorClass="ODD";
			}
			$reportData .="<tr class=$bgcolorClass><td><b>Grand Total</b></td><td><b>$grandTotalPageCounts</b></td>";
			
			
			if($expandSourceCode)
				$reportData .= "<td></td>";
			if($expandOffer)
				$reportData .= "<td></td>";
			$reportData .= "<td><b>$grandTotalDisplayCounts</b></td><td><b>$grandTotalPickCounts</b></td></tr>";				
			mysql_free_result($result);
			
		} else {
			echo mysql_error();
		}
	} else {
		$message = "Please select valid dates...";
	}			
?>


<html>

<head>
<title><?php echo "$pageTitle";?></title>
<LINK rel="stylesheet" href="<?php echo "$showMeSiteRoot";?>/styles.css" type="text/css" >
</head>
<body>
<form name=form1 action='<?php echo $PHP_SELF; ?>'>

<table width=95% align=center bgcolor=c9c9c9>
<tr><td><a href='index.php'>Back To ShowMe Admin</a></td></tr>
	<td>Date From</td><td><select name=monthFrom><?php echo $monthFromOptions; ?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions; ?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions; ?>
	</select></td><td>Date To</td>
	<td><select name=monthTo><?php echo $monthToOptions; ?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions; ?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions; ?>
	</select></td></tr>
	<tr>
	<td><input type=submit name=submit value='View Report'></td>
	<td colspan=3>
	<?php
	
	if ($expandSourceCode)
		echo "<a href = '$sortLink&expandOffer=$expandOffer'>Supress SourceCode</a> &nbsp; &nbsp;";
	else 
		echo "<a href = '$sortLink&expandOffer=$expandOffer&expandSourceCode=Y'>Expand SourceCode</a> &nbsp; &nbsp;";
	
	if ($expandOffer)
		echo "<a href = '$sortLink&expandSourceCode=$expandSourceCode'>Supress Offers</a>";
	else 
		echo "<a href = '$sortLink&expandSourceCode=$expandSourceCode&expandOffer=Y'>Expand Offers</a>";		
		
	?>
	</td></tr>

</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=right class=header><?php echo "$firstPageLink &nbsp;  $prevPageLink &nbsp; $nextPageLink &nbsp; $lastPageLink &nbsp; $currentPage"; ?></td></tr>	
<tr><TD align=left class=header>Date</TD>
<TD align=left class=header>Page Shown Count</TD>
<?php
if ($expandSourceCode)
	echo "<TD align=left class=header>SourceCode</TD>";
if($expandOffer) 
	echo "<TD align=left class=header>Offer</TD>";
?>
<TD align=left class=header>Offer Display Count</TD>
<TD align=left class=header>Offer Taken Count</TD>
</tr>
<?php echo $reportData; ?>
<tr><td colspan=5 align=right class=header><?php echo "$firstPageLink &nbsp;  $prevPageLink &nbsp; $nextPageLink &nbsp $lastPageLink &nbsp; $currentPage"; ?></td></tr>
</table>
</form>			
</body>

</html>
<?php
} else {
	header("Location:index.php");
}
?>
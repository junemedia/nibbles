<?php

/***********

Script to display Newsletter Pixels Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Newsletter Pixel Reporting";

session_start();
// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// set curr date values to be selected by default
	$sSave = stripslashes($sSave);
	$currYear = date('Y');
	$yesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($sSave != "Today's Report") {
		$reportTable = "nlPixelsTrackingHistorySum";
		
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
		$reportTable = "nlPixelsTracking";
		
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
	
	// check if the dates selected are valid dates
	if (checkDate($monthFrom, $dayFrom, $yearFrom) && checkdate($monthTo, $dayTo,$yearTo)) {
		
		if (!($orderColumn)) {
			$orderColumn = "nlCode";
			$nlCodeOrder = "ASC";
		}
		
		switch ($orderColumn) {
			
			case "publicationName" :
			$currOrder = $publicationNameOrder;
			$publicationNameOrder = ($publicationNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "publicationCode" :
			$currOrder = $publicationCodeOrder;
			$publicationCodeOrder = ($publicationCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "opens" :
			$currOrder = $opensOrder;
			$opensOrder = ($opensOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$currOrder = $nlCodeOrder;
			$nlCodeOrder = ($nlCodeOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sortLink = $PHP_SELF."?iMenuId=$iMenuId&monthFrom=$monthFrom&dayFrom=$dayFrom&yearFrom=$yearFrom";
		$sortLink .= "&monthTo=$monthTo&dayTo=$dayTo&yearTo=$yearTo&companyId=$companyId&sourceCode=$sourceCode&filter=$filter&sSave=".ascii_encode($sSave);
		
		// Specify Page no. settings
		if (!($recPerPage)) {
			$recPerPage = 10;
		}
		
		if (!($page)) {
			$page = 1;
		}
		$startRec = ($page-1) * $recPerPage;
		$endRec = $startRec + $recPerPage -1;
		
		
		// Prepare report data to display
		$selectQuery = "SELECT publicationName, LEFT($reportTable.nlCode, LENGTH($reportTable.nlCode)-6) as publicationCode, $reportTable.nlCode, ";
		if ($reportTable == "nlPixelsTrackingHistorySum") {
			$selectQuery .= " sum(opens) AS opens ";
		} else {
			$selectQuery .= " count(openDate) AS opens ";
		}
		
		$selectQuery .= " FROM $reportTable, publications
						WHERE ( publications.publicationCode = LEFT($reportTable.nlCode, LENGTH($reportTable.nlCode)-6)
						|| publications.publicationCode = LEFT($reportTable.nlCode, LENGTH($reportTable.nlCode)-7))
						AND openDate between '$dateFrom' AND '$dateTo'";
		
		if (trim($publicationId != '')) {						
			$selectQuery .= " AND publications.id = '$publicationId' ";

		}
		
		if (trim($nlCode != '')) {
			if ($filter == 'startsWith') {
				$selectQuery .= " AND $reportTable.nlCode LIKE '".$nlCode."%' ";
			} else if ($filter == 'exactMatch') {
				$selectQuery .= " AND $reportTable.nlCode = '$nlCode' ";
			}
		}
		
		$selectQuery .= " GROUP BY publicationCode	";
		$selectQuery .= " ORDER BY ".$orderColumn." $currOrder";
		

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		
		
		// Get the total no of records and count total no of pages
		$result = mysql_query($selectQuery);
		echo mysql_error();
		$numRecords = mysql_num_rows($result);
		$grandTotalOpens = 0;
		$totalPages = ceil($numRecords/$recPerPage);
		if ($numRecords > 0) {
			$currentPage = " Page $page "."/ $totalPages";
		}
		while ($tempRow = mysql_fetch_object($result)) {
			$grandTotalOpens += $tempRow->opens;
		}
		
		$pageTotalOpens = 0;
		$selectQuery .= " LIMIT $startRec, $recPerPage";
		$result = mysql_query($selectQuery);
		
		if ($result) {
			if (mysql_num_rows($result) > 0) {
				
				$totalClicks = 0;
				while ($row = mysql_fetch_object($result)) {
					
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					
					$pageTotalOpens += $row->opens;
					
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
					
					$reportData .= "<tr class=$bgcolorClass><td>$row->publicationName</td>
								 <td>$row->publicationCode</td><td>$row->nlCode</td>
								<td>$row->opens</td></tr>";										
				}
			} else {
				$message = "No Records Exist...";
			}
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			
			$reportData .= "<tr class=$bgcolorClass><td colspan=2></td><td><b>Page Total Opens</b></td><td><b>$pageTotalOpens</b></td></tr>";
			
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			
			$reportData .= "<tr class=$bgcolorClass><td colspan=2></td><td><b>Grand Total Opens</b></td><td><b>$grandTotalOpens</b></td></tr>";
			
			mysql_free_result($result);
		} else {
			echo mysql_error();
		}
	} else {
		$message = "Please Select Valid Dates...";
	}
	
	if ($filter == 'startsWith') {
		$startsWithChecked = "CHECKED";
	} else if ($filter == 'exactMatch') {
		$exactMatchChecked = "CHECKED";
	}
	
	// Prepare Newsletter options
	$publicationNameOptions .= "<option value='' selected>All";
	$companyQuery = "SELECT *
					 FROM publications
					 ORDER BY publicationName";
	$companyResult = mysql_query($companyQuery);
	
	while ( $companyRow = mysql_fetch_object($companyResult)) {
		if ($companyRow->id == $publicationId) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$publicationNameOptions .="<option value='".$companyRow->id."' $selected>".$companyRow->publicationName." - ".$companyRow->publicationCode;
	}
	
	if ($reportMenuId) {
		$hidden .=  "<input type=hidden name=reportMenuId value='$reportMenuId'>
					 <input type=hidden name=reportMenuFolder value='$reportMenuFolder'>";
		$managePixelsLink = "<a href='../$reportMenuFolder/index.php?iMenuId=$reportMenuId'>Back to Offer Reports Menu</a>";
	} else {
				
	$managePixelsLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Manage NL Pixels<a>";
	}
	
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
<table width=95% align=center bgcolor=c9c9c9>
<tr><td colspan=4><?php echo $managePixelsLink;?></td></tr>
<tr>
	<td>Date from</td><td><select name=monthFrom><?php echo $monthFromOptions;?>
	</select> &nbsp;<select name=dayFrom><?php echo $dayFromOptions;?>
	</select> &nbsp;<select name=yearFrom><?php echo $yearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp;<select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp;<select name=yearTo><?php echo $yearToOptions;?>
	</select></td></tr>
	<tr><td>Publication Name</td><td><select name=publicationId>
	<?php echo $publicationNameOptions;?>
	</select></td></tr><tr>
	<td>Newsletter Code</td><td colspan=3><input type=text name=nlCode value='<?php echo $nlCode;?>'>
	<input type='radio' name='filter' value='startsWith' <?php echo $startsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='filter' value='exactMatch' <?php echo $exactMatchChecked;?>> Exact Match</td>
		</tr><tr>
<td colspan=3><br><input type=submit name=sSave value='History Report'> &nbsp; &nbsp; &nbsp; <input type=submit name=sSave value="Today's Report"></td></tr>
<tr><td colspan=4 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><th align=left><a href='<?php echo $sortLink;?>&orderColumn=publicationName&publicationNameOrder=<?php echo $publicationNameOrder;?>'>Publication Name</a></th>
<th align=left><a href='<?php echo $sortLink;?>&orderColumn=publicationCode&publicationCodeOrder=<?php echo $publicationCodeOrder;?>'>Publication Code</a></th>
	<th align=left><a href='<?php echo $sortLink;?>&orderColumn=nlCode&nlCodeOrder=<?php echo $nlCodeOrder;?>'>Newsletter Code</a></th>
						<th align=left><a href='<?php echo $sortLink;?>&orderColumn=opens&opensOrder=<?php echo $opensOrder;?>'>Opens</a></th></tr>
<?php echo $reportData;?>
<tr><td colspan=4 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
</table>
</form>			

<?php
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}
?>
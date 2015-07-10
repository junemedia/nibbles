<?php

/***********

Script to display Offers Expiring Report

************/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Nibbles Editorial Offers Expiring In The Next 30 Days Report";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	$monthFrom = $currMonth;
	$dayFrom = "01";
	$yearFrom = $currYear;
	
	$monthTo = $currMonth;
	$dayTo = $currDay;
	$yearTo = $currYear;
	
	$dateFrom = date(Y)."-".date(m)."-".date(d);
	
	$dateQuery = "SELECT DATE_ADD('".$dateFrom."', INTERVAL 30 DAY) dateTo";
	
	$dateResult = mysql_query($dateQuery);
	while ($dateRow = mysql_fetch_object($dateResult)) {
		$dateTo = $dateRow->dateTo;
	}
	
	if (!($orderColumn)) {
		$orderColumn = "offerCode";
		$offerCodeOrder = "ASC";
	}
	
	if(!($currOrder)) {
		switch ($orderColumn) {
			case "companyName" :
			$currOrder = $companyNameOrder;
			$companyNameOrder = ($companyNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "headline" :
			$currOrder = $headlineOrder;
			$hadlineOrder = ($headlineOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "description" :
			$currOrder = $descriptionOrder;
			$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "offerCode" :
			$currOrder = $offerCodeOrder;
			$offerCodeOrder = ($offerCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "expirationDate" :
			$currOrder = $expirationDateOrder;
			$expirationDateOrder = ($expirationDateOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$currOrder = $offerCodeOrder;
			$offerCodeOrder = ($offerCodeOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($filter != '') {
		
		$filterPart .= " AND ( ";
		
		switch ($searchIn) {
			case "headline" :
			$filterPart .= ($exactMatch == 'Y') ? "headline = '$filter'" : "headline like '%$filter%'";
			break;
			case "description" :
			$filterPart .= ($exactMatch == 'Y') ? "description = '$filter'" : "description like '%$filter%'";
			break;
			case "offerCode" :
			$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter'" : "offerCode like '%$filter%'";
			break;
			case "companyName" :
			$filterPart .= ($exactMatch == 'Y') ? "OC.companyName = '$filter'" : "offerCode like '%$filter%'";
			break;
			case "expirationDate" :
			$filterPart .= ($exactMatch == 'Y') ? "expirationDate = '$filter'" : "expirationDate like '%$filter%'";
			break;
			default:
			if ($exactMatch != 'Y' || checkdate(substr($filter,5,2), substr($filter,8,2), substr(0,4)))
			$filterPart .= ($exactMatch == 'Y') ? " expirationDate = '$filter' || offerCode = '$filter' || OC.companyName = '$filter' || headline = '$filter' || description = '$filter'" : " expirationDate like '%$filter%' || offerCode like '%$filter%' || OC.companyName LIKE '%$filter%' || headline like '%$filter%' || description like '%$filter%' ";
			else
			$filterPart .= ($exactMatch == 'Y') ? " offerCode = '$filter' || OC.companyName = '$filter' || headline = '$filter' || description = '$filter'" : " offerCode like '%$filter%' || OC.companyName LIKE '%$filter%' || headline like '%$filter%' || description like '%$filter%' ";
		}
		
		$filterPart .= ") ";
	}
	
	if ($exclude != '') {
		$filterPart .= " AND ( ";
		switch ($exclude) {
			case "headline" :
			$filterPart .= "headline NOT LIKE '%$exclude%'";
			break;
			case "description" :
			$filterPart .= "description NOT LIKE '%$exclude%'";
			break;
			case "offerCode" :
			$filterPart .= "offerCode NOT LIKE '%$exclude%'";
			break;
			case "companyName" :
			$filterPart .= "OC.companyName NOT LIKE '%$exclude%'";
			break;
			default:
			$filterPart .= "offerCode NOT LIKE '%$exclude%' && OC.companyName NOT LIKE '%$exclude%' && headline NOT LIKE '%$exclude%' && description NOT LIKE '%$exclude%'" ;
		}
		$filterPart .= " ) ";
		
	}
	
	$filter = ascii_encode(stripslashes($filter));
	$exclude = ascii_encode(stripslashes($exclude));
	
	// Specify Page no. settings
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&filter=$filter&exactMatch=$exactMatch&exclude=$exclude&searchIn=$searchIn&recPerPage=$recPerPage";
	
	
	$selectQuery = "SELECT OC.companyName, O.*
				FROM  edOffers O, edOfferCompanies OC
				WHERE O.companyId = OC.id
				AND expirationDate BETWEEN '$dateFrom' AND '$dateTo'
				$filterPart ";
	if ($orderColumn == 'offerCode') {
		$selectQuery .= " ORDER BY substring(offerCode,1,3) $currOrder, substring(offerCode,4)+0 $currOrder ";
	} else {
		$selectQuery .= " ORDER BY $orderColumn $currOrder ";
	}
		
	$result = mysql_query($selectQuery);
	$numRecords = mysql_num_rows($result);
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$totalPages = ceil($numRecords/$recPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($page > $totalPages) {
		$page = $totalPages;
	}
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	if ($numRecords > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	}
	
	
		// use query to fetch only the rows of the page to be displayed
		$selectQuery .= " LIMIT $startRec, $recPerPage";
		
		$result = mysql_query($selectQuery);
		if ($result) {
			
			if (mysql_num_rows($result) > 0) {
				// Prepare Next/Prev/First/Last links
				
				if ($totalPages > $page ) {
					$nextPage = $page+1;
					$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
					$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
				}
				if ($page != 1) {
					$prevPage = $page-1;
					$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
					$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
				}
				
				while ($row = mysql_fetch_object($result)) {
					
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					$dispHeadline = ascii_encode(substr($row->headline,0,50));
					$dispDescription = ascii_encode(substr($row->description,0,50));
					$reportData .= "<tr class=$bgcolorClass><td>$row->offerCode</td>
									<td>$dispHeadline ...</td><td>$dispDescription ...</td>
								<td>$row->companyName</td>
							<td>$row->expirationDate</td>
							<td><a href='JavaScript:void(window.open(\"addOffer.php?iMenuId=$iMenuId&id=".$row->id."&offerCode=".$row->offerCode."&recPerPage=$recPerPage&filter=$filter&exactMatch=$exactMatch&exclude=$exclude&searchIn=$searchIn&backTo=expiring\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							</td></tr>";												
				}
			} else {
				$sMessage = "No Records Exist...";
			}
		}
	
	if ($exactMatch == 'Y') {
		$exactMatchChecked = "checked";
	}
	
	switch ($searchIn) {
		case 'headline':
		$headlineSelected = "selected";
		break;
		case 'description':
		$descriptionSelected = "selected";
		break;
		case 'offerCode':
		$offerCodeSelected = "selected";
		break;
		case 'companyName':
		$companyNameSelected = "selected";
		break;
		case 'expirationDate':
		$expirationDate = "selected";
		break;
		default:
		$allFieldsSelected = "selected";
	}
	
	$searchInOptions = "<option value='' $allFieldsSelected>All Fields
						<option value='headline' $headlineSelected>Headline
						<option value='description' $descriptionSelected>Description
						<option value='offerCode' $offerCodeSelected>OfferCode
						<option value='companyName' $companyNameSelected>Offer Company
						<option value='expirationDate' $expirationDateSelected>Expiration Date";
	
	
	$offerMgmntLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Offers Management</a>
					&nbsp;&nbsp;<a href='report.php?iMenuId=$iMenuId'>Offer Redirects Report</a>
					&nbsp;&nbsp;<a href='pixelReport.php?iMenuId=$iMenuId'>Offer Pixels Report</a>
					&nbsp;&nbsp;<a href='orphanOffersReport.php?iMenuId=$iMenuId'>Orphan Offers Report</a>
					&nbsp;&nbsp;<a href='deactOffersReport.php?iMenuId=$iMenuId'>Deactivated Offers Report</a>
					&nbsp;&nbsp;<a href='JavaScript:void(window.open(\"frameMgmnt.php?iMenuId=$iMenuId\",\"\",\"\"))'>Frame Managemnt</a>";
	
	// Hidden variable to be passed with form submit
	$hidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";

	include("../../includes/adminHeader.php");	
		
?>
<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}						
				function funcRecPerPage(form1) {
					document.form1.elements['add'].value='';					
					document.form1.submit();
				}												
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<table width=95% align=center bgcolor=c9c9c9 cellpadding=5 cellspacing=0><tr>
<tr><td colspan=6><?php echo $offerMgmntLink;?></td></tr><tr><td>Filter By</td><td colspan=4><input type=text name=filter value='<?php echo $filter?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td><input type=text name=exclude value='<?php echo $exclude;?>'></tR>
<tr><td>Search In</td><td><select name=searchIn>
	<?php echo $searchInOptions;?>
	</select></td><td><input type=submit name=viewReport value='View Report'></td></tr>
<tr><td colspan=5 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=page value='<?php echo $page;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	

<tr><td><a href='<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>' class=header>OfferCode</a></td>
<td><a href='<?php echo $sortLink;?>&orderColumn=headline&headlineOrder=<?php echo $headlineOrder;?>' class=header>Headline</a></td>
<td><a href='<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>' class=header>Description</a></td>
<td><a href='<?php echo $sortLink;?>&orderColumn=companyName&companyNameOrder=<?php echo $companyNameOrder;?>' class=header>Company Name</a></td>
<td><a href='<?php echo $sortLink;?>&orderColumn=expirationDate&expirationDateOrder=<?php echo $expirationDateOrder;?>' class=header>Expiration Date</a></td>
<td></td>
</tr>
<?php echo $reportData;?>
<TR><TD colspan=5 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><td colspan=6><?php echo $offerMgmntLink;?></td></tr>
</table>
</form>	

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
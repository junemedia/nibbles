<?php

/*********

Script to Display List/Delete ShowMe information

*********/

include("../../library.php");
include("../../includes/template.php");

$pageTitle = "Show Me Management";

session_start();

// Check if user is permitted to view this page
//if (session_is_registered("anlUserId")) {
if (session_is_registered("marsUserId") && ($marsAccessRights[$menuId]=='Y' || $marsLevel == 'admin')) {
	//&& $marsPermissions[$menuId]['perView']=='Y'
	// Create the template object
	$t = new Template($marsWebRoot,"comment");
	
	// Get the folder of this menu
	$menuFolder = $marsMenuFolder[$menuId]['link'];
	
	$t->set_file(array("main" => "main.phtml",
	"content" => "$menuFolder/index.phtml"));
	
	if ($delete) {
					
		$deleteQuery = "DELETE FROM ShowMeOffers
			   			WHERE id = '$id'"; 
		$result = mysql_query($deleteQuery);
		if ( $result) {
			// delete from Tracking tables
			$delete1Query = "DELETE FROM ShowMeTracking
							 WHERE  offerId = '$id'";
			$delete1Result = mysql_query($delete1Query);
			
		} else {
			echo mysql_error();
		}			
	}
	// set default order by column
	if (!($orderColumn)) {
		$orderColumn = "title";
		$titleOrder = "ASC";
	}
	if(!($currOrder)) {
	switch ($orderColumn) {
		
		case "url" :
		$currOrder = $urlOrder;
		$urlOrder = ($urlOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "emailType" :
		$currOrder = $emailTypeOrder;
		$emailTypeOrder = ($emailTypeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sortOrder" :
		$currOrder = $sortOrderOrder;
		$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
		break;		
		/*case "textContent" :
		$currOrder = $textContentOrder;
		$textContentOrder = ($textContentOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "htmlContent" :
		$currOrder = $htmlContentOrder;
		$htmlContentOrder = ($htmlContentOrder != "DESC" ? "DESC" : "ASC");
		break;		*/
		default :
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
	}
	}
	
	// Specify Page no. settings
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	// Query to get the list of Show Me offers
	$selectQuery = "SELECT *
					FROM ShowMeOffers";
	
	$result = mysql_query($selectQuery);
	$numRecords = mysql_num_rows($result);
	
	$totalPages = ceil($numRecords/$recPerPage);
	if ($numRecords > 0)
	$currentPage = " Page $page "."/ $totalPages";
	
	// Prepare query to fetch the records for only current page
	 $selectQuery .= " ORDER BY ";
	if ($orderColumn == "sortOrder") {
	 	$selectQuery .= " 0x41 + ";
	 }	 
	$selectQuery .= $orderColumn." $currOrder";
	$selectQuery .= " LIMIT $startRec, $recPerPage";
	
	$result = mysql_query($selectQuery);
	
	if ($result) {	
		
		if (mysql_num_rows($result) > 0) {
			
			$sortLink = "$PHP_SELF?menuId=$menuId&filter=$filter&alpha=$alpha&exactMatch=$exactMatch&recPerPage=$recPerPage";
			
			// Prepare Next/Prev/First/Last links
			if ($numRecords > ($endRec + 1)) {
				$nextPage = $page + 1;
				$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
				$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
			}
			if ($page != 1) {
				$prevPage = $page - 1;
				$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
				$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
			}
						
			while ($row = mysql_fetch_object($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$title = ascii_encode($row->title);
				
				$emailType = $row->emailType;
				
				if ($row->emailType == '')
					$emailType = "No eMail";
									
				$offerList .= "<tr class=$bgcolorClass>
					<td>$title</td>
					<td>$row->url</td>
					<td>$emailType</td>
					<td>$row->sortOrder</td>					
					<td><a href='JavaScript:void(window.open(\"addOffer.php?menuId=$menuId&menuFolder=$menuFolder&id=".$row->id."\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$message = "No records exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	$hidden = "<input type=hidden name=menuId value='$menuId'>
			<input type=hidden name=id value='$id'>";
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addOffer.php?menuId=$menuId&menuFolder=$menuFolder\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	

	$reportLinks = "<a href='reportByDate.php?menuId=$menuId'>Report By Date</a> &nbsp; &nbsp; <a href='reportBySrc.php?menuId=$menuId'>Report By SourceCode</a>";

	$sortOffersLink = "<a href='JavaScript:void(window.open(\"sortOffers.php?menuId=$menuId&menuFolder=$menuFolder\",\"\",\"scrollbars=yes, resizable=yes\"));'>Sort Offers</a>";
	
	$t->set_var(array(  "ACTION" => $PHP_SELF,
	"HIDDEN" => "$hidden",
	"SORT_LINK" => "$sortLink",
	"REC_PER_PAGE" => "$recPerPage",
	"NEXT_PAGE_LINK" => "$nextPageLink",
	"PREV_PAGE_LINK" => "$prevPageLink",
	"FIRST_PAGE_LINK" => "$firstPageLink",
	"LAST_PAGE_LINK" => "$lastPageLink",
	"CURRENT_PAGE" => "$currentPage",
	"REPORT_LINKS" => "$reportLinks",
	"SORT_OFFERS_LINK" => "$sortOffersLink",
	"ADD_BUTTON" => "$addButton",
	"TITLE_ORDER" => "$titleOrder",
	"URL_ORDER" => "$urlOrder",
	"EMAIL_TYPE_ORDER" => "$emailTypeOrder",
	"SORT_ORDER_ORDER" => "$sortOrderOrder",	
	"OFFER_LIST" => "$offerList"
	));
	
	include("../mainParse.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
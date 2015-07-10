<?php

/*********

Script to Display List/Delete Offer information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Offer Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// If clicked on Show Redirect link or New record Added
	
	if ($showRedirect) {
		$redirectLink = $sGblOfferRedirectsPath . "?src=". strtolower($offerCode);
		
		$showRedirect = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Redirect:</b>&nbsp; &nbsp;<a href='JavaScript:void(window.open(\"$redirectLink\",\"\",\"\"));'>" . $redirectLink . "</a></font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> AOL Redirect:</b>&nbsp; &nbsp;".htmlspecialchars("<A href=\" " . $redirectLink . " \">")."Click Here".htmlspecialchars("</a>")."</font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Pixel Tracking:</b>&nbsp; &nbsp;".htmlspecialchars("<IMG src=\"" . $sGblOfferPixelsTrackingPath . "?s=$offerCode\" width=\"1\" height=\"1\">")."</font></center>";		
	}
	
	if ($delete) {
		
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edDeactivatedOffers
			 		    WHERE id = '$id'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
			
		if ($result) {
			// Delete from OfferCategoryRel
			$deleteQuery = "DELETE FROM edOfferCategoryRel
			   			    WHERE offerId = '$id'"; 
			$result = mysql_query($deleteQuery);
		} else {
			echo mysql_error();
		}
	}
	
	// If offer deactivated
	if ($activate) {
		
		// if record activated...
		// get New SeqNo and change offercode accordingly
		
		// get three digit minimum seqNo available for the selected company
		// Check if the company of this offer exists...
		$checkQuery = "SELECT edOfferCompanies.id, edOfferCompanies.code
					   FROM   edOfferCompanies, edDeactivatedOffers
					   WHERE  edOfferCompanies.id = edDeactivatedOffers.companyId
					   AND    edDeactivatedOffers.id = '$id'";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0) {
			while ( $checkRow = mysql_fetch_object($checkResult)) {
				$companyId = $checkRow->id;
				$companyCode = $checkRow->code;
			}
			$seqNo = 0;
			
			$seqQuery = "SELECT MAX(seqNo) lastSeqNo
					FROM   edOffers
					WHERE  companyId='$companyId'";
			$seqResult = mysql_query($seqQuery);
			while ($seqRow = mysql_fetch_object($seqResult)) {
				$lastSeqNo = $seqRow->lastSeqNo;
			}
			//$seqNo = (int)$lastSeqNo + 1;
			$seqQuery = "SELECT seqNo
					 FROM   edOffers
					 WHERE  companyId = '$companyId'
					 ORDER BY seqNo";
			$seqResult = mysql_query($seqQuery);
			$i = 1;
			if (mysql_num_rows($seqResult) >0 ) {
				while ($seqRow = mysql_fetch_object($seqResult)) {
					if ($i == $seqRow->seqNo) {
						$i++;
						continue;
					} else {
						$seqNo = $i;
						break;
					}
				}
				if ($seqNo == 0 ) {
					$seqNo = $lastSeqNo + 1;
				}
				
			} else {
				$seqNo = 1;
			}
			
			$offerCode = $companyCode.$seqNo;
			//echo "offerCode".$offerCode;
			$deactQuery	= "INSERT INTO edOffers(offerCode, SQLofferCode, companyId, activationDate, expirationDate, headline, description, url, redirectUrl,
						  	  notes, displayInFrame, seqNo, edited, finalApproval, popOption, popupId)
					   SELECT '$offerCode', '$offerCode', companyId, activationDate, expirationDate, headline, description, url, redirectUrl, 
							  notes, displayInFrame, '$seqNo', edited, finalApproval, popOption, popupId 
					   FROM   edDeactivatedOffers
					   WHERE  id = '$id'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Activated: $deactQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$deactResult = mysql_query($deactQuery);			
			
			if ($deactResult) {
				// Delete from the offers table
				$deleteQuery = "DELETE FROM edDeactivatedOffers
			 		    WHERE id = '$id'"; 
				$result = mysql_query($deleteQuery);
				if (!($result)) {
					echo mysql_error();
				}
			}
		} else {
			$sMessage = "The Company Of This Offer Does Not Exist...";
		}
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "offerCode";
		$offerCode = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	// Set Order column as Current Order and set sorting order of it.
	// Don't change the order if Prev/Next/Last/First clicked, i.e. currOrder will be there
	if (!($currOrder)) {
		switch ($orderColumn) {
			case "description" :
			$currOrder = $descriptionOrder;
			$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "headline" :
			$currOrder = $headlineOrder;
			$headlineOrder = ($headlineOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "companyName" :
			$currOrder = $companyNameOrder;
			$companyNameOrder = ($companyNameOrder != "DESC" ? "DESC" : "ASC");
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
			default:
			$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter' || OC.companyName = '$filter' || headline = '$filter' || description = '$filter'" : " offerCode like '%$filter%' || OC.companyName LIKE '%$filter%' || headline like '%$filter%' || description like '%$filter%' ";
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
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&filter=$filter&exactMatch=$exactMatch&exclude=$exclude&searchIn=$searchIn&recPerPage=$recPerPage";
	
	// Query to get the list of Categories
	$selectQuery = "SELECT O.*, OC.companyName
					FROM edDeactivatedOffers O, edOfferCompanies OC
					WHERE O.companyId = OC.id
					$filterPart ";
	if ($orderColumn == 'offerCode') {
		$selectQuery .= " ORDER BY substring(offerCode,1,3) $currOrder, substring(offerCode,4)+0 $currOrder ";
	} else {
		$selectQuery .= " ORDER BY $orderColumn $currOrder ";
	}
		
	// Count no of records and total pages
	$result = mysql_query($selectQuery);
	
	$numRecords = mysql_num_rows($result);
	
	// Specify Page no. settings
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
				if ($showRedirect && $offerCode == $row->offerCode) {
					$offerCodeDisplay = "<b>".$row->offerCode."</b>";
				} else{
					$offerCodeDisplay = $row->offerCode;
				}
				
				$dispHeadline = ascii_encode(substr($row->headline,0,50));
				$dispDescription = ascii_encode(substr($row->description,0,50));
				$offerList .= "<tr class=$bgcolorClass>
					<td>$offerCodeDisplay</td>
					<td>$dispHeadline ...</td>					
					<td>$dispDescription ...</td>					
					<td>$row->companyName</td>
					<td><a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					&nbsp;| <a href='$sortLink&id=".$row->id."&activate=activate' >Activate</a><br>
					&nbsp;| <a href='".$sortLink."&offerCode=".$row->offerCode."&showRedirect=true&page=$page&orderColumn=$orderColumn&currOrder=$currOrder'>Show Redirect & Pixel Tracking</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
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
		default:
		$allFieldsSelected = "selected";
	}
	
	$searchInOptions = "<option value='' $allFieldsSelected>All Fields
						<option value='headline' $headlineSelected>Headline
						<option value='description' $descriptionSelected>Description
						<option value='offerCode' $offerCodeSelected>OfferCode
						<option value='companyName' $companyNameSelected>Offer Company";
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$reportsLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Offers Management</a>
					&nbsp; &nbsp;<a href='report.php?iMenuId=$iMenuId'>Offer Redirects Report</a>
					&nbsp; &nbsp;<a href='pixelReport.php?iMenuId=$iMenuId'>Offer Pixels Report</a>
					&nbsp; &nbsp;<a href='offersExpiringReport.php?iMenuId=$iMenuId'>Offers Expiring Report</a>
					&nbsp; &nbsp;<a href='orphanOffersReport.php?iMenuId=$iMenuId'>Orphan Offers Report</a>";
	
	$frameMgmntLink = "<a href='JavaScript:void(window.open(\"frameMgmnt.php?iMenuId=$iMenuId\",\"\",\"\"))'>Frame Managemnt</a>";
			
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

<?php echo $showRedirect;?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5><?php echo $reportsLink;?> &nbsp; &nbsp; <?php echo $frameMgmntLink;?></td>
</tr>
<tr><td>Filter By</td><td colspan=4><input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td colspan=4><input type=text name=exclude value='<?php echo $exclude;?>'></tR>
<tr><td>Search In</td><td><select name=searchIn>
	<?php echo $searchInOptions;?>
	</select></td><td colspan=3><input type=submit name=viewReport value='View Report'></td></tr>
<tr><td colspan=5 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=page value='<?php echo $page;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	

<tr>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>" class=header>OfferCode</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=headline&headlineOrder=<?php echo $headlineOrder;?>" class=header>Headline</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>" class=header>Description</a></th>	

	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=companyName&companyNameOrder=<?php echo $companyNameOrder;?>" class=header>Offer Company</a></th>
	<th width=18%>&nbsp; </th>
</tr>
<?php echo $offerList;?>
<TR><TD colspan=5 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><td colspan=5><?php echo $reportsLink;?> &nbsp; &nbsp; <?php echo $frameMgmntLink;?></td>
</tr>

</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
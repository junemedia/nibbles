<?php

/*********

Script to Display List/Delete Offer information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Editorial Offers Management - List/Delete Offers";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// If clicked on Show Redirect link or New record Added
	
	if ($showRedirect) {
		
		$redirectLink = $sGblOfferRedirectsPath . "?src=". strtolower($offerCode) ;
		
		$showRedirect = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Redirect:</b>&nbsp; &nbsp;<a href='JavaScript:void(window.open(\"$redirectLink\",\"\",\"\"));'>" . $redirectLink . "</a></font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> AOL Redirect:</b>&nbsp; &nbsp;".htmlspecialchars("<A href=\" " . $redirectLink . " \">")."Click Here".htmlspecialchars("</a>")."</font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Pixel Tracking:</b>&nbsp; &nbsp;".htmlspecialchars("<IMG src=\"" . $sGblOfferPixelsTrackingPath . "?s=$offerCode\" width=\"3\" height=\"2\">")."</font></center>";		
	}
	
	if ($delete) {
		
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edOffers
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
	if ($deactivate) {
		
		// if record deactivated...
		$deactQuery	= "INSERT INTO edDeactivatedOffers(SQLOfferCode, offerCode, companyId, activationDate, expirationDate, headline, description, url, redirectUrl,
						  	  notes, displayInFrame, seqNo, edited, finalApproval, popOption, popupId)
					   SELECT SQLOfferCode, offerCode, companyId, activationDate, expirationDate, headline, description, url, redirectUrl, 
							  notes, displayInFrame, seqNo, edited, finalApproval, popOption, popupId 
					   FROM   edOffers
					   WHERE  id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Deactivated: $deactQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$deactResult = mysql_query($deactQuery);
		if ($deactResult) {
			// Delete from the offers table
			$deleteQuery = "DELETE FROM edOffers
			 		    WHERE id = '$id'"; 
			$result = mysql_query($deleteQuery);
			if (!($result)) {
				echo mysql_error();
			}
		}
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "offerCode";
		$offerCodeOrder = "ASC";
	}
	
	$filter = ereg_replace("aaazzz","&",$filter);
	$exclude = ereg_replace("aaazzz","&",$exclude);	
	
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
	
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
			case "specialStatus" :
			$currOrder = $specialStatusOrder;
			$specialStatusOrder = ($specialStatusOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "dateLastUpdated" :
			$currOrder = $dateLastUpdatedOrder;
			$dateLastUpdatedOrder = ($dateLastUpdatedOrder != "DESC" ? "DESC" : "ASC");
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
			case "url" :
			$filterPart .= ($exactMatch == 'Y') ? "url = '$filter'" : "url like '%$filter%'";
			break;
			case "description" :
			$filterPart .= ($exactMatch == 'Y') ? "description = '$filter'" : "description like '%$filter%'";
			break;			
			case "companyName" :
			$filterPart .= ($exactMatch == 'Y') ? "OC.companyName = '$filter'" : "OC.companyName like '%$filter%'";
			break;
			case "offerCode" :
			$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter'" : "offerCode like '%$filter%'";
			break;
			case "dateLastUpdated" :
			$filterPart .= ($exactMatch == 'Y') ? "dateLastUpdated = '$filter'" : "dateLsatUpdated like '%$filter%'";
			break;
			default:
			$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter' || OC.companyName = '$filter' || headline = '$filter' || description = '$filter'  || dateLastUpdated = '$filter'" : " offerCode like '%$filter%' || OC.companyName LIKE '%$filter%' || headline like '%$filter%' || description like '%$filter%' || dateLastUpdated like '%$filter%'";
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
			case "url" :
			$filterPart .= "url NOT LIKE '%$exclude%'";
			break;
			case "companyName" :
			$filterPart .= "OC.companyName NOT LIKE '%$exclude%'";
			break;
			case "offerCode" :
			$filterPart .= "offerCode NOT LIKE '%$exclude%'";
			break;
			case "dateLastUpdated" :
			$filterPart .= "dateLastUpdated NOT LIKE '%$exclude%'";
			break;
			default:
			$filterPart .= "offerCode NOT LIKE '%$exclude%' && OC.companyName NOT LIKE '%$exclude%' && headline NOT LIKE '%$exclude%' && description NOT LIKE '%$exclude%' && dateLastUpdated NOT LIKE '%$exclude%'" ;
		}
		$filterPart .= " ) ";
		
	}
	
	$filterEncoded = ereg_replace("&","aaazzz",$filter);
	$filterEncoded = urlencode($filterEncoded);
	$excludeEncoded = ereg_replace("&","aaazzz",$exclude);			
	$excludeEncoded = urlencode($excludeEncoded);		
	
	$filter = stripslashes($filter);
	$exclude = stripslashes($exclude);
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&reportMenuId=$reportMenuId&reportMenuFolder=$reportMenuFolder&filter=$filter&exactMatch=$exactMatch&exclude=$exclude&searchIn=$searchIn&recPerPage=$recPerPage";
	
	$filter = ascii_encode(stripslashes($filter));
	$exclude = ascii_encode(stripslashes($exclude));
	
	
	// Query to get the list of Categories
	$selectQuery = "SELECT O.*, OC.companyName
					FROM edOffers O, edOfferCompanies OC
					WHERE O.companyId = OC.id
					$filterPart 	";
	if ($orderColumn == 'offerCode') {
		$selectQuery .= " ORDER BY substring(offerCode,1,3) $currOrder, substring(offerCode,4)+0 $currOrder ";
	} else {
		$selectQuery .= " ORDER BY $orderColumn $currOrder ";
	}

	// Count no of records and total pages
	$result = mysql_query($selectQuery);
	//echo $selectQuery;
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
		$sExportData = "OfferCode\tHeadling\tDescription\tCompanyName\tSpecialStatus\tLastUpdate\tActivationDate\tExpirationDate\tURL\tNotes\n";
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
				//$dispHeadline = stripslashes(substr($row->headline,0,50));
				//$filter = "index.php?i=1&j=2";
				
				$dispDescription = ascii_encode(substr($row->description,0,50));
				$offerList .= "<tr class=$bgcolorClass>
					<td>$offerCodeDisplay</td>
					<td>$dispHeadline ...</td>
					<td>$dispDescription ...</td>
					<td>$row->companyName</td>
					<td>$row->specialStatus</td>
					<td nowrap>$row->dateLastUpdated</td>
					<td><a href='JavaScript:void(window.open(\"addOffer.php?iMenuId=$iMenuId&id=".$row->id."&offerCode=".$row->offerCode."&recPerPage=$recPerPage&filter=$filterEncoded&exactMatch=$exactMatch&exclude=$excludeEncoded&searchIn=$searchIn&backTo=index\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp;| <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					&nbsp;| <a href='$sortLink&id=".$row->id."&deactivate=deactivate' >Deactivate</a><br>
					&nbsp;| <a href='".$sortLink."&offerCode=".$row->offerCode."&showRedirect=true&page=$page&orderColumn=$orderColumn&currOrder=$currOrder'>Show Redirect & Pixel Tracking</a></td></tr>";
				$sExportData .= "$offerCodeDisplay\t\"$row->headline\"\t\"$row->description\"\t$row->companyName\t$row->specialStatus\t$row->dateLastUpdated\t$row->activationDate\t$row->expirationDate\t$row->url\t$row->notes\n";
			}
			
		} else {

			$sMessage = "No Records Exist...";
			
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addOffer.php?iMenuId=$iMenuId&recPerPage=$recPerPage&filter=$filterEncoded&exactMatch=$exactMatch&exclude=$excludeEncoded&searchIn=$searchIn&backTo=index\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	
	if ($exactMatch == 'Y') {
		$exactMatchChecked = "checked";
	}
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	
	switch ($searchIn) {
		case 'headline':
		$headlineSelected = "selected";
		break;
		case 'description':
		$descriptionSelected = "selected";
		break;
		case 'url':
		$urlSelected = "selected";
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
						<option value='url' $urlSelected>Url
						<option value='description' $descriptionSelected>Description
						<option value='offerCode' $offerCodeSelected>OfferCode
						<option value='companyName' $companyNameSelected>Offer Company";
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	if ($reportMenuId) {
		$hidden .=  "<input type=hidden name=reportMenuId value='$reportMenuId'>
					 <input type=hidden name=reportMenuFolder value='$reportMenuFolder'>";
		
		$reportsLink = "<a href='../$reportMenuFolder/index.php?iMenuId=$reportMenuId'>Back to Offer Reports Menu</a>";
	} else {
	$reportsLink = "<a href='report.php?iMenuId=$iMenuId'>Offer Redirects Report</a>
					&nbsp; &nbsp; <a href='pixelReport.php?iMenuId=$iMenuId'>Offer Pixels Report</a>
	 				&nbsp; &nbsp; <a href='offersExpiringReport.php?iMenuId=$iMenuId'>Offers Expiring Report</a>
					&nbsp; &nbsp; <a href='orphanOffersReport.php?iMenuId=$iMenuId'>Orphan Offers Report</a>
					&nbsp; &nbsp; <a href='deactOffersReport.php?iMenuId=$iMenuId'>Deactivated Offers Report</a>
					&nbsp; &nbsp; <a href='JavaScript:void(window.open(\"frameMgmnt.php?iMenuId=$iMenuId\",\"\",\"\"))'>Frame Managemnt</a>";
	
	$ssSortOffersLink = "<A href='JavaScript:void(window.open(\"ssOffers.php?iMenuId=$iMenuId&backTo=index&".SID."\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Sort Special Status Offers</a>";
	
	}
		
	$sExportData .= "\n\nRun Date / Time: $sRunDateAndTime";
	if ($sExportExcel) {
			$sFileName = "offersMgmnt".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

			$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
			if ($rFpFile) {
				fputs($rFpFile, $sExportData, strlen($sExportData));
				fclose($rFpFile);

				echo "<script language=JavaScript>
					void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
				  </script>";
			} else {
				$sMessage = "Error exporting data...";
			}
	}
	
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


<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td><?php echo $addButton;?></td><td><?php echo $ssSortOffersLink;?></td></tr>
<tr><td colspan=7><?php echo $reportsLink;?></td>
</tr>
<tr><td>Filter By</td><td colspan=4><input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td><input type=text name=exclude value='<?php echo $exclude;?>'></tr>
<tr><td>Search In</td><td><select name=searchIn>
	<?php echo $searchInOptions;?>
	</select></td><td><input type=submit name=viewReport value='View Report'>
	 &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
	</td></tr>
<tr><td colspan=7 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=page value='<?php echo $page;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	

<tr>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>" class=header>OfferCode</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=headline&headlineOrder=<?php echo $headlineOrder;?>" class=header>Headline</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>" class=header>Description</a></th>	
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=companyName&companyNameOrder=<?php echo $companyNameOrder;?>" class=header>Offer Company</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=specialStatus&specialStatusOrder=<?php echo $specialStatusOrder;?>" class=header>Special Status</a></th>
	<th align=left><a href="<?php echo $sortLink;?>&orderColumn=dateLastUpdated&dateLastUpdatedOrder=<?php echo $dateLastUpdatedOrder;?>" class=header>Last Updated On</a></th>
	<th width=18%>&nbsp; </th>
</tr>
<?php echo $offerList;?>
<TR><TD colspan=7 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr><Td><?php echo $addButton;?></td></tr>
<tr><td colspan=7><?php echo $reportsLink;?></td>
</tr>

</table>
</form>


<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to List/Delete OT Pages

**********/

include("../../includes/paths.php");

include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles OT Pages - List/Delete OT Pages";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		// if record deleted
		
		/*************** check related entries first ******************/
		// check any offer related to this category
		$sCheckQuery = "SELECT *
					   FROM   pageMap
					   WHERE  pageId = '$iId'";
		$rCheckResult = dbQuery($sCheckQuery);
		
		if (dbNumRows($rCheckResult) == 0) {
		
		// get the page name to use in deleting page directories/files
		$sTempQuery = "SELECT pageName
					   FROM   otPages
					   WHERE  id = '$iId'";
		$rTempResult = dbQuery($sTempQuery);
		while ( $oTempRow = dbFetchObject($rTempResult)) {
			$sPageName = $oTempRow->pageName;
		}
		$sDeleteQuery = "DELETE FROM otPages
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {			
			echo dbError();
						
		} else {
			
			$rImageDir = opendir("$sGblOtPagesPath/$sPageName/images/");
			if ($rImageDir) {
				while (($sFile = readdir($rImageDir)) != false) {	
					if (!is_dir("$sGblOtPagesPath/$sPageName/images/$sFile")) {
						unlink("$sGblOtPagesPath/$sPageName/images/$sFile");
					}
				}
				closedir($rImageDir);				
				
			}
			
			@rmdir("$sGblOtPagesPath/$sPageName/images");
			
			$rFileDir = opendir("$sGblOtPagesPath/$sPageName/headers/");
			if ($rFileDir) {

				while (($sFile = readdir($rFileDir)) != false) {	
					if (!is_dir("$sGblOtPagesPath/$sPageName/headers/$sFile")) {
					
						unlink("$sGblOtPagesPath/$sPageName/headers/$sFile");
					}
				}
				closedir($rFileDir);
			}
			
			@rmdir("$sGblOtPagesPath/$sPageName/headers");
			
			@rmdir("$sGblOtPagesPath/$sPageName");
			$sPage1Name = $sPageName . ".php";
			$sPage2Name = $sPageName . "_2.php";
			unlink("$sGblOtPagesPath/$sPage1Name");
			unlink("$sGblOtPagesPath/$sPage2Name");
			
		}
		// reset $id
		$iId = '';
		} else {
			$sMessage = "Page can not be deleted. Offer related to this page...";
		}
	}
	

	include("../../includes/adminHeader.php");
		
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "pageName";
		$sPageNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		case "title" :
		$sCurrOrder = $sTitleOrder;
		$sTitleOrder = ($sTitleOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "noOfOffers" :
		$sCurrOrder = $sNoOfOffersOrder;
		$sNoOfOffersOrder = ($sNoOfOffersOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "offersByPageMap" :
		$sCurrOrder = $sOffersByPageMapOrder;
		$sOffersByPageMapOrder = ($sOffersByPageMapOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "offersByCatMap" :
		$sCurrOrder = $sOffersByCatMapOrder;
		$sOffersByCatMapOrder = ($sOffersByCatMapOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "redirectTo" :
		$sCurrOrder = $sRedirectToOrder;
		$sRedirectToOrder = ($sRedirectToOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "isCoBrand" :
		$sCurrOrder = $sIsCobrandOrder;
		$sIsCobrandOrder = ($sIsCobrandOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$sCurrOrder = $sPageNameOrder;
		$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
	}	
	
	
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		
		switch ($sSearchIn) {
			case "pageName" :
			$sFilterPart .= ($iExactMatch) ? "pageName = '$sFilter'" : "pageName like '%$sFilter%'";
			break;
			case "redirectTo" :
			$sFilterPart .= ($iExactMatch) ? "redirectTo = '$sFilter'" : "redirectTo like '%$sFilter%'";
			break;
			case "all":			
			$sFilterPart .= ($iExactMatch) ? "pageName = '$sFilter' || redirectTo = '$sFilter'" : " pageName like '%$sFilter%' || redirectTo LIKE '%$sFilter%'";
			break;
		}
		
		$sFilterPart .= ") ";
	}
	
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sExclude) {
			case "pageName" :
			$sFilterPart .= "pageName NOT LIKE '%$sExclude%'";
			break;
			case "redirectTo" :
			$sFilterPart .= "redirectTo NOT LIKE '%$sExclude%'";
			break;
			
			case "all" :			
			$sFilterPart .= "pageName NOT LIKE '%$sExclude%' && redirectTo NOT LIKE '%$sExclude%' " ;
			break;
		}
		$sFilterPart .= " ) ";
		
	}
	
	
	
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=".urlencode(stripslashes($sFilter))."&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage";
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	// Query to get the list of BDPartners
	$sSelectQuery = "SELECT otPages.*, categories.title as catTitle
					FROM   otPages LEFT JOIN categories ON otPages.offersByCatMap = categories.id
					WHERE 1 $sFilterPart 	
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rResult = dbQuery($sSelectQuery);
	echo dbError();
	
	$iNumRecords = dbNumRows($rResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rResult = dbQuery($sSelectQuery);
	if ($rResult) {
		
		if (dbNumRows($rResult) > 0) {
			// Prepare Next/Prev/First/Last links
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Last</a>";
			}
			if ($iPage != 1) {
				$iPrevPage = $iPage-1;
				$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
				$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
			}
			
			
	while ($oRow = dbFetchObject($rResult)) {
				
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sPageList .= "<tr class=$sBgcolorClass><td>$oRow->pageName</td>
					<td>$oRow->title</td>
					<td>$oRow->noOfOffers</td>					
					<td>$oRow->offersByPageMap</td>
					<td>$oRow->catTitle</td>
					<td>$oRow->isCobrand</td>
					<td>$oRow->redirectTo</td>
					<td nowrap><a href='JavaScript:void(window.open(\"addPage.php?iMenuId=$iMenuId&iId=".$oRow->id."&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp;<a href='JavaScript:void(window.open(\"cloneOtPage.php?iMenuId=$iMenuId&sPageName=".$oRow->pageName."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Clone</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
					&nbsp;<a href='JavaScript:void(window.open(\"offers.php?iMenuId=$iMenuId&iId=".$oRow->id."\",\"offers\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Offers</a> 
					&nbsp;<a href='JavaScript:void(window.open(\"generatePage.php?iMenuId=$iMenuId&iId=".$oRow->id."\",\"genePage\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Generate Regular Page</a> 
					&nbsp;<a href='JavaScript:void(window.open(\"generateNewPage.php?iMenuId=$iMenuId&iId=".$oRow->id."\",\"genePage\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Generate B Page</a> 
					&nbsp;<a href='JavaScript:void(window.open(\"generatePageC.php?iMenuId=$iMenuId&iId=".$oRow->id."\",\"genePage\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Generate C Page</a> 
					&nbsp;<a href='JavaScript:void(window.open(\"$sGblOtPagesUrl/$oRow->pageName.php?iMenuId=$iMenuId&iId=".$oRow->id."\",\"genePage\",\"scrollbars=yes, resizable=yes, status=yes, menubar=yes, toolbar=yes, location=yes\"));'>View Page</a> 
					</td></tr>";					
	}

	} else {
			$sMessage = "No Records Exist...";
	}
	}	
	
	if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}	
	
	switch ($sSearchIn) {
		case 'redirectTo':
		$sRedirectToSelected = "selected";
		break;		
		case 'all':
		$sAllFieldsSelected = "selected";
		break;
		default:
		$sPageNameSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='' $sAllFieldsSelected>All Fields
						<option value='pageName' $sPageNameSelected>Page Name
						<option value='redirectTo' $sRedirectToSelected>Redirect To";
	
	// page name, redirect url, 
	
	// Display Add Button

	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addPage.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";				
		
		
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
		
	?>
<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record?'))
					{							
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['iId'].value=id;
						document.form1.submit();								
					}
				}						
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=7><?php echo $sAddButton;?></td>
	<td><a href='JavaScript:void(window.open("generatePage.php?iMenuId=<?php echo $iMenuId;?>&sRegenerateAll=y","genePage","scrollbars=yes, resizable=yes, status=yes"));' class=header>Regenerate All Regular Pages</a><br>
		<a href='JavaScript:void(window.open("generateNewPage.php?iMenuId=<?php echo $iMenuId;?>&sRegenerateAll=y","genePage","scrollbars=yes, resizable=yes, status=yes"));' class=header>Regenerate All B Pages</a><br>
		<a href='JavaScript:void(window.open("generatePageC.php?iMenuId=<?php echo $iMenuId;?>&sRegenerateAll=y","genePage","scrollbars=yes, resizable=yes, status=yes"));' class=header>Regenerate All C Pages</a>
	</td>
</tr>

<tr><td>Filter By</td><td colspan=4><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td><input type=text name=sExclude value='<?php echo $sExclude;?>'></tR>
<tr><td>Search In</td><td><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></td><td><input type=submit name=sViewOffers value='Query'></td></tr>
<tr><td colspan=8 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>

<tr>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=pageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=title&sTitleOrder=<?php echo $sTitleOrder;?>" class=header>Title</a></td>	
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=noOfOffers&sNoOfOffersOrder=<?php echo $sNoOfOffersOrder;?>" class=header>No. Of Offers</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=offersByPageMap&sOffersByPageMapOrder=<?php echo $sOffersByPageMapOrder;?>" class=header>Offers By Page Map</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=catTitle&sOffersByCatMapOrder=<?php echo $sOffersByCatMapOrder;?>" class=header>Offers By Cat Map</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=isCobrand&sIsCobrandOrder=<?php echo $sIsCobrandOrder;?>" class=header>Is Cobrand</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=redirectTo&sRedirectToOrder=<?php echo $sRedirectToOrder;?>" class=header>Redirect To</a></td>				
	<td>&nbsp; </td>
</tr>
<?php echo $sPageList;?>
<tr><td><?php echo $sAddButton;?></td></tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
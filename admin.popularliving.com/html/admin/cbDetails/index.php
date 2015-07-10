<?php

/*********

Script to List/Delete Co-Brand Details

**********/

include("../../includes/paths.php");

include("$sGblLibsPath/stringFunctions.php");

session_start();


$sPageTitle = "Nibbles Co-Brand Details - List/Delete Co-Brand Details";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {


	if ($sShowUrl) {
		// get page name
		$sPageQuery = "SELECT otPages.pageName
			   		   FROM   coBrandDetails, otPages
			   		   WHERE  coBrandDetails.pageId = otPages.id
			   		   AND	  coBrandDetails.id = '$iId'";
		$rPageResult = dbQuery($sPageQuery);
		while ($oPageRow = dbFetchObject($rPageResult)) {
			$sPageName = $oPageRow->pageName;
		}

		if( $sShowUrl == "WWW" ) {
			$sCoBrandUrl = "http://www.popularliving.com/p/$sPageName.php?cbId=$iId";
		} else {
			$sCoBrandUrl = "$sGblSiteRoot/p/$sPageName.php?cbId=$iId";
		}

		$sCoBrandUrl = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Co-Brand URL:</b>&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sCoBrandUrl."\",\"\", \"\"));'>" . $sCoBrandUrl . "</a></font></center>";
	}


	if ($sDelete) {
		if ( isAdmin() ) {
			// if record deleted

			$sDeleteQuery = "DELETE FROM coBrandDetails
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
			}
			// reset $id
			$iId = '';
		} else {
			$sMessage = "You must be a system Admin to delete a coBrand.";
		}

	}


	include("../../includes/adminHeader.php");

	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "companyName";
		$sPageNameOrder = "ASC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {

		case "isCoBrand" :
		$sCurrOrder = $sIsCobrandOrder;
		$sIsCobrandOrder = ($sIsCobrandOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "shortDescription" :
		$sCurrOrder = $sShortDescriptionOrder;
		$sShortDescriptionOrder = ($sShortDescriptionOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "companyName" :
		$sCurrOrder = $sCompanyNameOrder;
		$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "userName" :
		$sCurrOrder = $sUserNameOrder;
		$sUserNameOrder = ($sUserNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sPageNameOrder;
		$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
	}


	// Prepare filter part of the query if filter/exclude specified...
	if ($sFilter != '') {

		$sFilterPart .= " AND ( ";

		switch ($sSearchIn) {
			case "companyName" :
			$sFilterPart .= ($iExactMatch) ? "companyName = '$sFilter'" : "companyName like '%$sFilter%'";
			break;
			case "shortDescription" :
			$sFilterPart .= ($iExactMatch) ? "shortDescription = '$sFilter'" : "shortDescription like '%$sFilter%'";
			break;
			case "pageName" :
			$sFilterPart .= ($iExactMatch) ? "pageName = '$sFilter'" : "pageName like '%$sFilter%'";
			break;
			case "userName" :
			$sFilterPart .= ($iExactMatch) ? "userName = '$sFilter'" : "userName like '%$sFilter%'";
			break;
			case "all":
			$sFilterPart .= ($iExactMatch) ? "shortDescription = '$sFilter' || pageName = '$sFilter' || redirectTo = '$sFilter' || userName = '$sFilter' || companyName= '$sFilter'": " pageName like '%$sFilter%' || redirectTo LIKE '%$sFilter%'|| userName LIKE '%$sFilter%' || companyName like '%$sFilter%' || shortDescription like '%$sFilter%'";
			break;
		}

		$sFilterPart .= ") ";
	}

	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sSearchIn) {
			case "companyName" :
			$sFilterPart .= "companyName NOT LIKE '%$sExclude%'";
			break;

			case "pageName" :
			$sFilterPart .= "pageName NOT LIKE '%$sExclude%'";
			break;

			case "shortDescription" :
			$sFilterPart .= "shortDescription NOT LIKE '%$sExclude%'";
			break;

			case "userName" :
			$sFilterPart .= "userName NOT LIKE '%$sExclude%'";
			break;

			case "all" :
			$sFilterPart .= "shortDescription NOT LIKE '%$sExclude%' && companyName NOT LIKE '%$sExclude%' && pageName NOT LIKE '%$sExclude%' && redirectTo NOT LIKE '%$sExclude%' && userName NOT LIKE '%$sExclude%' " ;
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
	$sSelectQuery = "SELECT coBrandDetails.*, otPages.pageName, nbUsers.userName, partnerCompanies.companyName
					 FROM   coBrandDetails LEFT JOIN nbUsers ON coBrandDetails.repDesignated=nbUsers.id LEFT JOIN partnerCompanies ON partnerCompanies.id=coBrandDetails.partnerId, otPages
					 WHERE  coBrandDetails.pageId = otPages.id
					 		 $sFilterPart 	
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

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $sSelectQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		

	
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

				if ($oRow->id == $iId) {
					$sPageNameDisplay = "<b>$oRow->pageName</b>";
					$sCbHeaderDisplay = "<b>$oRow->cbHeader</b>";
					$sUserNameDisplay = "<b>$oRow->userName</b>";
					$sCompanyNameDisplay = "<b>$oRow->companyName</b>";
				} else {
					$sPageNameDisplay = $oRow->pageName;
					$sCbHeaderDisplay = $oRow->cbHeader;
					$sUserNameDisplay = $oRow->userName;
					$sCompanyNameDisplay = $oRow->companyName;
				}
				$sPageList .= "<tr class=$sBgcolorClass><td>$sPageNameDisplay</td>
					<td>$sCbHeaderDisplay</td>";

				if( $oRow->id == $iId) {
					$sPageList .= "<td><b>".htmlentities($oRow->shortDescription)."</b></td>";
				} else {
					$sPageList .= "<td>".htmlentities($oRow->shortDescription)."</td>";
				}

				$sPageList .= "<td>$sUserNameDisplay</td>
					<td>$sCompanyNameDisplay</td>
					<td nowrap><a href='JavaScript:void(window.open(\"addCbDetails.php?iMenuId=$iMenuId&iId=".$oRow->id."&iRecPerPage=$iRecPerPage&iPage=$iPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>					
					&nbsp; <a href='$sSortLink&iId=".$oRow->id."&sShowUrl=WWW' >Show Page URL</a>
					&nbsp;<a href='JavaScript:void(window.open(\"$sGblOtPagesUrl/$oRow->pageName.php?cbId=".$oRow->id."\",\"genePage\",\"scrollbars=yes, resizable=yes, status=yes, menubar=yes, toolbar=yes, location=yes\"));'>View Page</a> 
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
		case 'all':
		$sAllFieldsSelected = "selected";
		break;
		case 'shortDescription':
		$sShortDescriptionSelected = "selected";
		break;
		case 'userName':
		$sUserNameSelected = "selected";
		break;
		case 'companyName':
		$sCompanyNameSelected = "selected";
		break;
		default:
		$sPageNameSelected = "selected";
	}

	$sSearchInOptions = "<option value='all' $sAllFieldsSelected>All Fields
						<option value='pageName' $sPageNameSelected>Page Name
						<option value='shortDescription' $sShortDescriptionSelected>Short Description
						<option value='companyName' $sCompanyNameSelected>Company Name
						<option value='userName' $sUserNameSelected>User Name";

	// page name, redirect url,

	// Display Add Button

	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addCbDetails.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";


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
<?php echo $sCoBrandUrl;?>	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4><?php echo $sAddButton;?></td>	
</tr>

<tr><td>Filter By</td><td colspan=3><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td colspan=3><input type=text name=sExclude value='<?php echo $sExclude;?>'></tR>
<tr><td>Search In</td><td colspan=3><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></td></tr>
	<tr><td></td><td colspan=3><input type=submit name=sView value='Query'></td></tr>
<tr><td colspan=6 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>

<tr>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=pageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=cbHeader&sCbHeaderOrder=<?php echo $sCbHeaderOrder;?>" class=header>Header</a></td>	
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=shortDescription&sShortDescriptionOrder=<?php echo $sShortDescriptionOrder;?>" class=header>Short Description</a></td>	
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=userName&sUserNameOrder=<?php echo $sUserNameOrder;?>" class=header>User Name</a></td>	
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Company Name</a></td>	
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
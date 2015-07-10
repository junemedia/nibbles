<?php

/*********

Script to Display List/Delete Phone Data

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Manage Phone Data Table";

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		// if record deleted
		
		
		if ($iId != '') {
			$sDeleteQuery = "DELETE FROM phoneData
	 			   		WHERE  id = '$iId'"; 

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sDeleteQuery);
			if (! $rResult) {			
				echo dbError();
			}
			
			// reset $iId
			echo dbError();
			$iId = '';
		}
	}
	
	include("../../includes/adminHeader.php");
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "areaCode";
		$sAreaCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		case "prefix" :
		$sCurrOrder = $sPrefixOrder;
		$sPrefixOrder = ($sPrefixOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "city" :
		$sCurrOrder = $sCityOrder;
		$sCityOrder = ($sCityOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "state" :
		$sCurrOrder = $sStateOrder;
		$sStateOrder = ($sStateOrder != "DESC" ? "DESC" : "ASC");
		break;
		
		default:
		$sCurrOrder = $sAreaCodeOrder;
		$sAreaCodeOrder = ($sAreaCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		
		switch ($sSearchIn) {
			case "areaCode" :
			$sFilterPart .= ($iExactMatch) ? "areaCode = '$sFilter'" : "areaCode like '%$sFilter%'";
			break;	
			case "prefix" :
			$sFilterPart .= ($iExactMatch) ? "prefix = '$sFilter'" : "prefix like '%$sFilter%'";
			break;	
			case "city" :
			$sFilterPart .= ($iExactMatch) ? "city = '$sFilter'" : "city like '%$sFilter%'";
			break;
			case "state" :
			$sFilterPart .= ($iExactMatch) ? "state = '$sFilter'" : "state like '%$sFilter%'";
			break;
					
			
			default:
			$sFilterPart .= ($iExactMatch) ? "city = '$sFilter' || state = '$sFilter' || areaCode = '$sFilter'  || prefix = '$sFilter'" : " city like '%$sFilter%' || state LIKE '%$sFilter%' || areaCode like '%$sFilter%'  || prefix like '%$sFilter%' ";
		}
		
		$sFilterPart .= ") ";
	}
	
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sExclude) {
			case "areaCode" :
			$sFilterPart .= "areaCode NOT LIKE '%$sExclude%'";
			break;
			case "prefix" :
			$sFilterPart .= "prefix NOT LIKE '%$sExclude%'";
			break;
			case "city" :
			$sFilterPart .= "city NOT LIKE '%$sExclude%'";
			break;
			case "state" :
			$sFilterPart .= "state NOT LIKE '%$sExclude%'";
			break;							
			default:
			$sFilterPart .= "areaCode NOT LIKE '%$sExclude%' && prefix NOT LIKE '%$sExclude%' && city NOT LIKE '%$sExclude%' && state NOT LIKE '%$sExclude%' " ;
		}
		$sFilterPart .= " ) ";
		
	}
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage";
	
	// Query to get the list of Categories
	$sSelectQuery = "SELECT *
					FROM phoneData
					WHERE 1 $sFilterPart 	";
	
	
	$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	
	
	$rSelectResult = dbQuery($sSelectQuery);	
	//echo $sSelectQuery. mysql_error();
	
	// Count no of records and total pages
	$rResult = dbQuery($sSelectQuery);
	//echo $selectQuery;
	$iNumRecords = dbNumRows($rResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rResult = dbQuery($sSelectQuery);
	if ($rResult) {
		
		if (dbNumRows($rResult) > 0) {
			// Prepare Next/Prev/First/Last links
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
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
				
				
				$sOfferList .= "<tr class=$sBgcolorClass>
					<td>$oRow->areaCode</td>
					<td>$oRow->prefix</td>
					<td>$oRow->city</td>	
					<td>$oRow->state</td>
					<td nowrap><a href='JavaScript:void(window.open(\"addPhoneData.php?iMenuId=$iMenuId&iId=".$oRow->id."&iId=".$oRow->id."&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					| <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a></td></tr>";
			}			
		} else {
			$sMessage = "No Records Exist...";
		}
	}	
	
	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addPhoneData.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}	
	
	switch ($sSearchIn) {
		case 'areaCode':
		$sAreaCodeSelected = "selected";
		break;
		case 'prefix':
		$sPrefixSelected = "selected";
		break;	
		case 'city':
		$sCitySelected = "selected";
		break;	
		case 'state':
		$sStateSelected = "selected";
		break;
		
		default:
		$sAllFieldsSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='' $sAllFieldsSelected>All Fields
						<option value='areaCode' $sZipSelected>AreaCode
						<option value='prefix' $sZipSelected>Prefix
						<option value='city' $sCitySelected>City
						<option value='state' $sStateSelected>State";
	

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	?>
<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{	
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['iId'].value=id;
						document.form1.submit();							
												
					}
				}	
								
					
				function funcRecPerPage(form1) {
					document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}					
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td><?php echo $sAddButton;?> </td></tr>

<tr><td width=250>Filter By</td><td colspan=4><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td><input type=text name=exclude value='<?php echo $sExclude;?>'></tR>
<tr><td>Search In</td><td><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></td><td><input type=submit name=sViewOffers value='Query'></td></tr>
<tr><td colspan=7 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>

<tr>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=areaCode&sAreaCodeOrder=<?php echo $sAreaCodeOrder;?>" class=header>AreaCode</a></th>
		<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=prefix&sPrefixOrder=<?php echo $sPrefixOrder;?>" class=header>Prefix</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=city&sCityOrder=<?php echo $sCityOrder;?>" class=header>City</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=state&sStateOrder=<?php echo $sStateOrder;?>" class=header>State</a></th>
	
	<th width=18%>&nbsp; </th>
</tr>
<?php echo $sOfferList;?>
<tr><td colspan=7 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>
<tr><Td><?php echo $sAddButton;?> </td></tr>

</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Jokes Management - List/Delete Jokes";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($delete) {
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edJokes
					    WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		$result = mysql_query($deleteQuery);
		
		if (!($result)) {
			echo mysql_error();
		}
		//reset $id to null
		$id = '';
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "headline";
		$headlineOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if(!($currOrder)) {
		switch ($orderColumn) {
			case "description":
			$currOrder = $descriptionOrder;
			$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "activeDate":
			$currOrder = $activeDateOrder;
			$activeDateOrder = ($activeDateOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "inactiveDate":
			$currOrder = $inactiveDateOrder;
			$inactiveDateOrder = ($inactiveDateOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "dateLastActive":
			$currOrder = $dateLastActiveOrder;
			$dateLastActiveOrder = ($dateLastActiveOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "dateInserted":
			$currOrder = $dateInsertedOrder;
			$dateInsertedOrder = ($dateInsertedOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "frontPageDisplay":
			$currOrder = $frontPageDisplayOrder;
			$frontPageDisplayOrder = ($frontPageDisplayOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$currOrder = $headlineOrder;
			$headlineOrder = ($headlineOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($filter != '') {
		switch ($searchIn) {
			case "headline" :
			$filterPart .= ($exactMatch == 'Y') ? "headline = '$filter'" : "headline like '%$filter%'";
			break;
			case "description" :
			$filterPart .= ($exactMatch == 'Y') ? "description = '$filter'" : "description like '%$filter%'";
			break;
			case "activeDate" :
			$filterPart .= ($exactMatch == 'Y') ? "activeDate = '$filter'" : "activeDate like '%$filter%'";
			break;
			case "inactiveDate" :
			$filterPart .= ($exactMatch == 'Y') ? "inactiveDate = '$filter'" : "inactiveDate like '%$filter%'";
			break;
			case "dateLastActive" :
			$filterPart .= ($exactMatch == 'Y') ? "dateLastActive = '$filter'" : "dateLastActive like '%$filter%'";
			break;			
			case "dateInserted" :
			$filterPart .= ($exactMatch == 'Y') ? "dateInserted = '$filter'" : "dateInserted like '%$filter%'";
			break;						
			default:
			// search in date fields only if it's correct date or exact match is not checked
			// otherwise it will convert filter string to date as 0000-00-00 and will display all those				
			if ($exactMatch != 'Y' || checkdate(substr($filter,5,2), substr($filter,8,2), substr(0,4))) 
				$filterPart .= ($exactMatch == 'Y') ? "activeDate = '$filter' || inactiveDate = '$filter' || dateLastActive = '$filter' || dateInserted = '$filter' || headline = '$filter' || description = '$filter'" : " activeDate like '%$filter%' || inactiveDate like '%$filter%' || dateLastActive like '%$filter%' || dateInserted like '%$filter%' || headline like '%$filter%' || description like '%$filter%' ";
			else
				$filterPart .= ($exactMatch == 'Y') ? " headline = '$filter' || description = '$filter'" : " headline like '%$filter%' || description like '%$filter%' ";			
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
	$endRec = $startRec + $recPerPage - 1;	
	
	if ($filterPart != '') {
		$filterPart = "WHERE $filterPart";
	}		
	$filter = ascii_encode(stripslashes($filter));
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&filter=$filter&exactMatch=$exactMatch&searchIn=$searchIn&recPerPage=$recPerPage";
	
	// Query to get the list of Jokes
	$selectQuery = "SELECT *
					FROM   edJokes
					$filterPart 	
					ORDER BY ".$orderColumn." $currOrder";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	// Count no of records and total pages
	$result = mysql_query($selectQuery);
	echo dbError();
	$numRecords = mysql_num_rows($result);
	$totalPages = ceil($numRecords/$recPerPage);
	if ($numRecords > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	}
	// use query to fetch only the rows of the page to be displayed
	$selectQuery .= " LIMIT $startRec, $recPerPage";
	
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
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
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				$dispHeadline = ascii_encode($row->headline);
				$dispDescription = ascii_encode(substr($row->description,0,50));
				$jokesList .= "<tr class=$bgcolorClass><td>$dispHeadline</td>
								<td>$dispDescription...</td>
								<td>$row->activeDate</td>
								<td>$row->inactiveDate</td>
								<td nowrap>$row->dateLastActive</td>
								<td nowrap>$row->dateInserted</td>
								<td>$row->frontPageDisplay</td><td><a href='JavaScript:void(window.open(\"addJoke.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>
								</td>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addJoke.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
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
		case 'activeDate':
		$activeDateSelected = "selected";
		break;
		case 'inactiveDate':
		$inactiveDateSelected = "selected";
		break;
		case 'dateLastActive':
		$dateLastActiveSelected = "selected";
		break;
		case 'dateInserted':
		$dateInsertedSelected = "selected";
		break;
		default:
		$allFieldsSelected = "selected";
	}
	
	$searchInOptions = "<option value='' $allFieldsSelected>All Fields
						<option value='headline' $headlineSelected>Headline
						<option value='description' $descriptionSelected>Description
						<option value='activeDate' $activeDateSelected>Scheduled Active Date
						<option value='inactiveDate' $inactiveDateSelected>Scheduled Inactive Date
						<option value='dateLastActive' $dateLastActiveSelected>Last Active Date
						<option value='dateInserted' $dateInsertedSelected>Date Added";
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortJokesLink = "<a href='JavaScript:void(window.open(\"sortJokes.php?iMenuId=$iMenuId\",\"\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Sort Jokes</a>";
	$myFreeJokesPageLink = "<a href='JavaScript:void(window.open(\"$sGblMyFreeSiteRoot/displayContent.php/content/jokes\",\"\"));'>MyFree Jokes Page</a>";
			
	include("../../includes/adminHeader.php");	
	
?>

<script language=JavaScript>
function confirmDelete(form1,id) {
	if(confirm('Are you sure to delete this record ?')) {							
		document.form1.elements['delete'].value='Delete';
		document.form1.elements['id'].value=id;
		document.form1.submit();								
	}
}
function funcRecPerPage(form1) {
	document.form1.submit();
}
</script>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><?php echo $addButton;?> &nbsp; <?php echo $sortJokesLink;?></td><td colspan=5 align=right><?php echo $myFreeJokesPageLink;?></td></tr>
<tr><td>Filter By</td><td colspan=4><input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Search In</td><td><select name=searchIn>
	<?php echo $searchInOptions;?>
	</select></td><td><input type=submit name=viewReport value='View Report'></td></tr>
<TR><TD colspan=8 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=headline&headlineOrder=<?php echo $headlineOrder;?>'>Headline</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>'>Description</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=activeDate&activeDateOrder=<?php echo $activeDateOrder;?>'>Scheduled Active Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=inactiveDate&inactiveDateOrder=<?php echo $inactiveDateOrder;?>'>Scheduled Inactive Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateLastActive&dateLastActiveOrder=<?php echo $dateLastActiveOrder;?>'>Last Active Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateInserted&dateInsertedOrder=<?php echo $dateInsertedOrder;?>'>Date Added</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=frontPageDisplay&frontPageDisplayOrder=<?php echo $frontPageDisplayOrder;?>'>Front Page</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $jokesList;?>
<TR><TD colspan=8 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr><td colspan=2><?php echo $addButton;?> &nbsp; <?php echo $sortJokesLink;?></td><td colspan=5 align=right><?php echo $myFreeJokesPageLink;?></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
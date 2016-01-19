<?php

/*********

Script to Display List/Delete Trivia

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Trivia Tidbits Management - List/Delete Trivia";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {
		
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edTrivia
					    WHERE  id = '$id'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
		echo mysql_error();
		
		if(!($result)) {
			echo mysql_error();
		}
		//reset $id to null
		$id = '';
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "question";
		$questionOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "answer":
		$currOrder = $answerOrder;
		$answerOrder = ($answerOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "frontActiveDate":
		$currOrder = $frontActiveDateOrder;
		$frontActiveDateOrder = ($frontActiveDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "frontInactiveDate":
		$currOrder = $frontInactiveDateOrder;
		$frontInactiveDateOrder = ($frontInactiveDateOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "listActiveDate":
		$currOrder = $listActiveDateOrder;
		$listActiveDateOrder = ($listActiveDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "listInactiveDate":
		$currOrder = $listInactiveDateOrder;
		$listInactiveDateOrder = ($listInactiveDateOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "dateLastActive":
		$currOrder = $dateLastActiveOrder;
		$dateLastActiveOrder = ($dateLastActiveOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateInserted":
		$currOrder = $dateInsertedOrder;
		$dateInsertedOrder = ($dateInsertedOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$currOrder = $questionOrder;
		$questionOrder = ($questionOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($filter != '') {
		switch ($searchIn) {
			case "question" :
			$filterPart .= ($exactMatch == 'Y') ? "question = '$filter'" : "question like '%$filter%'";
			break;
			case "answer" :
			$filterPart .= ($exactMatch == 'Y') ? "answer = '$filter'" : "answer like '%$filter%'";
			break;
			case "frontActiveDate" :
			$filterPart .= ($exactMatch == 'Y') ? "frontActiveDate = '$filter'" : "frontActiveDate like '%$filter%'";
			break;
			case "frontInactiveDate" :
			$filterPart .= ($exactMatch == 'Y') ? "frontInactiveDate = '$filter'" : "frontInactiveDate like '%$filter%'";
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
				$filterPart .= ($exactMatch == 'Y') ? "frontActiveDate = '$filter' || frontInactiveDate = '$filter' || dateLastActive = '$filter' || dateInserted = '$filter' || question = '$filter' || answer = '$filter'" : " frontActiveDate like '%$filter%' || frontInactiveDate like '%$filter%' || dateLastActive like '%$filter%' || dateInserted like '%$filter%' || question like '%$filter%' || answer like '%$filter%' ";
			else 
				$filterPart .= ($exactMatch == 'Y') ? "question = '$filter' || answer = '$filter'" : " question like '%$filter%' || answer like '%$filter%' ";
		}
	}
	$filter = ascii_encode(stripslashes($filter));
	//// Specify Page no. settings
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
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&filter=$filter&exactMatch=$exactMatch&searchIn=$searchIn&recPerPage=$recPerPage";
	
	// Query to get the list of Trivia Tidbits
	$selectQuery = "SELECT *
					FROM   edTrivia
					$filterPart
					ORDER BY ".$orderColumn." $currOrder";
	
	// Count no of records and total pages
	$result = mysql_query($selectQuery);
	
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
			
		$numRecords = mysql_num_rows($result);
		if ($numRecords > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$triviaList .= "<tr class=$bgcolorClass><td>$row->question</td>
								<td>".substr($row->answer,0,50)."...</td>
								<td nowrap>$row->frontActiveDate</td>
								<td nowrap>$row->frontInactiveDate</td>
								<td nowrap>$row->listActiveDate</td>
								<td nowrap>$row->listInactiveDate</td>
								<td nowrap>$row->dateLastActive</td>
								<td nowrap>$row->dateInserted</td>
								<td><a href='JavaScript:void(window.open(\"addTrivia.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No records exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addTrivia.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	if ($exactMatch == 'Y') {
		$exactMatchChecked = "checked";
	}
	
	switch ($searchIn) {
		case 'question':
		$questionSelected = "selected";
		break;
		case 'answer':
		$answerSelected = "selected";
		break;
		case 'frontActiveDate':
		$frontActiveDateSelected = "selected";
		break;
		case 'frontInactiveDate':
		$frontInactiveDateSelected = "selected";
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
						<option value='question' $questionSelected>Question
						<option value='answer' $descriptionSelected>Answer
						<option value='frontActiveDate' $frontActiveDateSelected>Front Page Active Date
						<option value='frontInactiveDate' $frontInactiveDateSelected>Front Page Inactive Date
						<option value='dateLastActive' $dateLastActiveSelected>Last Active Date
						<option value='dateInserted' $dateInsertedSelected>Date Added";
		
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortTriviaLink = "<a href='JavaScript:void(window.open(\"sortTrivia.php?iMenuId=$iMenuId\",\"\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Sort Trivia</a>";
	//$myFreeSiteLink = "<a href='JavaScript:void(window.open(\"$sGblMyFreeSiteRoot\",\"\"));'>MyFree Front Page</a>";
	$myFreeSiteLink = "<a href='JavaScript:void(window.open(\"http://www.myfree.com/\",\"\"));'>MyFree Front Page</a>";
	
		
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
</script>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><?php echo $addButton;?> &nbsp; <?php echo $sortTriviaLink;?></td><td colspan=5 align=right><?php echo $myFreeSiteLink;?></td></tr>
<tr><td>Filter By</td><td colspan=4><input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Search In</td><td><select name=searchIn>
	<?php echo $searchInOptions;?>
	</select></td><td><input type=submit name=viewReport value='View Report'></td></tr>
<TR><TD colspan=7 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=question&questionOrder=<?php echo $questionOrder;?>'>Question</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=answer&answerOrder=<?php echo $answerOrder;?>}'>Answer</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=frontActiveDate&frontActiveDateOrder=<?php echo $frontActiveDateOrder;?>'>Front Page Active Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=frontInactiveDate&frontInactiveDateOrder=<?php echo $frontInactiveDateOrder;?>'>Front Page Inactive Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=listActiveDate&listActiveDateOrder=<?php echo $listActiveDateOrder;?>'>List Page Active Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=listInactiveDate&listInactiveDateOrder=<?php echo $listInactiveDateOrder;?>'>List Page Inactive Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateLastActive&dateLastActiveOrder=<?php echo $dateLastActiveOrder;?>'>Last Active Date</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateInserted&dateInsertedOrder=<?php echo $dateInsertedOrder;?>'>Date Inserted</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $triviaList;?>
<tr><td colspan=2><?php echo $addButton;?> &nbsp; <?php echo $sortTriviaLink;?></td><td colspan=5 align=right><?php echo $myFreeSiteLink;?></td></tr>
<TR><TD colspan=7 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
	

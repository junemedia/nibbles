<?php

/*********

Script to Display List/Delete Polls

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Polls Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {
		// if poll record deleted...
		
		$deleteQuery = "DELETE FROM edPolls
					    WHERE  id = '$id'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
		
		if($result) {
			// delete all the poll options of this poll
			$deleteQuery1 = "DELETE FROM edPollOptions
							 WHERE pollId = '$id'";
			$deleteResult1 = mysql_query($deleteQuery1)	;
		} else {
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
		case "displayDate":
		$currOrder = $displayDateOrder;
		$displayDateOrder = ($displayDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $questionOrder;
		$questionOrder = ($questionOrder != "DESC" ? "DESC" : "ASC");
	}
	
	if ($filter != '') {
		switch ($searchIn) {
			case "question" :
			$filterPart .= ($exactMatch == 'Y') ? "question = '$filter'" : "question like '%$filter%'";
			break;			
			case "dateLastActive" :
			$filterPart .= ($exactMatch == 'Y') ? "dateLastActive = '$filter'" : "dateLastActive like '%$filter%'";
			break;						
			default:
			// search in date fields only if it's correct date or exact match is not checked
			// otherwise it will convert filter string to date as 0000-00-00 and will display all those				
			if ($exactMatch != 'Y' || checkdate(substr($filter,5,2), substr($filter,8,2), substr(0,4))) 
				$filterPart .= ($exactMatch == 'Y') ? "dateLastActive = '$filter' || question = '$filter'" : " dateLastActive like '%$filter%' || question like '%$filter%' ";
			else
				$filterPart .= ($exactMatch == 'Y') ? " question = '$filter' " : " question like '%$filter%' ";			
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
	
	// Query to get the list of Polls
	$selectQuery = "SELECT *
					FROM   edPolls
					$filterPart
					ORDER BY " . $orderColumn . " $currOrder";
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
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				$question = ascii_encode($row->question);
				// Mark active poll with red *
				if ($row->isActive == 'Y') {
					$question = "<font color=#FF0000>*</font> $question";
				}
				
				$pollsList .= "<tr class=$bgcolorClass><td>$question</td>
								<td>$row->dateLastActive</td><td><a href='JavaScript:void(window.open(\"addPoll.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
								&nbsp; <a href='JavaScript:void(window.open(\"result.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Poll Result</a></td></tr>";
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
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addPoll.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	//$sortLink =$PHP_SELF."?menuId=$menuId";
	$myFreeSiteLink = "<a href='JavaScript:void(window.open(\"$sGblMyFreeSiteRoot\",\"\"));'>MyFree Front Page</a>";
		
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
<tr><td colspan=2><?php echo $addButton;?> &nbsp; &nbsp; &nbsp; &nbsp; <font color=#FF0000>*</font> Shows active poll.</td><td align=right><?php echo $myFreeSiteLink;?></td></tr>
<tr><td colspan=3>Filter By &nbsp; &nbsp; <input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match &nbsp; &nbsp; <input type=submit name=viewReport value='View Report'></td></tr>	

<TR><TD colspan=7 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=question&questionOrder=<?php echo $questionOrder;?>'>Question</a></td>			
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dateLastActive&dateLastActiveOrder=<?php echo $displayDateOrder;?>'>Last Active Date</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $pollsList;?>
<TR><TD colspan=3 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr><td><?php echo $addButton;?></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
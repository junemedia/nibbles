<?php

/*********

Script to Display List/Delete Publication Information

*********/

include("../../includes/paths.php");

$sPageTitle = "Nibbles PopUp Management - List/Delete Popup";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	if ($delete) {
		
		$deleteQuery = "DELETE FROM edOfferPopUps
			   			WHERE id = '$id'"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
		if (!($result)) {
			echo mysql_error();
		}
	}
	// set default order by column
	if (!($orderColumn)) {
		$orderColumn = "popupName";
		$popupNameOrder = "ASC";
	}
	if (!($currOrder)) {
		switch ($orderColumn) {
			
			case "url" :
			$currOrder = $urlOrder;
			$urlOrder = ($urlOrder != "DESC" ? "DESC" : "ASC");
			break;
			default :
			$currOrder = $popupNameOrder;
			$popupNameOrder = ($popupNameOrder != "DESC" ? "DESC" : "ASC");
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
					FROM edOfferPopUps";
	
	$result = mysql_query($selectQuery);
	$numRecords = mysql_num_rows($result);
	
	$totalPages = ceil($numRecords/$recPerPage);
	if ($numRecords > 0) {
	$currentPage = " Page $page "."/ $totalPages";
	}
	
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
			
			$sortLink = "$PHP_SELF?iMenuId=$iMenuId&filter=$filter&alpha=$alpha&exactMatch=$exactMatch&recPerPage=$recPerPage";
			
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
															
				
				if ($row->emailType == '')
				$emailType = "No eMail";
				
				$popupList .= "<tr class=$bgcolorClass>
					<td>$row->popupName</td>
					<td>$row->url</td>
					<td>$row->vSize</td>
					<td>$row->hSize</td>					
					<td><a href='JavaScript:void(window.open(\"addPopup.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddPopup\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addPopup.php?iMenuId=$iMenuId\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
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
		document.form1.submit();
	}
						
</script>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td><?php echo $addButton;?></td></tr>
<tr><td colspan=5 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr>	
	<TD class=header><a href='<?php echo $sortLink;?>&orderColumn=popupName&popupNameOrder=<?php echo $popupNameOrder;?>' class=header>PopUp Name</a></td>
	<TD class=header><a href='<?php echo $sortLink;?>&orderColumn=url&urlOrder=<?php echo $urlOrder;?>' class=header>URL</a></td>	
	<TD class=header>PopUp Height</td>		
	<TD class=header>PopUp Width</td>
	
	<th>&nbsp; </th>
</tr>
<?php echo $popupList;?>
<tr><td colspan=5 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr><Td><?php echo $addButton;?></td></tr>
</table>
</form>


<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Order Processing";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);		

	if ($delete) {
		
		$deleteQuery = "DELETE FROM orders
						WHERE       orID = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$deleteResult = dbQuery($deleteQuery);
		//echo $dele
		// delete order details
			
		if ($deleteResult) {
		$deleteQuery2 = "DELETE FROM orderDetails
						WHERE       orderId = '$id'";
		$deleteResult2 = dbQuery($deleteQuery2);
		}
	}

	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "orDate";
		$orDateOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "orID":
		$currOrder = $orIDOrder;
		$orIDOrder = ($orIDOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "cuFirst":
		$currOrder = $cuFirstOrder;
		$cuFirstOrder = ($cuFirstOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "orStatus":
		$currOrder = $orStatusOrder;
		$orStatusOrder = ($orStatusOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "orCost":
		$currOrder = $orCostOrder;
		$orCostOrder = ($orCostOrder != "DESC" ? "DESC" : "ASC");
		break;	
		default:
		$currOrder = $orDateOrder;
		$orDateOrder = ($orDateOrder != "DESC" ? "DESC" : "ASC");
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
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&recPerPage=$recPerPage";
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT * 
					FROM   customer cu
					INNER JOIN orders ord ON cu.cuId = ord.orCustomerID";
	if ($orderColumn == "cuFirst") {
		$selectQuery .= " ORDER BY cu.cuFirst $currOrder, cu.cuLast $currOrder";
	} else if ($orderColumn == "orCost") {
		$selectQuery .= " ORDER BY orAmount+orTax+orShipping-orCouponSavings $currOrder";		
	} else {
		$selectQuery .= " ORDER BY $orderColumn $currOrder";
	}
	
	// Count no of records and total pages
	$result = dbQuery($selectQuery);
	
	$numRecords = dbNumRows($result);
	$totalPages = ceil($numRecords/$recPerPage);
	if ($numRecords > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	}
	// use query to fetch only the rows of the page to be displayed
	$selectQuery .= " LIMIT $startRec, $recPerPage";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		// Prepare Next/Prev/First/Last links
		
		if ($totalPages > $page ) {
			$nextPage = $page+1;
			$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
			$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
		}
		if ($page != 1) {
			$prevPage = $page-1;
			$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder&recPerPage=$recPerPage' class=header>Previous</a>";
			$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder&recPerPage=$recPerPage' class=header>First</a>";
		}
		
		
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				$totalCost = $row->orAmount + $row->orTax + $row->orShipping - $row->orCouponSavings;				
				
				$ordersList .= "<tr class=$bgcolorClass><td>$row->orDate</td>
								<td>$row->orID</td><td>$row->cuFirst $row->cuLast</td>
								<td>$row->orStatus</td><td>$totalCost</td><td>
								<a href='JavaScript:void(window.open(\"orderDetail.php?iMenuId=$iMenuId&orID=".$row->orID."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Details</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->orID.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	// Display Add Button if user has the permission and Not already clicked on Add Button
	/*if ($marsPermissions[$menuId]['perAdd'] == 'Y') {
		$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addBrand.php?menuId=$menuId&menuFolder=$menuFolder&parentMenuId=$parentMenuId&parentMenuFolder=$parentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	}	*/
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
				<input type=hidden name=id value='$id'>";
	
		
	include("$sGblIncludePath/adminHeader.php");	
?>

<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record from the database?'))
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
<input type=hidden name=delete>
<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<TR><TD colspan=7 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=orDate&orDateOrder=<?php echo $orDateOrder;?>' class=header>Order Date</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=orID&orIDOrder=<?php echo $orIDOrder;?>' class=header>Order No.</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=cuFirst&cuFirstOrder=<?php echo $cuFirstOrder;?>' class=header>Customer Name</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=orStatus&orStatusOrder=<?php echo $orStatusOrder;?>' class=header>Order Status</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=orCost&orCostOrder=<?php echo $orCostOrder;?>' class=header>Total Cost</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $ordersList;?>
<TR><TD colspan=7 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
</table>

</form>


<?php

// include footer

	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}					
?>	


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
		$deleteQuery = "DELETE FROM reoccuringOrders
						WHERE       id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$deleteResult = dbQuery($deleteQuery);					
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "cuFirst";
		$cuFirstOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "prName":
		$currOrder = $prNameOrder;
		$prNameOrder = ($prNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "prOurPrice":
		$currOrder = $prOurPriceOrder;
		$prOurPriceOrder = ($prOurPriceOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "quantity":
		$currOrder = $quantityOrder;
		$quantityOrder = ($quantityOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "dayInterval":
		$currOrder = $dayIntervalOrder;
		$dayIntervalOrder = ($dayIntervalOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "orCost":
		$currOrder = $orCostOrder;
		$orCostOrder = ($orCostOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "startDate":
		$currOrder = $startDateOrder;
		$startDateOrder = ($startDateOrder != "DESC" ? "DESC" : "ASC");
		break;			
		case "lastSentDate":
		$currOrder = $lastSentDateOrder;
		$lastSentDateOrder = ($lastSentDateOrder != "DESC" ? "DESC" : "ASC");
		break;			
		case "nextSchedule":
		$currOrder = $nextScheduleOrder;
		$nextScheduleOrder = ($nextScheduleOrder != "DESC" ? "DESC" : "ASC");
		break;	
		default:
		$currOrder = $cuFirstOrder;
		$cuFirstOrder = ($cuFirstOrder != "DESC" ? "DESC" : "ASC");
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
	
	// Query to get the list of reoccuring orders
	$selectQuery = "SELECT CU.cuFirst, CU.cuLast, RO.*, P.prName, P.prOurPrice, RO.quantity,
						   RO.dayInterval, prOurPrice*quantity AS orderCost,
						   date_format(RO.lastSentDate, '%m-%d-%Y') lastSentDate,
						   date_format(RO.startDate, '%m-%d-%Y') startDate,
						   date_format(date_add(lastSentDate, INTERVAL RO.dayInterval DAY), '%m-%d-%Y') nextSchedule
				FROM   reoccuringOrders RO, products P, customer CU
				WHERE  RO.productId = P.prID
				AND    RO.customerId = CU.cuId";
	if ($orderColumn == "cuFirst") {
		$selectQuery .= " ORDER BY CU.cuFirst $currOrder, CU.cuLast $currOrder";
	} else if ($orderColumn == "orCost") {
		$selectQuery .= " ORDER BY prOurPrice*quantity $currOrder";		
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
				
				$ordersList .= "<tr class=$bgcolorClass><td>$row->cuFirst $row->cuLast</td>
								<td>$row->prName</td><td>$row->prOurPrice</td><td>$row->quantity</td>
								<td>$row->dayInterval</td><td>$row->orderCost</td>
								<td nowrap>$row->startDate</td><td nowrap>$row->lastSentDate</td>
								<td nowrap>$row->nextSchedule</td><td>
								<a href='JavaScript:void(window.open(\"reoccurOrder.php?id=".$row->id."&iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";
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
				<input type=hidden name=ParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
		
	include("$sGblIncludePath/adminHeader.php");	
					
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
<input type=hidden name=delete>
<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<TR><TD colspan=10 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
<?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
<tr>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=cuFirst&cuFirstOrder=<?php echo $cuFirstOrder;?>' class=header>Customer Name</a></td>
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=prName&prNameOrder=<?php echo $prNameOrder;?>' class=header>Product Name</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=prOurPrice&prOurPriceOrder=<?php echo $prOurPriceOrder;?>' class=header>Unit Price</a></td>		
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=quantity&quantityOrder=<?php echo $$quantityOrder;?>' class=header>Qty</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=dayInterval&dayIntervalOrder=<?php echo $dayIntervalOrder;?>' class=header>Days Interval</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=orCost&orCostOrder=<?php echo $orCostOrder;?>' class=header>Order Cost</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=startDate&startDateOrder=<?php echo $startDateOrder;?>' class=header>Start Date</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=lastSentDate&lastSentDateOrder=<?php echo $lastSentDateOrder;?>' class=header>Last Sent On</a></td>	
	<td align=left class=header><a href='<?php echo $sortLink;?>&orderColumn=nextSchedule&nextScheduleOrder=<?php echo $nextScheduleOrder;?>' class=header>Next Scheduled Date</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $ordersList;?>
<TR><TD colspan=10 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>
</table>

</form>

<?php

// include footer

	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}					
?>	


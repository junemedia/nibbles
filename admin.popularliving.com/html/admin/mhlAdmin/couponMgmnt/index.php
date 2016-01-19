<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");


$sPageTitle = "MyHealthyLiving Coupon Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);		
	
	if ($delete) {
		//check if coupon is referenced in any order
		$checkQuery = "SELECT *
						FROM  orders
						WHERE orCouponID = '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) == 0) {
		$deleteQuery = "DELETE FROM discountCoupons
						WHERE  id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$deleteResult = dbQuery($deleteQuery);
		} else {			
			while ($orRow = dbFetchObject($checkResult)) {
				$orID = $orRow->orID;
			}
			$sMessage = "Coupon No. is referenced in Order No. $orID";
		}
	}
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "dcCode";
		$dcCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "dcName":
		$currOrder = $dcNameOrder;
		$dcNameOrder = ($dcNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dcAmount":
		$currOrder = $dcAmountOrder;
		$dcAmountOrder = ($dcAmountOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dcType":
		$currOrder = $dcTypeOrder;
		$dcTypeOrder = ($dcTypeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dcStartDate":
		$currOrder = $dcStartDateOrder;
		$dcStartDateOrder = ($dcStartDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dcEndDate":
		$currOrder = $dcEndDateOrder;
		$dcEndDateOrder = ($dcEndDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "minPurchase":
		$currOrder = $minPurchaseOrder;
		$minPurchaseOrder = ($minPurchaseOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $dcCodeOrder;
		$dcCodeOrder = ($dcCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   discountCoupons
					ORDER BY $orderColumn $currOrder";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$couponList .= "<tr class=$bgcolorClass><td>$row->dcCode</td>
						<td>$row->dcName</td><td>$row->dcAmount</td>
						<td>";
				if($row->dcType == 2)
					$couponList .= "Percentage";
				else					
					$couponList .= "Fixed";
					
				$couponList.= "</td><td>$row->dcStartDate</td>
						<td>$row->dcEndDate</td>
						<td>$row->minPurchase</td><td>
						<a href='JavaScript:void(window.open(\"addCoupon.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addCoupon.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
	
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

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcCode&dcCodeOrder=<?php echo $dcCodeOrder;?>' class=header>Coupon Code</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcName&dcNameOrder=<?php echo $dcNameOrder;?>' class=header>Coupon Name</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcAmount&dcAmountOrder=<?php echo $dcAmountOrder;?>' class=header>Coupon Amount</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcType&dcTypeOrder=<?php echo $dcTypeOrder;?>' class=header>Coupon Type</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcStartDate&dcStartDateOrder=<?php echo $dcStartDateOrder;?>' class=header>Disc. Start Date</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=dcEndDate&dcEndDateOrder=<?php echo $dcEndDateOrder;?>' class=header>Disc. End Date</a></td>	
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=minPurchase&minPurchaseOrder=<?php echo $minPurchaseOrder;?>' class=header>Min. Purchase</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $couponList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}				
?>	


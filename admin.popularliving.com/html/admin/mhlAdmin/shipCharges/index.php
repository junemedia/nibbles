<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Shipping Charges";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);		
	
	if ($delete) {
		$deleteQuery = "DELETE FROM shippingCharges
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
		$orderColumn = "priceFrom";
		$priceFromOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "priceTo":
		$currOrder = $priceToOrder;
		$priceToOrder = ($priceToOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "shippingCharge":
		$currOrder = $shippingChargeOrder;
		$shippingChargeOrder = ($shippingChargeOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "method":
		$currOrder = $methodOrder;
		$methodOrder = ($methodOrder != "DESC" ? "DESC" : "ASC");
		break;	
		default:
		$currOrder = $priceFromOrder;
		$priceFromOrder = ($priceFromOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT SC.*,SM.method
					FROM   shippingCharges SC, shippingMethods SM
					WHERE  SC.methodId = SM.id
					ORDER BY $orderColumn $currOrder";
	//echo $selectQuery;
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$shipChargesList .= "<tr class=$bgcolorClass>
							<td>$row->method</td><td>$row->priceFrom</td>
						<td>$row->priceTo</td><td>$row->shippingCharge</td>
						<td><a href='JavaScript:void(window.open(\"addShipCharge.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addShipCharge.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	/*
	$shipQuery = "SELECT *
				  FROM   shippingMethods";
	$shipResult = mysql_query($shipQuery);
	while ($shipRow = mysql_fetch_object($shipResult)) {
		if ($shipRow->id == $orActShip)
		$selected = "selected";
		else
		$selected = "";
		$shippingOptions .= "<option value=$shipRow->id $selected>$shipRow->method";
	}
	*/
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
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
<td align=left><a href='<?php echo $sortLink;?>&orderColumn=method&methodOrder=<?php echo $methodOrder;?>' class=header>ShippingMethod</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=priceFrom&priceFromOrder=<?php echo $priceFromOrder;?>' class=header>Price From</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=priceTo&priceToOrder=<?php echo $priceToOrder;?>' class=header>Price To</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=shippingCharge&shippingChargeOrder=<?php echo $shippingChargeOrder;?>' class=header>Shipping Charge</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $shipChargesList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}				
?>	


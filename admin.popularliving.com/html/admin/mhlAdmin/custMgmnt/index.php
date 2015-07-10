<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Customers";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	if ($delete) {
		//check if orders exists for this customer
		$checkQuery = "SELECT *
					   FROM   orders
					   WHERE  orCustomerID = '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) == 0) {
			$deleteQuery = "DELETE FROM customer
							WHERE       cuId = '$id'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			$deleteResult = dbQuery($deleteQuery);
		} else {
			$sMessage = "Order Exists For This Customer. Please Delete The Order First...";			
		}		
	}
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "cuFirst";
		$cuFirstOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "cuCity":
		$currOrder = $cuCityOrder;
		$cuCityOrder = ($cuCityOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "cuState":
		$currOrder = $cuStateOrder;
		$cuStateOrder = ($cuStateOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "cuZipCode":
		$currOrder = $cuZipCodeOrder;
		$cuZipCodeOrder = ($cuZipCodeOrder != "DESC" ? "DESC" : "ASC");
		break;	
		default:
		$currOrder = $cuFirstOrder;
		$cuFirstOrder = ($cuFirstOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   customer
					ORDER BY $orderColumn $currOrder";
	//echo $selectQuery;
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$customerList .= "<tr class=$bgcolorClass>
							<td>$row->cuFirst $row->cuLast</td><td>$row->cuCity</td>
						<td>$row->cuState</td><td>$row->cuZipCode</td>
						<td><a href='JavaScript:void(window.open(\"addCustomer.php?iMenuId=$iMenuId&id=".$row->cuId."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->cuId.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addCustomer.php?imenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
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
<td align=left><a href='<?php echo $sortLink;?>&orderColumn=cuFirst&cuFirstOrder=<?php echo $cuFirstOrder;?>' class=header>Customer Name</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=cuCity&cuCityOrder=<?php echo $cuCityOrder;?>' class=header>City</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=cuState&cuStateOrder=<?php echo $cuStateOrder;?>' class=header>State</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=cuZipCode&cuZipCodeOrder=<?php echo $cuZipCodeOrder;?>' class=header>Zip Code</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $customerList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>


<?php

	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}					
?>	


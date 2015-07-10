<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Products Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	
	if ($delete) {
		
		$deleteQuery = "DELETE FROM products
						WHERE  prID = '$prID'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$deleteResult = dbQuery($deleteQuery);
		echo dbError();
	}
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "prNo";
		$prNoOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "prName":
		$currOrder = $prNameOrder;
		$prNameOrder = ($prNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "prOurPrice":
		$currOrder = $prPriceOrder;
		$prPriceOrder = ($prPriceOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "mfgPrNo":
		$currOrder = $mfgPrNoOrder;
		$mfgPrNoOrder = ($mfgPrNoOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$currOrder = $prNoOrder;
		$prNoOrder = ($prNoOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   products
					ORDER BY $orderColumn $currOrder";
	
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
				
				$productsList .= "<tr class=$bgcolorClass><td>$row->prNo</td>
								<td>$row->mfgPrNo</td>
						<td>$row->prName</td><td>$row->prOurPrice</td>
						<td><a href='JavaScript:void(window.open(\"addProduct.php?iMenuId=$iMenuId&prID=".$row->prID."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->prID.");' >Delete</a></td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addProduct.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=prID value='$prID'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
	
	include("$sGblIncludePath/adminHeader.php");	
?>

<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['prID'].value=id;
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
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=prNo&prNoOrder=<?php echo $prNoOrder;?>' class=header>Product No.</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=mfgPrNo&mfgPrNoOrder=<?php echo $mfgPrNoOrder;?>' class=header>Mfg. Product No.</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=prName&prNameOrder=<?php echo $prNameOrder;?>' class=header>Name</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=prOurPrice&prPriceOrder=<?php echo $prPriceOrder;?>' class=header>Price</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $productsList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>


<?php

// include footer

	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}					
?>	


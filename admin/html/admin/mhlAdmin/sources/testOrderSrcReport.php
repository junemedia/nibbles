<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Sources - Order Source Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);
	
	if ($delete) {
		
		$selectQuery = "SELECT orID, date_format(orDate,'%m-%d-%Y') orDate, cuFirst, cuLast
					FROM   orders, customer						
					WHERE orders.orSource='test' 
					AND   orders.orCustomerID = customer.cuId";
		$result = mysql_query($selectQuery);
		if ($result) {
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_object($result)) {
				$tempOrId = $row->orID;
				
				$varName = "delete_".$tempOrId;				
				$varValue = $$varName;
				// If order deleted
				if ($varValue != '') {
					
					// delete from order details
					$odQuery = "DELETE FROM orderDetails
								WHERE  orderId = '$tempOrId'";
					$odResult = mysql_query($odQuery);
					
					$orQuery = "DELETE FROM orders
								WHERE  orID = '$tempOrId'";
					$orResult = mysql_query($orQuery);
					
					//$roQuery = "DELETE FROM reoccuringOrders
				}
			}
		}
		} else {
			echo dbError();
		}
	}
	
	$selectQuery = "SELECT orID, date_format(orDate,'%m-%d-%Y') orDate, cuFirst, cuLast
					FROM   orders, customer						
					WHERE orders.orSource='test' 
					AND   orders.orCustomerID = customer.cuId";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Test Order Source Report: $selectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
	
	
	$result = mysql_query($selectQuery);
	if ($result) {
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$reportData .= "<tr class=$bgcolorClass><td>$row->orID</td>
									<td>$row->orDate</td>
									<td>$row->cuFirst $row->cuLast</td>
						<td><input type=checkbox name='delete_".$row->orID."' value='Y'></td></tr>";					
			}
		} else {
			$message = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}

	$backToSourceLink = "<a href='index.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Back To Source Management</a>";

	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";

	include("$sGblIncludePath/adminHeader.php");
	
?>
	<form action="<?php echo $PHP_SELF;?>" method=post>
<?php echo $hidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=2 align=left><?php echo $backToSourceLink;?></td></tr>
	
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
<td class=header>Order No</td>
<td class=header>Order Date</td>
<td class=header>Customer Name</td>
<td class=header>Delete</td>
</tr>
<?php echo $reportData;?>
<tr>
<td colspan=2 align=center><br><input type=submit name=delete value='Delete'></td></tr>

</table>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>	


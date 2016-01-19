<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

session_start();

$sPageTitle = "MyHealthyLiving Reoccuring Order Management - Edit Reoccuring Order";

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	

if (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	$ccExpDate = "$ccExpYear-$ccExpMonth-00";
	$updateQuery = "UPDATE reoccuringOrders
					SET    productId = '$productId',
						   quantity = '$quantity',
						   dayInterval = '$dayInterval',
						   shippingMethod = '$shippingMethod',
						   ccType = '$ccType',
						   ccNumber = '$ccNumber',
						   ccExpDate = '$ccExpDate',
						   ccNameOnCard = '$ccNameOnCard'
					WHERE  id = '$id'";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $updateQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$updateResult = dbQuery($updateQuery);
	echo dbError();
	
}

if ($sSaveClose) {
	if ($keepValues !=true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$priceFrom='';
		$priceTo = '';
		$shippingCharge = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	
	$selectQuery = "SELECT CU.cuFirst, CU.cuLast, RO.*, P.prOurPrice, RO.quantity,
						   RO.dayInterval, prOurPrice*quantity AS orderCost,
						   date_format(RO.lastSentDate, '%m-%d-%Y') lastSentDate,
						   date_format(RO.startDate, '%m-%d-%Y') startDate,
						   date_format(date_add(lastSentDate, INTERVAL RO.dayInterval DAY), '%m-%d-%Y') nextSchedule
				FROM   reoccuringOrders RO, products P, customer CU
				WHERE  RO.productId = P.prID
				AND    RO.customerId = CU.cuId
				AND    RO.id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$customerName = "$row->cuFirst $row->cuLast";
			$productId = $row->productId;
			$quantity = $row->quantity;
			$dayInterval = $row->dayInterval;
			$startDate = $row->startDate;
			$lastSentDate = $row->lastSentDate;
			$shippingMethod = $row->shippingMethod;
			$ccType = $row->ccType;
			$ccNumber = $row->ccNumber;
			$ccNameOnCard = $row->ccNameOnCard;
			$ccExpDate = $row->ccExpDate;	
			$ccExpYear = substr($ccExpDate, 0, 4);
			$ccExpMonth = substr($ccExpDate, 5, 2);			
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}



// prepare product options
$productQuery = "SELECT *
				 FROM   products
				 ORDER BY products.prName";
$productResult = dbQuery($productQuery);
while ($productRow = dbFetchObject($productResult)) {
	if ($productRow->prID == $productId)
	$selected = "selected";
	else
	$selected = "";
	$productOptions .= "<option value='$productRow->prID' $selected>$productRow->prName - $productRow->prNo";
}

$shipMethodQuery = "SELECT *
					FROM   shippingMethods";
$shipMethodResult = dbQuery($shipMethodQuery);
while ($shipMethodRow = dbFetchObject($shipMethodResult)) {
	if ($shipMethodRow->method == $shippingMethod)
	$selected = "selected";
	else
	$selected = "";
	
	$shippingMethodOptions .= "<option value=$shipMethodRow->id $selected>$shipMethodRow->method";
}

// prepare ccType Options
$visaChecked = "";
$masterCardChecked = "";
$discoverChecked = "";

switch($ccType) {
	case "Visa":
		$visaChecked = "checked";
		break;		
	case "Master Card":
		$masterCardChecked = "checked";
		break;
	case "Discover":
		$discoverChecked = "checked";
		break;
}

$ccTypeOptions = "<input type=radio name=ccType value='Visa' $visaChecked>Visa
				  &nbsp; <input type=radio name=ccType value='Master Card' $masterCardChecked>Master Card
				  &nbsp; <input type=radio name=ccType value='Discover' $discoverChecked>Discover";

// prepare month options for From and To date

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	if ($i < 10) {
		$value ="0".$i+1;
	} else {
		$value =$i+1;
	}
	
	if ($value == $ccExpMonth) {
		$monthSel = "selected";
	} else {
		$monthSel = "";
	}
		
	$ccMonthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
}
$currYear = date("Y");
// prepare year options for From and To date
for ($i = $currYear-1; $i <= $currYear+5; $i++) {
	if ($i == $ccExpYear) {
		$yearSel = "selected";
	} else {
		$yearSel ="";
	}
	
	$ccYearOptions .= "<option value='$i' $yearSel>$i";
	
}

// Hidden variable to be passed with form submit
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>	
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	

?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $reloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Customer Name</td>
		<td><?php echo $customerName;?></td>
	</tr>
	<tr><td width=35%>Start Date</td>
		<td><?php echo $startDate;?></td>
	</tr>
	<tr><td width=35%>Last Sent Date</td>
		<td><?php echo $lastSentDate;?></td>
	</tr>
	
	<tr><td>Product Name</td>
		<td><select name=productId>
		<?php echo $productOptions;?>
		</select></td>
	</tr>
	<tr><td>Quantity</td>
		<td><input type=text name='quantity' value='<?php echo $quantity;?>' size=5></td>
	</tr>
	<tr><td>Days Interval</td>
		<td><input type=text name='dayInterval' value='<?php echo $dayInterval;?>' size=5></td>
	</tr>
	<tr><td>Shipping Method</td>
		<td><select name=shippingMethod>
		<?php echo $shippingMethodOptions;?>
		</select></td>
	</tr>
	<tr><td>CC Type</td>
		<td><?php echo $ccTypeOptions;?></td>
	</tr>
	<tr><td>CC Number</td>
		<td><input type=text name='ccNumber' value='<?php echo $ccNumber;?>' ></td>
	</tr>
	<tr><td>CC Exp. Date</td>
		<td><select name=ccExpMonth>
		<?php echo $ccMonthOptions;?>
		</select> <select name=ccExpYear>
		<?php echo $ccYearOptions;?>
		</select></td>
	</tr>
	
	<tr><td>CC Name On Card</td>
		<td><input type=text name='ccNameOnCard' value='<?php echo $ccNameOnCard;?>' ></td>
	</tr>
		
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>
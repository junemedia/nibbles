<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

session_start();

$sPageTitle = "MyHealthyLiving Coupon Management - Add/Edit Coupon";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   shippingCharges 
				   WHERE  ($priceFrom BETWEEN priceFrom AND priceTo 
						  || $priceTo BETWEEN priceFrom and priceTo)
				   AND    methodId = '$methodId'";
	$checkResult = dbQuery($checkQuery);
	if (dbNumRows($checkResult) > 0 ) {
		$sMessage = "Price Range Falls In Another Price Range. Please Choose Proper Price Range.";
		$keepValues = true;
	} else {
		
		$addQuery = "INSERT INTO shippingCharges(methodId, priceFrom, priceTo, shippingCharge)
				 VALUES('$methodId', '$priceFrom', '$priceTo', '$shippingCharge')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = dbQuery($addQuery);
		if (! $result) {
			echo dbError();
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   shippingCharges 
				   WHERE  ($priceFrom BETWEEN priceFrom AND priceTo 
						  || $priceTo BETWEEN priceFrom and priceTo)
					AND   id != '$id'
					AND   methodId = '$methodId'";
	$checkResult = dbQuery($checkQuery);
	if (dbNumRows($checkResult) > 0 ) {
		$sMessage = "Price Range Falls In Another Price Range. Please Choose Proper Price Range.";
		$keepValues = true;
	} else {
		
		$editQuery = "UPDATE shippingCharges
				  SET 	 methodId = '$methodId',
						 priceFrom='$priceFrom',
						 priceTo = '$priceTo',
						 shippingCharge = '$shippingCharge'						 
				  WHERE  id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = dbQuery($editQuery);
		echo $editQuery.dbError();
	}
	//echo $editQuery.$result;
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
		$methodId = '';
		$priceFrom='';
		$priceTo = '';
		$shippingCharge = '';		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT SC.*,SM.*
					FROM   shippingCharges SC, shippingMethods SM
					WHERE  SC.methodId = SM.id
			  		AND    SC.id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$methodId = $row->methodId;
			//echo $row->methodId;
			$priceFrom = $row->priceFrom;
			$priceTo = $row->priceTo;						
			$shippingCharge = $row->shippingCharge;						
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

$shipQuery = "SELECT *
				  FROM   shippingMethods";
	$shipResult = dbQuery($shipQuery);
	while ($shipRow = dbFetchObject($shipResult)) {
		if ($shipRow->id == $methodId)
			$selected = "selected";
		else
			$selected = "";
		$shippingMethodOptions .= "<option value=$shipRow->id $selected>$shipRow->method";
	}
	
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>	
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
	
?>

<form action='<?php echo $PHP_SELF;?>' method=post >
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr><td width=35%>Shipping Method</td>
		<td><select name='methodId'>
		<?php echo $shippingMethodOptions;?>
			</select></td>
	</tr>
	<tr><td width=35%>Price From</td>
		<td><input type=text name='priceFrom' value='<?php echo $priceFrom;?>' ></td>
	</tr>
	<tr><td>Price To</td>
		<td><input type=text name='priceTo' value='<?php echo $priceTo;?>' ></td>
	</tr>
	<tr><td>Shipping Charge</td>
		<td><input type=text name='shippingCharge' value='<?php echo $shippingCharge;?>' ></td>
	</tr>
		
</table>

<?php


include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	


?>
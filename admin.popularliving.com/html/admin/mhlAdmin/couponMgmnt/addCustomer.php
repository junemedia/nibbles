<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Customer - Add/Edit Customer";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT hl DATABASE
	dbSelect($sGblMhlDBName);	
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	//Check For Dupe
	$addQuery = "INSERT INTO customer(cuFirst, cuLast, cuAddress, cuAddress2, cuCity, cuState,
					 cuZipCode, cuPhone, cuEveningPhone, cuEmail, cuUserId, cuPassword)
				 VALUES('$cuFirst', '$cuLast', '$cuAddress', '$cuAddress2', '$cuCity', '$cuState',
					 '$cuZipCode', '$cuPhone', '$cuEveningPhone', '$cuEmail', '$cuUserId', '$cuPassword')";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = dbQuery($addQuery);
	if (! $result) {
		echo dbError();
	}
	
	$sCheckQuery = "SELECT cuId
	   FROM   customer
	   WHERE  cuFirst = '$cuFirst'
	   AND cuLast = '$cuLast'
	   AND cuEmail = '$cuEmail'
	   AND cuUserId = '$cuUserId'
	   AND cuPhone = '$cuPhone'
	   AND cuZipCode = '$cuZipCode'
	   AND cuAddress = '$cuAddress'";
	$rCheckResult = dbQuery($sCheckQuery);
	$sRow = dbFetchObject($rCheckResult);
	
	
	
	
	$id = $sRow->cuId;
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	$editQuery = "UPDATE customer
					  SET 	 cuFirst = '$cuFirst', 
							 cuLast = '$cuLast', 
							 cuAddress = '$cuAddress', 
							 cuAddress2 = '$cuAddress2', 
							 cuCity = '$cuCity', 
							 cuState = '$cuState',
					 		 cuZipCode = '$cuZipCode', 
							 cuPhone = '$cuPhone', 
							 cuEveningPhone = '$cuEveningPhone', 
							 cuEmail = '$cuEmail', 
							 cuUserId = '$cuUserId', 
							 cuPassword = '$cuPassword'
					  WHERE  cuId = '$id'";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	$result = dbQuery($editQuery);
	echo $editQuery.dbError();
}

if (($sSaveClose || $sSaveNew) && $orShipToFirst != '') {
	// enter shipto information
	if ( $orShipToFirst != '' && $orShipToLast != '' && $orShipToAddress != ''
	&& $orShipToCity != '' && $orShipToState != '' && $orShipToZipCode != '') {
		$checkQuery = "SELECT *
					   FROM   customer_shipto
					   WHERE  cstCustomerId = '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) >0) {
			$updateQuery2 = "UPDATE customer_shipto
					SET    cstShipToFirst = '$orShipToFirst',
						   cstShipToLast = '$orShipToLast',
						   cstShipToAddress = '$orShipToAddress',
						   cstShipToAddress2 = '$orShipToAddress2',
						   cstShipToCity = '$orShipToCity',
						   cstShipToState = '$orShipToState',
						   cstShipToZipCode = '$orShipToZipCode',
						   cstShipToPhone = '$orShipToPhone'
					WHERE  cstCustomerID = '$id'";
			
			$updateResult2 = dbQuery($updateQuery2);
			
		} else {
			$insertQuery = "INSERT INTO customer_shipto(cstCustomerID, cstShipToFirst, cstShipToLast,
								cstShipToAddress, cstShipToAddress2, cstShipToCity, 
								cstShipToState, cstShipToZipCode, cstShipToPhone)
								VALUES('$id', '$orShipToFirst', '$orShipToLast', '$orShipToAddress',
								'$orShipToAddress2', '$orShipToCity', '$orShipToState',
								'$orShipToZipCode', '$orShipToPhone')";
			$insertResult = dbQuery($insertQuery);
		}
		
	} else {
		$keepValues = "true";
		$sMessage = "Please Enter Full Shipping Information Or Leave It Blank";
	}
	
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
		$cuFirst = '';
		$cuLast = '';
		$cuAddress = '';
		$cuAddress2 = '';
		$cuCity = '';
		$cuState = '';
		$cuZipCode = '';
		$cuPhone = '';
		$cuEveningPhone = '';
		$cuEmail = '';
		$cuUserId = '';
		$cuPassword = '';
		
		$orShipToFirst = '';
		$orShipToLast = '';
		$orShipToAddress = '';
		$orShipToAddress2 = '';
		$orShipToCity = '';
		$orShipToState = '';
		$orShipToZipCode = '';
		$orShipToPhone = '';
			
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT cu.*, cs.*
					FROM   customer cu LEFT JOIN customer_shipto cs
	    			ON cu.cuId = cs.cstCustomerID
					WHERE  cu.cuId = '$id'
					";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$cuFirst = $row->cuFirst;
			$cuLast = $row->cuLast;
			$cuAddress = $row->cuAddress;
			$cuAddress2 = $row->cuAddress2;
			$cuCity = $row->cuCity;
			$cuState = $row->cuState;
			$cuZipCode = $row->cuZipCode;
			$cuPhone = $row->cuPhone;
			$cuEveningPhone = $row->cuEveningPhone;
			$cuEmail = $row->cuEmail;
			$cuUserId = $row->cuUserId;
			$cuPassword = $row->cuPassword;
			
			$orShipToFirst = $row->cstShipToFirst;
			$orShipToLast = $row->cstShipToLast;
			$orShipToAddress = $row->cstShipToAddress;
			$orShipToAddress2 = $row->cstShipToAddress2;
			$orShipToCity = $row->cstShipToCity;
			$orShipToState = $row->cstShipToState;
			$orShipToZipCode = $row->cstShipToZipCode;
			$orShipToPhone = $row->cstShipToPhone;
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

// prepare State Options
$stateQuery = "SELECT *
			   FROM   states
			   ORDER BY state";
$stateResult = dbQuery($stateQuery);
$orShipToStateOptions = "<option value='' selected>";
while ($stateRow = dbFetchObject($stateResult)) {
	if ($stateRow->stateId == $cuState)
	$selected = "selected";
	else
	$selected = "";
	
	$cuStateOptions .= "<option value='".$stateRow->stateId."' $selected>$stateRow->state";
	
	if ($stateRow->stateId == $orShipToState)
	$selected = "selected";
	else
	$selected = "";
	
	$orShipToStateOptions .= "<option value='".$stateRow->stateId."' $selected>$stateRow->state";
}


// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	


?>


<form action='<?php echo $PHP_SELF;?>' method=post">
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td width=35%>First Name</td>
		<td><input type=text name='cuFirst' value='<?php echo $cuFirst;?>' ></td>
	</tr>
	<tr><td>Last Name</td>
		<td><input type=text name='cuLast' value='<?php echo $cuLast;?>' ></td>
	</tr>
	<tr><td>Address</td>
		<td><input type=text name='cuAddress' value='<?php echo $cuAddress;?>' ></td>
	</tr>
	<tr><td></td>
		<td><input type=text name='cuAddress2' value='<?php echo $cuAddress2;?>' ></td>
	</tr>
	<tr><td>City</td>
		<td><input type=text name='cuCity' value='<?php echo $cuCity;?>' ></td>
	</tr>
	<tr><td>State</td>
		<td><select name='cuState'>
		<?php echo $cuStateOptions;?>
		</select></td>
	</tr>
	<tr><td>ZipCode</td>
		<td><input type=text name='cuZipCode' value='<?php echo $cuZipCode;?>' ></td>
	</tr>
	<tr><td>Phone No.</td>
		<td><input type=text name='cuPhone' value='<?php echo $cuPhone;?>' ></td>
	</tr>
	<tr><td>Evening Phone No.</td>
		<td><input type=text name='cuEveningPhone' value='<?php echo $cuEveningPhone;?>' ></td>
	</tr>		
	<tr><td>Email</td>
		<td><input type=text name='cuEmail' value='<?php echo $cuEmail;?>' ></td>
	</tr>	
	<tr><td>UserId</td>
		<td><input type=text name='cuUserId' value='<?php echo $cuUserId;?>' ></td>
	</tr>
	<tr><td>Password</td>
		<td><input type=text name='cuPassword' value='<?php echo $cuPassword;?>' ></td>
	</tr>
	
	
	<tr><td width=35% class=header><BR>Ship To</td>
		<td></td>
	</tr>
	
	<tr><td width=35%>First Name</td>
		<td><input type=text name='orShipToFirst' value='<?php echo $orShipToFirst;?>' ></td>
	</tr>
	<tr><td>Last Name</td>
		<td><input type=text name='orShipToLast' value='<?php echo $orShipToLast;?>' ></td>
	</tr>
	<tr><td>Address</td>
		<td><input type=text name='orShipToAddress' value='<?php echo $orShipToAddress;?>' ></td>
	</tr>
	<tr><td></td>
		<td><input type=text name='orShipToAddress2' value='<?php echo $orShipToAddress2;?>' ></td>
	</tr>
	<tr><td>City</td>
		<td><input type=text name='orShipToCity' value='<?php echo $orShipToCity;?>' ></td>
	</tr>
	<tr><td>State</td>
		<td><select name='orShipToState'>
		<?php echo $orShipToStateOptions;?>
		</select></td>
	</tr>
	<tr><td>ZipCode</td>
		<td><input type=text name='orShipToZipCode' value='<?php echo $orShipToZipCode;?>' ></td>
	</tr>
	<tr><td>Phone No.</td>
		<td><input type=text name='orShipToPhone' value='<?php echo $orShipToPhone;?>' ></td>
	</tr>
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	
?>
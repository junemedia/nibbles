<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

session_start();

$sPageTitle = "MyHealthyLiving Shipping Methods - Add/Edit Shipping Method";

if (hasAccessRight($iMenuId) || isAdmin()) {
			
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	$addQuery = "INSERT INTO shippingMethods(method)
					 VALUES('$method')";

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
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	$editQuery = "UPDATE shippingMethods
				  SET 	 method = '$method'				  						 
				  WHERE  id = '$id'";	

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = dbQuery($editQuery);
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
	// exit from this script
	exit();
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$method = "";		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   shippingMethods
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$method = $row->method;			
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

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>	
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	


?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Shipping Method</td>
		<td><input type=text name='method' value='<?php echo $method;?>' SIZE=35></td>
	</tr>			
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>
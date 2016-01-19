<?php

/*********

Script to Display Add/Edit/ Spam Trap Bad Addresses

*********/

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

$sPageTitle = "Spam Trap - Add/Edit Bad Addresses";


if ($sSaveClose || $sSaveNew) {
	
	// if new data submitted
	
	if (!($id)) {
		$checkQuery = "SELECT *
				   FROM   spamTrapBadAddr
				   WHERE  badAddress = '$badAddress'";
		$checkResult = mysql_query($checkQuery);
		
		if (mysql_num_rows($checkResult) == 0)  {
			
			
			// If adding num rows should be 0
			
			$addQuery = "INSERT INTO sSpamTrapBadAddr(badAddress, notes, dateInserted)
						 VALUES(LOWER('$badAddress'), '$notes', CURRENT_DATE)";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
						
			
			$result = mysql_query($addQuery);
			if (! $result) {
				echo mysql_error();
			}
		} else {
			$message = "Bad Address Already Exists...";
			$keepValues = true;
		}
	} else {
		$checkQuery = "SELECT *
				   		FROM   spamTrapBadAddr
				   		WHERE  badAddress = LOWER('$badAddress')
				   		AND id != '$id'";
		$checkResult = mysql_query($checkQuery);
		
		
		// If editing, num rows should not be more than one
		if (mysql_num_rows($checkResult2) == 0) {
			$editQuery = "UPDATE spamTrapBadAddr
				  SET 	 badAddress = LOWER('$badAddress'),
						 notes = '$notes'
				  WHERE  id = '$id'";	

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$result = mysql_query($editQuery);
		} else {
			$message = "Bad Address already exists...";
			$keepValues = true;
		}
	}	
	
	if ($sSaveClose) {
		if ($keepValues != true) {
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
			$badAddress = '';
			$notes = '';
		}
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   spamTrapBadAddr
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$badAddress = $row->badAddress;
			$notes = $row->notes;
			
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
}  else {
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Bad Address</td>
		<td><input type=text name='badAddress' value='<?php echo $badAddress;?>'></td>
	</tr>	
	<tr><td>Notes</td>
		<td><TEXTAREA name=notes rows=5 cols=40><?php echo $notes;?></TEXTAREA></td>
	</tr>
			
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display Add/Edit/ Spam Trap Blacklist

*********/

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// get opposite tables for the requested List Type, for cross-checking the addresses.
$listTypeCaption = ucfirst($listType);
if ($listType == "blacklist") {
	$listTable = "spamTrapBlacklist";
	$oppositeTable = "SpamTrapWhitelist";
	$oppositeTypeCaption = "Whitelist";
} else if ($listType == "whitelist") {
	$listTable = "spamTrapWhitelist";
	$oppositeTable = "spamTrapBlacklist";
	$oppositeTypeCaption = "Blacklist";
}

$sPageTitle = "Spam Trap - Add/Edit ".$listTypeCaption;

if ($sSaveClose || $sSaveNew) {	
	// if new data submitted
	
	// check if exists in opposite table
	if (trim($ipAddress) != '') {
		$checkQuery = "SELECT *
				   	   FROM   $oppositeTable
				   	   WHERE  ipAddress = '$ipAddress'";
		$checkResult = mysql_query($checkQuery);
	
		if (mysql_num_rows($checkResult) == 0)  {
			
			// check if exists in the same table
			$checkQuery2 = "SELECT *
				   			FROM   $listTable
				   			WHERE  ipAddress = '$ipAddress'";
			$checkResult2 = mysql_query($checkQuery2);
			
			if (!($id)) {
				// If adding num rows should be 0,
				// address should not exist in either whitelist or blacklist
				if (mysql_num_rows($checkResult2) == 0) {
					
					$addQuery = "INSERT INTO $listTable(ipAddress, serverName, notes, dateInserted)
						 		VALUES('$ipAddress', '$serverName', '$notes', CURRENT_DATE)";
					

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
					// exists in the same table
					$message = "IP Address Already Exists In ".$listTypeCaption."...";
					$keepValues = true;
				}
			} else {
				// If editing, num rows should not be more than one
				if (mysql_num_rows($checkResult2) <= 1) {
					$editQuery = "UPDATE $listTable
								  SET 	 serverName = '$serverName',
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
					// exists more than once in the same table, (while editing)
					$message = "IP Address Already Exists In ".$listTypeCaption."...";
					$keepValues = true;
				}
			}
		} else {
			// exists in opposite table
			$message = "IP Address Exists In ".$oppositeTypeCaption."...";
			$keepValues = true;
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
				$ipAddress = '';
				$serverName = '';
				$notes = '';
			}
		}
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields 
		
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   $listTable
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$ipAddress = $row->ipAddress;
			$serverName = $row->serverName;
			$notes = $row->notes;
			$dateAdded = "<TR><TD>Date Added</td><td>$row->dateInserted</td></tr>";
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>
			<input type=hidden name=listType value='$listType'>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>IP Address</td>
		<td><input type=text name='ipAddress' value='<?php echo $ipAddress;?>'></td>
	</tr>
	<tr><td>Server Name</td>
		<td><input type=text name='serverName' value='<?php echo $serverName;?>' size=30></td>
	</tr>
	<tr><td>Notes</td>
		<td><TEXTAREA name=notes rows=5 cols=40><?php echo $notes;?></TEXTAREA></td>
	</tr>
	<?php echo $dateAdded;?>
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
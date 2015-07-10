<?php

/*********

Script to Display Add/Edit/ Spam Trap Blacklist

*********/

include("../../includes/paths.php");

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$listTypeCaption = ucfirst($listType);
if ($listType == "blacklist") {
	$listTable = "spamTrapBlacklist";
	$oppositeTable = "spamTrapWhitelist";
	$oppositeTypeCaption = "Whitelist";
} else if ($listType == "whitelist") {
	$listTable = "spamTrapWhitelist";
	$oppositeTable = "spamTrapBlacklist";
	$oppositeTypeCaption = "Blacklist";
}

$sPageTitle = "Spam Trap - Add/Edit ".$listTypeCaption;

if ($sSaveClose || $sSaveNew) {
	
	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add spam trap list\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	// if new data submitted
	// check if exists in whitelist
	$ipAddressArray = explode("\n",$ipAddresses);
	for ($i=0; $i<count($ipAddressArray); $i++) {
		$ipAddress = trim($ipAddressArray[$i]);
		if ($ipAddress != '') {
			$checkQuery = "SELECT *
				   FROM   $oppositeTable
				   WHERE  ipAddress = '$ipAddress'";
			$checkResult = mysql_query($checkQuery);
			//echo "<BR>1".mysql_num_rows($checkResult). mysql_error();
			if (mysql_num_rows($checkResult) == 0)  {
				
				$checkQuery2 = "SELECT *
				   FROM   $listTable
				   WHERE  ipAddress = '$ipAddress'";
				$checkResult2 = mysql_query($checkQuery2);
				//echo "<BR>2".mysql_num_rows($checkResult2). mysql_error();
				
				// If adding num rows should be 0
				if (mysql_num_rows($checkResult2) == 0) {
					$addQuery = "INSERT INTO $listTable(ipAddress, serverName, notes, dateInserted)
						 VALUES('$ipAddress', '$serverName', '$notes', CURRENT_DATE)";
					
					$result = mysql_query($addQuery);
					if (! $result) {
						echo mysql_error();
					}
				} else {
					// exists in blacklist
					$message = "IP Address already exists in ".$listTypeCaption."...";
					$keepValues = true;
				}
			}
		}
	}
	if ($sSaveClose) {
		if ($keepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
		}
		
	} else if ($sSaveNew) {
		$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";				
	}
}

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	


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
		<td><textarea rows=10 cols=30 name='ipAddresses'></textarea></td>
	</tr>
			
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display Add/Edit/ Spam Trap Bad Addresses

*********/

include("../../includes/paths.php");

$sPageTitle = "Spam Trap - Add/Edit Bad Addresses";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	

if ($sSaveClose || $sSaveNew) {
	
	// if new data submitted
	
	$badAddressArray = explode("\n",$badAddresses);
	for ($i=0; $i<count($badAddressArray); $i++) {
		$badAddress = $badAddressArray[$i];
		$checkQuery = "SELECT *
				   FROM   spamTrapBadAddr
				   WHERE  badAddress = '$badAddress'";
		$checkResult = mysql_query($checkQuery);
		
		if (mysql_num_rows($checkResult) == 0)  {
			
			
			// If adding num rows should be 0
			
			$addQuery = "INSERT INTO spamTrapBadAddr(badAddress, dateInserted)
						VALUES(LOWER('$badAddress'), CURRENT_DATE)";
			
			$result = mysql_query($addQuery);
			if (! $result) {
				echo mysql_error();
			}
		}
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
		
	}
}



$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	


$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";


include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Bad Address List</td>
		<td><textarea name='badAddresses' rows=10 cols=40></textarea></td>
	</tr>	
			
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
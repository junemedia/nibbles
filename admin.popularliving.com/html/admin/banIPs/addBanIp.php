<?php

/*********

Script to Display Add/Edit Banned IPs

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned IPs - Add/Edit Banned IP";

if (hasAccessRight($iMenuId) || isAdmin()) {		

	if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned domain added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   bannedIps
					WHERE  ipAddress = '$sIpAddress'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "IP address already exists as banned IP...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO bannedIps(ipAddress)
				 VALUES('$sIpAddress')";
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
			$sMessage = dbError();		
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   bannedIps
					WHERE  ipAddress = '$sIpAddress'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "IP address already exists as banned IP address...";
		$bKeepValues = true;
	} else {
		
		$sEditQuery = "UPDATE bannedIps
				  SET ipAddress = '$sIpAddress'
				  WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}		
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$sIpAddress = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM bannedIps
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sIpAddress = $oSelectRow->ipAddress;
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>Banned IP Address</td><td><input type=text name=sIpAddress value='<?php echo $sIpAddress;?>'></td></tr>
	</table>	
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
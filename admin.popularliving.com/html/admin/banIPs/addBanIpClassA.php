<?php

/*********

Script to Display Add/Edit Banned IPs

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned IPs - Add/Edit Banned IP";

if (hasAccessRight($iMenuId) || isAdmin()) {		

	if ($sSaveClose || $sSaveNew) {
	// if new banned domain added

		if (preg_match('/^\d+\.\d+$/', $sIpAddress)) {	
			for ($a=0; $a<=255; $a++) {
				for ($i=0; $i<=255; $i++) {
					$sAddQuery = "INSERT INTO bannedIps(ipAddress) VALUES('$sIpAddress.$a.$i')";
					$rResult = dbQuery($sAddQuery);
					echo dbError();
				}
			}
		} else {
			$sMessage = "Invalid Ip.  Enter First Two Groups Of IP.  For example: 66.192";
			$bKeepValues = true;
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

	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td>Banned IP Address - Class A</td><td><input type=text name=sIpAddress maxlength="7" value='<?php echo $sIpAddress;?>'>
		Enter first two groups of IP address only.  For example: 66.192
		</td></tr>
	</table>

<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
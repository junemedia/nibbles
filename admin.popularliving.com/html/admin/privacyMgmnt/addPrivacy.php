<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles Add Privacy Policy";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && !($id)) {
		if ($sPolicyName != '' && $sPrivacyPolicy !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   privacyPolicy
							WHERE  name = \"$sPolicyName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Policy Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sPrivacyPolicy = addslashes($sPrivacyPolicy);
				$sAddQuery = "INSERT INTO privacyPolicy (name,body) 
								VALUES(\"$sPolicyName\",\"$sPrivacyPolicy\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = "Name / Privacy Policy Required...";
			$bKeepValues = true;
		}
	} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		if ($sPolicyName != '' && $sPrivacyPolicy !='') {
			$sCheckQuery = "SELECT *
							FROM   privacyPolicy
							WHERE  name = \"$sPolicyName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Policy Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sPrivacyPolicy = addslashes($sPrivacyPolicy);
				$editQuery = "UPDATE privacyPolicy 
							SET name = \"$sPolicyName\",
							body = \"$sPrivacyPolicy\"
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = "Name / Privacy Policy Required...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$sPolicyName = '';
			$sPrivacyPolicy = '';
			$id = '';
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$sPolicyName = '';
			$sPrivacyPolicy = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$sDescription = '';
		$selectQuery = "SELECT * FROM   privacyPolicy WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sPolicyName = $row->name;
			$sPrivacyPolicy = $row->body;
		}
	}


	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	include("../../includes/adminAddHeader.php");
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Privacy Policy Name: </td>
			<td><input type="text" name=sPolicyName maxlength="255" size='50' value="<?php echo $sPolicyName; ?>">
			</td>
		</tr>
		
	
		<tr><td width=35%>Privacy Policy: </td>
		<td><textarea name=sPrivacyPolicy rows=5 cols=50><?php echo $sPrivacyPolicy;?></textarea></td>
		</tr>
		
		
	</table>	
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
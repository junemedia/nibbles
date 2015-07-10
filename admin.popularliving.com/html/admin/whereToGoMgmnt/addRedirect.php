<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Add Redirect URL";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && !($id)) {
		if ($sRedirectUrl != '' && $sName !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   whereToGo
							WHERE  name = \"$sName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sAddQuery = "INSERT INTO whereToGo (name,redirectUrl,isDefault) 
								VALUES(\"$sName\",\"$sRedirectUrl\",\"$sDefault\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				if($sDefault == 'Y'){
					$sUpdateDefaults = "UPDATE whereToGo SET isDefault = 'N' WHERE name != '$sName' AND redirectUrl != '$sRedirectUrl'";
					$rUpdateDefaults = dbQuery($sUpdateDefaults);
					
					// start of track users' activity in nibbles
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdateDefaults) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
					// end of track users' activity in nibbles
				}
			}
		} else {
			$sMessage = "Name / Redirect Url Required...";
			$bKeepValues = true;
		}
	} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		if ($sRedirectUrl != '' && $sName !='') {
			$sCheckQuery = "SELECT *
							FROM   whereToGo
							WHERE  name = \"$sName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Name Already Exists...";
				$bKeepValues = true;
			} else {
				$editQuery = "UPDATE whereToGo 
							SET name = \"$sName\",
							redirectUrl = \"$sRedirectUrl\",
							isDefault = \"$sDefault\"
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				if($sDefault == 'Y'){
					$sUpdateDefaults = "UPDATE whereToGo SET isDefault = 'N' WHERE id != '$id'";
					$rUpdateDefaults = dbQuery($sUpdateDefaults);
					
					// start of track users' activity in nibbles
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdateDefaults) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
					// end of track users' activity in nibbles
				}
			}
		} else {
			$sMessage = "Name / Redirect Url Required...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
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
			$sRedirectUrl = '';
			$sName = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   whereToGo WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sName = $row->name;
			$sRedirectUrl = $row->redirectUrl;
			$sDefault = $row->isDefault;
		}
	}
	
	if($sDefault == 'N' || $sDefault == ''){
		$sDefaultNoSelected = ' checked';
		$sDefaultYesSelected = '';
	} else {
		$sDefaultYesSelected = ' checked';	
		$sDefaultNoSelected = '';	
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
	
		<tr><td width=35%>Name: </td>
			<td><input type="text" name=sName maxlength="255" size='50' value="<?php echo $sName; ?>">
			</td>
		</tr>
		
		<tr><td width=35%>Default: </td>
			<td><input type="radio" name=sDefault value="Y" <?php echo $sDefaultYesSelected;?>> Yes <input type="radio" name=sDefault value="N" <?php echo $sDefaultNoSelected;?>> No 
			</td>
		</tr>
	
		<tr><td width=35%>Redirect Url: </td>
		<td><input type="text" name=sRedirectUrl maxlength="255" size='50' value="<?php echo $sRedirectUrl; ?>">
		&nbsp;&nbsp;http://www.popularliving.com/p/onetime.php</td></tr>
		
		
	</table>
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Terms And Conditions Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && !($id)) {
		if ($sTermsConditions != '' && $sTermsName !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   termsConditions
							WHERE  name = \"$sTermsName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Terms & Conditions Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sTermsConditions = addslashes($sTermsConditions);
				$sAddQuery = "INSERT INTO termsConditions (name,body) 
								VALUES(\"$sTermsName\",\"$sTermsConditions\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = "Name / Terms And Conditions Required...";
			$bKeepValues = true;
		}
	} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		if ($sTermsConditions != '' && $sTermsName !='') {
			$sCheckQuery = "SELECT *
							FROM   termsConditions
							WHERE  name = \"$sTermsName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Terms & Conditions Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sTermsConditions = addslashes($sTermsConditions);
				$editQuery = "UPDATE termsConditions 
							SET name = \"$sTermsName\",
							body = \"$sTermsConditions\"
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = "Name / Terms And Conditions Required...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$sTermsName = '';
			$sTermsConditions = '';
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
			$sTermsName = '';
			$sTermsConditions = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   termsConditions WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sTermsName = $row->name;
			$sTermsConditions = $row->body;
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
		<tr><td width=35%>Terms And Conditions Name: </td>
			<td><input type="text" name=sTermsName maxlength="255" size='50' value="<?php echo $sTermsName; ?>">
			</td>
		</tr>
		
	
		<tr><td width=35%>Terms And Conditions: </td>
		<td><textarea name=sTermsConditions rows=5 cols=50><?php echo $sTermsConditions;?></textarea></td>
		</tr>
		
		
	</table>	
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
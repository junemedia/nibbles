<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Email Capture Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		$sMessage = '';
		if ($sEmailCaptureContent == '') {
			$sMessage = "Email Capture Form Required...";
			$bKeepValues = true;
		} elseif ($sName == ''){
			$sMessage = "Name Required...";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			$sEmailCaptureContent = addslashes($sEmailCaptureContent);
			$sDescription = addslashes($sDescription);
			$sNotes = addslashes($sNotes);
		}
		
		if (!($id) && $sMessage == '') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   emailCapCreative
							WHERE  name = \"$sName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Email Creative Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sAddQuery = "INSERT INTO emailCapCreative (name,content,dateTimeCreated,description,notes) 
								VALUES(\"$sName\",\"$sEmailCaptureContent\", NOW(),\"$sDescription\",\"$sNotes\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} elseif (($id) && $sMessage == '') {
			$sCheckQuery = "SELECT * FROM emailCapCreative
							WHERE  name = \"$sName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Email Creative Name Already Exists...";
				$bKeepValues = true;
			} else {
				$editQuery = "UPDATE emailCapCreative 
								SET name = \"$sName\",
								content = \"$sEmailCaptureContent\",
								description = \"$sDescription\",
								notes = \"$sNotes\"
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$id = '';
			$sName = '';
			$sEmailCaptureContent = '';
			$sDescription = '';
			$sNotes = '';
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
			$id = '';
			$sName = '';
			$sEmailCaptureContent = '';
			$sDescription = '';
			$sNotes = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM emailCapCreative WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sName = $row->name;
			$sEmailCaptureContent = $row->content;
			$sDescription = $row->description;
			$sNotes = $row->notes;
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
		<tr><td width=35%>Name: </td>
			<td><input type="text" name=sName maxlength="255" size='50' value="<?php echo $sName; ?>">
			</td>
		</tr>
		
	
		<tr><td width=35%>Email Capture Form: </td>
		<td><textarea name=sEmailCaptureContent rows=20 cols=100><?php echo $sEmailCaptureContent;?></textarea></td>
		</tr>
		
		<tr><td width=35%>Description: </td>
		<td><textarea name=sDescription rows=3 cols=50><?php echo $sDescription;?></textarea></td>
		</tr>

		<tr><td width=35%>Notes: </td>
		<td><textarea name=sNotes rows=3 cols=50><?php echo $sNotes;?></textarea></td>
		</tr>
		

	</table>
	
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
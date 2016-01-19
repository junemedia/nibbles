<?php

/*********

Script to You Won eMail Content

**********/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village TAF eMail Content";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	
if (($sSaveClose || $sSaveNew) && !($id)) {
		// if new data submitted
		$addQuery = "INSERT INTO emailMessages(subject, message )
				 VALUES('$subject', '$messageBody')";		
		$result = dbQuery($addQuery);
		if (!($result))
		$sMessage = dbError();
		
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		// if record edited
		
		$editQuery = "UPDATE emailMessages SET						
						subject = '$subject',
						message = '$messageBody'
						WHERE id = '$id'";
		$result = dbQuery($editQuery);
		if (!($result)) {
			$sMessage=dbError();
		}
		$id='';
		
	} 
	
	
if ($sSaveClose) {
	if ($keepValues !=true) {
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
		$title = '';
	}
}

	if ($id) {
		
		// If Clicked to edit, get the data to display in fields and
		// buttons to edit it...
		
		
		$messageQuery = "SELECT *
				  FROM emailMessages
				  WHERE id = '$id'";
		$messageResult = dbQuery($messageQuery);
		while ($messageRow = dbFetchObject($messageResult)) {
			$id = $messageRow->id;		
			$subject = $messageRow->subject;
			$messageBody = $messageRow->message;
		}
	}
	// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
	include("$sGblIncludePath/adminAddHeader.php");	
	?>
	
	<br>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td colspan=2>[SENDER_NAME] will be replaced with name of the sender.</td></tr>
	<tr><td colspan=2>[HCV_LINK] will be replaced with link to the Handcrafters Village site.</td></tr>		
	<tr><td colspan=2>[USER_MESSAGE] will be replaced with the message user wants to send along with the link.</td></tr>
				<tr><Td>Subject</td><td><input type=text name=subject value='<?php echo $subject;?>' size=50></td></tr>
	<tr><Td>Message Body</td><td>
	<textarea name=messageBody rows=12 cols=50><?php echo $messageBody;?></textarea></td></tr>

</table>
<?php 
	// include footer
	
	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}			
?>
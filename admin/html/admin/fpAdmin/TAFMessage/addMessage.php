<?php

/*********

Script toFun Page TAF Message

**********/

include("../../../includes/paths.php");

$sPageTitle = "Fun Page TAF eMail Content";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {

if (($sSaveClose || $sSaveNew) && !($id)) {
		// if new data submitted
		$addQuery = "INSERT INTO fpEmailMessages(subject, message )
				 VALUES('$subject', '$messageBody')";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: fpEmailMessages.subject=$subject\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($addQuery);
		if (!($result))
		$message = mysql_error();
		
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		// if record edited
		
		$editQuery = "UPDATE fpEmailMessages SET						
						subject = '$subject',
						message = '$messageBody'
						WHERE id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: fpEmailMessages.id=$id\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$result = mysql_query($editQuery);
		if (!($result)) {
			$message=mysql_error();
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
		$active='';
		$sortOrder = '';		
	}
}
		
	if ($id) {
		
		// If Clicked to edit, get the data to display in fields and
		// buttons to edit it...
		
		
		$messageQuery = "SELECT *
				  FROM fpEmailMessages";
		$messageResult = mysql_query($messageQuery);
		while ($messageRow = mysql_fetch_object($messageResult)) {
			$id = $messageRow->id;
		//	$emailPurpose = $messageRow->purpose;
			$subject = $messageRow->subject;
			$messageBody = $messageRow->message;
		}
		
	} else {
		// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}
		
	// Hidden fields to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?menuId=$menuId";
		
	
	include("$sGblIncludePath/adminAddHeader.php");	
	
	?>
	


<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<br>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td colspan=2>[SENDER_NAME] will be replaced with name of the sender.</td></tr>
	<tr><td colspan=2>[FUNPAGE_LINK] will be replaced with link to the fun page.</td></tr>		
	<tr><td colspan=2>[USER_MESSAGE] will be replaced with the message user wants to send along with the link.</td></tr>
	<tr><td colspan=2>[SENDER_EMAIL] will be replaced with the email of the sender.</td></tr>
	<tr><td colspan=2>[RECIPIENT_EMAIL] will be replaced with the email of the recipient.</td></tr>
				<tr><Td>Subject</td><td><input type=text name=subject value='<?php echo $subject;?>' size=50></td></tr>
	<tr><Td>Message Body</td><td>
	<textarea name=messageBody rows=12 cols=60><?php echo $messageBody;?></textarea></td></tr>
	
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}

?>
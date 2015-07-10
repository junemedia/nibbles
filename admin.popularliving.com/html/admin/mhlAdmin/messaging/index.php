<?php

/**
* Script to send message to subscribers
*
* Table required for this script: mailList
*
*
* Script called to display screen to write the message and when admin sends message to all the subscribers.
* Message(email) will be sent only to the subscribers, which has status active='Y'.
* Unsubscribe link is in the message by clicking on it, user can unsubscribe from the mailing list.
*
*/

/**
* include library
*/
include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Send Message To Subscribers";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	set_time_limit (180);
	
	if ($send) {
		
		if ($testMsg) {
			
			$emailMessage = $emailMessage . "\n\n---------------------------------------------------------------

					If you wish to unsubscribe from this list, please visit:
					$sGblMhlSiteRoot/unsub.php/email/$testRecipient";
			
			
			mail($testRecipient, $subject, $emailMessage, "From: $fromEmail");
			$sMessage = "Test message sent to $testRecipient";
		} else {
			$selectQuery = "SELECT DISTINCT email
							FROM   mailList
							WHERE  active='Y'";
			$selectResult = dbQuery($selectQuery);
			$counter=0;
			while ($selectRow = dbFetchObject($selectResult)) {
				$emailMessage = $emailMessage . "\n\n---------------------------------------------------------------

					If you wish to unsubscribe from this list, please visit:
					$sGblMhlSiteRoot/unsub.php/email/".$selectRow->email;
				
				mail($selectRow->email, $subject, $emailMessage, "From: $fromEmail");
				$counter++;
			}
			$sMessage = "message sent to $counter subscribers";
		}
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Message Sent\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
	}
	
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		   <input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";
	
	
	include("$sGblIncludePath/adminHeader.php");	
	
	?>
	<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td colspan=2>Use this form to email your past customers.</td></tR>
	<tr><td width=20%>From Email:</td>
	<td><input type=text name=fromEmail size=30 value="<?php echo $fromEmail;?>"></td></tr>

	<tr><td>Subject:</td>
	<td><input type=text name=subject size=40 value="<?php echo $subject;?>"></td></tr>

	<tr><td valign=top> Message:<br><br><font size="1">Note: an unsubscribe message & link will be added to the bottom of the message.</font></td>
	<td><textarea name=emailMessage rows=15 cols=50><?php echo $emailMessage;?></textarea>
	</td></tr>

	<tr><td>Is this a test message?</td><td>
	<input type=radio name=testMsg value="1"> Yes
	<input type=radio name=testMsg value="0" checked> No
	<br><br></td></tr><tr><td></td><td>
	If so, please enter an email address to receive the test below:
	<br><Br>
	<input type=text name=testRecipient size=30 value="<?php echo $testRecipient;?>">
	</td></tr>
	<tr><td colspan=2 align=center>
	<input name=send type="Submit" value="Send">
	</td></tr>
	
	</table>
</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>	



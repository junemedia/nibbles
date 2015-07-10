<?php

/*********

Script to You Won eMail Content

**********/

include("../../includes/paths.php");


$sPageTitle = "You Won eMail Content";

session_start();

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sSave) {
		
		// if record edited
		$sSelectQuery="SELECT * FROM emailContents
					  WHERE  emailPurpose = '$sEmailPurpose'
					  AND    system = 'youWon' ";
		$rSelectResult = dbQuery($sSelectQuery);
		if (dbNumRows($rSelectResult) == 0) {
		// if new data submitted		
		$sAddQuery = "INSERT INTO emailContents(emailPurpose, system, subject, messageBody ) 
				 VALUES('$sEmailPurpose', 'youWon', '$sSubject', '$sMessageBody')";		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
			$sMessage = dbError();
		} else {
			$sEditQuery = "UPDATE emailContents SET
						emailPurpose = '$sEmailPurpose',
						subject = '$sSubject',
						messageBody = '$sMessageBody'
						WHERE system = 'youWon'";
			
			$rResult = dbQuery($sEditQuery);
			if (!($rResult)) {
				$sMessage=dbError();
			}
		}
	} 
			
		// If Clicked to edit, get the data to display in fields and 
		// buttons to edit it...
		
		$sSelectQuery="SELECT * FROM emailContents
					  WHERE  system = 'youWon'";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$sEmailPurpose = $oSelectRow->emailPurpose;
			$sSubject = $oSelectRow->subject;
			$sMessageBody = $oSelectRow->messageBody;			
		}		
	
		
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	$sYouWonLink = "<a href='index.php?iMenuId=$iMenuId'>Back To You Won Admin Menu</a>";
					
	
include("../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>

		
<table width=95% align=center>
<tr><td align=left><?php echo $sYouWonLink;?></td></tr>
	<tr><td align=center width=100%>"[EMAIL_LINK]" in the message body will be replaced by user email address
	</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><Td>eMail Purpose</td><td><input type=text name=sEmailPurpose value='<?php echo $sEmailPurpose;?>'></td></tr>
				<tr><Td>Subject</td><td><input type=text name=sSubject value='<?php echo $sSubject;?>' size=30></td></tr>
	<tr><Td>Message Body</td><td>
	<textarea name=sMessageBody rows=8 cols=40><?php echo $sMessageBody;?></textarea></td></tr>
	<tr><Td></td><td><input type=submit name=sSave value='Save'> &nbsp; 
			<input type=reset name=reset value='Reset'></td>
	</tr>	
			
</table>

</form>

<?php 
	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authoresed to access this page...";
}				

?>
<?php

/*********

Script to Display List/Add/Edit/Delete You Won eMail Contents

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Change Privacy Policy";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
		
	if ($sSave) {
		$sDeleteEntry = "DELETE FROM helpPage
					WHERE contentType = 'privacy'";
		$rDeleteResult = dbQuery($sDeleteEntry);
		
		$sInsertPrivacy = "INSERT INTO helpPage (contentType, content) VALUES ('privacy', \"" . addslashes($sPolicyContent) . "\")";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update policy: " . addslashes($sInsertPrivacy) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rInsertPrivacyResult = dbQuery($sInsertPrivacy);
		echo mysql_error();
		$sMessage = "Changes Saved";
	}

	
	
	
	$sPrivacyContentQuery = "SELECT content
					FROM helpPage
					WHERE contentType = 'privacy'";
	$rPrivacyContentResult = dbQuery($sPrivacyContentQuery);

	while ($sRow = dbFetchObject($rPrivacyContentResult)) {
		$sPolicyContent = $sRow->content;
	}
	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td align=left valign=top>Privacy Policy:</a></th>

<Td align=left valign=top><textarea  name=sPolicyContent rows=15 cols=50><?php echo $sPolicyContent;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
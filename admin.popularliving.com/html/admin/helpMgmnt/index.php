<?php

/*********

Script to Display List/Add/Edit/Delete You Won eMail Contents

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Change Help";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {


	if ($sSave) {
		$sDeleteEntry = "DELETE FROM helpPage
					WHERE contentType = 'help'";
		$rDeleteResult = dbQuery($sDeleteEntry);
		$sHelpContent = addslashes($sHelpContent);
		$sInsertHelp = "INSERT INTO helpPage (contentType, content) VALUES ('help', \"$sHelpContent\")";
		$rInsertHelpResult = dbQuery($sInsertHelp);
		echo mysql_error();
		$sMessage = "Changes Saved";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Help Page Content Changed...\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
	}

	
	

	$sHelpContentQuery = "SELECT content
					FROM helpPage
					WHERE contentType = 'help'";
	$rHelpContentResult = dbQuery($sHelpContentQuery);

	while ($sRow = dbFetchObject($rHelpContentResult)) {
		$sHelpContent = $sRow->content;
	}


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

	include("../../includes/adminHeader.php");

?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td align=left valign=top>Help Content:</a></th>

<Td align=left valign=top><textarea  name=sHelpContent rows=15 cols=50><?php echo $sHelpContent;?></textarea></td>

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
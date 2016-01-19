<?php

include("../../includes/paths.php");

session_start();
$sPageTitle = "MyFree eCookBook";
session_start();

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSave) {
		$sDeleteEntry = "DELETE FROM nibbles_r4l.myfree_pages
					WHERE page = 'ecookbook.php'";
		$rDeleteResult = dbQuery($sDeleteEntry);
		$sContent = addslashes($sContent);
		$sInsertHelp = "INSERT INTO nibbles_r4l.myfree_pages (content, page) VALUES (\"$sContent\",'ecookbook.php')";
		$rInsertHelpResult = dbQuery($sInsertHelp);
		echo mysql_error();
		$sMessage = "Changes Saved";
	}

	$sContentQuery = "SELECT content
					FROM nibbles_r4l.myfree_pages
					WHERE page = 'ecookbook.php'";
	$rContentResult = dbQuery($sContentQuery);
	while ($sRow = dbFetchObject($rContentResult)) {
		$sContent = $sRow->content;
	}
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	include("../../includes/adminHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td align=left valign=top>MyFree eCookBook Page Content:</a></th>
<Td align=left valign=top><textarea  name=sContent rows=15 cols=50><?php echo $sContent;?></textarea></td>
</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
</table>
</form>
<table cellpadding=5 cellspacing=0 bgcolor='White' width=95% align=center>
<tr align="center"><td colspan="2"><b>Page Preview:</b></td></tr>
<tr align="center"><td colspan="2"><iframe src="http://www.myfree.com/ecookbook.php" width="950px" frameborder="0" height="800px"></td></tr>
</table>
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

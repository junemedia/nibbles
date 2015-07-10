<?php

/*********

Script to Display List/Add/Edit/Delete You Won eMail Contents

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Change Must Have Cookies Page Content";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
		
	if ($sSave) {
		
		$sFile = fopen($sGblWebRoot."/mustHaveCookies.html","w");
		$sPageContent = stripslashes($sPageContent);
		if ($sFile) {
			
			if (!fwrite($sFile, $sPageContent)) {				
				$sMessage = "Page Writing Error...";
				
			} else {
				$sMessage = "Must Have Cookies Page Changed...";
			}
		} else {
			$sMessage = "Error In Opening Must Have Cookies Page For Writing Content...";
		}
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Changes made to /home/sites/admin.popularliving.com/html/mustHaveCookies.html ...\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles

	} 

	
	$sPageContent = '';	
	
	
	$sFile = fopen($sGblWebRoot."/mustHaveCookies.html","r");
	if ($sFile) {
		
		while ($temp = fread($sFile,1024)) {
			$sPageContent .= $temp;
		}
		$sPageContent = stripslashes($sPageContent);
	} else {
		$sMessage = "Must Have Cookies Page Not Exists...";
	}
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td align=left valign=top>Must Have Cookies Page Content:</a></th>

<Td align=left valign=top><textarea  name=sPageContent rows=15 cols=50><?php echo $sPageContent;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td>This will change the Must Have Cookies file located at /home/sites/admin.popularliving.com/html/mustHaveCookies.html</td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
session_start();
if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && ($iId)) {
		$sDefaultTitle = addslashes($sDefaultTitle);
		$sEditQuery = "UPDATE links SET defaultTitle = \"$sDefaultTitle\" WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('".$_SERVER['PHP_AUTH_USER']."', '$PHP_SELF', now(), \"" . addslashes($sEditQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		echo "<script language=JavaScript>				
				 self.close();
				</script>";
	}
								
	if ($iId) {
		$sSelectQuery = "SELECT defaultTitle,sourceCode FROM links WHERE  id = '$iId'";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oSelectRow = dbFetchObject($rSelectResult)) {		
			$sDefaultTitle = $oSelectRow->defaultTitle;
			$sSourceCode = $oSelectRow->sourceCode;
		}
	}
		
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'><input type=hidden name=iId value='$iId'>";
	include("../../includes/adminAddHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan="2">Source Code: <?php echo $sSourceCode; ?></td></tr>
	
	<tr><td>Page Title</td>
	<td><textarea name=sDefaultTitle rows=15 cols=50><?php echo $sDefaultTitle;?></textarea>
	<br>One Page Title Per Line</td></tr>
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

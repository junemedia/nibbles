<?php

include("../../../includes/paths.php");

include_once("/var/www/html/admin.popularliving.com/config_newsletter_archive.php");

session_start();
$sList = '';

mysql_select_db($dbase);



$sSelectQuery = "SELECT * FROM ads ORDER BY id DESC";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}
	$sList .= "<tr class=$sBgcolorClass><TD>$oRow->tag</td>
					<TD><a href='JavaScript:void(window.open(\"add.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=600, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					</td></tr>";
}
	
if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Records Exist...";
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";

$sAddButton ="<input type=button name=sAdd value=Add disabled onClick='JavaScript:void(window.open(\"add.php?iMenuId=$iMenuId\", \"\", \"height=600, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
include("../../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=75% align=center>
<tr><td colspan=7 align="right"><?php echo $sAddButton;?></td></tr>
<tr><td class='header' colspan="2" align="center">Ads Management</td>
</tr>
<?php echo $sList;?>
</table>

</form>
	
<?php
	include("../../../includes/adminFooter.php");
?>

<?php

include("../../../includes/paths.php");

include_once("/var/www/html/admin.popularliving.com/config_newsletter_archive.php");

session_start();
$sList = '';

mysql_select_db($dbase);



$sSelectQuery = "SELECT * FROM newsletters ORDER BY id DESC";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}
	$sList .= "<tr class=$sBgcolorClass>
	<td>$oRow->subject</td>
	<td>$oRow->list</td>
	<td>$oRow->newsletterDate</td>
	<td><a href='JavaScript:void(window.open(\"edit.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=600, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a></td>
	</tr>";
}
	
if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Records Exist...";
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";

$sAddButton ="";
include("../../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<?php echo $sHidden;?>
<table cellpadding=10 cellspacing=10 bgcolor=c9c9c9 width=75% align=center border="0">
<tr><td colspan=4 align="right"><?php echo $sAddButton;?></td></tr>
<tr><td class='header' colspan="4" align="center">Meta Keywords/Description Management</td>
</tr>
<?php echo $sList;?>
</table>

</form>
	
<?php
	include("../../../includes/adminFooter.php");
?>


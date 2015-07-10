<?php

include("../../includes/paths.php");

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
	
	if ($oRow->live == 'Y') {
		$checked = ' checked ';
	} else {
		$checked = '';
	}
	
	
	$sList .= "<tr class=$sBgcolorClass>
	<td>$oRow->subject</td>
	<td><input type='checkbox' value='$oRow->id' onchange='process_pub_unpub(this.checked,$oRow->id);' $checked><div id='$oRow->id'></div></td>
	<td>$oRow->list</td>
	<td>$oRow->newsletterDate</td>
	</tr>";
}
	
if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Records Exist...";
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";

$sAddButton ="";
include("../../includes/adminHeader.php");	

?>



<SCRIPT LANGUAGE=JavaScript SRC="http://r4l.popularliving.com/subctr/js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function process_pub_unpub(request_type,id) {
	if (request_type == true) {
		// checked - meaning process publish
		div = getObject(id);
		txt = div.innerHTML;
		div.innerHTML = "<img src='r4l_loader.gif' border='0'>";
		response=coRegPopup.send('process_publish_unpublish.php?id='+id+'&live=Y','');
		//alert(response);
		div.innerHTML = "<font size='1' color='Green'><b>Published</b></font>";
	} else {
		// unchecked - meaning process unpublish
		div = getObject(id);
		txt = div.innerHTML;
		div.innerHTML = "<img src='r4l_loader.gif' border='0'>";
		response=coRegPopup.send('process_publish_unpublish.php?id='+id+'&live=N','');
		//alert(response);
		div.innerHTML = "<font size='1' color='Red'><b>Unpublished</b></font>";
	}
	return true;
}
</script>


<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<?php echo $sHidden;?>
<table cellpadding=10 cellspacing=10 bgcolor=c9c9c9 width=75% align=center border="0">
<tr><td colspan=4 align="right"><?php echo $sAddButton;?></td></tr>
<tr><td colspan=4 align="right">*** If checkbox is checked, that means newsletter is LIVE.  To unpublish newsletter, simply uncheck the checkbox. ***</td></tr>
<tr><td colspan=4 align="right">*** Publish and unpublish is done REAL-TIME. ***</td></tr>
<tr><td colspan=4 align="right">*** Even if Newsletter is LIVE, if newsletter date is within last 30 days, that newsletter will not be visible to public. ***</td></tr>
<tr><td class='header' colspan="4" align="center">Update Newsletter HTML Code Management</td>
</tr>
<tr><td><b>Title/Subject</b></td><td><b>Live?</b></td><td><b>List</b></td><td><b>Newsletter Date</b></td></tr>
<?php echo $sList;?>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
?>


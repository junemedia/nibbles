<?php

include_once("../../../includes/paths.php");
mysql_select_db('newsletter_templates');

session_start();
$sList = '';

$query_filter = "";
if ($submit == 'Search...') {
	if ($template != '') {
		$query_filter .= " AND template = \"$template\"";
	}
}


$sSelectQuery = "SELECT * FROM automated WHERE 1=1 $query_filter ORDER BY id DESC LIMIT 50";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($sBgcolorClass == "ODD") { 	$sBgcolorClass = "EVEN"; } else { 	$sBgcolorClass = "ODD";  }

	$sList .= "<tr class=$sBgcolorClass>
			<td><b>$oRow->id</b></td>
			<td><a href='create.php?iId=$oRow->id&subject=".urlencode($oRow->subject)."' target=_blank><b>$oRow->subject</b></a>
			</td>
			<td>$oRow->template</td>
			<td>$oRow->mailing_date</td>
			<td><a href='addEdit.php?iId=$oRow->id' onclick=\"javascript:void window.open('addEdit.php?iId=$oRow->id','edit_$oRow->id','width=700,height=700,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');return false;\">Edit</a></td>
			</tr>";
}

if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Records Exist...";
}

include_once("../../../includes/adminHeader.php");


$template_options = "<option></option>";
if ($handle = opendir('templates')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
        	if (strtolower($entry) == $template) { $selected = 'selected'; } else { $selected = ''; }
            $template_options .= "<option value='$entry' $selected>$entry</option>";
        }
    }
    closedir($handle);
}
?>

<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>' method="POST">
<?php echo $sHidden;?>
<table cellpadding='10' cellspacing='10' bgcolor=c9c9c9 width=75% align=center border="0">
	<tr>
		<td>Template: <select name="template" id="template"><?php echo $template_options; ?></select></td>
		<td><input type="submit" name="submit" id="submit" value="Search..."></td>
	</tr>
</table>
</form>
<table cellpadding='10' cellspacing='10' bgcolor=c9c9c9 width=75% align=center border="0">
<tr>
	<td colspan='4' align="right">
		<a href='image.php' onclick="javascript:void window.open('image.php','add','width=800,height=600,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=50,top=50');return false;">Upload Image to Campaigner/Cloud/Akamai</a>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<a href='addEdit.php' onclick="javascript:void window.open('addEdit.php','add','width=700,height=700,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=50,top=50');return false;">Create New Newsletter</a>
	</td>
</tr>
<tr>
	<td class='header' colspan="4" align="center">Automated Newsletters Management</td>
</tr>
<tr>
	<td><b>ID</b></td>
	<td><b>Subject</b></td>
	<td><b>Template</b></td>
	<td><b>Mailing Date</b></td>
	<td><b>Edit</b></td>
</tr>
<?php echo $sList;?>
</table>
<table cellpadding=5 cellspacing=0 width=75% align=center border="0">
<tr>
	<td><b>Note:</b>  This page will ONLY display last 50 issues.  If you need access to older issues, please contact Samir.</td>
</tr>
</table>

<?php include_once("../../../includes/adminFooter.php"); ?>

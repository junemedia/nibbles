<?php

include("../../../includes/paths.php");

session_start();

$sPageTitle = "Ads Templates";

session_start();

mysql_select_db('newsletter_templates');



if ($sSave) {
	$sContent = addslashes($sContent);
	$update = "UPDATE ads SET content = \"$sContent\" WHERE id = '$iId'";
	$update_result = dbQuery($update);
	echo mysql_error();
	$sMessage = "Changes Saved";
}

	
	

$rContentResult = dbQuery("SELECT * FROM ads WHERE id = '$iId'");
while ($sRow = dbFetchObject($rContentResult)) {
	$sContent = $sRow->content;
	$sContent = stripslashes($sContent);
	$title = $sRow->title;
}


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../../includes/adminHeader.php");

?>


<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td align=left valign=top></th>

<Td align=left valign=top>
<?php echo $title; ?><br><br>
<textarea  name=sContent rows=15 cols=50><?php echo $sContent;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td></td></tr>
</table>
</form>

<?php include("../../../includes/adminFooter.php"); ?>


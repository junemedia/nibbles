<?php

include("../../../includes/paths.php");

include_once("/var/www/html/admin.popularliving.com/config_newsletter_archive.php");

session_start();

$sPageTitle = "Ads Templates";

session_start();

mysql_select_db($dbase);



if ($sSave) {
	$code = addslashes($code);
	$update = "UPDATE ads SET code = \"$code\" WHERE id = '$iId'";
	$update_result = dbQuery($update);
	echo mysql_error();
	$sMessage = "Changes Saved";
}

	
	

$rContentResult = dbQuery("SELECT * FROM ads WHERE id = '$iId'");
while ($sRow = dbFetchObject($rContentResult)) {
	$code = $sRow->code;
	$code = stripslashes($code);
	$tag = $sRow->tag;
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
<?php echo $tag; ?><br><br>
<textarea  name=code rows=15 cols=50><?php echo $code;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td></td></tr>
</table>
</form>

<?php include("../../../includes/adminFooter.php"); ?>


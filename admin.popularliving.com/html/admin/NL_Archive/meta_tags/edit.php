<?php

include("../../../includes/paths.php");

include_once("/var/www/html/admin.popularliving.com/config_newsletter_archive.php");

session_start();

$sPageTitle = "Meta Keywords/Description Templates";

session_start();

mysql_select_db($dbase);



if ($sSave) {
	$keywords = addslashes($keywords);
	$desc = addslashes($desc);
	
	$update = "UPDATE newsletters SET 
				newsletters.keywords = \"$keywords\", 
				newsletters.desc = \"$desc\" 
			WHERE id = \"$iId\" LIMIT 1";
	$update_result = dbQuery($update);
	//echo $update."<br>";
	echo mysql_error();
	$sMessage = "Changes Saved";
}


$rContentResult = dbQuery("SELECT * FROM newsletters WHERE id = '$iId'");
while ($sRow = dbFetchObject($rContentResult)) {
	$subject = $sRow->subject;
	$keywords = stripslashes($sRow->keywords);
	$desc = stripslashes($sRow->desc);
}


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../../includes/adminHeader.php");

?>


<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr>
<td align=left valign=top colspan="2" valign="top">
<?php echo $subject; ?><br><br>Keywords:
<input type="text" size="50" name="keywords" id="keywords" value="<?php echo $keywords;?>">
</td>
</tr>

<tr><td></td><td></td></tr>
<tr>
<td align=left valign=top colspan="2" valign="top">
Description:
<input type="text" size="50" name="desc" id="desc" value="<?php echo $desc;?>">
</td>
</tr>


<tr><td></td><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></td><td></td></tr>
</table>
</form>

<?php include("../../../includes/adminFooter.php"); ?>


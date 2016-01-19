<?php

include_once("include.php");

$error = '';
if ($submit == 'Add') {
	if ($templateName == '') {
		$error = '* Please enter all required fields';
	}
	if (!ctype_alnum($templateName)) {
		$error = '* Template Name must be alpha numeric';
	}
	if (!ctype_digit($width)) {
		$error = '* Template width must be alpha numeric';
	}
	if (!ctype_digit($height)) {
		$error = '* Template height must be alpha numeric';
	}
	if ($listid == '') {
		$listid = '393,396';
	}
	
	if ($error == '') {
		$insert = "INSERT IGNORE INTO templates (templateName,listid,width,height) VALUES (\"$templateName\",\"$listid\",\"$width\",\"$height\")";
		$result = mysql_query($insert);
		echo mysql_error();
		$error = 'Added successfully...';
		$templateName = '';
	}
}
echo "<font color='red'>".$error."</font><br><br>";



$template_list = '';
$result = mysql_query("SELECT * FROM templates ORDER BY templateName ASC");
while ($row = mysql_fetch_object($result)) {
	$template_list .= $row->templateName . " => " . $row->listid."<br>";
}


?>
<script>
function isNumberKey(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		return false;
	} else {
		return true;
	}
}
</script>
<font color="green">To add new template, please send mockup to IT</font>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table>
	<tr>
		<td>Template Name: <input type="text" name="templateName" value="<?php echo $templateName; ?>" maxlength="255"> (letters and numbers only)</td>
	</tr>
	<tr>
		<td>ListID: <input type="text" name="listid" value="<?php echo $listid; ?>" maxlength="50"> (separated by comma:  393,396 - default)</td>
	</tr>
	<tr>
		<td>Width: <input type="text" name="width" onkeypress="return isNumberKey(event)" value="<?php echo $width; ?>" maxlength="4"></td>
	</tr>
	<tr>
		<td>Height: <input type="text" name="height" onkeypress="return isNumberKey(event)" value="<?php echo $height; ?>" maxlength="4"></td>
	</tr>
	<tr>
		<td><input type="submit" name="submit" value="Add"></td>
	</tr>
	<tr>
		<td>(Once added, it <b>cannot</b> be changed so please double check entry before adding it)</td>
	</tr>
</table>
</form>
<?php
echo "<br><br>";
echo $template_list;
echo "<br><br>";
?>

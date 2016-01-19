<?php


include_once("../subctr_config.php");

$error = '';
if ($submit == 'Submit' && $iId !='') {
	$sources = str_replace(' ','',$sources);
	$temp = explode(",", $sources);
	foreach ($temp as $val) {
		if (!ctype_digit($val)) {
			$error = 'Invalid subcampid found';
			break;
		}
	}
	
	if ($error == '') {
		if ($groupName !='' && $sources !='') {
			$groupName = addslashes($groupName);
			$sources = addslashes($sources);
			$insert = "UPDATE groupSubcampId SET groupName = \"$groupName\", sources = \"$sources\" WHERE id='$iId'";
			$insert_result = mysql_query($insert);
			echo mysql_error();
			$error = 'Updated Successfully...';
		} else {
			$error = 'Group Name and Sources are required field...';
		}
	}
}

$rSelectResult = mysql_query("SELECT * FROM groupSubcampId WHERE id='$iId'");
echo mysql_error();
while ($oRow = mysql_fetch_object($rSelectResult)) {
	$groupName = $oRow->groupName;
	$sources = $oRow->sources;
}


?>
<style>
* {
	font-family: verdana;
	font-size: 12px;
}
</style>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<table cellpadding=5 cellspacing=0 width=50% align=center>
<input type="hidden" name="iId" value="<?php echo $iId; ?>">
<tr align="center">
	<td colspan="2"><b>Edit Group</b></td>
</tr>
<tr>
	<td colspan="2" style="color:red;"><b><?php echo $error; ?></b></td>
</tr>
<tr>
	<td>Group Name:</td>
	<td><input type="text" maxlength="255" size="50" name="groupName" value="<?php echo $groupName; ?>"> <font size="2">Alpha Numeric name only</font></td>
</tr>
<tr>
	<td>Sources:</td>
	<td><input type="text" maxlength="255" size="50" name="sources" value="<?php echo $sources; ?>"> <font size="2">e.g.: 2761,2918,2917,etc (separated by comma)</font></td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" name="submit" value="Submit">
	</td>
</tr>
</table>
</form>
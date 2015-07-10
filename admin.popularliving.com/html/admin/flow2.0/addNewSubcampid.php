<?php

include_once("include.php");

$api = '';
$error = '';
if ($submit == 'Add') {
	if ($subcampid == '' || $subcampidName == '') {
		$error = '* Please enter all required fields';
	}
	if (strlen($subcampid) != 4) {
		$error = '* Subcampid must be 4 digit in length';
	}
	if (!ctype_digit($subcampid)) {
		$error = '* Subcampid must be numeric';
	}
	if (!ctype_alnum($subcampidName)) {
		$error = '* SubcampidName must be alpha numeric';
	}
	
	if ($error == '') {
		$insert = "INSERT IGNORE INTO subcampids (subcampid,subcampidName) VALUES (\"$subcampid\",\"$subcampidName\")";
		$result = mysql_query($insert);
		echo mysql_error();
		$error = 'Added successfully...';
		$api = "<img src='"."http://admin.popularliving.com/admin/subctr/subcampid_api.php?subcampid=".$subcampid."&subcampidName=".$subcampidName."' width='1' height='1' border='0' />";
		$subcampid = '';
		$subcampidName = '';
	}
}



$subcampid_list = '';
$result = mysql_query("SELECT * FROM subcampids ORDER BY subcampid ASC");
while ($row = mysql_fetch_object($result)) {
	$subcampid_list .= $row->subcampid . " => " . $row->subcampidName."<br>";
}


echo "<font color='red'>".$error."</font>".$api;
?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table>
	<tr>
		<td>Subcampid: <input type="text" name="subcampid" value="<?php echo $subcampid; ?>" maxlength="4" size="4"> (4 digit numeric only)</td>
	</tr>
	<tr>
		<td>SubcampId Name: <input type="text" name="subcampidName" value="<?php echo $subcampidName; ?>" maxlength="255"> (letters and numbers only)</td>
	</tr>
	<tr>
		<td><input type="submit" name="submit" value="Add"></td>
	</tr>
	<tr>
		<td>(Once added, it <b>cannot</b> be changed so please double check entry before adding it)</td>
	</tr>
	
	<tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr>
	<tr>
		<td><b>Existing SubcampIDs:</b></td>
	</tr>
	<tr>
		<td><?php echo $subcampid_list; ?></td>
	</tr>
</table>
</form>

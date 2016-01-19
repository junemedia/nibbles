<?php

include_once("include.php");

$error = '';
if ($submit == 'Add') {
	if ($sourceName == '') {
		$error = '* Please enter all required fields';
	}
	if (!ctype_alnum($sourceName)) {
		$error = '* Source Name must be alpha numeric';
	}
	
	if ($error == '') {
		$insert = "INSERT IGNORE INTO source (sourceName) VALUES (\"$sourceName\")";
		$result = mysql_query($insert);
		echo mysql_error();
		$error = 'Added successfully...';
		$sourceName = '';
	}
}


$source_list = '';
$result = mysql_query("SELECT * FROM source ORDER BY sourceName ASC");
while ($row = mysql_fetch_object($result)) {
	$source_list .= $row->sourceName."<br>";
}


echo "<font color='red'>".$error."</font>";
?>
<center><h3>FitAndFabLiving</h3></center>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table>
	<tr>
		<td>Source Name: <input type="text" name="sourceName" value="<?php echo $sourceName; ?>" maxlength="255"> (letters and numbers only)</td>
	</tr>
	<tr>
		<td><input type="submit" name="submit" value="Add"></td>
	</tr>
	<tr>
		<td>(Once added, it <b>cannot</b> be changed so please double check entry before adding it)</td>
	</tr>
	
	<tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr>
	<tr>
		<td><b>Existing Sources:</b></td>
	</tr>
	<tr>
		<td><?php echo $source_list; ?></td>
	</tr>
</table>
</form>

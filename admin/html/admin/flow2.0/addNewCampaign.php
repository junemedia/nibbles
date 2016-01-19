<?php

include_once("include.php");

$error = '';
if ($submit == 'Add') {
	if ($campaignName == '') {
		$error = '* Please enter all required fields';
	}
	if (!ctype_alnum($campaignName)) {
		$error = '* Campaign Name must be alpha numeric';
	}
	
	if ($error == '') {
		$insert = "INSERT IGNORE INTO campaign (campaignName) VALUES (\"$campaignName\")";
		$result = mysql_query($insert);
		echo mysql_error();
		$error = 'Added successfully...';
		$campaignName = '';
	}
}


$campaign_list = '';
$result = mysql_query("SELECT * FROM campaign ORDER BY campaignName ASC");
while ($row = mysql_fetch_object($result)) {
	$campaign_list .= $row->campaignName."<br>";
}



echo "<font color='red'>".$error."</font>";
?>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table>
	<tr>
		<td>Campaign Name: <input type="text" name="campaignName" value="<?php echo $campaignName; ?>" maxlength="255"> (letters and numbers only)</td>
	</tr>
	<tr>
		<td><input type="submit" name="submit" value="Add"></td>
	</tr>
	<tr>
		<td>(Once added, it <b>cannot</b> be changed so please double check entry before adding it)</td>
	</tr>
	
	<tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr>
	<tr>
		<td><b>Existing Campaigns:</b></td>
	</tr>
	<tr>
		<td><?php echo $campaign_list; ?></td>
	</tr>
	
	
</table>
</form>

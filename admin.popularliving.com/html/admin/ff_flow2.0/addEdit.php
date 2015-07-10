<?php

include_once("include.php");

$error = '';
if ($submit == 'Add/Update') {
	if ($linkName == '') {
		$error = '* Please enter link name.<br>';
	}
	if ($pageUrl == '') {
		$error = '* Please enter page URL.';
	} else {
		if (!strstr($pageUrl, 'fitandfabliving.com')) {
			$error = '* Page URL must be fitandfabliving.com.';
		}
	}
	if (!ctype_digit($opacity) || $opacity > 100 || $opacity < 0) {
		$error = '* Opacity must be numeric between 0 to 100.';
	}
	if (!ctype_digit($delay) || $delay > 30 || $delay < 0) {
		$error = '* Delay must be numeric between 0 to 30.';
	}
	
	// process only if there are no errors
	if ($error == '') {
		if ($linkid == '') {
			// INSERT
			$linkName = addslashes($linkName);
			
			$charid = strtoupper(md5(uniqid(rand(), true)));
        	$linkid = substr($charid, 0, 8).chr(45).substr($charid, 8, 4).chr(45).substr($charid,12, 4).chr(45).substr($charid,16, 4).chr(45).substr($charid,20,12);
        	
        	$insert = "INSERT INTO links (linkid,name,url,template,opacity,delay,usercontrol,source,campaign,dateTime,isActive) 
        			VALUES (\"$linkid\",\"$linkName\",\"$pageUrl\",\"$template\",\"$opacity\",\"$delay\",\"$userControl\",\"$source\",\"$campaign\",NOW(),\"$isActive\")";
        	$result = mysql_query($insert);
        	echo mysql_error();

        	$error = "Link added successfully (it may take upto 10 mins before preview link can work properly)";
		} else {
			// UPDATE
			$update = "UPDATE links SET 
								name=\"$linkName\",
								url=\"$pageUrl\",
								template=\"$template\",
								opacity=\"$opacity\",
								delay=\"$delay\",
								usercontrol=\"$userControl\",
								source=\"$source\",
								campaign=\"$campaign\",
								isActive=\"$isActive\"
						WHERE linkid = \"$linkid\"";
			$result = mysql_query($update);
        	echo mysql_error();
			
			$error = "Link updated successfully (it may take upto 10 mins for this changes to go live)";
		}
	}
}





if ($linkid != '') {
	// PULL DATA FROM DB AND FILL BELOW FORM
	$get_data_result = mysql_query("SELECT * FROM links WHERE linkid = \"$linkid\"");
	while ($data_row = mysql_fetch_object($get_data_result)) {
		$linkName = $data_row->name;
		$pageUrl = $data_row->url;
		$template = $data_row->template;
		$opacity = $data_row->opacity;
		$delay = $data_row->delay;
		$userControl = $data_row->usercontrol;
		$source = $data_row->source;
		$campaign = $data_row->campaign;
		$isActive = $data_row->isActive;
	}
	if (mysql_num_rows($get_data_result) == 0) {
		$error = "No Record Found";
		$linkid = '';	// clear linkid since we can't find this record
	}
}


$source_option = '';
$result_sources = mysql_query("SELECT sourceName FROM source ORDER BY sourceName ASC");
while ($source_row = mysql_fetch_object($result_sources)) {
	$selected = '';
	if ($source == $source_row->sourceName) {
		$selected = 'selected';
	}
	$source_option .= "<option value='$source_row->sourceName' $selected >$source_row->sourceName</option>";
}


$campaign_option = '';
$result_campaign = mysql_query("SELECT campaignName FROM campaign ORDER BY campaignName ASC");
while ($campaign_row = mysql_fetch_object($result_campaign)) {
	$selected = '';
	if ($campaign == $campaign_row->campaignName) {
		$selected = 'selected';
	}
	$campaign_option .= "<option value='$campaign_row->campaignName' $selected >$campaign_row->campaignName</option>";
}

$template_option = '';
$result_templates = mysql_query("SELECT * FROM templates ORDER BY templateName ASC");
while ($template_row = mysql_fetch_object($result_templates)) {
	$selected = '';
	if ($template == $template_row->templateName) {
		$selected = 'selected';
	}
	$template_option .= "<option value='$template_row->templateName' $selected >$template_row->templateName ($template_row->listid)</option>";
}

if ($opacity == '') {
	$opacity = '50';
}

if ($delay == '') {
	$delay = '1';
}

?>
<html>
<head>
<title>F&F Campaigns</title>
</head>
<body>
<center>
<h3>FitAndFabLiving</h3>
<a href="/admin/ff_flow2.0/">Back to Links Management</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="/admin/ff_flow2.0/addEdit.php">Create New Link</a>
<br><br>
<font color="red"><?php echo $error; ?></font>
</center>
<form name='form1' action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" value="<?php echo $linkid; ?>" name="linkid">
<table cellpadding="5" cellspacing="5" align="center">
	<tr>
		<td><b>Link Name</b>:</td>
		<td><input type="text" maxlength="255" size="50" name="linkName" id="linkName" value="<?php echo $linkName; ?>"></td>
	</tr>
	<tr>
		<td><b>Page URL</b>:</td>
		<td><input type="text" maxlength="255" size="50" name="pageUrl" id="pageUrl" value="<?php echo $pageUrl; ?>"> (you must include http://)</td>
	</tr>
	<tr>
		<td><b>Graphic (Template)</b>:</td>
		<td>
			<select name="template" id="template">
				<?php echo $template_option; ?>
			</select>
			&nbsp;(To add new template, please send mockup to IT)
		</td>
	</tr>
	<tr>
		<td><b>Opacity</b>:</td>
		<td><input type="text" maxlength="3" size="3" name="opacity" id="opacity" value="<?php echo $opacity; ?>"> (0 to 100 only, higher number = dark, lower number = light)</td>
	</tr>
	<tr>
		<td><b>Delay (in Seconds)</b>:</td>
		<td><input type="text" maxlength="2" size="2" name="delay" id="delay" value="<?php echo $delay; ?>"> (0 to 30 only)</td>
	</tr>
	<tr>
		<td><b>User Control</b>:</td>
		<td>
			Yes <input type="radio" name="userControl" id="userControl" value="Y" <?php if ($userControl == 'Y' || $userControl == '') { echo 'checked'; }?>>
			&nbsp;&nbsp;&nbsp;&nbsp;
			No <input type="radio" name="userControl" id="userControl" value="N" <?php if ($userControl == 'N') { echo 'checked'; }?>>
			&nbsp;&nbsp;&nbsp;&nbsp;
			(Allow user to close popup?)
		</td>
	</tr>
	<tr>
		<td><b>Source</b>:</td>
		<td>
			<select name="source" id="source">
				<?php echo $source_option; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Campaign</b>:</td>
		<td>
			<select name="campaign" id="campaign">
				<?php echo $campaign_option; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Active Link?</b>:</td>
		<td>
			Yes <input type="radio" name="isActive" id="isActive" value="Y" <?php if ($isActive == 'Y' || $isActive == '') { echo 'checked'; }?>>
			&nbsp;&nbsp;&nbsp;&nbsp;
			No <input type="radio" name="isActive" id="isActive" value="N" <?php if ($isActive == 'N') { echo 'checked'; }?>>
			&nbsp;&nbsp;&nbsp;&nbsp;
			(Yes = Live, No = Pause)
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Add/Update"></td>
	</tr>
	
	
	<tr>
		<td colspan="2" align="center">
		<b>Note:</b> SubcampID can be set <b>only</b> after generating link.
		<br>Once links are created, they cannot be deleted.
		<br><font color="red">Once links are created, you MUST assign subcampid.</font>
		</td>
	</tr>
</table>
</form>
</body>
</html>

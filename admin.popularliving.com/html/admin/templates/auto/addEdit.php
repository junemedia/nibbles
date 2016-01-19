<?php

include_once("../../../includes/paths.php");
mysql_select_db('newsletter_templates');

$refresh = '';
$error = '';
if ($submit == 'Add/Update') {
	// process only if there are no errors
	if ($error == '') {
		if ($iId == '') {	// INSERT
			$subject = addslashes($subject);
			$mailing_date = addslashes($mailing_date);
			$template = addslashes($template);
			$enable = addslashes($enable);
			$meta_keywords = addslashes($meta_keywords);
			$meta_desc = addslashes($meta_desc);
			
        	$insert = "INSERT IGNORE INTO automated (subject,mailing_date,enable,meta_keywords,meta_desc,template,from_name,from_email_id,reply_email_id,campaign_name) 
        			VALUES (\"$subject\",\"$mailing_date\",\"$enable\",\"$meta_keywords\",\"$meta_desc\",\"$template\",\"$from_name\",\"$from_email_id\", \"$reply_email_id\", \"$campaign_name\")";
        	$result = mysql_query($insert);
                //echo $insert;
                var_dump($result);
        	echo mysql_error();
        	
        	$iId = mysql_insert_id();

        	$error = "Insert Success";
		} else {
			// UPDATE
			$update = "UPDATE automated SET 
								subject=\"$subject\",
								mailing_date=\"$mailing_date\",
								enable=\"$enable\",
								meta_keywords=\"$meta_keywords\",
								meta_desc=\"$meta_desc\",
								template=\"$template\",
								from_name=\"$from_name\",
								from_email_id=\"$from_email_id\",
                                                                reply_email_id=\"$reply_email_id\",
								campaign_name=\"$campaign_name\"
						WHERE id = \"$iId\"";
			$result = mysql_query($update);
        	echo mysql_error();
			
			$error = "Update Success";
		}
	}
	
	$html = file_get_contents("templates/$template");
	preg_match_all("/\[.*?\]/",$html,$fields);
	$tags = array_unique ( $fields[0] );

	$curr_year = date('Y');
	foreach ($tags as $tag) {
		switch ($tag) {
			case "[ISSUE_DATE]":
				$insert = "REPLACE INTO automated_map (automated_id,tag_key,tag_value) VALUES (\"$iId\",\"$tag\",\"$mailing_date\")";
			break;
			case "[CURRENT_YEAR]":
				$insert = "REPLACE INTO automated_map (automated_id,tag_key,tag_value) VALUES (\"$iId\",\"$tag\",\"$curr_year\")";
			break;
			default:
				$insert = "INSERT IGNORE INTO automated_map (automated_id,tag_key) VALUES (\"$iId\",\"$tag\")";
		}
		$result = mysql_query($insert);
		echo mysql_error();
	}
	$refresh = "<script>window.opener.location = '/admin/templates/auto/index.php';</script>";
}


if ($iId != '') {
	// PULL DATA FROM DB AND FILL BELOW FORM
	$get_data_result = mysql_query("SELECT * FROM automated WHERE id = \"$iId\"");
	while ($data_row = mysql_fetch_object($get_data_result)) {
		$subject = $data_row->subject;
		$mailing_date = $data_row->mailing_date;
		$enable = $data_row->enable;
		$meta_keywords = $data_row->meta_keywords;
		$meta_desc = $data_row->meta_desc;
		$template = strtolower($data_row->template);
		$from_name = $data_row->from_name;
		$from_email_id = $data_row->from_email_id;
                $reply_email_id = $data_row->reply_email_id;
		$campaign_name = $data_row->campaign_name;
	}
	if (mysql_num_rows($get_data_result) == 0) {
		$error = "No Record Found";
		$iId = '';	// clear iId since we can't find this record
	}
} else {
	$mailing_date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
}


$template_options = "";
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
<html>
<head>
<title>Add New Newsletter</title>
<style>
* {
	font-family: verdana;
	font-size:12px;
}
</style>
<script>
function check_fields() {
	var subject = document.getElementById('subject').value;
	if(subject == '') {
		alert("Please enter subject");
		document.getElementById('subject').focus();
		return false;
	}
	return true;
}
function populateFromName(val) {
	if (val == 'copycat_pib.php') {
		document.getElementById('from_name').value = "Recipe4Living Copycat Classics";
	}
	if (val == 'diabetic.php') {
		document.getElementById('from_name').value = "Recipe4Living Diabetic-Friendly Dishes";
	}
	if (val == 'FF_daily_insider_v2.php') {
		document.getElementById('from_name').value = "Recipe4Living Diabetic-Friendly Dishes";
	}
	if (val == 'budget_pib.php') {
		document.getElementById('from_name').value = "Recipe4Living Budget Cooking";
	}
	if (val == 'daily_insider.php') {
		document.getElementById('from_name').value = "Fit&Fab Living Newsletter";
	}
	if (val == 'quickeasy.php') {
		document.getElementById('from_name').value = "Recipe4Living Quick and Easy";
	}
	if (val == 'savvy_fork_v2.php') {
		document.getElementById('from_name').value = "Dan at SavvyFork";
	}
	if (val == 'savvy_fork.php') {
		document.getElementById('from_name').value = "Dan at SavvyFork";
	}
	if (val == 'savvy_fork_pib.php' || val== 'savvy_fork_v2_pib.php') {
		document.getElementById('from_name').value = "Ashley at SavvyFork";
		document.getElementById('from_email_id').value = 5080120;
		document.getElementById('reply_email_id').value = 5080120;
	}
	if (val == 'second_send_alpha_pib.php') {
		document.getElementById('from_name').value = "Dan from Recipe4Living";
		document.getElementById('from_email_id').value = 5080120;
		document.getElementById('reply_email_id').value = 5080120;
	}
	if (val == 'diabetic_pib.php') {
		document.getElementById('from_name').value = "Recipe4Living Diabetic-Friendly Dishes";
		document.getElementById('from_email_id').value = 5080120;
		document.getElementById('reply_email_id').value = 5080120;
	}
	if (val == 'daily_recipes.php') {
		document.getElementById('from_name').value = "Recipe4Living Daily Recipes";
	}
	if (val == 'diet_insider.php') {
		document.getElementById('from_name').value = "Fit&Fab Living Diet Insider";
	}
	if (val == 'diet_insider_pib.php') {
		document.getElementById('from_name').value = "Fit&Fab Living Diet Insider";
		document.getElementById('from_email_id').value = 5080119;
		document.getElementById('reply_email_id').value = 5080119;
	}
	if (val == 'daily_insider_pib.php') {
		document.getElementById('from_name').value = "Fit&Fab Living Newsletter";
		document.getElementById('from_email_id').value = 5080119;
		document.getElementById('reply_email_id').value = 5080119;
	}
	if (val == 'casserole.php') {
		document.getElementById('from_name').value = "Recipe4Living Casserole Cookin'";
	}
	if (val == 'crockpot_pib.php') {
		document.getElementById('from_name').value = "Recipe4Living Crockpot Creations";
	}
	if (val == 'daily_recipes_v2.php') {
		document.getElementById('from_name').value = "Recipe4Living Daily Recipes";
	}
	if (val == 'daily_recipes_pib.php') {
		document.getElementById('from_name').value = "Recipe4Living Daily Recipes";
		document.getElementById('from_email_id').value = 5080120;
		document.getElementById('reply_email_id').value = 5080120;
	}
	if (val == 'makingitwork.php') {
		document.getElementById('from_name').value = "Making It Work by Work It Mom";
		document.getElementById('from_email_id').value = 5080118;
		document.getElementById('reply_email_id').value = 5080118;
	}
	if (val == 'br.php') {
		document.getElementById('from_name').value = "BetterRecipes.com Community Favorites";
		document.getElementById('from_email_id').value = 5169791;
		document.getElementById('reply_email_id').value = 5173458;
	}
	if (val == 'br_ss_pib.php') {
		document.getElementById('from_name').value = "BetterRecipes.com Community Favorites";
		document.getElementById('from_email_id').value = 5169791;
		document.getElementById('reply_email_id').value = 5173458;
	}
	if (val == 'br_sweeps.php') {
		document.getElementById('from_name').value = "BetterRecipes.com Community Favorites";
		document.getElementById('from_email_id').value = 5169791;
		document.getElementById('reply_email_id').value = 5173458;
	}
	if (val == 'br_daily.php') {
		document.getElementById('from_name').value = "BetterRecipes.com Community Favorites";
		document.getElementById('from_email_id').value = 5169791;
		document.getElementById('reply_email_id').value = 5173458;
	}
}
</script>
</head>
<body>
<center>
<h3>Create Newsletter</h3>
<font color="red"><?php echo $error; ?></font>
</center>
<form name='form1' action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" value="<?php echo $iId; ?>" name="iId">
<table cellpadding="5" cellspacing="5" align="center">
	<tr>
		<td><b>Newsletter Subject Line</b>:</td>
		<td><input type="text" maxlength="255" size="50" name="subject" id="subject" value="<?php echo $subject; ?>"></td>
	</tr>
	<tr>
		<td><b>Mailing Date</b>:</td>
		<td><input type="text" maxlength="10" size="50" name="mailing_date" id="mailing_date" value="<?php echo $mailing_date; ?>"> (YYYY-MM-DD)</td>
	</tr>
	<tr>
		<td><b>Template</b>:</td>
		<td><select name="template" id="template" onchange="populateFromName(this.value);">
		<?php echo $template_options; ?>
		</select><font size="1"><br>(selecting Template option will auto populate Default From Name and then you can change it if you like)</font>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" style="color:red;">Below two fields are required for Campaigner</td>
	</tr>
	<tr>
		<td><b>Campaign Name</b>:</td>
		<td><input type="text" maxlength="255" size="50" name="campaign_name" id="campaign_name" value="<?php echo $campaign_name; ?>"></td>
	</tr>
	<tr>
		<td><b>From Name</b>:</td>
		<td><input type="text" maxlength="255" size="50" name="from_name" id="from_name" value="<?php echo $from_name; ?>"></td>
	</tr>
	<tr>
		<td><b>From Email</b>:</td>
		<td><!-- from_email_id value must be obtained from Campaigner -->
			<select name="from_email_id" id="from_email_id">
				<option value="5080120" <?php if ($from_email_id == '5080120') { echo 'selected'; } ?>>R4L@recipe4living-recipes.com</option>
				<option value="5080118" <?php if ($from_email_id == '5080118') { echo 'selected'; } ?>>email@workitmom-newsletter.com</option>
				<option value="5080119" <?php if ($from_email_id == '5080119') { echo 'selected'; } ?>>email@fitandfabliving-newsletter.com</option>
                                <option value="5169791" <?php if ($from_email_id == '5169791') { echo 'selected'; } ?>>betterrecipes@email.betterrecipes.com</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><b>Reply Email</b>:</td>
		<td><!-- reply_email_id value must be obtained from Campaigner -->
			<select name="reply_email_id" id="reply_email_id">
				<option value="5080120" <?php if ($reply_email_id == '5080120') { echo 'selected'; } ?>>R4L@recipe4living-recipes.com</option>
				<option value="5080118" <?php if ($reply_email_id == '5080118') { echo 'selected'; } ?>>email@workitmom-newsletter.com</option>
				<option value="5080119" <?php if ($reply_email_id == '5080119') { echo 'selected'; } ?>>email@fitandfabliving-newsletter.com</option>
                                <option value="5173458" <?php if ($reply_email_id == '5173458') { echo 'selected'; } ?>>betterrecipes@junemedia.com</option>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<table style="border: 1px solid #383838;">
				<tr>
					<td colspan="2" align="center"><b>Below Fields Are For R4L Newsletters Only</b></td>
				</tr>
				<tr>
					<td><b>Add To Newsletter Archive System:</b>:</td>
					<td><input type="radio" name="enable" id="enableY" value="Y" <?php if ($enable == 'Y') { echo 'checked'; }?>> Yes
					<input type="radio" name="enable" id="enableN" value="N" <?php if ($enable == '' || $enable == 'N') { echo 'checked'; }?>> No</td>
				</tr>
				<tr>
					<td><b>Meta Keywords</b>:</td>
					<td><input type="text" maxlength="255" size="50" name="meta_keywords" id="meta_keywords" value="<?php echo $meta_keywords; ?>"></td>
				</tr>
				<tr>
					<td><b>Meta Desc</b>:</td>
					<td><input type="text" maxlength="255" size="50" name="meta_desc" id="meta_desc" value="<?php echo $meta_desc; ?>"></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Add/Update" onclick="return check_fields();"></td>
	</tr>
</table>
</form>
* Once newsletter is created, you cannot delete it.
<?php echo $refresh; ?>
</body>
</html>

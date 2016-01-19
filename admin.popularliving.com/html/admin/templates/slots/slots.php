<?php

include("../../../includes/paths.php");

session_start();

$sPageTitle = "Slots Templates";

mysql_select_db('newsletter_templates');

if ($sSave1 || $sSave2 || $sSave3 || $sSave4 || $sSave5 || $sSave6 || $sSave7 || $sSave8) {
	if ($title == '' || $name == '') {
		$sMessage = "Name or newsletter missing.";
	} else {
		$slot1 = addslashes($slot1);
		$slot2 = addslashes($slot2);
		$slot3 = addslashes($slot3);
		$slot4 = addslashes($slot4);
		$slot5 = addslashes($slot5);
		$slot6 = addslashes($slot6);
		$slot7 = addslashes($slot7);
		$slot8 = addslashes($slot8);
		
		$mailing_date = addslashes(trim($mailing_date));
		$subject = addslashes(trim($subject));
		$enable = addslashes($enable);
		
		$keywords = addslashes($keywords);
		$description = addslashes($description);
		
		$name = date('Y-m-d H:i:s') . "_" . $name . "_". $_SERVER['PHP_AUTH_USER'];
		$name = addslashes($name);
		
		if ($iId) {
			$record_query = "UPDATE slots SET 
					slot1 = \"$slot1\", 
					slot2 = \"$slot2\", 
					slot3 = \"$slot3\", 
					slot4 = \"$slot4\", 
					slot5 = \"$slot5\", 
					slot6 = \"$slot6\", 
					slot7 = \"$slot7\", 
					slot8 = \"$slot8\", 
					title = \"$title\", 
					mailing_date = \"$mailing_date\", 
					subject = \"$subject\", 
					enable = \"$enable\", 
					keywords = \"$keywords\", 
					description = \"$description\" 
				WHERE id = '$iId'";
			$record_result = dbQuery($record_query);
			echo mysql_error();
			$sMessage = "Slots updated successfully.";
		} else {
			$record_query = "INSERT INTO slots (name,slot1,slot2,slot3,slot4,slot5,slot6,slot7,slot8,title,mailing_date,subject,enable,keywords,description)
				VALUES (\"$name\",\"$slot1\",\"$slot2\",\"$slot3\",\"$slot4\",\"$slot5\",\"$slot6\",\"$slot7\",\"$slot8\",\"$title\",\"$mailing_date\",\"$subject\",\"$enable\",\"$keywords\",\"$description\")";
			$record_result = dbQuery($record_query);
			$iId = mysql_insert_id();
			echo mysql_error();
			$sMessage = "Slots recorded successfully.";
		}
	}
}

if ($iId) {
	$rContentResult = dbQuery("SELECT * FROM slots WHERE id = '$iId'");
	while ($sRow = dbFetchObject($rContentResult)) {
		$name = stripslashes($sRow->name);
		$title = stripslashes($sRow->title);
		$slot1 = stripslashes($sRow->slot1);
		$slot2 = stripslashes($sRow->slot2);
		$slot3 = stripslashes($sRow->slot3);
		$slot4 = stripslashes($sRow->slot4);
		$slot5 = stripslashes($sRow->slot5);
		$slot6 = stripslashes($sRow->slot6);
		$slot7 = stripslashes($sRow->slot7);
		$slot8 = stripslashes($sRow->slot8);
		
		$mailing_date = stripslashes($sRow->mailing_date);
		$subject = stripslashes($sRow->subject);
		$enable = stripslashes($sRow->enable);
		
		$keywords = stripslashes($sRow->keywords);
		$description = stripslashes($sRow->description);
	}
	$readonly = 'readonly';
} else {
	$readonly = '';
	$mailing_date = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
}

// include prefill only if slot(s) is new and prefill requested and nl is provided.
if ($iId == '' && $prefill == 'Y' && $title !='') {
	include_once('prefill.php');
}


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../../includes/adminHeader.php");

?>


<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan="2"><font color="Blue" size="5">* </font>Do <b>NOT</b> use double quote (") in these fields. If you have to use it, then please let Samir know.</td></tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
	<td align=left valign=top colspan="2">
		<font color="Blue" size="5">* </font>Name:&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="text" name="name" id="name" value="<?php echo $name; ?>" maxlength="100" size="70" <?php echo $readonly; ?>>
	</td>
</tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
	<td align=left valign=top colspan="2">
		<font color="Blue" size="5">* </font>Newsletter Subject Line:&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="text" name="subject" id="subject" value="<?php echo $subject; ?>" maxlength="255" size="70">
		&nbsp;&nbsp;<font color="Red" style="font-weight:bold;">&lt;&lt;-- This field is OPTIONAL for R4L SOLO and F&F SOLO and JuneExchange</font>
	</td>
</tr>

<tr>
	<td align=left valign=top colspan="2">
		Mailing Date:&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="text" name="mailing_date" id="mailing_date" value="<?php echo $mailing_date; ?>" maxlength="10" size="15"> [YYYY-MM-DD]
	</td>
</tr>

<tr>
	<td align=left valign=top colspan="2">
		Enable This Issue for Newsletter Archive System: &nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" id="enable" name="enable" value="Y" <?php if ($enable == 'Y') { echo ' checked '; }?>>
		&nbsp;&nbsp;<font color="Red" style="font-weight:bold;">&lt;&lt;-- Do <b>NOT</b> enable if this is SOLO (R4L, WIM, FF). Do NOT check if this is JuneExchange</font>
	</td>
</tr>



<tr>
	<td align=left valign=top colspan="2">
		<font color="Blue" size="5">* </font>Keywords:&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="text" name="keywords" id="keywords" value="<?php echo $keywords; ?>" size="70" maxlength="255">
		&nbsp;&nbsp;<font color="Red" style="font-weight:bold;">&lt;&lt;-- This field is OPTIONAL for R4L SOLO and F&F SOLO and JuneExchange</font>
	</td>
</tr>


<tr>
	<td align=left valign=top colspan="2">
		<font color="Blue" size="5">* </font>Description:&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="text" name="description" id="description" value="<?php echo $description; ?>" size="70" maxlength="255">
		&nbsp;&nbsp;<font color="Red" style="font-weight:bold;">&lt;&lt;-- This field is OPTIONAL for R4L SOLO and F&F SOLO and JuneExchange</font>
	</td>
</tr>

<tr><td colspan="2">&nbsp;</td></tr>

<tr>
	<td align=left valign=top colspan="2">
		Newsletter:<br>
		<b>F&F: </b>
		<input type="radio" id="title" name="title" value="FF_Solo" <?php if ($title == 'FF_Solo' ) { echo 'checked'; } ?>>FF_Solo
		<input type="radio" id="title" name="title" value="FF_FitnessInsider" <?php if ($title == 'FF_FitnessInsider' ) { echo 'checked'; } ?>>FF_FitnessInsider
		<input type="radio" id="title" name="title" value="FF_BeautyInsider" <?php if ($title == 'FF_BeautyInsider' ) { echo 'checked'; } ?>>FF_BeautyInsider
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" id="title" name="title" value="FF_FitFabLiving" <?php if ($title == 'FF_FitFabLiving' ) { echo 'checked'; } ?>>FF_FitFabLiving
		<input type="radio" id="title" name="title" value="FF_DietInsider" <?php if ($title == 'FF_DietInsider' ) { echo 'checked'; } ?>>FF_DietInsider
		<input type="radio" id="title" name="title" value="FF_DailyInsider" <?php if ($title == 'FF_DailyInsider' ) { echo 'checked'; } ?>>FF_DailyInsider (New F&F Template)
		<!--<br><br>
		<input type="radio" id="title" name="title" value="FF_FitnessInsider_ver2" <?php if ($title == 'FF_FitnessInsider_ver2' ) { echo 'checked'; } ?>>FF_FitnessInsider_ver2
		<input type="radio" id="title" name="title" value="FF_BeautyInsider_ver2" <?php if ($title == 'FF_BeautyInsider_ver2' ) { echo 'checked'; } ?>>FF_BeautyInsider_ver2
		<input type="radio" id="title" name="title" value="FF_FitFabLiving_ver2" <?php if ($title == 'FF_FitFabLiving_ver2' ) { echo 'checked'; } ?>>FF_FitFabLiving_ver2
		<input type="radio" id="title" name="title" value="FF_DietInsider_ver2" <?php if ($title == 'FF_DietInsider_ver2' ) { echo 'checked'; } ?>>FF_DietInsider_ver2-->
		<br><br>
		<b>R4L: </b>
		<input type="radio" id="title" name="title" value="R4L_Recipe4Living" <?php if ($title == 'R4L_Recipe4Living' ) { echo 'checked'; } ?>>R4L_Recipe4Living
		<input type="radio" id="title" name="title" value="R4L_BudgetCooking" <?php if ($title == 'R4L_BudgetCooking' ) { echo 'checked'; } ?>>R4L_BudgetCooking
		<input type="radio" id="title" name="title" value="R4L_QuickEasy" <?php if ($title == 'R4L_QuickEasy' ) { echo 'checked'; } ?>>R4L_QuickEasy
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" id="title" name="title" value="R4L_PartyRecipesTip" <?php if ($title == 'R4L_PartyRecipesTip' ) { echo 'checked'; } ?>>R4L_PartyRecipesTip
		<input type="radio" id="title" name="title" value="R4L_Solo" <?php if ($title == 'R4L_Solo' ) { echo 'checked'; } ?>>R4L_Solo
		<input type="radio" id="title" name="title" value="R4L_Crockpot" <?php if ($title == 'R4L_Crockpot' ) { echo 'checked'; } ?>>R4L_Crockpot
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" id="title" name="title" value="R4L_Casserole" <?php if ($title == 'R4L_Casserole' ) { echo 'checked'; } ?>>R4L_Casserole
		<input type="radio" id="title" name="title" value="R4L_Copycat" <?php if ($title == 'R4L_Copycat' ) { echo 'checked'; } ?>>R4L_Copycat
		<input type="radio" id="title" name="title" value="R4L_Diabetic" <?php if ($title == 'R4L_Diabetic' ) { echo 'checked'; } ?>>R4L_Diabetic
		<br><br>
		<b>WIM: </b>
		<input type="radio" id="title" name="title" value="WIM_MakingItWork" <?php if ($title == 'WIM_MakingItWork' ) { echo 'checked'; } ?>>WIM_MakingItWork
		<input type="radio" id="title" name="title" value="WIM_Solo" <?php if ($title == 'WIM_Solo' ) { echo 'checked'; } ?>>WIM_Solo
		<br><br><br>
		<b>R4L v2: </b>
		<input type="radio" id="title" name="title" value="R4L_DailyRecipes" <?php if ($title == 'R4L_DailyRecipes' ) { echo 'checked'; } ?>>R4L_DailyRecipes
		<input type="radio" id="title" name="title" value="R4L_BudgetCooking2" <?php if ($title == 'R4L_BudgetCooking2' ) { echo 'checked'; } ?>>R4L_BudgetCooking2
		<input type="radio" id="title" name="title" value="R4L_Casserole2" <?php if ($title == 'R4L_Casserole2' ) { echo 'checked'; } ?>>R4L_Casserole2
		<input type="radio" id="title" name="title" value="R4L_Copycat2" <?php if ($title == 'R4L_Copycat2' ) { echo 'checked'; } ?>>R4L_Copycat2
		<input type="radio" id="title" name="title" value="R4L_Crockpot2" <?php if ($title == 'R4L_Crockpot2' ) { echo 'checked'; } ?>>R4L_Crockpot2
		<input type="radio" id="title" name="title" value="R4L_QuickEasy2" <?php if ($title == 'R4L_QuickEasy2' ) { echo 'checked'; } ?>>R4L_QuickEasy2
		<input type="radio" id="title" name="title" value="R4L_Diabetic2" <?php if ($title == 'R4L_Diabetic2' ) { echo 'checked'; } ?>>R4L_Diabetic2
		<br><br>
		<b>F&F v2: </b>
		<input type="radio" id="title" name="title" value="FF_DailyInsider2" <?php if ($title == 'FF_DailyInsider2' ) { echo 'checked'; } ?>>FF_DailyInsider2
		<input type="radio" id="title" name="title" value="FF_DietInsider2" <?php if ($title == 'FF_DietInsider2' ) { echo 'checked'; } ?>>FF_DietInsider2
		<br><br>
		<b>WIM v2: </b>
		<input type="radio" id="title" name="title" value="WIM_MakingItWork2" <?php if ($title == 'WIM_MakingItWork2' ) { echo 'checked'; } ?>>WIM_MakingItWork2
		<br><br>
		<b>SF: </b>
		<input type="radio" id="title" name="title" value="SF_Feed" <?php if ($title == 'SF_Feed' ) { echo 'checked'; } ?>>SF_Feed
		<br><br>
		<b>JE: </b>
		<input type="radio" id="title" name="title" value="JE_JuneExchange" <?php if ($title == 'JE_JuneExchange' ) { echo 'checked'; } ?>>JE_JuneExchange
	</td>
</tr>




<tr>
	<td align=left valign=top>
		Slot 1:<br>
		<textarea name=slot1 rows=15 cols=55><?php echo $slot1;?></textarea>
		<br><input type=submit name='sSave1' value='Save'>
	</td>
	<td align=left valign=top>
	Slot 2:<br>
		<textarea name=slot2 rows=15 cols=55><?php echo $slot2;?></textarea>
		<br><input type=submit name='sSave2' value='Save'>
	</td>
</tr>
<tr>
	<td align=left valign=top>
	Slot 3:<br>
		<textarea name=slot3 rows=15 cols=55><?php echo $slot3;?></textarea>
		<br><input type=submit name='sSave3' value='Save'>
	</td>
	<td align=left valign=top>
	Slot 4:<br>
		<textarea name=slot4 rows=15 cols=55><?php echo $slot4;?></textarea>
		<br><input type=submit name='sSave4' value='Save'>
	</td>
</tr>
<tr>
	<td align=left valign=top>
	Slot 5:<br>
		<textarea name=slot5 rows=15 cols=55><?php echo $slot5;?></textarea>
		<br><input type=submit name='sSave5' value='Save'>
	</td>
	<td align=left valign=top>
	Slot 6:<br>
		<textarea name=slot6 rows=15 cols=55><?php echo $slot6;?></textarea>
		<br><input type=submit name='sSave6' value='Save'>
	</td>
</tr>
<tr>
	<td align=left valign=top>
	Slot 7:<br>
		<textarea name=slot7 rows=15 cols=55><?php echo $slot7;?></textarea>
		<br><input type=submit name='sSave7' value='Save'>
	</td>
	<td align=left valign=top>
	Slot 8:<br>
		<textarea name=slot8 rows=15 cols=55><?php echo $slot8;?></textarea>
		<br><input type=submit name='sSave8' value='Save'>
	</td>
</tr>
</table>
</form>

<?php include("../../../includes/adminFooter.php"); ?>

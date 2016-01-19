<?php

include("../../includes/paths.php");

session_start();

mysql_select_db('newsletter_templates');

$final_code = '';
$header_code = '';
$footer_code = '';
$body_code = '';

if ($submit == 'Submit') {
	$sSelectQuery = "SELECT content FROM header WHERE id='$header' LIMIT 1";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$header_code = "<!-- START OF HEADER CONTENT -->" . $oRow->content . "<!-- END OF HEADER CONTENT -->";
	}
	
	
	$sSelectQuery = "SELECT content FROM footer WHERE id='$footer' LIMIT 1";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$footer_code = "<!-- START OF FOOTER CONTENT -->" . $oRow->content . "<!-- END OF FOOTER CONTENT -->";
	}
	
	
	$sSelectQuery = "SELECT content FROM body WHERE id='$body' LIMIT 1";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$body_code = "<!-- START OF BODY CONTENT -->" . $oRow->content . "<!-- END OF BODY CONTENT -->";
	}
	

	$sSelectQuery = "SELECT * FROM slots WHERE id='$slots' LIMIT 1";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$slot_1 = "<!-- EDITORS INPUT - START OF SLOT 1 -->".$oRow->slot1."<!-- EDITORS INPUT - END OF SLOT 1 -->";
		$slot_2 = "<!-- EDITORS INPUT - START OF SLOT 2 -->".$oRow->slot2."<!-- EDITORS INPUT - END OF SLOT 2 -->";
		$slot_3 = "<!-- EDITORS INPUT - START OF SLOT 3 -->".$oRow->slot3."<!-- EDITORS INPUT - END OF SLOT 3 -->";
		$slot_4 = "<!-- EDITORS INPUT - START OF SLOT 4 -->".$oRow->slot4."<!-- EDITORS INPUT - END OF SLOT 4 -->";
		$slot_5 = "<!-- EDITORS INPUT - START OF SLOT 5 -->".$oRow->slot5."<!-- EDITORS INPUT - END OF SLOT 5 -->";
		$slot_6 = "<!-- EDITORS INPUT - START OF SLOT 6 -->".$oRow->slot6."<!-- EDITORS INPUT - END OF SLOT 6 -->";
		$slot_7 = "<!-- EDITORS INPUT - START OF SLOT 7 -->".$oRow->slot7."<!-- EDITORS INPUT - END OF SLOT 7 -->";
		$slot_8 = "<!-- EDITORS INPUT - START OF SLOT 8 -->".$oRow->slot8."<!-- EDITORS INPUT - END OF SLOT 8 -->";
		$nl_name = $oRow->name;
		
		$header_code = str_replace("[ISSUE_DATE]", date('M d, Y', strtotime($oRow->mailing_date)), $header_code);
	}
	
	$body_code = str_replace("[SLOT_1]", $slot_1, $body_code);
	$body_code = str_replace("[SLOT_2]", $slot_2, $body_code);
	$body_code = str_replace("[SLOT_3]", $slot_3, $body_code);
	$body_code = str_replace("[SLOT_4]", $slot_4, $body_code);
	$body_code = str_replace("[SLOT_5]", $slot_5, $body_code);
	$body_code = str_replace("[SLOT_6]", $slot_6, $body_code);
	$body_code = str_replace("[SLOT_7]", $slot_7, $body_code);
	$body_code = str_replace("[SLOT_8]", $slot_8, $body_code);
	
	$sSelectQuery = "SELECT * FROM ads";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$temp_title = $oRow->title;
		$$temp_title = $oRow->content;
	}
	
	
	$body_code = str_replace("[FF_FitnessInsider_ver2_300x250_BANNER]", "<!-- START OF ADS -->" .$FF_FitnessInsider_ver2_300x250_BANNER. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_BeautyInsider_ver2_300x250_BANNER]", "<!-- START OF ADS -->" .$FF_BeautyInsider_ver2_300x250_BANNER. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_DietInsider_ver2_300x250_BANNER]", "<!-- START OF ADS -->" .$FF_DietInsider_ver2_300x250_BANNER. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_FitFabLiving_ver2_300x250_BANNER]", "<!-- START OF ADS -->" .$FF_FitFabLiving_ver2_300x250_BANNER. "<!-- END OF ADS -->", $body_code);
	
	
	$body_code = str_replace("[FF_FitnessInsider_Above_Footer]", "<!-- START OF ADS -->" .$FF_FitnessInsider_Above_Footer. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_BeautyInsider_Above_Footer]", "<!-- START OF ADS -->" .$FF_BeautyInsider_Above_Footer. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_DietInsider_Above_Footer]", "<!-- START OF ADS -->" .$FF_DietInsider_Above_Footer. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_FitFabLiving_Above_Footer]", "<!-- START OF ADS -->" .$FF_FitFabLiving_Above_Footer. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_Solo_Ad]", "<!-- START OF ADS -->" .$FF_Solo_Ad. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Solo_Ad]", "<!-- START OF ADS -->" .$R4L_Solo_Ad. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_PartyRecipesTip_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_QuickEasy_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_QuickEasy_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Recipe4Living_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_Recipe4Living_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_BudgetCooking_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_BudgetCooking_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_PartyRecipesTip_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_QuickEasy_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_QuickEasy_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Recipe4Living_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_Recipe4Living_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_BudgetCooking_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_BudgetCooking_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_PartyRecipesTip_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_QuickEasy_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_QuickEasy_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Recipe4Living_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_Recipe4Living_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_BudgetCooking_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_BudgetCooking_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_PartyRecipesTip_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_QuickEasy_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_QuickEasy_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Recipe4Living_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_Recipe4Living_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_BudgetCooking_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_BudgetCooking_After_Slot_8. "<!-- END OF ADS -->", $body_code);

	$body_code = str_replace("[R4L_Crockpot_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_Crockpot_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Crockpot_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_Crockpot_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Crockpot_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_Crockpot_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Crockpot_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_Crockpot_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	
	$body_code = str_replace("[R4L_Casserole_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_Casserole_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Casserole_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_Casserole_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Casserole_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_Casserole_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Casserole_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_Casserole_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	
	
	$body_code = str_replace("[R4L_Copycat_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_Copycat_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Copycat_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_Copycat_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Copycat_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_Copycat_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Copycat_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_Copycat_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	
	$body_code = str_replace("[FF_DailyInsider_After_Slot_1]", "<!-- START OF ADS -->" .$FF_DailyInsider_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_DailyInsider_After_Slot_5]", "<!-- START OF ADS -->" .$FF_DailyInsider_After_Slot_5. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[FF_DailyInsider_After_Slot_6]", "<!-- START OF ADS -->" .$FF_DailyInsider_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	
	
	$body_code = str_replace("[WIM_MakingItWork_After_Slot_5]", "<!-- START OF ADS -->" .$WIM_MakingItWork_After_Slot_5. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[WIM_MakingItWork_After_Slot_6]", "<!-- START OF ADS -->" .$WIM_MakingItWork_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[WIM_Solo_Ad]", "<!-- START OF ADS -->" .$WIM_Solo_Ad. "<!-- END OF ADS -->", $body_code);
	
	$body_code = str_replace("[R4L_Diabetic_After_Slot_1]", "<!-- START OF ADS -->" .$R4L_Diabetic_After_Slot_1. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Diabetic_After_Slot_3]", "<!-- START OF ADS -->" .$R4L_Diabetic_After_Slot_3. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Diabetic_After_Slot_6]", "<!-- START OF ADS -->" .$R4L_Diabetic_After_Slot_6. "<!-- END OF ADS -->", $body_code);
	$body_code = str_replace("[R4L_Diabetic_After_Slot_8]", "<!-- START OF ADS -->" .$R4L_Diabetic_After_Slot_8. "<!-- END OF ADS -->", $body_code);
	
	
	$final_code = $header_code . $body_code . $footer_code;
	
	
	$final_code = str_replace("[R4L_DailyRecipes_Right_1]", "<!-- START OF ADS -->" .$R4L_DailyRecipes_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_DailyRecipes_Right_2]", "<!-- START OF ADS -->" .$R4L_DailyRecipes_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_DailyRecipes_Footer]", "<!-- START OF ADS -->" .$R4L_DailyRecipes_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_BudgetCooking2_Right_1]", "<!-- START OF ADS -->" .$R4L_BudgetCooking2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_BudgetCooking2_Right_2]", "<!-- START OF ADS -->" .$R4L_BudgetCooking2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_BudgetCooking2_Footer]", "<!-- START OF ADS -->" .$R4L_BudgetCooking2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_Casserole2_Right_1]", "<!-- START OF ADS -->" .$R4L_Casserole2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Casserole2_Right_2]", "<!-- START OF ADS -->" .$R4L_Casserole2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Casserole2_Footer]", "<!-- START OF ADS -->" .$R4L_Casserole2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_Copycat2_Right_1]", "<!-- START OF ADS -->" .$R4L_Copycat2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Copycat2_Right_2]", "<!-- START OF ADS -->" .$R4L_Copycat2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Copycat2_Footer]", "<!-- START OF ADS -->" .$R4L_Copycat2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_Crockpot2_Right_1]", "<!-- START OF ADS -->" .$R4L_Crockpot2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Crockpot2_Right_2]", "<!-- START OF ADS -->" .$R4L_Crockpot2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Crockpot2_Footer]", "<!-- START OF ADS -->" .$R4L_Crockpot2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_QuickEasy2_Right_1]", "<!-- START OF ADS -->" .$R4L_QuickEasy2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_QuickEasy2_Right_2]", "<!-- START OF ADS -->" .$R4L_QuickEasy2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_QuickEasy2_Footer]", "<!-- START OF ADS -->" .$R4L_QuickEasy2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[R4L_Diabetic2_Right_1]", "<!-- START OF ADS -->" .$R4L_Diabetic2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Diabetic2_Right_2]", "<!-- START OF ADS -->" .$R4L_Diabetic2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[R4L_Diabetic2_Footer]", "<!-- START OF ADS -->" .$R4L_Diabetic2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[FF_DailyInsider2_Right_1]", "<!-- START OF ADS -->" .$FF_DailyInsider2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[FF_DailyInsider2_Right_2]", "<!-- START OF ADS -->" .$FF_DailyInsider2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[FF_DailyInsider2_Footer]", "<!-- START OF ADS -->" .$FF_DailyInsider2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[FF_DietInsider2_Right_1]", "<!-- START OF ADS -->" .$FF_DietInsider2_Right_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[FF_DietInsider2_Right_2]", "<!-- START OF ADS -->" .$FF_DietInsider2_Right_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[FF_DietInsider2_Footer]", "<!-- START OF ADS -->" .$FF_DietInsider2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[WIM_MakingItWork2_Left_1]", "<!-- START OF ADS -->" .$WIM_MakingItWork2_Left_1. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[WIM_MakingItWork2_Left_2]", "<!-- START OF ADS -->" .$WIM_MakingItWork2_Left_2. "<!-- END OF ADS -->", $final_code);
	$final_code = str_replace("[WIM_MakingItWork2_Footer]", "<!-- START OF ADS -->" .$WIM_MakingItWork2_Footer. "<!-- END OF ADS -->", $final_code);
	
	$final_code = str_replace("[SF_Feed_Footer]", "<!-- START OF ADS -->" .$SF_Feed_Footer. "<!-- END OF ADS -->", $final_code);
	
	// ************************************************
	//
	// NO ADS FOR JUNE EXCHANGE NEWSLETTER
	//
	// ************************************************
	

	$final_code = addslashes($final_code);
	$user = $_SERVER['PHP_AUTH_USER'];
	$insert = "INSERT INTO templates (content, dateTimeAdded, username, name)
					VALUES (\"$final_code\", NOW(), \"$user\", \"$nl_name\")";
	$insert_result = dbQuery($insert);
	echo mysql_error();
	
	$url = 'http://admin.popularliving.com/admin/templates/preview.php';
	$sMessage = "<font color='Red'>Template Saved: </font><a href='$url?id=".mysql_insert_id()."' target=_blank>Preview Link</a>";
}



$header_option = '';
$sSelectQuery = "SELECT * FROM header ORDER BY title ASC";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($oRow->id == $header) {
		$selected = 'selected ';
	} else {
		$selected = '';
	}
	$header_option .= "<option value='$oRow->id' $selected>$oRow->title</option>";
}



$footer_option = '';
$sSelectQuery = "SELECT * FROM footer ORDER BY title ASC";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($oRow->id == $footer) {
		$selected = 'selected ';
	} else {
		$selected = '';
	}
	$footer_option .= "<option value='$oRow->id' $selected>$oRow->title</option>";
}

$body_option = '';
$sSelectQuery = "SELECT * FROM body ORDER BY title ASC";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($oRow->id == $body) {
		$selected = 'selected ';
	} else {
		$selected = '';
	}
	$body_option .= "<option value='$oRow->id' $selected>$oRow->title (# of slots: $oRow->slots)</option>";
}




$slots_option = '';
$sSelectQuery = "SELECT * FROM slots ORDER BY id DESC LIMIT 150";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($oRow->id == $slots) {
		$selected = 'selected ';
	} else {
		$selected = '';
	}
	$slots_option .= "<option value='$oRow->id' $selected>$oRow->name ($oRow->title)  {$oRow->subject}</option>";
}



include("../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $_SERVER['PHP_SELF']; ?>' method="POST">

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=75% align="center">

<script>
function change_header_body_footer (val) {
	//alert(val);
	document.form1.header.value = val;
	document.form1.body.value = val;
	document.form1.footer.value = val;
}
</script>


<tr>
	<td class='header' colspan="2">
		Main Template:
		<select name="header_body_footer" id="header_body_footer" onchange="change_header_body_footer(this.value);" onkeyup="change_header_body_footer(this.value);">
			<option value="" <?php if ($header_body_footer == '') { echo 'selected'; } ?>></option>
			<option value="27" <?php if ($header_body_footer == '27') { echo 'selected'; } ?>>FF_DailyInsider</option>
			<option value="3" <?php if ($header_body_footer == '3') { echo 'selected'; } ?>>FF_BeautyInsider</option>
			<!--<option value="13" <?php if ($header_body_footer == '13') { echo 'selected'; } ?>>FF_BeautyInsider_ver2</option>-->
			<option value="11" <?php if ($header_body_footer == '11') { echo 'selected'; } ?>>FF_DietInsider</option>
			<!--<option value="15" <?php if ($header_body_footer == '15') { echo 'selected'; } ?>>FF_DietInsider_ver2</option>-->
			<option value="4" <?php if ($header_body_footer == '4') { echo 'selected'; } ?>>FF_FitFabLiving</option>
			<!--<option value="17" <?php if ($header_body_footer == '17') { echo 'selected'; } ?>>FF_FitFabLiving_ver2</option>-->
			<option value="2" <?php if ($header_body_footer == '2') { echo 'selected'; } ?>>FF_FitnessInsider</option>
			<!--<option value="19" <?php if ($header_body_footer == '19') { echo 'selected'; } ?>>FF_FitnessInsider_ver2</option>-->
			<option value="1" <?php if ($header_body_footer == '1') { echo 'selected'; } ?>>FF_Solo</option>
			<option value="" disabled>---------------------</option>
			<option value="55" <?php if ($header_body_footer == '55') { echo 'selected'; } ?>>FF_DailyInsider2</option>
			<option value="57" <?php if ($header_body_footer == '57') { echo 'selected'; } ?>>FF_DietInsider2</option>
			<option value="" disabled>---------------------</option>
			<option value="6" <?php if ($header_body_footer == '6') { echo 'selected'; } ?>>R4L_BudgetCooking</option>
			<option value="8" <?php if ($header_body_footer == '8') { echo 'selected'; } ?>>R4L_PartyRecipesTip</option>
			<option value="7" <?php if ($header_body_footer == '7') { echo 'selected'; } ?>>R4L_QuickEasy</option>
			<option value="5" <?php if ($header_body_footer == '5') { echo 'selected'; } ?>>R4L_Recipe4Living</option>
			<option value="10" <?php if ($header_body_footer == '10') { echo 'selected'; } ?>>R4L_Solo</option>
			<option value="21" <?php if ($header_body_footer == '21') { echo 'selected'; } ?>>R4L_Crockpot</option>
			<option value="23" <?php if ($header_body_footer == '23') { echo 'selected'; } ?>>R4L_Casserole</option>
			<option value="25" <?php if ($header_body_footer == '25') { echo 'selected'; } ?>>R4L_Copycat</option>
			<option value="33" <?php if ($header_body_footer == '33') { echo 'selected'; } ?>>R4L_Diabetic</option>
			<option value="" disabled>---------------------</option>
			<option value="35" <?php if ($header_body_footer == '35') { echo 'selected'; } ?>>R4L_DailyRecipes</option>
			<option value="43" <?php if ($header_body_footer == '43') { echo 'selected'; } ?>>R4L_BudgetCooking2</option>
			<option value="45" <?php if ($header_body_footer == '45') { echo 'selected'; } ?>>R4L_Casserole2</option>
			<option value="47" <?php if ($header_body_footer == '47') { echo 'selected'; } ?>>R4L_Copycat2</option>
			<option value="49" <?php if ($header_body_footer == '49') { echo 'selected'; } ?>>R4L_Crockpot2</option>
			<option value="51" <?php if ($header_body_footer == '51') { echo 'selected'; } ?>>R4L_QuickEasy2</option>
			<option value="53" <?php if ($header_body_footer == '53') { echo 'selected'; } ?>>R4L_Diabetic2</option>
			<option value="" disabled>---------------------</option>
			<option value="31" <?php if ($header_body_footer == '31') { echo 'selected'; } ?>>WIM_MakingItWork</option>
			<option value="29" <?php if ($header_body_footer == '29') { echo 'selected'; } ?>>WIM_Solo</option>
			<option value="" disabled>---------------------</option>
			<option value="59" <?php if ($header_body_footer == '59') { echo 'selected'; } ?>>WIM_MakingItWork2</option>
			<option value="" disabled>---------------------</option>
			<option value="61" <?php if ($header_body_footer == '61') { echo 'selected'; } ?>>SF_Feed</option>
			<option value="" disabled>---------------------</option>
			<option value="63" <?php if ($header_body_footer == '63') { echo 'selected'; } ?>>JE_JuneExchange</option>
		</select>
	</td>
</tr>

<tr align="right">
	<td class='header' colspan="2">
		Header Template: <select name="header" id="header"><option value=""></option><?php echo $header_option; ?></select>
	</td>
</tr>
<tr align="right">
	<td class='header' colspan="2">
		Body Template: <select name="body" id="body"><option value=""></option><?php echo $body_option; ?></select>
	</td>
</tr>
<tr align="right">
	<td class='header' colspan="2">
		Footer Template: <select name="footer" id="footer"><option value=""></option><?php echo $footer_option; ?></select>
	</td>
</tr>

<tr>
	<td class='header' colspan="2">
		Slots Template: <select name="slots" id="slots"><option value=""></option><?php echo $slots_option; ?></select>
	</td>
</tr>


<tr>
	<td class='header' colspan="2" align="center">
		<input type="submit" name="submit" value="Submit">
	</td>
</tr>
</table>

</form>
<table cellpadding=5 cellspacing=0 width=75% align=center border="0">
<tr>
	<td><b>Note:</b>  <b>"Slots Template"</b> ONLY display last 150 issues.  If you need access to older issues, please contact Samir.
	
	<br><br><br>
	
	<a href='JavaScript:void(window.open("http://admin.popularliving.com/admin/templates/recent.php", "Recent", "height=400, width=700, scrollbars=yes, resizable=yes, status=no"));'><b>Recently Generated Preview Links</b></a>
	</td>
</tr>
</table>
<?php
	include("../../includes/adminFooter.php");
?>

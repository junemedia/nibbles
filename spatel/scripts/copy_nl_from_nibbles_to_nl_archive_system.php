<?php

//include_once("/home/spatel/config.php");


$nibbles_connect = mysql_pconnect ("192.168.51.33", "root", "5dsa234Y");
$nibbles_select_db = mysql_select_db ("newsletter_templates",$nibbles_connect);


$r4l_connect = mysql_pconnect ("192.168.51.65", "r4ldbuser", "acgnW3FsFSD2");
$r4l_select_db = mysql_select_db ("newsletter_archive",$r4l_connect);



$errors = "";

// THIS FUNCTION IS COPIED FROM  NEWSLETTER ARCHIVE CONFIG.PHP
function seoUrl($string) {
    //Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
    $string = strtolower($string);
    //Strip any unwanted characters
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    //Convert two -- with only 1 that was already done at top
    $string = str_replace("--", "", $string);
    $string = str_replace('"', '', $string);
    $string = str_replace("'", '', $string);
    $string = str_replace("’", '', $string);
    $string = str_replace(" ", '', $string);
    return $string;
}


$newsletters_slots_ids = '';


/*

*** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT ***

WHEN ADDING NEW NEWSLETTERS, YOU MUST UPDATE BELOW $title_to_get_code_for VARIABLE VALUES AND ALSO UPDATE SWITCH FUNCTION WITH APPROPRIATE LISTID AND LISTNAME.

ALSO UPDATE AFTER_SLOT_# BANNER CODE

*** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT *** IMPORTANT ***

*/

$title_to_get_code_for = "'R4L_Diabetic2','R4L_Copycat2','R4L_Crockpot2','R4L_QuickEasy2','R4L_BudgetCooking2','R4L_Casserole2','R4L_DailyRecipes','R4L_QuickEasy','R4L_Crockpot','R4L_BudgetCooking','R4L_PartyRecipesTip','R4L_Recipe4Living','R4L_Casserole','R4L_Copycat','R4L_Diabetic'";


$get_slots = "SELECT * FROM slots WHERE enable = 'Y' AND processed = 'N' AND mailing_date < CURRENT_DATE AND title IN ($title_to_get_code_for) ORDER BY id DESC";
$get_slots_result = mysql_query($get_slots,$nibbles_connect);
if (!$get_slots_result) {
	$errors .= __LINE__."========".$get_slots."========".mysql_error()."\n\n\n";
}
while ($slots_row = mysql_fetch_object($get_slots_result)) {
	$final_code = '';
	$header_code = '';
	$footer_code = '';
	$body_code = '';
	
	
	
	$slots_id = $slots_row->id;
	$title = $slots_row->title;
	
	$slot_1 = stripslashes($slots_row->slot1);
	$slot_2 = stripslashes($slots_row->slot2);
	$slot_3 = stripslashes($slots_row->slot3);
	$slot_4 = stripslashes($slots_row->slot4);
	$slot_5 = stripslashes($slots_row->slot5);
	$slot_6 = stripslashes($slots_row->slot6);
	$slot_7 = stripslashes($slots_row->slot7);
	$slot_8 = stripslashes($slots_row->slot8);

	
	$mailing_date = stripslashes($slots_row->mailing_date);
	$subject = stripslashes($slots_row->subject);
	$keywords = stripslashes($slots_row->keywords);
	$description = stripslashes($slots_row->description);
	
	
	
	
	$get_header = "SELECT content FROM header WHERE title='$title' LIMIT 1";
	$get_header_result = mysql_query($get_header,$nibbles_connect);
	if (!$get_header_result) {
		$errors .= __LINE__."========".$get_header."========".mysql_error()."\n\n\n";
	}
	while ($header_row = mysql_fetch_object($get_header_result)) {
		$header_code = "<!-- START OF HEADER CONTENT -->" . stripslashes($header_row->content) . "<!-- END OF HEADER CONTENT -->";
		//echo stripslashes($header_row->content);
		//exit;
	}
	
	//exit;
	
	
	
	
	$get_footer = "SELECT content FROM footer WHERE title='$title' LIMIT 1";
	$get_footer_result = mysql_query($get_footer,$nibbles_connect);
	if (!$get_footer_result) {
		$errors .= __LINE__."========".$get_footer."========".mysql_error()."\n\n\n";
	}
	while ($footer_row = mysql_fetch_object($get_footer_result)) {
		$footer_code = "<!-- START OF FOOTER CONTENT -->" . stripslashes($footer_row->content) . "<!-- END OF FOOTER CONTENT -->";
	}
	
	
	$get_body = "SELECT content FROM body WHERE title='$title' LIMIT 1";
	$get_body_result = mysql_query($get_body,$nibbles_connect);
	if (!$get_body_result) {
		$errors .= __LINE__."========".$get_body."========".mysql_error()."\n\n\n";
	}
	while ($body_row = mysql_fetch_object($get_body_result)) {
		$body_code = "<!-- START OF BODY CONTENT -->" . stripslashes($body_row->content) . "<!-- END OF BODY CONTENT -->";
	}
	
	$body_code = str_replace("[SLOT_1]", $slot_1, $body_code);
	$body_code = str_replace("[SLOT_2]", $slot_2, $body_code);
	$body_code = str_replace("[SLOT_3]", $slot_3, $body_code);
	$body_code = str_replace("[SLOT_4]", $slot_4, $body_code);
	$body_code = str_replace("[SLOT_5]", $slot_5, $body_code);
	$body_code = str_replace("[SLOT_6]", $slot_6, $body_code);
	$body_code = str_replace("[SLOT_7]", $slot_7, $body_code);
	$body_code = str_replace("[SLOT_8]", $slot_8, $body_code);
	
	
	
	$get_ads = "SELECT * FROM ads WHERE tag LIKE 'R4L_%'";
	$get_ads_result = mysql_query($get_ads,$r4l_connect);
	echo mysql_error();
	if (!$get_ads_result) {
		$errors .= __LINE__."========".$get_ads."========".mysql_error()."\n\n\n";
	}
	while ($ads_row = mysql_fetch_object($get_ads_result)) {
		$tag = stripslashes($ads_row->tag);
		$code = stripslashes($ads_row->code);
		
		if ($tag == 'R4L_NL_Archiving_TOP_300x250') {
			$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_QuickEasy_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Recipe4Living_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_BudgetCooking_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Crockpot_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Copycat_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Casserole_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Diabetic_After_Slot_6]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_DailyRecipes_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Casserole2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Copycat2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Crockpot2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_QuickEasy2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Diabetic2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_BudgetCooking2_Right_1]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
		}

		
		if ($tag == 'R4L_NL_Archiving_BOTTOM_300x250') {
			$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_QuickEasy_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Recipe4Living_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_BudgetCooking_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Crockpot_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Copycat_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Casserole_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Diabetic_After_Slot_8]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_DailyRecipes_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Casserole2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Copycat2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Crockpot2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_QuickEasy2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_Diabetic2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
			$body_code = str_replace("[R4L_BudgetCooking2_Right_2]", "<!-- START OF ADS -->" .$code. "<!-- END OF ADS -->", $body_code);
		}
		
		
		
		$body_code = str_replace("[R4L_BudgetCooking_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_BudgetCooking_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Crockpot_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Crockpot_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_PartyRecipesTip_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_QuickEasy_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_QuickEasy_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Recipe4Living_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Recipe4Living_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Copycat_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Copycat_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Casserole_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Casserole_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Diabetic_After_Slot_1]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Diabetic_After_Slot_3]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_DailyRecipes_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Casserole2_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Copycat2_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Crockpot2_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_QuickEasy2_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_Diabetic2_Footer]", "<!-- NO ADS -->", $body_code);
		$body_code = str_replace("[R4L_BudgetCooking2_Footer]", "<!-- NO ADS -->", $body_code);
	}
	
	
	$final_code = $header_code . $body_code . $footer_code;
	$final_code = str_replace("REDIR:", '', $final_code);
	
	
	//echo $final_code;
	
	// ADD SLASHES ONLY WHEN INSERTING INTO TABLE.
	$final_code = addslashes($final_code);
	

	$list_id = '';
	$list = '';
	switch ($title) {
		case "R4L_PartyRecipesTip":
	        $list_id = '392';
			$list = 'RSVP';
	        break;
	    case "R4L_BudgetCooking":
	        $list_id = '395';
			$list = 'Budget';
	        break;
	    case "R4L_Recipe4Living":
	        $list_id = '393';
			$list = 'R4L';
	        break;
	    case "R4L_DailyRecipes":
	        $list_id = '393';
			$list = 'R4L';
	        break;
        case "R4L_Crockpot":
	        $list_id = '511';
			$list = 'Crockpot';
	        break;
        case "R4L_QuickEasy":
	        $list_id = '394';
			$list = 'QE';
	        break;
	    case "R4L_Casserole":
	        $list_id = '539';
			$list = 'Casserole';
	        break;
	    case "R4L_Copycat":
	        $list_id = '554';
			$list = 'Copycat';
	        break;
	    case "R4L_Diabetic":
	        $list_id = '574';
			$list = 'Diabetic';
	        break;
	    case "R4L_BudgetCooking2":
	        $list_id = '395';
			$list = 'Budget';
	        break;
        case "R4L_Crockpot2":
	        $list_id = '511';
			$list = 'Crockpot';
	        break;
        case "R4L_QuickEasy2":
	        $list_id = '394';
			$list = 'QE';
	        break;
	    case "R4L_Casserole2":
	        $list_id = '539';
			$list = 'Casserole';
	        break;
	    case "R4L_Copycat2":
	        $list_id = '554';
			$list = 'Copycat';
	        break;
	    case "R4L_Diabetic2":
	        $list_id = '574';
			$list = 'Diabetic';
	        break;
	}
	
	
	$alias = seoUrl($subject);
	
	
	
	// WHEN INSERTING, MAKE SURE TO INSERT INTO newsletter TABLE in newsletter_archive DATABASE
	// BECAUSE ABOVE YOU SELECTED DIFFERENT DATABASE
	
	$insert_newsletter = "INSERT IGNORE INTO newsletters (newsletterDate,subject,list,html,keywords,newsletters.desc,listid,live,preview)
					VALUES (\"$mailing_date\",\"$subject\",\"$list\",\"$final_code\",\"$keywords\",\"$description\",\"$list_id\",\"Y\",\"$slots_id\")";
	$insert_result = mysql_query($insert_newsletter,$r4l_connect);
	if (!$insert_result) {
		$errors .= __LINE__."========".$insert_newsletter."========".mysql_error()."\n\n\n";
	}
	echo mysql_error();
	//echo $insert_newsletter;
	
	
	if ($insert_result) {
		// GET INSERTED ID and update alias with id- "starts with"....
		$last_insert_id = mysql_insert_id();
		$update_alias = "UPDATE newsletters SET alias = \"$last_insert_id-$alias\" WHERE id='$last_insert_id'";
		$update_alias_result = mysql_query($update_alias,$r4l_connect);
		if (!$update_alias_result) {
			$errors .= __LINE__."========".$update_alias."========".mysql_error()."\n\n\n";
		}
		echo mysql_error();
	}
	
	
	if ($insert_result && $update_alias_result) {
		// UPDATE slots table and set processed to Y for ID $slots_id
		$update_processed = "UPDATE slots SET processed = 'Y' WHERE id='$slots_id'";
		$update_processed_result = mysql_query($update_processed,$nibbles_connect);
		if (!$update_processed_result) {
			$errors .= __LINE__."========".$update_processed."========".mysql_error()."\n\n\n";
		}
		echo mysql_error();
	}
	

	echo "SLOT ID: ".$slots_id."\n\n\n<br><br>";
	$newsletters_slots_ids .= $slots_id. ', ';
	
	//exit;
}



$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

mail('samirp@junemedia.com',"Copy NLs from Nibbles to NL Archive System Report - ".date('Y-m-d'),$newsletters_slots_ids.$errors,$sHeaders);

exit;


?>

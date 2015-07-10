<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return '1'

include("/home/sites/admin.popularliving.com/html/includes/paths.php");

$campaignId = (!intval(trim($_GET['campaignId'])) ? '' : intval(trim($_GET['campaignId']))); 
$linkId = (!intval(trim($_GET['linkId'])) ? '' : intval(trim($_GET['linkId']))); 

//echo "$campaignId is campaignId, $linkId is linkId";


if (is_array($HTTP_GET_VARS)) {
	reset($HTTP_GET_VARS);
}

// Script called via AJAX, will return either a 1 (pass) or 0(fail) 

if ($campaignId != '') {
	$out = "<select name='iEmailCreativeId'>";
	//our $field is going to be which field we should try to validate
	if ($linkId != '') {
		$sEmailCapSQL = "SELECT emailCapCreative.*, linksEmailCreative.id as selected FROM emailCapCreative, campaignsEmailCreative LEFT JOIN linksEmailCreative ON (linksEmailCreative.linkId = '$linkId') WHERE campaignsEmailCreative.creativeId = emailCapCreative.id AND campaignsEmailCreative.campaignId = '$campaignId'";
	} else {
		$sEmailCapSQL = "SELECT emailCapCreative.*, '' as selected FROM emailCapCreative, campaignsEmailCreative WHERE campaignsEmailCreative.creativeId = emailCapCreative.id AND campaignsEmailCreative.campaignId = '$campaignId'";
	}

	$rEmailCap = dbQuery($sEmailCapSQL);
	while($oEmailCap = dbFetchObject($rEmailCap)) {
		if ($oEmailCap->selected == NULL) {
			$sSelected = '';
		} else {
			$sSelected = ' selected ';
		}
		$out .= "<option value='$oEmailCap->id' $sSelected>$oEmailCap->name</option>";
	}

	$out .= "</select>";
	echo $out;
}
?>

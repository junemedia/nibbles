<?php
include_once("../../includes/paths.php");

$out = '';

//there will be 2 important args: $partnerId, and $options
if(($partnerId == '') || ($options == '')){
	echo "nothing there";
	exit();
} else if($options == 'sourceCode'){
	//get all of this partner's sourceCodes
	$sourceCodeSQL = "SELECT sourceCode FROM links WHERE partnerId = '$partnerId'";
	$res = dbQuery($sourceCodeSQL);

	$out .= "<select name='sSourceCode' onChange='pixelHint(this.value);'>";
	while($data = dbFetchObject($res)){
		$out .= "<option value='$data->sourceCode' ".($value == $data->sourceCode ? 'selected' : '')." >$data->sourceCode";
	}
	$out.= "</select>";
		
	echo $out;
	exit();
} else if($options == 'campaign'){
	//get all of this partner's campaigns
	$sourceCodeSQL = "SELECT distinct links.campaignId as campaignId, campaigns.campaignName as campaignName
						FROM links, campaigns 
						WHERE links.partnerId = '$partnerId'
						AND links.campaignId = campaigns.id
						ORDER BY campaigns.campaignName ASC";
	$res = dbQuery($sourceCodeSQL);
	
	$out .= "<select name='iCampaignId'>";
	while($data = dbFetchObject($res)){
		$out .= "<option value='$data->campaignId' ".($value == $data->campaignId ? 'selected' : '').">$data->campaignName";
	}
	$out .= "</select>";
	
	echo $out;
	exit();
}


?>
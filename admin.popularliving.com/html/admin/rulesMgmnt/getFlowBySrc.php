<?php

include("../../includes/paths.php");

$iLinkId = trim($_GET['iLinkId']);
$iPgNum = trim($_GET['iPgNum']);



if ($iPgNum == 999) {
	$sSel = 'selected';
} else {
	$sSel = '';
}
$sFlowOptions = "<option value='999' $sSel> 999 &nbsp;&nbsp;&nbsp; (ALL PAGES)
				<OPTGROUP label='-------------------'>";


$sLink = "SELECT flowId FROM links WHERE id='$iLinkId'";
$rLink = mysql_query($sLink);
while ($oRow = mysql_fetch_object($rLink)) {
	$iFlowId = $oRow->flowId;
}


$sGetFlowDetails = "SELECT flowOrder,templateId FROM flowDetails 
				WHERE flowId='$iFlowId' 
				ORDER BY flowOrder ASC";
$rGetFlowResult = mysql_query($sGetFlowDetails);
while ($oRow = mysql_fetch_object($rGetFlowResult)) {
	
	$sGetTemplateType = "SELECT templateName
						FROM pageTemplates
						WHERE id = '$oRow->templateId'";
	$rTemplateType = mysql_query($sGetTemplateType);
	while ($oTemplateRow = mysql_fetch_object($rTemplateType)) {
		$sTemplateName = $oTemplateRow->templateName;
	}
	
	$iFlowOrder = $oRow->flowOrder - 1;	// the value starts with 0.
	
	if ($iPgNum == $iFlowOrder) {
		$sSel = 'selected';
	} else {
		$sSel = '';
	}
	
	$sFlowOptions .= "<option value='$iFlowOrder' $sSel>$oRow->flowOrder &nbsp;&nbsp;&nbsp; ($sTemplateName)";
}
echo $sFlowOptions;


?>


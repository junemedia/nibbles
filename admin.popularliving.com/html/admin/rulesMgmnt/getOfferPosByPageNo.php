<?php

include("../../includes/paths.php");

$iLinkId = trim($_GET['iLinkId']);
$iFlowId = trim($_GET['iFlowId']);
$iOfrPos = trim($_GET['iOfrPos']);

// increment pageNo by one drop down has starting value of 0.
$iPgNo = trim($_GET['PgNo']) + 1;
$sFlowOptions = '';

if ($iFlowId == '') {
	$sLink = "SELECT flowId FROM links WHERE id='$iLinkId'";
	$rLink = mysql_query($sLink);
	while ($oRow = mysql_fetch_object($rLink)) {
		$iFlowId = $oRow->flowId;
	}
}

$sGetFlowDetails = "SELECT maxOffers FROM flowDetails 
				WHERE flowId='$iFlowId' 
				AND flowOrder = '$iPgNo'
				ORDER BY flowOrder ASC";
$rGetFlowResult = mysql_query($sGetFlowDetails);
while ($oRow = mysql_fetch_object($rGetFlowResult)) {
	$iMaxOffers = $oRow->maxOffers;
}

for($i=1;$i<=$iMaxOffers;$i++) {
	$iPosOrder = $i - 1;	// the value starts with 0.
	
	if ($iOfrPos == $iPosOrder) {
		$sSel = 'selected';
	} else {
		$sSel = '';
	}
	
	$sFlowOptions .= "<option value='$iPosOrder' $sSel>$i";
}
echo $sFlowOptions;


?>


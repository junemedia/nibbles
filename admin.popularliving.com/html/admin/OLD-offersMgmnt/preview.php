<?php

/*********

Script to Preview Page 2 Template

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offer's Page2 Field Mappings - Add/Edit Field";

// get page2Info for the offer

if ($sPreview == 'page2') {
	$sOfferQuery = "SELECT page2Template
				   FROM   offers
				   WHERE  offerCode = '$sOfferCode'";
	$rOfferResult = dbQuery($sOfferQuery);

	while($oOfferRow = dbFetchObject($rOfferResult)) {
		$pageContent = $oOfferRow->page2Template;
	
	}
} else if ($sPreview == 'autoResp') {
	
	$sOfferQuery = "SELECT autoRespEmailBody
				   FROM   offers
				   WHERE  offerCode = '$sOfferCode'";
	$rOfferResult = dbQuery($sOfferQuery);

	while($oOfferRow = dbFetchObject($rOfferResult)) {
		
		$pageContent = $oOfferRow->autoRespEmailBody;
	
	}
} else if ($sPreview == 'addiInfo') {
	
	$sOfferQuery = "SELECT addiInfoText
				   FROM   offers
				   WHERE  offerCode = '$sOfferCode'";
	$rOfferResult = dbQuery($sOfferQuery);

	while($oOfferRow = dbFetchObject($rOfferResult)) {
		
		$pageContent = $oOfferRow->addiInfoText;
	
	}
}

if ($sPreview == 'page2') {
echo ("<form name=\"form1\">" . $pageContent . "</form>");
    } 

else {
echo $pageContent;
}
?>

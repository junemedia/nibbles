<?php

/*********

Script to Display Additional Info Of An Offer

**********/

include("includes/paths.php");

session_start();


// get addi info text
$sOfferQuery = "SELECT addiInfoTitle, addiInfoText
				FROM   offers
				WHERE  offerCode = '$sOfferCode'";
$rOfferResult = dbQuery($sOfferQuery);

while ($oOfferRow = dbFetchObject($rOfferResult)) {
	$sAddiInfoTitle = $oOfferRow->addiInfoTitle;
	$sAddiInfoText = $oOfferRow->addiInfoText;
	
}

$sPageTitle = $sAddiInfoTitle;

?><?php echo $sAddiInfoText;?>
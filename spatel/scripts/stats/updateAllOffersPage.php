<?php

// cleanup script to run daily to clean up tables deleting old records
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


$sOffersList = '';


// set all offers page's id
$iPageId = '318';

$sAllOffersQuery = "SELECT offers.*
					FROM   offers, offerCompanies
					WHERE  offers.companyId = offerCompanies.id
					AND	   mode = 'A'
					AND	   isLive = '1'
					AND	   creditStatus = 'ok'
					AND    excludeFromMasterPage != '1'";
$rAllOffersResult = dbQuery($sAllOffersQuery);
echo dbError();
while ($oAllOffersRow = dbFEtchObject($rAllOffersResult)) {
	$sOfferCode = $oAllOffersRow->offerCode;
	$sOffersList .= "'".$oAllOffersRow->offerCode."',";
	
	$sInsertQuery = "INSERT IGNORE INTO pageMap(pageId, offerCode)
					 VALUES('$iPageId', '$sOfferCode')";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();
}


// remove from all offers page if offer is not live now

if ($sOffersList != '') {
	$sOffersList = substr($sOffersList, 0, strlen($sOffersList)-1);

$sRemoveQuery = "DELETE FROM pageMap
				 WHERE  pageId = '$iPageId'
				 AND    offerCode NOT IN($sOffersList)";

$rRemoveResult = dbQuery($sRemoveQuery);
echo dbError();
}

?>

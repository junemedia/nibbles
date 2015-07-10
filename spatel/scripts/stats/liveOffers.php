<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$sOldDate = DateAdd("d", -10, date('Y')."-".date('m')."-".date('d'));


$sQuery = "SELECT offerCode FROM offers WHERE isLive='1' AND mode='A'";
$rResult = dbQuery($sQuery);
if (mysql_num_rows($rResult) > 0) {
	while ($oOffersRow = dbFetchObject($rResult)) {
		$sInsert = "INSERT IGNORE INTO liveOffers (offerCode,dateAdded)
					VALUES ('$oOffersRow->offerCode', CURRENT_DATE)";
		$rInsertResult = dbQuery($sInsert);
	}
}


$sDeleteOldRec = "DELETE FROM liveOffers WHERE dateAdded < '$sOldDate'";
$rDelResult = dbQuery($sDeleteOldRec);



?>


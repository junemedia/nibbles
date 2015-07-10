<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


$sDelete = "DELETE FROM xOutData WHERE offerCode='Array'";
$rDeleteResult = dbQuery($sDelete);
echo dbError();

$sSelectQuery = "SELECT distinct offerCode FROM xOutData";
$rSelectResult = dbQuery($sSelectQuery);
while ($sTempRow = dbFetchObject($rSelectResult)) {
	if (strlen($sTempRow->offerCode)==1) {
		$sDeleteQuery = "DELETE FROM xOutData WHERE offerCode='$sTempRow->offerCode'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}

// Start: Loop xOutData entries
$sSelectQuery = "SELECT *
                FROM   xOutData
                WHERE  dateTimeAdded < CURRENT_DATE";
$rSelectQueryResult = dbQuery($sSelectQuery);
echo dbError();
while ($oRow = dbFetchObject($rSelectQueryResult)) {
	// Insert into History Table
	$sInsertQuery = "INSERT INTO xOutDataHistory(offerCode,email,dateTimeAdded,sessionId,sourceCode)
                      VALUES(\"$oRow->offerCode\", \"$oRow->email\", \"$oRow->dateTimeAdded\", \"$oRow->sessionId\", \"$oRow->sourceCode\")";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();

	// If Insert Successful, Delete From Current table
	if ($rInsertResult) {
		$sDeleteQuery = "DELETE FROM xOutData WHERE  id = '$oRow->id'";
		$rDeleteResult = dbQuery($sDeleteQuery);
		echo dbError();
	}
}

$sDelete = "DELETE FROM xOutDataHistory WHERE offerCode='Array'";
$rDeleteResult = dbQuery($sDelete);
echo dbError();














// Start: Loop foreignIpLog entries
$sSelectQuery = "SELECT * FROM foreignIpLog WHERE dateTimeLogged < CURRENT_DATE";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {

	// Insert into History Table
	$sInsertQuery = "INSERT INTO foreignIpLogHistory
				(dateTimeLogged, remoteIp, sourceCode, subSourceCode, block, redirectUrl, country)
				VALUES(\"$oRow->dateTimeLogged\", \"$oRow->remoteIp\", \"$oRow->sourceCode\", \"$oRow->subSourceCode\", 
				\"$oRow->block\", \"$oRow->redirectUrl\", \"$oRow->country\")";
	$rInsertResult = dbQuery($sInsertQuery);

	// If Insert Successful, Delete From Current table
	if ($rInsertResult) {
		$sDeleteQuery = "DELETE FROM foreignIpLog WHERE id = '$oRow->id'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}



















$sQuery = "INSERT IGNORE INTO nibbles_datafeed.dataFeedLogHistory
			SELECT * FROM nibbles_datafeed.dataFeedLog
			WHERE  dateTime < CURRENT_DATE";
$rResult = dbQuery($sQuery);
echo dbError();
		
if ($rResult) {
	$sDelete = "DELETE FROM nibbles_datafeed.dataFeedLog
				WHERE  dateTime < CURRENT_DATE";
	$asdf = dbQuery($sDelete);
	echo dbError();
}













$sSelectQuery = "SELECT distinct offerCode FROM abandedOffers";
$rSelectResult = dbQuery($sSelectQuery);
while ($sTempRow = dbFetchObject($rSelectResult)) {
	if (strlen($sTempRow->offerCode)==1) {
		$sDeleteQuery = "DELETE FROM abandedOffers WHERE offerCode='$sTempRow->offerCode'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}

// Start: Loop abandedOffers entries
$sAbandedOffersSelectQuery = "SELECT *
                                FROM   abandedOffers
                                WHERE  dateTimeAdded < CURRENT_DATE";
$rAbandedOffersSelectResult = dbQuery($sAbandedOffersSelectQuery);
echo dbError();
while ($oAbandedOffersSelectRow = dbFetchObject($rAbandedOffersSelectResult)) {
	$iId = $oAbandedOffersSelectRow->id;
	$sEmail = $oAbandedOffersSelectRow->email;
	$sDateTimeAdded = $oAbandedOffersSelectRow->dateTimeAdded;
	$sRemoteIp = $oAbandedOffersSelectRow->remoteIp;
	$sSourceCode = $oAbandedOffersSelectRow->sourceCode;
	$sOfferCode = $oAbandedOffersSelectRow->offerCode;
	$sSessionId = $oAbandedOffersSelectRow->sessionId;
		
	// Insert into History Table
	$sInsertAbandedOffersQuery = "INSERT INTO abandedOffersHistory(email, dateTimeAdded, remoteIp, sourceCode, offerCode, sessionId)
                      VALUES(\"$sEmail\", \"$sDateTimeAdded\", \"$sRemoteIp\", \"$sSourceCode\", \"$sOfferCode\", \"$sSessionId\")";
	$rInsertAbandedOffersResult = dbQuery($sInsertAbandedOffersQuery);
	echo dbError();

		
	// If Insert Successful, Delete From Current table
	if ($rInsertAbandedOffersResult) {
		$sDeleteAbandedOffersQuery = "DELETE FROM abandedOffers
                                  WHERE  id = '$iId'";
		$rDeleteAbandedOffersResult = dbQuery($sDeleteAbandedOffersQuery);
		echo dbError();
	}
}
	
	
	
	
	
	
	


?>


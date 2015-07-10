<?php
/*
Moves data from 'abandedOffers' to 'abandedOffersHistory' table for previous day.
*/

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


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


// End: If Script not already running.
?>

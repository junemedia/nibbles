<?php

// cleanup script to run daily to clean up tables deleting old records
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

// cleanup held table
$sDeleteQuery = "DELETE FROM joinEmailInactive
				 WHERE  dateTimeAdded < date_add( CURRENT_DATE, INTERVAL -30 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup pending table
$sDeleteQuery = "DELETE FROM joinEmailPending
				 WHERE  dateTimeAdded < date_add( CURRENT_DATE, INTERVAL -60 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);


	
// cleanup email change pending table
$sDeleteQuery = "DELETE FROM joinEmailChangePending
				 WHERE  dateTimeAdded < date_add( CURRENT_DATE, INTERVAL -30 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);


// cleanup held table
for ($i=0; $i<50; $i=$i+5000) {
	$sDeleteQuery = "DELETE FROM joinEmailHeld
					 WHERE  dateTimeAdded < date_add( CURRENT_DATE, INTERVAL -180 DAY )";
	$rDeleteResult = dbQuery($sDeleteQuery);
	sleep(10);
}






// cleanup trackNibbleUse table
$sDeleteQuery = "DELETE FROM trackNibbleUse
				 WHERE  dateTimeLogged < date_add( CURRENT_DATE, INTERVAL -15 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);


// cleanup errorLog table - keep last 30 days only
$sDeleteQuery = "DELETE FROM errorLog
				 WHERE  errorDateTime < date_add( CURRENT_DATE, INTERVAL -30 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup held table
$sDeleteQuery = "DELETE FROM tempHttpFormPostTracking
				 WHERE  dateTimePosted < date_add( CURRENT_DATE, INTERVAL -3 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);


$sDeleteQuery = "DELETE FROM  nibbles_temp.tempApiLog
				 WHERE  dateTimeAdded < date_add( CURRENT_DATE, INTERVAL -15 DAY)";
$rDeleteResult = dbQuery($sDeleteQuery);






// cleanup nibbles.bdPixelsTrackingHistory
$sDeleteQuery = "DELETE FROM bdPixelsTrackingHistory
				 WHERE  openDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup nibbles.nlPixelsTrackingHistory
$sDeleteQuery = "DELETE FROM nlPixelsTrackingHistory
				 WHERE  openDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);


// cleanup nibbles.edOfferPixelsTrackingHistory
$sDeleteQuery = "DELETE FROM edOfferPixelsTrackingHistory
				 WHERE  openDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);




// cleanup nibbles.edOfferRedirectsTrackingHistory
$sDeleteQuery = "DELETE FROM edOfferRedirectsTrackingHistory
				 WHERE  clickDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup nibbles.bdRedirectsTrackingHistory
$sDeleteQuery = "DELETE FROM bdRedirectsTrackingHistory
				 WHERE  clickDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";

$rDeleteResult = dbQuery($sDeleteQuery);




// cleanup nibbles.offerStats
$sDeleteQuery = "DELETE FROM offerStats
                  WHERE displayDate < date_add( CURRENT_DATE, INTERVAL -10 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup held table
$sDeleteQuery = "DELETE FROM validateAddressAoStats
           WHERE  dateTimeCheck < date_add( CURRENT_DATE, INTERVAL -3 DAY )";
$rDeleteResult = dbQuery($sDeleteQuery);



// cleanup test leads
$sUserQuery = "SELECT email
                           FROM   userDataHistory
                           WHERE  address like \"3401 Dundee%\"";
$rUserResult = dbQuery($sUserQuery);
while ($oUserRow = dbFetchObject($rUserResult)) {
        $sUserEmail = $oUserRow->email;
        if ($sUserEmail !='') {
                $sDeleteOtDataQuery = "DELETE FROM otDataHistory
                                    WHERE  email = \"$sUserEmail\"";
                $rDeleteOtDataResult = dbQuery($sDeleteOtDataQuery);
                $sDeleteUserDataQuery = "DELETE FROM userDataHistory
                                     WHERE  email = \"$sUserEmail\"";
                $rDeleteUserDataResult = dbQuery($sDeleteUserDataQuery);
        }
}


?>

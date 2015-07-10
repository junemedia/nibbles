<?php

include("/home/sites/www_popularliving_com/html/includes/paths.php");

$sCoRegPageName = 'coReg_'.trim($_GET['sOfferCode']);
$sSourceCode = trim($_GET['src']);
$sSubSourceCode = trim($_GET['ss']);
$sSessionId = trim($_GET['sessId']);
$sRemoteIp = $_SERVER['REMOTE_ADDR'];

$sGetPageId = "SELECT * FROM otPages WHERE pageName='$sCoRegPageName'";
$rGetPageIdResult = dbQuery($sGetPageId);
while ($oOtPageRow = dbFetchObject($rGetPageIdResult)) {
	$iPageId = $oOtPageRow->id;
}


$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate, sessionId, ipAddress, openDateTime)
				VALUES('$iPageId', '$sSourceCode', '$sSubSourceCode', CURRENT_DATE, '$sSessionId', '$sRemoteIp', now())";
$rPageStatResult = dbQuery($sPageStatQuery);



?>

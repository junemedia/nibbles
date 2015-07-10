<?php

/*********
Script for Co-Reg Pixel Tracking
*********/

include("../includes/paths.php");
include('/home/sites/www_popularliving_com/html/nibbles2/session_handlers.php');

$sEmail = '';
$sSourceCode = '';
$sSubSourceCode = '';
$sSessId = '';
$sOfferCode = '';
$iPageId = '';
$fRevPerLead = '0.00';
$sPostalVerified = 'V';

$sSessId = trim($_GET['sesId']);
$sOfferCode = trim($_GET['offerCode']);
$sRemoteIp = $_SERVER['REMOTE_ADDR'];
$sServerIp = $_SERVER['SERVER_ADDR'];

if ($sSessId == '[sesId]') { $sSessId = ''; }

// AmpereSessionId cookie is set on ot.php - expires after 60 min.
// Cookie has session id in it.
if ($sSessId == '' && isset($_COOKIE["AmpereSessionId"])) {
	$sSessId = $_COOKIE["AmpereSessionId"];
	$_COOKIE['PHPSESSID'] = $sSessId;
}

session_start();
$sEmail = $_SESSION['sSesEmail'];
$sSourceCode = $_SESSION["sSesSourceCode"];
$sSubSourceCode = $_SESSION["sSesSubSourceCode"];

$iTempVal = $_SESSION['iSesCurrentPositionInFlow'] + 1;
$sGetPageId = "SELECT id FROM otPages
				WHERE flowId = '".$_SESSION['iSesFlowId']."'
				AND pageNo = '$iTempVal'";
$rPageId = dbQuery($sGetPageId);
echo dbError();
while ($oPageIdRow = dbFetchObject($rPageId)) {
	$iPageId = $oPageIdRow->id;
}


$sGetOffersQuery = "SELECT * FROM offers WHERE offerCode='$sOfferCode'";
$rOffersResult = dbQuery($sGetOffersQuery);
if (dbNumRows($rOffersResult) > 0 ) {
	while($sOffersRow = dbFetchObject($rOffersResult)) {
		$fRevPerLead = $sOffersRow->revPerLead;
		$sPixelEnable = $sOffersRow->isCoRegPopPixelEnable;
	}
}

if ($sPixelEnable == 'Y') {

	if ($sSessId != '') {
		$sGetOtData = "SELECT * FROM otData WHERE sessionId='$sSessId' LIMIT 1";
		$rGetOtData = dbQuery($sGetOtData);
		while($sOtData = dbFetchObject($rGetOtData)) {
			$sEmail = $sOtData->email;
			$sSourceCode = $sOtData->sourceCode;
			$sSubSourceCode = $sOtData->subSourceCode;
			$sPostalVerified = $sOtData->postalVerified;
		}
		
		if ($sEmail == '') {
			$sGetUserData = "SELECT * FROM userData WHERE sessionId='$sSessId'";
			$rGetUserData = dbQuery($sGetUserData);
			if (dbNumRows($rGetUserData) > 0 ) {
				while($sUserDataRow = dbFetchObject($rGetOtData)) {
					$sEmail = $sUserDataRow->email;
					$sPostalVerified = $sUserDataRow->postalVerified;
				}
			}
		}
	}
	
	
	if ($iPageId == '') {
		$sTempOfferCode = "coReg_".$sOfferCode;
		$sGetPageIdQuery = "SELECT * FROM otPages WHERE pageName = '$sTempOfferCode'";
		$rOtPageResult = dbQuery($sGetPageIdQuery);
		if (dbNumRows($rOtPageResult) > 0 ) {
			while($sOtPagesRow = dbFetchObject($rOtPageResult)) {
				$iPageId = $sOtPagesRow->id;
			}
		}
	}


	// Insert into otData
	$sCurrentDateTime = date('Y-m-d H:i:s');
	$sLeadInsertQuery = "INSERT INTO otData(email, offerCode, sourceCode, subSourceCode, revPerLead, pageId, dateTimeAdded, 
				remoteIp, serverIp, mode, postalVerified, processStatus, sendStatus, howSent, 
				dateTimeSent, dateTimeProcessed, isOpenTheyHost, sessionId)
			 VALUES(\"$sEmail\", \"$sOfferCode\", \"$sSourceCode\", \"$sSubSourceCode\", \"$fRevPerLead\", \"$iPageId\", '$sCurrentDateTime', 
			 \"$sRemoteIp\", \"$sServerIp\", 'A', \"$sPostalVerified\", 'P', 'S', 'crpPixel', '$sCurrentDateTime', '$sCurrentDateTime', 'Y', \"$sSessId\")";
	$rLeadInsertResult = dbQuery($sLeadInsertQuery);
}

?>
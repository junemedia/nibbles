<?php

//	Script for Close They-Host Pixel Tracking


include("../includes/paths.php");
include('../nibbles2/session_handlers.php');

$sEmail = '';
$sSourceCode = '';
$sSubSourceCode = '';
$sSessId = '';
$sOfferCode = '';
$iPageId = '';
$fRevPerLead = '0.00';
$sPostalVerified = 'V';
$sTypeOfOffer = '';

$sSessId = trim($_GET['sesId']);
$sOfferCode = trim($_GET['offerCode']);
$sRemoteIp = $_SERVER['REMOTE_ADDR'];
$sServerIp = $_SERVER['SERVER_ADDR'];

if ($sSessId == '[sesId]') { $sSessId = ''; }


if (isset($_COOKIE['AmpereOfferType'])) {
	$sTypeOfOffer = $_COOKIE["AmpereOfferType"];
}


// AmpereSessionId cookie is set on ot.php - expires after 60 min.
// Cookie has session id in it.
if ($sSessId == '' && isset($_COOKIE["AmpereSessionId"])) {
	$sSessId = $_COOKIE["AmpereSessionId"];
}
$_COOKIE['PHPSESSID'] = $sSessId;

session_start();

if ($_SESSION['iSesFlowId'] !='') {
	$iTempVal = $_SESSION['iSesCurrentPositionInFlow'] + 1;
	$sGetPageId = "SELECT id FROM otPages
					WHERE flowId = '".$_SESSION['iSesFlowId']."'
					AND pageNo = '$iTempVal'";
	$rPageId = dbQuery($sGetPageId);
	echo dbError();
	while ($oPageIdRow = dbFetchObject($rPageId)) {
		$iPageId = $oPageIdRow->id;
	}
}


$sGetOffersQuery = "SELECT * FROM offers WHERE offerCode='$sOfferCode'";
$rOffersResult = dbQuery($sGetOffersQuery);
if (dbNumRows($rOffersResult) > 0 ) {
	while($sOffersRow = dbFetchObject($rOffersResult)) {
		$fRevPerLead = $sOffersRow->revPerLead;
		$sPixelEnable = $sOffersRow->isCloseTheyHostPixelEnable;
	}
}

if ($sPixelEnable == 'Y') {
	if ($sSessId != '') {
		$sSessId = trim($sSessId);
		$sCheckSessionTable = "SELECT * FROM PHPSESSID WHERE session_id = '$sSessId' LIMIT 1";
		$rSessionTable = dbQuery($sCheckSessionTable);
		if (dbNumRows($rSessionTable) > 0 ) {
			while($sSesData = dbFetchObject($rSessionTable)) {
				$sEmail = $_SESSION['sSesEmail'];
				$sSourceCode = $_SESSION["sSesSourceCode"];
				$sSubSourceCode = $_SESSION["sSesSubSourceCode"];
				//$iPageId = $_SESSION['iSesPageId'];
			}
		} else {
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
	}
	
	if ($sTypeOfOffer == '') {
		$sTypeOfOffer = 'cth_';
	}
	
	if ($iPageId == '') {
		$sTempOfferCode = $sTypeOfOffer.$sOfferCode;
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
	$sLeadInsertQuery = "INSERT INTO otData(email, offerCode, sourceCode, subSourceCode, revPerLead, pageId, dateTimeAdded, remoteIp, serverIp, mode, postalVerified, processStatus, sendStatus, 
				dateTimeSent, dateTimeProcessed, isOpenTheyHost, sessionId)
			 VALUES(\"$sEmail\", \"$sOfferCode\", \"$sSourceCode\", \"$sSubSourceCode\", \"$fRevPerLead\", \"$iPageId\", '$sCurrentDateTime', 
			 \"$sRemoteIp\", \"$sServerIp\", 'A', \"$sPostalVerified\", 'P', 'S', '$sCurrentDateTime', '$sCurrentDateTime', 'Y', \"$sSessId\")";
	$rLeadInsertResult = dbQuery($sLeadInsertQuery);
	
	if (!($rLeadInsertResult)) {
		mail('spatel@amperemedia.com','failed - cth',$sLeadInsertQuery);
	} else {
	//	mail('spatel@amperemedia.com','success - cth',$sLeadInsertQuery);
	}
	
	
	
	
	$aOfferTakenInCookie = array();
	if (!(is_array($_SESSION['aOfferTakenForCookie']))) {
		$_SESSION['aOfferTakenForCookie'] = array();
	}
	if (isset($_COOKIE["OfferTakenInCookie"])) {
		$aOfferTakenInCookie = explode(",", $_COOKIE["OfferTakenInCookie"]);
		if (count($aOfferTakenInCookie) > 0) {
			foreach ($aOfferTakenInCookie as $sOfferTemp) {
				if (!in_array($sOfferTemp, $_SESSION['aOfferTakenForCookie'])) {
					array_push($_SESSION['aOfferTakenForCookie'], $sOfferTemp);
				}
			}
		}
	}
	if (!in_array($sOfferCode, $_SESSION['aOfferTakenForCookie'])) {
		array_push($_SESSION['aOfferTakenForCookie'], $sOfferCode);
	}

	$sCurrentCookieOfferCode = '';
	$_SESSION['aOfferTakenForCookie'] = array_unique($_SESSION['aOfferTakenForCookie']);
	foreach ($_SESSION['aOfferTakenForCookie'] as $sOfferCodeCookie) {
		$sCurrentCookieOfferCode .= "$sOfferCodeCookie,";
	}
	$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
	// expires in 180 days - 15552000 seconds	- Add/Update cookie.
	setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '.3400cookie.com', 0);
	
	setcookie("AmpereOfferType", "th_", time()-3600, "/", '.3400cookie.com', 0);
	setcookie("AmpereOfferType", "cth_", time()-3600, "/", '.3400cookie.com', 0);
}

?>

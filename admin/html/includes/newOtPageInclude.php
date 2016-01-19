<?php

// Initialize all the variables used in this script
// except those names which may be returned as form submission values or querystring values
/*****************  variable initialization *****************/
// get the referer page name to go back to that page if any error in data
//$sRefererScriptFileName = $HTTP_REFERER;
$sRefererScriptFileName = $_SERVER['HTTP_REFERER'];

// set session id in cookie for pixel firing script.
setcookie("AmpereSessionId", session_id(), time()+3600, "/", '', 0);

// remove any queryString variables...
if (strstr($sRefererScriptFileName,"?")) {
	$sRefererScriptFileName = substr($sRefererScriptFileName, 0, strpos($sRefererScriptFileName, "?"));
}

include_once("$sGblLibsPath/dateFunctions.php");
$iScriptStartTime = getMicroTime();
/****************** check the incoming sourceCode is a valid sourceCode, if not valid make it null *******/
include_once("$sGblLibsPath/validationFunctions.php");
if ($src != '') {
	if (!ctype_alnum($src)) {
		$src = '';
	} else {
		if ($_SESSION['sSesSourceCode'] == '') {
			$_SESSION['sSesSourceCode'] = $src;
		}
	}
}
/**********************************************************************/
/*START OF GETTING VALUE FOR TARGETTING OFFERS*/
/****************************/
	// check if zip is numeric and 5 digit long.  If valid, add to session else make it null.
	$tz = trim($_GET['tz']);
	if ($tz != '') {
		if (ctype_digit($tz) && strlen($tz) == 5) {
			if ($_SESSION['sSesTargetZip'] == '') {
				$_SESSION['sSesTargetZip'] = $tz;
			}
		} else {
			$_SESSION['sSesTargetZip'] = '';
			$tz = '';
		}
	}
	
	// check if birth year is numeric and 4 digit long.  If valid, add to session else make it null.
	$ty = trim($_GET['ty']);
	if ($ty != '') {
		if (ctype_digit($ty) && strlen($ty) == 4) {
			if ($_SESSION['sSesTargetYear'] == '') {
				$_SESSION['sSesTargetYear'] = $ty;
			}
		} else {
			$_SESSION['sSesTargetYear'] = '';
			$ty = '';
		}
	}
	
	if ($_SESSION['sSesTargetYear'] == '' && $iBirthYear !='') {
		if (ctype_digit($iBirthYear) && strlen($iBirthYear) == 4) {
			$_SESSION['sSesTargetYear'] = $iBirthYear;
		}
	}
	
	if ($_SESSION['sSesTargetYear'] == '' && $_SESSION["iSesBirthYear"] !='') {
		if (ctype_digit($_SESSION["iSesBirthYear"]) && strlen($_SESSION["iSesBirthYear"]) == 4) {
			$_SESSION['sSesTargetYear'] = $_SESSION["iSesBirthYear"];
		}
	}

	// check if exchange is numeric and 3 digit long.  If valid, add to session else make it null.
	// Also exchange should not start with 0 or 1.
	$te = trim($_GET['te']);
	if ($te != '') {
		if (ctype_digit($te) && strlen($te) == 3 && !ereg("^[01]{1}", $te)) {
			if ($_SESSION['sSesTargetExchange'] == '') {
				$_SESSION['sSesTargetExchange'] = $te;
			}
		} else {
			$_SESSION['sSesTargetExchange'] = '';
			$te = '';
		}
	}

	// check if target state is valid.  If valid, add to session else make it null.
	$ts = trim($_GET['ts']);
	$ts = strtolower($ts);
	if (strlen($ts) == 2) {
		if (validateTargetState($ts)) {
			if ($_SESSION['sSesTargetState'] == '') {
				$_SESSION['sSesTargetState'] = $ts;
			}
		} else {
			$_SESSION['sSesTargetState'] = '';
			$ts = '';
		}
	}
	
	$ts = trim($_GET['s']);
	$ts = strtolower($ts);
	if ($_SESSION['sSesTargetState'] =='') {
		if (strlen($ts) == 2) {
			if (validateTargetState($ts)) {
				$_SESSION['sSesTargetState'] = $ts;
			} else {
				$ts = '';
			}
		}
	}
	
	// check if 'Gender' is M, F, or ''.  If valid, add to session else make it null
	$tg = trim($_GET['tg']);
	$tg = strtoupper($tg);
	if ($tg == 'M' || $tg == 'F') {
		if ($_SESSION['sSesTargetGender'] == '') {
			$_SESSION['sSesTargetGender'] = $tg;
		}
	}
	
	
	if ($_SESSION['sSesTargetGender'] == '' && $sGender !='') {
		$_SESSION['sSesTargetGender'] = $sGender;
	}
	
	if ($_SESSION['sSesTargetGender'] == '' && $_SESSION["sSesGender"] !='') {
		$_SESSION['sSesTargetGender'] = $_SESSION["sSesGender"];
	}

	
	if ($_SESSION["sSesState"] != '') {
		$sStateTemp = $_SESSION["sSesState"];
		$sStateTemp = strtolower($sStateTemp);
		if (strlen($sStateTemp) == 2) {
			if (validateTargetState($sStateTemp)) {
				if ($_SESSION['sSesTargetState'] == '') {
					$_SESSION['sSesTargetState'] = $sStateTemp;
					$ts = $sStateTemp;
				}
			}
		}
	}
	

	if ($_SESSION["sSesZip"] != '') {
		$sTempZip = $_SESSION["sSesZip"];
		if (ctype_digit($sTempZip) && strlen($sTempZip) == 5) {
			if ($_SESSION['sSesTargetZip'] == '') {
				$_SESSION['sSesTargetZip'] = $sTempZip;
				$tz = $sTempZip;
			}
		}
	}
	
	if ($_SESSION["sSesPhoneNoDash"] != '') {
		$sTempExchange = substr($_SESSION["sSesPhoneNoDash"],3,3);
		if (ctype_digit($sTempExchange) && strlen($sTempExchange) == 3 && !ereg("^[01]{1}", $sTempExchange)) {
			if ($_SESSION['sSesTargetExchange'] == '') {
				$_SESSION['sSesTargetExchange'] = $sTempExchange;
				$te = $sTempExchange;
			}
		}
	}
	
	// ONLY IF BLANK
	/****************** Start *****************************/
	if ($_SESSION['sSesTargetExchange'] == '') {
		$te = trim($_GET['p']);
		$te = substr($te, 4, 3);
		if (ctype_digit($te) && strlen($te) == 3 && !ereg("^[01]{1}", $te)) {
			$_SESSION['sSesTargetExchange'] = $te;
		} else {
			$te = trim($_GET['pe']);
			if (ctype_digit($te) && strlen($te) == 3 && !ereg("^[01]{1}", $te)) {
				$_SESSION['sSesTargetExchange'] = $te;
			}
		}
	}
	
	if ($_SESSION['sSesTargetState'] == '') {
		$ts = trim($_GET['s']);
		$ts = strtolower($ts);
		if (strlen($ts) == 2) {
			if (validateTargetState($ts)) {
				$_SESSION['sSesTargetState'] = $ts;
			}
		}
	}
	
	if ($_SESSION['sSesTargetZip'] == '') {
		$tz = trim($_GET['z']);
		if (ctype_digit($tz) && strlen($tz) == 5) {
			$_SESSION['sSesTargetZip'] = $tz;
		}
	}
	
	if ($_SESSION['sSesTargetYear'] == '') {
		$by = trim($_GET['by']);
		if (ctype_digit($by) && strlen($by) == 4) {
			$_SESSION['sSesTargetYear'] = $by;
		}
	}
	
	if ($_SESSION['sSesTargetGender'] == '') {
		$gn = trim($_GET['gn']);
		$gn = strtoupper($gn);
		if ($gn == 'M' || $gn == 'F') {
			$_SESSION['sSesTargetGender'] = $gn;
		}
	}
	/****************** End *****************************/
		
/*****************************/
/*END OF GETTING VALUE FOR TARGETTING OFFERS*/

/************* store pageId into session variables **********************/
if ($iPageId) {
	if (!ctype_digit($iPageId)) { $iPageId = ''; }
	$_SESSION["iSesPageId"] = $iPageId;	
} else {
	if (!ctype_digit($_SESSION["iSesPageId"])) { $_SESSION["iSesPageId"] = ''; }
	$iPageId = $_SESSION["iSesPageId"];
}
/*************************************************************************/
/************* store site Id in session *************/
if ($sSiteId) {
	$_SESSION['sSiteId'] = $sSiteId;
}
/****************************************************/

/************************** get page details ***************************/
if ($_SESSION["iSesPageId"] != '') {
	$sPageQuery = "SELECT *
			   FROM   otPages
			   WHERE  id = '".$_SESSION["iSesPageId"]."'";
	$rPageResult = dbQuery($sPageQuery);
	while ($oPageRow = dbFetchObject($rPageResult)) {
		$sPageName = $oPageRow->pageName;
		$sPage2Name = $sPageName."b_2.php";
		$_SESSION["sSesPage2Name"] = $sPage2Name;
		if (substr($sPageName,0,4) =='test') {
			$sPageMode = "T";
		} else {
			$sPageMode = 'A';
		}
		$iPageAutoEmail = $oPageRow->autoEmail;
		$iDefaultAutoRespId = $oPageRow->defaultAutoRespId;
		$sRedirectTo = $oPageRow->redirectTo;
		$sRedirectToNotOfferTaken = $oPageRow->redirectToNotOfferTaken;
		if ($sRedirectToNotOfferTaken == '') {
			$sRedirectToNotOfferTaken = $sRedirectTo;
		}
		
		$sRedirectPopOption = $oPageRow->redirectPopOption;				
		$sRedirectPopUrl = $oPageRow->redirectPopUrl;
		$sRedirectNotOfferTakenPopOption = $oPageRow->redirectNotOfferTakenPopOption;
		$sRedirectNotOfferTakenPopUrl = $oPageRow->redirectNotOfferTakenPopUrl;
		if ($sRedirectNotOfferTakenPopUrl == '') {
			$sRedirectNotOfferTakenPopUrl = $sRedirectPopUrl;
		}		
		
		$iPassOnPrepopCodes = $oPageRow->passOnPrepopCodes;
		$iPassOnInboundQueryString = $oPageRow->passOnInboundQueryString;
		$iPassOnPhpsessid = $oPageRow->passOnPhpsessid;
		$iOfferNotRequired = $oPageRow->offerNotRequired;
		$sInboundVarMap = $oPageRow->inboundVarMap;
		$sOutboundVarMap = $oPageRow->outboundVarMap;
		$iDisplayYesNo = $oPageRow->displayYesNo;
		$sRequireYesNo = $oPageRow->requireYesNo;
		$iEnableGoTo = $oPageRow->enableGoTo;
		$iIsGoToPopUp = $oPageRow->isGoToPopUp;
		$iOffersByPageMap = $oPageRow->offersByPageMap;
		$iOffersByCatMap = $oPageRow->offersByCatMap;
		$sPageBgColor = $oPageRow->pageBgColor;
		
		if ($iDisplayYesNo) {
			if ($iOffersByPageMap) {
				$sYesNoOffersCountQuery = "SELECT count(*) as offersCount
					 FROM   offers, pageMap, offerCompanies AS oc
					 WHERE  offers.offerCode = pageMap.offerCode
					 AND    offers.companyId = oc.id					
					 AND    pageMap.pageId = '".$_SESSION["iSesPageId"]."'";
				if (substr($sPageName,0,4) == 'test') {
					$sYesNoOffersCountQuery .= " AND    (offers.mode = 'T' || offers.mode = 'A') ";
				} else {
					$sYesNoOffersCountQuery .= " AND    offers.mode = 'A'  AND    offers.isLive = '1'
		 				   AND    oc.creditStatus = 'ok'";
				}
			} else {
				$sYesNoOffersCountQuery = "SELECT count(*) as offersCount
					 FROM   offers, categoryMap, offerCompanies AS oc
					 WHERE  offers.offerCode = categoryMap.offerCode
					 AND    offers.companyId = oc.id					 
					 AND    categoryId = '$iOffersByCatMap'";
				if (substr($sPageName,0,4) == 'test') {
					$sYesNoOffersCountQuery .= " AND    (offers.mode = 'T' || offers.mode = 'A') ";
				} else {
					$sYesNoOffersCountQuery .= " AND    offers.mode = 'A'  AND offers.isLive = '1'
						   AND    oc.creditStatus = 'ok'";
				}
			}
			$rYesNoOffersCountResult = dbQuery($sYesNoOffersCountQuery);
			echo mysql_error();
			while ($oYesNoOffesCountRow = dbFetchObject($rYesNoOffersCountResult)) {
				$iYesNoOffersCount = $oYesNoOffesCountRow->offersCount;
			}			
		}
	}
	if ($rPageResult) {
		dbFreeResult($rPageResult);
	}
}
/************************** end of getting page details ***************************/
/************************ get inbound vars and store decoded value  ****************/
// place this code here outside of the loop if ($isset($_SESSION['aSesInboundQueryString') loop
// because incoming variable will be needed every time when user comes to the page again in case of error.
// all incoming variables also are attached in the url when redirected again to the same page with error message
// so again rename all the incoming variables to the mapping variable names
// except the variables user may change. i.e. user's own info should not be changed what it was in incoming url
// but it should remain as whatever user submitted through the form

$aInboundVarMap = explode(",",$sInboundVarMap);
$aQueryStringArray = explode("&", $sGblQueryString);

for ($i=0; $i<count($aQueryStringArray); $i++) {
	$aKeyValuePair = explode("=",$aQueryStringArray[$i]);
	$key = $aKeyValuePair[0];
	$$key = urldecode($aKeyValuePair[1]);
}

/*****************************************************************************/
if (ctype_digit($arId)) {
	$_SESSION['iSesPageAutoRespId'] = $arId;
}

/************** store inbound querystring into session variable only first time, if session variable not set **********/
if(!(isset($_SESSION['aSesInboundQueryString']))) {
	$_SESSION["aSesInboundQueryString"] = array();
	$_SESSION['sSesInboundJavaScriptVars'] = '';
	
	// to store srs in a javascript var to use in exit popup src check
	$_SESSION['sSesPage1JavaScriptVars'] = '';
	$aQueryStringArray = explode("&", $sGblQueryString);
	$j=0;
	$sGblQueryString = '';
	for ($i=0; $i<count($aQueryStringArray); $i++) {
		/******************* rename incoming variable if inbound var map is defined for the var *******/
		// this should be done only once otherwise "a" will be renamed as "b" and "b" will be 
		// renamed to "c" if user comes once again on the page in case of error
		$aKeyValuePair = explode("=",$aQueryStringArray[$i]);
		$key = $aKeyValuePair[0];
		$$key = urldecode($aKeyValuePair[1]);
		$value = urldecode($aKeyValuePair[1]);
				
		for ($ii=0;$ii<count($aInboundVarMap);$ii++) {
		$aInboundMapPair = explode("=",$aInboundVarMap[$ii]);
		if ($aInboundMapPair[0] == $key) {
			$newKey = $aInboundMapPair[1];
			if ($sMessage == '' || ($newKey != 'e' && $newKey != 'f' && $newKey != 'l' && $newKey != 'a1' && $newKey != 'a2' && $newKey != 'c' &&
			$newKey != 's' && $newKey != 'z' && $newKey != 'p')) {
				$key = $newKey;
				$$key = urldecode($value);
			}
		}
	}
	
	// build query string var using new var name ( inbound var name ) to use in rest of the script
	$sGblQueryString .= "$key=".urlencode($value)."&";
	
	/**************************************************/
	if ($key == 'src') {
		if (!ctype_alnum($value)) {
			$value = '';
		}
		$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '".$value."';";
		$bSrcDefined = true;
	}
		
	if ($key == 'popno') {
		$_SESSION['sSesPage1JavaScriptVars'] .= "\n var popno = '".$value."';";
	}

	if ($key != 'e' && $key != 'f' && $key != 'l' && $key != 'a1' && $key != 'a2' && $key != 'c' &&
		$key != 's' && $key != 'z' && $key != 'p' && $key != 'ext' && $key != 'src' && $key != 'ss' &&
		$key != 'tm' && $key != 'iMenuId' && $key != 'iId') {
			if ($key != 'PHPSESSID') {
				$_SESSION['aSesInboundQueryString'][$j]['key'] = $key;
				$_SESSION['aSesInboundQueryString'][$j]['value'] = $value;	
				$j++;
			}

			if ($value != '') {					
				$value = urldecode($value);
			}		
			if (ltrim((rtrim($key))) != '') {
				$_SESSION['sSesInboundJavaScriptVars'] .= "\n var $key = '".$value."';";
			}
			if ($key == 'g') {
				$_SESSION['sSesPage1JavaScriptVars'] .= "\n var g = '".$value."';";
			}
		} else 	if ($key != 'iMenuId' && $key != 'iId') {
			$aOutboundVarMapArray = explode(",",$sOutboundVarMap);
			$bFound = false;
			for ($o=0; $o<count($aOutboundVarMapArray); $o++) {
				$aInboundOutboundPair = explode("=",$aOutboundVarMapArray[$o]);
				switch ($aInboundOutboundPair[0]) {
					case $key:
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$value."&";
					$bFound = true;
					break;
				}
			}
			if (!($bFound) && $key != 'src') {		
				$sPrepopcodes .= "$key=$value&";
			}
			// make incoming variables available in javascript in page1
			$_SESSION['sSesPage1JavaScriptVars'] .= "\n var $key = '".$value."';";
		}
	}
	
	if ($sPrepopcodes != '') {
		$sPrepopcodes = substr($sPrepopcodes, 0, strlen($sPrepopcodes)-1);
	}
	
	if (!$bSrcDefined) {
		$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '';";
	}
	
	if ($sGblQueryString != '') {
		$sGblQueryString = substr($sGblQueryString ,0 ,strlen($sGblQueryString)-1);
	}
}
/********************* End storing incoming variables into session variable only once ***************************************/

/******************  Prepare outbound query string  ****************************/

if ($_SESSION["iSesPageId"] != '') {
	if ($iPassOnInboundQueryString) {
		if ($sOutboundVarMap != '') {
			$aOutboundVarMapArray = explode(",",$sOutboundVarMap);
			for ($j=0; $j<count($_SESSION["aSesInboundQueryString"]); $j++) {
				$iMapped = 0;
				for ($i=0; $i<count($aOutboundVarMapArray); $i++) {
					$aKeyValuePair = explode("=",$aOutboundVarMapArray[$i]);
					if ($aKeyValuePair[0] == $_SESSION["aSesInboundQueryString"][$j]['key']) {
						$iMapped = 1;
						break;
					}
				}

				if ($iMapped) {
					$sOutboundQueryString .= $aKeyValuePair[1]."=".$_SESSION["aSesInboundQueryString"][$j]['value']."&";
				} else {
					$sOutboundQueryString .= $_SESSION["aSesInboundQueryString"][$j]['key']."=".$_SESSION["aSesInboundQueryString"][$j]['value']."&";
				}
			}
		} else {						
			for ($j=0; $j<count($_SESSION["aSesInboundQueryString"]); $j++) {
				$sOutboundQueryString .= $_SESSION["aSesInboundQueryString"][$j]['key']."=".$_SESSION["aSesInboundQueryString"][$j]['value']."&";
			}
		}
		
		if ($sOutboundQueryString != '') {
			$iTempPageId = $_SESSION["iSesPageId"];
			$sCheckCbQuery = "SELECT * FROM coBrandDetails WHERE pageId = '$iTempPageId'";
			$rCheckCbResult = dbQuery($sCheckCbQuery);
			if (mysql_num_rows($rCheckCbResult) == 0) {
				$_SESSION['iSesCbId'] = '';
			}
			$sOutboundQueryString = substr($sOutboundQueryString,0, strlen($sOutboundQueryString)-1);
		}
	}
}

/*************************  End preparing outbound querystring  ***********************/
/*********** If error message is there, Add main error occuring message before it.  *********/
if ($sMessage != '') {
	// get Main Error Message
	$sMessageQuery = "SELECT *
					 FROM    otPageDefinitions
					 WHERE   definition = 'mainErrorMessage'";
	$rMessageResult = dbQuery($sMessageQuery);
	while ($oMessageRow = dbFetchObject($rMessageResult)) {
		$sMainErrorMessage = $oMessageRow->definedValue."<BR><BR>";
	}
	if ($rMessageResult) {
		dbFreeResult($rMessageResult);
	}
}

/******************  End adding main error message  ********************/
// Initialize session variables, if session is not yet created
if (!(session_id())) {
	$_SESSION["sSesSalutation"] = '';
	$_SESSION["sSesFirst"] = '';
	$_SESSION["sSesLast"] = '';
	$_SESSION["sSesEmail"] = '';
	$_SESSION["sSesAddress"] = '';
	$_SESSION["sSesAddress2"] = '';
	$_SESSION["sSesCity"] = '';
	$_SESSION["sSesState"] = '';
	$_SESSION["sSesZip"] = '';
	$_SESSION["sSesPhone"] = '';
	$_SESSION["sSesPhoneExt"] = '';
	$_SESSION["iSesJoinListId"] = '';
	$_SESSION["sSesSourceCode"] = '';
	$_SESSION["sSesSubSourceCode"] = '';
	$_SESSION["sSesPageMode"] = '';
	$_SESSION["sSesOffersTaken"] = '';
	$_SESSION["sSesFirstNameAsterisk"] = '';
	$_SESSION["sSesLastNameAsterisk"] = '';
	$_SESSION["sSesAddressAsterisk"] = '';
	$_SESSION["sSesAddress2Asterisk"] = '';
	$_SESSION["sSesCityAsterisk"] = '';
	$_SESSION["sSesStateAsterisk"] = '';
	$_SESSION["sSesZipCodeAsterisk"] = '';
	$_SESSION["sSesPhoneNoAsterisk"] = '';
	$_SESSION["sSesEmailAsterisk"] = '';
	$_SESSION["sSesGoTo"] = '';
}

//For PCH_1105Kid and PCH_1105VAN, they send us users with a 'g' (gender) variable in the query string. 
//They also send us an 'r' variable which is used as the dynamic re-direct, but our page uses the value in 'g'.
//We have javascript which re-names the value of 'r' to 'g' on the page (for the skip button), but the below code changes it in the session
if ($_SESSION["iSesPageId"] == '9077' || $_SESSION["iSesPageId"] == '9078') {
	$sGTemp = strtolower($g);
	if ($sGTemp == 'm' || $sGTemp == 'f') {
		$g = '';
		$_SESSION["sSesGoTo"] = '';
	} else {
		$_SESSION["sSesGoTo"] = $g;
	}
} else {	
	if ($g) {
		$_SESSION["sSesGoTo"] = $g;
	}
}

/*****************  end of variable initialization *****************/
// if prepopulated values passed, store those querystring variable values into proper form variables
if ($e) {
	$sEmail = $e;
}

if ($f) {
	$sFirst = $f;
}

if ($l) {
	$sLast = $l;
}

if ($a1) {
	$sAddress = $a1;
}

if ($a2) {
	$sAddress2 = $a2;
}

if ($c) {
	$sCity = $c;
}

if ($s) {
	if (!ctype_alpha($s)) { $s = ''; }
	$sState = $s;
}

if ($z) {
	$sZip = $z;
}

if ($p) {
	$sPhone = $p;
	$sPhone_areaCode = substr($sPhone, 0, 3);
	$sPhone_exchange = substr($sPhone, 4, 3);
	$sPhone_number = substr($sPhone, 8,4);
	if (!ctype_digit($sPhone_areaCode)) { $sPhone_areaCode = ''; }
	if (!ctype_digit($sPhone_exchange)) { $sPhone_exchange = ''; }
	if (!ctype_digit($sPhone_number)) { $sPhone_number = ''; }
}


if ($pa) {
	if (!ctype_digit($pa)) { $pa = ''; }
	$sPhone_areaCode = $pa;
}

if ($pe) {
	if (!ctype_digit($pe)) { $pe = ''; }
	$sPhone_exchange = $pe;
}

if ($pnum) {
	if (!ctype_digit($pnum)) { $pnum = ''; }
	$sPhone_number = $pnum;
}

if ($gn) {
	if (!ctype_alpha($gn)) { $gn = ''; }
	$sGender = $gn;
}

if ($by) {
	if (!ctype_digit($by)) { $by = ''; }
	$iBirthYear = $by;
}

if ($bd) {
	if (!ctype_digit($bd)) { $bd = ''; }
	$iBirthDay = $bd;
}

if ($bm) {
	if (!ctype_digit($bm)) { $bm = ''; }
	$iBirthMonth = $bm;
}

if ($ext) {
	// might have x208
	if (!ctype_alnum($ext)) { $ext = ''; }
	$sPhone_ext = $ext;
}
if ($src) {
	if (!ctype_alnum($src)) { $src = ''; }
	$sSourceCode = $src;
	$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
}
if ($ss) {
	if (!ctype_alnum($ss)) { $ss = ''; }
	$sSubSourceCode = $ss;
}
/***************  End getting values from prepopulated variables  ******************/
/***********  If values are already set in session variables, prepopulate it  ***********/
if (session_id()) {
	if ($_SESSION["sSesSalutation"] && !(isset($sSalutation))) {
		$sSalutation = $_SESSION["sSesSalutation"];
	}
	
	if ($_SESSION["sSesEmail"] && $e =='' && !(isset($sEmail))) {
		$sEmail = $_SESSION["sSesEmail"];
	}
	
	if ($_SESSION["sSesFirst"] && $f =='' && !(isset($sFirst))) {
		$sFirst = $_SESSION["sSesFirst"];
	}
	
	if ($_SESSION["sSesLast"] && $l =='' && !(isset($sLast))) {
		$sLast = $_SESSION["sSesLast"];
	}
	
	if ($_SESSION["sSesAddress"] && $a1 =='' && !(isset($sAddress))) {
		$sAddress = $_SESSION["sSesAddress"];
	}
	
	if ($_SESSION["sSesAddress2"] && $a2 =='' && ! isset($sAddress2)) {
		$sAddress2 = $_SESSION["sSesAddress2"];
	}
	
	if ($_SESSION["sSesCity"] && $c =='' && !(isset($sCity))) {
		$sCity = $_SESSION["sSesCity"];
	}
	
	if ($_SESSION["sSesState"] && $s =='' && !(isset($sState))) {
		$sState = $_SESSION["sSesState"];
	}
	
	if ($_SESSION["sSesZip"] && $z =='' && !(isset($sZip))) {
		$sZip = $_SESSION["sSesZip"];
	}
	
	if ($_SESSION["sSesPhone"] && $p=='' && !(isset($sPhone))) {
		$sPhone = $_SESSION["sSesPhone"];
		$sPhone_areaCode = substr($sPhone, 0, 3);
		$sPhone_exchange = substr($sPhone, 4, 3);
		$sPhone_number = substr($sPhone, 8,4);
	}
	
	if ($_SESSION["sSesPhoneAreaCode"] && $pa =='' && !(isset($sPhone_areaCode))) {
		$sPhone_areaCode = $_SESSION["sSesPhoneAreaCode"];
	}
	
	if ($_SESSION["sSesPhoneExchange"] && $pe =='' && !(isset($sPhone_exchange))) {
		$sPhone_exchange = $_SESSION["sSesPhoneExchange"];
	}
	
	if ($_SESSION["sSesPhoneNumber"] && $pnum =='' && !(isset($sPhone_number))) {
		$sPhone_number = $_SESSION["sSesPhoneNumber"];
	}
	
	if ($_SESSION["sSesPhoneExt"] && $ext =='' && !(isset($sPhone_ext))) {
		$sPhone_ext = $_SESSION["sSesPhoneExt"];
	}
	
	if ($_SESSION["iSesJoinListId"] && $iJoinListId == '') {
		$iJoinListId = $_SESSION["iSesJoinListId"];
	}
	
	if ($_SESSION["sSesSourceCode"] && $src =='' && !(isset($sSourceCode))) {
		$sSourceCode = $_SESSION["sSesSourceCode"];
		$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
	}
	
	if ($_SESSION["sSesSubSourceCode"] && $ss == '' && !(isset($sSubSourceCode))) {
		$sSubSourceCode = $_SESSION["sSesSubSourceCode"];
	}
	
	if ($_SESSION["sSesPageMode"] && $sPageMode = '' && !(isset($sPageMode))) {
		$sPageMode = $_SESSION["sSesPageMode"];
	}
	

	if ($_SESSION["iSesBirthYear"] && $iBirthYear = '' && !(isset($iBirthYear))) {
		$iBirthYear = $_SESSION["iSesBirthYear"];
	}
	
	if ($_SESSION["iSesBirthMonth"] && $iBirthMonth = '' && !(isset($iBirthMonth))) {
		$iBirthMonth = $_SESSION["iSesBirthMonth"];
	}
	
	if ($_SESSION["iSesBirthDay"] && $iBirthDay = '' && !(isset($iBirthDay))) {
		$iBirthDay = $_SESSION["iSesBirthDay"];
	}

	if ($iBirthDay == '' && $_SESSION["iSesBirthDay"] !='') {
		$iBirthDay = $_SESSION["iSesBirthDay"];
	}
	
	if ($iBirthMonth == '' && $_SESSION["iSesBirthMonth"] !='') {
		$iBirthMonth = $_SESSION["iSesBirthMonth"];
	}
	
	if ($iBirthYear == '' && $_SESSION["iSesBirthYear"] !='') {
		$iBirthYear = $_SESSION["iSesBirthYear"];
	}
	
	if ($_SESSION["sSesGender"] && $sGender = '' && !(isset($sGender))) {
		$sGender = $_SESSION["sSesGender"];
	}
	
	if ($sGender == '' && $_SESSION["sSesGender"] !='') {
		$sGender = $_SESSION["sSesGender"];
	}
}

/********************  End getting values from session  ****************/

$sFirst = stripslashes($sFirst);
$sLast = stripslashes($sLast);
$sAddress = stripslashes($sAddress);
$sAddress2 = stripslashes($sAddress2);
$sCity = stripslashes($sCity);

// prepare state options
$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value=''>";
while ($oStateRow = dbFetchObject($rStateResult)) {
	if (strtoupper($sState) == strtoupper($oStateRow->stateId)) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value=$oStateRow->stateId $sSelected>$oStateRow->state";
}

if ($rStateResult) {
	dbFreeResult($rStateResult);
}

// prepare salutation options
$sMrSelected = "";
$sMrsSelected = "";
$sMsSelected = "";
$sDrSelected = "";
$sOtherSelected = "";

switch ($sSalutation) {
	case "Mr.":
	$sMrSelected = "selected";
	break;
	case "Mrs.":
	$sMrsSelected = "selected";
	break;
	case "Ms.":
	$sMsSelected = "selected";
	break;
	case "Dr.":
	$sDrSelected = "selected";
	break;
	case "Other":
	$sOtherSelected = "selected";
	break;
}

$sSalutationOptions = "<option value=''>
					   <option value='Mr.' $sMrSelected>Mr.
					   <option value='Mrs.' $sMrsSelected>Mrs.
					   <option value='Ms.' $sMsSelected>Ms.
					   <option value='Dr.' $sDrSelected>Dr.
					   <option value='Other' $sOtherSelected>Other";

?>

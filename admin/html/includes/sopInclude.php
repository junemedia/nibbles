<?php

// get inbound vars to rename incoming vars
// place this code here outside of the loop if ($isset($_SESSION['aSesInboundQueryString') loop
// because incoming variable will be needed every time when user comes to the page again in case of error.
// all incoming variables also are attached in the url when redirected again to the same page with error message
// so again rename all the incoming variables to the mapping variable names
// except the variables user may change. i.e. user's own info should not be changed what it was in incoming url
// but it should be remain whatever user submitted through the form
// get the referer page name to go back to that page if any error in data
$sRefererScriptFileName = $_SERVER['HTTP_REFERER'];
// remove any queryString variables...
if (strstr($sRefererScriptFileName,"?")) {
	$sRefererScriptFileName = substr($sRefererScriptFileName, 0, strpos($sRefererScriptFileName, "?"));
}

// get the join list content to display
$sRedirectQuery = "SELECT * FROM   otPageDefinitions WHERE  definition = 'sopRedirectTo'";
$rRedirectResult = dbQuery($sRedirectQuery) ;
while ($oRedirectRow = dbFetchObject($rRedirectResult)) {
	$sRedirectTo = $oRedirectRow->definedValue;
}

// set single offer page's pageId
$iPageId = '9001';
$_SESSION["iSesPageId"] = $iPageId;

// session varible is set in sop.php as per offer mode
// if offer is in test mode, make the page as test mode
if ($_SESSION['sSesMode'] != 'T') {
	$sPageMode = 'A';
} else {
	$sPageMode = 'T';
}

$aQueryStringArray = explode("&", $sGblQueryString);
// set incoming variable every time only when sopPage.php and not sopPageSubmit.php
if(!strstr($_SERVER['PHP_SELF'], "sopSubmit.php")) {
	// get inbound vars to rename incoming vars
	$aInboundVarMap = explode(",",$sInboundVarMap);
	$_SESSION["aSesInboundQueryString"] = array();
	$j=0;
	$aQueryStringArray = explode("&", $sGblQueryString);
	for ($i=0; $i<count($aQueryStringArray); $i++) {
		$aKeyValuePair = explode("=",$aQueryStringArray[$i]);
		$key = $aKeyValuePair[0];
		$value = $aKeyValuePair[1];
		if ($key == 'src') {
			if (!(ctype_alnum($value))) { $value = '';}
			$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '".$value."';";
			$bSrcDefined = true;
		}

		if ($key != 'e' && $key != 'f' && $key != 'l' && $key != 'a1' && $key != 'a2' && $key != 'c' &&
		$key != 's' && $key != 'z' && $key != 'p' && $key != 'src' && $key != 'ss' &&
		 $key != 'g' && $key != 'iMenuId' && $key != 'iId' && $key != 'sMessage' && $key != 'PHPSESSID') {
			$_SESSION['aSesInboundQueryString'][$j]['key'] = $key;
			$_SESSION['aSesInboundQueryString'][$j]['value'] = $value;
			$j++;
		} else 	if ($key != 'iMenuId' && $key != 'iId') {
			// g variable should be available on page2 as javascript variable
			if (ltrim((rtrim($key))) == 'g') {
				$_SESSION['sSesInboundJavaScriptVars'] .= "\n var $key = '".$value."';";
			}
			$sPrepopcodes .= "$key=$value&";
		}
	}

	if ($sPrepopcodes != '') {
		$sPrepopcodes = substr($sPrepopcodes, 0, strlen($sPrepopcodes)-1);
	}

	if (!$bSrcDefined) {
		$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '';";
	}
}


for ($j=0; $j<count($_SESSION["aSesInboundQueryString"]); $j++) {
	if ($_SESSION["aSesInboundQueryString"][$j]['key'] != '') {	
		$sOutboundQueryString .= $_SESSION["aSesInboundQueryString"][$j]['key']."=".$_SESSION["aSesInboundQueryString"][$j]['value']."&";
	}
}
			
if ($sOutboundQueryString != '') {
	$sOutboundQueryString = substr($sOutboundQueryString,0, strlen($sOutboundQueryString)-1);
}

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
	$_SESSION["sSesRemoteIp"] = '';
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

if ($g) {
	$_SESSION["sSesGoTo"] = $g;
}
///************* replace user form variables now ********************/
// display values in case of errors if submitted before
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
	if (!(ctype_alpha($s))) { $s = '';}
	$sState = $s;
}

if ($z) {
	$sZip = $z;
}

if ($p) {
	$sPhone = $p;
}

if ($src) {
	if (!(ctype_alnum($src))) { $src = '';}
	$sSourceCode = $src;
	$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
}

if ($ss) {
	if (!(ctype_alnum($ss))) { $ss = '';}
	$sSubSourceCode = $ss;
}

if ($gn) {
	if (!(ctype_alpha($gn))) { $gn = '';}
	$sGender = $gn;
}

if ($by) {
	if (!(ctype_digit($by))) { $by = '';}
	$iBirthYear = $by;
}

if ($bd) {
	if (!(ctype_digit($bd))) { $bd = '';}
	$iBirthDay = $bd;
}

if ($bm) {
	if (!(ctype_digit($bm))) { $bm = '';}
	$iBirthMonth = $bm;
}

// If values are already set in session variables, prepopulate it
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

	if ($_SESSION["sSesPhone"] && $p =='' && !(isset($sPhone))) {
		$sPhone = $_SESSION["sSesPhone"];
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

	if ($_SESSION["sSesGender"] && $sGender = '' && !(isset($sGender))) {
		$sGender = $_SESSION["sSesGender"];
	}
}

$sFirst = stripslashes($sFirst);
$sLast = stripslashes($sLast);
$sAddress = stripslashes($sAddress);
$sAddress2 = stripslashes($sAddress2);

// prepare state options
$sStateQuery = "SELECT * FROM   states ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value=''>";
while ($oStateRow = dbFetchObject($rStateResult)) {
	if ($sState == $oStateRow->stateId) {
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
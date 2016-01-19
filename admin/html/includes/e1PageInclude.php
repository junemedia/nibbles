<?php

// get the referer page name to go back to that page if any error in data
$sRefererScriptFileName = $_SERVER['HTTP_REFERER'];
// remove any queryString variables...
if (strstr($sRefererScriptFileName,"?")) {
	$sRefererScriptFileName = substr($sRefererScriptFileName, 0, strpos($sRefererScriptFileName, "?"));
}

$sOutboundQueryString = $sGblQueryString;

// set incoming variable every time only when e1Page.php and not e1PageSubmit.php
if(!strstr($_SERVER['PHP_SELF'], "e1PageSubmit.php")) {
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
			if ((!ctype_alnum($value))) { $value = ''; }
			$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '".$value."';";
			$bSrcDefined = true;
		}

		if ($key != 'sMessage' && $key != 'PHPSESSID') {
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

if ($src) {
	if ((!ctype_alnum($src))) { $src = ''; }
	$sSourceCode = $src;
	$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
}

if ($ss) {
	if ((!ctype_alnum($ss))) { $ss = ''; }
	$sSubSourceCode = $ss;
}

if (session_id()) {
	if ($_SESSION["sSesSourceCode"] && $src == '' && !(isset($sSourceCode))) {
		$sSourceCode = $_SESSION["sSesSourceCode"];
		$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
	}

	if ($_SESSION["sSesSubSourceCode"] && $ss == '' && !(isset($sSubSourceCode))) {
		$sSubSourceCode = $_SESSION["sSesSubSourceCode"];
	}

	if (!(isset($_SESSION['sSesSourceCode'])) && $src != '') {
		$_SESSION['sSesSourceCode'] = $src;
	}

	if (!(isset($_SESSION['sSesSubSourceCode'])) && $ss != '') {
		$_SESSION['sSesSubSourceCode'] = $ss;
	}
}


?>

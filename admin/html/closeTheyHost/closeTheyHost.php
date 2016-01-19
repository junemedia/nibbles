<?php

include("../includes/paths.php");
session_start();

$sCloseTheyHostPageTitle = "Sign Up For Great Free Offers";


$sTempPrePop = '';
$sRemoteIp = trim($_SERVER['REMOTE_ADDR']);
$sCurSesId = session_id();
$sSourceCode = $_SESSION["sSesSourceCode"];
$sSubSourceCode = $_SESSION["sSesSubSourceCode"];

// Get Referer Url
$sRefererUrlDomain = $_SERVER['HTTP_REFERER'];
$aRefererUrl = (parse_url($sRefererUrlDomain));
$sRefererPath = $aRefererUrl['path'];
$sTempRefererFile = str_replace('/p/','',$sRefererPath);
$sTempRefererFile = str_replace('_2.php','',$sTempRefererFile);
$sTempRefererFile = str_replace('b.php','',$sTempRefererFile);
$sTempRefererFile = str_replace('b_2.php','',$sTempRefererFile);
$sTempRefererFile = str_replace('_c.php','',$sTempRefererFile);
$sTempRefererFile = str_replace('_c_2.php','',$sTempRefererFile);
$sPassOnSesId = "&PHPSESSID=".$sCurSesId;


if ($sRefererFile == '') { $sRefererFile = $sTempRefererFile; }

$sCloseTheyHostOfferCode = $_SESSION["aSesCloseTheyHostOffersChecked"][0];
if (count($_SESSION["aSesCloseTheyHostOffersChecked"]) > 1) {
	$sTheyHostContinueURL = "http://".$_SERVER['SERVER_NAME']."/closeTheyHost/closeTheyHost.php";
} else {
	$sTheyHostContinueURL = $_SESSION["sSesCloseTheyHostNextUrl"];
}


$sOtPageQuery = "SELECT * FROM otPages WHERE pageName = 'cth_$sCloseTheyHostOfferCode'";
$rOtPageResult = dbQuery($sOtPageQuery);
if (dbNumRows($rOtPageResult) > 0 ) {
	while($sOtPagesRow = dbFetchObject($rOtPageResult)) {
		$iPageId = $sOtPagesRow->id;
	}
}


$sCheckOfferQuery = "SELECT * FROM offers WHERE offerCode = '$sCloseTheyHostOfferCode' LIMIT 1";
$rCheckOfferResult = dbQuery($sCheckOfferQuery);
if (dbNumRows($rCheckOfferResult) > 0 ) {
	while($sOfferRow = dbFetchObject($rCheckOfferResult)) {
		$sCloseTheyHostUrl = $sOfferRow->closeTheyHostUrl;
		$sCloseTheyHostPassOnPrePop = $sOfferRow->closeTheyHostPrePop;
		$sCloseTheyHostVarMap = $sOfferRow->closeTheyHostVarMap;
		$sCloseTheyHeaderImage = $sOfferRow->closeTheyHostHeader;
	}
	
	if ($sCloseTheyHostPassOnPrePop == 'Y') {
		$sEmail = trim($_GET['e']);
		$sFirst = trim($_GET['f']);
		$sLast = trim($_GET['l']);
		$sAddress = trim($_GET['a1']);
		$sAddress2 = trim($_GET['a2']);
		$sCity = trim($_GET['c']);
		$sState = trim($_GET['s']);
		$sZip = trim($_GET['z']);
		$sPhone = trim($_GET['p']);
		$sPhoneNoDash = trim($_GET['pnd']);
	
		if($sEmail == '') { $sEmail = $_SESSION["sSesEmail"]; }
		if($sFirst == '') { $sFirst = $_SESSION["sSesFirst"]; }
		if($sLast == '') { $sLast = $_SESSION["sSesLast"]; }
		if($sAddress == '') { $sAddress = $_SESSION["sSesAddress"]; }
		if($sAddress2 == '') { $sAddress2 = $_SESSION["sSesAddress2"]; }
		if($sCity == '') { $sCity = $_SESSION["sSesCity"]; }
		if($sState == '') { $sState = $_SESSION["sSesState"]; }
		if($sZip == '') { $sZip = $_SESSION["sSesZip"]; }
		if($sPhone == '') { $sPhone = $_SESSION["sSesPhone"]; }
		if($sPhoneNoDash == '') { $sPhoneNoDash = $_SESSION["sSesPhoneNoDash"]; }
	
		if ($sCloseTheyHostVarMap !='') {
			$aPassOnCodeVarMap = explode(",",$sCloseTheyHostVarMap);
			for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
				$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
				if ($aKeyValuePair[0] == 'e') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sEmail."&"; }
				if ($aKeyValuePair[0] == 'f') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sFirst."&"; }
				if ($aKeyValuePair[0] == 'l') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sLast."&"; }
				if ($aKeyValuePair[0] == 'a1') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sAddress."&"; }
				if ($aKeyValuePair[0] == 'a2') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sAddress2."&"; }
				if ($aKeyValuePair[0] == 'c') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sCity."&"; }
				if ($aKeyValuePair[0] == 's') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sState."&"; }
				if ($aKeyValuePair[0] == 'z') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sZip."&"; }
				if ($aKeyValuePair[0] == 'p') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sPhone."&"; }
				if ($aKeyValuePair[0] == 'pnd') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$sPhoneNoDash."&"; }
			}
		} else {
			$sTempPrePop .= "e=".$sEmail."&";
			$sTempPrePop .= "f=".$sFirst."&";
			$sTempPrePop .= "l=".$sLast."&";
			$sTempPrePop .= "a1=".$sAddress."&";
			$sTempPrePop .= "a2=".$sAddress2."&";
			$sTempPrePop .= "c=".$sCity."&";
			$sTempPrePop .= "s=".$sState."&";
			$sTempPrePop .= "z=".$sZip."&";
			$sTempPrePop .= "p=".$sPhone."&";
			$sTempPrePop .= "pnd=".$sPhoneNoDash."&";
		}
		
		$sTempPrePop .= "sesId=".$sCurSesId."&";
		
		$sTempPrePop = substr($sTempPrePop,0, strlen($sTempPrePop)-1);
		$sCloseTheyHostUrl = $sCloseTheyHostUrl.'?'.$sTempPrePop;
	}
}

if ($sCloseTheyHeaderImage == '') {
	$sHeaderImageUrl = "http://www.popularliving.com/images/thHeaderDefault.gif";
} else {
	$sHeaderImageUrl = "http://www.popularliving.com/images/offers/$sCloseTheyHostOfferCode/$sCloseTheyHeaderImage";
}


// pageId and remoteIp are unique
$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate, sessionId, ipAddress, openDateTime)
				   VALUES('$iPageId', '$sSourceCode', '$sSubSourceCode',CURRENT_DATE, '$sCurSesId', '$sRemoteIp', now())";
$rPageStatResult = dbQuery($sPageStatQuery);


$sTempQueryString = trim($_GET['e'])."|";
$sTempQueryString .= trim($_GET['f'])."|";
$sTempQueryString .= trim($_GET['l'])."|";
$sTempQueryString .= trim($_GET['a1'])."|";
$sTempQueryString .= trim($_GET['a2'])."|";
$sTempQueryString .= trim($_GET['c'])."|";
$sTempQueryString .= trim($_GET['s'])."|";
$sTempQueryString .= trim($_GET['ss'])."|";
$sTempQueryString .= trim($_GET['z'])."|";
$sTempQueryString .= trim($_GET['p'])."|";
$sTempQueryString .= trim($_GET['pnd'])."|";
$sTempQueryString .= trim($_GET['ext'])."|";
$sTempQueryString .= trim($_GET['src'])."|";
$sTempQueryString .= trim($_GET['t'])."|";
$sTempQueryString .= $sRemoteIp;

if (strlen($sTempQueryString) > 14) {
	$sTempPassOnCode = "&sTempPassOn=$sTempQueryString";
} else {
	$sTempPassOnCode = '';
	$sTempQueryString = '';
}

?>

<html><head><title><?php echo $sCloseTheyHostPageTitle; ?></title></head>
<FRAMESET ROWS="30%,*,15%" frameborder=no border=no>
    <FRAME src="closeTheyHostHeader.php?closeTheyHostImage=<?php echo $sHeaderImageUrl; ?>&sCloseTheyHostContinueUrl=<?php echo $sTheyHostContinueURL; ?>&cthRefererPage=<?php echo $sRefererFile; ?><?php echo $sPassOnSesId; ?><?php echo $sTempPassOnCode; ?>" noresize scrolling="No">
	<FRAME src="<?php echo $sCloseTheyHostUrl; ?>" scrolling="auto">
    <FRAME src="closeTheyHostContinue.php?sCloseTheyHostContinueUrl=<?php echo $sTheyHostContinueURL; ?>&cthRefererPage=<?php echo $sRefererFile; ?><?php echo $sPassOnSesId; ?><?php echo $sTempPassOnCode; ?>" noresize scrolling="no">
</FRAMESET>
</html>

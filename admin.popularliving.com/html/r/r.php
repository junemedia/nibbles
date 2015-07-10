<?php

//This is script to record Redirects Entries


$bRedirectAllToPopularliving = true;

if ($bRedirectAllToPopularliving == true && $_SERVER['SERVER_NAME'] != 'www.popularliving.com') {
	header("Location:http://www.popularliving.com/r/r.php?".$_SERVER['QUERY_STRING']);
	exit;
}



include("../includes/paths.php");

session_start();

// set cookie with session id - cookie expires after 60 mins
// this cookie will be used on pixel scripts.  when client fires the pixel, we read this cookie
// and get session id.  with session id, we look into otData/userData table and get user's information
// that's the user information we use when we insert record into otData (insert into otData from pixel script).

setcookie("AmpereSessionId", session_id(), time()+3600, "/", '', 0);

if (isset($_COOKIE['AmpereSessionId'])) {
	$sCookieEnabledYesNo = 'Y';
} else {
	$sCookieEnabledYesNo = 'N';
}


// get all querystring that starts with q_ or Q_ and put them in sesssion
// so we can use that when we fire the pixel.
$aQueryString = explode("&",$_SERVER['QUERY_STRING']);
while (list($key,$val) = each($aQueryString)) {
	$aKeyVal = explode("=",$val);
	$aKeyVal[0] = strtoupper($aKeyVal[0]);
	if (strstr($aKeyVal[0],'Q_')) {
		$_SESSION[$aKeyVal[0]] = $aKeyVal[1];
	}
}

$_SESSION['sSesRevSourceCode'] = strrev($src);

//set default url, if url not found for the sourceCode
$url = $sGblDefaultUrl;

// Get varValue from vars table: blockAll, no, src, url.
$sCheckVarQuery = "SELECT * FROM vars
				WHERE system='foreignIp' 
				AND varName='blockForeignIp' LIMIT 1";
$rVarResult = dbQuery($sCheckVarQuery);
while($sVarRow = dbFetchObject($rVarResult)) {
	$sVarValue = $sVarRow->varValue;
}

// If varValue is blockAll, src, or url, then continue
if ($sVarValue != 'no') {
	// Get user's remoteIp
	$sRemoteIp = $_SERVER['REMOTE_ADDR'];
	//$sRemoteIp = '';

	// If remoteIp is blank, then set ipNum to 0.
	if ($sRemoteIp == '') {
		$iIpNum = 0;
	} else {
		// Split remoteIp and convert IP into numbers
		$iIpNum = split ("\.",$sRemoteIp);
		$iIpNum = ($iIpNum[3] + $iIpNum[2] * 256 + $iIpNum[1] * 256 * 256 + $iIpNum[0] * 256 * 256 * 256);
	}

	// Check if IP (Number) falls between From and To Range.
	$sIpCheckQuery = "SELECT * FROM  ipcountry
		 WHERE ipFROM <=$iIpNum AND ipTO >=$iIpNum
		 AND countrySHORT != 'US'";
	$sIpCheckResult = dbQuery($sIpCheckQuery);
	
	// IP is Foreign
	if (mysql_num_rows($sIpCheckResult) > 0 ) {
		while($sCountryRow = dbFetchObject($sIpCheckResult)) {
			$sCountry = $sCountryRow->countryLONG;
		}
		// If IP is foreign and varValue is blockAll, then exit.
		if ($sVarValue == 'blockAll') {
			$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
					VALUES (NOW(), \"$sRemoteIp\",\"$src\", \"$ss\", 'Y','', \"$sCountry\")";
			$rInsertLogResult = dbQuery($sInsertLogQuery);
			exit;
		} elseif ($sVarValue == 'src' || $sVarValue == 'url') {
			// If varValue is src or url, then check if entry exist in 
			// foreignIpHandling table with sourceCode
			$sGetQuery = "SELECT * FROM foreignIpHandling WHERE sourceCode='$src' LIMIT 1";
			$sGetResult = dbQuery($sGetQuery);
			
			// If entry found in foreignIpHandling table with a souceCode, get isBlock and redirectUrl.
			if (mysql_num_rows($sGetResult) > 0) {
				$sForeignRedirectUrl = '';
				while($sGetRow = dbFetchObject($sGetResult)) {
					$sIsBlock = $sGetRow->isBlock;
					$sTempRedirectUrl = $sGetRow->redirectUrl;
					
					if ($p) {
						$pa = substr($p, 0, 3);
						$pe = substr($p, 4, 3);
						$pnum = substr($p, 8,4);
					}
					
					
					$sTempRedirectUrl = str_replace("[email]",urlencode($e), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[first]",urlencode($f), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[last]",urlencode($l), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[address]",urlencode($a1), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[address2]",urlencode($a2), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[city]",urlencode($c), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[state]",urlencode($s), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[zip]",urlencode($z), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[phone]",urlencode($p), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[ipAddress]",urlencode($sRemoteIp), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[phone_areaCode]", urlencode($pa), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[phone_exchange]", urlencode($pe), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[phone_number]", urlencode($pnum), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[birthYear]", urlencode($by), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[birthMonth]", urlencode($bm), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[birthDay]", urlencode($bd), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[gender]", urlencode($gn), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[sourcecode]", urlencode($src), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[revSrc]", urlencode(strrev($src)), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[ss]", urlencode($ss), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[mm]", urlencode(date('m')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[dd]", urlencode(date('d')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[yyyy]", urlencode(date('Y')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[yy]", urlencode(date('y')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[hh]", urlencode(date('H')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[ii]", urlencode(date('i')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[sec]", urlencode(date('s')), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[gVariable]", urlencode($g), $sTempRedirectUrl);
					$sTempRedirectUrl = str_replace("[country]", urlencode($sCountry), $sTempRedirectUrl);
				}
				
				// If foreignIpHandling says block foreignIP and varValue is src, then exit.
				if ($sIsBlock == 'Y' && $sVarValue == 'src') {
					$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
						VALUES (NOW(), \"$sRemoteIp\",\"$src\", \"$ss\", 'Y','', \"$sCountry\")";
					$rInsertLogResult = dbQuery($sInsertLogQuery);
					exit;
				} elseif ($sIsBlock == 'N' && $sVarValue == 'src' && $sTempRedirectUrl != '') {
					// If foreignIpHandling says Do Not Block foreignIp AND
					// varValue is src AND redirectUrl is defined, then redirect user to this url.
					$sForeignRedirectUrl = $sTempRedirectUrl;
				} elseif ($sTempRedirectUrl != '' && $sVarValue == 'url') {
					// If redirectUrl is defined AND varValue is url, then redirect user to this url.
					$sForeignRedirectUrl = $sTempRedirectUrl;
				}
				if ($sForeignRedirectUrl !='') {
					$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
						VALUES (NOW(), \"$sRemoteIp\",\"$src\", \"$ss\", 'N',\"$sForeignRedirectUrl\", \"$sCountry\")";
					$rInsertLogResult = dbQuery($sInsertLogQuery);
				}
			}
		}
	}
}




$i = 0;
$_SESSION['bSesFlowUrl'] = false;
$sGetFlowsQuery = "SELECT * FROM flows WHERE sourceCode='$src' LIMIT 1";
$rGetFlowsResult = dbQuery($sGetFlowsQuery);
while($sFlowRow = dbFetchObject($rGetFlowsResult)) {
	$_SESSION['aScFlowHeader'] = array("$sFlowRow->header","$sFlowRow->header2","$sFlowRow->header3");
	$_SESSION['aScFlowHeaderTemp'] = array("$sFlowRow->header","$sFlowRow->header2","$sFlowRow->header3");
	$_SESSION['sScFlowFooter'] = $sFlowRow->footer;

	$sGetFlowUrl = "SELECT * FROM flowDetails WHERE flowId='$sFlowRow->id' ORDER BY flowOrder ASC";
	$rGetFlowUrl = dbQuery($sGetFlowUrl);
	while($sFlowUrlRow = dbFetchObject($rGetFlowUrl)) {
		if (strstr($sFlowUrlRow->url,"www.popularliving.com/p/")) {
			if ($sFlowUrlRow->showSkip == 'Y') {
				if (strstr($sFlowUrlRow->url,"?")) {
					$sTempShowSkip = "&sShowSkip=Y";
				} else {
					$sTempShowSkip = "?sShowSkip=Y";
				}
			} else {
				$sTempShowSkip = '';
			}
			$sTempUrl = $sFlowUrlRow->url.$sTempShowSkip;
			$aFlowUrl['flowUrl'][$i] = str_replace("www.popularliving.com", $_SERVER['SERVER_NAME'], $sTempUrl);
			$_SESSION['bSesFlowUrl'] = true;
			$i++;
		}
	}
	$_SESSION['aSesFlowUrl'] = $aFlowUrl['flowUrl'];
}


// All user to edit userForm on 2nd page questions for c page if src is listed on this table.
$sCheckIfWeAllowCPageEditQuery = "SELECT * FROM cPageEditAllowed WHERE sourceCode='$src'";
$rCheckIfWeAllowCPageEditResult = dbQuery($sCheckIfWeAllowCPageEditQuery);
if (mysql_num_rows($rCheckIfWeAllowCPageEditResult) > 0) {
	$_SESSION['sSesScFlowAllowEditUserForm'] = true;
} else {
	$_SESSION['sSesScFlowAllowEditUserForm'] = false;
}


// Get the url of this sourcecode
$query = "SELECT url, displayInFrame
		  FROM   links
		  WHERE  sourceCode = '$src'";
$result = dbQuery($query);
while($row = dbFetchObject($result)) {
	if ($_SESSION['bSesFlowUrl'] == true) {
		$url = $_SESSION['aSesFlowUrl'][0];
	} elseif ($sForeignRedirectUrl != '') {
		$url = $sForeignRedirectUrl;
	} else {
		$url = $row->url;
	}
	
	// get current server name
	$sCurrSite = "http://".$_SERVER['SERVER_NAME'];
	reset($aGblSiteNames);
	reset($aGblSites);
	while (list($key,$val) = each($aGblSiteNames)) {
		if ($sCurrSite == $val) {
			$sCurrServer = $aGblSiteNames[$key];
		}
	}

	// replace www.popularliving.com with current box url, e.g. web1.popularliving.com
	if ($_SESSION['bSesFlowUrl'] == false) {
		if (strstr($url,"www.popularliving.com/p/")) {
			$url = str_replace("www.popularliving.com", $_SERVER['SERVER_NAME'], $url);
		}
	}

	$displayInFrame = $row->displayInFrame;

	$redirect = true;
	/******* get custom frame content here to get width or height attribute from body tag to use it in frameset tag  ********/
	if (substr($displayInFrame,0,6) == "custom") {
		$customQuery = "SELECT *
						FROM   campaignCustomFrames
						WHERE  sourceCode = '$src'";
		$customResult = dbQuery($customQuery);
		while ($customRow = dbFetchObject($customResult)) {
			$varFrameContent = $customRow->frameContent;
		}
	}
	/********  End of getting frame content  **********/
}


/********  If sourceCode is not from BD, check offerCodes if it matches to  ********/
if (dbNumRows($result) == 0) {
	// Get the url of this offerCode
	$query = "SELECT offerCode, url,  displayInFrame
			  FROM   edOffers
			  WHERE  offerCode = '$src'";
	$result = dbQuery($query);
	while($row = dbFetchObject($result)) {
		if ($_SESSION['bSesFlowUrl'] == true) {
			$url = $_SESSION['aSesFlowUrl'][0];
		} elseif ($sForeignRedirectUrl != '') {
			$url = $sForeignRedirectUrl;
		} else {
			$url = $row->url;
		}

		$displayInFrame = $row->displayInFrame;
		$redirectUrl = $sGblOfferRedirectsPath."?src=$row->offerCode";
		$offer = true;
	}
}
/*********  End of checking offerCodes  ********/


$urlComponents = explode("&",$_SERVER['QUERY_STRING']);

// Append current url Components to the new Url
$newUrlComponents = "";
for ($i=0; $i<count($urlComponents); $i++) {
	if (trim($urlComponents[$i]) != '') {
		$findSS = explode("=",$urlComponents[$i]);

		if($redirect == true) {
			$newUrlComponents .= $urlComponents[$i]."&";
		} else {
			// DON'T APPEND SRC IN OFFER URL
			if ($findSS[0] != "src")
			$newUrlComponents .= $urlComponents[$i]."&";
		}
	}
}

if ($newUrlComponents != '') {
	$newUrlComponents = substr($newUrlComponents,0,strlen($newUrlComponents)-1);
}

/*
//sSiteId helps to maintain sessions. 
if(!isset($sSiteId) || $sSiteId == ''){
	//$asdf = '';
	$sSiteId = '';
	foreach($aGblSites as $k => $v){
		if($_SERVER['SERVER_ADDR'] == $v){
			$sSiteId = $k;
		}
	}
	if($sSiteId == ''){
		//then our server IP isn't one that the config file knows.
		$sErrorBody = "r.php doesn't recognize it's own \$_SERVER['SERVER_ADDR']!
		the server address is ".$_SERVER['SERVER_ADDR']."
		
		config's \$aGblSites is ".print_r($aGblSites,true)."
		
		attempting to redirect source code $src";
		//mail('bbevis@amperemedia.com','r.php error: unknown server IP',$sErrorBody);
	}
	//mail('bbevis@amperemedia.com','some testing.',$sSiteId.'is sSiteId');
}
$newUrlComponents = "sSiteId=$sSiteId&$newUrlComponents";
*/

/*******  Attach incoming querystring variables into redirecting url  ********/


if($redirect == true) {
	if(strstr($url,"?")) {
		$newUrl = $url."&".$newUrlComponents;
	} else {
		$newUrl = $url."?".$newUrlComponents;
	}
} else {
	if($newUrlComponents != '') {
		if (strstr($url,"www.recipe4living.com")) {
			$newUrl = $url;
		} else {
			if (strstr($url,"?")) {
				$newUrl = $url."&".$newUrlComponents;
			} else {
				$newUrl = $url."?".$newUrlComponents;
			}
		}
	} else {
		$newUrl = $url;
	}
}

$newUrl = urlencode($newUrl);

if ($redirect == true) {
	$redirectQuery = "INSERT INTO bdRedirectsTracking (clickDate, sourceCode, subSourceCode, ipAddress, cookieEnabled)
				  VALUES (CURRENT_DATE, \"$src\", \"$ss\", '".$_SERVER['REMOTE_ADDR']."', '$sCookieEnabledYesNo')";
	$result = dbQuery($redirectQuery);
} else if($offer == true) {
	$redirectQuery = "INSERT INTO edOfferRedirectsTracking (clickDate, offerCode, subsource, IPAddress, cookieEnabled)
				  VALUES (CURRENT_DATE, \"$src\", \"$ss\", '".$_SERVER['REMOTE_ADDR']."', '$sCookieEnabledYesNo')";
	$result = dbQuery($redirectQuery);
}

// Foreward to asp page if has redirect .asp link
if (strstr($redirectUrl, ".asp")) {
	header("Location:$redirectUrl");
}


/*****  Get the frame html name if redirect page should be framed  ******/
if ($displayInFrame == 'top') {

	if ($redirect == true) {
		$varFrameName = "redirectTopFrameHtml.html";
	} else {
		$varFrameName = "offerTopFrameHtml.html";
	}
} else if ($displayInFrame == 'bottom') {

	if ($redirect == true) {
		$varFrameName = "redirectBottomFrameHtml.html";
	} else {
		$varFrameName = "offerBottomFrameHtml.html";
	}
} else if ($displayInFrame == 'left') {

	if ($redirect == true) {
		$varFrameName = "redirectLeftFrameHtml.html";
	} else {
		$varFrameName = "offerLeftFrameHtml.html";
	}
} else if ($displayInFrame == 'right') {

	if ($redirect == true) {
		$varFrameName = "redirectRightFrameHtml.html";
	} else {
		$varFrameName = "offerRightFrameHtml.html";
	}
} else if ($displayInFrame == 'customTop' || $displayInFrame == 'customLeft'|| $displayInFrame == 'customRight' || $displayInFrame == 'customBottom') {

	if ($redirect == true) {
		$varFrameName = "redirectCustomFrameHtml.php?src=$src";
	}
}
/******  End of getting frame html name  *********/


/*******  Get the height and width from the frame html content  **********/

$varQuery = "SELECT varValue
			 FROM   vars
			 WHERE  varName = '".substr($varFrameName,0,strlen($varFrameName)-5)."'";

$varResult = dbQuery($varQuery);
while ($row = dbFetchObject($varResult)) {
	$varFrameContent = $row->varValue;
}
// Get the html width to use in Frameset specification
$bodyStart = explode("<body",$varFrameContent);
$bodyLine = $bodyStart[1];
$bodyEnd = explode(">",$bodyLine);
$bodyTag = $bodyEnd[0];
// set which attribute(width or hight) should be used from body tag to split frame
if($displayInFrame == 'top' || $displayInFrame == 'bottom' || $displayInFrame == "customTop" || $displayInFrame == "customBottom") {
	$partitionBy = 'height';
} else {
	$partitionBy = 'width';
}
// get width/height of the document from bodyTag
$bodyAttr = explode(" ",$bodyTag);
for ($i = 0; $i < count($bodyAttr); $i++) {
	//set default frameWidth;
	$frameWidth = "100";

	if (stristr($bodyAttr[$i], $partitionBy)) {
		$widthAttr = explode("=", $bodyAttr[$i]);
		$frameWidth = ereg_replace("\"","", $widthAttr[1]);
		$frameWidth = ereg_replace("'","", $frameWidth);
	}
}

/*******  End of getting height and width from the frame html content  ********/


/*********  Specify the two urls for two frames  *********/

switch ($displayInFrame) {
	case "top":
	$frameSetInfo = "<frameset rows=\"$frameWidth, *\" cols=\"1*\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	$frame1Src = $varFrameName;
	break;
	case "bottom":
	$frameSetInfo = "<frameset rows=\"*, $frameWidth\" cols=\"1*\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	break;
	case "left":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"$frameWidth, *\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	$frame1Src = $varFrameName;
	break;
	case "right":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"*, $frameWidth\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	break;
	case "customTop":
	$frameSetInfo = "<frameset rows=\"$frameWidth, *\" cols=\"1*\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	$frame1Src = $varFrameName;
	break;
	case "customLeft":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"$frameWidth, *\">";
	$frame2Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	$frame1Src = $varFrameName;
	break;
	case "customRight":
	$frameSetInfo = "<frameset rows=\"1*\" cols=\"*, $frameWidth\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	break;
	case "customBottom":
	$frameSetInfo = "<frameset rows=\"*, $frameWidth\" cols=\"1*\">";
	$frame2Src = $varFrameName;
	$frame1Src = "rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id();
	break;

}
/********  End of specifying two frames url  *********/


/*******  Display the frameset or redirect to rFrame.php directly if don't have to use frame  *********/
if ($frameSetInfo) {
	echo "<html>
				<head><title>No title</title></head>		
				$frameSetInfo
				<frame name=\"frame1\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" src=\"$frame1Src\">	

				<frame name=\"frame2\" scrolling=\"auto\" marginwidth=\"10\" marginheight=\"14\" src=\"$frame2Src\">
	
			<noframes>
			<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#0000FF\" vlink=\"#800080\" alink=\"#FF0000\">

<p>You need a browser that supports frame to view this page.</p>	
</body>
</noframes>
</frameset>
</html>";
} else {
	if ($_SESSION['bSesFlowUrl'] == true) {
		$_SESSION['sSesPreviousFlowUrl'] = $_SESSION['aSesFlowUrl'][0];
		unset($_SESSION['aSesFlowUrl'][0]);
		$temp_array = array_values($_SESSION['aSesFlowUrl']);
		$_SESSION['aSesFlowUrl'] = $temp_array;
		$_SESSION['bSesFlowOnlyOneUrlSet'] = true;
	} else {
		$_SESSION['bSesFlowOnlyOneUrlSet'] = false;
	}

	header("Location:rFrame.php?src=$src&newUrl=$newUrl&PHPSESSID=".session_id());
}

/********  End of displaying frameset  ********/
?>



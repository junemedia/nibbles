<?php
// Shree Ganeshay Namah...
// Hare Pritam...

include_once("../includes/paths.php");
include_once('session_handlers.php');
include_once("libs/function.php");
include_once("libs/fields.php");
include_once("credit.php");
include_once("libs/pixel.php");

// IF SESSION IS NOT SET, THEN START NEW SESSION
//	GET ALL QUERY STRING, RECORD REDIRECT
if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
	session_start();
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
	
	setcookie("AmpereSessionId", session_id(), time()+3600);

	$sDefaultSrc = "amptb060706090";
	// temp var....don't remove this.
	$_SESSION['sTempVar'] = microtime();
	$_SESSION['sSesRemoteIp'] = trim($_SERVER['REMOTE_ADDR']);
	//$_SESSION['sSesRemoteIp'] = '65.182.9.200';	//this is a foreign IP for testing. It's from Trinidad.
	$_SESSION['sSesServerIp'] = trim($_SERVER['SERVER_ADDR']);
	// if remote ip is banned, we just exit because
	// this user will not be able to take any offers or signup for any news letters.
	if (isBannedIp($_SESSION['sSesRemoteIp'])) {
		exit;
	}

	// For future use....
	$_SESSION['sSesQueryString'] = $_SERVER['QUERY_STRING'];

	// Get all data from querystring and trim it.
	$sSourceCode = (!(ctype_alnum(trim($_GET['src']))) ? '' : trim($_GET['src']));
	$sSubSourceCode = (!(ctype_alnum(trim($_GET['ss']))) ? '' : trim($_GET['ss']));
	
	$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
		
	$sEmail = (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", trim($_GET['e']))) ? '' : trim($_GET['e']);
	$sFirst = (!ctype_alpha(trim($_GET['f'])) ? '' : trim($_GET['f']));
	$sLast = (!eregi("^[-A-Z[:space:]'\.]*$", trim($_GET['l'])) ? '' : trim($_GET['l']));
	$sGender = ((strtoupper(trim($_GET['gn'])) == 'M' || strtoupper(trim($_GET['gn'])) == 'F') ? strtoupper(trim($_GET['gn'])) : '');

	$iBirthDay = '';
	if (strlen(trim($_GET['bd'])) == 2 && ctype_digit(trim($_GET['bd'])) && trim($_GET['bd']) <= 31) {
		$iBirthDay = trim($_GET['bd']);
	}
	
	$iBirthMonth = '';
	if (strlen(trim($_GET['bm'])) == 2 && ctype_digit(trim($_GET['bm'])) && trim($_GET['bm']) <= 12) {
		$iBirthMonth = trim($_GET['bm']);
	}
	
	$iBirthYear = '';
	if (strlen(trim($_GET['by'])) == 4 && ctype_digit(trim($_GET['by'])) && trim($_GET['by']) < date('Y')) {
		$iBirthYear = trim($_GET['by']);
	}

	$sPhone = (!(ereg("^[0-9-]+$", trim($_GET['p']))) ? '' : trim($_GET['p']));
	$sPhoneExtension = (ctype_digit(trim($_GET['ext'])) ? trim($_GET['ext']) : '');


	$sPhone_areaCode = '';
	if (ctype_digit(trim($_GET['pa'])) && strlen(trim($_GET['pa'])) == 3 && trim($_GET['pa']) >= 200) {
		$sPhone_areaCode = trim($_GET['pa']);
	}

	$sPhone_exchange = '';
	if (ctype_digit(trim($_GET['pe'])) && strlen(trim($_GET['pe'])) == 3 && trim($_GET['pe']) >= 200) {
		$sPhone_exchange = trim($_GET['pe']);
	}
	
	$sPhone_number = '';
	if (ctype_digit(trim($_GET['pnum'])) && strlen(trim($_GET['pnum'])) == 4) {
		$sPhone_number = trim($_GET['pnum']);
	}
	
	
	if ($sPhone) {
		if ($sPhone_areaCode =='') {
			$sPhone_areaCode = substr($sPhone, 0, 3);
		}
		if ($sPhone_exchange =='') {
			$sPhone_exchange = substr($sPhone, 4, 3);
		}
		if ($sPhone_number =='') {
			$sPhone_number = substr($sPhone, 8,4);
		}
	}
	
	
	$sAddress = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]{1,}$", trim($_GET['a1'])) ? '' : trim($_GET['a1']));
	$sAddress2 = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]*$", trim($_GET['a2'])) ? '' : trim($_GET['a2']));
	$sCity = (!ereg( "^[a-zA-Z0-9 \'\x2e\-\’\`\&]{1,}$", trim($_GET['c'])) ? '' : trim($_GET['c']));
	$sState = (!ereg("^[A-Z]{2,2}$", strtoupper(trim($_GET['s']))) ? '' : strtoupper(trim($_GET['s'])));
	$sZip = (!ereg("^[0-9-]{5,}$", strtoupper(trim($_GET['z']))) ? '' : strtoupper(trim($_GET['z'])));
	
	
	// if g variable is available, put it in session.
	$_SESSION["sSesRedirectUrl"] = '';
	if (trim($_GET['g']) != '') {
		$_SESSION["sSesRedirectUrl"] = trim($_GET['g']);
	}
	
	$_SESSION['sSesSalutation'] = '';
	$_SESSION['sSesFirst'] = '';
	$_SESSION['sSesLast'] = '';
	$_SESSION['sSesEmail'] = '';
	$_SESSION['sSesAddress'] = $sAddress;
	$_SESSION['sSesAddress2'] = $sAddress2;
	$_SESSION['sSesCity'] = $sCity;
	$_SESSION['sSesState'] = $sState;
	$_SESSION['sSesZip'] = $sZip;
	$_SESSION['iSesBirthYear'] = '';
	$_SESSION['iSesBirthMonth'] = '';
	$_SESSION['iSesBirthDay'] = '';
	$_SESSION['sSesBirthDate'] = '';
	$_SESSION['sSesGender'] = $sGender;
	$_SESSION['sSesPhone'] = '';
	$_SESSION['sSesPhoneNoDash'] = '';
	$_SESSION['sSesPhoneAreaCode'] = '';
	$_SESSION['sSesPhoneExchange'] = '';
	$_SESSION['sSesPhoneNumber'] = '';
	$_SESSION['sSesPhoneExtension'] = $sPhoneExtension;
	$_SESSION['sSesJavaScriptPrePop'] = '';
	$_SESSION['sShowSkipSubmitCth'] = '';
	$_SESSION['sSesShowNonRevOffers'] = '';
	$_SESSION['sSesPixelOnEPage'] = '';
	$_SESSION['sSesPixelOnRegPage'] = '';
	$_SESSION['iSesPageId'] = 0;
	$_SESSION['aSesPage2Offers'] = array();
	$_SESSION['sSesAllowEditUserForm'] = false;
	$aOffersChecked = array();
	$_SESSION['bSesWinPop'] = true;
	$_SESSION['bSesAbdPop'] = true;
	$_SESSION['iSesBPTotalOfferShown'] = 0;
	$_SESSION['iSesSPNSTotalOfferShown'] = 0;
	$_SESSION['iSesSPSTotalOfferShown'] = 0;
	$_SESSION['iSesOPTotalOfferShown'] = 0;
	$_SESSION['iSesFRPTotalOfferShown'] = 0;
	$_SESSION['iSesRPTotalOfferShown'] = 0;
	$_SESSION['sSesStandardPopContent'] = '';
	$_SESSION['aSesStandardPopPages'] = array();
	$_SESSION['aSesCloseTheyHostOffers'] = array();
	$_SESSION['bPage2Submit'] = true;
	$_SESSION['aArrayExcOffersByRange'] = array();
	$_SESSION['aSesMutExcOffersToExcludeByPageRange'] = array();
	$_SESSION['aSesExitPopUrl'] = array();
	$_SESSION['aSesExitPopUpUnder'] = array();
	$_SESSION['sSesExitPopContent'] = '';
	$_SESSION['bSesExitOpened'] = false;
	$_SESSION['aOfferTakenForCookie'] = array();
	$_SESSION['sSesFrame3rdPartyUrl'] = '';
	$_SESSION['i3rdPartyFrameHeight'] = '';
	$_SESSION['sSesRedirect3rdPartyUrl'] = '';
	$_SESSION['iSesSourceCodeId'] = '';
	$_SESSION['sSesTemplateType'] = '';
	$_SESSION['sSesLastTemplateType'] = '';
	$_SESSION['aSesWinManagerPopUrl'] = array();
	$_SESSION['aSesWinManagerPopUpUnder'] = array();
	$_SESSION['aSesWinManagerTimeDelayed'] = array();
	$_SESSION['bSesWinManagerOpened'] = false;
	$_SESSION['aSesAbandonedPopUrl'] = array();
	$_SESSION['aSesAbandonedPopUpUnder'] = array();
	$_SESSION['aSesAbandonedTimeDelayed'] = array();
	$_SESSION['bSesAbandonOpened'] = false;
	$_SESSION['aDefaultTitle'] = array();
	$_SESSION['sSesCampaignCSS'] = '';
	$aCampaignPageText = array();
	$_SESSION['aSesCampaignPageText'] = array();
	$_SESSION['aSesDontShowOfferAgain'] = array();
	$_SESSION['aShowOfferAgain'] = array();
	$_SESSION['sSesUniqueUserPerSite'] = false;
	$_SESSION['bdRedirectsTrackingInsertId'] = '';
	
	// this is used by offer caps section
	switch (date('D')) {
		case "Sun":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-0 day"));
			break;
		case "Mon":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-1 day"));
			break;
		case "Tue":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-2 day"));
			break;
		case "Wed":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-3 day"));
			break;
		case "Thu":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-4 day"));
			break;
		case "Fri":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-5 day"));
			break;
		case "Sat":
			$_SESSION['sSesLastSunday'] = strftime("%Y-%m-%d", strtotime("-6 day"));
			break;
	}
	

	// alternate offers bg color - spatel
	$aColorArray = array();
	$output = array();
	$_SESSION['aSesOfferBgColor'] = array();
	$iPg = 0;
	
	// if no source code, make it default source code.
	if ($sSourceCode == '') {
		$sSourceCode = $sDefaultSrc;
	}
	
			
	// Get varValue from vars table: blockAll, no, src, url.
	$sCheckVarQuery = "SELECT * FROM vars
					WHERE system='foreignIp' 
					AND varName='blockForeignIp' LIMIT 1";
	$rVarResult = dbQuery($sCheckVarQuery);
	echo dbError();
	while($sVarRow = dbFetchObject($rVarResult)) {
		$sVarValue = $sVarRow->varValue;
	}

	// If varValue is blockAll, src, or url, then continue
	if ($sVarValue != 'no') {
		// If remoteIp is blank, then set ipNum to 0.
		if ($_SESSION['sSesRemoteIp'] == '') {
			$iIpNum = 0;
		} else {
			// Split remoteIp and convert IP into numbers
			$iIpNum = split ("\.",$_SESSION['sSesRemoteIp']);
			$iIpNum = ($iIpNum[3] + $iIpNum[2] * 256 + $iIpNum[1] * 256 * 256 + $iIpNum[0] * 256 * 256 * 256);
		}
		
		if (!ctype_digit(trim($iIpNum))) {
			$iIpNum = 0;
		}

		// Check if IP (Number) falls between From and To Range.
		$sIpCheckQuery = "SELECT * FROM  ipcountry
			 WHERE ipFROM <=$iIpNum AND ipTO >=$iIpNum
			 AND countrySHORT != 'US'";
		$sIpCheckResult = dbQuery($sIpCheckQuery);
		echo dbError();
	
		// IP is Foreign
		if (dbNumRows($sIpCheckResult) > 0 ) {
			
			//echo "this is a foreign ip";flush();ob_flush();
			while($sCountryRow = dbFetchObject($sIpCheckResult)) {
				$sCountry = $sCountryRow->countryLONG;
			}
						
			$sGetLinksData = "SELECT foreignIPTracking FROM links
					WHERE sourceCode = '$sSourceCode' LIMIT 1";
			$rGetLinksResult = dbQuery($sGetLinksData);
			$oLinksRow = dbFetchObject($rGetLinksResult);
			
			$sGetQuery = "SELECT * FROM foreignIpHandling WHERE sourceCode='$sSourceCode' LIMIT 1";
			$rGetResult = dbQuery($sGetQuery);
			$oFIPH = dbFetchObject($rGetResult);
			echo dbError();
			
			$redirectURL = ($oFIPH->redirectUrl ? $oFIPH->redirectUrl : $sGblForeignIPRedirectURL);

			$redirectURL = str_replace("[email]",urlencode($sEmail), $redirectURL);
			$redirectURL = str_replace("[first]",urlencode($sFirst), $redirectURL);
			$redirectURL = str_replace("[last]",urlencode($sLast), $redirectURL);
			$redirectURL = str_replace("[address]",urlencode($sAddress), $redirectURL);
			$redirectURL = str_replace("[address2]",urlencode($sAddress2), $redirectURL);
			$redirectURL = str_replace("[city]",urlencode($sCity), $redirectURL);
			$redirectURL = str_replace("[state]",urlencode($sState), $redirectURL);
			$redirectURL = str_replace("[zip]",urlencode($sZip), $redirectURL);
			$redirectURL = str_replace("[phone]",urlencode($sPhone), $redirectURL);
			$redirectURL = str_replace("[ipAddress]",urlencode($_SESSION['sSesRemoteIp']), $redirectURL);
			$redirectURL = str_replace("[phone_areaCode]", urlencode($sPhone_areaCode), $redirectURL);
			$redirectURL = str_replace("[phone_exchange]", urlencode($sPhone_exchange), $redirectURL);
			$redirectURL = str_replace("[phone_number]", urlencode($sPhone_number), $redirectURL);
			$redirectURL = str_replace("[birthYear]", urlencode($iBirthYear), $redirectURL);
			$redirectURL = str_replace("[birthMonth]", urlencode($iBirthMonth), $redirectURL);
			$redirectURL = str_replace("[birthDay]", urlencode($iBirthDay), $redirectURL);
			$redirectURL = str_replace("[gender]", urlencode($sGender), $redirectURL);
			$redirectURL = str_replace("[sourcecode]", urlencode($sSourceCode), $redirectURL);
			$redirectURL = str_replace("[revSrc]", urlencode($_SESSION['sSesRevSourceCode']), $redirectURL);
			$redirectURL = str_replace("[ss]", urlencode($sSubSourceCode), $redirectURL);
			$redirectURL = str_replace("[mm]", urlencode(date('m')), $redirectURL);
			$redirectURL = str_replace("[dd]", urlencode(date('d')), $redirectURL);
			$redirectURL = str_replace("[yyyy]", urlencode(date('Y')), $redirectURL);
			$redirectURL = str_replace("[yy]", urlencode(date('y')), $redirectURL);
			$redirectURL = str_replace("[hh]", urlencode(date('H')), $redirectURL);
			$redirectURL = str_replace("[ii]", urlencode(date('i')), $redirectURL);
			$redirectURL = str_replace("[sec]", urlencode(date('s')), $redirectURL);
			$redirectURL = str_replace("[gVariable]", urlencode($_SESSION["sSesRedirectUrl"]), $redirectURL);
			$redirectURL = str_replace("[country]", urlencode($sCountry), $redirectURL);

			if(($oLinksRow->foreignIPTracking == 'block')||($oLinksRow->foreignIPTracking == 'redirect')||($oLinksRow->foreignIPTracking == 'log')){
				$sCurrentDateTime = date('Y-m-d H:i:s');
				$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
							VALUES ('$sCurrentDateTime', '".$_SESSION['sSesRemoteIp']."',\"$sSourceCode\", \"$sSubSourceCode\",
							'".($oLinksRow->foreignIPTracking == 'block' ? 'Y' : 'N')."',
							'".($oLinksRow->foreignIPTracking == 'redirect' ? $redirectURL : '')."',
							\"$sCountry\")";
				$rInsertLogResult = dbQuery($sInsertLogQuery);
				
				if($oLinksRow->foreignIPTracking == 'redirect'){
					header("Location:$redirectURL");
					exit;
				} else if($oLinksRow->foreignIPTracking == 'block'){
					exit;
				}
			} else if ($sVarValue == 'blockAll') {
				$sCurrentDateTime = date('Y-m-d H:i:s');
				$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
						VALUES ('$sCurrentDateTime', '".$_SESSION['sSesRemoteIp']."',\"$sSourceCode\",\"$sSubSourceCode\",'Y','', \"$sCountry\")";
				$rInsertLogResult = dbQuery($sInsertLogQuery);
				echo dbError();
				exit;
			} elseif ($sVarValue == 'src' || $sVarValue == 'url') {
				// If varValue is src or url, then check if entry exist in 
				// foreignIpHandling table with sourceCode
				$sGetQuery = "SELECT * FROM foreignIpHandling WHERE sourceCode='$sSourceCode' LIMIT 1";
				$sGetResult = dbQuery($sGetQuery);
				
				// If entry found in foreignIpHandling table with a souceCode, get isBlock and redirectUrl.
				if (dbNumRows($sGetResult) > 0) {
					$sForeignRedirectUrl = '';
					while($oFIPH = dbFetchObject($sGetResult)){
						$sIsBlock = $oFIPH->isBlock;
						$sTempRedirectUrl = $redirectURL;
					}
					
					// If foreignIpHandling says block foreignIP and varValue is src, then exit.
					if ($sIsBlock == 'Y' && $sVarValue == 'src') {
						$sCurrentDateTime = date('Y-m-d H:i:s');
						$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
							VALUES ('$sCurrentDateTime', '".$_SESSION['sSesRemoteIp']."',\"$sSourceCode\",\"$sSubSourceCode\",'Y','', \"$sCountry\")";
						$rInsertLogResult = dbQuery($sInsertLogQuery);
						echo dbError();
						exit;
					} elseif ($sIsBlock == 'N' && $sVarValue == 'src' && $sTempRedirectUrl != '') {
						// If foreignIpHandling says Do Not Block foreignIp AND
						// varValue is src AND redirectUrl is defined, then redirect user to this url.
						$sForeignRedirectUrl = $redirectURL;
					} elseif ($sTempRedirectUrl != '' && $sVarValue == 'url') {
						// If redirectUrl is defined AND varValue is url, then redirect user to this url.
						$sForeignRedirectUrl = $redirectURL;
					}
					if ($sForeignRedirectUrl !='') {
						$sCurrentDateTime = date('Y-m-d H:i:s');
						$sInsertLogQuery = "INSERT INTO foreignIpLog (dateTimeLogged,remoteIp,sourceCode,subSourceCode,block,redirectUrl,country) 
							VALUES ('$sCurrentDateTime', '".$_SESSION['sSesRemoteIp']."',\"$sSourceCode\",\"$sSubSourceCode\",'N',\"$sForeignRedirectUrl\", \"$sCountry\")";
						$rInsertLogResult = dbQuery($sInsertLogQuery);
						echo dbError();
						
						// redirect users
						header("Location:$sForeignRedirectUrl");
						exit;
					}
				}
			}
		}
	}
	
	
	// All user to edit userForm on 2nd page questions for c page if src is listed on this table.
	$sAllowCPageEditQuery = "SELECT * FROM cPageEditAllowed WHERE sourceCode='$sSourceCode'";
	$rAllowCPageEdit = dbQuery($sAllowCPageEditQuery);
	echo dbError();
	if (dbNumRows($rAllowCPageEdit) > 0) {
		$_SESSION['sSesAllowEditUserForm'] = true;
	}
	
	$sTemp11 = "<input type='hidden' name='PHPSESSID' value='".session_id()."'>";
	
	// after validating source and subsource, put it in session.
	// if source invalid, make it default source and put it in session
	$_SESSION['sSesSourceCode'] = $sSourceCode;
	$_SESSION['sSesSubSourceCode'] = $sSubSourceCode;
	$_SESSION['sSesHiddenSourceCode'] = "<input type='hidden' name='hiddenSrc' value='$sSourceCode'>".$sTemp11;
	
	

	// Check for valid states
	if ($sState !='') {
		$sCheckState = "SELECT * FROM states WHERE stateId = '$sState'";
		$rStateResult = dbQuery($sCheckState);
		if (dbNumRows($rStateResult) == 0) {
			$sState = '';
		}
	}
	
	// if birth date is invalid, clear it.
	if($iBirthMonth && $iBirthDay && $iBirthYear){
		if (!(checkdate($iBirthMonth,$iBirthDay,$iBirthYear))) {
			$iBirthDay = '';
			$iBirthMonth = '';
			$iBirthYear = '';
		}
	}
	
	// if cookies are disabled, get sessionId and add it to querystring.
	if (!(isset($_COOKIE['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
		$sSessionId = session_id();
	}
	
	// if domain in email address is banned, clear out email var.
	// and let the user enter new one on registration form.
	if (isBannedDomain($sEmail, $sDomainName)) {
		$sEmail = '';
	}
	
	// If email is valid email, set it in session
	if (validateEmail($sEmail)) {
		$_SESSION['sSesEmail'] = $sEmail;
	}
	
	// If first name is valid, set it in session
	if (validateName($sFirst)) {
		$_SESSION['sSesFirst'] = $sFirst;
	}
	
	// If last name is valid, set it in session
	if (validateName($sLast)) {
		$_SESSION['sSesLast'] = $sLast;
	}
	
	// If gender is M or F, then set it in session
	if ($sGender == 'M' || $sGender == 'F') {
		$_SESSION['sSesGender'] = $sGender;
		$_SESSION['sSesTargetGender'] = $sGender;
	}

	if ($iBirthYear !='') {
		$_SESSION['iSesBirthYear'] = $iBirthYear;
		$_SESSION['sSesTargetYear'] = $iBirthYear;
	}
		
	if ($iBirthMonth !='') {
		$_SESSION['iSesBirthMonth'] = $iBirthMonth;
	}
	
	if ($iBirthDay !='') {
		$_SESSION['iSesBirthDay'] = $iBirthDay;
	}

	if ($iBirthYear !='' && $iBirthMonth !='' && $iBirthDay !='' ) {
		$_SESSION['sSesBirthDate'] = $iBirthYear."-".$iBirthMonth."-".$iBirthDay;
	}
	
	// If phone number is not valid for zipcode entered, make it null.
	if ($sPhone !='' && $sZip !='') {
		$iPhoneZipDistance = getDistance($sPhone,$sZip,$sSourceCode);
		if ($iPhoneZipDistance > 250) {
			exceedsMaxDistance($sPhone, $sZip, 250, $sSourceCode);
			$sPhone = '';
		}
	}
	
	if (strlen($sPhone) == 10) {
		if (strlen(substr($sPhone, 0, 3)) == 3 && ctype_digit(substr($sPhone, 0, 3)) && substr($sPhone, 0, 3) >= 200) {
			$sPhone_areaCode = substr($sPhone,0,3);
		}
	
		if (strlen(substr($sPhone, 3, 3)) == 3 && ctype_digit(substr($sPhone, 3, 3)) && substr($sPhone, 3, 3) >= 200) {
			$sPhone_exchange = substr($sPhone,3,3);
		}
		
		if (strlen(substr($sPhone, 6,4)) == 4 && ctype_digit(substr($sPhone, 6,4))) {
			$sPhone_number = substr($sPhone,6,4);
		}
	} else if (strlen($sPhone) == 12) {
		if (strlen(substr($sPhone, 0, 3)) == 3 && ctype_digit(substr($sPhone, 0, 3)) && substr($sPhone, 0, 3) >= 200) {
			$sPhone_areaCode = substr($sPhone, 0, 3);
		}
		
		if (strlen(substr($sPhone, 4, 3)) == 3 && ctype_digit(substr($sPhone, 4, 3)) && substr($sPhone, 4, 3) >= 200) {
			$sPhone_exchange = substr($sPhone, 4, 3);
		}
		
		if (strlen(substr($sPhone, 8,4)) == 4 && ctype_digit(substr($sPhone, 8,4))) {
			$sPhone_number = substr($sPhone, 8,4);
		}
	}
	
	if ($sPhone !='') {
		if ((validatePhone($sPhone_areaCode, $sPhone_exchange, $sPhone_number, '', $sState, $sSourceCode))) {
			$_SESSION['sSesPhone'] = $sPhone_areaCode.'-'.$sPhone_exchange.'-'.$sPhone_number;
			$_SESSION['sSesPhoneNoDash'] = $sPhone_areaCode.$sPhone_exchange.$sPhone_number;
			$_SESSION['sSesPhoneAreaCode'] = $sPhone_areaCode;
			$_SESSION['sSesPhoneExchange'] = $sPhone_exchange;
			$_SESSION['sSesPhoneNumber'] = $sPhone_number;
			$_SESSION['sSesTargetExchange'] = $sPhone_exchange;
		}
	}

	$pFactory = new PixelFactory();
	$_SESSION['pFactory'] = $pFactory;
	// Get all data from table and put it in session
	$sGetLinksData = "SELECT * FROM links WHERE sourceCode = '$sSourceCode' LIMIT 1";
	$rGetLinksResult = dbQuery($sGetLinksData);
	echo dbError();
	while($oLinksRow = dbFetchObject($rGetLinksResult)) {
		$_SESSION['iSesSourceCodeId'] = $oLinksRow->id;
		$aOfferColor = explode(',',$oLinksRow->offerColor);
		$iLinksId = $oLinksRow->id;
		$_SESSION['iSesLinkId'] = $iLinksId;
		$iSiteId = $oLinksRow->siteId;
		$iCampaignId = $oLinksRow->campaignId;
		$_SESSION['iSesCampaignId'] = $oLinksRow->campaignId;
		$iFlowId = $oLinksRow->flowId;
		$iIoId = $oLinksRow->ioId;
		$iWhereToGoId = $oLinksRow->whereToGoId;
		$_SESSION['sSesShowEmailCapturePage'] = $oLinksRow->emailCapture;
		$_SESSION['sSesShowSkipButton'] = $oLinksRow->showSkip;
		$_SESSION['iSesFlowId'] = $iFlowId;
		
		
		if ($oLinksRow->showNonRevOffers == 'Y') {
			// no filter because we want to show non-rev offers
			$_SESSION['sSesShowNonRevOffers'] = '';
		} else {
			// set filter to exclude non-rev offers
			$_SESSION['sSesShowNonRevOffers'] = " AND    offers.isNonRevenue != '1' ";
		}
		
		$_SESSION['sSesEmailCapType'] = $oLinksRow->emailCapType;
		$_SESSION['sSesMemberCapType'] = $oLinksRow->memberCapType;
		$_SESSION['sSesCapType'] = $oLinksRow->captureType;
	
		$_SESSION['sSesStopAllPop'] = $oLinksRow->stopAllPopups;
		$_SESSION['sSesDisableStandardPop'] = $oLinksRow->disableStandardPop;
		$_SESSION['sSesDisableExitPop'] = $oLinksRow->disableExitPop;
		$_SESSION['sSesDisableAbandonedPop'] = $oLinksRow->disableAbandonedPop;
		$_SESSION['sSesDisableWinManagerPop'] = $oLinksRow->disableWinManagerPop;
		$_SESSION['aDefaultTitle'] = explode("\n",$oLinksRow->defaultTitle);
		
		if ($oLinksRow->isPixelEnable == 'Y') {
			$sPixelUrl = $oLinksRow->pixelUrl;
			$sPixelUrl = str_replace("[ss]", $_SESSION['sSesSubSourceCode'], $sPixelUrl);
			if ($oLinksRow->pixelLocation == 'E') {
				$_SESSION['sSesPixelOnEPage'] = $sPixelUrl;
			} else {
				$_SESSION['sSesPixelOnRegPage'] = $sPixelUrl;
			}
		}
		
		
		$_SESSION['sSesRecipeIngredients'] = '';
		$_SESSION['sSesRecipeTitle'] = '';
		$_SESSION['sSesRecipeDirections'] = '';
		if (($oLinksRow->recipe4Living == 'Y') && ($oLinksRow->recipeId != '')) {
			$iRecipeId = $oLinksRow->recipeId;
			$sGetRecipeSQL = "SELECT * FROM recipes WHERE id = '$iRecipeId'";
			$rGetRecipe = dbQuery($sGetRecipeSQL);
			$oRecipe = dbFetchObject($rGetRecipe);
			
			$_SESSION['sSesRecipeTitle'] = $oRecipe->header;
			$_SESSION['sSesRecipeDirections'] = $oRecipe->directions;
			
			$sGetIngredientsSQL = "SELECT * FROM recipeIngredients WHERE recipeId = '$iRecipeId'";
			$rGetIngredients = dbQuery($sGetIngredientsSQL);
			$aIngredients = array();
			$aStyles = array();
			while($oRow = dbFetchObject($rGetIngredients)){
				array_push($aIngredients, $oRow->amount." ".$oRow->material);
				array_push($aStyles, $oRow->style);
			}
			
			$sIngredientsList = '';
			$iCountIngredients = count($aIngredients);
			for($i=0;$i<$iCountIngredients;$i++){
				$sIngredientsList .= "<div style=\"".$aStyles[$i]."\">".$aIngredients[$i]."</div>";
			}
			$_SESSION['sSesRecipeIngredients'] = $sIngredientsList;
		}
	}
	
	
	$sGetSource1 = "SELECT domainName FROM links, domains
				where links.domainId = domains.id
				and sourceCode='$sSourceCode'";
	$rSourceResult1 = dbQuery($sGetSource1);
	while($oLinksRow1 = dbFetchObject($rSourceResult1)) {
		$_SESSION['sSesDomain'] = $oLinksRow1->domainName;
	}
	
	if (isset($_COOKIE['tempSesId'])) {
		$sTempSesId = trim($_COOKIE['tempSesId']);
	} else {
		// do not remove below 3 lines of code.  let the script set the cookie.
		setcookie("tempSesId", session_id(), time()+3600, "/", $_SESSION['sSesDomain'], 0);
		setcookie("tempSesId", session_id(), time()+3600, "/", '.popularliving.com', 0);
		setcookie("tempSesId", session_id(), time()+3600, "/", '.3400cookie.com', 0);
		setcookie("tempSesId", session_id(), time()+3600, "/", '', 0);
		$sTempSesId = session_id();
	}
	
	// set cookie with session id - cookie expires after 60 mins
	// do not remove below 3 lines of code.  let the script set the cookie.
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", $_SESSION['sSesDomain'], 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '.popularliving.com', 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '.3400cookie.com', 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '', 0);

	if (isset($_COOKIE['AmpereSessionId'])) {
		$sCookieEnabledYesNo = 'Y';
		$_SESSION['updateBdRedirectsTracking'] = false;
	} else {
		$sCookieEnabledYesNo = 'N';
		$_SESSION['updateBdRedirectsTracking'] = true;
	}
	
	// Check if src is a valid source code.  If invalid, make it default source
	$sGetSource = "SELECT * FROM links WHERE sourceCode='$sSourceCode'";
	$rSourceResult = dbQuery($sGetSource);
	echo dbError();
	if (dbNumRows($rSourceResult) == 0) {
		$query2 = "SELECT id FROM edOffers
			  WHERE  offerCode = '$sSourceCode'";
		$result2 = dbQuery($query2);
		if (dbNumRows($result2) > 0) {
			$redirectQuery1 = "INSERT INTO edOfferRedirectsTracking (clickDate, offerCode, subsource, IPAddress, cookieEnabled)
				  VALUES (CURRENT_DATE, '$sSourceCode', '$sSubSourceCode', '".$_SESSION['sSesRemoteIp']."', '$sCookieEnabledYesNo')";
			$result1 = dbQuery($redirectQuery1);
		}
		$sSourceCode = $sDefaultSrc;
	} else {
		$sRedirectInsert = "INSERT INTO bdRedirectsTracking (clickDate, sourceCode, subSourceCode, ipAddress, cookieEnabled)
					  VALUES (CURRENT_DATE, '$sSourceCode', '$sSubSourceCode', '".$_SESSION['sSesRemoteIp']."', '$sCookieEnabledYesNo')";
		$rRedirectResult = dbQuery($sRedirectInsert);
		$_SESSION['bdRedirectsTrackingInsertId'] = mysql_insert_id();
		echo dbError();
	}


	// Record Nibbles II clicks
	$sClicksInsert = "INSERT INTO nibbles_reporting.nibbles2Clicks (dateTime, sourceCode, remoteIp, sessionId)
			  VALUES (NOW(), \"$sSourceCode\", '".$_SESSION['sSesRemoteIp']."', '$sTempSesId')";
	$rClicksResult = dbQuery($sClicksInsert);
	echo dbError();	
	
	
	// create an array with offer background color.  alternate offer bg color - spatel
	for ($ix=0; $ix<count($aOfferColor); $ix++) {
		$output = array_slice($aOfferColor, $ix, 2);
		$aColorArray[$iPg] = array($output[0],$output[1]);
		$ix++;
		$iPg++;
	}
	$_SESSION['aSesOfferBgColor'] = $aColorArray;
	unset($aColorArray);
	unset($output);
	

	// get number of pages we have in this flow
	$sCountFlowDetails = "SELECT * FROM flowDetails
						WHERE flowId = '$iFlowId'
						ORDER BY flowOrder ASC";
	$rCtResult = dbQuery($sCountFlowDetails);
	$iTotalPagesInCurrFlow = dbNumRows($rCtResult);
	
	// ----- START ----- //
	// pageId 999 is used to exclude offer from entire flow or globally
	// prevent two offers of the same category from showing up on the same page
	$_SESSION['aSesMutExcOffersByCat'] = array();
	$sGetRulesCatQuery = "SELECT * FROM rules 
					WHERE mutExcCat = 'Y'
					AND (global = 'Y' OR linkId = '$iLinksId' OR flowId = '$iFlowId')
					AND catOffers !=''
					AND pageNo != 999
					ORDER BY orderId DESC";
	$sRulesCatResult = dbQuery($sGetRulesCatQuery);
	while($oRulesCatRow = dbFetchObject($sRulesCatResult)) {
		//	if by Page Range:
		if (strstr($oRulesCatRow->sMutExcRange,'range')) {
			$iStart = $oRulesCatRow->pageNo;
			$iEnd = trim(substr($oRulesCatRow->sMutExcRange,5,2)) + $iStart - 1;
			for ($itemp=$iStart; $itemp < $iEnd; $itemp++) {
				if (!(is_array($aMutExcOffersByCat[$itemp]))) {
					$aMutExcOffersByCat[$itemp] = array();
				}
				array_push($aMutExcOffersByCat[$itemp],$oRulesCatRow->catOffers);
			}
		} else if ($oRulesCatRow->sMutExcRange == 'flow') {
			for ($iTempCount = 0; $iTempCount < $iTotalPagesInCurrFlow; $iTempCount++) {
				if (!(is_array($aMutExcOffersByCat[$iTempCount]))) {
					$aMutExcOffersByCat[$iTempCount] = array();
				}
				array_push($aMutExcOffersByCat[$iTempCount],$oRulesCatRow->catOffers);
			}
		} else {
			if (!(is_array($aMutExcOffersByCat[$oRulesCatRow->pageNo]))) {
				$aMutExcOffersByCat[$oRulesCatRow->pageNo] = array();
			}
			array_push($aMutExcOffersByCat[$oRulesCatRow->pageNo],$oRulesCatRow->catOffers);
		}
	}
	$_SESSION['aSesMutExcOffersByCat'] = $aMutExcOffersByCat;
	// ----- ENDS ----- //
	
	// read the cookie and get all offerCodes.  These offers were taken in the past.
	// All offers taken by users, put it in offers to exclude so we don't show that offer again.
	$sFilterTakenOffer = '';
	if (isset($_COOKIE["OfferTakenInCookie"])) {
		$aOfferTakenInCookie = array();
		$aOfferTakenInCookie = explode(",", $_COOKIE["OfferTakenInCookie"]);
		if (count($aOfferTakenInCookie) > 0) {
			foreach ($aOfferTakenInCookie as $sOfferTemp) {
				if ($sOfferTemp !='') {
					$sFilterTakenOffer .= "'$sOfferTemp',";
				}
			}
			$sFilterTakenOffer = substr($sFilterTakenOffer,0,strlen($sFilterTakenOffer)-1);
			$sFilterTakenOffer = " AND offers.offerCode NOT IN ($sFilterTakenOffer) ";
		}
	}
	
	$sGetAllActiveAndLiveOffers = "SELECT distinct offers.offerCode
				FROM   offers,offerCompanies, categoryMap
				WHERE  offers.companyId = offerCompanies.id
				AND	categoryMap.offerCode = offers.offerCode
				AND	offers.isLive = '1'
				AND    offers.mode = 'A'
				AND    offerCompanies.creditStatus = 'ok'
				$sFilterTakenOffer";
	$rActiveAndLiveResult = dbQuery($sGetAllActiveAndLiveOffers);
	$sShowActiveLiveOkOffers = "'',";
	while($oLiveOffersRow = dbFetchObject($rActiveAndLiveResult)) {
		$sShowActiveLiveOkOffers .= "'$oLiveOffersRow->offerCode',";
	}
	$sShowActiveLiveOkOffers = substr($sShowActiveLiveOkOffers,0,strlen($sShowActiveLiveOkOffers)-1);
	$sShowActiveLiveOkOffers = " AND offerCode IN ($sShowActiveLiveOkOffers) ";
	

	##################################################################
	#
	#	Begin the Rules Section. We get Link, Flow, and Global rules
	#	Global rules are overridden by Flow and Link rules, Flow rules
	#	by Link rules, and Links rules by NOTHING!
	#	
	#	Rules can include or exclude an offer (or a category of offers) 
	#	in a flow, or on a certain page of a flow, or in a certain 
	#	position of a page.
	#
	##################################################################
	// pageId 999 is used to exclude offer from entire flow or globally
	$sGetLinkRules = "SELECT * FROM rules 
				WHERE linkId = '$iLinksId' 
				AND mutExcCat='N' 
				$sShowActiveLiveOkOffers 
				AND pageNo != 999
				ORDER BY orderId DESC";
	$rLinksRulesResult = dbQuery($sGetLinkRules);
	$aLinksExcludePages = array();
	$aLinksExcludeEverywhere = array();
	$aLinksIncludePages = array();
	$aLinksIncludeEverywhere = array();
	echo dbError();
	$aLinksOffersExclude = array();
	$aLinksOffersInclude = array();
	$aLinksCategoriesExclude = array();
	$aLinksCategoriesInclude = array();
	$aOfferLocations = array();
	$aPrecheckOffers = array();
	while($oLinksRulesRow = dbFetchObject($rLinksRulesResult)) {
		if($oLinksRulesRow->offerCode != ''){
			if ($oLinksRulesRow->offerIncExc == 'I') {
				if($oLinksRulesRow->offerCode != '' && $oLinksRulesRow->precheck == 'Y' && !in_array($oLinksRulesRow->offerCode, $aPrecheckOffers)){
					array_push($aPrecheckOffers, $oLinksRulesRow->offerCode);
				}
				array_push($aLinksOffersInclude, $oLinksRulesRow->offerCode);
				if (!is_array($aOfferLocations[$oLinksRulesRow->pageNo])) {
					$aOfferLocations[$oLinksRulesRow->pageNo] = array();
				}
				if (!is_array($aLinksIncludePages[$oLinksRulesRow->pageNo])) {
					$aLinksIncludePages[$oLinksRulesRow->pageNo] = array();
				}
				if($oLinksRulesRow->offerPosition || "$oLinksRulesRow->offerPosition" === "0"){
					$aOfferLocations[$oLinksRulesRow->pageNo][$oLinksRulesRow->offerPosition] = $oLinksRulesRow->offerCode;
				} else if(!in_array($oLinksRulesRow->offerCode,$aLinksIncludePages[$oLinksRulesRow->pageNo] )) {
					array_push($aLinksIncludePages[$oLinksRulesRow->pageNo], $oLinksRulesRow->offerCode);
				}
			} else if($oLinksRulesRow->offerIncExc == 'E'){
				array_push($aLinksOffersExclude, $oLinksRulesRow->offerCode);
				if($oLinksRulesRow->pageNo){
					if(!is_array($aLinksExcludePages[$oLinksRulesRow->pageNo]))
						$aLinksExcludePages[$oLinksRulesRow->pageNo] = array();
					if(!in_array($oLinksRulesRow->offerCode,$aLinksExcludePages[$oLinksRulesRow->pageNo] ))
						array_push($aLinksExcludePages[$oLinksRulesRow->pageNo], $oLinksRulesRow->offerCode);
				} else {
					if(!in_array($oLinksRulesRow->offerCode,$aLinksExcludeEverywhere))
						array_push($aLinksExcludeEverywhere,  $oLinksRulesRow->offerCode);
				}
			}
		} else if($oLinksRulesRow->catOffers != ''){
			if($oLinksRulesRow->offerIncExc == 'I'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oLinksRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aLinksOffersInclude, $oGetCodesFromCats->offerCode);
					if($oLinksRulesRow->pageNo){
						if(!is_array($aLinksIncludePages[$oLinksRulesRow->pageNo]))
							$aLinksIncludePages[$oLinksRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode, $aLinksIncludePages[$oLinksRulesRow->pageNo]))
							array_push($aLinksIncludePages[$oLinksRulesRow->pageNo],$oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oLinksRulesRow->offerCode,$aLinksExcludeEverywhere))
							array_push($aLinksExcludeEverywhere,  $oLinksRulesRow->offerCode);
					}
					
					if($oLinksRulesRow->precheck == 'Y' && !in_array($oGetCodesFromCats->offerCode, $aPrecheckOffers)){
						array_push($aPrecheckOffers, $oGetCodesFromCats->offerCode);
					}
				}
				array_push($aLinksCategoriesInclude, $oLinksRulesRow->catOffers);
			} else if($oLinksRulesRow->offerIncExc == 'E'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oLinksRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aLinksOffersExclude, $oLinksRulesRow->offerCode);
					if($oLinksRulesRow->pageNo){
						if(!is_array($aLinksExcludePages[$oLinksRulesRow->pageNo]))
							$aLinksExcludePages[$oLinksRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode,$aLinksExcludePages[$oLinksRulesRow->pageNo] ))
							array_push($aLinksExcludePages[$oLinksRulesRow->pageNo], $oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oGetCodesFromCats->offerCode,$aLinksExcludeEverywhere))
							array_push($aLinksExcludeEverywhere,  $oGetCodesFromCats->offerCode);
					}
				}
				array_push($aLinksCategoriesExclude, $oLinksRulesRow->catOffers);
			}
		}
	}
	
	
	// pageId 999 is used to exclude offer from entire flow or globally
	//get the flow rules after the link rules
	$sGetFlowRules = "SELECT * FROM rules 
				WHERE flowId = '$iFlowId' 
				AND mutExcCat='N' 
				$sShowActiveLiveOkOffers 
				AND pageNo != 999 
				ORDER BY orderId DESC"; 
	$rFlowRulesResult = dbQuery($sGetFlowRules);
	$aFlowExcludePages = array();
	$aFlowExcludeEverywhere = array();
	$aFlowIncludePages = array();
	$aFlowIncludeEverywhere = array();
	echo dbError();
	$aFlowOffersExclude = array();
	$aFlowOffersInclude = array();
	$aFlowCategoriesExclude = array();
	$aFlowCategoriesInclude = array();
	while($oFlowRulesRow = dbFetchObject($rFlowRulesResult)) {
		if($oFlowRulesRow->offerCode != ''){
			if($oFlowRulesRow->offerIncExc == 'I'){
				if($oFlowRulesRow->offerCode != '' && $oFlowRulesRow->precheck == 'Y' && !in_array($oFlowRulesRow->offerCode, $aPrecheckOffers)){
					array_push($aPrecheckOffers, $oFlowRulesRow->offerCode);
				}
				array_push($aFlowOffersInclude, $oFlowRulesRow->offerCode);
				if(!is_array($aOfferLocations[$oFlowRulesRow->pageNo])){
					$aOfferLocations[$oFlowRulesRow->pageNo] = array();
				} 
				if(!is_array($aFlowIncludePages[$oFlowRulesRow->pageNo])){
					$aFlowIncludePages[$oFlowRulesRow->pageNo] = array();
				}
				if($oFlowRulesRow->offerPosition || "$oFlowRulesRow->offerPosition" === "0"){
					$aOfferLocations[$oFlowRulesRow->pageNo][$oFlowRulesRow->offerPosition] = $oFlowRulesRow->offerCode;
				} else if(!in_array($oFlowRulesRow->offerCode,$aFlowIncludePages[$oFlowRulesRow->pageNo] )) {
					array_push($aFlowIncludePages[$oFlowRulesRow->pageNo], $oFlowRulesRow->offerCode);
				}
			} else if($oFlowRulesRow->offerIncExc == 'E'){
				array_push($aFlowOffersExclude, $oFlowRulesRow->offerCode);
				if($oFlowRulesRow->pageNo){
					if(!is_array($aFlowExcludePages[$oFlowRulesRow->pageNo]))
						$aFlowExcludePages[$oFlowRulesRow->pageNo] = array();
					if(!in_array($oFlowRulesRow->offerCode,$aFlowExcludePages[$oFlowRulesRow->pageNo] ))
						array_push($aFlowExcludePages[$oFlowRulesRow->pageNo], $oFlowRulesRow->offerCode);
				} else {
					if(!in_array($oFlowRulesRow->offerCode,$aFlowExcludeEverywhere))
						array_push($aFlowExcludeEverywhere,  $oFlowRulesRow->offerCode);
				}
			}
		} else if($oFlowRulesRow->catOffers != ''){
			if($oFlowRulesRow->offerIncExc == 'I'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oFlowRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aFlowOffersInclude, $oGetCodesFromCats->offerCode);
					if($oFlowRulesRow->pageNo){
						if(!is_array($aFlowIncludePages[$oFlowRulesRow->pageNo]))
							$aFlowIncludePages[$oFlowRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode, $aFlowIncludePages[$oFlowRulesRow->pageNo]))
							array_push($aFlowIncludePages[$oFlowRulesRow->pageNo],$oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oFlowRulesRow->offerCode,$aFlowExcludeEverywhere))
							array_push($aFlowExcludeEverywhere,  $oFlowRulesRow->offerCode);
					}
					
					if($oFlowRulesRow->precheck == 'Y' && !in_array($oGetCodesFromCats->offerCode, $aPrecheckOffers)){
						array_push($aPrecheckOffers, $oGetCodesFromCats->offerCode);
					}
				}
				array_push($aFlowCategoriesInclude, $oFlowRulesRow->catOffers);
			} else if($oFlowRulesRow->offerIncExc == 'E'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oFlowRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aFlowOffersExclude, $oFlowRulesRow->offerCode);
					if($oFlowRulesRow->pageNo){
						if(!is_array($aFlowExcludePages[$oFlowRulesRow->pageNo]))
							$aFlowExcludePages[$oFlowRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode,$aFlowExcludePages[$oFlowRulesRow->pageNo] ))
							array_push($aFlowExcludePages[$oFlowRulesRow->pageNo], $oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oGetCodesFromCats->offerCode,$aFlowExcludeEverywhere))
							array_push($aFlowExcludeEverywhere,  $oGetCodesFromCats->offerCode);
					}
				}
				array_push($aFlowCategoriesExclude, $oFlowRulesRow->catOffers);
			}
		}
	}
	
	// pageId 999 is used to exclude offer from entire flow or globally
	//finally, get global rules.
	$sGetGlobalRules = "SELECT * FROM rules 
						WHERE global = 'Y' 
						AND mutExcCat='N' 
						AND pageNo != 999 
						ORDER BY orderId DESC";
	$rGlobalRulesResult = dbQuery($sGetGlobalRules);
	$aGlobalExcludePages = array();
	$aGlobalExcludeEverywhere = array();
	$aGlobalIncludePages = array();
	$aGlobalIncludeEverywhere = array();
	echo dbError();
	$aGlobalOffersExclude = array();
	$aGlobalOffersInclude = array();
	$aGlobalCategoriesExclude = array();
	$aGlobalCategoriesInclude = array();
	while($oGlobalRulesRow = dbFetchObject($rGlobalRulesResult)) {
		if($oGlobalRulesRow->offerCode != ''){
			if($oGlobalRulesRow->offerIncExc == 'I'){
				if($oGlobalRulesRow->offerCode != '' && $oGlobalRulesRow->precheck == 'Y' && !in_array($oGlobalRulesRow->offerCode, $aPrecheckOffers)){
					array_push($aPrecheckOffers, $oGlobalRulesRow->offerCode);
				}
				array_push($aGlobalOffersInclude, $oGlobalRulesRow->offerCode);
				if(!is_array($aOfferLocations[$oGlobalRulesRow->pageNo])){
					$aOfferLocations[$oGlobalRulesRow->pageNo] = array();
				} 
				if(!is_array($aGlobalIncludePages[$oGlobalRulesRow->pageNo])){
					$aGlobalIncludePages[$oGlobalRulesRow->pageNo] = array();
				}
				if($oGlobalRulesRow->offerPosition || "$oGlobalRulesRow->offerPosition" === "0"){
					$aOfferLocations[$oGlobalRulesRow->pageNo][$oGlobalRulesRow->offerPosition] = $oGlobalRulesRow->offerCode;
				} else if(!in_array($oGlobalRulesRow->offerCode,$aGlobalIncludePages[$oGlobalRulesRow->pageNo] )) {
					array_push($aGlobalIncludePages[$oGlobalRulesRow->pageNo], $oGlobalRulesRow->offerCode);
				}
			} else if($oGlobalRulesRow->offerIncExc == 'E') {
				array_push($aGlobalOffersExclude, $oGlobalRulesRow->offerCode);
				if($oGlobalRulesRow->pageNo){
					if(!is_array($aGlobalExcludePages[$oGlobalRulesRow->pageNo]))
						$aGlobalExcludePages[$oGlobalRulesRow->pageNo] = array();
					if(!in_array($oGlobalRulesRow->offerCode,$aGlobalExcludePages[$oGlobalRulesRow->pageNo] ))
						array_push($aGlobalExcludePages[$oGlobalRulesRow->pageNo], $oGlobalRulesRow->offerCode);
				} else {
					if(!in_array($oGlobalRulesRow->offerCode,$aGlobalExcludeEverywhere))
						array_push($aGlobalExcludeEverywhere,  $oGlobalRulesRow->offerCode);
				}
			}
		} else if($oGlobalRulesRow->catOffers != ''){
			if($oGlobalRulesRow->offerIncExc == 'I'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oGlobalRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aGlobalOffersInclude, $oGetCodesFromCats->offerCode);
					if($oGlobalRulesRow->pageNo){
						if(!is_array($aGlobalIncludePages[$oGlobalRulesRow->pageNo]))
							$aGlobalIncludePages[$oGlobalRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode, $aGlobalIncludePages[$oGlobalRulesRow->pageNo]))
							array_push($aGlobalIncludePages[$oGlobalRulesRow->pageNo],$oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oGlobalRulesRow->offerCode,$aGlobalExcludeEverywhere))
							array_push($aGlobalExcludeEverywhere,  $oGlobalRulesRow->offerCode);
					}
					
					if($oGlobalRulesRow->precheck == 'Y' && !in_array($oGetCodesFromCats->offerCode, $aPrecheckOffers)){
						array_push($aPrecheckOffers, $oGetCodesFromCats->offerCode);
					}
				}
				
				array_push($aGlobalCategoriesInclude, $oGlobalRulesRow->catOffers);
			} else if($oGlobalRulesRow->offerIncExc == 'E'){
				$sGetCodesFromCatsSQL = "SELECT offerCode FROM categoryMap WHERE categoryId = '$oGlobalRulesRow->catOffers'";
				$rGetCodesFromCats = dbQuery($sGetCodesFromCatsSQL);
				while($oGetCodesFromCats = dbFetchObject($rGetCodesFromCats)){
					array_push($aGlobalOffersExclude, $oGlobalRulesRow->offerCode);
					if($oGlobalRulesRow->pageNo){
						if(!is_array($aGlobalExcludePages[$oGlobalRulesRow->pageNo]))
							$aGlobalExcludePages[$oGlobalRulesRow->pageNo] = array();
						if(!in_array($oGetCodesFromCats->offerCode,$aGlobalExcludePages[$oGlobalRulesRow->pageNo] ))
							array_push($aGlobalExcludePages[$oGlobalRulesRow->pageNo], $oGetCodesFromCats->offerCode);
					} else {
						if(!in_array($oGetCodesFromCats->offerCode,$aGlobalExcludeEverywhere))
							array_push($aGlobalExcludeEverywhere,  $oGetCodesFromCats->offerCode);
					}
				}
				array_push($aGlobalCategoriesExclude, $oGlobalRulesRow->catOffers);
			}
		}
	}
	

	//  so, now we try to merge the link and flow offers into two arrays:
	//  $aIncludeOffers, and $aExcludeOffers
	//  as well as another array that will tell us where to put the offers 
	//  that we're including.
	//  $aOfferLocations, keyed to the page number, and location.
	//  So, if we're in flow 5, and we're at page 3, then while we're
	//  making the list of offers to show, we do this:
	//  if(is_array($aOfferLocations[$thisPageId])){...}
	//  For a particular position on the page, we do:
	// 	if($aOfferLocations[$thisPageId][$thisOfferLocation] != ''){
	//  	then value is an offer code, and it should go in this order on the page
	// 	}

	$aIncludeOffers = array();
	$aExcludeOffers = array();
	$aIncludeOffersPages = array();
	$aExcludeOffersPages = array();
	
	##start globals
	foreach($aGlobalExcludePages as $iGPage => $aGOffers){
		if(!is_array($aExcludeOffersPages[$iGPage]))
			$aExcludeOffersPages[$iGPage] = array();
		foreach($aGOffers as $sGOffer){
			if(!in_array($sGOffer, $aLinksIncludeEverywhere) && !in_array($sGOffer, $aFlowIncludeEverywhere)){
				if(!(is_array($aLinksIncludePages[$iGPage]) && in_array($sGOffer, $aLinksIncludePages[$iGPage]))&&
					!(is_array($aFlowIncludePages[$iGPage]) && in_array($sGOffer, $aFlowIncludePages[$iGPage])))
					array_push($aExcludeOffersPages[$iGPage], $sGOffer);
			}
		}
	}
	
	foreach($aGlobalIncludePages as $iGPage => $aGOffers){
		if(!is_array($aIncludeOffersPages[$iGPage]))
			$aIncludeOffersPages[$iGPage] = array();
		foreach($aGOffers as $sGOffer){
			if(!in_array($sGOffer, $aLinksExcludeEverywhere) && !in_array($sGOffer, $aFlowExcludeEverywhere)){
				if(!(is_array($aLinksExcludePages[$iGPage]) && in_array($sGOffer, $aLinksExcludePages[$iGPage]))&&
					!(is_array($aFlowExcludePages[$iGPage]) && in_array($sGOffer, $aFlowExcludePages[$iGPage])))
					array_push($aIncludeOffersPages[$iGPage], $sGOffer);
			}
		}
	}
	
	foreach($aGlobalIncludeEverywhere as $sFOffer){
		if(!in_array($sFOffer, $aLinksOffersExclude) && !in_array($sFOffer, $aFlowOffersExclude))
			array_push($aIncludeOffers, $sFOffer);
	}
	
	foreach($aGlobalExcludeEverywhere as $sFOffer){
		if(!in_array($sFOffer, $aLinksOffersInclude) && !in_array($sFOffer, $aFlowOffersInclude))
			array_push($aExcludeOffers, $sFOffer);
	}
	##end globals
	
	##start flows
	foreach($aFlowExcludePages as $iFPage => $aFOffers){
		if(!is_array($aExcludeOffersPages[$iFPage]))
			$aExcludeOffersPages[$iFPage] = array();
		foreach($aFOffers as $sFOffer){
			if(!in_array($sFOffer, $aLinksIncludeEverywhere)){
				if(!(is_array($aLinksIncludePages[$iFPage]) && in_array($sFOffer, $aLinksIncludePages[$iFPage])))
					array_push($aExcludeOffersPages[$iFPage], $sFOffer);
			}
		}
	}
	
	foreach($aFlowIncludePages as $iFPage => $aFOffers){
		if(!is_array($aIncludeOffersPages[$iFPage]))
			$aIncludeOffersPages[$iFPage] = array();
		foreach($aFOffers as $sFOffer){
			if(!in_array($sFOffer, $aLinksExcludeEverywhere)){
				if(!(is_array($aLinksExcludePages[$iFPage]) && in_array($sFOffer, $aLinksExcludePages[$iFPage])))
					array_push($aIncludeOffersPages[$iFPage], $sFOffer);
			}
		}
	}
	
	foreach($aFlowIncludeEverywhere as $sFOffer){
		if(!in_array($sFOffer, $aLinksOffersExclude))
			array_push($aIncludeOffers, $sFOffer);
	}
	
	foreach($aFlowExcludeEverywhere as $sFOffer){
		if(!in_array($sFOffer, $aLinksOffersInclude))
			array_push($aExcludeOffers, $sFOffer);
	}
	##end flows
	
	##start links
	foreach($aLinksExcludePages as $iFPage => $aFOffers){
		if(!is_array($aExcludeOffersPages[$iFPage]))
			$aExcludeOffersPages[$iFPage] = array();
		foreach($aFOffers as $sFOffer){
			if(!in_array($sFOffer, $aExcludeOffersPages[$iFPage]))
				array_push($aExcludeOffersPages[$iFPage], $sFOffer);
		}
	}
	
	foreach($aLinksIncludePages as $iFPage => $aFOffers){
		if(!is_array($aIncludeOffersPages[$iFPage]))
			$aIncludeOffersPages[$iFPage] = array();
		foreach($aFOffers as $sFOffer){
			if(!in_array($sFOffer, $aIncludeOffersPages[$iFPage]))
				array_push($aIncludeOffersPages[$iFPage], $sFOffer);
		}
	}
	
	foreach($aLinksIncludeEverywhere as $sLOffer){
		array_push($aIncludeOffers, $sLOffer);	
	}
	
	foreach($aLinksExcludeEverywhere as $sLOffer){
		array_push($aExcludeOffers, $sLOffer);	
	}
	##end links
	
	$_SESSION['aIncludeOffers'] = array();
	$_SESSION['aExcludeOffers'] = array();
	$_SESSION['aIncludeOffersPages'] = array();
	$_SESSION['aExcludeOffersPages'] = array();
	$_SESSION['aOfferLocations'] = array();
	$_SESSION['aPrecheckOffers'] = array();
	
	$_SESSION['aIncludeOffers'] = $aIncludeOffers;
	$_SESSION['aExcludeOffers'] = $aExcludeOffers;
	//print_r($aIncludeOffersPages);
	$_SESSION['aPrecheckOffers'] = $aPrecheckOffers;
	
	$_SESSION['aIncludeOffersPages'] = $aIncludeOffersPages;
	$_SESSION['aExcludeOffersPages'] = $aExcludeOffersPages;
	//These two are arrays of arrays.
	//the key is a page number, and the value is an array of offer codes.
	//$_SESSION['aExcludeOffersPages'] == Array('1'=>array('asdf','asfd_1234'), '2'=>array('samir_1'));
	
	$_SESSION['aOfferLocations'] = $aOfferLocations;
	
	
	// pageId 999 is used to exclude offer from entire flow or globally
	$sGetRulesOffersQuery = "SELECT * FROM rules 
					WHERE mutExcCat = 'Y'
					AND (global = 'Y' OR linkId = '".$_SESSION['iSesLinkId']."' OR flowId = '".$_SESSION['iSesFlowId']."')
					AND offerCode !=''
					AND pageNo != 999
					ORDER BY orderId DESC";
	$sRulesOffersResult = dbQuery($sGetRulesOffersQuery);
	if (dbNumRows($sRulesOffersResult) > 0 ) {
		while($oRow12 = dbFetchObject($sRulesOffersResult)) {
			$sMutExclusiveQuery = "SELECT * FROM offersMutExclusive
				   WHERE  offerCode1 = '$oRow12->offerCode'
				   OR     offerCode2 = '$oRow12->offerCode'";
			$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
			if (dbNumRows($rMutExclusiveResult) > 0 ) {
				while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
					if ($oRow12->offerCode == $oMutExclusiveRow->offerCode1) {
						$sMutOfferCodeToExclude = $oMutExclusiveRow->offerCode2;
					} else {
						$sMutOfferCodeToExclude = $oMutExclusiveRow->offerCode1;
					}
					
					if ($oRow12->sMutExcRange == 'flow') {
						if (!in_array($sMutOfferCodeToExclude, $_SESSION['aExcludeOffers'])) {
							array_push($_SESSION['aExcludeOffers'], $sMutOfferCodeToExclude);
						}
					} else if (strstr($oRow12->sMutExcRange,'range')) {
						$iStart = $oRow12->pageNo;
						$iEnd = $iStart + trim(substr($oRow12->sMutExcRange,5,2)) - 1;
						for ($x=$iStart; $x<=$iEnd; $x++) {
							if (!(is_array($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x]))) {
								$_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x] = array();
							}
							array_push($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x],$sMutOfferCodeToExclude);
						}
					} else if (strstr($oRow12->sMutExcRange,'page')) {
						$iStart = $oRow12->pageNo;
						if (!(is_array($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$iStart]))) {
							$_SESSION['aSesMutExcOffersToExcludeByPageRange'][$iStart] = array();
						}
						array_push($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$iStart],$sMutOfferCodeToExclude);
					}
				}
			}
		}
	}
	
	
	
	// pageId 999 is used to exclude offer from entire flow or globally
	$sExcludeRulesOffers = "SELECT DISTINCT offerCode FROM rules WHERE 
					(global = 'Y' OR linkId = '".$_SESSION['iSesLinkId']."' OR flowId = '".$_SESSION['iSesFlowId']."')
					AND offerCode !='' AND pageNo = 999 AND offerIncExc = 'E'";
	$rExcludeRulesOffers = dbQuery($sExcludeRulesOffers);
	if (dbNumRows($rExcludeRulesOffers) > 0 ) {
		while($oExcludeOfferRow = dbFetchObject($rExcludeRulesOffers)) {
			if (!in_array($oExcludeOfferRow->offerCode, $_SESSION['aExcludeOffers'])) {
				array_push($_SESSION['aExcludeOffers'], $oExcludeOfferRow->offerCode);
			}
		}
	}
	
	
	// hide shown offer unless rules say to show the offer again.
	$sShowAgainOffers = "SELECT DISTINCT offerCode 
					FROM rules 
					WHERE (global = 'Y' OR linkId = '".$_SESSION['iSesLinkId']."' OR flowId = '".$_SESSION['iSesFlowId']."')
					AND offerCode !=''
					AND showAlways = 'Y'";
	$rShowAgainOffers = dbQuery($sShowAgainOffers);
	if (dbNumRows($rShowAgainOffers) > 0 ) {
		while($oShowAgainOfferCode = dbFetchObject($rShowAgainOffers)) {
			if (!in_array($oShowAgainOfferCode->offerCode, $_SESSION['aShowOfferAgain'])) {
				array_push($_SESSION['aShowOfferAgain'], $oShowAgainOfferCode->offerCode);
			}
		}
	}


	// exclude many offers from more than one flow/link at a time.
	$sExcludedOffers = "SELECT offerCode FROM excludedOffers 
					WHERE (linkId LIKE '%".$_SESSION['iSesLinkId']."%' OR flowId LIKE '%".$_SESSION['iSesFlowId']."%')
					AND offerCode !=''";
	$rExcludedOffers = dbQuery($sExcludedOffers);
	if (dbNumRows($rExcludedOffers) > 0 ) {
		while($oExcOffersRow = dbFetchObject($rExcludedOffers)) {
			$aOfferCode = explode(',', $oExcOffersRow->offerCode);
			if (count($aOfferCode) > 0) {
				foreach ($aOfferCode as $asdf) {
					if ($asdf !='') {
						if (!in_array($asdf, $_SESSION['aExcludeOffers'])) {
							array_push($_SESSION['aExcludeOffers'], $asdf);
						}
					}
				}
			}
		}
	}

	
	
	##################################################################
	#
	#	End Rules
	#
	##################################################################

	//echo "<br>".__line__.": got all of our rules and things.";flush();ob_flush();
	
	// Get Where To Go (Redirect) URL - user will be sent to this url once flow is done
	if ($_SESSION["sSesRedirectUrl"] == '') {
		$sGetRedirectUrl = "SELECT * FROM whereToGo WHERE id='$iWhereToGoId'";
		$rGetRedirectResult = dbQuery($sGetRedirectUrl);
		echo dbError();
		while($oWhereToGoRow = dbFetchObject($rGetRedirectResult)) {
			$_SESSION['sSesRedirectUrl'] = $oWhereToGoRow->redirectUrl;
		}
	}
	
	$_SESSION['iSesSiteId'] = $iSiteId;
	$sGetSiteQuery = "SELECT * FROM sites WHERE id = '$iSiteId'";
	$rGetSiteResult = dbQuery($sGetSiteQuery);
	echo dbError();
	while($oSiteRow = dbFetchObject($rGetSiteResult)) {
		if ($oSiteRow->header !='') {
			$sHeaderFile = "http://www.popularliving.com/nibbles2/flowHeader/".$oSiteRow->header;
		} else {
			$sHeaderFile = "http://www.popularliving.com/nibbles2/flowHeader/default.jpg";
		}
		$_SESSION['sSesHeaderImage'] = $sHeaderFile;
		$iPrivacyPolicyId = $oSiteRow->privacyPolicyId;
		$iTermsConditionsId = $oSiteRow->termsConditionsId;
		$iDomainId = $oSiteRow->domainId;
	}

	
	$sGetCampaignHeadersSQL = "SELECT * FROM campaignHeaders WHERE campaignId = '$iCampaignId'";
	$rGetCampaignHeaders = dbQuery($sGetCampaignHeadersSQL);
	$headers = array();
	while($oGetHeaders = dbFetchObject($rGetCampaignHeaders)){
		$headers[$oGetHeaders->pageOrder] = $oGetHeaders->content;
	}
	$_SESSION['aSesCampaignHeaders'] = $headers;
	
	
	$sGetCampaignTextSQL = "SELECT * FROM campaignText WHERE campaignId = '$iCampaignId'";
	$rGetCampaignText = dbQuery($sGetCampaignTextSQL);
	while ($oGetText = dbFetchObject($rGetCampaignText)) {
		$aCampaignPageText[$oGetText->pageOrder] = array($oGetText->text1,$oGetText->text2);
	}
	$_SESSION['aSesCampaignPageText'] = $aCampaignPageText;

	
	// Get Terms And Conditions and put it in session
	$sGetTandC = "SELECT body FROM termsConditions 
				WHERE id = '$iTermsConditionsId'";
	$rGetTandC = dbQuery($sGetTandC);
	echo dbError();
	while($oTcRow = dbFetchObject($rGetTandC)) {
		$_SESSION['sSesTermsAndConditions'] = $oTcRow->body;
	}
	
	// Get privacy policy and put it in session
	$sGetPrivacy = "SELECT body FROM privacyPolicy
				WHERE id = '$iPrivacyPolicyId'";
	$rPrivacyResult = dbQuery($sGetPrivacy);
	echo dbError();
	while($oPpRow = dbFetchObject($rPrivacyResult)) {
		$_SESSION['sSesPrivacyPolicy'] = $oPpRow->body;
	}
	
	// Get footer from flows table
	$sGetFlowQuery = "SELECT * FROM flows
					WHERE id = '$iFlowId'";
	$rGetFlowResult = dbQuery($sGetFlowQuery);
	echo dbError();
	while($oFlowRow = dbFetchObject($rGetFlowResult)) {
		$_SESSION['sSesFooter'] = $oFlowRow->footer;
	}
	
	
	// Get flow details
	$sGetFlowDetails = "SELECT * FROM flowDetails
						WHERE flowId = '$iFlowId'
						ORDER BY flowOrder ASC";
	$rGetFlowDetails = dbQuery($sGetFlowDetails);
	echo dbError();
	
	$_SESSION['aSesFlowOrder'] = array();
	$_SESSION['aSesTemplateId'] = array();
	$_SESSION['aSesMaxOffers'] = array();
	$iFlowIndex = 0;
	
	
	// Set current position in a flow.
	if ($_SESSION['sSesShowEmailCapturePage'] == 'N') {
		$_SESSION['iSesCurrentPositionInFlow'] = 1;
	} else {
		$_SESSION['iSesCurrentPositionInFlow'] = 0;
	}

	while($oFlowDetailsRow = dbFetchObject($rGetFlowDetails)) {
		$_SESSION['aSesFlowOrder'][$iFlowIndex] = $oFlowDetailsRow->flowOrder;
		$_SESSION['aSesTemplateId'][$iFlowIndex] = $oFlowDetailsRow->templateId;
		$_SESSION['aSesMaxOffers'][$iFlowIndex] = $oFlowDetailsRow->maxOffers;
		$iFlowIndex++;
	}
	$_SESSION['iSesNoOfFlow'] = $iFlowIndex;

	// Get domain and put it in session.
	$sGetDomain = "SELECT domainName FROM domains
					WHERE id = '$iDomainId'";
	$rDomainResult = dbQuery($sGetDomain);
	echo dbError();
	while($oDomainRow = dbFetchObject($rDomainResult)) {
		$_SESSION['sSesDomain'] = $oDomainRow->domainName;
	}
	

	// if popups are allowed, then ...
	if ($_SESSION['sSesStopAllPop'] == 'N') {
		$sTodayStringToTime = strtotime(date('Y').date('m').date('d'));
		
		$aBadPopTypes = array("''");
		$aBadPopIds = array();
		
		$sGetBadPopIdsSQL = "SELECT * FROM linksPopupsExclusion WHERE sourceCode = '".$_SESSION['sSesSourceCode']."'";
		$rGetBadPopIds = dbQuery($sGetBadPopIdsSQL);
		while($oGetBadPopIds = dbFetchObject($rGetBadPopIds)){
			array_push($aBadPopIds, $oGetBadPopIds->popupId);
		}
		
		if($_SESSION['sSesDisableStandardPop'] == 'Y'){
			array_push($aBadPopTypes, "'S'");
		}
		
		if($_SESSION['sSesDisableExitPop'] == 'Y'){
			array_push($aBadPopTypes, "'E'");
		}
		
		if($_SESSION['sSesDisableWinManagerPop'] == 'Y'){
			array_push($aBadPopTypes, "'W'");
		}
		
		if($_SESSION['sSesDisableAbandonedPop'] == 'Y'){
			array_push($aBadPopTypes, "'A'");
		}
				
		
		$sGetPopUpsSQL = "SELECT * FROM popups ";
		if(count($aBadPopIds) || count($aBadPopTypes)){
			$sGetPopUpsSQL .= "WHERE ";
			if(count($aBadPopIds)){
				$sGetPopUpsSQL .= "id not in (".join(',',$aBadPopIds).")".(count($aBadPopTypes) ? ' AND ' : '');
			}	
			if(count($aBadPopTypes)){
				$sGetPopUpsSQL .= "popType not in (".join(',',$aBadPopTypes).")";
			}
		}
		
		//mail('bbevis@amperemedia.com','popups query',$sGetPopUpsSQL);
		
		$sPopResult = dbQuery($sGetPopUpsSQL);
		
		// STANDARD
		$iPopCounterE = 0 ;
		$iPopCounterA = 0 ;
		$iPopCounterW = 0 ;
		
		while($oPopRow = dbFetchObject($sPopResult)) {
			// If standard pop is allowed and pop type is Standard
			if ($oPopRow->popType == 'S') {
				$sPopStartStringToTime = strtotime($oPopRow->startDate);
				$sPopEndStringToTime = strtotime($oPopRow->endDate);
				$aFlowAndPage = explode(',', $oPopRow->triggerPop);
				if ($aFlowAndPage[0] == $iFlowId) {
					// if standard popup is allowed, then find out if today is between start and end date range
					if (($sTodayStringToTime >= $sPopStartStringToTime) && ($sTodayStringToTime <= $sPopEndStringToTime)) {
						$aTemp = array('url'=>$oPopRow->popupUrl,'popUpUnder'=>$oPopRow->popUpUnder);
						if(!is_array($_SESSION['aSesStandardPopPages'][$aFlowAndPage[1]]))
							$_SESSION['aSesStandardPopPages'][$aFlowAndPage[1]] = array();
						array_push($_SESSION['aSesStandardPopPages'][$aFlowAndPage[1]],$aTemp);
						//mail('bbevis@amperemedia.com','standard popup',print_r($aTemp,true));
					}
				}
			} else {
				if ($_SESSION['sSesDisableAbandonedPop'] != 'Y' && $oPopRow->popType == 'A') {
					//Abandoned type popups
					
					$sPopStartStringToTime = strtotime($oPopRow->startDate);
					$sPopEndStringToTime = strtotime($oPopRow->endDate);
					
						// if abandoned popup is allowed, then find out if today is between start and end date range
					if (($sTodayStringToTime >= $sPopStartStringToTime) && ($sTodayStringToTime <= $sPopEndStringToTime)) {
						$_SESSION['aSesAbandonedPopUrl'][$iPopCounterA] = $oPopRow->popupUrl;
						$_SESSION['aSesAbandonedPopUpUnder'][$iPopCounterA] = $oPopRow->popUpUnder;
						$_SESSION['aSesAbandonedTimeDelayed'][$iPopCounterA] = $oPopRow->timeDelayed;
						$iPopCounterA++;
					}
				} else if ($_SESSION['sSesDisableExitPop'] != 'Y' && $oPopRow->popType == 'E') {
					//Exit type popups
					
					$sPopStartStringToTime = strtotime($oPopRow->startDate);
					$sPopEndStringToTime = strtotime($oPopRow->endDate);
					// if exit popup is allowed, then find out if today is between start and end date range
					if (($sTodayStringToTime >= $sPopStartStringToTime) && ($sTodayStringToTime <= $sPopEndStringToTime)) {
						$_SESSION['aSesExitPopUrl'][$iPopCounterE] = $oPopRow->popupUrl;
						$_SESSION['aSesExitPopUpUnder'][$iPopCounterE] = $oPopRow->popUpUnder;
						$iPopCounterE++;
					}
				} else if ($_SESSION['sSesDisableWinManagerPop'] != 'Y' && $oPopRow->popType == 'W') {
					//Window Manager type popups
					
					$sPopStartStringToTime = strtotime($oPopRow->startDate);
					$sPopEndStringToTime = strtotime($oPopRow->endDate);
					
					// if winManager popup is allowed, then find out if today is between start and end date range
					if (($sTodayStringToTime >= $sPopStartStringToTime) && ($sTodayStringToTime <= $sPopEndStringToTime)) {
						$_SESSION['aSesWinManagerPopUrl'][$iPopCounterW] = $oPopRow->popupUrl;
						$_SESSION['aSesWinManagerPopUpUnder'][$iPopCounterW] = $oPopRow->popUpUnder;
						$_SESSION['aSesWinManagerTimeDelayed'][$iPopCounterW] = $oPopRow->timeDelayed;
						$iPopCounterW++;
					}
				}
			}
		}
	}
	
	
	// Get campaigns data and put it in session
	$sGetCampaigns = "SELECT * FROM campaigns
					WHERE id = '$iCampaignId'";
	$rCampaignsResult = dbQuery($sGetCampaigns);
	echo dbError();
	while($oCampRow = dbFetchObject($rCampaignsResult)) {
		$_SESSION['sSesEPageHTML'] = $oCampRow->ePage;
		$_SESSION['sSesRegPageHTML'] = $oCampRow->regPage;
		$_SESSION['sSesFullRegPageHTML'] = $oCampRow->fullRegPage;
		// $oCampRow->showSkipSubmitCth - the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit
		$_SESSION['sShowSkipSubmitCth'] = $oCampRow->showSkipSubmitCth;
		$_SESSION['sSesCampaignCSS'] = $oCampRow->inLineCSS;
	}

	
	$sGetEmailCreativeSQL = "SELECT emailCapCreative.content FROM emailCapCreative, linksEmailCreative, links WHERE linksEmailCreative.creativeId = emailCapCreative.id AND linksEmailCreative.linkId = links.id AND links.sourceCode = '$sSourceCode' LIMIT 1";	
	$rGetEmailCreative = dbQuery($sGetEmailCreativeSQL);
	echo dbError();
	$oEmailCreative = dbFetchObject($rGetEmailCreative);
	if($oEmailCreative->content != ''){
		$_SESSION['sSesEPageHTML'] = $oEmailCreative->content;
	} else if($oCampRow->ePage != ''){
		$_SESSION['sSesEPageHTML'] = $oCampRow->ePage;		
	}
	
	
	$_SESSION['sSesPage2RegUserForm'] = "<style type='text/css'>
		.fieldnames {
			font: bold 13px Arial, Helvetica, sans-serif;
		}
		</style>
		
		<br />
		<img src='http://www.popularliving.com/images/page2Spacer.gif' height='2' vspace='4' width='100%'>
		<br />
			<table align='[ALIGN_LEFT_CENTER_RIGHT]' border='0' cellpadding='2' cellspacing='0'>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
				</td>
				<td>
					[FIRST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
				</td>
				<td>
					[LAST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Address </b></font>
				</td>
				<td>
					[ADDRESS_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>City </b></font>
				</td>
				<td>
					[CITY_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Zip Code </b></font>
				</td>
				<td>
					[ZIP_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Phone Number </b></font>
				</td>
				<td>
					[PHONE_GROUP]
				</td>
			</tr>
				<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>E-Mail Address </b></font>
				</td>
				<td>
					[EMAIL_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>Birth Date </td>
				<td>[DOB_GROUP]</td>
			</td></tr>
			<tr><td align='right' nowrap='nowrap' class='fieldnames'>Gender </td><td>
			    	[GENDER_RADIO]
				</td></tr>
	</table>";
	
	
	$_SESSION['sSesPage2ThinnerUserForm'] = "<style type='text/css'>
		.fieldnames {
			font: bold 13px Arial, Helvetica, sans-serif;
		}
		</style>
		
		<br />
		<img src='http://www.popularliving.com/images/page2Spacer.gif' height='2' vspace='4' width='100%'>
		<br />
			<table align='[ALIGN_LEFT_CENTER_RIGHT]' border='0' cellpadding='2' cellspacing='0'>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
				</td>
			</tr><tr>
				<td>
					[FIRST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
				</td>
			</tr><tr>
				<td>
					[LAST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Address </b></font>
				</td>
			</tr><tr>
				<td>
					[ADDRESS_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>City </b></font>
				</td>
			</tr><tr>
				<td>
					[CITY_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Zip Code</b></font>
				</td>
			</tr><tr>
				<td>
					[ZIP_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Phone Number</b></font>
				</td>
			</tr><tr>
				<td>
					[PHONE_GROUP]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>E-Mail Address</b></font>
				</td>
			</tr><tr>
				<td>
					[EMAIL_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Birth Date</b></font>
				</td>
			</tr><tr>
				<td>
					[DOB_GROUP]
				</td>
			</tr>
		<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Gender</b></font>
				</td>
			</tr><tr>
				<td>
					[GENDER_RADIO]
				</td>
			</tr>
	</table>";
	
	
	//if this domain isn't the link's site's domain, then redirect to ths site's domain.
	//get the site. 
	$aDomain = explode('.',$_SESSION['sSesDomain']);
	$output = array_slice($aDomain, -2);
	$sDomain = $output[0].'.'.$output[1];
	
	$aServer = explode('.',$_SERVER['SERVER_NAME']);
	$server = array_slice($aServer, -2);
	$sServerName = $server[0].'.'.$server[1];
	$iAjaxTempContent = '';
	
	if ($sDomain != $sServerName) {
		$sDomain = $_SESSION['sSesDomain'];
		echo "<html><head>\n<META HTTP-EQUIV=\"CACHE-CONTROL\" CONTENT=\"NO-CACHE\">\n<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=http://$sDomain/nibbles2/ot.php?PHPSESSID=".session_id()."\">\n</head></html>";
		exit;
	}
} else {
	if ($_POST['PHPSESSID']) {
		$PHPSESSID = $_POST['PHPSESSID'];
	} else {
		$PHPSESSID = $_GET['PHPSESSID'];
	}
	
	if (session_id() == "")
	session_start();
	$iAjaxTempContent = '';
	// this is for 3rd party pages and partner pages.  
	// if they send us skip=true, then increment the count
	// otherwise it will display the same page again.
	if (trim($_GET['skip']) == 'true') {
		$iTempSesId = session_id();
		$iAjaxTempContent = "<SCRIPT LANGUAGE='JAVASCRIPT'>
			if (top != self) {
				response=coRegPopup.send('3rdPartyPgDecrement.php?PHPSESSID=$iTempSesId','');
				top.location.href = new String(location.href);
			}
			</SCRIPT>";
		$_SESSION['iSesCurrentPositionInFlow']++;
		$_GET['skip'] = '';$_POST['skip'] = '';$skip = '';
	}

	$_SESSION['sTempVar'] = microtime();
}

	$sOfferTypeHidden = '';
	
	if($sSourceCode != '') {
		$_SESSION['sSesSourceCodePersists'] = $sSourceCode;
	}
	if($_SESSION['sSesSourceCode'] != ''){
		$_SESSION['sSesSourceCodePersists'] = $_SESSION['sSesSourceCode'];
	}
	
	

	if (count($_SESSION['aSesPage2Offers']) == 0 || count($_SESSION['aSesPage2Offers']) == 1) {
		$iTempVal = $_SESSION['iSesCurrentPositionInFlow'];
		$asdfId = $iTempVal+1;
	} else {
		$iTempVal = $_SESSION['iSesCurrentPositionInFlow']+1;
		$asdfId = $iTempVal;
	}
	
	$sGetPageId = "SELECT id FROM otPages
					WHERE flowId = '".$_SESSION['iSesFlowId']."'
					AND pageNo = '$iTempVal'";
	$rPageId = dbQuery($sGetPageId);
	echo dbError();
	while ($oPageIdRow = dbFetchObject($rPageId)) {
		$_SESSION['iSesPageId'] = $oPageIdRow->id;
	}
	
	// this is for co-reg offers since page# doesn't get 
	// incremented until all offers checked are completed.
	$_SESSION['iSesNextPageId'] = '';
	$sGetPageId2 = "SELECT id FROM otPages
					WHERE flowId = '".$_SESSION['iSesFlowId']."'
					AND pageNo = '$asdfId'";
	$rPageId2 = dbQuery($sGetPageId2);
	echo dbError();
	while ($oPageIdRow2 = dbFetchObject($rPageId2)) {
		$_SESSION['iSesNextPageId'] = $oPageIdRow2->id;
	}
	
	if ($_SESSION['sSesTemplateType'] == 'RP') {
		$_SESSION['iSesNextPageId'] = $_SESSION['iSesPageId'];
	}
	
	
	if ((trim($_POST['sEmail']) != $_SESSION['sSesEmail']) && trim($_POST['sEmail']) !='') {
		$_SESSION['sSesEmail'] = trim($_POST['sEmail']);
	}
	
	if ($_SESSION['sSesSalutation'] == '') { $_SESSION['sSesSalutation'] = trim($_POST['sSalutation']); }
	if ($_SESSION['sSesFirst'] == '') { $_SESSION['sSesFirst'] = trim($_POST['sFirst']); }
	if ($_SESSION['sSesLast'] == '') { $_SESSION['sSesLast'] = trim($_POST['sLast']); }
	if ($_SESSION['sSesAddress'] == '') { $_SESSION['sSesAddress'] = trim($_POST['sAddress']); }
	if ($_SESSION['sSesAddress2'] == '') { $_SESSION['sSesAddress2'] = trim($_POST['sAddress2']); }
	if ($_SESSION['sSesCity'] == '') { $_SESSION['sSesCity'] = trim($_POST['sCity']); }
	if ($_SESSION['sSesState'] =='') { $_SESSION['sSesState'] = trim($_POST['sState']); }
	if ($_SESSION["sSesZip"] == '') { $_SESSION["sSesZip"] = trim($_POST['sZip']); }
	if ($_SESSION['sSesGender'] == '') { $_SESSION['sSesGender'] = trim($_POST['sGender']); }
	if ($_SESSION['iSesBirthYear'] == '') { $_SESSION['iSesBirthYear'] = trim($_POST['iBirthYear']); }
	if ($_SESSION['iSesBirthMonth'] =='') { $_SESSION['iSesBirthMonth'] = trim($_POST['iBirthMonth']); }
	if ($_SESSION['iSesBirthDay']=='') { $_SESSION['iSesBirthDay'] = trim($_POST['iBirthDay']); }
	$_SESSION['sSesBirthDate'] = $_SESSION['iSesBirthYear'].'-'.$_SESSION['iSesBirthMonth'].'-'.$_SESSION['iSesBirthDay'];
	
	if ($_SESSION['sSesPhoneAreaCode'] == '') { $_SESSION['sSesPhoneAreaCode'] = trim($_POST['sPhone_areaCode']); }
	if ($_SESSION['sSesPhoneExchange']=='') { $_SESSION['sSesPhoneExchange'] = trim($_POST['sPhone_exchange']); }
	if ($_SESSION['sSesPhoneNumber']== '') { $_SESSION['sSesPhoneNumber'] = trim($_POST['sPhone_number']); }
	$_SESSION['sSesPhoneExtension'] = trim($_POST['sPhoneExtension']);
	$_SESSION['sSesPhoneNoDash'] = $_SESSION['sSesPhoneAreaCode'].$_SESSION['sSesPhoneExchange'].$_SESSION['sSesPhoneNumber'];
	$_SESSION['sSesPhone'] = $_SESSION['sSesPhoneAreaCode'].'-'.$_SESSION['sSesPhoneExchange'].'-'.$_SESSION['sSesPhoneNumber'];
	
	// process email capture
	if ($_SESSION['sSesTemplateType'] == 'EP' && $sSubmit == 'submit' && $_SESSION['sSesEmail'] !='') {
		// make entry into eTracking table to track submit action
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sCheckQuery1 = "SELECT id FROM uniqueUsersPerSite
					WHERE email = '".$_SESSION['sSesEmail']."'
					AND siteId = '".$_SESSION['iSesSiteId']."'";
		$rCheckResult1 = dbQuery($sCheckQuery1);
		if ( dbNumRows($rCheckResult1) == 0 ) {
			$sInsertQuery = "INSERT INTO uniqueUsersPerSite(email, siteId, dateTimeAdded)
						   VALUES('".$_SESSION['sSesEmail']."', '".$_SESSION['iSesSiteId']."', '$sCurrentDateTime')";
			$rInsertResult = dbQuery($sInsertQuery);
			echo dbError();
			$_SESSION['sSesUniqueUserPerSite'] = true;
		} else {
			$_SESSION['sSesUniqueUserPerSite'] = false;
		}


		$dedupSQL1 = "SELECT email FROM eTracking WHERE eTracking.email = '".$_SESSION['sSesEmail']."' 
					UNION 
					SELECT email FROM eTrackingHistory WHERE eTrackingHistory.email = '".$_SESSION['sSesEmail']."'";
		$dedupResult1 = dbQuery($dedupSQL1);
		if ( dbNumRows($dedupResult1) == 0 ) {
			$_SESSION['sSesUniqueEmailCapUserPerDb'] = true;
		} else {
			$_SESSION['sSesUniqueEmailCapUserPerDb'] = false;
		}

			
		$dedupSQL2 = "SELECT email FROM userData WHERE userData.email = '".$_SESSION['sSesEmail']."' 
					UNION 
					SELECT email FROM userDataHistory WHERE userDataHistory.email = '".$_SESSION['sSesEmail']."'";
		$dedupResult2 = dbQuery($dedupSQL2);
		if ( dbNumRows($dedupResult2) == 0 ) {
			$_SESSION['sSesUniqueUserMemberPerDb'] = true;
		} else {
			$_SESSION['sSesUniqueUserMemberPerDb'] = false;
		}


		$sTrackingQuery = "INSERT INTO eTracking(submitDateTime, pageId, sourceCode, subSourceCode, email, ipAddress)
					   	VALUES('$sCurrentDateTime', '".$_SESSION['iSesPageId']."', '".$_SESSION['sSesSourceCode']."', 
					   	'".$_SESSION['sSesSubSourceCode']."', '".$_SESSION['sSesEmail']."', '".$_SESSION['sSesRemoteIp']."' )";
		$rTrackingResult = dbQuery($sTrackingQuery);
		echo dbError();
	
		// increment e1 submit attempts and rejects
		$sCheckQuery = "SELECT *
				FROM   eTrackingSum
				WHERE  pageId = '".$_SESSION['iSesPageId']."'
				AND	   submitDate = CURRENT_DATE
				AND	   sourceCode = '".$_SESSION['sSesSourceCode']."'";
		$rCheckResult = dbQuery($sCheckQuery);
		echo dbError();
		if ( dbNumRows($rCheckResult) == 0 ) {
			$sInsertQuery = "INSERT INTO eTrackingSum(pageId, submitDate, sourceCode, attempts, subs)
					 VALUES('".$_SESSION['iSesPageId']."', CURRENT_DATE, '".$_SESSION['sSesSourceCode']."', '1', '1')";
			$rInsertResult = dbQuery($sInsertQuery);
			echo dbError();
		} else {
			$sUpdateQuery = "UPDATE eTrackingSum
					 SET    attempts = attempts + 1,
							subs = subs + 1
					 WHERE	pageId = '".$_SESSION['iSesPageId']."'
					 AND	submitDate = CURRENT_DATE
					 AND	sourceCode = '".$_SESSION['sSesSourceCode']."'";
			$rUpdateResult = dbQuery($sUpdateQuery);
			echo dbError();
		}
	}
	
	if ($sMessage == '' && $sSubmit == 'submit') {
		// check if entry exists in active table
		$sActiveCheckQuery = "SELECT * FROM   userData WHERE  email = '".$_SESSION['sSesEmail']."'";
		$rActiveCheckResult = dbQuery($sActiveCheckQuery);
		
		if ($_SESSION['sSesTemplateType'] != 'EP') {
			// check if entry exists in active table
			$sActiveCheckQuery = "SELECT * FROM   userData WHERE  email = '".$_SESSION['sSesEmail']."'";
			$rActiveCheckResult = dbQuery($sActiveCheckQuery);
			$sCurrentDateTime = date('Y-m-d H:i:s');
			if ( dbNumRows($rActiveCheckResult) == 0 ) {
				$sInsertQuery = "INSERT INTO userData(email, salutation, first, last, address,
								address2, city, state, zip, phoneNo, dateTimeAdded, postalVerified, sessionId, remoteIp, 
								dateOfBirth, gender)
							 VALUES(\"".$_SESSION['sSesEmail']."\", \"".$_SESSION['sSesSalutation']."\", \"".addslashes($_SESSION['sSesFirst'])."\", 
							 \"".addslashes($_SESSION['sSesLast'])."\", \"".addslashes($_SESSION['sSesAddress'])."\", 
								\"".addslashes($_SESSION['sSesAddress2'])."\", \"".addslashes($_SESSION['sSesCity'])."\", \"".$_SESSION['sSesState']."\", 
								'".$_SESSION["sSesZip"]."', \"".$_SESSION['sSesPhone']."\",
								'$sCurrentDateTime', 'V', '".session_id()."', 
								\"".$_SESSION['sSesRemoteIp']."\", \"".$_SESSION['sSesBirthDate']."\", \"".$_SESSION['sSesGender']."\")";
				$rInsertResult = dbQuery($sInsertQuery);
				if (!($rInsertResult)) {
					$sEmailMessage = "Insert into userData query failed.\n\n$sInsertQuery";
					mail('it@amperemedia.com',__FILE__." : Insert userData Failed - Line: ".__LINE__, "$sEmailMessage");
					echo dbError();
				}
			} else {
				$sUpdateQuery = "UPDATE userData 
							SET salutation = \"".$_SESSION['sSesSalutation']."\",
								first = \"".addslashes($_SESSION['sSesFirst'])."\", 
								last = \"".addslashes($_SESSION['sSesLast'])."\", 
								address = \"".addslashes($_SESSION['sSesAddress'])."\", 
								address2 = \"".addslashes($_SESSION['sSesAddress2'])."\", 
								city = \"".addslashes($_SESSION['sSesCity'])."\", 
								state = \"".$_SESSION['sSesState']."\",
								zip = '".$_SESSION["sSesZip"]."', 
								phoneNo = \"".$_SESSION['sSesPhone']."\", 
								postalVerified = 'V', 
								sessionId = '".session_id()."', 
								remoteIp = \"".$_SESSION['sSesRemoteIp']."\", 
								dateOfBirth = \"".$_SESSION['sSesBirthDate']."\",
								gender = \"".$_SESSION['sSesGender']."\" 
								WHERE email = \"".$_SESSION['sSesEmail']."\"";
				$rUpdateResult = dbQuery( $sUpdateQuery );
				echo dbError();
			}
		}

	if (count($_SESSION['aSesPage2Offers']) == 0 && count($aOffersChecked) > 0) {
		// reset offerchecked array
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$_SESSION['aSesPage2Offers'] = array();
		$_SESSION['aSesCloseTheyHostOffers'] = array();
		// entry in otData table
		$j=0;
		foreach ($aOffersChecked as $key => $value) {
			if(($value[0] != 'N')) {
				// check if offer has page2 info
				$sTempOffersCode = $value[0];
				
				
				$sCheckTemp1 = "SELECT * FROM xOutData
								WHERE offerCode = '$sTempOffersCode'
								AND email = '".$_SESSION['sSesEmail']."'
								AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
				$rCheckTempResult2 = dbQuery($sCheckTemp1);
				if ( dbNumRows($rCheckTempResult2) == 0 ) {
					$sInsertXOut2 = "INSERT INTO xOutData (offerCode, email, dateTimeAdded, sessionId, sourceCode,pageId)
							VALUES (\"$sTempOffersCode\", '".$_SESSION['sSesEmail']."', '$sCurrentDateTime', '".session_id()."', '".$_SESSION['sSesSourceCode']."','')";
					$rInsertXOutResult2 = dbQuery($sInsertXOut2);
				}

				$iPage2Info = '';
				$iDeliveryMethodId = '';
				$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
									OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
							FROM   offers AS O LEFT JOIN offerLeadSpec AS OL 
							ON  O.offerCode = OL.offerCode
							WHERE	   O.offerCode = '".$sTempOffersCode."'";
				$rOfferResult = dbQuery($sOfferQuery);
				echo dbError();
				while ($oOfferRow = dbFetchObject($rOfferResult)) {
					$iDeliveryMethodId = '';
					$fRevPerLead = $oOfferRow->revPerLead;
					$iPage2Info = $oOfferRow->page2Info;
					$sOfferMode = $oOfferRow->mode;
					$iOfferAutoEmail = $oOfferRow->autoRespEmail;
					$sOfferAutoEmailFormat = $oOfferRow->autoRespEmailFormat;
					$sOfferAutoEmailSub = $oOfferRow->autoRespEmailSub;
					$sOfferAutoEmailBody = $oOfferRow->autoRespEmailBody;
					$sOfferAutoEmailFromAddr = $oOfferRow->autoRespEmailFromAddr;
		
					// get fields which are used to send real time email
					$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
					$sPostingUrl = $oOfferRow->postingUrl;
					$sHttpPostString = $oOfferRow->httpPostString;
					$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
					$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
					$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
					$sSingleEmailBody = $oOfferRow->singleEmailBody;
					$sOfferType = $oOfferRow->offerType;
		
					$sDeliveryMethodQuery = "SELECT *
										 FROM   deliveryMethods
										 WHERE  id = '$iDeliveryMethodId'";
					$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
					while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
						$sHowSent = $oDeliveryMethodRow->shortMethod;
					}
				}
		
				if ($rOfferResult) {
					dbFreeResult($rOfferResult);
				}
				
				if ($sOfferType == 'CTH' || $sOfferType == 'OTH_CTH') {
					array_push($_SESSION['aSesCloseTheyHostOffers'], $sTempOffersCode);
					array_push($_SESSION['aSesPage2Offers'], $sTempOffersCode);
					$_SESSION['bPage2Submit'] = false;

					// If return false, then insert into otData.
					// User already took offer if return true.
					$bFoundDuplicateLead = checkForOtDataDups ($_SESSION['sSesEmail'],$sTempOffersCode);
		
					$sOfferIsCoRegQuery = "SELECT * FROM offers WHERE offerCode = '".$sTempOffersCode."' AND 
						((isCoRegPopUp = 'Y' and isCoRegPopPixelEnable != 'Y') OR 
				 		 (isCloseTheyHost = 'Y' and isCloseTheyHostPixelEnable != 'Y') OR 
				 		 (isCloseTheyHost = 'N' and isCoRegPopUp = 'N'))";
					$rOfferIsCoRegResult = dbQuery($sOfferIsCoRegQuery);
					if (dbNumRows($rOfferIsCoRegResult)) {
						if ($bFoundDuplicateLead == false) {
							$sCurrentDateTime = date('Y-m-d H:i:s');
							$sLeadInsertQuery = "INSERT IGNORE INTO otData (email, offerCode, revPerLead, sourceCode, 
										subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, mode, 
										sessionId, postalVerified)
										VALUES('".$_SESSION['sSesEmail']."', \"".$sTempOffersCode."\", 
										\"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\", \"".$_SESSION["sSesSubSourceCode"]."\", 
										'".$_SESSION['iSesNextPageId']."', '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', 
										'A', '".session_id()."', 'V')";
							$rLeadInsertResult = dbQuery($sLeadInsertQuery);
							if (!($rLeadInsertResult)) {
								$sEmailMessage = "Insert into otData query failed.  Please run below insert query manually\n\n$sLeadInsertQuery";
								mail('it@amperemedia.com',"Insert otData Failed - ot.php", "$sEmailMessage");
							}
						}
					}
					continue;
				}
		
				if ($iPage2Info) {
					//if page2Info, don't record the lead here,
					//add offercode in page2 offers list
					// put the page2 offer in page2Offers array
					array_push($_SESSION['aSesPage2Offers'], $sTempOffersCode);
					$_SESSION['bPage2Submit'] = false;
					//mail('spatel@amperemedia.com',__LINE__,'');
				} else {
					//mail('spatel@amperemedia.com',__LINE__,'');
					// If doesn't have page2Info, record the lead
					// put the checked offer in offersTaken array
					// If return false, then insert into otData.
					// User already took offer if return true.
					$bFoundDuplicateLead = checkForOtDataDups ($_SESSION['sSesEmail'],$sTempOffersCode);
		
					$sOfferIsCoRegQuery = "SELECT * FROM offers WHERE offerCode = '".$sTempOffersCode."' AND 
						((isCoRegPopUp = 'Y' and isCoRegPopPixelEnable != 'Y') OR 
				 		 (isCloseTheyHost = 'Y' and isCloseTheyHostPixelEnable != 'Y') OR 
				 		 (isCloseTheyHost = 'N' and isCoRegPopUp = 'N'))";
					$rOfferIsCoRegResult = dbQuery($sOfferIsCoRegQuery);
					
					//echo "<br>inserting for 1 page guys.";
					
					if (dbNumRows($rOfferIsCoRegResult)) {
						if ($bFoundDuplicateLead == false) {
							$sCurrentDateTime = date('Y-m-d H:i:s');
							$sLeadInsertQuery = "INSERT IGNORE INTO otData (email, offerCode, revPerLead, sourceCode, 
										subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, mode, 
										sessionId, postalVerified)
										VALUES('".$_SESSION['sSesEmail']."', \"".$sTempOffersCode."\", 
										\"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\", \"".$_SESSION["sSesSubSourceCode"]."\", 
										'".$_SESSION['iSesNextPageId']."', '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', 
										'A', '".session_id()."', 'V')";
							$rLeadInsertResult = dbQuery($sLeadInsertQuery);
							if (!($rLeadInsertResult)) {
								$sEmailMessage = "Insert into otData query failed.  Please run below insert query manually\n\n$sLeadInsertQuery";
								mail('it@amperemedia.com',"Insert otData Failed - ot.php", "$sEmailMessage");
							} else {
								//mail('spatel@amperemedia.com',__LINE__,$sLeadInsertQuery);
								$oCoRegPopRow = dbFetchObject($rOfferIsCoRegResult);
								if ($oCoRegPopRow->isCoRegPopUp == 'Y' && $oCoRegPopRow->isCoRegPopPixelEnable == 'N') {
									$sUpdate = "UPDATE otData
												SET processStatus = 'P', sendStatus = 'S', howSent = 'crpNoPixel'
												WHERE offerCode = '$sTempOffersCode'
												AND email = '".$_SESSION['sSesEmail']."' LIMIT 1";
									$rAsdfResult = dbQuery($sUpdate);
								}
							}
						} else {
							//mail('spatel@amperemedia.com',__LINE__,'');
						}
					} else {
						//mail('spatel@amperemedia.com',__LINE__,'');
					}

					// Create comma separated offerCode list for offers that are taken today
					// if offer taken, then put the offer code in array to exclude.
					if (!in_array($sTempOffersCode, $_SESSION['aOfferTakenForCookie'])) {
						array_push($_SESSION['aOfferTakenForCookie'], $sTempOffersCode);
					}
		
					// Send Offer Auto Responder email if set to do so
					if ($iOfferAutoEmail) {
						$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $_SESSION['sSesEmail'], $sOfferAutoEmailBody);
						$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
						$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
						if ($sOfferAutoEmailFormat == 'html') {
							$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
						}
						mail($_SESSION['sSesEmail'], $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
					}
		
					// ********************* post real time leads *************************
					// following condition commented to allow posting test leads in real time if "3401 dundee" address not used
					// for offer taken stat info
					$sOfferTakenStatInfo .= $sTempOffersCode. ",";
					$sRealTimeResponse = '';
						
		
					if ($bFoundDuplicateLead == false && !(strtolower(substr($_SESSION["sSesAddress"],0,11))=='3401 dundee' && $_SESSION["sSesZip"]=='60062')) {
						if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
							// *************************** Start posting real time lead ********************
							// 2 = real time form post - GET
							// 3 = real time form post - POST
								
							if ($sTempOffersCode == 'MS_Eversave') {
								if ($iBirthMonth < 10) {
									$iBirthMonth = substr($iBirthMonth,1,1);
								}
								if ($iBirthDay < 10) {
									$iBirthDay = substr($iBirthDay,1,1);
								}
							}
		
							$aUrlArray = explode("//", $sPostingUrl);
							$sUrlPart = $aUrlArray[1];
							
							$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
							
							$sHttpPostString = ereg_replace("\[salutation\]",urlencode(($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[email\]",urlencode($_SESSION['sSesEmail']), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[first\]",urlencode(($sFirst ? $sFirst : $_SESSION["sSesFirst"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[last\]",urlencode(($sLast ? $sLast : $_SESSION["sSesLast"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[address\]",urlencode(($sAddress ? $sAddress : $_SESSION["sSesAddress"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[address2\]",urlencode(($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[city\]",urlencode(($sCity ? $sCity : $_SESSION["sSesCity"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[state\]",urlencode(($sState ? $sState : $_SESSION["sSesState"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[zip\]",urlencode(($sZip ? $sZip : $_SESSION["sSesZip"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[zip5only\]",urlencode(substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5)), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone\]",urlencode(($sPhone ? $sPhone : $_SESSION["sSesPhone"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode(($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode(($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode(($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_number\]", urlencode(($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[mm\]", urlencode(date('m')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[dd\]", urlencode(date('d')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[yy\]", urlencode(date('y')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[hh\]", urlencode(date('H')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ii\]", urlencode(date('i')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ss\]", urlencode(date('s')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthYear\]", urlencode(($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthMonth\]", urlencode(($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthDay\]", urlencode(($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[gender\]", urlencode(($sGender ? $sGender : $_SESSION["sSesGender"])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);
									
							if ($sTempOffersCode == 'MS_Eversave') {
								if ($iBirthMonth < 10) {
									$iBirthMonth = '0'.$iBirthMonth;
								}
								if ($iBirthDay < 10) {
									$iBirthDay = '0'.$iBirthDay;
								}
							}
		
							// separate host part and script path
							$sResult = httpFormPostGet ($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sTempOffersCode,$_SESSION['sSesEmail'],$sHowSent);
							// *************************** End posting real time lead ********************
						} else if ($iDeliveryMethodId == 4) {
							// send lead email if lead delivery method set as "Real Time Email"
							// only if mode is active
		
							$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
							$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
		
							$sSingleEmailSubject = ereg_replace("\[offerCode\]",$sTempOffersCode, $sSingleEmailSubject);
		
							if (strstr($sSingleEmailSubject,"[d-")) {
								//get date arithmetic number
								$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
									
								$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
								$rTempResult = dbQuery($sTempQuery);
								while ($oTempRow = dbFetchObject($rTempResult)) {
									$sNewDate = $oTempRow->newDate;
								}
		
								if ($rTempResult) {
									dbFreeResult($rTempResult);
								}
		
								$sSingleEmailSubject = ereg_replace("\[dd\]", substr($sNewDate,8,2), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[mm\]", substr($sNewDate,5,2), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[yyyy\]", substr($sNewDate,0,4), $sSingleEmailSubject);
								$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
								$sSingleEmailSubject = str_replace($sDateArithString, "", $sSingleEmailSubject);
		
							} else {
								$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
							}
							
							$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
							
							$sSingleEmailBody = ereg_replace("\[salutation\]",urlencode(($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[email\]",urlencode($_SESSION['sSesEmail']), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[first\]",urlencode(($sFirst ? $sFirst : $_SESSION["sSesFirst"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[last\]",urlencode(($sLast ? $sLast : $_SESSION["sSesLast"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[address\]",urlencode(($sAddress ? $sAddress : $_SESSION["sSesAddress"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[address2\]",urlencode(($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[city\]",urlencode(($sCity ? $sCity : $_SESSION["sSesCity"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[state\]",urlencode(($sState ? $sState : $_SESSION["sSesState"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[zip\]",urlencode(($sZip ? $sZip : $_SESSION["sSesZip"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[zip5only\]",urlencode(substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5)), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[phone\]",urlencode(($sPhone ? $sPhone : $_SESSION["sSesPhone"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[ipAddress\]",urlencode(($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[phone_areaCode\]", urlencode(($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode'])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[phone_exchange\]", urlencode(($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange'])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[phone_number\]", urlencode(($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber'])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[mm\]", urlencode(date('m')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[dd\]", urlencode(date('d')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[yy\]", urlencode(date('y')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[hh\]", urlencode(date('H')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[ii\]", urlencode(date('i')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[ss\]", urlencode(date('s')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthYear\]", urlencode(($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthMonth\]", urlencode(($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthDay\]", urlencode(($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[gender\]", urlencode(($sGender ? $sGender : $_SESSION["sSesGender"])), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sSingleEmailBody);
									
							$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
							$sSingleEmailBody = "";
		
							for ($x=0;$x<count($aSingleEmailBodyArray);$x++) {
								$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
							}
							mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
							
							$sUpdateStatusQuery = "UPDATE otData
										   SET    processStatus = 'P',
												  sendStatus = 'S',
												  howSent = '$sHowSent'
										   WHERE  email='".$_SESSION['sSesEmail']."' AND offerCode='".$sTempOffersCode."'";
							$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
						}
					} // end if test lead
				}
			}	// end of skipping "No" radio buttons. 
		} // end of offersChecked loop
		
		// start cookie
		if (count($_SESSION['aOfferTakenForCookie']) > 0) {
			$aOfferTakenInCookie = array();
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
				
			$sCurrentCookieOfferCode = '';
			foreach ($_SESSION['aOfferTakenForCookie'] as $sOfferCodeCookie) {
				$sCurrentCookieOfferCode .= "$sOfferCodeCookie,";
			}
			$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
			// expires in 180 days - 15552000 seconds	- Add/Update cookie.
			// do not remove below 2 lines of code.  let the script set the cookie.
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", ".popularliving.com", 0);
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", $_SESSION['sSesDomain'], 0);
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '', 0);
		}
		// end cookie
			
			
			
		// **************  make entry into offer taken stats  *********************
		if ($sOfferTakenStatInfo != '') {
			$sOfferTakenStatInfo = substr($sOfferTakenStatInfo, 0, strlen($sOfferTakenStatInfo)-1);
			$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('".$_SESSION['iSesPageId']."', '$sOfferTakenStatInfo', '$sSourceCode', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
		}
		// *****************************************
	}

			
		if (count($_SESSION['aSesPage2Offers']) > 0 && $sSubmit == 'submit') {
			// Loop through each "Page2 Offer" that has not been removed from the list.
			while (list($i, $val) = each($_SESSION["aSesPage2Offers"])) {
				// Check for Offers that the user has dropped
				$bDropped = "false";
				$sPage2Fields = '';
				$sPage2Data = '';
	
				// For the current offerCode, check the "aDropOffers" variable to
				// See if it has been dropped.
				for ( $j=0; $j < count($aDropOffers);$j++) {
					if ($aDropOffers[$j] == $_SESSION["aSesPage2Offers"][$i]) {
						// If This offer has been dropped, add it to the stats for dropped offers.
						// And remove it from the Page2Offers
						$bDropped = "true";
						$_SESSION['sSesOfferAbortStatInfo'] .= $_SESSION["aSesPage2Offers"][$i].",";
						unset($_SESSION["aSesPage2Offers"][$i]);
						//unset($aDropOffers[$j]);
						if ($_SESSION['sSesTemplateType'] == 'SPNS') {
							break;
						}
					}
				}
			
				// If the offer was not dropped, process it.
				if ($bDropped != "true") {
					// Get all Page2 Fields from page2Map in nibbles database.
					$sPage2MapQuery = "SELECT *
										   FROM   page2Map
							 			   WHERE offerCode = '".$_SESSION["aSesPage2Offers"][$i]."'
							 			   ORDER BY storageOrder ";
					$rPage2MapResult = dbQuery($sPage2MapQuery);
					//$iPage2MapCount = dbNumRows($rPage2MapResult);
	
					// to track empty page2Data - UNKNOWN
					$sTestActualFieldNames = "";
					$sTestMessage = "";
					$sTempMessage = '';
			
					// Loop through all Page2 Field Names.
					while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
						$sActualFieldName = $oPage2MapRow->actualFieldName;
						// Each page2 "Actual Field Name" and its corresponding value are stored
						// in the database for future use (if error on page).
						$_SESSION['page2'][$sActualFieldName] = ${$sActualFieldName};
						$sLeadPage2Data .= "\r\n$sActualFieldName: ".${$sActualFieldName};
						$sPage2Data .= "\"".${$sActualFieldName}."\"|";
					}
			
					// Check page2 fields against the conditions in offerPage2Validation.
					// add offer in offers taken session variable
					// put the checked offer in offersTaken array
					$sPage2Data = addslashes($sPage2Data);
					$_SESSION['aSesOfferPage2Data'][$_SESSION["aSesPage2Offers"][$i]] = $sPage2Data;
				}
				if ($_SESSION['sSesTemplateType'] == 'SPNS') {
					break;
				}
			}
			

			// ******************** Loop through page2 offers ***************************
			// If any offer dropped, at this point, it was removed from $_SESSION["aSesPage2Offers"]
			//	$_SESSION['bPage2Submit']
			if ($sMessage == '' && $_SESSION['bPage2Submit'] == true && count($_SESSION["aSesPage2Offers"]) > 0 && $sSubmit == 'submit') {
				//mail('bbevis@amperemedia.com','hey hey, my my',print_r($_SESSION["aSesPage2Offers"],true));
				$sCurrentCookieOfferCode = '';
				$iCountOfPage2Offers = count($_SESSION["aSesPage2Offers"]);

				$temp_array = array();
				
				for($i=0;$i<$iCountOfPage2Offers;$i++) {
					// *********************** Get offer details  ****************************
					$iDeliveryMethodId = '';
					$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
						   OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
							FROM   offers AS O, offerLeadSpec AS OL
							WHERE  O.offerCode = OL.offerCode
							AND	   O.offerCode = '".$_SESSION["aSesPage2Offers"][$i]."'";
			
					$rOfferResult = dbQuery($sOfferQuery);
					echo dbError();
					while ($oOfferRow = dbFetchObject($rOfferResult)) {
						$iDeliveryMethodId = '';
						$fRevPerLead = $oOfferRow->revPerLead;
						$iOfferAutoEmail = $oOfferRow->autoRespEmail;
						$sOfferAutoEmailFormat = $oOfferRow->autoRespEmailFormat;
						$sOfferAutoEmailSub = $oOfferRow->autoRespEmailSub;
						$sOfferAutoEmailBody = $oOfferRow->autoRespEmailBody;
						$sOfferAutoEmailFromAddr = $oOfferRow->autoRespEmailFromAddr;
		
						// get fields which are used to send real time email
						$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
						$sPostingUrl = $oOfferRow->postingUrl;
						$sHttpPostString = $oOfferRow->httpPostString;
						$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
						$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
						$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
						$sSingleEmailBody = $oOfferRow->singleEmailBody;
		
						$sDeliveryMethodQuery = "SELECT *
										 FROM   deliveryMethods
										 WHERE  id = '$iDeliveryMethodId'";
						$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
						while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
							$sHowSent = $oDeliveryMethodRow->shortMethod;
						}
					}
		
					// ********************** End getting offer details ********************
					// If return false, then insert into otData.
					// User already took offer if return true.
					$bFoundDuplicateLead = checkForOtDataDups ($_SESSION["sSesEmail"],$_SESSION["aSesPage2Offers"][$i]);
						
					if ($bFoundDuplicateLead == false) {
						$sCurrentDateTime = date('Y-m-d H:i:s');
						$sLeadInsertQuery = "INSERT IGNORE INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode, sessionId, postalVerified )
							 VALUES(\"".$_SESSION["sSesEmail"]."\", \"".$_SESSION["aSesPage2Offers"][$i]."\", \"$fRevPerLead\", \"".$_SESSION['sSesSourceCodePersists']."\",  \"".$_SESSION["sSesSubSourceCode"].
							"\", \"".$_SESSION["iSesPageId"]."\", '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', \"".$_SESSION['aSesOfferPage2Data'][$_SESSION["aSesPage2Offers"][$i]]."\", 
							'A', '".session_id()."', 'V')";
							
						//mail('bbevis@amperemedia.com',__line__.": insert query", $sLeadInsertQuery);
							
						$rLeadInsertResult = dbQuery($sLeadInsertQuery);
						if (!($rLeadInsertResult)) {
							$sEmailMessage = "Insert into otData query failed.  Please run below insert query manually\n\n$sLeadInsertQuery";
							mail('it@amperemedia.com',"Insert otData Failed - ot.php", "$sEmailMessage");
						}
					}

					
					// Create comma separated offerCode list for offers that are taken today
					//$sCurrentCookieOfferCode .= $_SESSION["aSesPage2Offers"][$i].",";
					if (!in_array($_SESSION["aSesPage2Offers"][$i], $_SESSION['aOfferTakenForCookie'])) {
						array_push($_SESSION['aOfferTakenForCookie'], $_SESSION["aSesPage2Offers"][$i]);
					}

					//	************* send offer auto email if offer is set to do so ***************
					if ($iOfferAutoEmail) {
							$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
							$sOfferAutoEmailBody = eregi_replace("\[salutation\]",urlencode(($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[email\]",urlencode($_SESSION['sSesEmail']), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[first\]",urlencode(($sFirst ? $sFirst : $_SESSION["sSesFirst"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[last\]",urlencode(($sLast ? $sLast : $_SESSION["sSesLast"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[address\]",urlencode(($sAddress ? $sAddress : $_SESSION["sSesAddress"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[address2\]",urlencode(($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[city\]",urlencode(($sCity ? $sCity : $_SESSION["sSesCity"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[state\]",urlencode(($sState ? $sState : $_SESSION["sSesState"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[zip\]",urlencode(($sZip ? $sZip : $_SESSION["sSesZip"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[zip5only\]",urlencode(substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5)), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[phone\]",urlencode(($sPhone ? $sPhone : $_SESSION["sSesPhone"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[ipAddress\]",urlencode(($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[phone_areaCode\]", urlencode(($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode'])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[phone_exchange\]", urlencode(($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange'])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[phone_number\]", urlencode(($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber'])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[mm\]", urlencode(date('m')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[dd\]", urlencode(date('d')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[yy\]", urlencode(date('y')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[hh\]", urlencode(date('H')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[ii\]", urlencode(date('i')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[ss\]", urlencode(date('s')), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[birthYear\]", urlencode(($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[birthMonth\]", urlencode(($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[birthDay\]", urlencode(($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[gender\]", urlencode(($sGender ? $sGender : $_SESSION["sSesGender"])), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sOfferAutoEmailBody);
							$sOfferAutoEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sOfferAutoEmailBody);
						
						$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
						$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
						if ($sOfferAutoEmailFormat == 'html') {
							$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
						}
						mail($_SESSION["sSesEmail"], $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
					}
					// ************************** End sending offer auto responder ********************
			
		
					// ********************** Send real time leads ***********************
					
					if ($bFoundDuplicateLead == false && !(strtolower(substr($_SESSION["sSesAddress"],0,11)) == '3401 dundee' && $_SESSION["sSesZip"] == '60062') ) {
						$sRealTimeResponse = '';
						
						if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
							// 2 = real time form post - GET
							// 3 = real time form post - POST
		
							$aUrlArray = explode("//", $sPostingUrl);
							$sUrlPart = $aUrlArray[1];
							
							if ($_SESSION["aSesPage2Offers"][$i] == 'MS_Eversave') {
								if ($_SESSION["iSesBirthMonth"] < 10) {
									$_SESSION["iSesBirthMonth"] = substr($_SESSION["iSesBirthMonth"],1,1);
								}
								if ($_SESSION["iSesBirthDay"] < 10) {
									$_SESSION["iSesBirthDay"] = substr($_SESSION["iSesBirthDay"],1,1);
								}
							}
			
							$sBinGender = ($_SESSION["sSesGender"] == 'M' ? '1' :($_SESSION["sSesGender"] == 'F' ? '0' : ''));
							
							$sHttpPostString = ereg_replace("\[salutation\]",urlencode($_SESSION["sSesSalutation"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[email\]",urlencode($_SESSION["sSesEmail"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[first\]",urlencode($_SESSION["sSesFirst"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[last\]",urlencode($_SESSION["sSesLast"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[address\]",urlencode($_SESSION["sSesAddress"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[address2\]",urlencode($_SESSION["sSesAddress2"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[city\]",urlencode($_SESSION["sSesCity"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[state\]",urlencode($_SESSION["sSesState"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[zip\]",urlencode($_SESSION["sSesZip"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[zip5only\]",urlencode(substr($_SESSION["sSesZip"],0,5)), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone\]",urlencode($_SESSION["sSesPhone"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode($_SESSION["sSesRemoteIp"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode(($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode(($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[phone_number\]", urlencode(($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber'])), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[mm\]", urlencode(date('m')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[dd\]", urlencode(date('d')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[yy\]", urlencode(date('y')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[hh\]", urlencode(date('H')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ii\]", urlencode(date('i')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[ss\]", urlencode(date('s')), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthYear\]", urlencode($_SESSION["iSesBirthYear"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthMonth\]", urlencode($_SESSION["iSesBirthMonth"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[birthDay\]", urlencode($_SESSION["iSesBirthDay"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[gender\]", urlencode($_SESSION["sSesGender"]), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sHttpPostString);
							$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);
								
							if ($_SESSION["aSesPage2Offers"][$i] == 'MS_Eversave') {
								if ($_SESSION["iSesBirthMonth"] < 10) {
									$_SESSION["iSesBirthMonth"] = '0'.$_SESSION["iSesBirthMonth"];
								}
								if ($_SESSION["iSesBirthDay"] < 10) {
									$_SESSION["iSesBirthDay"] = '0'.$_SESSION["iSesBirthDay"];
								}
							}
								
							// get all the page2 fields of this offer and replace
							$sPage2MapQuery = "SELECT *
										   FROM   page2Map
						 	 			   WHERE offerCode = '".$_SESSION["aSesPage2Offers"][$i]."'
						 				   ORDER BY storageOrder ";
			
							$rPage2MapResult = dbQuery($sPage2MapQuery);
							$f = 1;
							while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
								$sActualFieldName = $oPage2MapRow->actualFieldName;
								$sFieldVar = "FIELD".$f;
								$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sActualFieldName), $sHttpPostString);
								$f++;
							}
			
							// separate host part and script path
							$sResult = httpFormPostGet ($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$_SESSION["aSesPage2Offers"][$i],$_SESSION["sSesEmail"],$sHowSent);
		
						} else if ($iDeliveryMethodId == 4) {
							// send lead email if lead delivery method set as real time email
							// only if mode is active
			
							$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
							$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
							$sSingleEmailSubject = ereg_replace("\[offerCode\]",$_SESSION["aSesPage2Offers"][$i], $sSingleEmailSubject);
			
			
							if (strstr($sSingleEmailSubject,"[d-")) {
								//get date arithmetic number
								$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
	
								$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
								$rTempResult = dbQuery($sTempQuery);
								while ($oTempRow = dbFetchObject($rTempResult)) {
									$sNewDate = $oTempRow->newDate;
								}
		
								$sSingleEmailSubject = ereg_replace("\[dd\]", substr($sNewDate, 8, 2), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[mm\]", substr($sNewDate, 5, 2), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[yyyy\]", substr($sNewDate, 0, 4), $sSingleEmailSubject);
								$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
								$sSingleEmailSubject = str_replace($sDateArithString, '', $sSingleEmailSubject);
							} else {
								$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
								$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
							}
							$sBinGender = ($_SESSION["sSesGender"] == 'M' ? '1' :($_SESSION["sSesGender"] == 'F' ? '0' : ''));
							
							$sSingleEmailBody = ereg_replace("\[email\]",$_SESSION["sSesEmail"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[salutation\]",$_SESSION["sSesSalutation"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[first\]",$_SESSION["sSesFirst"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[last\]",$_SESSION["sSesLast"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[address\]",$_SESSION["sSesAddress"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[address2\]",$_SESSION["sSesAddress2"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[city\]",$_SESSION["sSesCity"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[state\]",$_SESSION["sSesState"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[zip\]",$_SESSION["sSesZip"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[zip5only\]",substr($_SESSION["sSesZip"],0,5), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[phone\]",$_SESSION["sSesPhone"], $sSingleEmailBody);
		       	        	$sSingleEmailBody = ereg_replace("\[phone_areaCode\]",substr($_SESSION["sSesPhone"],0,3), $sSingleEmailBody);
		       	        	$sSingleEmailBody = ereg_replace("\[phone_exchange\]",substr($_SESSION["sSesPhone"],4,3), $sSingleEmailBody);
		       	        	$sSingleEmailBody = ereg_replace("\[phone_number\]",substr($_SESSION["sSesPhone"],8,4), $sSingleEmailBody); 
							$sSingleEmailBody = ereg_replace("\[ipAddress\]",$_SESSION["sSesRemoteIp"], $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[mm\]", urlencode(date('m')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[dd\]", urlencode(date('d')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[yy\]", urlencode(date('y')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[hh\]", urlencode(date('H')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[ii\]", urlencode(date('i')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[ss\]", urlencode(date('s')), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthYear\]", urlencode($_SESSION["iSesBirthYear"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthMonth\]", urlencode($_SESSION["iSesBirthMonth"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[birthDay\]", urlencode($_SESSION["iSesBirthDay"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[gender\]", urlencode($_SESSION["sSesGender"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sSingleEmailBody);
							$sSingleEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION['sSesRevSourceCode']), $sSingleEmailBody);
		
			
							// get all the page2 fields of this offer and replace
							$sPage2MapQuery = "SELECT *
										   FROM   page2Map
						 	 			   WHERE offerCode = '".$_SESSION["aSesPage2Offers"][$i]."'
						 				   ORDER BY storageOrder ";
							$rPage2MapResult = dbQuery($sPage2MapQuery);
							$f = 1;
							while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
								$sActualFieldName = $oPage2MapRow->actualFieldName;
								$sFieldVar = "FIELD".$f;
								$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",$$sActualFieldName, $sSingleEmailBody);
								$f++;
							}

							$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
							$sSingleEmailBody = '';
							for ( $x = 0; $x < count($aSingleEmailBodyArray); $x++ ) {
								$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
							}
		
							mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
							$sCurrentDateTime = date('Y-m-d H:i:s');
							$sUpdateStatusQuery = "UPDATE otData
											   		SET   processStatus = 'P',
														  sendStatus = 'S',
														  dateTimeProcessed = '$sCurrentDateTime',
														  dateTimeSent = '$sCurrentDateTime',
													 	  howSent = '$sHowSent'
											 	  WHERE   email='$sEmail' and offerCode='".$_SESSION["aSesPage2Offers"][$i]."'";
							$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
						}
					}
					// *********************  End sending real time leads  *****************
					
					$sOfferTypeSQL = "SELECT offerType FROM offers WHERE offerCode = '".$_SESSION["aSesPage2Offers"][$i]."'";
					$rOfferType = dbQuery($sOfferTypeSQL);
					$oOfferType = dbFetchObject($rOfferType);
					
					// If non-stacked, then unset 1st offer code from array and reindex.
					array_push($temp_array,$_SESSION['aSesPage2Offers'][$i]);
					
					if (($_SESSION['sSesTemplateType'] == 'SPNS')&&($oOfferType->offerType != 'CR')){
							break;
						
					}
				}
				
				foreach($_SESSION['aSesPage2Offers'] as $i => $offerCode){
					if(in_array($offerCode,$temp_array)){
						unset($_SESSION['aSesPage2Offers'][$i]);
					}
				}
				$temp = array_values($_SESSION['aSesPage2Offers']);
				$_SESSION['aSesPage2Offers'] = $temp;
				if ($_SESSION['sSesTemplateType'] == 'SPS') {
					unset($_SESSION['aSesPage2Offers']);
				}
			}
				
			// start cookie
			if (count($_SESSION['aOfferTakenForCookie']) > 0) {
				$aOfferTakenInCookie = array();
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
				
				$sCurrentCookieOfferCode = '';
				foreach ($_SESSION['aOfferTakenForCookie'] as $sOfferCodeCookie) {
					$sCurrentCookieOfferCode .= "$sOfferCodeCookie,";
				}
				$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
				// expires in 180 days - 15552000 seconds	- Add/Update cookie.
				// do not remove below 2 lines of code.  let the script set the cookie.
				setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", ".popularliving.com", 0);
				setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", $_SESSION['sSesDomain'], 0);
				setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '', 0);
			}
			// end cookie
			
			
			
			// *************** Make entries into offers stats tables ***************
			// offer taken stat info
			if ($sOfferTakenStatInfo != '') {
				$sOfferTakenStatInfo = substr($sOfferTakenStatInfo, 0, strlen($sOfferTakenStatInfo)-1);
				$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
									VALUES('".$_SESSION["iSesPageId"]."', \"$sOfferTakenStatInfo\", '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
				$rStatResult = dbQuery($sStatQuery);
				echo dbError();
			}
			// offer aborted count
				
			if ($_SESSION['sSesOfferAbortStatInfo'] != '') {
				$sOfferAbortStatInfo = $_SESSION['sSesOfferAbortStatInfo'];
				$sOfferAbortStatInfo = substr($sOfferAbortStatInfo, 0, strlen($sOfferAbortStatInfo)-1);
				$sStatQuery = "INSERT INTO tempOfferAbortStats(pageId, statInfo, sourceCode, displayDate)
									VALUES('".$_SESSION["iSesPageId"]."', \"$sOfferAbortStatInfo\", '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
				$rStatResult = dbQuery($sStatQuery);
				echo dbError();
				
				// Insert dropped offers entry into abandedOffers.
				$sTempOfferCode = explode(",", $sOfferAbortStatInfo);
				for ($iCount = 0; $iCount<count($sTempOfferCode); $iCount++) {
					$sCheckTemp = "SELECT * FROM abandedOffers
								WHERE offerCode = '".$sTempOfferCode[$iCount]."'
								AND email = '".$_SESSION["sSesEmail"]."'
								AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
					$rCheckTempResult = dbQuery($sCheckTemp);
					if ( dbNumRows($rCheckTempResult) == 0 ) {
						$sCurrentDateTime = date('Y-m-d H:i:s');
						$sInsertAbandedOffersQuery = "INSERT INTO abandedOffers(email, dateTimeAdded, remoteIp, sourceCode, offerCode, sessionId, pageId)
				                       VALUES('".$_SESSION["sSesEmail"]."', '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', \"".$_SESSION["sSesSourceCode"]."\", \"".$sTempOfferCode[$iCount]."\", '".session_id()."',".$_SESSION["iSesPageId"].")";
						$rInsertAbandedOffersResult = dbQuery($sInsertAbandedOffersQuery);
						echo dbError();
					}
				}
			}
			// ************************** End making entries into offers stats table *****************
		}
		// ************************* End looping page2 offers **************************
	}

	// If this is the last flow, send user to redirect url.
	if ($_SESSION['iSesCurrentPositionInFlow'] >= $_SESSION['iSesNoOfFlow']) {
		if (strstr($_SESSION['sSesRedirectUrl'],'?')) {
			$sRedirectTo = $_SESSION['sSesRedirectUrl']."&PHPSESSID=".session_id();
		} else {
			$sRedirectTo = $_SESSION['sSesRedirectUrl']."?PHPSESSID=".session_id();
		}
		session_write_close();
		header("Location:$sRedirectTo");
		exit;
	}
	
	// prepopulate values in user registration form
	// if we have query string, get all keys and values and validate them
	// if valid, prepopulate the user's form.
	// Ajax will be use later on to run validation.
	if ($e) {
		$sEmail = (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", trim($_GET['e']))) ? '' : trim($_GET['e']);
	}

	if ($f) {
		$sFirst = (!ctype_alpha(trim($_GET['f'])) ? '' : trim($_GET['f']));
		
		if (validateName($sFirst)) {
			$sFirst = $sFirst;
		}
	}
	
	if ($l) {
		$sLast = (!eregi("^[-A-Z[:space:]'\.]*$", trim($_GET['l'])) ? '' : trim($_GET['l']));
		
		if (validateName($sLast)) {
			$sLast = $sLast;
		}
	}
	
	if ($a1) {
		$sAddress = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]{1,}$", trim($_GET['a1'])) ? '' : trim($_GET['a1']));
	}
	
	if ($a2) {
		$sAddress2 = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]*$", trim($_GET['a2'])) ? '' : trim($_GET['a2']));
	}
	
	if ($c) {
		$sCity = (!ereg( "^[a-zA-Z0-9 \'\x2e\-\’\`\&]{1,}$", trim($_GET['c'])) ? '' : trim($_GET['c']));
	}
	
	// prepopulate user registration form
	// and also put value in target session for targetting.
	if ($s) {
		$sState = (!ereg("^[A-Z]{2,2}$", strtoupper(trim($_GET['s']))) ? '' : strtoupper(trim($_GET['s'])));
		if (strlen($sState) == 2) {
			if (validateTargetState($sState)) {
				if ($_SESSION['sSesTargetState'] == '') {
					$_SESSION['sSesTargetState'] = $sState;
				}
			}
		}
	}
	
	if ($z) {
		$sZip = (!ereg("^[0-9-]{5,}$", strtoupper(trim($_GET['z']))) ? '' : strtoupper(trim($_GET['z'])));
		if (ctype_digit($sZip) && strlen($sZip) == 5) {
			if ($_SESSION['sSesTargetZip'] == '') {
				$_SESSION['sSesTargetZip'] = $sZip;
			}
		}
	}

	if ($p) {
		$p = (!(ereg("^[0-9-]+$", trim($_GET['p']))) ? '' : trim($_GET['p']));
		if ($p !='') {
			$sPhone = $p;
			// phone area code cannot start with 0 or 1
			if (strlen(substr($p, 0, 3)) == 3 && ctype_digit(substr($p, 0, 3)) && substr($p, 0, 3) >= 200) {
				$sPhone_areaCode = substr($p, 0, 3);
			}
			
			// phone exchange cannot start with 0 or 1
			if (strlen(substr($p, 4, 3)) == 3 && ctype_digit(substr($p, 4, 3)) && substr($p, 4, 3) >= 200) {
				$sPhone_exchange = substr($p, 4, 3);
				if ($_SESSION['sSesTargetExchange'] == '') {
					$_SESSION['sSesTargetExchange'] = $sPhone_exchange;
				}
			}
			
			if (strlen(substr($p, 8,4)) == 4 && ctype_digit(substr($p, 8,4))) {
				$sPhone_number = substr($p, 8,4);
			}
		}
	}
	
	if ($pa) {
		$pa = (ctype_digit(trim($_GET['pa'])) ? trim($_GET['pa']) : '');
		// phone area code cannot start with 0 or 1
		if (strlen($pa) == 3 && ctype_digit($pa) && $pa >= 200) {
			$sPhone_areaCode = $pa;
		}
	}
	
	if ($pe) {
		$pe = (ctype_digit(trim($_GET['pe'])) ? trim($_GET['pe']) : '');
		// phone area exchange cannot start with 0 or 1
		if (strlen($pe) == 3 && ctype_digit($pe) && $pe >= 200) {
			$sPhone_exchange = $pe;
			if ($_SESSION['sSesTargetExchange'] == '') {
				$_SESSION['sSesTargetExchange'] = $sPhone_exchange;
			}
		}
	}
	
	if ($pnum) {
		$pnum = (ctype_digit(trim($_GET['pnum'])) ? trim($_GET['pnum']) : '');
		if (strlen($pnum) == 4 && ctype_digit($pnum)) {
			$sPhone_number = $pnum;
		}
	}
	
	
	if ($ext) {
		$sPhoneExtension = (ctype_digit(trim($_GET['ext'])) ? trim($_GET['ext']) : '');
	}

	
	if ($src) {
		$sSourceCode = (!(ctype_alnum(trim($_GET['src']))) ? '' : trim($_GET['src']));
	}
	
	if ($ss) {
		$sSubSourceCode = (!(ctype_alnum(trim($_GET['ss']))) ? '' : trim($_GET['ss']));
	}
	
	if ($gn) {
		$sGender = ((strtoupper(trim($_GET['gn'])) == 'M' || strtoupper(trim($_GET['gn'])) == 'F') ? strtoupper(trim($_GET['gn'])) : '');
		if ($_SESSION['sSesTargetGender'] == '') {
			$_SESSION['sSesTargetGender'] = $sGender;
		}
	}
	
	if ($by) {
		$by = (ctype_digit(trim($_GET['by'])) ? trim($_GET['by']) : '');
		if ($by >= date('Y') - 17) {
			$by = '';
		}
		if (strlen($by) == 4 && ctype_digit($by) && $by >= 1910) {
			$iBirthYear = $by;
			if ($_SESSION['sSesTargetYear'] == '') {
				$_SESSION['sSesTargetYear'] = $iBirthYear;
			}
		}
	}
	
	if ($bm) {
		$bm = (ctype_digit(trim($_GET['bm'])) ? trim($_GET['bm']) : '');
		if (strlen($bm) == 2 && ctype_digit($bm) && $bm <= 12) {
			$iBirthMonth = $bm;
		}
	}
	
	if ($bd) {
		$bd = (ctype_digit(trim($_GET['bd'])) ? trim($_GET['bd']) : '');
		$iMaxDay = 31;
		if ($iBirthMonth == 04 || $iBirthMonth == 06 || $iBirthMonth == 09 || $iBirthMonth == 11) {
			$iMaxDay = 30;
		}
		
		if ($iBirthMonth == 02) {
			if ($bd > 28) {
				$bd = ($iBirthYear % 4 == 0 && ($iBirthYear % 100 != 0 || $iBirthYear % 400 == 0)) ? $bd = 29 : $bd = 28;
				$iMaxDay = 29;
			}
		}
	
		if (strlen($bd) == 2 && ctype_digit($bd) && $bd <= $iMaxDay) {
			$iBirthDay = $bd;
		}
	}
	
	// If values are already set in session variables, prepopulate it
	if (session_id()) {
		if ($_SESSION["sSesSalutation"] && !(isset($sSalutation))) {
			$sSalutation = ($_SESSION["sSesSalutation"] != '' ? $_SESSION["sSesSalutation"] : $sSalutation);
		}
		
		if ($_SESSION["sSesEmail"] && $e == '' && !(isset($sEmail))) {
			$sEmail = ($_SESSION["sSesEmail"] != '' ? $_SESSION["sSesEmail"] : $sEmail);
		}
	
		if ($_SESSION["sSesFirst"] && $f == '' && !(isset($sFirst))) {
			$sFirst = ($_SESSION["sSesFirst"] != '' ? $_SESSION["sSesFirst"] : $sFirst);
		}
		
		if ($_SESSION["sSesLast"] && $l == '' && !(isset($sLast))) {
			$sLast = ($_SESSION["sSesLast"] != '' ? $_SESSION["sSesLast"] : $sLast);
		}
		
		if ($_SESSION["sSesAddress"] && $a1 == '' && !(isset($sAddress))) {
			$sAddress = ($_SESSION["sSesAddress"] != '' ? $_SESSION["sSesAddress"] : $sAddress);
		}
		
		if ($_SESSION["sSesAddress2"] && $a2 == '' && ! isset($sAddress2)) {
			$sAddress2 = ($_SESSION["sSesAddress2"] != '' ? $_SESSION["sSesAddress2"] : $sAddress2);
		}
		
		if ($_SESSION["sSesCity"] && $c == '' && !(isset($sCity))) {
			$sCity = ($_SESSION["sSesCity"] != '' ? $_SESSION["sSesCity"] : $sCity);
		}
		
		if ($_SESSION["sSesState"] && $s == '' && !(isset($sState))) {
			$sState = ($_SESSION["sSesState"] != '' ? $_SESSION["sSesState"] : $sState);
		}
		
		if ($_SESSION["sSesZip"] && $z == '' && !(isset($sZip))) {
			$sZip = ($_SESSION["sSesZip"] != '' ? $_SESSION["sSesZip"] : $sZip);
		}
		
		if ($_SESSION["sSesPhone"] && $p == '' && !(isset($sPhone))) {
			$sPhone = ($_SESSION["sSesPhone"] != '' ? $_SESSION["sSesPhone"] : $sPhone);
		}
		
		if ($_SESSION["sSesSourceCode"] && $src =='' && !(isset($sSourceCode))) {
			$sSourceCode = ($_SESSION["sSesSourceCode"] != '' ? $_SESSION["sSesSourceCode"] : $sSourceCode);
		}
		
		if ($_SESSION["sSesSubSourceCode"] && $ss == '' && !(isset($sSubSourceCode))) {
			$sSubSourceCode = ($_SESSION["sSesSubSourceCode"] != '' ? $_SESSION["sSesSubSourceCode"] : $sSubSourceCode);
		}
		
		if ($_SESSION["iSesBirthYear"] && $iBirthYear == '' && !(isset($iBirthYear))) {
			$iBirthYear = ($_SESSION["iSesBirthYear"] != '' ? $_SESSION["iSesBirthYear"] : $iBirthYear);
		}
		
		if ($_SESSION["iSesBirthMonth"] && $iBirthMonth == '' && !(isset($iBirthMonth))) {
			$iBirthMonth = ($_SESSION["iSesBirthMonth"] != '' ? $_SESSION["iSesBirthMonth"] : $iBirthMonth);
		}
		
		if ($_SESSION["iSesBirthDay"] && $iBirthDay == '' && !(isset($iBirthDay))) {
			$iBirthDay = ($_SESSION["iSesBirthDay"] != '' ? $_SESSION["iSesBirthDay"] : $iBirthDay);
		}
		
		if ($_SESSION["sSesGender"] && $sGender == '' && !(isset($sGender))) {
			$sGender = ($_SESSION["sSesGender"] != '' ? $_SESSION["sSesGender"] : $sGender);
		}
		
		if ($_SESSION["sSesPhoneAreaCode"] && $pa == '' && !(isset($sPhone_areaCode))) {
			$sPhone_areaCode = ($_SESSION["sSesPhoneAreaCode"] != '' ? $_SESSION["sSesPhoneAreaCode"] : $sPhone_areaCode);
		}
		
		if ($_SESSION["sSesPhoneExchange"] && $pe == '' && !(isset($sPhone_exchange))) {
			$sPhone_exchange = ($_SESSION["sSesPhoneExchange"] != '' ? $_SESSION["sSesPhoneExchange"] : $sPhone_exchange);
		}
		
		if ($_SESSION["sSesPhoneNumber"] && $pnum == '' && !(isset($sPhone_number))) {
			$sPhone_number = ($_SESSION["sSesPhoneNumber"] != '' ? $_SESSION["sSesPhoneNumber"] : $sPhone_number);
		}
	}
	
	
	// ********************* START - tagetting **************
	if ($_SESSION['sSesTargetZip'] == '' && strlen($_SESSION['sSesZip']) == 5) {
		if (ctype_digit($_SESSION['sSesZip'])) {
			$_SESSION['sSesTargetZip'] = $_SESSION['sSesZip'];
		}
	}
	
	if ($_SESSION['sSesTargetYear'] == '' && strlen($_SESSION['iSesBirthYear']) == 4) {
		if (ctype_digit($_SESSION['iSesBirthYear'])) {
			$_SESSION['sSesTargetYear'] = $_SESSION['iSesBirthYear'];
		}
	}
	
	if ($_SESSION['sSesTargetGender'] == '' && $_SESSION['sSesGender'] !='') {
		if (strtoupper($_SESSION['sSesGender']) == 'M' || strtoupper($_SESSION['sSesGender'] == 'F')) {
			$_SESSION['sSesTargetGender'] = strtoupper($_SESSION['sSesGender']);
		}
	}
	
	if ($_SESSION['sSesTargetState'] == '' && $_SESSION['sSesState'] !='') {
		$_SESSION['sSesTargetState'] = $_SESSION['sSesState'];
	}
	
	if ($_SESSION['sSesTargetExchange'] == '' && strlen($_SESSION['sSesPhoneExchange']) == 3) {
		if (ctype_digit($_SESSION['sSesPhoneExchange']) && !ereg("^[01]{1}", $_SESSION['sSesPhoneExchange'])) {
			$_SESSION['sSesTargetExchange'] = $_SESSION['sSesPhoneExchange'];
		}
	}
	// ********************* END - tagetting **************
	
	######################################################################
	#	ENDS
	#	INCLUDE SCRIPT ENDS
	#	
	######################################################################

	######################################################################
	#	STARTS
	#	OT OFFERS.  THIS SCRIPT WILL BUILD LIST OF OFFERS AND
	#	2ND PAGE QUESTIONS
	######################################################################
	
	$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));

	//var_dump($_SESSION['aSesTemplateId']);
	$sGetTemplate = "SELECT * FROM pageTemplates 
				WHERE id = '".$_SESSION['aSesTemplateId'][$_SESSION['iSesCurrentPositionInFlow']]."'";
	$rTemplateResult = dbQuery($sGetTemplate);
	echo dbError();
	while ($oTemplateRow = dbFetchObject($rTemplateResult)) {
		$_SESSION['sSesTemplateContent'] = $oTemplateRow->templateContent;
		$_SESSION['sSesLastTemplateType'] = ''.$_SESSION['sSesTemplateType'];
		$_SESSION['sSesTemplateType'] = $oTemplateRow->templateType;
		$_SESSION['sSesOneOfferRequired'] = $oTemplateRow->oneOfferReq;
		$_SESSION['sSesEachOfferRequired'] = $oTemplateRow->eachOfferReq;
		$_SESSION['sSesTemplateContent']=ereg_replace("\[PAGE_HASH\]", "'".$_SESSION['iSesCurrentPositionInFlow']."'", $_SESSION['sSesTemplateContent']);
		$_SESSION['sSesTemplateContent']=ereg_replace("\[BACK_SESSION\]",'', $_SESSION['sSesTemplateContent']);
		
		if ($_SESSION['sSesTemplateType'] == '3rdPP' || $_SESSION['sSesTemplateType'] == 'PP') {
			$iTempFlowOrder = $_SESSION['iSesCurrentPositionInFlow'] + 1;
			$sGet3rdPartyDetails = "SELECT frame3rdPartyUrl, frameHeight 
								FROM flowDetails
								WHERE flowId = '".$_SESSION['iSesFlowId']."'
								AND flowOrder = '$iTempFlowOrder'";
			$r3rdPartyDetailsResult = dbQuery($sGet3rdPartyDetails);
			echo dbError();
			while ($o3rdPartyRow = dbFetchObject($r3rdPartyDetailsResult)) {
				$_SESSION['sSesFrame3rdPartyUrl'] = $o3rdPartyRow->frame3rdPartyUrl;
				$_SESSION['sSesRedirect3rdPartyUrl'] = $o3rdPartyRow->frame3rdPartyUrl;
				$_SESSION['i3rdPartyFrameHeight'] = $o3rdPartyRow->frameHeight;
				
				if ($_SESSION['i3rdPartyFrameHeight'] == 0) {
					$_SESSION['i3rdPartyFrameHeight'] == 1500;
				}
			}
		}
	}
	
	$_SESSION['sSesOfferListLayouts'] = '';
	$iTemp = $_SESSION['iSesCurrentPositionInFlow'] + 1;
	$sGetOfferLayout = "SELECT nibbles2OfferLayouts.id, nibbles2OfferLayouts.content FROM nibbles2OfferLayouts, flowDetails
					WHERE flowDetails.offersLayoutId = nibbles2OfferLayouts.id
					AND flowOrder = '".$iTemp."'
					AND flowId = '".$_SESSION['iSesFlowId']."'";
	$rOfferLayout = dbQuery($sGetOfferLayout);
	while ($oLayoutRow = dbFetchObject($rOfferLayout)) {
		$_SESSION['sSesOfferListLayouts'] = $oLayoutRow->content;
	}

	if (count($_SESSION['aSesPage2Offers']) == 0) {
			// Get list of all offers that needs to be excluded because of cap limit
			// put it in array to exclude
			$sCappedOffers = "SELECT offerCaps.* FROM offerCaps, offers
							WHERE offerCaps.offerCode = offers.offerCode
							AND offers.isLive = '1' AND offers.mode = 'A'
							AND (offerCaps.isDailyCap = 'Y' || offerCaps.isWeeklyCap = 'Y' || offerCaps.isMonthlyCap = 'Y' || offerCaps.isLifeTimeCap = 'Y')";
			$rCappedOffers = dbQuery($sCappedOffers);
			while ($oCappedOffersRow = dbFetchObject($rCappedOffers)) {
				$iCurrOfferCount = 0;
				$iWeeklyOfferCount = 0;
				$iMonthlyOfferCount = 0;
				$iLifeTimeOfferCount = 0;
				
				$sGetCurrentCount = "SELECT count(*) AS count
								FROM otData
								WHERE offerCode = '$oCappedOffersRow->offerCode'
								AND SUBSTRING(dateTimeAdded,1,10) = CURRENT_DATE";
				$rCurrCount = dbQuery($sGetCurrentCount);
				while ($oDailyRow = dbFetchObject($rCurrCount)) {
					$iCurrOfferCount = $oDailyRow->count;
				}
				if (($iCurrOfferCount >= $oCappedOffersRow->dailyCap) && $oCappedOffersRow->isDailyCap == 'Y') {
					if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
						array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
					}
					continue;
				}
				
				
				if ($oCappedOffersRow->isLifeTimeCap == 'Y') {
					$sGetLifeTimeCount = "SELECT SUM(offerCount) AS count
									FROM offerCounts
									WHERE offerCode = '$oCappedOffersRow->offerCode'";
					$rGetLifeTimeCount = dbQuery($sGetLifeTimeCount);
					while ($oLifeTimeRow = dbFetchObject($rGetLifeTimeCount)) {
						$iLifeTimeOfferCount = $oLifeTimeRow->count;
					}
					if (($iLifeTimeOfferCount + $iCurrOfferCount) >= $oCappedOffersRow->lifeTimeCap) {
						if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
							array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
						}
						continue;
					}
				}
				

				if ($oCappedOffersRow->isWeeklyCap == 'Y') {
					if (date('D') == 'Sun') {
						if ($iCurrOfferCount >= $oCappedOffersRow->weeklyCap) {
							if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
								array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
							}
							continue;
						}
					} else {
						$sGetWeeklyCount = "SELECT SUM(offerCount) AS count
									FROM offerCounts
									WHERE offerCode = '$oCappedOffersRow->offerCode'
									AND dateAdded >= '".$_SESSION['sSesLastSunday']."'";
						$rGetWeeklyCount = dbQuery($sGetWeeklyCount);
						while ($oWeeklyRow = dbFetchObject($rGetWeeklyCount)) {
							$iWeeklyOfferCount = $oWeeklyRow->count;
						}
						if (($iWeeklyOfferCount + $iCurrOfferCount) >= $oCappedOffersRow->weeklyCap) {
							if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
								array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
							}
							continue;
						}
					}
				}
			
				if ($oCappedOffersRow->isMonthlyCap == 'Y') {
					if (date('d') == 01) {
						if ($iCurrOfferCount >= $oCappedOffersRow->monthlyCap) {
							if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
								array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
							}
							continue;
						}
					} else {
						$sFirstDayOfMonth = date('Y').'-'.date('m').'-01';
						$sGetMonthlyCount = "SELECT SUM(offerCount) AS count
									FROM offerCounts
									WHERE offerCode = '$oCappedOffersRow->offerCode'
									AND dateAdded >= '$sFirstDayOfMonth'";
						$rGetMonthlyCount = dbQuery($sGetMonthlyCount);
						while ($oMonthlyRow = dbFetchObject($rGetMonthlyCount)) {
							$iMonthlyOfferCount = $oMonthlyRow->count;
						}
						if (($iMonthlyOfferCount + $iCurrOfferCount) >= $oCappedOffersRow->monthlyCap) {
							if (!in_array($oCappedOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
								array_push($_SESSION['aExcludeOffers'], $oCappedOffersRow->offerCode);
							}
							continue;
						}
					}
				}
			}
			
		
			// read the cookie and get all offerCodes.  These offers were taken in the past.
			// All offers taken by users, put it in offers to exclude so we don't show that offer again.
			$aOfferTakenInCookie = array();
			if (isset($_COOKIE["OfferTakenInCookie"])) {
				//$_SESSION['sSesOfferTakenInCookie'] = $_COOKIE["OfferTakenInCookie"];
				$aOfferTakenInCookie = explode(",", $_COOKIE["OfferTakenInCookie"]);
				if (count($aOfferTakenInCookie) > 0) {
					foreach ($aOfferTakenInCookie as $sOfferTemp) {
						if (!in_array($sOfferTemp, $_SESSION['aExcludeOffers'])) {
							array_push($_SESSION['aExcludeOffers'], $sOfferTemp);
						}
					}
				}
			}


			// start: get value from sessionId if not blank.
			if ($_SESSION['sSesTargetZip'] != '') {
			    $sTargetZip = $_SESSION['sSesTargetZip'];
			    $sTargetScfZip = substr($_SESSION['sSesTargetZip'],0,3);
			}

			if ($_SESSION['sSesTargetYear'] != '') {
			    $iTargetYear = $_SESSION['sSesTargetYear'];
			}
			
			if ($_SESSION['sSesTargetExchange'] != '') {
			    $iTargetExchange = $_SESSION['sSesTargetExchange'];
			}
			   
			if ($_SESSION['sSesTargetGender'] != '') {
			    $sTargetGender = $_SESSION['sSesTargetGender'];
			}
			
			if ($_SESSION['sSesTargetState'] != '') {
				$sTargetState = $_SESSION['sSesTargetState'];
			}
			// end: get value from sessionId if not blank.
			
			
			$sOffersQuery = "SELECT * FROM offers
							 WHERE isLive = '1'
							 AND   mode = 'A'";
			$rOffersResult = dbQuery($sOffersQuery);
			/// *******************************
			//IMPORTANT : If additional column added into offer array in following while loop,
			//Make sure to add that column in array_multisort function right after this loop
			/// ******************************************
			while ($oOffersRow = dbFetchObject($rOffersResult)) {
				// Targeting:  Determine which offers should be shown to this user.
				// Default is to show the offer
				$bShowOffer = true;
			
				// If offer is not targeted, show it
				// If it is targetted, determine whether to show it or filter it out.
				if($oOffersRow->isTarget == 'Y') {
					// if showIfNoInfoAvailable is false, test fields to determine if we can show
					if ($oOffersRow->targetShowNoInfoAvailable == 'N') {
						if ($sTargetZip == '' && $iTargetYear == '' && $iTargetExchange == '' && $sTargetGender == '' && $sTargetState == '') {
							$bShowOffer = false;
						}
					}
			
					// if we can show offer, test each rule
					if( $bShowOffer == true ) {
						//START YEAR
						if ($iTargetYear != '') {	//if year in session is not blank
							if ($oOffersRow->targetStartYear != 0 && $oOffersRow->targetEndYear != 0) {	//if year range is not blank
								//check if range is include
								if($oOffersRow->targetIncExcYear == 'I') { //if year range is not blank and is include
			
									//if $iTargetYear is within the range
									if($iTargetYear >= $oOffersRow->targetStartYear && $iTargetYear <= $oOffersRow->targetEndYear) {
										//check if database is exclude.
										if ($oOffersRow->targetYearDatabase == 'E') {	//year database is excluded
											//if targetYear is in database
											$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
											$rGetYearResult = dbQuery($sGetYearQuery);
											if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
												while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
													if ($sYearDatabaseRow->year == $iTargetYear) {
														$bShowOffer = false;
													}
												}
											}
										}
									} else { //target year is not within range()
										if ($oOffersRow->targetYearDatabase == 'I') {	//year database is included
											//if database is I
											$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
											$rGetYearResult = dbQuery($sGetYearQuery);
											if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
												$bYearTemp = false;
												while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
													if ($sYearDatabaseRow->year != $iTargetYear) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bYearTemp = true;
													}
												}
												
												if ($bYearTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										 } else {	//if year database is not included, set the value to false
										 	$bShowOffer = false;
										 }
									}
								} elseif ($oOffersRow->targetIncExcYear == 'E') { //(range is exclude)
									//if year is within range()
									if ($iTargetYear >= $oOffersRow->targetStartYear && $iTargetYear <= $oOffersRow->targetEndYear) {
										//if database is I(	)
										if ($oOffersRow->targetYearDatabase == 'I') {	//if year database is included
											//if year not in database
											$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
											$rGetYearResult = dbQuery($sGetYearQuery);
											if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
												$bYearTemp = false;
												while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
													if ($sYearDatabaseRow->year != $iTargetYear) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bYearTemp = true;
													}
												}
												
												if ($bYearTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										} else {	//if year database is not include
											$bShowOffer = false;
										}
									} else {	// year is outside range // see if it's in database exclude
										$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
										$rGetYearResult = dbQuery($sGetYearQuery);
										if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
											while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
												if ($sYearDatabaseRow->year == $iTargetYear) {
													$bShowOffer = false;
												}
											}
										}
									}
								}
							} else { //range is blank, so only check database
								//if database is I
								if ($oOffersRow->targetYearDatabase == 'I') {	//range is blank and year database is include
									$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
									$rGetYearResult = dbQuery($sGetYearQuery);
									if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
										$bYearTemp = false;
										while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
											if ($sYearDatabaseRow->year != $iTargetYear) {
												$bShowOffer = false;
											} else {
												$bShowOffer = true;
												$bYearTemp = true;
											}
										}
										
										if ($bYearTemp == true) {
											$bShowOffer = true;
										} else {
											$bShowOffer = false;
										}
									}
								} elseif ($oOffersRow->targetYearDatabase == 'E') {		//range is blank and year database is exclude
									$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
									$rGetYearResult = dbQuery($sGetYearQuery);
									if (dbNumRows($rGetYearResult) > 0) {	//query retuns records
										while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
											if ($sYearDatabaseRow->year == $iTargetYear) {
												$bShowOffer = false;
											}
										}
									}
								}
							}
						}	//END YEAR
						

						// START TARGET SCF ZIP CODE - 1ST 3 DIGIT OF ZIP CODE
						if ($bShowOffer == true) {	// if offer has scf zip target
							if ($sTargetScfZip != '') {
								if ($oOffersRow->targetStartScfZip != '' && $oOffersRow->targetEndScfZip != '') {
										//check if range is include
										if($oOffersRow->targetIncExcScfZip == 'I') {
											//if $sTargetScfZip is within the range
											if($sTargetScfZip >= $oOffersRow->targetStartScfZip && $sTargetScfZip <= $oOffersRow->targetEndScfZip) {
												//check if database is exclude.
												if ($oOffersRow->targetScfZipDatabase == 'E') {
													//if targetZip is in database
													$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetScfZipResult = dbQuery($sGetScfZipQuery);
													if (dbNumRows($rGetScfZipResult) > 0) {
														while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
															if ($sZipDatabaseRow->zip == $sTargetScfZip) {
																$bShowOffer = false;
															}
														}
													}
												}
											} else { //target zip is not within range()
													if ($oOffersRow->targetScfZipDatabase == 'I') {
													//if database is I
													$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetScfZipResult = dbQuery($sGetScfZipQuery);
													if (dbNumRows($rGetScfZipResult) > 0) {
														$bScfZipTemp = false;
														while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
															if ($sZipDatabaseRow->zip != $sTargetScfZip) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bScfZipTemp = true;
															}
														}
														if ($bScfZipTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												} else {
													$bShowOffer = false;
												}
											}
										} elseif ($oOffersRow->targetIncExcScfZip == 'E') { //(range is exclude)
											//if zip is within range()
											if ($sTargetScfZip >= $oOffersRow->targetStartScfZip && $sTargetScfZip <= $oOffersRow->targetEndScfZip) {
												//if database is I(	)
												if ($oOffersRow->targetScfZipDatabase == 'I') {
													//if zip not in database
													$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetScfZipResult = dbQuery($sGetScfZipQuery);
													if (dbNumRows($rGetScfZipResult) > 0) {
														$bScfZipTemp = false;
														while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
															if ($sZipDatabaseRow->zip != $sTargetScfZip) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bScfZipTemp = true;
															}
														}
														
														if ($bScfZipTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												} else {
													$bShowOffer = false;
												}
											} else {	// zip is outside range	// see if it's in database exclude
												$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip !=''";
												$rGetScfZipResult = dbQuery($sGetScfZipQuery);
												if (dbNumRows($rGetScfZipResult) > 0) {	//if query returns records
													while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
														if ($sZipDatabaseRow->zip == $sTargetScfZip) {
															$bShowOffer = false;
														}
													}
												}
											}
										}
									} else { //range is blank
										if ($oOffersRow->targetScfZipDatabase == 'I') {	//if database is I
											$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
											$rGetScfZipResult = dbQuery($sGetScfZipQuery);
											if (dbNumRows($rGetScfZipResult) > 0) {
												$bScfZipTemp = false;
												while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
												if ($sZipDatabaseRow->zip != $sTargetScfZip) {
													$bShowOffer = false;
												} else {
													$bShowOffer = true;
													$bScfZipTemp = true;
												}
											}
											if ($bScfZipTemp == true) {
												$bShowOffer = true;
											} else {
												$bShowOffer = false;
											}
										}
									} elseif ($oOffersRow->targetScfZipDatabase == 'E') {		//if database is E
										$sGetScfZipQuery = "SELECT substring(zip,1,3) as zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
										$rGetScfZipResult = dbQuery($sGetScfZipQuery);
										if (dbNumRows($rGetScfZipResult) > 0) {
											while ($sZipDatabaseRow = dbFetchObject($rGetScfZipResult)) {
												if ($sZipDatabaseRow->zip == $sTargetScfZip) {
													$bShowOffer = false;
												}
											}
										}
									}
								}
							}
						}
						// END TARGET SCF ZIP CODE - 1ST 3 DIGIT OF ZIP CODE
						
			
	
						if ($bShowOffer == true) {	// if offer has zip target
								if ($sTargetZip != '') {
									if ($oOffersRow->targetStartZip != '' && $oOffersRow->targetEndZip != '') {
										//check if range is include
										if($oOffersRow->targetIncExcZip == 'I') {
											//if $sTargetZip is within the range
											if($sTargetZip >= $oOffersRow->targetStartZip && $sTargetZip <= $oOffersRow->targetEndZip) {
												//check if database is exclude.
												if ($oOffersRow->targetZipDatabase == 'E') {
													//if targetZip is in database
													$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetZipResult = dbQuery($sGetZipQuery);
													if (dbNumRows($rGetZipResult) > 0) {
														while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
															if ($sZipDatabaseRow->zip == $sTargetZip) {
																$bShowOffer = false;
															}
														}
													}
												}
											} else { //target zip is not within range()
													if ($oOffersRow->targetZipDatabase == 'I') {
													//if database is I
													$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetZipResult = dbQuery($sGetZipQuery);
													if (dbNumRows($rGetZipResult) > 0) {
														$bZipTemp = false;
														while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
															if ($sZipDatabaseRow->zip != $sTargetZip) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bZipTemp = true;
															}
														}
														if ($bZipTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												 } else {
												 	$bShowOffer = false;
												 }
											}
										} elseif ($oOffersRow->targetIncExcZip == 'E') { //(range is exclude)
											//if zip is within range()
											if ($sTargetZip >= $oOffersRow->targetStartZip && $sTargetZip <= $oOffersRow->targetEndZip) {
												//if database is I(	)
												if ($oOffersRow->targetZipDatabase == 'I') {
													//if zip not in database
													$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
													$rGetZipResult = dbQuery($sGetZipQuery);
													if (dbNumRows($rGetZipResult) > 0) {
														$bZipTemp = false;
														while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
															if ($sZipDatabaseRow->zip != $sTargetZip) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bZipTemp = true;
															}
														}
														
														if ($bZipTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												} else {
													$bShowOffer = false;
												}
											} else {	// zip is outside range	// see if it's in database exclude
												$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip !=''";
												$rGetZipResult = dbQuery($sGetZipQuery);
												if (dbNumRows($rGetZipResult) > 0) {	//if query returns records
													while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
														if ($sZipDatabaseRow->zip == $sTargetZip) {
															$bShowOffer = false;
														}
													}
												}
											}
										}
									} else { //range is blank
										if ($oOffersRow->targetZipDatabase == 'I') {	//if database is I
											$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
											$rGetZipResult = dbQuery($sGetZipQuery);
											if (dbNumRows($rGetZipResult) > 0) {
												$bZipTemp = false;
												while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
												if ($sZipDatabaseRow->zip != $sTargetZip) {
													$bShowOffer = false;
												} else {
													$bShowOffer = true;
													$bZipTemp = true;
												}
											}
											if ($bZipTemp == true) {
												$bShowOffer = true;
											} else {
												$bShowOffer = false;
											}
										}
									} elseif ($oOffersRow->targetZipDatabase == 'E') {		//if database is E
										$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
										$rGetZipResult = dbQuery($sGetZipQuery);
										if (dbNumRows($rGetZipResult) > 0) {
											while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
												if ($sZipDatabaseRow->zip == $sTargetZip) {
													$bShowOffer = false;
												}
											}
										}
									}
								}
							}
						}
			
						if ($bShowOffer == true) {	// if offer has exchange target
								if ($iTargetExchange != '') {
									if ($oOffersRow->targetStartExchange != '' && $oOffersRow->targetEndExchange != '') {
										//check if range is include
										if($oOffersRow->targetIncExcExchange == 'I') {
											//if $iTargetExchange is within the range
											if($iTargetExchange >= $oOffersRow->targetStartExchange && $iTargetExchange <= $oOffersRow->targetEndExchange) {
												//check if database is exclude.
												if ($oOffersRow->targetExchangeDatabase == 'E') {
													//if targetExchange is in database
													$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
													$rGetExchangeResult = dbQuery($sGetExchangeQuery);
													if (dbNumRows($rGetExchangeResult) > 0) {
														while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
															if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
																$bShowOffer = false;
															}
														}
													}
												}
											} else { //target exchange is not within range()
													if ($oOffersRow->targetExchangeDatabase == 'I') {
													//if database is I
													$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
													$rGetExchangeResult = dbQuery($sGetExchangeQuery);
													if (dbNumRows($rGetExchangeResult) > 0) {
														$bExchangeTemp = false;
														while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
															if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bExchangeTemp = true;
															}
														}
														if ($bExchangeTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												 } else {
												 	$bShowOffer = false;
												 }
											}
										} elseif ($oOffersRow->targetIncExcExchange == 'E') { //(range is exclude)
											//if exchange is within range()
											if ($iTargetExchange >= $oOffersRow->targetStartExchange && $iTargetExchange <= $oOffersRow->targetEndExchange) {
												//if database is I(	)
												if ($oOffersRow->targetExchangeDatabase == 'I') {
													//if exchange not in database
													$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
													$rGetExchangeResult = dbQuery($sGetExchangeQuery);
													if (dbNumRows($rGetExchangeResult) > 0) {
														$bExchangeTemp = false;
														while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
															if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
																$bShowOffer = false;
															} else {
																$bShowOffer = true;
																$bExchangeTemp = true;
															}
														}
														
														if ($bExchangeTemp == true) {
															$bShowOffer = true;	
														} else {
															$bShowOffer = false;
														}
													}
												} else {
													$bShowOffer = false;
												}
											} else {	// exchange is outside range // see if it's in database exclude
												$sGetExchangeQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE exchange !=''";
												$rGetExchangeResult = dbQuery($sGetExchangeQuery);
												if (dbNumRows($rGetExchangeResult) > 0) {	//if query returns records
													while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
														if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
															$bShowOffer = false;
														}
													}
												}
											}
											
										}
									} else { //range is blank
										if ($oOffersRow->targetExchangeDatabase == 'I') {	//if database is I
											$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
											$rGetExchangeResult = dbQuery($sGetExchangeQuery);
											if (dbNumRows($rGetExchangeResult) > 0) {
												$bExchangeTemp = false;
												while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
												if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
													$bShowOffer = false;
												} else {
													$bShowOffer = true;
													$bExchangeTemp = true;
												}
											}
											if ($bExchangeTemp == true) {
												$bShowOffer = true;
											} else {
												$bShowOffer = false;
											}
										}
									} elseif ($oOffersRow->targetExchangeDatabase == 'E') {		//if database is E
										$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
										$rGetExchangeResult = dbQuery($sGetExchangeQuery);
										if (dbNumRows($rGetExchangeResult) > 0) {
											while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
												if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
													$bShowOffer = false;
												}
											}
										}
									}
								}
							}
						}
						
						if ($bShowOffer == true) {	// start gender....
							if ($sTargetGender != '') {
								// if offer has gender target,
								if($oOffersRow->targetGender != '') {	// if include
									if($oOffersRow->targetIncExcGender == 'I') {
										// does it match the user?  // if not, throw it out
										if($oOffersRow->targetGender != $sTargetGender) {
											$bShowOffer = false;
										}
									}
									
									if($oOffersRow->targetIncExcGender == 'E') {	// if exclude
										// does it match the user?	// if so throw it out
										if($oOffersRow->targetGender == $sTargetGender) {
											$bShowOffer = false;
										}
									}
								}
							}
						}
						
						if ($bShowOffer == true && $sTargetState != '' && $oOffersRow->targetState != '') {	// start state....
							$aTempState = explode(",", $oOffersRow->targetState);
							if($oOffersRow->targetIncExcState == 'I') {	// if include
								$bTempStateShow = false;	// does it match the user?  If not, throw it out
								for ($ia=0; $ia<=count($aTempState); $ia++) {
									if($aTempState[$ia] == $sTargetState) {
										$bTempStateShow = true;
									}
								}
								
								if ($bTempStateShow == true) {
									$bShowOffer = true;
								} else {
									$bShowOffer = false;
								}
							}
			
							if($oOffersRow->targetIncExcState == 'E') {	// if exclude
								for ($a=0; $a<=count($aTempState); $a++) {	// does it match the user?  If so throw it out
									if($aTempState[$a] == $sTargetState) {
										$bShowOffer = false;
									}
								}
							}
						}
					}
				}
		
				if ($bShowOffer == false) {
					if (!in_array($oOffersRow->offerCode, $_SESSION['aExcludeOffers'])) {
						array_push($_SESSION['aExcludeOffers'], $oOffersRow->offerCode);
					}
				}
			}


			// get all offer taken from array and exclude them.
			if (count($_SESSION['aOfferTakenForCookie']) > 0) {
				foreach ($_SESSION['aOfferTakenForCookie'] as $asdf) {
					if (!in_array($asdf, $_SESSION['aExcludeOffers']) && !in_array($asdf, $_SESSION['aShowOfferAgain'])) {
						array_push($_SESSION['aExcludeOffers'], $asdf);
					}
				}
			}
			
			
			// Start: If an offer shows, even though it is not selected, it should not show again
			$sDontShowSameOfferAgain = '';
			for ($ii = 0; $ii < $_SESSION['iSesCurrentPositionInFlow']; $ii++) {
				if (is_array($_SESSION['aSesDontShowOfferAgain'][$ii])) {
					$aTempOfferCode = array_values($_SESSION['aSesDontShowOfferAgain'][$ii]);
					if (is_array($aTempOfferCode) && count($aTempOfferCode) > 0) {
						foreach ($aTempOfferCode as $asdf) {
							$sDontShowSameOfferAgain .= "'$asdf',";
							// hide shown offer unless rules say to show the offer again.
							if (!(in_array($asdf,$_SESSION['aShowOfferAgain']))) {
								if (is_array($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']])) {
									if (in_array($asdf, $_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']])) {
										$key = array_search($asdf, $_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]);
										unset($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']][$key]);
									}
								}
							}
						}
					}
				}
			}
			if ($sDontShowSameOfferAgain !='') {
				$sDontShowSameOfferAgain = substr($sDontShowSameOfferAgain,0,strlen($sDontShowSameOfferAgain)-1);
				$sDontShowSameOfferAgain = " AND offers.offerCode NOT IN ($sDontShowSameOfferAgain)";
			}
			// End: If an offer shows, even though it is not selected, it should not show again

			
			
			
			
			// Removes duplicate values from an array
			$_SESSION['aExcludeOffers'] = array_unique($_SESSION['aExcludeOffers']);

			$sAndOfferCodeNotIn = '';
			if (count($_SESSION['aExcludeOffers']) > 0) {
				$sAndOfferCodeNotIn = " AND offers.offerCode NOT IN ('".implode("','",$_SESSION['aExcludeOffers'])."')";
			}
			
			

			if((is_array($_SESSION['aOfferLocations']))&&(in_array($_SESSION['iSesCurrentPositionInFlow'],array_keys($_SESSION['aOfferLocations'])))){
				$iOffersWithPosCount = count($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]);
			} else {
				$iOffersWithPosCount = 0;
			}
			
			$sAndRulesOfferCodeNotIn = '';
			if ($iOffersWithPosCount > 0) {
				$aArrayOfferCode = array_values($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]);
				$aArrayOfferPos = array_keys($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]);
				$sAndRulesOfferCodeNotIn = " AND offers.offerCode NOT IN ('".implode("','",$aArrayOfferCode)."')";
			}
			
			
			// exclude page offers - START
			$sExcludePageOffers = '';
			$sExcludePageOffersFilter = '';
			if (count($_SESSION['aExcludeOffersPages'][$_SESSION['iSesCurrentPositionInFlow']]) > 0) {
				foreach ($_SESSION['aExcludeOffersPages'][$_SESSION['iSesCurrentPositionInFlow']] as $sTmpOffer) {
					$sExcludePageOffers .= "'$sTmpOffer',";
				}
				
				if ($sExcludePageOffers != '') {
					$sExcludePageOffers = substr($sExcludePageOffers,0,strlen($sExcludePageOffers)-1);
					$sExcludePageOffersFilter = " AND offers.offerCode NOT IN ($sExcludePageOffers)";
				}
			}
			// exclude page offers - END
			
			
			
			
			// include page offers - START
			$iCountOffersToIncludeByPage = count($_SESSION['aIncludeOffersPages'][$_SESSION['iSesCurrentPositionInFlow']]);
			$sIncludePageOffersFilter = '';
			$sExcludeFromMainQuery = '';
			$sOffersToIncludeByPage = '';
			if ($iCountOffersToIncludeByPage > 0) {
				foreach ($_SESSION['aIncludeOffersPages'][$_SESSION['iSesCurrentPositionInFlow']] as $sTmpOffer) {
					$sOffersToIncludeByPage .= "'$sTmpOffer',";
				}
				
				if ($sOffersToIncludeByPage != '') {
					$sOffersToIncludeByPage = substr($sOffersToIncludeByPage,0,strlen($sOffersToIncludeByPage)-1);
					$sIncludePageOffersFilter = " AND offers.offerCode IN ($sOffersToIncludeByPage)";
					$sExcludeFromMainQuery = " AND offers.offerCode NOT IN ($sOffersToIncludeByPage)";
				}
			}
			
			// prevent 2 offers of same category on the same page.
			// key is the page number and value is the array of categories.
			$aThisPageCatArray = array();
			$aThisPageCatArray = $_SESSION['aSesMutExcOffersByCat'][$_SESSION['iSesCurrentPositionInFlow']];
			$iMutCatOffersCount = count($_SESSION['aSesMutExcOffersByCat'][$_SESSION['iSesCurrentPositionInFlow']]);
			// include page offers - END
			
			
			$aArray = $_SESSION['aSesMutExcOffersToExcludeByPageRange'][$_SESSION['iSesCurrentPositionInFlow']];
			$sMutExcFilterByPageRange = '';
			if (count($aArray) > 0) {
				$sTemp = '';
				foreach ($aArray as $asdf) {
					$sTemp .= "'$asdf',";
				}
				if ($sTemp !='') {
					$sTemp = substr($sTemp,0,strlen($sTemp)-1);
					$sMutExcFilterByPageRange = " AND offers.offerCode NOT IN ($sTemp)";
				}
			}
	}
	
	$iRedirectCount = 0;
	$sSubmitSkipButton = '';
	$sNoThanksContinueSkip = '';
	$sNonRevenueOffersFilter = '';
	
	// alternate offer bg color - spatel
	$_SESSION['sOfferBgColor1'] = $_SESSION['aSesOfferBgColor'][$_SESSION['iSesCurrentPositionInFlow']][0];
	$_SESSION['sOfferBgColor2'] = $_SESSION['aSesOfferBgColor'][$_SESSION['iSesCurrentPositionInFlow']][1];
	$sNonRevenueOffersFilter = $_SESSION['sSesShowNonRevOffers'];
	
	if ($_SESSION['sSesTemplateType'] == 'BP' || $_SESSION['sSesTemplateType'] == 'SPNS' || $_SESSION['sSesTemplateType'] == 'SPS' || $_SESSION['sSesTemplateType'] == 'RP' || $_SESSION['sSesTemplateType'] == 'FRP') {
		$sOfferFontSize = '11px';

		// If there are no offers in array for 2nd page questions, list offers with checkbox or yes/no
		if (count($_SESSION['aSesPage2Offers']) == 0) {
			/// ************  get the offers and put into array to display on this page  ***********************
			// SORT BY ecpmTotal DESC will list all offers with highest to lowest ecpm.
			// Targetted offers are excluded.
			// Offer code found in cookie are excluded.
			$sTypesOfOffersFilter = '';
			$iTotalShown = 0;

			if ($_SESSION['sSesTemplateType'] == 'BP') {
				// BP: offers has user registration on 2nd page questions
				// SHOW BP OFFERS ONLY
				$iTotalShown = $_SESSION['iSesBPTotalOfferShown'];
				$sTypesOfOffersFilter = " AND offers.offerType = 'BP' ";
			} elseif ($_SESSION['sSesTemplateType'] == 'SPNS') {
				// SHOW ALL OFFERS EXCEPT: BP, OTH, AND OWH
				$sTypesOfOffersFilter = " AND offers.offerType != 'BP'
										  AND offers.offerType != 'OTH'
										  AND offers.offerType != 'OWH' 
										  AND offers.offerType !='' ";
				$iTotalShown = $_SESSION['iSesSPNSTotalOfferShown'];
				
			} elseif ($_SESSION['sSesTemplateType'] == 'SPS') {
				// SHOW ALL OFFERS EXCEPT: BP, OTH, OWH, CTH
				$sTypesOfOffersFilter = " AND offers.offerType != 'BP'
										  AND offers.offerType != 'OTH'
										  AND offers.offerType != 'OWH'
										  AND offers.offerType != 'OTH_CTH'
										  AND offers.offerType != 'CTH' 
										  AND offers.offerType !='' ";
				$iTotalShown = $_SESSION['iSesSPSTotalOfferShown'];
			} elseif ($_SESSION['sSesTemplateType'] == 'RP') {
				$iTotalShown = $_SESSION['iSesRPTotalOfferShown'];
				$sTypesOfOffersFilter = " AND 	(offers.offerType ='CRP' OR offers.offerType = 'CR') ";
			} else {
				$iTotalShown = $_SESSION['iSesFRPTotalOfferShown'];
				$sTypesOfOffersFilter = " AND 	(offers.offerType ='CRP' OR offers.offerType = 'CR') ";
			}

			//For Caps: caps should override rules when placing an offer. Ex.: I've got an offer that has a cap 
			//of just one lead per day, but I've got a rule saying put it on every page on some flow that's getting
			//mail TODAY. So, to be sure that we're getting enough offers to show for this page, caps or otherwise,
			//we've got to find out how many of our offers are ruled in, but also excluded, and keep a count of 
			//them ($iExtraOffers). Later, before we build the final list, we will check this again, offer code 
			//by offer code.
			$iExtraOffers = 0;
			if (is_array($_SESSION['aOfferLocations'])) {
				if (count($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]) > 0) {
					foreach($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']] as $key => $value){
						if(is_array($_SESSION['aExcludeOffers']) && in_array($value,$_SESSION['aExcludeOffers'])) {
							$iExtraOffers += 1;
						}
					}
				}
			}
			$iMaxOffers = $_SESSION['aSesMaxOffers'][$_SESSION['iSesCurrentPositionInFlow']] - $iOffersWithPosCount - $iCountOffersToIncludeByPage + $iExtraOffers;
			$aTempOffersArray = array();
			$aOffersArray = array();
			$i=0;
			$iNoOfLeads = 0;
			$sExcludeTheseOffersFromNextQuery = '';
			$sExcludeTheseOffersFromPreviousQuery = '';
			
			
			// GET OFFERS BY ECPM FROM NY.  OFFERECPM TABLE IS BEING POPULATED BY NY OFFICE EVERY HOUR.
			// IF OFFER NOT LISTED IN OFFERECPM TABLE, THEN GET OFFERS BY ECPM FROM OUR TABLE (offerStatsWorking)
			// AND date_format(offerEcpm.created,'%Y-%m-%d %H:%i:%s')>=date_add(NOW(),INTERVAL -1 HOUR)
			$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
							 FROM   offers LEFT JOIN offerEcpm ON (offers.offerCode = offerEcpm.offerCode AND offerEcpm.ecpm > 0.0)
							 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
							 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
							 WHERE  offers.isLive = '1'
							 AND    offers.mode = 'A'
							 $sNonRevenueOffersFilter
							 AND    offerCompanies.creditStatus = 'ok'
							 $sTypesOfOffersFilter
							 $sAndOfferCodeNotIn
							 $sDontShowSameOfferAgain
							 $sAndRulesOfferCodeNotIn
							 $sExcludePageOffersFilter
							 $sExcludeFromMainQuery
							 $sMutExcFilterByPageRange
							 ORDER BY offerEcpm.ecpm DESC LIMIT $iMaxOffers";
							 //LIMIT  ".$iTotalShown.",$iMaxOffers ";
			//echo $sOffersQuery;
			$rOffersResult = dbQuery($sOffersQuery);
			echo dbError();
			$iNoOfLeads = dbNumRows($rOffersResult);
			if ($iNoOfLeads > 0) {
				while ($oOffersRow = dbFetchObject($rOffersResult)) {
					$sExcludeTheseOffersFromNextQuery .= "'$oOffersRow->offerCode',";
					
					$aTempOffersArray['offerCode'][$i] = $oOffersRow->offerCode;
					$aTempOffersArray['offerHeadline'][$i] = $oOffersRow->headline;
					$aTempOffersArray['offerDescription'][$i] = $oOffersRow->description;
					$aTempOffersArray['offerShortDescription'][$i] = $oOffersRow->shortDescription;
					$aTempOffersArray['offerImageName'][$i] = $oOffersRow->imageName;
					$aTempOffersArray['offerSmallImageName'][$i] = $oOffersRow->smallImageName;
					$aTempOffersArray['offerMediumImageName'][$i] = $oOffersRow->mediumImageName;
					$aTempOffersArray['precheck'][$i] = $oOffersRow->offerPrecheck;
					$aTempOffersArray['addiInfoFormat'][$i] = $oOffersRow->addiInfoFormat;
					$aTempOffersArray['addiInfoTitle'][$i] = $oOffersRow->addiInfoTitle;
					$aTempOffersArray['addiInfoText'][$i] = $oOffersRow->addiInfoText;
					$aTempOffersArray['addiInfoPopupSize'][$i] = $oOffersRow->addiInfoPopupSize;
					$aTempOffersArray['isTopDisplay'][$i] = $oOffersRow->isTopDisplay;
					$aTempOffersArray['privacyPolicy'][$i] = $oOffersRow->privacyPolicy;
						
					// If offer is set to be prechecked on all pages
					if ($oOffersRow->precheckAllPages) {
						$aTempOffersArray['precheck'][$i] = '1';
					}
					
					if( in_array($oOffersRow->offerCode,$_SESSION['aPrecheckOffers'])){
						$aTempOffersArray['precheck'][$i] = '1';
					}
					$i++;
				}
			}
			
			if ($sExcludeTheseOffersFromNextQuery !='') {
				$sExcludeTheseOffersFromNextQuery = substr($sExcludeTheseOffersFromNextQuery,0,strlen($sExcludeTheseOffersFromNextQuery)-1);
				$sExcludeTheseOffersFromPreviousQuery = " AND offers.offerCode NOT IN ($sExcludeTheseOffersFromNextQuery) ";
			}
			$iMaxOffers = ($iMaxOffers - $iNoOfLeads);
			
			if ($iMaxOffers > 0) {
				$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
								 FROM   offers LEFT JOIN offerStatsWorking ON (offers.offerCode = offerStatsWorking.offerCode
								 		AND offerStatsWorking.displayDate = '$sYesterday')
								 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
								 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
								 WHERE  offers.isLive = '1'
								 AND    offers.mode = 'A'
								 $sNonRevenueOffersFilter
								 AND    offerCompanies.creditStatus = 'ok'
								 $sTypesOfOffersFilter
								 $sAndOfferCodeNotIn
								 $sDontShowSameOfferAgain
								 $sAndRulesOfferCodeNotIn
								 $sExcludePageOffersFilter
								 $sExcludeFromMainQuery
								 $sMutExcFilterByPageRange
								 $sExcludeTheseOffersFromPreviousQuery
								 ORDER BY offerStatsWorking.ecpmTotal DESC
								 LIMIT  ".$iTotalShown.",$iMaxOffers";
				$rOffersResult = dbQuery($sOffersQuery);
				echo dbError();
				while ($oOffersRow = dbFetchObject($rOffersResult)) {
					$aTempOffersArray['offerCode'][$i] = $oOffersRow->offerCode;
					$aTempOffersArray['offerHeadline'][$i] = $oOffersRow->headline;
					$aTempOffersArray['offerDescription'][$i] = $oOffersRow->description;
					$aTempOffersArray['offerShortDescription'][$i] = $oOffersRow->shortDescription;
					$aTempOffersArray['offerImageName'][$i] = $oOffersRow->imageName;
					$aTempOffersArray['offerSmallImageName'][$i] = $oOffersRow->smallImageName;
					$aTempOffersArray['offerMediumImageName'][$i] = $oOffersRow->mediumImageName;
					$aTempOffersArray['precheck'][$i] = $oOffersRow->offerPrecheck;
					$aTempOffersArray['addiInfoFormat'][$i] = $oOffersRow->addiInfoFormat;
					$aTempOffersArray['addiInfoTitle'][$i] = $oOffersRow->addiInfoTitle;
					$aTempOffersArray['addiInfoText'][$i] = $oOffersRow->addiInfoText;
					$aTempOffersArray['addiInfoPopupSize'][$i] = $oOffersRow->addiInfoPopupSize;
					$aTempOffersArray['isTopDisplay'][$i] = $oOffersRow->isTopDisplay;
					$aTempOffersArray['privacyPolicy'][$i] = $oOffersRow->privacyPolicy;
						
					// If offer is set to be prechecked on all pages
					if ($oOffersRow->precheckAllPages) {
						$aTempOffersArray['precheck'][$i] = '1';
					}
					
					if( in_array($oOffersRow->offerCode,$_SESSION['aPrecheckOffers'])){
						$aTempOffersArray['precheck'][$i] = '1';
					}
					$i++;
				}
			}
			
			if ($iCountOffersToIncludeByPage > 0) {
				// get offers to include on a page by rules.  don't need to look into offerEcpm table
				$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
							 FROM   offers LEFT JOIN offerStatsWorking ON (offers.offerCode = offerStatsWorking.offerCode
							 		AND offerStatsWorking.displayDate = '$sYesterday')
							 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
							 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
							 WHERE  offers.isLive = '1'
							 AND    offers.mode = 'A'
							 AND    offerCompanies.creditStatus = 'ok'
							 $sIncludePageOffersFilter
							 $sTypesOfOffersFilter
							 $sMutExcFilterByPageRange
							 ORDER BY offerStatsWorking.ecpmTotal DESC
							 LIMIT  ".$iTotalShown.",$iCountOffersToIncludeByPage";
				$rOffersResult = dbQuery($sOffersQuery);
				echo dbError();
				while ($oOffersRow = dbFetchObject($rOffersResult)) {
					$aTempOffersArray['offerCode'][$i] = $oOffersRow->offerCode;
					$aTempOffersArray['offerHeadline'][$i] = $oOffersRow->headline;
					$aTempOffersArray['offerDescription'][$i] = $oOffersRow->description;
					$aTempOffersArray['offerShortDescription'][$i] = $oOffersRow->shortDescription;
					$aTempOffersArray['offerImageName'][$i] = $oOffersRow->imageName;
					$aTempOffersArray['offerSmallImageName'][$i] = $oOffersRow->smallImageName;
					$aTempOffersArray['offerMediumImageName'][$i] = $oOffersRow->mediumImageName;
					$aTempOffersArray['precheck'][$i] = $oOffersRow->offerPrecheck;
					$aTempOffersArray['addiInfoFormat'][$i] = $oOffersRow->addiInfoFormat;
					$aTempOffersArray['addiInfoTitle'][$i] = $oOffersRow->addiInfoTitle;
					$aTempOffersArray['addiInfoText'][$i] = $oOffersRow->addiInfoText;
					$aTempOffersArray['addiInfoPopupSize'][$i] = $oOffersRow->addiInfoPopupSize;
					$aTempOffersArray['isTopDisplay'][$i] = $oOffersRow->isTopDisplay;
					$aTempOffersArray['privacyPolicy'][$i] = $oOffersRow->privacyPolicy;
						
					// If offer is set to be prechecked on all pages
					if ($oOffersRow->precheckAllPages) {
						$aTempOffersArray['precheck'][$i] = '1';
					}
					
					if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
						$aTempOffersArray['precheck'][$i] = '1';
					}
					$i++;
				}
			}

				$index = 0;
				if ($iOffersWithPosCount == 0) {
					$aOffersArray = $aTempOffersArray;
					unset($aTempOffersArray);
				} else {
					for ($ia=0; $ia<$_SESSION['aSesMaxOffers'][$_SESSION['iSesCurrentPositionInFlow']]; $ia++) {
						if (!($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']][$ia])|| in_array($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']][$ia],$_SESSION['aExcludeOffers'])) {
							if ($aTempOffersArray['offerCode'][$index] !='') {
								$aOffersArray['offerCode'][$ia] = $aTempOffersArray['offerCode'][$index];
								//echo $aOffersArray['offerCode'][$ia]."!!<br>";
								$aOffersArray['offerHeadline'][$ia] = $aTempOffersArray['offerHeadline'][$index];
								$aOffersArray['offerDescription'][$ia] = $aTempOffersArray['offerDescription'][$index];
								$aOffersArray['offerShortDescription'][$ia] = $aTempOffersArray['offerShortDescription'][$index];
								$aOffersArray['offerImageName'][$ia] = $aTempOffersArray['offerImageName'][$index];
								$aOffersArray['offerSmallImageName'][$ia] = $aTempOffersArray['offerSmallImageName'][$index];
								$aOffersArray['offerMediumImageName'][$ia] = $aTempOffersArray['offerMediumImageName'][$index];
								$aOffersArray['precheck'][$ia] = $aTempOffersArray['precheck'][$index];
								$aOffersArray['addiInfoFormat'][$ia] = $aTempOffersArray['addiInfoFormat'][$index];
								$aOffersArray['addiInfoTitle'][$ia] = $aTempOffersArray['addiInfoTitle'][$index];
								$aOffersArray['addiInfoText'][$ia] = $aTempOffersArray['addiInfoText'][$index];
								$aOffersArray['addiInfoPopupSize'][$ia] = $aTempOffersArray['addiInfoPopupSize'][$index];
								$aOffersArray['isTopDisplay'][$ia] = $aTempOffersArray['isTopDisplay'][$index];
								$aOffersArray['privacyPolicy'][$ia] = $aTempOffersArray['privacyPolicy'][$index];
								
				
								unset($aTempOffersArray['offerCode'][$index]);
								unset($aTempOffersArray['offerHeadline'][$index]);
								unset($aTempOffersArray['precheck'][$index]);
								unset($aTempOffersArray['offerDescription'][$index]);
								unset($aTempOffersArray['offerShortDescription'][$index]);
								unset($aTempOffersArray['offerMediumImageName'][$index]);
								unset($aTempOffersArray['offerImageName'][$index]);
								unset($aTempOffersArray['offerSmallImageName'][$index]);
								unset($aTempOffersArray['addiInfoFormat'][$index]);
								unset($aTempOffersArray['addiInfoTitle'][$index]);
								unset($aTempOffersArray['addiInfoPopupSize'][$index]);
								unset($aTempOffersArray['addiInfoText'][$index]);
								unset($aTempOffersArray['isTopDisplay'][$index]);
								unset($aTempOffersArray['privacyPolicy'][$index]);
								$index++;
							}
						} else {
							//there's a rule saying put a certain offer here
							if (in_array($ia,array_keys($_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']]))) {
								$tempOfferCodes = $_SESSION['aOfferLocations'][$_SESSION['iSesCurrentPositionInFlow']][$ia];
								//echo "session['aOfferLocations][".$_SESSION['iSesCurrentPositionInFlow']."][$ia] => $tempOfferCodes<br>";
								$sOffersQuery = "SELECT offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
												 FROM   offers, categoryMap, offerCompanies
												 WHERE  offers.offerCode = categoryMap.offerCode
												 AND    offers.companyId = offerCompanies.id	
												 AND    offers.isLive = '1'
												 AND    offers.mode = 'A'
												 AND    offers.offerCode = '$tempOfferCodes'
												 $sTypesOfOffersFilter
												 $sMutExcFilterByPageRange
												 AND    offerCompanies.creditStatus = 'ok'
												 LIMIT 1";
								$rOffersResult = dbQuery($sOffersQuery);
								echo dbError();
								while ($oOffersRow = dbFetchObject($rOffersResult)) {
									$aOffersArray['offerCode'][$ia] = $oOffersRow->offerCode;
									$aOffersArray['offerHeadline'][$ia] = $oOffersRow->headline;
									$aOffersArray['offerDescription'][$ia] = $oOffersRow->description;
									$aOffersArray['offerShortDescription'][$ia] = $oOffersRow->shortDescription;
									$aOffersArray['offerImageName'][$ia] = $oOffersRow->imageName;
									$aOffersArray['offerSmallImageName'][$ia] = $oOffersRow->smallImageName;
									$aOffersArray['offerMediumImageName'][$ia] = $oOffersRow->mediumImageName;
									$aOffersArray['precheck'][$ia] = $oOffersRow->offerPrecheck;
									$aOffersArray['addiInfoFormat'][$ia] = $oOffersRow->addiInfoFormat;
									$aOffersArray['addiInfoTitle'][$ia] = $oOffersRow->addiInfoTitle;
									$aOffersArray['addiInfoText'][$ia] = $oOffersRow->addiInfoText;
									$aOffersArray['addiInfoPopupSize'][$ia] = $oOffersRow->addiInfoPopupSize;
									$aOffersArray['isTopDisplay'][$ia] = $oOffersRow->isTopDisplay;
									$aOffersArray['privacyPolicy'][$ia] = $oOffersRow->privacyPolicy;
									
									// If offer is set to be prechecked on all pages
									if ($oOffersRow->precheckAllPages) {
										$aOffersArray['precheck'][$ia] = '1';
									}
									if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
										$aOffersArray['precheck'][$ia] = '1';
									}
								}
							}
						}
					}
				}


				// insert into temporary page display counts table
				$sCurrentDateTime = date('Y-m-d H:i:s');
				$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate,
								 sessionId, ipAddress, openDateTime)
								   VALUES('".$_SESSION['iSesPageId']."', '".$_SESSION['sSesSourceCode']."', 
								   '".$_SESSION['sSesSubSourceCode']."',CURRENT_DATE,'".session_id()."', 
								   '".$_SERVER['REMOTE_ADDR']."', '$sCurrentDateTime')";
				//$rPageStatResult = dbQuery($sPageStatQuery);
				echo dbError();
				
				
				$sExcludeSameCategoryOffers = '';
				if ($iMutCatOffersCount > 0) {
					$sCatIn = '';
					foreach ($aThisPageCatArray as $sTempCat) {
						$sCatIn .= "'$sTempCat',";
					}
					$sCatIn = substr($sCatIn,0,strlen($sCatIn)-1);
					$sMutCatOffers = "SELECT offerCode FROM categoryMap, categories
									WHERE categoryMap.categoryId = categories.id
									AND title IN ($sCatIn)";
					$rMutCatOffers = dbQuery($sMutCatOffers);
					if (dbNumRows($rMutCatOffers) > 0) {
						while ($oCatRow = dbFetchObject($rMutCatOffers)) {
							$sExcludeSameCategoryOffers .= "'$oCatRow->offerCode',";
						}
						$sExcludeSameCategoryOffers = substr($sExcludeSameCategoryOffers,0,strlen($sExcludeSameCategoryOffers)-1);
						$sExcludeSameCategoryOffers = " AND offers.offerCode NOT IN ($sExcludeSameCategoryOffers) ";
					}
				}


				// START - PREVENT 2 OFFERS OF SAME CATEGORY ON THE SAME PAGE
				$iCatGetMoreOffers = 0;
				do {
					if ($iMutCatOffersCount > 0) {
						$sOfferFilter = '';
						foreach ($aOffersArray['offerCode'] as $asdf) {
							$sOfferFilter .= "'$asdf',";
						}
						if ($sOfferFilter !='') {
							$sOfferFilter = substr($sOfferFilter,0,strlen($sOfferFilter)-1);
						}
						
						foreach ($aThisPageCatArray as $sTempCat) {
							$sTempCatOffers = '';
							$sMutCatOffers = "SELECT offerCode FROM categoryMap, categories
											WHERE categoryMap.categoryId = categories.id
											AND title = '$sTempCat'
											AND offerCode IN ($sOfferFilter) LIMIT 2";
							$rMutCatOffers = dbQuery($sMutCatOffers);
							if (dbNumRows($rMutCatOffers) == 2) {
								while ($oCatRow = dbFetchObject($rMutCatOffers)) {
									$sTempCatOffers .= "$oCatRow->offerCode,";
									break;
								}
							}
						}
						
						//echo $sMutCatOffers;
						if ($sTempCatOffers !='') {
							$sTempCatOffers = substr($sTempCatOffers,0,strlen($sTempCatOffers)-1);
							$aCatOffersToExclude = explode(',',$sTempCatOffers);
						}
						//print_r($aCatOffersToExclude);
						
						$iCatGetMoreOffers = 0;
						if (count($aCatOffersToExclude) > 0) {
							foreach ($aCatOffersToExclude as $sTempCatOc) {
								if (in_array($sTempCatOc, $aOffersArray['offerCode'])) {
									$iKey = array_search($sTempCatOc, $aOffersArray['offerCode']);
									unset($aOffersArray['offerCode'][$iKey]);
									unset($aOffersArray['offerHeadline'][$iKey]);
									unset($aOffersArray['precheck'][$iKey]);
									unset($aOffersArray['offerDescription'][$iKey]);
									unset($aOffersArray['offerShortDescription'][$iKey]);
									unset($aOffersArray['offerImageName'][$iKey]);
									unset($aOffersArray['offerSmallImageName'][$iKey]);
									unset($aOffersArray['offerMediumImageName'][$iKey]);
									unset($aOffersArray['addiInfoFormat'][$iKey]);
									unset($aOffersArray['addiInfoTitle'][$iKey]);
									unset($aOffersArray['addiInfoPopupSize'][$iKey]);
									unset($aOffersArray['addiInfoText'][$iKey]);
									unset($aOffersArray['isTopDisplay'][$iKey]);
									unset($aOffersArray['privacyPolicy'][$iKey]);
									$iCatGetMoreOffers++;
								}
							}

					
							if ($iCatGetMoreOffers > 0) {
								$sCatOffersToExcludeFromQuery = '';
								$sCatExcFilter = '';
								foreach ($aOffersArray['offerCode'] as $offer) {
									$sCatOffersToExcludeFromQuery .= "'$offer',";
								}
								if ($sCatOffersToExcludeFromQuery !='') {
									$sCatOffersToExcludeFromQuery = substr($sCatOffersToExcludeFromQuery,0,strlen($sCatOffersToExcludeFromQuery)-1);
									$sCatExcFilter = " AND offers.offerCode NOT IN ($sCatOffersToExcludeFromQuery) ";
								}
								
							
								$iCatStillGetMoreOffers = 0;
								$sExcludeTheseOffersFromNextQuery = '';
								$sExcludeTheseOffersFromPreviousQuery = '';
								
								// GET OFFERS BY ECPM FROM NY.  OFFERECPM TABLE IS BEING POPULATED BY NY OFFICE EVERY HOUR.
								// IF OFFER NOT LISTED IN OFFERECPM TABLE, THEN GET OFFERS BY ECPM FROM OUR TABLE (offerStatsWorking)
								// AND date_format(offerEcpm.created,'%Y-%m-%d %H:%i:%s')>=date_add(NOW(),INTERVAL -1 HOUR)
								$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
										FROM   offers RIGHT JOIN offerEcpm ON (offers.offerCode = offerEcpm.offerCode AND offerEcpm.ecpm > 0.0)
									 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
									 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
										WHERE  offers.isLive = '1'
										AND    offers.mode = 'A'
										$sNonRevenueOffersFilter
										AND    offerCompanies.creditStatus = 'ok'
										$sTypesOfOffersFilter
										$sAndOfferCodeNotIn
										$sDontShowSameOfferAgain
										$sCatExcFilter
										$sExcludePageOffersFilter
										$sMutExcFilterByPageRange
										ORDER BY offerEcpm.ecpm DESC
										LIMIT  ".$iTotalShown.",$iCatGetMoreOffers";
								$rOffersResult = dbQuery($sOffersQuery);
								echo dbError();
								$iNoOfLeads = 0;
								$iNoOfLeads = dbNumRows($rOffersResult);
								if ($iNoOfLeads > 0) {
									while ($oOffersRow = dbFetchObject($rOffersResult)) {
										$sExcludeTheseOffersFromNextQuery .= "'$oOffersRow->offerCode',";
										
										array_push($aOffersArray['offerCode'], $oOffersRow->offerCode);
										array_push($aOffersArray['offerHeadline'], $oOffersRow->headline);
										array_push($aOffersArray['offerDescription'], $oOffersRow->description);
										array_push($aOffersArray['offerShortDescription'], $oOffersRow->shortDescription);
										array_push($aOffersArray['offerImageName'], $oOffersRow->imageName);
										array_push($aOffersArray['offerSmallImageName'], $oOffersRow->smallImageName);
										array_push($aOffersArray['offerMediumImageName'], $oOffersRow->mediumImageName);
										//array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
										array_push($aOffersArray['addiInfoFormat'], $oOffersRow->addiInfoFormat);
										array_push($aOffersArray['addiInfoTitle'], $oOffersRow->addiInfoTitle);
										array_push($aOffersArray['addiInfoText'], $oOffersRow->addiInfoText);
										array_push($aOffersArray['addiInfoPopupSize'], $oOffersRow->addiInfoPopupSize);
										array_push($aOffersArray['isTopDisplay'], $oOffersRow->isTopDisplay);
										array_push($aOffersArray['privacyPolicy'], $oOffersRow->privacyPolicy);				
											
										if ($oOffersRow->precheckAllPages) {
											// If offer is set to be prechecked on all pages
											array_push($aOffersArray['precheck'],'1');
										} else if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
											array_push($aOffersArray['precheck'],'1');
										} else {
											array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
										}
									}
								}
									
								if ($sExcludeTheseOffersFromNextQuery !='') {
									$sExcludeTheseOffersFromNextQuery = substr($sExcludeTheseOffersFromNextQuery,0,strlen($sExcludeTheseOffersFromNextQuery)-1);
									$sExcludeTheseOffersFromPreviousQuery = " AND offers.offerCode NOT IN ($sExcludeTheseOffersFromNextQuery) ";
								}
								$iCatStillGetMoreOffers = ($iCatGetMoreOffers - $iNoOfLeads);
								
								
								
								if ($iCatStillGetMoreOffers > 0) {
									$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
											FROM   offers LEFT JOIN offerStatsWorking ON (offers.offerCode = offerStatsWorking.offerCode
										 		AND offerStatsWorking.displayDate = '$sYesterday')
										 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
										 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
											WHERE  offers.isLive = '1'
											AND    offers.mode = 'A'
											$sNonRevenueOffersFilter
											AND    offerCompanies.creditStatus = 'ok'
											$sTypesOfOffersFilter
											$sAndOfferCodeNotIn
											$sDontShowSameOfferAgain
											$sCatExcFilter
											$sExcludePageOffersFilter
											$sMutExcFilterByPageRange
											$sExcludeTheseOffersFromPreviousQuery
											ORDER BY offerStatsWorking.ecpmTotal DESC
											LIMIT  ".$iTotalShown.",$iCatStillGetMoreOffers";
									$rOffersResult = dbQuery($sOffersQuery);
									while ($oOffersRow = dbFetchObject($rOffersResult)) {
										array_push($aOffersArray['offerCode'], $oOffersRow->offerCode);
										array_push($aOffersArray['offerHeadline'], $oOffersRow->headline);
										array_push($aOffersArray['offerDescription'], $oOffersRow->description);
										array_push($aOffersArray['offerShortDescription'], $oOffersRow->shortDescription);
										array_push($aOffersArray['offerImageName'], $oOffersRow->imageName);
										array_push($aOffersArray['offerSmallImageName'], $oOffersRow->smallImageName);
										array_push($aOffersArray['offerMediumImageName'], $oOffersRow->mediumImageName);
										//array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
										array_push($aOffersArray['addiInfoFormat'], $oOffersRow->addiInfoFormat);
										array_push($aOffersArray['addiInfoTitle'], $oOffersRow->addiInfoTitle);
										array_push($aOffersArray['addiInfoText'], $oOffersRow->addiInfoText);
										array_push($aOffersArray['addiInfoPopupSize'], $oOffersRow->addiInfoPopupSize);
										array_push($aOffersArray['isTopDisplay'], $oOffersRow->isTopDisplay);
										array_push($aOffersArray['privacyPolicy'], $oOffersRow->privacyPolicy);				
										
										if ($oOffersRow->precheckAllPages) {
											// If offer is set to be prechecked on all pages
											array_push($aOffersArray['precheck'],'1');
										} else if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
											array_push($aOffersArray['precheck'],'1');
										} else {
											array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
										}
									}
								}

								$tempOffersArray = array('offerCode'=>array(), 
													'offerHeadline'=>array(), 
													'precheck'=>array(),
													'offerDescription'=>array(),
													'offerShortDescription'=>array(),
													'offerImageName'=>array(),
													'offerMediumImageName'=>array(),
													'offerSmallImageName'=>array(),
													'addiInfoFormat'=>array(),
													'addiInfoTitle'=>array(),
													'addiInfoPopupSize'=>array(),
													'addiInfoText'=>array(),
													'isTopDisplay'=>array(),
													'privacyPolicy'=>array());
								for ($y=0;$y<count($aOffersArray['offerCode']);$y++) {
									if ($aOffersArray['offerCode'][$y] != NULL) {
										array_push($tempOffersArray['offerCode'], $aOffersArray['offerCode'][$y]);
										array_push($tempOffersArray['offerHeadline'],$aOffersArray['offerHeadline'][$y]);
										array_push($tempOffersArray['precheck'],$aOffersArray['precheck'][$y]);
										array_push($tempOffersArray['offerDescription'],$aOffersArray['offerDescription'][$y]);
										array_push($tempOffersArray['offerShortDescription'],$aOffersArray['offerShortDescription'][$y]);
										array_push($tempOffersArray['offerImageName'],$aOffersArray['offerImageName'][$y]);
										array_push($tempOffersArray['offerSmallImageName'],$aOffersArray['offerSmallImageName'][$y]);
										array_push($tempOffersArray['offerMediumImageName'],$aOffersArray['offerMediumImageName'][$y]);
										array_push($tempOffersArray['addiInfoFormat'],$aOffersArray['addiInfoFormat'][$y]);
										array_push($tempOffersArray['addiInfoTitle'],$aOffersArray['addiInfoTitle'][$y]);
										array_push($tempOffersArray['addiInfoPopupSize'],$aOffersArray['addiInfoPopupSize'][$y]);
										array_push($tempOffersArray['addiInfoText'],$aOffersArray['addiInfoText'][$y]);
										array_push($tempOffersArray['isTopDisplay'],$aOffersArray['isTopDisplay'][$y]);
										array_push($tempOffersArray['privacyPolicy'],$aOffersArray['privacyPolicy'][$y]);
									}
								}
								$aOffersArray = $tempOffersArray;
							}
						}
					}
				} while($iCatGetMoreOffers != 0);
				// END - PREVENT 2 OFFERS OF SAME CATEGORY ON THE SAME PAGE
				
				
				
				
				
				$aMutExcOffersArray = array();
				
				// START MUTUALLY EXCLUSIVE OFFERS
				$iGetMoreOffers = 0;
				do {
					$aTempMutExcOffer = array();
					$sMutOfferCodeToExclude = '';
					for ($iCounter=0; $iCounter<count($aOffersArray['offerCode']); $iCounter++) {
						$sCheckMutExcOffer = $aOffersArray['offerCode'][$iCounter];
						
						$sMutExclusiveQuery = "SELECT * FROM offersMutExclusive
								   WHERE  offerCode1 = '$sCheckMutExcOffer'
								   OR     offerCode2 = '$sCheckMutExcOffer'";
						$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
						if (dbNumRows($rMutExclusiveResult) > 0 ) {
							while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
								if ($sCheckMutExcOffer == $oMutExclusiveRow->offerCode1) {
									$sMutOfferCodeToExclude .= "$oMutExclusiveRow->offerCode2,";
								} else {
									$sMutOfferCodeToExclude .= "$oMutExclusiveRow->offerCode1,";
								}
								break;
							}
						}
					}
					
					if ($sMutOfferCodeToExclude !='') {
						$sMutOfferCodeToExclude = substr($sMutOfferCodeToExclude,0,strlen($sMutOfferCodeToExclude)-1);
						$aTempMutExcOffer = explode(',',$sMutOfferCodeToExclude);
						foreach ($aTempMutExcOffer as $asdf) {
							if (!in_array($asdf, $aMutExcOffersArray)) {
								array_push($aMutExcOffersArray, $asdf);
							}
						}
					}
					
					$iGetMoreOffers = 0;
					if (count($aTempMutExcOffer) > 0) {
						//mail('bbevis@amperemedia.com','count is good for mutually exclusive offers on ot.php line '.__LINE__,'');
						foreach ($aTempMutExcOffer as $sTempOc) {
							if (in_array($sTempOc, $aOffersArray['offerCode'])) {
								$iKey = array_search($sTempOc, $aOffersArray['offerCode']);
								unset($aOffersArray['offerCode'][$iKey]);
								unset($aOffersArray['offerMediumImageName'][$iKey]);
								unset($aOffersArray['offerHeadline'][$iKey]);
								unset($aOffersArray['precheck'][$iKey]);
								unset($aOffersArray['offerDescription'][$iKey]);
								unset($aOffersArray['offerShortDescription'][$iKey]);
								unset($aOffersArray['offerImageName'][$iKey]);
								unset($aOffersArray['offerSmallImageName'][$iKey]);
								unset($aOffersArray['addiInfoFormat'][$iKey]);
								unset($aOffersArray['addiInfoTitle'][$iKey]);
								unset($aOffersArray['addiInfoPopupSize'][$iKey]);
								unset($aOffersArray['addiInfoText'][$iKey]);
								unset($aOffersArray['isTopDisplay'][$iKey]);
								unset($aOffersArray['privacyPolicy'][$iKey]);
								$iGetMoreOffers++;
							}
						}
						
						// Get more offers
						if ($iGetMoreOffers > 0) {
							$sMutOffersToExcludeFromQuery = '';
							$sMutExcFilter = '';
							foreach ($aOffersArray['offerCode'] as $offer) {
								$sMutOffersToExcludeFromQuery .= "'$offer',";
							}
							if ($sMutOffersToExcludeFromQuery !='') {
								$sMutOffersToExcludeFromQuery = substr($sMutOffersToExcludeFromQuery,0,strlen($sMutOffersToExcludeFromQuery)-1);
								$sMutExcFilter = " AND offers.offerCode NOT IN ($sMutOffersToExcludeFromQuery) ";
							}
							
							
							$iStillGetMoreOffers = 0;
							$sExcludeTheseOffersFromNextQuery = '';
							$sExcludeTheseOffersFromPreviousQuery = '';
							
							// GET OFFERS BY ECPM FROM NY.  OFFERECPM TABLE IS BEING POPULATED BY NY OFFICE EVERY HOUR.
							// IF OFFER NOT LISTED IN OFFERECPM TABLE, THEN GET OFFERS BY ECPM FROM OUR TABLE (offerStatsWorking)
							// AND date_format(offerEcpm.created,'%Y-%m-%d %H:%i:%s')>=date_add(NOW(),INTERVAL -1 HOUR)
							$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
									 FROM   offers RIGHT JOIN offerEcpm ON (offers.offerCode = offerEcpm.offerCode AND offerEcpm.ecpm > 0.0)
									 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
									 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
									 WHERE  offers.isLive = '1'
									 AND    offers.mode = 'A'
									 $sNonRevenueOffersFilter
									 AND    offerCompanies.creditStatus = 'ok'
									 $sExcludeSameCategoryOffers
									 $sTypesOfOffersFilter
									 $sAndOfferCodeNotIn
									 $sDontShowSameOfferAgain
									 $sMutExcFilter
									 $sExcludePageOffersFilter
									 $sMutExcFilterByPageRange
									 ORDER BY offerEcpm.ecpm DESC
									 LIMIT  ".$iTotalShown.",$iGetMoreOffers";
							$rOffersResult = dbQuery($sOffersQuery);
							echo dbError();
							$iNoOfLeads = 0;
							$iNoOfLeads = dbNumRows($rOffersResult);
							if ($iNoOfLeads > 0) {
								while ($oOffersRow = dbFetchObject($rOffersResult)) {
									$sExcludeTheseOffersFromNextQuery .= "'$oOffersRow->offerCode',";
									
									array_push($aOffersArray['offerCode'], $oOffersRow->offerCode);
									array_push($aOffersArray['offerHeadline'], $oOffersRow->headline);
									array_push($aOffersArray['offerDescription'], $oOffersRow->description);
									array_push($aOffersArray['offerShortDescription'], $oOffersRow->shortDescription);
									array_push($aOffersArray['offerImageName'], $oOffersRow->imageName);
									array_push($aOffersArray['offerSmallImageName'], $oOffersRow->smallImageName);
									//array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
									array_push($aOffersArray['addiInfoFormat'], $oOffersRow->addiInfoFormat);
									array_push($aOffersArray['addiInfoTitle'], $oOffersRow->addiInfoTitle);
									array_push($aOffersArray['addiInfoText'], $oOffersRow->addiInfoText);
									array_push($aOffersArray['addiInfoPopupSize'], $oOffersRow->addiInfoPopupSize);
									array_push($aOffersArray['isTopDisplay'], $oOffersRow->isTopDisplay);
									array_push($aOffersArray['offerMediumImageName'], $oOffersRow->mediumImageName);
									array_push($aOffersArray['privacyPolicy'], $oOffersRow->privacyPolicy);
									
									if ($oOffersRow->precheckAllPages) {
										// If offer is set to be prechecked on all pages
										array_push($aOffersArray['precheck'],'1');
									} else if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
										array_push($aOffersArray['precheck'],'1');
									} else {
										array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
									}
								}
							}
							
							if ($sExcludeTheseOffersFromNextQuery !='') {
								$sExcludeTheseOffersFromNextQuery = substr($sExcludeTheseOffersFromNextQuery,0,strlen($sExcludeTheseOffersFromNextQuery)-1);
								$sExcludeTheseOffersFromPreviousQuery = " AND offers.offerCode NOT IN ($sExcludeTheseOffersFromNextQuery) ";
							}
							$iStillGetMoreOffers = ($iGetMoreOffers - $iNoOfLeads);
							

							if ($iStillGetMoreOffers > 0) {
								$sOffersQuery = "SELECT distinct offers.offerCode, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
									 FROM   offers LEFT JOIN offerStatsWorking ON (offers.offerCode = offerStatsWorking.offerCode
									 		AND offerStatsWorking.displayDate = '$sYesterday')
									 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
									 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
									 WHERE  offers.isLive = '1'
									 AND    offers.mode = 'A'
									 $sNonRevenueOffersFilter
									 AND    offerCompanies.creditStatus = 'ok'
									 $sExcludeSameCategoryOffers
									 $sTypesOfOffersFilter
									 $sAndOfferCodeNotIn
									 $sDontShowSameOfferAgain
									 $sMutExcFilter
									 $sExcludePageOffersFilter
									 $sMutExcFilterByPageRange
									 $sExcludeTheseOffersFromPreviousQuery
									 ORDER BY offerStatsWorking.ecpmTotal DESC
									 LIMIT  ".$iTotalShown.",$iStillGetMoreOffers";
								$rOffersResult = dbQuery($sOffersQuery);
								while ($oOffersRow = dbFetchObject($rOffersResult)) {
									array_push($aOffersArray['offerCode'], $oOffersRow->offerCode);
									array_push($aOffersArray['offerHeadline'], $oOffersRow->headline);
									array_push($aOffersArray['offerDescription'], $oOffersRow->description);
									array_push($aOffersArray['offerShortDescription'], $oOffersRow->shortDescription);
									array_push($aOffersArray['offerImageName'], $oOffersRow->imageName);
									array_push($aOffersArray['offerSmallImageName'], $oOffersRow->smallImageName);
									//array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
									array_push($aOffersArray['addiInfoFormat'], $oOffersRow->addiInfoFormat);
									array_push($aOffersArray['addiInfoTitle'], $oOffersRow->addiInfoTitle);
									array_push($aOffersArray['addiInfoText'], $oOffersRow->addiInfoText);
									array_push($aOffersArray['addiInfoPopupSize'], $oOffersRow->addiInfoPopupSize);
									array_push($aOffersArray['isTopDisplay'], $oOffersRow->isTopDisplay);
									array_push($aOffersArray['offerMediumImageName'], $oOffersRow->mediumImageName);
									array_push($aOffersArray['privacyPolicy'], $oOffersRow->privacyPolicy);
									
									if ($oOffersRow->precheckAllPages) {
										// If offer is set to be prechecked on all pages
										array_push($aOffersArray['precheck'],'1');
									} else if(in_array($oOffersRow->offerCode, $_SESSION['aPrecheckOffers'])){
										array_push($aOffersArray['precheck'],'1');
									} else {
										array_push($aOffersArray['precheck'], $oOffersRow->offerPrecheck);
									}
								}
							}
						}
						
						if ($iGetMoreOffers > 0) {
							$tempOffersArray = array('offerCode'=>array(), 
													'offerHeadline'=>array(), 
													'precheck'=>array(),
													'offerDescription'=>array(),
													'offerShortDescription'=>array(),
													'offerImageName'=>array(),
													'offerMediumImageName'=>array(),
													'offerSmallImageName'=>array(),
													'addiInfoFormat'=>array(),
													'addiInfoTitle'=>array(),
													'addiInfoPopupSize'=>array(),
													'addiInfoText'=>array(),
													'isTopDisplay'=>array(),
													'privacyPolicy'=>array());
							for ($y=0;$y<count($aOffersArray['offerCode']);$y++) {
								if ($aOffersArray['offerCode'][$y] != NULL) {
									array_push($tempOffersArray['offerCode'], $aOffersArray['offerCode'][$y]);
									array_push($tempOffersArray['offerHeadline'],$aOffersArray['offerHeadline'][$y]);
									array_push($tempOffersArray['precheck'],$aOffersArray['precheck'][$y]);
									array_push($tempOffersArray['offerDescription'],$aOffersArray['offerDescription'][$y]);
									array_push($tempOffersArray['offerShortDescription'],$aOffersArray['offerShortDescription'][$y]);
									array_push($tempOffersArray['offerImageName'],$aOffersArray['offerImageName'][$y]);
									array_push($tempOffersArray['offerSmallImageName'],$aOffersArray['offerSmallImageName'][$y]);
									array_push($tempOffersArray['addiInfoFormat'],$aOffersArray['addiInfoFormat'][$y]);
									array_push($tempOffersArray['addiInfoTitle'],$aOffersArray['addiInfoTitle'][$y]);
									array_push($tempOffersArray['addiInfoPopupSize'],$aOffersArray['addiInfoPopupSize'][$y]);
									array_push($tempOffersArray['addiInfoText'],$aOffersArray['addiInfoText'][$y]);
									array_push($tempOffersArray['isTopDisplay'],$aOffersArray['isTopDisplay'][$y]);
									array_push($tempOffersArray['offerMediumImageName'],$aOffersArray['offerMediumImageName'][$y]);
									array_push($tempOffersArray['privacyPolicy'],$aOffersArray['privacyPolicy'][$y]);
								}
							}
							$aOffersArray = $tempOffersArray;
						}
					}
				} while($iGetMoreOffers != 0);
				// END MUTUALLY EXCLUSIVE OFFERS
				
				
				
				

				foreach ($aMutExcOffersArray as $asdf) {
					$sGetRulesOffersQuery = "SELECT * FROM rules 
							WHERE mutExcCat = 'Y'
							AND (global = 'Y' OR linkId = '".$_SESSION['iSesLinkId']."' OR flowId = '".$_SESSION['iSesFlowId']."')
							AND offerCode ='$asdf'";
					$sRulesOffersResult = dbQuery($sGetRulesOffersQuery);
					if (dbNumRows($sRulesOffersResult) > 0 ) {
						while($oRow12 = dbFetchObject($sRulesOffersResult)) {
							
							$sMutExclusiveQuery = "SELECT * FROM offersMutExclusive
										   WHERE  offerCode1 = '$asdf'
										   OR     offerCode2 = '$asdf'";
							$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
							if (dbNumRows($rMutExclusiveResult) > 0 ) {
								while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
									if ($asdf == $oMutExclusiveRow->offerCode1) {
										$sMutOfferCodeToExclude = $oMutExclusiveRow->offerCode2;
									} else {
										$sMutOfferCodeToExclude = $oMutExclusiveRow->offerCode1;
									}
									
									if ($oRow12->sMutExcRange == 'flow') {
										if (!in_array($sMutOfferCodeToExclude, $_SESSION['aExcludeOffers'])) {
											array_push($_SESSION['aExcludeOffers'], $sMutOfferCodeToExclude);
										}
									} else if (strstr($oRow12->sMutExcRange,'range')) {
										$iStart = $oRow12->pageNo;
										$iEnd = $iStart + trim(substr($oRow12->sMutExcRange,5,2)) - 1;
										//$_SESSION['aSesMutExcOffersToExcludeByPageRange'] = array();
										for ($x=$iStart; $x<=$iEnd; $x++) {
											if (!(is_array($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x]))) {
												$_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x] = array();
											}
											array_push($_SESSION['aSesMutExcOffersToExcludeByPageRange'][$x],$sMutOfferCodeToExclude);
										}
									}
								}
							}
						}
					}
				}
				
				if(is_array($aOffersArray['offerCode'])){
					$iRedirectCount = count(array_keys($aOffersArray['offerCode']));
				} else {
					$iRedirectCount = 0;
				}


				if (strstr($_SESSION['sSesOfferListLayouts'], "YES_NO_OFFERS")) {
					$iDisplayYesNo = 1;	// yes/no offers
					$sOfferTypeHidden = "<input type='hidden' name='sOfferTypeHidden' value='YN'>";
				} else {
					$iDisplayYesNo = '';	// checkbox offers
					$sOfferTypeHidden = "<input type='hidden' name='sOfferTypeHidden' value='CK'>";
				}
		
				$k=0;
				$sTempJavaScriptOffersValidation = '';
				$sTempJavaScriptOffersValidation2 = '';
				$sJavaScriptOffersValidation = '';
				$sJavaScriptOffersAtLeastOneCheckedYes= '';
				$sErrorMessage = '';
				$sErrorMessageAllOffer = '';
				$sErrorMessage1Offer = '';
				
				if(is_array($aOffersArray['offerCode'])){
					$aOffersArrayOfferCodeKeys = array_keys($aOffersArray['offerCode']);
				} else {
					$aOffersArrayOfferCodeKeys = array();
				}
				
				// if array is empty and no offers to display, then send the user to next page.
				if (count($aOffersArrayOfferCodeKeys) == 0) {
					$_SESSION['iSesCurrentPositionInFlow']++;
				}



				//	****************  Loop through offers array to prepare offers list to display  **************
				$_SESSION['iTempOfferShownCount'] = 0;
				//mail('bbevis@amperemedia.com','a count',count($aOffersArrayOfferCodeKeys));
				for ($o=0; $o < count($aOffersArrayOfferCodeKeys); $o++) {
					$sOfferCode = $aOffersArray['offerCode'][$aOffersArrayOfferCodeKeys[$o]];
					$_SESSION['aSesDontShowOfferAgain'][$_SESSION['iSesCurrentPositionInFlow']][$_SESSION['iTempOfferShownCount']] = $sOfferCode;
					
					$_SESSION['iTempOfferShownCount']++;

					// only Standard Non-Stacked Page has Close They Host offers
					if ($_SESSION['sSesTemplateType'] == 'SPNS') {
						$sCheckOfferType = "SELECT offerType FROM offers 
											WHERE offerCode = '$sOfferCode'
											AND offerType IN ('CTH','OTH_CTH')";
						$rTypeResult = dbQuery($sCheckOfferType);
						if (dbNumRows($rTypeResult) > 0 ) {
							$sOtPageName = "cth_".$sOfferCode;
							$sOtPageQuery = "SELECT id FROM otPages WHERE pageName = '$sOtPageName' LIMIT 1";
							$rOtPageResult = dbQuery($sOtPageQuery);
							while($sOtPagesRow = dbFetchObject($rOtPageResult)) {
								$iPageId = $sOtPagesRow->id;
							}
						} else {
							$iPageId = $_SESSION['iSesPageId'];
						}
					} else {
						$iPageId = $_SESSION['iSesPageId'];
					}
					
					
					// Insert into temporary offer display counts table
					$sStatQuery = "INSERT IGNORE INTO tempOfferDisplayStats(pageId, statInfo, sourceCode, subSourceCode, displayDate)
									VALUES('$iPageId', \"$sOfferCode\", '".$_SESSION['sSesSourceCode']."', 
									'".$_SESSION['sSesSubSourceCode']."', CURRENT_DATE)";
					$rStatResult = dbQuery($sStatQuery);
					echo dbError();
					// TRACKING ENDS HERE...........
					
					//echo "$o";
					$sErrorMessageSQL = "SELECT * FROM linksErrorMessages WHERE sourceCode = '".$_SESSION['sSesSourceCodePersists']."'";
					//$sErrorMessageSQL = "SELECT * FROM linksErrorMessages WHERE sourceCode = '$sSourceCode'";
					$rErrorMessage = dbQuery($sErrorMessageSQL);
					$oErrorMessage = dbFetchObject($rErrorMessage);
						
					// Below two vars will be used for java script validation.
					$sIdForYes = 'Yes'.$o;
					$sIdForNo = 'No'.$o;
					//, , 
					if ($iDisplayYesNo) {
						if ($_SESSION['sSesEachOfferRequired'] == 'Y') {
							$sErrorMessageAllOffer = ($oErrorMessage->checkAllOffers != '' ? $oErrorMessage->checkAllOffers : "In order to proceed, please check 'YES' or 'NO' to each offer. ");
							$sTempJavaScriptOffersValidation2 .= " (!document.getElementById('$sIdForYes').checked && !document.getElementById('$sIdForNo').checked) || ";
						}
						
						if ($_SESSION['sSesOneOfferRequired'] == 'Y') {
							$sErrorMessage1Offer = ($oErrorMessage->checkAtLeastOneOffer != '' ? $oErrorMessage->checkAtLeastOneOffer : "In order to proceed, please check 'YES' to receive information".'\n'." from at least one of our partners. ");
							$sJavaScriptOffersAtLeastOneCheckedYes .= " (document.getElementById('$sIdForYes') && document.getElementById('$sIdForYes').checked) || ";
						}
					} else {	// CHECKBOX OFFERS
						if ($_SESSION['sSesOneOfferRequired'] == 'Y') {
							$sErrorMessageAllOffer = ($oErrorMessage->ynAtLeastOneOffer != '' ? $oErrorMessage->ynAtLeastOneOffer : "In order to proceed, please check to receive information from at".'\n'." least one of our partners. ");
							// if checkbox, at least one offer is required.
							$sTempJavaScriptOffersValidation .= "document.getElementById('$o').checked || ";
						}
					}

					if ($aOffersArray['precheck'][$aOffersArrayOfferCodeKeys[$o]]) {
						$sOfferChecked = 'checked';
						$sOfferYesChecked = 'checked';
					} else {
						$sOfferChecked = '';
					}
					
					//<!-- [USE_SMALL_OFFER_IMAGES] -->
					//WE NO LONGER USE ABOVE IMAGE TAG IN OFFERSLAYOUT TEMPLATES.
					//INSTEAD, WE USE BELOW TAGS.
					
					//<!-- [OFFER_IMAGE_75x30] -->
					//<!-- [OFFER_IMAGE_88x31] -->
					//<!-- [OFFER_IMAGE_120x60] -->
					if (strstr($_SESSION['sSesOfferListLayouts'], "OFFER_IMAGE_75x30")) {
						$sOfferImage = $aOffersArray['offerSmallImageName'][$aOffersArrayOfferCodeKeys[$o]];
					}
					
					if (strstr($_SESSION['sSesOfferListLayouts'], "OFFER_IMAGE_88x31")) {
						$sOfferImage = $aOffersArray['offerMediumImageName'][$aOffersArrayOfferCodeKeys[$o]];
					}

					if (strstr($_SESSION['sSesOfferListLayouts'], "OFFER_IMAGE_120x60")) {
						$sOfferImage = $aOffersArray['offerImageName'][$aOffersArrayOfferCodeKeys[$o]];
					}
					
					
					
					
					// Store top offers and other offers list in separate variables
					if ($aOffersArray['isTopDisplay'][$aOffersArrayOfferCodeKeys[$o]]) {
						$sOfferListVariable = "sPageTopOffersList";
					} else {
						$sOfferListVariable = "sPageOffersList";
					}

					// check if layout shows two offers side by side in a row
					// user placeholder variables accordingly and get new layout template in alternate iteration only
					if (strstr($_SESSION['sSesOfferListLayouts'], "OFFER1_") && strstr($_SESSION['sSesOfferListLayouts'], "OFFER2_")) {
						if ($o%2 == 0) {
							$sTempTemplateContent = $_SESSION['sSesOfferListLayouts'];
							$sTmplOfferImage = "[OFFER1_IMAGE]";
							$sTmplOfferBgColor =  "[OFFER1_BG_COLOR]";
							$sTmplOfferHeadline =  "[OFFER1_HEADLINE]";
							$sTmplOfferFontClass = "[OFFER1_FONT_CLASS]";
							$sTmplOfferDescription = "[OFFER1_DESCRIPTION]";
							$sTmplOfferShortDescription = "[OFFER1_SHORT_DESCRIPTION]";
							$sTmplOfferSelect = "[OFFER1_SELECT]";
							$sTmplOfferSelectYes = "[OFFER1_SELECT_YES]";
							$sTmplOfferSelectNo = "[OFFER1_SELECT_NO]";
							$sTmplOfferAddiInfoLink = "[OFFER1_ADDI_INFO_LINK]";
							$sTmp1OfferSelectName = "[OFFER1_SELECT_NAME]";
							$sTmp1OfferSelectValueYes = "[OFFER1_SELECT_VALUE_YES]";
							$sTmp1OfferSelectYesChecked = "[OFFER1_SELECT_YES_CHECKED]";
							$sTmp1OfferSelectNoChecked = "[OFFER1_SELECT_NO_CHECKED]";
						} else {
							$sTmplOfferImage = "[OFFER2_IMAGE]";
							$sTmplOfferBgColor =  "[OFFER2_BG_COLOR]";
							$sTmplOfferHeadline =  "[OFFER2_HEADLINE]";
							$sTmplOfferFontClass = "[OFFER2_FONT_CLASS]";
							$sTmplOfferDescription = "[OFFER2_DESCRIPTION]";
							$sTmplOfferShortDescription = "[OFFER2_SHORT_DESCRIPTION]";
							$sTmplOfferSelect = "[OFFER2_SELECT]";
							$sTmplOfferSelectYes = "[OFFER2_SELECT_YES]";
							$sTmplOfferSelectNo = "[OFFER2_SELECT_NO]";
							$sTmplOfferAddiInfoLink = "[OFFER2_ADDI_INFO_LINK]";
							$sTmp1OfferSelectName = "[OFFER2_SELECT_NAME]";
							$sTmp1OfferSelectValueYes = "[OFFER2_SELECT_VALUE_YES]";
							$sTmp1OfferSelectYesChecked = "[OFFER2_SELECT_YES_CHECKED]";
							$sTmp1OfferSelectNoChecked = "[OFFER2_SELECT_NO_CHECKED]";
						}
					} else {
						$sTempTemplateContent = $_SESSION['sSesOfferListLayouts'];
						$sTmplOfferImage = "[OFFER_IMAGE]";
						$sTmplOfferBgColor =  "[OFFER_BG_COLOR]";
						$sTmplOfferHeadline =  "[OFFER_HEADLINE]";
						$sTmplOfferFontClass = "[OFFER_FONT_CLASS]";
						$sTmplOfferDescription = "[OFFER_DESCRIPTION]";
						$sTmplOfferShortDescription = "[OFFER_SHORT_DESCRIPTION]";
						$sTmplOfferSelect = "[OFFER_SELECT]";
						$sTmplOfferSelectYes = "[OFFER_SELECT_YES]";
						$sTmplOfferSelectNo = "[OFFER_SELECT_NO]";
						$sTmplOfferAddiInfoLink = "[OFFER_ADDI_INFO_LINK]";
						$sTmp1OfferSelectName = "[OFFER_SELECT_NAME]";
						$sTmp1OfferSelectValueYes = "[OFFER_SELECT_VALUE_YES]";
						$sTmp1OfferSelectYesChecked = "[OFFER_SELECT_YES_CHECKED]";
						$sTmp1OfferSelectNoChecked = "[OFFER_SELECT_NO_CHECKED]";
					}
					
					
					$sTempTemplateContent = str_replace("[PRIVACY_POLICY]", $aOffersArray['privacyPolicy'][$aOffersArrayOfferCodeKeys[$o]], $sTempTemplateContent);
					
					
					switch($sOfferFontSize) {
						case "9px":
						$sOfferFontClass = "offer9";
						break;
						case "11px":
						$sOfferFontClass = "offer11";
						break;
						case "12px":
						$sOfferFontClass = "offer12";
						break;
						default:
						$sOfferFontClass = "offer10";
					}
				
					if ($iDisplayYesNo) {
						$sOfferYesChecked = '';
						$sOfferNoChecked = '';
						// reset the array pointer to starting element, otherwise not working
						if (is_array($aOffersYesNo)) {
							reset($aOffersYesNo);
							// go through all the offers
							while (list($key,$val) = each($aOffersYesNo)) {
								//remove \" which is around the key
								$sTempKey = str_replace("\"", "",stripslashes($key));
								$sTempVal = $val[0];
								// if the key matches to offercode, check the value
								// if value is same as offerCode, user had checked 'yes', if value is N, user had checked 'No'
								// otherwise user left  yes/no unchecked for the offer
								if ($sTempKey == $sOfferCode && $sTempVal == $sOfferCode) {
									$sOfferYesChecked = "checked";
								} else if ($sTempKey == $sOfferCode && $sTempVal == 'N') {
									$sOfferNoChecked = "checked";
								}
							}
						} else {
							if ($aOffersArray['precheck'][$aOffersArrayOfferCodeKeys[$o]]) {
								$sOfferYesChecked = "checked";
							}
						}
					} else {
						// if offer is checked and user came back
						for ($i=0; $i<count($_SESSION["aSesOffersChecked"]);$i++) {
							if ($_SESSION["aSesOffersChecked"][$i] == $sOfferCode) {
								$sOfferChecked = "checked";
								break;
							}
						}
					}
					
					if ($sBgColor == $_SESSION['sOfferBgColor1'] || $sBgColor == '') {
						$sBgColor = $_SESSION['sOfferBgColor2'];
						//echo "<br>Color2: $sBgColor";
					} else {
						$sBgColor = $_SESSION['sOfferBgColor1'];
						//echo "<br>Color1: $sBgColor";
					}
					
					
					$sTempOfferImage = "$sGblDisplayOfferImagesUrl/$sOfferCode/$sOfferImage";
					$sTempTemplateContent = str_replace($sTmplOfferImage, $sTempOfferImage, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferBgColor, $sBgColor, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferHeadline, $aOffersArray['offerHeadline'][$aOffersArrayOfferCodeKeys[$o]], $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferFontClass, $sOfferFontClass, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferDescription, $aOffersArray['offerDescription'][$aOffersArrayOfferCodeKeys[$o]], $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferShortDescription, $aOffersArray['offerShortDescription'][$aOffersArrayOfferCodeKeys[$o]], $sTempTemplateContent);
					
					// Get Co-Reg Popup Offer Details
					$sCoRegOutBoundPassOnCode = '';
					$sOnClickPopUpCoRegPopup = '';
					$sGetCoRegPopupInfo = "SELECT * FROM offers WHERE offerCode='$sOfferCode' LIMIT 1";
					$sGetCoRegPopupInfoResult = dbQuery($sGetCoRegPopupInfo);
					while ($oCoRegPopUpRow = dbFetchObject($sGetCoRegPopupInfoResult)) {
						$sIsCoRegPopup = $oCoRegPopUpRow->isCoRegPopUp;
						$sIsCoRegPopupPassOnCode = $oCoRegPopUpRow->coRegPopPassOnPrepopCodes;
						$sCoRegVarMap = $oCoRegPopUpRow->coRegPopPassOnCodeVarMap;
						$sCoRegPopupUrl = $oCoRegPopUpRow->coRegPopUrl;
						$sCoRegPopupTriggerOn = $oCoRegPopUpRow->coRegPopUpTriggerOn;
						$sIsCloseTheyHost = $oCoRegPopUpRow->isCloseTheyHost;
						$sCloseTheyHostTriggerOn = $oCoRegPopUpRow->closeTheyHostTriggerOn;
					}
					
					if ($sIsCoRegPopup == 'Y' && $sIsCoRegPopupPassOnCode == 'Y') {
						if ($sCoRegVarMap !='') {	// Replace our vars with client's var - outbound query
							$aPassOnCodeVarMap = explode(",",$sPassOnCodeVarMap);
							for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
								$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
								if ($aKeyValuePair[0] == 'e') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesEmail"]."&"; }
								if ($aKeyValuePair[0] == 'f') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesFirst"]."&"; }
								if ($aKeyValuePair[0] == 'l') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesLast"]."&"; }
								if ($aKeyValuePair[0] == 'a1') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress"]."&"; }
								if ($aKeyValuePair[0] == 'a2') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress2"]."&"; }
								if ($aKeyValuePair[0] == 'c') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesCity"]."&"; }
								if ($aKeyValuePair[0] == 's') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesState"]."&"; }
								if ($aKeyValuePair[0] == 'z') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesZip"]."&"; }
								if ($aKeyValuePair[0] == 'p') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhone"]."&"; }
								if ($aKeyValuePair[0] == 'pnd') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhoneNoDash"]."&"; }
							}
						} else {	// use our vars
							$sCoRegOutBoundPassOnCode .= "e=".$_SESSION["sSesEmail"]."&";
							$sCoRegOutBoundPassOnCode .= "f=".$_SESSION["sSesFirst"]."&";
							$sCoRegOutBoundPassOnCode .= "l=".$_SESSION["sSesLast"]."&";
							$sCoRegOutBoundPassOnCode .= "a1=".$_SESSION["sSesAddress"]."&";
							$sCoRegOutBoundPassOnCode .= "a2=".$_SESSION["sSesAddress2"]."&";
							$sCoRegOutBoundPassOnCode .= "c=".$_SESSION["sSesCity"]."&";
							$sCoRegOutBoundPassOnCode .= "s=".$_SESSION["sSesState"]."&";
							$sCoRegOutBoundPassOnCode .= "z=".$_SESSION["sSesZip"]."&";
							$sCoRegOutBoundPassOnCode .= "p=".$_SESSION["sSesPhone"]."&";
							$sCoRegOutBoundPassOnCode .= "pnd=".$_SESSION["sSesPhoneNoDash"]."&";
						}
						$sCoRegOutBoundPassOnCode .= "sesId=".session_id()."&";
						$sCoRegOutBoundPassOnCode = substr($sCoRegOutBoundPassOnCode,0, strlen($sCoRegOutBoundPassOnCode)-1);
			
						if ($sCoRegOutBoundPassOnCode != '') {
							$sCoRegPopupUrl = $sCoRegPopupUrl.'?'.$sCoRegOutBoundPassOnCode;
						}
					}
					
					if ($sIsCoRegPopup == 'Y') {
						$sTrackCoRegOpensUrl = "coRegPopupCount.php?PHPSESSID=".session_id();
						$sOnClickPopUpCoRegPopup = "onClick=\"response=coRegPopup.send('$sTrackCoRegOpensUrl','');window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');\"";
					}
					
					
					if ($iDisplayYesNo) {
						if ($sIsCloseTheyHost == 'Y' && $sCloseTheyHostTriggerOn == 'N') {
						    $sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForYes' $sOfferNoChecked>Yes <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForNo' $sOfferYesChecked>No";
						    $sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForYes' $sOfferNoChecked>Yes";
						    $sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForNo' $sOfferYesChecked>No";
						} else {
							if ($sIsCoRegPopup == 'N') {	// If Offer Code is not a coRegPopup, then do regular stuff
								$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForYes' $sOfferYesChecked>Yes <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForNo' $sOfferNoChecked>No";
								$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForYes' $sOfferYesChecked>Yes";
								$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForNo' $sOfferNoChecked>No";
							} else {
								$sCoRegOnClickYes = '';
								$sCoRegOnClickNo = '';
								$sTempPreCheckCoReg = '';
		
								// If $sOfferCode is coRegPopup, then add onclick to radio buttons
								if ($sCoRegPopupTriggerOn == 'Y') {
									$sCoRegOnClickYes = $sOnClickPopUpCoRegPopup;
									if ($sOfferYesChecked == "checked" && $sMessage == '') {
										$sTempPreCheckCoReg .= "<img src='http://www.popularliving.com/nibbles2/coRegPopupCount.php?PHPSESSID=".session_id()."' width=1 height=1>";
										$sTempPreCheckCoReg .= "<script type=\"text/javascript\" >window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');</script>";
									}
									
									$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForYes' $sOfferYesChecked $sCoRegOnClickYes>Yes 
														<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForNo' $sOfferNoChecked $sCoRegOnClickNo>No";
									$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForYes' $sOfferYesChecked $sCoRegOnClickYes>Yes";
									$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForNo' $sOfferNoChecked $sCoRegOnClickNo>No";
								} else {
									$sCoRegOnClickNo = $sOnClickPopUpCoRegPopup;
									if ($sOfferNoChecked == "checked" && $sMessage == '') {
										$sTempPreCheckCoReg .= "<img src='http://www.popularliving.com/nibbles2/coRegPopupCount.php?PHPSESSID=".session_id()."' width=1 height=1>";
										$sTempPreCheckCoReg .= "<script type=\"text/javascript\" >window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');</script>";
									}
									
									$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForYes' $sOfferNoChecked $sCoRegOnClickYes>Yes 
														<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForNo' $sOfferYesChecked $sCoRegOnClickNo>No";
									$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' id='$sIdForYes' $sOfferNoChecked $sCoRegOnClickYes>Yes";
									$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' id='$sIdForNo' $sOfferYesChecked $sCoRegOnClickNo>No";
								}
							}
						}
					
						// following variables are to replace individual yes/no components, if template defined so
						$sTempOfferSelectName = "aOffersChecked[\"$sOfferCode\"][]";
						$sTempOfferSelectValueYes = "$sOfferCode";
						
					} else {	// CHECKBOX OFFERS
						if ($sIsCoRegPopup == 'Y') {
							$sTempPreCheckCoReg = '';
							if ($sOfferChecked == 'checked' && $sMessage == '') {
								$sTempPreCheckCoReg .= "<img src='http://www.popularliving.com/nibbles2/coRegPopupCount.php?PHPSESSID=".session_id()."' width=1 height=1>";
								$sTempPreCheckCoReg .= "<script type=\"text/javascript\" >window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');</script>";
							}
						}
						$sTempOfferSelectOption = "<input type=checkbox name='aOffersChecked[]' id='$o' value='$sOfferCode' $sOfferChecked $sOnClickPopUpCoRegPopup> &nbsp;";
					}
			
					$sTempTemplateContent = str_replace($sTmplOfferSelect, $sTempOfferSelectOption, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferSelectYes, $sTempOfferSelectOptionYes, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmplOfferSelectNo, $sTempOfferSelectOptionNo, $sTempTemplateContent);
			
					// following variables are to replace individual yes/no components, if template defined so
					$sTempTemplateContent = str_replace($sTmp1OfferSelectName, $sTempOfferSelectName, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmp1OfferSelectValueYes, $sTempOfferSelectValueYes, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmp1OfferSelectYesChecked, $sOfferYesChecked, $sTempTemplateContent);
					$sTempTemplateContent = str_replace($sTmp1OfferSelectNoChecked, $sOfferNoChecked, $sTempTemplateContent);
					
					$k++;
			
					$sTempAddiInfoLink = '';
					if ($aOffersArray['addiInfoText'][$aOffersArrayOfferCodeKeys[$o]] != '') {
						// add additional information link for popup
						$sAddiInfoTitle = $aOffersArray['addiInfoTitle'][$aOffersArrayOfferCodeKeys[$o]];
						$aAddiInfoPopupSizeArray = explode(",",$aOffersArray['addiInfoPopupSize'][$aOffersArrayOfferCodeKeys[$o]]);
						$iAddiInfoPopupWidth = $aAddiInfoPopupSizeArray[0];
						$iAddiInfoPopupHeight = $aAddiInfoPopupSizeArray[1];
						$sTempAddiInfoLink = " <a href='JavaScript:void(window.open(\"$sGblSiteRoot/offerAddiInfo.php?sOfferCode=$sOfferCode\",\"addiInfo\",\"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>$sAddiInfoTitle</a>";
					}
					$sTempTemplateContent = str_replace($sTmplOfferAddiInfoLink, $sTempAddiInfoLink, $sTempTemplateContent);
			
					// check if layout shows two offers side by side in a row
					// place template content into offer list in alternate iteration only when offer1 and offer2 variables are replaced in template
					if (strstr($_SESSION['sSesOfferListLayouts'], "OFFER1_") && strstr($_SESSION['sSesOfferListLayouts'], "OFFER2_") ) {
						if ($o%2 != 0) {
							$$sOfferListVariable .= "<tr><td  bgcolor=$sBgColor>".$sTempTemplateContent."</td></tr>";
						}
					} else {
						$$sOfferListVariable .= "<tr><td  bgcolor=$sBgColor>".$sTempTemplateContent."</td></tr>";
					}
				}	/// *************************  End loop through offers array  ***********************

				if ($sTempJavaScriptOffersValidation !='' || $sJavaScriptOffersAtLeastOneCheckedYes !='' || $sTempJavaScriptOffersValidation2 !='') {
					$sJavaScriptOffersAtLeastOneCheckedYes = substr($sJavaScriptOffersAtLeastOneCheckedYes,0,strlen($sJavaScriptOffersAtLeastOneCheckedYes)-3);
					$sTempJavaScriptOffersValidation = substr($sTempJavaScriptOffersValidation,0,strlen($sTempJavaScriptOffersValidation)-3);
					$sTempJavaScriptOffersValidation2 = substr($sTempJavaScriptOffersValidation2,0,strlen($sTempJavaScriptOffersValidation2)-3);
					$sJavaScriptOffersValidation = "
					<script type=\"text/javascript\" >
						function offerValidation() {
							var errMessage = '';

							";
					
					
					if($sTempJavaScriptOffersValidation2 != ''){
						$sJavaScriptOffersValidation .= "if(($sTempJavaScriptOffersValidation2)){
								errMessage +=\"\\n* $sErrorMessageAllOffer\";
							}";
					}
					
					if($sTempJavaScriptOffersValidation != ''){
						$sJavaScriptOffersValidation .= "if(!($sTempJavaScriptOffersValidation)){
								errMessage +=\"\\n* $sErrorMessageAllOffer\";
							}";
					}
					if($sJavaScriptOffersAtLeastOneCheckedYes != ''){
						$sJavaScriptOffersValidation .= "if(!($sJavaScriptOffersAtLeastOneCheckedYes)){
								errMessage +=\"\\n* $sErrorMessage1Offer\";
							}";
					}
					
					$sJavaScriptOffersValidation .= "
						
							if (errMessage != '') {
								alert(errMessage);
								return false;
							} else {			
								return true;
							}
						}
					</script>";
				}
	
				// pass all query string variables as hidden fields ONLY If not returned back from submit script becaouse of any error
				$sHiddenFields .= "<tr><td><input type=hidden name='sPageName' value='$sPageName'>
									<input type=hidden name='sSourceCode' value='$sSourceCode'>
									<input type=hidden name='sSubSourceCode' value='$sSubSourceCode'>
									<input type=hidden name='sPageMode' value='$sPageMode'></td></tr>";
		
				// place hidden fields in otPageContent alongwith offersList
				$sPageOffersList .= $sHiddenFields.$sTempPreCheckCoReg;
				//echo $sTempTemplateContent;
		} else {
			// GET ADDITIONAL QUESTIONS FOR OFFER.  IF TEMPLATE TYPE IS STACKED, SHOW ALL ADDITIONAL QUESTIONS IN ONE PAGE
			// ELSE SHOW ONE OFFER AT A TIME.
			$_SESSION['bPage2Submit'] = true;
			if (count($_SESSION['aSesPage2Offers']) > 0) {
				if ($_SESSION['sSesTemplateType'] == 'SPNS') {
					// standard page non-stacked
					if (in_array($_SESSION['aSesPage2Offers'][0], $_SESSION['aSesCloseTheyHostOffers'])) {
						$bCloseTheyHost = true;
					} else {
						$bCloseTheyHost = false;
						$sPage2Offers = "'".$_SESSION['aSesPage2Offers'][0]."'";
					}
				} else {
					// standard page stacked
					$bCloseTheyHost = false;
					$sPage2Offers = "'".implode("','",$_SESSION['aSesPage2Offers'])."'";
				}
				
				// if offer is close they host offer, then process it.
				if ($bCloseTheyHost == true) {
					// Record close they host display stats.  this is in addition to regular page display stats
					$sOtPageName = "cth_".$_SESSION['aSesCloseTheyHostOffers'][0];
					$sOtPageQuery = "SELECT id FROM otPages WHERE pageName = '$sOtPageName' LIMIT 1";
					$rOtPageResult = dbQuery($sOtPageQuery);
					$sCurrentDateTime = date('Y-m-d H:i:s');
					while($sOtPagesRow = dbFetchObject($rOtPageResult)) {
						$sPageStatQuery = "INSERT IGNORE INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, 
							openDate, sessionId, ipAddress, openDateTime)
						  VALUES('$sOtPagesRow->id', '".$_SESSION['sSesSourceCode']."', '".$_SESSION['sSesSubSourceCode']."',
						  CURRENT_DATE,'".session_id()."', '".$_SESSION['sSesRemoteIp']."', '$sCurrentDateTime')";
						//$rPageStatResult = dbQuery($sPageStatQuery);
					}
					
					
					// if the offer is close they host offer that showed up on standard non-stacked page,
					// then exclude that offer because we don't want to display the same offer as open they 
					// host offers. -- samir patel -- 9/5/06 -- 1:43pm
					if (!in_array($_SESSION['aSesPage2Offers'][0], $_SESSION['aExcludeOffers'])) {
						array_push($_SESSION['aExcludeOffers'], $_SESSION['aSesPage2Offers'][0]);
					}
					
					// do not remove below 3 lines of code.  let the script set the cookie.
					setcookie("AmpereOfferType", "cth_", time()+3600, "/", ".popularliving.com", 0);
					setcookie("AmpereOfferType", "cth_", time()+3600, "/", ".3400cookie.com", 0);
					setcookie("AmpereOfferType", "cth_", time()+3600, "/", $_SESSION['sSesDomain'], 0);
					setcookie("AmpereOfferType", "cth_", time()+3600, "/", '', 0);
					
					$sCloseTheyHostPath = "cth.php?PHPSESSID=".session_id()."&oc=".$_SESSION['aSesCloseTheyHostOffers'][0];
					session_write_close();
					header("Location:$sCloseTheyHostPath");
					exit;
				}


				/// ************** Prepare offers' page2 text and javascript validation *************
				if ($sPage2Offers != '' && $bCloseTheyHost == false) {
					$iRedirectCount = 1;//keeps the page from reloading due to "No offers on the page"
					// Check if any offers requires SSL - Start
					$bRequireSSL = false;
					$sCheckRequireSSLQuery = "SELECT * FROM offers 
									 WHERE offerCode IN (".$sPage2Offers.") AND isRequireSSL = 'Y'";
					$rCheckRequireSSLResult = dbQuery($sCheckRequireSSLQuery);
					if (dbNumRows($rCheckRequireSSLResult) > 0) {
						$bRequireSSL = true;
					}
					// Check if any offers requires SSL - End
					
					// write the javascript functions and page2Validation function to call on submit
					$sPage2JavaScript = "
						<script type=\"text/javascript\" >
					
						function page2Validation() {
							var errMessage = '';";
				
					$sOffersQuery = "SELECT * FROM offers WHERE offerCode IN ($sPage2Offers)";
					$rOffersResult = dbQuery($sOffersQuery);
					//echo $sOffersQuery;
					$sOffersOnPage2 = '';
					while ($oOffersRow = dbFetchObject($rOffersResult)) {
						$sOfferCode = $oOffersRow->offerCode;
	
						if ($oOffersRow->isCoolSavings == 'Y') {
							$sOfferPage2Template = "<table border='0' cellpadding='0' cellspacing='0' width='750'>
									<tr><td><img src=\"http://images.popularliving.com/images/offers/$sOfferCode/$oOffersRow->smallImageName\" /></td>
									<td width='10' bgcolor='#EFEFEF'></td><td class=offer11 bgcolor='#EFEFEF'>
									$oOffersRow->shortDescription</td></tr></table>".$oOffersRow->page2Template;
						} else {
							$sOfferPage2Template = $oOffersRow->page2Template;
						}
						
						if ($oOffersRow->sShowRegForm == 'Y') {
							if (strstr($sOfferPage2Template,"THINNER_USER_FORM")) {
								$sUserForm = $_SESSION['sSesPage2ThinnerUserForm'];
								
								if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_LEFT]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_LEFT]-->", $sUserForm, $sOfferPage2Template);
								}
								if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_CENTER]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_CENTER]-->", $sUserForm, $sOfferPage2Template);
								}
								if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_RIGHT]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_RIGHT]-->", $sUserForm, $sOfferPage2Template);
								}
								
							} else {
								$sUserForm = $_SESSION['sSesPage2RegUserForm'];
								
								if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_LEFT]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[USER_FORM_C_LEFT]-->", $sUserForm, $sOfferPage2Template);
								}
								if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_CENTER]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[USER_FORM_C_CENTER]-->", $sUserForm, $sOfferPage2Template);
								}
								if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_RIGHT]-->")) {
									$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
									$sOfferPage2Template = str_replace("<!--[USER_FORM_C_RIGHT]-->", $sUserForm, $sOfferPage2Template);
								}
							}
						}
						
						
	
						$sOfferDroppedVar = $sOfferCode."Dropped";
						if ($oOffersRow->page2JavaScript != '') {
							$sPage2JavaScript .= "var $sOfferDroppedVar = false;
							for (i = 0; i < document.form1.length; i++) {
							   if (document.form1.elements[i].name.indexOf(\"aDropOffers\") !=-1) {
							        if (document.form1.elements[i].checked && document.form1.elements[i].value == \"".$sOfferCode."\") {
							           $sOfferDroppedVar = true;
							        }
							   }
							}
							if ($sOfferDroppedVar != true) {
								$oOffersRow->page2JavaScript
							}";
						}
							
						if ($sBgColor == $_SESSION['sOfferBgColor1'] || $sBgColor == '') {
							$sBgColor = $_SESSION['sOfferBgColor2'];
						} else {
							$sBgColor = $_SESSION['sOfferBgColor1'];
						}
				
						// If 2nd page questions required ssl, change all image links from http to https
						if ($bRequireSSL) {
							$sOfferPage2Template = str_replace("http://","https://",$sOfferPage2Template);
						}
						
						$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td>$sOfferPage2Template
								</td></tr>".($oOffersRow->iDoNotWantThisOffer != 'N'? "<tr bgcolor=$sBgColor><td>I do not want this offer: <input type=checkbox name=aDropOffers[] value='$sOfferCode' onClick='sureOptOut(this);'></td></tr>" : "")."<tr bgcolor=$sBgColor><td><hr></td></tr>";
					}
					
					$sPage2JavaScript .= "
						if (errMessage != '') {
				    		alert(errMessage);
				    		return false;
				  		} else {
				    		return true;
				  		}
					}
					function sureOptOut(chkBox) {
						if (chkBox.checked) {
							if(!confirm(\"Are You Sure You Don\'t Want This Great Offer?\\nClick 'Cancel' to finish requesting offer.\\nClick 'OK' to confirm you don't want this offer.\")) {
								chkBox.checked = false;
							}
						}
					}
					</script>";
					$sPageOffersList = $sOffersOnPage2;
				}
			}
		}
	} else {
		
		// If Open They Host or Open We Host, then ...
		if ($_SESSION['sSesTemplateType'] == 'OP') {
			$_SESSION['sSesOpenTheyHostUrl'] = '';
			// If no rule, then get highest ecpm offer
			if ($iOffersWithPosCount == 0) {
				//AND date_format(offerEcpm.created,'%Y-%m-%d %H:%i:%s')>=date_add(NOW(),INTERVAL -1 HOUR)
				$sOffersQuery = "SELECT distinct offers.offerCode as temp, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
								 FROM   offers RIGHT JOIN offerEcpm ON (offers.offerCode = offerEcpm.offerCode AND offerEcpm.ecpm > 0.0)
								 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
								 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
								 WHERE  offers.isLive = '1'
								 AND    offers.mode = 'A'
								 $sNonRevenueOffersFilter
								 AND    offerCompanies.creditStatus = 'ok'
								 AND 	(offers.offerType = 'OWH' OR offers.offerType = 'OTH' OR offers.offerType = 'OTH_CTH') 
								 $sAndOfferCodeNotIn
								 $sDontShowSameOfferAgain
								 $sAndRulesOfferCodeNotIn
								 $sExcludePageOffersFilter
								 $sMutExcFilterByPageRange
								 ORDER BY offerEcpm.ecpm DESC
								 LIMIT  ".$_SESSION['iSesOPTotalOfferShown'].",1";
				$rTempResult = dbQuery($sOffersQuery);
				echo dbError();
				if (dbNumRows($rTempResult) == 0) {
					$sOffersQuery = "SELECT distinct offers.offerCode as temp, offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
								 FROM   offers LEFT JOIN offerStatsWorking ON (offers.offerCode = offerStatsWorking.offerCode
								 		AND offerStatsWorking.displayDate = '$sYesterday')
								 	LEFT JOIN categoryMap ON offers.offerCode = categoryMap.offerCode
								 	LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
								 WHERE  offers.isLive = '1'
								 AND    offers.mode = 'A'
								 $sNonRevenueOffersFilter
								 AND    offerCompanies.creditStatus = 'ok'
								 AND 	(offers.offerType = 'OWH' OR offers.offerType = 'OTH' OR offers.offerType = 'OTH_CTH') 
								 $sAndOfferCodeNotIn
								 $sDontShowSameOfferAgain
								 $sAndRulesOfferCodeNotIn
								 $sExcludePageOffersFilter
								 $sMutExcFilterByPageRange
								 ORDER BY offerStatsWorking.ecpmTotal DESC
								 LIMIT  ".$_SESSION['iSesOPTotalOfferShown'].",1";
				}
			} else {
				$tempOfferCodes = $aArrayOfferCode[0];
				$sOffersQuery = "SELECT offers.*
								 FROM   offers, offerCompanies
								 WHERE  offers.companyId = offerCompanies.id	
								 AND    offers.isLive = '1'
								 AND    offers.mode = 'A'
								 AND 	(offers.offerType = 'OWH' OR offers.offerType = 'OTH' OR offers.offerType = 'OTH_CTH') 
								 AND    offers.offerCode = '$tempOfferCodes'
								 $sMutExcFilterByPageRange
								 AND    offerCompanies.creditStatus = 'ok' LIMIT 1";
			}
			
			if ($iCountOffersToIncludeByPage > 0) {
				$sOffersQuery = "SELECT offers.*
								 FROM   offers, offerCompanies
								 WHERE  offers.companyId = offerCompanies.id	
								 AND    offers.isLive = '1'
								 AND    offers.mode = 'A'
								 AND 	(offers.offerType = 'OWH' OR offers.offerType = 'OTH' OR offers.offerType = 'OTH_CTH') 
								 $sIncludePageOffersFilter
								 $sMutExcFilterByPageRange
								 AND    offerCompanies.creditStatus = 'ok'
								 LIMIT  ".$_SESSION['iSesOPTotalOfferShown'].",1";
			}

			$rOffersResult = dbQuery($sOffersQuery);
			echo dbError();

			$iRedirectCount = dbNumRows($rOffersResult);
			
			while ($oOfferRow = dbFetchObject($rOffersResult)) {
				$sOfferType = $oOfferRow->offerType;

				// include only Open They Host offers - No stims.  Comes in a flow.
				if ($oOfferRow->isOpenTheyHost == 'Y') {
					$_SESSION['sSesOpenTheyHostUrl'] = $oOfferRow->theyHostOfferURL;
					$sTheyHostPassOnPrepopCodes = $oOfferRow->theyHostPassOnPrepopCodes;
					$sPassOnCodeVarMap = $oOfferRow->theyHostPassOnCodeVarMap;
				}

				$sOfferCode = $oOfferRow->offerCode;
				$sHeaderGraphicFile = $oOfferRow->singleOfferPageHeaderImage;
				$sOfferHeadline = $oOfferRow->headline;
				$sOfferDescription = $oOfferRow->description;
				$sOfferShortDescription = $oOfferRow->shortDescription;
				$sOfferImageName = $oOfferRow->imageName;
				$sOfferSmallImageName = $oOfferRow->smallImageName;
				$sAddiInfoText = $oOfferRow->addiInfoText;
				$sAddiInfoPopupSize = $oOfferRow->addiInfoPopupSize;
				$sAddiInfoTitle = $oOfferRow->addiInfoTitle;
				$sAddiInfoPopupSize = $oOfferRow->addiInfoPopupSize;
				$sOfferPage2Template = $oOfferRow->page2Template;
				$sPage2JavaScript .= $oOfferRow->page2JavaScript;
				$iFrameHeight = $oOfferRow->iFrameHeight;
				$sOfferType = $oOfferRow->offerType;
				
				$_SESSION['iFrameHeight'] = $iFrameHeight;
				
				if ($_SESSION['iFrameHeight'] == 0) {
					$_SESSION['iFrameHeight'] = 1500;
				}

				if ($sOfferType == 'OWH') {
					// Insert into temporary offer display counts table
					$sStatQuery = "INSERT IGNORE INTO tempOfferDisplayStats(pageId, statInfo, sourceCode, subSourceCode, displayDate)
									VALUES('".$_SESSION['iSesPageId']."', \"$oOfferRow->offerCode\", '".$_SESSION['sSesSourceCode']."', 
									'".$_SESSION['sSesSubSourceCode']."', CURRENT_DATE)";
					$rStatResult = dbQuery($sStatQuery);
					echo dbError();
						
					// page display stats
					$sCurrentDateTime = date('Y-m-d H:i:s');
					$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate,
									 sessionId, ipAddress, openDateTime)
									   VALUES('".$_SESSION['iSesPageId']."', '".$_SESSION['sSesSourceCode']."', 
									   '".$_SESSION['sSesSubSourceCode']."',CURRENT_DATE,'".session_id()."', 
									   '".$_SESSION['sSesRemoteIp']."', '$sCurrentDateTime')";
					//$rPageStatResult = dbQuery($sPageStatQuery);
					echo dbError();
				}
				
				$i++;
			}
			
			if ($sOfferType == 'OTH' || $sOfferType == 'OTH_CTH') {
				// do not remove below 3 lines of code.  let the script set the cookie.
				setcookie("AmpereOfferType", "th_", time()+3600, "/", ".popularliving.com", 0);
				setcookie("AmpereOfferType", "th_", time()+3600, "/", ".3400cookie.com", 0);
				setcookie("AmpereOfferType", "th_", time()+3600, "/", $_SESSION['sSesDomain'], 0);
				setcookie("AmpereOfferType", "th_", time()+3600, "/", '', 0);

				// Record open they host display stats.  this is in addition to regular page display stats
				$sOtPageName = "th_".$sOfferCode;
				$sOtPageQuery = "SELECT id FROM otPages WHERE pageName = '$sOtPageName' LIMIT 1";
				$rOtPageResult = dbQuery($sOtPageQuery);
				$sCurrentDateTime = date('Y-m-d H:i:s');
				//while($sOtPagesRow = dbFetchObject($rOtPageResult)) {
					$sPageStatQuery = "INSERT IGNORE INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, 
									openDate, sessionId, ipAddress, openDateTime)
								  VALUES('".$_SESSION['iSesPageId']."', '".$_SESSION['sSesSourceCode']."', '".$_SESSION['sSesSubSourceCode']."',
								  CURRENT_DATE,'".session_id()."', '".$_SESSION['sSesRemoteIp']."', '$sCurrentDateTime')";
					//$rPageStatResult = dbQuery($sPageStatQuery);
					
					// Insert into temporary offer display counts table
					$sStatQuery = "INSERT IGNORE INTO tempOfferDisplayStats(pageId, statInfo, sourceCode, subSourceCode, displayDate)
									VALUES('".$_SESSION['iSesPageId']."', \"$sOfferCode\", '".$_SESSION['sSesSourceCode']."', 
									'".$_SESSION['sSesSubSourceCode']."', CURRENT_DATE)";
					$rStatResult = dbQuery($sStatQuery);
					echo dbError();
				//}
				
				$sTempPrePop = '';
				if ($sTheyHostPassOnPrepopCodes == 'Y') {
					if ($sPassOnCodeVarMap !='') {
						$aPassOnCodeVarMap = explode(",",$sPassOnCodeVarMap);
						for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
							$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
							if ($aKeyValuePair[0] == 'e') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesEmail"]."&"; }
							if ($aKeyValuePair[0] == 'f') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesFirst"]."&"; }
							if ($aKeyValuePair[0] == 'l') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesLast"]."&"; }
							if ($aKeyValuePair[0] == 'a1') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress"]."&"; }
							if ($aKeyValuePair[0] == 'a2') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress2"]."&"; }
							if ($aKeyValuePair[0] == 'c') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesCity"]."&"; }
							if ($aKeyValuePair[0] == 's') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesState"]."&"; }
							if ($aKeyValuePair[0] == 'z') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesZip"]."&"; }
							if ($aKeyValuePair[0] == 'p') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhone"]."&"; }
							if ($aKeyValuePair[0] == 'pnd') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhoneNoDash"]."&"; }
						}
					} else {
						$sTempPrePop .= "e=".$_SESSION["sSesEmail"]."&";
						$sTempPrePop .= "f=".$_SESSION["sSesFirst"]."&";
						$sTempPrePop .= "l=".$_SESSION["sSesLast"]."&";
						$sTempPrePop .= "a1=".$_SESSION["sSesAddress"]."&";
						$sTempPrePop .= "a2=".$_SESSION["sSesAddress2"]."&";
						$sTempPrePop .= "c=".$_SESSION["sSesCity"]."&";
						$sTempPrePop .= "s=".$_SESSION["sSesState"]."&";
						$sTempPrePop .= "z=".$_SESSION["sSesZip"]."&";
						$sTempPrePop .= "p=".$_SESSION["sSesPhone"]."&";
						$sTempPrePop .= "pnd=".$_SESSION["sSesPhoneNoDash"]."&";
					}
					$sTempPrePop .= "sesId=".session_id();
				}

				if ($sTempPrePop != '') {
					$_SESSION['sSesOpenTheyHostUrl'] = $_SESSION['sSesOpenTheyHostUrl'].'?'.$sTempPrePop;
				}
				
				$sOthOwhUrl1 = "/nibbles2/ot.php?PHPSESSID=".session_id();
				$sIncrementPgId1 = "pageIncrement.php?PHPSESSID=".session_id();
				$sNoThanksOnClick1 = "onClick=\"response=coRegPopup.send('$sIncrementPgId1','');parent.location='$sOthOwhUrl1';\"";
				$sSubmitSkipButton = "<center><input type=submit value='Submit' $sNoThanksOnClick1 style=\"border-style:Double;\"></center>";
			}	// END OF OPEN THEY HOST - frame in 3rd party url

			// Open we host...
			if ($sOfferType == 'OWH') {
				//$_SESSION['sSesOpenTheyHostUrl'] = "owh.php?".SID."&oc=$sOfferCode";
				$_SESSION['sSesOpenTheyHostUrl'] = "owh.php?oc=$sOfferCode&ses=".session_id();
				//$_SESSION['aSesPage2Offers'][0] = $sOfferCode;
				// if open we host offer is skipped, then don't show the offer again.
				// -- samir patel -- 9/5/06 -- 1:52pm
				if (!in_array($sOfferCode, $_SESSION['aExcludeOffers'])) {
					array_push($_SESSION['aExcludeOffers'], $sOfferCode);
				}
			} // END OF OPEN WE HOST - frame in 2nd page questions

			
			$sOthOwhUrl = "/nibbles2/ot.php?PHPSESSID=".session_id();
			$sIncrementPgId = "pageIncrement.php?PHPSESSID=".session_id();
			$sNoThanksOnClick = "onClick=\"response=coRegPopup.send('$sIncrementPgId','');parent.location='$sOthOwhUrl';\"";
			$sNoThanksContinueSkip = "<div align='right'>
						<img src='http://www.popularliving.com/images/nothanks.gif' style='cursor: pointer;' 
						$sNoThanksOnClick></div>";
			
		}	// END OF OPEN PAGES:  open they host / open we host
	}
	
	
	
	$sCurrentDateTime = date('Y-m-d H:i:s');
	if ($_SESSION['sSesTemplateType'] == '3rdPP' || $_SESSION['sSesTemplateType'] == 'PP') {
		if ($_SESSION['sSesTemplateType'] == '3rdPP') {
			$sOthOwhUrl = "/nibbles2/ot.php?PHPSESSID=".session_id();
			$sIncrementPgId = "pageIncrement.php?PHPSESSID=".session_id();
			$sNoThanksOnClick = "onClick=\"response=coRegPopup.send('$sIncrementPgId','');parent.location='$sOthOwhUrl';\"";
			$sNoThanksContinueSkip = "<div align='right'>
					<img src='http://www.popularliving.com/images/nothanks.gif' style='cursor: pointer;' 
					$sNoThanksOnClick></div>";
		}
	}
	

	######################################################################
	#	ENDS
	#	OT OFFERS.  THIS SCRIPT WILL BUILD LIST OF OFFERS AND
	#	2ND PAGE QUESTIONS
	######################################################################
	$sReplaceHeaderSkip = '';
	$sSkipContinueURL = '';
	
	// If show skip button in header
	if ($_SESSION['sSesShowSkipButton'] == 'Y') {
		if ($_SESSION['sSesTemplateType']=='OP') {
			$sSkipContinueURL = "/nibbles2/ot.php?PHPSESSID=".session_id();
			$sAjax = "pageIncrement.php?PHPSESSID=".session_id();
			$sOnClick = "onClick=\"response=coRegPopup.send('$sAjax','');parent.location='$sSkipContinueURL';\"";
			$sReplaceHeaderSkip = "<div align='right'>
					<img src='http://www.popularliving.com/images/nothanks.gif' style='cursor: pointer;' 
					$sOnClick></div>";
		}
	} else {
		// If show skip button next to submit
		if ($_SESSION['sSesTemplateType']!='EP' && $_SESSION['sSesTemplateType']!='RP' && $_SESSION['sSesTemplateType']!='FRP') {
			$sSkipContinueURL = "/nibbles2/ot.php?PHPSESSID=".session_id();
			$sAjax = "pageIncrement.php?PHPSESSID=".session_id();
			$sSkipNextToSubmitOnClick = "response=coRegPopup.send('$sAjax','');parent.location='$sSkipContinueURL';";
		}
	}
	

	$_SESSION['sSesJavaScriptPrePop'] = "<script type=\"text/javascript\" >";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sEmail = '".$_SESSION['sSesEmail']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSalutation = '".$_SESSION['sSesSalutation']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sFirst = \"".$_SESSION['sSesFirst']."\";";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sLast = \"".$_SESSION['sSesLast']."\";";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sAddress = \"".$_SESSION['sSesAddress']."\";";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sAddress2 = \"".$_SESSION['sSesAddress2']."\";";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sCity = '".$_SESSION['sSesCity']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sState = '".$_SESSION['sSesState']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sZip = '".$_SESSION['sSesZip']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone = '".$_SESSION['sSesPhone']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_areaCode = '".$_SESSION['sSesPhoneAreaCode']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_exchange = '".$_SESSION['sSesPhoneExchange']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_number = '".$_SESSION['sSesPhoneNumber']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_ext = '".$_SESSION['sSesPhoneExtension']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhoneNoDash = '".$_SESSION['sSesPhoneNoDash']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSourceCode = '".$_SESSION['sSesSourceCode']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sRevSourceCode = '".$_SESSION['sSesRevSourceCode']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSubSourceCode = '".$_SESSION['sSesSubSourceCode']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sGender = '".$_SESSION['sSesGender']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var sRemoteIp = '".$_SESSION['sSesRemoteIp']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthYear = '".$_SESSION['iSesBirthYear']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthMonth = '".$_SESSION['iSesBirthMonth']."';";
	$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthDay = '".$_SESSION['iSesBirthDay']."';\n";
	$_SESSION['sSesJavaScriptPrePop'] .= "</script>";
	
	
	// Tags from Partners Page
	$submit = new SubmitButton();
	$sTempEPage = $_SESSION['sSesTemplateContent'];
	$sTempEPage = str_replace('[OFFERS_LIST]',$sPageOffersList,$sTempEPage);
	$sTempEPage = str_replace('[PAGE1_JAVA_SCRIPT]',$sJavaScriptOffersValidation,$sTempEPage);
	$sTempEPage = str_replace('[PAGE2_JAVA_SCRIPT]',$sPage2JavaScript,$sTempEPage);
	$sTempEPage = str_replace('[EMAIL_CAPTURE_FORM]',$_SESSION['sSesEPageHTML'],$sTempEPage);
	$sTempEPage = str_replace('[FULL_REG_FORM]',$_SESSION['sSesFullRegPageHTML'],$sTempEPage);
	$sTempEPage = str_replace('[CAMPAIGNS_CSS]',$_SESSION['sSesCampaignCSS'],$sTempEPage);
	$sTempEPage = str_replace('[SUBMIT_SKIP_BUTTON]',$sSubmitSkipButton,$sTempEPage);
	$sTempEPage = str_replace('[NO_THANKS_CONTINUE_IMAGE_SKIP]',$sNoThanksContinueSkip,$sTempEPage);
	
	if($iRedirectCount == 0) {
		$redirectJS = "document.location = 'ot.php?PHPSESSID=".session_id()."';\n";
		if($_SESSION['sSesTemplateType'] == 'OP')
			$redirectJS = "response=coRegPopup.send('pageIncrement.php?PHPSESSID=".session_id()."','');$redirectJS";
	} else {
		$redirectJS = '';
	}
	
	$sTempEPage = str_replace('[NO_OFFERS_RELOAD]',($iRedirectCount == 0 ? "<script type=\"text/javascript\" >$redirectJS</script>" : ''),$sTempEPage);
	
	if ($_SESSION['sSesShowEmailCapturePage'] == 'N') {
		$sTempEPage = str_replace('[REG_FORM]',$_SESSION['sSesFullRegPageHTML'],$sTempEPage);
	} else {
		$sTempEPage = str_replace('[REG_FORM]',$_SESSION['sSesRegPageHTML'],$sTempEPage);
	}
	
	if($_SESSION['aSesCampaignHeaders'][$_SESSION['iSesCurrentPositionInFlow']]){
		$sTempHeader = $_SESSION['aSesCampaignHeaders'][$_SESSION['iSesCurrentPositionInFlow']];
	} else {
		$sTempHeader = "<img src='".$_SESSION['sSesHeaderImage']."'>";//$sReplaceHeaderSkip";
	}


	$sTempEPage = str_replace('[HEADER_IMAGE]',$sTempHeader,$sTempEPage);
	$sTempEPage = str_replace('[PRIVACY_POLICY]',$_SESSION['sSesPrivacyPolicy'],$sTempEPage);
	$sTempEPage = str_replace('[TERMS_CONDITIONS]',$_SESSION['sSesTermsAndConditions'],$sTempEPage);
	$sTempEPage = str_replace('[OPEN_THEY_HOST_URL]',$_SESSION['sSesOpenTheyHostUrl'],$sTempEPage);
	$sTempEPage = str_replace('[IFRAME_HEIGHT]',$_SESSION['iFrameHeight'],$sTempEPage);
	$sTempEPage = str_replace('[3RD_PARTY_IFRAME_HEIGHT]',$_SESSION['i3rdPartyFrameHeight'],$sTempEPage);
	$sTempEPage = str_replace('[FRAME_IN_3RD_PARTY_URL]',$_SESSION['sSesFrame3rdPartyUrl'],$sTempEPage);
	$sTempEPage = str_replace('[FOOTER]',$_SESSION['sSesFooter'],$sTempEPage);
	$sTempEPage = str_replace('[SESSION_ID]',session_id(),$sTempEPage);
	$sTempEPage = str_replace('[DEFAULT_TITLE]',$_SESSION['aDefaultTitle'][$_SESSION['iSesCurrentPositionInFlow']],$sTempEPage);
	$sTempEPage = str_replace('[REDIRECT_3RD_PARTY_URL]',$_SESSION['sSesRedirect3rdPartyUrl'],$sTempEPage);
	$sTempEPage = str_replace('[SOURCE_CODE_ID]', $_SESSION['iSesSourceCodeId'], $sTempEPage);
	
	//Recipe for Living stuff.
	$sTempEPage = str_replace('[RECIPE_TITLE]',$_SESSION['sSesRecipeTitle'],$sTempEPage);
	$sTempEPage = str_replace('[RECIPE_INGREDIENTS]',$_SESSION['sSesRecipeIngredients'],$sTempEPage);
	$sTempEPage = str_replace('[RECIPE_DIRECTIONS]',$_SESSION['sSesRecipeDirections'],$sTempEPage);
	
	
	$bFirePixel = false;
	// if user came to this site for the first time and fire pixel based on unique per site, then fire pixel
	if (($_SESSION['sSesEmailCapType'] == 'uniqueSite' || $_SESSION['sSesMemberCapType'] == 'uniqueSite') && $_SESSION['sSesUniqueUserPerSite'] == true) {
		// okay to fire pixel
		$bFirePixel = true;
	}
	
	// if email cap is unique to DB and user doesn't exists in eTracking or eTrackingHistory, then fire the pixel
	if ($_SESSION['sSesEmailCapType'] == 'uniqueDB' && $_SESSION['sSesUniqueEmailCapUserPerDb'] == true) {
		// okay to fire pixel
		$bFirePixel = true;
	}
	
	// if member is unique per DB and user doesn't exists in userData or userDataHistory, then fire the pixel
	if ($_SESSION['sSesMemberCapType'] == 'uniqueDB' && $_SESSION['sSesUniqueUserMemberPerDb'] == true) {
		// okay to fire pixel
		$bFirePixel = true;
	}
	
	if ($_SESSION['sSesCapType'] == 'memberCapture' && ($_SESSION['sSesMemberCapType'] == 'raw' || $_SESSION['sSesMemberCapType'] == '')) {
		// okay to fire pixel
		$bFirePixel = true;
	}
	
	if ($_SESSION['sSesCapType'] == 'emailCapture' && ($_SESSION['sSesEmailCapType'] == 'raw' || $_SESSION['sSesEmailCapType'] == '')) {
		// okay to fire pixel
		$bFirePixel = true;
	}
	
	$pixelHTML = '';
	if ($bFirePixel == true) {
		//to get our pixels, we're going to have to ask for the list for this page
		$pFactory = new PixelFactory();
		$list = $pFactory->pixelListByPage($_SESSION['sSesLastTemplateType'], $_SESSION['iSesCurrentPositionInFlow'], $_SESSION['iSesNoOfFlow'], $_SESSION['sSesSourceCode']);
		foreach ($list as $pixel) {
			$pixel->incrementDisplays();
			$pixelHTML .= $pixel->html();
		}
	}
	

	if ($_SESSION['sSesTemplateType'] != 'EP' && $_SESSION['sSesTemplateType'] != 'FRP' && $_SESSION['sSesTemplateType'] !='RP') {
		$sTempEPage = str_replace("[PIXEL_AFTER_EPAGE]", '', $sTempEPage);
		if (strstr($sTempEPage,"PIXEL_AFTER_REG")) {
			$sTempEPage = str_replace("[PIXEL_AFTER_REG]", $pixelHTML, $sTempEPage);
		}
	} else {
		$sTempEPage = str_replace("[PIXEL_AFTER_REG]", '', $sTempEPage);
		if (strstr($sTempEPage,"PIXEL_AFTER_EPAGE")) {
			$sTempEPage = str_replace("[PIXEL_AFTER_EPAGE]",$pixelHTML, $sTempEPage);
		}
	}


	// Partner Page - pre-populate data and redirect to 3rd party site.
	// 3rd party page template.PHPSESSID=".session_id();
	$sGoToVariable = "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?PHPSESSID='.session_id()."&skip=true";
	$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
	$sTempEPage = str_replace("[salutation]",urlencode($_SESSION['sSesSalutation']), $sTempEPage);
	$sTempEPage = str_replace("[email]",urlencode($_SESSION['sSesEmail']), $sTempEPage);
	$sTempEPage = str_replace("[first]",urlencode($_SESSION['sSesFirst']), $sTempEPage);
	$sTempEPage = str_replace("[last]",urlencode($_SESSION['sSesLast']), $sTempEPage);
	$sTempEPage = str_replace("[address]",urlencode($_SESSION['sSesAddress']), $sTempEPage);
	$sTempEPage = str_replace("[address2]",urlencode($_SESSION['sSesAddress2']), $sTempEPage);
	$sTempEPage = str_replace("[city]",urlencode($_SESSION['sSesCity']), $sTempEPage);
	$sTempEPage = str_replace("[state]",urlencode($_SESSION['sSesState']), $sTempEPage);
	$sTempEPage = str_replace("[zip]",urlencode($_SESSION['sSesZip']), $sTempEPage);
	$sTempEPage = str_replace("[phone]",urlencode($_SESSION['sSesPhone']), $sTempEPage);
	$sTempEPage = str_replace("[ipAddress]",urlencode($_SESSION['sSesRemoteIp']), $sTempEPage);
	$sTempEPage = str_replace("[phone_areaCode]", urlencode($_SESSION['sSesPhoneAreaCode']), $sTempEPage);
	$sTempEPage = str_replace("[phone_exchange]", urlencode($_SESSION['sSesPhoneExchange']), $sTempEPage);
	$sTempEPage = str_replace("[phone_number]", urlencode($_SESSION['sSesPhoneNumber']), $sTempEPage);
	$sTempEPage = str_replace("[birthYear]", urlencode($_SESSION['iSesBirthYear']), $sTempEPage);
	$sTempEPage = str_replace("[birthMonth]", urlencode($_SESSION['iSesBirthMonth']), $sTempEPage);
	$sTempEPage = str_replace("[birthDay]", urlencode($_SESSION['iSesBirthDay']), $sTempEPage);
	$sTempEPage = str_replace("[gender]", urlencode($_SESSION['sSesGender']), $sTempEPage);
	$sTempEPage = str_replace("[sourcecode]", urlencode($_SESSION['sSesSourceCodePersists']), $sTempEPage);
	$sTempEPage = str_replace("[revSrc]", urlencode($_SESSION['sSesRevSourceCode']), $sTempEPage);
	$sTempEPage = str_replace("[binary_gender]", urlencode($sBinGender), $sTempEPage);
	$sTempEPage = str_replace("[mm]", urlencode(date('m')), $sTempEPage);
	$sTempEPage = str_replace("[dd]", urlencode(date('d')), $sTempEPage);
	$sTempEPage = str_replace("[yyyy]", urlencode(date('Y')), $sTempEPage);
	$sTempEPage = str_replace("[yy]", urlencode(date('y')), $sTempEPage);
	$sTempEPage = str_replace("[hh]", urlencode(date('H')), $sTempEPage);
	$sTempEPage = str_replace("[ii]", urlencode(date('i')), $sTempEPage);
	$sTempEPage = str_replace("[ss]", urlencode(date('s')), $sTempEPage);
	$sTempEPage = str_replace("[gVariable]", urlencode($sGoToVariable), $sTempEPage);
	// END OF PARTNER PAGE
	
	$sTempEPage = str_replace("[PAGE_BG_COLOR]", "#FFFFFF", $sTempEPage);
	$sTempEPage = str_replace("[PAGE_TITLE]", "Sign Up For Tons Of Great Free Offers All At Once", $sTempEPage);
	$sHiddenFieldForMaxOffers = "<input type='hidden' name='iMaxNumOffers' value='".$_SESSION['iTempOfferShownCount']."'>";
	

	$sTempEPage = str_replace("[JAVASCRIPT_PREPOP]", $_SESSION['sSesJavaScriptPrePop'], $sTempEPage);
	$sTempEPage = str_replace("[HIDDEN_SOURCECODE]", $_SESSION['sSesHiddenSourceCode'].$sHiddenFieldForMaxOffers.$sOfferTypeHidden, $sTempEPage);
	
	
	$sTempTempVal = '';
	if ($_SESSION['sSesTemplateType'] != 'EP' && count($_SESSION['aSesPage2Offers']) == 0) {
		$sTempTempVal = "var maxOffers = document.form1.iMaxNumOffers.value;
						var checkedOffer = '';
						if (document.form1.sOfferTypeHidden.value == 'CK') {
							for (i=0; i<maxOffers; i++) {
								if (document.getElementById(i).checked) {
									checkedOffer += document.getElementById(i).value + ',';
								}
							}
						} else {
							for (i=0; i<maxOffers; i++) {
								if ((document.getElementById('Yes'+i)) && document.getElementById('Yes'+i).checked) {
									checkedOffer += document.getElementById('Yes'+i).value + ',';
								}
							}
						}";
		$sTempSubmitPgIncrement = "'submitPgIncrement.php?PHPSESSID=".session_id()."&oc='+checkedOffer";
	} else {
		$sTempSubmitPgIncrement = "'submitPgIncrement.php?PHPSESSID=".session_id()."&oc='";
	}


	
	$sTempSubmitPgIncrement = $sTempTempVal."response=coRegPopup.send($sTempSubmitPgIncrement,'');";
	$sTempEPage = str_replace("[SUBMIT_PG_INCREMENT]", $sTempSubmitPgIncrement, $sTempEPage);
	
	
	// START -OPEN WE HOST
	$sTempEPage = str_replace("[PAGE2_JAVA_SCRIPT]",$sPage2JavaScript, $sTempEPage);
	$sTempEPage = str_replace("[MESSAGE]", $sMessage, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_IMAGE]",$sTempOfferImage, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_SMALL_IMAGE]",$sTempOfferSmallImage, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_HEADLINE]", $sOfferHeadline, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_DESCRIPTION]", $sOfferDescription, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_SHORT_DESCRIPTION]", $sOfferShortDescription, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_ADDI_INFO_LINK]", $sTempAddiInfoLink, $sTempEPage);
	$sTempEPage = str_replace("[OFFER_PAGE2_TEMPLATE]", $sOfferPage2Template, $sTempEPage);
	$sTempEPage = str_replace("</body>", $sJavaScriptDisplayValues."</body>", $sTempEPage);
	// END - OPEN WE HOST
	
	
	if (count($_SESSION['aSesPage2Offers']) == 0) {
		$sTempEPage = str_replace('[CALL_FUNCTION]',"offerValidation()",$sTempEPage);
		
		$sTempEPage = str_replace('[PAGE_TEXT]',$_SESSION['aSesCampaignPageText'][$_SESSION['iSesCurrentPositionInFlow']][0],$sTempEPage);
	} else {
		$sTempEPage = str_replace('[CALL_FUNCTION]',"page2Validation()",$sTempEPage);
		
		$sTempEPage = str_replace('[PAGE_TEXT]',$_SESSION['aSesCampaignPageText'][$_SESSION['iSesCurrentPositionInFlow']][1],$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[EMAIL_FIELD]')) {
		$email = new EmailField();
		$email->value = $_SESSION['sSesEmail'];
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$email->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[EMAIL_FIELD]',$email->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[SALUTATION_FIELD]')) {
		$sal = new SalutationSelect();
		$sTempEPage = str_replace('[SALUTATION_FIELD]',$sal->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[FIRST_FIELD]')) {
		$f = new FNameField();
		$f->value = $_SESSION['sSesFirst'];
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$f->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[FIRST_FIELD]',$f->html(),$sTempEPage);
	}
	
	
	if (strstr($sTempEPage,'[LAST_FIELD]')) {
		$l = new LNameField();
		$l->value = $_SESSION['sSesLast'];
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$l->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[LAST_FIELD]',$l->html(),$sTempEPage);
	}
	
	
	if (strstr($sTempEPage,'[ADDRESS_GROUP]')) {
		$add = new AddressGroup();
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$add->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[ADDRESS_GROUP]',$add->html(),$sTempEPage);
	}
	 

	if (strstr($sTempEPage,'[ADDRESS_FIELD]')) {
		$address = new AddressField();
		$address->value = $sAddress;

		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$address->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[ADDRESS_FIELD]',$address->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[ADDRESS2_FIELD]')) {
		$address2 = new AddressField();
		$address2->value = $sAddress2;
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$address2->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[ADDRESS2_FIELD]',$address2->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[CITY_FIELD]')) {
		$city = new CityField();
		$city->value = $sCity;
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$city->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[CITY_FIELD]',$city->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[STATE_FIELD]')) {
		$state = new StateField();
		$state->value = $sState;
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$state->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[STATE_FIELD]',$state->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[ZIP_FIELD]')) {
		$zip = new ZipField();
		$zip->value = $sZip;
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$zip->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[ZIP_FIELD]',$zip->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[GENDER_FIELD]')) {
		$gender = new GenderSelect();
		$gender->value = $_SESSION['sSesGender'];
		$sTempEPage = str_replace('[GENDER_FIELD]',$gender->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[GENDER_RADIO]')) {
		$gen = new GenderRadio();
		$gen->value = $_SESSION['sSesGender'];
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$gen->extra .= " disabled ";
		}
		$sTempEPage = str_replace('[GENDER_RADIO]',$gen->html(),$sTempEPage);
	}

	if (strstr($sTempEPage,'[SKIP_BUTTON]')) {
		if ($_SESSION['sSesShowSkipButton'] != 'Y') {
			$skip = new SkipButton();
			$skip->extra = $sSkipNextToSubmitOnClick;
			$sTempEPage = str_replace('[SKIP_BUTTON]',$skip->html(),$sTempEPage);
		} else {
			$sTempEPage = str_replace('[SKIP_BUTTON]','',$sTempEPage);
		}
	}
	
	if (strstr($sTempEPage,'[PHONE_GROUP]')) {
		$phone = new PhoneField();
		$phone->value = $_SESSION['sSesPhone'];
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$phone->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[PHONE_GROUP]',$phone->html(),$sTempEPage);
	}

	if (strstr($sTempEPage,'[DOB_GROUP]')) {
		$dob = new DOBField();
		
		if ((!$_SESSION['sSesAllowEditUserForm']) && count($_SESSION['aSesPage2Offers']) > 0) {
			$dob->extra .= " disabled ";
		}
		
		$sTempEPage = str_replace('[DOB_GROUP]',$dob->html($_SESSION['iSesBirthMonth']."/".$_SESSION['iSesBirthDay']."/".$_SESSION['iSesBirthYear']),$sTempEPage);
	}

	if (strstr($sTempEPage,'[SUBMIT_BUTTON]')) {
		$submit->extra .= 'f=0; ';
		
		if ($_SESSION['sSesTemplateType'] == 'SPNS' || $_SESSION['sSesTemplateType'] == 'OP') {
			$submit->style .= "border-style:Double;";
		}
		
		$sTempEPage = str_replace('[SUBMIT_BUTTON]',$submit->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[LETS_GO_BUTTON]')) {
		$letsGo = new LetsGoButton();
		$letsGo->extra .= $submit->extra;
		$sTempEPage = str_replace('[LETS_GO_BUTTON]',$letsGo->html(),$sTempEPage);
	}
	
	if (strstr($sTempEPage,'[SUBMIT_IMAGE]')) {
		$submitImage = new SubmitImage();
		if($_SESSION['iSesCampaignId'] == '187'){
			$submitImage->style = "border-width:0px;height:100px;width:300px; cursor: pointer;";
		}
		$submitImage->extra .= $submit->extra;
		$sTempEPage = str_replace('[SUBMIT_IMAGE]',$submitImage->html(),$sTempEPage);
	}

	if (strstr($sTempEPage,'[SUBMIT_IMAGE2')) {
		$submitImage = new SubmitImage2();
		$submitImage->extra .= $submit->extra;

		$a = substr($sTempEPage, 0, strpos($sTempEPage, '[SUBMIT_IMAGE2'));
		$a = str_replace('[SUBMIT_IMAGE2','<input type="image" ',$a);
		$a .= $submitImage->head();

		$b = substr($sTempEPage, (strlen('[SUBMIT_IMAGE2') + strpos($sTempEPage, '[SUBMIT_IMAGE2')));
		$pos = strpos($b,']');
		$b = substr($b,0,$pos) . $submitImage->tail() . substr($b, $pos+1);

		$sTempEPage = $a.$b;
	}
	
	
	if ($iAjaxTempContent !='') {
		echo "<SCRIPT LANGUAGE=JavaScript SRC=\"http://www.popularliving.com/libs/javaScriptFunctions.js\" TYPE=text/javascript></script>
			<SCRIPT LANGUAGE=JavaScript SRC=\"/nibbles2/libs/ajax.js\" TYPE=text/javascript></script>".$iAjaxTempContent;
	}
	

	echo $sTempEPage;
	//echo $iAjaxTempContent;
	echo "<!--".$_SESSION['iSesCampaignId']."-->";



// Standard Popups
if ($_SESSION['sSesStopAllPop'] == 'N' && $_SESSION['sSesDisableStandardPop'] == 'N') {
	$_SESSION['sSesStandardPopContent'] = '';
	if (in_array($_SESSION['iSesCurrentPositionInFlow'],array_keys($_SESSION['aSesStandardPopPages']))) {
		if (count($_SESSION['aSesStandardPopPages'][$_SESSION['iSesCurrentPositionInFlow']]) > 0) {
			$_SESSION['sSesStandardPopContent'] = "<script type=\"text/javascript\" >";
			$aTempPopups = $_SESSION['aSesStandardPopPages'][$_SESSION['iSesCurrentPositionInFlow']];
			foreach ($aTempPopups as $aSomePopUp) {
				$_SESSION['sSesStandardPopContent'] .= "swin=window.open('".$aSomePopUp['url']."','','width=800,height=600,scrollbars=yes,resizable=yes');";
				
				if ($aSomePopUp['popUpUnder'] == 'UP') {
					$_SESSION['sSesStandardPopContent'] .= "swin.focus();";
				} else {
					$_SESSION['sSesStandardPopContent'] .= "swin.blur();";
				}
			}
			$_SESSION['sSesStandardPopContent'] .= "</script>";
		}
	}
	echo $_SESSION['sSesStandardPopContent'];
}


$sIEPops = "";
$sFFPops = "";

$iWM = '0';
$iAB = '0'; 
$iEX = '0';




echo "
<script type=\"text/javascript\">
function popperUppers(){
var properties ;
var pwin ;
" ;	

$sFFPops .= "
if (navigator.appName==\"Netscape\") {
properties='width=10,height=10,scrollbars=0,location=0,toolbar=0,menubar=0,resizable=0,status=0,directories=0,screenX='+this.screenX+',screenY='+this.screenY ;
";

$sIEPops .= "
} else {
properties='width=10,height=10,scrollbars=0,location=0,toolbar=0,menubar=0,resizable=0,status=0,directories=0,top='+window.screenTop+',left='+window.screenLeft ;
" ;

// WINDOW MANAGER POPUP
if (count($_SESSION['aSesWinManagerPopUrl']) > 0 && $_SESSION['iSesCurrentPositionInFlow'] == 1) {
	$iWM = '1' ;
	$_SESSION['bSesWinManagerOpened'] = true;
}


// If exit pop up is not disabled - NO TIME DELAYED
if (count($_SESSION['aSesExitPopUrl']) > 0 && $_SESSION['iSesCurrentPositionInFlow'] == 1) {
	$iEX = '1' ;
	$_SESSION['bSesExitOpened'] = true;
}


// ABANDON POPUPS
if (count($_SESSION['aSesAbandonedPopUrl']) > 0 && $_SESSION['iSesCurrentPositionInFlow'] == 1) {
	$iAB = '1' ;
	$_SESSION['bSesAbandonOpened'] = true;
}
if($iAB != '0' || $iWM != '0' || $iEX != '0' ){
	$sFFPops .= "pwin=window.open('omnipop.php?PHPSESSID=".session_id()."&wm=$iWM&ex=$iEX&ab=$iAB','pwin',properties);window.focus(); " ;
	$sIEPops .= "pwin=window.open('omnipop.php?PHPSESSID=".session_id()."&wm=$iWM&ex=$iEX&ab=$iAB','pwin',properties);window.focus(); " ;
}
echo $sFFPops ."
". $sIEPops ;
echo "
 }
}
popperUppers();
</script>";


// call cookie script using 3400cookie domain to set the cookie
// with that domain.  pass in session id in the query string.
// also call the same script using popularliving to set the cookie with
// that domain since our client fires the pixel with popularliving domain.

$sUrl = "http://www.3400cookie.com/nibbles2/cookie.php?PHPSESSID=".session_id()."&url=";
echo "<img src='$sUrl' width='1' height='1'>";
$sUrl = "http://www.popularliving.com/nibbles2/cookie.php?PHPSESSID=".session_id()."&url=";
echo "<img src='$sUrl' width='1' height='1'>";
$sUrl = $_SESSION['sSesDomain']."/nibbles2/cookie.php?PHPSESSID=".session_id()."&url=".$_SESSION['sSesDomain'];
echo "<img src='$sUrl' width='1' height='1'>";
// Do not modify above 6 lines of code.  Above scripts will
// set the cookie with offer taken and it will also read the cookie
// and get all offer taken so we don't show the same offer again.
// Also this script will set the cookie with session id which will be used
// when our client fires the pixel script.

// the reason why we need to update this entry because the script won't set cookie until the page is loaded completely.
if ($_SESSION['updateBdRedirectsTracking'] && isset($_COOKIE['AmpereSessionId'])) {
	$update = dbQuery("UPDATE bdRedirectsTracking SET cookieEnabled = 'Y' WHERE id = '".$_SESSION['bdRedirectsTrackingInsertId']."' LIMIT 1");
	$_SESSION['updateBdRedirectsTracking'] = false;
	$_SESSION['bdRedirectsTrackingInsertId'] = '';
}




$sGetPageId = "SELECT id FROM otPages
			WHERE flowId = '".$_SESSION['iSesFlowId']."'
			AND pageNo = $iTempVal + 1";
$rPageId = dbQuery($sGetPageId);
echo dbError();
while ($oPageIdRow = dbFetchObject($rPageId)) {
	// don't want to count page reload.
	$sCheck = "SELECT * FROM tempPageDisplayStats
					WHERE sessionId = '".session_id()."'
					AND sourceCode = '".$_SESSION['sSesSourceCode']."'
					AND pageId = '".$oPageIdRow->id."'";
	$rCheckResult = dbQuery($sCheck);
	if (dbNumRows($rCheckResult) == 0 && count($_SESSION['aSesPage2Offers']) == 0) {
		// insert into temporary page display counts table
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate, sessionId, ipAddress, openDateTime)
			   VALUES('".$oPageIdRow->id."', '".$_SESSION['sSesSourceCode']."','".$_SESSION['sSesSubSourceCode']."',CURRENT_DATE,'".session_id()."', 
			   '".$_SERVER['REMOTE_ADDR']."', '$sCurrentDateTime')";
		$rPageStatResult = dbQuery($sPageStatQuery);
		echo dbError();
	}
}


?>


<?php
/*********
Script to Handle Ot Page1 Submission.
Script is Called when User submits the form in ot page1
**********/
/************* initialize script variables ***************/
$sMessage = "";
$sCheckQuery = "";
$rCheckResult = "";
$sInsertQuery = "";
$rInsertResult = "";
$sOfferQuery = "";
$rOfferResult = "";
$oOfferRow = "";
$aPage2Offers = "";
$sPageQuery = "";
$rPageResult = "";
$oPageRow = "";
$sRedirectTo = "";
$sLeadInsertQuery = "";
$rLeadInsertResult = "";

/***************  End Initializing variables **********************/
include("includes/paths.php");
// start session before including otPageInclude.php
session_start();
include_once("$sGblRoot/validateAddress/validateAddressAo.php");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

// get current date and time to replace in post string if have to
$iCurrYear = date('Y');
$iCurrYearTwoDigit = date('y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

// save values as session variables
if (! isset($_SESSION["sSesFirst"])) {
	// set session variables if not already set
	$_SESSION['sSesMessage'] = '';	
}

	// rest offers taken/stats session variables
	$_SESSION["aSesOffersTaken"] = array();
	$_SESSION["aSesPage1Offers"] = array();
	$_SESSION["aSesPage2Offers"] = array();
	$_SESSION['sSesOfferAbortStatInfo'] = '';
	$_SESSION['sSesOfferTakenStatInfo'] = '';
	$_SESSION['aSesOfferPage2Data'] = array();
	
	// reset user data session variables
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
	$_SESSION["iSesJoinListId"] = '';
	$_SESSION["sSesPageMode"] = '';
	$_SESSION["sSesFirstNameAsterisk"] = '';
	$_SESSION["sSesLastNameAsterisk"] = '';
	$_SESSION["sSesAddressAsterisk"] = '';
	$_SESSION["sSesAddress2Asterisk"] = '';
	$_SESSION["sSesCityAsterisk"] = '';
	$_SESSION["sSesStateAsterisk"] = '';
	$_SESSION["sSesZipCodeAsterisk"] = '';
	$_SESSION["sSesPhoneNoAsterisk"] = '';
	$_SESSION["sSesEmailAsterisk"] = '';
	$_SESSION["iSesPageAutoRespSent"] = '';
	$_SESSION["sSesBirthDateAsterisk"] = '';
	$_SESSION["sSesGenderAsterisk"] = '';
	$_SESSION["iSesBirthYear"] = '';
	$_SESSION["iSesBirthMonth"] = '';
	$_SESSION["iSesBirthDay"] = '';
	$_SESSION["sSesGender"] = '';

// include file after resetting session variables, 
// otherwise it will replace any blank field values and display that
// session value in the field while giving the error message (i.e. if user left any field balnk)
// echo "subSource".$sSubSourceCode;
include("$sGblIncludePath/newOtPageIncludeC.php");

// Important: Reset offers checked array here, otherwise, offers will be added again and again 
// while resubmitting page in case of any error

$_SESSION["aSesOffersChecked"] = array();
$_SESSION["aSesOffersChecked"] = $aOffersChecked;
$sRemoteIp = trim($_SERVER['REMOTE_ADDR']);
$sEmail = trim($sEmail);
$sFirst = trim($sFirst);
$sLast = trim($sLast);
$sAddress = trim($sAddress);
$sAddress2 = trim($sAddress2);
$sCity = trim($sCity);
$sZip = trim($sZip);
$sPhone = trim($sPhone);
$iBirthDay = trim($iBirthDay);
$iBirthMonth = trim($iBirthMonth);
$iBirthYear = trim($iBirthYear);
$sGender = trim($sGender);

if ($sCity != '') {
	$sCity = ereg_replace("\."," ",$sCity);
	$sCity = ereg_replace("  "," ",$sCity);
}

/***************  Place variables into session  ********************/
$_SESSION["sSesSalutation"] = $sSalutation;
$_SESSION["sSesFirst"] = $sFirst;
$_SESSION["sSesLast"] = $sLast;
$_SESSION["sSesEmail"] = $sEmail;
$_SESSION["sSesAddress"] = $sAddress;
$_SESSION["sSesAddress2"] = $sAddress2;
$_SESSION["sSesCity"] = $sCity;
$_SESSION["sSesState"] = $sState;
$_SESSION["sSesZip"] = $sZip;
$_SESSION["sSesPhone"] = $sPhone;
$_SESSION["iSesJoinListId"] = $iJoinListId;

$_SESSION["iSesBirthYear"] = $iBirthYear;
$_SESSION["iSesBirthMonth"] = $iBirthMonth;
$_SESSION["iSesBirthDay"] = $iBirthDay;
$_SESSION["sSesGender"] = $sGender;

if ($sSourceCode) {
	if (!ctype_alnum($sSourceCode)) {
		$sSourceCode = '';
	}
	$_SESSION["sSesSourceCode"] = $sSourceCode;
}

if ($sSubSourceCode) {
	if (!ctype_alnum($sSubSourceCode)) {
		$sSubSourceCode = '';
	}
	$_SESSION["sSesSubSourceCode"] = $sSubSourceCode;
}

if ($sPageMode) {
	$_SESSION["sSesPageMode"] = $sPageMode;
}

$_SESSION['sSesRevSourceCode'] = strrev($_SESSION["sSesSourceCode"]);

$sErrorMessageSQL = "SELECT * FROM linksErrorMessages WHERE sourceCode = '".$_SESSION["sSesSourceCode"]."'";
$rErrorMessage = dbQuery($sErrorMessageSQL);
$oErrorMessage = dbFetchObject($rErrorMessage);
/***************  End Placeing variables into session  ********************/
// If the page has yes/no button, then offers checked is a multidimensional array
// because had to give different name to radiobutton to create different yes/no group
// so the radio button group name was aOffersChecked['offerCode'][] for an offer
// instead of using checkbox name as aOffersChecked[] for other pages
// make the array unidimentional as others

if ($iDisplayYesNo) {
	$i = 0;
	$j=0;
	if (is_array($aOffersChecked)) {
		$_SESSION["aSesOffersYesNo"] = array();
		$_SESSION["aSesOffersYesNo"] = $aOffersChecked;
	while (list($key,$val) = each($aOffersChecked)) {	
		if (is_array($val)) {
			if ($val[0] != 'N') {					
				$aTempOffersChecked[$i] = $val[0];
				$i++;
			}
			$aTempYesNoOffersChecked[$j] = $val[0];
			$j++;
		}
	}
}

	if (!($iOfferNotRequired)) {
		if (count($aTempOffersChecked) == 0) {
			$sYesNoError .= "<li>To continue, you must choose to view at least one offer by checking YES.";
		}
	} 
		if ($sRequireYesNo == 'all') {
			if ($_SESSION['sSesOffersCount'] != '') {
				$iYesNoOffersCount = $_SESSION['sSesOffersCount'];
			}
			if (count($aOffersChecked) < $iYesNoOffersCount) {
				if($oErrorMessage->checkAllOffers != ''){
					$sYesNoError .= "<li>".$oErrorMessage->checkAllOffers."...";
				} else {
					$sYesNoError .= "<li>Please Make Sure That You Check Either Yes Or No For Each Offer...";
				}
			}
		} else if ($iOfferNotRequired) {
			//Note: If offerNotRequired is not checked in above condition, it will display two similar error messages...
			if ($sRequireYesNo == 'anyOne') {
				if (count($aTempYesNoOffersChecked) == 0) {
					if($oErrorMessage->ynAtLeastOneOffer != ''){
						$sYesNoError .= "<li>".$oErrorMessage->checkAtLeastOneOffer."...";
					} else {
						$sYesNoError .= "<li>Please Make Sure That You Check Either Yes Or No For At Least One Offer...";
					}
				}
			} else if ($sRequireYesNo == 'oneYes') {
				if (count($aOffersChecked) == 0) {
					if($oErrorMessage->checkAtLeastOneOffer != ''){
						$sYesNoError .= "<li>".$oErrorMessage->checkAtLeastOneOffer."...";
					} else {
						$sYesNoError .= "<li>Please Make Sure That You Check Either Yes For At Least One Offer...";
					}
				}
			}
		} //else if 
	
	if ($sYesNoError != '') {
		$sMessage .= $sYesNoError;
	}

	$aOffersChecked = $aTempOffersChecked;
	
	// store yes/no array for to use in case if user has any submission error, 
	// used to display yes/no checked which user checked before
	$_SESSION["aSesOffersChecked"] = array();
	$_SESSION["aSesOffersChecked"] = $aTempYesNoOffersChecked;
}

// This section is for close they host offers - START
$_SESSION['aSesCloseTheyHostOffersChecked'] = array();
$iTempCTH = 0;
// This is list of offers trigger on YES
foreach ($aTempOffersChecked as $sCloseTheyHostTempOffer) {
	$sCloseTheyHostQuery = "SELECT * FROM offers WHERE offerCode='$sCloseTheyHostTempOffer'
					AND isCloseTheyHost = 'Y' LIMIT 1";
	$rCloseTheyHostResult = dbQuery($sCloseTheyHostQuery);
	if ( dbNumRows($rCloseTheyHostResult) == 1 ) {
		$_SESSION['aSesCloseTheyHostOffersChecked'][$iTempCTH] = $sCloseTheyHostTempOffer;
		$iTempCTH++;
	}
}
// This section is for close they host offers - END

if (count($aOffersChecked) > 0) {
	if ($sEmail != '') {
		if ( !(validateEmailFormat($sEmail)) ) {
			$_SESSION['sSesEmail'] = '';
			$sEmail = '';
		}
	}
	
	if ($sFirst != '') {
		if ( !(validateName($sFirst))) {
			$_SESSION['sSesFirst'] = '';
			$sFirst = '';
		}
	}
	
	if ($sLast != '') {
		if ( !(validateName($sLast))) {
			$_SESSION['sSesLast'] = '';
			$sLast = '';
		}
	}
	
	if ($sAddress != '' && $sCity != '' && $sState != '' && $sZip != '') {
		$sAoValidation = validateAddressAo( addslashes($sAddress), addslashes($sAddress2), $sCity, $sState, $sZip, $sGblRoot );

		if( substr( $sAoValidation, 0, 7 ) == "Failure" ) {
			$_SESSION['sSesAddress'] = '';
			$_SESSION['sSesAddress2'] = '';
			$_SESSION['sSesCity'] = '';
			$_SESSION['sSesState'] = '';
			$_SESSION['sSesZip'] = '';
			$sAddress = '';
			$sAddress2 = '';
			$sCity = '';
			$sState = '';
			$sZip = '';
			$bIsPostalVerified = false;
			$_SESSION['sSesIsPostalVerified'] = false;
		}
	
		if ( substr( $sAoValidation, 0, 6 ) == "update" ) {
			$aAoErrorLine = explode( "|", $sAoValidation );
			$aNewAddressText = explode( "=", $aAoErrorLine[1] );
			$aNewAddress2Text = explode( "=", $aAoErrorLine[2] );
			$aNewCityText = explode( "=", $aAoErrorLine[3] );
			$aNewStateText = explode( "=", $aAoErrorLine[4] );
			$aNewZipText = explode( "=", $aAoErrorLine[5] );
			
			$sAddress = $aNewAddressText[1];
			$sAddress2 = $aNewAddress2Text[1];
			$sCity = $aNewCityText[1];
			$sState = $aNewStateText[1];
			$sZip = $aNewZipText[1];
			
			$_SESSION["sSesAddress"] = $sAddress;
			$_SESSION["sSesAddress2"] = $sAddress2;
			$_SESSION["sSesCity"] = $sCity;
			$_SESSION["sSesState"] = $sState;
			$_SESSION["sSesZip"] = $sZip;
			
			$bIsPostalVerified = true;
			$_SESSION['sSesIsPostalVerified'] = true;
		}
	}
}


// check validation only if user is allowed to take 0 offers and user has not taken 0 offers
if ((($sMessage == '' && count($aOffersChecked) > 0 ) || ($iOfferNotRequired && $sMessage == '')) && $sYesNoError == '' ) {
	$sTempSessId = session_id();
	$sTempSrcSourceCode = $_SESSION["sSesSourceCode"];
	foreach ($aOffersChecked as $sTempOfferCode) {
		$sCheckTemp = "SELECT * FROM xOutData
					WHERE offerCode = '$sTempOfferCode'
					AND email = '$sEmail'
					AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
		$rCheckTempResult = dbQuery($sCheckTemp);
		echo dbError();
		
		if ( dbNumRows($rCheckTempResult) == 0 ) {
			$sInsertXOut = "INSERT INTO xOutData (offerCode, email, dateTimeAdded, sessionId, sourceCode, pageId)
				VALUES (\"$sTempOfferCode\", \"$sEmail\", now(), \"$sTempSessId\", \"$sTempSrcSourceCode\",".$_SESSION["iSesPageId"].")";
			$rInsertXOutResult = dbQuery($sInsertXOut);
			$sTempSrcSourceCode = '';
		}
	}

	// If data validated successfully,
	// reset offerchecked array
	$_SESSION["aSesOffersChecked"] = array();

	if (count($aOffersChecked) > 0) {
		$j=0;
		//$bAllAndOnlyCoRegOffersChecked = true;
		for ($i = 0; $i < count($aOffersChecked); $i++) {
			// check if offer has page2 info
			$iPage2Info = '';
			$iDeliveryMethodId = '';
			$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
									OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
							FROM   offers AS O LEFT JOIN offerLeadSpec AS OL 
							ON  O.offerCode = OL.offerCode
							WHERE	   O.offerCode = '".$aOffersChecked[$i]."'";
			$rOfferResult = dbQuery($sOfferQuery);
			echo dbError();
			while ($oOfferRow = dbFetchObject($rOfferResult)) {
				$iPage2Info = $oOfferRow->page2Info;
				$cIsCoRegPopUp = $oOfferRow->isCoRegPopUp;
				$sIsCloseTheyHost = $oOfferRow->isCloseTheyHost;
				$sOfferType = $oOfferRow->offerType;
			}
			
			if ($rOfferResult) {
				dbFreeResult($rOfferResult);
			}
			
			//uncomment below for "no skips for co-reg"
			if($cIsCoRegPopUp == 'N' && $sIsCloseTheyHost == 'N' && $sOfferType != 'CR' && $iPage2Info) {
			//if($cIsCoRegPopUp == 'N' && $sIsCloseTheyHost == 'N') {
				//$bAllAndOnlyCoRegOffersChecked = false;
				array_push($_SESSION['aSesPage2Offers'], $aOffersChecked[$i]);
			} else {
				array_push($_SESSION['aSesPage1Offers'], $aOffersChecked[$i]);
			}
			
			//uncomment below for "no skips for co-reg"
			if ($iPage2Info && $sOfferType != 'CR') {
			//if ($iPage2Info) {
				// put the page2 offer in page2Offers array
				//array_push($_SESSION['aSesPage2Offers'], $aOffersChecked[$i]);
			} else {
				// put the page1 offer in page1Offers array
				array_push($_SESSION['aSesPage1Offers'], $aOffersChecked[$i]);
			}
		}
	} // end if offers taken count > 0 

		$_SESSION['sSesJavaScriptVars'] = $_SESSION['sSesInboundJavaScriptVars'];
		$_SESSION['sSesJavaScriptVars'] .= "\n var sEmail = '".$sEmail."';";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sSalutation = '".$sSalutation."';";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sFirst = \"".$sFirst."\";";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sLast = \"".$sLast."\";";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sAddress = \"".$sAddress."\";";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sAddress2 = \"".$sAddress2."\";";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sCity = '".$sCity."';";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sState = '".$sState."';";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sZip = '".$sZip."';";	
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhone = \"".$sPhone."\";";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sSourceCode = '".$sSourceCode."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sGender = '".$sGender."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var iBirthYear = '".$iBirthYear."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var iBirthMonth = '".$iBirthMonth."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var iBirthDay = '".$iBirthDay."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhone_areaCode = '".substr($sPhone,0,3)."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhone_exchange = '".substr($sPhone,4,3)."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhone_number = '".substr($sPhone,8,4)."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhone_ext = '';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sPhoneNoDash = '".substr($sPhone,0,3).substr($sPhone,4,3).substr($sPhone,8,4)."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sSubSourceCode = '".$sSubSourceCode."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sRemoteIp = '".trim($_SERVER['REMOTE_ADDR'])."';";
		$_SESSION['sSesJavaScriptVars'] .= "\n var sRevSourceCode = '".trim($_SESSION['sSesRevSourceCode'])."';";

		// changed following line to go to specific page2. If specific page2 is not there,
		// it will go to the default page2.php.
		// this is temporary until all the pages are regenerated (so, the page2 also will be generated now)
		if ($iOfferNotRequired && count($aOffersChecked) == 0) {
			if ($iEnableGoTo && $_SESSION["sSesGoTo"] != '') {
				header("Location:".$_SESSION["sSesGoTo"]);
				exit;
			}
			
			$sSesPage2NamePageName = $_SESSION['sSesPage2Name'];
			$sSesPage2NamePageName = str_replace('_c_2.php','',$sSesPage2NamePageName);//strip the suffix
			
			//get the redirectTo field from otPages where pageName = $_SESSION['sSesPage2Name']
			$sOtPagesRedirectToQuery = "SELECT * FROM otPages WHERE pageName = '$sSesPage2NamePageName'";
			$rOtPagesResult = dbQuery($sOtPagesRedirectToQuery);
			$oOtPagesRedirectTo = dbFetchObject($rOtPagesResult);
			$sOtPagesRedirectTo = $oOtPagesRedirectTo->redirectTo;
			
			if (!(strstr($sOtPagesRedirectTo,"/p/"))) {
				if ($iOfferNotRequired && count($aOffersChecked) == 0) {
					header("Location:$sOtPagesRedirectTo");
					exit;
				}
			}
			
			$sOtPagesRedirectTo = str_replace('/p/','',$sOtPagesRedirectTo);
			
			if(file_exists("$sGblOtPagesPath/".$sOtPagesRedirectTo)){ 
				if($_SERVER['HTTPS']){
					$sGblOtPagesUrl = "https://".$_SERVER['SERVER_NAME']."/p";
				} else {
					$sGblOtPagesUrl = "http://".$_SERVER['SERVER_NAME']."/p";
				}

				if (count($_SESSION['aSesCloseTheyHostOffersChecked']) == 0) {
					header("Location:$sGblOtPagesUrl/".$sOtPagesRedirectTo."?PHPSESSID=".session_id());
				} else {
					// Redirect to closeTheyHost pages.
					$prot = ($_SERVER['HTTPS'] ? "https://" : "http://");
					$sTheyHostContinueURL = $prot.$_SERVER['SERVER_NAME']."/closeTheyHost/closeTheyHost.php?PHPSESSID=".session_id();
					$_SESSION["sSesCloseTheyHostNextUrl"] = $sGblOtPagesUrl."/".$sOtPagesRedirectTo."?PHPSESSID=".session_id();
					header("Location:$sTheyHostContinueURL");
				}
			} else {
				header("Location:otPage2.php?PHPSESSID=".session_id());
			}
			
		} else if (isset($_SESSION["sSesPage2Name"]) && $_SESSION["sSesPage2Name"] != '' && file_exists("$sGblOtPagesPath/".$_SESSION["sSesPage2Name"])) {
			
			//ok, for c pages to work right now, we need to check that there is data in each of the form fields.
			//if the user completed all of the reg stuff, and we've got a phone number, then we can send them straight to the page 2 submit page
			//else, we should send them on to the right place. 
			$bRegDataSatisfied = false;
			if((strlen($sFirst) > 0) && 
				(strlen($sLast) > 0) &&
				(strlen($sAddress) > 0) &&
				(strlen($sCity) > 0) &&
				(strlen($sState) > 0) &&
				(strlen($sZip) > 0) &&
				(strlen($sEmail) > 0) &&
				((strlen($_SESSION['sSesPhone']) > 0) || 
					((strlen($_SESSION['sSesPhoneAreaCode'])) && 
					 (strlen($_SESSION['sSesPhoneExchange'])) && 
					 (strlen($_SESSION['sSesPhoneNumber'])
					)
				))){
			
				$bRegDataSatisfied = true;
			}

			if ((count($_SESSION['aSesPage1Offers']) > 0) && (count($_SESSION['aSesPage2Offers']) == 0) && $bRegDataSatisfied){
				$prot = ($_SERVER['HTTPS'] ? "https://" : "http://");
				$sGblOtPagesUrl = $prot.$_SERVER['SERVER_NAME'];
				header("Location:$sGblOtPagesUrl/newOtPage2SubmitC.php?PHPSESSID=".session_id());
			} else if ((count($_SESSION['aSesPage2Offers']) > 0) || (count($_SESSION['aSesPage1Offers']) > 0)){
				$prot = ($_SERVER['HTTPS'] ? "https://" : "http://");
				$sGblOtPagesUrl = $prot.$_SERVER['SERVER_NAME']."/p";
				// Check if any offers requires SSL - Start
				$_SESSION["bSesRedirectToSsl"] = false;
				foreach ($_SESSION['aSesPage2Offers'] as $sTempOffer) {
					$sCheckRequireSSLQuery = "SELECT * FROM offers WHERE offerCode='$sTempOffer' AND isRequireSSL = 'Y'";
					$rCheckRequireSSLResult = dbQuery($sCheckRequireSSLQuery);
					if (dbNumRows($rCheckRequireSSLResult) == 1) {
						$sGblOtPagesUrl = "https://".$_SERVER['SERVER_NAME']."/p";
						$_SESSION["bSesRedirectToSsl"] = true;
						break;
					}
				}
				// Check if any offers requires SSL - End
				header("Location:$sGblOtPagesUrl/".$_SESSION["sSesPage2Name"]."?PHPSESSID=".session_id());
			} else {
				if (count($_SESSION['aSesCloseTheyHostOffersChecked']) > 0) {
                                        // Redirect to closeTheyHost pages.
                                        $prot = ($_SERVER['HTTPS'] ? "https://" : "http://");
                                        $sTheyHostContinueURL = $prot.$_SERVER['SERVER_NAME']."/closeTheyHost/closeTheyHost.php?PHPSESSID=".session_id();
                                        $_SESSION["sSesCloseTheyHostNextUrl"] = $sRedirectToNotOfferTaken;
                                        header("Location:$sTheyHostContinueURL");
                                } else {
					header("Location:otPage2.php?PHPSESSID=".session_id());
				}
			}
		} else {
			header("Location:otPage2.php?PHPSESSID=".session_id());
		}
			
} else if (count($aOffersChecked) == 0) {
	if (!$iDisplayYesNo) {
		$sMessage .= "<li>You Must Check An Offer...";
	}
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=".urlencode($sMessage)."&$sOutboundQueryString&PHPSESSID=".session_id());
	} else {
		header("Location:$sRefererScriptFileName?sMessage=".urlencode($sMessage)."&$sOutboundQueryString&PHPSESSID=".session_id());
	}
} else {
	// display page again with the current values filled in
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=".urlencode($sMessage)."&$sOutboundQueryString&PHPSESSID=".session_id());
	} else {
		header("Location:$sRefererScriptFileName?sMessage=".urlencode($sMessage)."&$sOutboundQueryString&PHPSESSID=".session_id());
	}
}

?>

<?php

// Script to Handle Ot Page2 Submission.
// Script is Called when User submits the ot page 2
include("includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");
include_once("$sGblRoot/validateAddress/validateAddressAo.php");

// start the session before including otPageInclude.php
session_start();
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
$bIsPostalVerified = false;
$bInsert = false;
$_SESSION['sSesIsPostalVerified'] = false;
$_SESSION['sSesPhoneFalse'] = true;
$sTempSessionId = session_id();

// get current date and time to replace in post string if have to
$iCurrYear = date('Y');
$iCurrYearTwoDigit = date('y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

// Include otPageInclude, which performs the following actions:
include("$sGblIncludePath/newOtPageIncludeC.php");

// Variable Initialization:
// Set up flags for the Phone and User Form Data.
// Set to false if any validation on them fails.
$bPhoneIsSet=true;
$bUserDataIsSet=true;

// Initialize other variables.
$sMessage = '';
$sPage2ErrorMessage = '';

$sRemoteIp = trim($_SERVER['REMOTE_ADDR']);
$sServerIp = trim($_SERVER['SERVER_ADDR']);
$_SESSION["sSesFirstNameAsterisk"] = '';
$_SESSION["sSesLastNameAsterisk"] = '';
$_SESSION["sSesAddressAsterisk"] = '';
$_SESSION["sSesAddress2Asterisk"] = '';
$_SESSION["sSesCityAsterisk"] = '';
$_SESSION["sSesStateAsterisk"] = '';
$_SESSION["sSesZipCodeAsterisk"] = '';
$_SESSION["sSesPhoneNoAsterisk"] = '';
$_SESSION["sSesEmailAsterisk"] = '';

// Format and Validate Form Informaiton:
// Trim whitespace from form entries.

$sEmail = trim($sEmail);
$sFirst = trim($sFirst);
$sLast = trim($sLast);
$sAddress = trim($sAddress);
$sAddress2 = trim($sAddress2);
$sCity = trim($sCity);
$sZip = trim($sZip);
$sPhone_ext = trim($sPhone_ext);
// If previous page had 3-part phone form elements, set sPhone to the combined and formated value.
// Otherwise, set the 3-part values to the parts of sPhone
// If all are blank, all remain blank.
if (isset($sPhone_areaCode) && isset($sPhone_exchange) && isset($sPhone_number) && ($sPhone_areaCode != '') && ($sPhone_exchange != '') && ($sPhone_number != '')) {
	$sPhone = trim($sPhone_areaCode)."-".trim($sPhone_exchange)."-".trim($sPhone_number);
	$sPhoneNoDash = trim($sPhone_areaCode).trim($sPhone_exchange).trim($sPhone_number);
} else {
	$sPhone_areaCode = substr($sPhone,0,3);
	$sPhone_exchange = substr($sPhone,4,3);
	$sPhone_number = substr($sPhone,8,4);
}

// Replace "." with a space in the city.
// Replace any double-spaces (caused by previous) with single-space.

if ($sCity != '') {
	$sCity = ereg_replace("\."," ",$sCity);
	$sCity = ereg_replace("  "," ",$sCity);
}

// If Previous page had User Data Form Elements, and they were filled out,
// Place these in the session variables.

if ($sSalutation != '') {
	$_SESSION["sSesSalutation"] = $sSalutation;
	$sSalutationDisable = 'disabled';
}

if ($sFirst != '') {
	$_SESSION["sSesFirst"] = $sFirst;
	$sFirstDisable = 'disabled';
}

if ($sLast != '') {
	$_SESSION["sSesLast"] = $sLast;
	$sLastDisable = 'disabled';
}

if ($sEmail != '') {
	$_SESSION["sSesEmail"] = $sEmail;
	$sEmailDisable = 'disabled';
}

if ($sAddress != '' && $sCity != '' && $sState !='' && $sZip != '') {
	$_SESSION["sSesAddress"] = $sAddress;
	$sAddressDisable = 'disabled';
	$_SESSION["sSesCity"] = $sCity;
	$sCityDisable = 'disabled';
	$_SESSION["sSesState"] = $sState;
	$sStateDisable = 'disabled';
	$_SESSION["sSesZip"] = $sZip;
	$sZipDisable = 'disabled';
	$_SESSION["sSesAddress2"] = $sAddress2;
	$sAddress2Disable = 'disabled';
}

if ($sPhone != '') {
	$_SESSION['sSesPhone'] = substr($sPhone,0,12);
	$sPhoneDisable = 'disabled';
}

//mail('bbevis@amperemedia.com','page 2 offers stuff',"aSesPage1Offers:\n".print_r($_SESSION["aSesPage1Offers"],true)."\naSesPage2Offers:\n".print_r($_SESSION["aSesPage2Offers"],true));

// Process those offers which have no Page2 Questions.
// (IE: those offers in $_SESSION["aSesPage1Offers"].
while (list($i, $val) = each($_SESSION["aSesPage1Offers"])) {
	$sOfferCode = $_SESSION["aSesPage1Offers"][$i];
	if($aSkipOffers == $sOfferCode){
         $_SESSION['sSesOfferAbortStatInfo'] .= "$sOfferCode,";
	} else {
		// Add each offer to "offers taken" session variable
		// IE: $_SESSION['aSesOffersTaken']
		array_push($_SESSION['aSesOffersTaken'], $sOfferCode);
		// Add offer to Stat Info in session.
		$_SESSION['sSesOfferTakenStatInfo'] .= $sOfferCode.",";	
	}
    // Remove from array of Page1Offers
    unset($_SESSION["aSesPage1Offers"][$i]);
}


// Set variable to track whether any Page2 Fields have validation errors.
// If set to false now, can set to true for any error, and at the end,
// can tell if there were.
$bPage2Error = false;
// Loop through each "Page2 Offer" that has not been removed from the list.
while (list($i, $val) = each($_SESSION["aSesPage2Offers"])) {
	// Check for Offers that the user has skipped
	$bSkipped = "false";
	$sPage2Fields = '';
	$sPage2Data = '';
	$sOfferCode = $_SESSION["aSesPage2Offers"][$i];
	// For the current offerCode, check the "aSkipOffers" variable to
	// See if it has been skipped.
	//for ( $j=0; $j < count($aSkipOffers);$j++) {
	if ($aSkipOffers == $sOfferCode) {
		// If This offer has been dropped, add it to the stats for dropped offers.
		// And remove it from the Page2Offers
		$bSkipped = "true";
		//$sMessage='';
		$_SESSION['sSesOfferAbortStatInfo'] .= "$sOfferCode,";
		unset($_SESSION["aSesPage2Offers"][$i]);
		//break;
	}

	// If the offer was not dropped, process it.
	if ($bSkipped != "true") {
		// Get all Page2 Fields from page2Map in nibbles database.
		$sPage2MapQuery = "SELECT *
							   FROM   page2Map
				 			   WHERE offerCode = '$sOfferCode'
				 			   ORDER BY storageOrder ";
		$rPage2MapResult = dbQuery($sPage2MapQuery);

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
			$iIsRequired = $oPage2MapRow->isRequired;
			$iEncryptData = $oPage2MapRow->encryptData;
			// If a field is required and it has a zero length (blank), give error.
			if ( strlen($$sActualFieldName) == 0 && $iIsRequired) {
				$sTempMessage .= "Field $sActualFieldName Is Missing...\r\n";
				$bPage2Error = true;
			} else {
				// Check for entries in offerPage2Options
				$sTempOptionValid = "false";
				$sOptionsQuery = "SELECT *
							  FROM   offerPage2Options
							  WHERE  offerCode = '$sOfferCode'
							  AND	 fieldName = '$sActualFieldName'";
				$rOptionsResult = dbQuery($sOptionsQuery);
				echo dbError();
				// If there are no options in the database, then all options are valid,
				// else, loop options and try to find match.
				if ( dbNumRows($rOptionsResult) == 0 ) {
					$sTempOptionValid = "true";
				} else {
					while ($oOptionsRow = dbFetchObject($rOptionsResult)) {

						if ($oOptionsRow->fieldValue == stripslashes(${$sActualFieldName})) {
							$sTempOptionValid = "true";
						}
					}
				}

				// If not valid option, display error.
				// Else add this value to the "page2Data" to be written.
				if ($sTempOptionValid == "false") {
					$sTempMessage .= "Field $sActualFieldName Is Not Valid...\r\n";
					$bPage2Error = true;
				} else {
					// store page2 data in a variable to store in rejection log file in case of rejection
					// UNKNOWN!
					$sLeadPage2Data .= "\r\n$sActualFieldName: ".${$sActualFieldName};
					$sPage2Data .= "\"".${$sActualFieldName}."\"|";
				}
			}
		}

		// Check page2 fields against the conditions in offerPage2Validation.
		$sCondition = '';
		$sValid = false;
		$sValidityQuery = "SELECT *
						   FROM	  offerPage2Validation	
						   WHERE  offerCode = '$sOfferCode'";
		$rValidityResult = dbQuery($sValidityQuery);
		// Loop through validations for this offer.
		// Use EVAL to create the IF statement, and if invalid, display the
		// associated error message.
		while ($oValidityRow = dbFetchObject($rValidityResult)) {
			$sCondition = $oValidityRow->cond;
			$sErrorMessage = $oValidityRow->errorMessage;
			if ($sCondition != '') {
				eval("if(\$sValid = $sCondition);");
				if ( $sValid) {
					$sPage2ErrorMessage .= "<li>$sErrorMessage...";
					$sTempMessage .= $sErrorMessage;
				}
			}
		}

		// If $sTempMessage is null, then there were no errors.
		// For protection, use AddSlashed on the page2Data.
		// Set the page2Data for this offer.
		// Add the offer code to session Offers Taken.
		// Remove from Page2Offers, add to stats.

	if ( !(validateEmailFormat($_SESSION['sSesEmail'])) ) {
		$bUserDataIsSet=false;
		$_SESSION['sSesEmail'] = '';
		$sMessage .= "<li>Please Enter A Valid Email Address...";
		$_SESSION["sSesEmailAsterisk"] = "<font color=red>*</font> ";
	}


	if ( !(validateName($_SESSION['sSesFirst']))) {
		$bUserDataIsSet=false;
		$_SESSION['sSesFirst'] = '';
		$sMessage .= "<li>Please Enter A Valid First Name...";
		$_SESSION["sSesFirstNameAsterisk"] = "<font color=red>*</font> ";
	}

	if ( !(validateName($_SESSION['sSesLast']))) {
		$bUserDataIsSet=false;
		$_SESSION['sSesLast'] = '';
		$sMessage .= "<li>Please Enter A Valid Last Name...";
		$_SESSION["sSesLastNameAsterisk"] = "<font color=red>*</font> ";
	}

	$sAoValidation = validateAddressAo( $_SESSION['sSesAddress'], $_SESSION['sSesAddress2'], $_SESSION["sSesCity"], $_SESSION["sSesState"], $_SESSION["sSesZip"], $sGblRoot );
	if( substr( $sAoValidation, 0, 7 ) == "Failure" ) {
		$aAoErrorLine = explode( "|", $sAoValidation );
		$sAoErrorCode = $aAoErrorLine[1];
		$sAoErrorText = $aAoErrorLine[2];
		switch ( $sAoErrorCode ) {
			case 'AM':
				$sMessage .= "<li>$sAoErrorText Contains Invalid Characters or Are Blank";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'R':
				$sMessage .= "<li>Please Enter a Valid Address for your Street";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'U':
				$sMessage .= "<li>Please Enter a Valid Street for your City, State, and ZipCode";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'X':
				$sMessage .= "<li>Please Enter a Deliverable Address";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'T':
				$sMessage .= "<li>Please Check the Format of Your Address";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'Z':
				$sMessage .= "<li>Please Enter a Valid Zip Code";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			case 'W':
				$sMessage .= "<li>Address Failure: Early Waring Address.  Please correct.";
				$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
				$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
				break;
			default:
				mail( "it@amperemedia.com", "AO Address Validation Hiccup", $sAoValidation );
				break;
		}
		$_SESSION["sSesAddress"]='';
		$_SESSION["sSesAddress2"]='';
		$_SESSION["sSesCity"]='';
		$_SESSION["sSesState"]='';
		$_SESSION["sSesZip"]='';
		$sAddress = '';
		$sAddress2 = '';
		$sCity = '';
		$sState = '';
		$sZip = '';
	} elseif ( substr( $sAoValidation, 0, 6 ) == "update" ) {
		// EX: update|address=3198  Darby St |address2= |city=Simi Valley|state=CA|zip=93063|oldaddress=3198 Darby St|oldaddress2=115|oldcity=Simi Valley|oldstate=CA|oldzip=93065|
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
	} else {
		$bIsPostalVerified = true;
		$_SESSION['sSesIsPostalVerified'] = true;		
	}
	
	$sCheckPhoneResult = dbQuery("SELECT * FROM phoneValidateBypass WHERE sourceCode = '".$_SESSION['sSesSourceCode']."'");
	if (dbNumRows($sCheckPhoneResult) == 0 ) {
		$iPhoneZipDistance = getDistance($_SESSION['sSesPhone'],$_SESSION['sSesZip']);
		if ( strlen($_SESSION['sSesPhone']) == 0 ) {
			$bPhoneIsSet=false;
			$sMessage .= "<li>Please Enter Primary Phone Number...";
			$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
			$_SESSION['sSesPhoneFalse'] = false;
		} else if (strlen($_SESSION['sSesPhone']) < 12 || strlen($_SESSION['sSesPhone']) > 12) {
			$bPhoneIsSet=false;
			$sMessage .= "<li>Please Enter Valid Phone Number...";
			$_SESSION['sSesPhone'] = '';
			$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
			$_SESSION['sSesPhoneFalse'] = false;
		} else if ( !(validatePhone(substr($_SESSION['sSesPhone'],0,3), substr($_SESSION['sSesPhone'],4,3), substr($_SESSION['sSesPhone'],8,4), '', $_SESSION['sSesState']))  ) {
			$bPhoneIsSet=false;
			$_SESSION['sSesPhone'] = '';
			$sMessage .= "<li>Phone Number You Entered Is Not A Valid Phone Number...";
			$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
			$_SESSION['sSesPhoneFalse'] = false;
		} else if (  $iPhoneZipDistance > 250) {
			exceedsMaxDistance($_SESSION['sSesPhone'], $_SESSION['sSesZip'], 250);
			$bPhoneIsSet=false;
			$sMessage .= "<li>Phone Number Is Not Valid For The Zipcode You Entered...";
			$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
			$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
			$_SESSION['sSesPhoneFalse'] = false;
		} else if ($_SESSION['sSesPhoneExt'] != '' && !(isInteger($_SESSION['sSesPhoneExt']))) {
			$bPhoneIsSet=false;
			$sMessage .= "<li>Phone Number Extension Is Not Valid...";
			$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
			$_SESSION['sSesPhoneFalse'] = false;
		}
	}

	if ($bPhoneIsSet == false || $_SESSION['sSesPhoneFalse'] == false) {
		$_SESSION['sSesPhone'] = '';
		$sPhone = '';
		$sPhoneDisable = '';
	}

	if (isBannedIp($sRemoteIp)) {
		$sMessage .= "<li>We Do Not Accept Registration From $sRemoteIp...<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
	} else if (isBannedDomain($_SESSION['sSesEmail'], $sDomainName)) {
		$sMessage .= "<li>We Do Not Accept Registration From ...$sDomainName<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
	}

		if ($sTempMessage == '' && $sMessage == '') {
			$sPage2Data = addslashes($sPage2Data);
			$_SESSION['aSesOfferPage2Data'][$sOfferCode] = $sPage2Data;
			array_push($_SESSION['aSesOffersTaken'], $sOfferCode);
			unset($_SESSION["aSesPage2Offers"][$i]);
			$_SESSION['sSesOfferTakenStatInfo'] .= $sOfferCode.",";
			$bInsert = true;
		}
	} else {
		if ($bPhoneIsSet == false || $_SESSION['sSesPhoneFalse'] == false) {
			if (!(ctype_digit($sPhone_areaCode) && ctype_digit($sPhone_exchange) && ctype_digit($sPhone_number))) {
				$sPhoneDisable = '';
			}
			$sPhoneDisable = '';
		}
	}
	break;
}


// If any "page2 fields" were left blank, display general error.
if ($bSkipped != "true") {
	if ($bPage2Error) {
		$sMessage .= "<li>Please Provide All The Required Data...";
	}
}
// If $sPage2ErrorMessage contains any text, append it to the standard error message.
if ($sPage2ErrorMessage != '') {
	$sMessage .= $sPage2ErrorMessage;
}
	if ($_SESSION["sSesTempCount"] != 0) {
		$sMessage .= ' ';	
	}
// Check if this partner's source code is marked for "no bounce checking".

$sNoBounceCheckQuery = "SELECT noBounceChecks
						  FROM   links
						  WHERE sourceCode = '" . $_SESSION["sSesSourceCode"] . "' AND noBounceChecks = 1;";
$rNoBounceCheckResult = dbQuery($sNoBounceCheckQuery);
$iNoBounceNumRows = dbNumRows($rNoBounceCheckResult);

if( $iNoBounceNumRows == 0 ) {
	//$sMessage .= $sUserDataMessage;
} else {
	$_SESSION["sSesAddressAsterisk"] = "";
	$_SESSION["sSesCityAsterisk"] = "";
	$_SESSION["sSesStateAsterisk"] = "";
	$_SESSION["sSesZipCodeAsterisk"] = "";
}

	while (list($i, $val) = each($_SESSION["aSesOffersTaken"])) {
		// Get the Page2Data for the taken offer.
		$sOfferCode = $_SESSION['aSesOffersTaken'][$i];
		$sPage2Data = $_SESSION['aSesOfferPage2Data'][$sOfferCode];

		$iDeliveryMethodId = '';
		$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
						   OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
					FROM   offers AS O, offerLeadSpec AS OL
					WHERE  O.offerCode = OL.offerCode
					AND	   O.offerCode = '$sOfferCode'";
		$rOfferResult = dbQuery($sOfferQuery);
		echo dbError();
		//echo $sOfferQuery.mysql_num_rows($rOfferResult);
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
			//echo "Ddf".$sSesMode.$oOfferRow->deliveryMethodId;
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

		if ($rOfferResult) {
			dbFreeResult($rOfferResult);
		}
		
		$sSessionId = session_id();
		// record offer taken entry

		if (($sOfferCode=='')&&($_SERVER['REMOTE_ADDR'] == '198.63.247.2')) {
			$sBlankOfferCodeBody="$PHP_SELF\n".$sEmail."\n\n".var_export($_SESSION,TRUE)."\n\n".var_export($_GET,TRUE)."\n\n".var_export($_POST,TRUE)."\n\n".var_export($GLOBALS,TRUE)."\n\n".var_export($_SERVER,TRUE)."\n\n".var_export($_ENV,TRUE)."\n\n".var_export($_REQUEST,TRUE)."\n\n";
			mail('it@amperemedia.com', 'Blank Offer Code', $sBlankOfferCodeBody);
		}

		if( $_SESSION['sSesIsPostalVerified'] ) {
			$sPostalVerified = 'V';
		} else {
			$sPostalVerified = 'N';
		}
		
		// If return false, then insert into otData.
		// User already took offer if return true.
		$bFoundDuplicateLead = checkForOtDataDups ($sEmail,$sOfferCode);
		/*
			CoRegPopUp offers, and CloseTheyHost offers normally use
			pixel firing to get their data into otData. Sometimes they
			don't, and our offer's isCloseTheyHostPixelEnable or
			isCoRegPopPixelEnable should tell us wether or not that's 
			the case. So, we should only insert here if this is a 
			non-pixel CoReg or ClosedTheyHost, or if this is a normal offer. 
		*/
		
		$sOfferIsCoRegQuery = "SELECT * FROM offers WHERE offerCode = '$sOfferCode' AND 
			((isCoRegPopUp = 'Y' and isCoRegPopPixelEnable != 'Y') OR 
			 (isCloseTheyHost = 'Y' and isCloseTheyHostPixelEnable != 'Y') OR 
			 (isCloseTheyHost = 'N' and isCoRegPopUp = 'N'))";
		$rOfferIsCoRegResult = dbQuery($sOfferIsCoRegQuery);
				
		if(dbNumRows($rOfferIsCoRegResult)){
			if ($bFoundDuplicateLead == false) {
				$sCurrentDateTime = date('Y-m-d H:i:s');
				$sLeadInsertQuery = "INSERT INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode, postalVerified, sessionId )
							 VALUES(\"$sEmail\", \"$sOfferCode\", \"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\",  \"".$_SESSION["sSesSubSourceCode"].
				"\", \"".$_SESSION["iSesPageId"]."\", '$sCurrentDateTime', '$sRemoteIp', '$sServerIp', \"$sPage2Data\", '".$_SESSION["sSesPageMode"]."', '".$sPostalVerified."', \"$sSessionId\")";
				$rLeadInsertResult = dbQuery($sLeadInsertQuery);
				if (!($rLeadInsertResult)) {
					$sEmailMessage = "Insert into otData query failed.  Please run below insert query manually\n\n$sLeadInsertQuery";
					mail('it@amperemedia.com',"Insert otData Failed - newOtPage2SubmitC.php", "$sEmailMessage");
				}
			}
		}

		
		// Create comma separated offerCode list for offers that are taken today
		$sCurrentCookieOfferCode .= $sOfferCode.",";		
	
		$sCheckQuery = "SELECT id
			   FROM   otData
			   WHERE  email = \"$sEmail\"
			   AND offerCode = \"$sOfferCode\""; 
		$rCheckResult = dbQuery($sCheckQuery);
		$sRow = dbFetchObject($rCheckResult);
		$iOtDataId = $sRow->id;

		// send offer auto email if set to do so
		if ($iOfferAutoEmail) {
			$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $_SESSION["sSesEmail"], $sOfferAutoEmailBody);
			$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
			$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
			if ($sOfferAutoEmailFormat == 'html') {
				$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
			}
			mail($_SESSION["sSesEmail"], $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
		}

		// send ot page auto email if set to do so and it is not already sent for this page
		if ($_SESSION["iSesPageAutoRespSent"] == '' && ($iPageAutoEmail || $_SESSION['iSesCbId'])) {
			if ($_SESSION['iSesPageAutoRespId']) {
				$iTempAutoRespId = $_SESSION['iSesPageAutoRespId'];
			} else {
				$iTempAutoRespId = $iDefaultAutoRespId;
			}
			if ($_SESSION['iSesCbId']) {
				// get auto responder details from cobrand table if page is cobrand template
				// and cbId is passed
				$sAutoRespQuery = "SELECT *
									   FROM   coBrandDetails
									   WHERE  id = '".$_SESSION['iSesCbId']."'";
				$rAutoRespResult = dbQuery($sAutoRespQuery);
				echo dbError();
				while ($oAutoRespRow = dbFetchObject($rAutoRespResult)) {
					$iPageAutoEmail = $oAutoRespRow->autoEmail;
					$sPageAutoEmailText = $oAutoRespRow->autoEmailText;
					$sPageAutoEmailFromAddr = $oAutoRespRow->autoEmailFrom;
					$sPageAutoEmailFormat = $oAutoRespRow->autoEmailFormat;
					$sPageAutoEmailSub = $oAutoRespRow->autoEmailSub;
					$sPageAutoEmailReplyTo = $oAutoRespRow->autoEmailReplyTo;
				}
			} else {
				$sAutoRespQuery = "SELECT *
									   FROM   pageAutoResponders
									   WHERE  id = '$iTempAutoRespId'";
				$rAutoRespResult = dbQuery($sAutoRespQuery);
				echo dbError();
				while ($oAutoRespRow = dbFetchObject($rAutoRespResult)) {
					$sPageAutoEmailText = $oAutoRespRow->emailText;
					$sPageAutoEmailFromAddr = $oAutoRespRow->emailFromAddr;
					$sPageAutoEmailFormat = $oAutoRespRow->emailFormat;
					$sPageAutoEmailSub = $oAutoRespRow->emailSub;
					$sPageAutoEmailReplyTo = $oAutoRespRow->replyTo;
				}
			}

			// check again if auto resp to be sent, after getting value for cobrand
			if ($iPageAutoEmail) {
				$sPageAutoEmailText = eregi_replace("\[EMAIL\]", $_SESSION["sSesEmail"], $sPageAutoEmailText);
				$sPageAutoEmailText = eregi_replace("\[SOURCE_CODE\]", $_SESSION["sSesSourceCode"], $sPageAutoEmailText);
				$sPageAutoEmailText = eregi_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sPageAutoEmailText);

				$sPageAutoEmailHeaders = "From: $sPageAutoEmailFromAddr\r\n";
				$sPageAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				$sPageAutoEmailHeaders .= "Reply-To: $sPageAutoEmailReplyTo\r\n";
				if ($sPageAutoEmailFormat == 'html') {
					$sPageAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				}

				mail($_SESSION["sSesEmail"], $sPageAutoEmailSub, $sPageAutoEmailText, $sPageAutoEmailHeaders);

				$_SESSION["iSesPageAutoRespSent"] = 1;
			}
		}

		// offer taken stats
		$sRealTimeResponse = "";
		if ($bFoundDuplicateLead == false && $bIsPostalVerified && !(strtolower(substr($_SESSION["sSesAddress"],0,11) ) == '3401 dundee' && $_SESSION["sSesZip"] == '60062') ) {
			if ( ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) ) {
				// 2 = real time form post - GET
				// 3 = real time form post - POST
				$aUrlArray = explode("//", $sPostingUrl);
				$sUrlPart = $aUrlArray[1];
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
				$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode($sRemoteIp), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode($sPhone_areaCode), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode($sPhone_exchange), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_number\]", urlencode($sPhone_number), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[dd\]", urlencode($iCurrDay), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[hh\]", urlencode($iCurrHH), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[ii\]", urlencode($iCurrMM), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[ss\]", urlencode($iCurrSS), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);

				// get all the page2 fields of this offer and replace
				$sPage2MapQuery = "SELECT *
								   FROM   page2Map
				 	 			   WHERE offerCode = '$sOfferCode'
				 				   ORDER BY storageOrder ";
				$rPage2MapResult = dbQuery($sPage2MapQuery);
				$f = 1;
				while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
					$sActualFieldName = $oPage2MapRow->actualFieldName;
					$sFieldVar = "FIELD".$f;
					$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($_SESSION['page2'][$sActualFieldName]), $sHttpPostString);
					$f++;
				}
				
				if ($rPage2MapResult) {
					dbFreeResult($rPage2MapResult);
				}

				// separate host part and script path
				$sResult = httpFormPostGet ($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sOfferCode,$sEmail,$sHowSent);

			} else if ($iDeliveryMethodId == 4) {
				// send lead email if lead delivery method set as real time email
				// only if mode is active
				$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
				$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				$sSingleEmailSubject = ereg_replace("\[offerCode\]",$aOffersChecked[$i], $sSingleEmailSubject);
				if (strstr($sSingleEmailSubject,"[d-")) {
					//get date arithmetic number
					$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					$sSingleEmailSubject = ereg_replace("\[dd\]", $sNewDD, $sSingleEmailSubject);
					$sSingleEmailSubject = ereg_replace("\[mm\]", $sNewMM, $sSingleEmailSubject);
					$sSingleEmailSubject = ereg_replace("\[yyyy\]", $sNewYY, $sSingleEmailSubject);
					$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
					$sSingleEmailSubject = str_replace($sDateArithString, "", $sSingleEmailSubject);
				} else {
					$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
					$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
					$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
				}

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
				$sSingleEmailBody = ereg_replace("\[ipAddress\]",$sRemoteIp, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[dd\]", urlencode($iCurrDay), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[hh\]", urlencode($iCurrHH), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[ii\]", urlencode($iCurrMM), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[ss\]", urlencode($iCurrSS), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sSingleEmailBody);

				// get all the page2 fields of this offer and replace
				$sPage2MapQuery = "SELECT *
								   FROM   page2Map
				 	 			   WHERE offerCode = '$sOfferCode'
				 				   ORDER BY storageOrder ";

				$rPage2MapResult = dbQuery($sPage2MapQuery);
				$f = 1;
				while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
					$sActualFieldName = $oPage2MapRow->actualFieldName;
					$sFieldVar = "FIELD".$f;
					$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",$_SESSION['page2'][$sActualFieldName], $sSingleEmailBody);
					$f++;
				}

				if ($rPage2MapResult) {
					dbFreeResult($rPage2MapResult);
				}

				$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
				$sSingleEmailBody = "";

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
									 	  WHERE   email='$sEmail' and offerCode='$sOfferCode'";
				$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
			}
		}
		$bInsert = true;
	}
	
	
	// strip extra comma from end.
	$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
	// Get list of all offers that were taken in the past and add current offerTaken to the list.
	if($_SESSION['sSesOfferTakenInCookie'] != '') {
		$sCurrentCookieOfferCode .= ",".$_SESSION['sSesOfferTakenInCookie'];
	}
	// expires in 180 days - 15552000 seconds	- Add/Update cookie.
	//setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", ".popularliving.com", 0);
	setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '', 0);
	
	if ($bInsert == true) {
		$sSalutation = $_SESSION["sSesSalutation"];
		$sFirst =  $_SESSION["sSesFirst"];
		$sLast = $_SESSION["sSesLast"];
		$sEmail = $_SESSION["sSesEmail"];
		$sAddress = $_SESSION["sSesAddress"];
		$sAddress2 = $_SESSION["sSesAddress2"];
		$sCity = $_SESSION["sSesCity"];
		$sState = $_SESSION["sSesState"];
		$sZip = $_SESSION["sSesZip"];
		$sPhone = $_SESSION["sSesPhone"];
		$sPhone_ext = $_SESSION["sSesPhoneExt"];
		$iJoinListId = $_SESSION["iSesJoinListId"];

		if (count($_SESSION['aSesOffersTaken']) > 0) {
			// Check whether this email address already exists in the userData (current) table.
			$sActiveCheckQuery = "SELECT * FROM   userData WHERE  email = '$sEmail'";
			$rActiveCheckResult = dbQuery($sActiveCheckQuery);
	
			if( $_SESSION['sSesIsPostalVerified'] ) {
				$sPostalVerified = 'V';
			} else {
				$sPostalVerified = 'N';
			}
			
			
			$sSalutation = makeQuerySafeToRun($sSalutation);
			$sAddress = makeQuerySafeToRun($sAddress);
			$sAddress2 = makeQuerySafeToRun($sAddress2);
			$sCity = makeQuerySafeToRun($sCity);
			$sState = makeQuerySafeToRun($sState);
			$sZip = makeQuerySafeToRun($sZip);
			
			
			$sSessionId = session_id();
			// If user does not exist, create entry is userData for this user.
			if ( dbNumRows($rActiveCheckResult) == 0 ) {
				$sCurrentDateTime = date('Y-m-d H:i:s');
				$sInsertQuery = "INSERT INTO userData(email, salutation, first, last, address,
										 address2, city, state, zip, phoneNo, extension, dateTimeAdded, postalVerified, sessionId, remoteIp)
							 VALUES(\"$sEmail\", \"$sSalutation\", \"$sFirst\", \"$sLast\", \"$sAddress\", 
									\"$sAddress2\", \"$sCity\", \"$sState\", \"$sZip\", \"$sPhone\", \"$sPhone_ext\", '$sCurrentDateTime', \"$sPostalVerified\", \"$sSessionId\", \"$sRemoteIp\")";
				$rInsertResult = dbQuery($sInsertQuery);
				if (!($rInsertResult)) {
					$sEmailMessage = "Insert into userData query failed.\n\n$sInsertQuery";
					mail('it@amperemedia.com',"Insert userData Failed - newOtPage2SubmitC.php", "$sEmailMessage");
				}
				echo dbError();
			} else {
				if( $bIsPostalVerified ) {
					$sUpdateQuery = "UPDATE userData SET salutation=\"$sSalutation\",
									first=\"$sFirst\", last=\"$sLast\", address=\"$sAddress\", 
									address2=\"$sAddress2\", city=\"$sCity\", state=\"$sState\",
									zip=\"$sZip\", phoneNo=\"$sPhone\", postalVerified=\"$sPostalVerified\", 
									sessionId=\"$sSessionId\", remoteIp=\"$sRemoteIp\"
									WHERE email=\"$sEmail\"";
					$rUpdateResult = dbQuery( $sUpdateQuery );
					echo dbError();
				}
			}
		}
	}
	/**************** end of offers taken while loop  *********************/

// At this point, the data all appears to be valid in the Session.  Even when done in the "wrong" order.

// If no errors were produced in:
//		Any Page2 Fields left blank
//		Any Other Page2 Field Errors (Options or Validation)
//		Any User Data Errors, IF!!!
if ($sMessage == '') {
	$sSalutation = $_SESSION["sSesSalutation"];
	$sFirst =  $_SESSION["sSesFirst"];
	$sLast = $_SESSION["sSesLast"];
	$sEmail = $_SESSION["sSesEmail"];
	$sAddress = $_SESSION["sSesAddress"];
	$sAddress2 = $_SESSION["sSesAddress2"];
	$sCity = $_SESSION["sSesCity"];
	$sState = $_SESSION["sSesState"];
	$sZip = $_SESSION["sSesZip"];
	$sPhone = $_SESSION["sSesPhone"];
	$sPhone_ext = $_SESSION["sSesPhoneExt"];
	$iJoinListId = $_SESSION["iSesJoinListId"];
	// If user opted to join the newsletter, and is not a banned domain, handle this request.
	// Banned Domains:
	//		aol.com
	//		aol.net
	//		rr.com
	//		rr.net
	//		mail.com
if (!(ctype_digit($iJoinListId))) { $iJoinListId = ''; }
if( !$bGlobalJoinInsertDisable ) {
	//if ( $iJoinListId && !( substr($sEmail,-7) == 'aol.com' || substr($sEmail,-7) == 'aol.net' || substr($sEmail,-6) == 'rr.com' || substr($sEmail,-6) == 'rr.net' || substr($sEmail,-9) == '@mail.com')) {
	if ( $iJoinListId && !( substr($sEmail,-6) == 'rr.com' || substr($sEmail,-6) == 'rr.net' || substr($sEmail,-9) == '@mail.com')) {
		// Add to subscription, confirmation, activeList.
		// Remove from inactiveList and pending.
		$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
						VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', '$sRemoteIp', NOW() )";
		$rSubInsertResult = dbQuery($sSubInsertQuery);
		$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
						 VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', '$sRemoteIp', NOW() )";
		$rConfInsertResult = dbQuery($sConfInsertQuery);
		$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
					   VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', NOW() )";
		$rActiveInsertResult = dbQuery($sActiveInsertQuery);
		$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
							 WHERE  email = '$sEmail'
							 AND    joinListId = '$iJoinListId'";	
		$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
		$sPendingDeleteQuery = "DELETE FROM joinEmailPending
							WHERE  email = '$sEmail'
						AND    joinListId = '$iJoinListId'";	
		$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);

		// Determine whether to send Confirmation email or not.
		$sCheckQuery ="SELECT *
				   FROM   joinEmailConfirm
				   WHERE  email = '$sEmail'
				   AND    joinListId = '$iJoinListId'";
		$rCheckResult = dbQuery($sCheckQuery);
		while ($oCheckRow = dbFetchObject($rCheckResult)) {
			$sDateTimeSubscribed = $oCheckRow->dateTimeAdded;
		}

		if (dbNumRows($rCheckResult) > 0) {
			$sListEmailQuery = "SELECT *
						FROM   joinListEmailContents
						WHERE  joinListId = '$iJoinListId'
						AND	   emailPurpose = 'welcome'";
			$rListEmailResult = dbQuery($sListEmailQuery);
			while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
				$sWelcomeEmailContent = $oListEmailRow->emailBody;
				$sWelcomeEmailSubject = $oListEmailRow->emailSub;
				$sWelcomeEmailFromAddr = $oListEmailRow->emailFrom;
				$sWelcomeEmailFormat = $oListEmailRow->emailFormat;

				$sWelcomeEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[DATE_TIME_SUB\]", $sDateTimeSubscribed, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[SOURCE_CODE\]", $_SESSION['sSesSourceCode'], $sWelcomeEmailContent);
				$sWelcomeEmailHeaders = "From: $sWelcomeEmailFromAddr\r\n";
				$sWelcomeEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				if ($sWelcomeEmailFormat == 'html') {
					$sWelcomeEmailContent = nl2br($sWelcomeEmailContent);
					$sWelcomeEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				} else {
					$sWelcomeEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
				}

				mail($sEmail, $sWelcomeEmailSubject, $sWelcomeEmailContent, $sWelcomeEmailHeaders);

				$sUpdateQuery = "UPDATE joinEmailConfirm
						 SET    receivedWelcomeEmail = '1'
						 WHERE  email = '$sEmail'
						 AND    joinListId = '$iJoinListId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
			}
		}
	}
}

	/***************** make entries into offers states tables  ******************/
	if ($_SESSION["sSesPageMode"] == 'A' ) {
		$sOfferTakenStatInfo = $_SESSION['sSesOfferTakenStatInfo'];
		$sOfferAbortStatInfo = $_SESSION['sSesOfferAbortStatInfo'];
		// offer taken count
		if ($sOfferTakenStatInfo != '') {
			$sOfferTakenStatInfo = substr($sOfferTakenStatInfo, 0, strlen($sOfferTakenStatInfo)-1);
			$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('".$_SESSION["iSesPageId"]."', '$sOfferTakenStatInfo', '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
		}
		// offer aborted count
		if ($sOfferAbortStatInfo != '') {
			$sOfferAbortStatInfo = substr($sOfferAbortStatInfo, 0, strlen($sOfferAbortStatInfo)-1);
			$sStatQuery = "INSERT INTO tempOfferAbortStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('".$_SESSION["iSesPageId"]."', '$sOfferAbortStatInfo', '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
			echo dbError();

			// Insert dropped offers entry into abandedOffers.
			$sTempOfferCode = explode(",", $sOfferAbortStatInfo);
			$sCountTempOfferCode = count($sTempOfferCode);	
			for ($iCount = 0; $iCount<$sCountTempOfferCode; $iCount++) {
				$sTemp = $sTempOfferCode[$iCount];
				$sTempEmail = $_SESSION["sSesEmail"];
				$sCheckTemp = "SELECT * FROM abandedOffers
							WHERE offerCode = '$sTemp'
							AND email = '$sTempEmail'
							AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
				$rCheckTempResult = dbQuery($sCheckTemp);
				if ( dbNumRows($rCheckTempResult) == 0 ) {
					$sInsertAbandedOffersQuery = "INSERT INTO abandedOffers(email, dateTimeAdded, remoteIp, sourceCode, offerCode, sessionId)
			                       VALUES('".$_SESSION["sSesEmail"]."', NOW(), '$sRemoteIp', \"".$_SESSION["sSesSourceCode"]."\", \"".$sTempOfferCode[$iCount]."\", \"$sTempSessionId\")";
					$rInsertAbandedOffersResult = dbQuery($sInsertAbandedOffersQuery);
					echo dbError();
				}
			}
		}
	}
	/**************************************************************/
	if ($_SESSION["sSesPhone"] == '----' || $_SESSION["sSesPhone"] == '--') { $_SESSION["sSesPhone"] = ''; }
	/*************** attach sessionid if set to pass in page properties  ***********/
	if ( $_SESSION["sSesGoTo"] != '' ) { $_SESSION["sSesGoTo"] = urldecode($_SESSION["sSesGoTo"]); }
	/*************** attach phpsessid *****************/
	if (!strstr($sRedirectPopUrl,"http://")) {
		if ($iPassOnPhpsessid) {
			if (strstr($sRedirectPopUrl,"?")) {
				$sRedirectPopUrl .= "&PHPSESSID=".session_id();
			} else {
				$sRedirectPopUrl .= "?PHPSESSID=".session_id();
			}
			$sRedirectPopUrl .= "&sSiteId=".$_SESSION['sSiteId'];
			if ( $_SESSION["sSesGoTo"] != '' ) {
				if (strstr($_SESSION["sSesGoTo"],"?")) {
					$_SESSION["sSesGoTo"] .= "&PHPSESSID=".session_id();
				} else {
					$_SESSION["sSesGoTo"] .= "?PHPSESSID=".session_id();
				}
				$_SESSION["sSesGoTo"] .= "&sSiteId=".$_SESSION['sSiteId'];
			}
		}
	}

	/*************** attach phpsessid *****************/
	if ($iPassOnPhpsessid) {
		if (strstr($sRedirectTo,"?")) {
			$sRedirectTo .= "&PHPSESSID=".session_id();
			$sRedirectToNotOfferTaken .= "&PHPSESSID=".session_id();
		} else {
			$sRedirectTo .= "?PHPSESSID=".session_id();
			$sRedirectToNotOfferTaken .= "?PHPSESSID=".session_id();
		}
		$sRedirectTo .= "&sSiteId=".$_SESSION['sSiteId'];
		$sRedirectToNotOfferTaken .= "&sSiteId=".$_SESSION['sSiteId'];
		if ( $_SESSION["sSesGoTo"] != '' ) {
			if (strstr($_SESSION["sSesGoTo"],"?")) {
				$_SESSION["sSesGoTo"] .= "&PHPSESSID=".session_id();
			} else {
				$_SESSION["sSesGoTo"] .= "?PHPSESSID=".session_id();
			}
			$_SESSION["sSesGoTo"] .= "&sSiteId=".$_SESSION['sSiteId'];
		}
	}
	/************************  End attaching phpsessid  ************************/
	/*************** attached outbound querystring if set in page property  ***********/
	if ($iPassOnInboundQueryString) {
		if (strstr($sRedirectTo,"?")) {
			$sRedirectTo .= "&".$sOutboundQueryString;
			$sRedirectToNotOfferTaken .= "&".$sOutboundQueryString;
		} else {
			$sRedirectTo .= "?".$sOutboundQueryString;
			$sRedirectToNotOfferTaken .= "?".$sOutboundQueryString;
		}
		if ( $_SESSION["sSesGoTo"] != '' ) {
			if (strstr($_SESSION["sSesGoTo"],"?")) {
				$_SESSION["sSesGoTo"] .= "&".$sOutboundQueryString;
			} else {
				$_SESSION["sSesGoTo"] .= "?".$sOutboundQueryString;
			}
		}
	}
	/**************************************************************/
	/********************* Prepare prepop codes if prepop codes to send with redirecting to next page *********************/
	if ($iPassOnPrepopCodes) {
		if ($sOutboundVarMap != '') {
			$aOutboundVarMapArray = explode(",",$sOutboundVarMap);
			$bEmailFound = false;
			$bFirstFound = false;
			$bLastFound = false;
			$bAddressFound = false;
			$bAddress2Found = false;
			$bCityFound = false;
			$bStateFound = false;
			$bZipFound = false;
			$bPhoneFound = false;
			$bPhoneNoDashFound = false;
			$bPhoneExtFound = false;
			$bSrcFound = false;
			$bSSFound = false;
			$bTitleFound = false;
			$bIpFound = false;
			
			if ($_SESSION["sSesPhone"] == '----' || $_SESSION["sSesPhone"] == '--') { $_SESSION["sSesPhone"] = ''; }
			for ($i=0; $i<count($aOutboundVarMapArray); $i++) {
				$aInboundOutboundPair = explode("=",$aOutboundVarMapArray[$i]);
				switch ($aInboundOutboundPair[0]) {
					case "e":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesEmail"]."&";
					$bEmailFound = true;
					break;
					case "f":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesFirst"]."&";
					$bFirstFound = true;
					break;
					case "l":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesLast"]."&";
					$bLastFound = true;
					break;
					case "a1":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesAddress"]."&";
					$bAddressFound = true;
					break;
					case "a2":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesAddress2"]."&";
					$bAddress2Found = true;
					break;
					case "c":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesCity"]."&";
					$bCityFound = true;
					break;
					case "s":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesState"]."&";
					$bStateFound = true;
					break;
					case "ss":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesSubSourceCode"]."&";
					$bSSFound = true;
					break;
					case "z":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesZip"]."&";
					$bZipFound = true;
					break;
					case "p":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesPhone"]."&";
					$bPhoneFound = true;
					break;
					case "pnd":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesPhoneNoDash"]."&";
					$bPhoneNoDashFound = true;
					break;
					case "ext":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesPhoneExt"]."&";
					$bPhoneExtFound = true;
					break;
					case "src":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesSourceCode"]."&";
					$bSrcFound = true;
					break;
					case "t":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesSalutation"]."&";
					$bTitleFound = true;
					break;
					case "ip":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$sRemoteIp."&";
					$bIpFound = true;
					break;
				}
			}
			
			if ($_SESSION["sSesPhone"] == '----' || $_SESSION["sSesPhone"] == '--') {
				$_SESSION["sSesPhone"] = '';
			}
			
			if(!$bEmailFound) {
				$sPrepopcodes .= "e=".$_SESSION["sSesEmail"]."&";
			}
			if(!$bFirstFound) {
				$sPrepopcodes .= "f=".$_SESSION["sSesFirst"]."&";
			}
			if(!$bAddressFound) {
				$sPrepopcodes .= "a1=".$_SESSION["sSesAddress"]."&";
			}
			if(!$bAddress2Found) {
				$sPrepopcodes .= "a2=".$_SESSION["sSesAddress2"]."&";
			}
			if(!$bCityFound) {
				$sPrepopcodes .= "c=".$_SESSION["sSesCity"]."&";
			}
			if(!$bStateFound) {
				$sPrepopcodes .= "s=".$_SESSION["sSesState"]."&";
			}
			if(!$bZipFound) {
				$sPrepopcodes .= "z=".$_SESSION["sSesZip"]."&";
			}
			if(!$bPhoneFound) {
				$sPrepopcodes .= "p=".$_SESSION["sSesPhone"]."&";
			}
			if(!$bPhoneNoDashFound) {
				$sPrepopcodes .= "pnd=".$_SESSION["sSesPhoneNoDash"]."&";
			}
			if(!$bPhoneExtFound) {
				$sPrepopcodes .= "ext=".$_SESSION["sSesPhoneExt"]."&";
			}
			if(!$bSrcFound) {
				$sPrepopcodes .= "src=".$_SESSION["sSesSourceCode"]."&";
			}
			if(!$bSSFound) {
				$sPrepopcodes .= "ss=".$_SESSION["sSesSubSourceCode"]."&";
			}
			if(!$bTitleFound) {
				$sPrepopcodes .= "t=".$_SESSION["sSesSalutation"]."&";
			}
			if(!$bIpFound) {
				$sPrepopcodes .= "ip=".$sRemoteIp."&";
			}
			if ($sPrepopcodes != '') {
				$sPrepopcodes = substr($sPrepopcodes,0, strlen($sPrepopcodes)-1);
			}
			// Close They Host pass on prepop
			$sCloseTheyHostQueryString = $sPrepopcodes;
			if (strstr($sRedirectTo,"?")) {
				$sRedirectTo .= "&".$sPrepopcodes;
			} else {
				$sRedirectTo .= "?".$sPrepopcodes;
			}
			if (strstr($sRedirectToNotOfferTaken,"?")) {
				$sRedirectToNotOfferTaken .= "&".$sPrepopcodes;
			} else {
				$sRedirectToNotOfferTaken .= "?".$sPrepopcodes;
			}
			if ( $_SESSION["sSesGoTo"] != '' ) {
				if (strstr($_SESSION["sSesGoTo"],"?")) {
					$_SESSION["sSesGoTo"] .= "&".$sPrepopcodes;
				} else {
					$_SESSION["sSesGoTo"] .= "?".$sPrepopcodes;
				}
			}
		} else {
			if ($_SESSION["sSesPhone"] == '----' || $_SESSION["sSesPhone"] == '--') {
				$_SESSION["sSesPhone"] = '';
			}

			$sQueryStringToPass = "e=".$_SESSION["sSesEmail"]."&f=".$_SESSION["sSesFirst"]."&l=".$_SESSION["sSesLast"]."&a1=".
			$_SESSION["sSesAddress"]."&a2=".$_SESSION["sSesAddress2"]."&c=".$_SESSION["sSesCity"]."&s=".$_SESSION["sSesState"].
			"&z=".$_SESSION["sSesZip"]."&p=".$_SESSION["sSesPhone"]."&ext=".$_SESSION["sSesPhoneExt"]."&src=".$_SESSION["sSesSourceCode"].
			"&ss=".$_SESSION["sSesSubSourceCode"]."&t=".$_SESSION["sSesSalutation"]."&ip=".$sRemoteIp;

			// Close They Host pass on prepop
			$sCloseTheyHostQueryString = $sQueryStringToPass;
			
			if (strstr($sRedirectTo,"?")) {
				$sRedirectTo .= "&".$sQueryStringToPass;
			} else {
				$sRedirectTo .= "?".$sQueryStringToPass;
			}

			if (strstr($sRedirectToNotOfferTaken,"?")) {
				$sRedirectToNotOfferTaken .= "&".$sQueryStringToPass;
			} else {
				$sRedirectToNotOfferTaken .= "?".$sQueryStringToPass;
			}

			if ( $_SESSION["sSesGoTo"] != '' ) {
				if (strstr($_SESSION["sSesGoTo"],"?")) {
					$_SESSION["sSesGoTo"] .= "&".$sQueryStringToPass;
				} else {
					$_SESSION["sSesGoTo"] .= "?".$sQueryStringToPass;
				}
			}
		}
	}
	/******************************* end of attaching prepop codes to redirect url **********************/
	/*************************** redirect to next page ********************************/
	if ($iEnableGoTo && $_SESSION["sSesGoTo"] != '') {
		if ($iIsGoToPopUp) {
			echo "<script language=JavaScript>
							window.open(\"".$_SESSION["sSesGoTo"]."\",\"newWin\",\"scrollbars=yes, resizable=yes, status=yes\");
							window.location.replace('".$sRedirectTo."');
							</script>";
		} else {
			$rPixelFoundResult = dbQuery("SELECT * FROM pixels WHERE sourceCode='".$_SESSION["sSesSourceCode"]."'");
			$_SESSION['bSesPixelFound'] = false;
			$_SESSION['sSesPixelHtml'] = '';
			$_SESSION['sFirePixelAndRedirectUrl'] = '';
			$digits = array('0','1','2','3','4','5','6','7','8','9');
			while ($oPixelRow = dbFetchObject($rPixelFoundResult)) {
				$_SESSION['bSesPixelFound'] = true;
				$_SESSION['sSesPixelHtml'] .= $oPixelRow->pixelHtml;
				$iTempPageId = $oPixelRow->pageId;
					
				$iRandomNum = array_rand($digits,6);
				$_SESSION['sSesPixelHtml'] = str_replace("[ss]", $_SESSION["sSesSubSourceCode"], $_SESSION['sSesPixelHtml']);
				$_SESSION['sSesPixelHtml'] = str_replace("[yyyy]", date('Y'), $_SESSION['sSesPixelHtml']);
				$_SESSION['sSesPixelHtml'] = str_replace("[mm]", date('m'), $_SESSION['sSesPixelHtml']);
				$_SESSION['sSesPixelHtml'] = str_replace("[dd]", date('d'), $_SESSION['sSesPixelHtml']);
				$_SESSION['sSesPixelHtml'] = str_replace("[6_DIGIT_RAND_NUM]", $iRandomNum, $_SESSION['sSesPixelHtml']);
				$_SESSION['sSesPixelHtml'] = str_replace("[email]", $_SESSION["sSesEmail"], $_SESSION['sSesPixelHtml']);
			}
			//<!-- [FIRE_PIXEL_FOR_EACH_OFFER_TAKEN] -->
			if (strstr($_SESSION['sSesPixelHtml'],'[FIRE_PIXEL_FOR_EACH_OFFER_TAKEN]')) {
				$sTempContent = $_SESSION['sSesPixelHtml'];
				$_SESSION['sSesPixelHtml'] = '';
				foreach ($_SESSION['aSesOffersTaken'] as $asdf) {
					$sTemp1 = str_replace("[OFFER_CODE]", $asdf, $sTempContent);
					$_SESSION['sSesPixelHtml'] .= $sTemp1;
				}
			} else {
				$_SESSION['sSesPixelHtml'] = str_replace("[OFFER_CODE]", $_SESSION['aSesOffersTaken'][0], $_SESSION['sSesPixelHtml']);
			}

			if ($_SESSION['bSesPixelFound'] == true && $_SESSION['sSesPixelHtml'] != '' && $iTempPageId == '9454') {
				$_SESSION['sFirePixelAndRedirectUrl'] = $_SESSION["sSesGoTo"];
				header("Location:/p/firePixelAndRedirect.php?PHPSESSID=".session_id());
				exit;
			} else {
				header("Location:".$_SESSION["sSesGoTo"]);
			}
		}
	} else {
		
		// This is for close they host offers - start
		$sTheyHostContinueURL = "http://".$_SERVER['SERVER_NAME']."/closeTheyHost/closeTheyHost.php";
		if ($iPassOnPrepopCodes) {
			if (strstr($sTheyHostContinueURL,"?")) {
				$sTheyHostContinueURL .= "&".$sCloseTheyHostQueryString;
			} else {
				$sTheyHostContinueURL .= "?".$sCloseTheyHostQueryString;
			}
		} else {
			$sTheyHostContinueURL = "http://".$_SERVER['SERVER_NAME']."/closeTheyHost/closeTheyHost.php?PHPSESSID=".session_id();
		}
		// This is for close they host offers - end
		if (count($_SESSION["aSesOffersTaken"])>0) {
			if ($sRedirectPopOption != '') {
				$sPopUpContent =  "<script language=JavaScript>";
				$sPopUpContent .= "var popupWindow = window.open('".$sRedirectPopUrl."','','width=400,height=600, scrollbars=yes, resizable=yes');";
				$sPopUpContent .= "window.location.href = '".$sRedirectTo."'; ";

				if ($sRedirectPopOption == 'popUp') {
					$popUpContent .= "popupWindow.focus();";
				} else {
					$popUpContent .= "popupWindow.blur();";
				}

				$sPopUpContent .=  "</script>";
				echo $sPopUpContent;
			} else {
				if (count($_SESSION['aSesCloseTheyHostOffersChecked']) == 0 ) {
					if ($_SESSION["bSesRedirectToSsl"]) {
						if (!strstr($sRedirectTo,"http://")) {
							$sRedirectTo = "http://".$_SERVER['SERVER_NAME']."$sRedirectTo";
						}
					}
					header("Location:$sRedirectTo");
				} else {
					// Redirect to closeTheyHost pages.
					$_SESSION["sSesCloseTheyHostNextUrl"] = $sRedirectTo;
					header("Location:$sTheyHostContinueURL");
				}
			}
		} else {
			if ($sRedirectNotOfferTakenPopOption != '') {
				$sPopUpContent =  "<script language=JavaScript>";
				$sPopUpContent .= "var popupWindow = window.open('".$sRedirectNotOfferTakenPopUrl."','','width=400,height=600, scrollbars=yes, resizable=yes');";
				$sPopUpContent .= "window.location.href = '".$sRedirectToNotOfferTaken."'; ";

				if ($sRedirectNotOfferTakenPopOption == 'popUp') {
					$popUpContent .= "popupWindow.focus();";
				} else {
					$popUpContent .= "popupWindow.blur();";
				}

				$sPopUpContent .=  "</script>";
				echo $sPopUpContent;
			} else {
				if (count($_SESSION['aSesCloseTheyHostOffersChecked']) == 0 ) {
					if ($_SESSION["bSesRedirectToSsl"]) {
						if (!strstr($sRedirectToNotOfferTaken,"http://")) {
							$sRedirectToNotOfferTaken = "http://".$_SERVER['SERVER_NAME']."$sRedirectToNotOfferTaken";
						}
					}
					header("Location:$sRedirectToNotOfferTaken");
				} else {
					// Redirect to closeTheyHost pages.
					$_SESSION["sSesCloseTheyHostNextUrl"] = $sRedirectToNotOfferTaken;
					header("Location:$sTheyHostContinueURL");
				}
			}
		}
	}
	/****************** end of redirecting to next page ***********************/
} else {
	if ($bSkipped == 'true') {
		$sMessage='';
	}
	/********************* Send back to previous page if message is not blank *****************/
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=$sMessage&$sOutboundQueryString&PHPSESSID=".session_id());
	} else {
		header("Location:$sRefererScriptFileName?sMessage=$sMessage&$sOutboundQueryString&PHPSESSID=".session_id());
	}
	/***************************************************************/
}

?>


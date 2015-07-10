<?php

/*********

Script to Handle Ot Page2 Submission.
Script is Called when User submits the ot page 2

**********/

/************* initialize script variables ***************/


/*****************************************************/

include("includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");

// start the session before including otPageInclude.php
session_start();

// set error level to supress warnings, display all errors  except warnings
// used Particularly to suppress the "Fatal Error" warning from secure site when getting its response through fgets()

error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);


// get current date and time to replace in post string if have to
$iCurrYear = date('Y');
$iCurrYearTwoDigit = date('y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');


include("$sGblIncludePath/otPageInclude.php");


$sMessage = '';
$sPage2ErrorMessage = '';

$sRemoteIp = $_SERVER['REMOTE_ADDR'];
$sServerIp = $_SERVER['SERVER_ADDR'];

// validate data

	$_SESSION["sSesFirstNameAsterisk"] = '';
	$_SESSION["sSesLastNameAsterisk"] = '';
	$_SESSION["sSesAddressAsterisk"] = '';
	$_SESSION["sSesAddress2Asterisk"] = '';
	$_SESSION["sSesCityAsterisk"] = '';
	$_SESSION["sSesStateAsterisk"] = '';
	$_SESSION["sSesZipCodeAsterisk"] = '';
	$_SESSION["sSesPhoneNoAsterisk"] = '';
	$_SESSION["sSesEmailAsterisk"] = '';
	
	
$sEmail = trim($sEmail);
$sFirst = trim($sFirst);
$sLast = trim($sLast);
$sAddress = trim($sAddress);
$sAddress2 = trim($sAddress2);
$sCity = trim($sCity);
$sZip = trim($sZip);
$sPhone = trim($sPhone_areaCode)."-".trim($sPhone_exchange)."-".trim($sPhone_number);
$sPhone_ext = trim($sPhone_ext);

if ($sCity != '') {
	$sCity = ereg_replace("\."," ",$sCity);
	$sCity = ereg_replace("  "," ",$sCity);
}

if ($sSalutation != '') {
	$_SESSION["sSesSalutation"] = $sSalutation;
}
if ($sFirst != '') {
	$_SESSION["sSesFirst"] = $sFirst;
}
if ($sLast != '') {
	$_SESSION["sSesLast"] = $sLast;
}
if ($sEmail != '') {
	$_SESSION["sSesEmail"] = $sEmail;
}
if ($sAddress != '') {
	$_SESSION["sSesAddress"] = $sAddress;
}
if ($sAddress2 != '') {
	$_SESSION["sSesAddress2"] = $sAddress2;
}
if ($sCity != '') {
	$_SESSION["sSesCity"] = $sCity;
}
if ($sState != '') {
	$_SESSION["sSesState"] = $sState;
} else {
	$sState = $_SESSION["sSesState"];
}
if ($sZip != '') {
	$_SESSION["sSesZip"] = $sZip;
}
if ($sPhone_areaCode != '') {
	$_SESSION['sSesPhone_areaCode'] = $sPhone_areaCode;
}
if ($sPhone_exchange != '') {
	$_SESSION['sSesPhone_exchange'] = $sPhone_exchange;
}
if ($sPhone_number != '') {
	$_SESSION['sSesPhone_number'] = $sPhone_number;
}
if ($sPhone_areaCode != '' && $sPhone_exchange != '' && $sPhone_number != '') {
	$_SESSION["sSesPhone"] = $sPhone;
}
if ($sPhone_ext != '') {
	$_SESSION['sSesPhoneExt'] = $sPhone_ext;
}



if ( !(validateEmailFormat($_SESSION['sSesEmail'])) ) {
	$sMessage .= "<li>Please Enter A Valid Email Address...";
	$_SESSION["sSesEmailAsterisk"] = "<font color=red>*</font> ";
}
	

if ( !(validateName($_SESSION['sSesFirst']))) {

	$sMessage .= "<li>Please Enter A Valid First Name...";
	$_SESSION["sSesFirstNameAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateName($_SESSION['sSesLast']))) {
	$sMessage .= "<li>Please Enter A Valid Last Name...";
	$_SESSION["sSesLastNameAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateAddress($_SESSION['sSesAddress']) ) ) {
	$sMessage .= "<li>Please Enter Valid Address...";
	$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
} 

/* was failing on single char apts. For example "1".
else if ( $sAddress2 != '' && !(validateAddress($sAddress2) )) {
	$sMessage .= "<li>Please Enter Valid Address...";
	$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
}
*/

if ( !(validateZip($_SESSION['sSesZip']) )) {
	$sMessage .= "<li>Please Enter Valid ZipCode...";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateCityStateZip($_SESSION['sSesCity'], $_SESSION['sSesState'], $_SESSION['sSesZip']))) {
	$sMessage .= "<li>Please Enter A Valid Combination Of City, State And ZipCode";
	$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
}

$iPhoneZipDistance = getDistance($_SESSION['sSesPhone'],$_SESSION['sSesZip']);


if ( strlen($_SESSION['sSesPhone']) ==0 ) {
	$sMessage .= "<li>Please Enter Primary Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if (strlen($_SESSION['sSesPhone']) < 12 || strlen($_SESSION['sSesPhone']) > 12) {
	$sMessage .= "<li>Please Enter Valid Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if ( !(validatePhone(substr($_SESSION['sSesPhone'],0,3), substr($_SESSION['sSesPhone'],4,3), substr($_SESSION['sSesPhone'],8,4), '', $_SESSION['sSesState']))  ) {
	$sMessage .= "<li>Phone Number You Entered Is Not A Valid Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if (  $iPhoneZipDistance > 70) {
	$sMessage .= "<li>Phone Number Is Not Valid For The Zipcode You Entered...";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if ($_SESSION['sSesPhoneExt'] != '' && !(isInteger($_SESSION['sSesPhoneExt']))) {
	$sMessage .= "<li>Phone Number Extension Is Not Valid...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
}


if (isBannedIp($sRemoteIp)) {
	$sMessage .= "<li>We Do Not Accept Registration From $sRemoteIp...<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
} else if (isBannedDomain($_SESSION['sSesEmail'], $sDomainName)) {
	$sMessage .= "<li>We Do Not Accept Registration From ...$sDomainName<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
}


// keep track if any of page2 fields has validation error
$bPage2Error = false;

while (list($i, $val) = each($_SESSION["aSesPage2Offers"])) {
	
	// check if user dropped any offers from page2, remove that offer if user dropped
	$bDropped = "false";
	
	$sPage2Fields = '';
	$sPage2Data = '';
	
	$sOfferCode = $_SESSION["aSesPage2Offers"][$i];
		
	for ( $j=0; $j < count($aDropOffers);$j++) {
		if ($aDropOffers[$j] == $sOfferCode) {
			
			$bDropped = "true";
			
			// offer aborted stat info
			$_SESSION['sSesOfferAbortStatInfo'] .= "$sOfferCode,";
			
			//mail("smita@myfree.com","Test Page2 QueryString offer dropped ", $_SERVER['REMOTE_ADDR']." Offer Dropped - $sOfferCode");
			
			break;
		}
	}
	
	
	// process this offer if user didn't drop it
	if ($bDropped != "true") {
		
		// get all the page2 fields of this offer
		$sPage2MapQuery = "SELECT *
							   FROM   page2Map
				 			   WHERE offerCode = '$sOfferCode'
				 			   ORDER BY storageOrder ";
		
		$rPage2MapResult = dbQuery($sPage2MapQuery);
		
		
		// to track empty page2Data
		$sTestActualFieldNames = "";
		$sTestMessage = "";		
		$sTempMessage = '';
		
		while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
			
			$sActualFieldName = $oPage2MapRow->actualFieldName;
			// store in session variable to track display stored values again in case of any error
			$_SESSION['page2'][$sActualFieldName] = ${$sActualFieldName};			
			
			$iIsRequired = $oPage2MapRow->isRequired;
			$iEncryptData = $oPage2MapRow->encryptData;
			//echo "<BR>".$sActualFieldName;
			
			if ( strlen($$sActualFieldName) == 0 && $iIsRequired) {				
				$sTempMessage .= "Field $sActualFieldName Is Missing...\r\n";
				//echo $sActualFieldName;
				$bPage2Error = true;
			} else {
				
				// check if the field value is the valid option value if there are any options to choose from
				
				
				$sTempOptionValid = "false";
				
				$sOptionsQuery = "SELECT *
							  FROM   offerPage2Options
							  WHERE  offerCode = '$sOfferCode'
							  AND	 fieldName = '$sActualFieldName'";
				$rOptionsResult = dbQuery($sOptionsQuery);
				echo dbError();
				//echo "<BR>".$sOptionsQuery.dbNumRows($rOptionsResult);
				if ( dbNumRows($rOptionsResult) == 0 ) {
					$sTempOptionValid = "true";
					
					
				} else {
					while ($oOptionsRow = dbFetchObject($rOptionsResult)) {						
							
						if ($oOptionsRow->fieldValue == ${$sActualFieldName}) {
							$sTempOptionValid = "true";
							
						}
					}
				}
				
				if ($sTempOptionValid == "false") {
					$sTempMessage .= "Field $sActualFieldName Is Not Valid...\r\n";					
					$bPage2Error = true;
					//echo "error".$sActualFieldName.${$sActualFieldName};
				} else {
					
					// store page2 data in a variable to store in rejection log file in case of rejection
					$sLeadPage2Data .= "\r\n$sActualFieldName: ".${$sActualFieldName};
					
					$sPage2Data .= "\"".${$sActualFieldName}."\"|";
				}
			}
		}

		/********************* check data validity of page2 fields	************************/
		
		
		$sCondition = '';
		$sValid = false;
		$sValidityQuery = "SELECT *
						   FROM	  offerPage2Validation	
						   WHERE  offerCode = '$sOfferCode'";
		$rValidityResult = dbQuery($sValidityQuery);
		while ($oValidityRow = dbFetchObject($rValidityResult)) {
			$sCondition = $oValidityRow->condition;
			$sErrorMessage = $oValidityRow->errorMessage;
			
			if ($sCondition != '') {
				//echo $sCondition;
				eval("if(\$sValid = $sCondition);");
				if ( $sValid) {
					$sPage2ErrorMessage .= "<li>$sErrorMessage...";
					$sTempMessage .= $sErrorMessage;
				}
			}
		}


/********************/
		if ($sTempMessage == '') {

			
		$sPage2Data = addslashes($sPage2Data);
			
		// store offer's page2 data here
		$_SESSION['aSesOfferPage2Data']['$sOfferCode'] = $sPage2Data;
		// add offer in offers taken session variable
		// put the checked offer in offersTaken array		
		array_push($_SESSION['aSesOffersTaken'], $sOfferCode);
		
		// remove from page2Offers array, if user filled all the page2 info correctly
		unset($_SESSION["aSesPage2Offers"][$i]);
		
		$_SESSION['sSesOfferTakenStatInfo'] .= $sOfferCode.",";
		//$sPage2ErrorMessage .= $sTempMessage;
		}
	} else {
		// remove from page2Offers array, if user dropped the offer
		unset($_SESSION["aSesPage2Offers"][$i]);
	}
}


if ($bPage2Error) {
	$sMessage .= "<li>Please Provide All The Required Data...";
}

if ($sPage2ErrorMessage != '') {
	$sMessage .= $sPage2ErrorMessage;
}


// insert data here
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
//$sRemoteIp = $_SESSION["sSesRemoteIp"];
$iJoinListId = $_SESSION["iSesJoinListId"];


	
	// check if entry exists in active table
	$sActiveCheckQuery = "SELECT *
						  FROM   userData
						  WHERE  email = '$sEmail'
						  AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
	
	$rActiveCheckResult = dbQuery($sActiveCheckQuery);
		//echo $sActiveCheckQuery;
	// make entry into user table
	if ( dbNumRows($rActiveCheckResult) == 0 ) {
		$sInsertQuery = "INSERT INTO userData(email, salutation, first, last, address,
									 address2, city, state, zip, phoneNo, extension, dateTimeAdded)
						 VALUES(\"$sEmail\", \"$sSalutation\", \"$sFirst\", \"$sLast\", \"$sAddress\", 
								\"$sAddress2\", \"$sCity\", \"$sState\", \"$sZip\", \"$sPhone\", \"$sPhone_ext\", NOW())";
		$rInsertResult = dbQuery($sInsertQuery);
		echo dbError();
		
	}
	
	// user signup in joinlistid
	
	// If user checked Yes, to join newsletter
	if ($iJoinListId && !( substr($sEmail,-7) == 'aol.com' || substr($sEmail,-7) == 'aol.net' || substr($sEmail,-6) == 'rr.com' || substr($sEmail,-6) == 'rr.net' || substr($sEmail,-9) == '@mail.com')) {
		
		// Insert email in joinEmailSub if not exists with the same listId
		$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', '$sRemoteIp', NOW() )";
		$rSubInsertResult = dbQuery($sSubInsertQuery);
		
		// Insert email in joinEmailConf if not exists with the same listId
		$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							 VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', '$sRemoteIp', NOW() )";
		$rConfInsertResult = dbQuery($sConfInsertQuery);
		
		// Insert email in joinEmailActive if not exists with the same listId
		$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							   VALUES('$sEmail', '$iJoinListId', '".$_SESSION['sSesSourceCode']."', NOW() )";
		$rActiveInsertResult = dbQuery($sActiveInsertQuery);
		
		// delete from joinEmailInactive
		$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
								 WHERE  email = '$sEmail'
								 AND    joinListId = '$iJoinListId'";	
		$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
		
		// delete from pending
		$sPendingDeleteQuery = "DELETE FROM joinEmailPending
								WHERE  email = '$sEmail'
								AND    joinListId = '$iJoinListId'";	
		$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
		
		
		// get sub date and time
		
		
		//$bSendWelcomeEmail = false;
		$sCheckQuery ="SELECT *
					   FROM   joinEmailConfirm
					   WHERE  email = '$sEmail'
					   AND    joinListId = '$iJoinListId'";
					   //AND	  receivedWelcomeEmail = ''";
		
		$rCheckResult = dbQuery($sCheckQuery);
		while ($oCheckRow = dbFetchObject($rCheckResult)) {
		//	$iReceivedWelcomeEmail = $oCheckRow->receivedWelcomeEmail;
			$sDateTimeSubscribed = $oCheckRow->dateTimeAdded;			
		}
		
		if (dbNumRows($rCheckResult) > 0) {
		// send welcome email for this joinList if not sent already
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
			//if (substr($sEmail,0,5) == 'smita')  {
				
			mail($sEmail, $sWelcomeEmailSubject, $sWelcomeEmailContent, $sWelcomeEmailHeaders);

			$sUpdateQuery = "UPDATE joinEmailConfirm
							 SET    receivedWelcomeEmail = '1'
							 WHERE  email = '$sEmail'
							 AND    joinListId = '$iJoinListId'";
			$rUpdateResult = dbQuery($sUpdateQuery);
			echo dbError();
			//}
		}
		}

	}
	
	// make offers taken entries into otData table
	
	while (list($i, $val) = each($_SESSION["aSesOffersTaken"])) {
	
		$sOfferCode = $_SESSION['aSesOffersTaken'][$i];
		$sPage2Data = $_SESSION['aSesOfferPage2Data']['$sOfferCode'];
			
			
			//echo mysql_error();
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
			
			// record offer taken entry
						
			$sLeadInsertQuery = "INSERT INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode )
						 VALUES(\"$sEmail\", \"$sOfferCode\", \"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\",  \"".$_SESSION["sSesSubSourceCode"].
			"\", \"".$_SESSION["iSesPageId"]."\", NOW(), '$sRemoteIp', '$sServerIp', \"$sPage2Data\", '".$_SESSION["sSesPageMode"]."')";
		
			$rLeadInsertResult = dbQuery($sLeadInsertQuery);
			$iOtDataId = dbInsertId();		
			
			
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
			if ($_SESSION["iSesPageAutoRespSent"] == '' && $iPageAutoEmail) {
				
					$sPageAutoEmailText = eregi_replace("\[EMAIL\]", $_SESSION["sSesEmail"], $sPageAutoEmailText);
					$sPageAutoEmailText = eregi_replace("\[SOURCE_CODE\]", $_SESSION["sSesSourceCode"], $sPageAutoEmailText);
					$sPageAutoEmailText = eregi_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sPageAutoEmailText);
					
					$sPageAutoEmailHeaders = "From: $sPageAutoEmailFromAddr\r\n";
					$sPageAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					if ($sPageAutoEmailFormat == 'html') {
						$sPageAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
					}
					
					mail($_SESSION["sSesEmail"], $sPageAutoEmailSub, $sPageAutoEmailText, $sPageAutoEmailHeaders);
		
					$_SESSION["iSesPageAutoRespSent"] = 1;
			}
			
			
			// offer taken stats
			$sRealTimeResponse = "";
			
			// following condition commented to allow posting test leads in real time 
			//if ($_SESSION["sSesPageMode"] == 'A') {

			
				//$sOfferTakenStatInfo = substr($sOfferTakenStatInfo,0, strlen($sOfferTakenStatInfo)-1);
				//echo "method".$iDeliveryMethodId;
				//|| $_SESSION["sSesEmail"] =='smita@myfree.com'
				if (!(substr(strtolower($_SESSION["sSesAddress"]),0,11) == '3401 dundee' && $_SESSION["sSesZip"] == '60062') ) {
					
				if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
					// 2 = real time form post - GET
					// 3 = real time form post - POST
					
					$aUrlArray = explode("//", $sPostingUrl);
					$sUrlPart = $aUrlArray[1];
					
					
					//if ($_SESSION["sSesEmail"] == 'smita@myfree.com') {
					$sHttpPostString = ereg_replace("\[salutation\]",urlencode($_SESSION["sSesSalutation"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[email\]",urlencode($_SESSION["sSesEmail"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[first\]",urlencode($_SESSION["sSesFirst"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[last\]",urlencode($_SESSION["sSesLast"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address\]",urlencode($_SESSION["sSesAddress"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address2\]",urlencode($_SESSION["sSesAddress2"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[city\]",urlencode($_SESSION["sSesCity"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[state\]",urlencode($_SESSION["sSesState"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[zip\]",urlencode($_SESSION["sSesZip"]), $sHttpPostString);
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
						
						//if ($_SESSION["sSesEmail"] == 'smita@myfree.com') {
							$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sActualFieldName), $sHttpPostString);
						/*} else {
							$sHttpPostString = eregi_replace("\[$sFieldVar\]",$$sActualFieldName, $sHttpPostString);
						}*/
						
						
						$f++;
					}
					
					if ($rPage2MapResult) {
						dbFreeResult($rPage2MapResult);
					}
					
					// separate host part and script path
					
					$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
					$sHostPart = ereg_replace("\/","",$sHostPart);
					
					
					$sScriptPath = substr($sUrlPart,strlen($sHostPart));
					if (strstr($sPostingUrl, "https:")) {
						$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
					} else {
						
						$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
						
					}
					$sHttpPostString = stripslashes($sHttpPostString);
								
					if ($rSocketConnection) {
						if ($iDeliveryMethodId == '2') {
							// http form post - GET
							
							$sScriptPath  .= "?".$sHttpPostString;
							
							fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");
							fputs($rSocketConnection, "Host: $sHostPart\r\n");
							fputs($rSocketConnection, "User-Agent: MSIE\r\n");
							fputs($rSocketConnection, "Connection: close\r\n\r\n");
							
						} else if ($iDeliveryMethodId == '3') {
							// http form post - POST
							fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
							fputs($rSocketConnection, "Host: $sHostPart\r\n");
							fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
							fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
							fputs($rSocketConnection, "User-Agent: MSIE\r\n");
							fputs($rSocketConnection, "Connection: close\r\n\r\n");
							fputs($rSocketConnection, $sHttpPostString);
						}
						
						if ($sOfferCode != 'VIDEPROF') {
						
							while(!feof($rSocketConnection)) {
								$sRealTimeResponse .= fgets($rSocketConnection, 1024);
							}
						}
						
						fclose($rSocketConnection);
						
					} else {
						echo "$errstr ($errno)<br />\r\n";
					}
										
					$sUpdateStatusQuery = "UPDATE otData
									   SET    processStatus = 'P',
											  sendStatus = 'S',
											  howSent = '$sHowSent',
											  dateTimeProcessed = now(),
											  dateTimeSent = now(),
											  realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
									   WHERE  id = '$iOtDataId'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
					
				} else if ($iDeliveryMethodId == 4) {
					// send lead email if lead delivery method set as real time email
					// only if mode is active
					
					$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
					$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					//if ($sOfferAutoEmailFormat == 'html') {
					//$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
					//}
					
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
					$sSingleEmailBody = ereg_replace("\[phone\]",$_SESSION["sSesPhone"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ipAddress\]",$sRemoteIp, $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[dd\]", urlencode($iCurrDay), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[hh\]", urlencode($iCurrHH), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ii\]", urlencode($iCurrMM), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ss\]", urlencode($iCurrSS), $sSingleEmailBody);
				
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
						$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",$$sActualFieldName, $sSingleEmailBody);
						
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

					$sUpdateStatusQuery = "UPDATE otData
									   		SET   processStatus = 'P',
												  sendStatus = 'S',
											 	  howSent = '$sHowSent'
									 	  WHERE   id = '$iOtDataId'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
				}
				}

	} 
	/**************** end of offers taken while loop  *********************/
	

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
		}
	}
	/**************************************************************/
	
	
	/*************** attach sessionid if set to pass in page properties  ***********/
	
	if ( $_SESSION["sSesGoTo"] != '' ) {
		$_SESSION["sSesGoTo"] = urldecode($_SESSION["sSesGoTo"]);
	}
	
	if ($iPassOnPhpsessid) {
		if (strstr($sRedirectTo,"?")) {
			$sRedirectTo .= "&".SID;
			$sRedirectToNotOfferTaken .= "&".SID;
		} else {
			$sRedirectTo .= "?".SID;
			$sRedirectToNotOfferTaken .= "?".SID;
		}	
		
		$sRedirectTo .= "&sSiteId=".$_SESSION['sSiteId'];
		$sRedirectToNotOfferTaken .= "&sSiteId=".$_SESSION['sSiteId'];	
		
		if ( $_SESSION["sSesGoTo"] != '' ) {
			
			if (strstr($_SESSION["sSesGoTo"],"?")) {
				$_SESSION["sSesGoTo"] .= "&".SID;
			} else {
				$_SESSION["sSesGoTo"] .= "?".SID;
			}
			$_SESSION["sSesGoTo"] .= "&sSiteId=".$_SESSION['sSiteId'];
		}
		
	}
	/**************************************************************/
	
	
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
			$bPhoneExtFound = false;
			$bSrcFound = false;
			$bSSFound = false;
			$bTitleFound = false;
			$bIpFound = false;
			
			
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
			
			
			$sQueryStringToPass = "e=".$_SESSION["sSesEmail"]."&f=".$_SESSION["sSesFirst"]."&l=".$_SESSION["sSesLast"]."&a1=".
			$_SESSION["sSesAddress"]."&a2=".$_SESSION["sSesAddress2"]."&c=".$_SESSION["sSesCity"]."&s=".$_SESSION["sSesState"].
			"&z=".$_SESSION["sSesZip"]."&p=".$_SESSION["sSesPhone"]."&ext=".$_SESSION["sSesPhoneExt"]."&src=".$_SESSION["sSesSourceCode"].
			"&ss=".$_SESSION["sSesSubSourceCode"]."&t=".$_SESSION["sSesSalutation"]."&ip=".$sRemoteIp;
			
						
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
			
		
			header("Location:".$_SESSION["sSesGoTo"]);
		}
	} else {
		if (count($_SESSION["aSesOffersTaken"])>0) {
			
					
			if ($sRedirectPopOption != '') {
				 $sPopUpContent =  "<script language=JavaScript>";
				 $sPopUpContent .= "var popupWindow = window.open('".$sRedirectPopUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
				 $sPopUpContent .= "window.location.href = '".$sRedirectTo."'; ";				 
				 
				 if ($sRedirectPopOption == 'popUp') {
				 	$popUpContent .= "popupWindow.focus();";
				 } else {
				 	$popUpContent .= "popupWindow.blur();";
				 }
				 
				 $sPopUpContent .=  "</script>";
				 echo $sPopUpContent;
			} else {
				header("Location:$sRedirectTo");
			}
		} else {
						
			if ($sRedirectNotOfferTakenPopOption != '') {
				 $sPopUpContent =  "<script language=JavaScript>";
				 $sPopUpContent .= "var popupWindow = window.open('".$sRedirectNotOfferTakenPopUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
				 $sPopUpContent .= "window.location.href = '".$sRedirectToNotOfferTaken."'; ";				 
				 
				 if ($sRedirectNotOfferTakenPopOption == 'popUp') {
				 	$popUpContent .= "popupWindow.focus();";
				 } else {
				 	$popUpContent .= "popupWindow.blur();";
				 }
				 
				 $sPopUpContent .=  "</script>";
				 echo $sPopUpContent;
			} else {
				header("Location:$sRedirectToNotOfferTaken");
			}
		}
	}
		
	/****************** end of redirecting to next page ***********************/
	
} else {
	
	/********************* Send back to previous page if message is not blank *****************/
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=$sMessage&$sOutboundQueryString&".SID);
	} else {
		header("Location:$sRefererScriptFileName?sMessage=$sMessage&$sOutboundQueryString&".SID);
	}
	/***************************************************************/
	
}

?>
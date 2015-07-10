<?php

/*********

Script to Handle Ot Page1 Submission.
Script is Called when User submits the form in ot page1

**********/

/************* initialize script variables ***************/

/***************  End Initializing variables **********************/



include("includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");


// start session before including otPageInclude.php
session_start();


include("$sGblIncludePath/sopInclude.php");


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

	
$sRemoteIp = $_SERVER['REMOTE_ADDR'];


//validate data

$sEmail = trim($sEmail);
$sFirst = trim($sFirst);
$sLast = trim($sLast);
$sAddress = trim($sAddress);
$sAddress2 = trim($sAddress2);
$sCity = trim($sCity);
$sZip = trim($sZip);
$sPhone = trim($sPhone);

if ($sCity != '') {
	$sCity = ereg_replace("\."," ",$sCity);
	$sCity = ereg_replace("  "," ",$sCity);
}


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
$_SESSION["sSesRemoteIp"] = $sRemoteIp;
$_SESSION["iSesJoinListId"] = $iJoinListId;

if ($sSourceCode) {
	$_SESSION["sSesSourceCode"] = $sSourceCode;
}
if ($sSubSourceCode) {
	$_SESSION["sSesSubSourceCode"] = $sSubSourceCode;
}

// get all the page2 fields of this offer into session variable to display again in case of error
			$sPage2MapQuery = "SELECT *
							   FROM   page2Map
				 			   WHERE offerCode = '$sOfferCode'
				 			   ORDER BY storageOrder ";
			
			$rPage2MapResult = dbQuery($sPage2MapQuery);
			
			
			// to track empty page2Data
			$sTestActualFieldNames = "";
			$sTestMessage = "";
			
			while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
				$sActualFieldName = $oPage2MapRow->actualFieldName;
				$_SESSION['page2'][$sActualFieldName] = ${$sActualFieldName};			
				
			}

if ( !(validateEmailFormat($sEmail)) ) {
	$sMessage .= "<li>Please Enter A Valid Email Address...";
	$_SESSION["sSesEmailAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateName($sFirst))) {
	$sMessage .= "<li>Please Enter A Valid First Name...";
	$_SESSION["sSesFirstNameAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateName($sLast))) {
	$sMessage .= "<li>Please Enter A Valid Last Name...";
	$_SESSION["sSesLastNameAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateAddress($sAddress) ) ) {
	$sMessage .= "<li>Please Enter Valid Address...";
	$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
} 

/* was failing on single char apts. For example "1".
else if ( $sAddress2 != '' && !(validateAddress($sAddress2) )) {
	$sMessage .= "<li>Please Enter Valid Address...";
	$_SESSION["sSesAddressAsterisk"] = "<font color=red>*</font> ";
}
*/

if ( !(validateZip($sZip) )) {
	$sMessage .= "<li>Please Enter Valid ZipCode...";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
}

if ( !(validateCityStateZip($sCity, $sState, $sZip))) {
	$sMessage .= "<li>Please Enter A Valid Combination Of City, State And ZipCode";
	$_SESSION["sSesCityAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesStateAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
}

$iPhoneZipDistance = getDistance($sPhone,$sZip);


if ( strlen($sPhone) ==0 ) {
	$sMessage .= "<li>Please Enter Primary Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if (strlen($sPhone) < 12 || strlen($sPhone) > 12) {
	$sMessage .= "<li>Please Enter Valid Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if ( !(validatePhone(substr($sPhone,0,3), substr($sPhone,4,3), substr($sPhone,8,4), '', $sState))  ) {
	$sMessage .= "<li>Phone Number You Entered Is Not A Valid Phone Number...";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
} else if (  $iPhoneZipDistance > 70) {
	$sMessage .= "<li>Phone Number Is Not Valid For The Zipcode You Entered...";
	$_SESSION["sSesZipCodeAsterisk"] = "<font color=red>*</font> ";
	$_SESSION["sSesPhoneNoAsterisk"] = "<font color=red>*</font> ";
}


if (isBannedIp($sRemoteIp)) {
	$sMessage .= "<li>We Do Not Accept Registration From $sRemoteIp...<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
} else if (isBannedDomain($sEmail, $sDomainName)) {
	$sMessage .= "<li>We Do Not Accept Registration From ...$sDomainName<BR>
				  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com";
}


if ($sMessage == '') {
	// If data validated successfully,
	
	// reset offerchecked array
	$_SESSION["aSesOffersChecked"] = array();
	
	$_SESSION["sSesFirstNameAsterisk"] = '';
	$_SESSION["sSesLastNameAsterisk"] = '';
	$_SESSION["sSesAddressAsterisk"] = '';
	$_SESSION["sSesAddress2Asterisk"] = '';
	$_SESSION["sSesCityAsterisk"] = '';
	$_SESSION["sSesStateAsterisk"] = '';
	$_SESSION["sSesZipCodeAsterisk"] = '';
	$_SESSION["sSesPhoneNoAsterisk"] = '';
	$_SESSION["sSesEmailAsterisk"] = '';
	
	// entry in userData table
	
	// check if entry exists in active and history table
	$sActiveCheckQuery = "SELECT *
						  FROM   userData
						  WHERE  email = '$sEmail' AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
	$rActiveCheckResult = dbQuery($sActiveCheckQuery);
	
	$sFoundIn = '';
	
	// avoid using userDataHistory
	
	/*$sHistoryCheckQuery = "SELECT *
					FROM   userDataHistory
					WHERE  email = '$sEmail'";
	$rHistoryCheckResult = mysql_query($sHistoryCheckQuery);*/
	
	if (dbNumRows($rActiveCheckResult)>0) {
		
		$sFoundIn = "userData";

	} 
	// avoid using userDataHistory
	/*else if (mysql_num_rows($rHistoryCheckResult) > 0 ) {
		
		$sFoundIn = "userDataHistory";
		
	}*/
	
	// changed following row to avoid using userDataHistory
	//if ( mysql_num_rows($rActiveCheckResult) == 0  && mysql_num_rows($rHistoryCheckResult) == 0) {
	
	if ( dbNumRows($rActiveCheckResult) == 0 ) {
		$sInsertQuery = "INSERT INTO userData(email, salutation, first, last, address,
									 address2, city, state, zip, phoneNo, dateTimeAdded)
						 VALUES(\"$sEmail\", \"$sSalutation\", \"$sFirst\", \"$sLast\", \"$sAddress\", 
								\"$sAddress2\", \"$sCity\", \"$sState\", \"$sZip\", \"$sPhone\",
								NOW())";
		$rInsertResult = dbQuery($sInsertQuery);
		echo dbError();
		
	} 
	
	
	// If user checked Yes, to join newsletter
	if ($iJoinListId && !( substr($sEmail,-7) == 'aol.com' || substr($sEmail,-7) == 'aol.net' || substr($sEmail,-6) == 'rr.com' || substr($sEmail,-6) == 'rr.net' || substr($sEmail,-9) == '@mail.com')) {
		
		// Insert email in joinEmailSub if not exists with the same listId
		$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES('$sEmail', '$iJoinListId', '$sSourceCode', '$sRemoteIp', NOW() )";
		$rSubInsertResult = dbQuery($sSubInsertQuery);
		
		// Insert email in joinEmailConf if not exists with the same listId
		$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							 VALUES('$sEmail', '$iJoinListId', '$sSourceCode', '$sRemoteIp', NOW() )";
		$rConfInsertResult = dbQuery($sConfInsertQuery);
		
		// Insert email in joinEmailActive if not exists with the same listId
		$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							   VALUES('$sEmail', '$iJoinListId', '$sSourceCode', NOW() )";
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
			//if (!($iReceivedWelcomeEmail)) {
				//$bTempSendWelcomeEmail = true;
			//}
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
			$sWelcomeEmailContent = ereg_replace("\[SOURCE_CODE\]", $sSourceCode, $sWelcomeEmailContent);
			
			
			
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

		
	// entry in otData table
	$j=0;
	
	//for ($i = 0; $i < count($aOffersChecked); $i++) {
		// check if offer has page2 info
		
		$iDeliveryMethodId = '';
		$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
								OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
						FROM   offers AS O LEFT JOIN offerLeadSpec AS OL 
						ON  O.offerCode = OL.offerCode
						WHERE	   O.offerCode = '$sOfferCode'";
		
		$rOfferResult = dbQuery($sOfferQuery);
		echo dbError();
		
		while ($oOfferRow = dbFetchObject($rOfferResult)) {
			$iDeliveryMethodId = '';
			
			$fRevPerLead = $oOfferRow->revPerLead;
			//$sOfferMode = $oOfferRow->mode;
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
		
		if ($rOfferResult) {
			dbFreeResult($rOfferResult);
		}
		
		// get separate parts of the phone no for later use
			if (strlen($sPhone) == 10) {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,3,3);					
					$sPhone_number = substr($sPhone,6,4);
				
				} else {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,4,3);					
					$sPhone_number = substr($sPhone,8,4);
				}
									
					
			
			/******************** get page2 data here  ******************/
			
			
			// get all the page2 fields of this offer
			$sPage2MapQuery = "SELECT *
							   FROM   page2Map
				 			   WHERE offerCode = '$sOfferCode'
				 			   ORDER BY storageOrder ";
			
			$rPage2MapResult = dbQuery($sPage2MapQuery);
			
			
			// to track empty page2Data
			$sTestActualFieldNames = "";
			$sTestMessage = "";
			
			while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
				
				$sActualFieldName = $oPage2MapRow->actualFieldName;
				$sTestActualFieldNames .= "Field: $sActualFieldName\r\n";
				$sPage2Data .= "\"".${$sActualFieldName}."\"|";
				
				//$sSmitaMessage .= " $sActualFieldName - ".${$sActualFieldName};
			}
			$sPage2Data = addslashes($sPage2Data);
			
			// Insert only if user has not taken same offer any time
			// User IGNORE because there is UNIQUE index on email+offerCode
			
			$sLeadInsertQuery = "INSERT IGNORE INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, page2Data, mode )
								 VALUES(\"$sEmail\", \"$sOfferCode\", \"$fRevPerLead\", \"$sSourceCode\", \"$sSubSourceCode\", \"$iPageId\", NOW(), '$sRemoteIp', \"$sPage2Data\", '$sPageMode')";
			$rLeadInsertResult = dbQuery($sLeadInsertQuery);
			$iOtDataId = dbInsertId();
								
				
			// send offer auto email if set to do so
			if ($iOfferAutoEmail) {
				
				$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $sEmail, $sOfferAutoEmailBody);
				
				$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
				$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				if ($sOfferAutoEmailFormat == 'html') {
					$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				}
				
				mail($sEmail, $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
				
			}
			
			// send ot page auto email if set to do so and it is not sent
			if ($_SESSION["iSesPageAutoRespSent"] == '' && $iPageAutoEmail) {				
				
					$sPageAutoEmailText = eregi_replace("\[EMAIL\]", $sEmail, $sPageAutoEmailText);
					$sPageAutoEmailText = eregi_replace("\[SOURCE_CODE\]", $sSourceCode, $sPageAutoEmailText);
					$sPageAutoEmailText = eregi_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sPageAutoEmailText);
		
					$sPageAutoEmailHeaders = "From: $sPageAutoEmailFromAddr\r\n";
					$sPageAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					if ($sPageAutoEmailFormat == 'html') {
						$sPageAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
					}
					mail($sEmail, $sPageAutoEmailSub, $sPageAutoEmailText, $sPageAutoEmailHeaders);
		
					$_SESSION["iSesPageAutoRespSent"] = 1;
			}
				
			// following condition commented to allow posting test leads in real time 			
			//if ($sPageMode == 'A') {
			
				// for offer taken stat info
				$sOfferTakenStatInfo .= $sOfferCode. ",";								
			
				$sRealTimeResponse = '';
				
			if (!(substr(strtolower($sAddress,0,11)) == '3401 dundee' && $sZip == '60062') ) {
				
			if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
				// 2 = real time form post - GET
				// 3 = real time form post - POST
		
				$aUrlArray = explode("//", $sPostingUrl);
				$sUrlPart = $aUrlArray[1];
				$sHttpPostString = ereg_replace("\[salutation\]",urlencode($sSalutation), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[email\]",urlencode($sEmail), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[first\]",urlencode($sFirst), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[last\]",urlencode($sLast), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[address\]",urlencode($sAddress), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[address2\]",urlencode($sAddress2), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[city\]",urlencode($sCity), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[state\]",urlencode($sState), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[zip\]",urlencode($sZip), $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone\]",urlencode($sPhone), $sHttpPostString);
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
								
				if ($rSocketConnection) {
				if ($iDeliveryMethodId == '2') {
					
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
					
					$sRealTimeResponse = "";
					
					if ($sOfferCode != 'VIDEPROF') {
						while(!feof($rSocketConnection)) {
							$sRealTimeResponse .= fgets($rSocketConnection, 4096);
						}
					}
					
					fclose($rSocketConnection);
					
				} else {
					echo "$errstr ($errno)<br />\n";
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
								
				$sSingleEmailSubject = ereg_replace("\[offerCode\]", $sOfferCode, $sSingleEmailSubject);
				
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
				
				$sSingleEmailBody = ereg_replace("\[email\]",$sEmail, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[salutation\]",$sSalutation, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[first\]",$sFirst, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[last\]",$sLast, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[address\]",$sAddress, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[address2\]",$sAddress2, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[city\]",$sCity, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[state\]",$sState, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[zip\]",$sZip, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone\]",$sPhone, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[ipAddress\]",$sRemoteIp, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_areaCode\]", $sPhone_areaCode, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_exchange\]", $sPhone_exchange, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_number\]", $sPhone_number, $sSingleEmailBody);
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
							
				for ($x=0;$x<count($aSingleEmailBodyArray);$x++) {
					$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
				}
							
				mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
				
								
				$sUpdateStatusQuery = "UPDATE otData
									   SET    processStatus = 'P',
											  sendStatus = 'S',
											  howSent = '$sHowSent'
									   WHERE  id = '$iOtDataId'";
				$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
			}
			
			} // end if test lead
			
			
			//} // end if mode = 'A'
			
		//}		
	//}
	
	
	// offer taken stats
	if ($sPageMode == 'A' && $sOfferTakenStatInfo != '') {
		
		//$sOfferTakenStatInfo = substr($sOfferTakenStatInfo,0, strlen($sOfferTakenStatInfo)-1);
		
		$sOfferTakenStatInfo = substr($sOfferTakenStatInfo, 0, strlen($sOfferTakenStatInfo)-1);
		
		$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('$iPageId', '$sOfferTakenStatInfo', '$sSourceCode', CURRENT_DATE)";
		$rStatResult = dbQuery($sStatQuery);
		
	}	
	
			$sPrepopcodes = "";
						
				
				//echo $sOutboundQueryString;
				
				//if ($iPassOnInboundQueryString) {
					if (strstr($sRedirectTo,"?")) {
						$sRedirectTo .= "&".SID."&".$sOutboundQueryString;
					} else {
						$sRedirectTo .= "?".SID."&".$sOutboundQueryString;
					}
					
					if ($_SESSION["sSesGoTo"] != '') {
						if (strstr($_SESSION["sSesGoTo"],"?")) {
							$_SESSION["sSesGoTo"] .= "&".SID."&".$sOutboundQueryString;
						} else {
							$_SESSION["sSesGoTo"] .= "?".SID."&".$sOutboundQueryString;
						}
					}
				//}
				
						
						// if not mapped
						if (strstr($sRedirectTo,"?")) {
							$sRedirectTo .= "&e=$sEmail&f=$sFirst&l=$sLast&a1=$sAddress&a2=$sAddress2&c=$sCity&s=$sState&z=$sZip&p=$sPhone&src=$sSourceCode&ss=$sSubSourceCode&t=$sSalutation&ip=$sRemoteIp";
						} else {
							$sRedirectTo .= "?e=$sEmail&f=$sFirst&l=$sLast&a1=$sAddress&a2=$sAddress2&c=$sCity&s=$sState&z=$sZip&p=$sPhone&src=$sSourceCode&ss=$sSubSourceCode&t=$sSalutation&ip=$sRemoteIp";
						}
						
						if ($_SESSION["sSesGoTo"] != '') {
							if (strstr($_SESSION["sSesGoTo"],"?")) {
								$_SESSION["sSesGoTo"] .= "&e=$sEmail&f=$sFirst&l=$sLast&a1=$sAddress&a2=$sAddress2&c=$sCity&s=$sState&z=$sZip&p=$sPhone&src=$sSourceCode&ss=$sSubSourceCode&t=$sSalutation&ip=$sRemoteIp";
							} else {
								$_SESSION["sSesGoTo"] .= "?e=$sEmail&f=$sFirst&l=$sLast&a1=$sAddress&a2=$sAddress2&c=$sCity&s=$sState&z=$sZip&p=$sPhone&src=$sSourceCode&ss=$sSubSourceCode&t=$sSalutation&ip=$sRemoteIp";
							}
						}	
								
			if ($_SESSION["sSesGoTo"] != '') {				
				
				header("Location:".$_SESSION["sSesGoTo"]);
				
			} else {	

				
					header("Location:$sRedirectTo");
			}
			
	//}
	
} else {
	//echo "fgf".$sOutboundQueryString;
	// display page again with the current values filled in
	//$_SESSION["aSesOffersChecked"] = $aOffersChecked;
	//echo "bvvcbvb".$sOutboundQueryString;
	unset($_SESSION['sSesNewSession']);
	unset($_SESSION['aSesPageOffersDisplayed']);
	//$_SESSION['aSesPageOffersDisplayed'] = array();
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&".SID."&sMessage=$sMessage&$sOutboundQueryString");
	} else {
		header("Location:$sRefererScriptFileName?".SID."&sMessage=$sMessage&$sOutboundQueryString");
	}
	exit();
}

?>

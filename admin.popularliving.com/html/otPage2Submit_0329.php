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

/*
$sPage2QueryString =  $_SERVER['REMOTE_ADDR']."\n\n". $sPage2QueryString;
mail("smita@myfree.com","Test Page2 QueryString",$sPage2QueryString);
*/
//echo "df".$iSesPageId;
include("$sGblIncludePath/otPageInclude.php");


$sMessage = '';
// make comma separated list of page2 offers from the array

// If no error messages, make entry in otData table
if ($sMessage == '') {
	
	for ($i = 0; $i < count($_SESSION["aSesPage2Offers"]); $i++) {
		// check if user dropped any offers from page2, remove that offer if user dropped
		$bDropped = "false";
		
		$sPage2Fields = '';
		$sPage2Data = '';
		
		$sOfferCode = $_SESSION["aSesPage2Offers"][$i];
		
		for ( $j=0; $j < count($aDropOffers);$j++) {
			if ($aDropOffers[$j] == $sOfferCode) {
				
				$bDropped = "true";
				
				// offer aborted stat info
				$sOfferAbortStatInfo .= "$sOfferCode,";
				
				//mail("smita@myfree.com","Test Page2 QueryString offer dropped ", $_SERVER['REMOTE_ADDR']." Offer Dropped - $sOfferCode");
				
				break;
			}
		}

		
		// process this offer if user didn't drop it
		if ($bDropped != "true") {
				
			$sOfferTakenStatInfo .= $sOfferCode.",";
				
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
			
			
			// to track empty page2Data
			
				// omit to record lead if page2 data is blank
				if (!ereg("[^\"|]{1,}", $sPage2Data)) {
					continue;
				}
						
			
			if ($rPage2MapResult) {
				dbFreeResult($rPage2MapResult);
			}
			
			if (strlen($_SESSION["sSesPhone"]) == 10) {
					$sPhone_areaCode = substr($_SESSION["sSesPhone"],0,3);					
					$sPhone_exchange = substr($_SESSION["sSesPhone"],3,3);					
					$sPhone_number = substr($_SESSION["sSesPhone"],6,4);				
				} else {
					$sPhone_areaCode = substr($_SESSION["sSesPhone"],0,3);					
					$sPhone_exchange = substr($_SESSION["sSesPhone"],4,3);					
					$sPhone_number = substr($_SESSION["sSesPhone"],8,4);
				}
				
				
			// add offer in offers taken session variable
			// put the checked offer in offersTaken array
			array_push($_SESSION['aSesOffersTaken'], $sOfferCode);
			$sPage2Data = addslashes($sPage2Data);
			
			//$sSmitaMessage .= " page2Data - $sOfferCode - $sPage2Data";
			
			//mail("smita@myfree.com","Test Page2 Data",$sSmitaMessage);

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
			
			
			
			/******** following code is to track blank offercodes   **********/
			
			
			if ($sOfferCode == ''&& $sTrackingEmailSent != 'Y') {
				$sTempMessage = "SessionId:".session_id()."\n";
				$sTempMessage.= "Page2 Offers Count: ".count($_SESSION["aSesPage2Offers"])."\n";
				$sTempMessage .= "Page2 Offers list: \n";
				for ($x=0;$x<count($_SESSION["aSesPage2Offers"]);$x++) {
					$sTempMessage .= $_SESSION["aSesPage2Offers"][$x]."\n";
				}
				$sTempMessage .= "Dropped Offers Count: ".count($aDropOffers[$j])."\n";
				
				$sTempMessage .= "Dropped Offers list: \n";
				for ($x=0;$x<count($aDropOffers);$x++) {
					$sTempMessage .= "$x - ".$aDropOffers[$x]."\n";
				}
				mail ("smita@myfree.com","blank offercode tracking - page2", $sTempMessage,"From:nibbles@amperemedia.com\r\n");
				$sTempMessage = '';
				$sTrackingEmailSent = 'Y';
			}
			
			/*************** End code to track blank offerCodes ***********/
			
			
			
			
			// User IGNORE because there is UNIQUE index on email+offerCode
			$sLeadInsertQuery = "INSERT INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode, sessionId )
						 VALUES(\"".$_SESSION["sSesEmail"]."\", \"$sOfferCode\", \"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\",  \"".$_SESSION["sSesSubSourceCode"].
			"\", \"".$_SESSION["iSesPageId"]."\", NOW(), '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', \"$sPage2Data\", '".$_SESSION["sSesPageMode"]."', '".session_id()."')";
		
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
				
				
				if ($_SESSION['iSesPageAutoRespId']) {
					$iTempAutoRespId = $_SESSION['iSesPageAutoRespId'];					
				} else {
					$iTempAutoRespId = $iDefaultAutoRespId;
				}
				
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
						$sReplyTo = $oAutoRespRow->replyTo;
					}
									
				
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
					$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode($_SESSION["sSesRemoteIp"]), $sHttpPostString);
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
						
					
					/*$sHttpPostStringEncoded = "";
					if ($_SESSION["sSesEmail"] == 'smita@myfree.com') {
							
							$aHttpPostStringArray = explode("&",$sHttpPostString);
							$sHttpPostString = "";
							for ($i=0; $i < count($aHttpPostStringArray); $i++) {
								$iTempPos = strpos($aHttpPostStringArray[$i],"=");
								$sTempField = substr($aHttpPostStringArray[$i],0,$iTempPos);
								$sTempValue = substr($aHttpPostStringArray[$i],$iTempPos+1);
								$sHttpPostString .= $sTempField."=".urlencode($sTempValue)."&";
							}
							
					}
					*/
								
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
					$sSingleEmailBody = ereg_replace("\[ipAddress\]",$_SESSION["sSesRemoteIp"], $sSingleEmailBody);
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
			
			//} // end if mode = 'A'
		}
	}
	
	
	
	
	if ($_SESSION["sSesPageMode"] == 'A' ) {
		
		//$sOfferTakenStatInfo = substr($sOfferTakenStatInfo,0, strlen($sOfferTakenStatInfo)-1);
		
		// offer taken count
		if ($sOfferTakenStatInfo != '') {
			$sOfferTakenStatInfo = substr($sOfferTakenStatInfo, 0, strlen($sOfferTakenStatInfo)-1);
			
			$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('".$_SESSION["iSesPageId"]."', '$sOfferTakenStatInfo', '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
		}
		
		// offer aborted count
		//echo "Dfdd".$sOfferAbortStatInfo;
		
		if ($sOfferAbortStatInfo != '') {
			$sOfferAbortStatInfo = substr($sOfferAbortStatInfo, 0, strlen($sOfferAbortStatInfo)-1);
			
			$sStatQuery = "INSERT INTO tempOfferAbortStats(pageId, statInfo, sourceCode, displayDate)
								VALUES('".$_SESSION["iSesPageId"]."', '$sOfferAbortStatInfo', '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
			echo dbError();
		}
	}
	
	echo dbError();
		
	$sPrepopcodes = "";
	// reset page auto responderId session variable when going to other page.
	unset($_SESSION['iSesPageAutoRespId']);
	
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
	
	//echo $sOutboundQueryString;
	
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
					case "src":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesSourceCode"]."&";
					$bSrcFound = true;
					break;
					case "t":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesSalutation"]."&";
					$bTitleFound = true;
					break;
					case "ip":
					$sPrepopcodes .= $aInboundOutboundPair[1]."=".$_SESSION["sSesRemoteIp"]."&";
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
				$sPrepopcodes .= "ip=".$_SESSION["sSesRemoteIp"]."&";
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
			$_SESSION["sSesAddress"]."&a2=".$_SESSION["sSesAddress2"]."&c=".$_SESSION["sSesCity"]."&s=".
			$_SESSION["sSesState"]."&z=".$_SESSION["sSesZip"]."&p=".$_SESSION["sSesPhone"]."&src=".$_SESSION["sSesSourceCode"].
			"&ss=".$_SESSION["sSesSubSourceCode"]."&t=".$_SESSION["sSesSalutation"]."&ip=".$_SESSION["sSesRemoteIp"];
			
						
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
	
	if ($iEnableGoTo && $_SESSION["sSesGoTo"] != '') {
		
		if ($iIsGoToPopUp) {
			echo "<script language=JavaScript>
							window.open(\"".$_SESSION["sSesGoTo"]."\",\"newWin\",\"scrollbars=yes, resizable=yes, status=yes\");
							window.location.replace('".$sRedirectTo."');
							</script>";
		} else {
			
		
			header("Location:".$_SESSION["sSesGoTo"]);
		}
	} else if ($iHasCustomRedirectProc) {
		include("$sGblIncludePath/customPageRedirect.php");
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
	//}
	
}
else {
		
	header("Location:$sGblSiteRoot/otPage2.php?sMessage=$sMessage&".SID);
		
}

?>
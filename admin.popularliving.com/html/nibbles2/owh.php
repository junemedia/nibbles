<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');
include_once("libs/function.php");
include_once("libs/fields.php");


if ($_GET['ses'] !='') {
	$PHPSESSID = $_GET['ses'];
	$sHiddenSesField = "<input type=hidden name=PHPSESSID value='$PHPSESSID'>";
	$_COOKIE['PHPSESSID'] = $PHPSESSID;
}

if ($_POST['PHPSESSID']) {
	$PHPSESSID = $_POST['PHPSESSID'];
	$_COOKIE['PHPSESSID'] = $PHPSESSID;
}
if ($_GET['PHPSESSID']) {
	$PHPSESSID = $_GET['PHPSESSID'];
	$_COOKIE['PHPSESSID'] = $PHPSESSID;
}



session_start();

//echo "<br>cookie ses: ".$_COOKIE['PHPSESSID'];
//echo "<br>PHPSESSID: ".$PHPSESSID;
//echo "<br>session_id: ".session_id();


if ($sSubmit == 'submit') {
	if ($aDropOffers[0] != $sOfferCode) {
		$_SESSION['iSesPageId'] = $_SESSION['iSesNextPageId'];
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sMessage = '';
		$sPage2Fields = '';
		$sPage2Data = '';
		$iTempId = session_id();

		// Get all Page2 Fields from page2Map in nibbles database.
		$sPage2MapQuery = "SELECT *
						   FROM   page2Map
			 			   WHERE offerCode = '".$sOfferCode."'
			 			   ORDER BY storageOrder ";
		$rPage2MapResult = dbQuery($sPage2MapQuery);

		// to track empty page2Data - UNKNOWN
		$sTestActualFieldNames = "";
		$sTestMessage = "";
		$sTempMessage = '';
	
		// Loop through all Page2 Field Names.
		while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
			$sActualFieldName = $oPage2MapRow->actualFieldName;
			$_SESSION['page2'][$sActualFieldName] = ${$sActualFieldName};
			$sLeadPage2Data .= "\r\n$sActualFieldName: ".${$sActualFieldName};
			$sPage2Data .= "\"".${$sActualFieldName}."\"|";
		}
		
		$sPage2Data = addslashes($sPage2Data);

			// ************************ Get offer details  ***************************
			$iDeliveryMethodId = '';
			$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
				   OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
					FROM   offers AS O, offerLeadSpec AS OL
					WHERE  O.offerCode = OL.offerCode
					AND	   O.offerCode = '".$sOfferCode."'";
		
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
		
			/*********************** End getting offer details ********************/
			// If return false, then insert into otData.
			// User already took offer if return true.
			$bFoundDuplicateLead = checkForOtDataDups ($_SESSION["sSesEmail"],$sOfferCode);

			if ($bFoundDuplicateLead == false) {
				$sLeadInsertQuery = "INSERT IGNORE INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode, sessionId, postalVerified )
					 VALUES(\"".$_SESSION["sSesEmail"]."\", \"".$sOfferCode."\", \"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\",  \"".$_SESSION["sSesSubSourceCode"].
					"\", \"".$_SESSION["iSesPageId"]."\", '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', \"".$sPage2Data."\", 
					'A', '".session_id()."', 'V')";
				$rLeadInsertResult = dbQuery($sLeadInsertQuery);
				if (!($rLeadInsertResult)) {
					$sEmailMessage = "Insert into otData query failed.  Please run below insert query manually\n\n$sLeadInsertQuery";
					mail('it@amperemedia.com',"Insert otData Failed - otPage2Submit.php", "$sEmailMessage");
				}
			}
			
			// if offer taken, don't show same offer again
			if (is_array($_SESSION['aOfferTakenForCookie'])) {
				if (!in_array($sOfferCode, $_SESSION['aOfferTakenForCookie'])) {
					array_push($_SESSION['aOfferTakenForCookie'], $sOfferCode);
				}
			}

			/************** send offer auto email if offer is set to do so ***************/
			if ($iOfferAutoEmail) {
				$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $_SESSION["sSesEmail"], $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[salutation\]",urlencode($_SESSION["sSesSalutation"]), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[email\]",urlencode($_SESSION["sSesEmail"]), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[first\]",urlencode($_SESSION['sSesFirst']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[last\]",urlencode($_SESSION['sSesLast']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[address\]",urlencode($_SESSION['sSesAddress']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[address2\]",urlencode($_SESSION['sSesAddress2']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[city\]",urlencode($_SESSION['sSesCity']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[state\]",urlencode($_SESSION['sSesState']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[zip\]",urlencode($_SESSION['sSesZip']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[zip5only\]",urlencode(substr($_SESSION['sSesZip'], 0, 5)), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone\]",urlencode($_SESSION['sSesPhone']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ipAddress\]",urlencode($_SESSION['sSesRemoteIp']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_areaCode\]", urlencode($_SESSION['sSesPhoneAreaCode']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_exchange\]", urlencode($_SESSION['sSesPhoneExchange']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_number\]", urlencode($_SESSION['sSesPhoneNumber']), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[mm\]", urlencode(date('m')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[dd\]", urlencode(date('d')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[yy\]", urlencode(date('y')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[hh\]", urlencode(date('H')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ii\]", urlencode(date('i')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ss\]", urlencode(date('s')), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sOfferAutoEmailBody);
				
				$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
				$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				if ($sOfferAutoEmailFormat == 'html') {
					$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				}
				mail($_SESSION["sSesEmail"], $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
			}
			/*************************** End sending offer auto responder ********************/
			
			/*********************** Send real time leads ***********************/
			if ($bFoundDuplicateLead == false && !(strtolower(substr($_SESSION["sSesAddress"],0,11)) == '3401 dundee' && $_SESSION["sSesZip"] == '60062') ) {
				$sRealTimeResponse = '';
				
				if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
					// 2 = real time form post - GET
					// 3 = real time form post - POST

					$aUrlArray = explode("//", $sPostingUrl);
					$sUrlPart = $aUrlArray[1];
					
					if ($sOfferCode == 'MS_Eversave') {
						if ($_SESSION["iSesBirthMonth"] < 10) {
							$_SESSION["iSesBirthMonth"] = substr($_SESSION["iSesBirthMonth"],1,1);
						}
						if ($_SESSION["iSesBirthDay"] < 10) {
							$_SESSION["iSesBirthDay"] = substr($_SESSION["iSesBirthDay"],1,1);
						}
					}
	
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
					$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode($_SESSION['sSesPhoneAreaCode']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode($_SESSION['sSesPhoneExchange']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_number\]", urlencode($_SESSION['sSesPhoneNumber']), $sHttpPostString);
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
					$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);
						
					if ($sOfferCode == 'MS_Eversave') {
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
				 	 			   WHERE offerCode = '$sOfferCode'
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
					$sResult = httpFormPostGet($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sOfferCode,$_SESSION["sSesEmail"],$sHowSent);
					//mail('spatel@amperemedia.com', __line__.'asdf',"$sHttpPostString\n\n,$sUrlPart\n\n,$sPostingUrl\n\n,$iDeliveryMethodId\n\n,$sOfferCode\n\n,".$_SESSION["sSesEmail"]."\n\n,$sHowSent\n\n");
				} else if ($iDeliveryMethodId == 4) {
					// send lead email if lead delivery method set as real time email
					// only if mode is active
	
					$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
					$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					$sSingleEmailSubject = ereg_replace("\[offerCode\]",$sOfferCode, $sSingleEmailSubject);
	
	
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
									 	  WHERE   email='".$_SESSION["sSesEmail"]."' and offerCode='$sOfferCode'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
				}
			}
			/**********************  End sending real time leads  *****************/
			$_SESSION['iSesCurrentPositionInFlow']++;

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
			$_SESSION['aOfferTakenForCookie'] = array_unique($_SESSION['aOfferTakenForCookie']);
			foreach ($_SESSION['aOfferTakenForCookie'] as $sOfferCodeCookie) {
				$sCurrentCookieOfferCode .= "$sOfferCodeCookie,";
			}
			$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
			// expires in 180 days - 15552000 seconds	- Add/Update cookie.
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", ".popularliving.com", 0);
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", ".3400cookie.com", 0);
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", $_SESSION['sSesDomain'], 0);
			setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '', 0);
			
		}
		// end cookie

		$sStatQuery = "INSERT INTO tempOfferTakenStats(pageId, statInfo, sourceCode, displayDate)
				VALUES('".$_SESSION["iSesPageId"]."', \"$sOfferCode\", '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
		$rStatResult = dbQuery($sStatQuery);
		echo dbError();

		// If this is the last flow, send user to redirect url.
		if ($_SESSION['iSesCurrentPositionInFlow'] >= $_SESSION['iSesNoOfFlow']) {
			if (strstr($_SESSION['sSesRedirectUrl'],'?')) {
				$sRedirectTo = $_SESSION['sSesRedirectUrl']."&".SID;
			} else {
				$sRedirectTo = $_SESSION['sSesRedirectUrl']."?".SID;
			}
			echo "<script language=JavaScript>parent.location='$sRedirectTo'</script>";
		} else {
			echo "<script language=JavaScript>parent.location='ot.php?PHPSESSID=$iTempId'</script>";
		}
	} else {
		$_SESSION['iSesCurrentPositionInFlow']++;
		// IF OFFER DROPPED, THEN RECORD STATS AND REDIRECT URL TO NEXT FLOW.
		$sStatQuery = "INSERT INTO tempOfferAbortStats(pageId, statInfo, sourceCode, displayDate)
			VALUES('".$_SESSION["iSesPageId"]."', \"$sOfferCode\", '".$_SESSION["sSesSourceCode"]."', CURRENT_DATE)";
		$rStatResult = dbQuery($sStatQuery);
		echo dbError();
			
		$sCheckTemp = "SELECT * FROM abandedOffers
					WHERE offerCode = '$sOfferCode'
					AND email = '".$_SESSION["sSesEmail"]."'
					AND date_format(dateTimeAdded,'%Y-%m-%d') = CURRENT_DATE";
		$rCheckTempResult = dbQuery($sCheckTemp);
		if ( dbNumRows($rCheckTempResult) == 0 ) {
			$sInsertAbandedOffersQuery = "INSERT INTO abandedOffers(email, dateTimeAdded, remoteIp, sourceCode, offerCode, sessionId, pageId)
	                       VALUES('".$_SESSION["sSesEmail"]."', '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', \"".$_SESSION["sSesSourceCode"]."\", \"".$sOfferCode."\", '".session_id()."',".$_SESSION["iSesPageId"].")";
			$rInsertAbandedOffersResult = dbQuery($sInsertAbandedOffersQuery);
			echo dbError();
		}
		
		// If this is the last flow, send user to redirect url.
		if ($_SESSION['iSesCurrentPositionInFlow'] >= $_SESSION['iSesNoOfFlow']) {
			if (strstr($_SESSION['sSesRedirectUrl'],'?')) {
				$sRedirectTo = $_SESSION['sSesRedirectUrl']."&".SID;
			} else {
				$sRedirectTo = $_SESSION['sSesRedirectUrl']."?".SID;
			}
			echo "<script language=JavaScript>parent.location='$sRedirectTo'</script>";
		} else {
			echo "<script language=JavaScript>parent.location='ot.php?".SID."'</script>";
		}
	}
}


if ($sOfferCode == '') {
	$sOfferCode = trim($_GET['oc']);
}

// include only Open We Host offers - No stims.  Ampere's SOP offer
// prepare JavaScript for offer's special fields (page2 fields) validation
$sPage2JavaScript = "
	<script language=JavaScript>
	
	function pageValidation() {
	  var errMessage = '';";
			
// to track empty page2Data
$sTestActualFieldNames = '';
$sTestMessage = '';
$sJavaScriptDisplayValues = "<script language=JavaScript>\n
							  var eleType = '';\n 
							  var ele = '';\n
							  var selTemp = '';\n
							  ";
					
// get all the page2 fields of this offer
$sPage2MapQuery = "SELECT * FROM   page2Map
				   WHERE offerCode = '$sOfferCode'
				   ORDER BY storageOrder";
$rPage2MapResult = dbQuery($sPage2MapQuery);
while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
	$sActualFieldName = $oPage2MapRow->actualFieldName;
	if ($sMessage == '') {
		$_SESSION['page2'][$sActualFieldName] = '';
	}
			
	// check if any javascript function to be called when setting (changing)any values for this field
	$sSopOnChangeCall = $oPage2MapRow->sopOnChangeCall;
	${$sActualFieldName} = $_SESSION['page2'][$sActualFieldName];
	$sJavaScriptDisplayValues .= "if (document.form1.$sActualFieldName) {\n
								ele = document.form1.$sActualFieldName;\n
								  eleType = ele.type;\n
								if (eleType == 'text' || eleType == 'textarea') {\n
										ele.value = '".${$sActualFieldName}."';\n
								} else if (eleType == 'select-one') {
										for (var i=0; i < ele.length; i++) {
											selTemp = ele.options[i].value;

											if (selTemp == '".${$sActualFieldName}."') {
												ele.options[i].selected = true;
												break;
											}
										}
									} else if ( eleType == 'select-multiple') {
									} else if (eleType == 'checkbox') {
									if (ele.value == '".${$sActualFieldName}."') {
											ele.checked = true;
										} else {
											ele.checked = false;
									}
									} else if (document.forms[0].elements['$sActualFieldName'].length > 0) {
										for(r=0; r<ele.length; r++) {
										if (ele[r].value == '".${$sActualFieldName}."'){
											ele[r].checked = true;
										} else{
											ele[r].checked = false;
										}
									}
								}
							}\n";
		if ($sSopOnChangeCall != '') {
		$sJavaScriptDisplayValues .= "\n".$sSopOnChangeCall . "\n";
	}
}

	$sJavaScriptDisplayValues .= "</script>";
	$sPage2JavaScript .= "
			if (errMessage != '') {
	    		alert(errMessage);
	    		return false;
	  		} else {			
	    		return true;
	  		}
		}
	</script>";
					
	// place the javaScript of offer in template
	$sPage2JavaScript = "<script language=JavaScript>".$_SESSION['sSesJavaScriptVars']."</script>".$sPage2JavaScript;
			
	// replace offer variables
	$sTempOfferImage = "$sGblOfferImageUrl/$sOfferCode/$sOfferImageName";
	$sTempOfferSmallImage = "$sGblOfferImageUrl/$sOfferCode/$sOfferSmallImageName";
	if ($sAddiInfoText != '') {	// add additional information link for popup
		$aAddiInfoPopupSizeArray = explode(",",$sAddiInfoPopupSize);
		$iAddiInfoPopupWidth = $aAddiInfoPopupSizeArray[0];
		$iAddiInfoPopupHeight = $aAddiInfoPopupSizeArray[1];
		$sTempAddiInfoLink = " <a href='JavaScript:void(window.open(\"$sGblSiteRoot/offerAddiInfo.php?sOfferCode=$sOfferCode\",\"addiInfo\",\"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>$sAddiInfoTitle</a>";
	}
					
					
					
	// GET ADDITIONAL QUESTIONS FOR OFFER.  IF TEMPLATE TYPE IS STACKED, SHOW ALL ADDITIONAL QUESTIONS IN ONE PAGE
	// ELSE SHOW ONE OFFER AT A TIME.
	// *************** Prepare offers' page2 text and javascript validation *************
	// Check if any offers requires SSL - Start
	$bRequireSSL = false;
	$sCheckRequireSSLQuery = "SELECT * FROM offers 
					 WHERE offerCode ='$sOfferCode' AND isRequireSSL = 'Y'";
	$rCheckRequireSSLResult = dbQuery($sCheckRequireSSLQuery);
	if (dbNumRows($rCheckRequireSSLResult) > 0) {
		$bRequireSSL = true;
	}
	// Check if any offers requires SSL - End
					
	// write the javascript functions and page2Validation function to call on submit
	$sPage2JavaScript = "
		<script language=JavaScript>
	
		function page2Validation() {
		var errMessage = '';";
			
		$sOffersQuery = "SELECT * FROM offers WHERE offerCode IN ('$sOfferCode')";
		$rOffersResult = dbQuery($sOffersQuery);
		$sOffersOnPage2 = '';
		if(dbNumRows($rOffersResult) == 0){
			$sPage2JavaScript .= "\nparent.location = 'ot.php?".SID."';\n";
		}
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
				$submit = new SubmitButton();
				if (strstr($sOfferPage2Template,"THINNER_USER_FORM")) {
					$sUserForm = $_SESSION['sSesPage2ThinnerUserForm'];
					
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_LEFT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
					}
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_CENTER]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
					}
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_RIGHT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
					}
				} else {
					$sUserForm = $_SESSION['sSesPage2RegUserForm'];
					
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_LEFT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
					}
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_CENTER]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
					}
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_RIGHT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
					}
				}
				

				if (strstr($sUserForm,'[EMAIL_FIELD]')) {
					$email = new EmailField();
					$email->value = $_SESSION['sSesEmail'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$email->extra .= " disabled ";
					}
					$sUserForm = str_replace('[EMAIL_FIELD]',$email->html(),$sUserForm);
				}
			
			
				if (strstr($sUserForm,'[FIRST_FIELD]')) {
					$f = new FNameField();
					$f->value = $_SESSION['sSesFirst'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$f->extra .= " disabled ";
					}
					$sUserForm = str_replace('[FIRST_FIELD]',$f->html(),$sUserForm);
				}
				
				
				if (strstr($sUserForm,'[LAST_FIELD]')) {
					$l = new LNameField();
					$l->value = $_SESSION['sSesLast'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$l->extra .= " disabled ";
					}
					$sUserForm = str_replace('[LAST_FIELD]',$l->html(),$sUserForm);
				}
				
				
				if (strstr($sUserForm,'[ADDRESS_GROUP]')) {
					$add = new AddressGroup();
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$add->extra .= " disabled ";
					}
					$sUserForm = str_replace('[ADDRESS_GROUP]',$add->html(),$sUserForm);
				}
				 
			
				if (strstr($sUserForm,'[ADDRESS_FIELD]')) {
					$address = new AddressField();
					$address->value = $_SESSION['sSesAddress'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$address->extra .= " disabled ";
					}
					$sUserForm = str_replace('[ADDRESS_FIELD]',$address->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[ADDRESS2_FIELD]')) {
					$address2 = new AddressField();
					$address2->value = $_SESSION['sSesAddress2'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$address2->extra .= " disabled ";
					}
					$sUserForm = str_replace('[ADDRESS2_FIELD]',$address2->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[CITY_FIELD]')) {
					$city = new CityField();
					$city->value = $_SESSION['sSesCity'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$city->extra .= " disabled ";
					}
					$sUserForm = str_replace('[CITY_FIELD]',$city->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[STATE_FIELD]')) {
					$state = new StateField();
					$state->value = $_SESSION['sSesState'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$state->extra .= " disabled ";
					}
					$sUserForm = str_replace('[STATE_FIELD]',$state->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[ZIP_FIELD]')) {
					$zip = new ZipField();
					$zip->value = $_SESSION['sSesZip'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$zip->extra .= " disabled ";
					}
					$sUserForm = str_replace('[ZIP_FIELD]',$zip->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[GENDER_RADIO]')) {
					$gen = new GenderRadio();
					$gen->value = $_SESSION['sSesGender'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$gen->extra .= " disabled ";
					}
					$sUserForm = str_replace('[GENDER_RADIO]',$gen->html(),$sUserForm);
				}
				
				if (strstr($sUserForm,'[PHONE_GROUP]')) {
					$phone = new PhoneField();
					$phone->value = $_SESSION['sSesPhone'];
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$phone->extra .= " disabled ";
					}
					$sUserForm = str_replace('[PHONE_GROUP]',$phone->html(),$sUserForm);
				}
			
				if (strstr($sUserForm,'[DOB_GROUP]')) {
					$dob = new DOBField();
					if (!$_SESSION['sSesAllowEditUserForm']) {
						$dob->extra .= " disabled ";
					}
					$sUserForm = str_replace('[DOB_GROUP]',$dob->html($_SESSION['iSesBirthMonth']."/".$_SESSION['iSesBirthDay']."/".$_SESSION['iSesBirthYear']),$sUserForm);
				}

				$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_CENTER]-->", $sUserForm, $sOfferPage2Template);
				$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_RIGHT]-->", $sUserForm, $sOfferPage2Template);
				$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_LEFT]-->", $sUserForm, $sOfferPage2Template);
				$sOfferPage2Template = str_replace("<!--[USER_FORM_C_RIGHT]-->", $sUserForm, $sOfferPage2Template);
				$sOfferPage2Template = str_replace("<!--[USER_FORM_C_CENTER]-->", $sUserForm, $sOfferPage2Template);
				$sOfferPage2Template = str_replace("<!--[USER_FORM_C_LEFT]-->", $sUserForm, $sOfferPage2Template);
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

?>
<html>
<head>
<title><?php echo $_SESSION['aDefaultTitle'][$_SESSION['iSesCurrentPositionInFlow']]; ?></title>
<LINK rel="stylesheet" href="../pageStyles.css" type="text/css">


<style type="text/css">
	<?php echo $_SESSION['sSesCampaignCSS']; ?>
</style>

<SCRIPT LANGUAGE=JavaScript SRC="../libs/javaScriptFunctions.js" TYPE=text/javascript></script>
<script src="../libs/jsPopFuncs.js"></script>

<script type="text/javascript">
var form_submitted = false;
function submit_form () {
  if ( form_submitted ) {
    return false;
  } else {
    form_submitted = true;
    return true;
  }
}
</script>

<?php echo $_SESSION['sSesJavaScriptPrePop']; ?>

<?php echo $sPage2JavaScript; ?>
</head>

<body>
<form name=form1 action="owh.php" onSubmit="if(page2Validation()){return submit_form();} else {return false;}" method=post>
<?php echo $sHiddenSesField; ?>
<?php echo $_SESSION['sSesHiddenSourceCode']; ?>

<br><br>

<table class=table760 align=center cellpadding=0 cellspacing=0>
<?php echo $sOffersOnPage2;?>
</table>

<center>
	<input type="hidden" name='aOffersChecked["<?php echo $sOfferCode;?>"][]' value='<?php echo $sOfferCode;?>'>
	<input type="hidden" name=sOfferCode value=<?php echo $sOfferCode; ?>>
	<input type="submit" name="sSubmit" value="Submit" style="border-style:Double;">
	<input type="hidden" name="sSubmit" value="submit">
</center>

</body>
</html>

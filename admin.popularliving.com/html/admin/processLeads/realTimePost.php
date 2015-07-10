<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

session_start();
$sPageTitle = "Nibbles - Real Time Post / Email";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

if (hasAccessRight($iMenuId) || isAdmin()) {
	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if (!($sTodaysLeads || $sHistoryLeads)) {
		$iStartYear = date('Y');
		$iStartMonth = date('m');
		$iStartDay = date('d');
		$iEndMonth = $iStartMonth;
		$iEndDay = $iStartDay;
		$iEndYear = $iStartYear;
	}
	
	$sTodaysLeads = stripslashes($sTodaysLeads);
	
	if ($sTodaysLeads) {
		$iStartYear = date('Y');
		$iStartMonth = date('m');
		$iStartDay = date('d');
			
		$iEndMonth = $iStartMonth;
		$iEndDay = $iStartDay;
		$iEndYear = $iStartYear;
		
		$sOtDataTable = "otData";
		$sUserDataTable = "userData";
		
	} else if ($sHistoryLeads) {
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iEndMonth,$iEndDay,$iEndYear)) >= 0 || $iEndYear=='') {
			$iEndYear = substr( $sYesterday, 0, 4);
			$iEndMonth = substr( $sYesterday, 5, 2);
			$iEndDay = substr( $sYesterday, 8, 2);
		}
			
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iStartMonth,$iStartDay,$iStartYear)) >= 0 || $iStartYear=='') {
			$iStartYear = substr( $sYesterday, 0, 4);
			$iStartMonth = substr( $sYesterday, 5, 2);
			$iStartDay = "01";
		}
			
		$sOtDataTable = "otDataHistory";
		$sUserDataTable = "userDataHistory";
	}


	if ($sTodaysLeads || $sHistoryLeads) {
	
		$sStartDate = 	$iStartYear."-".$iStartMonth."-".$iStartDay." ".$iStartHour.":".$iStartMin.":".$iStartSec;
		$sEndDate = 	$iEndYear."-".$iEndMonth."-".$iEndDay." ".$iEndHour.":".$iEndMin.":".$iEndSec;
		$sOffersQuery = "SELECT * FROM offerLeadSpec WHERE offerCode = '$sOfferCode'";
		$rOffersResult = dbQuery($sOffersQuery);
		while ($oOfferRow = dbFetchObject($rOffersResult)) {
			echo '.';
			flush();
			ob_flush();
			
			$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
			$sOfferCode = $oOfferRow->offerCode;
			$sHttpPostString = $oOfferRow->httpPostString;
			$sPostingUrl = $oOfferRow->postingUrl;
			$sMainHttpPostString = $oOfferRow->httpPostString;
			$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
			$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
			$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
			$sSingleEmailBody = $oOfferRow->singleEmailBody;
					
			$sDeliveryMethodQuery = "SELECT * FROM deliveryMethods WHERE  id = '$iDeliveryMethodId'";
			$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
			while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
				$sHowSent = $oDeliveryMethodRow->shortMethod;
			}
	
			$sSelectQuery = "SELECT $sOtDataTable.*, first, last, address, address2, city, state, zip, phoneNo, gender, dateOfBirth
					 FROM	$sOtDataTable, $sUserDataTable
					 WHERE  $sOtDataTable.email = $sUserDataTable.email
					 AND    offerCode = '$sOfferCode'
					 AND 	$sOtDataTable.dateTimeAdded BETWEEN '$sStartDate' AND '$sEndDate'";

			if (!($iRerun)) {
				 $sSelectQuery .= "  AND    sendStatus IS NULL ";
			}

			$rSelectResult = dbQuery($sSelectQuery)  ;
			echo dbError();
			$iNumLeadsPosted = dbNumRows($rSelectResult);
			while ($oSelectRow= dbFetchObject($rSelectResult)) {
				echo '.';
				flush();
				ob_flush();
				
				$iOtDataId = $oSelectRow->id;
				$sPage2Data = $oSelectRow->page2Data;
				$aPage2DataArray = explode("|",$sPage2Data);
				
				
				$sEmail = $oSelectRow->email;
				$sSalutation = $oSelectRow->salutation;
				$sFirst = addslashes($oSelectRow->first);
				$sLast = addslashes($oSelectRow->last);
				$sAddress = addslashes($oSelectRow->address);
				$sAddress2 = addslashes($oSelectRow->address2);
				$sCity = $oSelectRow->city;
				$sState = $oSelectRow->state;
				$sZip = $oSelectRow->zip;
				$sPhone = $oSelectRow->phoneNo;
				$sRemoteIp = $oSelectRow->remoteIp;
				$sDateTimeAdded = $oSelectRow->dateTimeAdded;
				
				$sSourceCode = $oSelectRow->sourceCode;
				$sGender = $oSelectRow->gender;
				$iBirthYear = substr($oSelectRow->dateOfBirth,0,4);
				$iBirthMonth = substr($oSelectRow->dateOfBirth,5,2);
				$iBirthDay = substr($oSelectRow->dateOfBirth,8,2);
				
				
				$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));


				//wish I had seen this yesterday.
				$sSingleEmailBody = $oOfferRow->singleEmailBody;

				if (strlen($sPhone) == 10) {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,3,3);					
					$sPhone_number = substr($sPhone,6,4);
				} else {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,4,3);					
					$sPhone_number = substr($sPhone,8,4);
				}
	
				$sRealTimeResponse = '';
	
				if (!(substr(strtolower($sAddress),0,11) == '3401 dundee' && $sZip == '60062') ) {
					if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
						// 2 = real time form post - GET
						// 3 = real time form post - POST
						$aUrlArray = explode("//", $sPostingUrl);
						$sUrlPart = $aUrlArray[1];
						
						$sHttpPostString = ereg_replace("\[email\]",$sEmail, $sMainHttpPostString);
						$sHttpPostString = ereg_replace("\[first\]",$sFirst, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[last\]",$sLast, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[address\]",$sAddress, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[address2\]",$sAddress2, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[city\]",$sCity, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[state\]",$sState, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[zip\]",$sZip, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[zip5only\]",substr($sZip,0,5), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[phone\]",$sPhone, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[ipAddress\]",$sRemoteIp, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[phone_areaCode\]", $sPhone_areaCode, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[phone_exchange\]", $sPhone_exchange, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[phone_number\]", $sPhone_number, $sHttpPostString);
						$sHttpPostString = ereg_replace("\[mm\]", urlencode(substr($sDateTimeAdded,5,2)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[dd\]", urlencode(substr($sDateTimeAdded,8,2)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[yyyy\]", urlencode(substr($sDateTimeAdded,0,4)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[yy\]", urlencode(substr($sDateTimeAdded,2,2)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[hh\]", urlencode(substr($sDateTimeAdded,11,2)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[ii\]", urlencode(substr($sDateTimeAdded,14,2)), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[ss\]", urlencode(substr($sDateTimeAdded,17,2)), $sHttpPostString);
						
						$sHttpPostString = ereg_replace("\[salutation\]",urlencode($sSalutation), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[birthYear\]", urlencode($iBirthYear), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[birthMonth\]", urlencode($iBirthMonth), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[birthDay\]", urlencode($iBirthDay), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[gender\]", urlencode($sGender), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sHttpPostString);
						$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($sSourceCode), $sHttpPostString);
						
						$sTrackHttpPostInfo = "INSERT INTO tempHttpFormPostTracking
								( dateTimePosted, email, offerCode, httpPostString ) values
								( now(), \"$sEmail\", \"$sOfferCode\", \"".addslashes($sPostingUrl."?".$sHttpPostString)."\" )";
						$rTrackHttpPostResult = dbQuery($sTrackHttpPostInfo);
						echo dbError();
						
						if ($sOfferCode =='KPA_CONA') {
							mail('spatel@amperemedia.com','KPA_CONA',$sHttpPostString);
						}
					
					
						// replace page2 field values
						$sPage2MapQuery = "SELECT * FROM page2Map WHERE offerCode = '$sOfferCode' ORDER BY storageOrder";
						$rPage2MapResult = dbQuery($sPage2MapQuery);
						$f = 1;
						$ff=0;
						while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
							echo '.';
							flush();
							ob_flush();

							$sFieldVar = "FIELD".$f;
							$$sFieldVar = ereg_replace("\"","",$aPage2DataArray[$ff]);
							$sFieldVar2 = "field".$f;
							$$sFieldVar2 = ereg_replace("\"","",$aPage2DataArray[$ff]);
							$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sFieldVar), $sHttpPostString);
							$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",urlencode($$sFieldVar), $sSingleEmailBody);
							$sHttpPostString = eregi_replace("\[$sFieldVar2\]",urlencode($$sFieldVar2), $sHttpPostString);
							$sSingleEmailBody = eregi_replace("\[$sFieldVar2\]",urlencode($$sFieldVar2), $sSingleEmailBody);
							$f++;
							$ff++;
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
						
						echo "<br> $sOfferCode - socket ".$rSocketConnection;
						
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
							
							//mail('spatel@amperemedia.com',"fixing real time repost script","host: $sHostPart\n\n\nscript: $sScriptPath\n\n\nposting string: $sHttpPostString");

							echo '.';
							flush();
							ob_flush();
							if ($sOfferCode != 'VIDEPROF') {
								while(!feof($rSocketConnection)) {
									$sRealTimeResponse .= fgets($rSocketConnection, 1024);
								}
							}
							fclose($rSocketConnection);
						} else {
							echo "$errstr ($errno)<br />\r\n";
						}
	
						$sUpdateStatusQuery = "UPDATE $sOtDataTable
										   SET    processStatus = 'P',
												  sendStatus = 'S',
												  howSent = '$sHowSent',
												  dateTimeProcessed = dateTimeAdded,
												  dateTimeSent = dateTimeAdded,
												  realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
										   WHERE  id = '$iOtDataId'";
						$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
						echo dbError();
						
						
						// start of track users' activity in nibbles
						$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
								VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sUpdateStatusQuery) . "\")";
						$rLogResult = dbQuery($sLogAddQuery);
						// end of track users' activity in nibbles
						
						
						
						if($sOtDataTable == 'otDataHistory') {
							$sUpdateStatusQuery = "UPDATE otDataHistoryWorking
											   SET    processStatus = 'P',
													  sendStatus = 'S',
													  howSent = '$sHowSent',
													  dateTimeProcessed = dateTimeAdded,
													  dateTimeSent = dateTimeAdded,
													  realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
											   WHERE  id = '$iOtDataId'";
							//$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
							//echo dbError();
							
							// start of track users' activity in nibbles
							$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sUpdateStatusQuery) . "\")";
							$rLogResult = dbQuery($sLogAddQuery);
							// end of track users' activity in nibbles
						}
						
						echo '.';
						flush();
						ob_flush();
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
						
						echo '.';
						flush();
						ob_flush();
						
						// replace page2 field values
						$sPage2MapQuery = "SELECT * FROM page2Map WHERE offerCode = '$sOfferCode' ORDER BY storageOrder";
						$rPage2MapResult = dbQuery($sPage2MapQuery);
						$f = 1;
						$ff=0;
						while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
							$sFieldVar = "FIELD".$f;
							$$sFieldVar = ereg_replace("\"","",$aPage2DataArray[$ff]);
							$sFieldVar2 = "field".$f;
							$$sFieldVar2 = ereg_replace("\"","",$aPage2DataArray[$ff]);
							$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sFieldVar), $sHttpPostString);
							$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",($$sFieldVar), $sSingleEmailBody);
							$sHttpPostString = eregi_replace("\[$sFieldVar2\]",urlencode($$sFieldVar2), $sHttpPostString);
							$sSingleEmailBody = eregi_replace("\[$sFieldVar2\]",($$sFieldVar2), $sSingleEmailBody);
							$f++;
							$ff++;
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
						$sSingleEmailBody = ereg_replace("\[zip5only\]",substr($sZip,0,5), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[phone\]",$sPhone, $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[ipAddress\]",$sRemoteIp, $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[phone_areaCode\]", $sPhone_areaCode, $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[phone_exchange\]", $sPhone_exchange, $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[phone_number\]", $sPhone_number, $sSingleEmailBody);							
						$sSingleEmailBody = ereg_replace("\[mm\]", (substr($sDateTimeAdded,5,2)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[dd\]", (substr($sDateTimeAdded,8,2)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[yyyy\]", (substr($sDateTimeAdded,0,4)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[yy\]", (substr($sDateTimeAdded,2,2)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[hh\]", (substr($sDateTimeAdded,11,2)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[ii\]", (substr($sDateTimeAdded,14,2)), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[ss\]", (substr($sDateTimeAdded,17,2)), $sSingleEmailBody);
						
						$sSingleEmailBody = ereg_replace("\[birthYear\]", urlencode($iBirthYear), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[birthMonth\]", urlencode($iBirthMonth), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[birthDay\]", urlencode($iBirthDay), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[gender\]", urlencode($sGender), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sSingleEmailBody);
						$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($sSourceCode), $sSingleEmailBody);

						$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
						$sSingleEmailBody = '';
	
						for ( $x = 0; $x < count($aSingleEmailBodyArray); $x++ ) {
							$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
						}
	
						mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
	
						$sUpdateStatusQuery = "UPDATE $sOtDataTable
										   		SET   processStatus = 'P',
													  sendStatus = 'S',
													  dateTimeProcessed = dateTimeAdded,
												  	  dateTimeSent = dateTimeAdded,
												 	  howSent = '$sHowSent'
										 	  WHERE   id = '$iOtDataId'";
						$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
						echo dbError();
						
						// start of track users' activity in nibbles
						$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
								VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sUpdateStatusQuery) . "\")";
						$rLogResult = dbQuery($sLogAddQuery);
						// end of track users' activity in nibbles


						if($sOtDataTable == 'otDataHistory') {
							$sUpdateStatusQuery = "UPDATE otDataHistoryWorking
											   		SET   processStatus = 'P',
														  sendStatus = 'S',
														  dateTimeProcessed = dateTimeAdded,
													  	  dateTimeSent = dateTimeAdded,
													 	  howSent = '$sHowSent'
											 	  WHERE   id = '$iOtDataId'";
							//$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
							//echo dbError();
							
							// start of track users' activity in nibbles
							$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sUpdateStatusQuery) . "\")";
							$rLogResult = dbQuery($sLogAddQuery);
							// end of track users' activity in nibbles
						}
					}
				}
				
				echo '.';
				flush();
				ob_flush();
				sleep(3);
			} // end select
			
			echo '.';
			flush();
			ob_flush();
		} // end offer select
		
		
		$sMessage = "Total $iNumLeadsPosted Leads Attempted To Post";
		
		$sMsg = "This is for your information only.\n\n$sTrackingUser re-posted leads for $sOfferCode";
		$sMsg .= "\n\n\nQuery Used By The Script: $sSelectQuery ";
		$sMsg .= "\n\n\n$sMessage";
		
		mail('spatel@amperemedia.com',"FYI: $sOfferCode - Re-Posted Real Time Leads By: $sTrackingUser",$sMsg);

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"Re-Run RealTime Leads For $sOfferCode\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sSelectQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
	} /// end real time post
	
	
	
	// get the offers list which is not grouped
	$sOffersQuery = "SELECT *
				 	 FROM   offerLeadSpec
				 	 WHERE  deliveryMethodId IN ('2','3','4')				 	 
				 	 ORDER BY offerCode"; 
	$rOffersResult = dbQuery($sOffersQuery);
	echo dbError();
	$sOffersOptions .= "<option value=''>OfferCode";
	$sOffersOptions .= "<option value='allOffers'>All Offers";
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		if ($oOffersRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		$sOffersOptions .= "<option value='$oOffersRow->offerCode' $sOfferCodeSelected>$oOffersRow->offerCode";
	}
	

	// prepare month options for From and To date
	$sStartMonthOptions = '';
	$sEndMonthOptions = '';
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iStartMonth) {
			$sStartMonthSel = "selected";
		} else {
			$sStartMonthSel = "";
		}
		if ($iValue == $iEndMonth) {
			$sEndMonthSel = "selected";
		} else {
			$sEndMonthSel = "";
		}
		$sStartMonthOptions .= "<option value='$iValue' $sStartMonthSel>$aGblMonthsArray[$i]";
		$sEndMonthOptions .= "<option value='$iValue' $sEndMonthSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	$sStartDayOptions = "";
	$sEndDayOptions = "";
	for ($i = 1; $i <= 31; $i++) {
		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}
		if ($iValue == $iStartDay) {
			$sStartDaySel = "selected";
		} else {
			$sStartDaySel = "";
		}
		if ($iValue == $iEndDay) {
			$sEndDaySel = "selected";
		} else {
			$sEndDaySel = "";
		}		
		$sStartDayOptions .= "<option value='$iValue' $sStartDaySel>$i";
		$sEndDayOptions .= "<option value='$iValue' $sEndDaySel>$i";			
	}
	
	// prepare year options for From and To date
	$sStartYearOptions = "";
	$sEndYearOptions = "";
	for ($i = $iCurrYear-1; $i <= $iCurrYear+5; $i++) {
		if ($i == $iStartYear) {
			$sStartYearSel = "selected";
		} else {
			$sStartYearSel ="";
		}
		if ($i == $iEndYear) {
			$sEndYearSel = "selected";
		} else {
			$sEndYearSel = "";
		}
		$sStartYearOptions .= "<option value='$i' $sStartYearSel>$i";
		$sEndYearOptions .= "<option value='$i' $sEndYearSel>$i";	
	}
	
	
	$sStartHourOptions = "";
	$sEndHourOptions = "";
	for ($i = 0; $i < 24; $i++) {
		$iValue = $i;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iStartHour) {
			$sStartHourSel = "selected";
		} else {
			if($iValue == '00') {
				$sStartHourSel = "selected";
			} else {
				$sStartHourSel = "";
			}
		}
		if ($iValue == $iEndHour) {
			$sEndHourSel = "selected";
		} else {
			if($iValue == '23') {
				$sEndHourSel = "selected";
			} else {
				$sEndHourSel = "";
			}
		}
		$sStartHourOptions .= "<option value='$iValue' $sStartHourSel>$iValue";
		$sEndHourOptions .= "<option value='$iValue' $sEndHourSel>$iValue";
	}
	
	
	$sStartMinOptions = "";
	$sEndMinOptions = "";
	for ($i = 0; $i < 60; $i++) {
		$iValue = $i;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iStartMin) {
			$sStartMinSel = "selected";
		} else {
			if($iValue == '00') {
				$sStartMinSel = "selected";
			} else {
				$sStartMinSel = "";
			}
		}
		if ($iValue == $iEndMin) {
			$sEndMinSel = "selected";
		} else {
			if($iValue == '59') {
				$sEndMinSel = "selected";
			} else {
				$sEndMinSel = "";
			}
		}
		$sStartMinOptions .= "<option value='$iValue' $sStartMinSel>$iValue";
		$sEndMinOptions .= "<option value='$iValue' $sEndMinSel>$iValue";
	}
	
	
	$sStartSecOptions = "";
	$sEndSecOptions = "";
	for ($i = 0; $i < 60; $i++) {
		$iValue = $i;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iStartSec) {
			$sStartSecSel = "selected";
		} else {
			if($iValue == '00') {
				$sStartSecSel = "selected";
			} else {
				$sStartSecSel = "";
			}
		}
		if ($iValue == $iEndSec) {
			$sEndSecSel = "selected";
		} else {
			if($iValue == '59') {
				$sEndSecSel = "selected";
			} else {
				$sEndSecSel = "";
			}
		}
		$sStartSecOptions .= "<option value='$iValue' $sStartSecSel>$iValue";
		$sEndSecOptions .= "<option value='$iValue' $sEndSecSel>$iValue";
	}
	
	if ($iRerun) {
		$sRerunChecked = "checked";
	} else {
		$sRerunChecked = "";
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >
<input type=hidden name=iMenuId value='<?php echo $iMenuId;?>'>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><Td class=message align=center colspan=2><?php echo $sMessage;?></td></tr>
</table>

<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><BR>Only unprocessed real time leads will be posted unless Rerun is checked.<BR>Select date range on which leads were added.</td></tr>
<tr><td class=header>Offer Code</td><td>
	<select name=sOfferCode><?php echo $sOffersOptions;?>
	</select>
	</td>
</tr>
<tr><td class=header>Date Range</td><td>From: <select name=iStartMonth>
			<?php echo $sStartMonthOptions;?>
			</select> &nbsp;<select name=iStartDay>
			<?php echo $sStartDayOptions;?>
			</select> &nbsp;<select name=iStartYear>
			<?php echo $sStartYearOptions;?>
			</select>
			&nbsp;
			<select name=iStartHour>
			<?php echo $sStartHourOptions;?>
			</select>:
			<select name=iStartMin>
			<?php echo $sStartMinOptions;?>
			</select>:
			<select name=iStartSec>
			<?php echo $sStartSecOptions;?>
			</select>
			</td></tr>
			<tr><td></td><td>To: 
			<select name=iEndMonth>
			<?php echo $sEndMonthOptions;?>
			</select> &nbsp;<select name=iEndDay>
			<?php echo $sEndDayOptions;?>
			</select> &nbsp;<select name=iEndYear>
			<?php echo $sEndYearOptions;?>
			</select>
			&nbsp;
			<select name=iEndHour>
			<?php echo $sEndHourOptions;?>
			</select>:
			<select name=iEndMin>
			<?php echo $sEndMinOptions;?>
			</select>:
			<select name=iEndSec>
			<?php echo $sEndSecOptions;?>
			</select>
			
			</td></tr>
			<tr><td></td><td><input type=checkbox name=iRerun value='1' <?php echo $sRerunChecked;?>> Rerun Leads</td></tr>
<tr><td colspan=2 align=center><BR><BR><input type=submit name=sHistoryLeads value='Post History Leads'>  &nbsp; &nbsp; 
	<input type=submit name=sTodaysLeads value="Post Today's Leads"></td></tr>
</table>	
<?php

} else {
	echo "You are not authorized to access this page...";
}
?>

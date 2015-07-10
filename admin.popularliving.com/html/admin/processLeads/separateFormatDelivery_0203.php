<?php


	//if (!($sTestMode && $sTestProcessingEmailRecipients == '')) {

	$iTempPrevGroupId = 0;
	$sTempGrLeadFileName = '';
	// get all active offers which has separate lead file set

	$iCurrentRec = 0;
	$sOffersQuery = "SELECT offerLeadSpec.*
					 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					 WHERE  offers.offerCode = offerLeadSpec.offerCode
					 AND    activeDateTime <= now() 
					 AND    lastLeadDate >= CURRENT_DATE 
					 AND    (offerLeadSpec.separateLeadFile = '1' || leadGroups.separateLeadFile = '1') 
					 AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )";
	//AND    FIND_IN_SET(WEEKDAY(CURRENT_DATE),processingDays) > 0 ";
	
	if ($sProcessOption == "processOne") {
		if ($sOfferCode != '') {
			$sOffersQuery .= " AND  offers.offerCode='$sOfferCode'";
		} else if($iGroupId != '') {
			$sOffersQuery .= " AND offerLeadSpec.leadsGroupId = '$iGroupId'";
		} else {
			$sMessage = "You must select either an offer or a group to Process One...";
			$bKeepValues = "true";
			$sOffersQuery = '';
		}
	}
	
	// get the offer list/ one offer to get leads for
	if ($sOffersQuery != '') {
		
		$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
		$rOffersResult = dbQuery($sOffersQuery);
		$iNumRecords = dbNumRows($rOffersResult);
		
		echo dbError();
		//echo mysql_num_rows($rOffersResult);
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$iCurrentRec++;
			//echo "<BR>".$oOffersRow->leadsGroupId. " $oOffersRow->offerCode";
			// reset error message
			$sErrorInSendingLeads = '';
			$sLeadsData = '';
			$sLeadFileData = '';
			$sEmailMessage = '';
			
			$sTempOfferCode = $oOffersRow->offerCode;
			$sTempLeadsQuery = $oOffersRow->leadsQuery2;
			$iTempLeadsGroupId = $oOffersRow->leadsGroupId;
			$iTempMaxAgeOfLeads = $oOffersRow->maxAgeOfLeads;
			
			// get lead specific data from offerLeadSpec table
			$iTempDeliveryMethodId = $oOffersRow->deliveryMethodId2;
			$sTempProcessingDays = $oOffersRow->processingDays;
			$sTempPostingUrl = $oOffersRow->postingUrl2;
			$sTempFtpSiteUrl = $oOffersRow->ftpSiteUrl2;
			$sTempInitialFtpDirectory = $oOffersRow->initialFtpDirectory2;
			$iTempIsSecured = $oOffersRow->isSecured2;
			$sTempUserId = $oOffersRow->userId2;
			$sTempPasswd = $oOffersRow->passwd2;
			$sTempLeadFileName = $oOffersRow->leadFileName2;			
			$iTempIsEncrypted = $oOffersRow->isEncrypted2;
			$sTempEncMethod = $oOffersRow->encMethod2;
			$sTempEncType = $oOffersRow->encType2;
			$sTempEncKey = $oOffersRow->encKey2;
			$sTempHeaderText = $oOffersRow->headerText2;
			$sTempFooterText = $oOffersRow->footerText2;
			$sTempFieldDelimiter = $oOffersRow->fieldDelimiter2;
			$sTempFieldSeparater = $oOffersRow->fieldSeparater2;
			$sTempEndOfLine = $oOffersRow->endOfLine2;
			$sTempLeadsEmailSubject = $oOffersRow->leadsEmailSubject2;
			$sTempLeadsEmailFromAddr = $oOffersRow->leadsEmailFromAddr2;
			$sTempLeadsEmailBody = $oOffersRow->leadsEmailBody2;
			//$sTempSingleEmailFromAddr = $oOffersRow->singleEmailFromAddr2;
			//$sTempSingleEmailSubject = $oOffersRow->singleEmailSubject2;
			//$sTempSingleEmailBody = $oOffersRow->singleEmailBody;
			$sTempTestEmailRecipients = $oOffersRow->testEmailRecipients2;
			$sTempCountEmailRecipients = $oOffersRow->countEmailRecipients2;
			$sTempLeadsEmailRecipients = $oOffersRow->leadsEmailRecipients2;
			
			
			$sTempHowSent = '';
			
			$sDeliveryMethodQuery = "SELECT *
									 FROM   deliveryMethods
									 WHERE  id = '$iTempDeliveryMethodId'";
			$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
			while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
				$sTempHowSent = $oDeliveryMethodRow->shortMethod;
			}			
			
			if ($sTempLeadFileName != '') {
				
				$sTempLeadFileName = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempLeadFileName);
				
				if (strstr($sTempLeadFileName,"[d-")) {
					
					//get arithmetic number
					
					$iDateArithNum = substr($sTempLeadFileName,strpos($sTempLeadFileName,"[d-")+3,1);
					
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewShortYY = substr($sNewDate, 2, 2);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					
					$sTempLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempLeadFileName);
					
					$sDateArithString = substr($sTempLeadFileName, strpos($sTempLeadFileName,"[d-"),5);
					
					$sTempLeadFileName = str_replace($sDateArithString, "", $sTempLeadFileName);
					
				} else {
					$sTempLeadFileName = eregi_replace("\[dd\]", date(d), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[mm\]", date(m), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yy\]", date(y), $sTempLeadFileName);
				}
			}
						
			
					if ($sTempHeaderText != '') {
						
						$sTempHeaderText = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempHeaderText);
						
						if (strstr($sTempHeaderText,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempHeaderText,strpos($sTempHeaderText,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							//echo $sTempQuery. mysql_error();
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempHeaderText = eregi_replace("\[dd\]", $sNewDD, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", $sNewMM, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", $sNewYY, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", $sNewShortYY, $sTempHeaderText);
							
							$sDateArithString = substr($sTempHeaderText, strpos($sTempHeaderText,"[d-"),5);
							
							$sTempHeaderText = str_replace($sDateArithString, "", $sTempHeaderText);
							
						} else {
							$sTempHeaderText = eregi_replace("\[dd\]", date(d), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", date(m), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", date(Y), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", date(y), $sTempHeaderText);
						}
					}
					
			if ($sTempLeadsEmailSubject != '') {
				
				$sTempLeadsEmailSubject = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempLeadsEmailSubject);
				
				if (strstr($sTempLeadsEmailSubject,"[d-")) {
					
					//get date arithmetic number
					
					$iDateArithNum = substr($sTempLeadsEmailSubject,strpos($sTempLeadsEmailSubject,"[d-")+3,1);
					
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewYY = substr($sNewDate, 2, 2);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					
					$sTempLeadsEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempLeadsEmailSubject);
					
					$sDateArithString = substr($sTempLeadsEmailSubject, strpos($sTempLeadsEmailSubject,"[d-"),5);
					
					$sTempLeadsEmailSubject = str_replace($sDateArithString, "", $sTempLeadsEmailSubject);
					
				} else {
					
					$sTempLeadsEmailSubject = eregi_replace("\[dd\]", date(d), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[mm\]", date(m), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yy\]", date(y), $sTempLeadsEmailSubject);
				}
			}
			if ($sTempLeadsEmailBody != '') {
				$sTempLeadsEmailBody = eregi_replace("\[offerCode\]", $sTempOfferCode, $sTempLeadsEmailBody);
			}
			
					
			
			// get lead specific data from leadGroups table if offer is grouped
			// and lead group is not the same as previous loop
			//if ($iTempPrevGroupId != '' && $iTempLeadsGroupId != $iTempPrevGroupId) {
			
			if (($iTempPrevGroupId != 0 && $iTempPrevGroupId != $iTempLeadsGroupId && $iTempGrIsFileCombined && ($sTempGrHeaderText != '' || $sTempGrFooterText != '')) || $iCurrentRec == $iNumRecords) {
				
						$sTempData = '';
						
						$rFpGrLeadFileRead = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "r");
						
						
						if ($rFpGrLeadFileRead) {
							
							while (!feof($rFpGrLeadFileRead)) {
								$sTempData .= fread($rFpGrLeadFileRead, 1024);
							}
							
							fclose($rFpGrLeadFileRead);
						}
						
						// put header and footer
						if ($sTempGrHeaderText != '') {
							$sTempData = "$sTempGrHeaderText\r\n$sTempData";
						}
						if ($sTempGrFooterText != '') {
							$sTempData = "$sTempData\r\n$sTempGrFooterText";
						}
						
						// store data back in the file
						$rFpGrLeadFileWrite = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
						if ($rFpGrLeadFileWrite) {
							//$sTempData = "\\r\\n".$sTempData;
							fputs($rFpGrLeadFileWrite, $sTempData, strlen($sTempData));
							fclose($rFpGrLeadFileWrite);
						}
					} /// end of placing header and footer text in group file
					
					
					// get lead specific data from leadGroups table if offer is grouped
					// and lead group is not the same as previous loop
					if ($iTempLeadsGroupId != 0 && $iTempLeadsGroupId != $iTempPrevGroupId) {
						
						$sLeadsGroupQuery = "SELECT *
									FROM   leadGroups
									WHERE  id = '$iTempLeadsGroupId'";
						$rLeadsGroupResult = dbQuery($sLeadsGroupQuery);
						while ($oLeadsGroupRow = dbFetchObject($rLeadsGroupResult)) {
							$sTempGrName = $oLeadsGroupRow->name;
							$iTempGrDeliveryMethodId = $oLeadsGroupRow->deliveryMethodId2;							
							$sTempGrPostingUrl = $oLeadsGroupRow->postingUrl2;
							$sTempGrFtpSiteUrl = $oLeadsGroupRow->ftpSiteUrl2;
							$sTempGrInitialFtpDirectory = $oLeadsGroupRow->initialFtpDirectory2;
							$iTempGrIsSecured = $oLeadsGroupRow->isSecured2;
							$sTempGrUserId = $oLeadsGroupRow->userId2;
							$sTempGrPasswd = $oLeadsGroupRow->passwd2;
							$sTempGrLeadFileName = $oLeadsGroupRow->leadFileName2;
							$iTempGrIsFileCombined = $oLeadsGroupRow->isFileCombined2;
							$iTempGrIsEncrypted = $oLeadsGroupRow->isEncrypted2;
							$sTempGrEncMethod = $oLeadsGroupRow->encMethod2;
							$sTempGrEncType = $oLeadsGroupRow->encType2;
							$sTempGrEncKey = $oLeadsGroupRow->encKey2;
							$sTempGrHeaderText = $oLeadsGroupRow->headerText2;
							$sTempGrFooterText = $oLeadsGroupRow->footerText2;
														
							if ($sTempGrLeadFileName != '') {
								
								$sTempGrLeadFileName = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadFileName);
								
								//check if date should be different than current date in subject
								if (strstr($sTempGrLeadFileName,"[d-")) {
									
									//get arithmetic number
									
									$iDateArithNum = substr($sTempGrLeadFileName,strpos($sTempGrLeadFileName,"[d-")+3,1);
									
									$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
									$rTempResult = dbQuery($sTempQuery);
									while ($oTempRow = dbFetchObject($rTempResult)) {
										$sNewDate = $oTempRow->newDate;
									}
									
									$sNewYY = substr($sNewDate, 0, 4);
									$sNewShortYY = substr($sNewDate, 2, 2);
									$sNewMM = substr($sNewDate, 5, 2);
									$sNewDD = substr($sNewDate, 8, 2);
									
									$sTempGrLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadFileName);
									
									
									$sDateArithString = substr($sTempGrLeadFileName, strpos($sTempGrLeadFileName,"[d-"),5);
									
									
									$sTempGrLeadFileName = str_replace($sDateArithString, "", $sTempGrLeadFileName);
									
								} else {
									
									$sTempGrLeadFileName = eregi_replace("\[dd\]", date(d), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[mm\]", date(m), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yy\]", date(y), $sTempGrLeadFileName);
								}
							}
							
						}
					}
					
					
					
					if ($iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4') {
						// get last id reported
						$iTempLastIdReported = 0;
						$sTempLastIdQuery = "SELECT lastIdReported
											 FROM   realTimeDeliveryReporting
											 WHERE  offerCode = '$sTempOfferCode'
											 ORDER BY dateTimeSent DESC LIMIT 0,1";
						$rTempLastIdResult = dbQuery($sTempLastIdQuery);
						while ($oTempLastIdRow = dbFetchObject($rTempLastIdResult)) {
							$iTempLastIdReported = $oTempLastIdRow->lastIdReported;
						}
						//echo $sTempLastIdQuery.mysql_error().$iTempLastIdReported;
						$sTempLeadsQuery = eregi_replace("WHERE", "WHERE address NOT LIKE '3401 DUNDEE%' 
														  AND $sOtDataTable.id > '$iTempLastIdReported' AND ", $sTempLeadsQuery);
					
					} else {
						
						$sTempLeadsQuery = eregi_replace("WHERE", "WHERE (processStatus IS NULL || processStatus='P') 										 
										  AND date_format(dateTimeProcessed,'%Y-%m-%d') = CURRENT_DATE
										  AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
						
						if ($sTestMode) {
							$sTempLeadsQuery .= " AND    (sendStatus ='S' || sendStatus IS NULL)  ";
						} else {
							$sTempLeadsQuery .= " AND    sendStatus ='S' ";
							
						}
					}
					
					if ($sTempLeadsQuery != '') {
						if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
							$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id, ", $sTempLeadsQuery);
						} else {
							$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id, ", $sTempLeadsQuery);
						}
						if ($sOtDataTable == 'otData') {
							
							$sTempLeadsQuery = eregi_replace("otDataHistory", $sOtDataTable,$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("userDataHistory", $sUserDataTable,$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
						}
					}
					$rTempLeadsResult = dbQuery($sTempLeadsQuery);
					
					if (!($rTempLeadsResult)) {
						echo "<BR><BR><BR>$sTempOfferCode ".dbError();
					}					
					
		
					
					if (! $rTempLeadsResult) {
						
						echo "<BR>$sTempOfferCode Query Error: ".dbError();
						
					} else {
						$iNumFields = dbNumFields($rTempLeadsResult);
						$iLeadsCount = dbNumRows($rTempLeadsResult);

						$j = 1;
						$iLeadCounter = 1;
						$iDailyLeadCounter = 1;
						$sMutExclusiveQuery = "SELECT *
											   FROM   offersMutExclusive
											   WHERE  offerCode1 = '$sTempOfferCode'
											   OR     offerCode2 = '$sTempOfferCode'";							
						$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
						
						$sMutExclusiveOffers = '';
						if (dbNumRows($rMutExclusiveResult) > 0 ) {
							
							while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
								//echo $oMutExclusiveRow->offerCode1==$sTempOfferCode;
								if ($oMutExclusiveRow->offerCode1 == $sTempOfferCode) {
									
									$sMutExclusiveOffers .= "'". $oMutExclusiveRow->offerCode2."',";
								} else {
									
									$sMutExclusiveOffers .= "'".$oMutExclusiveRow->offerCode1."',";
								}
							}
						}
							
						if ($sMutExclusiveOffers != '') {
							$sMutExclusiveOffers = substr($sMutExclusiveOffers, 0, strlen($sMutExclusiveOffers)-1);											
						}
							
						if (!($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll") ){
								// get the offer count of this offer
								$sOfferCountQuery = "SELECT leadCounts, dailyLeadCounts
													 FROM   offerLeadsCount
													 WHERE  offerCode = '$sTempOfferCode'";
								$rOfferCountResult = dbQuery($sOfferCountQuery);
								echo dbError();
								while ($oOfferCountRow = dbFetchObject($rOfferCountResult)) {
									$iLeadCounter = $oOfferCountRow->leadCounts + 1;
									$iDailyLeadCounter = $oOfferCountRow->dailyLeadCounts +1;
								}
							}

						while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {
						
							$iId = $aTempLeadsRow['id'];
							$sTempLeadEmail = $aTempLeadsRow['email'];
							
							
							/************** mutually exclusive checking ****************/
							
														
								if ($sMutExclusiveOffers != '') {
													
									// check if this lead is delivered to any mutually exclusive offers
									$sMutCheckQuery = "SELECT *
													   FROM   otDataHistory
													   WHERE  email = '$sTempLeadEmail'
													   AND    offerCode IN (".$sMutExclusiveOffers.")
													   AND    sendStatus = 'S'";
									$rMutCheckResult = dbQuery($sMutCheckQuery);
									//echo $sMutCheckQuery.mysql_error();
									if (dbNumRows($rMutCheckResult) > 0) {
										// mark lead as rejected
										
										$sRejectMutExclQuery = "UPDATE $sOtDataTable
																SET    processStatus = 'R',
																		dateTimeProcessed = now(),	
																		reasonCode = 'meo'
																WHERE  id = '$iId'";
										echo "<BR>".$sRejectMutExclQuery;
										$rRejectMutExclResult = dbQuery($sRejectMutExclQuery);
										
										$iLeadsCount--;
										continue;
									}
								}
							
							
							/*****************************/

							// update process status and leadcounter only if lead not delivered real time
							if ($iTempDeliveryMethodId != '2' && $iTempDeliveryMethodId != '3' && $iTempDeliveryMethodId != '4') {
								$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable
																SET    processStatus = 'P',
																		dateTimeProcessed = now(),	
																		leadCounter = '$iLeadCounter',
																		dailyLeadCounter = '$iDailyLeadCounter'						
																WHERE  id = '$iId'
																AND    (processStatus IS NULL || processStatus='P')
																AND sendStatus IS NULL";
							
							//$rProcessStatusUpdateResult = mysql_query($sProcessStatusUpdateQuery);
							
							
							
							//echo "<BR>".$sProcessStatusUpdateQuery;
							echo dbError();
							
							//echo "<BR>0 field ".mysql_field_name($rTempLeadsResult, 0).$aTempLeadsRow[0];
							for ($i=1; $i < $iNumFields; $i++) {
								
								//if (testmode
								//if($i =='1') {
								//echo " <BR> $i ".mysql_field_name($rTempLeadsResult, $i) ." ".  $aTempLeadsRow[$i].$aTempLeadsRow['phoneNo']; 
								//}
								if (dbFieldName($rTempLeadsResult, $i) == 'leadCounter') {
									$sLeadsData .= $sTempFieldDelimiter.$iLeadCounter.$sTempFieldDelimiter;
								} else if (dbFieldName($rTempLeadsResult, $i) == 'dailyLeadCounter') {
									$sLeadsData .= $sTempFieldDelimiter.$iDailyLeadCounter.$sTempFieldDelimiter;
								} else {
									$sLeadsData .= $sTempFieldDelimiter.$aTempLeadsRow[$i].$sTempFieldDelimiter;
								}
								
								if (($i+1) != $iNumFields) {
									// put separater if this is not the last field
									switch($sTempFieldSeparater) {
										case "\\n":
										$sLeadsData .= chr(10);
										break;
										case "\\t":
										$sLeadsData .= chr(9);
										break;
										default:
										$sLeadsData .= $sTempFieldSeparater;
									}
								
									//$sLeadsData .= $sTempFieldSeparater;
								}
								
							} // end of for loop
							
							
							$iLeadCounter++;
							$iDailyLeadCounter++;
							
							// put end of line if this is the last field and not the last record
							if ($j < $iLeadsCount) {
								switch($sTempEndOfLine) {
									case "\\n":
									$sLeadsData .= chr(10);
									break;
									case "\\r\\n":
									$sLeadsData .= chr(13).chr(10);
									break;
									default:
									$sLeadsData .= $sTempEndOfLine;
								}
							}
							$j++;
							
							}
							
						} // end of leads data while loop
						
						echo "<BR>$sTempOfferCode - $iLeadsCount";
												
						// add header and footer text if file not grouped
						
						if ($sTempHeaderText != '') {
							$sLeadsData = "$sTempHeaderText\r\n$sLeadsData";
						}
						if ($sTempFooterText != '') {
							$sLeadsData .= "\r\n$sTempFooterText";
						}
						
						// create the folders if not exists
						if ( ! is_dir($sGblLeadFilesPath)) {
							mkdir($sGblLeadFilesPath, 0777);
							chmod($sGblLeadFilesPath, 0777);
						}
						
						if (! is_dir($sTodaysLeadsFolder)) {
							mkdir($sTodaysLeadsFolder, 0777);
							chmod($sTodaysLeadsFolder, 0777);
						}
						
						
						if (! is_dir("$sTodaysLeadsFolder/offers")) {
							mkdir("$sTodaysLeadsFolder/offers", 0777);
							chmod("$sTodaysLeadsFolder/offers", 0777);
						}
						
						if (! is_dir("$sTodaysLeadsFolder/offers/$sTempOfferCode")) {
							mkdir("$sTodaysLeadsFolder/offers/$sTempOfferCode", 0777);
							chmod("$sTodaysLeadsFolder/offers/$sTempOfferCode", 0777);
						}
						
						// create file and  store data in the file only if lead count is not 0
						
						
						$sTempLeadFileName = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadFileName);
						//$sTempLeadFileName = eregi_replace("\[count\]", date(y), $sTempLeadFileName);
					
						$rFpLeadFile = fopen("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", "w");
						if ($rFpLeadFile) {
							if ($iLeadsCount != 0) {
								fputs($rFpLeadFile, $sLeadsData, strlen($sLeadsData));
								
							}
							
							fclose($rFpLeadFile);
						} else {
							
						}
						
						// if offer is grouped, put the separate lead file of this offer in groups folder
						// or append to group file if it should be combined in one file
						
						if ($iTempLeadsGroupId) {
							
							//$iArrayCount = count($aOfferLeadCount);
							//$aGrOfferLeadCount[$iArrayCount][0] = $sTempOfferCode;
							//$aGrOfferLeadCount[$iArrayCount][1] = $iLeadsCount;
							
							// create the folders if not exists
							if (! is_dir($sGblLeadFilesPath)) {
								mkdir($sGblLeadFilesPath, 0777);
								chmod($sGblLeadFilesPath, 0777);
							}
							
							if (! is_dir("$sTodaysLeadsFolder/groups")) {
								mkdir("$sTodaysLeadsFolder/groups", 0777);
								chmod("$sTodaysLeadsFolder/groups", 0777);
							}
							
							if (! is_dir("$sTodaysLeadsFolder/groups/$sTempGrName")) {
								mkdir("$sTodaysLeadsFolder/groups/$sTempGrName", 0777);
								chmod("$sTodaysLeadsFolder/groups/$sTempGrName", 0777);
							}
							
							// copy data into group file if have to combine
							
							
							// if file not to combined. copy the lead file to group dir
							// if to combind, append lead file content to group lead file
							
							if ($iTempGrIsFileCombined) {
								if ($iTempLeadsGroupId != $iTempPrevGroupId) {
									// create new lead file for group when it comes for first time
									// otherwise will be appended again and again when we rerun the script
									$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
									
								} else {
									// open the file to append and set pointer to end of file, create the file if not exists
									$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "a");
									$sLeadsData = "\r\n".$sLeadsData;
								}
								
							} else {
								// copy lead file to group dir
								$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempLeadFileName", "w");
								
								
								//copy("$sGblLeadFilesPath/offers/$sTempOfferCode/$sTempLeadFileName", "$sGblLeadFilesPath/groups/$sTempGrName/$sTempLeadFileName");
								
							}
							
							if ($iLeadsCount != 0) {
								fputs($rFpLeadFile, $sLeadsData, strlen($sLeadsData));
							}
							fclose($rFpLeadFile);
							
						} // end of if($iTempLeadsGroupId)
						
						// store groupId now as previous groupId
						
						$iTempPrevGroupId = $iTempLeadsGroupId;
					}
					
				
		// If the last record was with groupId or processed for only one group, 
		// set header and footer in group lead file here 
		// because there was no NEXT record to decide that now the offer for a group are over and can put header and footer.
		//  only if file is combined and header/footer is not blank
			
			if ($iTempLeadsGroupId && $iTempGrIsFileCombined && ($sTempGrHeaderText != '' || $sTempGrFooterText != '')) {
				$sTempData = '';
				
				$rFpGrLeadFileRead = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "r");
				if ($rFpGrLeadFileRead) {
					
					while (!feof($rFpGrLeadFileRead)) {
						$sTempData .= fread($rFpGrLeadFileRead, 1024);
					}
					
					fclose($rFpGrLeadFileRead);
				}
				
				// put header and footer
				if ($sTempGrHeaderText != '') {
					$sTempData = "$sTempGrHeaderText\r\n$sTempData";
				}
				if ($sTempGrFooterText != '') {
					$sTempData = "$sTempData\r\n$sTempGrFooterText";
				}
				
				// store data back in the file
				$rFpGrLeadFileWrite = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
				if ($rFpGrLeadFileWrite) {
					fputs($rFpGrLeadFileWrite, $sTempData, strlen($sTempData));
					fclose($rFpGrLeadFileWrite);
				}
			} /// end of placing header and footer text in group file	
					
					
					
		//}
			
			
			
			
					
/**************************** send leads ***********************************/


			
			/// Before getting new group data,  send the leads of previous group
			// group details is already in the variables
			
			if (($iTempLeadsGroupId != 0  && $iTempPrevGroupId != $iTempLeadsGroupId) || $iNumRecords == $iCurrentRec) {
				echo "<BR>Sending group email for $iTempLeadsGroupId";
				$sLeadsGroupQuery = "SELECT *
									FROM   leadGroups
									WHERE  id = '$iTempLeadsGroupId'";
				$rLeadsGroupResult = dbQuery($sLeadsGroupQuery);
				while ($oLeadsGroupRow = dbFetchObject($rLeadsGroupResult)) {
					$sTempGrName = $oLeadsGroupRow->name;
					$iTempGrDeliveryMethodId = $oLeadsGroupRow->deliveryMethodId2;
					$sTempGrProcessingDays = $oLeadsGroupRow->processingDays2;
					$sTempGrPostingUrl = $oLeadsGroupRow->postingUrl2;
					$sTempGrFtpSiteUrl = $oLeadsGroupRow->ftpSiteUrl2;
					$sTempGrInitialFtpDirectory = $oLeadsGroupRow->initialFtpDirectory2;
					$iTempGrIsSecured = $oLeadsGroupRow->isSecured2;
					$sTempGrUserId = $oLeadsGroupRow->userId2;
					$sTempGrPasswd = $oLeadsGroupRow->passwd2;
					$sTempGrLeadFileName = $oLeadsGroupRow->leadFileName2;
					$iTempGrIsFileCombined = $oLeadsGroupRow->isFileCombined2;
					$iTempGrIsEncrypted = $oLeadsGroupRow->isEncrypted2;
					$sTempGrEncMethod = $oLeadsGroupRow->encMethod2;
					$sTempGrEncType = $oLeadsGroupRow->encType2;
					$sTempGrEncKey = $oLeadsGroupRow->encKey2;
					$sTempGrHeaderText = $oLeadsGroupRow->headerText2;
					$sTempGrFooterText = $oLeadsGroupRow->footerText2;
					$sTempGrLeadsEmailSubject = $oLeadsGroupRow->leadsEmailSubject2;
					$sTempGrLeadsEmailFromAddr = $oLeadsGroupRow->leadsEmailFromAddr2;
					$sTempGrLeadsEmailBody = $oLeadsGroupRow->leadsEmailBody2;
					$sTempGrTestEmailRecipients = $oLeadsGroupRow->testEmailRecipients2;
					$sTempGrCountEmailRecipients = $oLeadsGroupRow->countEmailRecipients2;
					$sTempGrLeadsEmailRecipients = $oLeadsGroupRow->leadsEmailRecipients2;	

					//echo $sTempGrLeadFileName;		
					$sTempHowSent = '';
					$sDeliveryMethodQuery = "SELECT *
									 FROM   deliveryMethods
									 WHERE  id = '$iTempGrDeliveryMethodId'";
					$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
					while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
						$sTempHowSent = $oDeliveryMethodRow->shortMethod;
					}					
					
				}
				
				if ($sTempGrLeadFileName != '') {
					
					$sTempGrLeadFileName = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadFileName);
					
					//check if date should be different than current date in subject
					if (strstr($sTempGrLeadFileName,"[d-")) {
						
						//get arithmetic number
						
						$iDateArithNum = substr($sTempGrLeadFileName,strpos($sTempGrLeadFileName,"[d-")+3,1);
						
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
						
						$sNewYY = substr($sNewDate, 0, 4);
						$sNewShortYY = substr($sNewDate, 2, 2);
						$sNewMM = substr($sNewDate, 5, 2);
						$sNewDD = substr($sNewDate, 8, 2);
						
						$sTempGrLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadFileName);
						
						
						$sDateArithString = substr($sTempGrLeadFileName, strpos($sTempGrLeadFileName,"[d-"),5);
						
						
						$sTempGrLeadFileName = str_replace($sDateArithString, "", $sTempGrLeadFileName);
						
					} else {
						
						$sTempGrLeadFileName = eregi_replace("\[dd\]", date(d), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[mm\]", date(m), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yy\]", date(y), $sTempGrLeadFileName);
					}
				} // end of if leadfilename != ''
				
				if ($sTempGrLeadsEmailSubject != '') {
					
					$sTempGrLeadsEmailSubject = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadsEmailSubject);
					
					//check if date should be different than current date in subject
					if (strstr($sTempGrLeadsEmailSubject,"[d-")) {
						
						//get arithmetic number
						
						$iDateArithNum = substr($sTempGrLeadsEmailSubject,strpos($sTempGrLeadsEmailSubject,"[d-")+3,1);
						
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
						
						$sNewYY = substr($sNewDate, 0, 4);
						$sNewShortYY = substr($sNewDate, 2, 2);
						$sNewMM = substr($sNewDate, 5, 2);
						$sNewDD = substr($sNewDate, 8, 2);
						
						$sTempGrLeadsEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadsEmailSubject);						
						$sTempGrLeadsEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadsEmailSubject);
						
						$sDateArithString = substr($sTempGrLeadsEmailSubject, strpos($sTempGrLeadsEmailSubject,"[d-"),5);						
						
						$sTempGrLeadsEmailSubject = str_replace($sDateArithString, "", $sTempGrLeadsEmailSubject);
						
					} else {
						
						$sTempGrLeadsEmailSubject = eregi_replace("\[dd\]", date(d), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[mm\]", date(m), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yy\]", date(y), $sTempGrLeadsEmailSubject);
					}
					
				} // end of leads subj != ''
				
				// get offercode wise count here
				$iGrLeadsCount = 0;
				$sTempGrLeadsEmailContent = '';
				$i = 0;
				$sGroupOffersCountQuery = "SELECT offerLeadSpec.offerCode, leadFileName, count($sOtDataTable.email) counts
												   FROM   $sUserDataTable, $sOtDataTable, offerLeadSpec 
												   WHERE  offerLeadSpec.offerCode = $sOtDataTable.offerCode 
												   AND    $sUserDataTable.email = $sOtDataTable.email 
												   AND    offerLeadSpec.leadsGroupId = '$iTempLeadsGroupId' 
												   AND	  processStatus = 'P'
												   AND	  verified != 'I'																								   
												   AND    date_format(dateTimeProcessed, '%Y-%m-%d') = CURRENT_DATE												   
												   AND postalVerified = 'V'  
												   AND   DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
												   AND address NOT LIKE '3401 DUNDEE%'";
				if ($sTestMode) {
					$sGroupOffersCountQuery .= " AND    (sendStatus ='S' || sendStatus IS NULL)  ";
				} else {
					$sGroupOffersCountQuery .= " AND    sendStatus ='S' ";
				}

				$sGroupOffersCountQuery .= " GROUP BY offerLeadSpec.offerCode";
								
				
				// don't check postal verification if testing from current table
				
				if ($sOtDataTable == 'otData') {
					
					$sGroupOffersCountQuery = eregi_replace("AND postalVerified = 'V'","", $sGroupOffersCountQuery);
					$sGroupOffersCountQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sGroupOffersCountQuery);
					$sGroupOffersCountQuery = eregi_replace("AND mode = 'A'","",$sGroupOffersCountQuery);
				}				
				//echo $sGroupOffersCountQuery;
													
				$rGroupOffersCountResult = dbQuery($sGroupOffersCountQuery);
				while ($oGroupOffersCountRow = dbFetchObject($rGroupOffersCountResult)) {
					$sTempGrOfferCode = $oGroupOffersCountRow->offerCode;
					$sTempGrLeadsEmailContent .=  "$sTempGrOfferCode - $oGroupOffersCountRow->counts\r\n";
					$iGrLeadsCount += $oGroupOffersCountRow->counts;
					
					$sTempFileName = $oGroupOffersCountRow->leadFileName;
					
					// replace variables in lead file name
					
					if ($sTempFileName != '' && $iTempGrIsFileCombined == '') {
						
						$sTempFileName = eregi_replace("\[offerCode\]",$sTempGrOfferCode, $sTempFileName);
						
						if (strstr($sTempFileName,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempFileName,strpos($sTempFileName,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempFileName = eregi_replace("\[dd\]", $sNewDD, $sTempFileName);
							$sTempFileName = eregi_replace("\[mm\]", $sNewMM, $sTempFileName);
							$sTempFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempFileName);
							$sTempFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempFileName);
							
							$sDateArithString = substr($sTempFileName, strpos($sTempFileName,"[d-"),5);
							
							$sTempFileName = str_replace($sDateArithString, "", $sTempFileName);
							
						} else {
							$sTempFileName = eregi_replace("\[dd\]", date(d), $sTempFileName);
							$sTempFileName = eregi_replace("\[mm\]", date(m), $sTempFileName);
							$sTempFileName = eregi_replace("\[yyyy\]", date(Y), $sTempFileName);
							$sTempFileName = eregi_replace("\[yy\]", date(y), $sTempFileName);
						}
						$aTempGrOfferLeadFiles[$i++] = $sTempFileName;
					}
				}
				
				$sTempGrLeadsEmailContent = "$sTempGrLeadsEmailContent\r\n"."Total Count - $iGrLeadsCount";
				$sTempGrLeadsEmailSubject = eregi_replace("\[count\]", "$iGrLeadsCount", $sTempGrLeadsEmailSubject);
				
				if ($sTempGrLeadsEmailBody != '') {
					$sTempGrLeadsEmailBody = eregi_replace("\[offerCode - count\]",$sTempGrLeadsEmailContent, $sTempGrLeadsEmailBody);
				}
				
				
				// if testing of lead delivery, then use the email address specified in leads processing screen
				
				if ($sTestMode == '') {
							
							$sTempGrLeadsEmailTo = $sTempGrLeadsEmailRecipients;
							
							// for count email
							
							$sTempGrCountEmailTo = $sTempGrCountEmailRecipients;							
							
						} else {
							
							$sTempGrLeadsEmailTo = $sTestProcessingEmailRecipients;
							// for count email
							$sTempGrCountEmailTo = $sTestProcessingEmailRecipients;
							
							
							
							// add "Test - " to subject line
							$sTempGrLeadsEmailSubject = "Test - ".$sTempGrLeadsEmailSubject;
						}						
						
						// replace count in email subject and email body
						
																		
						//	echo "<BR> $sTempOfferCode - $iLeadsCount - $sTempLeadFileName";
						
						// send leads data through specified delivery method
						// only if lead count is not 0
						
						if ($iGrLeadsCount != 0) {
							// send count email
						$sHeaders = "From: $sTempGrLeadsEmailFromAddr\n";
						//$sHeaders .= ": $sTempCountEmailCcTo\n";
						$sHeaders .= "Reply-To: $sTempGrLeadsEmailFromAddr\n";
						$sHeaders .= "X-Priority: 1\n";
						$sHeaders .= "X-MSMail-Priority: High\n";
						$sHeaders .= "X-Mailer: My PHP Mailer\n";
						
						if ($sTestMode) {
							$sDispGrCountEmailRecipients =  "Count Email Recipients: $sTempGrCountEmailRecipients\n\r\n\r";
							$sDispGrLeadsEmailRecipients =  "Leads Email Recipients: $sTempGrLeadsEmailRecipients\n\r\n\r";
						} else {
							$sDispGrCountEmailRecipients =  "";
							$sDispGrLeadsEmailRecipients =  "";
						}
						
						mail($sTempGrCountEmailTo, $sTempGrLeadsEmailSubject, $sDispGrCountEmailRecipients.$sTempGrLeadsEmailBody , $sHeaders);
						
						
							if ($iTempGrDeliveryMethodId == 1) {
																
								// If delivery method is 'FTP Daily Batch'
								
								/*$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
								FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
								UserId: $sTempUserId - Password: $sTempPasswd";
								*/
							} else if ($iTempGrDeliveryMethodId == 5) {
								// If delivery method is 'Daily Batch Form POST - GET'
								
								/*$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
								FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
								UserId: $sTempUserId - Password: $sTempPasswd";
								*/
							} else if ($iTempGrDeliveryMethodId == 7) {
								// If delivery method is 'Daily Batch Email'
								echo "send group leads email";
								$sHeaders = '';
								$sGrEmailMessage = '';
								$sGrLeadFileData = '';
								
								$sBorderRandom = md5(time());
								
								$sMailBoundry = "==x{$sBorderRandom}x";
								
								//$headers ="MIME-Version: 1.0\r\n";
								$sHeaders="From: $sTempGrLeadsEmailFromAddr\n";
								//$sHeaders.="To: $sTempLeadsEmailTo\n";
								//$sHeaders .= "cc: $sTempLeadsEmailCcTo\n";
								$sHeaders.="Reply-To: $sTempGrLeadsEmailFromAddr\n";
								$sHeaders.="X-Priority: 1\n";
								$sHeaders.="X-MSMail-Priority: High\n";
								$sHeaders.="X-Mailer: My PHP Mailer\n";
								$sHeaders.="Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
								
								$sHeaders .= "MIME-Version: 1.0\r\n";
								
								$sGrEmailMessage .= "This is a multi-part message in MIME format.\r\n\r\n";
								$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
								$sGrEmailMessage .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
								$sGrEmailMessage .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
								$sGrEmailMessage .= "$sDispGrLeadsEmailRecipients"."$sTempGrLeadsEmailBody\r\n\r\n";
								
								// get attachemnt file/files data
								// and attach one by one if there are more than one files in the folder
								
								$rFpGrLeadFilesDir = openDir("$sTodaysLeadsFolder/groups/$sTempGrName");
								
								if ($rFpGrLeadFilesDir) {
									
									if ($iTempGrIsFileCombined) {
										
										$sGrLeadFileData = "";
										
										$rFpGrLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName","r");
										
										if ($rFpGrLeadFile) {
											while (!feof($rFpGrLeadFile)) {
												$sGrLeadFileData .= fread($rFpGrLeadFile, 1024);
											}
											$sGrLeadFileData = base64_encode($sGrLeadFileData);
											$sGrLeadFileData = chunk_split($sGrLeadFileData);
											//echo $sTempLeadFileName;
											$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
											$sGrEmailMessage .= "Content-type: text/plain;  name=\"{$sTempGrLeadFileName}\"\r\n";
											$sGrEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
											$sGrEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempGrLeadFileName}\"\r\n\r\n";
											$sGrEmailMessage .= "$sGrLeadFileData\n";
											fclose($rFpGrLeadFile);
										} else {
											
											$sErrorInSendingLeads .= "$sTempGrName - Opening Lead File $sTempGrLeadFileName Failed<BR>";
										}										
										
									} else {
																				
										for ($i=0;$i<count($aTempGrOfferLeadFiles); $i++) {
											$sGrLeadFileData = "";
											$sTempLeadFileToAttach = $aTempGrOfferLeadFiles[$i];
											//echo "lead file ----- $sTodaysLeadsFolder/groups/$sTempGrName/$sTempLeadFileToAttach  --";
											$rFpGrLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempLeadFileToAttach","r");
											
											if ($rFpGrLeadFile) {
												while (!feof($rFpGrLeadFile)) {
													$sGrLeadFileData .= fread($rFpGrLeadFile, 1024);
												}
												$sGrLeadFileData = base64_encode($sGrLeadFileData);
												$sGrLeadFileData = chunk_split($sGrLeadFileData);
												//echo $sTempLeadFileName;
												$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
												$sGrEmailMessage .= "Content-type: text/plain;  name=\"{$sTempLeadFileToAttach}\"\r\n";
												$sGrEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
												$sGrEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempLeadFileToAttach}\"\r\n\r\n";
												$sGrEmailMessage .= "$sGrLeadFileData\n";
												fclose($rFpGrLeadFile);
											} else {
												$sErrorInSendingLeads .= "$sTempGrName - Opening Lead File $sTempLeadFileToAttach Failed<BR>";
											}
										}
									}							
								} // end if $rFpGrLeadFilesDir
								
								$sGrEmailMessage .= "--{$sMailBoundry}--\r\n";
								
								// send count
								
								//send lead data
								echo "sending lead data now";
								mail($sTempGrLeadsEmailTo, $sTempGrLeadsEmailSubject, $sGrEmailMessage , $sHeaders);
								
							} else if ($iTempDeliveryMethodId == 8) {
								// If delivery method is 'Upload In Browser'								
							}							
						}					
					
				}
				
				// get leads data for this offer
				
				//***** Temporary solution *******/
				// get id of the lead record and mark it processed, one by one until mysql upgraded
				// After mysql upgraded, use update with multiple table,
				// i.e. use same where condition as used for leads select query
				// get the id to update that ot data row
				
				
				// Important: replace following with just the count query when MySql is upgraded
				// and update processStatus one by one is not necessary

				if ($iTempLeadsGroupId == 0) {
				if ($sTestMode == '') {
							
							$sTempLeadsEmailTo = $sTempLeadsEmailRecipients;
							
							// for count email
							
							$sTempCountEmailTo = $sTempCountEmailRecipients;							
							
						} else {
							
							$sTempLeadsEmailTo = $sTestProcessingEmailRecipients;
							// for count email
							$sTempCountEmailTo = $sTestProcessingEmailRecipients;														
							
							// add "Test - " to subject line
							$sTempLeadsEmailSubject = "Test - ".$sTempLeadsEmailSubject;
							$sTempSingleEmailSubject = "Test - ".$sTempSingleEmailSubject;
						}
				}	
				
				$sTempLeadsQuery = eregi_replace( "WHERE", "WHERE processStatus='P' AND sendStatus ='S' AND
									date_format(dateTimeProcessed, '%Y-%m-%d') = CURRENT_DATE
								    AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
				

				if ($sTempLeadsQuery != '') {
					if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
							$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id, ", $sTempLeadsQuery);
						} else {
							$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id, ", $sTempLeadsQuery);
						}
					
					if ($sOtDataTable == 'otData') {
						$sTempLeadsQuery = eregi_replace("otDataHistory", $sOtDataTable,$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("userDataHistory", $sUserDataTable,$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
					}
				}				

				//echo $sTempLeadsQuery;
				//$sTempLeadsQuery .= " AND (processStatus IS NULL || processStatus='P') AND sendStatus IS NULL";
					
				$rTempLeadsResult = dbQuery($sTempLeadsQuery);
				//echo $sTempLeadsQuery.mysql_num_rows($rTempLeadsResult);
				$iLeadsCount = 0;
										
				if (! $rTempLeadsResult) {
					
					echo "<BR>$sTempOfferCode Query Error: $sTempLeadsQuery".dbError();
					
					
				} else {
					$iNumFields = dbNumFields($rTempLeadsResult);
					$iLeadsCount = dbNumRows($rTempLeadsResult);
					// update offerCount for this offer
										
					$iLastIdReported = 0;
					
					while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {
						
						$iTempId = $aTempLeadsRow['id'];
						$sTempLeadEmail = $aTempLeadsRow['email'];						
						
						// send lead emails here if lead delivery method is 
						// Single Email Delivery - Daily Batch
						
						if ($iTempDeliveryMethodId == 11) {
							$sHeaders = "From: $sTempSingleEmailFromAddr\n";								
							$sHeaders .= "Reply-To: $sTempSingleEmailFromAddr\n";
							$sSingleEmailHeaders = '';
							$sTempSingleEmailBodyRec = '';
							
							$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";

							$sTempSingleEmailBodyRec = eregi_replace("\[email\]",$aTempLeadsRow['email'], $sTempSingleEmailBody);
							$sTempSingleEmailBodyRec = eregi_replace("\[salutation\]",$aTempLeadsRow['salutation'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[first\]",$aTempLeadsRow['first'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[last\]",$aTempLeadsRow['last'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[address\]",$aTempLeadsRow['address'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[address2\]",$aTempLeadsRow['address2'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[city\]",$aTempLeadsRow['city'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[state\]",$aTempLeadsRow['state'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[zip\]",$aTempLeadsRow['zip'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone\]",$aTempLeadsRow['phoneNo'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_areaCode\]",$aTempLeadsRow['phone_areaCode'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_exchange\]",$aTempLeadsRow['phone_exchange'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_number\]",$aTempLeadsRow['phone_number'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[remoteIp\]",$aTempLeadsRow['remoteIp'], $sTempSingleEmailBodyRec);
							
							// get all the page2 fields of this offer and replace 
							$sPage2MapQuery = "SELECT *
											   FROM   page2Map
				 	 			   			   WHERE offerCode = '$sTempOfferCode'
				 				   			   ORDER BY storageOrder ";
		
							$rPage2MapResult = dbQuery($sPage2MapQuery);
							$f = 1;
							
							while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
																
								$sFieldVar = "FIELD".$f;
								
								$sTempSingleEmailBodyRec = eregi_replace("\[$sFieldVar\]",$aTempLeadsRow[$sFieldVar], $sTempSingleEmailBodyRec);
					
								$f++;
							}	

							
							$aTempSingleEmailBodyArray = explode("\\r\\n",$sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = "";
							
							for($x=0;$x<count($aTempSingleEmailBodyArray);$x++) {
								$sTempSingleEmailBodyRec .= $aTempSingleEmailBodyArray[$x]."\r\n";
							}
							mail($sTempLeadsEmailTo, $sTempSingleEmailSubject, $sTempSingleEmailBodyRec , $sHeaders);
							//echo "<BR>".$sTempSingleEmailBodyRec;
						}
						
						if ($iTempId > $iLastIdReported) {
							$iLastIdReported = $iTempId;
						}
						
					} // end of lead query while loop
					
					// insert lead counts and lastIdReported if leads were sent real time
					if ($sTestMode == '' && ( $iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4')) {
						
						$sLastIdReportedInsertQuery = "INSERT INTO realTimeDeliveryReporting(offerCode, counts, lastIdReported, dateTimeSent)
													   VALUES('$sTempOfferCode', '$iLeadsCount', '$iLastIdReported', now())";
						$rLastIdReportedInsertResult = dbQuery($sLastIdReportedInsertQuery);
					}
					
					
					// place lead count here, after while loop otherwise count will be wrong for mut. excl offer
					if ($sTempLeadsEmailSubject != '') {
						$sTempLeadsEmailSubject = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadsEmailSubject);
					}
					
					if ($sTempLeadFileName != '') {
						$sTempLeadFileName = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadFileName);
					}
					
					if ($sTempLeadsEmailBody != '') {
						$sTempLeadsEmailBody = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadsEmailBody);
					}
					
					
					// update process status of all the records
					/************ USE THE SAME CONDITION HERE AS USED IN OFFER QUERY *************/
					// SO THAT ONLY THOSE WILL BE MARKED AS 'PROCESSED' WHICH ARE PROCESSED
					
										// send the leads as per lead delivery method
					// send individual lead file if offer is not grouped
					// If offer is grouped, send the leads groupwise
					if ($iTempLeadsGroupId == 0) {
						
						// if testing of lead delivery, then use the email address specified in leads processing screen																			
						
						// send leads data through specified delivery method
						// only if lead count is not 0
						echo "<BR>Send: $sTempOfferCode $iLeadsCount $iTempDeliveryMethodId";
						if ($iLeadsCount != 0) {
							
							// send count email
						$sHeaders = "From: $sTempLeadsEmailFromAddr\n";
						//$sHeaders .= ": $sTempCountEmailCcTo\n";
						$sHeaders .= "Reply-To: $sTempLeadsEmailFromAddr\n";
						$sHeaders .= "X-Priority: 1\n";
						$sHeaders .= "X-MSMail-Priority: High\n";
						$sHeaders .= "X-Mailer: My PHP Mailer\n";
						if ($sTestMode) {
							$sDispCountEmailRecipients =  "Count Email Recipients: $sTempCountEmailRecipients\n\r\n\r";
							$sDispLeadsEmailRecipients =  "Leads Email Recipients: $sTempLeadsEmailRecipients\n\r\n\r";
						} else {
							$sDispCountEmailRecipients =  "";
							$sDispLeadsEmailRecipients =  "";
						}
						mail($sTempCountEmailTo, $sTempLeadsEmailSubject, $sDispCountEmailRecipients.$sTempLeadsEmailBody , $sHeaders);
							
							if ($iTempDeliveryMethodId == 1) {
								// If delivery method is 'FTP Daily Batch'
								
								//$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
									//			FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
										//		UserId: $sTempUserId - Password: $sTempPasswd";
								$rFtpConnection = 0;
								
								$rFtpConnection = ftp_connect($sTempFtpSiteUrl);
								
								if ($rFtpConnection) {
									echo "connected";
									$bFtpMode = ftp_pasv($rFtpConnection, false);
									$bFtpLogin = ftp_login($rFtpConnection, $sTempUserId, $sTempPasswd);
									if ($bFtpLogin) {
										echo "logged in";
										
										$bInitialFtpDirectory = ftp_chdir($rFtpConnection, $sTempInitialFtpDirectory);
										if ($bInitialFtpDirectory) {
											echo ftp_pwd($rFtpConnection);	
											//$contents = ftp_nlist($rFtpConnection, ".");

// output $contents
//var_dump($contents);
																				
											$bUploadFile = ftp_put($rFtpConnection, $sTempLeadFileName , "$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", FTP_ASCII);
											echo "upload ".$bUploadFile;
											if (!($bUploadFile)) {
												echo "error in uploading file";
											} else {
												echo "file uploaded";
											}
											
										} else {
											// error accessing initial FTP dir
											$sErrorInSendingLeads .= "<BR>$sTempOfferCode - Error accessing Initial FTP Directory";
											echo "error accessing initial dir";
										}
									}
									
									
   									ftp_close($rFtpConnection);

									
									
								} else {
									echo "not connected";
									
								}
								
								/*
								$sEncodedLeadsData = hex_encode(htmlspecialchars($sLeadsData));
								$rFtpConnection = ftp_connect($sTempFtpSiteUrl);
								
								if ($rFtpConnection) {
								echo "connected";
								
								ftp_close($rFtpConnection);
								} else {
								echo "FTP Error";
								}
								*/
								
							} else if ($iTempDeliveryMethodId == 7) {
								// If delivery method is 'Daily Batch Email'
								
								$sHeaders = '';
								$sEmailMessage = '';
								$sLeadFileData = '';
								
								$sBorderRandom = md5(time());
								
								$sMailBoundry = "==x{$sBorderRandom}x";
								
								//$headers ="MIME-Version: 1.0\r\n";
								$sHeaders="From: $sTempLeadsEmailFromAddr\n";
								//$sHeaders.="To: $sTempLeadsEmailTo\n";
								//$sHeaders .= "cc: $sTempLeadsEmailCcTo\n";
								$sHeaders.="Reply-To: $sTempLeadsEmailFromAddr\n";
								$sHeaders.="X-Priority: 1\n";
								$sHeaders.="X-MSMail-Priority: High\n";
								$sHeaders.="X-Mailer: My PHP Mailer\n";
								$sHeaders.="Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
								
								$sHeaders .= "MIME-Version: 1.0\r\n";
								
								$sEmailMessage .= "This is a multi-part message in MIME format.\r\n\r\n";
								$sEmailMessage .= "--{$sMailBoundry}\r\n";
								$sEmailMessage .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
								$sEmailMessage .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
								$sEmailMessage .= "$sDispLeadsEmailRecipients $sTempLeadsEmailBody\r\n\r\n";
								
								// get attachemnt file data
								$rFpLeadFile = fopen("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName","r");
								if ($rFpLeadFile) {
									while (!feof($rFpLeadFile)) {
										$sLeadFileData .= fread($rFpLeadFile, 1024);
									}
									fclose($rFpLeadFile);
									
								} else {
									echo " can't open lead file";
								}
								
								$sLeadFileData = base64_encode($sLeadFileData);
								$sLeadFileData = chunk_split($sLeadFileData);
								echo $sTempLeadFileName;
								$sEmailMessage .= "--{$sMailBoundry}\r\n";
								$sEmailMessage .= "Content-type: text/plain; \r\n";
								$sEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
								$sEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempLeadFileName}\"\r\n\r\n";
								$sEmailMessage .= "$sLeadFileData\n";
								$sEmailMessage .= "--{$sMailBoundry}--\r\n";
								// send count
								
								//send lead data
								
								mail($sTempLeadsEmailTo, $sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);
								
							} else if ($iTempDeliveryMethodId == 8) {
								// If delivery method is 'Upload In Browser'
								
							}
							
							
						} // if lead count != 0
						
						// end groupId == 0
					} 
					
										
				} // if get result of leads query
				
				// store groupId now as previous groupId
				
				$iTempPrevGroupId = $iTempLeadsGroupId;
			//	echo "Moved group Id to prevGroupId $iTempLeadsGroupId -> $iTempPrevGroupId";				
				
			} //offers while loop
			
		} // if offersQuery != ''
		
		
	//} // send leads
	
?>
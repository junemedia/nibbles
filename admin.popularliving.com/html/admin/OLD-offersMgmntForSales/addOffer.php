<?php

/*********

Script to Add/Edit Offer

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/validationFunctions.php");

session_start();


if (hasAccessRight($iMenuId) || isAdmin()) {

$sPageTitle = "Nibbles Offers - Add/Edit Offer";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if ($sTargetShowNoInfoAvailable == '') {
	$sTargetShowNoInfoAvailable = 'Y';
}

if ($sOpenOrLimited == '') {
	$sOpenOrLimited = 'O';
}

$iCurrYear = date(Y);
$iCurrMonth = date(m); //01 to 12
$iCurrDay = date(d); // 01 to 31

if ($sSaveClose || $sSaveNew || $sSaveContinue) { 

	// istarget should be Y or N, not empty
	if( $sIsTarget == '' ) {
		$sIsTarget = 'N';
	}
	
	// isOpenTheyHost should be Y or N, not empty
	if( $sIsOpenTheyHost == '' ) {
		$sIsOpenTheyHost = 'N';
	}
	
	//lower case
	$sTargetState = strtolower($sTargetState);
	$sTargetState = str_replace(" ", "", $sTargetState);
	$sTargetState = trim($sTargetState);
	
	// Set the active date and inactive date according to selection
	$sActiveDateTime = $iYearActive.$iMonthActive.$iDayActive.$iHourActive."0000";
	$sInactiveDateTime = $iYearInactive.$iMonthInactive.$iDayInactive.$iHourInactive."0000";
	
	$sLastLeadDate = "$iLastLeadYear-$iLastLeadMonth-$iLastLeadDay";
	
	if ($fRevPerLead == '') {
		$fRevPerLead = 0;
	}

	if ($fActualRevPerLead == '') {
		$fActualRevPerLead = $fRevPerLead;
	}
	
	// if delivery method is not single lead email, add leads@amperemedia.com as lead email recipient
	$sTempQuery = "SELECT *
				   FROM   offerLeadSpec
				   WHERE  offerCode = '$sOfferCode'";
	$rTempResult = dbQuery($sTempQuery);
	while ($oTempRow = dbFetchObject($rTempResult)) {
		$iTempDeliveryMethodId = $oTempRow->deliveryMethodId;
		
	}
	// force leads@amperemedia.com is recipient for lead and count emails
	if ( !strstr($sLeadsEmailRecipients,"leads@amperemedia.com") && $iTempDeliveryMethodId != '11') {
		if ($sLeadsEmailRecipients != '') {
			$sLeadsEmailRecipients .= ",";
		}
		$sLeadsEmailRecipients .= "leads@amperemedia.com";
	}
	if ( !strstr($sCountEmailRecipients,"leads@amperemedia.com")) {
		if ($sCountEmailRecipients != '') {
			$sCountEmailRecipients .= ",";
		}
		$sCountEmailRecipients .= "leads@amperemedia.com";
	}
		
	if (strlen($sOfferCode) > 25) {
		$sMessage = "OfferCode should be maximum 25 characters long...";
		$bKeepValues = true;
	} else if (!ereg("^[A-Za-z0-9_]+$", $sOfferCode)) {
		$sMessage = "OfferCode can contain only Alphabets, Numbers or _ ";
		$bKeepValues = true;
	} else if ($sName == '') {
		$sMessage = "Offer Name Should Not Be Blank...";
		$bKeepValues = true;
	} else if (!checkDate($iMonthActive, $iDayActive, $iYearActive) && checkdate($iMonthInactive, $iDayInactive,$iYearInactive)) {
		$sMessage = "Please Select Valid Dates...";
		$bKeepValues = true;
	} else if (DateDiff("d",mktime(0,0,0,$iMonthActive,$iDayActive,$iYearActive), mktime(0,0,0,$iMonthInactive,$iDayInactive,$iYearInactive))<1 && $iHourInactive < $iHourActive) {
		$sMessage = "Inactive Date Should Be Greater Than Active Date...";
		$bKeepValues = true;
	} else if (DateDiff("d",mktime(0,0,0,$iMonthInactive,$iDayInactive,$iYearInactive), mktime(0,0,0,$iLastLeadMonth,$iLastLeadDay,$iLastLeadYear))<1) {
		$sMessage = "Last Lead Date Should Be Greater Than Inactive Date...";
		$bKeepValues = true;
	} else if (!ereg("^[0-9\.]*$", $fRevPerLead)) {
		$sMessage = "Revenue Per Lead Can Contain Only Numbers or .";
		$bKeepValues = true;
	} else if (!ereg("^[0-9\.]*$", $fActualRevPerLead)) {
		$sMessage = "Actual Revenue Per Lead Can Contain Only Numbers or .";
		$bKeepValues = true;
	} else if ($iCompanyId == '') {
		$sMessage = "Please select offer company...";
		$bKeepValues = true;
	} else if (!checkDate($iCapStartMonth, $iCapStartDay, $iCapStartYear)) {
		$sMessage = "Please Select Valid Dates...";
		$bKeepValues = true;
	} else if ($iTargetStartYear > $iTargetEndYear) {
		$sMessage = "Target start date can't be later than end date";
		$bKeepValues = true;
	} else if ($sTargetStartZip > $sTargetEndZip) {
		$sMessage = "Target start zip can't be greater than end";
		$bKeepValues = true;
	} else if ($sTargetStartExchange > $sTargetEndExchange) {
		$sMessage = "Target start exchange can't be greater than end";
		$bKeepValues = true;
	} else if (!validateTargetState($sTargetState)) {
		$sMessage = "Target State Contains Invalid State/Characters.";
		$bKeepValues = true;
	} else if ( $sIsOpenTheyHost == 'Y' && $sTheyHostOfferURL == '') {
		$sMessage = "They Host Offer URL is required for TheyHost offers.";
		$bKeepValues = true;
	} else if ( $sIsOpenTheyHost == 'Y' && $sTheyHostContinueURL == '') {
		$sMessage = "They Host Continue URL is required for TheyHost offers.";
		$bKeepValues = true;
	} else if ( $sIsCoRegPopUp == 'Y' && $sCoRegPopUrl == '') {
		$sMessage = "CoReg Popup URL is required for CoReg offers.";
		$bKeepValues = true;
	} else if ( $sIsCloseTheyHost == 'Y' && $sCloseTheyHostUrl == '') {
		$sMessage = "'Close They Host Offer URL' Is Required For Close They-Host Offers.";
		$bKeepValues = true;
	} else if ( $sIsCloseTheyHost == 'Y' && $sIsCoRegPopUp == 'Y') {
		$sMessage = "Offer Cannot Be Close They-Host AND CoReg Popup.";
		$bKeepValues = true;
	} else {

		// check if offercode already exists
		
		$sCheckQuery = "SELECT *
						FROM   offers
						WHERE  offerCode = '$sOfferCode'";
		if ($iId)
		$sCheckQuery .= " AND id != '$iId'";
		
		$rCheckResult = dbQuery($sCheckQuery);

		
		// check if phantom page already exists (new offers only)
		if(!$iId)
		$sPhantomCheckQuery = "SELECT *
								FROM otPages
								WHERE pageName = 'th_" . $sOfferCode . "'";
		$rPhantomCheckResult = dbQuery($sPhantomCheckQuery);
		
		if ( dbNumRows($rCheckResult) > 0 ) {
			$sMessage = "Offercode already exists...";
			$bKeepValues = true;
		} else if(dbNumRows($rPhantomCheckResult) > 0) {
			$sMessage = "Offercode conflicts with an existing offer...";
			$bKeepValues = true;
		} else {
			
			// Prepare comma-separated pages if record added or edited
			
			/*$sPagesQuery = "SELECT id, pageName
					FROM   otPages
					ORDER BY pageName";*/
			$sPagesQuery = "SELECT pageId as id, pageName
							FROM   activePages
							UNION
							SELECT id, pageName
							FROM otPages
							WHERE pageName like 'test%'
							OR (date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
							AND date_add(CURRENT_DATE, INTERVAL -0 DAY))
							ORDER BY pageName";
			$rPagesResult = dbQuery($sPagesQuery);
			$i = 0;
			while ($oPagesRow = dbFetchObject($rPagesResult)) {
				
				// prepare Categories of this offer
				$sCheckboxName = "page_".$oPagesRow->id;
				
				$iCheckboxValue = $$sCheckboxName;
				
				if ($iCheckboxValue != '') {
					$aPagesArray[$i] = $iCheckboxValue;
					$sPagesString .= $iCheckboxValue.",";
					$i++;
				}
			}
			
			// Prepare comma-separated Categories if record added or edited
			
			$sCategoryQuery = "SELECT id, title
					FROM   categories
					ORDER BY title";
			$rCategoryResult = dbQuery($sCategoryQuery);
			$i = 0;
			while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
				
				// prepare Categories of this offer
				$sCheckboxName = "category_".$oCategoryRow->id;
				
				$iCheckboxValue = $$sCheckboxName;
				
				if ($iCheckboxValue != '') {
					$aCategoriesArray[$i] = $iCheckboxValue;
					$sCategoriesString .= $iCheckboxValue.",";
					$i++;
				}
			}
			
						
			
			if (!($iId)) {
				
				// create a targetData table for this offer if not created
				if ($sYearDatabase !='' || $sZipDatabase !='' || $sExchangeDatabase !='') {
					$sCreateNewTable = true;
					$sDbQuery = "SHOW TABLES FROM targetData";
					$rDbResult = mysql_query($sDbQuery);
				
					while ($sTableRow = mysql_fetch_row($rDbResult)) {
						if ($sOfferCode == $sTableRow) {
							$sCreateNewTable = false;
						}
					}
					
					if ($sCreateNewTable == true) {
						$sCreateTableQuery = "CREATE TABLE targetData.$sOfferCode (
								id INT(11) NOT NULL AUTO_INCREMENT,
								year INT(4) NOT NULL,
								zip VARCHAR(5) NOT NULL,
								exchange CHAR(3) NOT NULL,
								PRIMARY KEY (id), INDEX (year),
								INDEX (zip), INDEX (exchange))";
						$rNewTableResult = mysql_query($sCreateTableQuery);
					}
				}
				
				// if new data submitted
				$sHeadline = addslashes($sHeadline);
				$sDescription = addslashes($sDescription);
				$sShortDescription = addslashes($sShortDescription);
				$sNotes = addslashes($sNotes);
				$sRestrictions = addslashes($sRestrictions);
				$sAutoRespEmailSub = addslashes($sAutoRespEmailSub);
				$sAutoRespEmailBody = addslashes($sAutoRespEmailBody);
				$sHttpPostString = addslashes($sHttpPostString);
				$sHeaderText = addslashes($sHeaderText);
				$sFooterText = addslashes($sFooterText);
				$sLeadsQuery = addslashes($sLeadsQuery);
				$sLeadsEmailBody = addslashes($sLeadsEmailBody);
				$sLeadsEmailSubject = addslashes($sLeadsEmailSubject);
				$sSingleEmailSubject = addslashes($sSingleEmailSubject);
				$sSingleEmailBody = addslashes($sSingleEmailBody);
				$sLeadsInstruction = addslashes($sLeadsInstruction);

				//check if offercode exists
				$sCheckQuery = "SELECT *
				   		FROM offers
				   		WHERE offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) == 0) {
					$iNextYear = $iCurrYear + 1;

					if ($sTargetGender == '' && $iTargetStartYear == '' && $iTargetEndYear == '' && $sTargetStartZip == '' && $sTargetEndZip == '' && $sTargetStartExchange == '' && $sTargetEndExchange == '' && $sYearDatabase == '' && $sZipDatabase == '' && $sExchangeDatabase == '' && $sTargetState == '') {
						$sIsTarget = 'N';
					}
					
					// uncheck page2Info if coRegPopup is checked.
					if ($sIsCoRegPopUp == 'Y') { $iPage2Info = ''; }
					
					// uncheck page2Info if closeTheyHost is checked.
					if ($sIsCloseTheyHost == 'Y') { $iPage2Info = ''; }
					
					if ($sIsCoRegPopUp == '') { $sIsCoRegPopUp = 'N'; }
					
					if ($sIsCloseTheyHost == '') { $sIsCloseTheyHost = 'N'; }
					
					if ($sIsRequiredSSL == '') { $sIsRequiredSSL = 'N'; }
					
					if ($sIsAvailableForApi == '') { $sIsAvailableForApi = 'N'; }

					$sInsertQuery = "INSERT INTO offers(offerCode, companyId, name, headline,
								description, shortDescription, revPerLead, actualRevPerLead, autoRespEmail, autoRespEmailFormat, 
								autoRespEmailSub, autoRespEmailBody, autoRespEmailFromAddr, 
								notes, activeDateTime, inactiveDateTime, isCap, defaultSortOrder, isTarget, targetGender, targetStartYear, 
								targetEndYear, targetStartZip, targetEndZip, targetStartExchange, targetEndExchange, targetIncExcGender, targetIncExcYear, 
								targetIncExcZip, targetIncExcExchange, targetYearDatabase, targetZipDatabase, targetExchangeDatabase, targetState, 
								targetIncExcState, targetShowNoInfoAvailable, isOpenTheyHost, theyHostOfferURL, theyHostContinueURL, restrictions, 
								theyHostPassOnPrepopCodes, isRequireSSL, page2Info, isCoRegPopUp, coRegPopPassOnPrepopCodes, coRegPopPassOnCodeVarMap, 
								coRegPopUrl, coRegPopUpTriggerOn, isCloseTheyHost, closeTheyHostPrePop, closeTheyHostVarMap, closeTheyHostUrl, closeTheyHostTriggerOn, 
								isCloseTheyHostPixelEnable, isCoRegPopPixelEnable, isAvailableForApi, openOrLimited) 
					 	VALUES('$sOfferCode', '$iCompanyId', \"$sName\", \"$sHeadline\", \"$sDescription\", \"$sShortDescription\", 
								'$fRevPerLead', '$fActualRevPerLead', '$iAutoRespEmail', '$sAutoRespEmailFormat', \"$sAutoRespEmailSub\", \"$sAutoRespEmailBody\", \"$sAdutoRespEmailFromAddr\", 
								\"$sNotes\", '$sActiveDateTime', '$sInactiveDateTime', '$iIsCap', '$iDefaultSortOrder', '$sIsTarget', '$sTargetGender', '$iTargetStartYear', 
								'$iTargetEndYear', '$sTargetStartZip', '$sTargetEndZip', '$sTargetStartExchange', '$sTargetEndExchange', '$sTargetIncExcGender', '$sTargetIncExcYear', 
								'$sTargetIncExcZip', '$sTargetIncExcExchange', '$sYearDatabase', '$sZipDatabase', '$sExchangeDatabase', \"$sTargetState\", '$sTargetIncExcState', '$sTargetShowNoInfoAvailable',
								\"$sIsOpenTheyHost\", \"$sTheyHostOfferURL\", \"$sTheyHostContinueURL\", \"$sRestrictions\", \"$sPassOnPrepopCodes\", '$sIsRequiredSSL', 
								'$iPage2Info', '$sIsCoRegPopUp', '$sCoRegPopPassOnPrepopCodes', \"$sCoRegPopPassOnCodeVarMap\", \"$sCoRegPopUrl\", '$sCoRegTriggerOn', 
								'$sIsCloseTheyHost','$sCloseTheyHostPrePop',\"$sCloseTheyHostVarMap\",\"$sCloseTheyHostUrl\", '$sCloseTheyHostTriggerOn', 
								'$sCloseTheyHostPixelEnable', '$sCoRegPopPixelEnable', '$sIsAvailableForApi', '$sOpenOrLimited')";
					
					
					// start of track users' activity in nibbles
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add offer: " . addslashes($sInsertQuery) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
					// end of track users' activity in nibbles
										
					$rInsertResult = dbQuery($sInsertQuery);
					if (! $rInsertResult) {
						echo dbError();
					} else {

						// When inserted offer for the first time...new offer
						if ($sIsAvailableForApi == 'Y') {
							$sCheckQuery = "SELECT offerCompanies.repDesignated, companyName
									FROM   offers, offerCompanies
									WHERE  offers.companyId = offerCompanies.id
									AND    offers.offerCode = '$sOfferCode'";
							$rCheckResult = dbQuery($sCheckQuery);
							$iCheckNumRows = dbNumRows($rCheckResult);
							while ($oCheckRow = dbFetchObject($rCheckResult)) {
								$sCompanyName = $oCheckRow->companyName;
								$sRepQuery = "SELECT * FROM   nbUsers
										  WHERE  id IN (".$oCheckRow->repDesignated.")";
								$rRepResult = dbQuery($sRepQuery);
								$sOfferRep = '';
								while ($oRepRow = dbFetchObject($rRepResult)) {
									$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
								}
								if ($sOfferRep != '') {
									$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
								}
							}
							
							$sEmailHeaders = "From: ot@amperemedia.com\r\n";
							$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
							$sEmailSubject = "Offer Available For API - " . $sOfferCode;
							$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
							$sEmailMessage .= "OfferCode: $sOfferCode\r\n";
							$sEmailMessage .= "Offer Name: $sName\r\n";
							$sEmailMessage .= "Offer Company Name: $sCompanyName\r\n";
							$sEmailMessage .= "AE: $sOfferRep\r\n";
							$sEmailMessage .= "\r\nEffective Rate: $fRevPerLead";
							$sEmailMessage .= "\r\nPay Rate: $fActualRevPerLead\n\n".$sEmailSubject;

						
							$sEmailQuery = "SELECT * FROM emailRecipients 
										WHERE purpose = 'offers available for api' LIMIT 1";
							$rEmailResult = dbQuery($sEmailQuery);
							while ($oEmailRow = dbFetchObject($rEmailResult)) {
								mail($oEmailRow->emailRecipients, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
							}
						}
						
						
						$sCheckQuery = "SELECT id
								   FROM   offers
								   WHERE  offerCode = '$sOfferCode'"; 
						$rCheckResult = dbQuery($sCheckQuery);
						$sRow = dbFetchObject($rCheckResult);
						
						
						
						// get offerId to use in image name
						$iOfferId = $sRow->id;
						$iId = $iOfferId;
							
						// insert record into lead spec details	
						// set default values of the fields which are not given to sales persons
						// set default lead delivery method to Daily Batch Email
						$sLeadsQuery = "SELECT userDataHistoryWorking.email, first, last, address, address2, city, state, zip, phoneNo FROM userDataHistoryWorking, otDataHistoryWorking WHERE userDataHistoryWorking.email = otDataHistoryWorking.email AND otDataHistoryWorking.offerCode = '$sOfferCode' AND userDataHistoryWorking.postalVerified = 'V'  AND   DATE_ADD(date_format(otDataHistoryWorking.dateTimeAdded,'%Y-%m-%d'), INTERVAL $"."iTempMaxAgeOfLeads DAY) >= CURRENT_DATE ";
						$iDeliveryMethodId = '7';
						
						$sLeadFileName = "[offerCode]_[mm]_[dd]_[yyyy]_Ampere.csv";						
						$sLeadsEmailFromAddr = "Ampere Media Leads <leads@AmpereMedia.com>";
						$sLeadsEmailSubject = "Ampere Media - [offerCode], [count] [mm]-[dd]-[yyyy]";
						$sLeadsEmailBody = "[offerCode] - [count]";
						$sSingleEmailSubject = "Ampere Media - [offerCode], [mm]-[dd]-[yyyy]";
						$sSingleEmailFromAddr = "Ampere Media Lead <leads@AmpereMedia.com>";
						$iMaxAgeOfLeads = 30;
						$iNextYear = $iCurrYear + 1;
												
						$sLeadSpecInsertQuery = "INSERT INTO offerLeadSpec(offerCode, leadsGroupId, lastLeadDate,
													 processingDays, deliveryMethodId, maxAgeOfLeads,
													postingUrl, httpPostString, ftpSiteUrl, initialFtpDirectory, userId, passwd, leadFileName,
													isEncrypted, encMethod, encType, encKey, headerText, footerText, leadsQuery,
													fieldDelimiter, fieldSeparater, endOfLine, leadsEmailSubject, leadsEmailFromAddr, leadsEmailBody, 
													singleEmailSubject, singleEmailFromAddr, singleEmailBody, testEmailRecipients,
													countEmailRecipients, leadsEmailRecipients, leadsInstruction, separateLeadFile)
												 VALUES('$sOfferCode', '$iLeadsGroupId', '$sLastLeadDate',
													'$sProcessingDays', '$iDeliveryMethodId', '$iMaxAgeOfLeads',
													\"$sPostingUrl\", \"$sHttpPostString\", '$sFtpSiteUrl', \"$sInitialFtpDirectory\", '$sUserId', '$sPasswd', '$sLeadFileName',
													'$iIsEncrypted', '$sEncMethod', '$sEncType', \"$sEncKey\", \"$sHeaderText\", \"$sFooterText\", \"$sLeadsQuery\",
													\"$sFieldDelimiter\", \"$sFieldSeparater\", \"$sEndOfLine\", \"$sLeadsEmailSubject\", \"$sLeadsEmailFromAddr\",\"$sLeadsEmailBody\", 
													\"$sSingleEmailSubject\", \"$sSingleEmailFromAddr\", \"$sSingleEmailBody\", \"$sTestEmailRecipients\",
													\"$sCountEmailRecipients\", \"$sLeadsEmailRecipients\", \"$sLeadsInstruction\",\"$iSeparateLeadFile\")";

						$rLeadSpecInsertResult = dbQuery($sLeadSpecInsertQuery);
						//echo $sLeadSpecInsertQuery;
						echo dbError();
						
						
								
						// insert record into capCounts
						if ($iIsCap) {
							$sCapStartDate = "$iCapStartYear-$iCapStartMonth-$iCapStartDay";
							
							$sCapInsertQuery = "INSERT INTO capCounts(offerCode, capStartDate, maxCap, cap1PeriodType,
												cap1PeriodInterval, cap1Max, cap2PeriodType, cap2PeriodInterval, cap2Max)
									VALUES('$sOfferCode', '$sCapStartDate', '$iMaxCap', '$sCap1PeriodType',
											'$iCap1PeriodInterval', '$iCap1Max', '$sCap2PeriodType', '$iCap2PeriodInterval', '$iCap2Max')";
							$rCapInsertResult = dbQuery($sCapInsertQuery);
							echo dbError();
						}
						
						
						// Insert into PageMap according to page checkboxes checked
						if (count($aPagesArray) > 0) {
							for ($i = 0; $i < count($aPagesArray); $i++) {
								$sInsertQuery = "INSERT INTO pageMap(offerCode, pageId, sortOrder)
									VALUES('$sOfferCode', '".$aPagesArray[$i]."','0')";
								$rInsertResult = dbQuery($sInsertQuery);
								if (!($rInsertResult)) {
									echo dbError();
								}
							}
						}
						
						// Insert into categoryMap according to category checkboxes checked
						if (count($aCategoriesArray) > 0) {
							for ($i = 0; $i < count($aCategoriesArray); $i++) {
								$sInsertQuery = "INSERT INTO categoryMap(offerCode, categoryId)
									VALUES('$sOfferCode', '".$aCategoriesArray[$i]."')";
								$rInsertResult = dbQuery($sInsertQuery);
								if (!($rInsertResult)) {
									echo dbError();
								}
							}
						}
						
						
						// insert phantom page into otPages table (co-reg popup only)
						if($sIsCoRegPopUp == 'Y' ) {
							$sPhantomInsertQuery = "INSERT INTO otPages (pageName)
										VALUES ('coReg_" . $sOfferCode . "')";
							$rPhantomInsertResult = dbQuery($sPhantomInsertQuery);
							if (!($rPhantomInsertResult)) { echo dbError(); }
						}
						
						
						// insert phantom page into otPages table (close they host offers)
						if($sIsCloseTheyHost == 'Y' ) {
							$sPhantomInsertQuery = "INSERT INTO otPages (pageName)
										VALUES ('cth_" . $sOfferCode . "')";
							$rPhantomInsertResult = dbQuery($sPhantomInsertQuery);
							if (!($rPhantomInsertResult)) { echo dbError(); }
						}
						

						// insert phantom page into otPages table (theyhost only)
						if($sIsOpenTheyHost == 'Y' ) {
							$sPhantomInsertQuery = "INSERT INTO otPages (pageName)
										VALUES ('th_" . $sOfferCode . "')";
							$rPhantomInsertResult = dbQuery($sPhantomInsertQuery);
	
							if (!($rPhantomInsertResult)) {
								echo dbError();
							}
						}
						$sMessage = "Offer Added successfully...";
					}
				} else {
					$sMessage = "OfferCode Exists... $sOfferCode";
					$bKeepValues = true;
				}
				
			} else {
				// If record edited
				
				// insert phantom page into otPages table (co-reg popup only)
				if($sIsCoRegPopUp == 'Y' ) {
					$sTempCorRegPageName = "coReg_".$sOfferCode;
					$sCheckOtPageCoReg = "SELECT * FROM otPages WHERE pageName='$sTempCorRegPageName'";
					$rCheckOtPageCoRegResult = dbQuery($sCheckOtPageCoReg);
					if (dbNumRows($rCheckOtPageCoRegResult) == 0) {
						$sPhantomInsertQuery = "INSERT INTO otPages (pageName)
									VALUES ('coReg_" . $sOfferCode . "')";
						$rPhantomInsertResult = dbQuery($sPhantomInsertQuery);
						if (!($rPhantomInsertResult)) {	echo dbError(); }
					}
				}
					
					
				// insert phantom page into otPages table (close they host offers)
				if($sIsCloseTheyHost == 'Y' ) {
					$sTempCloseTheyHostPageName = "cth_".$sOfferCode;
					$sCheckOtPageCloseTheyHost = "SELECT * FROM otPages WHERE pageName='$sTempCloseTheyHostPageName'";
					$rCheckOtPageCloseTheyHost = dbQuery($sCheckOtPageCloseTheyHost);
					if (dbNumRows($rCheckOtPageCloseTheyHost) == 0) {
						$sPhantomInsertQuery = "INSERT INTO otPages (pageName)
									VALUES ('cth_" . $sOfferCode . "')";
						$rPhantomInsertResult = dbQuery($sPhantomInsertQuery);
						if (!($rPhantomInsertResult)) {	echo dbError(); }
					}
				}
				
				
	
				// create a targetData table for this offer if not created
				if ($sYearDatabase !='' || $sZipDatabase !='' || $sExchangeDatabase !='') {
					$sCreateNewTable = true;
					$sDbQuery = "SHOW TABLES FROM targetData";
					$rDbResult = mysql_query($sDbQuery);
				
					while ($sTableRow = mysql_fetch_row($rDbResult)) {
						if ($sOfferCode == $sTableRow) {
							$sCreateNewTable = false;
						}
					}
					
					if ($sCreateNewTable == true) {
						$sCreateTableQuery = "CREATE TABLE targetData.$sOfferCode (
								id INT(11) NOT NULL AUTO_INCREMENT,
								year INT(4) NOT NULL,
								zip VARCHAR(5) NOT NULL,
								exchange CHAR(3) NOT NULL,
								PRIMARY KEY (id), INDEX (year),
								INDEX (zip), INDEX (exchange))";
						$rNewTableResult = mysql_query($sCreateTableQuery);
					}
				}
				
				
				$sCheckQuery = "SELECT offers.*, offerCompanies.companyName, offerCompanies.repDesignated
								FROM   offers, offerCompanies
								WHERE  offers.companyId = offerCompanies.id
								AND    offers.offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				$iCheckNumRows = dbNumRows($rCheckResult);
				while ($oCheckRow = dbFetchObject($rCheckResult)) {
					$sOldMode = $oCheckRow->mode;					
					$iOldIsLive = $oCheckRow->isLive;
					$fOldActualRevPerLead = $oCheckRow->actualRevPerLead;
					$fOldRevPerLead = $oCheckRow->revPerLead;
					$sCompanyName = $oCheckRow->companyName;
					$sOfferName = $oCheckRow->name;
					$sRepDesignated = $oCheckRow->repDesignated;
					
					$sRepQuery = "SELECT *
								  FROM   nbUsers
								  WHERE  id IN (".$sRepDesignated.")";
					$rRepResult = dbQuery($sRepQuery);
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
					}
					if ($sOfferRep != '') {
						$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
					}
				}
				
				if ($sIsAvailableForApi == '') { $sIsAvailableForApi = 'N'; }
								
				$sGetDataQuery = "SELECT isAvailableForApi FROM offers WHERE offerCode='$sOfferCode'";
				$rGetDataResult = dbQuery($sGetDataQuery);
				while ($oRow1 = dbFetchObject($rGetDataResult)) {
					$sOldIsAvailableForApi = $oRow1->isAvailableForApi;
				}
				
				if ($sIsAvailableForApi == 'Y' && $sOldIsAvailableForApi == 'N') {
					$sEmailSubject = "Offer Available For API - " . $sOfferCode;
				
					$sEmailHeaders = "From: ot@amperemedia.com\r\n";
					$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
					$sEmailMessage .= "OfferCode: $sOfferCode\r\n";
					$sEmailMessage .= "Offer Name: $sName\r\n";
					$sEmailMessage .= "Offer Company Name: $sCompanyName\r\n";
					$sEmailMessage .= "AE: $sOfferRep\r\n";
					$sEmailMessage .= "\r\nEffective Rate: $fRevPerLead";
					$sEmailMessage .= "\r\nPay Rate: $fActualRevPerLead\n\n".$sEmailSubject;

					$sEmailQuery = "SELECT * FROM emailRecipients 
								WHERE purpose = 'offers available for api' LIMIT 1";
					$rEmailResult = dbQuery($sEmailQuery);
					while ($oEmailRow = dbFetchObject($rEmailResult)) {
						mail($oEmailRow->emailRecipients, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
					}
				}
				
				
				if ($sIsAvailableForApi == 'N' && $sOldIsAvailableForApi == 'Y') {
					$sEmailSubject = "Offer No Longer Available For API - " . $sOfferCode;
			
					$sEmailHeaders = "From: ot@amperemedia.com\r\n";
					$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
					$sEmailMessage .= "OfferCode: $sOfferCode\r\n";
					$sEmailMessage .= "Offer Name: $sName\r\n";
					$sEmailMessage .= "Offer Company Name: $sCompanyName\r\n";
					$sEmailMessage .= "AE: $sOfferRep\r\n";
					$sEmailMessage .= "\r\nEffective Rate: $fRevPerLead";
					$sEmailMessage .= "\r\nPay Rate: $fActualRevPerLead\n\n".$sEmailSubject;

					$sEmailQuery = "SELECT * FROM emailRecipients 
								WHERE purpose = 'offers available for api' LIMIT 1";
					$rEmailResult = dbQuery($sEmailQuery);
					while ($oEmailRow = dbFetchObject($rEmailResult)) {
						mail($oEmailRow->emailRecipients, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
					}
				}



				$sHeadline = addslashes($sHeadline);
				$sDescription = addslashes($sDescription);
				$sShortDescription = addslashes($sShortDescription);
				$sNotes = addslashes($sNotes);
				$sRestrictions = addslashes($sRestrictions);
				$sAutoRespEmailSub = addslashes($sAutoRespEmailSub);
				$sAutoRespEmailBody = addslashes($sAutoRespEmailBody);
				$sAutoRespEmailFromAddr = addslashes($sAutoRespEmailFromAddr);
				
				if ($sTargetGender == '' && $iTargetStartYear == '' && $iTargetEndYear == '' && $sTargetStartZip == '' && $sTargetEndZip == '' && $sTargetStartExchange == '' && $sTargetEndExchange == '' && $sYearDatabase == '' && $sZipDatabase == '' && $sExchangeDatabase == '' && $sTargetState == '') {
					$sIsTarget = 'N';
				}
				
				// uncheck page2Info if coRegPopup is checked.
				if ($sIsCoRegPopUp == 'Y') { $iPage2Info = ''; }
				
				// uncheck page2Info if closeTheyHost is checked.
				if ($sIsCloseTheyHost == 'Y') { $iPage2Info = ''; }
				
				if ($sIsCoRegPopUp == '') { $sIsCoRegPopUp = 'N'; }
					
				if ($sIsCloseTheyHost == '') { $sIsCloseTheyHost = 'N'; }
				
				if ($sIsRequiredSSL == '') { $sIsRequiredSSL = 'N'; }
				
				$sEditQuery = "UPDATE offers
					   SET    companyId = '$iCompanyId', 
							  name = '$sName',
							  headline = \"$sHeadline\",
							  description = \"$sDescription\",		
							  shortDescription = \"$sShortDescription\",
							  revPerLead = '$fRevPerLead',
							  actualRevPerLead = '$fActualRevPerLead',
							  autoRespEmail = '$iAutoRespEmail',
							  autoRespEmailFormat = '$sAutoRespEmailFormat',
							  autoRespEmailSub = \"$sAutoRespEmailSub\",
							  autoRespEmailBody = \"$sAutoRespEmailBody\",
							  autoRespEmailFromAddr = \"$sAutoRespEmailFromAddr\",					  
							  notes = \"$sNotes\",
							  isCap = '$iIsCap',
							  activeDateTime = '$sActiveDateTime',
							  inactiveDateTime = '$sInactiveDateTime',
							  defaultSortOrder = '$iDefaultSortOrder',
							  isTarget = '$sIsTarget',
							  targetGender = '$sTargetGender',
							  targetStartYear = '$iTargetStartYear',
							  targetEndYear = '$iTargetEndYear',
							  targetStartZip = '$sTargetStartZip',
							  targetEndZip = '$sTargetEndZip',
							  targetStartExchange = '$sTargetStartExchange',
							  targetEndExchange = '$sTargetEndExchange',
							  targetIncExcGender = '$sTargetIncExcGender',
							  targetIncExcYear = '$sTargetIncExcYear',
							  targetIncExcZip = '$sTargetIncExcZip',
							  targetIncExcExchange = '$sTargetIncExcExchange',
							  targetYearDatabase = '$sYearDatabase',
							  targetZipDatabase = '$sZipDatabase',
							  targetExchangeDatabase = '$sExchangeDatabase',
							  targetState = \"$sTargetState\",
							  targetIncExcState = '$sTargetIncExcState',
							  targetShowNoInfoAvailable = '$sTargetShowNoInfoAvailable',
							  theyHostOfferURL = '$sTheyHostOfferURL',
							  theyHostContinueURL = '$sTheyHostContinueURL',
							  restrictions = \"$sRestrictions\",
							  theyHostPassOnPrepopCodes = '$sPassOnPrepopCodes',
							  isRequireSSL = '$sIsRequiredSSL',
							  page2Info = '$iPage2Info',
							  isCoRegPopUp = '$sIsCoRegPopUp',
							  isCoRegPopPixelEnable = '$sCoRegPopPixelEnable',
							  coRegPopPassOnPrepopCodes = '$sCoRegPopPassOnPrepopCodes',
							  coRegPopPassOnCodeVarMap = \"$sCoRegPopPassOnCodeVarMap\",
							  coRegPopUrl = \"$sCoRegPopUrl\",
							  coRegPopUpTriggerOn = '$sCoRegTriggerOn',
							  isCloseTheyHost = '$sIsCloseTheyHost',
							  closeTheyHostPrePop = '$sCloseTheyHostPrePop',
							  closeTheyHostVarMap = \"$sCloseTheyHostVarMap\",
							  closeTheyHostUrl = \"$sCloseTheyHostUrl\",
							  closeTheyHostTriggerOn = '$sCloseTheyHostTriggerOn',
							  isCloseTheyHostPixelEnable = '$sCloseTheyHostPixelEnable',
							  isAvailableForApi = '$sIsAvailableForApi',
							  openOrLimited = '$sOpenOrLimited'
						  WHERE id = '$iId'";


				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit offer: " . addslashes($sEditQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				
				$rResult = dbQuery($sEditQuery);
				
				echo dbError();
				//}
				
				if ($rResult) {
					
					// send email if offer rate changed
					
					if (($fActualRevPerLead != $fOldActualRevPerLead || $fRevPerLead != $fOldRevPerLead)) {
										
						$sEmailSubject = "Offer Rate Changed - " . $sOfferCode;
						$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
						$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer's rate is changed";
						$sEmailMessage .= "\r\nOld Effective Rate: $fOldRevPerLead";
						$sEmailMessage .= "\r\nNew Effective Rate: $fRevPerLead";
						$sEmailMessage .= "\r\nOld Pay Rate: $fOldActualRevPerLead";
						$sEmailMessage .= "\r\nNew Pay Rate: $fActualRevPerLead";						
						
						if ($fRevPerLead != $fOldRevPerLead) {
							// insert offersLog - START
							$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
									  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$fOldRevPerLead\", \"$fRevPerLead\", \"Effective Rate\")";
							$rOfferLogResult = dbQuery($sOfferLogQuery);
							// insert offersLog - END
						}
						
						if ($fActualRevPerLead != $fOldActualRevPerLead) {
							// insert offersLog - START
							$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
									  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$fOldActualRevPerLead\", \"$fActualRevPerLead\", \"Pay Rate\")";
							$rOfferLogResult = dbQuery($sOfferLogQuery);
							// insert offersLog - END
						}
						
						// get the recipients
						$sRecQuery = "SELECT *
									  FROM   emailRecipients
									  WHERE  purpose = 'Offer rate change'";
						$rRecResult = dbQuery($sRecQuery);
						
						while ($oRecRow = dbFetchObject($rRecResult)) {
							$sEmailRecipients = $oRecRow->emailRecipients;
							
						}
						
						if ($sEmailRecipients != '') {
							$sEmailHeaders = "From: ot@amperemedia.com\r\n";
							$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
							$sEmailHeaders .= "cc:";
							$aEmailRecipients = explode(",",$sEmailRecipients);
							$sEmailTo = $aEmailRecipients[0];
							for ($i=1;$i<count($aEmailRecipients);$i++) {
								$sEmailHeaders .= $aEmailRecipients[$i].",";							
							}
							
							if (count($aEmailRecipients) > 1) {
								$sEmailHeaders = substr($sEmailHeaders, 0, strlen($sEmailHeaders)-1);
							}							
							mail($sEmailTo, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
						}
	
					}
					
					
					// update cap counts
					if ($iIsCap) {
						// check if capcount entry exists
						$sCapStartDate = "$iCapStartYear-$iCapStartMonth-$iCapStartDay";
						$sCapCheckQuery = "SELECt *
									   FROM   capCounts
									   WHERE  offerCode = '$sOfferCode'";
						$rCapCheckResult = dbQuery($sCapCheckQuery);
						if (dbNumRows($rCapCheckResult) == 0 ) {
							// insert record into capCounts
							$sCapInsertQuery = "INSERT INTO capCounts(offerCode, capStartDate, maxCap, cap1PeriodType,
												cap1PeriodInterval, cap1Max, cap2PeriodType, cap2PeriodInterval, cap2Max)
									VALUES('$sOfferCode', '$sCapStartDate', '$iMaxCap', '$sCap1PeriodType',
											'$iCap1PeriodInterval', '$iCap1Max', '$sCap2PeriodType', '$iCap2PeriodInterval', '$iCap2Max')";
							$rCapInsertResult = dbQuery($sCapInsertQuery);
							echo dbError();
						} else {
							// update capCounts record
							$sCapUpdateQuery = "UPDATE capCounts
											SET     capStartDate = '$sCapStartDate', 
													maxCap = '$iMaxCap', 
												    cap1PeriodType = '$sCap1PeriodType',
													cap1PeriodInterval = '$iCap1PeriodInterval', 
												    cap1Max = '$iCap1Max', 
													cap2PeriodType = '$sCap2PeriodType', 
													cap2PeriodInterval = '$iCap2PeriodInterval', 
													cap2Max = '$iCap2Max'
											WHERE   offerCode = '$sOfferCode'";
							$rCapUpdateResult = dbQuery($sCapUpdateQuery);
							echo $sCapUpdateQuery. dbError();
						}
					}
							
					
					// update lead details
						// check if record exists
				$sCheckQuery = "SELECT *
								FROM   offerLeadSpec
								WHERE  offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				$iCheckNumRows = dbNumRows($rCheckResult);
								
				echo dbError();
				if ($iCheckNumRows == 0 ) {
					// insert lead details
					$iDeliveryMethodId = '7';
						
					$sLeadFileName = "[offerCode]_[mm]_[dd]_[yyyy]_Ampere.csv";						
					$sLeadsEmailFromAddr = "Ampere Media Leads <leads@AmpereMedia.com>";
					$sLeadsEmailSubject = "Ampere Media - [offerCode], [count] [mm]-[dd]-[yyyy]";
					$sLeadsEmailBody = "[offerCode] - [count]";
					$sSingleEmailSubject = "Ampere Media - [offerCode], [mm]-[dd]-[yyyy]";
					$sSingleEmailFromAddr = "Ampere Media Lead <leads@AmpereMedia.com>";
					$iMaxAgeOfLeads = 30;
					// last lead date should be next to inactive date
						$sLastLeadDate = date("Y-m-d");
	
						$sLastLeadDate = dateAdd("d",1,$sLastLeadDate);
						$sLastLeadDate = dateAdd("y",1, $sLastLeadDate);						
						
						$sHttpPostString = addslashes($sHttpPostString);
						$sHeaderText = addslashes($sHeaderText);
						$sFooterText = addslashes($sFooterText);
						$sLeadsQuery = addslashes($sLeadsQuery);
						$sLeadsEmailBody = addslashes($sLeadsEmailBody);
						$sLeadsEmailSubject = addslashes($sLeadsEmailSubject);
						$sSingleEmailSubject = addslashes($sSingleEmailSubject);
						$sSingleEmailBody = addslashes($sSingleEmailBody);
						$sLeadsInstruction = addslashes($sLeadsInstruction);
						
						$sLeadSpecInsertQuery = "INSERT INTO offerLeadSpec(offerCode, leadsGroupId, lastLeadDate,
													 processingDays, deliveryMethodId, maxAgeOfLeads,
													postingUrl, httpPostString, ftpSiteUrl, initialFtpDirectory, userId, passwd, leadFileName,
													isEncrypted, encMethod, encType, encKey, headerText, footerText, leadsQuery,
													fieldDelimiter, fieldSeparater, endOfLine, leadsEmailSubject, leadsEmailFromAddr, leadsEmailBody, 
													singleEmailSubject, singleEmailFromAddr, singleEmailBody, testEmailRecipients,
													countEmailRecipients, leadsEmailRecipients, leadsInstruction, separateLeadFile)
												 VALUES('$sOfferCode', '$iLeadsGroupId', '$sLastLeadDate',
													'$sProcessingDays', '$iDeliveryMethodId', '$iMaxAgeOfLeads',
													\"$sPostingUrl\", \"$sHttpPostString\", '$sFtpSiteUrl', \"$sInitialFtpDirectory\", '$sUserId', '$sPasswd', '$sLeadFileName',
													'$iIsEncrypted', '$sEncMethod', '$sEncType', \"$sEncKey\", \"$sHeaderText\", \"$sFooterText\", \"$sLeadsQuery\",
													\"$sFieldDelimiter\", \"$sFieldSeparater\", \"$sEndOfLine\", \"$sLeadsEmailSubject\", \"$sLeadsEmailFromAddr\",\"$sLeadsEmailBody\", 
													\"$sSingleEmailSubject\", \"$sSingleEmailFromAddr\", \"$sSingleEmailBody\", \"$sTestEmailRecipients\",
													\"$sCountEmailRecipients\", \"$sLeadsEmailRecipients\", \"$sLeadsInstruction\",\"$iSeparateLeadFile\")";
						$rLeadSpecInsertResult = dbQuery($sLeadSpecInsertQuery);
					//	echo $sLeadSpecInsertQuery;
						echo dbError();
						
				} else {
					// update lead details
					
					$sUpdateQuery = "UPDATE offerLeadSpec
									 SET    lastLeadDate = '$sLastLeadDate',
									 		ftpSiteUrl = \"$sFtpSiteUrl\", 
											initialFtpDirectory = \"$sInitialFtpDirectory\",
											userId = \"$sUserId\", 
											passwd = \"$sPasswd\", 
											countEmailRecipients = \"$sCountEmailRecipients\", 
											leadsEmailRecipients = \"$sLeadsEmailRecipients\"
									 WHERE  offerCode = '$sOfferCode'";
					$rUpdateResult = dbQuery($sUpdateQuery);
					echo dbError(); 

				}					
									
					// Delete records from pageMap with the pages which are not checked
					
					// remove last comma from the pages list
					$sPagesString = substr($sPagesString, 0, strlen($sPagesString)-1);
					$sListOfOldPages = '';
					$iCountOfOldPages = 0;

					
					$sGetListOfOldPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
								WHERE otPages.id = pageMap.pageId
								AND pageMap.offerCode = '$sOfferCode'";
					$rGetListOfOldPages = dbQuery($sGetListOfOldPages);
					while ($oOldPageNameRow = dbFetchObject($rGetListOfOldPages)) {
						$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oOldPageNameRow->ids'";
							$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
							$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
							if ($oActivePageRow->count > 0) {
								$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
							} else {
								$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oOldPageNameRow->ids'
										AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
										AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
								$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
								$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
								if ($oPageDateRow->count > 0) {
									$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
								} else {
									$sListOfOldPages .= $oOldPageNameRow->pageName." [I] ".',';
								}
							}
						$iCountOfOldPages++;
					}
					$sListOfOldPages = substr($sListOfOldPages, 0, strlen($sListOfOldPages)-1);

					
					// get the count for entries that will be deleted on next query.
					$sGetCount = "SELECT * FROM pageMap WHERE  offerCode = '$sOfferCode'";
					if ($sPagesString != '') { $sGetCount .= " AND pageId NOT IN (".$sPagesString.")"; }
					$rGetCountResult = dbQuery($sGetCount);
					$iNumRowsDeleted = mysql_num_rows($rGetCountResult);
					
					
					// Get list of all pages that are about to delete
					$sGetPageNameBeingRemoved = "SELECT * FROM pageMap WHERE  offerCode = '$sOfferCode'";
					if ($sPagesString != '') {
						$sGetPageNameBeingRemoved .= " AND pageId NOT IN (".$sPagesString.")";
					}
					$rGetPageNameBeingRemoved = dbQuery($sGetPageNameBeingRemoved);
					$sRemovedPages = '';
					while ($sGetPageNameBeingRemovedRow = dbFetchObject($rGetPageNameBeingRemoved)) {
						$sRemovedPages .= "'".$sGetPageNameBeingRemovedRow->pageId."',";
					}

						
					// Delete if any page unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM pageMap
						WHERE  offerCode = '$sOfferCode'";
					if ($sPagesString != '') {
						$sDeleteQuery .= " AND pageId NOT IN (".$sPagesString.")";
					}
					$rDeleteResult = dbQuery($sDeleteQuery);

				
					if (count($aPagesArray) > 0) {
						$iNumRowsInserted = 0;
						$sGetPageNameFromId = '';
						for ($i = 0; $i<count($aPagesArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   pageMap
							   WHERE  pageId = ".$aPagesArray[$i]."
							   AND    offerCode = '$sOfferCode'";
							$rCheckResult = dbQuery($sCheckQuery);
							if (dbNumRows($rCheckResult) == 0) {
								$sInsertQuery = "INSERT INTO pageMap (pageId, offerCode, sortOrder)
									VALUES('".$aPagesArray[$i]."', '$sOfferCode', '0')";
								$rInsertResult = dbQuery($sInsertQuery);
								$iNumRowsInserted++;
								$sGetPageNameFromId .= "'".$aPagesArray[$i]."',";
							}
						}

						$sGetPageNameFromId = substr($sGetPageNameFromId, 0, strlen($sGetPageNameFromId)-1);
						$sGetPageName = "SELECT * FROM otPages WHERE id IN ($sGetPageNameFromId)";
						$rGetPageNameResult = dbQuery($sGetPageName);
						$sAddedPages = '';
						while ($sGetPageNameRow = dbFetchObject($rGetPageNameResult)) {
							$sAddedPages .= $sGetPageNameRow->pageName.',';
						}
						
						if ($sAddedPages != '') {
							$sAddedPages = str_replace(",", "\n",$sAddedPages);
							$sAddedPages = "\r\nOffer Added To: \n$sAddedPages\n\n";
						}
					}
					
					// If CoRegPopup is checked, remove offer from all pages other than yes/no
					// Close They Host is only for yes/no pages so remove offer from all other pages.
					$iCoRegPageMapTempCount = 0;
					if ($sIsCoRegPopUp == 'Y' || $sIsCloseTheyHost == 'Y') {
						$sGetPageMapQuery = "SELECT pageMap.* FROM pageMap, otPages
										WHERE pageMap.pageId = otPages.id
										AND pageMap.offerCode = '$sOfferCode'
										AND otPages.displayYesNo != '1'";
						$rGetPageMapResult = dbQuery($sGetPageMapQuery);
						if (dbNumRows($rGetPageMapResult) > 0) {
							while ($oPageMapRow = dbFetchObject($rGetPageMapResult)) {
								$sDeletePageMapQuery = "DELETE FROM pageMap WHERE id='$oPageMapRow->id'";
								$rDeletePageMapResult = dbQuery($sDeletePageMapQuery);
								$sRemovedPages .= "'".$oPageMapRow->pageId."',";
								$iCoRegPageMapTempCount++;
							}
						}
					}

					if ($sRemovedPages != '') {
						$sRemovedPages = substr($sRemovedPages, 0, strlen($sRemovedPages)-1);
						$sGetDeletedPageName = "SELECT pageName FROM otPages WHERE id IN ($sRemovedPages)";
						$rGetDeletedPageName = dbQuery($sGetDeletedPageName);
						$sRemovedPages = '';
						while ($sGetPageNameRow = dbFetchObject($rGetDeletedPageName)) {
							$sRemovedPages .= $sGetPageNameRow->pageName.',';
						}
						
						if ($sRemovedPages != '') {
							$sRemovedPages = str_replace(",", "\n",$sRemovedPages);
							$sRemovedPages = "\r\nOffer Removed From: \n$sRemovedPages\n\n";
						}
					}


					if ($iNumRowsInserted > 0 || $iNumRowsDeleted > 0 || $iCoRegPageMapTempCount > 0) {
						$sCurrPages = '';
						$iCountOfNewPages = 0;
						
						$sGetListOfCurrPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
										WHERE otPages.id = pageMap.pageId
										AND pageMap.offerCode = '$sOfferCode'";
						$rGetListOfCurrPages = dbQuery($sGetListOfCurrPages);
						while ($oCurrPageNameRow = dbFetchObject($rGetListOfCurrPages)) {
							$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oCurrPageNameRow->ids'";
							$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
							$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
							if ($oActivePageRow->count > 0) {
								$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
							} else {
								$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oCurrPageNameRow->ids'
										AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
										AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
								$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
								$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
								if ($oPageDateRow->count > 0) {
									$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
								} else {
									$sCurrPages .= $oCurrPageNameRow->pageName." [I] ".',';
								}
							}
							$iCountOfNewPages++;
						}
						$sCurrPages = substr($sCurrPages, 0, strlen($sCurrPages)-1);

						// insert offersLog - START
						$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
								  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sListOfOldPages\", \"$sCurrPages\", \"Ot Pages\")";
						$rOfferLogResult = dbQuery($sOfferLogQuery);
						// insert offersLog - END
						
						$sEmailSubject = "Offer OT Pages Assignments Update - " . $sOfferCode;
						$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
						$sEmailMessage .= "OfferCode: $sOfferCode\r\nOffer Name: $sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\n";
						
						if ($fOldRevPerLead != $fRevPerLead || $fOldActualRevPerLead != $fActualRevPerLead) {
							$sEmailMessage .= "\r\nOld Effective Rate: $fOldRevPerLead";
							$sEmailMessage .= "\r\nNew Effective Rate: $fRevPerLead";
							$sEmailMessage .= "\r\nOld Pay Rate: $fOldActualRevPerLead";
							$sEmailMessage .= "\r\nNew Pay Rate: $fActualRevPerLead\n";
						}

						$sEmailMessage .= "\r\nOld Page Count: $iCountOfOldPages";
						$sEmailMessage .= "\r\nNew Page Count: $iCountOfNewPages\n".$sAddedPages.$sRemovedPages;
						
						$sListOfOldPages = str_replace(",", "\n",$sListOfOldPages);
						$sCurrPages = str_replace(",", "\n",$sCurrPages);
						
						$sEmailMessage .= "\r\nOffer Was Previously Assigned To The Following OT Pages:\n$sListOfOldPages";
						$sEmailMessage .= "\r\n\nCurrently Offer Assigned To The Following OT Pages:\n$sCurrPages";
						$sEmailMessage .= "\r\n\r\nA = Active Pages     I = Inactive Pages";
						$sEmailMessage .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
						
						// get the recipients
						$sRecQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'Offer status change'";
						$rRecResult = dbQuery($sRecQuery);
						
						while ($oRecRow = dbFetchObject($rRecResult)) {
							$sEmailRecipients = $oRecRow->emailRecipients;
						}

						if ($sEmailRecipients != '') {
							$sEmailHeaders = "From: ot@amperemedia.com\r\n";
							$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
							$sEmailHeaders .= "cc:";
							$aEmailRecipients = explode(",",$sEmailRecipients);
							$sEmailTo = $aEmailRecipients[0];
							for ($i=1;$i<count($aEmailRecipients);$i++) {
								$sEmailHeaders .= $aEmailRecipients[$i].",";							
							}
							
							if (count($aEmailRecipients) > 1) {
								$sEmailHeaders = substr($sEmailHeaders, 0, strlen($sEmailHeaders)-1);
							}
							mail($sEmailTo, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
						}
					}
					
					// Delete records from categoryMap with the categories which are not checked
					
					// remove last comma from the categories list
					$sCategoriesString = substr($sCategoriesString, 0, strlen($sCategoriesString)-1);
					// Delete if any category unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM categoryMap
						WHERE  offerCode = '$sOfferCode'";
					if ($sCategoriesString != '') {
						$sDeleteQuery .= " AND categoryId NOT IN (".$sCategoriesString.")";
					}
					$rDeleteResult = dbQuery($sDeleteQuery);					
					
					if (count($aCategoriesArray) > 0) {
						for ($i = 0; $i<count($aCategoriesArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   categoryMap
							   WHERE  categoryId = '".$aCategoriesArray[$i]."' 
							   AND    offerCode = '$sOfferCode'";
							
							$rCheckResult = dbQuery($sCheckQuery);
							echo dbError();
							if (dbNumRows($rCheckResult) == 0) {
								// INSERT OfferCategoryRel record
								
								$sInsertQuery = "INSERT INTO categoryMap (categoryId, offerCode)
									VALUES('".$aCategoriesArray[$i]."', '$sOfferCode')";
								$rInsertResult = dbQuery($sInsertQuery);
							}
						}
					}
					
				} else {
					echo dbError();
				}
				$iOfferId = $iId;
			}
			
			// save uploaded image
			
			if (!(is_dir("$sGblOfferImagePath/$sOfferCode")) ) {				
				mkdir("$sGblOfferImagePath/$sOfferCode",0777);
				chmod("$sGblOfferImagePath/$sOfferCode",0777);
			}
			
			
			// upload open they-host header image.
			if ($_FILES['th_image']['tmp_name'] && $_FILES['th_image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['th_image']['tmp_name'];
				$sFileSize = $_FILES['th_image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 550 && $aImageSize[1] <= 100) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['th_image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sTheyHostFileName = 'theyHost_header_'.$sOfferCode.".gif";
				$sNewTheyHostImageFile  = "$sGblOfferImagePath/$sOfferCode/$sTheyHostFileName";

				move_uploaded_file( $sUploadedFileName, $sNewTheyHostImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
					SET    theyHostImage = '$sTheyHostFileName'
					WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				
				} else {
					$sMessage = "Open They Host Header Image Should Be Maximum of 550 W x 100 H Size...";
					$bKeepValues = true;
					$sIsOpenTheyHost = 'Y';
				}
			}
			
			
			// upload close they-host header image.
			if ($_FILES['cth_image']['tmp_name'] && $_FILES['cth_image']['tmp_name']!="none") {
				$sUploadedFileName = $_FILES['cth_image']['tmp_name'];
				$sFileSize = $_FILES['cth_image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 550 && $aImageSize[1] <= 100) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['cth_image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sCloseTheyHostFileName = 'closeTheyHost_header_'.$sOfferCode.".gif";
				$sNewCloseTheyHostImageFile  = "$sGblOfferImagePath/$sOfferCode/$sCloseTheyHostFileName";

				move_uploaded_file( $sUploadedFileName, $sNewCloseTheyHostImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
					SET    closeTheyHostHeader = '$sCloseTheyHostFileName'
					WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				
				} else {
					$sMessage = "Close They Host Header Image Should Be Maximum of 550 W x 100 H Size...";
					$bKeepValues = true;
					$sIsCloseTheyHost = 'Y';
				}
			}
			
			
			if ($_FILES['image']['tmp_name'] && $_FILES['image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['image']['tmp_name'];
				$sFileSize = $_FILES['image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 150 && $aImageSize[1] <= 150) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sImageFileName = $sOfferCode."_page1". ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
					SET    imageName = '$sImageFileName'
					WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				} else {
					$sMessage = "Image Should Be Maximum Of 150 W x 150 H Size...";
					$bKeepValues = true;
				}
			}
			
			
			// upload page1 small image if selected
			if ($_FILES['small_image']['tmp_name'] && $_FILES['small_image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['small_image']['tmp_name'];
				$sFileSize = $_FILES['small_image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				if ( $aImageSize[0] <= 88 && $aImageSize[1] <= 31) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['small_image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sSmallImageFileName = $sOfferCode."_small_page1". ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sSmallImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
								 SET    smallImageName = '$sSmallImageFileName'
								 WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				} else {
					$sMessage = "Small Image Should Be Exactly 88 W x 31 H Size Only...";
					$bKeepValues = true;
				}
				
			}
			
			
			// upload page2 image
			if ($_FILES['page2Image']['tmp_name'] && $_FILES['page2Image']['tmp_name']!="none") {
				
				$sUploadedFileName = $_FILES['page2Image']['tmp_name'];
				
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['page2Image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sImageFileName = $sOfferCode."_". time(). ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagePath/$sOfferCode/$sImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);											
			}
			
			// Find out on which page this sourceCode will appear, set ORDER BY as sourcecode
			// and go to that page, and display redirect for this sourceCode
			if ($sFilter != '') {
				
				$sFilterPart .= " AND ( ";
				
				switch ($sSearchIn) {
					case "headline" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "headline = '$sFilter'" : "headline like '%$sFilter%'";
					break;
					case "description" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "description = '$sFilter'" : "description like '%$sFilter%'";
					break;
					case "companyName" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "OC.companyName = '$sFilter'" : "OC.companyName like '%$sFilter%'";
					break;
					case "offerCode" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "offerCode = '$sFilter'" : "offerCode like '%$sFilter%'";
					break;
					default:
					$sFilterPart .= ($sExactMatch == 'Y') ? "offerCode = '$sFilter' || OC.companyName = '$sFilter' || headline = '$sFilter' || description = '$sFilter'  " : " offerCode like '%$sFilter%' || OC.companyName LIKE '%$sFilter%' || headline like '%$sFilter%' || description like '%$sFilter%' ";
				}
				$sFilterPart .= ") ";
			}
			
			if ($sExclude != '') {
				$filterPart .= " AND ( ";
				switch ($sExclude) {
					case "headline" :
					$sFilterPart .= "headline NOT LIKE '%$sExclude%'";
					break;
					case "description" :
					$sFilterPart .= "description NOT LIKE '%$sExclude%'";
					break;
					case "companyName" :
					$sFilterPart .= "OC.companyName NOT LIKE '%$sExclude%'";
					break;
					case "offerCode" :
					$sFilterPart .= "offerCode NOT LIKE '%$sExclude%'";
					break;
					default:
					$sFilterPart .= "offerCode NOT LIKE '%$sExclude%' && OC.companyName NOT LIKE '%$sExclude%' && headline NOT LIKE '%$sExclude%' && description NOT LIKE '%$sExclude%'" ;
				}
				$sFilterPart .= " ) ";
				
			}
			
			$sTempQuery = "SELECT count(*) numRecords
			  FROM   offers O, offerCompanies OC
			  WHERE  O.companyId = OC.id AND offerCode < '$sOfferCode' 
			  $sFilterPart 
			  ORDER BY offerCode $sCurrOrder";
			
			$rTempResult = dbQuery($sTempQuery);
			echo dbError();
			while ($oTempRow = dbFetchObject($rTempResult)) {
				$iNumRecords = $oTempRow->numRecords;
			}
			
			$iThisRecordNo = $iNumRecords + 1; // because the next record will be the current record (record of this offercode)
			
			if (!($iRecPerPage)) {
				$iRecPerPage = 20;
			}
			$iPage = ceil($iThisRecordNo/$iRecPerPage);
			
					
			$sPageReloadUrl .= "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage&iPage=$iPage&sOfferCode=$sOfferCode";
			
		}
		if ($sSaveContinue) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';			
					</script>";			
				// exit from this script				
			}
		} else if ($sSaveClose) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';
					self.close();
					</script>";			
				// exit from this script
				exit();
			}
		} else if ($sSaveNew) {
			
			if ($bKeepValues != true) {
				$sReloadWindowOpener = "<script language=JavaScript>
					window.opener.location.href = '".$sPageReloadUrl."';
					</script>";	
				
				// reset offer variables
				$sOfferCode = "";
				$sName = "";
				$iCompanyId = "";
				$sHeadline = "";
				$sDescription = "";	
				$sShortDescription = "";			
				$fRevPerLead = "";
				$fActualRevPerLead = "";
				$sImageName = "";	
				$sSmallImageFileName = '';			
				$iAutoRespEmail = "";
				$sIsTarget = '';
				$sTargetIncludeExclude = '';
				$sAutoRespEmailFormat = "";
				$sAutoRespEmailSub = "";
				$sAutoRespEmailBody = "";
				$sAutoRespEmailFromAddr = "";
				$sNotes = "";
				$sRestrictions = '';
				$sRequiredSSL = '';
				$iIsCap = '';
				$iDefaultSortOrder = '';
				$sActiveDateTime = '';
				$sInactiveDateTime = '';
				// reset cap variables
				$sCapStartDate = "";
				$iMaxCap = "";
				$sCap1PeriodType = "";
				$iCap1PeriodInterval = "";
				$iCap1Max = "";
				$sCap2PeriodType = "";
				$iCap2PeriodInterval = "";
				$iCap2Max = "";
			}
		}
		$iOfferId = '';
	}
}

if ($iId != ''  || $sOfferCode != '') {
	// If Clicked Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	if ($sOfferCode != '') {
	$sSelectQuery = "SELECT *
					 FROM   offers
					 WHERE  offerCode = '$sOfferCode'";
	} else {
		$sSelectQuery = "SELECT *
					 FROM   offers
					 WHERE  id = '$iId'";
	}
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			
			if ($bKeepValues != "true") {
				$iId = $oRow->id;
				$sOfferCode = $oRow->offerCode;
				$sName = $oRow->name;			
				$iCompanyId = $oRow->companyId;
				$sHeadline = ascii_encode($oRow->headline);
				$sDescription = ascii_encode($oRow->description);
				$sShortDescription = ascii_encode($oRow->shortDescription);
				$fRevPerLead = $oRow->revPerLead;
				$fActualRevPerLead = $oRow->actualRevPerLead;
				$sNotes = ascii_encode($oRow->notes);
				$iAutoRespEmail = $oRow->autoRespEmail;
				$sAutoRespEmailFormat = $oRow->autoRespEmailFormat;
				$sAutoRespEmailSub = ascii_encode($oRow->autoRespEmailSub);			
				$sAutoRespEmailBody = ascii_encode($oRow->autoRespEmailBody);
				$sAutoRespEmailFromAddr = $oRow->autoRespEmailFromAddr;
				$iIsCap = $oRow->isCap;
				$iDefaultSortOrder = $oRow->defaultSortOrder;	
				$sActiveDateTime = $oRow->activeDateTime;
				$sInactiveDateTime = $oRow->inactiveDateTime;				
				$sIsTarget = $oRow->isTarget;
				$sTargetGender = $oRow->targetGender;
				$iTargetStartYear = $oRow->targetStartYear;
				$iTargetEndYear = $oRow->targetEndYear;
				$sTargetStartZip = $oRow->targetStartZip;
				$sTargetEndZip = $oRow->targetEndZip;
				$sTargetStartExchange = $oRow->targetStartExchange;
				$sTargetEndExchange = $oRow->targetEndExchange;
				$sTargetIncExcGender = $oRow->targetIncExcGender;
				$sTargetIncExcYear = $oRow->targetIncExcYear;
				$sTargetIncExcZip = $oRow->targetIncExcZip;
				$sTargetIncExcExchange = $oRow->targetIncExcExchange;
				$sYearDatabase = $oRow->targetYearDatabase;
				$sZipDatabase = $oRow->targetZipDatabase;
				$sExchangeDatabase = $oRow->targetExchangeDatabase;
				$sTargetState = $oRow->targetState;
				$sTargetIncExcState = $oRow->targetIncExcState;
				$sTargetShowNoInfoAvailable = $oRow->targetShowNoInfoAvailable;
				$sIsOpenTheyHost = $oRow->isOpenTheyHost;
				$sTheyHostOfferURL = $oRow->theyHostOfferURL;
				$sTheyHostContinueURL = $oRow->theyHostContinueURL;
				$sRestrictions = ascii_encode($oRow->restrictions);
				$sPassOnPrepopCodes = $oRow->theyHostPassOnPrepopCodes;
				$sIsRequiredSSL = $oRow->isRequireSSL;
				$iPage2Info = $oRow->page2Info;
				$sIsAvailableForApi = $oRow->isAvailableForApi;
				$sOpenOrLimited = $oRow->openOrLimited;
				
				if ($sOpenOrLimited == '') {
					$sOpenOrLimited = 'O';
				}

				// Co-Reg
				$sIsCoRegPopUp = $oRow->isCoRegPopUp;
				$sCoRegPopPassOnPrepopCodes = $oRow->coRegPopPassOnPrepopCodes;
				$sCoRegPopPassOnCodeVarMap = $oRow->coRegPopPassOnCodeVarMap;
				$sCoRegPopUrl = $oRow->coRegPopUrl;
				$sCoRegTriggerOn = $oRow->coRegPopUpTriggerOn;
				$sCoRegPopPixelEnable = $oRow->isCoRegPopPixelEnable;
				
				// Close They Host
				$sIsCloseTheyHost = $oRow->isCloseTheyHost;
				$sCloseTheyHostPrePop = $oRow->closeTheyHostPrePop;
				$sCloseTheyHostVarMap = $oRow->closeTheyHostVarMap;
				$sCloseTheyHostUrl = $oRow->closeTheyHostUrl;
				$sCloseTheyHostTriggerOn = $oRow->closeTheyHostTriggerOn;
				$sCloseTheyHostPixelEnable = $oRow->isCloseTheyHostPixelEnable;

				// Close They Host
				if ($sIsCloseTheyHost == 'Y') {
					if ($oRow->closeTheyHostHeader != '') {
						$sCloseTheyHostImage = "<img src='http://www.popularliving.com/images/offers/$sOfferCode/$oRow->closeTheyHostHeader'>";
					} else {
						$sCloseTheyHostImage = "<img src='http://www.popularliving.com/images/thHeaderDefault.gif'>";
					}
				} else {
					$sCloseTheyHostImage = '';
				}
				
				
				// Open They Host
				if ($sIsOpenTheyHost == 'Y') {
					if ($oRow->theyHostImage != '') {
						$sTheyHostImage = "<img src='http://www.popularliving.com/images/offers/$sOfferCode/$oRow->theyHostImage'>";
					} else {
						$sTheyHostImage = "<img src='http://www.popularliving.com/images/thHeaderDefault.gif'>";
					}
				} else {
					$sTheyHostImage = '';
				}
			}
			
 			if ($oRow->imageName != '') {
				$sCurrentImage = "<img src='$sGblOfferImageUrl/$sOfferCode/$oRow->imageName'>";
			} else {
				$sCurrentImage = "No Image";
			}
			
			if ($oRow->smallImageName != '') {
				$sCurrentImage .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
									Small Image &nbsp; &nbsp; &nbsp; <img src='$sGblOfferImageUrl/$sOfferCode/$oRow->smallImageName'>";
			}
		
		
		
			if ($bKeepValues != "true") {
				// get lead spec data
			
				$sLeadSpecQuery = "SELECT *
								   FROM   offerLeadSpec
								   WHERE  offerCode = '$sOfferCode'";
				$rLeadSpecResult = dbQuery($sLeadSpecQuery);
				while ($oLeadSpecRow = dbFetchObject($rLeadSpecResult)) {
				$sFtpSiteUrl = $oLeadSpecRow->ftpSiteUrl;
				$sInitialFtpDirectory = $oLeadSpecRow->initialFtpDirectory;			
				$sUserId = $oLeadSpecRow->userId;
				$sPasswd = $oLeadSpecRow->passwd;
				$sCountEmailRecipients = $oLeadSpecRow->countEmailRecipients;
				$sLeadsEmailRecipients = $oLeadSpecRow->leadsEmailRecipients;
				$sLastLeadDate = $oLeadSpecRow->lastLeadDate;
				}
				// get capCounts Data
				
				$sCapQuery = "SELECT *
							   FROM   capCounts
							   WHERE  offerCode = '$sOfferCode'";
				
				$rCapResult = dbQuery($sCapQuery);
				while ($oCapRow = dbFetchObject($rCapResult)) {
					$sCapStartDate = $oCapRow->capStartDate;
					$iMaxCap = $oCapRow->maxCap;
					$sCap1PeriodType = $oCapRow->cap1PeriodType;
					$iCap1PeriodInterval = $oCapRow->cap1PeriodInterval;
					$iCap1Max = $oCapRow->cap1Max;
					$sCap2PeriodType = $oCapRow->cap2PeriodType;
					$iCap2PeriodInterval = $oCapRow->cap2PeriodInterval;
					$iCap2Max = $oCapRow->cap2Max;
				}
			}
		}
		
		
	} else {
		echo dbError();
	}
	
} else {
		
	if ($sAutoRespEmailFromAddr == '') {
		$sAutoRespEmailFromAddr = "support@amperemedia.com";
	}
	
	
	$sHeadline = ascii_encode(stripslashes($sHeadline));
	$sDescription = ascii_encode(stripslashes($sDescription));
	$sShortDescription = ascii_encode(stripslashes($sShortDescription));
	
	$sAutoRespEmailSub = ascii_encode(stripslashes($sAutoRespEmailSub));
	$sAutoRespEmailBody = ascii_encode(stripslashes($sAutoRespEmailBody));
	$sNotes = ascii_encode(stripslashes($sNotes));
	$sRestrictions = ascii_encode(stripslashes($sRestrictions));
	
	
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

if ($iId != '') {
	$sOfferCodeField = "<tr><td align=right>Offer Code</td><td colspan=3>$sOfferCode</td></tr>";
} else {
	$sOfferCodeField = "<tr><td align=right>Offer Code</td><td colspan=3><input type=text name=sOfferCode value='$sOfferCode'><BR>
							OfferCode must contain AlphaNumeric characters, - or _ only and maximum 25 chars long.</td></tr>";
}


// prepare auto email format options
$sHtmlSelected = "";
$sTextSelected = "";
switch ($sAutoRespEmailFormat) {			
	case "html":
		$sHtmlSelected = "selected";
		break;
	case "text":
		$sTextSelected = "selected";		
		break;
}
$sAutoRespEmailFormatOptions = "<option value='' >
							<option value='text' $sTextSelected>Text
							<option value='html' $sHtmlSelected>Html";

$sAutoRespEmailChecked = '';
if ($iAutoRespEmail) {
	$sAutoRespEmailChecked = "checked";
}

$sPage2InfoChecked = '';
if ($iPage2Info) {
	$sPage2InfoChecked = "checked";
}


$sIsTargettedChecked='';
if ($sIsTarget=='Y') {
	$sIsTargettedChecked = "checked";
}

$sIsRequiredSSLChecked = '';
if ($sIsRequiredSSL == 'Y') {
	$sIsRequiredSSLChecked = "checked";
}

$sIsAvailableForApiChecked = '';
if ($sIsAvailableForApi == 'Y') {
	$sIsAvailableForApiChecked = 'checked';
}


$sIsCapChecked = '';
if ($iIsCap) {
	$sIsCapChecked = "checked";
}

if($sTargetIncExcGender == '') {
	$sTargetIncExcGender = 'I';
}

if($sTargetIncExcZip == '') {
	$sTargetIncExcZip = 'I';
}

if($sTargetIncExcYear == '') {
	$sTargetIncExcYear = 'I';
}

if($sTargetIncExcExchange == '') {
	$sTargetIncExcExchange = 'I';
}

$sCompanyQuery = "SELECT   id, companyName, code
				   FROM     offerCompanies
				   ORDER BY companyName";
$rCompanyResult = dbQuery($sCompanyQuery);

$sCompanyOptions .= "<option value=''>Select Company</option>";
while ( $oCompanyRow = dbFetchObject($rCompanyResult)) {
	if ($oCompanyRow->id == $iCompanyId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sCompanyOptions .= "<option value='".$oCompanyRow->id."' $sSelected>".$oCompanyRow->companyName . " - " . $oCompanyRow->code . "</option>";
}

// set curr date values to be selected by default
if ($iId == '' && !($saveClose || $sSaveContinue)) {
	$iCapStartMonth = $iCurrMonth;
	$iCapStartDay = $iCurrDay;
	$iCapStartYear = $iCurrYear;	
} else {
	$iCapStartMonth = substr($sCapStartDate,5,2);
	$iCapStartDay = substr($sCapStartDate,8,2);
	$iCapStartYear = substr($sCapStartDate,0,4);
	
}



if ($iId == '' && !($sSaveClose || $sSaveContinue)) {
	
	$iMonthActive = $iCurrMonth;
	$iMonthInactive = $iCurrMonth;
	$iDayActive = $iCurrDay;
	$iDayInactive = $iCurrDay;
	$iYearActive = $iCurrYear;
	$iYearInactive = $iCurrYear+1;
	$iHourActive = 0;
	$iHourInactive = 0;
	
	$iCapStartMonth = $iCurrMonth;
	$iCapStartDay = $iCurrDay;
	$iCapStartYear = $iCurrYear;	
	
	$iLastLeadMonth = $iMonth;
	$iLastLeadDay = $iCurrDay;
	$iLastLeadYear = $iCurrYear+1;	
	
	// last lead date should be next to inactive date
	$sLastLeadDate = date("Y-m-d");
	
	$sLastLeadDate = dateAdd("d",1,$sLastLeadDate);
	$sLastLeadDate = dateAdd("y",1, $sLastLeadDate);
	$iLastLeadMonth = substr($sLastLeadDate,5,2);
	$iLastLeadDay = substr($sLastLeadDate,8,2);
	$iLastLeadYear = substr($sLastLeadDate,0,4);	
	
} else {
	
	$iMonthActive = substr($sActiveDateTime,4,2);
	$iMonthInactive = substr($sInactiveDateTime,4,2);
	$iDayActive = substr($sActiveDateTime,6,2);
	$iDayInactive = substr($sInactiveDateTime,6,2);
	$iYearActive = substr($sActiveDateTime,0,4);
	$iYearInactive = substr($sInactiveDateTime,0,4);
	$iHourActive = substr($sActiveDateTime,8,2);
	$iHourInactive = substr($sInactiveDateTime,8,2);
	
	$iCapStartMonth = substr($sCapStartDate,5,2);
	$iCapStartDay = substr($sCapStartDate,8,2);
	$iCapStartYear = substr($sCapStartDate,0,4);
	
	$iLastLeadMonth = substr($sLastLeadDate,5,2);
	$iLastLeadDay = substr($sLastLeadDate,8,2);
	$iLastLeadYear = substr($sLastLeadDate,0,4);	
}

// prepare month options for From and To date

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$iValue = $i+1;
	
	if ($iValue < 10) {
		$iValue = "0".$iValue;
	}
	
	if ($iValue == $iMonthActive) {
		$sFromSel = "selected";
	} else {
		$sFromSel = "";
	}
	if ($iValue == $iMonthInactive) {
		$sToSel = "selected";
	} else {
		$sToSel = "";
	}	
	
	$sMonthActivationOptions .= "<option value='$iValue' $sFromSel>$aGblMonthsArray[$i]";
	$sMonthExpirationOptions .= "<option value='$iValue' $sToSel>$aGblMonthsArray[$i]";
	
	if ($iValue == $iCapStartMonth) {
		$sCapStartMonthSel = "selected";
	} else {
		$sCapStartMonthSel = "";
	}
	$sCapStartMonthOptions .= "<option value='$iValue' $sCapStartMonthSel>$aGblMonthsArray[$i]";
	
	if ($iValue == $iLastLeadMonth) {
		$sLastLeadMonthSel = "selected";
	} else {
		$sLastLeadMonthSel = "";
	}
	$sLastLeadMonthOptions .= "<option value='$iValue' $sLastLeadMonthSel>$aGblMonthsArray[$i]";
	
}


// prepare day options for From and To date
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$iValue = "0".$i;
	} else {
		$iValue = $i;
	}
	
	if ($iValue == $iDayActive) {
		$sFromSel = "selected";
	} else {
		$sFromSel = "";
	}
	
	if ($iValue == $iDayInactive) {
		$sToSel = "selected";
	} else {
		$sToSel = "";
	}
	
	
	if ($iValue == $iCapStartDay) {
		$sCapStartDaySel = "selected";
	} else {
		$sCapStartDaySel = "";
	}
	
	
	$sDayActivationOptions .= "<option value='$iValue' $sFromSel>$i";
	$sDayExpirationOptions .= "<option value='$iValue' $sToSel>$i";
	$sCapStartDayOptions .= "<option value='$iValue' $sCapStartDaySel>$i";
	
	if ($iValue == $iLastLeadDay) {
		$sLastLeadDaySel = "selected";
	} else {
		$sLastLeadDaySel = "";
	}
	$sLastLeadDayOptions .= "<option value='$iValue' $sLastLeadDaySel>$i";
		
}

// prepare year options for From and To date
$sLastLeadYearOptions = "";
for ($i = $iCurrYear-1; $i <= $iCurrYear+5; $i++) {
	
	if ($i == $iLastLeadYear) {
		$sLastLeadYearSel = "selected";
	} else {
		$sLastLeadYearSel ="";
	}
	$sLastLeadYearOptions .= "<option value='$i' $sLastLeadYearSel>$i";
	
	if ($i == $iYearActive) {
		$fromSel = "selected";
	} else {
		$fromSel = "";
	}
	
	if ($i == $iYearInactive) {
		$sToSel = "selected";
	} else {
		$sToSel ="";
	}
	
	if ($i == $iCapStartYear) {
		$sCapStartYearSel = "selected";
	} else {
		$sCapStartYearSel ="";
	}
	
	$sYearActivationOptions .= "<option value='$i' $fromSel>$i";
	$sYearExpirationOptions .= "<option value='$i' $sToSel>$i";
	$sCapStartYearOptions .= "<option value='$i' $sCapStartYearSel>$i";	
}


// prepare hour options
//$sHourActiveOptions = "<option value='00' selected>Hour";
//$sHourInactiveOptions = "<option value='00' selected>Hour";

for ($i = 0; $i < 24; $i++) {
	$iValue = $i;
	if ($iValue < 10) {
		$iValue = "0".$iValue;
	}
	
	if ($iValue == $iHourActive) {
		$sHourActiveSelected = "selected";
	} else {
		$sHourActiveSelected = "";
	}
	$sHourActiveOptions .= "<option value='$iValue' $sHourActiveSelected>$iValue";
	
	if ($iValue == $iHourInactive) {
		$sHourInactiveSelected = "selected";
	} else {
		$sHourInactiveSelected = "";
	}
	$sHourInactiveOptions .= "<option value='$iValue' $sHourInactiveSelected>$iValue";		
}



/*********************** CAP LIMITS SECTION *******************************/

// prepare cap period type options
$sCap1DaysSelected = '';
$sCap1WeeksSelected = '';
$sCap1MonthsSelected = '';
$sCap1YearsSelected = '';
switch($sCap1PeriodType) {
	case 'W':
	$sCap1WeeksSelected = "selected";
	break;
	case 'M':
	$sCap1MonthsSelected = "selected";
	break;
	case 'Y':
	$sCap1YearsSelected = "Selected";
	break;
	default:
	$sCap1DaysSelected = "selected";
}

$sCap1PeriodTypeOptions = "<option value='D' $sCap1DaysSelected>Days
						   <option value='W' $sCap1WeeksSelected>Weeks
						   <option value='M' $sCap1MonthsSelected>Months
						   <option value='Y' $sCap1YearsSelected>Years";		

// prepare options for period type cap2
$sCap2DaysSelected = '';
$sCap2WeeksSelected = '';
$sCap2MonthsSelected = '';
$sCap2YearsSelected = '';
switch($sCap2PeriodType) {
	case 'W':
	$sCap2WeeksSelected = "selected";
	break;
	case 'M':
	$sCap2MonthsSelected = "selected";
	break;
	case 'Y':
	$sCap2YearsSelected = "Selected";
	break;
	default:
	$sCap2DaysSelected = "selected";
}

$sCap2PeriodTypeOptions = "<option value='D' $sCap2DaysSelected>Days
						   <option value='W' $sCap2WeeksSelected>Weeks
						   <option value='M' $sCap2MonthsSelected>Months
						   <option value='Y' $sCap2YearsSelected>Years";		

/*********************** END CAP LIMITS SECTION *******************************/



// Prepare checkboxes for Pages
// Exclude CoReg phantom page
// Exclude open theyhost phantom page
// Exclude close theyhost phantom page
/*
$sPagesQuery = "SELECT *
			    FROM   otPages
			    WHERE pageName NOT LIKE 'coReg_%'
			    AND pageName NOT LIKE 'th_%'
			    AND pageName NOT LIKE 'cth_%'
				ORDER BY pageName";*/
// Display active pages, test pages, and pages that were created within past 30 days

$sPagesQuery = "SELECT pageId as id, pageName
				FROM   activePages
				UNION
				SELECT id, pageName
				FROM otPages
				WHERE pageName like 'test%'
				OR (date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
					AND date_add(CURRENT_DATE, INTERVAL -0 DAY))
				ORDER BY pageName";

$rPagesResult = dbQuery($sPagesQuery);

$j = 0;
$sPageCheckboxes = "<tr>";
while ($oPagesRow = dbFetchObject($rPagesResult)) {
	$iPageId = $oPagesRow->id;
	$sPageName = $oPagesRow->pageName;
	
	if ($oPagesRow->displayYesNo == '1') {
		$sTypeOfPage = " [y/n]";
	} else {
		$sTypeOfPage = "";
	}
	
	$sOfferQuery = "SELECT offerCode
				   FROM   otPages, pageMap
				   WHERE  pageMap.pageId = '$iPageId'
				   AND    pageMap.offerCode = '$sOfferCode'
				   AND    pageMap.pageId = otPages.id";
	
	$rOfferResult = dbQuery($sOfferQuery);
	
	if (dbNumRows($rOfferResult) > 0) {
		$sPageChecked  = "checked";
	} else {
		$sPageChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sPageCheckboxes .= "</tr>";
		}
		$sPageCheckboxes .= "<tr>";
	}

	$sPageCheckboxes .= "<td width=5% valign=top><input type=checkbox name='page_".$oPagesRow->id."' value='".$oPagesRow->id."' $sPageChecked></td><td width=28%>$sPageName $sTypeOfPage</td>";
	$j++;
}
$sPageCheckboxes .= "</tr>";


// Prepare checkboxes for Categories
$sCategoriesQuery = "SELECT *
			  	FROM   categories
				ORDER BY title";
$rCategoriesResult = dbQuery($sCategoriesQuery);
echo dbError();
$j = 0;
$sCategoryCheckboxes = "<tr>";
while ($oCategoriesRow = dbFetchObject($rCategoriesResult)) {
	$iCategoryId = $oCategoriesRow->id;
	$sCategoryTitle = $oCategoriesRow->title;
	//echo "<BR>".$iCategoryId.$sCategoryTitle;
	$sOfferQuery = "SELECT offerCode
				   FROM   categories, categoryMap
				   WHERE  categoryMap.categoryId = '$iCategoryId'
				   AND    categoryMap.offerCode = '$sOfferCode'
				   AND    categoryMap.categoryId = categories.id";
	
	$rOfferResult = dbQuery($sOfferQuery);
	
	echo dbError();
	if(dbNumRows($rOfferResult)>0){
		$sCategoryChecked  = "checked";
	} else {
		$sCategoryChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sCategoryCheckboxes .= "</tr>";
		}
		$sCategoryCheckboxes .= "<tr>";
	}
	
	//echo "<BR>".$iCategoryId.$sCategoryTitle;
	$sCategoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$iCategoryId."' value='".$iCategoryId."' $sCategoryChecked></td><td  width=28%>$sCategoryTitle</td>";
	$j++;
	
}
$sCategoryCheckboxes .= "</tr>";


// delete the image if clicked on the delete link

if ($sDeleteImage) {
	unlink("$sGblOfferImagePath/$sOfferCode/$sDeleteImage");
}

// get list of page2 images, if offer is selected to edit
if ($sOfferCode && $iId && file_exists("$sGblOfferImagePath/$sOfferCode")) {
$rImageDir = opendir("$sGblOfferImagePath/$sOfferCode");
	if ($rImageDir) {
		//echo $rImageDir;
		while (($sFile = readdir($rImageDir)) != false) {	
			if (!is_dir("$sGblOfferImagePath/$sOfferCode/$sFile")) {
						
				$page2ImagesList .=  "<a href='JavaScript:void(window.open(\"$sGblOfferImageUrl/$sOfferCode/$sFile\",\"\",\"\"));'>$sGblOfferImageUrl/$sOfferCode/$sFile</a> 
						&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sOfferCode=$sOfferCode&iRecPerPage=$iRecPerPage&";
				$page2ImagesList .="sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&sDeleteImage=$sFile'>Delete</a><BR>";
			}
		}
	}
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=iRecPerPage value='$iRecPerPage'>
			<input type=hidden name=sFilter value='$sFilter'>
			<input type=hidden name=sExactMatch value='$sExactMatch'>
			<input type=hidden name=sExclude value='$sExclude'>
			<input type=hidden name=sSearchIn value='$sSearchIn'>
			<input type=hidden name=iPage value='$iPage'>";

if( $iId ) {
	$sHidden .= "<input type=hidden name=sOfferCode value='$sOfferCode'>";
}

//include("../../includes/adminAddHeader.php");

?>

<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>

<table width=85% align=center>
<tr><Td class=message align=center colspan=2><?php echo $sMessage;?>
</td></tr></table>	


<script language=JavaScript>

function checkForm() {
var activeDate=document.form1.iYearActive.value + document.form1.iMonthActive.value + document.form1.iDayActive.value;
var inactiveDate=document.form1.iYearInactive.value + document.form1.iMonthInactive.value + document.form1.iDayInactive.value;
var lastDate=document.form1.iLastLeadYear.value + document.form1.iLastLeadMonth.value + document.form1.iLastLeadDay.value;

if (inactiveDate < activeDate) {
  alert("Inactive date must be greater than active date...");
  return false;
}

if (lastDate < inactiveDate) {
  alert("Last lead date must be greater than inactive date...");
  return false;
}

return true;
}


function openWin(winUrl) {
	checkForm();
	var temp = window.open(winUrl,'','');		
}


function addDays(myDate,days) {
    return new Date(myDate.getTime() + days*24*60*60*1000);
}

/* function to change last lead date when inactive date changed */
function inactiveDateChanged() {

var inYearSelIndex = document.form1.iYearInactive.selectedIndex;
var inMonthSelIndex = document.form1.iMonthInactive.selectedIndex;
var inDaySelIndex = document.form1.iDayInactive.selectedIndex;
var inYearSel = document.form1.iYearInactive.options[inYearSelIndex].value;
var inMonthSel = document.form1.iMonthInactive.options[inMonthSelIndex].value -1;
var inDaySel = document.form1.iDayInactive.options[inDaySelIndex].value;

var newDate = new Date(inYearSel,inMonthSel,inDaySel);

var lastLeadDate = addDays(newDate,1);

var newWeekDay = lastLeadDate.getDay(); // gives days of the week 0 to 6
var newMonth = lastLeadDate.getMonth();
var newYear = lastLeadDate.getYear();

if (newWeekDay == 0) {
	
	// if sunday add 1
	lastLeadDate = addDays(lastLeadDate,1);
		
} else if(newWeekDay == 6) {

	// if saturday, add 2
	lastLeadDate = addDays(lastLeadDate,2);
		
} 


var newDay = lastLeadDate.getDate();
var newMonth = lastLeadDate.getMonth()+1;
var newYear = lastLeadDate.getYear();

for (var i=0;i<document.form1.iLastLeadYear.length;i++) {
	temp = document.form1.iLastLeadYear.options[i].value;
	
	if (temp == newYear) {
		document.form1.iLastLeadYear.options[i].selected = true;
		break;
		
	}
}
for (var i=0;i<document.form1.iLastLeadDay.length;i++) {
	temp = document.form1.iLastLeadDay.options[i].value;
	
	if (temp == newDay) {
		document.form1.iLastLeadDay.options[i].selected = true;
		break;
		
	}
}
for (var i=0;i< document.form1.iLastLeadMonth.length;i++) {
	temp = document.form1.iLastLeadMonth.options[i].value;
	
	if (temp == newMonth) {
		document.form1.iLastLeadMonth.options[i].selected = true;
		break;
		
	}
}

alert("Last Lead Date Changed");

return true;
}

function validateTargetZip(zip) {
	if(zip.value == '') {
		return true;
	}
	if( zip.name == 'sTargetStartZip' ) {
		fieldname = 'Target Zip Start';
	}
	if( zip.name == 'sTargetEndZip' ) {
		fieldname = 'Target Zip End';
	}
	result = validateZipCode(zip.value);
	if(result == false) {
		zip.value = '';
		alert( "Please correct " + fieldname );
	}
}


function validateZipCode(zipCode) {
	if (!zipCode.match("^[0-9]{5}$")) {
		return false;
	}
	return true;
}


function validateTargetYOB(yob) {
	if(yob.value == '') {
		return true;
	}
	if( yob.name == 'iTargetStartYear' ) {
		fieldname = 'Target Year of Birth Start';
	}
	if( yob.name == 'iTargetEndYear' ) {
		fieldname = 'Target Year of Birth End';
	}
	if( !yob.value.match("^[0-9]{4}$")) {
		alert( "Please correct " + fieldname );
	}
}


function validateTargetExchange(ext) {
	if(ext.value == '') {
		return true;
	}
	if( ext.name == 'sTargetStartExchange' ) {
		fieldname = 'Target Exchange Start';
	}
	if( ext.name == 'sTargetEndExchange' ) {
		fieldname = 'Target Exchange End';
	}
	result = validateExt(ext.value);
	if(result == false) {
		ext.value = '';
		alert( "Please correct " + fieldname );
	}
}


function validateExt(ext) {
	if ( ext.match("^[01]{1}") || !ext.match("^[0-9]{3}$")) {
		return false;
	}
	return true;
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data onSubmit="return checkForm();">

<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>		
	<?php echo $sOfferCodeField;?>
	</tr>
	<tr><td align=right>Offer Name</td>
		<td  colspan=3><input type=text name=sName value='<?php echo $sName;?>'></td>
		</tr>
		<Tr>
	<td align=right>Company</td>
		<td  colspan=3><select name=iCompanyId>
		<?php echo $sCompanyOptions;?>
			</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/offerCompanies/addCompany.php?iMenuId=15&sReturnTo=iCompanyId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Company</a></td>
	</tr>
		
	<tr><td align=right>Active Date<BR>mm-dd-yyyy-hh</td>
		<td><select name=iMonthActive>
			<?php echo $sMonthActivationOptions;?>
			</select> &nbsp;<select name=iDayActive>
			<?php echo $sDayActivationOptions;?>
			</select> &nbsp;<select name=iYearActive>
			<?php echo $sYearActivationOptions;?>
			</select> &nbsp;<select name=iHourActive>
			<?php echo $sHourActiveOptions;?>
			</select> </td>
	<td align=right>Inactive Date<BR>mm-dd-yyyy-hh</td>
		<td><select name=iMonthInactive onChange=inactiveDateChanged();>
			<?php echo $sMonthExpirationOptions;?>
			</select> &nbsp;<select name=iDayInactive onChange=inactiveDateChanged();>
			<?php echo $sDayExpirationOptions;?>
			</select> &nbsp;<select name=iYearInactive onChange=inactiveDateChanged();>
			<?php echo $sYearExpirationOptions;?>
			</select> &nbsp;<select name=iHourInactive>
			<?php echo $sHourInactiveOptions;?>
			</select></td>
	</tr>
	<tr><td align=right>Last Lead Date</td>
		<td colspan=3><select name=iLastLeadMonth>
			<?php echo $sLastLeadMonthOptions;?>
			</select> &nbsp;<select name=iLastLeadDay>
			<?php echo $sLastLeadDayOptions;?>
			</select> &nbsp;<select name=iLastLeadYear>
			<?php echo $sLastLeadYearOptions;?>
			</select> &nbsp;<b>Caution: Editing Inactive Date will change Last Lead Date to the next business day of Inactive Date.</b>
		</td>
	</tr>	
	
	<tr><td align=right>Headline</td>
		<td colspan=3><input type=text name=sHeadline value='<?php echo $sHeadline;?>' size=70></td>
	</tr>
	<tr>
		<td align=right>Image</td>
		<td colspan=3><input type=file name='image'>
		<BR> Image Should Be Maximum Of 150 W x 150 H size</td>
	</tr>
	<tr>
		<td align=right>Small Image</td>
		<td colspan=3><input type=file name='small_image'>
		<BR> Image Should Be Exactly 88 W x 31 H size</td>
	</tr>
	
	<tr><td align=right>Description</td>
		<td colspan=3><textarea name=sDescription rows=5 cols=80><?php echo $sDescription;?></textarea></td>
	</tr>	
	<tr><td align=right>Short Description</td>
		<td colspan=3><textarea name=sShortDescription rows=5 cols=80><?php echo $sShortDescription;?></textarea></td>
	</tr>	
	<?php 
	if ($iId) {
		echo "<tr><td align=right>Current Image</td>
					<td colspan=3>$sCurrentImage</td></tr>";
	}
	?>
	
	
	<tr><td align=right>Notes</td>
		<td  colspan=3><textarea name=sNotes rows=3 cols=70><?php echo $sNotes;?></textarea></td>
	</tr>
	
	<tr><td align=right>Auto Responder Email</td>
		<td><input type=checkbox name=iAutoRespEmail value='1' <?php echo $sAutoRespEmailChecked;?>>
		&nbsp; &nbsp; Auto Responder Email Format &nbsp; <select name=sAutoRespEmailFormat>
		<?php echo $sAutoRespEmailFormatOptions;?>
		</select></td>
		<td align=right>Auto Responder Email Subject</td>
		<td><input type=text name=sAutoRespEmailSub value='<?php echo $sAutoRespEmailSub;?>' size=35></td>
	</tr>	
	<Tr><td align=right>Auto Responder Email From Address</td>
		<td colspan=3><input type=text name=sAutoRespEmailFromAddr value='<?php echo $sAutoRespEmailFromAddr;?>' size=35></td></tr>
	<tr>	
	<tr>
	<td align=right>Auto Responder Email Body</td>
		<td  colspan=3><?php echo $sAutoRespPreviewLink;?><BR><textarea name=sAutoRespEmailBody rows=5 cols=80><?php echo $sAutoRespEmailBody;?></textarea><BR>
			[EMAIL] will be replaced with user's email address while sending the email.</td>
	</tr>		
	
	<tr><td align=right>Effective Rate</td>
		<td><input type=text name=fRevPerLead value='<?php echo $fRevPerLead;?>'> $</td>		
	</tr>	
	<tr><td align=right>Pay Rate</td>
		<td><input type=text name=fActualRevPerLead value='<?php echo $fActualRevPerLead;?>'> $</td>		
	</tr>	
	<!--<tr><td colspan=3><b>Changing Mode May Change Last Delivery Date</b></td>-->
	<tr><td align=right valign=top>Is Capped</td>
		<td><input type=checkbox name=iIsCap value='1' <?php echo $sIsCapChecked;?>><br>
		Cap Limits will be applied only if this is checked.</td>
	</tr>
	<tr><td align=right>Default Sort Order</td><td colspan=3><input type=text name=iDefaultSortOrder size=5 value='<?php echo $iDefaultSortOrder;?>'>
		<BR>Default sort order will be applied only if sort order is 0 in page sort order.</td></tr>
	
	<tr><td align=right>Restrictions </td>
		<td colspan=3><textarea name=sRestrictions rows=3 cols=70><?php echo $sRestrictions;?></textarea></td>
	</tr>
	
	<tr><td align=right valign=top>Required SSL</td>
		<td><input type=checkbox name="sIsRequiredSSL" value='Y' <?php echo $sIsRequiredSSLChecked; ?>></td>
	</tr>
	
	<tr><td align=right valign=top>Available For API</td>
		<td><input type=checkbox name="sIsAvailableForApi" value='Y' <?php echo $sIsAvailableForApiChecked; ?>></td>
	</tr>
	
	<td align=right>Page 2 Info</td>
		<td><input type=checkbox name=iPage2Info value='1' <?php echo $sPage2InfoChecked;?>>
		&nbsp;&nbsp;Check the box if offer has 2nd page questions.
		</td>
	</tr>
	

		
	<td align=right>Open or Limited</td>
		<td>
		
		<input type="radio" name="sOpenOrLimited" value="O" <?php if( $sOpenOrLimited == 'O' ) { echo 'checked'; } ?>>&nbsp;Open
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sOpenOrLimited" value="L" <?php if( $sOpenOrLimited == 'L' ) { echo 'checked'; } ?>>&nbsp;Limited
		
		</td>
	</tr>
	
	</table>
	<BR>
	
	<table cellpadding=3 cellspacing=3 bgcolor=c9c9c9 width=95% align=center border=1>
	<tr>
	<td class=header>Co-Reg Popup Offer:</td>
	<td>&nbsp;</td>
	</tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Is Co-Reg Popup</td>
		<td><input type=checkbox name="sIsCoRegPopUp" value='Y' onclick="if (!document.form1.sIsCoRegPopUp.checked) { alert('Please make sure \'Page 2 Info\' is checked [if applicable].')} 
	else { alert('Co-Reg Popup is only for yes/no pages. Offer will be removed from all other pages.') };" <?php if($sIsCoRegPopUp == 'Y') { echo 'checked '; }?>> 
		Check here if the offer is Co-Reg Popup offer.<br>
		If offer is marked as Co-Reg Popup, it will be removed from all pages other than yes/no pages.
		</td>
	</tr>
	
	<tr><td colspan=5>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Trigger Popup On</td>
	<td colspan="4">
		<input type="radio" name="sCoRegTriggerOn" value="Y" <?php if( $sCoRegTriggerOn == 'Y' ) { echo 'checked'; } ?>>&nbsp;Trigger Popup on Yes<br>
		<input type="radio" name="sCoRegTriggerOn" value="N" <?php if( $sCoRegTriggerOn == 'N' ) { echo 'checked'; } ?>>&nbsp;Trigger Popup on No
	</td>
	</tr>

	<tr><td width = 15% align=right>Pass On Prepop Codes & SessionId</td>
		<td><input type=checkbox name="sCoRegPopPassOnPrepopCodes" value='Y' <?php if($sCoRegPopPassOnPrepopCodes == 'Y') { echo 'checked '; } ?>></td>
	</tr>
	
	<tr>
	<td width = 15% align=right>Outbound Variable Map</td>
	<td>
		<input size=75 name="sCoRegPopPassOnCodeVarMap" value="<?php echo $sCoRegPopPassOnCodeVarMap; ?>">
		<br>&nbsp;Required if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Specify comma separated inbound=outbound variable name pairs to map.
		<br>&nbsp;You can use the code "pnd" to prepopulate a field with a 10-digit phone number (numerical only, no dashes).
		<br>&nbsp;Do not include sessionId here.  SessionId is included by default if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Example: e=email,f=first,l=last,a1=addr1,a2=addr2,c=city,s=state,z=zip,p=phone
	</td>
	</tr>
	
	
	<?php
		if($sCoRegPopPassOnPrepopCodes == 'Y') {
			$sTempCoRegAddToPixel = "&sesId=[sesId]";
		} else {
			$sTempCoRegAddToPixel = '';
		}
	?>
	
	<tr><td width=15% align=right>Pixel Enable</td>
		<td>
		<input type="radio" name="sCoRegPopPixelEnable" value="Y" <?php if( $sCoRegPopPixelEnable == 'Y' ) { echo 'checked'; } ?>>&nbsp;Yes
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sCoRegPopPixelEnable" value="N" <?php if( $sCoRegPopPixelEnable == 'N' ) { echo 'checked'; } ?>>&nbsp;No
		</td>
	</tr>

	<tr>
		<td width = 15% align=right>Co-Reg Pixel</td>
		<td>
		&lt;IMG SRC="http://www.popularliving.com/pixels/coRegPopUpOfferTakenPixel.php?offerCode=<?php echo $sOfferCode; echo $sTempCoRegAddToPixel; ?>" width=1 height=1&gt;
		</td>
	</tr>

	<tr><td colspan=2>&nbsp;</td></tr>
	<tr>
	<td width = 15% align=right>Co-Reg Popup URL</td>
	<td>
		<input size=50 name="sCoRegPopUrl" value="<?php echo $sCoRegPopUrl; ?>">
	</td>
	</tr>

	</table>
	<br>

	
	<table cellpadding=3 cellspacing=3 bgcolor=c9c9c9 width=95% align=center border=1>
	<tr>
	<td class=header>Close They-Host</td>
	<td>&nbsp;</td>
	</tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Is Close They-Host</td>
		<td><input type=checkbox name="sIsCloseTheyHost" value='Y' <?php if($sIsCloseTheyHost == 'Y') { echo 'checked '; }  ?>
		onclick="if (!document.form1.sIsCloseTheyHost.checked) { alert('Please make sure \'Page 2 Info\' is checked [if applicable].')} 
	else { alert('Close They-Host is only for yes/no pages. Offer will be removed from all other pages.') };">
		Check here if the offer is Close They-Host</td>
	</tr>
	
	<tr><td colspan=5>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Close They-Host Trigger On</td>
	<td colspan="4">
		<input type="radio" name="sCloseTheyHostTriggerOn" value="Y" <?php if( $sCloseTheyHostTriggerOn == 'Y' ) { echo 'checked'; } ?>>&nbsp;Yes
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sCloseTheyHostTriggerOn" value="N" <?php if( $sCloseTheyHostTriggerOn == 'N' ) { echo 'checked'; } ?>>&nbsp;No
	</td>
	</tr>

	<tr><td width = 15% align=right>Pass On Prepop Codes & SessionId</td>
		<td><input type=checkbox name="sCloseTheyHostPrePop" value='Y' <?php if($sCloseTheyHostPrePop == 'Y') { echo 'checked '; } ?>></td>
	</tr>
	
	
	<tr><td width = 15% align=right>Outbound Variable Map</td>
	<td>
		<input size=75 name="sCloseTheyHostVarMap" value="<?php echo $sCloseTheyHostVarMap; ?>">
		<br>&nbsp;Required if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Specify comma separated inbound=outbound variable name pairs to map.
		<br>&nbsp;You can use the code "pnd" to prepopulate a field with a 10-digit phone number (numerical only, no dashes).
		<br>&nbsp;Do not include sessionId here.  SessionId is included by default if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Example: e=email,f=first,l=last,a1=addr1,a2=addr2,c=city,s=state,z=zip,p=phone
	</td>
	</tr>
	
	<?php
		if($sCloseTheyHostPrePop == 'Y') {
			$sTempAddCloseTheyHostToPixel = "&sesId=[sesId]";
		} else {
			$sTempAddCloseTheyHostToPixel = '';
		}
	?>
	
	<tr><td width=15% align=right>Pixel Enable</td>
		<td>
		<input type="radio" name="sCloseTheyHostPixelEnable" value="Y" <?php if( $sCloseTheyHostPixelEnable == 'Y' ) { echo 'checked'; } ?>>&nbsp;Yes
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sCloseTheyHostPixelEnable" value="N" <?php if( $sCloseTheyHostPixelEnable == 'N' ) { echo 'checked'; } ?>>&nbsp;No
		</td>
	</tr>

	<tr><td width=15% align=right>Close They Host Pixel</td>
		<td>
		&lt;IMG SRC="http://www.popularliving.com/pixels/closeTheyHostPixel.php?offerCode=<?php echo $sOfferCode; echo $sTempAddCloseTheyHostToPixel; ?>" width=1 height=1&gt;
	</td></tr>
	

	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td align=right>Header Image </td>
		<td colspan=3><input type=file name='cth_image'>
		<BR> Image Should Be Maximum Of 550 W x 100 H Size.  Push is required after you upload image.  Hit refresh (F5) if you still see old image.<br><br>
		<?php echo $sCloseTheyHostImage; ?></td>
	</tr>
	
	
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Close They Host Offer URL</td>
	<td><input size=50 name="sCloseTheyHostUrl" value="<?php echo $sCloseTheyHostUrl; ?>"></td>
	</tr>
	</table>
	<br>
	
	
	<table cellpadding=3 cellspacing=3 bgcolor=c9c9c9 width=95% align=center border=1>
	<tr>
	<td class=header>Open They-Host</td>
	<td>&nbsp;</td>
	</tr>
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Is Open They-Host</td>
		<td><input type=checkbox name="sIsOpenTheyHost" value='Y' <?php if($sIsOpenTheyHost == 'Y') { echo 'checked '; }  if($iId){ echo 'disabled '; }?>> Check here if the offer is an Open They-Host</td>
	</tr>

	<tr><td width = 15% align=right>Pass On Prepop Codes & SessionId</td>
		<td><input type=checkbox name="sPassOnPrepopCodes" value='Y' <?php if($sPassOnPrepopCodes == 'Y') { echo 'checked '; } if($iId && $sIsOpenTheyHost == 'N' ){ echo 'disabled '; } ?>></td>
	</tr>
		<?php	if($sPassOnPrepopCodes == 'Y') {
			$sTempAddToPixel = "&sesId=[sesId]";
		} else {
			$sTempAddToPixel = '';
		}	?>
	
	<?php
		if( $iId && $sIsOpenTheyHost == 'Y') {
	?>

	<tr>
		<td width = 15% align=right>Open They Host Offer Page</td>
		<td>
		http://www.popularliving.com/p/th.php?sThId=<?php echo $sOfferCode;?>
		</td>
	</tr>

	<tr>
		<td width = 15% align=right>Open They Host Pixel</td>
		<td>
		&lt;IMG SRC="http://www.popularliving.com/pixels/thOfferTakenPixel.php?offerCode=<?php echo $sOfferCode; echo $sTempAddToPixel; ?>" width=1 height=1&gt;
		</td>
	</tr>

	<?php } ?>

	<tr><td colspan=2>&nbsp;</td></tr>
	<tr>
		<td align=right>Header Image </td>
		<td colspan=3><input type=file name='th_image' <?php if($iId && $sIsOpenTheyHost == 'N' ){ echo 'disabled '; }?>>
		<BR> Image Should Be Maximum Of 550 W x 100 H Size.  Push is required after you upload image.  Hit refresh (F5) if you still see old image.<br><br>
		<?php echo $sTheyHostImage; ?>
		</td>
	</tr>
	
	
	<tr><td colspan=2>&nbsp;</td></tr>
	<tr>
	<td width = 15% align=right>They Host Offer URL</td>
	<td>
		<input size=50 name="sTheyHostOfferURL" value="<?php echo $sTheyHostOfferURL; ?>" <?php if($iId && $sIsOpenTheyHost == 'N' ){ echo 'disabled '; }?>>
	</td>
	</tr>

	<tr><td colspan=2>&nbsp;</td></tr>
	<tr>
	<td width = 15% align=right>Continue URL</td>
	<td>
		<input size=50 name="sTheyHostContinueURL" value="<?php echo $sTheyHostContinueURL; ?>" <?php if($iId && $sIsOpenTheyHost == 'N' ){ echo 'disabled '; }?>>
	</td>
	</tr>

	</table>
	
	<BR>


	<table cellpadding=3 cellspacing=3 bgcolor=c9c9c9 width=95% align=center border=1>
	<tr>
	<td class=header>Targeting</td>
	<td colspan="4"><b>Note:</b> Any changes made to this section will directly affect to the targeting offers.</td>
	</tr>
	<tr><td colspan=5>&nbsp;</td></tr>
	<tr><td width = 15% align=right>Enable targetting?</td>
		<td colspan=4><input type=checkbox name="sIsTarget" value='Y' <?php echo $sIsTargettedChecked;?>>Targeting will be applied only if this is checked.</td>
	</tr>
	
	
	<tr><td colspan=5>&nbsp;</td></tr>
	<tr><td>&nbsp;</td>
	<td colspan="4">
		<input type="radio" name="sTargetShowNoInfoAvailable" value="Y" <?php if( $sTargetShowNoInfoAvailable == 'Y' ) { echo 'checked'; } ?>>&nbsp;Show Offer If Targeting Info Not Available<br>
		<input type="radio" name="sTargetShowNoInfoAvailable" value="N" <?php if( $sTargetShowNoInfoAvailable == 'N' ) { echo 'checked'; } ?>>&nbsp;Hide Offer If Targeting Info Not Available
	</td>
	</tr>
	<tr><td colspan=5>&nbsp;</td></tr>
	
	
	<tr><td width = 15% align=right>Gender</td>
	<td colspan=3>
		<input type="radio" name="sTargetGender" value="M" <?php if( $sTargetGender == 'M' ) { echo 'checked'; } ?>>&nbsp;Male&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetGender" value="F" <?php if( $sTargetGender == 'F' ) { echo 'checked'; } ?>>&nbsp;Female&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetGender" value="" <?php if( $sTargetGender == '' ) { echo 'checked'; } ?>>&nbsp;Do not use&nbsp;&nbsp;&nbsp;
	</td>
	<td>
		<input type="radio" name="sTargetIncExcGender" value="I" <?php if( $sTargetIncExcGender == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcGender" value="E" <?php if( $sTargetIncExcGender == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
	</td>
	</tr>

	<tr><td colspan=5>&nbsp;</td></tr>

	<tr>
	<td width = 15% align=right>Year Of Birth Start</td>
	<td width = 10%><select name="iTargetStartYear" onblur="validateTargetStartYear(this)">
	<option value="">
	<?php 
		$iMaxTargetYOB = date("Y") - 18;
		for($i = $iMaxTargetYOB; $i >= 1900; $i--) {
			$selected = '';
			if( $iTargetStartYear == $i ) {
				$selected = 'selected';
			}
			print "<option value=\"$i\" $selected>$i";
		}
	
	?>
	</select>
	</td>
	<td width = 12% align=right>Year Of Birth End</td>
	<td width = 10%><select name="iTargetEndYear" onblur="validateTargetEndYear(this)">
	<option value="">
	<?php 
		$iMaxTargetYOB = date("Y") - 18;
		for($i = $iMaxTargetYOB; $i >= 1900; $i--) {
			$selected = '';
			if( $iTargetEndYear == $i ) {
				$selected = 'selected';
			}
			print "<option value=\"$i\" $selected>$i";
		}
	
	?>
	</select>
	</td>
	<td>
		<input type="radio" name="sTargetIncExcYear" value="I" <?php if( $sTargetIncExcYear == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcYear" value="E" <?php if( $sTargetIncExcYear == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
	</td>
	</tr>

	<tr><td width = 15% align=right>Year of Birth Database</td>
	<td colspan=3>
		<input type="radio" name="sYearDatabase" value="I" <?php if( $sYearDatabase == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sYearDatabase" value="E" <?php if( $sYearDatabase == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sYearDatabase" value="" <?php if( $sYearDatabase == '' ) { echo 'checked'; } ?>>&nbsp;Do not use&nbsp;&nbsp;&nbsp;
	</td>
	<td>&nbsp;</td>
	</tr>
	
	<tr><td colspan=5>&nbsp;</td></tr>
	
	
	<tr>
	<td width=15% align=right>Zip Code Start</td>
	<td width = 10%>
	<input name="sTargetStartZip" value="<?php echo $sTargetStartZip; ?>" size="6" maxlength="5" onblur="validateTargetZip(this)">
	</td>
	<td width=12% align=right>Zip Code End</td>
	<td>
	<input name="sTargetEndZip" value="<?php echo $sTargetEndZip; ?>" size="6" maxlength="5" onblur="validateTargetZip(this)">
	</td>
	<td>
		<input type="radio" name="sTargetIncExcZip" value="I" <?php if( $sTargetIncExcZip == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcZip" value="E" <?php if( $sTargetIncExcZip == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
	</td>
	</tr>

	<tr><td width = 15% align=right>Zip Code Database</td>
	<td colspan=3>
		<input type="radio" name="sZipDatabase" value="I" <?php if( $sZipDatabase == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sZipDatabase" value="E" <?php if( $sZipDatabase == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sZipDatabase" value="" <?php if( $sZipDatabase == '' ) { echo 'checked'; } ?>>&nbsp;Do not use&nbsp;&nbsp;&nbsp;
	</td>
	<td>&nbsp;</td>
	</tr>

	<tr><td colspan=5>&nbsp;</td></tr>
	
	
	<tr>
	<td width=15% align=right>Exchange Start</td>
	<td>
	<input name="sTargetStartExchange" value="<?php echo $sTargetStartExchange; ?>" size="4" maxlength="3" onblur="validateTargetExchange(this)">
	</td>
	<td width=12% align=right>Exchange End</td>
	<td>
	<input name="sTargetEndExchange" value="<?php echo $sTargetEndExchange; ?>" size="4" maxlength="3" onblur="validateTargetExchange(this)">
	</td>
	<td>
		<input type="radio" name="sTargetIncExcExchange" value="I" <?php if( $sTargetIncExcExchange == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcExchange" value="E" <?php if( $sTargetIncExcExchange == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
	</td>
	</tr>

	<tr><td width = 15% align=right>Exchange Database</td>
	<td colspan=3>
		<input type="radio" name="sExchangeDatabase" value="I" <?php if( $sExchangeDatabase == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sExchangeDatabase" value="E" <?php if( $sExchangeDatabase == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sExchangeDatabase" value="" <?php if( $sExchangeDatabase == '' ) { echo 'checked'; } ?>>&nbsp;Do not use&nbsp;&nbsp;&nbsp;
	</td>
	<td>&nbsp;</td>
	</tr>
	
	
	<tr><td colspan=5>&nbsp;</td></tr>
	<tr><td width = 15% align=right>State</td>
	<td colspan=4>
		<input type=text name=sTargetState value='<?php echo $sTargetState;?>' size='70' maxlength="130">&nbsp;&nbsp;Example: il,pa,nj,de,ny<br>
		<input type="radio" name="sTargetIncExcState" value="I" <?php if( $sTargetIncExcState == 'I' ) { echo 'checked'; } ?>>&nbsp;Include&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcState" value="E" <?php if( $sTargetIncExcState == 'E' ) { echo 'checked'; } ?>>&nbsp;Exclude&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sTargetIncExcState" value="" <?php if( $sTargetIncExcState == '' ) { echo 'checked'; } ?>>&nbsp;Do not use&nbsp;&nbsp;&nbsp;
		&nbsp;&nbsp;&nbsp;List states separated by comma. (NO SPACE)
	</td>
	</tr>
	
	</table>


	
	<BR>
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<td colspan=4 class=header>Offer Cap Limits</td>
	</tr>
	<tr><td align=right width=15%>Cap Start Date</td>
		<td width=20%><select name=iCapStartMonth>
			<?php echo $sCapStartMonthOptions;?>
			</select> &nbsp;<select name=iCapStartDay>
			<?php echo $sCapStartDayOptions;?>
			</select> &nbsp;<select name=iCapStartYear>
			<?php echo $sCapStartYearOptions;?>
			</select></td>
	<td align=right width=15%>Max Cap</td>
		<td colspan=3><input type=text name=iMaxCap value='<?php echo $iMaxCap;?>'></td>
	</tr>	
	<tr><td align=right>Cap1 Period Type</td>
		<td><select name=sCap1PeriodType>
		<?php echo $sCap1PeriodTypeOptions;?>
		</select></td>
	<td align=right>Cap1 Period Interval</td>
		<td><input type=text name=iCap1PeriodInterval value='<?php echo $iCap1PeriodInterval;?>'></td>
	<td align=right>Cap1 Max</td>
		<td colspan=3><input type=text name=iCap1Max value='<?php echo $iCap1Max;?>'></td>
	</tr>	
	<tr><td align=right>Cap2 Period Type</td>
		<td><select name=sCap2PeriodType>
		<?php echo $sCap2PeriodTypeOptions;?>
		</select></td>
	<td align=right>Cap2 Period Interval</td>
		<td><input type=text name=iCap2PeriodInterval value='<?php echo $iCap2PeriodInterval;?>'></td>
	<td align=right>Cap2 Max</td>
		<td colspan=3><input type=text name=iCap2Max value='<?php echo $iCap2Max;?>'></td>
	</tr>	
	</table>
	<BR>

	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<td colspan=2 class=header>Lead Delivery Specifications</td>
	</tr>
	<tr><td colspan=2><b>Note:</b> Any changes made to this section will directly affect to the lead delivery.</td></tr>
	
	<tr><td align=right>FTP Site URL</td>
		<td colspan=3><input type=text name=sFtpSiteUrl value='<?php echo $sFtpSiteUrl;?>' size=40>
			<BR>Don't include http:// or https://</td>
	</tr>
	
	<tr>
		<td align=right>Initial FTP Directory</td>
		<td colspan=3><input type=text name=sInitialFtpDirectory value='<?php echo $sInitialFtpDirectory;?>' size=60></td>
	
	</tr>		
	<tr><td align=right>User Id</td>
		<td><input type=text name=sUserId value='<?php echo $sUserId;?>'></td>
	<td align=right>Password</td>
		<td><input type=text name=sPasswd value='<?php echo $sPasswd;?>'></td>
	</tr>
	
	<tr><td align=right>Count Mail Recipients</td>
		<td colspan=3><input type=text name=sCountEmailRecipients value='<?php echo $sCountEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Mail Recipients</td>
		<td colspan=3><input type=text name=sLeadsEmailRecipients value='<?php echo $sLeadsEmailRecipients;?>' size=70></td>
	</tr>
	</table>
	
	<BR>
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<td colspan=2 class=header>Assign Offer To The Following OT Pages</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	
	<?php echo $sPageCheckboxes;?>
		
</table>
<BR>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td colspan=2 class=header>Assign Offer To The Following Categories</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	
	<?php echo $sCategoryCheckboxes;?>
		
</table>
<!--
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
		-->
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>


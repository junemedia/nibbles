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

// if copyTemplate button clicked to copy page2 template from another offer
if ($sCopyTemplate) {
	// get page2 template
	$sPage2TemplateQuery = "SELECT page2Template
							FROM   offers
							WHERE  offerCode = '$sCopyTemplateFrom'";
	$rPage2TemplateResult = dbQuery($sPage2TemplateQuery);
	while ($oPage2TemplateRow = dbFetchObject($rPage2TemplateReesult)) {
		$sPage2Template = $oPage2TemplateRow->page2Template;
		// keep the values as it is in other textboxes
		$bKeepValues = 'true';
	}
}

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
	$sRerunStartDate = "$iRerunStartYear-$iRerunStartMonth-$iRerunStartDay";
	$sRerunEndDate = "$iRerunEndYear-$iRerunEndMonth-$iRerunEndDay";
	
	if ($fRevPerLead == '') {
		$fRevPerLead = 0;
	}
		
	if ($fActualRevPerLead == '') {
		$fActualRevPerLead = $fRevPerLead;
	}
	
	if (count($iProcessingDay)>0) {
		for($i=0;$i<count($iProcessingDay);$i++) {
			$sProcessingDays .= $iProcessingDay[$i].",";
		}
		
		$sProcessingDays = substr($sProcessingDays,0,strlen($sProcessingDays)-1);
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
	} /*else if ($iImageHeight != 0 && $iImageWidth != 0 && !($iImageWidth==120 && $iImageHeight == 60)){
		$sMessage = "Image must be 120 W x 60 H size...";
		$bKeepValues = true;
	} */
	else if (!ereg("^[0-9\.]*$", $fRevPerLead)) {
		$sMessage = "Revenue Per Lead Can Contain Only Numbers or .";
		$bKeepValues = true;
	} else if (!ereg("^[0-9\.]*$", $fActualRevPerLead)) {
		$sMessage = "Actual Revenue Per Lead Can Contain Only Numbers or .";
		$bKeepValues = true;
	} else if (!ereg("^[0-9,]*$", $sAddiInfoPopupSize)) {
		$sMessage = "Popup Size Can Contain Only Numbers and ,";
		$bKeepValues = true;
	} else if ($iCompanyId == '') {
		$sMessage = "Please select offer company...";
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
		
		if ( dbNumRows($rCheckResult) > 0 ) {
			$sMessage = "Offercode already exists...";
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
			
			// get page2 template content if to copy template from another offer
			$sTemplateQuery = "SELECT page2Template
							   FROM   offers
							   WHERE  offerCode = '$sCopyTemplateFrom'";
			$rTemplateResult = dbQuery($sTemplateQuery);
			while ($oTemplateRow = dbFetchObject($rTemplateResult)) {
				$sPage2Template = $oTemplateRow->page2Template;
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
				$sAddiInfoTitle = addslashes($sAddiInfoTitle);
				$sPage2Template = addslashes($sPage2Template);
				$sAddiInfoFormat = addslashes($sAddiInfoFormat);
				$sPage2JavaScript = addslashes($sPage2JavaScript);
				$sNewPage2JavaScript = addslashes($sNewPage2JavaScript);
				$iPage2Info = addslashes($iPage2Info);
				$sAddiInfoText = addslashes($sAddiInfoText);
				$sHttpPostString = addslashes($sHttpPostString);
				$sHeaderText = addslashes($sHeaderText);
				$sFooterText = addslashes($sFooterText);
				$sLeadsQuery = addslashes($sLeadsQuery);
				$sLeadsEmailBody = addslashes($sLeadsEmailBody);
				$sSingleEmailBody = addslashes($sSingleEmailBody);
				$sLeadsInstruction = addslashes($sLeadsInstruction);
				
				
				//check if offercode exists
				$sCheckQuery = "SELECT *
				   		FROM offers
				   		WHERE offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) == 0) {
				
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
					
				
					$sInsertQuery = "INSERT INTO offers(offerCode, companyId, partnerId, name, headline,
								description, shortDescription, isNonRevenue, revPerLead, actualRevPerLead, autoRespEmail, autoRespEmailFormat, autoRespEmailSub, autoRespEmailBody, autoRespEmailFromAddr, 
								notes, addiInfoFormat, addiInfoTitle, addiInfoText, addiInfoPopupSize, precheckAllPages, page2Info,
							mode, isLive, activeDateTime, inactiveDateTime, page2Template, page2JavaScript, newPage2JavaScript, isCap, defaultSortOrder, apiAvailable, excludeFromMasterPage, 
							isTarget, targetGender, targetStartYear, targetEndYear, targetStartZip, targetEndZip, targetStartExchange, targetEndExchange, targetIncExcGender, targetIncExcYear, targetIncExcZip, 
							targetIncExcExchange, targetYearDatabase, targetZipDatabase, targetExchangeDatabase, targetState, targetIncExcState, targetShowNoInfoAvailable,
							isOpenTheyHost, theyHostOfferURL, theyHostContinueURL, restrictions, theyHostPassOnPrepopCodes, theyHostPassOnCodeVarMap, isRequireSSL, isCoRegPopUp, coRegPopPassOnPrepopCodes, coRegPopPassOnCodeVarMap, 
							coRegPopUrl, coRegPopUpTriggerOn, isCloseTheyHost, closeTheyHostPrePop, closeTheyHostVarMap, closeTheyHostUrl, closeTheyHostTriggerOn, isCloseTheyHostPixelEnable, isCoRegPopPixelEnable, isAvailableForApi, 
							openOrLimited) 
					 	VALUES('$sOfferCode', '$iCompanyId', '$iPartnerId', \"$sName\", \"$sHeadline\", \"$sDescription\", \"$sShortDescription\", '$iIsNonRevenue', '$fRevPerLead', '$fActualRevPerLead',
								'$iAutoRespEmail', '$sAutoRespEmailFormat', \"$sAutoRespEmailSub\", \"$sAutoRespEmailBody\", \"$sAdutoRespEmailFromAddr\", 
								\"$sNotes\", '$sAddiInfoFormat', \"$sAddiInfoTitle\", \"$sAddiInfoText\", '$sAddiInfoPopupSize', '$iPrecheckAllPages',
								'$iPage2Info', '$sMode', '$iIsLive', '$sActiveDateTime', '$sInactiveDateTime', \"$sPage2Template\", \"$sPage2JavaScript\", \"$sNewPage2JavaScript\", '$iIsCap', '$iDefaultSortOrder', '$iApiAvailable', '$iExcludeFromMasterPage', 
								'$sIsTarget', '$sTargetGender', '$iTargetStartYear', '$iTargetEndYear', '$sTargetStartZip', '$sTargetEndZip', '$sTargetStartExchange', '$sTargetEndExchange', '$sTargetIncExcGender', '$sTargetIncExcYear', '$sTargetIncExcZip', '$sTargetIncExcExchange', '$sYearDatabase', '$sZipDatabase', '$sExchangeDatabase', \"$sTargetState\", '$sTargetIncExcState', '$sTargetShowNoInfoAvailable',
								\"$sIsOpenTheyHost\", \"$sTheyHostOfferURL\", \"$sTheyHostContinueURL\", \"$sRestrictions\", \"$sPassOnPrepopCodes\", \"$sPassOnCodeVarMap\", '$sIsRequiredSSL', 
								'$sIsCoRegPopUp', '$sCoRegPopPassOnPrepopCodes', \"$sCoRegPopPassOnCodeVarMap\", \"$sCoRegPopUrl\", '$sCoRegTriggerOn', 
								'$sIsCloseTheyHost','$sCloseTheyHostPrePop',\"$sCloseTheyHostVarMap\",\"$sCloseTheyHostUrl\", '$sCloseTheyHostTriggerOn', '$sCloseTheyHostPixelEnable', '$sCoRegPopPixelEnable', '$sIsAvailableForApi', 
								'$sOpenOrLimited')";

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
						// insert record into offerLeadSpec
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
						echo dbError();
						// insert record into capCounts
						if ($iIsCap) {
							$sCapStartDate = "$iCapStartYear-$iCapStartMonth-$iCapStartDay";
							
							$sCapInsertQuery = "INSERT INTO capCounts(offerCode, capStartDate, maxCap, cap1PeriodType,
												cap1PeriodInterval, maxCap1, cap2PeriodType, cap2PeriodInterval, maxCap2)
									VALUES('$sOfferCode', '$sCapStartDate', '$iMaxCap', '$sCap1PeriodType',
											'$iCap1PeriodInterval', '$iCap1Max', '$sCap2PeriodType', '$iCap2PeriodInterval', '$iCap2Max')";
							$rCapInsertResult = dbQuery($sCapInsertQuery);
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
				$sAddiInfoTitle = addslashes($sAddiInfoTitle);
				$sPage2Template = addslashes($sPage2Template);
				$sAddiInfoFormat = addslashes($sAddiInfoFormat);
				$sPage2JavaScript = addslashes($sPage2JavaScript);
				$sNewPage2JavaScript = addslashes($sNewPage2JavaScript);
				$iPage2Info = addslashes($iPage2Info);
				$sAddiInfoText = addslashes($sAddiInfoText);
				
				
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
							  partnerId = '$iPartnerId',
							  name = '$sName',
							  headline = \"$sHeadline\",
							  description = \"$sDescription\",
							  shortDescription = \"$sShortDescription\",
							  isNonRevenue = '$iIsNonRevenue',
							  revPerLead = '$fRevPerLead',							 
							  actualRevPerLead = '$fActualRevPerLead',							 
							  autoRespEmail = '$iAutoRespEmail',
							  autoRespEmailFormat = '$sAutoRespEmailFormat',
							  autoRespEmailSub = \"$sAutoRespEmailSub\",
							  autoRespEmailBody = \"$sAutoRespEmailBody\",
							  autoRespEmailFromAddr = \"$sAutoRespEmailFromAddr\",
							  notes = \"$sNotes\", 
							  addiInfoFormat = '$sAddiInfoFormat',
							  addiInfoTitle = '$sAddiInfoTitle',
							  addiInfoText = \"$sAddiInfoText\",		
							  addiInfoPopupSize = '$sAddiInfoPopupSize',
							  precheckAllPages = '$iPrecheckAllPages',
							  page2Info = \"$iPage2Info\",
							  mode = '$sMode',
							  isLive = '$iIsLive',
							  activeDateTime = '$sActiveDateTime',
							  inactiveDateTime = '$sInactiveDateTime',
							  page2Template = \"$sPage2Template\",
							  page2JavaScript = \"$sPage2JavaScript\",
							  newPage2JavaScript = \"$sNewPage2JavaScript\",
							  isCap = '$iIsCap',
							  defaultSortOrder = '$iDefaultSortOrder',
							  apiAvailable = '$iApiAvailable',
							  excludeFromMasterPage = '$iExcludeFromMasterPage',
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
							  theyHostPassOnCodeVarMap = \"$sPassOnCodeVarMap\",
							  isRequireSSL = '$sIsRequiredSSL',
							  isCoRegPopUp = '$sIsCoRegPopUp',
							  coRegPopPassOnPrepopCodes = '$sCoRegPopPassOnPrepopCodes',
							  coRegPopPassOnCodeVarMap = \"$sCoRegPopPassOnCodeVarMap\",
							  coRegPopUrl = \"$sCoRegPopUrl\",
							  isCoRegPopPixelEnable = '$sCoRegPopPixelEnable',
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
					
					// if offerStatus changed, send status email
					if (($sMode != $sOldMode || $iIsLive != $iOldIsLive) && ($sMode == 'A' || $sOldMode == 'A')) {
						
						$sTempOldModeValue = '';
						if ($sOldMode == 'A') {
							$sTempOldModeValue = 'Offer Up';
						} elseif ($sOldMode == 'I') {
							$sTempOldModeValue = 'Offer Down';
						} elseif ($sOldMode == 'P') {
							$sTempOldModeValue = 'Offer API Only';
						}
						
						$sOfferOnPages = '';
						$sPageQuery= "SELECT pageName, otPages.id as ids 
									  FROM   otPages, pageMap
									  WHERE  otPages.id = pageMap.pageId
									  AND	 offerCode = '$sOfferCode'";
						$rPageResult = dbQuery($sPageQuery);
						echo dbError();
						while ( $oPageRow = dbFetchObject($rPageResult)) {
							
							$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oPageRow->ids'";
							$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
							$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
							if ($oActivePageRow->count > 0) {
								$sOfferOnPages .= $oPageRow->pageName." [A] ".',';
							} else {
								$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oPageRow->ids'
										AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
										AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
								$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
								$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
								if ($oPageDateRow->count > 0) {
									$sOfferOnPages .= $oPageRow->pageName." [A] ".',';
								} else {
									$sOfferOnPages .= $oPageRow->pageName." [I] ".',';
								}
							}
						}
						if ($sOfferOnPages != '') {
							$sOfferOnPages = substr($sOfferOnPages, 0, strlen($sOfferOnPages)-1);
						}
						
						if ($sMode == 'A' && $iIsLive == '1') {
							$sEmailSubject = "Offer Up - " . $sOfferCode;
							$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
							$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer code has gone up";
							$sEmailMessage .= "\r\nOffer Rate: $fRevPerLead";
							
							$sOfferOnPages = str_replace(",", "\n",$sOfferOnPages);
							
							$sEmailMessage .= "\r\n\nOffer On Pages: \n$sOfferOnPages";
							$sEmailMessage .= "\r\n\r\nA = Active Pages     I = Inactive Pages";
							$sEmailMessage .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
							
							// insert offersLog - START
							$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
									  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sTempOldModeValue\", \"Offer Up\", \"Offer Status\")";
							$rOfferLogResult = dbQuery($sOfferLogQuery);
							// insert offersLog - END
						} else {
							$sEmailSubject = "Offer Down - " . $sOfferCode;
							$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
							$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer code has been taken down";
							$sEmailMessage .= "\r\nOffer Rate: $fRevPerLead";
							
							$sOfferOnPages = str_replace(",", "\n",$sOfferOnPages);
							
							$sEmailMessage .= "\r\n\nOffer On Pages: \n$sOfferOnPages";
							$sEmailMessage .= "\r\n\r\nA = Active Pages     I = Inactive Pages";
							$sEmailMessage .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
							
							// insert offersLog - START
							$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
									  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sTempOldModeValue\", \"Offer Down\", \"Offer Status\")";
							$rOfferLogResult = dbQuery($sOfferLogQuery);
							// insert offersLog - END
						}
					
						
						// get the recipients
						$sRecQuery = "SELECT *
									  FROM   emailRecipients
									  WHERE  purpose = 'Offer status change'";
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
					
					
					$sHttpPostString = addslashes($sHttpPostString);
					$sHeaderText = addslashes($sHeaderText);
					$sFooterText = addslashes($sFooterText);
					$sLeadsQuery = addslashes($sLeadsQuery);
					$sLeadsEmailBody = addslashes($sLeadsEmailBody);
					$sSingleEmailBody = addslashes($sSingleEmailBody);
					$sLeadsInstruction = addslashes($sLeadsInstruction);
					// check if record exists
				$sCheckQuery = "SELECT *
								FROM   offerLeadSpec
								WHERE  offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				$iCheckNumRows = dbNumRows($rCheckResult);

				echo dbError();
				if ($iCheckNumRows == 0 ) {
					$sInsertQuery = "INSERT INTO offerLeadSpec(offerCode, leadsGroupId, lastLeadDate,
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
													'$sFieldDelimiter', \"$sFieldSeparater\", \"$sEndOfLine\", \"$sLeadsEmailSubject\", \"$sLeadsEmailFromAddr\",\"$sLeadsEmailBody\", 
													\"$sSingleEmailSubject\", \"$sSingleEmailFromAddr\", \"$sSingleEmailBody\", \"$sTestEmailRecipients\",
													\"$sCountEmailRecipients\", \"$sLeadsEmailRecipients\", \"$sLeadsInstruction\",\"$iSeparateLeadFile\")";
					$rResult = dbQuery($sInsertQuery);
					
				} else {
					// update lead spec entry
					$sLeadSpecUpdateQuery = "UPDATE offerLeadSpec
											 SET    leadsGroupId = '$iLeadsGroupId', 
													lastLeadDate = '$sLastLeadDate',													
													processingDays = '$sProcessingDays', 
													deliveryMethodId = '$iDeliveryMethodId',
													maxAgeOfLeads = '$iMaxAgeOfLeads',
													postingUrl = \"$sPostingUrl\", 
													httpPostString = \"$sHttpPostString\",
													ftpSiteUrl = '$sFtpSiteUrl', 
													initialFtpDirectory = \"$sInitialFtpDirectory\",													
													userId = '$sUserId', 
													passwd = '$sPasswd', 
													leadFileName ='$sLeadFileName',
													isEncrypted = '$iIsEncrypted', 
													encMethod = '$sEncMethod', 
													encType = '$sEncType',
													encKey = \"$sEncKey\", 
													headerText = \"$sHeaderText\", 
													footerText =  \"$sFooterText\", 
													leadsQuery = \"$sLeadsQuery\",
													fieldDelimiter = '$sFieldDelimiter', 
													fieldSeparater = \"$sFieldSeparater\", 
													endOfLine = \"$sEndOfLine\", 
													leadsEmailSubject = \"$sLeadsEmailSubject\", 
													leadsEmailFromAddr = \"$sLeadsEmailFromAddr\",
													leadsEmailBody = \"$sLeadsEmailBody\",
													singleEmailSubject = \"$sSingleEmailSubject\",
												    singleEmailFromAddr = \"$sSingleEmailFromAddr\", 
													singleEmailBody = \"$sSingleEmailBody\",					
													testEmailRecipients = \"$sTestEmailRecipients\",
													countEmailRecipients = \"$sCountEmailRecipients\", 
													leadsEmailRecipients = \"$sLeadsEmailRecipients\", 
													leadsInstruction = \"$sLeadsInstruction\",
													separateLeadFile = \"$iSeparateLeadFile\"
											 WHERE  offerCode = '$sOfferCode'";
					//echo $sLeadSpecUpdateQuery;
					//exit;
					$rLeadSpecUpdateResult = dbQuery($sLeadSpecUpdateQuery);
					
					echo dbError();
				}

					if ($iIsCap) {
						// check if capcount entry exists
						$sCapStartDate = "$iCapStartYear-$iCapStartMonth-$iCapStartDay";
						$sCapCheckQuery = "SELECT *
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
						}
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
								$sInsertQuery = "INSERT IGNORE INTO pageMap (pageId, offerCode, sortOrder)
												 VALUES('".$aPagesArray[$i]."', '$sOfferCode', '0')";
								$rInsertResult = dbQuery($sInsertQuery);
								$sGetPageNameFromId .= "'".$aPagesArray[$i]."',";
								$iNumRowsInserted++;
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
								
								$sInsertQuery = "INSERT IGNORE INTO categoryMap (categoryId, offerCode)
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
			
			if (!(is_dir("$sGblOfferImagesPath/$sOfferCode")) ) {				
				mkdir("$sGblOfferImagesPath/$sOfferCode",0777);
				chmod("$sGblOfferImagesPath/$sOfferCode",0777);
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
				$sNewImageFile  = "$sGblOfferImagesPath/$sOfferCode/$sImageFileName";
				
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
				$sNewImageFile  = "$sGblOfferImagesPath/$sOfferCode/$sSmallImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
								 SET    smallImageName = '$sSmallImageFileName'
								 WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				} else {
					$sMessage = "Small Image Should Be Maximum Of 88 W x 31 H Size Only...";
					$bKeepValues = true;
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
			
			
			
			// upload page1 small image if selected
			if ($_FILES['single_page_header_image']['tmp_name'] && $_FILES['single_page_header_image']['tmp_name']!="none") {
				//echo  $_FILES['image']['type'];
				$sUploadedFileName = $_FILES['single_page_header_image']['tmp_name'];
				$sFileSize = $_FILES['single_page_header_image']['size'];
				$aImageSize = getimagesize($sUploadedFileName);
				//if ( $aImageSize[0] <= 88 && $aImageSize[1] <= 31) {
								
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['single_page_header_image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sSinglePageImageFileName = $sOfferCode."_single_page_header". ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagesPath/$sOfferCode/$sSinglePageImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);
				
				// store the image file name in database
				$sUpdateQuery = "UPDATE offers
								 SET    singleOfferPageHeaderImage = '$sSinglePageImageFileName'
								 WHERE  id = '$iOfferId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				/*} else {
					$sMessage = "Small Image Should Be Maximum Of 88 W x 31 H Size Only...";
					$bKeepValues = true;
				}
				*/
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
					$sMessage = "They Host Header Image Should Be Maximum of 550 W x 100 H Size...";
					$bKeepValues = true;
					$sIsOpenTheyHost = 'Y';
				}
			}
			
			
			
			// upload page2 image/s
			if ($_FILES['page2Image']['tmp_name'] && $_FILES['page2Image']['tmp_name']!="none") {
				
				$sUploadedFileName = $_FILES['page2Image']['tmp_name'];
				
				//Get Extention
				$aImageFileNameArray = explode(".",$_FILES['page2Image']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileExt = $aImageFileNameArray[$i];
				
				$sImageFileName = $sOfferCode."_". time(). ".$sImageFileExt";
				$sNewImageFile  = "$sGblOfferImagesPath/$sOfferCode/$sImageFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewImageFile);											
			}
			
			// Find out on which page this offerCode will appear, set ORDER BY as offercode
			// and go to that page
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
			
			$sFilter = ascii_encode(stripslashes($sFilter));
			$sExclude = ascii_encode(stripslashes($sExclude));
	
	
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
				$iPartnerId = "";
				$sHeadline = "";
				$sDescription = "";
				$sShortDescription = "";
				$iIsNonRevenue = "";
				$fRevPerLead = "";
				$fActualRevPerLead = "";
				$sImageName = "";
				$sSmallImageFileName = '';
				$iAutoRespEmail = "";
				$sIsTarget = "";
				$sTargetIncludeExclude = '';
				$sAutoRespEmailFormat = "";
				$sAutoRespEmailSub = "";
				$sAutoRespEmailBody = "";
				$sAutoRespEmailFromAddr = "";
				$sNotes = "";
				$sRestrictions = '';
				$sAddiInfoFormat = "";
				$sAddiInfoTitle = "";
				$sAddiInfoText = "";
				$sAddiInfoPopupSize = "";
				$iPrecheckAllPages = "";
				$iPage2Info = "";			
				$sMode = "";
				$iIsLive = "";
				$sActiveDateTime = "";
				$sInactiveDateTime = "";
				$sPage2Template = "";
				$sPage2JavaScript = "";
				$sNewPage2JavaScript = "";
				$iDeliveryMethodId = "";
				
				// reset cap variables
				$sCapStartDate = "";
				$iMaxCap = "";
				$sCap1PeriodType = "";
				$iCap1PeriodInterval = "";
				$iCap1Max = "";
				$sCap2PeriodType = "";
				$iCap2PeriodInterval = "";
				$iCap2Max = "";
			
				// reset lead spec variables
				$iLeadsGroupId = "";
				$iMaxAgeOfLeads = "";
				$sLastLeadDate = "";
				$sRerunStartDate = "";
				$sRerunEndDate = "";
				$sProcessingDays = "";
				$iDeliveryMethodId = "";
				$sPostingUrl = "";
				$sHttpPostString = "";
				$sFtpSiteUrl = "";
				$sInitialFtpDirectory = "";
				$sUserId = "";
				$sPasswd = "";
				$sLeadFileName = "";
				$iIsEncrypted = "";
				$sEndMethod = "";
				$sEncKey = "";
				$sHeaderText = "";
				$sFooterText = "";
				$sLeadsQuery = "";
				$sFieldDelimiter = "";
				$sFieldSeparater = "";
				$sEndOfLine = "";
				$sLeadsEmailSubject = "";
				$sLeadsEmailFromAddr = "";
				$sLeadsEmailBody = "";
				$sSingleEmailSubject = "";
				$sSingleEmailFromAddr = "";
				$sSingleEmailBody = "";				
				$sTestEmailRecipients = "";
				$sCountEmailRecipients = "";
				$sLeadsEmailRecipients = "";
				$sLeadsInstruction = "";
				$iSeparateLeadFile = "";
				$iIsCap = '';
				$iDefaultSortOrder = '';
				$iApiAvailable = '';
				$iExcludeFromMasterPage = '';			}
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
				$iPartnerId = $oRow->partnerId;
				$sHeadline = ascii_encode($oRow->headline);
				$sDescription = ascii_encode($oRow->description);
				$sShortDescription = ascii_encode($oRow->shortDescription);
				$iIsNonRevenue = $oRow->isNonRevenue;
				$fRevPerLead = $oRow->revPerLead;
				$fActualRevPerLead = $oRow->actualRevPerLead;
				$sNotes = ascii_encode($oRow->notes);
				$iAutoRespEmail = $oRow->autoRespEmail;
				$sAutoRespEmailFormat = $oRow->autoRespEmailFormat;
				$sAutoRespEmailSub = ascii_encode($oRow->autoRespEmailSub);			
				$sAutoRespEmailBody = ascii_encode($oRow->autoRespEmailBody);
				$sAutoRespEmailFromAddr = $oRow->autoRespEmailFromAddr;
				$sAddiInfoFormat = $oRow->addiInfoFormat;
				$sAddiInfoTitle = ascii_encode($oRow->addiInfoTitle);
				$sAddiInfoText = ascii_encode($oRow->addiInfoText);
				$sAddiInfoPopupSize = $oRow->addiInfoPopupSize;
				$iPrecheckAllPages = $oRow->precheckAllPages;
				$iPage2Info = $oRow->page2Info;
				$sMode = $oRow->mode;
				$iIsLive = $oRow->isLive;
				$sActiveDateTime = $oRow->activeDateTime;
				$sInactiveDateTime = $oRow->inactiveDateTime;
				$sPage2Template = ascii_encode($oRow->page2Template);
				$sPage2JavaScript = ascii_encode($oRow->page2JavaScript);
				$sNewPage2JavaScript = ascii_encode($oRow->newPage2JavaScript);
				$iIsCap = $oRow->isCap;
				$iDefaultSortOrder = $oRow->defaultSortOrder;
				$iApiAvailable = $oRow->apiAvailable;
				$iExcludeFromMasterPage = $oRow->excludeFromMasterPage;
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
				$sPassOnCodeVarMap = $oRow->theyHostPassOnCodeVarMap;
				$sIsRequiredSSL = $oRow->isRequireSSL;
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
				$sCurrentImage = "<img src='$sGblOfferImagesUrl/$sOfferCode/$oRow->imageName'>";
			} else {
				$sCurrentImage = "No Image";
			}
			
			if ($oRow->smallImageName != '') {
				$sCurrentImage .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
									Small Image &nbsp; &nbsp; &nbsp; <img src='$sGblOfferImagesUrl/$sOfferCode/$oRow->smallImageName'>";
			}
			
			if ($oRow->singleOfferPageHeaderImage != '') {
				$sSinglePageCurrentImage = "<img src='$sGblOfferImagesUrl/$sOfferCode/$oRow->singleOfferPageHeaderImage'>";
			}
			
		}				
		
		// get capCounts Data
		if ($bKeepValues != "true") {
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
			$iCap2Max = $oRow->cap2Max;
		}
		
		// get lead spec data
		
		$sLeadSpecQuery = "SELECT *
						   FROM   offerLeadSpec
						   WHERE  offerCode = '$sOfferCode'";
		$rLeadSpecResult = dbQuery($sLeadSpecQuery);
		while ($oLeadSpecRow = dbFetchObject($rLeadSpecResult)) {
			$iLeadsGroupId = $oLeadSpecRow->leadsGroupId;
			$sLastLeadDate = $oLeadSpecRow->lastLeadDate;
			$sRerunStartDate = $oLeadSpecRow->rerunStartDate;
			$sRerunEndDate = $oLeadSpecRow->rerunEndDate;
			$sProcessingDays = $oLeadSpecRow->processingDays;
			$iDeliveryMethodId = $oLeadSpecRow->deliveryMethodId;
			$iMaxAgeOfLeads = $oLeadSpecRow->maxAgeOfLeads;
			$sPostingUrl = $oLeadSpecRow->postingUrl;
			$sHttpPostString = $oLeadSpecRow->httpPostString;
			$sFtpSiteUrl = $oLeadSpecRow->ftpSiteUrl;
			$sInitialFtpDirectory = $oLeadSpecRow->initialFtpDirectory;			
			$sUserId = $oLeadSpecRow->userId;
			$sPasswd = $oLeadSpecRow->passwd;
			$sLeadFileName = $oLeadSpecRow->leadFileName;
			$iIsEncrypted = $oLeadSpecRow->isEncrypted;
			$sEncMethod = $oLeadSpecRow->encMethod;
			$sEncKey = $oLeadSpecRow->encKey;
			$sHeaderText = $oLeadSpecRow->headerText;
			$sFooterText = $oLeadSpecRow->footerText;
			$sLeadsQuery = $oLeadSpecRow->leadsQuery;
			$sFieldDelimiter = $oLeadSpecRow->fieldDelimiter;
			$sFieldSeparater = $oLeadSpecRow->fieldSeparater;
			$sEndOfLine = $oLeadSpecRow->endOfLine;
			$sLeadsEmailSubject = $oLeadSpecRow->leadsEmailSubject;
			$sLeadsEmailFromAddr = $oLeadSpecRow->leadsEmailFromAddr;
			$sLeadsEmailBody = $oLeadSpecRow->leadsEmailBody;
			$sSingleEmailSubject = $oLeadSpecRow->singleEmailSubject;
			$sSingleEmailFromAddr = $oLeadSpecRow->singleEmailFromAddr;
			$sSingleEmailBody = $oLeadSpecRow->singleEmailBody;
			$sTestEmailRecipients = $oLeadSpecRow->testEmailRecipients;
			$sCountEmailRecipients = $oLeadSpecRow->countEmailRecipients;
			$sLeadsEmailRecipients = $oLeadSpecRow->leadsEmailRecipients;
			$sLeadsInstruction = $oLeadSpecRow->leadsInstruction;
			$iSeparateLeadFile = $oLeadSpecRow->separateLeadFile;
	
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
	$sAddiInfoTitle = ascii_encode(stripslashes($sAddiInfoTitle));
	$sAddiInfoText = ascii_encode(stripslashes($sAddiInfoText));
	$sPage2Template = ascii_encode(stripslashes($sPage2Template));
	$sPage2JavaScript = stripslashes($sPage2JavaScript);
	$sNewPage2JavaScript = stripslashes($sNewPage2JavaScript);
	$sRestrictions = ascii_encode(stripslashes($sRestrictions));
	//$sLeadsQuery = stripslashes($sLeadsQuery);

	// set default values
	if ($sLeadFileName == '') {
		$sLeadFileName = "[offerCode]_[mm]_[dd]_[yyyy]_Ampere.csv";
	}
	if ($sTestEmailRecipients == '') {
		$sTestEmailRecipients = $sSesEmail;
	}
	if ($sLeadsEmailSubject == '') {
		$sLeadsEmailSubject = "Ampere Media - [offerCode], [count] [mm]-[dd]-[yyyy]";	
	}
	if ($sSingleEmailSubject == '') {
		$sSingleEmailSubject = "Ampere Media - [offerCode], [mm]-[dd]-[yyyy]";	
	}
	if ($sAddiInfoPopupSize == '') {
		$sAddiInfoPopupSize = "500,400";
	}
	if ($sLeadsEmailFromAddr == '') {
		$sLeadsEmailFromAddr = 'Ampere Media Leads <leads@AmpereMedia.com>';
	}
	if ($sSingleEmailFromAddr == '') {
		$sSingleEmailFromAddr = 'Ampere Media Lead <leads@AmpereMedia.com>';
	}
	if ($sLeadsEmailBody == '') {
		$sLeadsEmailBody = "[offerCode] - [count]";
	}
	
	if ($iMaxAgeOfLeads == '') {
		$iMaxAgeOfLeads = 30;
	}
	
	
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

if ($iId != '') {
	$sOfferCodeField = "$sOfferCode";
} else {
	$sOfferCodeField = "<input type=text name=sOfferCode value='$sOfferCode'><BR>
							OfferCode must contain AlphaNumeric characters, - or _ only and maximum 25 chars long.";
}

// prepare company options
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

// prepare partner options
$sPartnerQuery = "SELECT   id, companyName, code
				   FROM     partnerCompanies
				   ORDER BY companyName";
$rPartnerResult = dbQuery($sPartnerQuery);

$sPartnerOptions .= "<option value=''>Select Partner";
while ( $oPartnerRow = dbFetchObject($rPartnerResult)) {
	if ($oPartnerRow->id == $iPartnerId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sPartnerOptions .= "<option value='".$oPartnerRow->id."' $sSelected>".$oPartnerRow->companyName . " - " . $oPartnerRow->code;
}


$sIsNonRevenueChecked = '';
if ($iIsNonRevenue) {
	$sIsNonRevenueChecked = "checked";
}

$sAutoRespEmailChecked = '';
if ($iAutoRespEmail) {
	$sAutoRespEmailChecked = "checked";
}

/********************** Start Targeting **********************/
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
/********************** End Targeting **********************/

$sPrecheckAllPagesChecked = '';
if ($iPrecheckAllPages) {
	$sPrecheckAllPagesChecked = "checked";
}


$sPage2InfoChecked = '';
if ($iPage2Info) {
	$sPage2InfoChecked = "checked";
}

$sIsLiveChecked = '';
if ($iIsLive) {
	$sIsLiveChecked = "checked";
}

$sIsCapChecked = '';
if ($iIsCap) {
	$sIsCapChecked = "checked";
}


$sApiAvailableChecked = '';
if ($iApiAvailable) {
	$sApiAvailableChecked = "checked";
}


$sExcludeFromMasterPageChecked = '';
if ($iExcludeFromMasterPage) {
	$sExcludeFromMasterPageChecked = "checked";
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


// prepare addi info email format options
$sHtmlSelected = "";
$sTextSelected = "";
switch ($sAddiInfoFormat) {			
	case "html":
		$sHtmlSelected = "selected";
		break;
	case "text":
		$sTextSelected = "selected";		
		break;
}
$sAddiInfoFormatOptions = "<option value='' >
							<option value='text' $sTextSelected>Text
							<option value='html' $sHtmlSelected>Html";


// prepare mode options

$sActiveSelected = "";
$sTestSelected = "";
$sDownSelected = "";

switch ($sMode) {
	case "A":
	$sActiveSelected = "selected";
	break;
	case "T":
	$sTestSelected = "selected";
	break;
	case "P":
	$sApiOnlySelected = "selected";
	break;
	default:
	$sDownSelected = "selected";
}

$sModeOptions = "<option value='A' $sActiveSelected>Active
				<option value='T' $sTestSelected>Test
				<option value='I' $sDownSelected>Down
				<option value='P' $sApiOnlySelected>API Only";
//<option value='T' $sTestSelected>Test
// set curr date values to be selected by default
if ($iId == '' && !($saveClose || $sSaveContinue)) {
	
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


$sLastLeadDayOptions = "";

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


/*********************** LEAD SPECIFICATIONS SECTION *******************************/

$sGroupQuery = "SELECT *
				FROM   leadGroups
				ORDER BY name";
$rGroupResult = dbQuery($sGroupQuery);
$sLeadsGroupOptions = "<option value='' selected>";
while ($oGroupRow = dbFetchObject($rGroupResult)) {
	if ($oGroupRow->id == $iLeadsGroupId) {
		$sGroupIdSelected = "selected";
	} else {
		$sGroupIdSelected = "";
	}
	
	$sLeadsGroupOptions .= "<option value='".$oGroupRow->id."' $sGroupIdSelected>$oGroupRow->name - $oGroupRow->leadFileName";
}


// prepare options for delivery methods
$sMethodQuery = "SELECT *
				 FROM   deliveryMethods
				 ORDER BY method";
$rMethodResult = dbQuery($sMethodQuery);
while ($oMethodRow= dbFetchObject($rMethodResult)) {
	if ($oMethodRow->id == $iDeliveryMethodId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sDeliveryMethodOptions .= "<option value=$oMethodRow->id $sSelected>$oMethodRow->method";
	
}

if ($iIsEncrypted) {
	$sIsEncryptedChecked = "checked";
} else {
	$sIsEncryptedChecked = "";
}

// prepare field delimiter options
$sDblQteSelected = "";
$sNoDelimiterSelected = "";

switch ($sFieldDelimiter) {
	case "\"":
	$sDblQteSelected = "selected";
	break;
	default:
	$sNoDelimiterSelected = "selected";
}

$sFieldDelimiterOptions = "<option value='' $sNoDelimiterSelected>No Delimiter
							<option value='\"' $sDblQteSelected>\"";

$sTabSelected = "";
$sCommaSelected = "";
$sPipeSelected = "";
$sTildSelected = "";
$sNLSelected = "";
$sNoSeparaterSelected = "";
// prepare field separater options
switch ($sFieldSeparater) {
	case "\t":
	$sTabSelected = "selected";
	break;
	case "|":
	$sPipeSelected = "selected";
	break;
	case "\n":
	$sNLSelected = "selected";
	break;
	case "~":
	$sTildSelected = "selected";
	break;
	case "":
	$sNoSeparaterSelected = "selected";
	break;
	case ",":	
	default:
	$sCommaSelected = "selected";
}

$sFieldSeparaterOptions = "<option value=',' $sCommaSelected>Comma
  							<option value='|' $sPipeSelected>|  							
							<option value='\\t' $sTabSelected>Tab
							<option value='~' $sTildSelected>~  	
							<option value='\\n' $sNLSelected>\\n
							<option value='' $sNoSeparaterSelected>No Separater";

// prepare end of line options
$sNLCRSelected = "";
$sNLSelected = "";
$sNoneSelected = "";

switch ($sEndOfLine) {
	case "":
	$sNoneSelected = "selected";
	break;
	case "\\n":
	$sNLSelected = "selected";
	break;		
	default:
	$sNLCRSelected = "selected";
}
$sEndOfLineOptions = "<option value='\\r\\n' $sNLCRSelected>\\r\\n
					  <option value='\\n' $sNLSelected>\\n
					  <option value='' $sNoneSelected>";


// prepare enc method options
$sGpgSelected = "";
switch ($sEncMethod) {
	case "gpg":
		$sGpgSelected = "selected";
		break;
		
}

$sEncMethodOptions .= "<option value=''>
					   <option value='gpg' $sGpgSelected>GPG";

// prepare enc type options
$sTextEncTypeSelected = "";
$sBinaryEncTypeSelected = "";
switch ($sEncType) {
	case "text":
	$sTextEncTypeSelected = "selected";
	break;
	case "binary":
	$sBinaryEncTypeSelected = "";	
	break;
}
$sEncTypeOptions .= "<option value=''>
					<option value='text' $sTextEncTypeSelected>Text
					<option value='binary' $sBinaryEncTypeSelected>Binary";

//echo $sProcessingDays;
// prepare processing days checkboxes
$sProcessingDaysCheckboxes = '';

for ($i=0;$i<count($aGblWeekDaysArray)-2;$i++) {
	//$j=$i+1;
		
	if (strstr($sProcessingDays,"$i") !='') {
		$sDayChecked = "checked";
	} else {
		$sDayChecked = '';
	}
	
	$sProcessingDaysCheckboxes .= "<input type=checkbox name=iProcessingDay[] value='$i' $sDayChecked>$aGblWeekDaysArray[$i] &nbsp; ";
}

/*********************** END LEAD SPECIFICATIONS SECTION *******************************/

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

// remove additional slashes if any
/*$sHeadline = stripslashes($sHeadline);
$sDescription = stripslashes($sDescription);
$sAutoRespEmailSub = stripslashes($sAutoRespEmailSub);
$sAutoRespEmailBody = stripslashes($sAutoRespEmailBody);
$sNotes = stripslashes($sNotes);
$sAddiInfoTitle = stripslashes($sAddiInfoTitle);
$sAddiInfoText = stripslashes($sAddiInfoText);
$sPage2Template = stripslashes($sPage2Template);
*/

// prepare select options for Copy Template From another Offer
$sOffersQuery = "SELECT *
				 FROM   offers
				 WHERE  offerCode != '$sOfferCode'";
$rOffersResult = dbQuery($sOffersQuery);
$sTemplateOptions .= "<option value=''>";
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	$sTempOfferCode = $oOffersRow->offerCode;
	$sTemplateOptions .= "<option value=$sTempOfferCode>$sTempOfferCode";
}

// prepare offers list 

$sOffersQuery = "SELECT *
				 FROM   offers
				 ORDER BY offerCode";
$rOffersResult = dbQuery($sOffersQuery);
$sOffersOptions = "";
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	$iTempOfferId = $oOffersRow->id;
	$sTempOfferCode = $oOffersRow->offerCode;
	if ($sTempOfferCode == '$sOfferCode' || $iTempOfferId == $iId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sOffersOptions .= "<option value='$iTempOfferId' $sSelected>$sTempOfferCode";
}


$sPage2FieldsLink = "<a href='JavaScript:openWin(\"listMapFields.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode\");'>Page2 Fields</a>";


$sPage2PreviewLink = "<a href='JavaScript:openWin(\"preview.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode&sPreview=page2\");'>Page2 Preview</a>";
$sAutoRespPreviewLink = "<a href='JavaScript:openWin(\"preview.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode&sPreview=autoResp\");'>Auto Responder Preview</a>";
//$sAddiInfoPreviewLink = "<a href='JavaScript:openWin(\"preview.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode&sPreview=addiInfo\");'>Additional Info Preview</a>";
$sAddiInfoPreviewLink = "<a href=\"javascript:void(window.open('http://www.popularliving.com/offerAddiInfo.php?sOfferCode=$sOfferCode','addiInfo','height=600,width=400,scrollbars=yes, resizable=yes'))\">Additional Info Preview</a><BR>";
$sAddiInfoPreviewLink .= htmlentities("<a style=\"font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #0000EF\"  href=\"javascript:void(window.open('http://www.popularliving.com/offerAddiInfo.php?sOfferCode=$sOfferCode','addiInfo','height=600,width=400,scrollbars=yes, resizable=yes'))\">Additional Info Preview</a>");
//<a style="font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #0000EF" href="javascript:void(window.open('http://www.popularliving.com/offerAddiInfo.php?sOfferCode=WCTM_TUL','addiInfo','height=600,width=400,scrollbars=yes, resizable=yes'))">



// delete the image if clicked on the delete link

if ($sDeleteImage) {
	unlink("$sGblOfferImagesPath/$sOfferCode/$sDeleteImage");
}

// get list of page2 images, if offer is selected to edit
if ($sOfferCode && $iId) {
$rImageDir = opendir("$sGblOfferImagesPath/$sOfferCode");
if ($rImageDir) {
	//echo $rImageDir;
	while (($sFile = readdir($rImageDir)) != false) {	
		if (!is_dir("$sGblOfferImagesPath/$sOfferCode/$sFile")) {
					
			$page2ImagesList .=  "<a href='JavaScript:void(window.open(\"$sGblOfferImagesUrl/$sOfferCode/$sFile\",\"\",\"\"));'>$sGblOfferImagesUrl/$sOfferCode/$sFile</a> 
					&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sOfferCode=$sOfferCode&iRecPerPage=$iRecPerPage&";
			$page2ImagesList .="sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&sDeleteImage=$sFile'>Delete</a><BR>";
		}
	}
	
	
}
}


$sSeparateLeadFileChecked = '';
if ($iSeparateLeadFile) {
	$sSeparateLeadFileChecked = "checked";
}

$sSepareteFileLink = "<a href='JavaScript:void(window.open(\"addSeparateFile.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode\",\"separateFile\",\"\"));'>Separate Lead File Spec</a>";

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

<script lanugage=JavaScript>

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

document.form1.submitClicked.value=1;
return true;
}
</script>

<body>

<table width=85% align=center>
<tr><Td class=message align=center colspan=2><?php echo $sMessage;?>
</td></tr></table>	


<script language=JavaScript>


function openWin(winUrl) {
	checkForm();
	var temp = window.open(winUrl,'','');		
}

/* function to define leadfile name using groupname instead of offerCode */
/*
function groupChanged() {	
	var gId = document.form1.iLeadsGroupId.selectedIndex;
var grpSelected = document.form1.iLeadsGroupId.options[gId].value;
	
	if (grpSelected != '') {
		document.form1.sLeadFileName.disabled=true;
		document.form1.iDeliveryMethodId.disabled=true;		
		document.form1.sPostingUrl.disabled = true;
		document.form1.sFtpSiteUrl.disabled = true;
		document.form1.sInitialFtpDirectory.disabled = true;
		document.form1.iIsSecured.disabled = true;
		document.form1.sUserId.disabled = true;
		document.form1.sPasswd.disabled = true;
		document.form1.iIsEncrypted.disabled = true;
		document.form1.sEncType.disabled = true;
		document.form1.sEncMethod.disabled = true;
		document.form1.sEncKey.disabled = true;
		document.form1.sHeaderText.disabled = true;
		document.form1.sFooterText.disabled = true;
		document.form1.sLeadsEmailSubject.disabled = true;
		document.form1.sLeadsEmailFromAddr.disabled = true;
		document.form1.sTestEmailRecipients.disabled = true;
		document.form1.sCountEmailRecipients.disabled = true;
		document.form1.sLeadsEmailRecipients.disabled = true;
		for (i=0;i<5;i++) {
		document.form1["iProcessingDay[]"][i].disabled = true;	
		}
		
	} else {
		
		document.form1.sLeadFileName.disabled=false;
		document.form1.iDeliveryMethodId.disabled=false;		
		document.form1.sPostingUrl.disabled = false;
		document.form1.sFtpSiteUrl.disabled = false;
		document.form1.sInitialFtpDirectory.disabled = false;
		document.form1.iIsSecured.disabled = false;
		document.form1.sUserId.disabled = false;
		document.form1.sPasswd.disabled = false;
		document.form1.iIsEncrypted.disabled = false;
		document.form1.sEncType.disabled = false;
		document.form1.sEncMethod.disabled = false;
		document.form1.sEncKey.disabled = false;
		document.form1.sHeaderText.disabled = false;
		document.form1.sFooterText.disabled = false;
		document.form1.sLeadsEmailSubject.disabled = false;
		document.form1.sLeadsEmailFromAddr.disabled = false;
		document.form1.sTestEmailRecipients.disabled = false;
		document.form1.sCountEmailRecipients.disabled = false;
		document.form1.sLeadsEmailRecipients.disabled = false;
		for (i=0;i<5;i++) {
		document.form1["iProcessingDay[]"][i].disabled = false;	
		}
	}
}
*/
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

function testQuery() {

	checkForm();

var query = document.form1.sLeadsQuery.value;
var testQueryLink = "testQuery.php?sQuery=" + query;
var winOpen = window.open(testQueryLink,"testQuery","height=450, width=600, scrollbars=yes, resizable=yes, status=yes")

}



function viewGroupInfo() {	

var iGroupSelIndex = document.form1.iLeadsGroupId.selectedIndex;
if (iGroupSelIndex !=0) {
var iGroupSel = document.form1.iLeadsGroupId.options[iGroupSelIndex].value;

var viewGroupLink = '<?php echo "$sGblAdminSiteRoot/leadGroups/addGroup.php?iMenuId=21&iId=";?>'+ iGroupSel;
var winOpen = window.open(viewGroupLink,"leadGroup","height=450, width=600, scrollbars=yes, resizable=yes, status=yes")
} else {
	alert("Please Select A Group To View The Group Information");
}
}

function funcChangeOffer() {
	
var iOfferSelIndex = document.form1.iOfferId.selectedIndex;
if (iOfferSelIndex !=0) {
var iOfferSel = document.form1.iOfferId.options[iOfferSelIndex].value;
var newLink = '<?php echo "$sGblAdminSiteRoot/offersMgmnt/addOffer.php?iMenuId=$iMenuId&iId=";?>' + iOfferSel;
window.location.replace(newLink, '',"height=450, width=600, scrollbars=yes, resizable=yes, status=yes");	
return true;
}
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
<input type=hidden name=submitClicked>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
		<td class=header>Offer Specifications for</td><td colspan=3> <select name=iOfferId onChange='funcChangeOffer();'><?php echo $sOffersOptions;?></select></td>
	</tr>
	<tr><td align=right>Offer Code</td>
		<td><?php echo $sOfferCodeField;?></td>
		<td width=15% align=right valign=top>Offer Name</td>
		<td width=25% valign=top><input type=text name=sName value='<?php echo $sName;?>'></td>
	</tr>
	<tr>
	<td width=15% align=right>Company</td>
		<td width=25%><select name=iCompanyId>
		<?php echo $sCompanyOptions;?>
			</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/offerCompanies/addCompany.php?iMenuId=15&sReturnTo=iCompanyId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Company</a></td>
			<td width=15% align=right valign=top >Partner</td>
		<td width=25% valign=top><select name=iPartnerId>
		<?php echo $sPartnerOptions;?>
			</select></td>
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
	<tr>
		<td align=right>Single Offer Page Header Image</td>
		<td colspan=3><input type=file name='single_page_header_image'>
		</td>
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
					<td colspan=3>$sCurrentImage</td></tr>
			  <tr><td align=right>Single Offer Page Current Header Image</td>
				 	<td>$sSinglePageCurrentImage</td></tr>";
	}
	?>
	
	
	<tr><td align=right>Notes</td>
		<td colspan=3><textarea name=sNotes rows=3 cols=70><?php echo $sNotes;?></textarea></td>
	</tr>	
	<tr><td align=right>Auto Responder Email</td>
		<td><input type=checkbox name=iAutoRespEmail value='1' <?php echo $sAutoRespEmailChecked;?>>
		&nbsp; &nbsp; Auto Responder Email Format &nbsp; <select name=sAutoRespEmailFormat>
		<?php echo $sAutoRespEmailFormatOptions;?>
		</select></td>
		<td align=right>Auto Responder Email Subject</td>
		<td><input type=text name=sAutoRespEmailSub value='<?php echo $sAutoRespEmailSub;?>' size=35></td>
	</tr>	
	<Tr><td align=right>Auto Responder Email From Address</td><td colspan=3><input type=text name=sAutoRespEmailFromAddr value='<?php echo $sAutoRespEmailFromAddr;?>' size=35></td></tr>
	<tr>
	<td align=right>Auto Responder Email Body</td>
		<td colspan=3><?php echo $sAutoRespPreviewLink;?><BR><textarea name=sAutoRespEmailBody rows=5 cols=80><?php echo $sAutoRespEmailBody;?></textarea><BR>
			[EMAIL] will be replaced with user's email address while sending the email.</td>
	</tr>	
	<tr><td align=right>Addi Info Format</td>
		<td><select name=sAddiInfoFormat>
		<?php echo $sAddiInfoFormatOptions;?>
		</select></td>
		
		<td align=right>Additional Info. Popup Size</td>
		<td><input type=text name=sAddiInfoPopupSize value='<?php echo $sAddiInfoPopupSize;?>'> width,height</td>
	</tr>	
	<tr><td align=right>Additional Info. Title</td>
		<td colspan=3><input type=text name=sAddiInfoTitle value='<?php echo $sAddiInfoTitle;?>' size=70></td>
	</tr>
	<tr><td align=right>Additional Info. Text/Html</td>
		<td colspan=3><?php echo $sAddiInfoPreviewLink;?><BR><textarea name=sAddiInfoText rows=5 cols=80><?php echo $sAddiInfoText;?></textarea></td>
	</tr>	
	
	<tr><td align=right>Effective Rate</td>
		<td><input type=text name=fRevPerLead value='<?php echo $fRevPerLead;?>'> $</td>
		<td align=right>Is Non-Revenue</td>
		<td><input type=checkbox name=iIsNonRevenue value='1' <?php echo $sIsNonRevenueChecked;?>></td>
	</tr>	
	<tr><td align=right>Pay Rate</td>
		<td colspan=3><input type=text name=fActualRevPerLead value='<?php echo $fActualRevPerLead;?>'> $</td>
		
	</tr>	
	<tr><td align=right>Prechecked On All Pages</td>
		<td><input type=checkbox name=iPrecheckAllPages value='1' <?php echo $sPrecheckAllPagesChecked;?>></td>
	<td align=right>Page 2 Info</td>
		<td><input type=checkbox name=iPage2Info value='1' <?php echo $sPage2InfoChecked;?>></td>
	</tr>
	
	<tr><td align=right>Mode</td>
		<td><select name=sMode>
		<?php echo $sModeOptions;?>
		</select></td>
		<td align=right>Is Live</td>
		<td><input type=checkbox name=iIsLive value='1' <?php echo $sIsLiveChecked;?>>		
		</select></td>
	</tr>	
	
	<tr><td align=right valign=top>Available For API</td>
		<td><input type=checkbox name="sIsAvailableForApi" value='Y' <?php echo $sIsAvailableForApiChecked; ?>></td>
	</tr>
	
	<!--<tr><td colspan=3><b>Changing Mode May Change Last Delivery Date</b></td>-->
	</tr>	
	<tr><td align=right valign=bottom>Page 2 Template</td>
		<td valign=middle colspan=3>Copy Page2 Template From <select name=sCopyTemplateFrom>
					<?php echo $sTemplateOptions;?>
					</select> &nbsp; &nbsp; <input type=submit name=sCopyTemplate value='Copy'> 
					&nbsp; &nbsp; <?php echo "$sPage2FieldsLink &nbsp; $sPage2PreviewLink";?></td></tr>
	<tr><td></td><td colspan=3>
			<textarea name=sPage2Template rows=9 cols=80><?php echo $sPage2Template;?></textarea></td>
	</tr>
	<tr><td valign=top>page2 JavaScript</td><td colspan=3>
			<textarea name=sPage2JavaScript rows=9 cols=80><?php echo $sPage2JavaScript;?></textarea>
			<BR><b>Note:</b> Don't include &lt;SCRIPT&gt; tag or function in javascript. Just put condition and append error message to errMessage.</td></tr>
		
	<tr><td valign=top>New page2 JavaScript</td><td colspan=3>
			<textarea name=sNewPage2JavaScript rows=9 cols=80><?php echo $sNewPage2JavaScript;?></textarea>
			<BR><b>Note:</b> Don't include &lt;SCRIPT&gt; tag or function in javascript.</td></tr>
		
	<tr><td align=right>Other Images</td><td colspan=3><input type=file name='page2Image'></td></tr>
	<tr><td align=right>Current Images</td><td colspan=3><?php echo $page2ImagesList;?></td></tr>
	<tr><td align=right valign=top>Is Capped</td>
		<td><input type=checkbox name=iIsCap value='1' <?php echo $sIsCapChecked;?>><br>
		Cap Limits will be applied only if this is checked.</td>
	</tr>
	<tr><td align=right>Default Sort Order</td><td colspan=3><input type=text name=iDefaultSortOrder size=5 value='<?php echo $iDefaultSortOrder;?>'>
		<BR>Default sort order will be applied only if sort order is 0 in page sort order.</td></tr>
	<tr><td align=right>API Available</td>
		<td><input type=checkbox name=iApiAvailable value='1' <?php echo $sApiAvailableChecked;?>></td>
		<td colspan=2>Exclude From Master Page &nbsp; &nbsp; 
		<input type=checkbox name=iExcludeFromMasterPage value='1' <?php echo $sExcludeFromMasterPageChecked;?>></td>
	</tr>
		
	<tr><td align=right>Restrictions </td>
		<td colspan=3><textarea name=sRestrictions rows=3 cols=70><?php echo $sRestrictions;?></textarea></td>
	</tr>
	
	<tr><td align=right valign=top>Required SSL</td>
		<td><input type=checkbox name="sIsRequiredSSL" value='Y' <?php echo $sIsRequiredSSLChecked; ?>></td>
	</tr>
	
	<td align=right>Open or Limited</td>
		<td>
		<input type="radio" name="sOpenOrLimited" value="O" <?php if( $sOpenOrLimited == 'O' ) { echo 'checked'; } ?>>&nbsp;Open
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sOpenOrLimited" value="L" <?php if( $sOpenOrLimited == 'L' ) { echo 'checked'; } ?>>&nbsp;Limited
		</td>
	</tr>

	</table>
	<br>
	
	
	
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

	<tr><td width = 15% align=right>Outbound Variable Map</td>
	<td>
		<input size=75 name="sPassOnCodeVarMap" value="<?php echo $sPassOnCodeVarMap; ?>" <?php if($iId && $sIsOpenTheyHost == 'N' ){ echo 'disabled '; }?>>
		<br>&nbsp;Required if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Specify comma separated inbound=outbound variable name pairs to map.
		<br>&nbsp;You can use the code "pnd" to prepopulate a field with a 10-digit phone number (numerical only, no dashes).
		<br>&nbsp;Do not include sessionId here.  SessionId is included by default if "Pass On Prep Codes & SessionId" is checked.
		<br>&nbsp;Example: e=email,f=first,l=last,a1=addr1,a2=addr2,c=city,s=state,z=zip,p=phone
	</td>
	</tr>
	
	
	<?php
		if( $iId && $sIsOpenTheyHost == 'Y') {
	?>

	<tr>
		<td width = 15% align=right>Open They Host Offer Page</td>
		<td>
		http://www.popularliving.com/p/th.php?sThId=<?php echo $sOfferCode; ?>
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
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center border=1>
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
	<tr><td align=right>Leads Group</td>
		<td><select name=iLeadsGroupId >
		<?php echo $sLeadsGroupOptions;?>
		</select><BR><a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/leadGroups/addGroup.php?iMenuId=21&sReturnTo=iLeadsGroupId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Group</a>
		&nbsp; &nbsp; &nbsp; <a href='JavaScript:viewGroupInfo();'>View Group Info</a>

		</td>
	<td align=right>Processing Days *</td>
		<td><?php echo $sProcessingDaysCheckboxes;?>
		<!--<input type=text name=sProcessingDays value='<php echo $sProcessingDays;?>'><br>
			1 - Mon, 2 - Tue, 3 - Wed, 4 - Thu, 5 - Fri--></td>
	</tr>	
	<tr><td align=right>Delivery Method *</td>
		<td><select name=iDeliveryMethodId>
		<?php echo $sDeliveryMethodOptions;?>
		</select></td>
		<td align=right>Max Age Of Leads</td>
		<td><input type=text name=iMaxAgeOfLeads value='<?php echo $iMaxAgeOfLeads;?>'> Days</td>
	</tr>
	
	<tr><td align=right>Posting URL *</td>
		<td colspan=3><input type=text name=sPostingUrl value='<?php echo $sPostingUrl;?>' size=60>
		<BR>Include http:// or https://</td>
	</tr>
	<tr><td align=right>HTTP Post String</td>
		<td colspan=3><input type=text name=sHttpPostString value='<?php echo $sHttpPostString;?>' size=60></td>
	</tr>
	<tr><td align=right>FTP Site URL *</td>
		<td colspan=3><input type=text name=sFtpSiteUrl value='<?php echo $sFtpSiteUrl;?>' size=40>
			<BR>Don't include http:// or https://</td>
	</tr>
	
	<tr>
		<td align=right>Initial FTP Directory *</td>
		<td colspan=3><input type=text name=sInitialFtpDirectory value='<?php echo $sInitialFtpDirectory;?>'></td>	
	</tr>		
	<tr><td align=right>User Id *</td>
		<td><input type=text name=sUserId value='<?php echo $sUserId;?>'></td>
	<td align=right>Password *</td>
		<td><input type=text name=sPasswd value='<?php echo $sPasswd;?>'></td>
	</tr>
	<tr><td align=right>Lead File Name *</td>
		<td colspan=3><input type=text name=sLeadFileName value='<?php echo $sLeadFileName;?>' size=45>
		&nbsp; &nbsp; Separate Lead File <input type=checkbox name=iSeparateLeadFile value='1' <?php echo $sSeparateLeadFileChecked;?>> &nbsp;
		 <?php echo $sSepareteFileLink;?>
		 <BR>
			[offerCode], [dd], [mm], [yy], [yyyy], [count] will be replaced with its value.<BR>
			[d-n] or [d+n] used anywhere in the file name will be applied to the date to add or subtract days.(n = Any integer value)</td>
	</tr>
	<tr><td align=right>Is Encrypted *</td>
		<td><input type=checkbox name=iIsEncrypted value='1' <?php echo $sIsEncryptedChecked;?>>
			&nbsp; &nbsp; Enc. Type * <select name=sEncType>
					<?php echo $sEncTypeOptions;?>
					</select>
		</td>
	<td align=right>Encryption Method *</td>
		<td><select name=sEncMethod>
			<?php echo $sEncMethodOptions;?>
			</select>
		</td>
	</tr>
	<tr><td align=right>Encryption Key *</td>
		<td colspan=3><input type=text name=sEncKey value='<?php echo $sEncKey;?>' size=80></td>
	</tr>
	<tr><td align=right>Header Text *</td>
		<td colspan=3><textarea name=sHeaderText rows=3 cols=80><?php echo $sHeaderText;?></textarea></td>
	</tr>
	<tr><td align=right>Footer Text *</td>
		<td colspan=3><textarea name=sFooterText rows=3 cols=80><?php echo $sFooterText;?></textarea></td>
	</tr>
	<tr><td align=right>Leads Query</td>
		<td colspan=3><textarea name=sLeadsQuery rows=10 cols=80><?php echo $sLeadsQuery;?></textarea>
			&nbsp; <a href='JavaScript:testQuery();'>Test Query</a><BR>
	<b> Sample Leads Query:</b> SELECT userDataHistoryWorking.email, first, last, address, address2, city, state, zip, phoneNo 
		FROM userDataHistoryWorking, otDataHistoryWorking 
		WHERE userDataHistoryWorking.email = otDataHistoryWorking.email 
		AND otDataHistoryWorking.offerCode = '[offerCode]' 
		AND userDataHistoryWorking.postalVerified = 'V'  
		AND   DATE_ADD(date_format(otDataHistoryWorking.dateTimeAdded,"%Y-%m-%d"), INTERVAL $iTempMaxAgeOfLeads DAY) >= CURRENT_DATE
		<BR><BR>
	
	<b>Sample Leads Query With Page2 Data: </b>
	SELECT userDataHistoryWorking.email, first, last, address, address2, city, state, zip, phoneNo, 
	<b>TRIM( BOTH '"' FROM substring_index( substring_index( page2Data, "|", <i>n</i> ) , "|", - 1 ) ) AS FIELDn</b>  
	FROM userDataHistoryWorking, otDataHistoryWorking 
	WHERE userDataHistoryWorking.email = otDataHistoryWorking.email 
	AND otDataHistoryWorking.offerCode = '[offerCode]' 
	AND userDataHistoryWorking.postalVerified = 'V'  AND   
	DATE_ADD(date_format(otDataHistoryWorking.dateTimeAdded,"%Y-%m-%d"), INTERVAL $iTempMaxAgeOfLeads DAY) >= CURRENT_DATE  

	<BR><BR>
<b>Note:</b>Replace <i>n</i> with page2 field storage order.
	</td>
	</tr>
	<tr><td align=right>Field Delimiter</td>
		<td><select name=sFieldDelimiter>
			<?php echo $sFieldDelimiterOptions;?>
			</select>
		</td>
	<td align=right>Field Separater</td>
		<td><select name=sFieldSeparater>
			<?php echo $sFieldSeparaterOptions;?>
			</select>
		</td>
	</tr>
	<tr><td align=right>End Of Line</td>
		<td><select name=sEndOfLine>
			<?php echo $sEndOfLineOptions;?>
			</select>
		</td>
	<td align=right>Leads Subject *</td>
		<td><input type=text name=sLeadsEmailSubject value='<?php echo $sLeadsEmailSubject;?>' size=60><BR>
		[offerCode], [dd], [mm], [yy], [yyyy], [count]s will be replaced with its value.</td>
	</tr>
	<tr><td align=right>Leads Email From Address *</td>
		<td colspan=3><input type=text name=sLeadsEmailFromAddr value='<?php echo $sLeadsEmailFromAddr;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Email Body *</td>
		<td colspan=3><textarea name=sLeadsEmailBody  rows=4 cols=70><?php echo $sLeadsEmailBody;?></textarea></td>
	</tr>
	<tr><td></td><td colspan=3>Single email info can be used for Real Time Email  or  Daily Batch Email - One Per Lead</td></tr>
	<tr><td align=right>Single Email Subject *</td>
		<td colspan=3><input type=text name=sSingleEmailSubject value='<?php echo $sSingleEmailSubject;?>' size=60><BR>
		[offerCode], [dd], [mm], [yy], [yyyy], [count]s will be replaced with its value.</td>
	</tr>
	<tr><td align=right>Single Email From Address *</td>
		<td colspan=3><input type=text name=sSingleEmailFromAddr value='<?php echo $sSingleEmailFromAddr;?>' size=70></td>
	</tr>
	<tr><td align=right>Single Email Body *</td>
		<td colspan=3><textarea rows=5 cols=70 name=sSingleEmailBody><?php echo $sSingleEmailBody;?></textarea>
				<BR>[FIELD1], [FIELD2], [FIELD3]... will be replaced with the respective page2 fields.
				<BR> [dd], [mm], [yy], [yyyy], [hh], [ii], [ss] will be replaced with day, month, two digit year, year, hour, minute and second values</td>
	</tr>
	
	<tr><td align=right>Test Mail Recipients *</td>
		<td colspan=3><input type=text name=sTestEmailRecipients value='<?php echo $sTestEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Count Mail Recipients *</td>
		<td colspan=3><input type=text name=sCountEmailRecipients value='<?php echo $sCountEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Mail Recipients *</td>
		<td colspan=3><input type=text name=sLeadsEmailRecipients value='<?php echo $sLeadsEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Instruction</td>
		<td colspan=3><textarea name=sLeadsInstruction rows=5 cols=80><?php echo $sLeadsInstruction;?></textarea></td>
	</tr>
	<!--<tr><td></td><td colspan=2>Fields marked with * will be disabled if lead group is selected.<BR>
						Values from the group record will be used instead.</td></tr>-->
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

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	

<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onbeforeunload>
<!-- Beginning of JavaScript --------

var strMsg = "All the changes you didn't saved, will be lost.";
if (document.form1.submitClicked.value==0) {
window.event.returnValue = strMsg;
document.form1.sSaveClose.focus()
} 

// -- End of JavaScript code -------------- -->
</SCRIPT>



<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>


<?php

/*********

Script to Clone an Offer

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offers - Clone Offer";

$iCurrYear = date(Y);
$iCurrMonth = date(m); //01 to 12
$iCurrDay = date(d); // 01 to 31

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sClone ) {
	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Clone offer: $sOfferCode to $sNewOfferCode\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	// clone records from offers, capCounts, offerLeadSpec, page2Map, categoryMap, pageMap
	
	// don't clone emage name and image file
	if ($sNewOfferCode != '') {
	$sOffersQuery = "SELECT *
					 FROM   offers
					 WHERE  offerCode = '$sOfferCode'";
	$rOffersResult = dbQuery($sOffersQuery);
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
			
			$iCompanyId = $oOffersRow->companyId;
			$iPartnerId = $oOffersRow->partnerId;
			$sName = $oOffersRow->name;
			$sHeadline = addslashes($oOffersRow->headline);
			$sDescription = addslashes($oOffersRow->description);
			$sShortDescription = addslashes($oOffersRow->shortDescription);
			$iIsNonRevenue = $oOffersRow->isNonRevenue;
			$fRevPerLead = $oOffersRow->revPerLead;
			$fActualRevPerLead = $oOffersRow->actualRevPerLead;
			$iAutoRespEmail = $oOffersRow->autoRespEmail;
			$sAutoRespEmailFormat = $oOffersRow->autoRespEmailFormat;
			$sAutoRespEmailSub = addslashes($oOffersRow->autoRespEmailSub);
			$sAutoRespEmailFromAddr = $oOffersRow->autoRespEmailFromAddr;
			$sAutoRespEmailBody = addslashes($oOffersRow->autoRespEmailBody);
			$sNotes = addslashes($oOffersRow->notes);
			$sAddiInfoFormat = $oOffersRow->addiInfoFormat;
			$sAddiInfoTitle = addslashes($oOffersRow->addiInfoTitle);
			$sAddiInfoText = addslashes($oOffersRow->addiInfoText);
			$sAddiInfoPopupSize = $oOffersRow->addiInfoPopupSize;
			$iPrecheckAllPages = $oOffersRow->precheckAllPages;			
			$iPage2Info = $oOffersRow->page2Info;
			$sPage2Template = addslashes($oOffersRow->page2Template);
			$sPage2JavaScript = addslashes($oOffersRow->page2JavaScript);
			$sNewPage2JavaScript = addslashes($oOffersRow->newPage2JavaScript);
			$sMode = $oOffersRow->mode;
			$iIsLive = $oOffersRow->isLive;
			$sRestrictions = $oOffersRow->restrictions;
			
			$iMonthActive = $iCurrMonth;
			$iMonthInactive = $iCurrMonth;
			$iDayActive = $iCurrDay;
			$iDayInactive = $iCurrDay;
			$iYearActive = $iCurrYear;
			$iYearInactive = $iCurrYear+1;
			$iHourActive = "00";
			$iHourInactive = "00";
			$sActiveDateTime = $iYearActive.$iMonthActive.$iDayActive.$iHourActive."0000";
			$sInactiveDateTime = $iYearInactive.$iMonthInactive.$iDayInactive.$iHourInactive."0000";
			
			echo "<script name='javascript'>alert($sActiveDateTime)</script>";
			
			$sPage2Template = addslashes($oOffersRow->page2Template);
			$iIsCap = $oOffersRow->isCap;
			$iDefaultSortOrder = $oOffersRow->defaultSortOrder;
			$iApiAvailable = $oOffersRow->apiAvailable;
			$iExcludeFromMasterPage = $oOffersRow->excludeFromMasterPage;

			$sPage2Template = ereg_replace($sOfferCode, $sNewOfferCode, $sPage2Template);
			$sPage2JavaScript = ereg_replace($sOfferCode, $sNewOfferCode, $sPage2JavaScript);
			$sNewPage2JavaScript = ereg_replace($sOfferCode, $sNewOfferCode, $sNewPage2JavaScript);
	}
	$sInsertOfferQuery = "INSERT INTO offers(offerCode, companyId, partnerId, name, headline, description, shortDescription, isNonRevenue, 
									  revPerLead, actualRevPerLead, autoRespEmail, 
									  autoRespEmailFormat, autoRespEmailSub, autoRespEmailBody, autoRespEmailFromAddr,
									  notes, addiInfoFormat, addiInfoTitle, addiInfoText, addiInfoPopupSize, precheckAllPages, page2Info,
									  page2Template, page2JavaScript, newPage2JavaScript, mode, isLive, activeDateTime, inactiveDateTime, isCap, defaultSortOrder, apiAvailable,
									 excludeFromMasterPage, restrictions) 
					 	  VALUES('$sNewOfferCode', '$iCompanyId', '$iPartnerId', \"$sName\", \"$sHeadline\", \"$sDescription\", \"$sShortDescription\", 
								'$iIsNonRevenue', '$fRevPerLead', '$fActualRevPerLead',
								'$iAutoRespEmail', '$sAutoRespEmailFormat', \"$sAutoRespEmailSub\", \"$sAutoRespEmailBody\", \"$sAutoRespEmailFromAddr\",
								\"$sNotes\", '$sAddiInfoFormat', \"$sAddiInfoTitle\", \"$sAddiInfoText\", \"$sAddiInfoPopupSize\", '$iPrecheckAllPages',
								'$iPage2Info', \"$sPage2Template\", \"$sPage2JavaScript\", \"$sNewPage2JavaScript\" ,'T', '$iIsLive' , '$sActiveDateTime', '$sInactiveDateTime', '$iIsCap',
								 '$iDefaultSortOrder', '$iApiAvailable', '$iExcludeFromMasterPage', \"$sRestrictions\")";
	$rInsertOfferResult = dbQuery($sInsertOfferQuery);
	
	if ($rInsertOfferResult) {
		
		// create directory for offer images
		if (!(is_dir("$sGblOfferImagePath/$sNewOfferCode")) ) {				
				mkdir("$sGblOfferImagePath/$sNewOfferCode",0777);
				chmod("$sGblOfferImagePath/$sNewOfferCode",0777);

			}
			
		// clone cap counts entry
		$sCapCountsQuery = "SELECT *
							 FROM   capCounts
							 WHERE  offerCode = '$sOfferCode'";
		$rCapCountsResult = dbQuery($sCapCountsQuery);
		while ($oCapCountsRow = dbFetchObject($rCapCountsResult)) {
			$sCapStartDate = $oCapCountsRow->capStartDate;
			$iMaxCap = $oCapCountsRow->maxCap;
			$sCap1PeriodType = $oCapCountsRow->cap1PeriodType;
			$iCap1PeriodInterval = $oCapCountsRow->cap1PeriodInterval;
			$iCap1Max = $oCapCountsRow->cap1Max;
			$sCap2PeriodType = $oCapCountsRow->cap2PeriodType;
			$iCap2PeriodInterval = $oCapCountsRow->cap2PeriodInterval;
			$iCap2Max = $oCapCountsRow->cap2Max;
			$iTotalCounts = $oCapCountsRow->totalCounts;
			$iCap1Counts = $oCapCountsRow->cap1Counts;
			$iCap2Counts = $oCapCountsRow->cap2Counts;			
			
			$sCapInsertQuery = "INSERT INTO capCounts(offerCode, capStartDate, maxCap, cap1PeriodType, cap1PeriodInterval,
										 cap1Max, cap2PeriodType, cap2PeriodInterval, cap2Max, totalCounts, cap1Counts, cap2Counts)
							VALUES('$sNewOfferCode', '$sCapStartDate', '$iMaxCap', '$sCap1PeriodType',
									'$iCap1PeriodInterval', '$iCap1Max', '$sCap2PeriodType', '$iCap2PeriodInterval', '$iCap2Max',
			 						'$iTotalCounts', '$iCap1Counts', '$iCap2Counts')";
			$rCapInsertResult = dbQuery($sCapInsertQuery);				
		}
		
		// clone offerLeadSpec entry
		
		$sLeadSpecQuery = "SELECT *
						   FROM   offerLeadSpec
						   WHERE  offerCode = '$sOfferCode'";
		$rLeadSpecResult = dbQuery($sLeadSpecQuery);
		while ($oLeadSpecRow = dbFetchObject($rLeadSpecResult)) {
			$iLeadsGroupId = $oLeadSpecRow->leadsGroupId;
			$sLastLeadDate = $oLeadSpecRow->lastLeadDate;			
			$sProcessingDays = $oLeadSpecRow->processingDays;
			$iDeliveryMethodId = $oLeadSpecRow->deliveryMethodId;
			$iMaxAgeOfLeads = $oLeadSpecRow->maxAgeOfLeads;
			$sPostingUrl = addslashes($oLeadSpecRow->postingUrl);
			$sHttpPostString = addslashes($oLeadSpecRow->httpPostString);
			$sFtpSiteUrl = $oLeadSpecRow->ftpSiteUrl;
			$sInitialFtpDirectory = addslashes($oLeadSpecRow->initialFtpDirectory);
			//$iIsSecured = $oLeadSpecRow->isSecured;
			$sUserId = $oLeadSpecRow->userId;
			$sPasswd = $oLeadSpecRow->passwd;
			$sLeadFileName = addslashes($oLeadSpecRow->leadFileName);
			$iIsEncrypted = $oLeadSpecRow->isEncrypted;
			$sEncMethod = $oLeadSpecRow->encMethod;
			$sEncType = $oLeadSpecRow->encType;
			$sEncKey = $oLeadSpecRow->encKey;
			$sHeaderText = addslashes($oLeadSpecRow->headerText);
			$sFooterText = addslashes($oLeadSpecRow->footerText);
			$sLeadsQuery = addslashes($oLeadSpecRow->leadsQuery);
			$sFieldDelimiter = addslashes($oLeadSpecRow->fieldDelimiter);
			$sFieldSeparater = addslashes($oLeadSpecRow->fieldSeparater);
			$sEndOfLine = addslashes($oLeadSpecRow->endOfLine);
			$sLeadsEmailSubject = addslashes($oLeadSpecRow->leadsEmailSubject);
			$sLeadsEmailFromAddr = $oLeadSpecRow->leadsEmailFromAddr;
			$sLeadsEmailBody = addslashes($oLeadSpecRow->leadsEmailBody);
			$sSingleEmailSubject = addslashes($oLeadSpecRow->singleEmailSubject);
			$sSingleEmailFromAddr = addslashes($oLeadSpecRow->singleEmailFromAddr);
			$sSingleEmailBody = addslashes($oLeadSpecRow->singleEmailBody);
			$sTestEmailRecipients = $oLeadSpecRow->testEmailRecipients;
			$sCountEmailRecipients = $oLeadSpecRow->countEmailRecipients;
			$sLeadsEmailRecipients = $oLeadSpecRow->leadsEmailRecipients;
			$sLeadsInstruction = addslashes($oLeadSpecRow->leadsInstruction);
			
			
			$iDeliveryMethodId2 = $oLeadSpecRow->deliveryMethodId2;
			//$iMaxAgeOfLeads = $oLeadSpecRow->maxAgeOfLeads;
			$sPostingUrl2 = addslashes($oLeadSpecRow->postingUrl2);
			$sHttpPostString2 = addslashes($oLeadSpecRow->httPPostString2);
			$sFtpSiteUrl2 = $oLeadSpecRow->ftpSiteUrl2;
			$sInitialFtpDirectory2 = addslashes($oLeadSpecRow->initialFtpDirectory2);
			//$iIsSecured = $oLeadSpecRow->isSecured;
			$sUserId2 = $oLeadSpecRow->userId2;
			$sPasswd2 = $oLeadSpecRow->passwd2;
			$sLeadFileName2 = addslashes($oLeadSpecRow->leadFileName2);
			$iIsEncrypted2 = $oLeadSpecRow->isEncrypted2;
			$sEncMethod2 = $oLeadSpecRow->encMethod2;
			$sEncType2 = $oLeadSpecRow->encType2;
			$sEncKey2 = $oLeadSpecRow->encKey2;
			$sHeaderText2 = addslashes($oLeadSpecRow->headerText2);
			$sFooterText2 = addslashes($oLeadSpecRow->footerTex2);
			$sLeadsQuery2 = addslashes($oLeadSpecRow->leadsQuery2);
			$sFieldDelimiter2 = addslashes($oLeadSpecRow->fieldDelimiter2);
			$sFieldSeparater2 = addslashes($oLeadSpecRow->fieldSeparater2);
			$sEndOfLine2 = addslashes($oLeadSpecRow->endOfLine2);
			$sLeadsEmailSubject2 = addslashes($oLeadSpecRow->leadsEmailSubject2);
			$sLeadsEmailFromAddr2 = $oLeadSpecRow->leadsEmailFromAddr2;
			$sLeadsEmailBody2 = addslashes($oLeadSpecRow->leadsEmailBody2);
			$sSingleEmailSubject2 = addslashes($oLeadSpecRow->singleEmailSubject2);
			$sSingleEmailFromAddr2 = addslashes($oLeadSpecRow->singleEmailFromAddr2);
			$sSingleEmailBody2 = addslashes($oLeadSpecRow->singleEmailBody2);
			$sTestEmailRecipients2 = $oLeadSpecRow->testEmailRecipients2;
			$sCountEmailRecipients2 = $oLeadSpecRow->countEmailRecipients2;
			$sLeadsEmailRecipients2 = $oLeadSpecRow->leadsEmailRecipients2;
			//$sLeadsInstruction = addslashes($oLeadSpecRow->leadsInstruction);
			
			$sLeadsQuery = ereg_replace($sOfferCode, $sNewOfferCode, $sLeadsQuery);			
			$sLeadsQuery2 = ereg_replace($sOfferCode, $sNewOfferCode, $sLeadsQuery2);
			
			$sInsertLeadSpecQuery = "INSERT INTO offerLeadSpec(offerCode, leadsGroupId, lastLeadDate,
													 processingDays, deliveryMethodId, maxAgeOfLeads,
													postingUrl, httpPostString, ftpSiteUrl, initialFtpDirectory, userId, passwd, leadFileName,
													isEncrypted, encMethod, encType, encKey, headerText, footerText, leadsQuery,
													fieldDelimiter, fieldSeparater, endOfLine, leadsEmailSubject, leadsEmailFromAddr, leadsEmailBody,
													singleEmailSubject, singleEmailFromAddr, singleEmailBody, testEmailRecipients,	countEmailRecipients, leadsEmailRecipients, leadsInstruction,
													deliveryMethodId2, postingUrl2, httpPostString2, ftpSiteUrl2, initialFtpDirectory2, userId2, passwd2, leadFileName2,
													isEncrypted2, encMethod2, encType2, encKey2, headerText2, footerText2, leadsQuery2,
													fieldDelimiter2, fieldSeparater2, endOfLine2, leadsEmailSubject2, leadsEmailFromAddr2, leadsEmailBody2,
													singleEmailSubject2, singleEmailFromAddr2, singleEmailBody2, testEmailRecipients2,	countEmailRecipients2, leadsEmailRecipients2)
												 VALUES('$sNewOfferCode', '$iLeadsGroupId', '$sLastLeadDate',
													'$sProcessingDays', '$iDeliveryMethodId', '$iMaxAgeOfLeads', 
													\"$sPostingUrl\", \"$sHttpPostString\", '$sFtpSiteUrl', \"$sInitialFtpDirectory\", '$sUserId', '$sPasswd', '$sLeadFileName',
													'$iIsEncrypted', '$sEncMethod', '$sEncType', \"$sEncKey\", \"$sHeaderText\", \"$sFooterText\", \"$sLeadsQuery\",
													\"$sFieldDelimiter\", \"$sFieldSeparater\", \"$sEndOfLine\", \"$sLeadsEmailSubject\", 
													\"$sLeadsEmailFromAddr\",\"$sLeadsEmailBody\", \"$sSingleEmailSubject\",\"$sSingleEmailFromAddr\",\"$sSingleEmailBody\", \"$sTestEmailRecipients\",
													\"$sCountEmailRecipients\", \"$sLeadsEmailRecipients\", \"$sLeadsInstruction\", '$iDeliveryMethodId2',  
													\"$sPostingUrl2\", \"$sHttpPostString2\", '$sFtpSiteUrl2', \"$sInitialFtpDirectory2\", '$sUserId2', '$sPasswd2', '$sLeadFileName2',
													'$iIsEncrypted2', '$sEncMethod2', '$sEncType2', \"$sEncKey2\", \"$sHeaderText2\", \"$sFooterText2\", \"$sLeadsQuery2\",
													\"$sFieldDelimiter2\", \"$sFieldSeparater2\", \"$sEndOfLine2\", \"$sLeadsEmailSubject2\", 
													\"$sLeadsEmailFromAddr2\",\"$sLeadsEmailBody2\", \"$sSingleEmailSubject2\",\"$sSingleEmailFromAddr2\",\"$sSingleEmailBody2\", \"$sTestEmailRecipients2\",
													\"$sCountEmailRecipients2\", \"$sLeadsEmailRecipients2\")";								 
			$rInsertLeadSpecResult = dbQuery($sInsertLeadSpecQuery);
			
		}
		echo dbError();
		// clone page2Map entry
		
		$sPage2MapQuery = "SELECT *
						   FROM   page2Map
						   WHERE  offerCode = '$sOfferCode'";
		$sPage2MapResult = dbQuery($sPage2MapQuery);
		
		while ($oPage2MapRow = dbFetchObject($sPage2MapResult)) {
			$sFieldName = $oPage2MapRow->fieldName;			
			$sActualFieldName = $sNewOfferCode."_".$oPage2MapRow->fieldName;;
			$iIsRequired = $oPage2MapRow->isRequired;
			$iEncryptData = $oPage2MapRow->encryptData;			
			$sValidation = $oPage2MapRow->validation;
			$iStorageOrder = $oPage2MapRow->storageOrder;
			$sSopOnChangeCall = addslashes($oPage2MapRow->sopOnChangeCall);
			$sActualFieldName = ereg_replace($sOfferCode, $sNewOfferCode, $sActualFieldName);
			$sSopOnChangeCall = ereg_replace($sOfferCode, $sNewOfferCode, $sSopOnChangeCall);
							
			$sInsertPage2MapQuery = "INSERT INTO page2Map(offerCode, fieldName, actualFieldName, isRequired, encryptData, validation, storageOrder, sopOnChangeCall)
							 	  	VALUES('$sNewOfferCode', '$sFieldName', '$sActualFieldName', '$iIsRequired', '$iEncryptData', '$sValidation', '$iStorageOrder',\"$sSopOnChangeCall\")";
		
			$rInsertPage2MapResult = dbQuery($sInsertPage2MapQuery);
				
		}

		$sCategoryMapQuery = "SELECT *
							  FROM   categoryMap
							  WHERE  offerCode = '$sOfferCode'";
		$rCategoryMapResult = dbQuery($sCategoryMapQuery);
		echo dbError();
		while ($oCategoryMapRow = dbFetchObject($rCategoryMapResult)) {
			$iCategoryId = $oCategoryMapRow->categoryId;
			$iSortOrder = $oCategoryMapRow->sortOrder;
			$iIsTopDisplay = $oCategoryMapRow->isTopDisplay;
			$iPrecheck = $oCategoryMapRow->precheck;
			$sInsertCatMapQuery = "INSERT INTO categoryMap(categoryId, offerCode, sortOrder, isTopDisplay, precheck)
								   VALUES('$iCategoryId', '$sNewOfferCode', '$iSortOrder','$iIsTopDisplay', '$iPrecheck')";
			$rInsertCatMapResult = dbQuery($sInsertCatMapQuery) ;
			echo dbError();
		}		
		
		$sPageMapQuery = "SELECT *
						  FROM   pageMap
						  WHERE  offerCode = '$sOfferCode'";
		$rPageMapResult = dbQuery($sPageMapQuery);
		while ($oPageMapRow = dbFetchObject($rPageMapResult)) {
			$iPageId = $oPageMapRow->pageId;
			$iSortOrder = $oPageMapRow->sortOrder;
			$iIsTopDisplay = $oPageMapRow->isTopDisplay;
			$iPrecheck = $oPageMapRow->precheck;
			$sInsertPageMapQuery = "INSERT INTO pageMap(pageId, offerCode, sortOrder, isTopDisplay, precheck)
									VALUES('$iPageId', '$sNewOfferCode', '$iSortOrder', '$iIsTopDisplay', '$iPrecheck')";
			$rInsertPageMapResult = dbQuery($sInsertPageMapQuery);
		}				
	
		echo dbError();
		
	// Find out on which page this sourceCode will appear, set ORDER BY as sourcecode
			// and go to that page, and display redirect for this sourceCode
			if ($sFilter != '') {
				
				$sFilterPart .= " AND ( ";
				
				switch ($sSearchIn) {
					case "headline" :
					$sFilterPart .= ($sExactMatch == 'Y') ? "title = '$sFilter'" : "headline like '%$sFilter%'";
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
					$sFilterPart .= ($sExactMatch == 'Y') ? "offerCode = '$sFilter' || OC.companyName = '$sFilter' || title = '$sFilter' || description = '$sFilter'  " : " offerCode like '%$sFilter%' || OC.companyName LIKE '%$sFilter%' || headline like '%$sFilter%' || description like '%$sFilter%' ";
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
			$iPage = ceil($iThisRecordNo/$iRecPerPage);
			
			$sPageReloadUrl .= "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage&iPage=$iPage&sOfferCode=$sOfferCode";
			
			echo dbError();
			echo "<script language=JavaScript>
			window.opener.location.href='".$sPageReloadUrl."';
			self.close();
			</script>";
			// exit from this script
			//exit();
		} else {
						
			$sMessage = "Error in clone process<BR>".dbError();
		}
	
}
}
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>
			<input type=hidden name=iRecPerPage value='$iRecPerPage'>
			<input type=hidden name=sFilter value='$sFilter'>
			<input type=hidden name=sExactMatch value='$sExactMatch'>
			<input type=hidden name=sExclude value='$sExclude'>
			<input type=hidden name=sSearchIn value='$sSearchIn'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td>Clone offer</td><td><B><?php echo $sOfferCode;?></b>
	</td>
</tr>
<tr>
	<td>New OfferCode</td>
	<td><input type=text name=sNewOfferCode value='<?php echo $sNewOfferCode;?>'>
	</td>
</tr>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sClone value='Clone & Close'> &nbsp; &nbsp; 
		<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();" >
		</td>
	</tr>		
	</table>
	<form>
</body>

</html>
<?php
} else {
	echo "You are not authorized to access this page...";
}
?>
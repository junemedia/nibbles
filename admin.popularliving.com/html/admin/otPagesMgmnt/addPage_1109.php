<?php

/*********

Script to Add/Edit Ot Page

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");

session_start();

$sPageTitle = "Nibbles Ot Page User Form Layouts - Add/Edit OT Page User Form Layout";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew || $sSaveContinue) {
	// When New Record Submitted
	
	if ( $iCheckRedirectTo &&  !remoteFileExists($sRedirectTo)) {
		$sMessage = "Redirect To If Offer Taken URL Is Not A Valid URL...";
		$bKeepValues = true;
	} if ($iCheckRedirectToNotOfferTaken &&  $sRedirectToNotOfferTaken !='' && !remoteFileExists($sRedirectToNotOfferTaken)) {
		$sMessage = "Redirect To If Not Offer Taken URL Is Not A Valid URL...";
		$bKeepValues = true;
	} else {
		
		if (!($iId)) {
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   otPages
					   WHERE  pageName = '$sPageName'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			
			if (dbNumRows($rCheckResult) == 0) {
				// Insert record if everything is fine
				$sAddQuery = "INSERT INTO otPages(pageName, title, notes, pageLayoutId, page2LayoutId, userFormLayoutId, hiddenForm, minNoOfOffers, maxNoOfOffers, displayYesNo, offerImageSize, offerFontSize,
							displayOfferHeadline, displayList, listText, listIdToDisplay, listPrecheck, submitText, redirectTo, redirectToNotOfferTaken, passOnPrepopCodes,
							passOnInboundQueryString, inboundVarMap, outboundVarMap,
							enableGoTo, isGoToPopUp, offersByPageMap, offersByCatMap, autoEmail, autoEmailSub,
							autoEmailText, autoEmailFromAddr, isCobrand, displayPoweredBy, optOut, sureOptOut, sureOptOutText, noThankYouCode,
							pageBgColor, fontColor, offerBgColor1, offerBgColor2, page2BgColor, displayPage2HeaderImage, page1ExtraText, page2ExtraText1, page2ExtraText2,
							redirectPopOption, redirectPopUrl, checkRedirectPopUrl,redirectNotOfferTakenPopOption, redirectNotOfferTakenPopUrl, checkRedirectNotOfferTakenPopUrl, displayShoppingSpreeDisclaimer,
							showExitPopup, srcForExitPopup )
					 VALUES('$sPageName', \"$sTitle\", \"$sNotes\", '$iPageLayoutId', '$iPage2LayoutId', '$iUserFormLayoutId', '$iHiddenForm', '$iMinNoOfOffers', '$iMaxNoOfOffers', '$iDisplayYesNo', '$sOfferImageSize', '$sOfferFontSize',
							'$iDisplayOfferHeadline', '$iDisplayList', \"$sListText\", '$iListIdToDisplay', '$iListPrecheck', \"$sSubmitText\", '$sRedirectTo', \"$sRedirectToNotOfferTaken\",
							'$iPassOnPrepopCodes', '$iPassOnInboundQueryString', \"$sInboundVarMap\",  \"$sOutboundVarMap\", '$iEnableGoTo', '$iIsGoToPopUp', 
							'$iOffersByPageMap', '$iOffersByCatMap', '$iAutoEmail', \"$sAutoEmailSub\", \"$sAutoEmailText\", '$sAutoEmailFromAddr',
							'$iIsCobrand', '$iDisplayPoweredBy',  '$iOptOut', '$iSureOptOut', \"$sSureOptOutText\", \"$sNoThankYouCode\", 
							'$sPageBgColor', '$sFontColor', '$sOfferBgColor1', '$sOfferBgColor2', '$sPage2BgColor', '$iDisplayPage2HeaderImage',\"$sPage1ExtraText\", \"$sPage2ExtraText1\", \"$sPage2ExtraText2\",
							\"$sRedirectPopOption\", \"$sRedirectPopUrl\", \"$iCheckRedirectPopUrl\",\"$sRedirectNotOfferTakenPopOption\", \"$sRedirectNotOfferTakenPopUrl\",\"$iCheckRedirectNotOfferTakenPopUrl\",
							'$iDisplayShoppingSpreeDisclaimer', '$sShowExitPopup', \"$sSrcForExitPopup\")";
				
				$rResult = dbQuery($sAddQuery);

				if ( $rResult ) {
					$iPageId = dbInsertId();
				} else {
					echo dbError();
				}
			} else {
				$sMessage = "Page Name Already Exists...";
				$bKeepValues = true;
			}

		} else if ($iId) {

			// When Record Edited
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   otPages
					   WHERE  pageName = '$sPageName'
					   AND    id != '$iId'";
			$rCheckResult = dbQuery($sCheckQuery);

			if (dbNumRows($rCheckResult) == 0) {

				// get the old page name
				$sNameCheckQuery = "SELECT pageName
								    FROM   otPages
									WHERE  id = '$iId'";
				$rNameCheckResult = dbQuery($sNameCheckQuery);
				while ($oNameCheckRow = dbFetchObject($rNameCheckResult)) {
					$sOldPageName = $oNameCheckRow->pageName;

				}// end of name check while loop

				$sEditQuery = "UPDATE   otPages
					   SET 		pageName = '$sPageName',
								title = \"$sTitle\",
								notes = \"$sNotes\",
								pageLayoutId = '$iPageLayoutId',
								page2LayoutId = '$iPage2LayoutId',
								userFormLayoutId = '$iUserFormLayoutId',
								hiddenForm = '$iHiddenForm',
								minNoOfOffers = '$iMinNoOfOffers',
								maxNoOfOffers = '$iMaxNoOfOffers',
								displayYesNo = '$iDisplayYesNo', 
								offerImageSize = '$sOfferImageSize',
								offerFontSize = '$sOfferFontSize',
								displayOfferHeadline = '$iDisplayOfferHeadline',
								displayList = '$iDisplayList', 
								listText = \"$sListText\",
								listIdToDisplay = '$iListIdToDisplay',
								listPrecheck = '$iListPrecheck',
								submitText = \"$sSubmitText\", 
								redirectTo = '$sRedirectTo', 
								redirectToNotOfferTaken = \"$sRedirectToNotOfferTaken\", 
								passOnPrepopCodes = '$iPassOnPrepopCodes',
								passOnInboundQueryString = '$iPassOnInboundQueryString',
								inboundVarMap = \"$sInboundVarMap\",
								outboundVarMap = \"$sOutboundVarMap\",
				 				enableGoTo = '$iEnableGoTo', 
								isGoToPopUp = '$iIsGoToPopUp',								
								offersByPageMap = '$iOffersByPageMap', 
								offersByCatMap = '$iOffersByCatMap', 
								autoEmail = '$iAutoEmail', 
								autoEmailSub = \"$sAutoEmailSub\",
								autoEmailText = \"$sAutoEmailText\", 
								autoEmailFromAddr = '$sAutoEmailFromAddr', 								
								isCobrand = '$iIsCobrand', 
								displayPoweredBy = '$iDisplayPoweredBy', 								
								pageBgColor = '$sPageBgColor',
								optOut = '$iOptOut',
								sureOptOut = '$iSureOptOut',
								sureOptOutText = \"$sSureOptOutText\",
								noThankYouCode = \"$sNoThankYouCode\",
								fontColor = '$sFontColor',
								offerBgColor1 = '$sOfferBgColor1',
								offerBgColor2 = '$sOfferBgColor2',
								noThankYouCode = \"$sNoThankYouCode\",
								page2BgColor = '$sPage2BgColor', 
								displayPage2HeaderImage ='$iDisplayPage2HeaderImage',
								page1ExtraText = \"$sPage1ExtraText\",
								page2ExtraText1 = \"$sPage2ExtraText1\",
								page2ExtraText2 = \"$sPage2ExtraText2\",
								redirectPopOption = \"$sRedirectPopOption\", 
								redirectPopUrl = \"$sRedirectPopUrl\", 
								checkRedirectPopUrl = \"$iCheckRedirectPopUrl\", 
								redirectNotOfferTakenPopOption = \"$sRedirectNotOfferTakenPopOption\", 
								redirectNotOfferTakenPopUrl = \"$sRedirectNotOfferTakenPopUrl\",
								checkRedirectNotOfferTakenPopUrl = \"$iCheckRedirectNotOfferTakenPopUrl\",
								displayShoppingSpreeDisclaimer = '$iDisplayShoppingSpreeDisclaimer',
								showExitPopup = '$sShowExitPopup',
								srcForExitPopup = \"$sSrcForExitPopup\"
		 			   WHERE    id = '$iId'";
				
				$rResult = dbQuery($sEditQuery);	
				
				if ($rResult && $sOldPageName != '$sPageName') {										
					system("mv $sGblOtPagesPath/$sOldPageName $sGblOtPagesPath/$sPageName");	
					$sOldOtPage = $sOldPageName .".php";
					$sNewOtPage = $sPageName.".php";
					
					system("mv $sGblOtPagesPath/$sOldOtPage $sGblOtPagesPath/$sNewOtPage");
					
					$sOldOtPage2 = $sOldPageName ."_2.php";
					$sNewOtPage2 = $sPageName."_2.php";
					
					system("mv $sGblOtPagesPath/$sOldOtPage2 $sGblOtPagesPath/$sNewOtPage2");
					
				}			
				
				echo dbError();				
			}
			
			$iPageId = $iId;
		}
		
	}
	
	
	// save uploaded image	
	/*if (!(is_dir("$sGblPageImagesPath/$iPageId")) ) {				
		mkdir("$sGblPageImagesPath/$iPageId",0777);
		chmod("$sGblPageImagesPath/$iPageId",0777);
	}	*/
	
	if (!(is_dir("$sGblOtPagesPath/$sPageName")) ) {				
		mkdir("$sGblOtPagesPath/$sPageName",0777);
		chmod("$sGblOtPagesPath/$sPageName",0777);
	}

	if (!(is_dir("$sGblOtPagesPath/$sPageName/headers")) ) {				
		mkdir("$sGblOtPagesPath/$sPageName/headers",0777);
		chmod("$sGblOtPagesPath/$sPageName/headers",0777);
	}
	
	if (!(is_dir("$sGblOtPagesPath/$sPageName/images")) ) {				
		mkdir("$sGblOtPagesPath/$sPageName/images",0777);
		chmod("$sGblOtPagesPath/$sPageName/images",0777);
	}
	
	
	if ($_FILES['image']['tmp_name'] && $_FILES['image']['tmp_name']!="none") {
		
		$sUploadedFileName = $_FILES['image']['tmp_name'];
		
		//Get Extention
		$aImageFileNameArray = explode(".",$_FILES['image']['name']);
		$i = count($aImageFileNameArray) - 1;
		$sImageFileExt = $aImageFileNameArray[$i];
		
		$sImageFileName = "header_" . $iPageId . ".$sImageFileExt";
		$sNewImageFile  = "$sGblOtPagesPath/$sPageName/images/$sImageFileName";
		
		move_uploaded_file( $sUploadedFileName, $sNewImageFile);
		
		// store the image file name in database
		$sUpdateQuery = "UPDATE otPages
						 SET     headerGraphicFile = '$sImageFileName'
						 WHERE  id = '$iPageId'";
		$rUpdateResult = dbQuery($sUpdateQuery);		
	}
	
	
	if ($_FILES['otherImage']['name'] && $_FILES['otherImage']['name']!="none") {
		
		$sUploadedFileName = $_FILES['otherImage']['tmp_name'];
		
		//Get Extention
		//$aImageFileNameArray = explode(".",$_FILES['otherImage']['name']);
		//$i = count($aImageFileNameArray) - 1;
		//$sImageFileExt = $aImageFileNameArray[$i];
		
		$sImageFileName = $_FILES['otherImage']['name'];
		$sNewImageFile  = "$sGblOtPagesPath/$sPageName/images/$sImageFileName";
		
		move_uploaded_file( $sUploadedFileName, $sNewImageFile);				
	}
	
	if ($_FILES['headerFile']['name'] && $_FILES['headerFile']['name']!="none") {
		
		$sUploadedFileName = $_FILES['headerFile']['tmp_name'];
		
		//Get Extention
		//$aImageFileNameArray = explode(".",$_FILES['otherImage']['name']);
		//$i = count($aImageFileNameArray) - 1;
		//$sImageFileExt = $aImageFileNameArray[$i];
		
		$sFileName = $_FILES['headerFile']['name'];
		$sNewFileName  = "$sGblOtPagesPath/$sPageName/headers/$sFileName";
		
		move_uploaded_file( $sUploadedFileName, $sNewFileName);		
		chmod("$sNewFileName",0777);		
	}
		
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		
		switch ($sSearchIn) {
			case "pageName" :
			$sFilterPart .= ($iExactMatch) ? "pageName = '$sFilter'" : "pageName like '%$sFilter%'";
			break;
			case "redirectTo" :
			$sFilterPart .= ($iExactMatch) ? "redirectTo = '$sFilter'" : "redirectTo like '%$sFilter%'";
			break;
			case "all":			
			$sFilterPart .= ($iExactMatch) ? "pageName = '$sFilter' || redirectTo = '$sFilter'" : " pageName like '%$sFilter%' || redirectTo LIKE '%$sFilter%'";
			break;
		}
		
		$sFilterPart .= ") ";
	}
	
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sExclude) {
			case "pageName" :
			$sFilterPart .= "pageName NOT LIKE '%$sExclude%'";
			break;
			case "redirectTo" :
			$sFilterPart .= "redirectTo NOT LIKE '%$sExclude%'";
			break;
			
			case "all" :			
			$sFilterPart .= "pageName NOT LIKE '%$sExclude%' && redirectTo NOT LIKE '%$sExclude%' " ;
			break;
		}
		$sFilterPart .= " ) ";
		
	}
	
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	
	$sTempQuery = "SELECT count(*) numRecords
			  FROM   otPages
			  WHERE  pageName < '$sPageName' 
			  $sFilterPart 
			  ORDER BY pageName $sCurrOrder";
			
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
			
					
			$sPageReloadUrl .= "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage&iPage=$iPage";
			
			
	if ($sSaveContinue) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
		window.opener.location.reload();	
		</script>";
		// exit from this script
		}
	} else if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";
			// exit from this script
			exit();
		}
	} else if ($sSaveNew) {
		
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";		
			
			$sPageName = '';
			$sTitle = '';
			$sHeaderGraphicFile = '';
			$iPageLayoutId = '';
			$iPage2LayoutId = '';
			$iUserFormLayoutId = '';
			$iHiddenForm = '';
			$iMinNoOfOffers = '';
			$iMaxNoOfOffers = '';
			$iDisplayYesNo = '';
			$sOfferImageSize = '';
			$sOfferFontSize = '';
			$iDisplayList = '';
			$sListText = '';
			$iListIdToDisplay = '';
			$iListPrecheck = '';
			$sSubmitText = '';
			$sRedirectTo = '';
			$sRedirectToNotOfferTaken = '';
			$iPassOnPrepopCodes = '';
			$iPassOnInboundQueryString = '';
			$sInboundVarMap = '';
			$sOutboundVarMap = '';
			$iEnableGoTo = '';
			$iIsGoToPopUp = '';
			$iOffersByPageMap = '';
			$iOffersByCatMap = '';
			$iAutoEmail = '';
			$sAutoEmailSub = '';
			$sAutoEmailText = '';
			$sAutoEmailFromAddr = '';
			$iIsCobrand = '';
			$iDisplayPoweredBy = '';
			$iOptOut = '';
			$iSureOptOut = '';
			$sSureOptOutText = '';
			$sPageBgColor = '';
			$sFontColor = '';
			$sOfferBgColor1 = '';
			$sOfferBgColor2 = '';
			$sNoThankYouCode = '';
			$sPage2BgColor = '';
			$iDisplayPage2HeaderImage = '';
			$sPage1ExtraText = '';
			$sPage2ExtraText1 = '';
			$sPage2ExtraText2 = '';
			$sRedirectPopOption = '';
			$sRedirectPopUrl = '';
			$iCheckRedirectPopUrl = '';
			$sRedirectNotOfferTakenPopOption = '';
			$sRedirectNotOfferTakenPopUrl = '';
			$iCheckRedirectNotOfferTakenPopUrl = '';
			$iDisplayShoppingSpreeDisclaimer = '';
			$sShowExitPopup = '';
			$sSrcForExitPopup = '';
		}
	}
}


if ($iId) {
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT *
					 FROM   otPages
			  		 WHERE  id = '$iId'";
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			$sPageName = $oRow->pageName;
			$sTitle = ascii_encode($oRow->title);
			$sNotes = ascii_encode($oRow->notes);
			$sHeaderGraphicFile = $oRow->headerGraphicFile;
			
			if ($oRow->headerGraphicFile != '') {
				$sCurrentImage = "<img src='/p/$sPageName/images/$oRow->headerGraphicFile'>";
			} else {
				$sCurrentImage = "No Image";
			}
			
			$iPageLayoutId = $oRow->pageLayoutId;
			$iPage2LayoutId = $oRow->page2LayoutId;
			$iUserFormLayoutId = $oRow->userFormLayoutId;
			$iHiddenForm = $oRow->hiddenForm;
			$iMinNoOfOffers = $oRow->minNoOfOffers;
			$iMaxNoOfOffers = $oRow->maxNoOfOffers;
			$iDisplayYesNo = $oRow->displayYesNo;
			$sOfferImageSize = $oRow->offerImageSize;
			$sOfferFontSize = $oRow->offerFontSize;
			$iDisplayOfferHeadline = $oRow->displayOfferHeadline;
			$iDisplayList = $oRow->displayList;
			$sListText = $oRow->listText;
			$iListIdToDisplay = $oRow->listIdToDisplay;
			$iListPrecheck = $oRow->listPrecheck;
			$sSubmitText = $oRow->submitText;
			$sRedirectTo = $oRow->redirectTo;
			$sRedirectToNotOfferTaken = $oRow->redirectToNotOfferTaken;
			$iPassOnPrepopCodes = $oRow->passOnPrepopCodes;
			$iPassOnInboundQueryString = $oRow->passOnInboundQueryString;
			$sInboundVarMap = $oRow->inboundVarMap;
			$sOutboundVarMap = $oRow->outboundVarMap;
			$iEnableGoTo = $oRow->enableGoTo;
			$iIsGoToPopUp = $oRow->isGoToPopUp;			
			$iOffersByPageMap = $oRow->offersByPageMap;
			$iOffersByCatMap = $oRow->offersByCatMap;
			$iAutoEmail = $oRow->autoEmail;
			$sAutoEmailSub = ascii_encode($oRow->autoEmailSub);
			$sAutoEmailText = ascii_encode($oRow->autoEmailText);
			$sAutoEmailFromAddr = $oRow->autoEmailFromAddr;
			$iIsCobrand = $oRow->isCobrand;
			$iDisplayPoweredBy = $oRow->displayPoweredBy;
			$iOptOut = $oRow->optOut;
			$iSureOptOut = $oRow->sureOptOut;
			$sSureOptOutText = $oRow->sureOptOutText;
			$sPageBgColor = $oRow->pageBgColor;
			$sFontColor = $oRow->fontColor;
			$sOfferBgColor1 = $oRow->offerBgColor1;
			$sOfferBgColor2 = $oRow->offerBgColor2;
			$sNoThankYouCode = ascii_encode($oRow->noThankYouCode);
			$sPage2BgColor = $oRow->page2BgColor;
			$iDisplayPage2HeaderImage = $oRow->displayPage2HeaderImage;
			$sPage1ExtraText = ascii_encode($oRow->page1ExtraText);
			$sPage2ExtraText1 = ascii_encode($oRow->page2ExtraText1);
			$sPage2ExtraText2 = ascii_encode($oRow->page2ExtraText2);
			$sRedirectPopOption = $oRow->redirectPopOption;
			$sRedirectPopUrl = $oRow->redirectPopUrl;
			$iCheckRedirectPopUrl = $oRow->checkRedirectPopUrl;
			$sRedirectNotOfferTakenPopOption = $oRow->redirectNotOfferTakenPopOption;
			$sRedirectNotOfferTakenPopUrl = $oRow->redirectNotOfferTakenPopUrl;
			$iCheckRedirectNotOfferTakenPopUrl = $oRow->checkRedirectNotOfferTakenPopUrl;
			$iDisplayShoppingSpreeDisclaimer = $oRow->displayShoppingSpreeDisclaimer;
			$sShowExitPopup = $oRow->showExitPopup;
			$sSrcForExitPopup = $oRow->srcForExitPopup;
			
		}
		
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
} else {
	$sTitle = ascii_encode(stripslashes($sTitle));
	$sNotes = ascii_encode(stripslashes($sNotes));
	$sListText = ascii_encode(stripslashes($sListText));
	$sSubmitText = ascii_encode(stripslashes($sSubmitText));
	$sAutoEmailSub = ascii_encode(stripslashes($sAutoEmailSub));
	$sSureOptOutText = ascii_encode(stripslashes($sSureOptOutText));
	$sNoThankYouCode = ascii_encode(stripslashes($sNoThankYouCode));
	$sPage1ExtraText = ascii_encode(stripslashes($sPage1ExtraText));
	$sPage2ExtraText1 = ascii_encode(stripslashes($sPage2ExtraText1));
	$sPage2ExtraText2 = ascii_encode(stripslashes($sPage2ExtraText2));
	
	
	// set default values
	
	$iCheckRedirectTo = '1';
	$iCheckRedirectToNotOfferTaken = '1';
	$iDisplayOfferHeadline = '1';
	
	if (!(isset($iDisplayPage2HeaderImage))) {
		$iDisplayPage2HeaderImage = '1';
	}
	if ($sPageBgColor == '') {
		$sPageBgColor = "#FFFFFF";
	}
	if ($sPage2BgColor == '') {
		$sPage2BgColor = "#FFFFFF";
	}
	
	if ($sAutoEmailFromAddr == '') {
		$sAutoEmailFromAddr = "assist@AmpereMedia.com";
	}

	if ($sSureOptOutText == '') {
		$sSureOptOutText = "Are You Sure You Don't Want This Great Offer?\\nWe know you will enjoy the benefits. Remember it is totally free with no obligation of any kind.\\nIf you do not want to cancel this offer please click \\\"Cancel\\\" and finish providing the information requested.";
	}
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Prepare Page layout options for selection box
$sLayoutQuery = "SELECT *
			 	 FROM   pageLayouts";

$rLayoutResult = dbQuery($sLayoutQuery);

while ($oLayoutRow = dbFetchObject($rLayoutResult)) {
	if ($oLayoutRow->id == $iPageLayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sPageLayoutOptions .= "<option value=$oLayoutRow->id $sSelected>$oLayoutRow->layout";
}


// Prepare Page2 layout options for selection box
$sLayoutQuery2 = "SELECT *
			 	  FROM   page2Layouts";

$rLayoutResult2 = dbQuery($sLayoutQuery2);

while ($oLayoutRow2 = dbFetchObject($rLayoutResult2)) {
	if ($oLayoutRow2->id == $iPage2LayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sPage2LayoutOptions .= "<option value=$oLayoutRow2->id $sSelected>$oLayoutRow2->layout";
}


// Prepare user form layout options for selection box
$sUserFormLayoutQuery = "SELECT *
			 			 FROM   userFormLayouts
						 WHERE  layout != 'hiddenForm'
						 ORDER BY layout ";

$rUserFormLayoutResult = dbQuery($sUserFormLayoutQuery);

while ($oUserFormLayoutRow = dbFetchObject($rUserFormLayoutResult)) {
	if ($oUserFormLayoutRow->id == $iUserFormLayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sUserFormLayoutOptions .= "<option value=$oUserFormLayoutRow->id $sSelected>$oUserFormLayoutRow->layout";
}


// prepare listId doptions

$sListIdQuery = "SELECT *
				 FROM   joinLists
				 ORDER BY title";
$rListIdResult = dbQuery($sListIdQuery);
$sListIdToDisplayOptions = "<option value=''>None";
while ($oListIdRow = dbFetchObject($rListIdResult)) {
	if ($oListIdRow->id == $iListIdToDisplay) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	
	$sListIdToDisplayOptions .= "<option value=$oListIdRow->id $sSelected>$oListIdRow->title";
}


// if displayYesNo checked
$sDisplayYesNoChecked = "";
if ($iDisplayYesNo) {
	$sDisplayYesNoChecked = "checked";
}

// if displayOfferHeadline checked
$sDisplayOfferHeadlineChecked = "";
if ($iDisplayOfferHeadline) {
	$sDisplayOfferHeadlineChecked = "checked";
}

// if displayList checked
$sDisplayListChecked = "";
if ($iDisplayList) {
	$sDisplayListChecked = "checked";
}

// if displayList checked
$sListPrecheckChecked = "";
if ($iListPrecheck) {
	$sListPrecheckChecked = "checked";
}


// if autoEmail checked
$sAutoEmailChecked = "";
if ($iAutoEmail) {
	$sAutoEmailChecked = "checked";
}

// if displayPoweredBy checked
$sDisplayPoweredByChecked = "";
if ($iDisplayPoweredBy) {
	$sDisplayPoweredByChecked = "checked";
}

// if isCobrand checked
$sIsCobrandChecked = "";
if ($iIsCobrand) {
	$sIsCobrandChecked = "checked";
}


// if optOut checked
$sOptOutChecked = "";
if ($iOptOut) {
	$sOptOutChecked = "checked";
}

// if sureOptOut checked
$sSureOptOutChecked = "";
if ($iSureOptOut) {
	$sSureOptOutChecked = "checked";
}

$sOffersByPageMapChecked = "";
if ($iOffersByPageMap) {
	$sOffersByPageMapChecked = "checked";
}

// if enableGoTo checked
$sEnableGoToChecked = "";
if ($iEnableGoTo) {
	$sEnableGoToChecked = "checked";
}

// if isGoToPopUp checked
$sIsGoToPopUpChecked = "";
if ($iIsGoToPopUp) {
	$sIsGoToPopUpChecked = "checked";
}

// if display page2 header image checked
$sDisplayPage2HeaderImageChecked = "";
if ($iDisplayPage2HeaderImage) {
	$sDisplayPage2HeaderImageChecked = "checked";
}

// if display page2 header image checked
$sDisplayShoppingSpreeDisclaimerChecked = "";
if ($iDisplayShoppingSpreeDisclaimer) {
	$sDisplayShoppingSpreeDisclaimerChecked = "checked";
}



// if passOnInboundQueryString checked
$sPassOnPrepopCodesChecked = "";
if ($iPassOnPrepopCodes) {
	$sPassOnPrepopCodesChecked = "checked";
}

// if passOnInboundQueryString checked
$sPassOnInboundQueryStringChecked = "";
if ($iPassOnInboundQueryString) {
	$sPassOnInboundQueryStringChecked = "checked";
}


// if passOnInboundQueryString checked
$sHiddenFormChecked = "";
if ($iHiddenForm) {
	$sHiddenFormChecked = "checked";
}

// prepare category options for offersByCatMap
$sCategoryQuery = "SELECT *
				   FROM   categories
				   ORDER BY title";
$rCategoryResult = dbQuery($sCategoryQuery);
$sCategoriesOptions = "<option value=''>";
while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
	if ($oCategoryRow->id == $iOffersByCatMap) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sCategoriesOptions .= "<option value=$oCategoryRow->id $sSelected>$oCategoryRow->title";
}


$sCheckRedirectToChecked = '';
if ($iCheckRedirectTo) {
	$sCheckRedirectToChecked = "checked";
}

$sCheckRedirectToNotOfferTakenChecked = '';
if ($iCheckRedirectToNotOfferTaken) {
	$sCheckRedirectToNotOfferTakenChecked = "checked";
}

$sRedirectPopUpChecked = '';
$sRedirectPopUnderChecked = '';
$sRedirectNoPopUpChecked = '';

switch ($sRedirectPopOption) {
	case "popUp":
		$sRedirectPopUpChecked = "checked";
		break;
	case "popUnder":
		$sRedirectPopUnderChecked = "checked";
		break;
	default:
		$sRedirectNoPopUpChecked = "checked";
}

$sCheckRedirectPopUrlChecked = '';
if ($iCheckRedirectPopUrl) {
	$sCheckRedirectPopUrlChecked = "checked";
}


$sCheckRedirectNotOfferTakenPopUrlChecked = '';

if ($iCheckRedirectNotOfferTakenPopUrl) {
	$sCheckRedirectNotOfferTakenPopUrlChecked = "checked";
	
}


$sShowExitPopupAlwaysChecked = '';
$sShowExitPopupNeverChecked = '';
$sShowExitPopupOnlySrcChecked = '';
$sShowExitPopupNotForSrcChecked = '';

switch ($sShowExitPopup) {
	case "always":
		$sShowExitPopupAlwaysChecked = "checked";
		break;
	case "onlyForSrc":
		$sShowExitPopupOnlySrcChecked = "checked";
		break;		
	case "notForSrc":
		$sShowExitPopupNotForSrcChecked = "checked";
		break;	
	default:
		$sShowExitPopupNeverChecked = "checked";
			
}

if ($iShowExitPopup) {
	$sShowExitPopupChecked = "checked";
	
}

$sRedirectNotOfferTakenPopUpChecked = '';
$sRedirectNotOfferTakenPopUnderChecked = '';
$sRedirectNotOfferTakenNoPopUpChecked = '';

switch ($sRedirectNotOfferTakenPopOption) {
	case "popUp":
		$sRedirectNotOfferTakenPopUpChecked = "checked";
		break;
	case "popUnder":
		$sRedirectNotOfferTakenPopUnderChecked = "checked";
		break;
	default:
		$sRedirectNotOfferTakenNoPopUpChecked = "checked";
}

$sRegularSelected = "";
$sSmallSelected = "";
switch($sOfferImageSize) {
	
	case 'small':
		$sSmallSelected = "Selected";
		break;
	case 'regular':
	default:
	$sRegularSelected = "selected";
} 

$sOfferImageSizeOptions = "<option value='regular' $sRegularSelected>Regular
						   <option value='small' $sSmallSelected>Small";

$s9PxSelected = '';
$s10PxSelected = '';
$s11PxSelected = '';
$s12PxSelected = '';

switch($sOfferFontSize) {
	
	case '9px':
		$s9PxSelected = "Selected";
		break;
	case '11px':
		$s11PxSelected = "Selected";
		break;
	case '12px':
		$s12PxSelected = "Selected";
		break;
		
	default:
	$s10PxSelected = "selected";
} 
$sOfferFontSizeOptions = "<option value='9px' $s9PxSelected>9px
						  <option value='10px' $s10PxSelected>10px
						  <option value='11px' $s11PxSelected>11px
						  <option value='12px' $s12PxSelected>12px";

// delete the image if clicked on the delete link

if ($sDeleteImage) {
	unlink("$sGblOtPagesPath/$sPageName/images/$sDeleteImage");
}

// get list of page2 images, if offer is selected to edit
if ($iId) {
	$rImageDir = opendir("$sGblOtPagesPath/$sPageName/images/");
	if ($rImageDir) {

		while (($sFile = readdir($rImageDir)) != false) {	
			if (!is_dir("$sGblOtPagesPath/$sPageName/images/$sFile")) {
					
				$otherImagesList .=  "<a href='JavaScript:void(window.open(\"$sGblOtPagesPath/$sPageName/images/$sFile\",\"\",\"\"));'>$sGblPageImagesUrl/$iId/$sFile</a> 
						&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sDeleteImage=$sFile'>Delete</a><BR>";
			}
		}
	}
}


// delete the image if clicked on the delete link

if ($sDeleteFile) {
	unlink("$sGblOtPagesPath/$sPageName/headers/$sDeleteFile");
}


// get list of page2 images, if offer is selected to edit
if ($iId) {
	$rFileDir = opendir("$sGblOtPagesPath/$sPageName/headers");
	if ($rFileDir) {

		while (($sFile = readdir($rFileDir)) != false) {	
			if (!is_dir("$sGblOtPagesPath/$sPageName/headers/$sFile")) {
					
				$headerFilesList .=  "<a href='JavaScript:void(window.open(\"$sGblOtPagesUrl/$sPageName/headers/$sFile\",\"\",\"\"));'>$sGblOtPagesUrl/$sPageName/headers/$sFile</a> 
						&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sDeleteFile=$sFile'>Delete</a><BR>";
			}
		}
	}
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tR><td class=message colspan=2>If the value of the fields marked with * changed, requires page to be regenerated.<BR></td></tr>

	<tr><td>Page Name *</td>
		<td colspan=3><input type=text name='sPageName' value="<?php echo $sPageName;?>"></td>
	</tr>
	<tr><td>Title *</td>
		<td colspan=3><input type=text name='sTitle' value="<?php echo $sTitle;?>" size=70></td>
	</tr>	
	<tr><td>Notes</td>
		<td colspan=3><textarea name='sNotes' rows=7 cols=70><?php echo $sNotes;?></textarea></td>
	</tr>	

	<?php 
	if ($iId) {
		echo "<tr><td>Current Image</td>
					<td colspan=3>$sCurrentImage</td></tr>";
	}
	?>
	
	<tr><td>Header Image</td>
		<td colspan=3><input type=file name='image'></td>
	</tr>
	<tr><td>Other Images</td><td colspan=3><input type=file name='otherImage'></td></tr>
	<tr><td>Current Images</td><td colspan=3><?php echo $otherImagesList;?></td></tr>
	
	<tr><td>Header Files</td><td colspan=3><input type=file name='headerFile'></td></tr>
	<tr><td>Current Files</td><td colspan=3><?php echo $headerFilesList;?></td></tr>
	
	<tr><td>Page Layout *</td>
		<td colspan=3><select name='iPageLayoutId'>
		<?php echo $sPageLayoutOptions;?>
		</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/pageLayouts/addLayout.php?iMenuId=15&sReturnTo=iPageLayoutId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Page Layout</a></td>
	</tr>
	
	<tr><td>Page2 Layout *</td>
		<td colspan=3><select name='iPage2LayoutId'>
		<?php echo $sPage2LayoutOptions;?>
		</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/page2Layouts/addLayout.php?iMenuId=15&sReturnTo=iPage2LayoutId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Page2 Layout</a></td>
	</tr>
	
	<tr><td>User Form Layout *</td>
		<td colspan=3><select name='iUserFormLayoutId'>
		<?php echo $sUserFormLayoutOptions;?>
		</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/userFormLayouts/addUserForm.php?iMenuId=15&sReturnTo=iUserFormLayoutId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add User Form Layout</a></td>
	</tr>
	<tr><td>User Form Can Be Hidden *</td>
		<td colspan=3><input type=checkbox name='iHiddenForm' value='1' <?php echo $sHiddenFormChecked;?>></td>
	</tr>
	<tr><td>Min No. Of Offers</td>
		<td colspan=3><input type=text name='iMinNoOfOffers' value='<?php echo $iMinNoOfOffers;?>' size=5></td>
	</tr>
	<tr><td>Max No. Of Offers</td>
		<td colspan=3><input type=text name='iMaxNoOfOffers' value='<?php echo $iMaxNoOfOffers;?>' size=5></td>
	</tr>
	<tr><td>Display Yes/No</td>
		<td colspan=3><input type=checkbox name='iDisplayYesNo' value='1' <?php echo $sDisplayYesNoChecked;?>></td>
	</tr>
	<tr><td>Offer Image Size</td>
		<td colspan=3><select name=sOfferImageSize><?php echo $sOfferImageSizeOptions;?></select></td>
	</tr>
	<tr><td>Offer Font Size</td>
		<td colspan=3><select name=sOfferFontSize><?php echo $sOfferFontSizeOptions;?></select></td>
	</tr>
	<tr><td>Display Offer Headline</td>
		<td colspan=3><input type=checkbox name='iDisplayOfferHeadline' value='1' <?php echo $sDisplayOfferHeadlineChecked;?>></td>
	</tr>
	<tr><td>Display Join List *</td>
		<td colspan=3><input type=checkbox name='iDisplayList' value='1' <?php echo $sDisplayListChecked;?>></td>
	</tr>
	<tr><td>List Id To Display *</td>
		<td colspan=3><select name=iListIdToDisplay>
		<?php echo $sListIdToDisplayOptions;?>
		</select></td>
	</tr>
	<tr><td>List Precheck *</td>
		<td colspan=3><input type=checkbox name='iListPrecheck' value='1' <?php echo $sListPrecheckChecked;?>></td>
	</tr>
	<tr><td>List Text *</td>
		<td colspan=3><textarea name='sListText' rows=3 cols=40><?php echo $sListText;?></textarea><BR>
			[JOIN_LIST_TITLE] will be replaced by the title of the selected List.</td>
	</tr>
	
	<tr><td>Submit Text *</td>
		<td colspan=3><input type=text name='sSubmitText' value='<?php echo $sSubmitText;?>' size=50></td>
	</tr>
	
	<tr><td>Redirect To If Offer Taken</td>
		<td><input type=text name='sRedirectTo' value='<?php echo $sRedirectTo;?>' size=40></td>
		<td>Redirect Pop URL</td>
		<td><input type=text name='sRedirectPopUrl' value='<?php echo $sRedirectPopUrl;?>' size=40></td>
	</tr>
	<tr><td>Check Redirect To URL</td>
		<td ><input type=checkbox name='iCheckRedirectTo' value='1' <?php echo $sCheckRedirectToChecked;?>></td>
		<td nowrap>Check Redirect Pop URL</td>
		<td><input type=checkbox name='iCheckRedirectPopUrl' value='1' <?php echo $sCheckRedirectPopUrlChecked;?>>
		</td>
	</tr>
	<tr><td>Redirect Pop Option</td><td colspan=3><input type=radio name=sRedirectPopOption value='' <?php echo $sRedirectNoPopUpChecked;?>> No popUp 
 			&nbsp; <input type=radio name=sRedirectPopOption value='popUp' <?php echo $sRedirectPopUpChecked;?>> popUp 
  			&nbsp; <input type=radio name=sRedirectPopOption value='popUnder' <?php echo $sRedirectPopUnderChecked;?>> popUnder 			
		</td>
	</tr>
	
	<tr><td>Redirect To If Not Offer Taken</td>
		<td><input type=text name='sRedirectToNotOfferTaken' value='<?php echo $sRedirectToNotOfferTaken;?>' size=40></td>
		<td nowrap>Redirect Not Offer Taken Pop URL</td>
		<td><input type=text name='sRedirectNotOfferTakenPopUrl' value='<?php echo $sRedirectNotOfferTakenPopUrl;?>' size=40></td>
	</tr>
	<tr><td>Check Redirect To If Not Offer Taken URL</td>
		<td ><input type=checkbox name='iCheckRedirectToNotOfferTaken' value='1' <?php echo $sCheckRedirectToNotOfferTakenChecked;?>></td>
		<td nowrap>Check Redirect Pop URL</td>
		<td><input type=checkbox name='iCheckRedirectNotOfferTakenPopUrl' value='1' <?php echo $sCheckRedirectNotOfferTakenPopUrlChecked;?>>
	</tr>
	<tr><td>Redirect Pop Option</td><td colspan=3><input type=radio name=sRedirectNotOfferTakenPopOption value='' <?php echo $sRedirectNotOfferTakenNoPopUpChecked;?>> No popUp 
 			&nbsp; <input type=radio name=sRedirectNotOfferTakenPopOption value='popUp' <?php echo $sRedirectNotOfferTakenPopUpChecked;?>> popUp 
  			&nbsp; <input type=radio name=sRedirectNotOfferTakenPopOption value='popUnder' <?php echo $sRedirectNotOfferTakenPopUnderChecked;?>> popUnder 			
		</td>
	</tr>
	
	
	<tr><td>Pass On Prepop Codes</td>
		<td colspan=3><input type=checkbox name='iPassOnPrepopCodes' value='1' <?php echo $sPassOnPrepopCodesChecked;?>></td>
	</tr>
	<tr><td>Pass On Inbound Query String Values</td>
		<td colspan=3><input type=checkbox name='iPassOnInboundQueryString' value='1' <?php echo $sPassOnInboundQueryStringChecked;?>></td>
	</tr>
	<tr><td>Inbound Variable Map</td>
		<td colspan=3><input type=text name='sInboundVarMap' value='<?php echo $sInboundVarMap;?>' size=40><BR>
			Specify comma separated inbound=newInbound variable name pairs to map</td>
	</tr>
	<tr><td>Outbound Variable Map</td>
		<td colspan=3><input type=text name='sOutboundVarMap' value='<?php echo $sOutboundVarMap;?>' size=40><BR>
			Specify comma separated inbound=outbound variable name pairs to map</td>
	</tr>
	<tr><td>Enable Go To</td>
		<td colspan=3><input type=checkbox name='iEnableGoTo' value='1' <?php echo $sEnableGoToChecked;?>></td>
	</tr>
	<tr><td>Is Go To PopUp</td>
		<td colspan=3><input type=checkbox name='iIsGoToPopUp' value='1' <?php echo $sIsGoToPopUpChecked;?>></td>
	</tr>
		
	<tr><td>Offers By</td>
		<td colspan=3><input type=checkbox name='iOffersByPageMap' value='1' <?php echo $sOffersByPageMapChecked;?>> Page Map
		 &nbsp; &nbsp; <B>OR</B> &nbsp; &nbsp; <select name=iOffersByCatMap>
		 	<?php echo $sCategoriesOptions;?>
		 	</select> Category Map</td>
	</tr>
	
	<tr><td>Auto Email</td>
		<td colspan=3><input type=checkbox name='iAutoEmail' value='1' <?php echo $sAutoEmailChecked;?>></td>
	</tr>
	
	<tr><td>Auto Email Sub</td>
		<td colspan=3><input type=text name='sAutoEmailSub' value='<?php echo $sAutoEmailSub;?>' size=35></td>
	</tr>
	
	<tr><td>Auto Email Text</td>
		<td colspan=3><textarea name='sAutoEmailText' rows=5 cols=45><?php echo $sAutoEmailText;?></textarea>
			<BR>[EMAIL] will be replaced with user's email address
			<BR>[SOURCE_CODE] will be replaced with the sourceCode
			<BR>[MMDDYY] will be replaced with current date in MMDDYY format
		</td>
	</tr>	
	<tr><td>Auto Email From Address</td>
		<td colspan=3><input type=text name='sAutoEmailFromAddr' value='<?php echo $sAutoEmailFromAddr;?>'></td>
	</tr>	
	<tr><td>Is Cobrand</td>
		<td colspan=3><input type=checkbox name='iIsCobrand' value='1' <?php echo $sIsCobrandChecked;?>></td>
	</tr>

	<tr><td>Display Powered By *</td>
		<td colspan=3><input type=checkbox name='iDisplayPoweredBy' value='1' <?php echo $sDisplayPoweredByChecked;?>></td>
	</tr>			
	<tr><td>Opt Out</td>
		<td colspan=3><input type=checkbox name='iOptOut' value='1' <?php echo $sOptOutChecked;?>></td>
	</tr>
	<tr><td>Sure Opt Out</td>
		<td colspan=3><input type=checkbox name='iSureOptOut' value='1' <?php echo $sSureOptOutChecked;?>></td>
	</tr>
	<tr><td>Sure Opt Out</td>
		<td colspan=3><textarea name='sSureOptOutText' rows=5 cols=45><?php echo $sSureOptOutText;?></textarea></td>
	</tr>
	<tr><td>No Thank You Code *</td>
		<td colspan=3><textarea name='sNoThankYouCode' rows=5 cols=45><?php echo $sNoThankYouCode;?></textarea></td>
	</tr>	
	<tr><td>Page 1 Extra text *</td>
		<td colspan=3><textarea name='sPage1ExtraText' rows=5 cols=45><?php echo $sPage1ExtraText;?></textarea></td>
	</tr>	
	
	<tr><td>Page 2 Extra text 1 *</td>
		<td colspan=3><textarea name='sPage2ExtraText1' rows=5 cols=45><?php echo $sPage2ExtraText1;?></textarea></td>
	</tr>	
	
	<tr><td>Page 2 Extra text 2 *</td>
		<td colspan=3><textarea name='sPage2ExtraText2' rows=5 cols=45><?php echo $sPage2ExtraText2;?></textarea></td>
	</tr>		
	
	<tr><td>Page Background Color *</td>
		<td><input type=text name=sPageBgColor value='<?php echo $sPageBgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPageBgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'></td>
		<td>Font Color *</td>
		<td><input type=text name=sFontColor value='<?php echo $sFontColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sFontColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>
		<BR> (Will be applied to Error Message, Terms & Conditions and Note)</td>
	</tr>
	
	<tr><td>Offer Background Color 1</td>
		<td colspan=3><input type=text name=sOfferBgColor1 value='<?php echo $sOfferBgColor1;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor1","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Offer Background Color 2</td>
		<td colspan=3><input type=text name=sOfferBgColor2 value='<?php echo $sOfferBgColor2;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor2","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>	
	<tr><td>Page2 Background Color *</td>
		<td colspan=3><input type=text name=sPage2BgColor value='<?php echo $sPage2BgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPage2BgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>	
	<tr><td>Display Page2 Header Image *</td>
		<td colspan=3><input type=checkbox name='iDisplayPage2HeaderImage' value='1' <?php echo $sDisplayPage2HeaderImageChecked;?>></td>
	</tr>	
	<tr><td>Display Shopping Spree Disclaimer *</td>
		<td colspan=3><input type=checkbox name='iDisplayShoppingSpreeDisclaimer' value='1' <?php echo $sDisplayShoppingSpreeDisclaimerChecked;?>></td>
	</tr>
	<tr><td valign=top>Show Exit Popup *</td>
		<td colspan=3><input type=radio name='sShowExitPopup' value='never' <?php echo $sShowExitPopupNeverChecked;?>> Never
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type=radio name='sShowExitPopup' value='always' <?php echo $sShowExitPopupAlwaysChecked;?>> Always
			<BR><input type=radio name='sShowExitPopup' value='onlyForSrc' <?php echo $sShowExitPopupOnlySrcChecked;?>> Only For SourceCode
			&nbsp; &nbsp; &nbsp; <input type=radio name='sShowExitPopup' value='notForSrc' <?php echo $sShowExitPopupNotForSrcChecked;?>> Not For SourceCode
			<BR>Source Code <input type=text name=sSrcForExitPopup value='<?php echo $sSrcForExitPopup;?>'>
		<BR><BR>Make sure you have <?php echo htmlentities("<head>");?> tag in page1 template to enable this.
		<BR>You need to place <b>onClick="f=0"</b> in any other link tags and in submit button tag to enable this.
		<BR>Same template can't be used for the pages with/without exit popup checked.</td>
	</tr>
</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Add/Edit Ot Page

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");

session_start();

$sPageTitle = "Nibbles Ot Pages Managements - Add/Edit OT Page";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew || $sSaveContinue) {
	// When New Record Submitted
		
	if ($sPageName == '') {
		$sMessage = "You must enter page name...";
		$bKeepValues = true;
	} else if (!ereg("^[A-Za-z0-9_]+$", $sPageName)) {
		$sMessage = "Page name can contain only Alphabets, Numbers or _ ";
		$bKeepValues = true;
	}
		else {
		
		if (!($iId)) {
			
			//get new pageId
			$sMaxQuery = "SELECT max(id) maxId
						  FROM	 otPages
						  WHERE  id < 9000";
			$rMaxResult = dbQuery($sMaxQuery);
			$iMaxId = 0;
			echo dbError();
			while ($oMaxRow = dbFetchObject($rMaxResult)) {
				$iMaxId = $oMaxRow->maxId;
					
			}
			
			if ( substr( $sPageName, -1 ) == "b" ) {
				$removedName = substr( $sPageName, 0, strlen( $sPageName )-1 );
				$removedClause = " or pageName = '$removedName'";
			} else {
				$removedClause = "";
			}
			$addedName = $sPageName.'b';
			
	
						
			$iNewId = $iMaxId + 1;
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   otPages
					   WHERE  pageName = '$sPageName'
						$removedClause
						or pageName = '$addedName'"; 
			$rCheckResult = dbQuery($sCheckQuery);

			if (dbNumRows($rCheckResult) == 0) {
			// set default values
	
				$iPageLayoutId = '2';
				$iPage2LayoutId = '1';
				$iUserFormLayoutId = '1';
				$iOfferListLayoutId = '1';
				$iE1PageLayoutId = '1';
				$iPassOnPhpsessid = '1';
				//$iDisplayPage2HeaderImage = '';
				
				$sPageName = addslashes($sPageName);
				$sTitle = addslashes($sTitle);
				$sNotes = addslashes($sNotes);
				$sSubmitText = addslashes($sSubmitText);
				$iDisplayOfferHeadline = addslashes($iDisplayOfferHeadline);
				$sListText = addslashes($sListText);
				$sSureOptOutText = addslashes($sSureOptOutText);

				// Insert record if everything is fine
				$sAddQuery = "INSERT INTO otPages(id, pageName, dateTimeAdded, title, notes, pageLayoutId, page2LayoutId, newPage2LayoutId, userFormLayoutId, newUserFormLayoutId, offerListLayoutId, e1PageLayoutId, hiddenForm, minNoOfOffers, maxNoOfOffers, displayYesNo, offerImageSize, offerFontSize,
							displayOfferHeadline, displayList, listText, listIdToDisplay, listPrecheck, submitText, redirectTo, 
							 passOnPhpsessid, offersByPageMap, offersByCatMap, autoEmail, 
							isCobrand, displayPoweredBy, optOut, sureOptOut, sureOptOutText, 
							pageBgColor, borderColor, fontColor, offerBgColor1, offerBgColor2, page2BgColor, displayPage2HeaderImage)
					 VALUES('$iNewId', '$sPageName', NOW(), \"$sTitle\", \"$sNotes\", '$iPageLayoutId', '$iPage2LayoutId', '$iNewPage2LayoutId', '$iUserFormLayoutId', '$iNewUserFormLayoutId', '$iOfferListLayoutId', '$iE1PageLayoutId', '$iHiddenForm', '$iMinNoOfOffers', '$iMaxNoOfOffers', '$iDisplayYesNo', '$sOfferImageSize', '$sOfferFontSize',
							'$iDisplayOfferHeadline', '$iDisplayList', \"$sListText\", '$iListIdToDisplay', '$iListPrecheck', \"$sSubmitText\", '$sRedirectTo', 
							 \"$iPassOnPhpsessid\", '$iOffersByPageMap', '$iOffersByCatMap', '$iAutoEmail', 
							'$iIsCobrand', '$iDisplayPoweredBy', '$iOptOut', '$iSureOptOut', \"$sSureOptOutText\", 
							'$sPageBgColor', '$sBorderColor', '$sFontColor', '$sOfferBgColor1', '$sOfferBgColor2', '$sPage2BgColor', '$iDisplayPage2HeaderImage')";
				$rResult = dbQuery($sAddQuery);

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add page: " . addslashes($sAddQuery) . "\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				
				if ( $rResult ) {
					
					
					$sCheckQuery = "SELECT id
					   FROM   otPages
					   WHERE  pageName = '$sPageName'"; 
					$rCheckResult = dbQuery($sCheckQuery);
					$sRow = dbFetchObject($rCheckResult);
					
					
					
					
					$iPageId = $sRow->id;
				} else {
					echo dbError();
				}
			} else {
				$sMessage = "Page Name Already Exists OR Similar Page Name Already Exists...";
				$bKeepValues = true;
			}
			
	

		} else if ($iId) {

			if ( substr( $sPageName, -1 ) == "b" ) {
				$removedName = substr( $sPageName, 0, strlen( $sPageName )-1 );
				$removedClause = " or pageName = '$removedName'";
			} else {
				$removedClause = "";
			}
			$addedName = $sPageName.'b';


			// When Record Edited
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   otPages
					   WHERE  pageName = '$addedName'
					   $removedClause
					   AND id != '$iId'";
			$rCheckResult = dbQuery($sCheckQuery);

			$sTitle = addslashes($sTitle);
			$sNotes = addslashes($sNotes);
			$sSubmitText = addslashes($sSubmitText);
			$iDisplayOfferHeadline = addslashes($iDisplayOfferHeadline);
			$sListText = addslashes($sListText);
			$sSureOptOutText = addslashes($sSureOptOutText);

			$sEditQuery = "UPDATE   otPages
					   SET 		title = \"$sTitle\",
								notes = \"$sNotes\",								
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
								offersByPageMap = '$iOffersByPageMap', 
								offersByCatMap = '$iOffersByCatMap', 
								autoEmail = '$iAutoEmail', 
								isCobrand = '$iIsCobrand', 
								displayPoweredBy = '$iDisplayPoweredBy', 								
								pageBgColor = '$sPageBgColor',								
								optOut = '$iOptOut',
								sureOptOut = '$iSureOptOut',
								sureOptOutText = \"$sSureOptOutText\",								
								borderColor = '$sBorderColor',
								fontColor = '$sFontColor',
								offerBgColor1 = '$sOfferBgColor1',
								offerBgColor2 = '$sOfferBgColor2',								
								page2BgColor = '$sPage2BgColor', 
								displayPage2HeaderImage ='$iDisplayPage2HeaderImage'
		 			   WHERE    id = '$iId'";
				$rResult = dbQuery($sEditQuery);


				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit page: " . addslashes($sEditQuery) . "\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		

			
			if (dbNumRows($rCheckResult) == 0) {

				$sEditQuery = "UPDATE   otPages
					   SET 		pageName = '$sPageName'
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
				$error = dbError();
				if ($error == "Duplicate entry '$sPageName' for key 2") {
				$sMessage = "Page Name Already Exists OR Similar Page Name Already Exists...";
				}
				else {
					echo dbError();
				}
			}
			else {
				$sMessage = "Page Name Already Exists OR Similar Page Name Already Exists...";
				$bKeepValues = true;
			}
			$iPageId = $iId;
		}
	}

	if (!(is_dir("$sGblPageImagesPath/$sPageName")) ) {				
		mkdir("$sGblPageImagesPath/$sPageName",0777);
		chmod("$sGblPageImagesPath/$sPageName",0777);
	}

	if (!(is_dir("$sGblPageImagesPath/$sPageName/headers")) ) {				
		mkdir("$sGblPageImagesPath/$sPageName/headers",0777);
		chmod("$sGblPageImagesPath/$sPageName/headers",0777);
	}
	
	if (!(is_dir("$sGblPageImagesPath/$sPageName/images")) ) {				
		mkdir("$sGblPageImagesPath/$sPageName/images",0777);
		chmod("$sGblPageImagesPath/$sPageName/images",0777);
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
			$sBorderColor = '';
			$sFontColor = '';
			$sOfferBgColor1 = '';
			$sOfferBgColor2 = '';			
			$sPage2BgColor = '';
			$iDisplayPage2HeaderImage = '';
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
			$sBorderColor = $oRow->borderColor;
			$sFontColor = $oRow->fontColor;
			$sOfferBgColor1 = $oRow->offerBgColor1;
			$sOfferBgColor2 = $oRow->offerBgColor2;			
			$sPage2BgColor = $oRow->page2BgColor;
			$iDisplayPage2HeaderImage = $oRow->displayPage2HeaderImage;
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
		
	// set default values
		
	if (!(isset($iDisplayOfferHeadline))) {
		$iDisplayOfferHeadline = '1';
	}
	
	if ($sBorderColor == '') {
		$sBorderColor = "#F6CA83";
	}
	
	if ($sPageBgColor == '') {
		$sPageBgColor = "#E7EFFF";
	}
	if ($sPage2BgColor == '') {
		$sPage2BgColor = "#E7EFFF";
	}
	
	if ($sAutoEmailFromAddr == '') {
		$sAutoEmailFromAddr = "assist@AmpereMedia.com";
	}

	if ($sOfferBgColor1 == '') {
		$sOfferBgColor1 = 'E7EFFF';
	}
	
	if ($sOfferBgColor2 == '') {
		$sOfferBgColor2 = 'D6DEF7';		
	}
			
	if (!(isset($iDisplayYesNo))) {
		$iDisplayYesNo = '1';	
	}
	
	if (!(isset($sOfferImageSize))) {
		$sOfferImageSize = 'small';	
	}
	
	if (!(isset($sOfferFontSize))) {
		$sOfferFontSize = '12px';	
	}
	
	if (!(isset($iDisplayOfferHeadline))) {
		$iDisplayOfferHeadline = '1';	
	}
	
	if (!(isset($iDisplayList))) {
		$iDisplayList = '1';	
	}
	
	if (!(isset($iListIdToDisplay))) {
		$iListIdToDisplay = '215';	
	}
	
	if (!(isset($iListPrecheck))) {
		$iListPrecheck = '1';
	}
	
	if ($sSureOptOutText == '') {
		$sSureOptOutText = "Are You Sure You Don't Want This Great Offer?\\nWe know you will enjoy the benefits. Remember it is totally free with no obligation of any kind.\\nIf you do not want to cancel this offer please click \\\"Cancel\\\" and finish providing the information requested.";
	}
	
	if (!(isset($sListText))) {
		$sListText = "Join the \"[JOIN_LIST_TITLE]\". FREE Membership, FREE Newsletter,FREE PRIZE-A-MONTH GIVEAWAY Entry! Don't miss out on all the special deals, new products and other third-party offers we know you'll love! Free Bonuses: $125+ Special Instant Shopping Spree Discount Package + Special Report \"Free Samples from National Brands You Trust!\"  Recent featured free samples include Advil, Nesquik, Tide - and more!";
	} else {
		$sListText = ascii_encode(stripslashes($sListText));
	}
	
	if (!(isset($sRedirectTo))) {
		$sRedirectTo = '/p/b2b.php';
	}
	
	if (!(isset($iOptOut))) {
		$iOptOut = '1';
	}
	
	if (!(isset($iSureOptOut))) {
		$iSureOptOut = '1';
	}
	
	if (!(isset($iOffersByPageMap) || isset($iOffersByCatMap))) {
		$iOffersByPageMap = '1';
	}
	
	if (!(isset($iMinNoOfOffers))) {
		$iMinNoOfOffers = '15';
	}
	
	if (!(isset($iMaxNoOfOffers))) {
		$iMaxNoOfOffers = '25';
	}
	
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Prepare Page layout options for selection box
$sLayoutQuery = "SELECT *
			 	 FROM   pageLayouts
				 ORDER BY layout";

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
			 	  FROM   page2Layouts
				  ORDER BY layout ";

$rLayoutResult2 = dbQuery($sLayoutQuery2);
$sNewPage2LayoutOptions = "<option value=''>";
while ($oLayoutRow2 = dbFetchObject($rLayoutResult2)) {
	if ($oLayoutRow2->id == $iPage2LayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sPage2LayoutOptions .= "<option value=$oLayoutRow2->id $sSelected>$oLayoutRow2->layout";
	
	if ($oLayoutRow2->id == $iNewPage2LayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sNewPage2LayoutOptions .= "<option value=$oLayoutRow2->id $sSelected>$oLayoutRow2->layout";
}


// Prepare user form layout options for selection box
$sUserFormLayoutQuery = "SELECT *
			 			 FROM   userFormLayouts
						 WHERE  layout != 'hiddenForm'
						 ORDER BY layout ";

$rUserFormLayoutResult = dbQuery($sUserFormLayoutQuery);
$sNewUserFormLayoutOptions = "<option value=''>";
while ($oUserFormLayoutRow = dbFetchObject($rUserFormLayoutResult)) {
	if ($oUserFormLayoutRow->id == $iUserFormLayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sUserFormLayoutOptions .= "<option value=$oUserFormLayoutRow->id $sSelected>$oUserFormLayoutRow->layout";
	
	if ($oUserFormLayoutRow->id == $iNewUserFormLayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sNewUserFormLayoutOptions .= "<option value=$oUserFormLayoutRow->id $sSelected>$oUserFormLayoutRow->layout";
}


// Prepare offerlist layout options for selection box
$sLayoutQuery = "SELECT *
			 	 FROM   offerListLayouts
				 ORDER BY layout";

$rLayoutResult = dbQuery($sLayoutQuery);

while ($oLayoutRow = dbFetchObject($rLayoutResult)) {
	if ($oLayoutRow->id == $iOfferListLayoutId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sOfferListLayoutOptions .= "<option value=$oLayoutRow->id $sSelected>$oLayoutRow->layout";
}

// Prepare e1 Page layout options for selection box
$sE1LayoutQuery = "SELECT *
			 	   FROM   ePageLayouts
				   ORDER BY layout";

$rE1LayoutResult = dbQuery($sE1LayoutQuery);

while ($oE1LayoutRow = dbFetchObject($rE1LayoutResult)) {
	if ($oE1LayoutRow->id == $iE1PageLayoutId) {		
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sE1PageLayoutOptions .= "<option value=$oE1LayoutRow->id $sSelected>$oE1LayoutRow->layout";
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


// if display page2 header image checked
$sDisplayPage2HeaderImageChecked = "";
if ($iDisplayPage2HeaderImage) {
	$sDisplayPage2HeaderImageChecked = "checked";
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
	
	<tr><td>Redirect To</td>
		<td colspan=3><input type=text name='sRedirectTo' value='<?php echo $sRedirectTo;?>' size=40></td>
		
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
	<tr><td>Sure Opt Out Text</td>
		<td colspan=3><textarea name='sSureOptOutText' rows=5 cols=45><?php echo $sSureOptOutText;?></textarea></td>
	</tr>
	
	<tr><td>Page Background Color *</td>
		<td colspan=3><input type=text name=sPageBgColor value='<?php echo $sPageBgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPageBgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'></td>
		
	</tr>
	
	<tr><td>Page Border Color</td>
		<td colspan=3><input type=text name=sBorderColor value='<?php echo $sBorderColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sBorderColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Offer Background Color 1</td>
		<td colspan=3><input type=text name=sOfferBgColor1 value='<?php echo $sOfferBgColor1;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor1","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Offer Background Color 2</td>
		<td colspan=3><input type=text name=sOfferBgColor2 value='<?php echo $sOfferBgColor2;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor2","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>	
	<tr><td>Page2 Background Color *</td>
		<td colspan=3><input type=text name=sPage2BgColor value='<?php echo $sPage2BgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPage2BgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>	
	
	
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
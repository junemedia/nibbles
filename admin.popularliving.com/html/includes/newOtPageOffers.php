<?php


// Initialize all the variables used in this script
// except those names which may be returned as form submission values or querystring values
$aPathInfo = "";
$sFileName = "";
$sPageName = "";

$sOtPageQuery = "";
$rOtPageResult = "";
$oOtPageRow = "";
$iPageId = "";
$sPageName = "";
$sOtPageTitle = "";
$sHeaderGraphicFile = "";
$iPageLayoutId = "";
$iNoOfOffers = "";
$iDisplayYesNo = "";
$iDisplayBdList = "";
$iListIdToDisplay = "";
$sBdListText = "";
$sSubmitText = "";
$sRedirectTo = "";
$iOffersByPageMap = "";
$iOffersByCatMap = ""; // contains catId, if it's mapped by cat
$iAutoEmail = "";
$sAutoEmailSub = "";
$sAutoEmailText = "";
$sAutoEmailFromAddr = "";
$sAutoSiteName = "";
$iIsCobrand = "";
$iDisplayPoweredBy = "";
$sPageBgColor = "";
$sOfferBgColor1 = "";
$sOfferBgColor2 = "";
$sPixelHtmlCode = '';

$sOffersQuery = "";
$rOffersResult = "";
$oOffersRow = "";
$sOfferCode = "";
$sOfferTitle = "";
$sOfferDescription = "";
$sOfferImage = "";
$iPrecheck = "";
$sAddiInfoFormat = "";
$sAddiInfoTitle = "";
$sAddiInfoText = "";
$sAddiInfoPopupSize = "";
$sOfferChecked = "";
$sBgColor = "";
$sOffersOnPage = "";

$aAddiInfoPopupSizeArray = "";
$iAddiInfoPopupWidth = "";
$iAddiInfoPopupHeight = "";

$sHiddenFields = "";

$sCheckQuery = "";
$rCheckResult = "";
$sPageStatInsertQuery = "";
$rPageStatInsertResult = "";
$sPageStatUpdateQuery = "";
$rPageStatUpdateResult = "";


// Start - Get data for CoRegPopup
$sCoRegEmail = trim($_GET['e']);
$sCoRegFirst = trim($_GET['f']);
$sCoRegLast = trim($_GET['l']);
$sCoRegAddress = trim($_GET['a1']);
$sCoRegAddress2 = trim($_GET['a2']);
$sCoRegCity = trim($_GET['c']);
$sCoRegState = trim($_GET['s']);
$sCoRegZip = trim($_GET['z']);
$sCoRegPhone = trim($_GET['p']);
$sCoRegPhoneNoDash = trim($_GET['pnd']);

if($sCoRegEmail == '') { $sCoRegEmail = $_SESSION["sSesEmail"]; }
if($sCoRegFirst == '') { $sCoRegFirst = $_SESSION["sSesFirst"]; }
if($sCoRegLast == '') { $sCoRegLast = $_SESSION["sSesLast"]; }
if($sCoRegAddress == '') { $sCoRegAddress = $_SESSION["sSesAddress"]; }
if($sCoRegAddress2 == '') { $sCoRegAddress2 = $_SESSION["sSesAddress2"]; }
if($sCoRegCity == '') { $sCoRegCity = $_SESSION["sSesCity"]; }
if($sCoRegState == '') { $sCoRegState = $_SESSION["sSesState"]; }
if($sCoRegZip == '') { $sCoRegZip = $_SESSION["sSesZip"]; }
if($sCoRegPhone == '') { $sCoRegPhone = $_SESSION["sSesPhone"]; }
if($sCoRegPhoneNoDash == '') { $sCoRegPhoneNoDash = $_SESSION["sSesPhoneNoDash"]; }

$sCoRegSessId = $_GET['PHPSESSID'];
$sCoRegSessId = trim($sCoRegSessId);

// End - Get data for CoRegPopup



// if source code and subsource code are not valid, then set it to NULL before we plug this variable
// into mysql query. this is done to prevent sql injection attacks.
if ($sSourceCode) {
	if (!ctype_alnum($sSourceCode)) {
		$sSourceCode = '';
	}
}
if ($sSubSourceCode) {
	if (!ctype_alnum($sSubSourceCode)) {
		$sSubSourceCode = '';
	}
}








// start: initialize variables
$sTargetZip = '';
$iTargetYear = '';
$iTargetExchange = '';
$sTargetGender = '';
$sTargetState = '';
// end: initialize variables

// fire the pixel from e1PageSubmit.php and/or e2PageSubmit.php
if ($_SESSION['sSesEpagePixelUrl'] != '') {
	echo $_SESSION['sSesEpagePixelUrl'];
	$_SESSION['sSesEpagePixelUrl']='';
}


// read the cookie and get all offerCodes.  These offers were taken in the past.
$bCookieSet = false;
if (isset($_COOKIE["OfferTakenInCookie"])) {
	$_SESSION['sSesOfferTakenInCookie'] = $_COOKIE["OfferTakenInCookie"];
	$aOfferTakenInCookie = explode(",", $_COOKIE["OfferTakenInCookie"]);
	$aOfferTakenInCookieCount = count($aOfferTakenInCookie);
	$bCookieSet = true;
}


// start: get value from sessionId if not blank.
if ($_SESSION['sSesTargetZip'] != '') {
    $sTargetZip = $_SESSION['sSesTargetZip'];
}


if ($_SESSION['sSesTargetYear'] != '') {
    $iTargetYear = $_SESSION['sSesTargetYear'];
}


if ($_SESSION['sSesTargetExchange'] != '') {
    $iTargetExchange = $_SESSION['sSesTargetExchange'];
}
    

if ($_SESSION['sSesTargetGender'] != '') {
    $sTargetGender = $_SESSION['sSesTargetGender'];
}


if ($_SESSION['sSesTargetState'] != '') {
	$sTargetState = $_SESSION['sSesTargetState'];
}
// end: get value from sessionId if not blank.

// get the name of this page (the file name itself is the page name)

$aPathInfo = explode("/",$_SERVER['PHP_SELF']);
$sFileName = $aPathInfo[count($aPathInfo)-1];
$sPageName = substr($sFileName,0,strlen($sFileName)-5);


/**************** Get page details which are offers related ************************/

$sOtPageQuery = "SELECT *
				 FROM   otPages
				 WHERE  id = '".$_SESSION["iSesPageId"]."'";

$rOtPageResult = dbQuery($sOtPageQuery);
while ($oOtPageRow = dbFetchObject($rOtPageResult)) {
	$iPageId = $oOtPageRow->id;
	$sPageName = $oOtPageRow->pageName;
	$iOfferListLayoutId = $oOtPageRow->offerListLayoutId;
	$iDisplayYesNo = $oOtPageRow->displayYesNo;
	$sOfferImageSize = $oOtPageRow->offerImageSize;
	$sOfferFontSize = $oOtPageRow->offerFontSize;
	$iDisplayOfferHeadline = $oOtPageRow->displayOfferHeadline;
	$iOffersByPageMap = $oOtPageRow->offersByPageMap;
	$iOffersByCatMap = $oOtPageRow->offersByCatMap; // contains catId, if it's mapped by cat
	$sOfferBgColor1 = $oOtPageRow->offerBgColor1;
	$sOfferBgColor2 = $oOtPageRow->offerBgColor2;
}

if ($rOtPageResult) {
	dbFreeResult($rOtPageResult);
}

if ($_SESSION['iSesCbId'] != '') {
	$sGetCbColorsQuery = "SELECT *
					 FROM   coBrandDetails
					 WHERE  id = '".$_SESSION["iSesCbId"]."'";
	$rGetCbColorsResult = dbQuery($sGetCbColorsQuery);
	while ($oCbColor = dbFetchObject($rGetCbColorsResult)) {
		$sOfferBgColor1 = $oCbColor->offerBgColor1;
		$sOfferBgColor2 = $oCbColor->offerBgColor2;
	}
}

if ($rGetCbColorsResult) {
	dbFreeResult($rGetCbColorsResult);
}

/***********************  End getting page details  ************************/
/*****************  Get the offer list layout  ********************/
$sOfferListLayoutQuery = "SELECT *
							FROM   offerListLayouts
							WHERE  id = '$iOfferListLayoutId'";
$rOfferListLayoutResult = dbQuery($sOfferListLayoutQuery);
while ($oOfferListLayoutRow = dbFetchObject($rOfferListLayoutResult)) {
	$sOfferListLayout = $oOfferListLayoutRow->content;
}

/**********************  End getting offer list layout  **********************/

/************************ Set newSession variable per page **********************/
// used for page display stats.
// Entry in page display stats is made only once per page per session

if (!(isset($_SESSION['sSesNewSession']))) {
	$_SESSION['sSesNewSession'] = 'Y';
	// to keep track of which pages are already displayed in this session
	$_SESSION['aSesPageOffersDisplayed'] = array();
} else if (isset($_SESSION['sSesNewSession'])) {
	// check this page already displayed in this session
	$sTempOldPage = '';
	//echo "dfdf".count($_SESSION['aSesPageOffersDisplayed']);
	for ($i=0; $i<count($_SESSION['aSesPageOffersDisplayed']);$i++) {
		if ($_SESSION['aSesPageOffersDisplayed'][$i][0] == $iPageId) {
			$sTempOldPage = 'Y';
			break;
		}
	}
	if ($sTempOldPage == '') {
		$_SESSION['sSesNewSession'] = 'Y';
	}
}

/************************** End setting new session variable *********************/

/*************  get the offers and put into array to display on this ot page  ***********************/

if ($iOffersByPageMap) {
	$sOffersQuery = "SELECT offers.*, pageMap.isTopDisplay, sortOrder, pageMap.precheck as offerPrecheck
					 FROM   offers, pageMap, offerCompanies AS oc
					 WHERE  offers.offerCode = pageMap.offerCode
					 AND    offers.companyId = oc.id					
					 AND    pageMap.pageId = '$iPageId'";

	if (substr($sPageName,0,4) == 'test') {
		$sOffersQuery .= " AND    (offers.mode = 'T' || offers.mode = 'A') ";
	} else {
		$sOffersQuery .= " AND    offers.mode = 'A' AND   offers.isLive = '1'
		 				   AND    oc.creditStatus = 'ok'";
	}

	$sOffersQuery .= " AND    nibbles2Only != 'Y' 
					ORDER BY isTopDisplay DESC, sortOrder";
} else {
	$sOffersQuery = "SELECT offers.*, categoryMap.isTopDisplay, sortOrder, categoryMap.precheck as offerPrecheck
					 FROM   offers, categoryMap, offerCompanies AS oc
					 WHERE  offers.offerCode = categoryMap.offerCode
					 AND    offers.companyId = oc.id					 
					 AND    categoryId = '$iOffersByCatMap'";

	if (substr($sPageName,0,4) == 'test') {
		$sOffersQuery .= " AND    (offers.mode = 'T' || offers.mode = 'A') ";
	} else {
		$sOffersQuery .= " AND    offers.mode = 'A' AND    offers.isLive = '1'
						   AND    oc.creditStatus = 'ok'";
	}

	$sOffersQuery .= " AND    nibbles2Only != 'Y' 
						ORDER BY isTopDisplay DESC, sortOrder
			 		  LIMIT 0, $iNoOfOffers";
}


$aOffersYesNo = $_SESSION["aSesOffersYesNo"];
$rOffersResult = dbQuery($sOffersQuery);
echo dbError();
$i=0;
if (dbNumRows($rOffersResult) > 0) {

	/********************************

	IMPORTANT : If additional column added into offer array in following while loop,
	Make sure to add that column in array_multisort function right after this loop

	*******************************************/

	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		// Targeting:  Determine which offers should be shown to this user.
		// Default is to show the offer
		$bShowOffer = true;
		// Hide all offers listed on cookie.
		if ($bCookieSet == true) {
			for ($iCount = 0; $iCount<$aOfferTakenInCookieCount; $iCount++) {
				if ($oOffersRow->offerCode == $aOfferTakenInCookie[$iCount]) {
					$bShowOffer = false;
				}
			}
		}
		// If offer is not targeted, show it
		// If it is targetted, determine whether to show it or filter it out.
	if( $bShowOffer == true ) {
		if($oOffersRow->isTarget == 'Y') {
			// if showIfNoInfoAvailable is false, test fields to determine if we can show
			if ($oOffersRow->targetShowNoInfoAvailable == 'N') {
				if ($sTargetZip == '' && $iTargetYear == '' && $iTargetExchange == '' && $sTargetGender == '' && $sTargetState == '') {
					$bShowOffer = false;
				}
			}

			// if we can show offer, test each rule
			if( $bShowOffer == true ) {
					
				//START YEAR
				if ($iTargetYear != '') {	//if year in session is not blank
					if ($oOffersRow->targetStartYear != 0 && $oOffersRow->targetEndYear != 0) {	//if year range is not blank
						//check if range is include
						if($oOffersRow->targetIncExcYear == 'I') { //if year range is not blank and is include
	
							//if $iTargetYear is within the range
							if($iTargetYear >= $oOffersRow->targetStartYear && $iTargetYear <= $oOffersRow->targetEndYear) {
								//check if database is exclude.
								if ($oOffersRow->targetYearDatabase == 'E') {	//year database is excluded
									//if targetYear is in database
									$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
									$rGetYearResult = dbQuery($sGetYearQuery);
									if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
										while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
											if ($sYearDatabaseRow->year == $iTargetYear) {
												$bShowOffer = false;
											}
										}
									}
								}
							} 
							
							//target year is not within range()
							else { 
								if ($oOffersRow->targetYearDatabase == 'I') {	//year database is included
									//if database is I
									$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
									$rGetYearResult = dbQuery($sGetYearQuery);
									if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
										$bYearTemp = false;
										while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
											if ($sYearDatabaseRow->year != $iTargetYear) {
												$bShowOffer = false;
											} else {
												$bShowOffer = true;
												$bYearTemp = true;
											}
										}
										
										if ($bYearTemp == true) {
											$bShowOffer = true;	
										} else {
											$bShowOffer = false;
										}
									}
								 } else {	//if year database is not included, set the value to false
								 	$bShowOffer = false;
								 }
							}
						} 
						
						//(range is exclude)
						elseif ($oOffersRow->targetIncExcYear == 'E') { 
							//if year is within range()
							if ($iTargetYear >= $oOffersRow->targetStartYear && $iTargetYear <= $oOffersRow->targetEndYear) {
								//if database is I(	)
								if ($oOffersRow->targetYearDatabase == 'I') {	//if year database is included
									//if year not in database
									$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
									$rGetYearResult = dbQuery($sGetYearQuery);
									if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
										$bYearTemp = false;
										while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
											if ($sYearDatabaseRow->year != $iTargetYear) {
												$bShowOffer = false;
											} else {
												$bShowOffer = true;
												$bYearTemp = true;
											}
										}
										
										if ($bYearTemp == true) {
											$bShowOffer = true;	
										} else {
											$bShowOffer = false;
										}
									}
								} else {	//if year database is not include
									$bShowOffer = false;
								}
							} 
							// year is outside range
							// see if it's in database exclude
							else {
								$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
								$rGetYearResult = dbQuery($sGetYearQuery);
								if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
									while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
										if ($sYearDatabaseRow->year == $iTargetYear) {
											$bShowOffer = false;
										}
									}
								}
							}
						}
					} 
					
					//range is blank, so only check database
					else { 
						//if database is I
						if ($oOffersRow->targetYearDatabase == 'I') {	//range is blank and year database is include
							$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
							$rGetYearResult = dbQuery($sGetYearQuery);
							if (dbNumRows($rGetYearResult) > 0) {	//if query returns records
								$bYearTemp = false;
								while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
									if ($sYearDatabaseRow->year != $iTargetYear) {
										$bShowOffer = false;
									} else {
										$bShowOffer = true;
										$bYearTemp = true;
									}
								}
								
								if ($bYearTemp == true) {
									$bShowOffer = true;
								} else {
									$bShowOffer = false;
								}
							}
						} elseif ($oOffersRow->targetYearDatabase == 'E') {		//range is blank and year database is exclude
							$sGetYearQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE year !='0'";
							$rGetYearResult = dbQuery($sGetYearQuery);
							if (dbNumRows($rGetYearResult) > 0) {	//query retuns records
								while ($sYearDatabaseRow = dbFetchObject($rGetYearResult)) {
									if ($sYearDatabaseRow->year == $iTargetYear) {
										$bShowOffer = false;
									}
								}
							}
						}
					}
				}
				//END YEAR
			
				// if offer has zip target
				if ($bShowOffer == true) {
						if ($sTargetZip != '') {
							if ($oOffersRow->targetStartZip != '' && $oOffersRow->targetEndZip != '') {
								//check if range is include
								if($oOffersRow->targetIncExcZip == 'I') {
									//if $sTargetZip is within the range
									if($sTargetZip >= $oOffersRow->targetStartZip && $sTargetZip <= $oOffersRow->targetEndZip) {
										//check if database is exclude.
										if ($oOffersRow->targetZipDatabase == 'E') {
											//if targetZip is in database
											$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
											$rGetZipResult = dbQuery($sGetZipQuery);
											if (dbNumRows($rGetZipResult) > 0) {
												while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
													if ($sZipDatabaseRow->zip == $sTargetZip) {
														$bShowOffer = false;
													}
												}
											}
										}
									} else { //target zip is not within range()
											if ($oOffersRow->targetZipDatabase == 'I') {
											//if database is I
											$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
											$rGetZipResult = dbQuery($sGetZipQuery);
											if (dbNumRows($rGetZipResult) > 0) {
												$bZipTemp = false;
												while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
													if ($sZipDatabaseRow->zip != $sTargetZip) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bZipTemp = true;
													}
												}
												if ($bZipTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										 } else {
										 	$bShowOffer = false;
										 }
									}
								} elseif ($oOffersRow->targetIncExcZip == 'E') { //(range is exclude)
									//if zip is within range()
									if ($sTargetZip >= $oOffersRow->targetStartZip && $sTargetZip <= $oOffersRow->targetEndZip) {
										//if database is I(	)
										if ($oOffersRow->targetZipDatabase == 'I') {
											//if zip not in database
											$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
											$rGetZipResult = dbQuery($sGetZipQuery);
											if (dbNumRows($rGetZipResult) > 0) {
												$bZipTemp = false;
												while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
													if ($sZipDatabaseRow->zip != $sTargetZip) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bZipTemp = true;
													}
												}
												
												if ($bZipTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										} else {
											$bShowOffer = false;
										}
									}
									
									// zip is outside range
									// see if it's in database exclude
									else {
										$sGetZipQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE zip !=''";
										$rGetZipResult = dbQuery($sGetZipQuery);
										if (dbNumRows($rGetZipResult) > 0) {	//if query returns records
											while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
												if ($sZipDatabaseRow->zip == $sTargetZip) {
													$bShowOffer = false;
												}
											}
										}
									}
								}
							} else { //range is blank
								//if database is I
								if ($oOffersRow->targetZipDatabase == 'I') {
									$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
									$rGetZipResult = dbQuery($sGetZipQuery);
									if (dbNumRows($rGetZipResult) > 0) {
										$bZipTemp = false;
										while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
										if ($sZipDatabaseRow->zip != $sTargetZip) {
											$bShowOffer = false;
										} else {
											$bShowOffer = true;
											$bZipTemp = true;
										}
									}
									if ($bZipTemp == true) {
										$bShowOffer = true;
									} else {
										$bShowOffer = false;
									}
								}
							} elseif ($oOffersRow->targetZipDatabase == 'E') {		//if database is E
								$sGetZipQuery = "SELECT zip FROM targetData.$oOffersRow->offerCode WHERE zip != ''";
								$rGetZipResult = dbQuery($sGetZipQuery);
								if (dbNumRows($rGetZipResult) > 0) {
									while ($sZipDatabaseRow = dbFetchObject($rGetZipResult)) {
										if ($sZipDatabaseRow->zip == $sTargetZip) {
											$bShowOffer = false;
										}
									}
								}
							}
						}
					}
				}
				
				// if offer has exchange target
				if ($bShowOffer == true) {
						if ($iTargetExchange != '') {
							if ($oOffersRow->targetStartExchange != '' && $oOffersRow->targetEndExchange != '') {
								//check if range is include
								if($oOffersRow->targetIncExcExchange == 'I') {
									//if $iTargetExchange is within the range
									if($iTargetExchange >= $oOffersRow->targetStartExchange && $iTargetExchange <= $oOffersRow->targetEndExchange) {
										//check if database is exclude.
										if ($oOffersRow->targetExchangeDatabase == 'E') {
											//if targetExchange is in database
											$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
											$rGetExchangeResult = dbQuery($sGetExchangeQuery);
											if (dbNumRows($rGetExchangeResult) > 0) {
												while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
													if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
														$bShowOffer = false;
													}
												}
											}
										}
									} else { //target exchange is not within range()
											if ($oOffersRow->targetExchangeDatabase == 'I') {
											//if database is I
											$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
											$rGetExchangeResult = dbQuery($sGetExchangeQuery);
											if (dbNumRows($rGetExchangeResult) > 0) {
												$bExchangeTemp = false;
												while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
													if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bExchangeTemp = true;
													}
												}
												if ($bExchangeTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										 } else {
										 	$bShowOffer = false;
										 }
									}
								} elseif ($oOffersRow->targetIncExcExchange == 'E') { //(range is exclude)
									//if exchange is within range()
									if ($iTargetExchange >= $oOffersRow->targetStartExchange && $iTargetExchange <= $oOffersRow->targetEndExchange) {
										//if database is I(	)
										if ($oOffersRow->targetExchangeDatabase == 'I') {
											//if exchange not in database
											$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
											$rGetExchangeResult = dbQuery($sGetExchangeQuery);
											if (dbNumRows($rGetExchangeResult) > 0) {
												$bExchangeTemp = false;
												while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
													if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
														$bShowOffer = false;
													} else {
														$bShowOffer = true;
														$bExchangeTemp = true;
													}
												}
												
												if ($bExchangeTemp == true) {
													$bShowOffer = true;	
												} else {
													$bShowOffer = false;
												}
											}
										} else {
											$bShowOffer = false;
										}
									}
									
									// exchange is outside range
									// see if it's in database exclude
									else {
										$sGetExchangeQuery = "SELECT year FROM targetData.$oOffersRow->offerCode WHERE exchange !=''";
										$rGetExchangeResult = dbQuery($sGetExchangeQuery);
										if (dbNumRows($rGetExchangeResult) > 0) {	//if query returns records
											while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
												if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
													$bShowOffer = false;
												}
											}
										}
									}
									
								}
							} else { //range is blank
								//if database is I
								if ($oOffersRow->targetExchangeDatabase == 'I') {
									$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
									$rGetExchangeResult = dbQuery($sGetExchangeQuery);
									if (dbNumRows($rGetExchangeResult) > 0) {
										$bExchangeTemp = false;
										while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
										if ($sExchangeDatabaseRow->exchange != $iTargetExchange) {
											$bShowOffer = false;
										} else {
											$bShowOffer = true;
											$bExchangeTemp = true;
										}
									}
									if ($bExchangeTemp == true) {
										$bShowOffer = true;
									} else {
										$bShowOffer = false;
									}
								}
							} elseif ($oOffersRow->targetExchangeDatabase == 'E') {		//if database is E
								$sGetExchangeQuery = "SELECT exchange FROM targetData.$oOffersRow->offerCode WHERE exchange != ''";
								$rGetExchangeResult = dbQuery($sGetExchangeQuery);
								if (dbNumRows($rGetExchangeResult) > 0) {
									while ($sExchangeDatabaseRow = dbFetchObject($rGetExchangeResult)) {
										if ($sExchangeDatabaseRow->exchange == $iTargetExchange) {
											$bShowOffer = false;
										}
									}
								}
							}
						}
					}
				}
				
				// start gender....
				if ($bShowOffer == true) {
					if ($sTargetGender != '') {
						// if offer has gender target,
						if($oOffersRow->targetGender != '') {			
							// if include
							if($oOffersRow->targetIncExcGender == 'I') {
								// does it match the user?
								// if not, throw it out
								if($oOffersRow->targetGender != $sTargetGender) {
									$bShowOffer = false;
								}
							}
							
							// if exclude
							if($oOffersRow->targetIncExcGender == 'E') {
								// does it match the user?
								// if so throw it out
								if($oOffersRow->targetGender == $sTargetGender) {
									$bShowOffer = false;
								}
							}
						}
					}
				}
				
				// start state....
				if ($bShowOffer == true && $sTargetState != '' && $oOffersRow->targetState != '') {
					$aTempState = explode(",", $oOffersRow->targetState);
					
					if($oOffersRow->targetIncExcState == 'I') {	// if include
						// does it match the user?  If not, throw it out
						$bTempStateShow = false;
						for ($ia=0; $ia<=count($aTempState); $ia++) {
							if($aTempState[$ia] == $sTargetState) {
								$bTempStateShow = true;
							}
						}

						if ($bTempStateShow == true) {
							$bShowOffer = true;
						} else {
							$bShowOffer = false;
						}
					}
	
					if($oOffersRow->targetIncExcState == 'E') {	// if exclude
						// does it match the user?  If so throw it out
						for ($a=0; $a<=count($aTempState); $a++) {
							if($aTempState[$a] == $sTargetState) {
								$bShowOffer = false;
							}
						}
					}
				}
			}
		}
	}

		if ($bShowOffer == true) {

			$aOffersArray['offerCode'][$i] = $oOffersRow->offerCode;
			$aOffersArray['offerHeadline'][$i] = $oOffersRow->headline;
			$aOffersArray['offerDescription'][$i] = $oOffersRow->description;
			$aOffersArray['offerShortDescription'][$i] = $oOffersRow->shortDescription;
			$aOffersArray['offerImageName'][$i] = $oOffersRow->imageName;
			$aOffersArray['offerSmallImageName'][$i] = $oOffersRow->smallImageName;
	
			$aOffersArray['precheck'][$i] = $oOffersRow->offerPrecheck;
			// If offer is set to be prechecked on all pages
			if ($oOffersRow->precheckAllPages) {
				$aOffersArray['precheck'][$i] = '1';
			}


			$aOffersArray['addiInfoFormat'][$i] = $oOffersRow->addiInfoFormat;
			$aOffersArray['addiInfoTitle'][$i] = $oOffersRow->addiInfoTitle;
			$aOffersArray['addiInfoText'][$i] = $oOffersRow->addiInfoText;
			$aOffersArray['addiInfoPopupSize'][$i] = $oOffersRow->addiInfoPopupSize;
			$aOffersArray['offerMode'][$i] = $oOffersRow->mode;
			$aOffersArray['isTopDisplay'][$i] = $oOffersRow->isTopDisplay;
			$iSortOrder = $oOffersRow->sortOrder;
			$iDefaultSortOrder = $oOffersRow->defaultSortOrder;
	
			// if sort order is zero in pageMap or categoryMap, apply offer's default sort order
			if ($iSortOrder == 0) {
				$iSortOrder = $iDefaultSortOrder;
			}
			$aOffersArray['sortOrder'][$i] = $iSortOrder;
			$aOffersArray['privacyPolicy'][$i] = $oOffersRow->privacyPolicy;

			$i++;
		}
		
	}
	
	if ($rOffersResult) {
		dbFreeResult($rOffersResult);
	}

	/********************* End getting offers and storing into array **********************/

	//This array contains list of all offerCode that needs to be displayed on the page after targetting filter.
	$_SESSION['sSesOffersCount'] = count($aOffersArray['offerCode']);
	
	// sort offers once again to apply any default sort order of an aoffer

	if (count($aOffersArray['offerCode']) > 0) {
		array_multisort($aOffersArray['sortOrder'], SORT_ASC, $aOffersArray['offerCode'], $aOffersArray['offerHeadline'], $aOffersArray['offerDescription'], $aOffersArray['offerShortDescription'], $aOffersArray['offerImageName'], $aOffersArray['offerSmallImageName'], $aOffersArray['precheck'], $aOffersArray['addiInfoFormat'], $aOffersArray['addiInfoTitle'], $aOffersArray['addiInfoText'], $aOffersArray['addiInfoPopupSize'], $aOffersArray['offerMode'], $aOffersArray['isTopDisplay'], $aOffersArray['privacyPolicy']);
	}

	$k=0;
	/*****************  Loop through offers array to prepare offers list to display  **************/
	$sOffersCheckedJavaScriptValidation = "	if ( ";
	$iTemp = 16;
	for ($o=0; $o < count($aOffersArray['offerCode']); $o++) {

		$sOfferCode = $aOffersArray['offerCode'][$o];

		$sOfferHeadline = $aOffersArray['offerHeadline'][$o];
		$sOfferDescription = $aOffersArray['offerDescription'][$o];
		$sOfferShortDescription = $aOffersArray['offerShortDescription'][$o];
		$sOfferImageName = $aOffersArray['offerImageName'][$o];
		$sOfferSmallImageName = $aOffersArray['offerSmallImageName'][$o];

		$iPrecheck = $aOffersArray['precheck'][$o];
		//$iSureOptOut = $oOffersRow->sureOptOut;

		$sAddiInfoFormat = $aOffersArray['addiInfoFormat'][$o];
		$sAddiInfoTitle = $aOffersArray['addiInfoTitle'][$o];
		$sAddiInfoText = $aOffersArray['addiInfoText'][$o];
		$sAddiInfoPopupSize = $aOffersArray['addiInfoPopupSize'][$o];
		$sOfferMode = $aOffersArray['offerMode'][$o];
		$iIsTopDisplay = $aOffersArray['isTopDisplay'][$o];
		
		
		$sOffersCheckedJavaScriptValidation .= "!document.form1.elements[$iTemp].checked && ";

		// check if layout shows two offers side by side in a row
		// user placeholder variables accordingly and get new layout template in alternate iteration only

		if (strstr($sOfferListLayout, "OFFER1_") && strstr($sOfferListLayout, "OFFER2_")) {
			if ($o%2 == 0) {
				$sTempOfferListLayout = $sOfferListLayout;
				$sTmplOfferImage = "\[OFFER1_IMAGE\]";
				$sTmplOfferBgColor =  "\[OFFER1_BG_COLOR\]";
				$sTmplOfferHeadline =  "\[OFFER1_HEADLINE\]";
				$sTmplOfferFontClass = "\[OFFER1_FONT_CLASS\]";
				$sTmplOfferDescription = "\[OFFER1_DESCRIPTION\]";
				$sTmplOfferShortDescription = "\[OFFER1_SHORT_DESCRIPTION\]";
				$sTmplOfferSelect = "\[OFFER1_SELECT\]";
				$sTmplOfferSelectYes = "\[OFFER1_SELECT_YES\]";
				$sTmplOfferSelectNo = "\[OFFER1_SELECT_NO\]";
				$sTmplOfferAddiInfoLink = "\[OFFER1_ADDI_INFO_LINK\]";
				$sTmp1OfferSelectName = "\[OFFER1_SELECT_NAME\]";
				$sTmp1OfferSelectValueYes = "\[OFFER1_SELECT_VALUE_YES\]";
				$sTmp1OfferSelectYesChecked = "\[OFFER1_SELECT_YES_CHECKED\]";
				$sTmp1OfferSelectNoChecked = "\[OFFER1_SELECT_NO_CHECKED\]";
			} else {
				$sTmplOfferImage = "\[OFFER2_IMAGE\]";
				$sTmplOfferBgColor =  "\[OFFER2_BG_COLOR\]";
				$sTmplOfferHeadline =  "\[OFFER2_HEADLINE\]";
				$sTmplOfferFontClass = "\[OFFER2_FONT_CLASS\]";
				$sTmplOfferDescription = "\[OFFER2_DESCRIPTION\]";
				$sTmplOfferShortDescription = "\[OFFER2_SHORT_DESCRIPTION\]";
				$sTmplOfferSelect = "\[OFFER2_SELECT\]";
				$sTmplOfferSelectYes = "\[OFFER2_SELECT_YES\]";
				$sTmplOfferSelectNo = "\[OFFER2_SELECT_NO\]";
				$sTmplOfferAddiInfoLink = "\[OFFER2_ADDI_INFO_LINK\]";
				$sTmp1OfferSelectName = "\[OFFER2_SELECT_NAME\]";
				$sTmp1OfferSelectValueYes = "\[OFFER2_SELECT_VALUE_YES\]";
				$sTmp1OfferSelectYesChecked = "\[OFFER2_SELECT_YES_CHECKED\]";
				$sTmp1OfferSelectNoChecked = "\[OFFER2_SELECT_NO_CHECKED\]";
			}
		} else {
			$sTempOfferListLayout = $sOfferListLayout;
			$sTmplOfferImage = "\[OFFER_IMAGE\]";
			$sTmplOfferBgColor =  "\[OFFER_BG_COLOR\]";
			$sTmplOfferHeadline =  "\[OFFER_HEADLINE\]";
			$sTmplOfferFontClass = "\[OFFER_FONT_CLASS\]";
			$sTmplOfferDescription = "\[OFFER_DESCRIPTION\]";
			$sTmplOfferShortDescription = "\[OFFER_SHORT_DESCRIPTION\]";
			$sTmplOfferSelect = "\[OFFER_SELECT\]";
			$sTmplOfferSelectYes = "\[OFFER_SELECT_YES\]";
			$sTmplOfferSelectNo = "\[OFFER_SELECT_NO\]";
			$sTmplOfferAddiInfoLink = "\[OFFER_ADDI_INFO_LINK\]";
			$sTmp1OfferSelectName = "\[OFFER_SELECT_NAME\]";
			$sTmp1OfferSelectValueYes = "\[OFFER_SELECT_VALUE_YES\]";
			$sTmp1OfferSelectYesChecked = "\[OFFER_SELECT_YES_CHECKED\]";
			$sTmp1OfferSelectNoChecked = "\[OFFER_SELECT_NO_CHECKED\]";
		}
		
		$sTempOfferListLayout = str_replace("[PRIVACY_POLICY]", $aOffersArray['privacyPolicy'][$o], $sTempOfferListLayout);

		if ($sOfferImageSize == 'small') {
			$sOfferImage = $sOfferSmallImageName;
		} else {
			$sOfferImage = $sOfferImageName;
		}

		switch($sOfferFontSize) {
			case "9px":
			$sOfferFontClass = "offer9";
			break;
			case "11px":
			$sOfferFontClass = "offer11";
			break;
			case "12px":
			$sOfferFontClass = "offer12";
			break;
			default:
			$sOfferFontClass = "offer10";
		}

		// blank the headline if display offer headline is not set
		if(!($iDisplayOfferHeadline)) {
			$sOfferHeadline = "&nbsp;";
		}

		if ($iPrecheck) {
			$sOfferChecked = "checked";
			$sOfferYesChecked = "checked";
		} else {
			$sOfferChecked = "";
		}

		if ($iDisplayYesNo) {
			$sOfferYesChecked = "";
			$sOfferNoChecked = "";

			// reset the array pointer to starting element, otherwise not working
			if (is_array($aOffersYesNo)) {
				reset($aOffersYesNo);
				// go through all the offers
				while (list($key,$val) = each($aOffersYesNo)) {
					//remove \" which is around the key
					$sTempKey = ereg_replace("\"", "",stripslashes($key));
					$sTempVal = $val[0];
					// if the key matches to offercode, check the value
					// if value is same as offerCode, user had checked 'yes', if value is N, user had checked 'No'
					// otherwise user left  yes/no unchecked for the offer
					if ($sTempKey == $sOfferCode && $sTempVal == $sOfferCode) {
						$sOfferYesChecked = "checked";
					} else if ($sTempKey == $sOfferCode && $sTempVal == 'N') {
						$sOfferNoChecked = "checked";
					}
				}
			} else {
				if ($iPrecheck) {
					$sOfferYesChecked = "checked";
				}
			}
		} else {
			// if offer is checked and user came back
			for ($i=0; $i<count($_SESSION["aSesOffersChecked"]);$i++) {
				if ($_SESSION["aSesOffersChecked"][$i] == $sOfferCode) {
					$sOfferChecked = "checked";
					break;
				}
			}
		}

		if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
			$sBgColor = $sOfferBgColor2;
		} else {
			$sBgColor = $sOfferBgColor1;
		}

		if ($iIsTopDisplay) {
			$sOfferListVariable = "sPageTopOffersList";
		} else {
			$sOfferListVariable = "sPageOffersList";
		}

		$sTempOfferImage = "$sGblDisplayOfferImagesUrl/$sOfferCode/$sOfferImage";
		$sTempOfferListLayout = ereg_replace($sTmplOfferImage, $sTempOfferImage, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferBgColor, $sBgColor, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferHeadline, $sOfferHeadline, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferFontClass, $sOfferFontClass, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferDescription, $sOfferDescription, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferShortDescription, $sOfferShortDescription, $sTempOfferListLayout);

		if ($iDisplayYesNo) {
			$sCoRegOutBoundPassOnCode = '';
			$sOnClickPopUpCoRegPopup = '';
			$sGetCoRegPopupInfo = "SELECT * FROM offers WHERE offerCode='$sOfferCode' LIMIT 1";
			$sGetCoRegPopupInfoResult = dbQuery($sGetCoRegPopupInfo);
			while ($oCoRegPopUpRow = dbFetchObject($sGetCoRegPopupInfoResult)) {
				$sIsCoRegPopup = $oCoRegPopUpRow->isCoRegPopUp;
				$sIsCoRegPopupPassOnCode = $oCoRegPopUpRow->coRegPopPassOnPrepopCodes;
				$sCoRegVarMap = $oCoRegPopUpRow->coRegPopPassOnCodeVarMap;
				$sCoRegPopupUrl = $oCoRegPopUpRow->coRegPopUrl;
				$sCoRegPopupTriggerOn = $oCoRegPopUpRow->coRegPopUpTriggerOn;
				$sIsCloseTheyHost = $oCoRegPopUpRow->isCloseTheyHost;
				$sCloseTheyHostTriggerOn = $oCoRegPopUpRow->closeTheyHostTriggerOn;
				if ($sIsCoRegPopup == 'Y') {
					if ($sIsCoRegPopupPassOnCode == 'Y') {
						if ($sCoRegVarMap !='') {
							// Replace our vars with client's var - outbound query
							$aPassOnCodeVarMap = explode(",",$sPassOnCodeVarMap);
							for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
								$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
								if ($aKeyValuePair[0] == 'e') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegEmail."&"; }
								if ($aKeyValuePair[0] == 'f') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegFirst."&"; }
								if ($aKeyValuePair[0] == 'l') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegLast."&"; }
								if ($aKeyValuePair[0] == 'a1') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegAddress."&"; }
								if ($aKeyValuePair[0] == 'a2') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegAddress2."&"; }
								if ($aKeyValuePair[0] == 'c') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegCity."&"; }
								if ($aKeyValuePair[0] == 's') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegState."&"; }
								if ($aKeyValuePair[0] == 'z') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegZip."&"; }
								if ($aKeyValuePair[0] == 'p') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegPhone."&"; }
								if ($aKeyValuePair[0] == 'pnd') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$sCoRegPhoneNoDash."&"; }
							}
						} else {
							// use our vars
							$sCoRegOutBoundPassOnCode .= "e=".$sCoRegEmail."&";
							$sCoRegOutBoundPassOnCode .= "f=".$sCoRegFirst."&";
							$sCoRegOutBoundPassOnCode .= "l=".$sCoRegLast."&";
							$sCoRegOutBoundPassOnCode .= "a1=".$sCoRegAddress."&";
							$sCoRegOutBoundPassOnCode .= "a2=".$sCoRegAddress2."&";
							$sCoRegOutBoundPassOnCode .= "c=".$sCoRegCity."&";
							$sCoRegOutBoundPassOnCode .= "s=".$sCoRegState."&";
							$sCoRegOutBoundPassOnCode .= "z=".$sCoRegZip."&";
							$sCoRegOutBoundPassOnCode .= "p=".$sCoRegPhone."&";
							$sCoRegOutBoundPassOnCode .= "pnd=".$sCoRegPhoneNoDash."&";
						}
						
						if ($sCoRegSessId !='') {
							$sCoRegOutBoundPassOnCode .= "sesId=".$sCoRegSessId."&";
						} else {
							$sCoRegOutBoundPassOnCode .= "sesId=".session_id()."&";
						}
						
						$sCoRegOutBoundPassOnCode = substr($sCoRegOutBoundPassOnCode,0, strlen($sCoRegOutBoundPassOnCode)-1);
	
						if ($sCoRegOutBoundPassOnCode != '') {
							$sCoRegPopupUrl = $sCoRegPopupUrl.'?'.$sCoRegOutBoundPassOnCode;
						}
					}
					$sTrackCoRegOpensUrl = "../includes/trackCoRegPopupCount.php?sOfferCode=$sOfferCode&src=$src&ss=$ss&sessId=$sCoRegSessId";
					$sOnClickPopUpCoRegPopup = "onClick=\"response=coRegPopup.send('$sTrackCoRegOpensUrl','');window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');\"";
				}
			}
			
		if ($sIsCloseTheyHost == 'Y' && $sCloseTheyHostTriggerOn == 'N') {
			// If $sOfferCode is closeTheyHost and triggerOn is No, it will marked as Yes
		    $sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked>Yes <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked>No";
		    $sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked>Yes";
		    $sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked>No";	
		} else {
			if ($sIsCoRegPopup == 'N') {
				// If $sOfferCode is not a coRegPopup, then do regular stuff
				$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked>Yes <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked>No";
				$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked>Yes";
				$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked>No";
			} else {
				$sCoRegOnClickYes = '';
				$sCoRegOnClickNo = '';
				$sTempPreCheckCoReg = '';

				// If $sOfferCode is coRegPopup, then add onclick to radio buttons
				if ($sCoRegPopupTriggerOn == 'Y') {
					$sCoRegOnClickYes = $sOnClickPopUpCoRegPopup;
					if ($sOfferYesChecked == "checked" && $sMessage == '') {
						$sTempPreCheckCoReg .= "<img src='http://www.popularliving.com/includes/trackCoRegPopupCount.php?sOfferCode=$sOfferCode&src=$src&ss=$ss&sessId=$sCoRegSessId' width=1 height=1>";
						$sTempPreCheckCoReg .= "<script language=JavaScript>window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');</script>";
					}
					
					$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked $sCoRegOnClickYes>Yes 
										<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked $sCoRegOnClickNo>No";
					$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked $sCoRegOnClickYes>Yes";
					$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked $sCoRegOnClickNo>No";
				} else {
					$sCoRegOnClickNo = $sOnClickPopUpCoRegPopup;
					if ($sOfferNoChecked == "checked" && $sMessage == '') {
						$sTempPreCheckCoReg .= "<img src='http://www.popularliving.com/includes/trackCoRegPopupCount.php?sOfferCode=$sOfferCode&src=$src&ss=$ss&sessId=$sCoRegSessId' width=1 height=1>";
						$sTempPreCheckCoReg .= "<script language=JavaScript>window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');</script>";
					}
					
					$sTempOfferSelectOption = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked $sCoRegOnClickYes>Yes 
										<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked $sCoRegOnClickNo>No";
					$sTempOfferSelectOptionYes = "<input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='N' $sOfferNoChecked $sCoRegOnClickYes>Yes";
					$sTempOfferSelectOptionNo = " <input type=radio name='aOffersChecked[\"$sOfferCode\"][]' value='$sOfferCode' $sOfferYesChecked $sCoRegOnClickNo>No";
				}
			}
		}
			// following variables are to replace individual yes/no components, if template defined so
			$sTempOfferSelectName = "aOffersChecked[\"$sOfferCode\"][]";
			$sTempOfferSelectValueYes = "$sOfferCode";

		} else {
			$sTempOfferSelectOption = "<input type=checkbox name='aOffersChecked[]' value='$sOfferCode' $sOfferChecked> &nbsp;";
		}


		$sTempOfferListLayout = ereg_replace($sTmplOfferSelect, $sTempOfferSelectOption, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferSelectYes, $sTempOfferSelectOptionYes, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmplOfferSelectNo, $sTempOfferSelectOptionNo, $sTempOfferListLayout);

		// following variables are to replace individual yes/no components, if template defined so
		$sTempOfferListLayout = ereg_replace($sTmp1OfferSelectName, $sTempOfferSelectName, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmp1OfferSelectValueYes, $sTempOfferSelectValueYes, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmp1OfferSelectYesChecked, $sOfferYesChecked, $sTempOfferListLayout);
		$sTempOfferListLayout = ereg_replace($sTmp1OfferSelectNoChecked, $sOfferNoChecked, $sTempOfferListLayout);
		$k++;

		$sTempAddiInfoLink = '';

		if ($sAddiInfoText != '') {
			// add additional information link for popup
			$aAddiInfoPopupSizeArray = explode(",",$sAddiInfoPopupSize);
			$iAddiInfoPopupWidth = $aAddiInfoPopupSizeArray[0];
			$iAddiInfoPopupHeight = $aAddiInfoPopupSizeArray[1];
			$sTempAddiInfoLink = " <a href='JavaScript:void(window.open(\"$sGblSiteRoot/offerAddiInfo.php?sOfferCode=$sOfferCode\",\"addiInfo\",\"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>$sAddiInfoTitle</a>";
		}

		$sTempOfferListLayout = ereg_replace($sTmplOfferAddiInfoLink, $sTempAddiInfoLink, $sTempOfferListLayout);

		// check if layout shows two offers side by side in a row
		// place template content into offer list in alternate iteration only when offer1 and offer2 variables are replaced in template
		if (strstr($sOfferListLayout, "OFFER1_") && strstr($sOfferListLayout, "OFFER2_") ) {
			if ($o%2 != 0) {
				$$sOfferListVariable .= "<tr><td  bgcolor=$sBgColor>".$sTempOfferListLayout."</td></tr>";
			}
		} else {
			$$sOfferListVariable .= "<tr><td  bgcolor=$sBgColor>".$sTempOfferListLayout."</td></tr>";
		}

		// offer display stats
		$sPageOfferDisplayed = '';
		if ($sOfferMode != 'T') {
			for ($i=0; $i < count($_SESSION['aSesPageOffersDisplayed']); $i++) {
				if ($_SESSION['aSesPageOffersDisplayed'][$i][0] == $iPageId && $_SESSION['aSesPageOffersDisplayed'][$i][1] == $sOfferCode) {
					$sPageOfferDisplayed = 'Y';
					break;
				}
			}

			if ($sPageOfferDisplayed != 'Y') {
				$sOfferDisplayStatInfo .= "$sOfferCode,";
				// push in offers array for stats of offers per session per page
				array_push($_SESSION['aSesPageOffersDisplayed'], array($iPageId, $sOfferCode));
			}
		}
		$iTemp = $iTemp + 2;
	}
	$sOffersCheckedJavaScriptValidation = substr($sOffersCheckedJavaScriptValidation,0,strlen($sOffersCheckedJavaScriptValidation)-3);
	$sOffersCheckedJavaScriptValidation .= ") {
		errMessage +=\"\\n* In order to proceed, please check 'YES' to receive information \\r\\nfrom at least one of our partners.\";
	}";

	/**************************  End loop through offers array  ***********************/
	// pass all query string variables as hidden fields ONLY If not returned back from submit script becaouse of any error
	$sHiddenFields .= "<tr><td><input type=hidden name='sPageName' value='$sPageName'>
						<input type=hidden name='sSourceCode' value='$sSourceCode'>
						<input type=hidden name='sSubSourceCode' value='$sSubSourceCode'>
						<input type=hidden name='sPageMode' value='$sPageMode'></td></tr>";

	// place hidden fields in otPageContent alongwith offersList
	$sPageOffersList .= $sHiddenFields.$sTempPreCheckCoReg;

	/*************** make an entry in display stats tables and display pixel code if not in Test mode('T') **************/
	// this entry also indicates redirect stats through sourceCode
	if ($sPageMode != 'T') {
		// insert into temporary page display counts table
		if (isset($_SESSION['sSesNewSession']) && $_SESSION['sSesNewSession']=='Y') {
			$sPageStatQuery = "INSERT INTO tempPageDisplayStats(pageId, sourceCode, subSourceCode, openDate)
							   VALUES('$iPageId', '$sSourceCode', '$sSubSourceCode',CURRENT_DATE)";
			$rPageStatResult = dbQuery($sPageStatQuery);
			echo dbError();
			$_SESSION['sSesNewSession'] = '';
		}

		// Insert into Temporary offer display counts table
		if (strlen($sOfferDisplayStatInfo) != 0 ) {
			$sOfferDisplayStatInfo = substr($sOfferDisplayStatInfo,0,strlen($sOfferDisplayStatInfo)-1);
			$sStatQuery = "INSERT INTO tempOfferDisplayStats(pageId, statInfo, sourceCode, subSourceCode, displayDate)
						VALUES('$iPageId', '$sOfferDisplayStatInfo', '$sSourceCode', '$sSubSourceCode', CURRENT_DATE)";
			$rStatResult = dbQuery($sStatQuery);
		}


		/***************** Display Pixel *********************/
		// Pixel Display Code (As per Pixel Display Process in Docs)

		/********************* get always display pixels to display on page ******************/
		$sPixelCheckQuery = "SELECT *
							 FROM   pixels
							 WHERE  pageId = '$iPageId' 							 
							 AND    alwaysDisplay = '1' ";
		$rPixelCheckResult = dbQuery($sPixelCheckQuery);

		while ($oPixelCheckRow = dbFetchObject($rPixelCheckResult)) {
			$sTempPixelHtmlCode = $oPixelCheckRow->pixelHtml;
			$sTempPixelHtmlCode = str_replace("[salutation]",($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[email]",($sEmail ? $sEmail : $_SESSION["sSesEmail"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[first]",($sFirst ? $sFirst : $_SESSION["sSesFirst"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[last]",($sLast ? $sLast : $_SESSION["sSesLast"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[address]",($sAddress ? $sAddress : $_SESSION["sSesAddress"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[address2]",($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[city]",($sCity ? $sCity : $_SESSION["sSesCity"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[state]",($sState ? $sState : $_SESSION["sSesState"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[zip]",($sZip ? $sZip : $_SESSION["sSesZip"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[zip5only]",substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[phone]",($sPhone ? $sPhone : $_SESSION["sSesPhone"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[ipAddress]",($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[phone_areaCode]", ($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode']), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[phone_exchange]", ($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange']), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[phone_number]", ($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber']), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[mm]", date('m'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[dd]", date('d'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[yyyy]", date('Y'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[yy]", date('y'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[hh]", date('H'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[ii]", date('i'), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[birthYear]", ($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[birthMonth]", ($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[birthDay]", ($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[gender]", ($sGender ? $sGender : $_SESSION["sSesGender"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[sourcecode]", ($sSourceCode ? $sSourceCode : $_SESSION["sSesSourceCode"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[src]", ($sSourceCode ? $sSourceCode : $_SESSION["sSesSourceCode"]), $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = str_replace("[ss]", ($sSubSourceCode ? $sSubSourceCode : $_SESSION["sSesSubSourceCode"]), $sTempPixelHtmlCode);
			
			$digits = array('0','1','2','3','4','5','6','7','8','9');
			$aTemp = array_rand($digits,6);
			$iRandomNum = '';
			foreach ($aTemp as $val) {
				$iRandomNum .= $val;
			}
			$sTempPixelHtmlCode = str_replace("[6_DIGIT_RAND_NUM]", $iRandomNum, $sTempPixelHtmlCode);
			
			// this script will only replaces [Q_WHATEVER] with whatever the data we get from query string
			while (strstr($sTempPixelHtmlCode,"[Q_")) {
				$iPosStart = strpos($sTempPixelHtmlCode, "[Q_");
				$iPosEnd = strpos($sTempPixelHtmlCode, "]", $iPosStart);
				$iLength = $iPosEnd - $iPosStart + 1;
				if ($iLength > 0) {
					$sFindThisTag = substr($sTempPixelHtmlCode,$iPosStart,$iLength);
					$sReplaceWith = substr($sFindThisTag,1,strlen($sFindThisTag)-2);
					$sTempPixelHtmlCode = str_replace($sFindThisTag,$_SESSION["$sReplaceWith"], $sTempPixelHtmlCode);
				}
			}

			// replace [emailId] with id of the email
			$iEmailId = '';
			if ($sEmail) {
				$sCheckQuery1 = "SELECT id
									 FROM   userData
									 WHERE  email = '$sEmail'";
				$rCheckResult1 = dbQuery($sCheckQuery1);
				echo dbError();
				if ( dbNumRows($rCheckResult1) > 0 ) {
					while($oCheckRow1 = dbFetchObject($rCheckResult1)) {
						$iEmailId = $oCheckRow1->id;
					}
				} else {
					$sCheckQuery2 = "SELECT id
									 FROM   userDataHistory
									 WHERE  email = '$sEmail'";
					$rCheckResult2 = dbQuery($sCheckQuery2);
					echo dbError();
					while($oCheckRow2 = dbFetchObject($rCheckResult2)) {
						$iEmailId = $oCheckRow2->id;
					}
				}
			}
			$sTimeStamp = (string) time();
			$sTempPixelHtmlCode = eregi_replace("\[emailId\]", $iEmailId, $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = eregi_replace("\[email\]", $sEmail, $sTempPixelHtmlCode);
			$sTempPixelHtmlCode = eregi_replace("\[timeStamp\]", $sTimeStamp, $sTempPixelHtmlCode);
			$sPixelHtmlCode .= $sTempPixelHtmlCode;

			/**** following is for displaying incoming var in pixel code ********/
			// replace [START_VAR]...[END_VAR] with php incoming variable
			while( preg_match( "/\[START_VAR\]/", $sTempPixelHtmlCode ) ) {
				$iPosStart = strpos($sTempPixelHtmlCode, "[START_VAR]");
				$iPosEnd = strpos($sTempPixelHtmlCode, "[END_VAR]", $iPosStart);
				$iLength = $iPosEnd - $iPosStart-2;
				if ($iLength > 0) {
					$sVarString = substr($sTempPixelHtmlCode, $iPosStart, $iLength+11);
					// get the var name
					$sVarName = ereg_replace("\[START_VAR\]", "", $sVarString);
					$sVarName = ereg_replace("\[END_VAR\]", "", $sVarName);

					// get the var value
					$sVarValue = $$sVarName;
					// put var value at the place of [START_VAR]...[END_VAR] tag
					if ($sVarString != '') {
						$sTempPixelHtmlCode = str_replace($sVarString, $sVarValue, $sTempPixelHtmlCode);
					}
				}
			}

			// increment the pixel display count
			$sCheckQuery = "SELECT *
								FROM   otPixelTracking
								WHERE  sourceCode = '$sSourceCode'
								AND    subSourceCode = '$sSubSourceCode'
								AND    openDate = CURRENT_DATE";
			$rCheckResult = dbQuery($sCheckQuery);
			echo dbError();
			if ( dbNumRows($rCheckResult) == 0 ) {
				// insert pixel tracking record
				$sPixelTrackInsertQuery = "INSERT INTO otPixelTracking(sourceCode, subSourceCode, openDate, opens)
											   VALUES('$sSourceCode', '$sSubSourceCode',CURRENT_DATE, 1)";	
				$rPixelTrackInsertResult = dbQuery($sPixelTrackInsertQuery);
			} else {
				// update pixel tracking record
				$sPixelTrackUpdateQuery = "UPDATE otPixelTracking
											   SET    opens = opens + 1
											   WHERE  sourceCode = '$sSourceCode'
											   AND    subSourceCode = '$sSubSourceCode'
											   AND    openDate = CURRENT_DATE";
				$rPixelTrackUpdateResult = dbQuery($sPixelTrackUpdateQuery);
				echo dbError();
			}
			if ($rCheckResult) {
				dbFreeResult($rCheckResult);
			}
		}

		/******************** End always display pixels **********************/
		/***************** get conditional pixels to display ****************/
		if (count($_SESSION["aSesOffersTaken"]) > 0) {
			
			$sPixelCheck2ndRound = "SELECT subSourceCode FROM pixels WHERE
							pageId = '$iPageId' 
							 AND    sourceCode = '$sSourceCode'
							 AND    alwaysDisplay = '' ";
			$rPixelCheck2ndRound = dbQuery($sPixelCheck2ndRound);
			$aSubSourcesForThisPixel = array();
			//echo "$sPixelCheck2ndRound<br>";
			while($oPixelCheck2ndRound = dbFetchObject($rPixelCheck2ndRound)){
				array_push($aSubSourcesForThisPixel, ($oPixelCheck2ndRound->subSourceCode == "" ? "" : $oPixelCheck2ndRound->subSourceCode));
			}
			//var_dump($aSubSourcesForThisPixel);
			if(!(in_array($sSubSourceCode,$aSubSourcesForThisPixel))&&(in_array('',$aSubSourcesForThisPixel))){
				$sPixelCheckQuery = "SELECT *
								 FROM   pixels
								 WHERE  pageId = '$iPageId' 
								 AND    sourceCode = '$sSourceCode'
								 AND    subSourceCode = ''
								 AND    alwaysDisplay = '' ";
				//echo "$sPixelCheckQuery<br>";
			} else {
				$sPixelCheckQuery = "SELECT *
								 FROM   pixels
								 WHERE  pageId = '$iPageId' 
								 AND    sourceCode = '$sSourceCode'
								 AND    subSourceCode = '$sSubSourceCode'
								 AND    alwaysDisplay = '' ";
				//echo "$sPixelCheckQuery<br>";
			}

			$rPixelCheckResult = dbQuery($sPixelCheckQuery);
			echo dbError();
			if ( (dbNumRows($rPixelCheckResult) > 0) ) {
				while ($oPixelCheckRow = dbFetchObject($rPixelCheckResult)) {

					// display the pixel if user has taken at least one offer
					// or pixel is always display pixel
					$sTempPixelHtmlCode = $oPixelCheckRow->pixelHtml;
					$sTempPixelHtmlCode = str_replace("[salutation]",($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[email]",($sEmail ? $sEmail : $_SESSION["sSesEmail"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[first]",($sFirst ? $sFirst : $_SESSION["sSesFirst"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[last]",($sLast ? $sLast : $_SESSION["sSesLast"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[address]",($sAddress ? $sAddress : $_SESSION["sSesAddress"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[address2]",($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[city]",($sCity ? $sCity : $_SESSION["sSesCity"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[state]",($sState ? $sState : $_SESSION["sSesState"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[zip]",($sZip ? $sZip : $_SESSION["sSesZip"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[zip5only]",substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[phone]",($sPhone ? $sPhone : $_SESSION["sSesPhone"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[ipAddress]",($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[phone_areaCode]", ($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode']), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[phone_exchange]", ($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange']), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[phone_number]", ($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber']), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[mm]", date('m'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[dd]", date('d'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[yyyy]", date('Y'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[yy]", date('y'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[hh]", date('H'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[ii]", date('i'), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[birthYear]", ($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[birthMonth]", ($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[birthDay]", ($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[gender]", ($sGender ? $sGender : $_SESSION["sSesGender"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[sourcecode]", ($sSourceCode ? $sSourceCode : $_SESSION["sSesSourceCode"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[src]", ($sSourceCode ? $sSourceCode : $_SESSION["sSesSourceCode"]), $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = str_replace("[ss]", ($sSubSourceCode ? $sSubSourceCode : $_SESSION["sSesSubSourceCode"]), $sTempPixelHtmlCode);
					
					$digits = array('0','1','2','3','4','5','6','7','8','9');
					$aTemp = array_rand($digits,6);
					$iRandomNum = '';
					foreach ($aTemp as $val) {
						$iRandomNum .= $val;
					}
					$sTempPixelHtmlCode = str_replace("[6_DIGIT_RAND_NUM]", $iRandomNum, $sTempPixelHtmlCode);
					
					// this script will only replaces [Q_WHATEVER] with whatever the data we get from query string
					while (strstr($sTempPixelHtmlCode,"[Q_")) {
						$iPosStart = strpos($sTempPixelHtmlCode, "[Q_");
						$iPosEnd = strpos($sTempPixelHtmlCode, "]", $iPosStart);
						$iLength = $iPosEnd - $iPosStart + 1;
						if ($iLength > 0) {
							$sFindThisTag = substr($sTempPixelHtmlCode,$iPosStart,$iLength);
							$sReplaceWith = substr($sFindThisTag,1,strlen($sFindThisTag)-2);
							$sTempPixelHtmlCode = str_replace($sFindThisTag,$_SESSION["$sReplaceWith"], $sTempPixelHtmlCode);
						}
					}

					// replace [emailId] with id of the email
					$iEmailId = '';
					if ($sEmail) {
						$sCheckQuery1 = "SELECT id
									 FROM   userData
									 WHERE  email = '$sEmail'";
						$rCheckResult1 = dbQuery($sCheckQuery1);
						echo dbError();
						if ( dbNumRows($rCheckResult1) > 0 ) {
							while($oCheckRow1 = dbFetchObject($rCheckResult1)) {
								$iEmailId = $oCheckRow1->id;
							}
						} else {
							$sCheckQuery2 = "SELECT id
									 FROM   userDataHistory
									 WHERE  email = '$sEmail'";
							$rCheckResult2 = dbQuery($sCheckQuery2);
							echo dbError();
							while($oCheckRow2 = dbFetchObject($rCheckResult2)) {
								$iEmailId = $oCheckRow2->id;
							}
						}
					}

					$sTimeStamp = (string) time();
					$sTempPixelHtmlCode = eregi_replace("\[emailId\]", $iEmailId, $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = eregi_replace("\[email\]", $sEmail, $sTempPixelHtmlCode);
					$sTempPixelHtmlCode = eregi_replace("\[timeStamp\]", $sTimeStamp, $sTempPixelHtmlCode);

					$sPixelHtmlCode .= $sTempPixelHtmlCode;

					/**** following is for displaying incoming var in pixel code ********/
					// replace [START_VAR]...[END_VAR] with php incoming variable
					while( preg_match( "/\[START_VAR\]/", $sTempPixelHtmlCode ) ) {
						$iPosStart = strpos($sTempPixelHtmlCode, "[START_VAR]");
						$iPosEnd = strpos($sTempPixelHtmlCode, "[END_VAR]", $iPosStart);
						$iLength = $iPosEnd - $iPosStart-2;
						if ($iLength > 0) {

							$sVarString = substr($sTempPixelHtmlCode, $iPosStart, $iLength+11);
							// get the var name
							$sVarName = ereg_replace("\[START_VAR\]", "", $sVarString);
							$sVarName = ereg_replace("\[END_VAR\]", "", $sVarName);

							// get the var value
							$sVarValue = $$sVarName;
							// put var value at the place of [START_VAR]...[END_VAR] tag
							if ($sVarString != '') {
								$sTempPixelHtmlCode = str_replace($sVarString, $sVarValue, $sTempPixelHtmlCode);
							}
						}
					}

					// increment the pixel display count
					$sCheckQuery = "SELECT *
								FROM   otPixelTracking
								WHERE  sourceCode = '$sSourceCode'
								AND    subSourceCode = '$sSubSourceCode'
								AND    openDate = CURRENT_DATE";
					$rCheckResult = dbQuery($sCheckQuery);
					echo dbError();
					if ( dbNumRows($rCheckResult) == 0 ) {
						// insert pixel tracking record
						$sPixelTrackInsertQuery = "INSERT INTO otPixelTracking(sourceCode, subSourceCode, openDate, opens)
											   VALUES('$sSourceCode', '$sSubSourceCode',CURRENT_DATE, 1)";	
						$rPixelTrackInsertResult = dbQuery($sPixelTrackInsertQuery);
						echo dbError();
					} else {
						// update pixel tracking record
						$sPixelTrackUpdateQuery = "UPDATE otPixelTracking
											   SET    opens = opens + 1
											   WHERE  sourceCode = '$sSourceCode'
											   AND    subSourceCode = '$sSubSourceCode'
											   AND    openDate = CURRENT_DATE";
						$rPixelTrackUpdateResult = dbQuery($sPixelTrackUpdateQuery);
						echo dbError();
					}
					if ($rCheckResult) {
						dbFreeResult($rCheckResult);
					}
					//}
				} // end of while loop
			}
		}
		/********************** End conditional pixel display *****************/
		/************************ End pixel display ************************/
	}
	$sPageOffersList .= "<TR><TD colspan=3>$sPixelHtmlCode</td></tr>";
}

?>
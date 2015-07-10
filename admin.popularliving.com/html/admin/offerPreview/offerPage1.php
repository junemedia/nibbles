<?php


// Initialize all the variables used in this script
// except those names which may be returned as form submission values or querystring values

// get the name of this page (the file name itself is the page name)

$aPathInfo = explode("/",$PHP_SELF);
$sFileName = $aPathInfo[count($aPathInfo)-1];
$sPageName = substr($sFileName,0,strlen($sFileName)-4);

// get the offers to display on this ot page
/*

$sOtPageQuery = "SELECT *
				 FROM   otPages
				 WHERE  pageName = 'offerPreview'";
$rOtPageResult = mysql_query($sOtPageQuery);
while ($oOtPageRow = mysql_fetch_object($rOtPageResult)) {
	$iPageId = $oOtPageRow->id;
	$sPageName = $oOtPageRow->pageName;
	$sOtPageTitle = $oOtPageRow->title;
	$sHeaderGraphicFile = $oOtPageRow->headerGraphicFile;
	$iPageLayoutId = $oOtPageRow->pageLayoutId;
	$iNoOfOffers = $oOtPageRow->pageLayoutId;
	$iDisplayYesNo = $oOtPageRow->displayYesNo;
	$iDisplayBdList = $oOtPageRow->displayBdList;
	$iListIdToDisplay = $oOtPageRow->listIdToDisplay;
	$sBdListText = $oOtPageRow->bdListText;
	$sSubmitText = $oOtPageRow->submitText;
	$sRedirectTo = $oOtPageRow->redirectTo;
	$iOffersByPageMap = $oOtPageRow->offersByPageMap;
	$iOffersByCatMap = $oOtPageRow->offersByCatMap; // contains catId, if it's mapped by cat
	$iAutoEmail = $oOtPageRow->autoEmail;
	$sAutoEmailSub = $oOtPageRow->autoEmailSub;
	$sAutoEmailText = $oOtPageRow->autoEmailText;
	$sAutoEmailFromAddr = $oOtPageRow->autoEmailFromAddr;
	$sAutoSiteName = $oOtPageRow->autoSiteName;
	$iIsCobrand = $oOtPageRow->isCobrand;
	$iDisplayPoweredBy = $oOtPageRow->displayPoweredBy;
	$sPageBgColor = $oOtPageRow->pageBgColor;
	$sOfferBgColor1 = $oOtPageRow->offerBgColor1;
	$sOfferBgColor2 = $oOtPageRow->offerBgColor2;
	
}
if ($rOtPageResult) {
	mysql_free_result($rOtPageResult);
}
*/

if (!(isset($_SESSION['sSesNewSession']))) {
	$_SESSION['sSesNewSession'] = 'Y';
	
	// to keep track of which pages are already displayed in this session
	$_SESSION['aSesPageOffersDisplayed'] = array();
} else if (isset($_SESSION['sSesNewSession'])) {
	// check this page already displayed in this session
	$sTempOldPage = '';
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


$sOffersQuery = "SELECT *
					 FROM   offers
					 WHERE  offers.offerCode = '$sOfferCode'";
		

$rOffersResult = mysql_query($sOffersQuery);
echo mysql_error();
$k=0;
//echo $sOffersQuery. mysql_num_rows($rOffersResult);
if (mysql_num_rows($rOffersResult) > 0) {
	while ($oOffersRow = mysql_fetch_object($rOffersResult)) {
		
		$sOfferCode = $oOffersRow->offerCode;
		$sOfferHeadline = $oOffersRow->headline;
		$sOfferDescription = $oOffersRow->description;
		$sOfferImageName = $oOffersRow->imageName;
		$iPrecheck = $oOffersRow->precheck;
		//$iSureOptOut = $oOffersRow->sureOptOut;
		
		$sAddiInfoFormat = $oOffersRow->addiInfoFormat;
		$sAddiInfoTitle = $oOffersRow->addiInfoTitle;
		$sAddiInfoText = $oOffersRow->addiInfoText;
		$sAddiInfoPopupSize = $oOffersRow->addiInfoPopupSize;
		$sOfferMode = $oOffersRow->mode;
		
		
		if ($iPrecheck) {
			$sOfferChecked = "checked";
		} else {
			$sOfferChecked = "";
		}
		
		
		
			// if offer is checked and user came back
			for ($i=0; $i<count($_SESSION["aSesOffersChecked"]);$i++) {
				
				if ($_SESSION["aSesOffersChecked"][$i] == $sOfferCode) {
					$sOfferChecked = "checked";
					break;
				}
				
			}
		
		
		if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
			$sBgColor = $sOfferBgColor2;
		} else {
			$sBgColor = $sOfferBgColor1;
		}
		
		$sOfferListVariable = "sPageOffersList";
		
		
		$$sOfferListVariable .= "<tr><td  bgcolor=$sBgColor>
							<table class=table650 align=center cellpaddint=0 cellspacing=0 border=0>
								<tr bgcolor=$sBgColor><td width=15%><img src='$sGblOfferImageUrl/$sOfferCode/$sOfferImageName'></td>
									<td width=15>&nbsp;</td>
									<td width=80% class=bigBold valign=center><b>$sOfferHeadline</b></td>
								</tr>
								<tr bgcolor=$sBgColor>";
		
			$$sOfferListVariable .= "<td colspan=3><input type=checkbox name='aOffersChecked[]' value='$sOfferCode' $sOfferChecked> &nbsp; ";
		
		$k++;
		$$sOfferListVariable .= "$sOfferDescription";
		
		if ($sAddiInfoText != '') {
			// add additional information link for popup
			$aAddiInfoPopupSizeArray = explode(",",$sAddiInfoPopupSize);
			$iAddiInfoPopupWidth = $aAddiInfoPopupSizeArray[0];
			$iAddiInfoPopupHeight = $aAddiInfoPopupSizeArray[1];
			
			$$sOfferListVariable .= " <a href='JavaScript:void(window.open(\"$sGblSiteRoot/offerAddiInfo.php?sOfferCode=$sOfferCode\",\"addiInfo\",\"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Additional Information</a>";
		}
		
		$$sOfferListVariable .= "</td></tr><tr bgcolor=$sBgColor><td colspan=3 align=center ><HR width=580></td></tr></table></td></tr>";
		
		// offer display stats
		$sPageOfferDisplayed = '';
		
	}
	
	if ($rOffersResult) {
		mysql_free_result($rOffersResult);
	}
	
	$sHiddenFields .= "<tr><td><input type=hidden name='sPageName' value='$sPageName'>
					</td></tr>";
	
	// place hidden fields in otPageContent alongwith offersList
	$sPageOffersList .= $sHiddenFields;
		
}


?>
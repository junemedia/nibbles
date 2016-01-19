<?php

//Script prepares offers list to display on ot page2
session_start();

/**************** get ot page information which are related to offers list  ***************/
$sPageQuery = "SELECT *   FROM   otPages    WHERE  id = '".$_SESSION["iSesPageId"]."'";
$rPageResult = dbQuery($sPageQuery);
while ($oPageRow = dbFetchObject($rPageResult)) {
	$iPageId = $oPageRow->id;
	$sPageName = $oPageRow->pageName;
	$sOtPageTitle = $oPageRow->title;	
	$sHeaderGraphicFile = $oPageRow->headerGraphicFile;		
	$sOfferBgColor1 = $oPageRow->offerBgColor1;
	$sOfferBgColor2 = $oPageRow->offerBgColor2;
	$iOptOut = $oPageRow->optOut;
	$iSureOptOut = $oPageRow->sureOptOut;
	$sSureOptOutText = $oPageRow->sureOptOutText;		
}
if ($rPageResult) {
	dbFreeResult($rPageResult);
}

/*********************** End getting ot page information *********************/
/****** make comma separated list of page2 offers from the array to use in query  ******/
for ($i = 0; $i < count($_SESSION["aSesPage2Offers"]); $i++) {
	$sPage2Offers .= "'".$_SESSION["aSesPage2Offers"][$i]."', ";
}

$sPage2Offers = substr($sPage2Offers,0,strlen($sPage2Offers)-2);
/****** End making comma separated list of page2 offers ******/
// Check if any offers requires SSL - Start
$bRequireSSL = false;
$sCheckRequireSSLQuery = "SELECT * FROM offers 
				 WHERE offerCode IN (".$sPage2Offers.") 
				 AND isRequireSSL = 'Y'";
$rCheckRequireSSLResult = dbQuery($sCheckRequireSSLQuery);
if (dbNumRows($rCheckRequireSSLResult) > 0) {
	$bRequireSSL = true;
}
// Check if any offers requires SSL - End
// write the javascript functions 
// and page2Validation function to call on submit

$sPage2JavaScript = "
<script language=JavaScript>

function page2Validation() {
  var errMessage = '';";

/*************** Prepare offers' page2 text and javascript validation *************/
if ($sPage2Offers != '') {
$sOffersQuery = "SELECT *
				 FROM   offers
				 WHERE offerCode IN (".$sPage2Offers.")";
$rOffersResult = dbQuery($sOffersQuery);
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	$sOfferCode = $oOffersRow->offerCode;
	
	if ($oOffersRow->isCoolSavings == 'Y') {
		$sOfferPage2Template = "<table border='0' cellpadding='0' cellspacing='0' width='650'>
			<tr><td><img src=\"http://images.popularliving.com/images/offers/$sOfferCode/$oOffersRow->smallImageName\" /></td>
			<td width='10' bgcolor='#EFEFEF'></td><td class=offer11 bgcolor='#EFEFEF'>
			$oOffersRow->shortDescription</td></tr></table>".$oOffersRow->page2Template;
	} else {
		$sOfferPage2Template = $oOffersRow->page2Template;
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
";
		$sPage2JavaScript .= $oOffersRow->page2JavaScript;
		$sPage2JavaScript .= "}";
	}
		
	if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
			$sBgColor = $sOfferBgColor2;
	} else {
		$sBgColor = $sOfferBgColor1;
	}

	//	[PAGE_2_HTML_IMAGE_PATH]
	//	$sPage2HtmlImagePath = "http://www.popularliving.com/images/offers"; - this is set in config file
	$sOfferPage2Template = str_replace("[PAGE_2_HTML_IMAGE_PATH]", $sPage2HtmlImagePath, $sOfferPage2Template);


	if ($bRequireSSL) {
		$sOfferPage2Template = str_replace("http://","https://",$sOfferPage2Template);
	}
	
	$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td>$sOfferPage2Template";

	if ($iOptOut) {
		$sOffersOnPage2 .= "</td></tr><tr bgcolor=$sBgColor><td>I do not want this offer: 
							<input type=checkbox name=aDropOffers[] value='$sOfferCode'";
		if ($iSureOptOut) {
			$sOffersOnPage2 .= " onClick='sureOptOut(this);'";
		}
		$sOffersOnPage2 .= "></td></tr>";
	}
	$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td><hr></td></tr>";	
}

if ($rOffersResult) {
	dbFreeResult($rOffersResult);
}

} // end of if ($sPage2Offers != '') 

/*****************  End preparing offers page2 text and javascript  *****************/
$sPage2JavaScript .= "
		if (errMessage != '') {
    		alert(errMessage);
    		return false;
  		} else {
    		return true;
  		}
	}
	</script>";

/*********  If iSureOptOut is set for the page, write JavaScript function for asking the user if he is sure to opt out the offer **********/
if ($iOptOut && $iSureOptOut) {
	if ($sSureOptOutText == '') {
		$sSureOptOutText = "Are You Sure You Don't Want This Great Offer?\\nClick \"Cancel\" to finish requesting offer.\nClick \"OK\" to confirm you don't want this offer.";		
	}
		$sOffersOnPage2 .= "
							<script language=JavaScript>
							function sureOptOut(chkBox) {
								if (chkBox.checked) {
									if(!confirm(\"". $sSureOptOutText . "\")) {
										chkBox.checked = false;
									}
								}
							}
							</script>";
}

/******************** End sureoptout *********************/

?>

<?php

session_start();
/*********  prepare user form if there is any error or user didn't fill out detail  *********/
if ($_SESSION['sSesFirstNameAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>First Name:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sFirst\" value=\"$sFirst\" size=\"30\"> 
					</td></tr>";
}

if ($_SESSION['sSesLastNameAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>Last Name:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sLast\" value=\"$sLast\" size=\"30\"> 
					</td></tr>";
}

if ($_SESSION['sSesAddressAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>Address:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sAddress\" value=\"$sAddress\" size=\"30\"> 
					</td></tr>";
}
if ($_SESSION['sSesAddress2Asterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>Apt/Suite </b></font>
						<font size=\"-2\" face=\"Helvetica,Arial\">(optional):</font>
					</td>
					<td>
						<input type=\"text\" name=\"sAddress2\" value=\"$sAddress2\" size=\"30\"> 
					</td></tr>";
}


if ($_SESSION['sSesCityAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>City:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sCity\" value=\"$sCity\" size=\"30\"> 
					</td></tr>";
}

if ($_SESSION['sSesStateAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>State:</b></font>
					</td>
					<td>
						<font size=\"-1\" face=\"Arial,Helvetica\"><select name=sState size=\"1\">
						$sStateOptions 
						</select>
					</td></tr>";
}


if ($_SESSION['sSesZipCodeAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>Zip Code:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sZip\" value=\"$sZip\" size=\"30\"> 
					</td></tr>";
}


if ($_SESSION['sSesEmailAsterisk'] != '') {
$sUserForm .= "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
						<font size=\"2\" face=\"Arial,Helvetica\"><b>Email:</b></font>
					</td>
					<td>
						<input type=\"text\" name=\"sEmail\" value=\"$sEmail\" size=\"30\"> 
					</td></tr>";
}


if ($_SESSION['sSesPhoneNoAsterisk'] != '' || $sPhone == '') {
	if ($sPhone != '') {
		$sPhoneArray = explode("-",$sPhone);
		$sPhone_areaCode = $sPhoneArray[0];
		$sPhone_exchange = $sPhoneArray[1];
		$sPhone_number = $sPhoneArray[2];
	}
$sUserFormPhone = "<tr><td width=\"120\" valign=\"top\" bgcolor=\"#F0FFFF\">
					<div STYLE= line-height:7pt;>
					<font size=\"2\" face=\"Arial,Helvetica\"><b>Primary Phone #:</b></font><br>
					<font size=\"-2\" face=\"Helvetica,Arial\">i.e. 847-555-3434</font>
					</div>
					</td>	
					<td>
						<input type=\"text\" name=\"sPhone_areaCode\" value=\"$sPhone_areaCode\" size=\"3\" maxlength=\"3\">
						<input type=\"text\" name=\"sPhone_exchange\" value=\"$sPhone_exchange\" size=\"3\" maxlength=\"3\">
						<input type=\"text\" name=\"sPhone_number\" value=\"$sPhone_number\" size=\"4\" maxlength=\"4\">
						&nbsp; Ext.<input type=\"text\" name=\"sPhone_ext\" value=\"$sPhone_ext\" size=\"3\" maxlength=\"3\">
					</td></tr>";
}

if ($sUserForm != '') {
	$sUserForm .= $sUserFormPhone;
}
if ($sUserForm != '') {
$sUserForm = "<tr><td align=center>
				<table border=\"1\" cellpadding=\"2\" cellspacing=\"1\" width=\"652\" BGCOLOR = \"F0FFFF\" align=\"center\">
					<tr><td>
					<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"650\">".$sUserForm.
						"</table></td></tr></table>
						<BR><hr width='580'><BR></td></tr>";
}

/*******************  End preparing user form  **********************/

/**************** get ot page information which are related to offers list  ***************/
$sPageQuery = "SELECT *
			   FROM   otPages
			   WHERE  id = '".$_SESSION["iSesPageId"]."'";
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
	
while (list($i, $val) = each($_SESSION["aSesPage2Offers"])) {	
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

// start before submit function to put any code into it which needs to be executed before page is sbumitted
$sFuncBeforeSubmit = "<script language=JavaScript>
					  function funcBeforeSubmit() {
					  ";
$sJavaScriptDisplayValues = "<script language=JavaScript>\n
							  var eleType = '';\n 
							  var ele = '';\n
							  var selTemp = '';\n
							  ";
/*************** Prepare offers' page2 text and put if any conditions into funcBeforeSubmit() *************/
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
	
	//	[PAGE_2_HTML_IMAGE_PATH]
	//	$sPage2HtmlImagePath = "http://www.popularliving.com/images/offers"; - this is set in config file
	$sOfferPage2Template = str_replace("[PAGE_2_HTML_IMAGE_PATH]", $sPage2HtmlImagePath, $sOfferPage2Template);
	
	if ($bRequireSSL) {
		$sOfferPage2Template = str_replace("http://","https://",$sOfferPage2Template);
	}

	$sNewPage2JavaScript = $oOffersRow->newPage2JavaScript;
	$sOfferDroppedVar = $sOfferCode."Dropped";

	if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
		$sBgColor = $sOfferBgColor2;
	} else {
		$sBgColor = $sOfferBgColor1;
	}
	
	// put javaScript code into before submit function which needs to be called on submit
	$sFuncBeforeSubmit .= $sNewPage2JavaScript;
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
	
// prepare javascript query to place the previous form values in case of error

// get all the page2 fields of this offer
$sPage2MapQuery = "SELECT *
				   FROM   page2Map
				   WHERE offerCode = '$sOfferCode'
				   ORDER BY storageOrder ";
$rPage2MapResult = dbQuery($sPage2MapQuery);

// to track empty page2Data
$sTestActualFieldNames = "";
$sTestMessage = "";

while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
	$sActualFieldName = $oPage2MapRow->actualFieldName;
	if ($sMessage == '') {
		$_SESSION['page2'][$sActualFieldName] = '';
	}
	
	// check if any javascript function to be called when setting (changing)any values for this field
	$sOnChangeCall = $oPage2MapRow->sopOnChangeCall;

	${$sActualFieldName} = $_SESSION['page2'][$sActualFieldName];
	//echo "<BR>$sActualFieldName - ".$_SESSION['page2'][$sActualFieldName];
	$sJavaScriptDisplayValues .= "if (document.form1.$sActualFieldName) {\n
								ele = document.form1.$sActualFieldName;\n
								  eleType = ele.type;\n
								  
								if (eleType == 'text' || eleType == 'textarea') {\n
										ele.value = '".${$sActualFieldName}."';\n
									} else if (eleType == 'select-one') {
	
										for (var i=0; i < ele.length; i++) {
											selTemp = ele.options[i].value;
	
											if (selTemp == '".${$sActualFieldName}."') {
												ele.options[i].selected = true;
												break;
											}
										}
									} else if ( eleType == 'select-multiple') {
									} else if (eleType == 'checkbox') {
										if (ele.value == '".${$sActualFieldName}."') {
											ele.checked = true;
										} else {
											ele.checked = false;
										}
									} else if (document.forms[0].elements['$sActualFieldName'].length > 0) {
										
										for(i=0; i<ele.length; i++) {
											
											if (ele[i].value == '".${$sActualFieldName}."'){

												ele[i].checked = true;
											} else{
												ele[i].checked = false;
											}
										}
	
									}
	
									}\n";
	if ($sOnChangeCall != '') {
		$sJavaScriptDisplayValues .= "\n".$sOnChangeCall . "\n";
	}
}
} // end of offer while loop

if ($rOffersResult) {
	dbFreeResult($rOffersResult);
}

}// end of if ($sPage2Offers != '') 

/*****************  End preparing offers page2 text and javascript:funcBeforeSubmit()  *****************/
$sFuncBeforeSubmit .= " 
						return true;
					  } 
						</script>";

$sJavaScriptDisplayValues .= "</script>";
$sOffersOnPage2 .= $sJavaScriptDisplayValues. $sFuncBeforeSubmit;
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

if (($_SESSION['sSesPhoneNoAsterisk'] != '' || $sPhone == '') && $sUserForm == '') {
	if ($sPhone != '') {
		$sPhoneArray = explode("-",$sPhone);
		$sPhone_areaCode = $sPhoneArray[0];
		$sPhone_exchange = $sPhoneArray[1];
		$sPhone_number = $sPhoneArray[2];
	}
	$sOffersOnPage2 .= "<tr><td align=center>
				<table border=\"1\" cellpadding=\"2\" cellspacing=\"1\" width=\"652\" BGCOLOR = \"F0FFFF\" align=\"center\">
					<tr><td>
					<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"650\">".$sUserFormPhone.
						"</table></td></tr></table>
						<BR><hr width='580'><BR></td></tr>";
}

/******************** End sureoptout *********************/
?>

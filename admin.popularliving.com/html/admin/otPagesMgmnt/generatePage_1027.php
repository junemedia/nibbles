<?php

/*********

Script to Create the OT Pages

**********/

include("../../includes/paths.php");

$sOtPageSubmitFile = $sGblSiteRoot."/otPageSubmit.php";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

$sPageTitle = "Nibbles OT Pages - Create OT Pages";

$sOtPageQuery = "SELECT *
				 FROM   otPages";

if (! $sRegenerateAll) {
	 $sOtPageQuery .= " WHERE  id = '$iId'";
}

$rOtPageResult = dbQuery($sOtPageQuery);
while ($oOtPageRow = dbFetchObject($rOtPageResult)) {
	$sPageName = $oOtPageRow->pageName;
	$sOtPageTitle = $oOtPageRow->title;
	$sHeaderGraphicFile = $oOtPageRow->headerGraphicFile;
	$iPageLayoutId = $oOtPageRow->pageLayoutId;
	$iPage2LayoutId = $oOtPageRow->page2LayoutId;
	$iUserFormLayoutId = $oOtPageRow->userFormLayoutId;
	$iHiddenForm = $oOtPageRow->hiddenForm;
	
	$iDisplayJoinList = $oOtPageRow->displayList;
	$iListIdToDisplay = $oOtPageRow->listIdToDisplay;
	$sJoinListText = $oOtPageRow->listText;
	$iListPrecheck = $oOtPageRow->listPrecheck;
	$sSubmitText = $oOtPageRow->submitText;
	
	$iDisplayPoweredBy = $oOtPageRow->displayPoweredBy;	
	$sPageBgColor = $oOtPageRow->pageBgColor;
	$sFontColor = $oOtPageRow->fontColor;
	
	$sNoThankYouCode = $oOtPageRow->noThankYouCode;
	$sPage1ExtraText = $oOtPageRow->page1ExtraText;	
	$sPage2ExtraText1 = $oOtPageRow->page2ExtraText1;	
	$sPage2ExtraText2 = $oOtPageRow->page2ExtraText2;	
	
	$iDisplayPage2HeaderImage = $oOtPageRow->displayPage2HeaderImage;
	$sPage2BgColor = $oOtPageRow->page2BgColor;
	$iDisplayShoppingSpreeDisclaimer = $oOtPageRow->displayShoppingSpreeDisclaimer;
	$iShowExitPopup = $oOtPageRow->showExitPopup;


// get the ot page layout and set it as ot page content initial step to build the ot page

$sOtPageContent = '';
$sUserFormContent = '';
$sJoinListContent = '';
$sHiddenFormContent = '';
$sShoppingSpreeDisclaimer = '';


$sLayoutQuery = "SELECT *
				 FROM   pageLayouts
				 WHERE  id = '$iPageLayoutId'";
$rLayoutResult = dbQuery($sLayoutQuery) ;
echo dbError();
while ($oLayoutRow = dbFetchObject($rLayoutResult)) {
	$sOtPageContent = $oLayoutRow->content;
}

// get the user form layout and set it as ot page user form withing the ot page

$sUserFormLayoutQuery = "SELECT *
				 FROM   userFormLayouts
				 WHERE  id = '$iUserFormLayoutId'";
$rUserFormLayoutResult = dbQuery($sUserFormLayoutQuery) ;
echo dbError();
while ($oUserFormLayoutRow = dbFetchObject($rUserFormLayoutResult)) {
	$sUserFormContent = $oUserFormLayoutRow->content;
}

// replace the user Form
//$iHiddenForm = 1;
if ($iHiddenForm) {
	
	// get the hidden form content
	$sHiddenFormQuery = "SELECT *
				   		 FROM   userFormLayouts
				   		 WHERE  layout = 'hiddenForm'";
	$rHiddenFormResult = dbQuery($sHiddenFormQuery) ;

	while ($oHiddenFormRow = dbFetchObject($rHiddenFormResult)) {
		$sHiddenFormContent = $oHiddenFormRow->content;
	}
	
	//$sHiddenFormContent = ereg_replace("\[SALUTATION\]",  "\$s" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[FIRST\]",  "\$f" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[LAST\]",  "\$l" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[EMAIL\]",  "\$e" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[ADDRESS\]",  "\$a1" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[ADDRESS2\]",  "\$a2" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[CITY\]",  "\$c" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[STATE\]",  "\$s" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[ZIP\]",  "\$z" , $sHiddenFormContent);
	$sHiddenFormContent = ereg_replace("\[PHONE\]",  "\$p" , $sHiddenFormContent);	
	
		

	$sUserFormContent = "<?php if (\$sMessage != ''){\n echo \"".addslashes($sUserFormContent)."\";\n}\n
	else {\n
	echo \"".addslashes($sHiddenFormContent)."\";\n
	}
	?>\n";
		
	
	$sOtPageContent = ereg_replace("\[USER_FORM\]", $sUserFormContent, $sOtPageContent);
	
} else {
	$sOtPageContent = ereg_replace("\[USER_FORM\]", $sUserFormContent, $sOtPageContent);
}


//echo $sUserFormLayoutQuery;

// get the join list content to display
$sJoinListContentQuery = "SELECT *
				   FROM   otPageDefinitions
				   WHERE  definition = 'joinListContent'";
$rJoinListContentResult = dbQuery($sJoinListContentQuery) ;

while ($oJoinListContentRow = dbFetchObject($rJoinListContentResult)) {
	$sJoinListContent = $oJoinListContentRow->definedValue;
}
	


// replace the page title in ot page content
$sOtPageContent = ereg_replace("\[PAGE_TITLE\]", $sOtPageTitle, $sOtPageContent);
// replace the style sheet tag
$sOtPageContent = ereg_replace("\[STYLE_SHEET\]", "<LINK rel=\"stylesheet\" href=\"$sGblSiteRoot/pageStyles.css\" type=\"text/css\">", $sOtPageContent);

// replace the page background color in ot page content
if ($sPageBgColor == '') {
	$sPageBgColor = "#FFFFFF";
}
$sOtPageContent = ereg_replace("\[PAGE_BG_COLOR\]", $sPageBgColor, $sOtPageContent);

// replace header graphic in page layout

$sOtPageContent = ereg_replace("\[HEADER_GRAPHIC_FILE\]", "/p/$sPageName/images/$sHeaderGraphicFile", $sOtPageContent);


// replace header graphic in page layout
	//if ($iDisplayPage2HeaderImage) {
	
	$sPage1Header = "<?php  if (\$header == ''){\n 						
						echo \"".addslashes("<img src=\"/p/$sPageName/images/$sHeaderGraphicFile\">")."\";\n}\n
					  else {\n
						
						if (file_exists(\"\$sGblOtPagesPath/$sPageName/headers/\$header.php\")) {\n
						
							include(\"$sGblOtPagesPath/\$sPageName/headers/\$header.php\");\n
						} else {
							echo \"".addslashes("<img src=\"/p/$sPageName/images/$sHeaderGraphicFile\">")."\";\n}\n
						
					  }\n
					?>\n";
	$sOtPageContent = ereg_replace("\[PAGE1_HEADER\]", $sPage1Header, $sOtPageContent);
	
	//} else {
		//$sOtPageContent = ereg_replace("\[PAGE1_HEADER\]", "", $sOtPageContent);
	//}
	

// replace other images in the page
$sOtPageContent = ereg_replace("\[STEP0_GRAPHIC_FILE\]", "/p/$sPageName/images/step0.gif", $sOtPageContent);
$sOtPageContent = ereg_replace("\[STEP1_GRAPHIC_FILE\]", "/p/$sPageName/images/step1.gif", $sOtPageContent);
$sOtPageContent = ereg_replace("\[STEP2_GRAPHIC_FILE\]", "/p/$sPageName/images/step2.gif", $sOtPageContent);
$sOtPageContent = ereg_replace("\[STEP3_GRAPHIC_FILE\]", "/p/$sPageName/images/step3.gif", $sOtPageContent);
$sOtPageContent = ereg_replace("\[ARROW_GRAPHIC_FILE\]", "/p/$sPageName/images/arrow.jpg", $sOtPageContent);


// replace the action attribute in form tag
//$sOtPageContent = ereg_replace("\[OT_PAGE_SUBMIT_FILE\]", $sOtPageSubmitFile, $sOtPageContent);

// replace bdList Text, if displayBdList set to 1
if ($iDisplayJoinList) {
	
	// get the title of listId to display in bdlist text
	$sListQuery = "SELECT title
				   FROM   joinLists
				   WHERE  id = '$iListIdToDisplay'";
	$rListResult = dbQuery($sListQuery)	;
	while ($oListRow = dbFetchObject($rListResult)) {
		$sJoinListTitle = $oListRow->title;
	}
	
	//place the list title in list text
	$sJoinListText = ereg_replace("\[JOIN_LIST_TITLE\]", $sJoinListTitle, $sJoinListText);
	
	// place the list text in list content
	$sJoinListContent = ereg_replace("\[JOIN_LIST_TEXT\]", $sJoinListText, $sJoinListContent);
	
	
} 
// replace the form action

$sOtPageContent = ereg_replace("\[ACTION\]", $sOtPageSubmitFile, $sOtPageContent);


///************* replace user form variables now ********************/

// replace the submit value
	$sOtPageContent = ereg_replace("\[SUBMIT_TEXT\]", $sSubmitText, $sOtPageContent);
	
	if ($iHiddenForm) {
		
		$sOtPageContent = ereg_replace("\[SALUTATION_OPTIONS\]",  addslashes("\$sSalutationOptions") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[FIRST\]",  addslashes("\$sFirst") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[LAST\]",  addslashes("\$sLast") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[EMAIL\]",  addslashes("\$sEmail") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS\]",  addslashes("\$sAddress") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS2\]",  addslashes("\$sAddress2") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[CITY\]",  addslashes("\$sCity") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[STATE_OPTIONS\]",  addslashes("\$sStateOptions") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ZIP\]",  addslashes("\$sZip") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[PHONE\]",  addslashes("\$sPhone") , $sOtPageContent);	
	
	// replace asterisks with php code for its values in case of any error
	
		$sOtPageContent = ereg_replace("\[FIRST_ASTERISK\]",  "\".\$_SESSION[\"sSesFirstNameAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[LAST_ASTERISK\]",  "\".\$_SESSION[\"sSesLastNameAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS_ASTERISK\]",  "\".\$_SESSION[\"sSesAddressAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS2_ASTERISK\]",  "\".\$_SESSION[\"sSesAddress2Asterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[CITY_ASTERISK\]",  "\".\$_SESSION[\"sSesCityAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[STATE_ASTERISK\]",  "\".\$_SESSION[\"sSesStateAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ZIP_ASTERISK\]",  "\".\$_SESSION[\"sSesZipCodeAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[PHONE_ASTERISK\]",  "\".\$_SESSION[\"sSesPhoneNoAsterisk\"].\"" , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[EMAIL_ASTERISK\]",  "\".\$_SESSION[\"sSesEmailAsterisk\"].\"", $sOtPageContent);
	
		
	} else {
	// replace the form element values with php script to place values
		$sOtPageContent = ereg_replace("\[SALUTATION_OPTIONS\]",  addslashes("<?php echo \$sSalutationOptions; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[FIRST\]",  addslashes("<?php echo \$sFirst; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[LAST\]",  addslashes("<?php echo \$sLast; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[EMAIL\]",  addslashes("<?php echo \$sEmail; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS\]",  addslashes("<?php echo \$sAddress; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS2\]",  addslashes("<?php echo \$sAddress2; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[CITY\]",  addslashes("<?php echo \$sCity; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[STATE_OPTIONS\]",  addslashes("<?php echo \$sStateOptions; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ZIP\]",  addslashes("<?php echo \$sZip; ?>") , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[PHONE\]",  addslashes("<?php echo \$sPhone; ?>") , $sOtPageContent);	
	
	// replace asterisks with php code for its values in case of any error
	
		$sOtPageContent = ereg_replace("\[FIRST_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesFirstNameAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[LAST_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesLastNameAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesAddressAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ADDRESS2_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesAddress2Asterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[CITY_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesCityAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[STATE_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesStateAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[ZIP_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesZipCodeAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[PHONE_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesPhoneNoAsterisk\"".addslashes(']; ?>') , $sOtPageContent);
		$sOtPageContent = ereg_replace("\[EMAIL_ASTERISK\]",  addslashes('<?php echo $_SESSION[')."\"sSesEmailAsterisk\"".addslashes(']; ?>' ), $sOtPageContent);
	
	}
	/***************   end of replacing user form variables   ******************/	
	
	
if ($iDisplayJoinList) {
// place the whole list content alongwith userform
//if ($iHiddenForm) {
//	$sOtPageContent = ereg_replace("\[JOIN_LIST_CONTENT\]", addslashes($sJoinListContent), $sOtPageContent);
//} else {
	$sOtPageContent = ereg_replace("\[JOIN_LIST_CONTENT\]", $sJoinListContent, $sOtPageContent);
//}
	
	// place the listId as the value of the Yes radio button
	$sOtPageContent = ereg_replace("\[JOIN_LIST_ID\]", $iListIdToDisplay, $sOtPageContent);
	
} else {
	$sOtPageContent = ereg_replace("\[JOIN_LIST_CONTENT\]", "", $sOtPageContent);
}

if ($sFontColor != '') {	
//	$sOtPageContent = ereg_replace("\[MESSAGE\]", "<font color=$sFontColor>[MESSAGE]</font>", $sOtPageContent);
//	$sOtPageContent = ereg_replace("\[MAIN_ERROR_MESSAGE\]", "<font color=$sFontColor>[MAIN_ERROR_MESSAGE]</font>", $sOtPageContent);
	$sOtPageContent = ereg_replace("\[START_FONT_COLOR\]", "<font color=$sFontColor>", $sOtPageContent);
	$sOtPageContent = ereg_replace("\[END_FONT_COLOR\]", "</font>", $sOtPageContent);
	
} else {
		$sOtPageContent = ereg_replace("\[START_FONT_COLOR\]", "", $sOtPageContent);
		$sOtPageContent = ereg_replace("\[END_FONT_COLOR\]", "", $sOtPageContent);
}


while (strstr($sOtPageContent, "[customQstring")) {

	// place custom variable replacement code
	$iPosStart = strpos($sOtPageContent, "[customQstring");
	$iPosEnd =  strpos($sOtPageContent, "]",$iPosStart);
	$iLength = $iPosEnd - $iPosStart;
	$sVarString = substr($sOtPageContent, $iPosStart, $iLength + 1);	
	
	$sTempNewString = substr($sVarString, 1, strlen($sVarString)-2);
	$sNewVarString = addslashes("<?php echo \$$sTempNewString;?>");
	
	$sOtPageContent = str_replace($sVarString, $sNewVarString, $sOtPageContent);
					
}

// place asp file output if there is any
if (strstr($sOtPageContent, "[ASP_FILE]")) {
	
	$iPosStart = strpos($sOtPageContent, "[ASP_FILE]");
	$iPosEnd = strpos($sOtPageContent, "[/ASP_FILE]", $iPosStart);
	$iLength = $iPosEnd - $iPosStart;
	$sVarString = substr($sOtPageContent, $iPosStart, $iLength+11);
	$sAspFile = ereg_replace("\[ASP_FILE\]", "", $sVarString);
	$sAspFile = ereg_replace("\[/ASP_FILE\]", "", $sAspFile);
	$sAspFile = ereg_replace("\[OUTBOUND_QUERY_STRING\]","\$sOutboundQueryString",$sAspFile);
		
		
	$sNewVarString = "<?php
					if (\$PMID !='') {
						echo \"<IFRAME src=\\\"".$sAspFile."\\\" frameborder=0 scrolling=0 width=625 height=490>
						</iframe>\";
					} else {
						echo \"<img src = \\\"/p/$sPageName/images/".$sHeaderGraphicFile."\\\" border=0>\";
					}\n?>";
	
	
	$sOtPageContent = str_replace($sVarString, $sNewVarString, $sOtPageContent);
}


// replaced powered by if to display it
if ($iDisplayPoweredBy) {
	$sOtPageContent = ereg_replace("\[POWERED_BY\]", "<img src='$sGblImageUrl/poweredBy.gif'>", $sOtPageContent);
} else {
	$sOtPageContent = ereg_replace("\[POWERED_BY\]", "", $sOtPageContent);
}


//$sOffersList = "\n\r<?php include(\"\$sGblIncludePath/otPageOffers.php\"); >\n\r";

$sOtPageContent = ereg_replace("\[TOP_OFFERS_LIST\]", addslashes("<?php echo \$sPageTopOffersList; ?>"), $sOtPageContent);


$sOtPageContent = ereg_replace("\[OFFERS_LIST\]", addslashes("<?php echo \$sPageOffersList; ?>"), $sOtPageContent);


$sOtPageContent = ereg_replace("\[NO_THANK_YOU_CODE\]", $sNoThankYouCode, $sOtPageContent);


$sOtPageContent = ereg_replace("\[PAGE1_EXTRA_TEXT\]", $sPage1ExtraText, $sOtPageContent);


// get the join list content to display
if ($iDisplayShoppingSpreeDisclaimer) {
$sShoppingSpreeQuery = "SELECT *
				   FROM   otPageDefinitions
				   WHERE  definition = 'shoppingSpreeDisclaimer'";
$rShoppingSpreeResult = dbQuery($sShoppingSpreeQuery) ;
echo dbError();
while ($oShoppingSpreeRow = dbFetchObject($rShoppingSpreeResult)) {
	$sShoppingSpreeDisclaimer = $oShoppingSpreeRow->definedValue;	
}
}
$sOtPageContent = ereg_replace("\[SHOPPING_SPREE_DISCLAIMER\]", $sShoppingSpreeDisclaimer, $sOtPageContent);


// replace prepop codes and inboundquery sting if have to pass with any links
$sOtPageContent = ereg_replace("\[PREPOP_CODES\]", addslashes("<?php echo \$sPrepopcodes; ?>"), $sOtPageContent);
$sOtPageContent = ereg_replace("\[OUTBOUND_QUERY_STRING\]", addslashes("<?php echo \$sOutboundQueryString; ?>"), $sOtPageContent);


// replace main error message with its value through php script
$sOtPageContent = ereg_replace("\[MAIN_ERROR_MESSAGE\]", addslashes("<?php echo \$sMainErrorMessage; ?>"), $sOtPageContent);


// replace message with any message through php script
$sOtPageContent = ereg_replace("\[MESSAGE\]", addslashes("<?php echo \$sMessage; ?>"), $sOtPageContent);

// check if join list should be prechecked
if ($iDisplayJoinList) {
if ($iListPrecheck) {
	$sOtPageContent .= "\n\r<script language=Javascript>
					for(var i=0; i< document.forms.length;i++) {
						var frmName = document.forms[i].name;
						for(j=0;j<document.forms[i].elements.length;j++) {
							var eleName = document.forms[i].elements[j].id;
							if ( eleName == 'listIdY') {
								obj1 = document.forms[i].listIdY;
								obj1.checked = true;
							}
						}
					}
						
						</script>";
	
} else {
		$sOtPageContent .= "\n\r<script language=Javascript>
		for(var i=0; i< document.forms.length;i++) {
						var frmName = document.forms[i].name;
						for(j=0;j<document.forms[i].elements.length;j++) {
							var eleName = document.forms[i].elements[j].id;
							if ( eleName == 'listIdN') {
								obj1 = document.forms[i].listIdN;
								obj1.checked = true;
							}
						}
					}				
						</script>";
}
}

// place code if exit popup is to be displayed
if ($iShowExitPopup) {
	
	$sExitPopupScript = "\r\n<script LANGUAGE=\"javascript\">var f=1;</script>\r\n
<script src=\"http://www.popularliving.com/libs/jsPopFuncs.js\"></script>\r\n</head>";
	$sOtPageContent = eregi_replace("</head>", $sExitPopupScript, $sOtPageContent);
	$sExitPopupText = "<body onunload=\"showPopup(f)\" ";
	$sOtPageContent = eregi_replace("<body", $sExitPopupText, $sOtPageContent);
	
}

// Display pixel on the page according to pixel display process
		
	/*
} else {
	$sMessage = "Can't generate the page. No offers in this page...";
}
*/

// store the generated page

$bOtPagesDir = chDir($sGblOtPagesPath);

if ($bOtPagesDir) {
	
	unlink($sPageName.".php");
	$rOtPageFile = fopen($sPageName.".php", "w");
	
	
	if ($rOtPageFile) {
		fwrite($rOtPageFile, "<?php include('../includes/paths.php');?>\n");
		// get the name of this page (the file name itself is the page name)
		fwrite($rOtPageFile,"<?php session_start();\n
								   \$aPathInfo = explode(\"/\",\$PHP_SELF);\n
								   \$sFileName = \$aPathInfo[count(\$aPathInfo)-1];\n
								   \$sPageName = substr(\$sFileName,0,strlen(\$sFileName)-4);\n
									\$iPageId = getPageId(\$sPageName);\n
									if(!(isset(\$_SESSION[\"iSesPageId\"]))) {
										\$_SESSION[\"iSesPageId\"] = '';\n
									}\n
									\$_SESSION[\"iSesPageId\"] = \$iPageId;\n");
		
		
		fwrite($rOtPageFile, " \n include(\"\$sGblIncludePath/otPageInclude.php\");\n");
		fwrite($rOtPageFile, " \n include(\"\$sGblIncludePath/otPageOffers.php\");?>\n");
		fwrite($rOtPageFile, $sOtPageContent);
		fwrite($rOtPageFile, " \n <?php \n include(\"\$sGblIncludePath/otPage1BottomInclude.php\");?>\n");
		fclose($rOtPageFile);
	}
	//chmod($sPageName.".php","0777");
}

//echo $sOtPageContent;


// generate page2

$sOtPage2Content = '';
$sPage2Template = '';

// get the page2 template

$sPage2TemplateQuery = "SELECT *
					   FROM   page2Layouts
					   WHERE  id = '$iPage2LayoutId'";
$rPage2TemplateResult = dbQuery($sPage2TemplateQuery);
while ($oPage2TemplateRow = dbFetchObject($rPage2TemplateResult)) {
	
	$sPage2Template = $oPage2TemplateRow->content;
	
}

if ($rPage2TemplateResult) {
	dbFreeResult($rPage2TemplateResult);
}

$sPage2Template = ereg_replace("\[ACTION\]","$sGblSiteRoot/otPage2Submit.php", $sPage2Template);

// replace the page title in ot page content
	$sPage2Template = ereg_replace("\[PAGE_TITLE\]", $sOtPageTitle, $sPage2Template);
	
	// replace the page bg color in ot page content
	$sPage2Template = ereg_replace("\[PAGE_BG_COLOR\]", $sPage2BgColor, $sPage2Template);
	
	
	// replace the style sheet tag
	$sPage2Template = ereg_replace("\[STYLE_SHEET\]", "<LINK rel=\"stylesheet\" href=\"$sGblSiteRoot/pageStyles.css\" type=\"text/css\">", $sPage2Template);
	
	// replace the message
	$sPage2Template = ereg_replace("\[MESSAGE\]", addslashes("<?php echo \$sMessage;?>"), $sPage2Template);	
	
	
// place the list of offers in page2 template
$sPage2Template = ereg_replace("\[OFFERS_LIST\]", addslashes("<?php echo \$sOffersOnPage2;?>"), $sPage2Template);


// place the javascript in page2 template
$sPage2Template = ereg_replace("\[PAGE2_JAVA_SCRIPT\]", addslashes("<?php echo \$sPage2JavaScript;?>"), $sPage2Template);


// replace page2 extra text 1
$sPage2Template = ereg_replace("\[PAGE2_EXTRA_TEXT1\]", $sPage2ExtraText1, $sPage2Template);

// replace page2 extra text 2
$sPage2Template = ereg_replace("\[PAGE2_EXTRA_TEXT2\]", $sPage2ExtraText2, $sPage2Template);


	if ($sFontColor != '') {
		$sPage2Template = ereg_replace("\[START_FONT_COLOR\]", "<font color='$sFontColor'>", $sPage2Template);
		$sPage2Template = ereg_replace("\[END_FONT_COLOR\]", "</font>", $sPage2Template);
	} else {
		$sPage2Template = ereg_replace("\[START_FONT_COLOR\]", "", $sPage2Template);		
		$sPage2Template = ereg_replace("\[END_FONT_COLOR\]", "", $sPage2Template);
	}
	

// replace header graphic in page layout
	if ($iDisplayPage2HeaderImage) {
		$sPage2Template = ereg_replace("\[HEADER_GRAPHIC_IMAGE_TAG\]", "<img src='/p/$sPageName/images/$sHeaderGraphicFile'>", $sPage2Template);
	} else {
		$sPage2Template = ereg_replace("\[HEADER_GRAPHIC_IMAGE_TAG\]", "", $sPage2Template);
	}

// place code if exit popup is to be displayed
if ($iShowExitPopup) {
	
	$sExitPopupScript = "\r\n<script LANGUAGE=\"javascript\">var f=1;</script>\r\n
<script src=\"http://www.popularliving.com/libs/jsPopFuncs.js\"></script>\r\n</head>";
	$sPage2Template = eregi_replace("</head>", $sExitPopupScript, $sPage2Template);
	$sExitPopupText = "<body onunload=\"showPopup(f)\" ";
	$sPage2Template = eregi_replace("<body", $sExitPopupText, $sPage2Template);
	
	// findout submit button of page2 layout and place OnClick="f=0" within the tag
	$sPage2Template = eregi_replace("type=submit", "type=submit onClick=\"f=0\"", $sPage2Template);
	$sPage2Template = eregi_replace("type=\"submit\"", "type=submit onClick=\"f=0\"", $sPage2Template);
	$sPage2Template = eregi_replace("type='submit'", "type=submit onClick=\"f=0\"", $sPage2Template);
	
}

	$sOtPage2Content = "<?php\n
include(\"../includes/paths.php\");\n
include_once(\"\$sGblLibsPath/validationFunctions.php\");\n

// start session before including otPageInclude.php\n
session_start();\n

include(\"\$sGblIncludePath/otPageInclude.php\");\n
include(\"\$sGblIncludePath/otPage2Offers.php\");\n

if (!isset(\$_SESSION[\"iSesPageId\"])) {\n
	echo \"Session expired\";\n
} else {\n
	



// place the javaScript of offers in page2 template which is prepared in otPage2Offers.php\n
\$sPage2JavaScript = \"<script language=JavaScript>\".\n
					\$_SESSION['sSesJavaScriptVars'] .\n
					 \"</script>\".\$sPage2JavaScript;\n	
?>\n". $sPage2Template."\n
 <?php
}

?>";
	
	
if ($bOtPagesDir) {
	
	unlink($sPageName."_2.php");
	$rOtPage2File = fopen($sPageName."_2.php", "w");
	
	
	if ($rOtPage2File) {
		
		fwrite($rOtPage2File, $sOtPage2Content);
		fclose($rOtPage2File);
	}
	//chmod($sPageName.".php","0777");
}


	//echo "dfdf".$sOtPage2Content;
	
}

echo "<script language=JavaScript>
	self.close();
</script>";

} else {
	echo "You are not authorized to access this page...";
}
?>

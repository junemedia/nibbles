<?php

/*********

Script to Clone an otPage

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles otPages - Clone otPage";
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sClone ) {
	if ($sNewOtPageName != '') {
		$sCheckQuery = "SELECT * FROM otPages WHERE pageName = '$sNewOtPageName'";
		$rCheckResult = dbQuery($sCheckQuery);
		
		if (mysql_num_rows($rCheckResult) == 0) {
			$sOtPageQuery = "INSERT INTO otPages(pageName,title,dateTimeAdded,notes,headerGraphicFile,e1HeaderGraphicFile,pageLayoutId,page2LayoutId,
				newPage2LayoutId,userFormLayoutId,newUserFormLayoutId,offerListLayoutId,e1PageLayoutId,hiddenForm,minNoOfOffers,
				maxNoOfOffers,displayYesNo,requireYesNo,offerImageSize,offerFontSize,displayOfferHeadline,displayList,
				listText,listIdToDisplay,listPrecheck,submitText,redirectTo,checkRedirectTo,redirectPopOption,
				redirectPopUrl,checkRedirectPopUrl,redirectToNotOfferTaken,checkRedirectToNotOfferTaken,
				redirectNotOfferTakenPopOption,redirectNotOfferTakenPopUrl,checkRedirectNotOfferTakenPopUrl,
				hasCustomRedirectProc,passOnPrepopCodes,passOnInboundQueryString,inboundVarMap,outboundVarMap,
				passOnPhpsessid,enableGoTo,isGoToPopUp,offersByPageMap,offersByCatMap,autoEmail,defaultAutoRespId,
				isCobrand,isCobrandTemplate,displayPoweredBy,offerNotRequired,optOut,sureOptOut,sureOptOutText,
				noThankYouCode,pageBgColor,borderColor,fontColor,offerBgColor1,offerBgColor2,page2BgColor,
				displayPage2HeaderImage,page1ExtraText,page2ExtraText1,page2ExtraText2,displayShoppingSpreeDisclaimer,
				displayShoppingSpreeDisclaimerWithPPLink,showExitPopup,srcForExitPopup) 
				SELECT '$sNewOtPageName',title, NOW(), notes,headerGraphicFile,e1HeaderGraphicFile,pageLayoutId,page2LayoutId,
				newPage2LayoutId,userFormLayoutId,newUserFormLayoutId,offerListLayoutId,e1PageLayoutId,hiddenForm,minNoOfOffers,
				maxNoOfOffers,displayYesNo,requireYesNo,offerImageSize,offerFontSize,displayOfferHeadline,displayList,
				listText,listIdToDisplay,listPrecheck,submitText,redirectTo,checkRedirectTo,redirectPopOption,
				redirectPopUrl,checkRedirectPopUrl,redirectToNotOfferTaken,checkRedirectToNotOfferTaken,
				redirectNotOfferTakenPopOption,redirectNotOfferTakenPopUrl,checkRedirectNotOfferTakenPopUrl,
				hasCustomRedirectProc,passOnPrepopCodes,passOnInboundQueryString,inboundVarMap,outboundVarMap,
				passOnPhpsessid,enableGoTo,isGoToPopUp,offersByPageMap,offersByCatMap,autoEmail,defaultAutoRespId,
				isCobrand,isCobrandTemplate,displayPoweredBy,offerNotRequired,optOut,sureOptOut,sureOptOutText,
				noThankYouCode,pageBgColor,borderColor,fontColor,offerBgColor1,offerBgColor2,page2BgColor,
				displayPage2HeaderImage,page1ExtraText,page2ExtraText1,page2ExtraText2,displayShoppingSpreeDisclaimer,
				displayShoppingSpreeDisclaimerWithPPLink,showExitPopup,srcForExitPopup from otPages where pageName = '$sPageName'";

			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Clone OT page: $sPageName\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rOtPageResult = dbQuery($sOtPageQuery);
			echo dbError();
			
			if ($rOtPageResult) {
				$sCheck2Query = "SELECT id, headerGraphicFile FROM otPages WHERE pageName = '$sNewOtPageName'";
				$rCheck2Result = dbQuery($sCheck2Query);
				echo dbError();
				while ($oTempRow = dbFetchObject($rCheck2Result)) {
					$sNewId = $oTempRow->id;
					$sOldFileName = $oTempRow->headerGraphicFile;
					$sFileExt = strstr($oTempRow->headerGraphicFile, '.');
					$sNewFileName = 'header_'.$sNewId.$sFileExt;
				}

				$sUpdateQuery = "UPDATE otPages
								SET headerGraphicFile = '$sNewFileName'
								WHERE id ='$sNewId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
				
				
				$sOldIdQuery = "SELECT id FROM otPages WHERE pageName = '$sPageName'";
				$rOldIdResult = dbQuery($sOldIdQuery);
				echo dbError();
				while ($oOldRow = dbFetchObject($rOldIdResult)) {
					$sOldId = $oOldRow->id;
				}

				$sInsertQuery = "INSERT INTO pageMap (pageId, offerCode, sortOrder) SELECT '$sNewId', offerCode, sortOrder 
								FROM pageMap WHERE pageId = '$sOldId'";
				$rInsertResult = dbQuery($sInsertQuery);
				echo dbError();

				
				if (!(is_dir("$sGblPageImagesPath/$sNewOtPageName")) ) {				
					mkdir("$sGblPageImagesPath/$sNewOtPageName",0777);
					chmod("$sGblPageImagesPath/$sNewOtPageName",0777);
				}
				
				if (!(is_dir("$sGblPageImagesPath/$sNewOtPageName/headers")) ) {				
					mkdir("$sGblPageImagesPath/$sNewOtPageName/headers",0777);
					chmod("$sGblPageImagesPath/$sNewOtPageName/headers",0777);
				}
				
				if (!(is_dir("$sGblPageImagesPath/$sNewOtPageName/images")) ) {				
					mkdir("$sGblPageImagesPath/$sNewOtPageName/images",0777);
					chmod("$sGblPageImagesPath/$sNewOtPageName/images",0777);
				}
				
				
				$rImageDir = opendir("$sGblOtPagesPath/$sPageName/images/");
				if ($rImageDir) {
					while (($sFile = readdir($rImageDir)) != false) {
						if( $sFile != '.' && $sFile != '..' ) {
							if (!copy("$sGblOtPagesPath/$sPageName/images/$sFile", "$sGblOtPagesPath/$sNewOtPageName/images/$sFile")) {
								$sMessage = "<br>otPage Cloned Successfully, but failed to copy image files.<br>";
							} else {
								if ($sFile == $sOldFileName) {
									$sFile = rename("$sGblOtPagesPath/$sNewOtPageName/images/".$sOldFileName,"$sGblOtPagesPath/$sNewOtPageName/images/".$sNewFileName);
								}
							}
						}
					}
				}

				$rHeaderDir = opendir("$sGblOtPagesPath/$sPageName/headers/");
				if ($rHeaderDir) {
					while (($sHeaderFile = readdir($rHeaderDir)) != false) {
						if( $sHeaderFile != '.' && $sHeaderFile != '..' ) {
							if (!copy("$sGblOtPagesPath/$sPageName/headers/$sHeaderFile", "$sGblOtPagesPath/$sNewOtPageName/headers/$sHeaderFile")) {
								$sMessage = "<br>otPage Cloned Successfully, but failed to copy header files.<br>";
							}
						}
					}
				}
				$sMessage = "Successfully Cloned: $sNewOtPageName";
			} else {
				$sMessage = "Error in clone process<BR>".dbError();
			}
		} else {
			$sMessage = "Page Name Already Exists OR Similar Page Name Already Exists: $sNewOtPageName";
		}
	} else {
		$sMessage = "Please enter new otPageName.";
	}
}
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sPageName value='$sPageName'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td>Clone otPage</td><td><B><?php echo $sPageName;?></b>
	</td>
</tr>
<tr>
	<td>New otPage Name</td>
	<td><input type=text name=sNewOtPageName value='<?php echo $sNewOtPageName;?>'>
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
<?php

/*********

Script to Display Add/Edit Co-Brand Details

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Co-Brand Details - Add/Edit Co-Brand Details";
if (hasAccessRight($iMenuId) || isAdmin()) {

	if (($sSaveClose || $sSaveNew)) {
		// if new record added
		
		if (!($iId)) {
			$iUnixTimeStamp = time();
			
			$sTopText = addslashes($sTopText);
			$sAutoEmailSub = addslashes($sAutoEmailSub);
			$sAutoEmailText = addslashes($sAutoEmailText);
			$sShortDescription = addslashes($sShortDescription);
			
			
			$sAddQuery = "INSERT INTO coBrandDetails(id, pageId, topText, autoEmail, autoEmailFormat, 
							autoEmailFrom, autoEmailSub, autoEmailText, autoEmailReplyTo, partnerId, repDesignated, shortDescription, pageBgColor, borderColor, fontColor, offerBgColor1, offerBgColor2, page2BgColor)
						 VALUES('$iUnixTimeStamp', '$iPageId', \"$sTopText\", \"$iAutoEmail\", \"$sAutoEmailFormat\", 
							\"$sAutoEmailFrom\", \"$sAutoEmailSub\", \"$sAutoEmailText\", \"$sAutoEmailReplyTo\", \"$iPartnerId\", \"$iRepDesignated\", \"$sShortDescription\", 
							'$sPageBgColor', '$sBorderColor', '$sFontColor', '$sOfferBgColor1', '$sOfferBgColor2', '$sPage2BgColor')";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sAddQuery);
			if (!($rResult)) {
				$sMessage = dbError();
				$bKeepValues = true;
			} else {
				$iId = $iUnixTimeStamp;
			}
			
		} else {

			$sTopText = addslashes($sTopText);
			$sAutoEmailSub = addslashes($sAutoEmailSub);
			$sAutoEmailText = addslashes($sAutoEmailText);
			$sShortDescription = addslashes($sShortDescription);

			
			$sEditQuery = "UPDATE coBrandDetails
					  SET 	  pageId = '$iPageId',					  		  
					  		  topText = \"$sTopText\",
					  		  autoEmail = \"$iAutoEmail\",
					  		  autoEmailFormat = \"$sAutoEmailFormat\",
					  		  autoEmailFrom = \"$sAutoEmailFrom\",
					  		  autoEmailSub = \"$sAutoEmailSub\",
					  		  autoEmailText = \"$sAutoEmailText\",
					  		  autoEmailReplyTo = \"$sAutoEmailReplyTo\",
					  		  partnerId = \"$iPartnerId\",
					  		  repDesignated = \"$iRepDesignated\",
					  		  shortDescription = \"$sShortDescription\",
					  		  pageBgColor = '$sPageBgColor',
					  		  borderColor = '$sBorderColor',
					  		  fontColor = '$sFontColor',
					  		  offerBgColor1 = '$sOfferBgColor1',
					  		  offerBgColor2 = '$sOfferBgColor2',
					  		  page2BgColor = '$sPage2BgColor'
					  WHERE id = '$iId'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sEditQuery);

			if (!($rResult)) {
				$sMessage = dbError();
				$bKeepValues = true;
			}

		}

		
		// get page name
		$sPageQuery = "SELECT *
			   		   FROM   otPages
			   		   WHERE  id = '$iPageId'";
		$rPageResult = dbQuery($sPageQuery);
		while ($oPageRow = dbFetchObject($rPageResult)) {
			$sPageName = $oPageRow->pageName;
		}

		//$sHeaderImagesPath = "$sGblPageImagesPath/$sPageName/headers";
		
		if ($_FILES['headerFile']['name'] && $_FILES['headerFile']['name']!="none") {
		
		$sUploadedFileName = $_FILES['headerFile']['tmp_name'];
		
		//Get Extention
		$aImageFileNameArray = explode(".",$_FILES['headerFile']['name']);
		$i = count($aImageFileNameArray) - 1;
		$sImageFileExt = strtolower($aImageFileNameArray[$i]);
		if ($sImageFileExt == 'gif' || $sImageFileExt == 'jpg' || $sImageFileExt == 'jpeg' || $sImageFileExt == 'png') {
		
		$sFileName = $_FILES['headerFile']['name'];
		$sNewFileName  = "$sGblPageImagesPath/$sPageName/headers/$sFileName";
		//echo "DFdf".$sNewFileName;
		move_uploaded_file( $sUploadedFileName, $sNewFileName);
		
		chmod("$sNewFileName",0777);
		
		// set header name as the file name
		$sUpdateQuery = "UPDATE coBrandDetails
						 SET    cbHeader = \"$sFileName\"
						 WHERE  id = '$iId'";

		$rUpdateResult = dbQuery($sUpdateQuery);
		if (!($rUpdateResult)) {
			echo dbError();
			$bKeepValues = true;
		}
		} else {
			$sMessage = "Header file must be of type .gif, .jpeg or .png...";
			$bKeepValues = true;
		}
	}
	
		$sPageReloadUrl = "index.php?iMenuId=$iMenuId&iId=$iId&iRecPerPage=$iRecPerPage&iPage=$iPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&sShowUrl=WWW";
	
		if ($sSaveClose) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
			window.opener.location.href='".$sPageReloadUrl."';
			self.close();
			</script>";			
				// exit from this script
				exit();
			}
		} else if ($sSaveNew) {
			if ($bKeepValues != true) {
				$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.href='".$sPageReloadUrl."';
							</script>";	

				$sCbHeader = '';
				$iPageId = '';
				$sTopText = '';
				$iAutoEmail = '';
				$sAutoEmailFormat = '';
				$sAutoEmailFrom = '';
				$sAutoEmailSub = '';
				$sAutoEmailText = '';
				$sAutoEmailReplyTo = '';
				$iPartnerId = '';
				$iRepDesignated = '';
				$sShortDescription = '';
				$sPageBgColor = '';
				$sBorderColor = '';
				$sFontColor = '';
				$sOfferBgColor1 = '';
				$sOfferBgColor2 = '';
				$sPage2BgColor = '';
			}
		}
	}


	if ($sDeleteFile) {
		// get page name
		$sPageQuery = "SELECT otPages.pageName
			   		   FROM   coBrandDetails, otPages
			   		   WHERE  coBrandDetails.pageId = otPages.id
			   		   AND	  coBrandDetails.id = '$iId'";
		$rPageResult = dbQuery($sPageQuery);
		while ($oPageRow = dbFetchObject($rPageResult)) {
			$sPageName = $oPageRow->pageName;
		}
		
		unlink("$sGblPageImagesPath/$sPageName/headers/$sDeleteFile");
		$sUpdateQuery = "UPDATE coBrandDetails
						 SET    cbHeader = \"$sFileName\"
						 WHERE  id = '$iId'";
		$rUpdateResult = dbQuery($sUpdateQuery);
		if (!($rUpdateResult)) {
			echo dbError();
		}
	}

	if ($iId) {

		// If Clicked to edit, get the data to display in fields

		$sSelectQuery = "SELECT coBrandDetails.*, otPages.pageName
					 FROM   coBrandDetails, otPages
				     WHERE  coBrandDetails.pageId = otPages.id
				     AND	coBrandDetails.id = '$iId'";
		$rSelectResult = dbQuery($sSelectQuery);
		echo dbError();
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$iPageId = $oSelectRow->pageId;
			$sPageName = $oSelectRow->pageName;
			$sCbHeader = $oSelectRow->cbHeader;
			$sTopText = ascii_encode($oSelectRow->topText);
			$iAutoEmail = $oSelectRow->autoEmail;
			$sAutoEmailFormat = $oSelectRow->autoEmailFormat;
			$sAutoEmailFrom = $oSelectRow->autoEmailFrom;
			$sAutoEmailSub = ascii_encode($oSelectRow->autoEmailSub);
			$sAutoEmailText = ascii_encode($oSelectRow->autoEmailText);
			$sAutoEmailReplyTo = $oSelectRow->autoEmailReplyTo;
			$iPartnerId = $oSelectRow->partnerId;
			$iRepDesignated = $oSelectRow->repDesignated;
			$sShortDescription = $oSelectRow->shortDescription;
			
			$sPageBgColor = $oSelectRow->pageBgColor;
			$sBorderColor = $oSelectRow->borderColor;
			$sFontColor = $oSelectRow->fontColor;
			$sOfferBgColor1 = $oSelectRow->offerBgColor1;
			$sOfferBgColor2 = $oSelectRow->offerBgColor2;
			$sPage2BgColor = $oSelectRow->page2BgColor;


	// get list of page2 images, if offer is selected to edit
	if ($sCbHeader!= '' && file_exists("$sGblOtPagesPath/$sPageName/headers/$sCbHeader")) {
		$sDisplayHeaderFile =  "<img src=\"$sGblOtPagesUrl/$sPageName/headers/$sCbHeader\">
			&nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&iId=$iId&sDeleteFile=$sCbHeader'>Delete Header File</a><BR>";
	}


		}
	} else {

		$sTopText = ascii_encode(stripslashes($sTopText));
		// If add button is clicked, display another two buttons
		$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}


	$sPageQuery = "SELECT *
			   FROM   otPages
			   WHERE  isCobrandTemplate = '1'
			   ORDER BY pageName";
	$rPageResult = dbQuery($sPageQuery);
	while ($oPageRow = dbFetchObject($rPageResult)) {
		if ($oPageRow->id == $iPageId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}

		$sPageOptions .= "<option value='".$oPageRow->id."' $sSelected>$oPageRow->pageName";
	}

	$sPartnersQuery = "SELECT id, companyName, code
				FROM partnerCompanies
				ORDER BY companyName";
	$rPartnersResult = dbQuery($sPartnersQuery);
	while( $oPartnerRow = dbFetchObject($rPartnersResult)) {
		if ($oPartnerRow->id == $iPartnerId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		
		$sPartnerOptions .= "<option value='".$oPartnerRow->id."' $sSelected>$oPartnerRow->companyName";
	}
	
	$sRepsQuery = "SELECT id, userName
				FROM nbUsers
				ORDER BY userName";
	$rRepsResult = dbQuery($sRepsQuery);
	while( $oRepRow = dbFetchObject($rRepsResult)) {
		if ($oRepRow->id == $iRepDesignated) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		
		$sRepDesignatedOptions .= "<option value='".$oRepRow->id."' $sSelected>$oRepRow->userName";
	}

	$sAutoEmailFormatHtmlChecked = '';
	$sAutoEmailFormatTextChecked = '';
	if ($sAutoEmailFormat == 'html') {
		$sAutoEmailFormatHtmlChecked = "checked";
	} else {
		$sAutoEmailFormatTextChecked = "checked";
	}
	
	if ($iAutoEmail) {
		$sAutoEmailChecked = "checked";
	}

	if ($sBorderColor == '') {
		$sBorderColor = "#3A7DE1";
	}
	
	if ($sPageBgColor == '') {
		$sPageBgColor = "#FFFFFF";
	}
	
	if ($sPage2BgColor == '') {
		$sPage2BgColor = "#FFFFFF";
	}
	
	if ($sOfferBgColor1 == '') {
		$sOfferBgColor1 = "#FFFFFF";
	}
	
	if ($sOfferBgColor2 == '') {
		$sOfferBgColor2 = "#E7EFFF";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=iRecPerPage value='$iRecPerPage'>
			<input type=hidden name=iPage value='$iPage'>
			<input type=hidden name=sFilter value='$sFilter'>
			<input type=hidden name=iExactMatch value='$sExactMatch'>
			<input type=hidden name=sExclude value='$sExclude'>
			<input type=hidden name=sSearchIn value='$sSearchIn'>";

	include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td colspan=4><b>Notes:</b><BR>Uploaded image will be displayed here immediately. But will appear on the OT page only after it's copied to image server.
	<BR>Page auto responder will not be sent if auto responder is not enabled.
	<BR>Page auto responder will only be sent if user completes at least one offer.
	</td>
</tr>
		<tr><td nowrap>Co-Brand Template</td>
			<td><select name=iPageId>
			<?php echo $sPageOptions;?>
			</select></td></tr>
		<tr><td nowrap>Partner Company</td>
			<td><select name=iPartnerId>
			<?php echo $sPartnerOptions;?>
			</select></td></tr>
		<tr><td nowrap>Rep Designated</td>
			<td><select name=iRepDesignated>
			<?php echo $sRepDesignatedOptions;?>
			</select></td></tr>
			<?php echo $sHeaderField;?>
			<tr><td>New Header File</td><td><input type=file name='headerFile'></td></tr>
			<tr><td>Current Header File</td><td><?php echo $sDisplayHeaderFile;?></td></tr>
		<tr><TD>Short Description</td><td><input name=sShortDescription value="<?php echo $sShortDescription;?>" size='40' maxlength='100'></td></tr>
		<tr><TD>Top Text<br><br><a href="JavaScript:void(window.open('http://web0.popularliving.com/p/editorhtml/index.html','new_window','height=520,width=600'));">HTML Editor</a></td><td><textarea name=sTopText rows=10 cols=60><?php echo $sTopText;?></textarea></td></tr>
		<tr><td>Enable Page Auto-Responder</td>
			<td><input type=checkbox name=iAutoEmail value='1' <?php echo $sAutoEmailChecked;?>></td>
		</tr>
		<tr><TD>EmailFormat</td>
		<td><input type=radio name=sAutoEmailFormat value='text' <?php echo $sAutoEmailFormatTextChecked;?>> Text 
		<input type=radio name=sAutoEmailFormat value='html' <?php echo $sAutoEmailFormatHtmlChecked;?>> Html</td></tr>		
		<tr><td>Email From Address</td>
			<td><input type=text name=sAutoEmailFrom value='<?php echo $sAutoEmailFrom;?>' size=40></td>
		</tr>
		<tr><td>Email Subject</td>
			<td><input type=text name=sAutoEmailSub value='<?php echo $sAutoEmailSub;?>' size=40></td>
		</tr>
		<tr><td valign=top>Email Text</td>
			<td><textarea name=sAutoEmailText rows=20 cols=65><?php echo $sAutoEmailText;?></textarea></td>
		</tr>
		<tr><td>Reply To</td>
			<td><input type=text name=sAutoEmailReplyTo value='<?php echo $sAutoEmailReplyTo;?>' size=40></td>
		</tr>
	</table>

	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Page Background Color *</td>
		<td><input type=text name=sPageBgColor value='<?php echo $sPageBgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPageBgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'></td>
		<td>Font Color *</td>
		<td><input type=text name=sFontColor value='<?php echo $sFontColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sFontColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>
		<BR> (Will be applied to Error Message, Terms & Conditions and Note)</td>
	</tr>
	
	<tr><td>Page Border Color</td>
		<td><input type=text name=sBorderColor value='<?php echo $sBorderColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sBorderColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Offer Background Color 1</td>
		<td><input type=text name=sOfferBgColor1 value='<?php echo $sOfferBgColor1;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor1","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Offer Background Color 2</td>
		<td><input type=text name=sOfferBgColor2 value='<?php echo $sOfferBgColor2;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sOfferBgColor2","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	
	<tr><td>Page2 Background Color *</td>
		<td><input type=text name=sPage2BgColor value='<?php echo $sPage2BgColor;?>' size=10> <input type=button onClick='Javascript:void(window.open("colorPalette.php?returnTo=sPage2BgColor","","width=100 height=450, scrollbars=no,resizable=no, status=no"));' Value='...'>		
	</td></tr>
	</table>
	
	
	
	
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
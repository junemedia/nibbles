<?php

/*********

Script to Display Add/Edit Campaign

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Campaigns - Add/Edit Campaign";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
// if a pixel deleted from nibbles page

if ($iDelete) {
	// if a poll option deleted
	// Poll will not be deleted from this script (It's from index.php in the same folder)
		
	$sDeleteQuery = "DELETE FROM pixels
				    WHERE  id = '$iDelete'";	
	$rResult = dbQuery($sDeleteQuery);
	//echo $deleteQuery;
	if (!($rResult)) {
		echo dbError();
	}
}

//prepare sourceCode and seqNo here
$iLastSeqNo = 0;

$sSeqQuery = "SELECT MAX(seqNo) lastSeqNo
				FROM   campaigns
				WHERE  partnerId='$iPartnerId'";
$rSeqResult = dbQuery($sSeqQuery);
echo dbError();

while ($oSeqRow = dbFetchObject($rSeqResult)) {
	$iLastSeqNo = $oSeqRow->lastSeqNo;
	// $iLastSeqNo = ltrim($iLastSeqNo,"0");
}

//echo "last ".$iLastSeqNo;

$iSeqNo = $iLastSeqNo+1;

if ($iSeqNo < 10) {
	$iSeqNo = "00".$iSeqNo;
} else if ($iSeqNo < 100) {
	$iSeqNo = "0".$iSeqNo;
}

//echo "<BR>new ".$iSeqNo;

//echo "<BR>new ".$iLastSeqNo;

//echo "<BR>new ".$sSeqQuery;

/*
if (($sSaveClose) && ($iId)) {
	// Check if it's differnet company than previous
	$sCheckQuery = "SELECT partnerId, seqNo
				   FROM   campaigns
				   WHERE  id = '$iId'";
	$rCheckResult = mysql_query($sCheckQuery);
	while ($oCheckRow = mysql_fetch_object($rCheckResult)) {
		$iPrevPartnerId = $oCheckRow->partnerId;
		$iPrevSeqNo = $oCheckRow->seqNo;
	}
	
	// If company is not changed while editing, don't change the seqno
	if ($prevPartnerId == $partnerId) {
		$seqNo = $prevSeqNo;
	}
}
*/

if ($sSaveClose || $sSaveNew) {
	// If adding/editing record findout partnerCode for selected partnerId
	$sPartnerQuery = "SELECT companyName, code
					  FROM   partnerCompanies
					  WHERE id = '$iPartnerId'";	
	
	$rPartnerResult = dbQuery($sPartnerQuery);
	
	while ( $oPartnerRow = dbFetchObject($rPartnerResult)) {
		$sPartnerCode = $oPartnerRow->code;
		$sPartnerName = $oPartnerRow->companyName;
		//set typeCode if it's not add record
		if (!($sTypeCode))
		$sTypeCode = $oPartnerRow->typeCode;
	}
	// 'b' is for Business Development
	if (!($sSourceCode)) {
		// Don't change the sourcecode if record is being edited
		$sSourceCode = strtolower($sPartnerCode.$sTypeCode.'b'.date('m').date('d').date('y').$iSeqNo);
	}
	
	// merge pixel's selected pageId array (already saved and newly added), to check duplication
	if (is_array($aPixelsPageId) || is_array($aNewPixelsPageId)) {
		if (is_array($aPixelsPageId) && is_array($aNewPixelsPageId)) {
			$aTempArray = array_merge($aPixelsPageId, $aNewPixelsPageId);
		} else if (!(is_array($aPixelsPageId))) {
			$aTempArray = $aNewPixelsPageId;
		} else if (!(is_array($aNewPixelsPageId))) {
			$aTempArray = $aPixelsPageId;
		}
		$aTempArrayUnique = array_unique($aTempArray);
		if (count($aTempArray) > count($aTempArrayUnique)) {
			$sMessage = "Can add only one pixel per page for the same sourceCode...";
			$bKeepValues = true;
		}
	}

	if ($iCampaignTypeId == '4' && $fRevShareRate >=1) {
		$sMessage = "Revenue Share Rate must be less than 1...";
		$bKeepValues = "true";
	} else {

if (($sSaveClose || $sSaveNew) && !($iId) && $bKeepValues != true) {
	// if new record added
	
	$sAddQuery = "INSERT INTO campaigns(sourceCode, partnerId, campaignTypeId, typeCode, url, rate, 
							isParent, notes, displayInFrame, popOption, popupUrl, vSize, hSize, seqNo, createDate)
				  VALUES('$sSourceCode', '$iPartnerId', '$iCampaignTypeId', '$sTypeCode', '$sUrl',
							'$fRate', '$iIsParent', \"$sNotes\", '$sDisplayInFrame', '$sPopOption', \"$sPopupUrl\", '$iVSize',
							'$iHSize', '$iSeqNo', CURRENT_DATE)";
	$rResult = dbQuery($sAddQuery);
	if (!($rResult)) {
		$sMessage = dbError();
	} else {
		if ($sDisplayInFrame == "custom") {
			$sFrameQuery = "INSERT INTO campaignCustomFrames(sourceCode, frameContent)
							VALUES('$sSourceCode', '$sCustomFrameContent')";
			$rFrameResult = dbQuery($sFrameQuery);
		}
	}
	
	// make pixel entry database
	for ($i = 0; $i < count($newPixels); $i++) {
		$sAddPixelQuery = "INSERT INTO pixels(sourceCode, pageId, pixelHtml, alwaysDisplay)
							VALUES('$sSourceCode, '".$aNewPixelsPageId[$i]."', '".$aNewPixels[$i]."', '".$aNewAlwaysDisplay[$i]."')";
		$rAddPixelResult = dbQuery($sAddPixelQuery);
	}
		
	
} else if (($sSaveClose || $sSaveNew) && ($iId) && $bKeepValues != true) {
	
	// If record Edited
	// Don't allow partner OR Type code to change
	$sSelectQuery="SELECT url, partnerId, typeCode
				  FROM  campaigns
				  WHERE id = '$id'";
	
	$rResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rResult)) {
		$iPartnerId = $oRow->partnerId;
		$sTypeCode = $oRow->typeCode;
	}
	
	$sEditQuery = "UPDATE campaigns
					  SET sourceCode = '$sSourceCode',
						  partnerId = '$iPartnerId',
						  campaignTypeId = '$iCampaignTypeId',
						  typeCode = '$sTypeCode',
						  url = '$sUrl',
						  rate = '$fRate',						 
						  isParent = '$iIsParent',
						  notes = \"$sNotes\",						 
						  displayInFrame = '$sDisplayInFrame',
						  popOption = '$sPopOption',
						  popupUrl = '$sPopupUrl',
						  vSize = '$iVSize',
						  hSize = '$iHSize',
						  bustFrames = '$iBustFrames'
					  WHERE id = '$iId'";
	$rResult = dbQuery($sEditQuery);
	
	if (!($rResult)) {
		$sMessage = dbError();
	} else {
		
		if( substr($sDisplayInFrame, 0, 6) == "custom") {
			//check if frame record exists
			$sCheckQuery = "SELECT *
					   		FROM   campaignCustomFrames
					   		WHERE  sourceCode = '$sSourceCode'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult)==0) {
				$sFrameQuery = "INSERT INTO campaignCustomFrames(sourceCode, frameContent)
								VALUES('$sSourceCode', '$sCustomFrameContent')";
				$rFrameResult = dbQuery($sFrameQuery);
				echo dbError();
			} else {
				// update frame content if changed
				$sFrameQuery = "UPDATE campaignCustomFrames
						 	    SET    frameContent = '$sCustomFrameContent'
					   			WHERE  sourceCode = '$sSourceCode'";
				$rFrameResult = dbQuery($sFrameQuery);
			}
		}
	}
	
	// Traverse through all existing poll options and edit them
	if (is_array($aNibblesPixels)) {
		while (list($key, $value) = each($aNibblesPixels)) {
			$sEditPixelQuery = "UPDATE pixels
							 SET    pageId = '".$aPixelsPageId[$key]."',
									pixelHtml = '$value',
									alwaysDisplay = '".$aAlwaysDisplay[$key]."'
							 WHERE  id = '$key'";
			//echo $editPixelQuery;
			$rEditPixelResult = dbQuery($sEditPixelQuery);
		}
	}
	
	if (is_array($aNewPixels)) {
		// Insert all newly added poll options
		while (list($key, $value) = each($aNewPixels)) {
			$sAddPixelQuery = "INSERT INTO pixels(sourceCode, pageId, pixelHtml, alwaysDisplay)
							VALUES('$sSourceCode', '".$aNewPixelsPageId[$key]."','$value', '".$aNewAlwaysDisplay[$key]."')";
			$rAddPixelResult = dbQuery($sAddPixelQuery);
			//echo "<BR>".$addPixelQuery;
		}
	}
}

	}
}

// Find out on which page this sourceCode will appear, set ORDERY BY as sourcecode
// and go to that page, and display redirect for this sourceCode

// Prepare filter part of the query if filter specified...
if ($sFilter != '') {
	if ($sExactMatch == 'Y') {
		$sFilterPart = " AND (sourceCode = '$sFilter' || url = '$sFilter' || companyName = '$sFilter') ";
	} else {
		if ($sAlpha) {
			$sFilterPart = " AND (sourceCode like '$sFilter%') ";
		} else {
			$sFilterPart = " AND (sourceCode like '$sFilter%' || url like '$sFilter%' || companyName like '$sFilter%') ";
		}
	}
}

$sTempQuery = "SELECT count(campaigns.*) numRecords
			  FROM   campaigns, partnerCompanies
			  WHERE  campaigns.partnerId = partnerCompanies.id
			  AND    ascii(sourceCode) < ascii('$sSourceCode')
 			  $sFilterPart 
			  ORDER BY sourceCode";
$rTempResult = dbQuery($sTempQuery);

$iNumRecords = dbNumRows($rTempResult);
while ($oTempRow = dbFetchObject($rTempResult)) {
	$iNumRecords = $oTempRow->numRecords;
}

$iThisRecordNo = $iNumRecords; // because record will start with 0
$iPage = ceil($iThisRecordNo/$iRecPerPage);

$sPageReloadUrl = "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage&iPage=$page&sSourceCode=$sSourceCode&sShowRedirect=true";


if ($sSaveClose) {
	if ($bKeepValues != true) {
		
		// don't reload the list page if show active was checked. As page loading takes time 
		if ($sShowActive != 'Y') {
			echo "<script language=JavaScript>
				window.opener.location.href='".$sPageReloadUrl."';
				 self.close();
				</script>";			
			// exit from this script
				exit();
		} else {
			echo "<script language=JavaScript>				
				 self.close();
				</script>";			
			// exit from this script
				exit();
		}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		// don't reload the list page if show active was checked. As page loading takes time 
		if ($sShowActive != 'Y') {
			$sReloadWindowOpener = "<script language=JavaScript>
								window.opener.location.href='".$sPageReloadUrl."';
								</script>";	
		}
	}
		$sSourceCode = '';
		$iPartnerId = '';
		$iCampaignTypeId = '';
		$sTypeCode = '';
		$sUrl = '';
		$fRate = '';
		$sPixelCode = '';
		$iIsParent = '';
		$sNotes = '';
		$sDisplayInFrame = '';
		$sCustomFrameContent = '';
		$sPopOption = '';
		$sPopupUrl = '';
		$iVSize = '';
		$iHSize = '';
		$iSeqNo = '';
		$iBustFrames = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM campaigns
				     WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {		
		$sSourceCode = $oSelectRow->sourceCode;
		$iPartnerId = $oSelectRow->partnerId;
		$iCampaignTypeId = $oSelectRow->campaignTypeId;
		$sTypeCode = $oSelectRow->typeCode;
		$sUrl = $oSelectRow->url;
		$fRate = $oSelectRow->rate;
		$iIsParent = $oSelectRow->isParent;
		$sNotes = ascii_encode($oSelectRow->notes);
		$sDisplayInFrame = $oSelectRow->displayInFrame;
		$sPopOption = $oSelectRow->popOption;
		$sPopupUrl = $oSelectRow->popupUrl;
		$iVSize = $oSelectRow->vSize;
		$iHSize = $oSelectRow->hSize;		
		$iBustFrames = $oSelectRow->bustFrames;
		// get custom frame if defined any time even if not selected to display
		$sCustomQuery = "SELECT *
						 FROM   campaignCustomFrames
						 WHERE  sourceCode = '$sSourceCode'";
		$rCustomResult = dbQuery($sCustomQuery);
		echo dbError();
		while ($oCustomRow = dbFetchObject($rCustomResult)) {
			$sCustomFrameContent = ascii_encode($oCustomRow->frameContent);
		}
		
		$sDisplaySourceCode = "<tr><td>SourceCode</td><td>$sSourceCode</td></tr>";
		
	}
} else {
		
	$sNotes = ascii_encode(stripslashes($sNotes));
	$sCustomFrameContent = ascii_encode(stripslashes($sCustomFrameContent));
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// pixel number
$iNibblesPixelsNo = 0;

$sNibblesPixelsQuery = "SELECT *
				FROM   pixels
				WHERE  sourceCode = '$sSourceCode'
				ORDER BY id";
$rNibblesPixelsResult = dbQuery($sNibblesPixelsQuery);

while ($oNibblesPixelsRow = dbFetchObject($rNibblesPixelsResult)) {
	// display new option value if changed after adding new options
	$iNibblesPageId = $oNibblesPixelsRow->pageId;
	$sNibblesAlwaysDisplay = $oNibblesPixelsRow->alwaysDisplay;
	
	// get the pixel text to display
	// get the modified content if user tried to modify ( i.e. from the array aNibblesPixels )
	// otherwise get from database
	if ($aNibblesPixels[$oNibblesPixelsRow->id]) {
		$sPixelValue = ascii_encode(stripslashes($aNibblesPixels[$oNibblesPixelsRow->id]));
	} else {
		$sPixelValue = ascii_encode(stripslashes($oNibblesPixelsRow->pixelHtml));
	}
	
	$sPageOptions = "";
	
	// prepare page drop down box
	$sPagesQuery = "SELECT *
				   FROM   otPages
				   ORDER BY pageName";
	$rPagesResult = dbQuery($sPagesQuery);
	while ($oPagesRow = dbFetchObject($rPagesResult)) {
		$iPageId = $oPagesRow->id;
		$sPageName = $oPagesRow->pageName;
		
		if ($aPixelsPageId[$oNibblesPixelsRow->id]) {
			if ($iPageId == $aPixelsPageId[$oNibblesPixelsRow->id]) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		} else {
			if ($iPageId == $oNibblesPixelsRow->pageId) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		}
		$sPageOptions .= "<option value='$iPageId' $sSelected>$sPageName";
	}
	
	
	// check if nibblesPixels array is set because alwaysDisplay array might not have set if not checked the checkboxes
	if (isset($aNibblesPixels)) {
		
		if ($aAlwaysDisplay[$oNibblesPixelsRow->id]) {
			$sAlwaysDisplayChecked = "checked";
		} else {
			$sAlwaysDisplayChecked = "";
		}
	} else {
		if ($sNibblesAlwaysDisplay) {
			$sAlwaysDisplayChecked = "checked";
		} else {
			$sAlwaysDisplayChecked = "";
		}
	}
	
	// Display existing poll options with delete link for each of them
	$sNibblesPixelsList .="<tr><td>Pixel $iNibblesPixelsNo</td><Td><textarea name='aNibblesPixels[".$oNibblesPixelsRow->id."]' rows=4 cols=40>$sPixelValue</textarea>
						&nbsp; &nbsp; Page <select name='aPixelsPageId[".$oNibblesPixelsRow->id."]'>$sPageOptions</select>
						&nbsp; &nbsp; <input type=checkbox value='1' name='aAlwaysDisplay[".$oNibblesPixelsRow->id."]' $sAlwaysDisplayChecked> Always Display 
						 <a href='JavaScript:delPx(".$oNibblesPixelsRow->id.");'>Delete</a></td></tr>";
	$iNibblesPixelsNo++;
}

// Display currently added new poll options except last one, without delete link
for ($i = $iNibblesPixelsNo; $i < $iAddPixel; $i++) {
	
	$sPageOptions = "";
	// prepare page drop down box
	$sPagesQuery = "SELECT *
				   FROM   otPages
				   ORDER BY pageName";
	$rPagesResult = dbQuery($sPagesQuery);
	while ($oPagesRow = dbFetchObject($rPagesResult)) {
		$iPageId = $oPagesRow->id;
		$sPageName = $oPagesRow->pageName;
		if ($iPageId == $aNewPixelsPageId[$i]) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sPageOptions .= "<option value='$iPageId' $sSelected>$sPageName";
	}
	if ($aNewAlwaysDisplay[$i]) {
		$sNewAlwaysDisplayChecked = "checked";
	} else {
		$sNewAlwaysDisplayChecked = "";
	}
	
	$sNibblesPixelsList .= "<tr><td>Pixel $i a</td><Td><textarea name='aNewPixels[$i]' rows=4 cols=40>".ascii_encode(stripslashes($aNewPixels[$i]))."</textarea>
						&nbsp; &nbsp; Page <select name='aNewPixelsPageId[$i]'>$sPageOptions</select>
						&nbsp; &nbsp; <input type=checkbox value='1' name=aNewAlwaysDisplay[$i]' $sNewAlwaysDisplayChecked> Always Display </td></tr>";
	$iNibblesPixelsNo++;
}

// Display the last currently added new poll option with delete link for it
if (isset($iAddPixel) && $iAddPixel >= $iNibblesPixelsNo) {
	$sPageOptions = "";
	// prepare page drop down box
	$sPagesQuery = "SELECT *
				   FROM   otPages
				   ORDER BY pageName";
	$rPagesResult = dbQuery($sPagesQuery);
	while ($oPagesRow = dbFetchObject($rPagesResult)) {
		$iPageId = $oPagesRow->id;
		$sPageName = $oPagesRow->pageName;
		if ($iPageId == $aNewPixelsPageId[$iAddPixel]) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sPageOptions .= "<option value='$iPageId' $sSelected>$sPageName";
	}
	
	if ($aNewAlwaysDisplay[$iAddPixel]) {
		$sNewAlwaysDisplayChecked = "checked";
	} else {
		$sNewAlwaysDisplayChecked = "";
	}
	
	$sNibblesPixelsList .= "<tr><td>Pixel $iNibblesPixelsNo b</td><Td><textarea name='aNewPixels[".$iAddPixel."]'  rows=4 cols=40>".ascii_encode(stripslashes($aNewPixels[$iAddPixel]))."</textarea>
							&nbsp; &nbsp; Page <select name='aNewPixelsPageId[$iAddPixel]'>$sPageOptions</select> 
							&nbsp; &nbsp; <input type=checkbox value='1' name=aNewAlwaysDisplay[".$iAddPixel."]' $sNewAlwaysDisplayChecked> Always Display 
							&nbsp; <a href='JavaScript:addPx(".($iNibblesPixelsNo-1).");'>Delete</a></td></tr>";
	$iNibblesPixelsNo++;
}



// Prepare partner options for Partner Selection box
$sPartnerQuery = "SELECT id, companyName, code
				  FROM   partnerCompanies
				  ORDER BY companyName";
$rPartnerResult = dbQuery($sPartnerQuery);

while ( $oPartnerRow = dbFetchObject($rPartnerResult)) {
	
	if ($oPartnerRow->id == $iPartnerId) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sPartnerOptions .="<option value='".$oPartnerRow->id."' $sSelected>".$oPartnerRow->companyName;
}


// Prepare typeCode options for Partner Selection box
$sTypeCodeQuery = "SELECT *
				  FROM   typeCodes
				  ORDER BY title";
$rTypeCodeResult = dbQuery($sTypeCodeQuery);

while ( $oTypeCodeRow = dbFetchObject($rTypeCodeResult)) {
	
	if ($oTypeCodeRow->typeCode == $sTypeCode) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sTypeCodeOptions .="<option value='".$oTypeCodeRow->typeCode."' $sSelected>".$oTypeCodeRow->title;
}


// Prepare frame options
switch ($sDisplayInFrame) {
	case "top":
	$sTopFrameSelected = "selected";
	break;
	case "left":
	$sLeftFrameSelected = "selected";
	break;
	case "right":
	$sRightFrameSelected = "selected";
	break;
	case "bottom":
	$sBottomFrameSelected = "selected";
	break;
	case "customTop":
	$sCustomTopFrameSelected = "selected";
	break;
	case "customLeft":
	$sCustomLeftFrameSelected = "selected";
	break;
	case "customRight":
	$sCustomRightFrameSelected = "selected";
	break;
	case "customBottom":
	$sCustomBottomFrameSelected = "selected";
	break;
	default:
	$sNoFrameSelected = "selected";
}

// Prepare Frame Options

$sFrameOptions = "<option value='' $sNoFrameSelected>No Frame
				 <option value='top' $sTopFrameSelected>Common Top Frame
				 <option value='left' $sLeftFrameSelected>Common Left Frame
				 <option value='right' $sRightFrameSelected>Common Right Frame
				 <option value='bottom' $sBottomFrameSelected>Common Bottom Frame
				 <option value='customTop' $sCustomTopFrameSelected>Custom Top Frame
				 <option value='customLeft' $sCustomLeftFrameSelected>Custom Left Frame
				 <option value='customRight' $sCustomRightFrameSelected>Custom Right Frame
				 <option value='customBottom' $sCustomBottomFrameSelected>Custom Bottom Frame";


$sNoPopupChecked = '';
$sPopupChecked = '';
$sPopUnderChecked = '';
switch ($sPopOption) {
	case "popup":
	$sPopupChecked = "checked";
	break;
	case "popunder":
	$sPopUnderChecked = "checked";
	break;
	default:
	$sNoPopupChecked = "checked";
}

if ($iIsParent == 'Y') {
	$sIsParentChecked = "checked";
} else {
	$sIsParentChecked = '';
}


$sCampTypeQuery = "SELECT *
				   FROM	  campaignTypes
				   ORDER BY campaignType";
$rCampTypeResult = mysql_query($sCampTypeQuery);
while ($oCampTypeRow = mysql_fetch_object($rCampTypeResult)) {
	if ($oCampTypeRow->id == $iCampaignTypeId) {
		$sSelected = "Selected";
	} else {
		$sSelected = "";
	}
	$sCampaignTypeOptions .= "<option value='$oCampTypeRow->id' $sSelected>$oCampTypeRow->campaignType";
}
	
if ($iBustFrames) {
	$iBustFramesChecked = "checked";
} else {
	$iBustFramesChecked = '';
}

$sAddPixelLink = "<a href='JavaScript:addPx(".$iNibblesPixelsNo.");'>Add Nibbles Pixel</a> - Can add only one pixel per page for the same sourceCode.
				<BR>[emailId] in the pixel code will be replaced with unique userId while displaying the pixel.";

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sSourceCode value='$sSourceCode'>
			<input type=hidden name=sFilter value='$sFilter'>
			<input type=hidden name=sAlpha value='$sAlpha'>
			<input type=hidden name=sExactMatch value='$sExactMatch'>
			<input type=hidden name=sShowActive value='$sShowActive'>
			<input type=hidden name=iRecPerPage value='$iRecPerPage'>
			<input type=hidden name=iAddPixel value=''>
			<input type=hidden name=iDelete>";

include("../../includes/adminAddHeader.php");

?>
<script language=JavaScript>
function addPx(addPixel) {
	document.forms[0].elements['iAddPixel'].value=addPixel;
	document.forms[0].submit();
}
function delPx(pxNo) {
	document.forms[0].elements['iDelete'].value=pxNo;
	document.forms[0].submit();
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<?php echo $sDisplaySourceCode;?>
		<tr><TD>Partner Company</td><td><select name=iPartnerId><?php echo $sPartnerOptions;?></select>
		 				&nbsp; &nbsp; <?php echo $addPartnerLink;?></td></tr>				
		<tr><TD>Type Code</td><td><select name=sTypeCode><?php echo $sTypeCodeOptions;?></select></td></tr>
		<tr><TD>URL</td><td><input type=text name=sUrl value='<?php echo $sUrl;?>' size=45></td></tr>		
		<tr><TD>Campaign Type</td><td><select name=iCampaignTypeId>
									<?php echo $sCampaignTypeOptions;?>
									</select></td></tr>
		<tr><TD>Rate</td><td><input type=text name=fRate value='<?php echo $fRate;?>'> $
							<BR>Rate must be less than 1 for revenue type campaign.</td></tr>
		<tr><TD>Is Parent</td><td><input type=checkbox name=iIsParent value='1' <?php echo $sIsParentChecked;?>></td></tr>
		<tr><td>Bust Frames</td><td><input type=checkbox name=iBustFrames value='1' size=8 <?php echo $iBustFramesChecked;?>></td>
			</tr>
		<tr><TD>Notes</td><td><textarea name=sNotes rows=3 cols=40><?php echo $sNotes;?></textarea></td></tr>
		<tr><td colspan=2><BR></td></tr>
		
		<?php echo $sNibblesPixelsList;?>
		
		<tr><td colspan=2><?php echo $sAddPixelLink;?><BR><BR><BR></td></tr>
		<tr><td>Display Frame</td><td><select name=sDisplayInFrame>
			<?php echo $sFrameOptions;?>
			</select></td></tr>
	<tr><td>Custom Frame Content</td>
		<td><textarea name=sCustomFrameContent rows=4 cols=40><?php echo $sCustomFrameContent;?></textarea></td>
	</tr>
	
	<tr><td>PopUp</td><td><input type=radio name=sPopOption value='' <?php echo $sNoPopupChecked;?>> No PopUp
		&nbsp; &nbsp; &nbsp; <input type=radio name=sPopOption value='popup' <?php echo $sPopupChecked;?>> PopUp
		&nbsp; &nbsp; &nbsp; <input type=radio name=sPopOption value='popunder' <?php echo $sPopunderChecked;?>> PopUnder
		</td>
	</tr>
	<tr><td>PopUp URL</td>
		<td><input type=text name=sPopupUrl value='<?php echo $sPopupUrl;?>' size=40></td>
	</tr>
	<tr><td></td><td>V Size &nbsp; &nbsp;<input type=text name=iVSize value='<?php echo $iVSize;?>' size=5>
		 &nbsp; &nbsp; &nbsp; H Size &nbsp; &nbsp;<input type=text name=iHSize value='<?php echo $iHSize;?>' size=5>
	</td></tr>	
</table>	
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
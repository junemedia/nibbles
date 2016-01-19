<?php

/*********
Script to Display Add/Edit link
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Links - Add/Edit Link";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
		// if a pixel deleted from nibbles page
		if ($iDelete) {
			// if a poll option deleted
			// Poll will not be deleted from this script (It's from index.php in the same folder)
				
			$sDeleteQuery = "DELETE FROM pixels WHERE  id = '$iDelete'";

			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
			
			$rResult = dbQuery($sDeleteQuery);
			if (!($rResult)) {
				echo dbError();
			}
		}

		//prepare sourceCode and seqNo here
		$iSeqNo = 0;
		
		$sSeqQuery = "SELECT MAX(seqNo) lastSeqNo
						FROM   links
						WHERE  partnerId='$iPartnerId'";
		$rSeqResult = dbQuery($sSeqQuery);
		echo dbError();
		
		while ($oSeqRow = dbFetchObject($rSeqResult)) {
			$iSeqNo = $oSeqRow->lastSeqNo;
		}
		
		if(!($sOfferCode)){
			$sOfferCode = '';
		}
		
		$aSourceCode = '';
		if ($sSaveClose || $sSaveNew) {
			if ($iCampaignTypeId != '') {
		//	if ($ioId != '' && $iCampaignTypeId != '') {
				if ($iCampaignTypeId != '1' && $sUrl =='') {
					$sMessage = "URL is required if Campaign Type is other than API...";
					$bKeepValues = true;
			 	} else {

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
						for ($a=1; $a<=$sCreateSourceCode; $a++) {
							$iSeqNo = $iSeqNo + 1;
							if ($iSeqNo < 10) {
								$iSeqNo = "00".$iSeqNo;
							} else if ($iSeqNo < 100) {
								$iSeqNo = "0".$iSeqNo;
							}
							$sSourceCode = strtolower($sPartnerCode.$sTypeCode.'b'.date('m').date('d').date('y').$iSeqNo);
							$aSourceCode .= $sSourceCode.",";
						}
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
				
					if($iCampaignTypeId == '1' && $sOfferCode == ''){
						$sMessage = "When Creating an API Campaign, you must select an Offer Code.";
						$bKeepValues = true;
					} else if ($iCampaignRateTypeId == '4' && $fRevShareRate >=1) {
						$sMessage = "Revenue Share Rate must be less than 1...";
						$bKeepValues = true;
					} else if ($iExpDay != '' && $iExpMonth != '' && $iExpYear != '' && !checkDate($iExpMonth, $iExpDay, $iExpYear)) {
						$sMessage = "Expiration Date is invalid...";
						$bKeepValues = true;
					} else {
						$sExpirationDate = "$iExpYear-$iExpMonth-$iExpDay";
						if (($sSaveClose || $sSaveNew) && !($iId) && $bKeepValues != true) {
							// if new record added
						
							// start of track users' activity in nibbles
							$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
										  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Add New Campaign - partnerId: $iPartnerId')";
							$rResult = dbQuery($sAddQuery);
							// end of track users' activity in nibbles
							
							$sMore = '';
							$aSourceCode = substr($aSourceCode, 0, strlen($aSourceCode)-1);
							$sTempSourceCode = explode(",", $aSourceCode);
							$sCountTempSourceCode = count($sTempSourceCode);
							for ($iCount = 0; $iCount<=$sCountTempSourceCode; $iCount++) {
								$sSourceCode = $sTempSourceCode[$iCount];
								if ($sSourceCode != '') {
									if ($sCountTempSourceCode > 1) {
										$sPassSourceCode .= $sSourceCode.",";
										$sMore = 'yes';
									} else {
										$sPassSourceCode = $sSourceCode;
									}
									$sAddQuery = "INSERT INTO links(sourceCode, partnerId, campaignTypeId, campaignRateTypeId, typeCode, url, rate, 
												isParent, creative, notes, displayInFrame, popOption, popupUrl, vSize, 
												hSize, seqNo, createDate, bustFrames, expirationDate, groupName, ioId, offerCode)
										  VALUES('$sSourceCode', '$iPartnerId', '$iCampaignTypeId', '$iCampaignRateTypeId', '$sTypeCode', '$sUrl',
												'$fRate', '$iIsParent', \"$sCreative\", \"$sNotes\", '$sDisplayInFrame', '$sPopOption', \"$sPopupUrl\", '$iVSize',
												'$iHSize', '$iSeqNo', CURRENT_DATE, '$iBustFrames', '$sExpirationDate', '$sGroupName','$ioId','$sOfferCode')";
									$rResult = dbQuery($sAddQuery);
									
									if (!($rResult)) {
										$sMessage = dbError();
									} else {
										if ($sDisplayInFrame == "custom") {
											$sFrameQuery = "INSERT INTO campaignCustomFrames(sourceCode, frameContent)
															VALUES('$sSourceCode', '$sCustomFrameContent')";
											$rFrameResult = dbQuery($sFrameQuery);
											if (!($rFrameResult)) {
												$sMessage = dbError();
												$bKeepValues = true;
											}
										}
									}
									
									// make pixel entry database
									for ($i = 0; $i < count($aNewPixels); $i++) {
										$sAddPixelQuery = "INSERT INTO pixels(sourceCode, pageId, pixelHtml, alwaysDisplay)
															VALUES('$sSourceCode', '".$aNewPixelsPageId[$i]."', '".$aNewPixels[$i]."', '".$aNewAlwaysDisplay[$i]."')";
										$rAddPixelResult = dbQuery($sAddPixelQuery);
										if (!($rAddPixelResult)) {
											$sMessage = dbError();
											$bKeepValues = true;
										}
									}
								}
							}
							
							if ($sMore == 'yes') {
								$sPassSourceCode = substr($sPassSourceCode, 0, strlen($sPassSourceCode)-1);
								$sSourceCode = $sPassSourceCode;
							} else {
								$sSourceCode = $sPassSourceCode;
							}
				
						} else if (($sSaveClose || $sSaveNew) && ($iId) && $bKeepValues != true) {
							
							// start of track users' activity in nibbles
							$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
										  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Edit Campaign And Changes Made - partnerId: $iPartnerId')";
							$rResult = dbQuery($sAddQuery);
							// end of track users' activity in nibbles
							
							
							
							// If record Edited
							// Don't allow partner OR Type code to change
							$sSelectQuery="SELECT url, partnerId, typeCode
										  FROM  links
										  WHERE id = '$id'";
							
							$rResult = dbQuery($sSelectQuery);
							
							while ($oRow = dbFetchObject($rResult)) {
								$iPartnerId = $oRow->partnerId;
								$sTypeCode = $oRow->typeCode;
							}
							
							
							$sEditQuery = "UPDATE links
											  SET sourceCode = '$sSourceCode',
												  partnerId = '$iPartnerId',
												  campaignTypeId = '$iCampaignTypeId',
												  campaignRateTypeId = '$iCampaignRateTypeId',
												  typeCode = '$sTypeCode',
												  url = '$sUrl',
												  rate = '$fRate',							  
												  isParent = '$iIsParent',
												  creative = \"$sCreative\",
												  notes = \"$sNotes\",						 
												  displayInFrame = '$sDisplayInFrame',
												  popOption = '$sPopOption',
												  popupUrl = '$sPopupUrl',
												  vSize = '$iVSize',
												  hSize = '$iHSize',
												  bustFrames = '$iBustFrames',
												  expirationDate = '$sExpirationDate',
												  groupName = '$sGroupName',
												  ioId = '$ioId',
												  offerCode = '$sOfferCode' 
											  WHERE id = '$iId'";
						
							// start of track users' activity in nibbles 
							$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
							  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
							$rLogResult = dbQuery($sLogAddQuery); 
							// end of track users' activity in nibbles		
							
							
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
								}
							}
						}
					}
				}
			} else {
				//$sMessage = "IO # / Campaign Type is required...";
				$sMessage = "Campaign Type is required...";
				$bKeepValues = true;
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

$sTempQuery = "SELECT count(links.*) numRecords
			  FROM   links, partnerCompanies
			  WHERE  links.partnerId = partnerCompanies.id
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

$sPageReloadUrl = "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage&iPage=$page&sSourceCode=$sSourceCode&sShowRedirect=true&sMore=$sMore";


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
		$sGroupName = '';
		$iPartnerId = '';
		$iCampaignTypeId = '';
		$iCampaignRateTypeId = '';
		$sTypeCode = '';
		$sUrl = '';
		$fRate = '';
		$sPixelCode = '';
		$iIsParent = '';
		$sCreative = '';
		$sNotes = '';		
		$sDisplayInFrame = '';
		$sCustomFrameContent = '';
		$sPopOption = '';
		$sPopupUrl = '';
		$iVSize = '';
		$iHSize = '';
		$iSeqNo = '';
		$iBustFrames = '';
		$sExpirationDate = '';
		$iExpDay = '';
		$iExpMonth = '';
		$iExpYear = '';
		$ioId = '';
		$sOfferCode = '';
}


if ($iId) {
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM links
				     WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {		
		$sSourceCode = $oSelectRow->sourceCode;
		$sGroupName = $oSelectRow->groupName;
		$iPartnerId = $oSelectRow->partnerId;
		$iCampaignTypeId = $oSelectRow->campaignTypeId;
		$iCampaignRateTypeId = $oSelectRow->campaignRateTypeId;
		$sTypeCode = $oSelectRow->typeCode;
		$sUrl = $oSelectRow->url;
		$fRate = $oSelectRow->rate;		
		$iIsParent = $oSelectRow->isParent;
		$sCreative = ascii_encode($oSelectRow->creative);
		$sNotes = ascii_encode($oSelectRow->notes);
		$sDisplayInFrame = $oSelectRow->displayInFrame;
		$sPopOption = $oSelectRow->popOption;
		$sPopupUrl = $oSelectRow->popupUrl;
		$iVSize = $oSelectRow->vSize;
		$iHSize = $oSelectRow->hSize;		
		$iBustFrames = $oSelectRow->bustFrames;
		$sExpirationDate = $oSelectRow->expirationDate;
		$iExpYear = substr($sExpirationDate,0,4);
		$iExpMonth = substr($sExpirationDate,5,2);
		$iExpDay = substr($sExpirationDate,8,2);
		$ioId = $oSelectRow->ioId;
		$sOfferCode = $oSelectRow->offerCode;
		
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
	
	// start of track users' activity in nibbles
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Clicked on Edit Campaign, No Changes Yet - partnerId: $iPartnerId')";
	$rResult = dbQuery($sAddQuery);
	// end of track users' activity in nibbles
} else {
	$sCreative = ascii_encode(stripslashes($sCreative));
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


$sAddPixelLink = "<a href='JavaScript:addPx(".$iNibblesPixelsNo.");'>Add Nibbles Pixel</a> - Can add only one pixel per page for the same sourceCode.
				<BR>[emailId] in the pixel code will be replaced with unique userId while displaying the pixel.
				<BR>[email] in the pixel code will be replaced with user's email address while displaying the pixel.
				<BR />[timeStamp] in the pixel code will be replaced with the unix timestamp while displaying the pixel.
				<BR>[START_VAR]varName[END_VAR] will be replaced with the value of the incoming 'varName' variable.
				<BR> &nbsp; e.g. ".htmlentities("<img src='xxx.php?partner=yyy&sid=[START_VAR]sid[END_VAR]' >");



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


// Prepare group options
$sGroupQuery = "SELECT groupName FROM campaignsGroup
				  ORDER BY groupName";
$rGroupResult = dbQuery($sGroupQuery);

while ( $oGroupRow = dbFetchObject($rGroupResult)) {
	
	if ($oGroupRow->groupName == $sGroupName) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sGroupOptions .="<option value='".$oGroupRow->groupName."' $sSelected>".$oGroupRow->groupName;
}

//prepare IO options.
$sIOQuery = "SELECT I.*, C.companyName FROM io I, partnerCompanies C WHERE I.partnerId = C.id";
$sIOOptions = "<select name='ioId'><option value=''>Select One</option>\n";
$rIOResult = dbQuery($sIOQuery);
while($oIO = dbFetchObject($rIOResult)){
	//name, & id
	$sIOOptions .= "<option value='$oIO->id' ".($oIO->id == $ioId ? 'selected' : '').">$oIO->id ($oIO->type $oIO->companyName)</option>";
}
$sIOOptions .= "</select>";


// Prepare typeCode options for Partner Selection box
$sTypeCodeQuery = "SELECT *
				  FROM   typeCodes
				  ORDER BY id";
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


$sCampRateTypeQuery = "SELECT *
				   FROM	  campaignRateStructure
				   ORDER BY rateType";
$rCampRateTypeResult = mysql_query($sCampRateTypeQuery);
while ($oCampRateTypeRow = mysql_fetch_object($rCampRateTypeResult)) {
	if ($oCampRateTypeRow->id == $iCampaignRateTypeId) {
		$sSelected = "Selected";
	} else {
		$sSelected = "";
	}
	$sRateStructureOptions .= "<option value='$oCampRateTypeRow->id' $sSelected>$oCampRateTypeRow->rateType";
}


$sCampTypeQuery = "SELECT *
				   FROM	  campaignTypes
				   ORDER BY campaignType";
$rCampTypeResult = mysql_query($sCampTypeQuery);
$sCampaignTypeOptions = "<option value=''>Select One</option>";

//special for API offers: these need to turn on the sOfferCode input

while ($oCampTypeRow = mysql_fetch_object($rCampTypeResult)) {
	if ($oCampTypeRow->id == $iCampaignTypeId) {
		$sSelected = "Selected";
	} else {
		$sSelected = "";
	}
	$sCampaignTypeOptions .= "<option value='$oCampTypeRow->id' $sSelected >$oCampTypeRow->campaignType";
}

if ($iBustFrames) {
	$iBustFramesChecked = "checked";
} else {
	$iBustFramesChecked = '';
}

//prepare options for offerCodes
//if this is an edit, then only show the one offer code.
//else, make an options list.
if ($sOfferCode) {
	$sOfferCodeOptions = $sOfferCode."<input type='hidden' name='sOfferCode' value='$sOfferCode'>";
	$sRequiredMsg = '';
} else {
	$sOfferCodeOptions = "<select name='sOfferCode' ".($iCampaignTypeId != '1' ? 'disabled' : '')."><option value=''></option>";
	$sRequiredMsg = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ignore If Campaign Type Is Not API.";

	$sOfferCodeSql = "SELECT offerCode from offers WHERE mode IN ('A','P','T') ORDER BY offerCode ASC";
	$rOfferCodeResp = dbQuery($sOfferCodeSql);
	while($oOfferCode = dbFetchObject($rOfferCodeResp)){
		$sOfferCodeOptions .= "<option value='$oOfferCode->offerCode'>$oOfferCode->offerCode</option>";
	}
	
	$sOfferCodeOptions .= "</select>";
}

$sUrlDisable = '';
if ($iCampaignTypeId == '1') {
	$sUrlDisable = ' disabled ';
}

// prepare month options for From and To date
$sExpMonthOptions = "<option value=''>Month";
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $iExpMonth) {
			$sMonthSel = "selected";
		} else {
			$sMonthSel = "";
		}
		
		
		$sExpMonthOptions .= "<option value='$value' $sMonthSel>$aGblMonthsArray[$i]";		
	}
	
	// prepare day options for From and To date
	$sExpDayOptions = "<option value=''>Day";
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iExpDay) {
			$sDaySel = "selected";
		} else {
			$sDaySel = "";
		}
		
		$sExpDayOptions .= "<option value='$value' $sDaySel>$i";
		
	}
	
	// prepare year options
	$sExpYearOptions = "<option value=''>Year";
	for ($i = $iCurrYear; $i <= $iCurrYear+5; $i++) {
		
		if ($i == $iExpYear) {
			$sYearSel = "selected";
		} else {
			$sYearSel ="";
		}
		
		$sExpYearOptions .= "<option value='$i' $sYearSel>$i";
	}	
	
	$sAddGroupLink = "<a class=header href='JavaScript:void(window.open(\"addGroup.php?iMenuId=$iMenuId\", \"AddGroup\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Add New Group</a>";
	
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
function toggleOfferCodes(){
	if((document.forms[0].elements['iCampaignTypeId'].options[document.forms[0].elements['iCampaignTypeId'].selectedIndex].value != '1') && (document.forms[0].elements['sOfferCode'].type != 'hidden')){
		document.forms[0].elements['sOfferCode'].disabled = true;
		document.form1.sUrl.disabled = false;
	} else {
		document.forms[0].elements['sOfferCode'].disabled = false;
		document.form1.sUrl.disabled = true;
	}
}
function enableUrl(){
	if(document.form1.iCampaignTypeId.value == '1') {
		document.form1.sUrl.disabled = true;
	} else {
		document.form1.sUrl.disabled = false;
	}
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<?php echo $sDisplaySourceCode;?>
		<tr><td>No. of Source Code to Create: </td><td><select name='sCreateSourceCode'>
		<option value='1' selected>1</option><option value='2'>2</option>
		<option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option>
		<option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option>
		<option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option>
		<option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option>
		<option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option>
		<option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option>
		<option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option>
		<option value='31'>31</option><option value='32'>32</option><option value='33'>33</option><option value='34'>34</option>
		<option value='35'>35</option><option value='36'>36</option><option value='37'>37</option><option value='38'>38</option>
		<option value='39'>39</option><option value='40'>40</option><option value='41'>41</option><option value='42'>42</option>
		<option value='43'>43</option><option value='44'>44</option><option value='45'>45</option><option value='46'>46</option>
		<option value='47'>47</option><option value='48'>48</option><option value='49'>49</option><option value='50'>50</option>
		</select></td></tr>
		
		<tr><td>Group</td><td><select name=sGroupName><?php echo $sGroupOptions;?></select>
		 				&nbsp; &nbsp; <?php echo $sAddGroupLink;?>&nbsp; &nbsp;Note: If you add new group, you must hit F5 to reload selection list.</td></tr>

		<tr><TD>Partner Company</td><td><select name=iPartnerId><?php echo $sPartnerOptions;?></select>
		 				&nbsp; &nbsp; <?php echo $addPartnerLink;?></td></tr>
		 <tr><td>IO #</td>
		 	<td><?php echo $sIOOptions;?>
		 	<!--&nbsp;&nbsp;<font color="red">required</font>-->
		 	   </td>
		 </tr>
		<tr><TD>Type Code</td><td><select name=sTypeCode><?php echo $sTypeCodeOptions;?></select></td></tr>
		<tr><TD>URL</td><td><input type=text name=sUrl value='<?php echo $sUrl;?>' <?php echo $sUrlDisable; ?> size=45>
		&nbsp;&nbsp;Required if Campaign Type is other than API.
		</td></tr>		
		<tr><TD>Rate Structure</td><td><select name=iCampaignRateTypeId>
									<?php echo $sRateStructureOptions;?>
									</select></td></tr>

		<tr><TD>Campaign Types</td><td><select name=iCampaignTypeId onChange='toggleOfferCodes();enableUrl();' >
				<?php echo $sCampaignTypeOptions;?></select> &nbsp;&nbsp;<font color="red">required</font></td></tr>
		
		<tr><td>Offer Code For API</td><td><?php echo $sOfferCodeOptions;?>
		<?php echo $sRequiredMsg; ?>
		</td></tr>
		
		<tr><TD>Rate</td><td><input type=text name=fRate value='<?php echo $fRate;?>'> $
							<BR>Rate must be less than 1 for revenue type campaign.</td></tr>		
		<tr><TD>Is Parent</td><td><input type=checkbox name=iIsParent value='1' <?php echo $sIsParentChecked;?>></td></tr>
		<tr><td>Bust Frames</td><td><input type=checkbox name=iBustFrames value='1' size=8 <?php echo $iBustFramesChecked;?>></td>
			</tr>
		<tr><TD>Creative</td><td><textarea name=sCreative rows=3 cols=40><?php echo $sCreative;?></textarea></td></tr>
		<tr><TD>Notes</td><td><textarea name=sNotes rows=3 cols=40><?php echo $sNotes;?></textarea></td></tr>
		<tr><td colspan=2><BR></td></tr>
		<tr><td>Expiration Date</td><td><select name=iExpMonth><?php echo $sExpMonthOptions;?>
			</select> &nbsp;<select name=iExpDay><?php echo $sExpDayOptions;?>
			</select> &nbsp;<select name=iExpYear><?php echo $sExpYearOptions;?>
			</select></td>
		</tr>
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
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
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
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
		
		if (!($sOfferCode)) {
			$sOfferCode = '';
		}
		
		$aSourceCode = '';
		if ($sSaveClose || $sSaveNew) {
			if ($iCampaignTypeId != '') {
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
					$sNoOfLinksDifference = ($sNoOfLinksToCreate - $oldSNoOfLinksToCreate);
					
					if(($sNoOfLinksDifference > 0)&&($sGroupName == '')){
						$sMessage = "In order to create multiple links, you must select or create a group.";
						$sNoOfLinksDifference = 0;
						//mail('bbevis@amperemedia.com', __file__.":".__line__, __file__.":".__line__.': no of links diff is > 0, and sGroupName is nothing.');
					}
					if ((!($sSourceCode))||($sNoOfLinksDifference > 0)) {
						// Don't change the sourcecode if record is being edited
						for ($a=1; $a<=$sNoOfLinksDifference; $a++) {
							$iSeqNo = $iSeqNo + 1;
							if ($iSeqNo < 10) {
								$iSeqNo = "00".$iSeqNo;
							} else if ($iSeqNo < 100) {
								$iSeqNo = "0".$iSeqNo;
							}
							$sSourceCode = strtolower($sPartnerCode.$sTypeCode.'b'.date('m').date('d').date('y').$iSeqNo);
							$aSourceCode .= $sSourceCode.",";
							//mail('bbevis@amperemedia.com', __line__."::".__file__, "$aSourceCode");
						}
						
						//mail('bbevis@amperemedia.com', __file__.":".__line__, __file__.":".__line__.': $sSourceCode is not there or $sNoOfLinksDiff is greater than 0');
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

					if($sGroupName == '' && $sNoOfLinksDifference > 0){
						$sMessage = "Group is required when making new links.";
						$bKeepValues = true;
					} else if ($iSiteId == '') {
						$sMessage = "Site Name Required";
						$bKeepValues = true;
					} else if ($iDomainId == '' && $sRandomDomain == '') {
						$sMessage = "Please Select Either a Domain or 'Random Domain'";
						$bKeepValues = true;
					} else if ($iCampaignId == '') {
						$sMessage = "Campaign Name Required";
						$bKeepValues = true;
					} else if ($iFlowId == '') {
						$sMessage = "Flow Name Required";
						$bKeepValues = true;
					} else if ($iRedirectUrlId == '') {
						$sMessage = "Redirect Url Required";
						$bKeepValues = true;
					} else if ($sPixelEnable == 'Y' && $sPixelUrl == '') {
						$sMessage = "Pixel URL Required";
						$bKeepValues = true;
					} else if($ioId == ''){
						$sMessage = "IO # Required.";
						$bKeepValues = true;
					} else if($iCampaignTypeId == '1' && $sOfferCode == '') {
						$sMessage = "When Creating an API Campaign, you must select an Offer Code.";
						$bKeepValues = true;
					} else if ($iCampaignRateTypeId == '4' && $fRevShareRate >=1) {
						$sMessage = "Revenue Share Rate must be less than 1...";
						$bKeepValues = true;
					} else if ($iEmailCreativeId == '') {
						$sMessage = "Email Creative Required...";
						$bKeepValues = true;					
					} else if ($iExpDay != '' && $iExpMonth != '' && $iExpYear != '' && !checkDate($iExpMonth, $iExpDay, $iExpYear)) {
						$sMessage = "Expiration Date is invalid...";
						$bKeepValues = true;
					} else {
						$sExpirationDate = "$iExpYear-$iExpMonth-$iExpDay";
						
					
						if ($sPixelEnable == '') { $sPixelEnable = 'N'; }
						if ($sPixelLocation == '') { $sPixelLocation = 'E'; }

						
						if (($sSaveClose || $sSaveNew) && (!($iId) || $sNoOfLinksDifference > 0) && $bKeepValues != true && !($sMessage)) {
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
							$aDomainIdArray = $_SESSION['aDomainIdArray'];
							for ($iCount = 0; $iCount<=$sCountTempSourceCode; $iCount++) {
								$sSourceCode = $sTempSourceCode[$iCount];
								if ($sSourceCode != '') {
									if ($sCountTempSourceCode > 1) {
										$sPassSourceCode .= $sSourceCode.",";
										$sMore = 'yes';
									} else {
										$sPassSourceCode = $sSourceCode;
									}
									
									if ($sDisableStandardPop == '') {
										$sDisableStandardPop = 'N';
									}
									if ($sDisableExitPop == '') {
										$sDisableExitPop = 'N';
									}
									if ($sDisableAbandonedPop == '') {
										$sDisableAbandonedPop = 'N';
									}
									if ($sDisableWinManagerPop == '') {
										$sDisableWinManagerPop = 'N';
									}
									if ($sShowNonRevOffers == '') { $sShowNonRevOffers = 'N'; }
									
									$sPixelUrl = addslashes($sPixelUrl);
									$sDefaultTitle = addslashes($sDefaultTitle);
									
									
									if ($sCountTempSourceCode > 1 && $sRandomDomain == 'Y') {
										$iTemp = array_rand($aDomainIdArray, 1);
										$iDomainId = $aDomainIdArray[$iTemp];
									}
									$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


									$sAddQuery = "INSERT INTO links(sourceCode, partnerId, campaignTypeId, campaignRateTypeId, typeCode, url, rate, 
													creative, notes, seqNo, createDate, bustFrames, expirationDate, groupName, ioId, offerCode, 
													siteId, campaignId, flowId, description, dateTimeCreated, whereToGoId, emailCapture, showSkip, 
													stopAllPopups, disableStandardPop, disableExitPop, disableAbandonedPop, disableWinManagerPop, domainId,
													isPixelEnable,pixelLocation,pixelUrl,partnerCanLogin, userName, defaultTitle, recipe4Living, recipeId,
													foreignIPTracking,showNonRevOffers)
													  VALUES('$sSourceCode', '$iPartnerId', '$iCampaignTypeId', '$iCampaignRateTypeId', '$sTypeCode', '$sUrl',
													'$fRate', \"$sCreative\", \"$sNotes\", '$iSeqNo', CURRENT_DATE, '$iBustFrames', '$sExpirationDate', '$sGroupName','$ioId','$sOfferCode',
													'$iSiteId', '$iCampaignId', '$iFlowId', \"$sDescription\", NOW(), '$iRedirectUrlId', '$sShowEmailCapture', '$sShowSkipButton',
													'$sStopAllPopups', '$sDisableStandardPop', '$sDisableExitPop', '$sDisableAbandonedPop', '$sDisableWinManagerPop', '$iDomainId', 
													'$sPixelEnable', '$sPixelLocation', \"$sPixelUrl\", \"$sPartnerCanLogin\", '$sTrackingUser', \"$sDefaultTitle\", \"$sRecipeForLiving\", \"$iRecipeId\",
													'$sForeignIPTracking','$sShowNonRevOffers')";
									$rResult = dbQuery($sAddQuery);
									
									
									// start of track users' activity in nibbles
									//echo "\t ".__line__.": ".memory_get_usage()."<br>";
									$tempString = addslashes($sAddQuery);
									//echo "\t ".__line__.": ".memory_get_usage()."<br>";
									$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$tempString\")";
									  /*
									$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"".addslashes($sAddQuery)."\")";
									*/
									//echo "\t ".__line__.": ".memory_get_usage()."<br>";
									$rLogResult = dbQuery($sLogAddQuery);
									
									//echo "\t ".__line__.": ".memory_get_usage()."<br>";
									// end of track users' activity in nibbles
									
									
									//mail('bbevis@amperemedia.com', __line__.": insert query","$sAddQuery");
									if (!($rResult)) {
										$sMessage = dbError();
									} 
									
									//also, do some inserts into the Foreign IP tracking table
									$foreignIPHandlingSQL = "INSERT INTO foreignIpHandling (sourceCode, redirectUrl, isBlock) values 
																							('$sSourceCode','$sForeignRedirectURL','".($sForeignIPTracking == 'block' ? 'Y' : 'N')."')";
									
									$rResult = dbQuery($foreignIPHandlingSQL);									
									$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($foreignIPHandlingSQL) . "\")";
									$rLogResult = dbQuery($sLogAddQuery);
									
									// make pixel entry database
									
									echo "\t ".__line__.": ".memory_get_usage()."<br>";
									for ($i = 0; $i < count($aNewPixels); $i++) {
										$sAddPixelQuery = "INSERT INTO pixels(sourceCode, pageId, pixelHtml, alwaysDisplay)
															VALUES('$sSourceCode', '".$aNewPixelsPageId[$i]."', '".$aNewPixels[$i]."', '".$aNewAlwaysDisplay[$i]."')";
										$rAddPixelResult = dbQuery($sAddPixelQuery);
										
										// start of track users' activity in nibbles
										$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
										  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddPixelQuery) . "\")";
										$rLogResult = dbQuery($sLogAddQuery);
										// end of track users' activity in nibbles
										
										if (!($rAddPixelResult)) {
											$sMessage = dbError();
											$bKeepValues = true;
										}
										
										unset($sAddPixelQuery);
										unset($rAddPixelResult);
										unset($sLogAddQuery);
										unset($rLogResult);
									}
									
									echo "\t ".__line__.": ".memory_get_usage()."<br>";
								}
								unset($sAddQuery);
								unset($sLogAddQuery);
								unset($foreignIPHandlingSQL);
								unset($rResult);
								unset($rLogResult);
								
								echo "\t ".__line__.": ".memory_get_usage()."<br>";
							}
							
							
							//also, update the link's email creative choice.
							if($iEmailCreativeId != ''){
								$sDeleteEmailLinkSQL = "DELETE FROM linksEmailCreative WHERE linkId = '$iId'";
								$res = dbQuery($sDeleteEmailLinkSQL);
								
								$sInsertEmailLinkSQL = "INSERT INTO linksEmailCreative (linkId, creativeId) values ('$iId','$iEmailCreativeId')";
								$res = dbQuery($sInsertEmailLinkSQL);
								mail('bbevis@amperemedia.com',__line__.": inserting into linksEmailCreative","$sInsertEmailLinkSQL");
								
							} else {
								mail('bbevis@amperemedia.com',__line__.": error","$iEmailCreativeId");
							}
							
							if ($sMore == 'yes') {
								$sPassSourceCode = substr($sPassSourceCode, 0, strlen($sPassSourceCode)-1);
								$sSourceCode = $sPassSourceCode;
							} else {
								$sSourceCode = $sPassSourceCode;
							}
				
						} else if (($sSaveClose || $sSaveNew) && ($iId) && $bKeepValues != true && !($sMessage)) {
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
							
							if ($sDisableStandardPop == '') {
								$sDisableStandardPop = 'N';
							}
							if ($sDisableExitPop == '') {
								$sDisableExitPop = 'N';
							}
							if ($sDisableAbandonedPop == '') {
								$sDisableAbandonedPop = 'N';
							}
							if ($sDisableWinManagerPop == '') {
								$sDisableWinManagerPop = 'N';
							}
							if ($sShowNonRevOffers == '') { $sShowNonRevOffers = 'N'; }
							
							if ($sRandomDomain == 'Y') {
								$sGetDomainsCountSQL = "SELECT count(*) as count FROM maskingDomains WHERE randomDomain='Y'";
								$rGetDomainsCount = dbQuery($sGetDomainsCountSQL);
								$oGetDomainsCount = dbFetchObject($rGetDomainsCount);
								$iDomainIndex = rand(intval('0'),count($_SESSION['aDomainIdArray']));
								$iDomainId = $_SESSION['aDomainIdArray'][$iDomainIndex];
							}
							
							
							$sPixelUrl = addslashes($sPixelUrl);
							//sourceCode = '$sSourceCode',
							$sEditQuery = "UPDATE links
											  SET partnerId = '$iPartnerId',
												  campaignTypeId = '$iCampaignTypeId',
												  campaignRateTypeId = '$iCampaignRateTypeId',
												  typeCode = '$sTypeCode',
												  url = '$sUrl',
												  rate = '$fRate',			
												  creative = \"$sCreative\",
												  notes = \"$sNotes\",		
												  bustFrames = '$iBustFrames',
												  expirationDate = '$sExpirationDate',
												  groupName = '$sGroupName',
												  ioId = '$ioId',
												  offerCode = '$sOfferCode',
												  siteId = '$iSiteId',
												  campaignId = '$iCampaignId',
												  flowId = '$iFlowId',
												  description = \"$sDescription\",
												  dateTimeCreated = NOW(),
												  whereToGoId = '$iRedirectUrlId',
												  emailCapture = '$sShowEmailCapture',
												  showSkip = '$sShowSkipButton',
												  stopAllPopups = '$sStopAllPopups',
												  disableStandardPop = '$sDisableStandardPop',
												  disableExitPop = '$sDisableExitPop',
												  disableAbandonedPop = '$sDisableAbandonedPop',
												  disableWinManagerPop = '$sDisableWinManagerPop',
												  domainId = '$iDomainId',
												  isPixelEnable = '$sPixelEnable',
												  pixelLocation = '$sPixelLocation',
												  pixelUrl = \"$sPixelUrl\",
												  partnerCanLogin = \"$sPartnerCanLogin\",
												  recipe4Living = \"$sRecipeForLiving\",
												  recipeId = \"$iRecipeId\",
												  foreignIPTracking = \"$sForeignIPTracking\",
												  showNonRevOffers = '$sShowNonRevOffers'
											  WHERE id = '$iId'";
							
						//mail('bbevis@amperemedia.com', __line__.": update query", "$sEditQuery");

							// start of track users' activity in nibbles 
							$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
							  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sEditQuery) . "\")"; 
							$rLogResult = dbQuery($sLogAddQuery); 
							// end of track users' activity in nibbles		
							
							
							$rResult = dbQuery($sEditQuery);
							
							if (!($rResult)) {
								$sMessage = dbError();
							} 
							
							$foreignIPHandlingSQL = "UPDATE foreignIpHandling SET
													redirectUrl = '$sForeignRedirectURL',
													isBlock = '".($sForeignIPTracking == 'block' ? 'Y' : 'N')."'
													WHERE sourceCode = '$sSourceCode'";
							
							$rResult = dbQuery($foreignIPHandlingSQL);
									
							if (!($rResult)) {
								$sMessage = dbError();
							} 
							
							
							//also, update the link's email creative choice.
							
							if($iEmailCreativeId != ''){
								$sDeleteEmailLinkSQL = "DELETE FROM linksEmailCreative WHERE linkId = '$iId'";
								$res = dbQuery($sDeleteEmailLinkSQL);
								
								$sInsertEmailLinkSQL = "INSERT INTO linksEmailCreative (linkId, creativeId) values ('$iId','$iEmailCreativeId')";
								$res = dbQuery($sInsertEmailLinkSQL);
								//mail('bbevis@amperemedia.com',__line__.": inserting into linksEmailCreative","$sInsertEmailLinkSQL");
								
							} else {
								//mail('bbevis@amperemedia.com',__line__.": error","$iEmailCreativeId");
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
									
									// start of track users' activity in nibbles
									$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sEditPixelQuery) . "\")";
									$rLogResult = dbQuery($sLogAddQuery);
									// end of track users' activity in nibbles
								}
							}
							
							if (is_array($aNewPixels)) {
								// Insert all newly added poll options
								while (list($key, $value) = each($aNewPixels)) {
									$sAddPixelQuery = "INSERT INTO pixels(sourceCode, pageId, pixelHtml, alwaysDisplay)
													VALUES('$sSourceCode', '".$aNewPixelsPageId[$key]."','$value', '".$aNewAlwaysDisplay[$key]."')";
									$rAddPixelResult = dbQuery($sAddPixelQuery);
									
									// start of track users' activity in nibbles
									$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
									  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddPixelQuery) . "\")";
									$rLogResult = dbQuery($sLogAddQuery);
									// end of track users' activity in nibbles
								}
							}
						}
					}
				//}
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


if ($sSaveClose && $sMessage == '') {
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
	
	//mail('bbevis@amperemedia.com', __file__.":".__line__, __file__.":".__line__.': this is where all of the close stuff comes in.');
} else if ($sSaveNew && $sMessage == '') {
	if ($bKeepValues != true) {
		// don't reload the list page if show active was checked. As page loading takes time 
		if ($sShowActive != 'Y') {
			$sReloadWindowOpener = "<script language=JavaScript>
								//window.opener.location.href='".$sPageReloadUrl."';
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
		$sCreative = '';
		$sNotes = '';		
		$iSeqNo = '';
		$iBustFrames = '';
		$sExpirationDate = '';
		$iExpDay = '';
		$iExpMonth = '';
		$iExpYear = '';
		$ioId = '';
		$sOfferCode = '';
		$iSiteId = '';
		$iCampaignId = '';
		$iFlowId = '';
		$sDescription = '';
		$iRedirectUrlId = '';
		$sShowEmailCapture = '';
		$sShowSkipButton = '';
		$sStopAllPopups = '';
		$sDisableStandardPop = '';
		$sDisableExitPop = '';
		$sDisableAbandonedPop = '';
		$sDisableWinManagerPop = '';
		$sShowNonRevOffers = '';
		$iDomainId = '';
		$sPartnerCanLogin = '';
		$sRecipeForLiving = '';
		$iRecipeId = '';
		$sPixelEnable = '';
		$sPixelLocation = '';
		$sPixelUrl = '';
		$sDefaultTitle = '';
		$sForeignIPTracking = '';
		$sForeignRedirectURL = '';
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
		$sCreative = ascii_encode($oSelectRow->creative);
		$sNotes = ascii_encode($oSelectRow->notes);
		$iBustFrames = $oSelectRow->bustFrames;
		$sExpirationDate = $oSelectRow->expirationDate;
		$iExpYear = substr($sExpirationDate,0,4);
		$iExpMonth = substr($sExpirationDate,5,2);
		$iExpDay = substr($sExpirationDate,8,2);
		$ioId = $oSelectRow->ioId;
		$sOfferCode = $oSelectRow->offerCode;
		
		$iSiteId = $oSelectRow->siteId;
		$iCampaignId = $oSelectRow->campaignId;
		$iFlowId = $oSelectRow->flowId;
		$sDescription = $oSelectRow->description;
		$iRedirectUrlId = $oSelectRow->whereToGoId;
		$sShowEmailCapture = $oSelectRow->emailCapture;
		$sShowSkipButton = $oSelectRow->showSkip;
		$sStopAllPopups = $oSelectRow->stopAllPopups;
		$sDisableStandardPop = $oSelectRow->disableStandardPop;
		$sDisableExitPop = $oSelectRow->disableExitPop;
		$sDisableAbandonedPop = $oSelectRow->disableAbandonedPop;
		$sDisableWinManagerPop = $oSelectRow->disableWinManagerPop;
		$iDomainId = $oSelectRow->domainId;
		$sDefaultTitle = $oSelectRow->defaultTitle;
		$sShowNonRevOffers = $oSelectRow->showNonRevOffers;

	
		$sPixelEnable = $oSelectRow->isPixelEnable;
		$sPixelLocation = $oSelectRow->pixelLocation;
		$sPixelUrl = $oSelectRow->pixelUrl;
		$sPartnerCanLogin = $oSelectRow->partnerCanLogin;
		$sRecipeForLiving = $oSelectRow->recipe4Living;
		$iRecipeId = $oSelectRow->recipeId;
		$sForeignIPTracking = $oSelectRow->foreignIPTracking;
		
		$ForeignIPTrackingSQL = "SELECT * FROM foreignIpHandling WHERE sourceCode = '$sSourceCode'";
		$rRes = dbQuery($ForeignIPTrackingSQL);
		$oFIPH = dbFetchObject($rRes);
		
		$sForeignRedirectURL = $oFIPH->redirectUrl;
		
		$sDisablePixel = '';
		if ($sPixelEnable != 'Y') {
			$sDisablePixel = ' disabled ';
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
	//$sCustomFrameContent = ascii_encode(stripslashes($sCustomFrameContent));
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
				  ORDER BY id,groupName ASC";
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
$sIOOptions = "<select name='ioId' id='ioId'><option value=''>Select One</option>\n";
$sIOSearchArray = array();
$rIOResult = dbQuery($sIOQuery);
$i=0;
while($oIO = dbFetchObject($rIOResult)){
	//name, & id
	array_push($sIOSearchArray ,"\"$oIO->id ( $oIO->type $oIO->companyName )\"");
	$sIOOptions .= "<option value='$oIO->id' ".($oIO->id == $ioId ? 'selected' : '').">$oIO->id ($oIO->type $oIO->companyName)</option>";
	$i++;
}

$sIOOptions .= "</select>";


// Prepare typeCode options for Partner Selection box
$sTypeCodeQuery = "SELECT *
				  FROM   typeCodes
				  ORDER BY id DESC";
$rTypeCodeResult = dbQuery($sTypeCodeQuery);

while ( $oTypeCodeRow = dbFetchObject($rTypeCodeResult)) {
	
	if ($oTypeCodeRow->typeCode == $sTypeCode) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sTypeCodeOptions .="<option value='".$oTypeCodeRow->typeCode."' $sSelected>".$oTypeCodeRow->title;
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


if ($iBustFrames == 1 || !($iId)) {
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
	$sPageTitleLink = "<a class=header href='JavaScript:void(window.open(\"editPageTitle.php?iMenuId=$iMenuId&iId=$iId\", \"AddGroup\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Page Title</a>";
	
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



$sGetSiteId = "SELECT id,siteName FROM sites order by siteName ASC";
$rGetSiteIdResult = mysql_query($sGetSiteId);
$sSiteIdOptions = "<option value=''>";
$sSiteIdOptions .= "<option value='' onclick='createNewSite();'>CREATE NEW SITE";
while ($oSiteIdRow = mysql_fetch_object($rGetSiteIdResult)) {
	if ($oSiteIdRow->id == $iSiteId) {
		$sSiteIdSelected = "selected";
	} else {
		$sSiteIdSelected = "";
	}
	$sSiteIdOptions .= "<option value='$oSiteIdRow->id' $sSiteIdSelected>$oSiteIdRow->siteName";
}


$sGetFlowId = "SELECT id,flowName FROM flows 
				WHERE nibblesVersion='2'
				order by flowName ASC";
$rGetFlowIdResult = mysql_query($sGetFlowId);
$sFlowIdOptions = "<option value=''>";
$sFlowIdOptions .= "<option value='' onclick='createNewFlow();'>CREATE NEW FLOW";
while ($oFlowRow = mysql_fetch_object($rGetFlowIdResult)) {
	if ($oFlowRow->id == $iFlowId) {
		$sFlowSelected = "selected";
	} else {
		$sFlowSelected = "";
	}
	$sFlowIdOptions .= "<option value='$oFlowRow->id' $sFlowSelected>$oFlowRow->flowName";
}



$sGetRedirectId = "SELECT id,name,redirectUrl,isDefault FROM whereToGo order by name ASC";
$rGetRedirectIdResult = mysql_query($sGetRedirectId);
$sRedirectUrlOptions = "<option value=''>";
$sRedirectUrlOptions .= "<option value='' onclick='createNewRedirect();'>CREATE NEW REDIRECT URL";
if(($iRedirectUrlId == '')||($iRedirectUrlId == NULL)){
	$iRedirectUrlId = 'DEFAULT';
}

while ($oRedirectRow = mysql_fetch_object($rGetRedirectIdResult)) {
	if (($oRedirectRow->id == $iRedirectUrlId)||($iRedirectUrlId == 'DEFAULT' && $oRedirectRow->isDefault == 'Y')) {
		$sRedirectSelected = "selected";
	} else {
		$sRedirectSelected = "";
	}
	$sRedirectUrlOptions .= "<option value='$oRedirectRow->id' $sRedirectSelected>$oRedirectRow->name";
}



$_SESSION['aDomainIdArray'] = array();
$sGetDomainId = "SELECT * FROM maskingDomains order by domainName ASC";
$rGetDomainIdResult = mysql_query($sGetDomainId);
$sDomainIdOptions = "<option value=''>";
while ($oDomainRow = mysql_fetch_object($rGetDomainIdResult)) {
	if ($oDomainRow->id == $iDomainId) {
		$sDomainSelected = "selected";
	} else {
		$sDomainSelected = "";
	}
	$sDomainIdOptions .= "<option value='$oDomainRow->id' $sDomainSelected>$oDomainRow->domainName";
	
	if ($oDomainRow->randomDomain == 'Y') {
		array_push($_SESSION['aDomainIdArray'],$oDomainRow->id);
	}
}


$sGetCampaignId = "SELECT id,campaignName FROM campaigns order by campaignName ASC";
$rGetCampaignIdResult = mysql_query($sGetCampaignId);
$sCampaignOptions = "<option value=''>";
$sCampaignOptions .= "<option value='' onclick='createNewCamp();'>CREATE NEW CAMPAIGN";
while ($oCampRow = mysql_fetch_object($rGetCampaignIdResult)) {
	if ($oCampRow->id == $iCampaignId) {
		$sCampSelected = "selected";
	} else {
		$sCampSelected = "";
	}
	$sCampaignOptions .= "<option value='$oCampRow->id' $sCampSelected>$oCampRow->campaignName";
}

//for the "number of links" feature, we're going to need to get the number of links we already have.

if($sGroupName != ''){
	$sNumberOfLinksInGroupSQL = "SELECT count(*) as count FROM links WHERE groupName = '$sGroupName'";
	$rNumberOfLinks = dbQuery($sNumberOfLinksInGroupSQL);
	$oNumberOfLinks = dbFetchObject($rNumberOfLinks);
	$iNumberOfLinks = $oNumberOfLinks->count;
	$oldSNoOfLinksToCreate = $iNumberOfLinks;
} else {
	$oldSNoOfLinksToCreate = 0;
}

$sNoOfLinksToCreate = '';
for ($i=1;$i<=50;$i++) {
	$selected = ($oldSNoOfLinksToCreate == $i ? 'selected' : '');
	$sNoOfLinksToCreate .= "<option value='$i' $selected>$i</option>";
}

// uncheck random domain checkbox
$sRandomDomain = '';
if($iDomainId != ''){
	$sRandomDomainChecked = '';
	$sDomainIdEnabled = '';
} else {
	$sRandomDomainChecked = 'checked';
	$sDomainIdEnabled = 'disabled';
}

$sRecipeIdOptions = "";
$sRecipeSQL = "SELECT * FROM recipes";
$rRecipes = dbQuery($sRecipeSQL);
while($oRecipe = dbFetchObject($rRecipes)){
	$sRecipeIdOptions .= "<option value='$oRecipe->id' ".($oRecipe->id == $iRecipeId ? 'selected' : '').">$oRecipe->title</option>";
}

if($sRecipeForLiving == 'Y'){
	$sRecipeForLivingDisabled = '';
} else {
	$sRecipeForLivingDisabled = 'disabled';	
}

if($sPartnerCanLogin == 'Y' || $sPartnerCanLogin == ''){
	$sPartnerLoginYesChecked = 'checked';
	$sPartnerLoginNoChecked = '';
} else {
	$sPartnerLoginYesChecked = '';
	$sPartnerLoginNoChecked = 'checked';
}

if($sRecipeForLiving == 'Y'){
	$sRecipeYesChecked = 'checked';
	$sRecipeNoChecked = '';
} else {
	$sRecipeYesChecked = '';
	$sRecipeNoChecked = 'checked';	
}

$sForeignBlockChecked = '';
$sForeignRedirectChecked = '';
$sForeignLogChecked = '';
switch($sForeignIPTracking){
	case 'block':
		$sForeignBlockChecked = 'checked';
		break;
	case 'redirect':
		$sForeignRedirectChecked = 'checked';
		break;
	case 'log':
		$sForeignLogChecked = 'checked';
		break;
	default:
		$sForeignRedirectChecked = 'checked';
		break;		
}


// SET DEFAULT VALUE
if ($sShowEmailCapture == '') { $sShowEmailCapture = 'Y'; }
if ($sShowSkipButton == '') { $sShowSkipButton = 'N'; }
if ($sStopAllPopups == '') { $sStopAllPopups = 'N'; }

if ($sCategoryIncExc == '') { $sCategoryIncExc = 'E'; }
if ($sOfferIncExc == '') { $sOfferIncExc = 'E'; }

if ($sPixelLocation == '') { $sPixelLocation = 'E'; }

if ($sShowNonRevOffers == '') { $sShowNonRevOffers = 'N'; }


include("../../includes/adminAddHeader.php");
echo "<script language='javascript'>\nvar IOSearchArray = Array(".join(',',$sIOSearchArray).");</script>\n";
echo "<script language='javascript' src='/libs/ajax.js'></script>\n";

?>
<script language=JavaScript>
function emailCreativeChange(value, linkId){
	var emailCreatives = new AmpereMedia();
	var pathString = '/admin/linksMgmnt/linksEmailCreativeSelect.php?campaignId='+value;
	if(linkId != ''){
		pathString += '&linkId='+linkId;
	}
	//alert(pathString);
	result = emailCreatives.send(pathString,'');
	//alert(result);
	div = document.getElementById('emailCreative');
	div.innerHTML = result;
}

//alert(IOSearchArray);
function addPx(addPixel) {
	document.forms[0].elements['iAddPixel'].value=addPixel;
	document.forms[0].submit();
}

function createNewSite () {
	window.open("../sitesMgmnt/addSites.php?iMenuId=261",'newSite','height=300,width=500');
}

function createNewFlow () {
	window.open("../flowMgmnt/addFlow.php?iMenuId=260",'newFlow','height=300,width=600');
}

function createNewRedirect () {
	window.open("../whereToGoMgmnt/addRedirect.php?iMenuId=257",'newRedirect','height=300,width=600');
}

function createNewDomain () {
	window.open("../domainsMgmnt/addDomain.php?iMenuId=258",'newDomain','height=300,width=600');
}

function createNewCamp () {
	window.open("../campaignsManagement/addCampaign.php?iMenuId=259",'newCamp','height=700,width=600');
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
function enableDisablePixel() {
	document.form1.sPixelLocation[0].disabled = true;
	document.form1.sPixelLocation[1].disabled = true;
	document.form1.sPixelUrl.disabled = true;
	
	if (document.form1.sPixelEnable.checked == true) {
		document.form1.sPixelLocation[0].disabled = false;
		document.form1.sPixelLocation[1].disabled = false;
		document.form1.sPixelUrl.disabled = false;
	}
}
function toggleDomain(){
	if(document.form1.sRandomDomain.checked){
		document.form1.iDomainId.disabled = true;
	} else {
		document.form1.iDomainId.disabled = false;
	}
}


function toggleRecipes(value){
	if(value == 'Y'){
		document.form1.iRecipeId.disabled = false;
	} else {
		document.form1.iRecipeId.disabled = true;
	}
}

function toggleForeignRedirect(value){
	if(value == 'redirect'){
		document.form1.sForeignRedirectURL.disabled = false;
	} else {
		document.form1.sForeignRedirectURL.disabled = true;
	}
}
toggleForeignRedirect('<?php echo $sForeignIPTracking;?>');

function IOSearch(value){
	var val = value.toUpperCase();
	var l = document.form1.ioId.options.length;
	for(i=0;i<l;i++){
		iostr = IOSearchArray[i].toUpperCase();
		if(iostr.match(val)){
			//alert(IOSearchArray[i]);
			document.getElementById('ioId').selectedIndex = i + 1;
			//document.form1.ioId.selectedIndex = i;
			//document.form1.ioId.options[i].selected = true;
			break;
			//alert(document.getElementById('ioId').options[document.getElementById('ioId').selectedIndex].value);
		}
	}
}

</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<?php echo $sDisplaySourceCode;?>
		
		<tr><td>Group</td><td><select name=sGroupName><?php echo $sGroupOptions;?></select>
		 				&nbsp; &nbsp; <?php echo $sAddGroupLink;?>&nbsp;&nbsp;<font color="red">required</font>&nbsp; &nbsp;<br>Note: If you add new group, you must hit F5 to reload selection list.</td></tr>

		<input type='hidden' name='oldSNoOfLinksToCreate' value='<?php echo $iNumberOfLinks;?>'>
		<tr><td>No. of Links To Create: </td>
			<td><select name='sNoOfLinksToCreate'>
			<?php echo $sNoOfLinksToCreate;?>
		</select></td></tr>
		
		
		<tr><td>Masking Domain</td>
		<td>Random: <input type="checkbox" name="sRandomDomain" value="Y" <?php echo $sRandomDomainChecked;?> onClick='toggleDomain();'>
		&nbsp; &nbsp;&nbsp; &nbsp;
		<select name='iDomainId' <?php echo $sDomainIdEnabled;?>>
		<?php echo $sDomainIdOptions;?>
		</select>
		 &nbsp;&nbsp;<font color="red">required</font>
		</td>
		</tr>
		
		
		
		
		<tr><td>Site Name</td>
		<td><select name='iSiteId'>
		<?php echo $sSiteIdOptions;?>
		</select>
		 &nbsp;&nbsp;<font color="red">required</font>
		</td>
		</tr>
		
		<tr><td>Campaign Name</td>
		<td><select name='iCampaignId' onChange="emailCreativeChange(this.value,<?php echo "'$iId'";?>);">
		<?php echo $sCampaignOptions;?>
		</select>
		 &nbsp;&nbsp;<font color="red">required</font>
		</td></tr>
		
		<tr><td>Default Email Creative</td>
		<td><div id='emailCreative'><select name='iEmailCreativeId'></select></div></td>
		</tr>


		<tr><td>Flow Name</td>
		<td><select name='iFlowId'>
		<?php echo $sFlowIdOptions;?>
		</select>
		 &nbsp;&nbsp;<font color="red">required</font>
		</td>
		</tr>
		
		<tr><td>Redirect URL</td>
		<td><select name='iRedirectUrlId'>
		<?php echo $sRedirectUrlOptions;?>
		</select>
		 &nbsp;&nbsp;<font color="red">required</font>
		</td>
		</tr>		
		
		<tr><td>Show Email Capture Page</td>
		<td><input type='radio' name='sShowEmailCapture' value='Y' <?php if($sShowEmailCapture=='Y') { echo 'checked'; }?>> Yes
			&nbsp;&nbsp;
			<input type='radio' name='sShowEmailCapture' value='N' <?php if($sShowEmailCapture=='N') { echo 'checked'; }?>> No
		</td>
		</tr>
		
		<tr><td>Show Skip Button In Header</td>
		<td><input type='radio' name='sShowSkipButton' value='Y' <?php if($sShowSkipButton=='Y') { echo 'checked'; }?>> Yes
			&nbsp;&nbsp;
			<input type='radio' name='sShowSkipButton' value='N' <?php if($sShowSkipButton=='N') { echo 'checked'; }?>> No
		</td></tr>
		
		<tr><td>Stop All Popups</td>
		<td><input type='radio' name='sStopAllPopups' value='Y' <?php if($sStopAllPopups=='Y') { echo 'checked'; }?>> Yes
			&nbsp;&nbsp;
			<input type='radio' name='sStopAllPopups' value='N' <?php if($sStopAllPopups=='N') { echo 'checked'; }?>> No
		</td></tr>
		

		<tr><td>Disable Standard Popup</td>
		<td><input type="checkbox" value="Y" name="sDisableStandardPop" <?php if($sDisableStandardPop=='Y') { echo 'checked'; }?>>
		</td></tr>
		
		
		<tr><td>Disable Exit Popup</td>
		<td><input type="checkbox" value="Y" name="sDisableExitPop" <?php if($sDisableExitPop=='Y') { echo 'checked'; }?>>
		</td></tr>
		
		<tr><td>Disable Abandoned Popup</td>
		<td><input type="checkbox" value="Y" name="sDisableAbandonedPop" <?php if($sDisableAbandonedPop=='Y') { echo 'checked'; }?>>
		</td></tr>
		
		<tr><td>Disable Window Manager Popup</td>
		<td><input type="checkbox" value="Y" name="sDisableWinManagerPop" <?php if($sDisableWinManagerPop=='Y') { echo 'checked'; }?>>
		</td></tr>
		
		
		
		<tr><td>Show Non-Revenue Offers</td>
		<td><input type="radio" name="sShowNonRevOffers" value="Y" <?php if($sShowNonRevOffers=='Y') { echo 'checked'; } ?>>Yes
			&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sShowNonRevOffers" value="N" <?php if($sShowNonRevOffers=='N') { echo 'checked'; } ?>>No
		</td>
		</tr>
		
	
		<tr><td>Description</td>
			<td><textarea name=sDescription rows=3 cols=40><?php echo $sDescription;?></textarea>
		</td></tr>
		
		
		<?php if (!($iId)) { ?>
		<tr><td>Page Title</td>
			<td><textarea name=sDefaultTitle rows=3 cols=40><?php echo $sDefaultTitle;?></textarea></td></tr>
		<?php } else { ?>
		<tr><td>Page Title</td>
			<td><?php echo $sPageTitleLink; ?></td></tr>
		<?php } ?>
			
			
		<tr><td colspan="2">&nbsp;</td></tr>
		
		<tr><td>Enable Affiliate Pixel</td>
		<td><input type="checkbox" value="Y" name="sPixelEnable" <?php if($sPixelEnable=='Y') { echo 'checked'; }?> 
				onclick='enableDisablePixel();'>
		</td></tr>
		
		
		
		<tr><td>Fire Pixel Location</td>
		<td><input type='radio' name='sPixelLocation' value='E' <?php if($sPixelLocation=='E') { echo 'checked'; }?> 
			<?php echo $sDisablePixel; ?>>
			After Email Capture Page&nbsp;&nbsp;
			<input type='radio' name='sPixelLocation' value='R' <?php if($sPixelLocation=='R') { echo 'checked'; }?> 
			<?php echo $sDisablePixel; ?>>
			After User Registration Page
		</td></tr>
		
		
		<tr><td>Affiliate Pixel URL</td>
			<td><textarea name=sPixelUrl <?php echo $sDisablePixel; ?> rows=3 cols=40><?php echo $sPixelUrl;?></textarea>
		</td></tr>
		
		
		
	<!--	<tr><td>Categories To Include or Exclude</td>
		<td colspan=2><select name='aCategories[]' multiple size=10>
		<?php //echo $sCategoriesOptions;?>
		</select>
			<input type='radio' name='sCategoryIncExc' value='I' <?php //if($sCategoryIncExc=='I') { echo 'checked'; }?>> Include
			&nbsp;&nbsp;
			<input type='radio' name='sCategoryIncExc' value='E' <?php //if($sCategoryIncExc=='E') { echo 'checked'; }?>> Exclude
		</td></tr>
		
		
		<tr><td>Offers To Include or Exclude</td>
		<td colspan=2><select name='aOffers[]' multiple size=10>
		<?php //echo $sOfferOptions;?>
		</select>
			<input type='radio' name='sOfferIncExc' value='I' <?php //if($sOfferIncExc=='I') { echo 'checked'; }?>> Include
			&nbsp;&nbsp;
			<input type='radio' name='sOfferIncExc' value='E' <?php //if($sOfferIncExc=='E') { echo 'checked'; }?>> Exclude
		</td></tr>-->
		
		<tr><td colspan="2"><hr size="2"></td></tr>
		
		<tr><td>Recipe For Living</td>
			<td><input type='radio' name='sRecipeForLiving' value='Y' <?php echo $sRecipeYesChecked;?> onClick='toggleRecipes(this.value);'>Yes <input type='radio' name='sRecipeForLiving' value='N' <?php echo $sRecipeNoChecked;?> onClick='toggleRecipes(this.value);'>No</td>
		
		</tr>
		
		<tr><td>Recipe</td>
			<td><select name='iRecipeId' <?php echo $sRecipeForLivingDisabled;?> >
				<?php echo $sRecipeIdOptions;?>
				</select>
			</td>
		</tr>
		
		<tr><td colspan="2"><hr size="2"></td></tr>
		
		<tr><td><b>Foreign IP Tracking</b></td></tr>
		<tr><td>For this link, we should do the following for Foreign IPs:</td>
			<td><input type='radio' name='sForeignIPTracking' value='block' <?php echo $sForeignBlockChecked;?> onClick='toggleForeignRedirect(this.value);'>Block</td></tr>
		<tr><td></td><td><input type='radio' name='sForeignIPTracking' value='redirect' <?php echo $sForeignRedirectChecked;?> onClick='toggleForeignRedirect(this.value);'>Redirect to <input name='sForeignRedirectURL' value='<?php echo $sForeignRedirectURL;?>'></td></tr>
		<tr><td></td><td><input type='radio' name='sForeignIPTracking' value='log' <?php echo $sForeignLogChecked;?> onClick='toggleForeignRedirect(this.value);'>Log Only</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan=2><b>Note: </b>When a link is saved with the "Redirect to" option selected, and no entry is made into the "Redirect to" field, the default redirect URL will be used for foreign traffic.</td></tr>
		</tr>
		
		
		<tr><td colspan="2"><hr size="2"></td></tr>

	
		<tr><TD>Partner Company</td><td><select name=iPartnerId><?php echo $sPartnerOptions;?></select>
		 				&nbsp; &nbsp; <?php echo $addPartnerLink;?>
		 &nbsp;&nbsp;<font color="red">required</font></td></tr>
		 <tr><td></td><td>
		 	&nbsp;&nbsp; Search IO#:<input name='IOSearcher' onChange='IOSearch(this.value);'></td></tr>
		 <tr><td>IO #</td>
		 	<td><?php echo $sIOOptions;?>
		 	&nbsp;&nbsp;<font color="red">required</font>
		 	   </td>
		 </tr>
		
		<tr><TD>Type Code</td><td><select name=sTypeCode><?php echo $sTypeCodeOptions;?></select>
		 &nbsp;&nbsp;<font color="red">required</font></td></tr>
		
		<!--
		<tr><TD>URL</td><td><input type=text name=sUrl value='<?php //echo $sUrl;?>' <?php //echo $sUrlDisable; ?> size=45>
		&nbsp;&nbsp;Required if Campaign Type is other than API.
		</td></tr>-->
		
		
		
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
		
		<tr><td></td><td><b>Expiration Date</b> is currently used only for reporting and billing purposes. <br>Your selection of <b>Expiration Date</b> will have no effect on the functionality of your links. </td>
		</tr>
		
		<tr><td>Partners can log in and get new links:</td>
		<td><input type='radio' name='sPartnerCanLogin' value='Y' <?php echo $sPartnerLoginYesChecked;?>>Yes <input type='radio' name='sPartnerCanLogin' value='N' <?php echo $sPartnerLoginNoChecked;?>>No</td>
		</tr>
		
</table>

<script language=JavaScript>
	enableDisablePixel();
	emailCreativeChange(document.form1.iCampaignId.value,<?php echo "'$iId'";?>);
	toggleRecipes('<?php echo $sRecipeForLiving;?>');
</script>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
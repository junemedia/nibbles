<?php

/*********

Script to Display List/Delete Partner Companies

**********/
// kdn_inv count rec: phil@myfree.com, keith@amperemedia.com, bulebosh.becky@davison54.com, michaels.jude@davison54.com, leads@amperemedia.com
// KDN_INV form post url https://www.davison54.com/tools/leadcollect/index.php

//counts phil@myfree.com, jr@myfree.com, leads@amperemedia.com
//lead fred@amperemedia.com, leads@amperemedia.com
/*
 gpg -r "Paul Thau <pthau@SingerDirect.com>" --always-trust --cipher-a
lgo cast5 --output /home/sites/www_popularliving_com/html/admin/leads/20050110/o
ffers/SSR_HEALTH/SSR_HEALTH_01_10_2005_Ampere.gpg --encrypt /home/sites/www_popu
larliving_com/html/admin/leads/20050110/offers/SSR_HEALTH/SSR_HEALTH_01_10_2005_
Ampere.txt

robo counts - phil@myfree.com, Brad.Becker@ahahome.com, leads@AmpereMedia.com
robo leads - batchprocess@ahahome.com, leads@amperemedia.com

*/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");


set_time_limit(5000);

session_start();

$sPageTitle = "Nibbles - Process Leads";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (!($sUseCurrentTable)) {
	$sOtDataTable = "otDataHistory";
	$sUserDataTable = "userDataHistory";
} else {
	$sOtDataTable = "otData";
	$sUserDataTable = "userData";
}

$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
	
$sRunDate = "$iCurrMonth-$iCurrDay-$iCurrYear";


// get today's date for leads folder name
$sToday = date(Y).date(m).date(d);
$iJulianDays = date(z) + 1;

// get today's leads folder
$sTodaysLeadsFolder = "$sGblLeadFilesPath/$sToday";

// set the reRun folder
$sRerunFolder = "$sGblLeadFilesPath/reRun";

// set today's reRun folder
$sTodaysRerunFolder = "$sRerunFolder/$sToday";


// Check user permission to access this page

if ($sExportData || $sImportData || $sProcessLeads || $sSendLeads || $sSendFormPostLeads) {
	
	if ($sTestMode && $sTestProcessingEmailRecipients == '') {
		$sErrorInSendingLeads = "Please Enter Test Email Recipients For Test Leads...";
		
	} else {
		if ($sExportData) {
			
			//mark all the leads as test leads which are collected in Test mode
			$sOtDataUpdateQuery = "UPDATE otDataHistory
									 SET    processStatus = 'R',
											reasonCode = 'tst',
											dateTimeProcessed = now(),
											sendStatus = 'N',
											dateTimeSent = now()
									 WHERE  mode = 'T'";
							
			$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);		
			
						
			// export data as csv format
			$sUserDataQuery = "SELECT userDataHistory.*
						FROM   userDataHistory
						WHERE  (postalVerified IS NULL
						OR postalVerified = '')";
					
						$rUserDataResult = dbQuery($sUserDataQuery);
			echo dbError();
			
			if ($rUserDataResult) {
				while ($oUserDataRow = dbFetchObject($rUserDataResult)) {
					$sUserDataToExport .= "\"$oUserDataRow->email\",\"$oUserDataRow->address\",\"$oUserDataRow->address2\",\"$oUserDataRow->city\",\"$oUserDataRow->state\",\"$oUserDataRow->zip\"\r\n";
				}
			
				dbFreeResult($rUserDataResult);
			}
			
			
			header("Content-type: text/plain");
			header("Content-Disposition: attachment; filename=otData.txt");
			header("Content-Description: Text output");
			echo $sUserDataToExport;
			exit();
			
			/** process steps
			1. get all the active and live offers and its lead delivery details one by one
			2. get the leads,  for the offer,
			3. validate data
			4. process postal verify on the data.
			4. update the lead data columns like postalVerified, processStatus, dateTimeProcessed etc.
			4. deliver the lead according to lead delivery spec.
			**/
			
			
		} else if ($sImportData) {
			
			if ($_FILES['fImportFile']['tmp_name'] && $_FILES['fImportFile']['tmp_name']!="none") {
			
				$sUploadedFileName = $_FILES['fImportFile']['tmp_name'];
				
				
				$sFileName = "tempOtData.csv";
				$sNewFilePath  = "$sGblWebRoot/temp/$sFileName";
				
				move_uploaded_file( $sUploadedFileName, $sNewFilePath);
				chmod($sNewFilePath, 0777);
				
			
				$sImportQuery = "LOAD DATA INFILE '$sNewFilePath'
						 INTO TABLE tempUserData
						FIELDS TERMINATED BY ',' 
						OPTIONALLY ENCLOSED BY '\"'
						LINES TERMINATED BY '\n'
						(email, address, address2, city, state, zip, postalVerified, postalErrors)";
				
				$rImportResult = dbQuery($sImportQuery);
				echo $sImportQuery. dbError();
				
				// Mark leads as valid If it is invalidated using 'M' (Multimatched)
				$sMultiUpdateQuery = "UPDATE tempUserData
									 SET    postalVerified = 'V'											
									 WHERE  postalVerified = 'M'";
				$rMultiUpdateResult = dbQuery($sMultiUpdateQuery);
											
				
				if ($rImportResult) {
					// update otData table
					$sTempDataQuery = "SELECT *
							 FROM   tempUserData";
					$rTempDataResult = dbQuery($sTempDataQuery);
					while ($oTempDataRow = dbFetchObject($rTempDataResult)) {
						$sTempEmail = $oTempDataRow->email;
						$sTempPostalVerified = $oTempDataRow->postalVerified;
						$sTempPostalErrors = $oTempDataRow->postalErrors;
						
						// update userData table for postalVerified column
						
						$sUpdateQuery = "UPDATE userDataHistory
										 SET    postalVerified = '$sTempPostalVerified',
												postalErrors = '$sTempPostalErrors'
										 WHERE  email = '$sTempEmail'
										 AND    (postalVerified IS NULL
										 OR      postalVerified = '') ";
						
						$rUpdateResult = dbQuery($sUpdateQuery);
						echo dbError();
						
						// update otDataHistory table
						if ($sTempPostalVerified != 'V') {
						$sOtDataUpdateQuery = "UPDATE otDataHistory
								 SET    processStatus = 'R',
										reasonCode = 'npv',
										dateTimeProcessed = now(),
										sendStatus = 'N',
										dateTimeSent = now()
								 WHERE  email = '$sTempEmail'
								 AND    processStatus IS NULL ";
						
						$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);
						
						}
						
					}
					
					// delete records from temp table
					$sTempDeleteQuery = "DELETE FROM tempUserData";
					$rTempDeleteResult = dbQuery($sTempDeleteQuery);
					
					// mark 3401 leads as rejected
						
					$sTestLeadsQuery = "SELECT *
		  							   FROM   userDataHistory
		  							   WHERE address like '3401 DUNDEE%'";
		  			$rTestLeadsResult = dbQuery($sTestLeadsQuery);
		  			
		  			if ($rTestLeadsResult) {
			  			while ($oTestLeadsRow = dbFetchObject($rTestLeadsResult)) {
			  				$sTestEmail = $oTestLeadsRow->email;
			  				$sOtDataUpdateQuery = "UPDATE otDataHistory
									 SET    processStatus = 'R',
											reasonCode = 'tst',
											dateTimeProcessed = now()
									 WHERE  email = '$sTestEmail'";
							
						$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);					
		  				}
		  			}
															
					
					@unlink($sNewFilePath);
				} else {
					
					echo "<BR>Error in importing postal verified data  ...";
				}
				
				
				
			} else {
				
				$sMessage = "Please Select The .csv File to Import The Postal Verified Data...";
			}
			
		} else if ($sProcessLeads) {
			
			// get the offers list which is not grouped
			if ($sProcessOption == 'processOne') {
				$sOffersQuery = "SELECT offers.*
				 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
				 WHERE  offers.offerCode = offerLeadSpec.offerCode				
				 AND    activeDateTime <= now() 
				 AND    lastLeadDate >= CURRENT_DATE 
				 AND	(  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )";
				if ($sOfferCode != '') {
				 	$sOffersQuery .= "AND    offers.offerCode = '$sOfferCode'";
				} else {
					$sOffersQuery .= "AND    offerLeadSpec.leadsGroupId = '$iGroupId'";
				}
				 
				 $sOffersQuery .= "ORDER BY offerCode"; 
				$rOffersResult = dbQuery($sOffersQuery);				
				if ( dbNumRows($rOffersResult) == 0 ) {
					$sMessage = "Leads Processing is not scheduled for this Offer on today OR offer is not an active offer.
						<script language=JavaScript>
		alert('Leads Processing is not scheduled for this Offer on today OR offer is not an active offer');
						</script>";
					
				}			
			}
			
			// include custom validation script here
			
			include("$sGblIncludePath/customProcessing.php");		
			
			
			
			if ($sProcessOption == "rerun" || $sProcessOption == "rerunOne") {
				// get offers whether active or inactive
				$sRerunStartDate = 	$iStartYear."-".$iStartMonth."-".$iStartDay;
				$sRerunEndDate = 	$iEndYear."-".$iEndMonth."-".$iEndDay;
				
				$sOffersQuery = "SELECT offerLeadSpec.*
						 FROM   offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id";			
				if ($sProcessOption == "rerunOne") {
					if ($sOfferCode != '') {
						$sOffersQuery .= " WHERE offerCode = '$sOfferCode'";
					} else if ($iGroupId != '') {
						$sOffersQuery .= " WHERE groupId = '$iGroupId'";
					} else {
						$sMessage = "You must select either an offer or a group to Rerun One...";
						$bKeepValues = "true";
						$sOffersQuery = '';
					}
				}
				
				// create rerun folder if not there
				if (! is_dir("$sRerunFolder")) {
					mkdir("$sRerunFolder", 0777);
					chmod("$sRerunFolder", 0777);
				}
				
				$sTodaysLeadsFolder = $sTodaysRerunFolder;
				
			} else {
				// get all active offers
				$sOffersQuery = "SELECT offerLeadSpec.*
					 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					 WHERE  offers.offerCode = offerLeadSpec.offerCode
					 AND    activeDateTime <= now() 
					 AND    lastLeadDate >= CURRENT_DATE 
					 AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )";
				
				if ($sProcessOption == "processOne") {
					if ($sOfferCode != '') {
						$sOffersQuery .= " AND  offers.offerCode='$sOfferCode'";
					} else if($iGroupId != '') {
						$sOffersQuery .= " AND offerLeadSpec.leadsGroupId = '$iGroupId'";
					} else {
						$sMessage = "You must select either an offer or a group to Process One...";
						$bKeepValues = "true";
						$sOffersQuery = '';
					}
				}
				
			}
			
			// get the offer list/ one offer to get leads for
			if ($sOffersQuery != '') {
				
				$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
				$rOffersResult = dbQuery($sOffersQuery);
				echo dbError();
				
				while ($oOffersRow = dbFetchObject($rOffersResult)) {
					
					
					$sLeadsData = '';
					
					$iTempDeliveryMethodId = $oOffersRow->deliveryMethodId;
					$sTempOfferCode = $oOffersRow->offerCode;
					$sTempLeadsQuery = $oOffersRow->leadsQuery;
					$iTempLeadsGroupId = $oOffersRow->leadsGroupId;
					$iTempMaxAgeOfLeads = $oOffersRow->maxAgeOfLeads;
										
					$sTempLeadFileName = $oOffersRow->leadFileName;
					
					$iTempIsEncrypted = $oOffersRow->isEncrypted;
					$sTempEncMethod = $oOffersRow->encMethod;
					$sTempEncType = $oOffersRow->encType;
					$sTempEncKey = $oOffersRow->encKey;
					$sTempHeaderText = $oOffersRow->headerText;
					$sTempFooterText = $oOffersRow->footerText;
					$sTempFieldDelimiter = $oOffersRow->fieldDelimiter;
					$sTempFieldSeparater = $oOffersRow->fieldSeparater;
					$sTempEndOfLine = $oOffersRow->endOfLine;
					
					if ($sTempLeadFileName != '') {
						
						$sTempLeadFileName = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempLeadFileName);
						
												
						$sTempLeadFileName = eregi_replace("\[jd\]","$iJulianDays", $sTempLeadFileName);
						
						
						if (strstr($sTempLeadFileName,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempLeadFileName,strpos($sTempLeadFileName,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							//echo $sTempQuery. mysql_error();
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempLeadFileName);
							
							$sDateArithString = substr($sTempLeadFileName, strpos($sTempLeadFileName,"[d-"),5);
							
							$sTempLeadFileName = str_replace($sDateArithString, "", $sTempLeadFileName);
							
						} else {
							$sTempLeadFileName = eregi_replace("\[dd\]", date(d), $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[mm\]", date(m), $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempLeadFileName);
							$sTempLeadFileName = eregi_replace("\[yy\]", date(y), $sTempLeadFileName);
						}
					}
					
					
					if ($sTempHeaderText != '') {
						
						$sTempHeaderText = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempHeaderText);
						
						if (strstr($sTempHeaderText,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempHeaderText,strpos($sTempHeaderText,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							//echo $sTempQuery. mysql_error();
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempHeaderText = eregi_replace("\[dd\]", $sNewDD, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", $sNewMM, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", $sNewYY, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", $sNewShortYY, $sTempHeaderText);
							
							$sDateArithString = substr($sTempHeaderText, strpos($sTempHeaderText,"[d-"),5);
							
							$sTempHeaderText = str_replace($sDateArithString, "", $sTempHeaderText);
							
						} else {
							$sTempHeaderText = eregi_replace("\[dd\]", date(d), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", date(m), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", date(Y), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", date(y), $sTempHeaderText);
						}
					}
					
					
					// before getting new group data,
					// set header and footer in group lead file for previous groupId, only if file is combined
					
					if ($iTempPrevGroupId != 0 && $iTempPrevGroupId != $iTempLeadsGroupId && $iTempGrIsFileCombined && ($sTempGrHeaderText != '' || $sTempGrFooterText != '')) {
						
						$sTempData = '';
						
						$rFpGrLeadFileRead = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "r");
						
						
						if ($rFpGrLeadFileRead) {
							
							while (!feof($rFpGrLeadFileRead)) {
								$sTempData .= fread($rFpGrLeadFileRead, 1024);
							}
							
							fclose($rFpGrLeadFileRead);
						}
						
						// put header and footer
						if ($sTempGrHeaderText != '') {
							$sTempData = "$sTempGrHeaderText\r\n$sTempData";
						}
						if ($sTempGrFooterText != '') {
							$sTempData = "$sTempData\r\n$sTempGrFooterText";
						}
						
						// store data back in the file
						$rFpGrLeadFileWrite = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
						if ($rFpGrLeadFileWrite) {
							//$sTempData = "\\r\\n".$sTempData;
							fputs($rFpGrLeadFileWrite, $sTempData, strlen($sTempData));
							fclose($rFpGrLeadFileWrite);
						}
					} /// end of placing header and footer text in group file
					
					
					// get lead specific data from leadGroups table if offer is grouped
					// and lead group is not the same as previous loop
					//echo "<BR>$sTempOfferCode - $iTempLeadsGroupId prev $iTempPrevGroupId";
					
					if ($iTempLeadsGroupId != 0) {
						
						$sLeadsGroupQuery = "SELECT *
									FROM   leadGroups
									WHERE  id = '$iTempLeadsGroupId'";
						$rLeadsGroupResult = dbQuery($sLeadsGroupQuery);
						while ($oLeadsGroupRow = dbFetchObject($rLeadsGroupResult)) {
							$sTempGrName = $oLeadsGroupRow->name;
							$iTempGrDeliveryMethodId = $oLeadsGroupRow->deliveryMethodId;
							$sTempGrProcessingDays = $oLeadsGroupRow->processingDays;
							$sTempGrPostingUrl = $oLeadsGroupRow->postingUrl;
							$sTempGrFtpSiteUrl = $oLeadsGroupRow->ftpSiteUrl;
							$sTempGrInitialFtpDirectory = $oLeadsGroupRow->initialFtpDirectory;
							$iTempGrIsSecured = $oLeadsGroupRow->isSecured;
							$sTempGrUserId = $oLeadsGroupRow->userId;
							$sTempGrPasswd = $oLeadsGroupRow->passwd;
							$sTempGrLeadFileName = $oLeadsGroupRow->leadFileName;
							$iTempGrIsFileCombined = $oLeadsGroupRow->isFileCombined;
							$iTempGrIsEncrypted = $oLeadsGroupRow->isEncrypted;
							$sTempGrEncMethod = $oLeadsGroupRow->encMethod;
							$sTempGrEncType = $oLeadsGroupRow->encType;
							$sTempGrEncKey = $oLeadsGroupRow->encKey;
							$sTempGrHeaderText = $oLeadsGroupRow->headerText;
							$sTempGrFooterText = $oLeadsGroupRow->footerText;
							
							
							if ($sTempGrLeadFileName != '') {
								
								$sTempGrLeadFileName = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadFileName);
								
								//check if date should be different than current date in subject
								if (strstr($sTempGrLeadFileName,"[d-")) {
									
									//get arithmetic number
									
									$iDateArithNum = substr($sTempGrLeadFileName,strpos($sTempGrLeadFileName,"[d-")+3,1);
									
									$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
									$rTempResult = dbQuery($sTempQuery);
									while ($oTempRow = dbFetchObject($rTempResult)) {
										$sNewDate = $oTempRow->newDate;
									}
									
									$sNewYY = substr($sNewDate, 0, 4);
									$sNewShortYY = substr($sNewDate, 2, 2);
									$sNewMM = substr($sNewDate, 5, 2);
									$sNewDD = substr($sNewDate, 8, 2);
									
									$sTempGrLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadFileName);
									
									
									$sDateArithString = substr($sTempGrLeadFileName, strpos($sTempGrLeadFileName,"[d-"),5);
									
									
									$sTempGrLeadFileName = str_replace($sDateArithString, "", $sTempGrLeadFileName);
									
								} else {
									
									$sTempGrLeadFileName = eregi_replace("\[dd\]", date(d), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[mm\]", date(m), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadFileName);
									$sTempGrLeadFileName = eregi_replace("\[yy\]", date(y), $sTempGrLeadFileName);
								}
							}
							
						}
					}
					
					// get leads data for this offer
					
					//***** Temporary solution *******/
					// get id of the lead record and mark it processed, one by one until mysql upgraded
					// After mysql upgraded, use update with multiple table,
					// i.e. use same where condition as used for leads select query
					// get the id to update that ot data row
					
					if ($iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4') {
						// get last id reported
						$iTempLastIdReported = 0;
						$sTempLastIdQuery = "SELECT lastIdReported
											 FROM   realTimeDeliveryReporting
											 WHERE  offerCode = '$sTempOfferCode'
											 ORDER BY dateTimeSent DESC LIMIT 0,1";
						$rTempLastIdResult = dbQuery($sTempLastIdQuery);
						while ($oTempLastIdRow = dbFetchObject($rTempLastIdResult)) {
							$iTempLastIdReported = $oTempLastIdRow->lastIdReported;
						}
						
						$sTempLeadsQuery = eregi_replace("WHERE", "WHERE $sOtDataTable.id > '$iTempLastIdReported'
														  AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
					
					} else {
						$sTempLeadsQuery = eregi_replace("WHERE", "WHERE (processStatus IS NULL || processStatus='P') 
										  AND sendStatus IS NULL
										  AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
					}
					
					if ($sTempLeadsQuery != '') {
						if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
							$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id, ", $sTempLeadsQuery);
						} else {
							$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id, ", $sTempLeadsQuery);
						}
						if ($sOtDataTable == 'otData') {
							
							$sTempLeadsQuery = eregi_replace("otDataHistory", $sOtDataTable,$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("userDataHistory", $sUserDataTable,$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);							
							$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
							$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
						}
					}
					
					if ($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll") {
						
						echo $sTempLeadsQuery;
					}
					
					
					$rTempLeadsResult = dbQuery($sTempLeadsQuery);
					//echo "<BR>$sTempOfferCode ".$sTempLeadsQuery.dbError();
									
					if (!($rTempLeadsResult)) {
						echo "<BR>$sTempOfferCode ".$sTempLeadsQuery.dbError();
					}					
					
					if (! $rTempLeadsResult) {
						
						echo "<BR>$sTempOfferCode Query Error: ".dbError();
						
					} else {
						$iNumFields = dbNumFields($rTempLeadsResult);
						$iLeadsCount = dbNumRows($rTempLeadsResult);

						$j = 1;
						$iLeadCounter = 1;
						$iDailyLeadCounter = 1;
						$sMutExclusiveQuery = "SELECT *
											   FROM   offersMutExclusive
											   WHERE  offerCode1 = '$sTempOfferCode'
											   OR     offerCode2 = '$sTempOfferCode'";							
						$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
						
						$sMutExclusiveOffers = '';
						if (dbNumRows($rMutExclusiveResult) > 0 ) {
							
							while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
								//echo $oMutExclusiveRow->offerCode1==$sTempOfferCode;
								if ($oMutExclusiveRow->offerCode1 == $sTempOfferCode) {
									
									$sMutExclusiveOffers .= "'". $oMutExclusiveRow->offerCode2."',";
								} else {
									
									$sMutExclusiveOffers .= "'".$oMutExclusiveRow->offerCode1."',";
								}
							}
						}
							
						if ($sMutExclusiveOffers != '') {
							$sMutExclusiveOffers = substr($sMutExclusiveOffers, 0, strlen($sMutExclusiveOffers)-1);											
						}
							
						if (!($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll") ){
								// get the offer count of this offer
								$sOfferCountQuery = "SELECT leadCounts, dailyLeadCounts
													 FROM   offerLeadsCount
													 WHERE  offerCode = '$sTempOfferCode'";
								$rOfferCountResult = dbQuery($sOfferCountQuery);
								echo dbError();
								while ($oOfferCountRow = dbFetchObject($rOfferCountResult)) {
									$iLeadCounter = $oOfferCountRow->leadCounts + 1;
									$iDailyLeadCounter = $oOfferCountRow->dailyLeadCounts +1;
								}
							}

						while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {
						
							$iId = $aTempLeadsRow['id'];
							$sTempLeadEmail = $aTempLeadsRow['email'];
							
							
							/************** mutually exclusive checking ****************/
							
														
								if ($sMutExclusiveOffers != '') {
													
									// check if this lead is delivered to any mutually exclusive offers
									$sMutCheckQuery = "SELECT *
													   FROM   otDataHistory
													   WHERE  email = '$sTempLeadEmail'
													   AND    offerCode IN (".$sMutExclusiveOffers.")
													   AND    (sendStatus = 'S'
													   OR     processStatus = 'P' and date_format(dateTimeProcessed,'%Y-%m-%d') = CURRENT_DATE)";
									$rMutCheckResult = dbQuery($sMutCheckQuery);
									
									if (dbNumRows($rMutCheckResult) > 0) {
										// mark lead as rejected
										
										$sRejectMutExclQuery = "UPDATE $sOtDataTable
																SET    processStatus = 'R',
																		dateTimeProcessed = now(),	
																		reasonCode = 'meo'
																WHERE  id = '$iId'";
										echo "<BR>".$sRejectMutExclQuery;
										$rRejectMutExclResult = dbQuery($sRejectMutExclQuery);
										
										$iLeadsCount--;
										continue;
									}
								}
							
							/*****************************/

							// update process status and leadcounter only if lead not delivered real time
							if ($iTempDeliveryMethodId != '2' && $iTempDeliveryMethodId != '3' && $iTempDeliveryMethodId != '4') {
								$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable
																SET    processStatus = 'P',
																		dateTimeProcessed = now(),	
																		leadCounter = '$iLeadCounter',
																		dailyLeadCounter = '$iDailyLeadCounter'						
																WHERE  id = '$iId'
																AND    (processStatus IS NULL || processStatus='P')
																AND sendStatus IS NULL";
							
							$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
		
							echo dbError();
														
							for ($i=1; $i < $iNumFields; $i++) {
								
								
								if (dbFieldName($rTempLeadsResult, $i) == 'leadCounter') {
									$sLeadsData .= $sTempFieldDelimiter.$iLeadCounter.$sTempFieldDelimiter;
								} else if (dbFieldName($rTempLeadsResult, $i) == 'dailyLeadCounter') {
									$sLeadsData .= $sTempFieldDelimiter.$iDailyLeadCounter.$sTempFieldDelimiter;
								} else {
									$sLeadsData .= $sTempFieldDelimiter.$aTempLeadsRow[$i].$sTempFieldDelimiter;
								}
								
								if (($i+1) != $iNumFields) {
									// put separater if this is not the last field
									switch($sTempFieldSeparater) {
										case "\\n":
										$sLeadsData .= chr(10);
										break;
										case "\\t":
										$sLeadsData .= chr(9);
										break;
										default:
										$sLeadsData .= $sTempFieldSeparater;
									}
								
									//$sLeadsData .= $sTempFieldSeparater;
								}
								
							} // end of for loop
							
							
							$iLeadCounter++;
							$iDailyLeadCounter++;
							
							// put end of line if this is the last field and not the last record
							if ($j < $iLeadsCount) {
								switch($sTempEndOfLine) {
									case "\\n":
									$sLeadsData .= chr(10);
									break;
									case "\\r\\n":
									$sLeadsData .= chr(13).chr(10);
									break;
									default:
									$sLeadsData .= $sTempEndOfLine;
								}
							}
							$j++;
							
							}
							
						} // end of leads data while loop
						
						echo "<BR>$sTempOfferCode - $iLeadsCount";
												
						// add header and footer text if file not grouped
						
						if ($sTempHeaderText != '') {
							$sLeadsData = "$sTempHeaderText\r\n$sLeadsData";
						}
						if ($sTempFooterText != '') {
							$sLeadsData .= "\r\n$sTempFooterText";
						}
						
						// create the folders if not exists
						if ( ! is_dir($sGblLeadFilesPath)) {
							mkdir($sGblLeadFilesPath, 0777);
							chmod($sGblLeadFilesPath, 0777);
						}
						
						if (! is_dir($sTodaysLeadsFolder)) {
							mkdir($sTodaysLeadsFolder, 0777);
							chmod($sTodaysLeadsFolder, 0777);
						}
						
						
						if (! is_dir("$sTodaysLeadsFolder/offers")) {
							mkdir("$sTodaysLeadsFolder/offers", 0777);
							chmod("$sTodaysLeadsFolder/offers", 0777);
						}
						
						if (! is_dir("$sTodaysLeadsFolder/offers/$sTempOfferCode")) {
							mkdir("$sTodaysLeadsFolder/offers/$sTempOfferCode", 0777);
							chmod("$sTodaysLeadsFolder/offers/$sTempOfferCode", 0777);
						}
						
						// create file and  store data in the file only if lead count is not 0
						
						
						$sTempLeadFileName = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadFileName);
						//$sTempLeadFileName = eregi_replace("\[count\]", date(y), $sTempLeadFileName);
					
						$rFpLeadFile = fopen("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", "w");
						if ($rFpLeadFile) {
							if ($iLeadsCount != 0) {
								fputs($rFpLeadFile, $sLeadsData, strlen($sLeadsData));
								
							}
							
							fclose($rFpLeadFile);
							chmod("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", 0777);
						} else {
							
						}
						
						
						// encrypt the file if is encrypted is set. Store the encrypted file in the same folder.
											
						if ($iTempIsEncrypted) {

							if ($sTempEncMethod =='gpg') {
								$sTempEncLeadFileName = $sTempLeadFileName.".gpg";
								echo "<BR>gpg -r \"$sTempEncKey\" --always-trust --cipher-algo cast5 --output $sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempEncLeadFileName --encrypt $sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName<BR>";
								exec("gpg -r \"$sTempEncKey\" --always-trust --cipher-algo cast5 --output $sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempEncLeadFileName --encrypt $sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName");
							}
						}
						
						// if offer is grouped, put the separate lead file of this offer in groups folder
						// or append to group file if it should be combined in one file
						
						if ($iTempLeadsGroupId) {
							
							
							// create the folders if not exists
							if (! is_dir($sGblLeadFilesPath)) {
								mkdir($sGblLeadFilesPath, 0777);
								chmod($sGblLeadFilesPath, 0777);
							}
							
							if (! is_dir("$sTodaysLeadsFolder/groups")) {
								mkdir("$sTodaysLeadsFolder/groups", 0777);
								chmod("$sTodaysLeadsFolder/groups", 0777);
							}
							
							if (! is_dir("$sTodaysLeadsFolder/groups/$sTempGrName")) {
								mkdir("$sTodaysLeadsFolder/groups/$sTempGrName", 0777);
								chmod("$sTodaysLeadsFolder/groups/$sTempGrName", 0777);
							}
							
							// copy data into group file if have to combine
							
							
							// if file not to combined. copy the lead file to group dir
							// if to combind, append lead file content to group lead file
							
							if ($iTempGrIsFileCombined) {
								if ($iTempLeadsGroupId != $iTempPrevGroupId) {
									// create new lead file for group when it comes for first time
									// otherwise will be appended again and again when we rerun the script
									$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
									
								} else {
									// open the file to append and set pointer to end of file, create the file if not exists
									$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "a");
									$sLeadsData = "\r\n".$sLeadsData;
								}
								
							} else {
								// copy lead file to group dir
								$rFpLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempLeadFileName", "w");
																
							}
							
							if ($iLeadsCount != 0) {
								fputs($rFpLeadFile, $sLeadsData, strlen($sLeadsData));
							}
							fclose($rFpLeadFile);
							
						} // end of if($iTempLeadsGroupId)
						
						// store groupId now as previous groupId
						
						$iTempPrevGroupId = $iTempLeadsGroupId;
					}

		
		
		
				
		// If the last record was with groupId or processed for only one group, 
		// set header and footer in group lead file here 
		// because there was no NEXT record to decide that now the offer for a group are over and can put header and footer.
		//  only if file is combined and header/footer is not blank
			
			if ($iTempLeadsGroupId && $iTempGrIsFileCombined && ($sTempGrHeaderText != '' || $sTempGrFooterText != '')) {
				$sTempData = '';
				
				$rFpGrLeadFileRead = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "r");
				if ($rFpGrLeadFileRead) {
					
					while (!feof($rFpGrLeadFileRead)) {
						$sTempData .= fread($rFpGrLeadFileRead, 1024);
					}
					
					fclose($rFpGrLeadFileRead);
				}
				
				// put header and footer
				if ($sTempGrHeaderText != '') {
					$sTempData = "$sTempGrHeaderText\r\n$sTempData";
				}
				if ($sTempGrFooterText != '') {
					$sTempData = "$sTempData\r\n$sTempGrFooterText";
				}
				
				// store data back in the file
				$rFpGrLeadFileWrite = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "w");
				if ($rFpGrLeadFileWrite) {
					fputs($rFpGrLeadFileWrite, $sTempData, strlen($sTempData));
					fclose($rFpGrLeadFileWrite);
				}
			} /// end of placing header and footer text in group file			
			
	}
	
			} // if offersQuery != ''
} else if (($sSendLeads || $sSendFormPostLeads) && ($sProcessOption == 'processAll' || $sProcessOption == 'processOne')) {
	
	
	// get all active offers
	
	$sOffersQuery = "SELECT offerLeadSpec.*
					 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					 WHERE  offers.offerCode = offerLeadSpec.offerCode
					 AND    activeDateTime <= now() 
					 AND    lastLeadDate >= CURRENT_DATE 
					 AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )";
	
	if ($sSendFormPostLeads) {
		$sOffersQuery .= " AND offerLeadSpec.deliveryMethodId = '5'";
	} else {
		$sOffersQuery .= " AND offerLeadSpec.deliveryMethodId != '5'";
	}
	
	if ($sProcessOption == "processOne") {
		if ($sOfferCode != '') {
			$sOffersQuery .= " AND  offers.offerCode='$sOfferCode'";
		} else if($iGroupId != '') {
			$sOffersQuery .= " AND offerLeadSpec.leadsGroupId = '$iGroupId'";
		} else {
			$sMessage = "You must select either an offer or a group to Process One...";
			$bKeepValues = "true";
			$sOffersQuery = '';
		}
	}
	//$sOffersQuery .= " AND offers.offerCode NOT IN ('AIU','CTU_ONLINE','KABW_WHITE','WCTM_TUL','WCTM_REG','WESTWOOD','WMDN_DLN')";
	
	
	// get the offer list/ one offer to get leads for
	if ($sOffersQuery != '') {
		
		$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
		$rOffersResult = dbQuery($sOffersQuery);
		echo dbError();
		
		$iNumRecords = dbNumRows($rOffersResult);
		$iCurrentRec = 0;
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			
			// reset error message
			$sErrorInSendingLeads = '';
			$iCurrentRec++;
			
			$sLeadsData = '';
			$sLeadFileData = '';
			$sEmailMessage = '';
			
			$sTempOfferCode = $oOffersRow->offerCode;
			$sTempLeadsQuery = $oOffersRow->leadsQuery;
			$iTempLeadsGroupId = $oOffersRow->leadsGroupId;
			$iTempMaxAgeOfLeads = $oOffersRow->maxAgeOfLeads;
			
			// get lead specific data from offerLeadSpec table
			$iTempDeliveryMethodId = $oOffersRow->deliveryMethodId;
			$sTempProcessingDays = $oOffersRow->processingDays;
			$sTempPostingUrl = $oOffersRow->postingUrl;
			$sTempHttpPostString = $oOffersRow->httpPostString;
			$sTempFtpSiteUrl = $oOffersRow->ftpSiteUrl;
			$sTempInitialFtpDirectory = $oOffersRow->initialFtpDirectory;
			$iTempIsSecured = $oOffersRow->isSecured;
			$sTempUserId = $oOffersRow->userId;
			$sTempPasswd = $oOffersRow->passwd;
			$sTempLeadFileName = $oOffersRow->leadFileName;			
			$iTempIsEncrypted = $oOffersRow->isEncrypted;
			$sTempEncMethod = $oOffersRow->encMethod;
			$sTempEncType = $oOffersRow->encType;
			$sTempEncKey = $oOffersRow->encKey;
			$sTempHeaderText = $oOffersRow->headerText;
			$sTempFooterText = $oOffersRow->footerText;
			$sTempFieldDelimiter = $oOffersRow->fieldDelimiter;
			$sTempFieldSeparater = $oOffersRow->fieldSeparater;
			$sTempEndOfLine = $oOffersRow->endOfLine;
			$sTempLeadsEmailSubject = $oOffersRow->leadsEmailSubject;
			$sTempLeadsEmailFromAddr = $oOffersRow->leadsEmailFromAddr;
			$sTempLeadsEmailBody = $oOffersRow->leadsEmailBody;
			$sTempSingleEmailFromAddr = $oOffersRow->singleEmailFromAddr;
			$sTempSingleEmailSubject = $oOffersRow->singleEmailSubject;
			$sTempSingleEmailBody = $oOffersRow->singleEmailBody;
			$sTempTestEmailRecipients = $oOffersRow->testEmailRecipients;
			$sTempCountEmailRecipients = $oOffersRow->countEmailRecipients;
			$sTempLeadsEmailRecipients = $oOffersRow->leadsEmailRecipients;
			
			$sTempHowSent = '';
			
			$sDeliveryMethodQuery = "SELECT *
									 FROM   deliveryMethods
									 WHERE  id = '$iTempDeliveryMethodId'";
			$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
			while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
				$sTempHowSent = $oDeliveryMethodRow->shortMethod;
			}			
			
			if ($sTempLeadFileName != '') {
				
				$sTempLeadFileName = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempLeadFileName);
				
				$sTempLeadFileName = eregi_replace("\[jd\]", "$iJulianDays", $sTempLeadFileName);
				
				if (strstr($sTempLeadFileName,"[d-")) {
					
					//get arithmetic number
					
					$iDateArithNum = substr($sTempLeadFileName,strpos($sTempLeadFileName,"[d-")+3,1);
					
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewShortYY = substr($sNewDate, 2, 2);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					
					$sTempLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempLeadFileName);
					
					$sDateArithString = substr($sTempLeadFileName, strpos($sTempLeadFileName,"[d-"),5);
					
					$sTempLeadFileName = str_replace($sDateArithString, "", $sTempLeadFileName);
					
				} else {
					$sTempLeadFileName = eregi_replace("\[dd\]", date(d), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[mm\]", date(m), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempLeadFileName);
					$sTempLeadFileName = eregi_replace("\[yy\]", date(y), $sTempLeadFileName);
				}
			}
						
			
					if ($sTempHeaderText != '') {
						
						$sTempHeaderText = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempHeaderText);
						
						if (strstr($sTempHeaderText,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempHeaderText,strpos($sTempHeaderText,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempHeaderText = eregi_replace("\[dd\]", $sNewDD, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", $sNewMM, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", $sNewYY, $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", $sNewShortYY, $sTempHeaderText);
							
							$sDateArithString = substr($sTempHeaderText, strpos($sTempHeaderText,"[d-"),5);
							
							$sTempHeaderText = str_replace($sDateArithString, "", $sTempHeaderText);
							
						} else {
							$sTempHeaderText = eregi_replace("\[dd\]", date(d), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[mm\]", date(m), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yyyy\]", date(Y), $sTempHeaderText);
							$sTempHeaderText = eregi_replace("\[yy\]", date(y), $sTempHeaderText);
						}
					}
					
			if ($sTempLeadsEmailSubject != '') {
				
				$sTempLeadsEmailSubject = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempLeadsEmailSubject);
				
				if (strstr($sTempLeadsEmailSubject,"[d-")) {
					
					//get date arithmetic number
					
					$iDateArithNum = substr($sTempLeadsEmailSubject,strpos($sTempLeadsEmailSubject,"[d-")+3,1);
					
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewYY = substr($sNewDate, 2, 2);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					
					$sTempLeadsEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempLeadsEmailSubject);
					
					$sDateArithString = substr($sTempLeadsEmailSubject, strpos($sTempLeadsEmailSubject,"[d-"),5);
					
					$sTempLeadsEmailSubject = str_replace($sDateArithString, "", $sTempLeadsEmailSubject);
					
				} else {
					
					$sTempLeadsEmailSubject = eregi_replace("\[dd\]", date(d), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[mm\]", date(m), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempLeadsEmailSubject);
					$sTempLeadsEmailSubject = eregi_replace("\[yy\]", date(y), $sTempLeadsEmailSubject);
				}
			}
			if ($sTempLeadsEmailBody != '') {
				$sTempLeadsEmailBody = eregi_replace("\[offerCode\]", $sTempOfferCode, $sTempLeadsEmailBody);
			}
			
			
			if ($sTempSingleEmailSubject != '') {
			$sTempSingleEmailSubject = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempSingleEmailSubject);
				
				
				if (strstr($sTempSingleEmailSubject,"[d-")) {
					
					//get date arithmetic number
					
					$iDateArithNum = substr($sTempSingleEmailSubject,strpos($sTempSingleEmailSubject,"[d-")+3,1);
					
					$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
					$rTempResult = dbQuery($sTempQuery);
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$sNewDate = $oTempRow->newDate;
					}
					
					$sNewYY = substr($sNewDate, 0, 4);
					$sNewShortYY = substr($sNewDate, 2, 2);
					$sNewMM = substr($sNewDate, 5, 2);
					$sNewDD = substr($sNewDate, 8, 2);
					
					$sTempSingleEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempSingleEmailSubject);
					
					$sDateArithString = substr($sSingleEmailSubject, strpos($sTempSingleEmailSubject,"[d-"),5);
					
					$sTempSingleEmailSubject = str_replace($sDateArithString, "", $sTempSingleEmailSubject);
					
				} else {
					
					$sTempSingleEmailSubject = eregi_replace("\[dd\]", date(d), $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[mm\]", date(m), $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempSingleEmailSubject);
					$sTempSingleEmailSubject = eregi_replace("\[yy\]", date(y), $sTempSingleEmailSubject);
				}
				
			}
						
			
			/// Before getting new group data,  send the leads of previous group
			// group details is already in the variables
			//echo $iTempLeadsGroupId. " prev ".$iTempPrevGroupId;
			// send group email if this is the last record of offer loop (necessary in the case when processing one group)
			
			//echo "<BR>$sTempOfferCode groupId $iTempLeadsGroupId prev groupId $iTempPrevGroupId";
			//if (($iTempLeadsGroupId != 0  && $iTempPrevGroupId != $iTempLeadsGroupId && $iTempPrevGroupId != '') || ($iTempLeadsGroupId != 0 && $iNumRecords == $iCurrentRec)) {
			if (($iTempLeadsGroupId != 0  && $iTempPrevGroupId != $iTempLeadsGroupId )) {
						
				echo "<BR>Sending group email for $iTempLeadsGroupId";
				$sLeadsGroupQuery = "SELECT *
									FROM   leadGroups
									WHERE  id = '$iTempLeadsGroupId'";
				
				$rLeadsGroupResult = dbQuery($sLeadsGroupQuery);
				while ($oLeadsGroupRow = dbFetchObject($rLeadsGroupResult)) {
					$sTempGrName = $oLeadsGroupRow->name;
					$iTempGrDeliveryMethodId = $oLeadsGroupRow->deliveryMethodId;
					$sTempGrProcessingDays = $oLeadsGroupRow->processingDays;
					$sTempGrPostingUrl = $oLeadsGroupRow->postingUrl;
					$sTempGrFtpSiteUrl = $oLeadsGroupRow->ftpSiteUrl;
					$sTempGrInitialFtpDirectory = $oLeadsGroupRow->initialFtpDirectory;
					$iTempGrIsSecured = $oLeadsGroupRow->isSecured;
					$sTempGrUserId = $oLeadsGroupRow->userId;
					$sTempGrPasswd = $oLeadsGroupRow->passwd;
					$sTempGrLeadFileName = $oLeadsGroupRow->leadFileName;
					$iTempGrIsFileCombined = $oLeadsGroupRow->isFileCombined;
					$iTempGrIsEncrypted = $oLeadsGroupRow->isEncrypted;
					$sTempGrEncMethod = $oLeadsGroupRow->encMethod;
					$sTempGrEncType = $oLeadsGroupRow->encType;
					$sTempGrEncKey = $oLeadsGroupRow->encKey;
					$sTempGrHeaderText = $oLeadsGroupRow->headerText;
					$sTempGrFooterText = $oLeadsGroupRow->footerText;
					$sTempGrLeadsEmailSubject = $oLeadsGroupRow->leadsEmailSubject;
					$sTempGrLeadsEmailFromAddr = $oLeadsGroupRow->leadsEmailFromAddr;
					$sTempGrLeadsEmailBody = $oLeadsGroupRow->leadsEmailBody;
					$sTempGrTestEmailRecipients = $oLeadsGroupRow->testEmailRecipients;
					$sTempGrCountEmailRecipients = $oLeadsGroupRow->countEmailRecipients;
					$sTempGrLeadsEmailRecipients = $oLeadsGroupRow->leadsEmailRecipients;	

					$sTempHowSent = '';
					$sDeliveryMethodQuery = "SELECT *
									 FROM   deliveryMethods
									 WHERE  id = '$iTempGrDeliveryMethodId'";
					$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
					while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
						$sTempHowSent = $oDeliveryMethodRow->shortMethod;
					}					
					
				}
				
				if ($sTempGrLeadFileName != '') {
					
					$sTempGrLeadFileName = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadFileName);
					
					//check if date should be different than current date in subject
					if (strstr($sTempGrLeadFileName,"[d-")) {
						
						//get arithmetic number
						
						$iDateArithNum = substr($sTempGrLeadFileName,strpos($sTempGrLeadFileName,"[d-")+3,1);
						
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
						
						$sNewYY = substr($sNewDate, 0, 4);
						$sNewShortYY = substr($sNewDate, 2, 2);
						$sNewMM = substr($sNewDate, 5, 2);
						$sNewDD = substr($sNewDate, 8, 2);
						
						$sTempGrLeadFileName = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadFileName);
						
						
						$sDateArithString = substr($sTempGrLeadFileName, strpos($sTempGrLeadFileName,"[d-"),5);
						
						
						$sTempGrLeadFileName = str_replace($sDateArithString, "", $sTempGrLeadFileName);
						
					} else {
						
						$sTempGrLeadFileName = eregi_replace("\[dd\]", date(d), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[mm\]", date(m), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadFileName);
						$sTempGrLeadFileName = eregi_replace("\[yy\]", date(y), $sTempGrLeadFileName);
					}
				} // end of if leadfilename != ''
				
				if ($sTempGrLeadsEmailSubject != '') {
					
					$sTempGrLeadsEmailSubject = eregi_replace("\[groupName\]",$sTempGrName, $sTempGrLeadsEmailSubject);
					
					//check if date should be different than current date in subject
					if (strstr($sTempGrLeadsEmailSubject,"[d-")) {
						
						//get arithmetic number
						
						$iDateArithNum = substr($sTempGrLeadsEmailSubject,strpos($sTempGrLeadsEmailSubject,"[d-")+3,1);
						
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
						
						$sNewYY = substr($sNewDate, 0, 4);
						$sNewShortYY = substr($sNewDate, 2, 2);
						$sNewMM = substr($sNewDate, 5, 2);
						$sNewDD = substr($sNewDate, 8, 2);
						
						$sTempGrLeadsEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempGrLeadsEmailSubject);						
						$sTempGrLeadsEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempGrLeadsEmailSubject);
						
						$sDateArithString = substr($sTempGrLeadsEmailSubject, strpos($sTempGrLeadsEmailSubject,"[d-"),5);						
						
						$sTempGrLeadsEmailSubject = str_replace($sDateArithString, "", $sTempGrLeadsEmailSubject);
						
					} else {
						
						$sTempGrLeadsEmailSubject = eregi_replace("\[dd\]", date(d), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[mm\]", date(m), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempGrLeadsEmailSubject);
						$sTempGrLeadsEmailSubject = eregi_replace("\[yy\]", date(y), $sTempGrLeadsEmailSubject);
					}
					
				} // end of leads subj != ''
				
				// get offercode wise count here
				$iGrLeadsCount = 0;
				$sTempGrLeadsEmailContent = '';
				$i = 0;
				$sGroupOffersCountQuery = "SELECT offerLeadSpec.offerCode, leadFileName, count($sOtDataTable.email) counts
												   FROM   $sUserDataTable, $sOtDataTable, offerLeadSpec 
												   WHERE  offerLeadSpec.offerCode = $sOtDataTable.offerCode 
												   AND    $sUserDataTable.email = $sOtDataTable.email 
												   AND    offerLeadSpec.leadsGroupId = '$iTempLeadsGroupId' 
												   AND	  processStatus = 'P'
												   AND	  verified != 'I'												
												   AND    sendStatus IS NULL												   
												   AND postalVerified = 'V'  
												   AND   DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
												   AND address NOT LIKE '3401 DUNDEE%'
												   GROUP BY offerLeadSpec.offerCode";
				
				// don't check postal verification if testing from current table
				if ($sOtDataTable == 'otData') {

					$sGroupOffersCountQuery = eregi_replace("AND postalVerified = 'V'","", $sGroupOffersCountQuery);
					$sGroupOffersCountQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sGroupOffersCountQuery);					
					$sGroupOffersCountQuery = eregi_replace("AND mode = 'A'","",$sGroupOffersCountQuery);
				}				
													
				$rGroupOffersCountResult = dbQuery($sGroupOffersCountQuery);				
				while ($oGroupOffersCountRow = dbFetchObject($rGroupOffersCountResult)) {
					$sTempGrOfferCode = $oGroupOffersCountRow->offerCode;
					$sTempGrLeadsEmailContent .=  "$sTempGrOfferCode - $oGroupOffersCountRow->counts\r\n";
					$iGrLeadsCount += $oGroupOffersCountRow->counts;
					
					$sTempFileName = $oGroupOffersCountRow->leadFileName;
					
					// replace variables in lead file name
					
					if ($sTempFileName != '' && $iTempGrIsFileCombined == '') {
						
						$sTempFileName = eregi_replace("\[offerCode\]",$sTempGrOfferCode, $sTempFileName);
						
						if (strstr($sTempFileName,"[d-")) {
							
							//get arithmetic number
							
							$iDateArithNum = substr($sTempFileName,strpos($sTempFileName,"[d-")+3,1);
							
							$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
							$rTempResult = dbQuery($sTempQuery);
							while ($oTempRow = dbFetchObject($rTempResult)) {
								$sNewDate = $oTempRow->newDate;
							}
							
							$sNewYY = substr($sNewDate, 0, 4);
							$sNewShortYY = substr($sNewDate, 2, 2);
							$sNewMM = substr($sNewDate, 5, 2);
							$sNewDD = substr($sNewDate, 8, 2);
							
							$sTempFileName = eregi_replace("\[dd\]", $sNewDD, $sTempFileName);
							$sTempFileName = eregi_replace("\[mm\]", $sNewMM, $sTempFileName);
							$sTempFileName = eregi_replace("\[yyyy\]", $sNewYY, $sTempFileName);
							$sTempFileName = eregi_replace("\[yy\]", $sNewShortYY, $sTempFileName);
							
							$sDateArithString = substr($sTempFileName, strpos($sTempFileName,"[d-"),5);
							
							$sTempFileName = str_replace($sDateArithString, "", $sTempFileName);
							
						} else {
							$sTempFileName = eregi_replace("\[dd\]", date(d), $sTempFileName);
							$sTempFileName = eregi_replace("\[mm\]", date(m), $sTempFileName);
							$sTempFileName = eregi_replace("\[yyyy\]", date(Y), $sTempFileName);
							$sTempFileName = eregi_replace("\[yy\]", date(y), $sTempFileName);
						}
						$aTempGrOfferLeadFiles[$i++] = $sTempFileName;
					}					
				}
				
				$sTempGrLeadsEmailContent = "$sTempGrLeadsEmailContent\r\n"."Total Count - $iGrLeadsCount";
				$sTempGrLeadsEmailSubject = eregi_replace("\[count\]", "$iGrLeadsCount", $sTempGrLeadsEmailSubject);
				
				if ($sTempGrLeadsEmailBody != '') {
					$sTempGrLeadsEmailBody = eregi_replace("\[offerCode - count\]",$sTempGrLeadsEmailContent, $sTempGrLeadsEmailBody);
				}
				
				
				// if testing of lead delivery, then use the email address specified in leads processing screen
				
				if ($sTestMode == '') {
							
							$sTempGrLeadsEmailTo = $sTempGrLeadsEmailRecipients;
							
							// for count email
							
							$sTempGrCountEmailTo = $sTempGrCountEmailRecipients;							
							
						} else {
							
							$sTempGrLeadsEmailTo = $sTestProcessingEmailRecipients;
							// for count email
							$sTempGrCountEmailTo = $sTestProcessingEmailRecipients;
																		
							// add "Test - " to subject line
							$sTempGrLeadsEmailSubject = "Test - ".$sTempGrLeadsEmailSubject;
						}						
						
						// replace count in email subject and email body
						
												
						// send leads data through specified delivery method
						// only if lead count is not 0
						
						if ($iGrLeadsCount != 0) {
							
							// send count email
						$sHeaders = "From: $sTempGrLeadsEmailFromAddr\n";						
						$sHeaders .= "Reply-To: $sTempGrLeadsEmailFromAddr\n";
						$sHeaders .= "X-Priority: 1\n";
						$sHeaders .= "X-MSMail-Priority: High\n";
						$sHeaders .= "X-Mailer: My PHP Mailer\n";
						
						if ($sTestMode) {
							$sDispGrCountEmailRecipients =  "Count Email Recipients: $sTempGrCountEmailRecipients\n\r\n\r";
							$sDispGrLeadsEmailRecipients =  "Leads Email Recipients: $sTempGrLeadsEmailRecipients\n\r\n\r";
						} else {
							$sDispGrCountEmailRecipients =  "";
							$sDispGrLeadsEmailRecipients =  "";
						}
						
						mail($sTempGrCountEmailTo, $sTempGrLeadsEmailSubject, $sDispGrCountEmailRecipients.$sTempGrLeadsEmailBody , $sHeaders);
						
						
							if ($iTempGrDeliveryMethodId == 1) {
																
								// If delivery method is 'FTP Daily Batch'
								
								/*$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
								FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
								UserId: $sTempUserId - Password: $sTempPasswd";
								*/
							} else if ($iTempGrDeliveryMethodId == 5) {
								// If delivery method is 'Daily Batch Form POST - GET'
								
								/*$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
								FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
								UserId: $sTempUserId - Password: $sTempPasswd";
								*/
							} else if ($iTempGrDeliveryMethodId == 7) {
								// If delivery method is 'Daily Batch Email'
								echo "send group leads email";
								$sHeaders = '';
								$sGrEmailMessage = '';
								$sGrLeadFileData = '';
								
								$sBorderRandom = md5(time());
								
								$sMailBoundry = "==x{$sBorderRandom}x";
																
								$sHeaders="From: $sTempGrLeadsEmailFromAddr\n";
								
								$sHeaders.="Reply-To: $sTempGrLeadsEmailFromAddr\n";
								$sHeaders.="X-Priority: 1\n";
								$sHeaders.="X-MSMail-Priority: High\n";
								$sHeaders.="X-Mailer: My PHP Mailer\n";
								$sHeaders.="Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
								
								$sHeaders .= "MIME-Version: 1.0\r\n";
								
								$sGrEmailMessage .= "This is a multi-part message in MIME format.\r\n\r\n";
								$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
								$sGrEmailMessage .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
								$sGrEmailMessage .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
								$sGrEmailMessage .= "$sDispGrLeadsEmailRecipients"."$sTempGrLeadsEmailBody\r\n\r\n";
								
								// get attachemnt file/files data
								// and attach one by one if there are more than one files in the folder
								
								$rFpGrLeadFilesDir = openDir("$sTodaysLeadsFolder/groups/$sTempGrName");
								
								if ($rFpGrLeadFilesDir) {
									
									if ($iTempGrIsFileCombined) {
										
										$sGrLeadFileData = "";
										$rFpGrLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName","r");
										
										if ($rFpGrLeadFile) {
											while (!feof($rFpGrLeadFile)) {
												$sGrLeadFileData .= fread($rFpGrLeadFile, 1024);
											}
											$sGrLeadFileData = base64_encode($sGrLeadFileData);
											$sGrLeadFileData = chunk_split($sGrLeadFileData);											
											$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
											$sGrEmailMessage .= "Content-type: text/plain;  name=\"{$sTempGrLeadFileName}\"\r\n";
											$sGrEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
											$sGrEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempGrLeadFileName}\"\r\n\r\n";
											$sGrEmailMessage .= "$sGrLeadFileData\n";
											fclose($rFpGrLeadFile);
										} else {
											
											$sErrorInSendingLeads .= "$sTempGrName - Opening Lead File $sTempGrLeadFileName Failed<BR>";
										}										
										
									} else {
																				
										for ($i=0;$i<count($aTempGrOfferLeadFiles); $i++) {
											$sGrLeadFileData = "";
											$sTempLeadFileToAttach = $aTempGrOfferLeadFiles[$i];

											$rFpGrLeadFile = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempLeadFileToAttach","r");
											
											if ($rFpGrLeadFile) {
												while (!feof($rFpGrLeadFile)) {
													$sGrLeadFileData .= fread($rFpGrLeadFile, 1024);
												}
												$sGrLeadFileData = base64_encode($sGrLeadFileData);
												$sGrLeadFileData = chunk_split($sGrLeadFileData);
											
												$sGrEmailMessage .= "--{$sMailBoundry}\r\n";
												$sGrEmailMessage .= "Content-type: text/plain;  name=\"{$sTempLeadFileToAttach}\"\r\n";
												$sGrEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
												$sGrEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempLeadFileToAttach}\"\r\n\r\n";
												$sGrEmailMessage .= "$sGrLeadFileData\n";
												fclose($rFpGrLeadFile);
											} else {
												$sErrorInSendingLeads .= "$sTempGrName - Opening Lead File $sTempLeadFileToAttach Failed<BR>";
											}
										}
									}							
								} // end if $rFpGrLeadFilesDir
								
								$sGrEmailMessage .= "--{$sMailBoundry}--\r\n";
								
								// send count
								
								//send lead data
								echo "sending lead data now";
								mail($sTempGrLeadsEmailTo, $sTempGrLeadsEmailSubject, $sGrEmailMessage , $sHeaders);
								
							} else if ($iTempDeliveryMethodId == 8) {
								// If delivery method is 'Upload In Browser'								
							}
						}
						
						// update group leads and set sendStatus = 'S' for all the leads of the group
						
						if (!($sTestMode)) {
						$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable, offerLeadSpec
												 	  SET    sendStatus = 'S',
						 		   							 dateTimeSent = now(),
								   							 howSent = '$sTempHowSent'		
													  WHERE  $sOtDataTable.offerCode = offerLeadSpec.offerCode
													  AND    offerLeadSpec.leadsGroupId = '$iTempLeadsGroupId'
													  AND    processStatus = 'P'								
													  AND    sendStatus IS NULL";
						echo $sProcessStatusUpdateQuery;
						$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
						}
						
				}
				
				// get leads data for this offer
				
				//***** Temporary solution *******/
				// get id of the lead record and mark it processed, one by one 
				// i.e. use same where condition as used for leads select query
				// get the id to update that ot data row
				
				
				if ($iTempLeadsGroupId == 0) {
				if ($sTestMode == '') {
							
							$sTempLeadsEmailTo = $sTempLeadsEmailRecipients;
							
							// for count email
							
							$sTempCountEmailTo = $sTempCountEmailRecipients;							
							
						} else {
							
							$sTempLeadsEmailTo = $sTestProcessingEmailRecipients;
							// for count email
							$sTempCountEmailTo = $sTestProcessingEmailRecipients;														
							
							// add "Test - " to subject line
							$sTempLeadsEmailSubject = "Test - ".$sTempLeadsEmailSubject;
							$sTempSingleEmailSubject = "Test - ".$sTempSingleEmailSubject;
						}
				}	
				if ($iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4') {
					// get last id reported
						$sTempLastIdQuery = "SELECT lastIdReported
											 FROM   realTimeDeliveryReporting
											 WHERE  offerCode = '$sTempOfferCode'
											 ORDER BY dateTimeSent DESC LIMIT 0,1";
						$rTempLastIdResult = dbQuery($sTempLastIdQuery);
						while ($oTempLastIdRow = dbFetchObject($rTempLastIdResult)) {
							$iTempLastIdReported = $oTempLastIdRow->lastIdReported;
						}
						 
						$sTempLeadsQuery = eregi_replace( "WHERE", "WHERE $sOtDataTable.id > '$iTempLastIdReported'
														   AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
						
						
				} else {
						$sTempLeadsQuery = eregi_replace( "WHERE", "WHERE processStatus='P' AND sendStatus IS NULL
										  AND address NOT LIKE '3401 DUNDEE%' AND ", $sTempLeadsQuery);
				}

				if ($sTempLeadsQuery != '') {
					if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
						$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id id, ", $sTempLeadsQuery);
					} else {
						$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id id, ", $sTempLeadsQuery);
					}
					if ($sOtDataTable == 'otData') {
						$sTempLeadsQuery = eregi_replace("otDataHistory", $sOtDataTable,$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("userDataHistory", $sUserDataTable,$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
						$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
					}
				}				
				
				/*if ($sTempOfferCode == 'KDN_INV') {					
					$sTempLeadsQuery .=  "LIMIT 0,2";
				}*/
				
				$rTempLeadsResult = dbQuery($sTempLeadsQuery);
				
				//echo $sTempLeadsQuery.mysql_num_rows($rTempLeadsResult);
				
				
				$iLeadsCount = 0;
				
				$sMutExclusiveQuery = "SELECT *
									   FROM   offersMutExclusive
									   WHERE  offerCode1 = '$sTempOfferCode'
									   OR     offerCode2 = '$sTempOfferCode'";							
				$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
				
				$sMutExclusiveOffers = '';
				if (dbNumRows($rMutExclusiveResult) > 0 ) {
					
					while ($oMutExclusiveRow = dbFetchObject($rMutExclusiveResult)) {
						
						if ($oMutExclusiveRow->offerCode1 == $sTempOfferCode) {
							
							$sMutExclusiveOffers .= "'". $oMutExclusiveRow->offerCode2."',";
						} else {
							
							$sMutExclusiveOffers .= "'".$oMutExclusiveRow->offerCode1."',";
						}
					}
				}
					
				if ($sMutExclusiveOffers != '') {
					$sMutExclusiveOffers = substr($sMutExclusiveOffers, 0, strlen($sMutExclusiveOffers)-1);											
				}
						
				
				if (! $rTempLeadsResult) {
					
					echo "<BR>$sTempOfferCode Query Error: ".dbError();
					
					
				} else {
					$iNumFields = dbNumFields($rTempLeadsResult);
					$iLeadsCount = dbNumRows($rTempLeadsResult);
					
					//	echo "<BR>$sTempOfferCode - $iLeadsCount";				
					
					// update offerCount for this offer
					if ($sTestMode == ''  && !($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll")) {
						
						// make daily leadCoutner 0
						$sUpdateOfferCountQuery = "UPDATE offerLeadsCount
												   SET    dailyLeadCounts = 0														  
												   WHERE  offerCode = '$sTempOfferCode'";
						$rUpdateOfferCountResult = dbQuery($sUpdateOfferCountQuery);
						// check if record exists
						$sCheckQuery = "SELECT *
										FROM   offerLeadsCount
										WHERE  offerCode = '$sTempOfferCode'";
						$rCheckResult = dbQuery($sCheckQuery);
						if (dbNumRows($rCheckResult) == 0 ) {
							$sInsertOfferCountQuery = "INSERT INTO offerLeadsCount(offerCode, leadCounts, dailyLeadCounts)
													   VALUES ('$sTempOfferCode', '$iLeadsCount', '$iLeadsCount')";
							$rInsertOfferCountResult = dbQuery($sInsertOfferCountQuery);
						} else {
							
							$sUpdateOfferCountQuery = "UPDATE offerLeadsCount
													   SET    leadCounts = leadCounts + $iLeadsCount,
															  dailyLeadCounts = dailyLeadCounts	+ $iLeadsCount
													   WHERE  offerCode = '$sTempOfferCode'";
							$rUpdateOfferCountResult = dbQuery($sUpdateOfferCountQuery);
							
						}
					}
										
					
					$iLastIdReported = 0;
					
					while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {
						
						$iTempId = $aTempLeadsRow['id'];
						$sTempLeadEmail = $aTempLeadsRow['email'];
					
						$sTempSendingError = '';
						
						if ($sMutExclusiveOffers != '') {
													
									// check if this lead is delivered to any mutually exclusive offers
									$sMutCheckQuery = "SELECT *
													   FROM   otDataHistory
													   WHERE  email = '$sTempLeadEmail'
													   AND    offerCode IN (".$sMutExclusiveOffers.")
													   AND    sendStatus = 'S'";
									$rMutCheckResult = dbQuery($sMutCheckQuery);
									//echo $sMutCheckQuery.mysql_error();
									if (dbNumRows($rMutCheckResult) > 0) {
										
										// reverse the lead count to -1 if found mut excl. lead
										$sUpdateMutOfferCountQuery = "UPDATE offerLeadsCount
													   SET    leadCounts = leadCounts - 1
															  dailyLeadCounts = dailyLeadCounts	- 1
													   WHERE  offerCode = '$sTempOfferCode'";
										$rUpdateMutOfferCountResult = dbQuery($sUpdateMutOfferCountQuery);
										
										$iLeadsCount--;
										continue;
									}
						}
							
						
												
						// send lead emails here if lead delivery method is 
						// daily batch form post - GET
						if ($iTempDeliveryMethodId == '5' ) {				
							
							$sTempHttpPostStringRec = eregi_replace("\[email\]", urlencode($aTempLeadsRow['email']), $sTempHttpPostString);
							$sTempHttpPostStringRec = eregi_replace("\[salutation\]",urlencode($aTempLeadsRow['salutation']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[first\]",urlencode($aTempLeadsRow['first']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[last\]",urlencode($aTempLeadsRow['last']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[address\]",urlencode($aTempLeadsRow['address']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[address2\]",urlencode($aTempLeadsRow['address2']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[city\]",urlencode($aTempLeadsRow['city']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[state\]",urlencode($aTempLeadsRow['state']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[zip\]",urlencode($aTempLeadsRow['zip']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[phone\]",urlencode($aTempLeadsRow['phoneNo']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[phone_areaCode\]",urlencode($aTempLeadsRow['phone_areaCode']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[phone_exchange\]",urlencode($aTempLeadsRow['phone_exchange']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[phone_number\]",urlencode($aTempLeadsRow['phone_number']), $sTempHttpPostStringRec);
							$sTempHttpPostStringRec = eregi_replace("\[remoteIp\]",urlencode($aTempLeadsRow['remoteIp']), $sTempHttpPostStringRec);
							
							// get all the page2 fields of this offer and replace 
							$sPage2MapQuery = "SELECT *
											   FROM   page2Map
				 	 			   			   WHERE offerCode = '$sTempOfferCode'
				 				   			   ORDER BY storageOrder ";
		
							$rPage2MapResult = dbQuery($sPage2MapQuery);
							$f = 1;
							
							while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
																
								$sFieldVar = "FIELD".$f;
								
								$sTempHttpPostStringRec = eregi_replace("\[$sFieldVar\]",urlencode($aTempLeadsRow[$sFieldVar]), $sTempHttpPostStringRec);
					
								$f++;
							}	
							
							$aUrlArray = explode("//", $sTempPostingUrl);
							$sUrlPart = $aUrlArray[1];				
							
							$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
							$sHostPart = ereg_replace("\/","",$sHostPart);

							$sScriptPath = substr($sUrlPart,strlen($sHostPart));
							
							if (strstr($sTempPostingUrl, "https:")) {								
								$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
							} else {								
								$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);								
							}
							
							
							$sFormPostResponse = "";
								
							if ($rSocketConnection) {
								$sScriptPath  .= "?".$sTempHttpPostStringRec;
								
								fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");					
								fputs($rSocketConnection, "Host: $sHostPart\r\n");
								fputs($rSocketConnection, "User-Agent: MSIE\r\n");
								fputs($rSocketConnection, "Connection: close\r\n\r\n");

								while(!feof($rSocketConnection)) {
									$sFormPostResponse .= fgets($rSocketConnection, 1024);
								}
																		
								fclose($rSocketConnection);							
							
								$sUpdateStatusQuery = "UPDATE $sOtDataTable
										   SET    processStatus = 'P',
												  sendStatus = 'S',
												  howSent = '$sTempHowSent',
												  dateTimeProcessed = now(),
												  dateTimeSent = now(),
												  realTimeResponse = \"".addslashes($sFormPostResponse)."\"
									 	  WHERE  id = '$iTempId'";
							
								$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
							
							} else {
								echo "$sTempOfferCode Form Post error: $errstr ($errno)<br />\r\n";
								//$sErrorInSendingLeads .= "<BR>$sTempOfferCode Form Post Error: $errstr ($errno)";
								$sTempSendingError = "$sTempOfferCode Form Post error: $errstr ($errno)";
								
							}
							
							//echo "<BR><BR>$sUpdateStatusQuery error- ".mysql_error();
							
							// keep 5 seconds delay between each post
							sleep(5);
							
						} else if ($iTempDeliveryMethodId == 11 && $sTestMode == '') {
							// single email per lead
							$sHeaders = "From: $sTempSingleEmailFromAddr\n";								
							$sHeaders .= "Reply-To: $sTempSingleEmailFromAddr\n";
							$sSingleEmailHeaders = '';
							$sTempSingleEmailBodyRec = '';
							
							$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";

							$sTempSingleEmailBodyRec = eregi_replace("\[email\]",$aTempLeadsRow['email'], $sTempSingleEmailBody);
							$sTempSingleEmailBodyRec = eregi_replace("\[salutation\]",$aTempLeadsRow['salutation'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[first\]",$aTempLeadsRow['first'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[last\]",$aTempLeadsRow['last'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[address\]",$aTempLeadsRow['address'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[address2\]",$aTempLeadsRow['address2'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[city\]",$aTempLeadsRow['city'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[state\]",$aTempLeadsRow['state'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[zip\]",$aTempLeadsRow['zip'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone\]",$aTempLeadsRow['phoneNo'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_areaCode\]",$aTempLeadsRow['phone_areaCode'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_exchange\]",$aTempLeadsRow['phone_exchange'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[phone_number\]",$aTempLeadsRow['phone_number'], $sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = eregi_replace("\[remoteIp\]",$aTempLeadsRow['remoteIp'], $sTempSingleEmailBodyRec);
							
							// get all the page2 fields of this offer and replace 
							$sPage2MapQuery = "SELECT *
											   FROM   page2Map
				 	 			   			   WHERE offerCode = '$sTempOfferCode'
				 				   			   ORDER BY storageOrder ";
		
							$rPage2MapResult = dbQuery($sPage2MapQuery);
							$f = 1;
							
							while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
																
								$sFieldVar = "FIELD".$f;
								
								$sTempSingleEmailBodyRec = eregi_replace("\[$sFieldVar\]",$aTempLeadsRow[$sFieldVar], $sTempSingleEmailBodyRec);
					
								$f++;
							}	

							
							$aTempSingleEmailBodyArray = explode("\\r\\n",$sTempSingleEmailBodyRec);
							$sTempSingleEmailBodyRec = "";
							
							for($x=0;$x<count($aTempSingleEmailBodyArray);$x++) {
								$sTempSingleEmailBodyRec .= $aTempSingleEmailBodyArray[$x]."\r\n";
							}
							mail($sTempLeadsEmailTo, $sTempSingleEmailSubject, $sTempSingleEmailBodyRec , $sHeaders);
							
						}
						
						
						// don't mark leads as send which are grouped
						// leads of a group should be marked all at once
						if ($sTestMode == '' && $sTempSendingError == '' && $iTempLeadsGroupId == 0) {
							$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable
							SET    sendStatus = 'S',
						 		   dateTimeSent = now(),
								   howSent = '$sTempHowSent'		
							WHERE  id = '$iTempId'
							AND    processStatus = 'P'								
							AND    sendStatus IS NULL";
							$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
							
						}
						
						if ($iTempId > $iLastIdReported) {
							$iLastIdReported = $iTempId;
						}
						
					} // end of lead query while loop
					
					// insert lead counts and lastIdReported if leads were sent real time
					if ($sTestMode == '' && $iLastIdReported != 0 && ( $iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4')) {
						
						$sLastIdReportedInsertQuery = "INSERT INTO realTimeDeliveryReporting(offerCode, counts, lastIdReported, dateTimeSent)
													   VALUES('$sTempOfferCode', '$iLeadsCount', '$iLastIdReported', now())";
						$rLastIdReportedInsertResult = dbQuery($sLastIdReportedInsertQuery);
					}
					
					
					// place lead count here, after while loop otherwise count will be wrong for mut. excl offer
					if ($sTempLeadsEmailSubject != '') {
						$sTempLeadsEmailSubject = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadsEmailSubject);
					}
					
					if ($sTempLeadFileName != '') {
						$sTempLeadFileName = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadFileName);
					}
					
					if ($sTempLeadsEmailBody != '') {
						$sTempLeadsEmailBody = eregi_replace("\[count\]", "$iLeadsCount", $sTempLeadsEmailBody);
					}
					
					//$sTempLeadsEmailSubject	= "Revised: ".$sTempLeadsEmailSubject;
					
					// send the leads as per lead delivery method
					// send individual lead file if offer is not grouped
					// If offer is grouped, send the leads groupwise
					if ($iTempLeadsGroupId == 0) {
						
						// use the encrypted file name if file is encrypted
						if ($iTempIsEncrypted && $sTempEncMethod == 'gpg') {
							$sTempLeadFileName .= ".gpg";
						}
						
						
						// if testing of lead delivery, then use the email address specified in leads processing screen
						
						
						// send leads data through specified delivery method
						// only if lead count is not 0
						echo "<BR>Send: $sTempOfferCode $iLeadsCount";
						if ($iLeadsCount != 0) {
							
							// send count email
						$sHeaders = "From: $sTempLeadsEmailFromAddr\n";						
						$sHeaders .= "Reply-To: $sTempLeadsEmailFromAddr\n";
						$sHeaders .= "X-Priority: 1\n";
						$sHeaders .= "X-MSMail-Priority: High\n";
						$sHeaders .= "X-Mailer: My PHP Mailer\n";
						if ($sTestMode) {
							$sDispCountEmailRecipients =  "Count Email Recipients: $sTempCountEmailRecipients\n\r\n\r";
							$sDispLeadsEmailRecipients =  "Leads Email Recipients: $sTempLeadsEmailRecipients\n\r\n\r";
						} else {
							$sDispCountEmailRecipients =  "";
							$sDispLeadsEmailRecipients =  "";
						}
						
						mail($sTempCountEmailTo, $sTempLeadsEmailSubject, $sDispCountEmailRecipients.$sTempLeadsEmailBody , $sHeaders);
							
							 if ($iTempDeliveryMethodId == 1 || $iTempDeliveryMethodId == 7) {

								// If delivery method is ftp daily batch, ftp the file
								
								if ($iTempDeliveryMethodId == 1) {
								// If delivery method is 'FTP Daily Batch'
								
								//$sLeadsProcessingNotes .= "<BR>Offer:$sTempOfferCode -  FTP File: $sTempLeadFileName -
									//			FTP Site: $sTempFtpSiteUrl - FTP Initial Dir: $sTempInitialFtpDirectory
										//		UserId: $sTempUserId - Password: $sTempPasswd";
								$rFtpConnection = 0;
								
								$rFtpConnection = ftp_connect($sTempFtpSiteUrl);
								
								if ($rFtpConnection) {
									//echo "connected";
									$bFtpMode = ftp_pasv($rFtpConnection, false);
									$bFtpLogin = ftp_login($rFtpConnection, $sTempUserId, $sTempPasswd);
									if ($bFtpLogin) {
										//echo "logged in";
										
										if ($sTempInitialFtpDirectory != '') {
											$bInitialFtpDirectory = ftp_chdir($rFtpConnection, $sTempInitialFtpDirectory);
										}										
										
										if ($sTempInitialFtpDirectory == '' || ($sTempInitialFtpDirectory != '' && $bInitialFtpDirectory)) {
											//echo ftp_pwd($rFtpConnection);	
											//$contents = ftp_nlist($rFtpConnection, ".");

// output $contents
//var_dump($contents);
																				
											$bUploadFile = ftp_put($rFtpConnection, $sTempLeadFileName , "$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", FTP_ASCII);
											//echo "upload ".$bUploadFile;
											if (!($bUploadFile)) {
												echo "<BR>$sTempOfferCode - error in uploading file";
											} else {
												echo "<BR>$sTempOfferCode - file uploaded";
											}
											
										} else {
											// error accessing initial FTP dir
											$sErrorInSendingLeads .= "<BR>$sTempOfferCode - Error accessing Initial FTP Directory";
											echo "<BR>$sTempOfferCode - error accessing initial dir";
										}
									}
		
		
   									ftp_close($rFtpConnection);							

								} else {
									echo "<BR>$sTempOfferCode - not connected";
									
								}
								
								/******
								$sEncodedLeadsData = hex_encode(htmlspecialchars($sLeadsData));
								$rFtpConnection = ftp_connect($sTempFtpSiteUrl);
								
								if ($rFtpConnection) {
								echo "connected";
								
								ftp_close($rFtpConnection);
								} else {
								echo "FTP Error";
								}
								******/
								
							} 
							
								// If delivery method is 'Daily Batch Email'
								
								$sHeaders = '';
								$sEmailMessage = '';
								$sLeadFileData = '';
								
								$sBorderRandom = md5(time());
								
								$sMailBoundry = "==x{$sBorderRandom}x";
																
								$sHeaders="From: $sTempLeadsEmailFromAddr\r\n";
								
								$sHeaders.="Reply-To: $sTempLeadsEmailFromAddr\r\n";
								$sHeaders.="X-Priority: 1\r\n";
								$sHeaders.="X-MSMail-Priority: High\r\n";
								$sHeaders.="X-Mailer: My PHP Mailer\r\n";
								$sHeaders.="Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
								
								$sHeaders .= "MIME-Version: 1.0\r\n";
								
								$sEmailMessage .= "This is a multi-part message in MIME format.\r\n\r\n";
								$sEmailMessage .= "--{$sMailBoundry}\r\n";
								$sEmailMessage .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
								$sEmailMessage .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
								$sEmailMessage .= "$sDispLeadsEmailRecipients $sTempLeadsEmailBody\r\n\r\n";
								
								// get attachemnt file data
								$rFpLeadFile = fopen("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName","r");
								if ($rFpLeadFile) {
									while (!feof($rFpLeadFile)) {
										$sLeadFileData .= fread($rFpLeadFile, 1024);
									}
									fclose($rFpLeadFile);
									
								} else {
									echo " can't open lead file";
								}
								
								$sLeadFileData = base64_encode($sLeadFileData);
								$sLeadFileData = chunk_split($sLeadFileData);
								echo $sTempLeadFileName;
								$sEmailMessage .= "--{$sMailBoundry}\r\n";
								$sEmailMessage .= "Content-type: text/plain; \r\n";
								$sEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
								$sEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sTempLeadFileName}\"\r\n\r\n";
								$sEmailMessage .= "$sLeadFileData\r\n";
								$sEmailMessage .= "--{$sMailBoundry}--\r\n";
								// send count
								
								//send lead data
								
								mail($sTempLeadsEmailTo, $sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);
								
							} else if ($iTempDeliveryMethodId == 8) {
								// If delivery method is 'Upload In Browser'
								
							}
							
							
						} // if lead count != 0
						
						// end groupId == 0
					} 
					
					if ($sErrorInSendingLeads == '' && $iTempLeadsGroupId == 0 && $sTestMode == '') {
						// update process status of all the leads of this offer
																	
						$sProcessStatusUpdateQuery = "UPDATE  $sUserDataTable, $sOtDataTable, offerLeadSpec
											  SET     sendStatus = 'S',
													  dateTimeSent = now(),
													  howSent = '$sTempHowSent',
											  WHERE   offerLeadSpec.offerCode = $sOtDataTable.offerCode 
											  AND     $sUserDataTable.email = $sOtDataTable.email 
											  AND     $sOtDataTable.offerCode = '$sTempOfferCode' 
											  AND 	  postalVerified = 'V' 
											  AND     processStatus = 'P'
											  AND     sendStatus IS NULL
											  AND 	  DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
											  AND 	  address NOT LIKE '3401 DUNDEE%' ";
						
						// don't check postal verification if testing from current table
						if ($sOtDataTable == 'otData') {
					
							$sProcessStatusUpdateQuery = eregi_replace("AND postalVerified = 'V'","", $sProcessStatusUpdateQuery);
							$sProcessStatusUpdateQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sProcessStatusUpdateQuery);
							$sProcessStatusUpdateQuery = eregi_replace("AND mode = 'A'","",$sProcessStatusUpdateQuery);
						}	
					}
					
				} // if get result of leads query
				
				
				
				// store groupId now as previous groupId
				
				$iTempPrevGroupId = $iTempLeadsGroupId;
			//	echo "Moved group Id to prevGroupId $iTempLeadsGroupId -> $iTempPrevGroupId";				
				
			} //offers while loop
			
		} // if offersQuery != ''
		
		
		// include separate lead file script
		include("$sGblAdminWebRoot/processLeads/separateFormatDelivery.php");
		
	} // send leads
	
	
	// call the script to update the leads sent count in 
	
	exec("php /home/sites/www_popularliving_com/crons/offerLeadsCountSum.php");
	
	
	
	} // end of if ($sTestMode && $sTestProcessingEmailRecipients == '')
} // end of if ($sExportData || $sImportData || $sProcessLeads || $sSendLeads)
	

// send lead counts to Fred
if ($sSendLeadCounts) {
	
	//$iRealTimeDaysBack = '1';
	
	$sCountsEmailContent = "<html><body><table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>
							<tr><td><font face=verdana size=1><b>Offer Code</b></font></td>
									<td align=right><font face=verdana size=1><b>Leads Count</b></font></td></tr>";
	
$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
$sHeaders .= "cc: ";

$sEmailQuery = "SELECT *
			   FROM   emailRecipients
			   WHERE  purpose = 'lead counts'";
$rEmailResult = dbQuery($sEmailQuery);
echo dbError();
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
	
}

if (!($sEmailTo)) {
	$sLeadCountsEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
} 
	
$sCcTo = substr($sRecipients,strlen($sLeadCountsEmailTo));
	
$sHeaders .= ", $sCcTo";
	
$sHeaders .= "\r\n";



// get lead counts of all the offers except real time and form post

	$sLeadCountsQuery = "SELECT otDataHistory.offerCode, count(otDataHistory.dateTimeSent) AS counts 
						 FROM   otDataHistory 
						 WHERE  date_format(otDataHistory.dateTimeSent, '%Y-%m-%d') = CURRENT_DATE
						 AND	sendStatus = 'S'
						 AND	howSent NOT IN ('rtfpp', 'rtfpg', 'rte', 'dbfpg', 'dbfpp')
						 GROUP BY offerCode";
	$rLeadCountsResult = dbQuery($sLeadCountsQuery);
	echo dbError();
	$iTotalLeads = 0;
	while ($oLeadCountsRow = dbFetchObject($rLeadCountsResult)) {
		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>$oLeadCountsRow->offerCode</font></td>
									<td align=right><font face=verdana size=1>$oLeadCountsRow->counts</font></td></tr>";
		$iTotalLeads += $oLeadCountsRow->counts;
	}
		
	// get real time offers counts
	$sRealTimeLeadCountsQuery = "SELECT otDataHistory.offerCode, count(otDataHistory.email) AS counts
								 FROM   otDataHistory, offerLeadSpec
								 WHERE  otDataHistory.offercode = offerLeadSpec.offerCode
								 AND	date_format(otDataHistory.dateTimeSent, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
								 		AND date_add(CURRENT_DATE, INTERVAL -1 DAY) 
								 AND    sendStatus = 'S'
								 AND    howSent IN ('rtfpp', 'rtfpg', 'rte')
							     AND    deliveryMethodId IN ('2','3','4')
								 GROUP BY offerLeadSpec.offerCode";
	$rRealTimeLeadCountsResult = dbQuery($sRealTimeLeadCountsQuery);
	echo dbError();
	while ($oRealTimeLeadCountsRow = dbFetchObject($rRealTimeLeadCountsResult)) {
		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>$oRealTimeLeadCountsRow->offerCode</font></td>
									<td align=right><font face=verdana size=1>$oRealTimeLeadCountsRow->counts</font></td></tr>";
		$iTotalLeads += $oRealTimeLeadCountsRow->counts;
	}

	$sCountsEmailContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td>
									<td align=right><font face=verdana size=1><b>$iTotalLeads</b></font></td></tr>";
	$sCountsEmailContent .= "</table></body></html>";
	
	
	$sLeadsCountEmailSubject = "Lead Counts - $sRunDate";

	mail($sLeadCountsEmailTo, $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);
	
	$sMessage = "Lead Counts Email Is Sent...";
	
}

if (!($iRealTimeDaysBack)) {
	if (date('D') =='Mon') {
		$iRealTimeDaysBack = "3";
	} else {
		$iRealTimeDaysBack = "1";
	}
}

switch($iRealTimeDaysBack) {	
	case "2":
		$sTwoSelected = "selected";
		break;
	case "3":
		$sThreeSelected = "selected";
		break;
	case "4":
		$sFourSelected = "selected";
		break;	
	case "5":
		$sFiveSelected = "selected";
		break;
	case "6":
		$sSixSelected = "selected";
		break;
	default:
	$sOneSelected = "selected";
	
}

$sRealTimeDaysBackOptions = "<option value='1' $sOneSelected>1
							  <option value='2' $sTwoSelected>2
							  <option value='3' $sThreeSelected>3
							  <option value='4' $sFourSelected>4
							  <option value='5' $sFiveSelected>5
							  <option value='6' $sSixSelected>6";

	// get various count to display before processing the leads
	
	// get number of leads processed last 10 days
	/*
	$sTenDaysLeadsQuery = "SELECT date_format(dateTimeProcessed,\"%Y-%m-%d\") AS processDate, count(*) AS counts
					   FROM   otDataHistory
					   WHERE  date_format(dateTimeProcessed,\"%Y-%m-%d\") >= DATE_ADD(CURRENT_DATE, INTERVAL -9 DAY)
					   AND    processStatus IS NOT NULL
					   AND    sendStatus IS NOT NULL
					   GROUP BY processDate"; 
	$rTenDaysLeadsResult = dbQuery($sTenDaysLeadsQuery);
	echo dbError();
	
	while ($oTenDaysLeadsRow = dbFetchObject($rTenDaysLeadsResult)) {
		$sTenDaysCounts .= "$oTenDaysLeadsRow->processDate - $oTenDaysLeadsRow->counts<br>";
		
	}
	*/
	
	/*// No of leads would be processed if hit the Process button
	
	$sTodaysCountQuery = "SELECT count(*) AS counts
					  FROM   otDataHistory, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					  WHERE  otDataHistory.offerCode = offerLeadSpec.offerCode
					  AND    (processStatus IS NULL || processStatus = 'P')
					  AND    sendStatus IS NULL
					  AND    DATE_ADD(date_format(dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE
					  AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )";
	$rTodaysCountResult = dbQuery($sTodaysCountQuery);
	echo dbError();
	while ($oTodaysCountRow = dbFetchObject($rTodaysCountResult)) {
		
		$iTodaysCount = $oTodaysCountRow->counts;
	}
	*/
	// Date Added Count
	/*
	$sDateAddedQuery = "SELECT date_format(dateTimeAdded,\"%Y-%m-%d\") dateAdded, count(*) counts
					FROM   otDataHistory, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					WHERE  otDataHistory.offerCode = offerLeadSpec.offerCode
					AND    (processStatus IS NULL || processStatus = 'P')
					AND    sendStatus IS NULL 
					AND    DATE_ADD(date_format(dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE
 					AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )
					GROUP BY dateAdded";
	$rDateAddedResult = dbQuery($sDateAddedQuery);
	while ($oDateAddedRow = dbFetchObject($rDateAddedResult)) {
		$sDateAddedCounts .= "$oDateAddedRow->dateAdded - $oDateAddedRow->counts<BR>";
	}
	echo dbError();
	*/
	// get the offers list which is not grouped
	$sOffersQuery = "SELECT offers.*
				 FROM   offers, offerLeadSpec
				 WHERE  offers.offerCode = offerLeadSpec.offerCode
				 AND    leadsGroupId = 0				 
				 ORDER BY offerCode"; 
	$rOffersResult = dbQuery($sOffersQuery);
	echo dbError();
	$sOffersOptions .= "<option value=''>OfferCode";
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		if ($oOffersRow->offerCode == $sOfferCode)
		{
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		
		$sOffersOptions .= "<option value='$oOffersRow->offerCode' $sOfferCodeSelected>$oOffersRow->offerCode";
	}
	
	
	// get the groups list
	$sGroupsQuery = "SELECT *
				 FROM   leadGroups
				 ORDER BY name"; 
	$rGroupsResult = dbQuery($sGroupsQuery);
	$sGroupsOptions .= "<option value=''>Lead Group";
	while ($oGroupsRow = dbFetchObject($rGroupsResult)) {
		if ($oGroupsRow->id == $iGroupId)
		{
			$sGroupSelected = "selected";
		} else {
			$sGroupSelected = "";
		}
		$sGroupsOptions .= "<option value='$oGroupsRow->id' $sGroupSelected>$oGroupsRow->name";
	}
	
	//
	$sProcessAllChecked = "";
	$sProcessOneChecked = "";
	$sRerunOneChecked = "";
	$sRerunAllChecked = "";
	
	switch($sProcessOption) {
		case "processOne":
		$sProcessOneChecked = "checked";
		break;
		case "rerunOne":
		$sRerunOneChecked = "checked";
		break;
		case "rerunAll":
		$sRerunAllChecked = "checked";
		break;
		default:
		$sProcessAllChecked = "checked";
	}
	
	
	if ($sTestMode) {
		$sTestModeChecked = "checked";
	}
	

	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31

	if (!($iStartMonth && $iStartDay && $iStartYear)) {
		$iStartMonth = $iCurrMonth;		
		$iStartDay = $iCurrDay;		
		$iStartYear = $iCurrYear;		
	} 
	
	if (!($iEndMonth && $iEndDay && $iEndYear)) {
		$iEndMonth = $iCurrMonth;		
		$iEndDay = $iCurrDay;		
		$iEndYear = $iCurrYear;		
	} 
		
// prepare month options for From and To date

$sStartMonthOptions = "";
$sEndMonthOptions = "";

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$iValue = $i+1;
	
	if ($iValue < 10) {
		$iValue = "0".$iValue;
	}
	
	if ($iValue == $iStartMonth) {
		$sStartMonthSel = "selected";
	} else {
		$sStartMonthSel = "";
	}
	if ($iValue == $iEndMonth) {
		$sEndMonthSel = "selected";
	} else {
		$sEndMonthSel = "";
	}
	
	
	$sStartMonthOptions .= "<option value='$iValue' $sStartMonthSel>$aGblMonthsArray[$i]";
	$sEndMonthOptions .= "<option value='$iValue' $sEndMonthSel>$aGblMonthsArray[$i]";
}


// prepare day options for From and To date
$sStartDayOptions = "";
$sEndDayOptions = "";

for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$iValue = "0".$i;
	} else {
		$iValue = $i;
	}
	
	if ($iValue == $iStartDay) {
		$sStartDaySel = "selected";
	} else {
		$sStartDaySel = "";
	}
	
	if ($iValue == $iEndDay) {
		$sEndDaySel = "selected";
	} else {
		$sEndDaySel = "";
	}		
	
	$sStartDayOptions .= "<option value='$iValue' $sStartDaySel>$i";
	$sEndDayOptions .= "<option value='$iValue' $sEndDaySel>$i";			
}

// prepare year options for From and To date
$sStartYearOptions = "";
$sEndYearOptions = "";

for ($i = $iCurrYear-1; $i <= $iCurrYear+5; $i++) {
	
	if ($i == $iStartYear) {
		$sStartYearSel = "selected";
	} else {
		$sStartYearSel ="";
	}
		
	if ($i == $iEndYear) {
		$sEndYearSel = "selected";
	} else {
		$sEndYearSel = "";
	}
	
		
	$sStartYearOptions .= "<option value='$i' $sStartYearSel>$i";
	$sEndYearOptions .= "<option value='$i' $sEndYearSel>$i";	
}


if ($sUseCurrentTable == 'Y') {
	$sUseCurrentTableChecked = "checked";
}

	
	$sSetLeadsBackLink = "<a href='JavaScript:void(window.open(\"setBack.php?iMenuId=$iMenuId\",\"setBack\",\"width=550 height=300, scrollbars=yes, resizable=yes\"));'>Set Leads Back</a>";
	$sRealTimePostLink = "<a href='JavaScript:void(window.open(\"realTimePost.php?iMenuId=$iMenuId\",\"setBack\",\"width=550 height=300, scrollbars=yes, resizable=yes\"));'>Real Time Post</a>";
	
	include("../../includes/adminHeader.php");
	
?>
	
<script language=JavaScript>

function leadDetails() {
	var offerIndex = document.form1.sOfferCode.selectedIndex;
	var offerCode = document.form1.sOfferCode.options[offerIndex].value;
	var groupIndex = document.form1.iGroupId.selectedIndex;
	var groupId = document.form1.iGroupId.options[groupIndex].value;
	
	
	if (offerCode != '' && groupId != '') {
		alert("You must select any one either from Offer or from Group list to view lead specific details");
	} else if (offerCode != '') {
		//var leadDetailsUrl = "offerLeadSpec.php";
		var leadDetailsUrl = "<?php echo $sGblAdminSiteRoot;?>/offersMgmnt/addOffer.php?menuId=18&sOfferCode="+offerCode;
		//leadDetailsUrl = leadDetailsUrl + "?sOfferCode=" + offerCode;
		var newWin = window.open(leadDetailsUrl,"leadSpec","height=450, width=600, scrollbars=yes, resizable=yes, status=yes");
	} else if (groupId != '') {
		var leadDetailsUrl = "<?php echo $sGblAdminSiteRoot;?>/leadGroups/addGroup.php?menuId=21&iId="+groupId;
		var newWin = window.open(leadDetailsUrl,"leadSpec","height=450, width=600, scrollbars=yes, resizable=yes, status=yes");
		
	} else {
		alert("You must select an Offer or Group to view lead specific details");
	}
}


function enableOfferGroup() {
		document.form1.sOfferCode.disabled=false;
		document.form1.iGroupId.disabled=false;
}

function disableOfferGroup() {
		document.form1.sOfferCode.options[0].selected = true;
		document.form1.iGroupId.options[0].selected = true;
		document.form1.sOfferCode.disabled=true;
		document.form1.iGroupId.disabled=true;				
}

function enableDateSelector() {
	document.form1.iStartMonth.disabled=false;
	document.form1.iStartDay.disabled=false;
	document.form1.iStartYear.disabled=false;		
	document.form1.iEndMonth.disabled=false;
	document.form1.iEndDay.disabled=false;
	document.form1.iEndYear.disabled=false;	
}

function disableDateSelector() {
	document.form1.iStartMonth.disabled=true;
	document.form1.iStartDay.disabled=true;
	document.form1.iStartYear.disabled=true;	
	document.form1.iEndMonth.disabled=true;
	document.form1.iEndDay.disabled=true;
	document.form1.iEndYear.disabled=true;	
}

function checkFileName() {
	if (document.form1.fImportFile.value == '') {
		alert('Please Select The .csv File to Import The Postal Verified Data...');
		return false;
	} else {
		return true;
	}
}
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >
<input type=hidden name=iMenuId value='<?php echo $iMenuId;?>'>
<table cellpadding=3 cellspacing=0 width=95% align=center>
	<tr><td class=message align=center><?php echo $sErrorInSendingLeads;?></td><td></tr>
	</table>
<?php echo $sHidden;?>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan=2 class=header><?php echo $sSetLeadsBackLink;?> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=setLeadsBack", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a>
				&nbsp; &nbsp; <?php echo $sRealTimePostLink;?> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=realTimePost", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a></td><td></tr>
	<!--<tr><td class=header>No. of leads processed for last 10 days</td><td>
	<php //echo $sTenDaysCounts;?></td></tr>
	<tr><td class=header colspan=2>No of leads would be processed today: <php //echo $iTodaysCount;?></td></tr>
	<tr><td class=header valign=top>List the date that today's leads were added<BR><BR></td><td>
	<php //echo $sDateAddedCounts;?></td></tr>-->
	<tr><td colspan=2><BR></td></tr>
	<tr><td colspan=2>Abbreviations used in leads table:</td></tr>
	<tr><td colspan=2><b>processStatus:</b> NULL = Not Processed, P = Processed, R = Rejected
					  <BR><b>reasonCode:</b> tst = Test Lead, ncv = Not Custom Verified, meo = Mutually Exclusive Offer
					  <BR><b>sendStatus:</b> NULL = Not Sent, S = Sent</td></tr>
	<tr><td colspan=2><BR><BR></td></tr>
	<tr><td><b>Step 1:</b> </td><td><input type=submit name=sExportData value='Export Data For Postal Verification'></td></tr>
	<tr><td><b>Step 2:</b> </td><td><input type=file name='fImportFile'> &nbsp; <input type=submit name=sImportData value='Import Postal Verified Data' onClick='return checkFileName();'></td></tr>
	<tr><td colspan=2><BR><BR></td></tr>
	<tr><td><b>Step 3:</b> </td>
		<td><input type=radio name=sProcessOption value='processAll' <?php echo $sProcessAllChecked;?> onClick='disableOfferGroup(); disableDateSelector();'>Process All Leads
			&nbsp; &nbsp; <input type=radio name=sProcessOption value='processOne' <?php echo $sProcessOneChecked;?> onClick='enableOfferGroup(); disableDateSelector();'>Process One
			&nbsp; &nbsp; <input type=radio name=sProcessOption value='rerunOne' <?php echo $sRerunOneChecked;?> onClick='enableOfferGroup(); enableDateSelector();'>Rerun One
			&nbsp; &nbsp; <input type=radio name=sProcessOption value='rerunAll' <?php echo $sRerunAllChecked;?> onClick='disableOfferGroup(); enableDateSelector();'>Rerun All
	</td></tr>
	<tr><td></td><td>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
					&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
			Rerun Date Range From: <select name=iStartMonth>
			<?php echo $sStartMonthOptions;?>
			</select> &nbsp;<select name=iStartDay>
			<?php echo $sStartDayOptions;?>
			</select> &nbsp;<select name=iStartYear>
			<?php echo $sStartYearOptions;?>
			</select>
			&nbsp; To: 
			<select name=iEndMonth>
			<?php echo $sEndMonthOptions;?>
			</select> &nbsp;<select name=iEndDay>
			<?php echo $sEndDayOptions;?>
			</select> &nbsp;<select name=iEndYear>
			<?php echo $sEndYearOptions;?>
			</select>
			</td></tr>
	
	<tr><td><b>Step 4:</b> Select Either Offer Or Lead Group<BR>
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (For <b>Process One</b> Or <b>Rerun One</b> Only)</td><td><select name=sOfferCode>
		<?php echo $sOffersOptions;?>
		</select>
		<select name=iGroupId>
		<?php echo $sGroupsOptions;?>
		</select> &nbsp; &nbsp; <a href='JavaScript:void(leadDetails());'>Lead Specific Details</a>
		</td>
	</tr>
	<tr><td><b>Step 5:</b> </td><td><input type=submit name=sProcessLeads value='Process Leads'> &nbsp; &nbsp; &nbsp; Use Current Table <input type=checkbox name=sUseCurrentTable value='Y' <?php echo $sUseCurrentTableChecked;?>>
		<A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=useCurrentTable", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a></td></tr>		
	<tr><td><b>Step 6:</b> </td><td nowrap><input type=submit name=sSendLeads value='Send Leads Out'>		
		&nbsp; Test Mode <input type=checkbox name=sTestMode value="1" <?php echo $sTestModeChecked;?>> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=testMode", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a> &nbsp; 
		&nbsp; Test Email Recipient(s)<input type=text name=sTestProcessingEmailRecipients value="<?php echo $sTestProcessingEmailRecipients;?>" size=50>
		<BR><BR><input type=submit name=sSendFormPostLeads value='Send Form Post Leads Out'> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=sendFormPostLeads", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a>
		</td></tr>
		
	<tr><td><b>Step 7:</b> </td><td><input type=submit name=sSendLeadCounts value='Send Lead Counts to Fred'>
		Calculate real time leads of last <select name=iRealTimeDaysBack><?php echo $sRealTimeDaysBackOptions;?></select> days. 
		<A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=realTimeDaysBack", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a></td></tr>		
		
	<tr><td colspan=2 class=header><BR>Note:</td></tr>
	<tr><Td colspan=2>Form Post and Single email leads will not be sent with test mode.</td></tr>
	
	</tr>
</table>
<!--<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td align=center><input type=submit name=sProcessAll value="Process All Leads">
		&nbsp; &nbsp; <input type=submit name=sProcessOne value="Process One">
		&nbsp; &nbsp; <input type=submit name=sRerunOne value="Rerun One">
		&nbsp; &nbsp; <input type=submit name=sRerunAll value="Rerun All">
	</tr>
</table>-->
</form>
	

<?php

if ($sProcessAllChecked == 'checked' || $sRerunAllChecked == 'checked') {
echo "<script language=JavaScript>
disableOfferGroup();
</script>";
} else {
	echo "<script language=JavaScript>
	enableOfferGroup();
	</script>";
}


if ($sProcessAllChecked == 'checked' || $sProcessOneChecked == 'checked') {
echo "<script language=JavaScript>
disableDateSelector();
</script>";
} else {
	echo "<script language=JavaScript>
	enableDateSelector();
	</script>";
}


include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}
?>
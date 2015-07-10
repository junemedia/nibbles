<?php

ini_set('max_execution_time', 5000);

/*********
Script to Process and Send Leads
**********/
// kdn_inv count rec: phil@myfree.com, keith@amperemedia.com, bulebosh.becky@davison54.com, michaels.jude@davison54.com, leads@amperemedia.com
// KDN_INV form post url https://www.davison54.com/tools/leadcollect/index.php

//counts phil@myfree.com, jr@myfree.com, leads@amperemedia.com
//lead fred@amperemedia.com, leads@amperemedia.com


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$iPvThreshold = 90;
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

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

	/***************  check if dedup script is finished  **********************/
	$sDedupVarQuery = "SELECT *
				   FROM	  vars
				   WHERE  system = 'cron'
				   AND	  varName = 'dedupScriptRunning'";
	$rDedupVarResult= dbQuery($sDedupVarQuery);
	while ($oDedupVarRow = dbFetchObject($rDedupVarResult)) {
		$iDedupScriptVar = $oDedupVarRow->varValue;
	}

	$sOvernightDataMoveVarQuery = "SELECT *
				   FROM	  vars
				   WHERE  system = 'cron'
				   AND	  varName = 'overnightDataMove'";
	$rOvernightDataVarResult= dbQuery($sOvernightDataMoveVarQuery);
	while ($oOvernightDataMoveVarRow = dbFetchObject($rOvernightDataVarResult)) {
		$iOvernightDataMoveScriptVar = $oOvernightDataMoveVarRow->varValue;
	}

	/*****************  End checking if dedup script is running  *****************/


	// Check user permission to access this page

	if ( $sDailyProcessing || $sExportData || $sImportData || $sProcessLeads || $sSendLeads || $sSendFormPostLeads) {

		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Processed leads\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		// end of track users' activity in nibbles		

		
		
		if ($sTestMode && $sTestProcessingEmailRecipients == '') {
			$sErrorInSendingLeads = "Please Enter Test Email Recipients For Test Leads...";

		} else if (($iDedupScriptVar != '0') || ($iOvernightDataMoveScriptVar != '0') ) {
			$sErrorInSendingLeads = "Dedup Script Is Running. Leads Can't be Processed Before Script is Finished...";
		} else {

			if ($sDailyProcessing) {

				echo '.';
				flush();
				ob_flush();
				//First, mark all the leads as test leads which are collected in Test mode
				$sOtDataUpdateQuery = "UPDATE otDataHistory
									 SET    processStatus = 'R',
											reasonCode = 'tst',
											dateTimeProcessed = now(),
											sendStatus = 'N',
											dateTimeSent = now()
									 WHERE  mode = 'T'
									 		AND (reasonCode <> 'tst' or reasonCode IS NULL)
									 		AND (sendStatus <> 'N' or sendStatus IS NULL)
									 		AND (processStatus <> 'R' or processStatus IS NULL)";

				$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);
				
				echo '.';
				flush();
				ob_flush();
				/************  mark 3401 leads as rejected  *************/

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
				/*************  End marking 3401 leads as rejected  *************/
				echo '.';
				flush();
				ob_flush();
				$sMessage .= "Daily Maintenance Performed";


			} else if ($sProcessLeads) {
				echo "<br>Process Leads Started.<br>";
				flush();
				ob_flush();
				
				$sThresholdTotal = "SELECT count(*) as total FROM otDataHistory WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)";
				$rThresholdTotal = dbQuery( $sThresholdTotal );
				$oThresholdTotal = dbFetchObject( $rThresholdTotal );
				$iThresholdTotal = $oThresholdTotal->total;

				$sThresholdPV = "SELECT count(*) as pv FROM otDataHistory WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)
								AND postalVerified = 'V'";
				$rThresholdPV = dbQuery( $sThresholdPV );
				$oThresholdPV = dbFetchObject( $rThresholdPV );
				$iThresholdPV = $oThresholdPV->pv;

				if( $iThresholdTotal == 0 ) {
					$iThresholdPct = 0;
				} else {
					$iThresholdPct = intval(10000*($iThresholdPV / $iThresholdTotal))/100;
				}
				echo '.';
				flush();
				ob_flush();
				if( $iThresholdPct < $iPvThreshold ) {
					$sMessage .= "Less than $iThresholdPct % leads were postal verified.  Please verify data.";
				} else {
				echo '.';
				flush();
				ob_flush();
					/**************  get one offer/group which and make sure it is scheduled to process leads today  *****************/
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
					/**************  End getting one offer/group and check it's schedule  ***********/
				echo '.';
				flush();
				ob_flush();
					// include custom leads validation script here

					include("$sGblIncludePath/customProcessing.php");
				echo '.';
				flush();
				ob_flush();


					if ($sProcessOption == "rerun" || $sProcessOption == "rerunOne") {
				echo '.';
				flush();
				ob_flush();
						/*********** This is not implemented and tested yet **************/

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

						/***********************************/

					} else {
				echo '.';
				flush();
				ob_flush();
						/**************  get all active offers and process it's leads  **************/
						// Processing will prepare the lead file as per spec and mark the lead's processStatus field as 'P' ( Processed)
						// All the leads which is not already sent ( sendStatus = 'S') will be processed again if not rejected
						// Processing will process all the offers even if it's not scheduled,
						// As all the reports looks for processStatus= 'P' if the lead is valid
						// this way, offers which are scheduled only once per week, will be reflected correctly in postal verified report

						$sOffersQuery = "SELECT offerLeadSpec.*
								 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
								 WHERE  offers.offerCode = offerLeadSpec.offerCode
								 AND    activeDateTime <= now() 
								 AND    lastLeadDate >= CURRENT_DATE
								 AND    isOpenTheyHost = 'N'";

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
				echo '.';
				flush();
				ob_flush();
						}

					}

					// get the offers list/one offer to get leads for
					if ($sOffersQuery != '') {
						echo '.';
						flush();
						ob_flush();
						$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
						$rOffersResult = dbQuery($sOffersQuery);
						echo dbError();

						while ($oOffersRow = dbFetchObject($rOffersResult)) {
							echo '.';
							flush();
							ob_flush();
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
							$sTempLeadsEmailBody = $oOffersRow->leadsEmailBody;
				
							echo '.';
							flush();
							ob_flush();
							/***********  Replace tags with values in lead file name  **************/
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
										echo '.';
										flush();
										ob_flush();
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
									echo '.';
									flush();
									ob_flush();
							}
							/**********  End replacing tags with values in lead file name  *********/

							/**********  Replace tags with values in headerText  ***********/
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
									echo '.';
									flush();
									ob_flush();
							}
							/*************  End replacing tags with values in headerText  **********/
							echo '.';
							flush();
							ob_flush();
							/********** before getting new group data,
							set header and footer in group lead file for previous groupId, only if file is combined   *************/
							// If previous groupId was not 0, and current groupId is different than previous,
							// that means offers of that group are over.
							// Before getting group details for offer in current loop,
							// place header and footer in previous group's file if the file was combined
							// if the file was not combined, Nothing need to be done here
							// because all the separate files of that group are handled fully ( adding header, footer etc)
							// in it's loop. This is only for combined group file.

							if ($iTempPrevGroupId != 0 && $iTempPrevGroupId != $iTempLeadsGroupId && $iTempGrIsFileCombined && ($sTempGrHeaderText != '' || $sTempGrFooterText != '')) {

								$sTempData = '';

								$rFpGrLeadFileRead = fopen("$sTodaysLeadsFolder/groups/$sTempGrName/$sTempGrLeadFileName", "r");


								if ($rFpGrLeadFileRead) {

									while (!feof($rFpGrLeadFileRead)) {
										$sTempData .= fread($rFpGrLeadFileRead, 1024);
									}

									fclose($rFpGrLeadFileRead);
								}
								echo '.';
								flush();
								ob_flush();
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

							/***************  End setting header and footer in combined group file  ***********/

							/***********  get lead specific data from leadGroups table if offer is grouped
							and lead group is not the same as previous loop  **************/

							if ($iTempLeadsGroupId != 0) {
							echo '.';
							flush();
							ob_flush();
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
									//$iTempGrIsSecured = $oLeadsGroupRow->isSecured;
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
							echo '.';
							flush();
							ob_flush();
							}
							/*************  End getting lead specific details  ****************/

							/************ get leads data for this offer
							get id of the lead record and mark it processed, one by one until mysql upgraded  **********/


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
								} else {
									$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","AND $sUserDataTable.postalVerified = 'V'",$sTempLeadsQuery);
								}
							}

							/*************  Not implemented yet  **************/
							if ($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll") {

								echo $sTempLeadsQuery;
							}
							/*************************/

							$rTempLeadsResult = dbQuery($sTempLeadsQuery);

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

							echo '.';
							flush();
							ob_flush();
								/*******  Check if the offer has any mutually exclusive offers  *********/
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
								/********  End checking if offer has any mutually exclusive offers  ********/


								/**************  Get the next lead count numbers for the offer  *************/
								if (!($sProcessOption == "rerunOne" || $sProcessOption == "rerunAll") ) {
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
								/*********  End getting next lead count numbers for the offer  *********/

								while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {

									$iId = $aTempLeadsRow['id'];
									$sTempLeadEmail = $aTempLeadsRow['email'];


									/**************  Check if leads are mutually exclusive  ****************/


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

									/*************  End checking mutually exclusive leads  *************/
							echo '.';
							flush();
							ob_flush();

									/********  Update process status and leadcounter only if lead not delivered real time  *******/
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
										/**********  End updating process status and lead counter  ********/


										/*********  If delivery method is not 'leads in email body',
										Prepare lead data as per specification details like field separator, field delimiter etc  *******/

										if ($iTempDeliveryMethodId != '13' ) {
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


										} else {
											/******** if delivery method = 13 - daily batch email - leads in email body
											replace fields with values in email body  ********/


											$sTempLeadsEmailBodyRec = '';

											$sTempLeadsEmailBodyRec = eregi_replace("\[email\]",$aTempLeadsRow['email'], $sTempLeadsEmailBody);
											$sTempLeadsEmailBodyRec = eregi_replace("\[salutation\]",$aTempLeadsRow['salutation'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[first\]",$aTempLeadsRow['first'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[last\]",$aTempLeadsRow['last'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[address\]",$aTempLeadsRow['address'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[address2\]",$aTempLeadsRow['address2'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[city\]",$aTempLeadsRow['city'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[state\]",$aTempLeadsRow['state'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[zip\]",$aTempLeadsRow['zip'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[phone\]",$aTempLeadsRow['phoneNo'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[phone_areaCode\]",$aTempLeadsRow['phone_areaCode'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[phone_exchange\]",$aTempLeadsRow['phone_exchange'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[phone_number\]",$aTempLeadsRow['phone_number'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[remoteIp\]",$aTempLeadsRow['remoteIp'], $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[yyyy\]",substr($aTempLeadsRow['dateTimeAdded'],0,4), $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[mm\]",substr($aTempLeadsRow['dateTimeAdded'],5,2), $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[dd\]",substr($aTempLeadsRow['dateTimeAdded'],8,2), $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[hh\]",substr($aTempLeadsRow['dateTimeAdded'],11,2), $sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = eregi_replace("\[ii\]",substr($aTempLeadsRow['dateTimeAdded'],14,2), $sTempLeadsEmailBodyRec);



											// get all the page2 fields of this offer and replace
											$sPage2MapQuery = "SELECT *
											   FROM   page2Map
				 	 			   			   WHERE offerCode = '$sTempOfferCode'
				 				   			   ORDER BY storageOrder ";

											$rPage2MapResult = dbQuery($sPage2MapQuery);
											$f = 1;

											while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {

												$sFieldVar = "FIELD".$f;

												$sTempLeadsEmailBodyRec = eregi_replace("\[$sFieldVar\]",$aTempLeadsRow[$sFieldVar], $sTempLeadsEmailBodyRec);


												$f++;
											}


											$aTempLeadsEmailBodyArray = explode("\\r\\n",$sTempLeadsEmailBodyRec);
											$sTempLeadsEmailBodyRec = "";

											for($x=0; $x<count($aTempLeadsEmailBodyArray); $x++) {
												$sTempLeadsEmailBodyRec .= $aTempLeadsEmailBodyArray[$x]."\r\n";
											}

											$sLeadsData .= $sTempLeadsEmailBodyRec;
											/*****************************/

										} // end of if($iTempDeliveryMethodId == '13')

									} // end of delivery method id condition
								} // end of leads data while loop

								echo "<BR>$sTempOfferCode - $iLeadsCount";
								flush();
								ob_flush();


								/***********  add header and footer text if file not grouped  **********/
								// ( if file grouped, header and footer will be added after combining the files)

								if ($sTempHeaderText != '') {
									$sLeadsData = "$sTempHeaderText\r\n$sLeadsData";
								}
								if ($sTempFooterText != '') {
									$sLeadsData .= "\r\n$sTempFooterText";
								}
								/***********  End adding header and footer  ************/


								/**************  Stored the prepared lead file  **************/

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
								} else {

								}

								/********** if offer is grouped, put the separate lead file of this offer in groups folder
								or append to group file if it should be combined in one file  **********/

								if ($iTempLeadsGroupId) {
							echo '.';
							flush();
							ob_flush();

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
								/*********  End if offer is grouped  ********/
							echo '.';
							flush();
							ob_flush();
								/*************  End storing prepared lead file  ****************/



								// now store groupId as previous groupId

								$iTempPrevGroupId = $iTempLeadsGroupId;
							}


							// If the last record was with groupId or leads processed for only one group,
							// set header and footer in group lead file here only if file is combined and header/footer is not blank
							// because there will not be NEXT record to decide that now the offer for a group are over and can put header and footer.


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
							echo '.';
							flush();
							ob_flush();
						} // end of offers while loop

					} // if offersQuery != ''
				}
			} else if (($sSendLeads || $sSendFormPostLeads) && ($sProcessOption == 'processAll' || $sProcessOption == 'processOne')) {


				// get all active offers

				$sOffersQuery = "SELECT offerLeadSpec.*
					 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					 WHERE  offers.offerCode = offerLeadSpec.offerCode
					 AND    activeDateTime <= now() 
					 AND    lastLeadDate >= CURRENT_DATE 
					 AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )
					 AND    isOpenTheyHost = 'N'";

				if ($sSendFormPostLeads) {
							echo '.';
							flush();
							ob_flush();
					$sOffersQuery .= " AND offerLeadSpec.deliveryMethodId = '5'";
				} else {
							echo '.';
							flush();
							ob_flush();
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
						//$iTempIsSecured = $oOffersRow->isSecured;
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

						// if delivery method is 'Manual', sent leads email to leads@amperemedia.com only
						if ($iTempDeliveryMethodId == '12') {
							$sTempLeadsEmailRecipients = "leads@amperemedia.com";
						}

						$sTempHowSent = '';

						/********  Get delivery method short description  ***********/
						$sDeliveryMethodQuery = "SELECT *
									 FROM   deliveryMethods
									 WHERE  id = '$iTempDeliveryMethodId'";
						$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
						while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
							$sTempHowSent = $oDeliveryMethodRow->shortMethod;
						}
						/***********  End getting delivery method short description  *********/

							echo '.';
							flush();
							ob_flush();
						/********  Replace tags with values in lead file name  ***********/
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

						/***********  End replacing tags with values in lead file name  ***********/

						/************  Replace tags with values in header text  **************/
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
						/************  End replacing tags in header text  **************/
							echo '.';
							flush();
							ob_flush();
						/************  Replace tags with values in leads email subject  ************/

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
						/***************  End replacing tags in leads email subject  ***************/

						/************  Replace tags with values in leads email body  ***********/
						if ($sTempLeadsEmailBody != '') {
							$sTempLeadsEmailBody = eregi_replace("\[offerCode\]", $sTempOfferCode, $sTempLeadsEmailBody);
						}
						/*************  End replacing tags in leads email body  ***********/

						/*************  Replace tags with values in single email subject  *************/
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
						/****************  End replacing tags in single email subject  ***************/

						/********  Before getting new group data,  send the leads of previous group  ********/
						// group details is already in the variables
						// send group email if this is the last record of offer loop (necessary in the case when processing one group)

						if (($iTempLeadsGroupId != 0  && $iTempPrevGroupId != $iTempLeadsGroupId )) {

							echo "<BR>Sending group email for $iTempLeadsGroupId";
							flush();
							ob_flush();
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
								//$iTempGrIsSecured = $oLeadsGroupRow->isSecured;
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

							/**********  Replace tags with values in group lead file name  ***********/
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
							/**********  End replacing tags in group lead file name  *************/


							/***********  Replace tags with values in group lead email subject  *********/
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
							/************  End replacing tags in group leads email subject  *************/

							/******** get offercode wise lead counts and file names here *************/
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
												   AND   $sUserDataTable.postalVerified = 'V'  
												   AND   DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
												   AND address NOT LIKE '3401 DUNDEE%'
												   GROUP BY offerLeadSpec.offerCode";

							// don't check postal verification if testing from current table
							if ($sOtDataTable == 'otData') {

								$sGroupOffersCountQuery = eregi_replace("AND $sUserDataTable.postalVerified = 'V'","", $sGroupOffersCountQuery);
								$sGroupOffersCountQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sGroupOffersCountQuery);
								$sGroupOffersCountQuery = eregi_replace("AND mode = 'A'","",$sGroupOffersCountQuery);
							} //else {
							//$sGroupOffersCountQuery = eregi_replace("AND $userDataTable.postalVerified = 'V'","AND userDataHistory.postalVerified = 'V'", $sGroupOffersCountQuery);
							//}

							$rGroupOffersCountResult = dbQuery($sGroupOffersCountQuery);
							echo dbError();
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
							/***********  End getting offercode wise lead counts and file names  **********/

							/*********  Place lead counts in group email subject and email body  *************/
							$sTempGrLeadsEmailContent = "$sTempGrLeadsEmailContent\r\n"."Total Count - $iGrLeadsCount";
							$sTempGrLeadsEmailSubject = eregi_replace("\[count\]", "$iGrLeadsCount", $sTempGrLeadsEmailSubject);

							if ($sTempGrLeadsEmailBody != '') {
								$sTempGrLeadsEmailBody = eregi_replace("\[offerCode - count\]",$sTempGrLeadsEmailContent, $sTempGrLeadsEmailBody);
							}
							/**********  End placing lead counts in group email subject and email body  **********/

							/******** if testing of lead delivery, then use the email address specified in leads processing screen *********/

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
							/***********  End if testing of lead delivery  **************/

							// send group leads data through specified delivery method
							// only if lead count is not 0

							if ($iGrLeadsCount != 0) {
							echo '.';
							flush();
							ob_flush();
								/**********  Send count email  *************/
								$sHeaders = "From: $sTempGrLeadsEmailFromAddr\n";
								$sHeaders .= "Reply-To: $sTempGrLeadsEmailFromAddr\n";
								$sHeaders .= "X-Priority: 1\n";
								$sHeaders .= "X-MSMail-Priority: High\n";
								$sHeaders .= "X-Mailer: My PHP Mailer\n";

								/********  If test mode, put recipients lists in email body  *********/
								if ($sTestMode) {
									$sDispGrCountEmailRecipients =  "Count Email Recipients: $sTempGrCountEmailRecipients\n\r\n\r";
									$sDispGrLeadsEmailRecipients =  "Leads Email Recipients: $sTempGrLeadsEmailRecipients\n\r\n\r";
								} else {
									$sDispGrCountEmailRecipients =  "";
									$sDispGrLeadsEmailRecipients =  "";
								}
								/********* End of putting repipients lists in email body  **********/


								mail($sTempGrCountEmailTo, $sTempGrLeadsEmailSubject, $sDispGrCountEmailRecipients.$sTempGrLeadsEmailBody , $sHeaders);

								/***************  End of sending count email  **************/

								/*************  Send leads email  *************/
								if ($iTempGrDeliveryMethodId == 1) {

									// If delivery method is 'FTP Daily Batch'

								} else if ($iTempGrDeliveryMethodId == 5) {
									// If delivery method is 'Daily Batch Form POST - GET'

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
									// This section is for group leads
								}
								/************  End of sending leads email  *************/

							} // end if ($iGrLeadsCount != 0)


							/******  Update group leads and set sendStatus = 'S' for all the leads of the group  *****/
							if (!($sTestMode)) {
							echo '.';
							flush();
							ob_flush();
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
							/******  End updating sendStatus  *******/
						}

						/***************  End of sending leads of previous group  **************/

						/****** If offer is not grouped, Get id of the lead record and mark it processed,
						one by one get the id to update that ot data row *********/
						// i.e. use same where condition as used for leads select query

						/************ If offer is not grouped and not processed in test mode,
						Set recipeints to test recipients *********/
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
						/**********  End if offer not grouped, set recipients to test recipients *******/


						/**********  Get last id reported for real time offers  **********/
						if ($iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4') {

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
						/********  End getting last id reported for real time offers  *******/


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
							} else {
								$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","AND $sUserDataTable.postalVerified = 'V'",$sTempLeadsQuery);
							}
						}


						$rTempLeadsResult = dbQuery($sTempLeadsQuery);

						$iLeadsCount = 0;


						/*******  Check if offer has any mutually exclusive offers  *********/
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
							flush();
							ob_flush();


						} else {
							$iNumFields = dbNumFields($rTempLeadsResult);
							$iLeadsCount = dbNumRows($rTempLeadsResult);

							/********  update offerCount for this offer  **********/
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
							/***********  End of updating offer count for this offer  ********/


							$iLastIdReported = 0;

							/***********  Leads query while loop  ***********/
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

								/*******  Send leads one by one for form post or single email delivery method  ******/
								if ($iTempDeliveryMethodId == '5' ) {
									//if lead delivery method is daily batch form post - GET

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

									//echo "2";
									$sFormPostResponse = "";

									if ($rSocketConnection) {
										$sScriptPath  .= "?".$sTempHttpPostStringRec;

										fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");
										fputs($rSocketConnection, "Host: $sHostPart\r\n");
										fputs($rSocketConnection, "User-Agent: MSIE\r\n");
										fputs($rSocketConnection, "Connection: close\r\n\r\n");

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

										echo dbError();

									} else {
										echo "$sTempOfferCode Form Post error: $errstr ($errno)<br />\r\n";
										//$sErrorInSendingLeads .= "<BR>$sTempOfferCode Form Post Error: $errstr ($errno)";
										$sTempSendingError = "$sTempOfferCode Form Post error: $errstr ($errno)";

									}

									// keep 5 seconds delay between each post
									echo '.';
									flush();
									ob_flush();
									sleep(2);

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
								/***********  End of sending leads one by one  ***********/

								/*********  Mark the leads as send which are not grouped  *********/
								// don't mark leads as send which are grouped
								// leads of a group should be marked all at once
							echo '.';
							flush();
							ob_flush();
								
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
								/*********  End of marking the leads as send which are not grouped  ********/

								if ($iTempId > $iLastIdReported) {
									$iLastIdReported = $iTempId;
								}

							}
							/*************  End of leads query while loop  *************/

							/***** insert lead counts and lastIdReported if leads were sent real time  ******/
							if ($sTestMode == '' && $iLastIdReported != 0 && ( $iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4')) {

								$sLastIdReportedInsertQuery = "INSERT INTO realTimeDeliveryReporting(offerCode, counts, lastIdReported, dateTimeSent)
													   VALUES('$sTempOfferCode', '$iLeadsCount', '$iLastIdReported', now())";
								$rLastIdReportedInsertResult = dbQuery($sLastIdReportedInsertQuery);
								echo dbError();
							}
							/*********  End of inserting lead counts and lastIdReported  ********/

							/*********  Place lead counts in lead email sub, body and file name  **********/
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
							/*********  End of placing lead counts in lead email sub, body and file name  *******/

							/*********  Send the leads as per lead delivery method if offer not grouped **********/
							if ($iTempLeadsGroupId == 0) {



								echo "<BR>Send: $sTempOfferCode $iLeadsCount";
								flush();
								ob_flush();

								/********** send leads data through specified delivery method
								only if lead count is not 0  *********/
								if ($iLeadsCount != 0) {

									/**********  send counts email  **************/
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
									/*********  End of sending counts email  **********/


									if ($iTempDeliveryMethodId == 1 || $iTempDeliveryMethodId == 7) {

										/**** If delivery method is ftp daily batch, ftp the file  *****/
										if ($iTempDeliveryMethodId == 1) {

											$rFtpConnection = 0;

											$rFtpConnection = ftp_connect($sTempFtpSiteUrl);

											if ($rFtpConnection) {

												$bFtpMode = ftp_pasv($rFtpConnection, false);
												$bFtpLogin = ftp_login($rFtpConnection, $sTempUserId, $sTempPasswd);
												if ($bFtpLogin) {


													if ($sTempInitialFtpDirectory != '') {
														$bInitialFtpDirectory = ftp_chdir($rFtpConnection, $sTempInitialFtpDirectory);
													}

													if ($sTempInitialFtpDirectory == '' || ($sTempInitialFtpDirectory != '' && $bInitialFtpDirectory)) {

														$bUploadFile = ftp_put($rFtpConnection, $sTempLeadFileName , "$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName", FTP_ASCII);

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
												} else {
													echo "<BR>$sTempOfferCode - error in FTP login";
												}


												ftp_close($rFtpConnection);

											} else {
												echo "<BR>$sTempOfferCode - not connected";

											}

										}
										/********  End of ftp file if method is ftp daily batch  ********/

										/*********  Send lead email with attaching file  **********/

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
										$sEmailMessage .= "$sTempLeadsEmailBody\r\n\r\n";

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

										//send lead data

										mail($sTempLeadsEmailTo, $sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);

										/**********  End of sending email with attaching the file  *********/


									} else if ($iTempDeliveryMethodId == 8) {
										// If delivery method is 'Upload In Browser'

									} else if ($iTempDeliveryMethodId == '13') {
										// Daily batch email - Leads in email body
										// content which should be send in email body, is already stored in file for this method during 'Process leads' step
										// Open the file, get the content and put it into email body

										$sHeaders = '';
										$sEmailMessage = '';
										$sLeadFileData = '';

										// get attachemnt file data
										$sTempLeadsEmailBody = '';
										$rFpLeadFile = fopen("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName","r");
										if ($rFpLeadFile) {
											while (!feof($rFpLeadFile)) {
												$sTempLeadsEmailBody .= fread($rFpLeadFile, 1024);
											}
											fclose($rFpLeadFile);

										} else {
											echo " can't open lead file";
										}

										$sHeaders="From: $sTempLeadsEmailFromAddr\r\n";
										$sHeaders.="Reply-To: $sTempLeadsEmailFromAddr\r\n";
										$sHeaders.="X-Priority: 1\r\n";
										$sHeaders.="X-MSMail-Priority: High\r\n";
										$sHeaders.="X-Mailer: My PHP Mailer\r\n";
										$sHeaders .= "MIME-Version: 1.0\r\n";
										$sHeaders .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
										$sEmailMessage .= $sDispLeadsEmailRecipients."$sTempLeadsEmailBody\r\n\r\n";

										//send lead data
										mail($sTempLeadsEmailTo, $sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);
									}

								} // if lead count != 0
								/*************  End of sending leads  ************/
							}
							/*********  End of sending the leads if offer not grouped **********/



							/********  update sendStatus of all the leads of this offer if not any error in sending leads
							and offer is not grouped  ********/
							if ($sErrorInSendingLeads == '' && $iTempLeadsGroupId == 0 && $sTestMode == '') {
								// update send status of all the leads of this offer

								$sProcessStatusUpdateQuery = "UPDATE  $sUserDataTable, $sOtDataTable, offerLeadSpec
											  SET     sendStatus = 'S',
													  dateTimeSent = now(),
													  howSent = '$sTempHowSent',
											  WHERE   offerLeadSpec.offerCode = $sOtDataTable.offerCode 
											  AND     $sUserDataTable.email = $sOtDataTable.email 
											  AND     $sOtDataTable.offerCode = '$sTempOfferCode' 
											  AND 	  $sUserDataTable.postalVerified = 'V' 
											  AND     processStatus = 'P'
											  AND     sendStatus IS NULL
											  AND 	  DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
											  AND 	  address NOT LIKE '3401 DUNDEE%' ";

								// don't check postal verification if testing from current table
								if ($sOtDataTable == 'otData') {

									$sProcessStatusUpdateQuery = eregi_replace("AND $sUserDataTable.postalVerified = 'V'","", $sProcessStatusUpdateQuery);
									$sProcessStatusUpdateQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sProcessStatusUpdateQuery);
									$sProcessStatusUpdateQuery = eregi_replace("AND mode = 'A'","",$sProcessStatusUpdateQuery);
								}	//else {
								//$sProcessStatusUpdateQuery = eregi_replace("AND postalVerified = 'V'","AND userDataHistory.postalVerified = 'V'", $sProcessStatusUpdateQuery);
								//}
							}
							/********** End of updating send status if offer is not grouped  *************/

						} // if get result of leads query

						// store groupId now as previous groupId
						$iTempPrevGroupId = $iTempLeadsGroupId;
					} // end of offers while loop
				} // if offersQuery != ''


				// include separate lead file script
				include("$sGblAdminWebRoot/processLeads/separateFormatDelivery.php");


				/******* send postal verified notification email, only if all the leads processed/sent and not in test mode  ********/

				if ($sProcessOption == 'processAll' && $sTestMode != '1') {
					$sHeaders  = "MIME-Version: 1.0\r\n";
					$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
					$sHeaders .= "From:nibbles@amperemedia.com\r\n";
					//$sHeaders .= "cc: ";

					$sEmailQuery = "SELECT *
					   FROM   emailRecipients
					   WHERE  purpose = 'postal verified'";
					$rEmailResult = dbQuery($sEmailQuery);
					echo dbError();
					while ($oEmailRow = dbFetchObject($rEmailResult)) {
						$sEmailTo = $oEmailRow->emailRecipients;

					}

					$sSubject = "We are Postal Verified - $sRunDate";
					mail($sEmailTo, $sSubject, "", $sHeaders);
				}
				/********  End of sending postal verification email  *********/

			} // send leads
			// call the script to update the leads sent count in offerLeadsCountSum table
			exec("php /home/sites/www_popularliving_com/crons/offerLeadsCountSum.php");

		} // end of if ($sTestMode && $sTestProcessingEmailRecipients == '')
	} // end of if ($sExportData || $sImportData || $sProcessLeads || $sSendLeads)


	/***********  Send lead counts to Fred  ***********/
	if ($sSendLeadCounts) {
		echo '.';
		flush();
		ob_flush();
		if ($sProcessOption == 'processAll' || $sProcessOption == 'rerunAll') {
			// This script will take the file and does encryption and sends it.
			include("$sGblIncludePath/gpgProcessingAndSend.php");
		}
		echo '.';
		flush();
		ob_flush();

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
		
		echo '.';
		flush();
		ob_flush();

		if (!($sEmailTo)) {
			$sLeadCountsEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
		}

		$sCcTo = substr($sRecipients,strlen($sLeadCountsEmailTo));
		$sHeaders .= " $sCcTo";
		$sHeaders .= "\r\n";

		// get lead counts of all the offers except real time and form post
		$sLeadCountsQuery = "SELECT offerCode, count(dateTimeSent) AS counts
						 FROM   otDataHistory 
						 WHERE  date_format(dateTimeSent, '%Y-%m-%d') = CURRENT_DATE
						 AND	sendStatus = 'S'
						 AND	howSent NOT IN ('rtfpp', 'rtfpg', 'rte', 'dbfpg', 'dbfpp')
						 AND    offerCode NOT LIKE 'SAMPLE%'
						 GROUP BY offerCode";
		$rLeadCountsResult = dbQuery($sLeadCountsQuery);
		echo dbError();
		$iTotalLeads = 0;
		$i = 0;
		
		echo '.';
		flush();
		ob_flush();

		while ($oLeadCountsRow = dbFetchObject($rLeadCountsResult)) {
			$aCountsArray['offerCode'][$i] = $oLeadCountsRow->offerCode;
			$aCountsArray['counts'][$i] = $oLeadCountsRow->counts;
			$i++;
		}

		// get real time offers counts
		$sRealTimeLeadCountsQuery = "SELECT offerCode, count(email) AS counts
								 FROM   otDataHistory
								 WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
								 		AND date_add(CURRENT_DATE, INTERVAL -1 DAY) 
								 AND    sendStatus = 'S'
								 AND    howSent IN ('rtfpp', 'rtfpg', 'rte')
								 GROUP BY offerCode";
		$rRealTimeLeadCountsResult = dbQuery($sRealTimeLeadCountsQuery);
		echo dbError();
		while ($oRealTimeLeadCountsRow = dbFetchObject($rRealTimeLeadCountsResult)) {
			$aCountsArray['offerCode'][$i] = $oRealTimeLeadCountsRow->offerCode;
			$aCountsArray['counts'][$i] = $oRealTimeLeadCountsRow->counts;
			$i++;
		}

		echo '.';
		flush();
		ob_flush();

		if ( count($aCountsArray) > 0) {
			array_multisort($aCountsArray['offerCode'],SORT_ASC, $aCountsArray['counts']);
		}

		for ($i = 0; $i<count($aCountsArray['offerCode']);$i++) {
			$sCountsEmailContent .= "<tr><td><font face=verdana size=1>".$aCountsArray['offerCode'][$i]."</font></td>
								<td align=right><font face=verdana size=1>".$aCountsArray['counts'][$i]."</font></td></tr>";

			$iTotalLeads += $aCountsArray['counts'][$i];
			echo '.';
			flush();
			ob_flush();
		}

		$sCountsEmailContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td>
									<td align=right><font face=verdana size=1><b>$iTotalLeads</b></font></td></tr>";
		$sCountsEmailContent .= "</table></body></html>";

		$sLeadsCountEmailSubject = "Lead Counts - $sRunDate";
		mail($sLeadCountsEmailTo, $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);

		echo '.';
		flush();
		ob_flush();
		
		// Email Report starts here
		$sPVQuery = "SELECT count(id) as id
						 FROM   otDataHistory 
						 WHERE  dateTimeAdded BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) AND date_add(CURRENT_DATE, INTERVAL -1 SECOND)
						 AND postalVerified='V'";
		$rPVResult = dbQuery($sPVQuery);
		echo dbError();
		$oRepCount = dbFetchObject($rPVResult);
		$iPVLeads = $oRepCount->id;
		
		echo '.';
		flush();
		ob_flush();
		
		$sFullCountQuery = "SELECT count(id) as id
						 FROM   otDataHistory 
						 WHERE  dateTimeAdded BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) AND date_add(CURRENT_DATE, INTERVAL -1 SECOND)";
		$rFullCountResult = dbQuery($sFullCountQuery);
		echo dbError();
		$oRepFullCount = dbFetchObject($rFullCountResult);
		$iFullCountLeads = $oRepFullCount->id;
		$iPVPercent = number_format((($iPVLeads/$iFullCountLeads)*100), 2, '.', "");
		
		$sCountsEmailContent = "<html><body><table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>";
		
		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>Gross Leads:</font></td>
								<td align=right><font face=verdana size=1>".$iFullCountLeads."</font></td></tr>";

		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>No. of Leads Sent:</font></td>
								<td align=right><font face=verdana size=1>".$iTotalLeads."</font></td></tr>";
		
		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>No of Postal Verified:</font></td>
								<td align=right><font face=verdana size=1>".$iPVLeads."</font></td></tr>";
		
		$sCountsEmailContent .= "<tr><td><font face=verdana size=1>% Postal Verified:</font></td>
								<td align=right><font face=verdana size=1>".$iPVPercent."</font></td></tr>";
		
		$sCountsEmailContent .= "</table></body></html>";
		
		$sHeaders  = "MIME-Version: 1.0\r\n";
		$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$sHeaders .= "From:nibbles@amperemedia.com\r\n";
		$sHeaders .= "cc: ";
		
		$sLeadsCountEmailSubject = "Postal Verified Statistics - $sRunDate";
		mail('it@amperemedia.com', $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);
		echo '.';
		flush();
		ob_flush();

		
		// send email notification that leads are done for the day.
		$sHeaders = "";
		$sHeaders = "From: leads@amperemedia.com\r\n";
		$sHeaders .= "Reply-To: leads@amperemedia.com\r\n";
		$sHeaders .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
		$sSubject = "Leads Completed - ".date(Y)."-".date(m)."-".date(d);
		$sEmailBody = "Leads Completed: ".date(Y)."-".date(m)."-".date(d)."\n\n";
		$sEmailBody .= "Leads Processed by: ".$sTrackingUser."\n\n";
		mail('it@amperemedia.com', $sSubject, $sEmailBody , $sHeaders);

		$sMessage = "Lead Counts Email Is Sent...";
	}
	/*********  End of sending lead counts  **********/


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


	/******** get the offers list which are not grouped  **********/
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
	/***********  End of getting offers list which are not grouped  **********/


	/******** get the groups list  *********/
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
	/**********  End of getting groups list  **********/



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
	<tr><td colspan=2><BR></td></tr>
	<tr><td colspan=2>Abbreviations used in leads table:</td></tr>
	<tr><td colspan=2><b>processStatus:</b> NULL = Not Processed, P = Processed, R = Rejected
					  <BR><b>reasonCode:</b> tst = Test Lead, ncv = Not Custom Verified, meo = Mutually Exclusive Offer
					  <BR><b>sendStatus:</b> NULL = Not Sent, S = Sent</td></tr>
	<tr><td colspan=2><BR><BR></td></tr>
	<tr><td><b>Step 1:</b> </td><td><input type=submit name=sDailyProcessing value='Daily Maintenance'></td></tr>
	<tr><td colspan=2><BR></td></tr>
	<tr><td><b>Step 2:</b> </td>
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
	
	<tr><td><b>Step 3:</b> Select Either Offer Or Lead Group<BR>
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (For <b>Process One</b> Or <b>Rerun One</b> Only)</td><td><select name=sOfferCode>
		<?php echo $sOffersOptions;?>
		</select>
		<select name=iGroupId>
		<?php echo $sGroupsOptions;?>
		</select> &nbsp; &nbsp; <a href='JavaScript:void(leadDetails());'>Lead Specific Details</a>
		</td>
	</tr>
	<tr><td><b>Step 4:</b> </td><td><input type=submit name=sProcessLeads value='Process Leads'> &nbsp; &nbsp; &nbsp; Use Table With Today's Leads<input type=checkbox name=sUseCurrentTable value='Y' <?php echo $sUseCurrentTableChecked;?>>
		<A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=useCurrentTable", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a></td></tr>		
	<tr><td><b>Step 5:</b> </td><td nowrap><input type=submit name=sSendLeads value='Send Leads Out'>		
		&nbsp; Test Mode <input type=checkbox name=sTestMode value="1" <?php echo $sTestModeChecked;?>> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=testMode", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a> &nbsp; 
		&nbsp; Test Email Recipient(s)<input type=text name=sTestProcessingEmailRecipients value="<?php echo $sTestProcessingEmailRecipients;?>" size=50>
		<BR><BR><input type=submit name=sSendFormPostLeads value='Send Form Post Leads Out'> <A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=sendFormPostLeads", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a>
		</td></tr>
		
	<tr><td><b>Step 6:</b> </td><td><input type=submit name=sSendLeadCounts value='Send Lead Counts to Fred'>
		Calculate real time leads of last <select name=iRealTimeDaysBack><?php echo $sRealTimeDaysBackOptions;?></select> days. 
		<A href='JavaScript:void(window.open("processLeadsDesc.php?sItem=realTimeDaysBack", "", "height=350, width=450, scrollbars=auto, resizable=yes, status=no"));' class=header>?</a></td></tr>		
		
	<tr><td colspan=2 class=header><BR>Note:</td></tr>
	<tr><td colspan=2>Form Post and Single email leads will not be sent with test mode.<br><br></td></tr>
	
	<tr><td colspan=2 class=header><BR>Leads Processing Checklist:</td></tr>
	<tr><td colspan=2><b>Step 1: Daily Maintenance:</b>  Click 'Daily Maintenance' button.  This may take about 5 minutes to complete.  When completed, it will 
	display the text 'Daily Maintenance Performed' at the top of the screen.  
	It mark all the leads as test leads which are collected in Test mode and mark 3401 leads as rejected.<br></td></tr>
	
	<tr><td colspan=2><b>Step 4: Process Leads:</b> Click 'Process Leads'.  This may take about 20 minutes to complete.  Ensure that the 'Process All Leads' radio button is checked in step 2, and 
	the the 'Use Current Table' and 'Test Mode' checkboxes are unchecked in step 4.  When that is finished, 
	you'll see a list of all offers and the number of leads that were processed.
	(some will be 0 if they are not live. Some might also have errors. If you are not sure what the errors mean, see John)<br>
	Process Leads gets all active offers and process it's leads.  
	It will prepare the lead file as per spec and mark the lead's processStatus field as 'P' (Processed).  
	Also, all the leads that are not already sent (sendStatus = 'S') will be processed again if not rejected.  
	Processing will process all the offers even if it's not scheduled, as all the reports looks for processStatus= 'P'.
	<br></td></tr>
	
	<tr><td colspan=2><b>Step 4: Send Leads Out:</b>  Click 'Send Leads Out'. This may take about 20 minutes to complete.  When that is finished, you'll see a list of all offers that were sent.
	This will send leads out to our clients.
	<br></td></tr>
	
	<tr><td colspan=2><b>Step 5: Send Form Post Leads Out:</b>  Click 'Send Form Post Leads Out'.  This may take about 15 minutes to complete.  When that is finished, you'll see "Form Post Lead Sent".
	This will send form post leads.
	<br></td></tr>
	
	<tr><td colspan=2><b>Step 6: Send Leads Counts to Fred:</b>  Click 'Send Leads Counts to Fred'. This may take about 3 minutes to complete.  When that is finished, you'll see "Lead Counts Email Is Sent...".
	This will send out 3 emails: Leads Count, Postal Verified Stats, and Leads Completed Notification.
	<br></td></tr>
	
	</tr>
</table>

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
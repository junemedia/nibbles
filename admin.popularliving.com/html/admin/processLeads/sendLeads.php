<?php

ini_set('max_execution_time', 5000);

/*********
Script to Process and Send Leads
**********/
// kdn_inv count rec: phil@myfree.com, keith@amperemedia.com, bulebosh.becky@davison54.com, michaels.jude@davison54.com, leads@amperemedia.com
// KDN_INV form post url https://www.davison54.com/tools/leadcollect/index.php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include_once("$sGblLibsPath/stringFunctions.php");

//report to cron.
/*
$cronReportingSql = "INSERT INTO cronScriptStatus (scriptName, startDateTime) values ('sendLeads.php', now());";
$reportingResponse = dbQuery($cronReportingSql);
$cronReportingSql = "SELECT id FROM cronScriptStatus WHERE scriptName = 'sendLeads.php' ORDER BY id DESC LIMIT 1;";
$reportingResponse = dbQuery($cronReportingSql);
$cronReportingId = dbFetchObject($reportingResponse);
*/

include_once( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "sendLeads.php" );

mysql_select_db('nibbles');

//if (date('D') =='Mon') {
//	$iRealTimeDaysBack = "3";
//} else {
	$iRealTimeDaysBack = "1";
//}

$iPvThreshold = 90;
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

$sPageTitle = "Nibbles - Process Leads";

	$sOtDataTable = "process_leads.otDataHistoryWorking";
	$sUserDataTable = "process_leads.userDataHistoryWorking";
	
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
	
	///check if dedup script is finished
/*	$sDedupVarQuery = "SELECT *
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
	
	
	$sOvernightDataWorkingVarQuery = "SELECT *
				   FROM	  vars
				   WHERE  system = 'cron'
				   AND	  varName = 'otDataUserDataWorking'";
	$rOvernightDataWorkingVarResult= dbQuery($sOvernightDataWorkingVarQuery);
	while ($oOvernightDataWorkingVarRow = dbFetchObject($rOvernightDataWorkingVarResult)) {
		$iOvernightDataWorkingScriptVar = $oOvernightDataWorkingVarRow->varValue;
	}
	
	if ($iOvernightDataWorkingScriptVar){
		$msg = "Send Leads: can't run, process_leads.otDataHistoryWorking is still populating.";
		mail('bbevis@amperemedia.com,samir@amperemedia.com',"Overnight processing couldn't run.",$msg);
		exit();
	} else if($iOvernightDataMoveScriptVar){
		$msg = "Send Leads : can't run, overnight data move is still running.";
		mail('bbevis@amperemedia.com,samir@amperemedia.com',"Overnight processing couldn't run.",$msg);
		exit();
	} else if($iDedupScriptVar){
		$msg = "Send Leads :can't run, dedup is still running.";
		mail('bbevis@amperemedia.com,samir@amperemedia.com',"Overnight processing couldn't run.",$msg);
		exit();	
	}
	///End checking if dedup script is running
*/
	
	//set a ver for this guy
	
	$sDedupVarQuery = "UPDATE vars SET varValue = varValue+1 WHERE varName = 'sendLeadsRunning';";
	$rDedupVarResult= dbQuery($sDedupVarQuery);
	
	$aQueryFailures = array();
	
	//
	$sLeadsGroupsQuery = "SELECT leadsGroupId, offerCode
							FROM offerLeadSpec
							WHERE leadsGroupId != 0;";
									
	$rLeadsGroupsResult = dbQuery($sLeadsGroupsQuery);
	$aLeadsGroupsArrays = array();
	$aLeadsGroups = array();
	while($oLeadsGroup = dbFetchObject($rLeadsGroupsResult)){
		if(!is_array($aLeadsGroupsArrays[$oLeadsGroup->leadsGroupId]))
			$aLeadsGroupsArrays[$oLeadsGroup->leadsGroupId] = array();
		array_push($aLeadsGroupsArrays[$oLeadsGroup->leadsGroupId],$oLeadsGroup->offerCode);
	}
				
	//print_r($aLeadsGroupsArrays);
				
	foreach($aLeadsGroupsArrays as $key => $arr){
		$aLeadsGroups[$key] = "('".join("','",$arr)."')";
	}
				
	print_r($aLeadsGroups);
	flush();
	ob_flush();
		

	// get all active offers

	$sOffersQuery = "SELECT offerLeadSpec.*
					 FROM   offers, offerLeadSpec LEFT JOIN leadGroups ON offerLeadSpec.leadsGroupId = leadGroups.id
					 WHERE  offers.offerCode = offerLeadSpec.offerCode
					 AND    activeDateTime <= now() 
					 AND    lastLeadDate >= CURRENT_DATE 
					 AND    (  (FIND_IN_SET(WEEKDAY(CURRENT_DATE), offerLeadSpec.processingDays) AND leadsGroupId =0) 
							OR (FIND_IN_SET(WEEKDAY(CURRENT_DATE), leadGroups.processingDays) AND  leadsGroupId != 0) )
					 AND    isOpenTheyHost = 'N'";
/*
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
	}*/

	// get the offer list/ one offer to get leads for
	if ($sOffersQuery != '') {

		$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
									//echo "process start time : "
		$startTime = date("U");
		echo "offers query:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
		$rOffersResult = dbQuery($sOffersQuery);
									//echo "process end time : "
		$endTime = date("U");
		echo "<br> &nbsp; &nbsp; query: ".$sOffersQuery."<br>";
		echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
		flush();
		ob_flush();
		echo dbError();
					
		$aProcessingLoopTimes = array();
		$aGroupOffersTimes = array();
		$aProcessStatusUpdateTimes = array();
		$aMutCheckTimes = array();
		$aProcessStatusUpdateTimes = array();
		$aUpdateStatusTimes = array();

		$iNumRecords = dbNumRows($rOffersResult);
		$iCurrentRec = 0;
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$processingStartTime = date('U');

			// reset error message
			$sErrorInSendingLeads = '';
			$iCurrentRec++;

			$sLeadsData = '';
			$sLeadFileData = '';
			$sEmailMessage = '';
			$iTempMaxAgeOfLeads = '';

			$sTempOfferCode = $oOffersRow->offerCode;
			$sTempLeadsQuery = $oOffersRow->leadsQuery;
			
			$sTempLeadsQuery = eregi_replace("otDataHistoryWorking", $sOtDataTable,$sTempLeadsQuery);
			$sTempLeadsQuery = eregi_replace("userDataHistoryWorking", $sUserDataTable,$sTempLeadsQuery);

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
			$sTempCountsEmailSubject = $oOffersRow->countEmailSubject;
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

			/************  Replace tags with values in counts email subject  ************/

                        if ($sTempCountsEmailSubject != '') {
                                $sTempCountsEmailSubject = eregi_replace("\[offerCode\]",$sTempOfferCode, $sTempCountsEmailSubject);

                                if (strstr($sTempCountsEmailSubject,"[d-")) {
                                        //get date arithmetic number

                                        $iDateArithNum = substr($sTempCountsEmailSubject,strpos($sTempCountsEmailSubject,"[d-")+3,1);

                                        $sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
                                        $rTempResult = dbQuery($sTempQuery);
                                        while ($oTempRow = dbFetchObject($rTempResult)) {
                                                $sNewDate = $oTempRow->newDate;
                                        }

                                        $sNewYY = substr($sNewDate, 0, 4);
                                        $sNewYY = substr($sNewDate, 2, 2);
                                        $sNewMM = substr($sNewDate, 5, 2);
                                        $sNewDD = substr($sNewDate, 8, 2);

                                        $sTempCountsEmailSubject = eregi_replace("\[dd\]", $sNewDD, $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[mm\]", $sNewMM, $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[yyyy\]", $sNewYY, $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[yy\]", $sNewShortYY, $sTempCountsEmailSubject);

                                        $sDateArithString = substr($sTempCountsEmailSubject, strpos($sTempCountsEmailSubject,"[d-"),5);

                                        $sTempCountsEmailSubject = str_replace($sDateArithString, "", $sTempCountsEmailSubject);

                                } else {

                                        $sTempCountsEmailSubject = eregi_replace("\[dd\]", date(d), $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[mm\]", date(m), $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[yyyy\]", date(Y), $sTempCountsEmailSubject);
                                        $sTempCountsEmailSubject = eregi_replace("\[yy\]", date(y), $sTempCountsEmailSubject);
                                }
                        }
                        /***************  End replacing tags in counts email subject  ***************/

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
		
				//new group offers count query:
				$sGroupOffersCountQuery = "SELECT offerLeadSpec.offerCode, leadFileName, count(email) counts
											FROM   $sOtDataTable, offerLeadSpec 
											WHERE  offerLeadSpec.offerCode = $sOtDataTable.offerCode 
											AND    offerLeadSpec.leadsGroupId = '$iTempLeadsGroupId'  
											AND	  processStatus = 'P'									
											AND    sendStatus IS NULL					
											AND   DATE_ADD(date_format($sOtDataTable.dateTimeAdded,\"%Y-%m-%d\"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE 
											GROUP BY offerLeadSpec.offerCode";

				// don't check postal verification if testing from current table
											
							
				$startTime = date("U");
				$rGroupOffersCountResult = dbQuery($sGroupOffersCountQuery);
				$endTime = date("U");
				array_push($aGroupOffersTimes,($endTime - $startTime));
				flush();
				ob_flush();
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

					$sTempGrLeadsEmailTo = $sTempGrLeadsEmailRecipients;
					// for count email
					$sTempGrCountEmailTo = $sTempGrCountEmailRecipients;

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
					
						$sDispGrCountEmailRecipients =  "";
						$sDispGrLeadsEmailRecipients =  "";
					
					/********* End of putting repipients lists in email body  **********/

					$sendmailtobill = 'bbevis@amperemedia.com';
					mail($sTempGrCountEmailTo, $sTempGrLeadsEmailSubject, $sDispGrCountEmailRecipients.$sTempGrLeadsEmailBody , $sHeaders);
					//mail($sendmailtobill, $sTempGrLeadsEmailSubject, $sDispGrCountEmailRecipients.$sTempGrLeadsEmailBody , $sHeaders);

					/***************  End of sending count email  **************/

					/*************  Send leads email  *************/
					if ($iTempGrDeliveryMethodId == 7) {
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
						//mail($sendmailtobill, $sTempGrLeadsEmailSubject, $sGrEmailMessage , $sHeaders);

					} 
					/************  End of sending leads email  *************/

				} // end if ($iGrLeadsCount != 0)


				/******  Update group leads and set sendStatus = 'S' for all the leads of the group  *****/
				
				echo '.';
				flush();
				ob_flush();
								
				//new send status update query:
				$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable
												SET    sendStatus = 'S',
														dateTimeSent = now(),
														howSent = '$sTempHowSent'		
												WHERE  offerCode IN ".$aLeadsGroups[$iTempLeadsGroupId]."
												AND    processStatus = 'P'								
												AND    sendStatus IS NULL";
									//echo $sProcessStatusUpdateQuery;
													//echo "process start time : "
				$startTime = date("U");
				$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
													//echo "process end time : "
				array_push($aProcessStatusUpdateTimes,(date('U') - $startTime));
					
				$sProcessStatusUpdateQuery = "UPDATE otDataHistory
												SET    sendStatus = 'S',
														dateTimeSent = now(),
														howSent = '$sTempHowSent'		
												WHERE  offerCode IN ".$aLeadsGroups[$iTempLeadsGroupId]."
												AND    processStatus = 'P'								
												AND    sendStatus IS NULL";
										//echo $sProcessStatusUpdateQuery;
														//echo "process start time : "
				$startTime = date("U");
				$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
														//echo "process end time : "
				array_push($aProcessStatusUpdateTimes,(date('U') - $startTime));
				
							
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

				
				//$sTempLeadsQuery = eregi_replace(" otDataHistoryWorking", " $sOtDataTable",$sTempLeadsQuery);
				//$sTempLeadsQuery = eregi_replace(" userDataHistoryWorking", " $sUserDataTable",$sTempLeadsQuery);
				
				$sTempLastIdQuery = "SELECT lastIdReported
									 FROM   realTimeDeliveryReporting
									 WHERE  offerCode = '$sTempOfferCode'
									 ORDER BY dateTimeSent DESC LIMIT 0,1";
				$rTempLastIdResult = dbQuery($sTempLastIdQuery);
				while ($oTempLastIdRow = dbFetchObject($rTempLastIdResult)) {
					$iTempLastIdReported = $oTempLastIdRow->lastIdReported;
				}
				
				// 2006-06-28:  samir patel.
				// fixed andy's issue with lead count.
				// andy said the Leads Count report shows correct # of leads for TDMB_UOPO offer
				// but the individual email count shows gross total including dupes.
				// so added following to $sTempLeadsQuery:
				// "processStatus !='R' AND "
				$sTempLeadsQuery = eregi_replace( "WHERE", "WHERE $sOtDataTable.id > '$iTempLastIdReported'
														   AND address NOT LIKE '3401 DUNDEE%' AND processStatus !='R' AND ", $sTempLeadsQuery);


			} else {
				$sTempLeadsQuery = eregi_replace( "WHERE", "WHERE processStatus='P' AND sendStatus IS NULL
										  AND ", $sTempLeadsQuery);
			}
			/********  End getting last id reported for real time offers  *******/


			if ($sTempLeadsQuery != '') {
				$sTempLeadsQuery = eregi_replace('\$iTempMaxAgeOfLeads', $iTempMaxAgeOfLeads, $sTempLeadsQuery);
				if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
					$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id id, ", $sTempLeadsQuery);
				} else {
					$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id id, ", $sTempLeadsQuery);
				}
				
				$sTempLeadsQuery = eregi_replace("process_leads.otDataHistoryWorking", $sOtDataTable,$sTempLeadsQuery);
				$sTempLeadsQuery = eregi_replace("process_leads.userDataHistoryWorking", $sUserDataTable,$sTempLeadsQuery);
				$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);
				$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
				$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
				$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
			}

			//echo __LINE__."maybe here's where that's coming from.";
			$sTempLeadsQuery = eregi_replace('\$iTempMaxAgeOfLeads',$iTempMaxAgeOfLeads,$sTempLeadsQuery);
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
			
				/***********  End of updating offer count for this offer  ********/


				$iLastIdReported = 0;

				/***********  Leads query while loop  ***********/
				while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {

					$iTempId = $aTempLeadsRow['id'];
					$sTempLeadEmail = $aTempLeadsRow['email'];

					$sTempSendingError = '';

					if ($sMutExclusiveOffers != '') {
						$sMutCheckQuery = "SELECT *
										   FROM   process_leads.otDataHistoryWorking
										   WHERE  email = '$sTempLeadEmail'
										   AND    offerCode IN (".$sMutExclusiveOffers.")
										   AND    sendStatus = 'S'";
										//echo "process start time : "
						$startTime = date("U");
						$rMutCheckResult = dbQuery($sMutCheckQuery);
																		//echo "process end time : "
						$endTime = date("U");
						array_push($aMutCheckTimes,($endTime - $startTime));
						flush();
						ob_flush();
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
						//echo "process start time : "
						$startTime = date("U");
						//echo "page 2 map query:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
						$rPage2MapResult = dbQuery($sPage2MapQuery);
						//echo "process end time : "
						$endTime = date("U");
						//echo "<br> &nbsp; &nbsp; query: ".$sPage2MapQuery."<br>";
						//echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
						flush();
						ob_flush();
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
							$startTime = date("U");
							$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
							$endTime = date("U");
							array_push($aUpdateStatusTimes,($endTime - $startTime));
																						
							$sUpdateStatusQuery = "UPDATE otDataHistory
											   SET    processStatus = 'P',
													  sendStatus = 'S',
													  howSent = '$sTempHowSent',
													  dateTimeProcessed = now(),
													  dateTimeSent = now(),
													  realTimeResponse = \"".addslashes($sFormPostResponse)."\"
										 	  WHERE  id = '$iTempId'";
							$startTime = date("U");
							$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
							$endTime = date("U");
							array_push($aUpdateStatusTimes,($endTime - $startTime));
							
																			
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

					} else if ($iTempDeliveryMethodId == 11) {
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
						//mail($sendmailtobill, $sTempSingleEmailSubject, $sTempSingleEmailBodyRec , $sHeaders);

					}
					/***********  End of sending leads one by one  ***********/
					/*********  Mark the leads as send which are not grouped  *********/
					// don't mark leads as send which are grouped
					// leads of a group should be marked all at once
					echo '.';
					flush();
					ob_flush();
								
					if ($sTempSendingError == '' && $iTempLeadsGroupId == 0) {
						$sProcessStatusUpdateQuery = "UPDATE $sOtDataTable
													SET    sendStatus = 'S',
												 		   dateTimeSent = now(),
														   howSent = '$sTempHowSent'		
													WHERE  id = '$iTempId'
													AND    processStatus = 'P'								
													AND    sendStatus IS NULL";
																		//echo "process start time : "
						$startTime = date("U");
						$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
																		//echo "process end time : "
						$endTime = date("U");
						array_push($aProcessStatusUpdateTimes,($endTime - $startTime));
									
						$sProcessStatusUpdateQuery = "UPDATE otDataHistory
											SET    sendStatus = 'S',
										 		   dateTimeSent = now(),
												   howSent = '$sTempHowSent'		
											WHERE  id = '$iTempId'
											AND    processStatus = 'P'								
											AND    sendStatus IS NULL";
																			//echo "process start time : "
						$startTime = date("U");
						$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);
																			//echo "process end time : "
						$endTime = date("U");
						array_push($aProcessStatusUpdateTimes,($endTime - $startTime));
						
									
						flush();
						ob_flush();

					}
					/*********  End of marking the leads as send which are not grouped  ********/

					if ($iTempId > $iLastIdReported) {
						$iLastIdReported = $iTempId;
					}

				}
				/*************  End of leads query while loop  *************/

				/***** insert lead counts and lastIdReported if leads were sent real time  ******/
				if ($iLastIdReported != 0 && ( $iTempDeliveryMethodId == '2' || $iTempDeliveryMethodId == '3' || $iTempDeliveryMethodId == '4')) {

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

				if ($sTempCountsEmailSubject != ''){
					$sTempCountsEmailSubject = eregi_replace("\[count\]", "$iLeadsCount", $sTempCountsEmailSubject);
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
						
							$sDispCountEmailRecipients =  "";
							$sDispLeadsEmailRecipients =  '';
						
						//hey hey
						if($sTempCountsEmailSubject != ''){
							mail($sTempCountEmailTo, $sTempCountsEmailSubject, $sDispCountEmailRecipients.$sTempLeadsEmailBody , $sHeaders);
						} else {
							mail($sTempCountEmailTo, $sTempLeadsEmailSubject,$sDispCountEmailRecipients.$sTempLeadsEmailBody , $sHeaders);
						}
						//mail($sendmailtobill, $sTempLeadsEmailSubject, $sDispCountEmailRecipients.$sTempLeadsEmailBody , $sHeaders);
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
												$aQueryFailures[$sTempOfferCode] = "error in uploading file";
											} else {
												echo "<BR>$sTempOfferCode - file uploaded";
											}

										} else {
											// error accessing initial FTP dir
											$sErrorInSendingLeads .= "<BR>$sTempOfferCode - Error accessing Initial FTP Directory";
											echo "<BR>$sTempOfferCode - error accessing initial dir";
											$aQueryFailures[$sTempOfferCode] = "error accessing initial dir";
										}
									} else {
										echo "<BR>$sTempOfferCode - error in FTP login";
										$aQueryFailures[$sTempOfferCode] = "error in FTP login";
									}


									ftp_close($rFtpConnection);

								} else {
									echo "<BR>$sTempOfferCode - not connected";
									$aQueryFailures[$sTempOfferCode] = "not connected";

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
							//mail($sendmailtobill,$sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);

							/**********  End of sending email with attaching the file  *********/


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
							//mail($sendmailtobill, $sTempLeadsEmailSubject, $sEmailMessage , $sHeaders);
						}

					} // if lead count != 0
					/*************  End of sending leads  ************/
				}
				/*********  End of sending the leads if offer not grouped **********/

				/********  update sendStatus of all the leads of this offer if not any error in sending leads
							and offer is not grouped  ********/
							
				// WHY ISN"T THIS BEING RUN?
							
				if ($sErrorInSendingLeads == '' && $iTempLeadsGroupId == 0 ) {
					// update send status of all the leads of this offer
					//new send status update query:
					$sProcessStatusUpdateQuery = "UPDATE  $sOtDataTable
												  SET     sendStatus = 'S',
														  dateTimeSent = now(),
														  howSent = '$sTempHowSent',
												  WHERE   offerCode = '$sTempOfferCode' 
												  AND     processStatus = 'P'
												  AND     sendStatus IS NULL
												  AND 	  DATE_ADD(date_format(dateTimeAdded,\"%Y-%m-%d\"), INTERVAL $iTempMaxAgeOfLeads DAY) >= CURRENT_DATE  ";

					// don't check postal verification if testing from current table
						
				}
				/********** End of updating send status if offer is not grouped  *************/

			} // if get result of leads query

			// store groupId now as previous groupId
			$iTempPrevGroupId = $iTempLeadsGroupId;
			array_push($aProcessingLoopTimes, (date('U') - $processingStartTime));
		} // end of offers while loop
						
		$sum = 0;
		foreach($aProcessingLoopTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg processing loop time: ".($sum/ count($aProcessingLoopTimes))."<br>";
						
		$sum = 0;
		foreach($aGroupOffersTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg group processing time: ".($sum/ count($aGroupOffersTimes))."<br>";
						
		$sum = 0;
		foreach($aProcessStatusUpdateTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg process status update time: ".($sum/ count($aProcessStatusUpdateTimes))."<br>";
						
		$sum = 0;
		foreach($aMutCheckTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg mutex check times: ".($sum/count($aMutCheckTimes))."<br>";
						
		$sum = 0;
		foreach($aProcessStatusUpdateTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg process status update times: ".($sum/count($aProcessStatusUpdateTimes))."<br>";
				
		$sum = 0;
		foreach($aUpdateStatusTimes as $s){$sum += $s;}
		echo "<br>".__LINE__." avg update status times: ".($sum/count($aUpdateStatusTimes))."<br>";
					
	} // if offersQuery != ''


	// include separate lead file script
	//echo "process start time : "
	$startTime = date("U");
	echo "seperate format delivery include:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	include_once("/home/sites/admin.popularliving.com/html/admin/processLeads/separateFormatDelivery.php");
								//echo "process end time : "
	$endTime = date("U");
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();

	/******* send postal verified notification email, only if all the leads processed/sent and not in test mode  ********/

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
	
	// ********  End of sending postal verification email  *********  /

	// call the script to update the leads sent count in offerLeadsCountSum table
							//echo "process start time : "
	$startTime = date("U");
	echo "offer leads count sum script:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	exec("php /home/sites/admin.popularliving.com/crons/offerLeadsCountSum.php");
	$endTime = date("U");
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
	//echo "process end time : "
					
	//  Send lead counts to Fred  

	echo '.';
	flush();
	ob_flush();
	
	$startTime = date("U");
	echo "gpg processing/encrypting:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	include_once("/home/sites/admin.popularliving.com/html/includes/gpgProcessingAndSend.php");
							//echo "process end time : "
	$endTime = date("U");
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();

	
	// Start of getting count for they host - 4/13/06 - samir
	$sThContent = "<table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>
							<tr><td><font face=verdana size=1><b>They Host Offer</b></font></td>
							<td><font face=verdana size=1><b>Offer Name</b></font></td>
							<td align=right><font face=verdana size=1><b>Count</b></font></td></tr>";
	$iThTotalLeads = 0;
	$sThCountQuery = "SELECT offerCode, count(email) AS counts
					FROM   nibbles.otDataHistory
					WHERE  date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
	 					AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
					AND    isOpenTheyHost = 'Y'
					GROUP BY offerCode";
	$rThCountResult = dbQuery($sThCountQuery);
	while ($oThRow = dbFetchObject($rThCountResult)) {
		$sThOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oThRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oThNameRow = dbFetchObject($rGetOfferName)) {
			$sThOfferName = $oThNameRow->name;
		}

		$sThContent .= "<tr><td><font face=verdana size=1>".$oThRow->offerCode."</font></td>
			<td><font face=verdana size=1>$sThOfferName</font></td>
			<td align=right><font face=verdana size=1>".$oThRow->counts."</font></td></tr>";
		$iThTotalLeads += $oThRow->counts;
	}
	$sThContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iThTotalLeads</b></font></td></tr>
					</table>";
	// End of getting count for they host - 4/13/06 - samir
	

	

	$sCountsEmailContent = "<html><body>
							<table><tr><td>
							<table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>
							<tr><td><font face=verdana size=1><b>Offer Code</b></font></td>
							<td><font face=verdana size=1><b>Offer Name</b></font></td>
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
						 FROM   process_leads.otDataHistoryWorking 
						 WHERE  date_format(dateTimeSent, '%Y-%m-%d') = CURRENT_DATE
						 AND	sendStatus = 'S'
						 AND	howSent NOT IN ('rtfpp', 'rtfpg', 'rte', 'dbfpg', 'dbfpp')
						 AND    offerCode NOT LIKE 'SAMPLE%'
						 GROUP BY offerCode";
						//echo "process start time : "
	$startTime = date("U");
	echo "leads counts query1:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rLeadCountsResult = dbQuery($sLeadCountsQuery);
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sLeadCountsQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
						//echo "process end time : "
	echo dbError();
	$iTotalLeads = 0;
	$i = 0;
		
	echo '.';
	flush();
	ob_flush();

	while ($oLeadCountsRow = dbFetchObject($rLeadCountsResult)) {
		$sOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oLeadCountsRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oNameRow = dbFetchObject($rGetOfferName)) {
			$sOfferName = $oNameRow->name;
		}
		
		$aCountsArray['offerCode'][$i] = $oLeadCountsRow->offerCode;
		$aCountsArray['offerName'][$i] = $sOfferName;
		$aCountsArray['counts'][$i] = $oLeadCountsRow->counts;
		$i++;
	}

	// get real time offers counts
	$sRealTimeLeadCountsQuery = "SELECT offerCode, count(email) AS counts
								 FROM   process_leads.otDataHistoryWorking
								 WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
	 					 		 AND date_add(CURRENT_DATE, INTERVAL -1 DAY) 
								 AND    sendStatus = 'S'
								 AND    howSent IN ('rtfpp', 'rtfpg', 'rte')
								 GROUP BY offerCode";
						//echo "process start time : "
	$startTime = date("U");
	echo "real time lead counts query:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rRealTimeLeadCountsResult = dbQuery($sRealTimeLeadCountsQuery);
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sRealTimeLeadCountsQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
						//echo "process end time : "
	echo dbError();
	while ($oRealTimeLeadCountsRow = dbFetchObject($rRealTimeLeadCountsResult)) {
		$sOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oRealTimeLeadCountsRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oNameRow = dbFetchObject($rGetOfferName)) {
			$sOfferName = $oNameRow->name;
		}
		
		$aCountsArray['offerCode'][$i] = $oRealTimeLeadCountsRow->offerCode;
		$aCountsArray['offerName'][$i] = $sOfferName;
		$aCountsArray['counts'][$i] = $oRealTimeLeadCountsRow->counts;
		$i++;
	}

	echo '.';
	flush();
	ob_flush();

	if ( count($aCountsArray) > 0) {
		array_multisort($aCountsArray['offerCode'],SORT_ASC, $aCountsArray['offerName'], $aCountsArray['counts']);
	}

	$iChicagoCount = 0;
	$iNewYorkCount = 0;
	$sChicagoContent = '';
	$sNewYorkContent = '';
	for ($i = 0; $i<count($aCountsArray['offerCode']);$i++) {
		
		$sCheckQuery = "SELECT offerCompanies.repDesignated
					FROM   offers, offerCompanies
					WHERE  offers.companyId = offerCompanies.id
					AND    offers.offerCode = '".$aCountsArray['offerCode'][$i]."' LIMIT 1";
		$rCheckResult = dbQuery($sCheckQuery);
		while ($oCheckRow = dbFetchObject($rCheckResult)) {
			$sLocQuery = "SELECT * FROM nbUsers WHERE id IN (".$oCheckRow->repDesignated.")
						AND officeLocation = 'NY'";
			$rLocResult = dbQuery($sLocQuery);
			if (dbNumRows($rLocResult) > 0) {
				// new york offer
				$sNewYorkContent .= "<tr><td><font face=verdana size=1>".$aCountsArray['offerCode'][$i]."</font></td>
								<td><font face=verdana size=1>".$aCountsArray['offerName'][$i]."</font></td>
								<td align=right><font face=verdana size=1>".$aCountsArray['counts'][$i]."</font></td></tr>";
				
				$iNewYorkCount += $aCountsArray['counts'][$i];
			} else {
				// northbrook offer
				$sChicagoContent .= "<tr><td><font face=verdana size=1>".$aCountsArray['offerCode'][$i]."</font></td>
								<td><font face=verdana size=1>".$aCountsArray['offerName'][$i]."</font></td>
								<td align=right><font face=verdana size=1>".$aCountsArray['counts'][$i]."</font></td></tr>";
				
				$iChicagoCount += $aCountsArray['counts'][$i];
			}
		}

		$iTotalLeads += $aCountsArray['counts'][$i];
		echo '.';
		flush();
		ob_flush();
	}
	
	
	$sCountsEmailContent .= "<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>
					<tr><td colspan=3 align=center><font face=verdana size=1><b>Northbrook Offers</b></td></tr>
					$sChicagoContent
					<tr><td><font face=verdana size=1><b>Northbrook Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iChicagoCount</b></font></td></tr>
					
					
					<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>
					<tr><td colspan=3 align=center><font face=verdana size=1><b>New York Offers</b></td></tr>
					$sNewYorkContent
					<tr><td><font face=verdana size=1><b>New York Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iNewYorkCount</b></font></td></tr>
					<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>";

	$sCountsEmailContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td><td>&nbsp;</td>
							<td align=right><font face=verdana size=1><b>$iTotalLeads</b></font></td></tr>";
	$sCountsEmailContent .= "</table></td></tr><tr><td>&nbsp;</td></tr><tr><td>$sThContent</td></tr></table></body></html>";
	

	$sLeadsCountEmailSubject = "Lead Counts - $sRunDate";
	mail($sLeadCountsEmailTo, $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);

	echo '.';
	flush();
	ob_flush();
		
	// Email Report starts here
	$sPVQuery = "SELECT count(id) as id
				 FROM   process_leads.otDataHistoryWorking 
				 WHERE  dateTimeAdded BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) AND date_add(CURRENT_DATE, INTERVAL -1 SECOND)
				 AND postalVerified='V'";
						//echo "process start time : "
	$startTime = date("U");
	echo "postal verified count query:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rPVResult = dbQuery($sPVQuery);
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sPVQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
						//echo "process end time : "
	echo dbError();
	$oRepCount = dbFetchObject($rPVResult);
	$iPVLeads = $oRepCount->id;
		
	echo '.';
	flush();
	ob_flush();
		
	$sFullCountQuery = "SELECT count(id) as id
						 FROM   process_leads.otDataHistoryWorking 
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
	// commented out because no need for this report
	//mail('it@amperemedia.com', $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);
	echo '.';
	flush();
	ob_flush();

	if(count($aQueryFailures)){
		$sEmailReport = "There were problems with uploading some leads:\n";
		foreach($aQueryFailures as $k => $v){
			$sEmailReport .= "\n$k => $v\n";
		}
		mail('it@amperemedia.com','Errors in send leads FTPing',$sEmailReport);
		//mail($sendmailtobill, 'Errors in send leads FTPing',$sEmailReport);
	}
	
	//make sure that people can use the web interface.
	exec("chown -R apache.apache $sTodaysLeadsFolder");
	
	// send email notification that leads are done for the day.
	$sHeaders = "";
	$sHeaders = "From: leads@amperemedia.com\r\n";
	$sHeaders .= "Reply-To: leads@amperemedia.com\r\n";
	$sHeaders .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
	$sSubject = "Leads Completed - ".date(Y)."-".date(m)."-".date(d);
	$sEmailBody = "Leads Completed: ".date(Y)."-".date(m)."-".date(d)."\n\n";
	$sEmailBody .= "Leads Processed by: ".$sTrackingUser."\n\n";
	// commented out because no need for this report
	//mail('it@amperemedia.com', $sSubject, $sEmailBody , $sHeaders);

	$sMessage = "Lead Counts Email Is Sent...";
	
	// *********  End of sending lead counts  **********
	
	//$rTruncateUserData= dbQuery("TRUNCATE TABLE process_leads.userDataHistoryWorking");
	//$rTruncateOtData= dbQuery("TRUNCATE TABLE process_leads.otDataHistoryWorking");
	//$rDropUserData= dbQuery("DROP TABLE process_leads.userDataHistoryWorking");
	//$rDropOtData= dbQuery("DROP TABLE process_leads.otDataHistoryWorking");
	

$sDedupVarQuery = "UPDATE vars SET varValue = varValue-1 WHERE varName = 'sendLeadsRunning';";
$rDedupVarResult= dbQuery($sDedupVarQuery);
		/*
$cronReportingSql = "UPDATE cronScriptStatus SET endDateTime = now() WHERE id = $cronReportingId";
$reportingResponse = dbQuery($cronReportingSql);
*/
cssLogFinish( $iScriptId );

?>

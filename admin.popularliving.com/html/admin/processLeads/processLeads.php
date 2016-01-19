<?php
/*********
Script to Process and Send Leads
**********/
// kdn_inv count rec: phil@myfree.com, keith@amperemedia.com, bulebosh.becky@davison54.com, michaels.jude@davison54.com, leads@amperemedia.com
// KDN_INV form post url https://www.davison54.com/tools/leadcollect/index.php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include_once("$sGblLibsPath/stringFunctions.php");


//report to cron.
/*
$cronReportingSql = "INSERT INTO cronScriptStatus (scriptName, startDateTime) values ('processLeads.php', now());";
$reportingResponse = dbQuery($cronReportingSql);
$cronReportingSql = "SELECT id FROM cronScriptStatus WHERE scriptName = 'processLeads.php' ORDER BY id DESC LIMIT 1;";
$reportingResponse = dbQuery($cronReportingSql);
$cronReportingId = dbFetchObject($reportingResponse);
*/

include_once( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "processLeads.php" );

mysql_select_db('nibbles');

$iPvThreshold = 90;
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

$sPageTitle = "Nibbles - Process Leads";

//if (!($sUseCurrentTable)) {
	$sOtDataTable = "process_leads.otDataHistoryWorking";
	$sUserDataTable = "process_leads.userDataHistoryWorking";
//} 

$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$sRunDate = "$iCurrMonth-$iCurrDay-$iCurrYear";

//some vars for the processing email report
$aQueryFailures = array();
$aQueryPasses = array();
$iLeadsToal = 0;
$iGroupId =  '0';

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


$sOvernightDataWorkingVarQuery = "SELECT *
			   FROM	  vars
			   WHERE  system = 'cron'
			   AND	  varName = 'otDataUserDataWorking'";
$rOvernightDataWorkingVarResult= dbQuery($sOvernightDataWorkingVarQuery);
while ($oOvernightDataWorkingVarRow = dbFetchObject($rOvernightDataWorkingVarResult)) {
	$iOvernightDataWorkingScriptVar = $oOvernightDataWorkingVarRow->varValue;
}

if ($iOvernightDataWorkingScriptVar){
	$msg = "Overnight processing:can't run, process_leads.otDataHistoryWorking is still populating.";
	mail('bbevis@amperemedia.com,samir@amperemedia.com,7739344565@tmomail.net,6306700018@messaging.sprintpcs.com',"Overnight processing couldn't run.",$msg);
	exit();
} else if($iOvernightDataMoveScriptVar){
	$msg = "Overnight processing: can't run, overnight data move is still running.";
	mail('bbevis@amperemedia.com,samir@amperemedia.com,7739344565@tmomail.net,6306700018@messaging.sprintpcs.com',"Overnight processing couldn't run.",$msg);
	exit();
} else if($iDedupScriptVar){
	$msg = "Overnight processing:can't run, dedup is still running.";
	mail('bbevis@amperemedia.com,samir@amperemedia.com,7739344565@tmomail.net,6306700018@messaging.sprintpcs.com',"Overnight processing couldn't run.",$msg);
	exit();	
}

//set a ver for this guy
	
$sDedupVarQuery = "UPDATE vars SET varValue = varValue+1 WHERE varName = 'processLeadsRunning';";
$rDedupVarResult= dbQuery($sDedupVarQuery);

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
// start of track users' activity in nibbles 
$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Processed leads\")"; 
$rLogResult = dbQuery($sLogAddQuery); 
// end of track users' activity in nibbles		

echo "<br>Process Leads Started.<br>";
								
$startTime = date("U");
echo "process leads:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
//$sThresholdTotal = "SELECT count(*) as total FROM otDataHistory WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)";
$sThresholdTotal = "SELECT count(*) as total FROM process_leads.otDataHistoryWorking WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)";
$rThresholdTotal = dbQuery( $sThresholdTotal );
$oThresholdTotal = dbFetchObject( $rThresholdTotal );
$iThresholdTotal = $oThresholdTotal->total;

//$sThresholdPV = "SELECT count(*) as pv FROM otDataHistory WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)	AND postalVerified = 'V'";
$sThresholdPV = "SELECT count(*) as pv FROM process_leads.otDataHistoryWorking WHERE dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY)
								AND postalVerified = 'V'";
$rThresholdPV = dbQuery( $sThresholdPV );
$oThresholdPV = dbFetchObject( $rThresholdPV );
$iThresholdPV = $oThresholdPV->pv;
//echo "process end time : "
$endTime = date("U");
echo "<br> &nbsp; &nbsp; query: ".$sThresholdPV."<br>";
echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
flush();
ob_flush();
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
		
		
	// START CODE FOR SENDING OUT ALERT EMAIL IF THE LAST LEAD DATE IS APPROACHING //
	$sQuery1 = "SELECT offerLeadSpec.offerCode, offerLeadSpec.lastLeadDate, offerCompanies.repDesignated
			FROM offerLeadSpec LEFT JOIN offers ON offerLeadSpec.offerCode = offers.offerCode LEFT JOIN offerCompanies ON offerCompanies.id
 = offers.companyId
			WHERE offerLeadSpec.lastLeadDate BETWEEN CURRENT_DATE
			AND date_add(CURRENT_DATE, INTERVAL +10 DAY)
			ORDER BY offerLeadSpec.offerCode";
	$rResult1 = dbQuery($sQuery1);
	
	$sQuery2 = "SELECT offerLeadSpec.offerCode,offerLeadSpec.lastLeadDate, offerCompanies.repDesignated
				FROM offerLeadSpec LEFT JOIN offers ON offerLeadSpec.offerCode = offers.offerCode LEFT JOIN offerCompanies ON offerCompanies.id = offers.companyId
				WHERE offerLeadSpec.lastLeadDate BETWEEN date_add(CURRENT_DATE, INTERVAL -60 DAY)
				AND date_add(CURRENT_DATE, INTERVAL -1 DAY)
				ORDER BY offerLeadSpec.offerCode";
	$rResult2 = dbQuery($sQuery2);
	
	$sReportContent1 = "
		<html><head>
		<style =\"text/css\">
		TD.big {
			FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 12px; COLOR: #000000;
		}
		TD.header {
		FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
		}
		</style>
		</head>
		<body>
		<table border='1' align='center' width=400>
		<tr align=center><td class=big colspan=3>
		<b>Last Lead Date Approaching</b></td></tr>
		<tr><td class=header><b>Offer Code</b></td>
		<td class=header><b>Last Lead Date</b></td>
		<td class=header><b>Acct. Exec.</b></td></tr>";
	while ($oRow1 = dbFetchObject($rResult1)) {
		$sReportContent1 .= "<tr><td class=big>$oRow1->offerCode</td>
				<td class=big>$oRow1->lastLeadDate</td>";
				
				$sAcctExec = "SELECT firstName, lastName FROM nbUsers where id in (".$oRow1->repDesignated.")";
				$res = dbQuery($sAcctExec);
				$oUser = dbFetchObject($res);

		$sReportContent1 .= "<td class=big>$oUser->firstName $oUser->lastName</td></tr>";
	}
	
	$sReportContent1 .= "<tr><td class=big colspan=3>&nbsp;</td></tr>
						<tr align=center><td class=big colspan=3>
						<b>Past Last Lead Date</b></td></tr>
						<tr><td class=header><b>Offer Code</b></td>
						<td class=header><b>Last Lead Date</b></td>
						<td class=header><b>Acct. Exec.</b></td></tr>";
	
	while ($oRow2 = dbFetchObject($rResult2)) {
		$sReportContent1 .= "<tr><td class=big>$oRow2->offerCode</td>
				<td class=big>$oRow2->lastLeadDate</td>";

				$sAcctExec = "SELECT firstName, lastName FROM nbUsers where id in (".$oRow2->repDesignated.")";
                                $res = dbQuery($sAcctExec);
                                $oUser = dbFetchObject($res);

                $sReportContent1 .= "<td class=big>$oUser->firstName $oUser->lastName</td></tr>";
	}
	
	
	$sReportContent1 .= "</table></body></html>";
	
	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$sHeaders .= "From:nibbles@amperemedia.com\r\n";
	

	$sEmailQuery = "SELECT * FROM emailRecipients WHERE purpose='last lead date approaching'";
	$rEmailResult = dbQuery($sEmailQuery);
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		mail($oEmailRow->emailRecipients, "Last Lead Date Report - $sRunDate", $sReportContent1, $sHeaders);
	}
	
	$sHeaders = '';
	$sReportContent1 = '';
	// END CODE FOR SENDING OUT ALERT EMAIL IF THE LAST LEAD DATE IS APPROACHING //

	
	/**************  get one offer/group which and make sure it is scheduled to process leads today  *****************/

	echo '.';
	flush();
	ob_flush();
					// include custom leads validation script here
				//echo "process start time : "
	$startTime = date("U");
		
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

	echo '.';
	flush();
	ob_flush();
	
	$sOffersQuery .= " ORDER BY leadsGroupId DESC, offerCode";
						//echo "process start time : "
	$startTime = date("U");
	echo "offers query: <br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rOffersResult = dbQuery($sOffersQuery);
	
	echo dbError();
	//echo "process end time : "
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sOffersQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();

	$aOffersLoopTimes = array();
	$aTempLeadsQueryTimes = array();
	$aMutExQueryTimes = array();
				
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$offersstartTime = date("U");
		echo '.';
		flush();
		ob_flush();
		$sLeadsData = '';

		$iTempDeliveryMethodId = $oOffersRow->deliveryMethodId;
		$sTempOfferCode = $oOffersRow->offerCode;
		$sTempLeadsQuery = $oOffersRow->leadsQuery;
		
		$sTempLeadsQuery = eregi_replace("otDataHistoryWorking", $sOtDataTable,$sTempLeadsQuery);
		$sTempLeadsQuery = eregi_replace("userDataHistoryWorking", $sUserDataTable,$sTempLeadsQuery);
		
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
			//echo "process start time : "
			$sLeadsGroupQuery = "SELECT *
				FROM   leadGroups
				WHERE  id = '$iTempLeadsGroupId'";
			$rLeadsGroupResult = dbQuery($sLeadsGroupQuery);
			//echo "process end time : "
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
						AND ", $sTempLeadsQuery);
			}

			if ($sTempLeadsQuery != '') {
								
				$sTempLeadsQuery = eregi_replace('\$iTempMaxAgeOfLeads',$iTempMaxAgeOfLeads,$sTempLeadsQuery);
				if (stristr($sTempLeadsQuery, "SELECT DISTINCT")) {
					$sTempLeadsQuery = eregi_replace("SELECT DISTINCT", "SELECT DISTINCT $sOtDataTable.id, ", $sTempLeadsQuery);
				} else {
					$sTempLeadsQuery = eregi_replace("SELECT", "SELECT $sOtDataTable.id, ", $sTempLeadsQuery);
				}


				if ($sOtDataTable == 'otData') {

					//$sTempLeadsQuery = eregi_replace("process_leads.otDataHistoryWorking", $sOtDataTable,$sTempLeadsQuery);
					//$sTempLeadsQuery = eregi_replace("process_leads.userDataHistoryWorking", $sUserDataTable,$sTempLeadsQuery);
					
					$sTempLeadsQuery = eregi_replace("process_leads.otDataHistoryWorking", $sOtDataTable,$sTempLeadsQuery);
					$sTempLeadsQuery = eregi_replace("process_leads.userDataHistoryWorking", $sUserDataTable,$sTempLeadsQuery);
					$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","",$sTempLeadsQuery);
					$sTempLeadsQuery = eregi_replace("AND mode = 'A'","",$sTempLeadsQuery);
					$sTempLeadsQuery = eregi_replace("AND address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
					$sTempLeadsQuery = eregi_replace("WHERE address NOT LIKE '3401 DUNDEE%'","", $sTempLeadsQuery);
				} else {
					$sTempLeadsQuery = eregi_replace("AND postalVerified = 'V'","AND $sUserDataTable.postalVerified = 'V'",$sTempLeadsQuery);
				}
			}

			/*************************/
			//echo "process start time : "
			$startTime = date("U");
			$sTempLeadsQuery = eregi_replace('\$iTempMaxAgeOfLeads',$iTempMaxAgeOfLeads,$sTempLeadsQuery);
			//echo __LINE__.", $iTempMaxAgeOfLeads is iTempMaxAgeOfLeads";
			$rTempLeadsResult = dbQuery($sTempLeadsQuery);
			//echo "maybe so";
			//echo "process end time : "
			$endTime = date("U");
			array_push($aTempLeadsQueryTimes,($endTime - $startTime));

			if (!($rTempLeadsResult)) {
				echo "<BR>$sTempOfferCode ".$sTempLeadsQuery.dbError();
				$aQueryFailures[$sTempOfferCode] = $sTempLeadsQuery."\n".dbError();
			}

			if (! $rTempLeadsResult) {
				echo "<BR>$sTempOfferCode Query Error: ".dbError();
				$aQueryFailures[$sTempOfferCode] = $sTempLeadsQuery."\n".dbError();

			} else {
				$iNumFields = dbNumFields($rTempLeadsResult);
				$iLeadsCount = dbNumRows($rTempLeadsResult);
				$j = 1;
				$iLeadCounter = 1;
				$iDailyLeadCounter = 1;
				array_push($aQueryPasses,$sTempOfferCode);

				echo '.';
				flush();
				ob_flush();
								/*******  Check if the offer has any mutually exclusive offers  *********/
				$sMutExclusiveQuery = "SELECT *
							   FROM   offersMutExclusive
							   WHERE  offerCode1 = '$sTempOfferCode'
							   OR     offerCode2 = '$sTempOfferCode'";		
												//echo "process start time : "					
												
				$startTime = date("U");
				$rMutExclusiveResult = dbQuery($sMutExclusiveQuery);
				$endTime = date("U");
				array_push($aMutExQueryTimes,($endTime - $startTime));
				flush();
				ob_flush();

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



				while ($aTempLeadsRow = dbFetchArray($rTempLeadsResult)) {

					$iId = $aTempLeadsRow['id'];
					$sTempLeadEmail = $aTempLeadsRow['email'];


					/**************  Check if leads are mutually exclusive  ****************/


					if ($sMutExclusiveOffers != '') {

						// check if this lead is delivered to any mutually exclusive offers
						/*
										$sMutCheckQuery = "SELECT *
													   FROM   otDataHistory
													   WHERE  email = '$sTempLeadEmail'
													   AND    offerCode IN (".$sMutExclusiveOffers.")
													   AND    (sendStatus = 'S'
													   OR     processStatus = 'P' and date_format(dateTimeProcessed,'%Y-%m-%d') = CURRENT_DATE)";
						*/
						$sMutCheckQuery = "SELECT *
									FROM   process_leads.otDataHistoryWorking
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
											
												//if we're using the normal working table, then we've got to update
												//both the working table, and the normal history table, so this one
												//is for the normal history table.
							$sRejectMutExclQuery = "UPDATE otDataHistory
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

						
						$sProcessStatusUpdateQuery = "UPDATE otDataHistory
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
				$iLeadsToal += $iLeadsCount;
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
					chmod("$sTodaysLeadsFolder/offers/$sTempOfferCode/$sTempLeadFileName",0777);
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
							
			$offersendTime = date("U");
			array_push($aOffersLoopTimes, ($offersendTime - $offersstartTime));
		} // end of offers while loop

									//echo "process end time : "
									
	$sum = 0;	
	foreach($aOffersLoopTimes as $sTime){$sum += $sTime;} 					
	echo "<br>".__LINE__." avg offer loop time: ".($sum / count($aOffersLoopTimes))."<br>";
	$sum = 0;	
	foreach($aTempLeadsQueryTimes as $sTime){$sum += $sTime;} 	
	echo "<br>".__LINE__." avg temp leads query time: ".($sum/ count($aTempLeadsQueryTimes))."<br>";
	$sum = 0;	
	foreach($aMutExQueryTimes as $sTime){$sum += $sTime;} 	
	echo "<br>".__LINE__." avg mutex query time: ".($sum/ count($aMutExQueryTimes))."<br>";
	flush();
	ob_flush();
	
	
	$sEmailReport = "Totaled count from the processing loop: $iLeadsToal\n";
	
	$sql = "SELECT count(*) as count FROM process_leads.otDataHistoryWorking WHERE processStatus = 'P' AND dateTimeAdded like '".date('Y-m-d',strtotime('yesterday'))."%';";
	$resp = dbQuery($sql);
	$obj = dbFetchObject($resp);
	$sEmailReport .= "Yesterday's processed leads: ".$obj->count."\n";
	
	$sql = "SELECT count(*) as count FROM process_leads.otDataHistoryWorking WHERE processStatus = 'R' AND dateTimeAdded like '".date('Y-m-d',strtotime('yesterday'))."%';";
	$resp = dbQuery($sql);
	$obj = dbFetchObject($resp);
	$sEmailReport .= "Yesterday's rejected leads: ".$obj->count."\n";
	
	$sql = "SELECT count(*) as count FROM process_leads.otDataHistoryWorking WHERE processStatus IS NULL AND dateTimeAdded like '".date('Y-m-d',strtotime('yesterday'))."%';";
	$resp = dbQuery($sql);
	$obj = dbFetchObject($resp);
	$sEmailReport .= "Yesterday's NULL processStatus leads: ".$obj->count."\n\n";
	
	
	if(count(array_keys($aQueryFailures))){
		$sEmailReport .= "There were query errors:\n";
		foreach($aQueryFailures as $k => $v){
			$sEmailReport .= "\n$k => $v\n";
		}
	}
	if(count($aQueryPasses)){
		$sEmailReport .= "Offers that processed successfully: ".join(', ',$aQueryPasses)."\n\n";
	}
	mail('it@amperemedia.com','Overnight Leads Processing Report', $sEmailReport);

} // if offersQuery != ''



$sDedupVarQuery = "UPDATE vars SET varValue = varValue-1 WHERE varName = 'processLeadsRunning';";
$rDedupVarResult= dbQuery($sDedupVarQuery);
/*	
$cronReportingSql = "UPDATE cronScriptStatus SET endDateTime = now() WHERE id = $cronReportingId";
$reportingResponse = dbQuery($cronReportingSql);
*/

cssLogFinish( $iScriptId );

include_once("/home/sites/admin.popularliving.com/html/admin/processLeads/sendLeads.php");
?>
						

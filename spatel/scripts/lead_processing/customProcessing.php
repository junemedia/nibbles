<?php


/*
This is custom processing script that runs after datamove and dedup And before populating working table.

We use this custom processing script for offer that requires scrubbing leads that we can't do using our system.

For example:  Offer ABC.  The client requested that we should only send leads if they are within certain zip range,
user age, phone range, state, or any other type of scrubbing leads.  This script will reject the leads if they don't fall
within client's requiremnts so that way we don't send this to client.

This script will mark leads as NCV (non custom validation) so we know that this lead doesn't qualify so we don't send the leads to client.


	BEFORE YOU MAKE ANY CHANGES TO THIS SCRIPTS, PLEASE TALK TO SPATEL OR BBEVIS.

	-- SPATEL - 7/24/06 4:15PM
	
	
	NOTE:
		FOLLOWING TWO TABLES MOVED FROM NIBBLES DATABASE TO PROCESS_LEADS DATABASE
			customProcessingApprovedZips
			customProcessingExcludedZips
	-- SPATEL - 7/24/06 4:00PM
	
*/



$sThisScriptName = "customProcessing.php";
$iScriptId = cssLogStart( "$sThisScriptName" );

$dbase = 'nibbles';
mysql_select_db ($dbase);





// 
/*
// devry custom processing
$sCustomOfferQuery = "SELECT $sOtDataTable.id
					  FROM   $sOtDataTable
					  WHERE  postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'DEVRY'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);

while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT C.zip
					FROM $sOtDataTable O, $sUserDataTable U, customProcessingApprovedZips C 	
					WHERE C.offerCode = 'DEVRY' 							
					AND	  substring(U.zip,1,5) = C.zip
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);

	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE $sOtDataTable
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
//		echo "<BR>".$sRejectQuery.mysql_error();
	}
}
*/


// keller custom processing
/*
$sCustomOfferQuery = "SELECT $sOtDataTable.id
					  FROM   $sOtDataTable, $sUserDataTable
					  WHERE $sOtDataTable.email = $sUserDataTable.email
					  AND   postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'KELLER'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);

while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT C.zip
					FROM $sOtDataTable O, $sUserDataTable U, customProcessingApprovedZips C 	
					WHERE C.offerCode = 'KELLER' 							
					AND	  substring(U.zip,1,5) = C.zip
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);

	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE $sOtDataTable
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		//echo "<BR>".$sRejectQuery.mysql_error();
	}
}


*/





//echo "start: BMG\n";
// BMG START VALIDATING PAGE2DATA
$sCustomOfferQuery = "SELECT id FROM otDataHistory WHERE offerCode = 'BMG' AND LENGTH(page2Data) < 27 AND sendStatus IS NULL";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);
//echo dbError();
$sMsg = '';
while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$sUpdateOffer = "UPDATE otDataHistory
					SET processStatus = 'R', reasonCode = 'ncv',
					sendStatus = 'N', dateTimeProcessed = NOW()
					WHERE id = '$oCustomOfferRow->id' LIMIT 1";
	$rUpdateOfferResult = dbQuery($sUpdateOffer);
	//echo dbError();
	$sMsg .= $sUpdateOffer."\n\n";
}
if ($sMsg !='') {
	mail('it@amperemedia.com',"Invalid BMG Leads: ".__FILE__,$sMsg);
}
// BMG END VALIDATING PAGE2DATA
//echo "end: BMG\n";

















//echo "start: TMCH_IMAIL";
//flush();
//ob_flush();

// TMCH_IMAIL custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'TMCH_IMAIL'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);
echo dbError();
while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT W.areaCode
					FROM otDataHistory O, userDataHistory U, tmchImailPhoneData AS W
					WHERE substring(U.phoneNo,1,3) = W.areaCode
					AND   substring(U.phoneNo,5,3) = W.exchange
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);
	echo dbError();
	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		echo dbError();
	}
	echo '.';
}

echo "end: TMCH_IMAIL";
echo "start: WNWM_VML";
//flush();
//ob_flush();





// WNWM_VML custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE  postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'WNWM_VML'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);
echo dbError();
while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT W.areaCode
					FROM otDataHistory O, userDataHistory U, process_leads.wnwmVmlPhoneData AS W 	
					WHERE substring(U.phoneNo,1,3) = W.areaCode
					AND   substring(U.phoneNo,5,3) = W.exchange
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);
	echo dbError();
	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		echo dbError();
	}
	echo '.';
}
echo "end: WNWM_VML";
echo "start: wnwm_eml";
//flush();
//ob_flush();

// WNWM_EML custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE  postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'WNWM_EML'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);
echo dbError();
while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT W.areaCode
					FROM otDataHistory O, userDataHistory U, process_leads.wnwmEmlPhoneData AS W 	
					WHERE substring(U.phoneNo,1,3) = W.areaCode
					AND   substring(U.phoneNo,5,3) = W.exchange
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);
	echo dbError();
	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		echo dbError();
	}
	echo '.';
}
echo "end: WNWM_EML";
//flush();
//ob_flush();



include("/home/scripts/includes/validationFunctions.php");
echo "start: TALC_HAR";
//flush();
//ob_flush();

// TALC_HAR custom processing

// Pull valid zips
$aTALC_HARValidZips = array();
$sTALC_HARZipQuery = "SELECT zip FROM process_leads.talc_harZipData";
$rTALC_HARZipOfferResult = dbQuery($sTALC_HARZipQuery);
while ($oTALC_HARZipRow = dbFetchObject($rTALC_HARZipOfferResult)) {
	$aTALC_HARValidZips[] = $oTALC_HARZipRow->zip;
}

$iTALC_HARdistance = 30;
// remove before going live!!
//$sOtDataTable = 'otData';
//$sUserDataTable = 'userData';

$sTALC_HARCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE   sendStatus IS NULL
						AND	(processStatus IS NULL || processStatus='P') 
					  AND   offerCode = 'TALC_HAR'";

$live_sTALC_HARCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE  postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'TALC_HAR'";

$rTALC_HARCustomOfferResult = dbQuery($sTALC_HARCustomOfferQuery);
echo dbError();

// loop thru customer leads
while ($oTALC_HARCustomOfferRow = dbFetchObject($rTALC_HARCustomOfferResult)) {
	$iTALC_HARid = $oTALC_HARCustomOfferRow->id;
	

	$sTALC_HARCustomZipQuery = "SELECT U.zip
					FROM otDataHistory O, userDataHistory U
					WHERE O.email = U.email 
					AND O.id = '$iTALC_HARid'";
	$rTALC_HARCustomZipResult = dbQuery($sTALC_HARCustomZipQuery);
	echo dbError();
	$oTALC_HARCustomZipRow = dbFetchObject($rTALC_HARCustomZipResult);

	print $iTALC_HARid . " " . $oTALC_HARCustomZipRow->zip . "<br>\n";

	$isValid = 0;
	// loop thru valid zips and test: within 30 miles of lead zip?
	foreach( $aTALC_HARValidZips as $sTALC_HARvalidzip ) {
		// exceedsMaxDistance returns TRUE if 2 zips are within the given distance
		if( exceedsMaxDistance($oTALC_HARCustomZipRow->zip, $sTALC_HARvalidzip, $iTALC_HARdistance) ) {
			//			print $oTALC_HARCustomZipRow->zip . " " . $sTALC_HARvalidzip . " is valid<br>\n";			
			$isValid = 1;
			break;			
		}
	}	
	
	if( $isValid != 1 ) {
		$sTALC_HARRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iTALC_HARid'";

		$rTALC_HARRejectResult = dbQuery($sTALC_HARRejectQuery);
		//print $sTALC_HARRejectQuery . "<br>";
		echo dbError();
	}
}

echo "end: TALC_HAR";
//flush();
//ob_flush();

echo "start: G4L_HAIR";
//flush();
//ob_flush();

// G4L_HAIR custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'G4L_HAIR'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);

while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT C.zip
					FROM otDataHistory O, userDataHistory U, process_leads.G4L_HAIRZipData C
					WHERE substring(U.zip,1,5) = C.zip
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);

	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		//echo "<BR>".$sRejectQuery.mysql_error();
	}
}

echo "end: G4L_HAIR";
//flush();
//ob_flush();


echo "start: KPA_HAIR";
//flush();
//ob_flush();

// KPA_HAIR custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'KPA_HAIR'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);

while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT C.zip
					FROM otDataHistory O, userDataHistory U, process_leads.KPA_HAIRZipData C
					WHERE substring(U.zip,1,5) = C.zip
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);

	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		//echo "<BR>".$sRejectQuery.mysql_error();
	}
}

echo "end: KPA_HAIR";
//flush();
//ob_flush();

echo "start: DSC_MEM";
flush();
ob_flush();

// DSC_MEM custom processing
$sCustomOfferQuery = "SELECT otDataHistory.id
					  FROM   otDataHistory
					  WHERE  postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'DSC_MEM'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);
echo dbError();
while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT W.areaCode
					FROM otDataHistory O, userDataHistory U, dscmemPhoneData AS W 	
					WHERE substring(U.phoneNo,1,3) = W.areaCode
					AND   substring(U.phoneNo,5,3) = W.exchange
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);
echo dbError();
	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE otDataHistory
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		echo dbError();
	}
	echo '.';
}
echo "end: DSC_MEM";
flush();
ob_flush();


/*
// wpea_uop custom processing
$sCustomOfferQuery = "SELECT $sOtDataTable.id
					  FROM   $sOtDataTable
					  WHERE postalVerified = 'V'
					  AND	(processStatus IS NULL || processStatus='P') 
				 	  AND   sendStatus IS NULL
					  AND   offerCode = 'WPEA_UOP'";
$rCustomOfferResult = dbQuery($sCustomOfferQuery);

while ($oCustomOfferRow = dbFetchObject($rCustomOfferResult)) {
	$iCustomId = $oCustomOfferRow->id;
	$sCustomZipQuery = "SELECT C.zip
					FROM $sOtDataTable O, $sUserDataTable U, customProcessingApprovedZips C 	
					WHERE C.offerCode = 'WPEA_UOP' 							
					AND	  substring(U.zip,1,5) = C.zip
					AND O.email = U.email 
					AND O.id = '$iCustomId'";
	$rCustomZipResult = dbQuery($sCustomZipQuery);

	if (dbNumRows($rCustomZipResult) == 0 ) {
		// reject this lead
		$sRejectQuery = "UPDATE $sOtDataTable
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dateTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";

		$rRejectResult = dbQuery($sRejectQuery);
		//echo "<BR>".$sRejectQuery.mysql_error();
	}
}

*/



/*
$sVerifyQuery = "SELECT O.*, U.zip
				 FROM   $sOtDataTable O, $sUserDataTable U LEFT JOIN customProcessingApprovedZips C ON U.zip = C.zip
				 WHERE  O.email = U.email
				 AND    O.offerCode = 'DEVRY'	
				 AND    O.offerCode = C.offerCode
				 AND    C.zip IS NULL
				 AND (processStatus IS NULL || processStatus='P') 
				 AND sendStatus IS NULL";
$rVerifyResult = mysql_query($sVerifyQuery);
echo mysql_error();
while ($oVerifyRow = mysql_fetch_object($rVerifyResult)) {
	$sDevryZip = $oVerifyRow->zip;
	$iCustomId = $oVerifyRow->id;
	
	$sRejectQuery = "UPDATE $sOtDataTable
						   SET    processStatus = 'R',
								  verified = 'I',
								  reasonCode = 'ncv',
								  dataTimeProcessed = now(),
								  sendStatus = 'N'
						   WHERE  id = '$iCustomId'";
//	$rRejectResult = mysql_query($sRejectQuery);
	echo "<BR>".$sRejectQuery.mysql_error();
}
*/


//$sTestMode
/*
//Line 213 - 324:  Sending Real Time Leads for G4M_MDIS offer.
if (!$sTestMode && ($sProcessOption == 'processAll' || ($sProcessOption == 'processOne' && $sOfferCode=='G4M_MDIS'))) {
	$sDay = date('w');
	if ($sDay==1) {
		$iIntervalNum = 3;
	} else {
		$iIntervalNum = 1;
	}
	
$sDataQuery = "SELECT otDataHistory.id as otdhId, otDataHistory.page2Data, userDataHistory.*
				FROM otDataHistory, userDataHistory
      			WHERE otDataHistory.offerCode='G4M_MDIS'
      			AND otDataHistory.email=userDataHistory.email
      			AND otDataHistory.postalVerified='V'
      			AND otDataHistory.dateTimeAdded > DATE_ADD(CURRENT_DATE, INTERVAL -$iIntervalNum DAY)
      			AND otDataHistory.mode='A'";

$rDataResult = dbQuery($sDataQuery);
echo  dbError();

$count = 0;
while ($oRepRow = dbFetchObject($rDataResult)) {
	$count++;
	$aPage2Data = $oRepRow->page2Data;
	list($ft,$in,$gender,$month,$day,$year,$weight,$phone2,$marital,$resident,$occupation,$income,$hIncome,$yearsAtWork,$comments) = split("\|", $aPage2Data." ");
	$ft=trim($ft,'"');
	$in=trim($in,'"');
	$gender=trim($gender,'"');
	$month=trim($month,'"');
	$day=trim($day,'"');
	$year=trim($year,'"');
	$weight=trim($weight,'"');
	$phone2=trim($phone2,'"');
	$marital=trim($marital,'"');
	$resident=trim($resident,'"');
	$occupation=trim($occupation,'"');
	$income=trim($income,'"');
	$hIncome=trim($hIncome,'"');
	$yearsAtWork=trim($yearsAtWork,'"');
	$comments=trim($comments,'"');

	$sReportContent = "Transaction_ID=$oRepRow->phoneNo
		Foreign_Key1=$oRepRow->phoneNo
		Foreign_Key2=
		Sub_Marketer_Code1=Ampere Media
		Sub_Marketer_Code2=
		Client_Honorific=$oRepRow->salutation
		Client_First_Name=$oRepRow->first
		Client_MI=
		Client_Last_Name=$oRepRow->last
		Client_Suffix=
		Client_Sex=$gender
		Client_DOB=$month/$day/$year
		Client_Height_Feet=$ft
		Client_Height_Inch=$in
		Client_Weight=$weight
		Client_Address1=$oRepRow->address
		Client_Address2=$oRepRow->address2
		Client_City=$oRepRow->city
		Client_State=$oRepRow->state
		Client_Zip=$oRepRow->zip
		Client_Phone_Home=$oRepRow->phoneNo
		Client_Phone_Work=$phone2
		Client_Phone_Work_Ext=$oRepRow->extension
		Client_eMail=$oRepRow->email
		Client_Marital_Status=$marital
		Client_Citizenship=$resident
		Client_Occupation=$occupation
		Client_Occupation_Yrs=$yearsAtWork
		Client_Annual_Income=$income
		Client_Household_Income=$hIncome
		Client_DNIS=9723
		Comments=$comments
		";

	$sUrlPart = "diana.matrixdirect.com/AutoLeads/autoleadsctrl";
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	$sHttpPostString = "filecount=1&rootname=leads&encrypted=false&marketid=MID63&transmethod=jms&queue=ascii_prop_1_ne&leads0=$sReportContent";

	if ($rSocketConnection) {
		fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rSocketConnection, "Host: $sHostPart\r\n");
		fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
		fputs($rSocketConnection, $sHttpPostString);

		while(!feof($rSocketConnection)) {
			$sRealTimeResponse .= fgets($rSocketConnection, 1024);
		}

		fclose($rSocketConnection);
		$sUpdateStatusQuery = "UPDATE otDataHistory
				SET sendStatus = 'S',
				processStatus = 'P',
				howSent = 'rtfp',
				dateTimeProcessed = now(),
				dateTimeSent = now(),
				realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
				WHERE otDataHistory.id = $oRepRow->otdhId
				limit 1";
		$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
		echo dbError();
	}
	sleep(5);
}
echo "G4M_MDIS: - ".$count."<br>";

}*/


cssLogFinish( $iScriptId );


?>


<?php

/**********   SCRIPT MUST RUN EVERYDAY  ******************************/
/*********************************************************************
** This script runs nightly, and must be completed before running any
** of the "dedup" processing scripts.
** The script performs the following tasks:
		Verify this script is not currently running
		Write script Data to cronScriptStatus
		Increase script var "overnightDataMove" by 1
		Cycle all of Yesterday's userData entries {
			Check Exists in History
			Yes? If not PV, then update.
			Yes? If PV, do nothing.
			No? Insert new.
			Delete This Entry in Current
		}
		Cycle all of Yesterday's otData entries {
			Insert into History
			Delete from Current
		}
		Update ProcessStatus and SendStatus NULL where blank
		Decrease "overnightDataMove" by 1
		Write Script end times.
		
		
•	overnightDataMove.php:  This script performs a transfer of the previous day’s data into the history table for both nibbles.otData and nibbles.userData.  The transfer takes into account any duplicate entries and the updates that need to be made, as well as some general cleanup of columns that should be blank.
*********************************************************************/

//include( "/home/scripts/includes/cssLogFunctions.php" );
//$iScriptId = cssLogStart( "overnightDataMove.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


	$sYesterdayQuery = "SELECT date_add(CURRENT_DATE,INTERVAL -1 DAY) as yesterday";
	$rYesterdayResult = dbQuery($sYesterdayQuery);
	while ($oYesterdayRow = dbFetchObject($rYesterdayResult)) {
		$sYesterday = $oYesterdayRow->yesterday;
	}

	// Set nibbles.vars, "overnightDataMove" +1, prevent running twice, or starting dedupScripts

	$sVarUpdateQuery = "UPDATE vars
                                        SET    varValue = varValue+1
                                        WHERE  system = 'cron'
                                        AND    varName = 'overnightDataMove'";
	$rVarUpdateResult = dbQuery($sVarUpdateQuery);
	echo dbError();

	// Start: Loop userData entries

	$sUserDataSelectQuery = "SELECT userData.*
                                           FROM   userData
                                           WHERE  userData.dateTimeAdded < CURRENT_DATE";
	$rUserDataSelectResult = dbQuery($sUserDataSelectQuery);
	echo dbError();

	while ($oUserDataSelectRow = dbFetchObject($rUserDataSelectResult)) {
		//echo "Within Loop\n";
		$iId = $oUserDataSelectRow->id;
		$sEmail = $oUserDataSelectRow->email;
		$sSalutation = $oUserDataSelectRow->salutation;
		$sFirst = addslashes($oUserDataSelectRow->first);
		$sLast = addslashes($oUserDataSelectRow->last);
		$sAddress = addslashes($oUserDataSelectRow->address);
		$sAddress2 = addslashes($oUserDataSelectRow->address2);
		$sCity = $oUserDataSelectRow->city;
		$sState = $oUserDataSelectRow->state;
		$sZip = $oUserDataSelectRow->zip;
		$sPhoneNo = $oUserDataSelectRow->phoneNo;
		$sDateTimeAdded = $oUserDataSelectRow->dateTimeAdded;
		$sPostalVerified = $oUserDataSelectRow->postalVerified;
		$sPostalErrors = $oUserDataSelectRow->postalErrors;
		$sSessionId = $oUserDataSelectRow->sessionId;
		$sUserRemoteIp = $oUserDataSelectRow->remoteIp;
		$sDateOfBirth = $oUserDataSelectRow->dateOfBirth;
		$sGender = $oUserDataSelectRow->gender;

		// Check User Already Exists?

		$sCheckQuery = "SELECT *
                                        FROM   userDataHistory
                                        WHERE  email = '$sEmail'";
		$rCheckResult = dbQuery($sCheckQuery);
		if ( dbNumRows($rCheckResult) == 0 ) {

			// If User Not Already Exists

			$sUserInsertQuery = "INSERT IGNORE INTO userDataHistory(email, first, last, address, address2, city, state, zip,
                                                                phoneNo, dateTimeAdded, postalVerified, postalErrors, sessionId, remoteIp, dateOfBirth, gender)
                                                 VALUES(\"$sEmail\", \"$sFirst\", \"$sLast\", \"$sAddress\", \"$sAddress2\", \"$sCity\", \"$sState\",
                                                                \"$sZip\", \"$sPhoneNo\", \"$sDateTimeAdded\", \"$sPostalVerified\", \"$sPostalErrors\", \"$sSessionId\", \"$sUserRemoteIp\", \"$sDateOfBirth\", \"$sGender\")";

			$rUserInsertResult = dbQuery($sUserInsertQuery);
			if (!($rUserInsertResult)) {
				echo $sUserInsertQuery.dbError();
			}

		} else {

			// If User Already Exists, Cycle Entries

			while ($oCheckRow = dbFetchObject($rCheckResult)) {
				$iTempId = $oCheckRow->id;
				$sOldPostalVerified = $oCheckRow->postalVerified;
				if (strtoupper($sOldPostalVerified) == 'N' || ($sPostalVerified == 'V')) {

					// If User Exists, Not Postal Verified

					$sUpdateQuery = "UPDATE userDataHistory
                                                         SET    first = \"$sFirst\",
                                                                        last = \"$sLast\",
                                                                        address = \"$sAddress\",
                                                                        address2 = \"$sAddress2\",
                                                                        city = \"$sCity\",
                                                                        zip = \"$sZip\",
                                                                        phoneNo = \"$sPhoneNo\",
                                                                        postalVerified = \"$sPostalVerified\",
                                                                        postalErrors = '',
                                                                        sessionId = \"$sSessionId\",
                                                                        remoteIp = \"$sUserRemoteIp\",
                                                                        dateOfBirth = \"$sDateOfBirth\",
                                                                        gender = \"$sGender\"
                                                         WHERE  id = '$iTempId'";
					//echo $sUpdateQuery;
					$rUpdateResult = dbQuery($sUpdateQuery);
					echo dbError();

					$sOldPostalVerified = '';
				} else {
					// This entry can be ignored.
					$rUpdateResult = true;
				}

				// If User Exists, Postal Verified, DO NOTHING
			}
		}

		// Delete Entry from Current Table, once processed

		if ($rUserInsertResult || $rUpdateResult) {
			$sUserDataDeleteQuery = "DELETE FROM userData
                                                         WHERE  dateTimeAdded < CURRENT_DATE
                                                        AND id = '$iId'";
			//echo $sUserDataDeleteQuery;
			$rUserDataDeleteResult = dbQuery($sUserDataDeleteQuery);
		}
		unset( $rUpdateResult );
	}

	// End: Loop userData entries



	// Start: Loop otData entries

	$sOtDataSelectQuery = "SELECT *
                                           FROM   otData
                                           WHERE  dateTimeAdded < CURRENT_DATE";
	$rOtDataSelectResult = dbQuery($sOtDataSelectQuery);
	echo dbError();
	while ($oOtDataSelectRow = dbFetchObject($rOtDataSelectResult)) {
		$iId = $oOtDataSelectRow->id;
		$sEmail = $oOtDataSelectRow->email;
		$sOfferCode =  $oOtDataSelectRow->offerCode;
		$fRevPerLead = $oOtDataSelectRow->revPerLead;
		$sSourceCode = $oOtDataSelectRow->sourceCode;
		$sSubSourceCode  = $oOtDataSelectRow->subSourceCode;
		$iPageId = $oOtDataSelectRow->pageId;
		$sDateTimeAdded = $oOtDataSelectRow->dateTimeAdded;
		$sPostalVerified = $oOtDataSelectRow->postalVerified;
		$sVerified  = $oOtDataSelectRow->verified;
		$sProcessStatus = $oOtDataSelectRow->processStatus;
		$sReasonCode = $oOtDataSelectRow->reasonCode;
		$sDateTimeProcessed = $oOtDataSelectRow->dateTimeProcessed;
		$sSendStatus  = $oOtDataSelectRow->sendStatus;
		$sDateTimeSent =  $oOtDataSelectRow->dateTimeSent;
		$sHowSent = $oOtDataSelectRow->howSent;
		$sRealTimeResponse = addslashes($oOtDataSelectRow->realTimeResponse);
		$sRemoteIp = $oOtDataSelectRow->remoteIp;
		$sServerIp = $oOtDataSelectRow->serverIp;
		$iIsConfirmed  = $oOtDataSelectRow->isConfirmed;
		$sPage2Data =  addslashes($oOtDataSelectRow->page2Data);
		$sMode  = $oOtDataSelectRow->mode;
		$iLeadCounter =  $oOtDataSelectRow->leadCounter;
		$iDailyLeadCounter = $oOtDataSelectRow->dailyLeadCounter;
		$sSessionId = $oOtDataSelectRow->sessionId;
		$sIsOpenTheyHost = $oOtDataSelectRow->isOpenTheyHost;

		// Insert into History Table

		$sInsertOtDataQuery = "INSERT INTO otDataHistory(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded,
                                                                        postalVerified, verified, processStatus, reasonCode, dateTimeProcessed, sendStatus, dateTimeSent, howSent,
                                                                        realTimeResponse, remoteIp, serverIp, isConfirmed, page2Data, mode, leadCounter, dailyLeadCounter, sessionId, isOpenTheyHost)
                                                           VALUES(\"$sEmail\", \"$sOfferCode\", \"$fRevPerLead\", \"$sSourceCode\", \"$sSubSourceCode\", \"$iPageId\", \"$sDateTimeAdded\",
                                                                        \"$sPostalVerified\", \"$sVerified\", \"$sProcessStatus\", \"$sReasonCode\", \"$sDateTimeProcessed\", \"$sSendStatus\", \"$sDateTimeSent\", \"$sHowSent\",
                                                                        \"$sRealTimeResponse\", \"$sRemoteIp\", \"$sServerIp\", \"$iIsConfirmed\", \"$sPage2Data\", \"$sMode\", \"$iLeadCounter\", \"$iDailyLeadCounter\", \"$sSessionId\", '$sIsOpenTheyHost')";
		$rInsertOtDataResult = dbQuery($sInsertOtDataQuery);
		echo dbError();

		// If Insert Successful, Delete From Current table

		if ($rInsertOtDataResult) {
			$sDeleteOtDataQuery = "DELETE FROM otData
                                                           WHERE  id = '$iId'";
			$rDeleteOtDataResult = dbQuery($sDeleteOtDataQuery);
			echo dbError();
		}
	}

	// End: Loop otData entries

	// Set ProcessStatus = NULL where Blank

	$sProcessStatusUpdateQuery = "UPDATE otDataHistory
                                SET    processStatus = NULL
                                WHERE  processStatus =''
                                AND dateTimeAdded > '$sYesterday 00:00:00'";
	$rProcessStatusUpdateResult = dbQuery($sProcessStatusUpdateQuery);

	// Set SendStatus = NULL where Blank

	$sSendStatusUpdateQuery = "UPDATE otDataHistory
                               SET    sendStatus = NULL
                               WHERE  sendStatus =''
                               AND dateTimeAdded > '$sYesterday 00:00:00'";
	$rSendStatusUpdateResult = dbQuery($sSendStatusUpdateQuery);


	// Set nibbles.vars "overnightDataMove" -1 so that processing can complete

	$sVarUpdateQuery = "UPDATE vars
                                        SET    varValue = varValue-1
                                        WHERE  system = 'cron'
                                        AND    varName = 'overnightDataMove'";
	$rVarUpdateResult = dbQuery($sVarUpdateQuery);
	echo dbError();



	/************  mark test leads as rejected  *************/
	$sOtDataUpdateQuery = "UPDATE otDataHistory
						SET processStatus = 'R', reasonCode = 'tst',
						dateTimeProcessed = now(), sendStatus = 'N',
						dateTimeSent = now()
						WHERE  mode = 'T'
				 		AND (reasonCode <> 'tst' or reasonCode IS NULL)
				 		AND (sendStatus <> 'N' or sendStatus IS NULL)
				 		AND (processStatus <> 'R' or processStatus IS NULL)
				 		AND dateTimeAdded > '$sYesterday 00:00:00'";
	$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);
	/*************  End marking test leads as rejected  *************/

	/************  mark 3401 leads as rejected  *************/
	$sTestLeadsQuery = "SELECT * FROM userDataHistory WHERE address LIKE '3401 DUNDEE%'";
	$rTestLeadsResult = dbQuery($sTestLeadsQuery);
	if ($rTestLeadsResult) {
		while ($oTestLeadsRow = dbFetchObject($rTestLeadsResult)) {
			$sOtDataUpdateQuery = "UPDATE otDataHistory
				 SET    processStatus = 'R',
						reasonCode = 'tst',
						dateTimeProcessed = now()
				 WHERE  email = \"$oTestLeadsRow->email\" 
				 AND dateTimeAdded > '$sYesterday 00:00:00'";
			$rOtDataUpdateResult = dbQuery($sOtDataUpdateQuery);
		}
	}
	/*************  End marking 3401 leads as rejected  *************/
	
//}


//cssLogFinish( $iScriptId );

?>

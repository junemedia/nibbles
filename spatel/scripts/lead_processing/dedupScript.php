<?php

/**********   SCRIPT MUST RUN EVERYDAY  ******************************/
/*********************************************************************
** This script runs nightly, and must be completed after the
** overnightDataMove.php script has completed.
**
** The script performs the following tasks:
                Process passed parameters (startTime, endTime) and Verify
                Verify "overnightDataMove" script is not currently running
                Write script Data to cronScriptStatus
                Increase script var "dedupScriptRunning" by 1

                Loop through otData Leads Not Processed for Times {
                        If user NPV, reject lead
                        Check Same record Last 3 Months, Mark Dup
                }
                Decrease "dedupScriptRunning" by 1
                Write Script end times.
•	dedupScript.php: Finds all duplicate User and Lead entries that are duplicates, and marks them so that they are not send when leads are processed.
*********************************************************************/
$numParameters = $argc;

// Start: Process Parameters, get valid times and Validate

for( $i=0;$i<$numParameters; $i++ ) {
        if ($argv[$i] == "-t1") {
                if ( preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $argv[$i+1] ) ) {
                        $timeStart = $argv[$i+1];
                }
        }
        if ($argv[$i] == "-t2") {
                if ( preg_match("/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/", $argv[$i+1] ) ) {
                        $timeEnd = $argv[$i+1];
                }
        }
}

$sThisScriptName = "dedupScript.php $timeStart $timeEnd";

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "$sThisScriptName" );


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

                
// End: Process and Validate Times

// If Invalid Times

if( !$timeStart || !$timeEnd ) {
        echo "Invalid Start and End Times.\n";
      //  exit();
} else {

        // Else Valid Times

        
        $sOvernightDataMoveVarQuery = "SELECT *
                                                   FROM   vars
                                                   WHERE  system = 'cron'
                                                   AND    varName = 'overnightDataMove'";
        $rOvernightDataVarResult= dbQuery($sOvernightDataMoveVarQuery);
        while ($oOvernightDataMoveVarRow = dbFetchObject($rOvernightDataVarResult)) {
                $iOvernightDataMoveScriptVar = $oOvernightDataMoveVarRow->varValue;
        }

        // Check overnightDataMove Script Finished

        if ( $iOvernightDataMoveScriptVar != '0' ) {

                // If overnightDataMove Not Finished
                $sEmailMsg = "overnightDataMove.php Not completed.  Dedup cannot be started until this is finished.\n\n";
                $sEmailMsg .= date("F j, Y, g:i a")."\n\n";	// March 10, 2001, 5:16 pm
                
                // Send email notification!
                mail('it@amperemedia.com',"Dedup Cannot Be Started: Data Move In Progress", $sEmailMsg);

        } else {

                // Else overnightDataMove Is Finished
                // Start: Process Dups.  Valid Dates and overnightDataMove Finished
                

        		$sYesterdayQuery = "SELECT date_add(CURRENT_DATE,INTERVAL -1 DAY) as yesterday";
                $rYesterdayResult = dbQuery($sYesterdayQuery);
                while ($oYesterdayRow = dbFetchObject($rYesterdayResult)) {
					$sYesterday = $oYesterdayRow->yesterday;
                }

                $sVarUpdateQuery = "UPDATE vars
                                        SET    varValue = varValue+1
                                        WHERE  system = 'cron'
                                        AND    varName = 'dedupScriptRunning'";
                $rVarUpdateResult = dbQuery($sVarUpdateQuery);
                echo dbError();

                // Get Leads not Processed between Start and End Times


                $sLeadQuery = "SELECT otDataHistory.*, date_format(otDataHistory.dateTimeAdded, \"%Y-%m-%d\") dateAdded,
                                          userDataHistory.first, userDataHistory.last, userDataHistory.address, userDataHistory.zip,
                                          userDataHistory.postalVerified
                           FROM   otDataHistory, userDataHistory
                           WHERE  otDataHistory.email = userDataHistory.email
                           AND    otDataHistory.dateTimeAdded BETWEEN '$sYesterday $timeStart' AND '$sYesterday $timeEnd'
                           ORDER BY otDataHistory.dateTimeAdded";
                
                //echo "line ".__LINE__.": selecting leads from BETWEEN '$sYesterday $timeStart' AND '$sYesterday $timeEnd'\n";
                
                $rLeadResult = dbQuery($sLeadQuery);


                
                $i=0;

                echo dbError();
                if ( dbNumRows($rLeadResult) ==0) {
//                      break;
                }

                // Start: Loops leads not processed

                while ($oLeadRow = dbFetchObject($rLeadResult)) {
                		

                		
		               // echo "line ".__LINE__.": lead returned, processing\n";
                		
                        $iId = $oLeadRow->id;
                        $sDateAdded = $oLeadRow->dateAdded;
                        $sOfferCode = $oLeadRow->offerCode;
                        $sEmail = $oLeadRow->email;
                        // get first 3 letters of first name
                        $sFirstFirst3 = substr($oLeadRow->first,0,3);
                        // get first 8 letters of last name
                        $sLastFirst8 = substr($oLeadRow->last,0,8);
                        $sAddressFirst8 = substr($oLeadRow->address,0,8);
                        $sZip = $oLeadRow->zip;
                        $sPostalVerified = $oLeadRow->postalVerified;

                        // Check If User is Postal Verified

                        if ($sPostalVerified == 'N') {


                                // User exists but is Not Postal Verified, reject lead

                                $sUpdateQuery1 = "UPDATE otDataHistory
                                                 SET    processStatus = 'R',
                                                                reasonCode = 'npv',
                                                                sendStatus = 'N',
                                                                dateTimeProcessed = now()
                                                 WHERE  id = '$iId'";
                                $rUpdateResult1 = dbQuery($sUpdateQuery1);
                                echo dbError();

                        } else {

                                // User exists and is Postal Verified
                                // Check if Record Exists in Past 3 Months

                                // Note: use otDataHistory.id < '$iId' to get older


                                
                                
                                $sCheckUsersQuery = "SELECT id 
													FROM userDataHistory 
													WHERE email = '$sEmail'";
				               // echo "line ".__LINE__.": selecting from userDataHistory\n";
				               // echo "query: $sCheckUsersQuery\n";
				                
		               			$timeQStart = date("U");
                                $rCheckUsersResult = dbQuery($sCheckUsersQuery);
                				$timeQDone = date("U");
								$timeQDiff = $timeQDone - $timeQStart;
				                //echo "query time: $timeQDiff\n";
                                $ids = array();
                                while($oRow = dbFetchObject($rCheckUsersResult)){
                                	array_push($ids,$oRow->id);
                                }

								$sCheckDupQuery = "SELECT otDataHistory.*
                                           FROM   otDataHistory, userDataHistory
                                           WHERE  otDataHistory.email = userDataHistory.email
                                           AND    offerCode = '$sOfferCode'
                                           AND    userDataHistory.id IN (".join(',',$ids).")
                                           AND    otDataHistory.id != '$iId'
                                           AND    otDataHistory.dateTimeAdded >= date_add(CURRENT_DATE, INTERVAL -120 DAY)
                                           AND    ((processStatus = 'P' AND sendStatus = 'S') || processStatus IS NULL)";
                                
								
				                //echo "line ".__LINE__.": selecting from otDataHistory\n";
				                //echo "query: $sCheckDupQuery\n";
								
		               	//		$timeQStart = date("U");
                                $rCheckDupResult = dbQuery($sCheckDupQuery);
                		//		$timeQDone = date("U");
				//				$timeQDiff = $timeQDone - $timeQStart;
				               // echo "query time: $timeQDiff\n";


                                
                                if (!$rCheckDupResult) {
                                    //    echo $sCheckDupQuery.dbError();
                                }

                                // Check if Record Exists

                                if ( dbNumRows($rCheckDupResult) > 0 ) {

                                        // Record Exists: Mark as Duplicate


                                        
                                        $sUpdateQuery = "UPDATE otDataHistory
                                                 SET    processStatus = 'R',
                                                                reasonCode = 'dup',
                                                                sendStatus = 'N',
                                                                dateTimeProcessed = now()
                                                 WHERE  id = '$iId'";
                                        $rUpdateResult = dbQuery($sUpdateQuery);
                                        echo dbError();
                                        //echo "dupe!\n";
                                }

                                if ($rCheckDupResult) {
                                   dbFreeResult($rCheckDupResult);
                                }

                               // echo $i++." dup checked \n";
                        }
                }

                // End: Loops leads not processed

                // Update nibbles.vars "dedupScriptRunning" -1 to allow processing when completed.


                
                if ($rLeadResult) {
                        dbFreeResult($rLeadResult);
                }

                $sVarUpdateQuery = "UPDATE vars
                                        SET    varValue = varValue-1
                                        WHERE  system = 'cron'
                                        AND    varName = 'dedupScriptRunning'";
                $rVarUpdateResult = dbQuery($sVarUpdateQuery);
                echo dbError();
        }

}
cssLogFinish( $iScriptId );


//if (date('w') != '0' && date('w') !='6') {
	// Call customProcessing
	//include("/home/scripts/lead_processing/customProcessing.php");
	
	
	// Create Working tables
	include("/home/scripts/lead_processing/otDataUserDataWorking.php");
//}


?>

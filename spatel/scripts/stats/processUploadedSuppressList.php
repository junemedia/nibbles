<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

// Update # of records per loop - sleep for 10 secs between each loop
$iUpdateLimitSet = 10000;

$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');
$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
$sSuppressionTableName = "nibbles.advertisersSuppressionLists";
$sTempSuppressionTableName = "nibbles.tempAdvertisersSuppressionLists";

	$sOpenedDirectory = opendir("/home/supup/");
	while ($sSuppFile = readdir($sOpenedDirectory)) {
		if ($sSuppFile != '.' && $sSuppFile != '..') {
			$iTempCount = 0;
			$iInvalidEmails = 0;
			
			$sDeleteTempQuery = "TRUNCATE TABLE $sTempSuppressionTableName ";
			$rDeleteTempResult = dbQuery($sDeleteTempQuery);
			
			$sPartnerCode = substr($sSuppFile,0,strlen($sSuppFile)-4);
			chmod("/home/supup/$sSuppFile",0777);

			$rFileGuy = @fopen("/home/supup/$sSuppFile",'r');
			if ($rFileGuy) {
				$sInsertQuery = 'INSERT INTO '.$sTempSuppressionTableName.' (email) VALUES ';
				while (!feof($rFileGuy)) {
					$aEmails = array();
					for($j=0;(($j<500)&&(!feof($rFileGuy)));$j++){
						$sLine = fgets($rFileGuy, 1024);
						array_push($aEmails,'(\''.rtrim($sLine).'\')');
					}
					$sQuery = $sInsertQuery.join(',',$aEmails);
					$rTempInsertResult = dbQuery($sQuery);
				}
   				fclose($rFileGuy);
			}
				
			if ($rTempInsertResult) {
				sleep(10);
			
				$sCountQuery = "SELECT count(*) AS counts
								FROM   $sTempSuppressionTableName";
				$rCountResult = dbQuery($sCountQuery);
				echo dbError();
				while ($oCountRow = dbFetchObject($rCountResult)) {
					$iTotalAttempted = $oCountRow->counts;
					$iUpdateLimit = ceil($oCountRow->counts / $iUpdateLimitSet);
				}
				sleep(5);
				
				
				// Get 1000 rows at a time and delete bad email entry.
				for ($i=0;$i<2000000; $i=$i+1000) {
					$sSelectQuery = "SELECT * FROM  $sTempSuppressionTableName LIMIT $i,1000";
					$rSelectResult = dbQuery($sSelectQuery);
					echo dbError();
					if (dbNumRows($rSelectResult) == 0) { break; }
					
					// delete records if email is not valid
					while ($oSelectRow = dbFetchObject($rSelectResult)) {
						$id = $oSelectRow->id;
						$sEmail = trim($oSelectRow->email);
						if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sEmail)) {
							$sDeleteQuery = "DELETE FROM $sTempSuppressionTableName WHERE  id = '$id'";
							$rDeleteResult = dbQuery($sDeleteQuery);		
							echo dbError();
							$iInvalidEmails++;
							echo "Bad: ".$sEmail."\n";
						}
					}
					sleep(5);
				}
				
				// Update after deleting bad emails so that way less records to update.
				for ($i=1; $i<=$iUpdateLimit; $i++) {
					$sUpdateQuery = "UPDATE LOW_PRIORITY $sTempSuppressionTableName
									SET partnerCode = '$sPartnerCode'
									WHERE partnerCode = '' LIMIT $iUpdateLimitSet ";
					$rUpdateResult = dbQuery($sUpdateQuery);
					sleep(10);
				}

				// delete duplicates from main table
				$sDeleteQuery = "DELETE $sSuppressionTableName FROM $sSuppressionTableName, $sTempSuppressionTableName
								 WHERE  $sSuppressionTableName.partnerCode = $sTempSuppressionTableName.partnerCode
								 AND    $sSuppressionTableName.email = $sTempSuppressionTableName.email" ;
				$rDeleteResult = dbQuery($sDeleteQuery);
				if (!($rDeleteResult)) {
					echo $sDeleteQuery.dbError();
				} else {
					$iDupEmails = dbAffectedRows($rDeleteResult);
				}
				sleep(5);
				
				
				// move validated data from suppression temp table to main table
				$sInsertQuery = "INSERT IGNORE INTO $sSuppressionTableName(partnerCode, email, addDate)
								 SELECT partnerCode, email, CURRENT_DATE 
								 FROM   $sTempSuppressionTableName ";
				$rInsertResult = dbQuery($sInsertQuery);
				if ($rInsertResult) {
					// make temp table empty here after making sure that records are inserted in main table
					sleep(5);
					
					$sCountQuery = "SELECT count(*) AS counts FROM $sTempSuppressionTableName ";
					$rCountResult = dbQuery($sCountQuery);
					while ($oCountRow = dbFetchObject($rCountResult)) {
						$iEmailsInserted = $oCountRow->counts;
					}
					
					// Empty temp table.  Line 44
					$rDeleteTempResult = dbQuery($sDeleteTempQuery);
					
					// delete processed file here
					@unlink( "/home/supup/$sSuppFile" );
				}
				sleep(5);
				
				// send email
				$iFinalInserted = $iEmailsInserted - $iDupEmails;
				$sEmailContent = "Total emails attempted: $iTotalAttempted\r\n\r\n";
				$sEmailContent .= "Invalid Emails: $iInvalidEmails\r\n\r\n";
				$sEmailContent .= "Dup Emails: $iDupEmails\r\n\r\n";
				$sEmailContent .= "New Emails Inserted: $iFinalInserted";
				$iTempCount = $iTotalAttempted + $iInvalidEmails + $iDupEmails + $iFinalInserted;
				
				$sHeaders  = "MIME-Version: 1.0\r\n";
				$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
				$sHeaders .= "From:nibbles@amperemedia.com\r\n";
				$sHeaders .= "cc: ";
				
				$sEmailQuery = "SELECT * FROM   nibbles.emailRecipients WHERE  purpose = 'Suppression list counts'";
				$rEmailResult = dbQuery($sEmailQuery);
				echo dbError();
				while ($oEmailRow = dbFetchObject($rEmailResult)) {
					$sRecipients = $oEmailRow->emailRecipients;
				}
				
				if (!($sEmailTo)) {
					$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
				}
				
				$sCcTo = substr($sRecipients,strlen($sEmailTo));
				$sHeaders .= ", $sCcTo";
				$sHeaders .= "\r\n";
				$sSubject = "Suppression List Counts - $sRunDateAndTime";
				
				if ($iTempCount > 0) {
					mail($sEmailTo, $sSubject, $sEmailContent, $sHeaders);
				}
			}
		}
	}


?>

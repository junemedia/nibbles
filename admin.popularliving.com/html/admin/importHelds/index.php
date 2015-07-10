<?php

ini_set('max_execution_time', 5000000);
include("../../includes/paths.php");
session_start();
set_time_limit(10000);
$sPageTitle = "Nibbles Email Contents - List/Delete Email Content";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sImport) {
		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Import\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		// end of track users' activity in nibbles

		if ($_FILES['fImportFile']['tmp_name'] && $_FILES['fImportFile']['tmp_name']!="none") {
				$sUploadedFile = $_FILES['fImportFile']['tmp_name'];
				$sNewFile = "$sGblWebRoot/temp/heldTemp.txt";
				
				move_uploaded_file($sUploadedFile,$sNewFile);
				chmod("$sNewFile",0777);
				
				// empty the table
				$sDeleteTempHeldQuery = "DELETE FROM tempJoinEmailHeld";
				$rDeleteTempHeldResult = dbQuery($sDeleteTempHeldQuery);
	
				$sImportQuery = "LOAD DATA LOCAL INFILE '$sNewFile'
								 IGNORE INTO TABLE tempJoinEmailHeld
								 FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
								 LINES TERMINATED BY '\r\n'
								 (email, lyrisListName)";
				$rImportResult = dbQuery($sImportQuery);
				echo dbError();

				@unlink($sNewFile);

				// process from temp held table
				if ($rImportResult) {
					// get the import count
					$iCounts = 0;
					$sCountQuery = "SELECT count(*) AS counts
									FROM   tempJoinEmailHeld";
					$rCountResult = dbQuery($sCountQuery);
					
					echo dbError();
					
					while ($oCountRow = dbFetchObject($rCountResult)) {
						$iCounts = $oCountRow->counts;
					}
					$sMessage .= "<BR>$iCounts records imported.";
					
					// place listId with matching lyris name
					$sListQuery = "SELECT *
								   FROM   joinLists";
					$rListResult = dbQuery($sListQuery);
					echo dbError();
					while ($oListRow = dbFetchObject($rListResult)) {
						$iListId = $oListRow->id;
						$sLyrisName = $oListRow->lyrisName;
						$sUpdateQuery = "UPDATE tempJoinEmailHeld
										 SET    joinListId = '$iListId'
										 WHERE	lyrisListName = '$sLyrisName'";
						$rUpdateResult = dbQuery($sUpdateQuery);
						echo dbError();
					}
					
					// process records now
					$sSelectQuery = "SELECT *
									 FROM   tempJoinEmailHeld
									 WHERE  joinListId !='' && joinListId != '0' ";
					$rSelectResult = dbQuery($sSelectQuery);
					echo dbError();
					
					if ($rSelectResult) {
						while ($oSelectRow = dbFetchObject($rSelectResult)) {
							$sEmail = $oSelectRow->email;
							$iJoinListId = $oSelectRow->joinListId;

							// check if exists in active
							$sCheckQuery = "SELECT *
											FROM   joinEmailActive
											WHERE  email = \"$sEmail\"";
							$rCheckResult = dbQuery($sCheckQuery);
							echo dbError();
							while ($oCheckRow = dbFetchObject($rCheckResult)) {
								$iJoinListId = $oCheckRow->joinListId;
								$sSourceCode = $oCheckRow->sourceCode;
		
								// delete from held if already exists and then insert with new insert date
								$sDeleteHeldQuery = "DELETE FROM joinEmailHeld
													 WHERE  email = \"$sEmail\"
													 AND    joinListId = '$iJoinListId'";
								$rDeleteHeldResult = dbQuery($sDeleteHeldQuery);
								 
								$sHeldQuery = "INSERT IGNORE INTO joinEmailHeld(email, joinListId, sourceCode, dateTimeAdded)
											   VALUES(\"$sEmail\",'$iJoinListId', \"$sSourceCode\", now())";
								$rHeldResult = dbQuery($sHeldQuery);
								echo dbError();
														
								// make entry into held journal
								$sHeldJournalQuery = "INSERT INTO joinEmailHeldJournal(email, joinListId, sourceCode, dateTimeAdded)
													  VALUES(\"$sEmail\", '$iJoinListId', \"$sSourceCode\", now())";
								$rHeldJournalResult =  dbQuery ($sHeldJournalQuery);
								echo dbError();
							}
							
							// now delete from active table for that email address' records
							$sDeleteActiveQuery = "DELETE FROM joinEmailActive
												   WHERE  email = '$sEmail'";
							$rDeleteActiveResult = dbQuery($sDeleteActiveQuery);
							echo dbError();
							
							// delete from pending also to stop sending any dbm or 2nd and third confirm
							$sPendingDeleteQuery = "DELETE FROM joinEmailPending
								  					WHERE  email = '$sEmail'";
							$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
							echo dbError();
						}
					}

					// now delete from tempHeld table whatever matched joinListId because those are processed now
					$sDeleteTempHeldQuery1 = "DELETE FROM tempJoinEmailHeld
											 WHERE joinListId !='' && joinListId != '0' ";
					$rDeleteTempHeldResult1 = dbQuery($sDeleteTempHeldQuery1);
					echo dbError();
					
					// delete which has lyris name except mw, mwtext, buzz, buzztext
					$sDeleteTempHeldQuery2 = "DELETE FROM tempJoinEmailHeld
											  WHERE  lyrisListName NOT IN ('mw','mwtext','buzz','buzztext')";
					$rDeleteTempHeldResult2 = dbQuery($sDeleteTempHeldQuery2);
					echo dbError();
					
					// now process all the remaining records (there are only mw now)
					$sSelectQuery = "SELECT *
									 FROM   tempJoinEmailHeld";
					$rSelectResult = dbQuery($sSelectQuery);
					echo dbError();
				if ($rSelectResult) {
					while ($oSelectRow = dbFetchObject($rSelectResult)) {
						$sEmail = $oSelectRow->email;
						$sNonAmpereListQuery = "SELECT *
												FROM   joinListsNonAmpere";
						$rNonAmpereListResult = dbQuery($sNonAmpereListQuery);
						echo dbError();
						while ($oNonAmpereListRow = dbFetchObject($rNonAmpereListResult)) {
							$sShortName = $oNonAmpereListRow->shortName;
							$sInsertQuery = "INSERT INTO myfree.mw(email, action, list)
											 VALUES(\"$sEmail\", 'd', '$sShortName')";
							$rInsertResult = dbQuery($sInsertQuery);
							echo dbError();		
			
							$sMwInsertQuery = "INSERT IGNORE INTO myfree.mwfeedback(email, listid, reason, dateTimeAdded)
										   VALUES(\"$sEmail\", '$sShortName', 'bounces', now())";
							$rMwInsertResult = dbQuery($sMwInsertQuery);
							echo dbError();	
								
							$sMwInsertQuery2 = "INSERT IGNORE INTO myfree.mwfeedbackArchieve(email, listid, reason, dateTimeAdded)
										   VALUES(\"$sEmail\", '$sShortName', 'bounces', now())";
							$rMwInsertResult2 = dbQuery($sMwInsertQuery2);
							echo dbError();
						}
					}
				}
			}

			$sDeleteTempHeldQuery = "DELETE FROM tempJoinEmailHeld";
			$rDeleteTempHeldResult = dbQuery($sDeleteTempHeldQuery);
		}
	}
	include("../../includes/adminHeader.php");
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>File To Import</td><td><input type=file name='fImportFile'></td></tr>
<tr><td></td><td><input type=submit name=sImport value='Import Helds'></td>
</tr>

<?php echo $sEmailContentsList;?>
<tr><td align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>


<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
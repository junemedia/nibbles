<?php

include("../../includes/paths.php");

session_start();

set_time_limit(5000);
$sPageTitle = "Nibbles Email Contents - List/Delete Email Content";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($sImport) {
		if ($_FILES['fImportFile']['tmp_name'] && $_FILES['fImportFile']['tmp_name']!="none") {
			
			$sUploadedFile = $_FILES['fImportFile']['tmp_name'];
			$sNewFile = "$sGblWebRoot/temp/heldTemp.txt";
			
			move_uploaded_file($sUploadedFile,$sNewFile);
			
			chmod("$sNewFile",0777);
			
			// empty the table
			$sDeleteTempHeldQuery = "DELETE FROM tempJoinEmailHeld";
			$rDeleteTempHeldResult = dbQuery($sDeleteTempHeldQuery);
				//$sMessage .= "<BR>deleted from temp join which matched listid";
				
			$sImportQuery = "LOAD DATA INFILE '$sNewFile'
							 IGNORE INTO TABLE tempJoinEmailHeld
							 FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
							 LINES TERMINATED BY '\r\n'
							 (email, lyrisListName)";
			$rImportResult = dbQuery($sImportQuery);
			echo dbError();
			//$sMessage .= "<BR>File Imported";
			unlink($sNewFile);
			
			// process from temp held table
			if ($rImportResult) {
				// get the import count
				$iCounts = 0;
				$sCountQuery = "SELECT count(*) AS counts
								FROM   tempJoinEmailHeld";
				$rCountResult = dbQuery($sCountQuery);
				
				echo dbError();
				
				//$sMessage .= "<BR>got the import count ";
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
					//$sMessage .= "<BR>Placed listId with matching lyris name";
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
						
						// insert into inactive
						//$sInactiveQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
						//				   VALUES(\"$sEmail\",'$iJoinListId', \"$sSourceCode\", now())";
						//$rInactiveResult = dbQuery($sInactiveQuery);
						//$sMessage .= "<BR>inserted into inactive";
						//echo dbError();
						
						// delete from held if already exists and then insert with new insert date
						
						$sDeleteHeldQuery = "DELETE FROM joinEmailHeld
											 WHERE  email = \"$sEmail\"
											 AND    joinListId = '$iJoinListId'";
						$rDeleteHeldResult = dbQuery($sDeleteHeldQuery);
						 
						$sHeldQuery = "INSERT IGNORE INTO joinEmailHeld(email, joinListId, sourceCode, dateTimeAdded)
									   VALUES(\"$sEmail\",'$iJoinListId', \"$sSourceCode\", now())";
						$rHeldResult = dbQuery($sHeldQuery);
						//$sMessage .= "<BR>inserted into held";
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
					//$sMessage .= "<BR>deleted from active";
					echo dbError();
					
					// delete from pending also to stop sending any dbm or 2nd and third confirm
					$sPendingDeleteQuery = "DELETE FROM joinEmailPending
						  					WHERE  email = '$sEmail'";
	
					$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
					echo dbError();
					
				}

				// now delete from tempHeld table whatever matched joinListId because those are processed now

				$sDeleteTempHeldQuery1 = "DELETE FROM tempJoinEmailHeld
										 WHERE joinListId !='' && joinListId != '0' ";
				$rDeleteTempHeldResult1 = dbQuery($sDeleteTempHeldQuery1);
				//$sMessage .= "<BR>deleted from temp join which matched listid";
				echo dbError();
				// delete which has lyris name except mw, mwtext, buzz, buzztext
				
				$sDeleteTempHeldQuery2 = "DELETE FROM tempJoinEmailHeld
										  WHERE  lyrisListName NOT IN ('mw','mwtext','buzz','buzztext')";
				$rDeleteTempHeldResult2 = dbQuery($sDeleteTempHeldQuery2);
				//$sMessage .= "<BR>deleted from temp join all except mw records";
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
		
							$sMwInsertQuery = "INSERT IGNORE INTO mwfeedback(email, listid, reason)
										   VALUES(\"$sEmail\", '$sShortName', 'bounces')";
							$rMwInsertResult = dbQuery($sMwInsertQuery);
							//$sMessage .= "<BR>inserted into mwfeedback";
							echo dbError();	
						}		
									
					}
					
				}
					// now delete mw records also from temp held table
					
					$sDeleteTempHeldQuery = "DELETE FROM tempJoinEmailHeld";
					$rDeleteTempHeldResult = dbQuery($sDeleteTempHeldQuery);
					//$sMessage .= "<BR>deleted mw records from temp join";
					echo dbError();
				
			}
		}
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
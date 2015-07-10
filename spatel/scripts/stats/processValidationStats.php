<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sGetRecords = "SELECT *
			FROM  nibbles.validateAddressAoStats
			WHERE  dateTimeCheck < CURRENT_DATE limit 500";

$bMoreRecords = TRUE;

while( $bMoreRecords ) {
	//echo "inside big while\n";
	$rGetRecords = dbQuery( $sGetRecords );
	if( dbNumRows( $rGetRecords ) == 0 ) {
		$bMoreRecords = FALSE;
	}
	while( $oRowRecord = dbFetchObject( $rGetRecords ) ) {
		//echo "inside 2nd while - insert 36\n";
		$sInsertRecord = "INSERT INTO validateAddressAoStatsHistory (dateTimeCheck,address,address2,city,state,zip,response,sourceCode) VALUES
						( \"".addslashes($oRowRecord->dateTimeCheck)."\", \"".addslashes($oRowRecord->address)."\", \"".addslashes($oRowRecord->address2)."\",
						\"".addslashes($oRowRecord->city)."\", \"".addslashes($oRowRecord->state)."\", \"".addslashes($oRowRecord->zip)."\", \"".addslashes($oRowRecord->response)."\",
						\"".addslashes($oRowRecord->sourceCode)."\" )";
		//echo $sInsertRecord."<br><br>";
		
		$iAddSuccess=0;
		$iAddFailure=0;
		$iAddUpdate=0;
		if( $oRowRecord->response == "Success" ) {
			$iAddSuccess = 1;
		}
		if( substr( $oRowRecord->response, 0, 7 ) == "Failure" ) {
			$iAddFailure = 1;
		}
		if( substr( $oRowRecord->response, 0, 6 ) == "update" ) {
			$iAddUpdate = 1;
		}
		
		if( $iAddFailure == 1 ) {
			$errorLine = explode( "|", $oRowRecord->response );
			$errorCode = $errorLine[1];
			$sUpdateSpecificError = ", error".$errorCode."=error".$errorCode."+1 ";
		} else {
			$sUpdateSpecificError = "";
		}

		
		$sGetRows = "SELECT * FROM validateAddressAoStatsHistorySum WHERE
					dateChecked = (CURRENT_DATE - interval '1' day)
					AND sourceCode = '$oRowRecord->sourceCode'";
		
		$rGetRowsResult = dbQuery( $sGetRows );
		if( dbNumRows( $rGetRowsResult ) == 0 ) {
			//echo "insert blank row\n";
			$sInsertBlankRow = "INSERT INTO validateAddressAoStatsHistorySum (dateChecked,successes,updates,failures,errorR,errorU,errorAM,errorX,errorT,errorZ,sourceCode)
						VALUES
						( CURRENT_DATE-interval '1' day, 0, 0, 0, 0, 0, 0, 0, 0, 0, '$oRowRecord->sourceCode' )";
			$rInsertBlankRowResults = dbQuery( $sInsertBlankRow );
			echo dbError();
		//	echo $sInsertBlankRow."<br><br>";
			}
			//echo "started update\n";
			$sUpdateQuery = "UPDATE validateAddressAoStatsHistorySum SET 
						successes=successes+$iAddSuccess,
						failures=failures+$iAddFailure,
						updates=updates+$iAddUpdate $sUpdateSpecificError
						WHERE dateChecked = '".substr($oRowRecord->dateTimeCheck, 0, 10)." 00:00:00'
						AND sourceCode = '".$oRowRecord->sourceCode."'";
			$rUpdateQuery = dbQuery( $sUpdateQuery );
		//	echo $sUpdateQuery;
			echo dbError();
		//echo $sUpdateQuery."<br><br>";
		//exit();
		
		$rInsertRecord = dbQuery( $sInsertRecord );
		echo dbError();
		if( dbAffectedRows() > 0 ) {
			//echo "delete 92\n";
			$sDeleteRecord = "DELETE FROM validateAddressAoStats WHERE id=$oRowRecord->id LIMIT 1";
			$rDeleteRecord = dbQuery( $sDeleteRecord );
			echo dbError();
		}
	}
	//exit();
}

$sDeleteOldData = "DELETE FROM validateAddressAoStatsHistory WHERE
					dateTimeCheck < (CURRENT_DATE - interval '7' day)";
$rDeleteOldData = dbQuery( $sDeleteOldData );
echo dbError();

?>


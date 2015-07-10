<?php


include("/home/sites/admin.popularliving.com/html/includes/paths.php");

$iSuccessful=1;

$sQuery = "SELECT date_format(errorDateTime,'%Y-%m-%d') as errorDate, valueInvalidated, function, sourceCode, pageId, count(id) AS counts
		   FROM   errorLog
		   WHERE  errorDateTime >= date_add(CURRENT_DATE, INTERVAL -1 DAY) 
		   GROUP BY errorDate, valueInvalidated, function, sourceCode, pageId";
$rResult = dbQuery($sQuery);
if ($rResult) {
	while ($oRow = dbFetchObject($rResult)) {

		$sErrorDate = $oRow->errorDate;
		$sValueInvalidated = addslashes($oRow->valueInvalidated);
		$sFunction = $oRow->function;
		$sSourceCode = $oRow->sourceCode;
		$iPageId = $oRow->pageId;
		$iCounts = $oRow->counts;

		$sCheckQuery = "SELECT *
					FROM   errorStats
					WHERE  errorDate = '$sErrorDate'
					AND	   valueInvalidated = \"$sValueInvalidated\"
					AND	   function = '$sFunction'
					AND	   sourceCode = \"$sSourceCode\"
					AND	   pageId = '$iPageId'";
		$rCheckResult = dbQuery($sCheckQuery);
		echo dbError();
		if (dbNumRows($rCheckResult) == 0) {
			$sInsertQuery = "INSERT INTO errorStats(errorDate, valueInvalidated, function, sourceCode, pageId, counts)
						 VALUES('$sErrorDate', \"$sValueInvalidated\", \"$sFunction\", \"$sSourceCode\", \"$iPageId\", '$iCounts')";
			$rInsertResult = dbQuery($sInsertQuery);
			if (!($rInsertResult)) {
				echo $sInsertQuery. dbError();
			}

		} else {
			//$
			$sUpdateQuery = "UPDATE errorStats
						 SET    counts = '$iCounts'
						 WHERE  errorDate = '$sErrorDate'
						 AND    valueInvalidated = \"$sValueInvalidated\"
						 AND    function = '$sFunction'
						 AND    sourceCode = \"$sSourceCode\"
						 AND    pageId = '$iPageId'";
			$rUpdateResult = dbQuery($sUpdateQuery);
			if (!($rUpdateResult)) {
				echo $sUpdateQuery. dbError();
				$iSuccessful = 0;
			}
		}

	}
	// TODO: Delete all of the errorLog Data before today.
	if( $iSuccessful = 1 ) {
		$sDeleteQuery = "DELETE
		   FROM   errorLog
		   WHERE  errorDateTime < date_add(CURRENT_DATE, INTERVAL -1 DAY)";
		dbQuery( $sDeleteQuery );
		echo dbError();
	} else {
		mail( "spatel@amperemedia.com", "'errorStats.php' - ERRORS!", "errorStats.php did not complete as planned, and the data was not removed from the previous day." );
	}
} else {
	mail( "spatel@amperemedia.com", "'errorStats.php' - ERRORS!", "errorStats.php did not complete as planned, and the data was not removed from the previous day." );
}

echo dbError();

?>

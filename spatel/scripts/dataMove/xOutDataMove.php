<?php
/*  Moves data from 'xOutData' to 'xOutDataHistory' table for previous day.  */
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sDelete = "DELETE FROM xOutData WHERE offerCode='Array'";
$rDeleteResult = dbQuery($sDelete);
echo dbError();

$sSelectQuery = "SELECT distinct offerCode FROM xOutData";
$rSelectResult = dbQuery($sSelectQuery);
while ($sTempRow = dbFetchObject($rSelectResult)) {
	if (strlen($sTempRow->offerCode)==1) {
		$sDeleteQuery = "DELETE FROM xOutData WHERE offerCode='$sTempRow->offerCode'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}

// Start: Loop xOutData entries
$sSelectQuery = "SELECT *
                FROM   xOutData
                WHERE  dateTimeAdded < CURRENT_DATE";
$rSelectQueryResult = dbQuery($sSelectQuery);
echo dbError();
while ($oRow = dbFetchObject($rSelectQueryResult)) {
	// Insert into History Table
	$sInsertQuery = "INSERT INTO xOutDataHistory(offerCode,email,dateTimeAdded,sessionId,sourceCode)
                      VALUES(\"$oRow->offerCode\", \"$oRow->email\", \"$oRow->dateTimeAdded\", \"$oRow->sessionId\", \"$oRow->sourceCode\")";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();

	// If Insert Successful, Delete From Current table
	if ($rInsertResult) {
		$sDeleteQuery = "DELETE FROM xOutData WHERE  id = '$oRow->id'";
		$rDeleteResult = dbQuery($sDeleteQuery);
		echo dbError();
	}
}

$sDelete = "DELETE FROM xOutDataHistory WHERE offerCode='Array'";
$rDeleteResult = dbQuery($sDelete);
echo dbError();


?>

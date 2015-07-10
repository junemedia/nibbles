<?php

// Moves data from 'foreignIpLog' to 'foreignIpLogHistory' table for previous day.
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


// Start: Loop foreignIpLog entries
$sSelectQuery = "SELECT * FROM foreignIpLog WHERE dateTimeLogged < CURRENT_DATE";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {

	// Insert into History Table
	$sInsertQuery = "INSERT INTO foreignIpLogHistory
				(dateTimeLogged, remoteIp, sourceCode, subSourceCode, block, redirectUrl, country)
				VALUES(\"$oRow->dateTimeLogged\", \"$oRow->remoteIp\", \"$oRow->sourceCode\", \"$oRow->subSourceCode\", 
				\"$oRow->block\", \"$oRow->redirectUrl\", \"$oRow->country\")";
	$rInsertResult = dbQuery($sInsertQuery);

	// If Insert Successful, Delete From Current table
	if ($rInsertResult) {
		$sDeleteQuery = "DELETE FROM foreignIpLog WHERE id = '$oRow->id'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}

?>


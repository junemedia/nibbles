<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sQuery = "SELECT count(*) AS count,remoteIp,sourceCode,sessionId,substring(dateTime,1,10) AS clickDate
			FROM nibbles_reporting.nibbles2Clicks
			WHERE  dateTime < CURRENT_DATE
			GROUP BY sessionId";
$rResult = dbQuery($sQuery);
echo dbError();
while ($oRow = dbFetchObject($rResult)) {
	$sInsertQuery = "INSERT INTO nibbles_reporting.nibbles2ClicksHistory
					(clickDate,count,sourceCode,remoteIp,sessionId) VALUES
					('$oRow->clickDate','$oRow->count',\"$oRow->sourceCode\",\"$oRow->remoteIp\",
					\"$oRow->sessionId\")";
	$rInsertResult = dbQuery($sInsertQuery);
	
	if ($rInsertResult) {
		$sDelete = "DELETE FROM nibbles_reporting.nibbles2Clicks
					WHERE sessionId = \"$oRow->sessionId\"
					AND dateTime < CURRENT_DATE";
		$rDeleteResult = dbQuery($sDelete);
	}
}


?>

<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


$sQuery = "INSERT IGNORE INTO nibbles_datafeed.dataFeedLogHistory
			SELECT * FROM nibbles_datafeed.dataFeedLog
			WHERE  dateTime < CURRENT_DATE";
$rResult = dbQuery($sQuery);
echo dbError();
		
if ($rResult) {
	$sDelete = "DELETE FROM nibbles_datafeed.dataFeedLog
				WHERE  dateTime < CURRENT_DATE";
	$asdf = dbQuery($sDelete);
	echo dbError();
}

?>

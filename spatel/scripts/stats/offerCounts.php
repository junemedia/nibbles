<?php
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sYesterday = strftime("%Y-%m-%d", strtotime("-1 day"));

$sHistoryQuery = "SELECT offerCode, count(offerCode) AS count
			FROM nibbles.otDataHistory
			WHERE dateTimeAdded >= '$sYesterday 00:00:00'
			AND processStatus != 'R'
			GROUP BY offerCode";
$rHistoryResult = dbQuery($sHistoryQuery);
echo dbError();
$iCount = 0;
while ($oCountRow = dbFetchObject($rHistoryResult)) {
	$sInsert = "INSERT INTO nibbles.offerCounts (offerCode, offerCount, dateAdded)
				VALUES ('$oCountRow->offerCode','$oCountRow->count','$sYesterday')";
	$rResult = dbQuery($sInsert);
	$iCount++;
}


if ($iCount == 0) {
	mail('it@amperemedia.com',"ReRun: ".__FILE__,"Rerun this script on web1.");
}



?>


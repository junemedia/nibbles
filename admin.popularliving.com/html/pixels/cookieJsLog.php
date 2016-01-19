<?php

include("../includes/paths.php");

$sRemoteIp = trim($_SERVER['REMOTE_ADDR']);

$sSourceCode = (!(ctype_alnum(trim($_GET['src']))) ? '' : trim($_GET['src']));

if (trim($_GET['nibbles']) == '1') {
	$sNibbles = 'Y';
} else {
	$sNibbles = 'N';
}

$sCurrentDateTime = date('Y-m-d H:i:s');
$sInsertQuery = "INSERT IGNORE INTO noCookieJavaScriptLog (sourceCode,dateTimeAdded,remoteIp,legacySystem)
			VALUES (\"$sSourceCode\",\"$sCurrentDateTime\",\"$sRemoteIp\",\"$sNibbles\")";
$rResult = dbQuery($sInsertQuery);


?>


<?php

$src = trim($_GET['src']);

if (!(ctype_alnum($src))) {
	$src = '';
}

include("../../../includes/paths.php");

$iData = '';
$sGetData = "SELECT id FROM links WHERE sourceCode = '$src' LIMIT 1";
$rResult = dbQuery($sGetData);
while ($oRow = dbFetchObject($rResult)) {
	$iData = $oRow->id;
}

echo $iData;

?>

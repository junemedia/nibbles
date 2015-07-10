<?php

$iId = trim($_GET['iId']);
$sGet = trim($_GET['get']);

if (!(ctype_digit($iId))) { $iId = 0; }

include("../../../includes/paths.php");

$sData = '';
$sGetData1 = "SELECT * FROM customDataValidation.TMOT_UOPC WHERE accept_zip_code = '$iId' LIMIT 1";
$rResult = dbQuery($sGetData1);
while ($oRow = dbFetchObject($rResult)) {
	if ($sGet == 'name') {
		$sData = $oRow->campus_name;
	} else {
		$sData = $oRow->campus_id;
	}
}

echo $sData;

?>

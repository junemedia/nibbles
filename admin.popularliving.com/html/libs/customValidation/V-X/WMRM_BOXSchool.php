<?php

$state = trim($_GET['state']);
$city = trim(urlEncode($_GET['city']));


include("../../../includes/paths.php");


$iDatab = '';
$sGetData = "select schoolId,schoolName from customDataValidation.WMRM_BOX where city = '$city' and stateId = '$state'";
$rResult = dbQuery($sGetData);
while ($oRow = dbFetchObject($rResult)) {
	$iDatab = $oRow->schoolName;
	$iDatac = $oRow->schoolId;
	$dropDown2 .= "<option value=$iDatac>$iDatab</option>";
}

echo $dropDown2;
?>



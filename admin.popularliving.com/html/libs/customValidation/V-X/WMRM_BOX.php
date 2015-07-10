<?php

$state = trim($_GET['state']);
$city = trim($_GET['city']);


include("../../../includes/paths.php");

$iData = '';
$sGetData = "select city from customDataValidation.WMRM_BOX where state = '$state' group by city";
$rResult = dbQuery($sGetData);
while ($oRow = dbFetchObject($rResult)) {
	$iData = $oRow->city;
	$dropDown .= "<option value=$oRow->city>$oRow->city</option>";
}


$iDatab = '';
$sGetData = "select schoolId,schoolName from customDataValidation.WMRM_BOX where city = '$city' and state = '$state'";
$rResult = dbQuery($sGetData);
while ($oRow = dbFetchObject($rResult)) {
	$iDatab = $oRow->schoolName;
	$iDatac = $oRow->schoolId;
	$dropDown2 .= "<option value=$oRow->schoolId>$oRow->schoolName</option>";
}

echo $dropDown;
echo $dropDown2;

?>



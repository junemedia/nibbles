<?php

$state = trim($_GET['state']);
$city = trim($_GET['city']);


include("../../../includes/paths.php");

$iData = '';
$sGetData = "select city from customDataValidation.WMRM_BOX where stateId = '$state' group by city";
$rResult = dbQuery($sGetData);
while ($oRow = dbFetchObject($rResult)) {
	$iData = $oRow->city;
	//$dropDown .= "<option value=$oRow->city>$oRow->city</option>";
	$dropDown .= "<option value='$iData'>$iData</option>";
}



echo $dropDown;


?>



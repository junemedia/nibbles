<?php


include("/home/sites/www_popularliving_com/html/includes/paths.php");
$sVal = trim($_GET['areaExchange']);

$sQuery = "SELECT * FROM customDataValidation.phoneAreaCodeExchangeOptions WHERE offerCode='TWEB_VOICE' AND value = '$sVal' LIMIT 1";
$rResult = dbQuery($sQuery);
if (dbNumRows($rResult) > 0 ) {
	echo "accept";
} else {
	echo "reject";
}


?>

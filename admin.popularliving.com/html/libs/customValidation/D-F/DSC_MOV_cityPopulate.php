<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return a string (comma separated values)

include("../../../includes/paths.php");


//$sCampusName = (!ereg( "^[a-zA-Z0-9 _-']{1,}$", trim($_GET['sCampusName'])) ? '' : trim($_GET['sCampusName']));


if (isset($_GET['selectedState']) && strlen($_GET['selectedState']) == 2) {
	$sState = trim($_GET['selectedState']);
	$sState = (!ereg("^[A-Z]{2,2}$", strtoupper($sState)) ? '' : strtoupper($sState));
	if (is_array($HTTP_GET_VARS)) {
		reset($HTTP_GET_VARS);
	}

	$sCDVTurnedOn = "SELECT city
					FROM nibbles_reference.DSC_MOV_cities
					WHERE State = \"$sState\" ";

	$rCDVTurnedOn = dbQuery($sCDVTurnedOn);
	if(dbNumRows($rCDVTurnedOn) > 0) {
		while($oCDValidation = dbFetchObject($rCDVTurnedOn)) {
			$sReturnString .= '|'.$oCDValidation->city;
		}
		$sReturnString = substr($sReturnString,1);
		echo $sReturnString;
	} else {
		echo '0';
	}
} else { //if selectedState is NOT set and is NOT 2 characters long
	echo "0";
}
?>


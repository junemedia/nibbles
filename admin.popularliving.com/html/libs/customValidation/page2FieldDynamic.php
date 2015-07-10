<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return a string (pipe separated values)

include("../../includes/paths.php");


$sFieldName = (!ereg( "^[a-zA-Z0-9 _-]{1,}$", trim($_GET['sFieldName'])) ? '' : trim($_GET['sFieldName']));
$sFieldGroup = (!ereg( "^[a-zA-Z0-9 _-]{1,}$", trim($_GET['sFieldGroup'])) ? '' : trim($_GET['sFieldGroup']));
$sOfferCode = (!ereg( "^[a-zA-Z0-9 _-]{1,}$", trim($_GET['sOfferCode'])) ? '' : trim($_GET['sOfferCode']));
$table = '';

if (is_array($HTTP_GET_VARS)) {
	reset($HTTP_GET_VARS);
}

$sCDVTurnedOn = "SELECT fieldValue 
				FROM customDataValidation.dynamicFieldOptions
				WHERE offerCode = \"$sOfferCode\" 
				AND fieldGroup = \"$sFieldGroup\" 
				AND fieldName= \"$sFieldName\"";
$rCDVTurnedOn = dbQuery($sCDVTurnedOn);
if(dbNumRows($rCDVTurnedOn) > 0) {
	while($oCDValidation = dbFetchObject($rCDVTurnedOn)) {
		$sReturnString .= '|'.$oCDValidation->fieldValue;
	}
	$sReturnString = substr($sReturnString,1);
	echo $sReturnString;
} else {
	echo '0';
}

?>

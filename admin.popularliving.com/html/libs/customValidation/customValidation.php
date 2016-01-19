<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return '1'

include("../../includes/paths.php");

mysql_selectdb('customDataValidation');

$field = (!ctype_alpha(trim($_GET['field'])) ? '' : trim($_GET['field'])); 
$value = (!ereg( "^[a-zA-Z0-9 _-]{1,}$", trim($_GET['value'])) ? '' : trim($_GET['value']));
$sOfferCode = (!ereg( "^[a-zA-Z0-9 _-]{1,}$", trim($_GET['offerCode'])) ? '' : trim($_GET['offerCode']));
$table = '';

if (is_array($HTTP_GET_VARS)) {
	reset($HTTP_GET_VARS);
}

/* Script called via AJAX, will return either a 1 (pass) or 0(fail) */

if (($field != '')&&($value != '')&&($sOfferCode != '')) {
//our $field is going to be which field we should try to validate
switch($field){
	case 'zip': 
		$table = 'zipOptions';
		break;
	case 'gender':
		$table = 'genderOptions';
		break;
	case 'phoneAreaCode':
		$table = 'phoneAreaCode';
		break;
	case 'phoneExchange':
		$table = 'phoneExchange';
		break;
	case 'phoneAreaCodeExchange':
		$table = 'phoneAreaCodeExchangeOptions';
		break;
	case 'state':
		$table = 'stateOptions';
		break;
	case 'birthYear':
		$table = 'birthYearOptions';
		break;
	default:
		echo '0';
		exit;
		break;
}

$sCDVTurnedOn = "SELECT count(*) as count FROM $table WHERE offerCode = '$sOfferCode'";
$rCDVTurnedOn = dbQuery($sCDVTurnedOn);
$oCDVTurnedOn = dbFetchObject($rCDVTurnedOn);
if($oCDVTurnedOn->count > 0){
	$sCDValidationSQL = "SELECT count(*) as count FROM $table WHERE offerCode = '$sOfferCode' and value = '$value'";
	$rCDValidation = dbQuery($sCDValidationSQL);
	$oCDValidation = dbFetchObject($rCDValidation);
	if($oCDValidation->count == 0){
		echo '0';
		exit;
	} else {
		echo '1';
		exit;
	}
} else {
	echo '0';
	exit;
}
}
?>

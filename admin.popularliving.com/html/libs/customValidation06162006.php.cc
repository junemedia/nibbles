<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return '1'

include("/home/sites/www_popularliving_com/html/includes/paths.php");

mysql_selectdb('customDataValidation');

$field = (!ctype_alpha(trim($_GET['field'])) ? '' : trim($_GET['field'])); 
$value = trim($_GET['value']);
$sOfferCode = trim($_GET['offerCode']);
$table = '';
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
		echo '1';
		exit;
		break;
}


//phone area code
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
	echo '1';
	exit;
}

?>
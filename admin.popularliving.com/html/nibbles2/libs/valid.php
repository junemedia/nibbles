<?php
//ajaxified validation script

//take an argument from the query string
//use the arg as the value in a switch for what we should be validating
//if we fail any of the validations, return '0', else return '1'


include_once("../../includes/paths.php");
include_once("function.php");

$field = $_GET['field'];
$value = $_GET['value'];
$src = $_GET['src'];
//our $field is going to be which field we should try to validate
switch($field){
	case 'zip2State':
		$sQuery = "select * from zipStateCity WHERE zip='$value' LIMIT 1";
		$rResult = mysql_query($sQuery);
		if (($oStateRow = dbFetchObject($rResult)) && ($oStateRow->state !='')){
			echo $oStateRow->state;
		} else {
			echo '0';
			exit;
		}
		break;
	case 'email': 
		echo (validateEmail($value) ? '1' : '0');
		break;
	case 'first':
	case 'last':
		echo (validateName($value) ? '1' : '0');
		break;
	case 'phone':
		list($area, $exch, $last4) = explode('-', $value);
		echo (validatePhone($area, $exch, $last4, '','',$src) ? '1' : '0');
		break;
	case 'phoneDistance':
		list($sZip, $sPhone) = explode('@', $value);
		echo (getDistance($sZip, $sPhone, $src) ? '1' : '0');
		break;
	case 'address':
		list($addr, $city, $zip) = explode('-', $value);
		$sQuery = "select * from zipStateCity WHERE zip='$zip' LIMIT 1";
		$rResult = mysql_query($sQuery);
		if (($oStateRow = dbFetchObject($rResult)) && ($oStateRow->state !='')){
			$state = $oStateRow->state;
		} else {
			echo '0';
			exit;
		}
		echo validateAddress($addr,$city, $state, $zip, $sGblRoot);
		break;
	case 'dob':
		list($mm, $dd, $yyyy) = explode('/', $value);
		//echo (validateBirthDate($yyyy, $mm, $dd) ? '1' : '0');//Turned off age check, because SC doesn't do it. :( Bill-2006-05-31
		echo ((($mm) && ($dd) && ($yyyy)) ? '1' : '0');
		break;
	default:
		echo '1';
		break;
}

?>
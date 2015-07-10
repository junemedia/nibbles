<?php

include_once("../../includes/paths.php");


$aValidFields = array('sFirst','sLast','sEmail','sAddress','sCity','sState','sZip','sPhone_areaCode','sPhone_exchange','sPhone_number','iBirthYear','iBirthMonth','iBirthDay','sGender','sPhoneDistance');

$field = (!in_array(trim($_GET['field']), $aValidFields) ? '' : trim($_GET['field'])); 
$value = (trim($_GET['value']) ? trim($_GET['value']) : '');
$sSourceCode = (!ctype_alnum(trim($_GET['src'])) ? '' : trim($_GET['src']));

if($field == ''){
	echo '';
	exit;
}


//select for the source code from the error messages table, and populate
$sql = "SELECT ".$field."_empty as empty, ".$field."_error as error FROM linksErrorMessages WHERE sourceCode = '$sSourceCode'";
$res = dbQuery($sql);
if(dbError()){
	echo __line__.'';
	exit;
}

$o = dbFetchObject($res);
//mail('bbevis@amperemedia.com', __line__.': error message sql',$sql."\n\n\n".print_r($o,true));
$custom = array('empty'=>$o->empty, 'error'=>$o->error);

//next, set up the associative array of the default messages
$defaults = array('sFirst' => array('empty'=>"\n* Please enter your First Name.", 'error'=>"\n* Please enter your First Name."),
		'sLast' => array('empty'=>"\n* Please enter your Last Name.", 'error'=>"\n* Please enter your Last Name."),
		'sEmail' => array('empty'=>"\n* Please enter your Email.", 'error'=> "\n* Please enter a valid Email address."),
		'sAddress' => array('empty'=>"\n* Please enter your Address.", 'error'=>"\n* Please enter your a valid Address."),
		'sCity' => array('empty'=>"\n* Please enter your city.", 'error'=>"\n* Please enter your city."),
		'sState' => array('empty'=>"\n* Please enter your state.", 'error'=>"\n* Please enter your state."),
		'sZip' => array('empty'=>"\n* Please enter your zip code.", 'error'=>"\n* Please enter a valid zip code."),
		'sPhone_areaCode' => array('empty'=>"\n* Please enter area code.", 'error'=>"\n* Please enter area code."),
		'sPhone_exchange' => array('empty'=>"\n* Please enter your exchange.", 'error'=>"\n* Please enter your exchange."),
		'sPhone_number' => array('empty'=>"\n* Please enter your number.", 'error'=>"\n* Please enter your number."),
		'iBirthYear' => array('empty'=>"\n* Please select birth year.", 'error'=>"\n* Please select birth year."),
		'iBirthMonth' => array('empty'=>"\n* Please select birth month.", 'error'=>"\n* Please select birth month."),
		'iBirthDay' => array('empty'=>"\n* Please select birth day.", 'error'=>"\n* Please select birth day."),
		'sGender' => array('empty'=>"\n* Please select your gender.", 'error'=>"\n* Please select your gender."),
		'sPhoneDistance' => array('error'=>"\n* Please select your gender.")
);
  


//if value == ''
	//if there is an empty message, return that, else the empty default
//else
	//if there is error message, reutrn that, else the error default

if($value == ''){
	if($custom['empty']){
		echo "\n* ".$custom['empty'];
	} else {
		echo $defaults[$field]['empty'];
	}
} else {
	if($custom['error']){
		echo "\n* ".$custom['error'];
	} else {
		echo $defaults[$field]['error'];
	}
}

?>
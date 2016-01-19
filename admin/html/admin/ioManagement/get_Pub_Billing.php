<?php

include_once("../../includes/paths.php");
include('../../nibbles2/session_handlers.php');


$sQuery = '';
$sAction = trim($_GET['action']) ;
$sPartnerId = trim($_GET['pid']) ;

if(!$sPartnerId){
	echo 'error' ;
	exit() ;
}

$echo_this = '';
$ret = "
" ;

if($sAction == '1'){ //get info for publisher
	$sQuery = "select contact, email, phoneNo from nibbles.partnerContacts where partnerId = '$sPartnerId' limit 1" ;
	$sRes = dbQuery($sQuery);
	while ($oRow = dbFetchObject($sRes)) {
		$echo_this = $oRow->contact . $ret . $oRow->email . $ret . $oRow->phoneNo ;
	}
}

if($sAction == '2'){ //get info for billing
	$sQuery = "select companyName, paymentMethod, paymentTerms, faxNo, taxId from nibbles.partnerCompanies where id = '$sPartnerId' limit 1" ;
	$sRes = dbQuery($sQuery);
	while ($oRow = dbFetchObject($sRes)) {
		if($oRow->companyName) { $echo_this .= $oRow->companyName . $ret ;}
		if($oRow->paymentMethod) { $echo_this .= $oRow->paymentMethod . $ret ;}
		if($oRow->paymentTerms) { $echo_this .= $oRow->paymentTerms . $ret ;}
		if($oRow->taxId) { $echo_this .= $oRow->taxId . $ret ;}
		if($oRow->faxNo) { $echo_this .= 'fax # : '.$oRow->faxNo ;}
	}
}


if($sAction == '3'){ //get info for publisher
	$sQuery = "select contact, email from nibbles.partnerContacts where partnerId = '$sPartnerId' limit 1" ;
	$sRes = dbQuery($sQuery);
	while ($oRow = dbFetchObject($sRes)) {
		$echo_this = $oRow->contact.' - '.$oRow->email ;
	}
}


echo $echo_this ;



?>
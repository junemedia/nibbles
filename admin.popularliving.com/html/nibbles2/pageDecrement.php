<?php

$PHPSESSID = $_GET['PHPSESSID'];
if(!($PHPSESSID))
	exit();
	
session_start();


$iPos = $_SESSION['iSesCurrentPositionInFlow'] ;
$_SESSION['iSesCurrentPositionInFlow']-- ;

if($_SESSION['sSesShowEmailCapturePage'] == 'Y'){
	switch($_SESSION['aSesTemplateId'][$iPos]){
		case 14 : 
			break;
		case 16 :
			$_SESSION['iSesRPTotalOfferShown'] -= $_SESSION['aSesMaxOffers'][$iPos] ;
			if($_SESSION['iSesRPTotalOfferShown'] < 0){
				$_SESSION['iSesRPTotalOfferShown'] = 0;
			}
			break;
		case 15 :
			$_SESSION['iSesFRPTotalOfferShown'] -= $_SESSION['aSesMaxOffers'][$iPos] ;
			if($_SESSION['iSesFRPTotalOfferShown'] < 0){
				$_SESSION['iSesFRPTotalOfferShown'] = 0;
			}
			break;
		case 13 :
			$_SESSION['iSesSPNSTotalOfferShown'] -= $_SESSION['aSesMaxOffers'][$iPos] ;
			if($_SESSION['iSesSPNSTotalOfferShown'] < 0){
				$_SESSION['iSesSPNSTotalOfferShown'] = 0;
			}
			break;
		case 24 :
			$_SESSION['iSesSPNSTotalOfferShown'] -= $_SESSION['aSesMaxOffers'][$iPos] ;
			if($_SESSION['iSesSPNSTotalOfferShown'] < 0){
				$_SESSION['iSesSPNSTotalOfferShown'] = 0;
			}
			break; 
		case 18 :
			$_SESSION['iSesBPTotalOfferShown'] -= $_SESSION['aSesMaxOffers'][$iPos] ;
			if($_SESSION['iSesBPTotalOfferShown'] < 0){
				$_SESSION['iSesBPTotalOfferShown'] = 0;
			}
			break ;
		case 22 :
			$_SESSION['iSesOPTotalOfferShown'] = $_SESSION['iSesOPTotalOfferShown']-1 ;
			if($_SESSION['iSesOPTotalOfferShown'] < 0 ){
				$_SESSION['iSesOPTotalOfferShown'] = 0 ;
			}
			break;
	}
	
}

echo $_SESSION['iSesCurrentPositionInFlow'];
	

?>
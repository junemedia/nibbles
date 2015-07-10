<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');


$PHPSESSID = $_GET['PHPSESSID'];
if(!($PHPSESSID))
	exit();
	
session_start();

if ($_SESSION['sSesTemplateType'] == 'OP') {
	$_SESSION['iSesOPTotalOfferShown']++;
	$_SESSION['iSesCurrentPositionInFlow']++;
	// delete cookie if not deleted, because by this time, the pixel is already fired. - samir
	setcookie("AmpereOfferType", "th_", time()-3600, "/", ".popularliving.com", 0);
	setcookie("AmpereOfferType", "th_", time()-3600, "/", '', 0);
	session_write_close();
}

if ($_SESSION['sSesTemplateType'] == 'SPS') {
	$_SESSION['aSesPage2Offers'] = array();
	$_SESSION['iSesCurrentPositionInFlow']++;
	session_write_close();
}

if ($_SESSION['sSesTemplateType'] == 'SPNS') {
	$before = $_SESSION['aSesPage2Offers'];
	//$_SESSION['aSesPage2Offers'] = array_shift($_SESSION['aSesPage2Offers']);
	
	unset($_SESSION['aSesPage2Offers'][0]);
	$temp_array = array_values($_SESSION['aSesPage2Offers']);
	$_SESSION['aSesPage2Offers'] = $temp_array;

	
	if (count($_SESSION['aSesPage2Offers']) == 0 && count($_SESSION['aSesCloseTheyHostOffers']) == 0) {
		$_SESSION['iSesSPNSTotalOfferShown']+=$_SESSION['iTempOfferShownCount'];
		$_SESSION['iSesCurrentPositionInFlow']++;
		// delete cookie if not deleted, because by this time, the pixel is already fired. - samir
		setcookie("AmpereOfferType", "cth_", time()-3600, "/", ".popularliving.com", 0);
		setcookie("AmpereOfferType", "cth_", time()-3600, "/", '', 0);
	}
	$after = $_SESSION['aSesPage2Offers'];
	
	if (count($_SESSION['aSesPage2Offers']) == 0 && count($_SESSION['aSesCloseTheyHostOffers']) == 0) {
		session_write_close();
	}
}

if ($_SESSION['sSesTemplateType'] == 'BP') {
	$_SESSION['iSesCurrentPositionInFlow']++;
	session_write_close();
}

if ($_SESSION['sSesTemplateType'] == 'PP') {
	$_SESSION['iSesCurrentPositionInFlow']++;
	session_write_close();
}


if ($_SESSION['sSesTemplateType'] == '3rdPP') {
	$_SESSION['iSesCurrentPositionInFlow']++;
}




?>


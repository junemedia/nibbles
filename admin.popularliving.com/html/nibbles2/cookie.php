<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');

$PHPSESSID = trim($_GET['PHPSESSID']);
session_start();
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);


$url = trim($_GET['url']);


// set cookie with session id - cookie expires after 60 mins
if (!(isset($_COOKIE['AmpereSessionId']))) {
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '.popularliving.com', 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '.3400cookie.com', 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '', 0);
	
	if ($url !='') {
		setcookie("AmpereSessionId", session_id(), time()+3600, "/", $url, 0);
	}
}

// start cookie
$aOfferTakenInCookie = array();
$sCurrentCookieOfferCode = '';
if (isset($_COOKIE["OfferTakenInCookie"])) {
	$aOfferTakenInCookie = explode(",", $_COOKIE["OfferTakenInCookie"]);
	if (count($aOfferTakenInCookie) > 0) {
		foreach ($aOfferTakenInCookie as $sOfferTemp) {
			if (!in_array($sOfferTemp, $_SESSION['aOfferTakenForCookie'])) {
				array_push($_SESSION['aOfferTakenForCookie'], $sOfferTemp);
			}
			if (!in_array($sOfferTemp, $_SESSION['aExcludeOffers'])) {
				array_push($_SESSION['aExcludeOffers'], $sOfferTemp);
			}
		}
	}
}

if (count($_SESSION['aOfferTakenForCookie']) > 0) {
	//	$_SESSION['aOfferTakenForCookie'] = array_unique($_SESSION['aOfferTakenForCookie']);
	foreach ($_SESSION['aOfferTakenForCookie'] as $sOfferCodeCookie) {
		$sCurrentCookieOfferCode .= "$sOfferCodeCookie,";
	}
	$sCurrentCookieOfferCode = substr($sCurrentCookieOfferCode,0,strlen($sCurrentCookieOfferCode)-1);
	setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '.popularliving.com', 0);
	setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '.3400cookie.com', 0);
	setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", '', 0);
	
	if ($url !='') {
		setcookie("OfferTakenInCookie", "$sCurrentCookieOfferCode", time()+15552000, "/", $url, 0);
	}
}
// end cookie


?>

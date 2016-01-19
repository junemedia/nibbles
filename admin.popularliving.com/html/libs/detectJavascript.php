<?php

/*

Javascript Detector

*/

// function call
function redirectIfJavascriptDisabled($sErrMsg = "You do not have JavaScript enabled.") {

	$sEncodedErrMsg = urlencode($sErrMsg);

	$returnURL = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$sEncodedReturnUrl = urlencode( $returnURL );

	$location = "/p/enableJavascript.php?sErrMsg=$sEncodedErrMsg&sReturnUrl=$sEncodedReturnUrl";
	
	echo '<noscript>';
	echo '<meta http-equiv="refresh" content="0; URL=' . $location .'">';
	echo '</noscript>';
}
?>

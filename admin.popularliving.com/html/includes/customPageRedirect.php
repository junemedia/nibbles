<?php

// script to handle page redirect after finishing ot page/page2 leads recording
// this script is included in otPageSubmit.php/otPage2Submit.php script/s

// it is required to use exit() after finishing custom page redirect processing
// to stop rest of the codes executing from otPageSubmit.php/otPage2Submit.php script

// custom processing for theNetPanel page



	
if ($_SESSION['iSesPageId'] == '405') {

	$sPostingUrl = "http://www.freeforum.com/scripts/channels.php";
	$sCustomRedirectTo = "http://www.freeforum.com/scripts/channels.php";
	$sTempPostString = "QueryString=[queryString]";


	$sQueryStringValue = "firstname=[first]&lastname=[last]&address1=[address]&address2=[address2]&city=[city]&state=[state]";
	$sQueryStringValue .= "&zip=[zip]&code=BOC-&channel=inv&session=1&format=autofill&salutation=&optin=no&private=no";
	$sQueryStringValue .= "&privateurl=http://www.popularliving.com/p/b2b.php";
	
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	if (strstr($sPostingUrl, "https:")) {
		$rTempSocketConn = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	} else {
		
		$rTempSocketConn = fsockopen($sHostPart, 80, $errno, $errstr, 30);
		
	}
	
	
	$sQueryStringValue = ereg_replace("\[first\]",urlencode($_SESSION["sSesFirst"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[last\]",urlencode($_SESSION["sSesLast"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[address\]",urlencode($_SESSION["sSesAddress"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[address2\]",urlencode($_SESSION["sSesAddress2"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[city\]",urlencode($_SESSION["sSesCity"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[state\]",urlencode($_SESSION["sSesState"]), $sQueryStringValue);
	$sQueryStringValue = ereg_replace("\[zip\]",urlencode($_SESSION["sSesZip"]), $sQueryStringValue);

	
	$sQueryStringValue = urlencode($sQueryStringValue);
	$sTempPostString = ereg_replace("\[queryString\]", $sQueryStringValue, $sTempPostString);
	
	
	if ($rTempSocketConn) {
		fputs($rTempSocketConn, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rTempSocketConn, "Host: $sHostPart\r\n");
		fputs($rTempSocketConn, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rTempSocketConn, "Content-length: " . strlen($sTempPostString) . "\r\n");
		fputs($rTempSocketConn, "User-Agent: MSIE\r\n");
		fputs($rTempSocketConn, "Connection: close\r\n\r\n");
		fputs($rTempSocketConn, $sTempPostString);
		
		$sFormPostResponse = '';
		while(!feof($rTempSocketConn)) {
			$sFormPostResponse .= fgets($rTempSocketConn, 1024);
		}
		
		fclose($rTempSocketConn);
		
		header("Location:$sCustomRedirectTo");
		exit();
	} else {
		echo "$errstr ($errno)<br />\r\n";
	}
}


?>

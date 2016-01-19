<?php
/*
$post_string = "email=".$_GET['email']."&bouncecount=0";
$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
$aUrlArray = explode("//", $sPostingUrl);
$sUrlPart = $aUrlArray[1];
$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
$sHostPart = ereg_replace("\/","",$sHostPart);
$sScriptPath = substr($sUrlPart,strlen($sHostPart));
$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
$server_response = '';
if ($rSocketConnection) {
	fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
	fputs($rSocketConnection, "Host: $sHostPart\r\n");
	fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
	fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
	fputs($rSocketConnection, "User-Agent: MSIE\r\n");
	fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
	fputs($rSocketConnection, "Connection: close\r\n\r\n");
	fputs($rSocketConnection, $post_string);
	while(!feof($rSocketConnection)) {
		$server_response .= fgets($rSocketConnection, 1024);
	}
	fclose($rSocketConnection);
}*/

?>
<meta http-equiv="refresh" content="0;URL='lookup.php?GET=Y&email=<?php echo $_GET['email']; ?>'" />
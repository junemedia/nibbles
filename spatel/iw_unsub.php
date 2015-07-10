<?php

$start_time = microtime(true);
include_once("/home/spatel/config.php");

$count = 0;
$result = mysql_query("SELECT * FROM iw_unsub");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$subcampid = '2921';
	$listid = '411,432,433,410,393,396,395,394,392,448,511,539,554,553,558,574,583';
	
	$count++;
	
	// START = SEND TO ARCAMAX
	$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid";
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
	}
	// END = SEND TO ARCAMAX
	$server_response = addslashes($post_string."\n\n\n".$server_response);
	
	echo "\n\n\n\nCount: $count => \n\n".$server_response;
}

$stop_time = microtime(true);
$time = $stop_time - $start_time;

echo "\n\n\nElapsed time was $time seconds.\n\n\n";


?>

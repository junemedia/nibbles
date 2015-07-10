<?php

include_once("/home/spatel/config.php");

function HardFlush ($x = 0) {
    echo "\n\n$x--";
    if (ob_get_length()) { @ob_flush();@flush();@ob_end_flush(); }
    @ob_start();
}

$x = 0;
$result = mysql_query("SELECT * FROM resetBounceCount WHERE response = '' ORDER BY email ASC");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$x++;
	
	echo ' Start Processing: '.$email;
	
	$post_string = "email=".$email."&bouncecount=0";
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
	
	$server_response = addslashes($server_response);

	$update_query = "UPDATE resetBounceCount SET response = \"$server_response\" WHERE email = \"$email\" LIMIT 1";
	$update_query_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 100 == 0) {
		sleep(1);
	}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}


mail('samirp@junemedia.com,leonz@junemedia.com','bounce count reset done','done');

?>

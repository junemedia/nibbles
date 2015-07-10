<?php

$start_time = microtime(true);

include_once("/home/spatel/config.php");

function HardFlush ($x = 0) {
    echo "\n\n$x--";
    // check that buffer is actually set before flushing
    if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
    }
    @ob_start();
}

function Arcamax_Request($post_string) {
	$server_response = '';
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];

	// separate host part and script path
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
			
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
	} else {
		$server_response = "$errstr ($errno)<br />\r\n";
	}
	return addslashes($server_response);
}


$x = 0;
$result = mysql_query("SELECT * FROM moveQuery WHERE result='' ORDER BY id DESC LIMIT 265392");
while ($row = mysql_fetch_object($result)) {
	$x++;
	
	$send_to_arcamax = Arcamax_Request($row->post_string);
	
	$update_query = "UPDATE moveQuery SET result=\"$send_to_arcamax\" WHERE id = \"$row->id\"";
	$update_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 100 == 0) {
		sleep(1);
	}

	echo $send_to_arcamax;
	
	HardFlush($row->id);
}

$stop_time = microtime(true);
$time = $stop_time - $start_time;
echo "Script Run Time: $time Seconds";

?>

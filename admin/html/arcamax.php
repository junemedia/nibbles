<?php

mysql_pconnect ("mydb01.amperemedia.com", "nibbles", "#a!!yu5");

$result = mysql_query("SELECT * FROM nibbles_temp.Recipe4LivingUnsub2");
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$unsublists = $row->listid;
	$ip = $row->ip;
	
	$post_string = "email=$email&unsublists=$unsublists&subcampid=2639&ipaddr=$ip";
		$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
		$aUrlArray = explode("//", $sPostingUrl);
		$sUrlPart = $aUrlArray[1];
		
		// separate host part and script path
		$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
		$sHostPart = ereg_replace("\/","",$sHostPart);
		$sScriptPath = substr($sUrlPart,strlen($sHostPart));
		
		if (strstr($sPostingUrl, "https:")) {
			$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
		} else {
			$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
		}
		
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
			
			if (strstr($server_response,"error")) {
				echo "Error: $server_response<br><br>";
			} else {
				echo "Success: Unsub Successful! ($email) <br><br>";
			}
		} else {
			echo "$errstr ($errno)<br />\r\n<br><br>";
		}
}

		
		
?>

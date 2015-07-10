<?php

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

$x = 0;
$result = mysql_query("SELECT * FROM manualSub");
while ($row = mysql_fetch_object($result)) {
	$post_string = "email=$row->email&sublists=$row->listid&subcampid=$row->subcampid";
	
	// insert into joinEmailSub
	$insert = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",\"\",\"$row->listid\",\"$row->subcampid\",\"HotmailBouncedOutReEngagement\",\"HotmailBouncedOutReEngagement\")";
	$insert_query_result = mysql_query($insert);
	echo mysql_error();

	// insert into joinEmailActive
	$insert = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",\"\",\"$row->listid\",\"$row->subcampid\",\"HotmailBouncedOutReEngagement\",\"HotmailBouncedOutReEngagement\")";
	$insert_query_result = mysql_query($insert);
	echo mysql_error();
	
	$delete_result = mysql_query("DELETE FROM manualSub WHERE email='$row->email' AND listid='$row->listid' LIMIT 1");
	
	$x++;
	
	// START = SEND TO ARCAMAX
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
	$server_response = addslashes($server_response);
	
	echo $post_string."\n\n".$server_response."\n\n";
	
	
	// record arcamax server response log
	$insert = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,type,response)
				VALUES (NOW(),\"$row->email\",\"$row->listid\",\"$row->subcampid\",\"sub\",\"$server_response\")";
	$insert_query_result = mysql_query($insert);
	echo mysql_error();
	
	if ($x % 10 == 0) {
		sleep(1);
	}
	
	HardFlush($x);
}

mail('samirp@junemedia.com,leonz@junemedia.com','done','hotmail reengagement done to listid/subcampid 586/3554');

?>

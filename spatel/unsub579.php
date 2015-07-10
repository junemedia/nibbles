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
$result = mysql_query("SELECT * FROM deleteSubscribers");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$listid = $row->listid;
	$x++;
	
	echo ' Start Processing: '.$email;

	$get_user_data = "SELECT * FROM joinEmailActive WHERE email=\"$email\" AND listid='$listid'";
	$get_user_data_result = mysql_query($get_user_data);
	while ($user_row = mysql_fetch_object($get_user_data_result)) {
		$user_ip = $user_row->ipaddr;
		$listid = $user_row->listid;
		$subcampid = '3362';
	
		$insert_query = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid) VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\")";
		$insert_query_result = mysql_query($insert_query);
		echo mysql_error();
	
		$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
		$delete_query_result = mysql_query($delete_query);
		echo mysql_error();
	
		$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid&ipaddr=$user_ip";
		
		// call to function to send unsub to Arcamax
		$send_to_arcamax = Arcamax_Request($post_string);
	
		//echo $send_to_arcamax;
		// record arcamax server response log
		$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
					VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
		$insert_log_result = mysql_query($insert_log);
		echo mysql_error();
	}
	
	$delete_query = "DELETE FROM deleteSubscribers WHERE email =\"$email\" AND listid='$listid'";
	$delete_query_result = mysql_query($delete_query);
	echo mysql_error();
	
	if ($x % 10 == 0) {
		sleep(1);
	}
	if ($x % 1000 == 0) {
		mail('samirp@junemedia.com,leonz@junemedia.com','remove from 579',$x);
	}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}

?>

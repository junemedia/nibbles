<?php

mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("arcamax");

function HardFlush () {
    echo '.';
    // check that buffer is actually set before flushing
    if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
    }   
    @ob_start();
}

function send_to_arcamax ($email,$ipaddr) {
	$post_string = "email=$email&sublists=411&subcampid=3099&ipaddr=$ipaddr";
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
	return addslashes($post_string."\n\n\n".$server_response);
}


// now for each distinct email in ztest, sign up to 411 list with 3099 subcampid
// insert into joinEmailSub
// insert into joinEmailActive
// send sub to arcamax
// record arcamax log


$x = 0;
$get_emails = "SELECT * FROM ztest ORDER BY email DESC LIMIT 11386";
$result_email = mysql_query($get_emails);
echo mysql_error();
while ($row = mysql_fetch_object($result_email)) {
	$response = send_to_arcamax($row->email,$row->ip);
	$x++;
	
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",\"$row->ip\",\"411\",\"3099\",\"MigrateFitnessAndBeautyToGeneralFitFab\",\"MigrateFitnessAndBeautyToGeneralFitFab\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",\"$row->ip\",\"411\",\"3099\",\"MigrateFitnessAndBeautyToGeneralFitFab\",\"MigrateFitnessAndBeautyToGeneralFitFab\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// record arcamax server response log
	$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$row->email\",\"411\",\"3099\",\"$row->ip\",\"sub\",\"$response\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	
	echo $row->email;
	HardFlush();
	
	$insert_done = "INSERT INTO ztestdone (email) VALUES (\"$row->email\")";
	$insert_done_result = mysql_query($insert_done);
	echo mysql_error();
}


echo $x;

?>

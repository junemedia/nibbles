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


// MAKE SURE ztest TABLE IS EMPTY BEFORE RUNNING THE SCRIPT

$x = 0;
$get_emails = "SELECT * FROM joinEmailActive WHERE listid IN ('432','433') ORDER BY id DESC LIMIT 8172";
$result_email = mysql_query($get_emails);
echo mysql_error();
while ($row = mysql_fetch_object($result_email)) {
	$x++;
	$listid = $row->listid;
	$email = $row->email;
	$subcampid = $row->subcampid;
	$ipaddr = $row->ipaddr;
	
	echo $row->id."-";
	
	
	$insert = "INSERT INTO ztest (email,ip) VALUES (\"$email\",\"$ipaddr\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
	
	
	$delete = "DELETE FROM joinEmailActive WHERE email=\"$email\" AND listid=\"$listid\" LIMIT 1";
	$delete_result = mysql_query($delete);
	echo mysql_error();
	
	
	$unsub = "INSERT INTO joinEmailUnsub (dateTime,email,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",'MigrateFitnessAndBeautyToGeneralFitFab')";
	$unsub_result = mysql_query($unsub);
	echo mysql_error();
	
	
	// now send unsub to arcamax
	// START = SEND TO ARCAMAX
	$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid&ipaddr=$ipaddr";
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

	$log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$ipaddr\",\"unsub\",\"$server_response\")";
	$log_result = mysql_query($log);
	echo mysql_error();
	
	HardFlush();
	
}

echo $x;

?>

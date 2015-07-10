<?php

// setup mysql connection...
include_once("config.php");


function send_to_arcamax ($email) {
	$post_string = "email=$email&sublists=553&subcampid=3156";
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


// get new emails for signup
$get_emails = "SELECT email FROM test.wim_signup";
$result_email = mysql_query($get_emails);
echo mysql_error();
$total_count = mysql_num_rows($result_email);
$x = 0;
while ($row = mysql_fetch_object($result_email)) {
	$response = send_to_arcamax($row->email);
	
	$x++;
	
	// delete from wim_signup take too...
	$delete = "DELETE FROM test.wim_signup WHERE email = \"$row->email\"";
	$result = mysql_query($delete);
	echo mysql_error();
	
	
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO arcamax.joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",'',\"553\",\"3156\",\"WIM\",\"import\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO arcamax.joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",'',\"553\",\"3156\",\"WIM\",\"import\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// record arcamax server response log
	$insert_log = "INSERT IGNORE INTO arcamax.arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$row->email\",\"553\",\"3156\",'',\"sub\",\"$response\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	
	echo $x . " of " . $total_count . "  $row->email  \n";
    @ob_flush();
    @flush();
    @ob_end_flush();
    @ob_start();
}


echo $x;


?>


<?php

$start_time = microtime(true);

include_once("/home/spatel/config.php");

$today = date('Y-m-d');

function HardFlush ($x = 0) {
    echo "\n\n$x--";
    if (ob_get_length()) { @ob_flush();@flush();@ob_end_flush(); }
    @ob_start();
}

function Arcamax_Request($post_string) {
	$server_response = '';
	$aUrlArray = explode("//", 'https://www.arcamax.com/esp/bin/espsub');
	$sHostPart = substr($aUrlArray[1],0,strlen($aUrlArray[1])-strrpos(strrev($aUrlArray[1]),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($aUrlArray[1],strlen($sHostPart));
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
		while(!feof($rSocketConnection)) { $server_response .= fgets($rSocketConnection, 1024); }
		fclose($rSocketConnection);
	} else { $server_response = "$errstr ($errno)<br />\r\n"; }
	return addslashes($post_string.'	|	'.$server_response);
}

$x = 0;
$result = mysql_query("SELECT * FROM badEmails WHERE processed='Y' AND unsub_again='N' AND bounce_count='1000'");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$subcampid = '3691';
	$listid = '393';
	
	$x++;
	
	echo ' Start Processing: '.$email;
	
	// unsub above from 393 and then set unsub_type column to "hard" and unsub_again to "Y"
		
	// insert into joinEmailSub
	$delete = "DELETE FROM joinEmailActive WHERE email=\"$email\" AND listid=\"$listid\" LIMIT 1";
	$delete_result = mysql_query($delete);
	echo mysql_error();
	
	$unsub = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,listid,subcampid) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\")";
	$unsub_result = mysql_query($unsub);
	echo mysql_error();
		
	// call to function to send sub to Arcamax
	$send_to_arcamax = Arcamax_Request("email=$email&unsublists=$listid&subcampid=$subcampid");
		
	// record arcamax server response log
	$log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"unsub\",\"$send_to_arcamax\")";
	$log_result = mysql_query($log);
	echo mysql_error();
	
	$update_query = "UPDATE badEmails SET unsub_type='hard', unsub_again='Y' WHERE email=\"$email\"";
	$update_query_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 100 == 0) {
		sleep(1);
	}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}

$time = microtime(true) - $start_time;
mail('samirp@junemedia.com,leonz@junemedia.com','Unsub 6+ bounces',"It took $time seconds to Unsub $x emails (includes 1 second hold time every 25th record).");



$x = 0;
$result = mysql_query("SELECT * FROM badEmails WHERE processed='Y' AND bounce_count > 5 AND unsub_again='N' AND bounce_count !='1000'");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$subcampid = '3691';
	$listid = '393';
	
	$x++;
	
	echo ' Start Processing: '.$email;
	
	// unsub above from 393 and then set unsub_type column to "soft" and unsub_again to "Y"
		
	// insert into joinEmailSub
	$delete = "DELETE FROM joinEmailActive WHERE email=\"$email\" AND listid=\"$listid\" LIMIT 1";
	$delete_result = mysql_query($delete);
	echo mysql_error();
	
	$unsub = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,listid,subcampid) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\")";
	$unsub_result = mysql_query($unsub);
	echo mysql_error();
		
	// call to function to send sub to Arcamax
	$send_to_arcamax = Arcamax_Request("email=$email&unsublists=$listid&subcampid=$subcampid");
		
	// record arcamax server response log
	$log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"unsub\",\"$send_to_arcamax\")";
	$log_result = mysql_query($log);
	echo mysql_error();
	
	$update_query = "UPDATE badEmails SET unsub_type='soft', unsub_again='Y' WHERE email=\"$email\"";
	$update_query_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 100 == 0) {
		sleep(1);
	}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}

$time = microtime(true) - $start_time;
mail('samirp@junemedia.com,leonz@junemedia.com','Unsub 6+ bounces',"It took $time seconds to Unsub $x emails (includes 1 second hold time every 25th record).");




?>

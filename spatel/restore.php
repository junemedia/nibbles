<?php

$start_time = microtime(true);

include_once("/home/spatel/config.php");


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
$result = mysql_query("SELECT * FROM restore_why WHERE processed='N' ORDER BY opendt DESC LIMIT 30000");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$x++;
	
	echo ' Start Processing: '.$email;
	
	$listid_array = explode(",", $row->listid);
	foreach ($listid_array as $listid) {
		$subcampid = '3362';
		
		$lookup = "SELECT * FROM joinEmailSub WHERE email=\"$email\" AND listid=\"$listid\" ORDER BY id DESC LIMIT 1";
		$lookup_result = mysql_query($lookup);
		while ($lookup_row = mysql_fetch_object($lookup_result)) {
			$subcampid = $lookup_row->subcampid;
		}
		
		// insert into joinEmailSub
		$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"RestoreBackFrom579\")";
		$insert_query_result = mysql_query($insert_query);
		echo mysql_error();
	
		// insert into joinEmailActive
		$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"RestoreBackFrom579\")";
		$insert_query_result = mysql_query($insert_query);
		echo mysql_error();
		
		// call to function to send sub to Arcamax
		$send_to_arcamax = Arcamax_Request("email=$email&sublists=$listid&subcampid=$subcampid");
		
		// record arcamax server response log
		$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"sub\",\"$send_to_arcamax\")";
		$insert_log_result = mysql_query($insert_log);
		echo mysql_error();
	}
	
	$update_query = "UPDATE restore_why SET processed='Y' WHERE email=\"$email\"";
	$update_query_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 25 == 0) {
		sleep(1);
	}
	if ($x % 1000 == 0) {
		mail('samirp@junemedia.com,leonz@junemedia.com','Restore',$x);
	}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}

$time = microtime(true) - $start_time;
mail('samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com','Email Restore Completed',"It took $time seconds to restore $x emails (includes 1 second hold time every 25th record).");

?>

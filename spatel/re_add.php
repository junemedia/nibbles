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
//$result = mysql_query("SELECT * FROM badEmails WHERE iw_good='Y' AND reAddedDate='$today' AND processed='N' LIMIT 20001");
$result = mysql_query("SELECT * FROM badEmails WHERE iw_good = 'Y' AND processed = 'N' ORDER BY reAddedDate ASC LIMIT 30000");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$subcampid = '3691';
	$listid = '393';
	
	$x++;
	
	echo ' Start Processing: '.$email;
		
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"800kReAdded\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"800kReAdded\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
		
	// call to function to send sub to Arcamax
	$send_to_arcamax = Arcamax_Request("email=$email&sublists=$listid&subcampid=$subcampid");
		
	// record arcamax server response log
	$insert_log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"sub\",\"$send_to_arcamax\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	
	$update_query = "UPDATE badEmails SET processed='Y' WHERE email=\"$email\"";
	$update_query_result = mysql_query($update_query);
	echo mysql_error();
	
	if ($x % 25 == 0) {
		sleep(1);
	}
	//if ($x % 5000 == 0) {
	//	mail('samirp@junemedia.com,leonz@junemedia.com','ReAdded: '.$x,$x);
	//}

	echo ' End Processing: '.$email;
	
	HardFlush($x);
}

$time = microtime(true) - $start_time;
mail('samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com','30,000 Emails Re-Added',"It took $time seconds to Re-Add $x emails (includes 1 second hold time every 25th record).");

?>

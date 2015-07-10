<?php

$start_time = microtime(true);
include_once("/home/spatel/config.php");
$bounce_out_report = "samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com";
$last_bounce_date_90_days_ago = date("Y-m-d",(strtotime(date('Y-m-d')) - 7776000));

/*
$query = "INSERT IGNORE INTO bounced90DaysAgo (email,listid,subcampid,last_bounce_date) 
		SELECT email,listid,subcampid,last_bounce_date FROM joinEmailActiveArcamax 
		WHERE bounce_count >=20 
		AND last_bounce_date < DATE_SUB(CURDATE(),INTERVAL 90 DAY) 
		AND listid != 0";
*/


$query = "INSERT IGNORE INTO bounced90DaysAgo (email,listid,subcampid,last_bounce_date) 
		SELECT email,listid,subcampid,last_bounce_date FROM joinEmailActiveArcamax 
		WHERE bounce_count >=20 
		AND last_bounce_date = '$last_bounce_date_90_days_ago' 
		AND listid != 0";
$bounced90DaysAgo = mysql_query($query);
echo mysql_error();


$results = mysql_query("SELECT DISTINCT email FROM bounced90DaysAgo");
echo mysql_error();
$unique_count = mysql_num_rows($results);

$total_count = 0;
$result = mysql_query("SELECT * FROM bounced90DaysAgo ORDER BY last_bounce_date ASC");
while ($row = mysql_fetch_object($result)) {
	$id = $row->id;
	$email = $row->email;
	$listid = $row->listid;
	$last_bounce_date = $row->last_bounce_date;
	$subcampid = $row->subcampid;
	$total_count++;
	
	$get_count_result = mysql_query("SELECT * FROM joinEmailActive WHERE email=\"$email\" AND listid=\"$listid\" LIMIT 1");
	echo mysql_error();
	if (mysql_num_rows($get_count_result) > 0) {
		$delete = "DELETE FROM joinEmailActive WHERE email=\"$email\" AND listid=\"$listid\" LIMIT 1";
		$delete_result = mysql_query($delete);
		echo mysql_error();
		
		$subsource = "LBD:$last_bounce_date";
		$unsub = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,listid,subcampid,source,subsource) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",'BounceOut',\"$subsource\")";
		$unsub_result = mysql_query($unsub);
		echo mysql_error();
	}
	
	$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid";
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
	
	$server_response = addslashes($post_string."\n\n\n".$server_response);
	$log = "INSERT IGNORE INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response) VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"\",\"unsub\",\"$server_response\")";
	$log_result = mysql_query($log);
	echo mysql_error();
	
	$del_result = mysql_query("DELETE FROM bounced90DaysAgo WHERE id = '$id' LIMIT 1");
	
	echo '.';
	sleep(1);
}

$subject = "Unsubscribed Report: Bounced Out 90+ Days Ago - ".date('Y-m-d');
$header = "From:Subscription Center Admin <admin@myfree.com>";

$stop_time = microtime(true);
$time = $stop_time - $start_time;

$body = "Bounced Out 90 Days Ago And Bounce Count Is Still 20+ ($last_bounce_date_90_days_ago).\n\n\nEmail Count: $unique_count Unsubscribed\n\n\n"."Script Run Time: $time Seconds";

mail($bounce_out_report,$subject,$body,$header);

mysql_close();

?>

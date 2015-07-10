<?php

$start_time = microtime(true);
include_once("/home/spatel/config.php");

$x = 0;
$result = mysql_query("SELECT email FROM tempReport ORDER BY email ASC");
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	
	$subcampid = "";
	$sub_dt = '';
	$ipaddr = '';
	$unsub_dt = '';
	$errorCode = '';
	$reason = '';
	$jobid = '';
	$details = mysql_query("SELECT * FROM joinEmailSub WHERE email=\"$email\" ORDER BY dateTime ASC LIMIT 1");
	echo mysql_error();
	while ($details_row = mysql_fetch_object($details)) {
		$subcampid = $details_row->subcampid;
		$sub_dt = $details_row->dateTime;
	}
	
	$details = mysql_query("SELECT * FROM joinEmailUnsub WHERE email=\"$email\" ORDER BY dateTime DESC LIMIT 1");
	echo mysql_error();
	while ($details_row = mysql_fetch_object($details)) {
		$ipaddr = $details_row->ipaddr;
		$unsub_dt = $details_row->dateTime;
	}
	
	
	$details = mysql_query("SELECT * FROM bounceLogArchiveTEMP WHERE email=\"$email\" ORDER BY dateTimeAdded DESC LIMIT 1");
	echo mysql_error();
	while ($details_row = mysql_fetch_object($details)) {
		$reason = $details_row->reason;
		$jobid = $details_row->jobid;
		$errorCode = $details_row->type;
	}
	
	if ($errorCode == '' || $reason == '') {
		$details = mysql_query("SELECT * FROM bounceOutArchiveTEMP WHERE email=\"$email\" ORDER BY dateTimeAdded DESC LIMIT 1");
		echo mysql_error();
		while ($details_row = mysql_fetch_object($details)) {
			if ($errorCode == '') {
				$errorCode = $details_row->type;
			}
			if ($reason == '') {
				$reason = $details_row->reason;
			}
		}
	}
	if (strstr($errorCode, 'hardbounce')) {
		$reason = '';
	}
	
	
	$update_result = mysql_query("UPDATE tempReport SET subcampid=\"$subcampid\",sub_dt=\"$sub_dt\",soft_hard=\"$errorCode\",unsub_dt=\"$unsub_dt\",ipaddr=\"$ipaddr\",reason=\"$reason\",jobid=\"$jobid\" WHERE email=\"$email\"");
	echo mysql_error();
	$x++;
	
	
	if ($x % 100 == 0) {
		echo $x."\n";
	}
	
	
}

mysql_close();


?>

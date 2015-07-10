<?php

//include_once("/home/spatel/config.php");

$start_time = microtime(true);


$production_server = mysql_pconnect ("192.168.51.33", "root", "5dsa234Y");
$production_db = mysql_select_db ("arcamax",$production_server);


$reporting_server = mysql_pconnect ("192.168.51.56", "root", "");
$reporting_db = mysql_select_db ("arcamax",$reporting_server);




$count = 0;
$find = mysql_query("SELECT id,email,listId FROM opensClicksRawDataArchive WHERE subcampid='0'",$reporting_server);
echo mysql_error();
while ($find_row = mysql_fetch_object($find)) {
	$subcampid = '';
	$joinDate = '';
	$get_details = mysql_query("SELECT DATE(dateTime) AS joinDate,subcampid FROM joinEmailSub WHERE email=\"$find_row->email\" AND listid=\"$find_row->listId\" ORDER BY id DESC LIMIT 1",$production_server);
	echo mysql_error();
	while ($details_row = mysql_fetch_object($get_details)) {
		$subcampid = $details_row->subcampid;
		$joinDate = $details_row->joinDate;
	}
	
	if ($subcampid == '') {
		$get_details = mysql_query("SELECT DATE(dateTime) AS joinDate,subcampid FROM joinEmailActive WHERE email=\"$find_row->email\" AND listid=\"$find_row->listId\"",$production_server);
		echo mysql_error();
		while ($details_row = mysql_fetch_object($get_details)) {
			$subcampid = $details_row->subcampid;
			$joinDate = $details_row->joinDate;
		}
	}
	
	$update_result = mysql_query("UPDATE opensClicksRawDataArchive SET subcampid=\"$subcampid\", joinDate=\"$joinDate\" WHERE id = '$find_row->id'",$reporting_server);
	echo mysql_error();
	//echo $find_row->id."\n\n";
	
	if ($count % 250 == 0) {
		sleep(1);
		echo '.';
	}
	
	if ($count % 500000 == 0) {
		sleep(1);
		mail("samirp@silvercarrot.com",'updated subcampid/joindate - ' . $count,'updated subcampid/joindate',"From:Subscription Center Admin <admin@myfree.com>");
	}
	
	$count++;
}


mysql_close($production_server);
mysql_close($reporting_server);


mail("samirp@junemedia.com,leonz@junemedia.com",'updated subcampid/joindate','updated subcampid/joindate',"From:Subscription Center Admin <admin@myfree.com>");


$stop_time = microtime(true);
$time = $stop_time - $start_time;

echo "\n\nTime: ".$time."\n\n";

?>

<?php

$start_time = microtime(true);

include_once("/home/spatel/config.php");

$yesterday = date("Y-m-d", strtotime("-1 day"));
$active_lists = '';



$query = "SELECT listid FROM joinLists WHERE isActive='Y'";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$active_lists .= "'$row->listid',";
}
$active_lists = substr($active_lists,0,strlen($active_lists)-1);


$query = "SELECT listid, count(*) AS count
		FROM joinEmailActive 
		WHERE dateTime <= '$yesterday 23:59:59'
		GROUP BY listid";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$insert = "INSERT IGNORE INTO historicalActiveStats (statDate,listid,count)
				VALUES (\"$yesterday\",\"$row->listid\",\"$row->count\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
}

$query = "SELECT count(DISTINCT email) AS count  FROM joinEmailActive WHERE dateTime <= '$yesterday 23:59:59' AND listid IN ($active_lists) LIMIT 1";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$insert = "INSERT IGNORE INTO historicalActiveStats (statDate,listid,count)
				VALUES (\"$yesterday\",\"0\",\"$row->count\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
}


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

$stop_time = microtime(true);
$time = $stop_time - $start_time;

$report = "<table><tr><td>Historical Active Stats Script Completed.  Elapsed time was $time seconds.</td></tr></table>";

//mail('samirp@junemedia.com,leonz@junemedia.com',"Historical Active Stats Script Completed - $yesterday",$report,$sHeaders);


?>

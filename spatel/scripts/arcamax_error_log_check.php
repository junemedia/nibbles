<?php

include_once("/home/spatel/config.php");

$yesterday = date("Y-m-d", strtotime("-1 day"));

$report = "<table width='70%' border='1' align='center'>";
$report .= "<tr><td><b>Error Logs</b></td><td><b>Data</b></td></tr>";

$get_log = "SELECT * FROM arcamaxNewLog WHERE dateTime BETWEEN '$yesterday 00:00:00' AND '$yesterday 23:59:59'";
$result = mysql_query($get_log);
echo mysql_error();
$count = 0;
$red_count = 0;
$white_count = 0;
$yellow_count = 0;
while ($row = mysql_fetch_object($result)) {
	if (strstr($row->response,'error')) {
		$bgcolor = 'white';
		if (strstr($row->response,'MASTERUNSUB')) {
			$bgcolor = 'yellow';
			$yellow_count++;
		}
		if (strstr($row->response,'BADLIST')) {
			$bgcolor = 'red';
			$red_count++;
		}
		
		if ($bgcolor == 'white') {
			$white_count++;
		}
		
		$report .= "<tr bgcolor='$bgcolor'><td>$row->response</td><td><b>ID:</b> $row->id<br><b>Email:</b> $row->email<br><b>ListID:</b> $row->listid<br><b>SubcampID:</b> $row->subcampid<br><b>IP:</b> $row->ipaddr<br><b>Type:</b> $row->type<br><b>DateTime:</b> $row->dateTime<br></td></tr>";
	}
}

$report .= "</table>";

$extra = "<table width='40%' border='1' align='center'><tr><td>Red Count: </td><td>$red_count (error: BADLIST)</td></tr>
			<tr><td>Yellow Count: </td><td>$yellow_count (error: MASTERUNSUB)</td></tr>
			<tr><td>White Count: </td><td>$white_count (review entries in white)</td></tr></table><br><br>";

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

mail('samirp@junemedia.com,leonz@junemedia.com',"Arcamax Error Logs - $yesterday",$extra.$report,$sHeaders);


?>

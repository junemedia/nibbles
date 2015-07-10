<?php

mysql_pconnect ("mydb01.amperemedia.com", "nibbles", "#a!!yu5");
mysql_select_db ("nibbles_temp");

$where = $_GET['where'];
if ($where =='') {
	echo 'missing where= value';exit;
}

$result = mysql_query("select * from keith_dp");
$x = 0;
$count = mysql_num_rows($result);
while ($row = mysql_fetch_object($result)) {
	$x++;
	$email = $row->email;
	$api = $row->api;
	$subid = $row->subid;
	$home = $row->home;
	$afid1 = $row->afid1;
	$afid2 = $row->afid2;
	
	$update = "update keith_client set 
							afid1=\"$afid1\",
							afid2=\"$afid2\",
							home=\"$home\",
							subid=\"$subid\",
							api=\"$api\"
							where $where=\"$email\"";
	//echo $update;exit;
	$update_result = mysql_query($update);
	echo mysql_error();
	echo "-";
	if ($x % 100 == 0) {
		echo $x." -->> <br><br>$count<br><br>";
	}
	if ($x % 1000 == 0) {
		echo "<br><br>$count<br><br>";
	}
	flush();ob_flush();
}
echo 'done';
?>


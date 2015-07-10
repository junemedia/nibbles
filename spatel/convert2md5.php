<?php

mysql_pconnect ("192.168.51.33", "root", "5dsa234Y");
mysql_select_db ("utility");

$get_emails = "SELECT * FROM convertmd5";
$result_email = mysql_query($get_emails);
echo mysql_error();
while ($row = mysql_fetch_object($result_email)) {
	$md5 = md5(strtolower($row->email));
	$update = "UPDATE convertmd5 SET md5='$md5' WHERE id='$row->id';";
	$update_result = mysql_query($update);
	echo mysql_error();
}


?> 

<?php

ini_set('max_execution_time', 5000);

include("../../includes/paths.php");
mysql_select_db ('utility');

$filename = trim($_GET['sFile']);
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Description: Text");
header("Connection: close");

$result_get_md5 = mysql_query("SELECT md5 FROM emails2convert");
while ($row = mysql_fetch_object($result_get_md5)) {
	echo $row->md5."\r\n";
}

$truncate = mysql_query("TRUNCATE TABLE emails2convert;");

exit();

?>

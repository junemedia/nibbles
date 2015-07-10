<?php

include_once("subctr_config.php");

$subcampid = trim($_GET['subcampid']);
$subcamp_name = trim($_GET['subcampidName']);

if ($subcampid !='' && ctype_digit($subcampid) && strlen($subcampid) == 4 && $subcamp_name !='') {
	$query = "INSERT IGNORE INTO subcampid (subcampid,notes) VALUES (\"$subcampid\",\"$subcamp_name\")";
	$result = mysql_query($query);
	echo mysql_error();
}

?>

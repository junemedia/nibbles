<?php

include_once("include.php");


if ($subcampid == '' || !ctype_digit($subcampid) || strlen($subcampid) != 4) { echo 'invalid subcampid';exit; }
if ($month_year == '') { echo 'missing month/year';exit; }
if ($linkid == '') { echo 'linkid';exit; }



$check = mysql_query("SELECT * FROM subcampids WHERE subcampid='$subcampid' LIMIT 1");
echo mysql_error();
if (mysql_num_rows($check) == 0) { echo 'invalid subcampid';exit; }
while ($row = mysql_fetch_object($check)) {
	$subcampidName = $row->subcampidName;
}

$insert = "REPLACE INTO links_subcampid (linkid,month_year,subcampid) VALUES (\"$linkid\",\"$month_year\",\"$subcampid\")";

$result = mysql_query($insert);
echo mysql_error();

if ($result) { echo 'success|'.$subcampidName; }

?>

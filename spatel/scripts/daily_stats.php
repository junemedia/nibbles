<?php

include_once("/home/spatel/config.php");


function lookupListName ($listid) {
	if ($listid == '586') { return 'Hotmail Re-engagement'; }
	if ($listid == '579') { return 'Inactive Re-engagement'; }
	$query = "SELECT title FROM joinLists WHERE listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	if (mysql_num_rows($result) == 0) {
		return '&nbsp;';
	} else {
		$row = mysql_fetch_object($result);
		return $row->title;
	}
}

$yesterday = date("Y-m-d", strtotime("-1 day"));

$report = "<table width='50%' border='1' align='center'>";
$report .= "<tr><td colspan='3'><b>Current Active List Size</b></td></tr>";
$report .= "<tr><td><b>listid</b></td><td><b>count</b></td><td><b>list name</b></td></tr>";

$query = "SELECT listid, count(*) as ct FROM joinEmailActive GROUP BY listid ORDER BY listid ASC";
$result = mysql_query($query);
echo mysql_error();
$count = 0;
while ($row = mysql_fetch_object($result)) {
	$list_name = lookupListName($row->listid);
	$report .= "<tr><td>$row->listid</td><td>$row->ct</td><td>$list_name</td></tr>";
	$count += $row->ct;
}
$report .= "<tr><td><b>Total: </b></td><td><b>$count</b></td><td>* If user is signed up for 5 newsletters, he is counted 5 times in this total.</td></tr>";



$unique_result = mysql_query("SELECT COUNT(DISTINCT email) AS ct FROM joinEmailActive WHERE listid NOT IN ('579','586');");
echo mysql_error();
$unique_count = 0;
while ($unique_row = mysql_fetch_object($unique_result)) {
	$unique_count = $unique_row->ct;
}

$report .= "<tr><td><b>Unique Emails: </b></td><td><b>$unique_count</b></td><td>* If user is signed up for 5 newsletters, he is counted once in this total. (excludes list 579/586)</td></tr>";
$report .= "</table><br><br>";












$report .= "<table width='50%' border='1' align='center'><tr><td colspan='3'><b>Active New Subscribers (Net - after unsub/dupes) - $yesterday</b></td></tr>";
$report .= "<tr><td><b>listid</b></td><td><b>count</b></td><td><b>list name</b></td></tr>";

$query = "SELECT listid, count(*) as ct FROM joinEmailActive WHERE dateTime BETWEEN '$yesterday 00:00:00' AND '$yesterday 23:59:59' GROUP BY listid ORDER BY listid ASC";
$result = mysql_query($query);
echo mysql_error();
$count = 0;
while ($row = mysql_fetch_object($result)) {
	$list_name = lookupListName($row->listid);
	$report .= "<tr><td>$row->listid</td><td>$row->ct</td><td>$list_name</td></tr>";
	$count += $row->ct;
}
$report .= "<tr><td>&nbsp;</td><td><b>$count</b></td><td>&nbsp;</td></tr>";
$report .= "</table><br><br>";









$report .= "<table width='50%' border='1' align='center'><tr><td colspan='3'><b>New Subscribers (Gross - before unsub/dupes) - $yesterday</b></td></tr>";
$report .= "<tr><td><b>listid</b></td><td><b>count</b></td><td><b>list name</b></td></tr>";

$query = "SELECT listid, count(*) as ct FROM joinEmailSub WHERE dateTime BETWEEN '$yesterday 00:00:00' AND '$yesterday 23:59:59' GROUP BY listid ORDER BY listid ASC";
$result = mysql_query($query);
echo mysql_error();
$count = 0;
while ($row = mysql_fetch_object($result)) {
	$list_name = lookupListName($row->listid);
	$report .= "<tr><td>$row->listid</td><td>$row->ct</td><td>$list_name</td></tr>";
	$count += $row->ct;
}
$report .= "<tr><td>&nbsp;</td><td><b>$count</b></td><td>&nbsp;</td></tr>";
$report .= "</table>";






$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

//mail('samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com',"New Subscribers by List and Current Active List Sizes - $yesterday",$report,$sHeaders);

?>

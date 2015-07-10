<?php

include_once("/home/spatel/config.php");

$soft_report = "<table border='1' cellspacing='10' cellpadding='10'>";
$soft_report .= "<tr><td colspan='2'><b>Soft Bounced</b></td></tr>";
$soft_report .= "<tr><td><b>Bounce Date</b></td><td><b>Count</b></td></tr>";

$result = mysql_query("SELECT bounceDate, COUNT(DISTINCT email) AS ct FROM bounceOut WHERE type='softbounce' GROUP BY bounceDate ORDER BY bounceDate DESC LIMIT 10;");
while ($row = mysql_fetch_object($result)) {
	$soft_report .= "<tr><td>$row->bounceDate</td><td>$row->ct</td></tr>";
}

$soft_report .= "</table>";




$hard_report = "<table border='1' cellspacing='10' cellpadding='10'>";
$hard_report .= "<tr><td colspan='2'><b>Hard Bounced</b></td></tr>";
$hard_report .= "<tr><td><b>Bounce Date</b></td><td><b>Count</b></td></tr>";

$result = mysql_query("SELECT bounceDate, COUNT(DISTINCT email) AS ct FROM bounceOut WHERE type='hardbounce' GROUP BY bounceDate ORDER BY bounceDate DESC LIMIT 10;");
while ($row = mysql_fetch_object($result)) {
	$hard_report .= "<tr><td>$row->bounceDate</td><td>$row->ct</td></tr>";
}
$hard_report .= "</table>";

$report = "<table border='0' cellspacing='10' cellpadding='10'><tr><td>$soft_report</td><td>$hard_report</td></tr></table>";

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:Subscription Center Admin <admin@myfree.com>\r\n";

mail('samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com,patriciad@junemedia.com',"Bounced Stats",$report,$sHeaders);

?>

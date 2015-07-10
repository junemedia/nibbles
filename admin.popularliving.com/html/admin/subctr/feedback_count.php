<?php

include_once("subctr_config.php");


$report = '';
$result = mysql_query("SELECT feedBackDate, count(*) as ct FROM feedBackLoop GROUP BY feedBackDate ORDER BY feedBackDate DESC LIMIT 365");
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$report .= "<tr>
		<td>$row->feedBackDate</td>
		<td>$row->ct</td>
	</tr>";
}


?>
<html>
<head>
<title>Feedback Loop Stats</title>
<style>
table {
	font-family: verdana;
	font-size:75%;
}
</style>
</head>
<body>

<table border="0" align="center" width="400px" cellpadding="5" cellspacing="5">
	<tr>
		<td colspan="2" align="center">
		<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" align="center"><b>Feedback Loop Count</b></td></tr>
</table>
<table border="1" align="center" width="400px" cellpadding="5" cellspacing="5">
<?php echo $report; ?>
</table>
</body>
</html>

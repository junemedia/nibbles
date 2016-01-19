<?php

include_once("../subctr_config.php");

$content_r4l = '';
$content_ff = '';

$month = date("m");
$day = date("d");
$year = date("Y");

for ($i=0; $i<15; $i++) {
	$date = date("Y-m-d", time()-(86400*$i));
	
	$query = "SELECT count(*) AS ct FROM signin 
			WHERE dateTimeAdded BETWEEN '$date 00:00:00' AND '$date 23:59:59'
			AND site = 'fitfab'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		if ($sBgcolorClass=="#E6E6FA") {
			$sBgcolorClass="#FFFACD";
		} else {
			$sBgcolorClass="#E6E6FA";
		}
		$content_ff .= "<tr bgcolor=$sBgcolorClass>
					<td>$date</td>
					<td>$row->ct</td>
					<td>fitfab</td>
				</tr>";
	}

	$query = "SELECT count(*) AS ct FROM signin 
			WHERE dateTimeAdded BETWEEN '$date 00:00:00' AND '$date 23:59:59'
			AND site = 'r4l'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		if ($sBgcolorClass!="#E6E6FA") {
			$sBgcolorClass="#FFFACD";
		} else {
			$sBgcolorClass="#E6E6FA";
		}
		$content_r4l .= "<tr bgcolor=$sBgcolorClass>
					<td>$date</td>
					<td>$row->ct</td>
					<td>r4l</td>
				</tr>";
	}
}



?>
<html>
<head>
<title>Subscription Signin Report</title>
<style>
table {
	font-family: verdana;
	font-style: normal;font-size: 12px;font-weight: normal;text-decoration: none;
}
</style>
</head>
<body>
<table align="center" border="0" cellpadding="1" cellspacing="1" width="50%" align="center">
	<tr>
		<td align="center">
			<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
</table>
<br><br>

<table align="center" border="1" cellpadding="1" cellspacing="1" width="50%">
<tr>
	<td colspan="3" align="center"><b>Subscription Signin Report</b></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><b>Count</b></td>
	<td><b>Site</b></td>
</tr>
<?php echo $content_r4l; ?>
</table>

<br><br>

<table align="center" border="1" cellpadding="1" cellspacing="1" width="50%">
<tr>
	<td colspan="3" align="center"><b>Subscription Signin Report</b></td>
</tr>
<tr>
	<td><b>Date</b></td>
	<td><b>Count</b></td>
	<td><b>Site</b></td>
</tr>
<?php echo $content_ff; ?>
</table>


<p style="font-size:12px;" align='right'>
Note: Data not available before 2011-03-11 13:38:51 EST/EDT (<?php echo $_SERVER['SERVER_ADDR']; ?>)
</p>

</body>
</html>

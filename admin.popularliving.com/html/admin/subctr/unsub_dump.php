<?php

$start_time = microtime(true);

include_once("subctr_config.php");

$authUser = strtolower(trim($_SERVER['PHP_AUTH_USER']));

function ValidateDate($date) {
	if (date('Y-m-d', strtotime($date)) == $date) {
		return true;
	} else {
		return false;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') {
	$from_date = '2013-10-28';	//date('Y-m-d', strtotime('-3 days'));
}
if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d', strtotime('-1 days'));
}

$download_link = '';
$output = '';
if ($submit == 'Submit') {
	$query = "SELECT * FROM joinEmailUnsubDetails WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
	$sql = mysql_query($query);
	echo mysql_error();
	$columns_total = mysql_num_fields($sql);
	
	for ($i = 0; $i < $columns_total; $i++) {
		$heading = mysql_field_name($sql, $i);
		$output .= '"'.$heading.'",';
	}
	$output .="\n";
	
	
	while ($row = mysql_fetch_array($sql)) {
		for ($i = 0; $i < $columns_total; $i++) {
			$output .='"'.$row["$i"].'",';
		}
		$output .="\n";
	}
	
	$file_name = "UnsubSurveyDump-$authUser-$from_date-$to_date.csv";
	if (!$fp = fopen(dirname(__FILE__)."/export/".$file_name, 'w')) {
		echo 'error';
	}
	fwrite($fp, $output);
	fclose($fp);
	$download_link = "<a href='export/$file_name' target=_blank>Download CSV</a><br><br>(After right clicking this link and select 'Save Link As' option and save it to your desktop.  Or open the link in new tab/window and save the text in .csv format - Ctrl + S key)";
}

?>
<html>
<head>
<title>Unsub Survey Dump</title>
<style>
* {
	font-family: verdana;
	font-size: 12px;
}
</style>
</head>
<body>
<table align="center" border="0" cellpadding="5" cellspacing="5" width="100%">
<tr>
	<td align="center"><b>Unsub Survey Dump</b></td>
</tr>
<tr>
	<td align="center" style="color:red;"></td>
</tr>
</table>
<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table align="center" border="0" cellpadding="5" cellspacing="5" width="100%">
<tr>
	<td align="center"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to 
		<input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font style="font-size:9px;">YYYY-MM-DD</font>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" name="submit" value="Submit">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</td>
</tr>
</table>
<br><br><br>
<center>
<?php echo $download_link; ?>
</center>
<br><br><br>
</form>
<ul>
<li>
	<?php
		$end_time = microtime(true);
		$duration = number_format($end_time - $start_time, 2, '.', '');
		echo "Time: $duration seconds";
	?>
</li>
<li>Data NOT available before 2013-10-28 13:49:39 ET</li>
<li>Do NOT run today's report.</li>
</ul>
</body>
</html>
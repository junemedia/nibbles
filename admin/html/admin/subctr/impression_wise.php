<?php

include_once("subctr_config.php");


function ValidateDate($date) {
	if (date('Y-m-d', strtotime($date)) == $date) {
		return true;
	} else {
		return false;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') {
	$from_date = date('Y-m-d', strtotime('-7 days'));
}

if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d');
}


$query = "SELECT * FROM impression_wise WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
$result = mysql_query($query);
echo mysql_error();
$num_of_records = 0;
$accepted_count = 0;
$rejected_count = 0;
$invalid = 0;
$seed = 0;
$trap = 0;
$mole = 0;
while ($row = mysql_fetch_object($result)) {
	if ($row->result == 'invalid') { $invalid++; }
	if ($row->result == 'seed') { $seed++; }
	if ($row->result == 'trap') { $trap++; }
	if ($row->result == 'mole') { $mole++; }
	
	if ($row->isValid == 'Y') {
		$accepted_count++;
	} else {
		$rejected_count++;
	}
		
	$num_of_records++;
}


$report = "<table align='center' border='1' width='30%'>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Invalid</b></td><td>$invalid</td></tr>
<tr><td><b>Seed</b></td><td>$seed</td></tr>
<tr><td><b>Mole</b></td><td>$mole</td></tr>
<tr><td><b>Trap</b></td><td>$trap</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Rejected</b></td><td>$rejected_count</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Accepted</b></td><td>$accepted_count</td></tr>
<tr><td colspan='2'>&nbsp;</td></tr>
<tr><td><b>Total</b></td><td>$num_of_records</td></tr>
</table>";

?>
<html>
<head>
<title>Real-Time Impression Wise Lookup Stats</title>
<style>
body {
	font-family: verdana;
}
</style>
</head>
<body>
<table align="center" border="0" cellpadding="5" cellspacing="5" width="30%">
<tr>
	<td colspan="5" align="center"><b>Real-Time Impression Wise Lookup Stats</b></td>
</tr>
<tr><form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<td colspan="2" align="center"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to <input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font size="1">e.g. <?php echo date('Y-m-d'); ?></font></td>
	<td><input type="submit" name="submit" value="Submit"></td>
	</form>
</tr>
</table>
<?php echo $report; ?>
</body>
</html>

<?php

$start_time = microtime(true);

include_once("subctr_config.php");

$authUser = strtolower(trim($_SERVER['PHP_AUTH_USER']));


$active_lists = '';
$query = "SELECT listid FROM joinLists WHERE isActive='Y'";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$active_lists .= "'$row->listid',";
}
$active_lists = substr($active_lists,0,strlen($active_lists)-1);


function ValidateDate($date) {
	if (date('Y-m-d', strtotime($date)) == $date) {
		return true;
	} else {
		return false;
	}
}

function lookupNameBySubcampId ($subcampid) {
	$find_name = "SELECT notes FROM subcampid WHERE subcampid = '$subcampid' LIMIT 1";
	$find_name_result = mysql_query($find_name);
	echo mysql_error();
	while ($row = mysql_fetch_object($find_name_result)) {
		return $row->notes;
	}
}

function historicalActiveStats ($date,$listid) {
	$query = "SELECT `count` FROM historicalActiveStats  WHERE statDate='$date'  AND listid='$listid'";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		return $row->count;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') {
	//$from_date = date('Y-m-d', strtotime('-3 days'));
	$from_date = date("Y-m-01", strtotime("last month"));
}
if (ValidateDate($to_date) == false || $to_date == '') {
	//$to_date = date('Y-m-d', strtotime('-3 days'));
	$to_date = date("Y-m-t", strtotime("last month"));
}

$download_link = '';
$report = '';
$export_data = '';
$ending_total = 0;
if ($submit == 'Submit') {
	$report .= "<table align='center' border='1' cellpadding='5' cellspacing='5' width='40%'>";
	$starting_total = historicalActiveStats($from_date,0);
	$report .= "<tr><td><b>Starting</b>: </td><td>".number_format($starting_total)."</td></tr>";
	$export_data .= "Starting:,".($starting_total)."\n";
	
	$query = "SELECT COUNT(DISTINCT email) AS ct FROM joinEmailSub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' AND listid IN ($active_lists);";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		$new_signup_total = $row->ct;
		$report .= "<tr><td><b>Subscribe</b>: </td><td>".number_format($new_signup_total)."</td></tr>";
		$export_data .= "\nSubscribe:,".($new_signup_total)."\n";
	}
	
	
	
	if ($show_details == 'Y') {
		$report .= "<tr><td colspan='2'><table border='1' align='right' width='90%'>";
		$query = "SELECT subcampid,COUNT(DISTINCT email) AS ct FROM joinEmailSub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' AND listid IN ($active_lists) GROUP BY subcampid ORDER BY ct DESC;";
		//echo $query;
		$result = mysql_query($query);
		echo mysql_error();
		while ($row = mysql_fetch_object($result)) {
			$new_signup = $row->ct;
			$subcampid = $row->subcampid;
			$report .= "<tr><td>".$subcampid." - ".lookupNameBySubcampId($subcampid)."</td><td>".number_format($new_signup)."</td><td>".number_format((($new_signup/$new_signup_total)*100),3)." %</td></tr>";
			$export_data .= "$subcampid - ".lookupNameBySubcampId($subcampid).",".($new_signup).",".number_format((($new_signup/$new_signup_total)*100),3)." %\n";
		}
		$report .= "</table></td></tr>";
	}
	
	
	
	
	
	
	
	
	$query = "SELECT COUNT(DISTINCT email) AS ct FROM joinEmailUnsub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' AND listid IN ($active_lists);";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		$new_unsub_total = $row->ct;
		$report .= "<tr><td><b>Unsubscribe</b>: </td><td>".number_format($new_unsub_total)."</td></tr>";
		$export_data .= "\nUnsubscribe,".($new_unsub_total)."\n";
	}
	
	
	if ($show_details == 'Y') {
		$report .= "<tr><td colspan='2'><table border='1' align='right' width='90%'>";
		$query = "SELECT source,COUNT(DISTINCT email) AS ct FROM joinEmailUnsub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' AND listid IN ($active_lists) GROUP BY source ORDER BY ct DESC;";
		//echo $query;
		$result = mysql_query($query);
		echo mysql_error();
		while ($row = mysql_fetch_object($result)) {
			$new_unsub = $row->ct;
			$source = $row->source;
			if ($source == '') { $source = 'SubCenter'; }
			$report .= "<tr><td>$source</td><td>".number_format($new_unsub)."</td><td>".number_format((($new_unsub/$new_unsub_total)*100),3)." %</td></tr>";
			$export_data .= "$source,".($new_unsub).",".number_format((($new_unsub/$new_unsub_total)*100),3)." %\n";
		}
		$report .= "</table></td></tr>";
	}
	
	
	
	
	
	
	
	$ending_total = $starting_total + $new_signup_total - $new_unsub_total;
	
	$report .= "<tr><td><b>Ending</b>: </td><td>".number_format($ending_total)."</td></tr>";
	$export_data .= "\nEnding:,".($ending_total)."\n";
	
	
	$report .= "<tr><td><b>Net Change</b>: </td><td>".number_format($ending_total - $starting_total)."</td></tr>";
	$export_data .= "\nNet Change:,".($ending_total - $starting_total)."\n";
	

	$report .= '</table>';
	
	
	$file_name = "UniqueReport-$authUser-$from_date-$to_date.csv";
	if (!$fp = fopen(dirname(__FILE__)."/export/".$file_name, 'w')) {
		echo 'error';
	}
	fwrite($fp, $export_data);
	fclose($fp);
	$download_link = "<a href='export/$file_name' target=_blank>Download CSV</a>";
}

?>
<html>
<head>
<title>Unique Report</title>
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
	<td align="center"><b>Unique Report</b></td>
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
		<input type="checkbox" name="show_details" value="Y" <?php if ($show_details == 'Y') { echo ' checked '; } ?>> Show Details
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="submit" name="submit" value="Submit">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo $download_link; ?>
	</td>
</tr>
</table>
</form>
<?php echo $report; ?>
<br><br><br><br>
<ul>
<li>
	<?php
		$end_time = microtime(true);
		$duration = number_format($end_time - $start_time, 2, '.', '');
		echo "Time: $duration seconds";
	?>
</li>
</ul>
</body>
</html>
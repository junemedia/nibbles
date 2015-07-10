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

function lookupTitleByListId ($val) {
	global $title_array;
	for($x=0;$x<count($title_array);$x++) {
		if ($title_array[$x][$val] != '') {
			return str_replace(',',' ',$title_array[$x][$val]);
		}
	}
}

function historicalActiveStats ($date,$listid) {
	$query = "SELECT `count` FROM historicalActiveStats  WHERE statDate='$date'  AND listid='$listid'";
	$result = mysql_query($query);
	while ($row = mysql_fetch_object($result)) {
		return $row->count;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') {
	$from_date = date('Y-m-d', strtotime('-1 days'));
}
if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d', strtotime('-1 days'));
}

$report = '';
$export_data = '';
$listing_array = array();
$title_array = array();

if ($submit == 'Submit') {
	$unsub_source = array('Admin','BounceOut','FeedBackLoop','FFUnsubLink','R4LUnsubLink','WIMUnsubLink','SFUnsubLink','');
	
	foreach ($unsub_source as $source) {
		if ($source == '') { $source = 'SubCenter'; }
		$$source = 0;
	}
	
	$list_result = mysql_query("SELECT listid,title FROM joinLists WHERE isActive='Y'");
	$list_id_in = '';
	while ($list_row = mysql_fetch_object($list_result)) {
		array_push($listing_array, $list_row->listid);
		array_push($title_array, array("$list_row->listid"=>"$list_row->title"));
		$list_id_in .= $list_row->listid.",";
	}
	$list_id_in = substr($list_id_in,0,strlen($list_id_in)-1);
	
	$report .= "<table border='1' width='100%' align='center' cellspacing='0' cellpadding='0'><tr><td>&nbsp;</td><td><b>List Size<br>($to_date)</b></td>";
	$export_data .= ",List Size ($to_date),";
	foreach ($unsub_source as $source) {
		if ($source == '') { $source = 'SubCenter'; }
		
		// export before below IFs condition to avoid <br> tags in exported file :)
		$export_data .= "$source,$source %,";
		
		if ($source == 'BounceOut') { $source = 'Bounced<br>Out'; }
		if ($source == 'FeedBackLoop') { $source = 'FeedBack<br>Loop'; }
		if ($source == 'FFUnsubLink') { $source = 'FF<br>UnsubLink'; }
		if ($source == 'R4LUnsubLink') { $source = 'R4L<br>UnsubLink'; }
		if ($source == 'WIMUnsubLink') { $source = 'WIM<br>UnsuBLink'; }
		if ($source == 'SFUnsubLink') { $source = 'SF<br>UnsubLink'; }
		
		$report .= "<td><b>$source</b></td>";
		$report .= "<td><b>$source<br>%</b></td>";
	}
	
	$report .= "<td><b>Total</b></td><td><b>Total<br>%</b></td></tr><tr>";
	$export_data .= "Total,Total %\n";
	
	$total_total = 0;
	$list_size_total = 0;
	$list_size = 0;
	foreach ($listing_array as $listid) {
		$total = 0;
		$list_size = historicalActiveStats($to_date,$listid);
		$list_size_total += $list_size;
		$report .= "<td><b>".lookupTitleByListId($listid)." ($listid)</b></td><td>".number_format($list_size)."</td>";
		$export_data .= lookupTitleByListId($listid)." ($listid),".$list_size.",";
		foreach ($unsub_source as $source) {
			$query = "SELECT COUNT(*) AS ct 
						FROM joinEmailUnsub 
						WHERE listid='$listid' 
						AND source='$source'
						AND dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
			$result = mysql_query($query);
			while ($row = mysql_fetch_object($result)) {
				if ($source == '') { $source = 'SubCenter'; }
				$report .= "<td>$row->ct</td>";
				$report .= "<td>".number_format((($row->ct/$list_size)*100),3)."%</td>";
				$export_data .= "$row->ct,".number_format((($row->ct/$list_size)*100),3)."%,";
				$total += $row->ct;
				$$source += $row->ct;
			}
		}
		$report .= "<td><b>$total</b></td><td>".number_format((($total/$list_size)*100),3)."%</td></tr><tr>";
		$export_data .= "$total,".number_format((($total/$list_size)*100),3)."%\n";
		$total_total += $total;
	}
	
	$report .= "<td><b>Total</b></td><td>".number_format($list_size_total)."</td>";
	$export_data .= "Total,".$list_size_total.",";
	foreach ($unsub_source as $source) {
		if ($source == '') { $source = 'SubCenter'; }
		$report .= "<td><b>".$$source."</b></td><td>".number_format((($$source/$list_size_total)*100),3)."%</td>";
		$export_data .= $$source.",".number_format((($$source/$list_size_total)*100),3)."%,";
	}
	$report .= "<td><b>$total_total</b></td><td>".number_format((($total_total/$list_size_total)*100),3)."%</td></tr><tr>";
	$export_data .= $total_total.",".number_format((($total_total/$list_size_total)*100),3)."%\n";
	
	$report .= "<td><b>Unique Emails</b></td><td>n/a</td>";
	$export_data .= "Unique Emails,,";
	$unique_total_total = 0;
	foreach ($unsub_source as $source) {
		$unique_total = 0;
		$query = "SELECT COUNT(DISTINCT email) AS ct 
						FROM joinEmailUnsub 
						WHERE source='$source' 
						AND listid IN ($list_id_in) 
						AND dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' LIMIT 1";
		$result = mysql_query($query);
		//if ($authUser == 'spatel') { echo $query."<br>"; }
		while ($row = mysql_fetch_object($result)) {
			$report .= "<td><b>".$row->ct."</b></td><td>&nbsp;</td>";
			$export_data .= $row->ct.",,";
			$unique_total_total += $row->ct;
		}
	}
	$report .= "<td><b>$unique_total_total</b></td><td>&nbsp;</td></tr></table>";
	$export_data .= $unique_total_total.",\n";
}

$download_link = '';
if ($export == 'Y') {
	$file_name = "UnsubReport-$authUser-$from_date-$to_date.csv";
	if (!$fp = fopen(dirname(__FILE__)."/export/".$file_name, 'w')) {
		echo 'error';
	}
	fwrite($fp, $export_data);
	fclose($fp);
	$download_link = "&nbsp;&nbsp;&nbsp;&nbsp;<a href='export/$file_name' target=_blank>Download</a>";
}

?>
<html>
<head>
<title>Unsubscribe Report</title>
<style>
* {
	font-family: verdana;
	font-size: 12px;
}
</style>
</head>
<body>
<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table align="center" border="0" cellpadding="5" cellspacing="5" width="80%">
<tr>
	<td align="center"><u><b>Unsubscribe Report</b></u></td>
	<td align="center"><a href="/admin/subctr/sub_report.php">Subscribe Report</a>
	 | <a href="/admin/subctr/unsub_by_age_report.php">Unsubscribe By Age Report</a></td>
</tr>
<tr>
	<td align="left"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to 
		<input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font style="font-size:9px;">YYYY-MM-DD</font>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="export" id="export" value="Y" <?php if ($export == 'Y') { echo ' checked '; } ?>> Export <?php echo $download_link; ?>
	</td>
	<td align="right"><input type="submit" name="submit" value="Submit"></td>
</tr>
</table>
</form>
<?php echo $report; ?>
<br><br><br><br>
<ul>
<li>Try not to run reports for big date range</li>
<li>If you use today's date, you will get real-time data</li>
<li>n/a = information not available</li>
<li>% = (unsub count / list size) x 100</li>
<li><?php echo "Time: ".number_format(microtime(true) - $start_time, 2, '.', '')." seconds"; ?></li>
<li>Export - If checked before you run the report, it will give you link to download csv file.</li>
<li></li>
<li>1.	Admin - June Media employee unsubscribes someone based on their request via contact form or reply to a newsletter.</li>
<li>2.	BounceOut - After 90 days, anyone in a bounced out state is unsubscribed. To reach a bounced out state, you  need 1 hard bounce or a soft bounce count of 20. </li>
<li>3.	FeedbackLoop - Pressing the "Junk" or "Spam" button in your email domain.</li>
<li>4.	FFUnsubLink - Anyone clicking on the link in the footer from Fit and Fab Living emails</li>
<li>5.	R4LUnsubLink - Anyone clicking on the link in the footer from Recipe4Living emails</li>
<li>6.	WIMUnsubLink - Anyone clicking on the link in the footer from Work It Mom emails</li>
<li>7.	SFUnsubLink - Anyone clicking on the link in the footer from SavvyFork emails</li>
<li>8.	SubCenter - Someone unsubscribes from the subscription center interface (accessed through email footer or Web site)</li>
</ul>
</body>
</html>
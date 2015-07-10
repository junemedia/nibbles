<?php

$start_time = microtime(true);

include_once("subctr_config.php");

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

if (ValidateDate($from_date) == false || $from_date == '') {
	$from_date = date('Y-m-d', strtotime('-1 days'));
}
if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d', strtotime('-1 days'));
}

$report = '';
$listing_array = array();
$title_array = array();

if ($submit == 'Submit') {
	$life_group = array('0-30','31-60','61-90','91-120','121-150','151-180','181-210','211-240','241-270','271-300','301-330','331-365','366+');
	
	$m1 = array();$m2 = array();$m3 = array();$m4 = array();$m5 = array();$m6 = array();$m7 = array();
	$m8 = array();$m9 = array();$m10 = array();$m11 = array();$m12 = array();$m13 = array();$m14 = array();
	
	$list_result = mysql_query("SELECT listid,title FROM joinLists WHERE isActive='Y'");
	while ($list_row = mysql_fetch_object($list_result)) {
		array_push($listing_array, $list_row->listid);
		array_push($title_array, array("$list_row->listid"=>"$list_row->title"));
	}
	
	$report .= "<table border='1' width='90%' align='center' cellspacing='5' cellpadding='5'><tr><td>&nbsp;</td>";
	foreach ($life_group as $age_group) {
		$report .= "<td><b>$age_group</b></td>";
	}
	$report .= "<td><b>Total</b></td></tr>";
	
	$total_total = 0;
	$total0_30 = 0;$total31_60 = 0;$total61_90 = 0;$total91_120 = 0;$total121_150 = 0;$total151_180 = 0;$total181_210 = 0;
	$total211_240 = 0;$total241_270 = 0;$total271_300 = 0;$total301_330 = 0;$total331_365 = 0;$total366_plus = 0;
	
	foreach ($listing_array as $listid) {
		$total = 0;
		$report .= "<tr><td><b>".lookupTitleByListId($listid)." ($listid)</b></td>";
		
		$z0_30 = 0;$z31_60 = 0;$z61_90 = 0;$z91_120 = 0;$z121_150 = 0;$z151_180 = 0;$z181_210 = 0;
		$z211_240 = 0;$z241_270 = 0;$z271_300 = 0;$z301_330 = 0;$z331_365 = 0;$z366_plus = 0;
		
		$query = "SELECT email,dateTime FROM joinEmailUnsub WHERE listid='$listid' AND dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'";
		//echo $query."<br>";
		$result = mysql_query($query);
		echo mysql_error();
		$unsub_count = mysql_num_rows($result);
		//echo $listid." ==> ".$unsub_count."<br>";
		while ($unsub_row = mysql_fetch_object($result)) {
			$unsub_date = substr($unsub_row->dateTime,0,10);
			$sub_date = date('Y-m-d');	// today's date as default sign up date if not found...
			
			$sub_query = "SELECT dateTime FROM joinEmailSub WHERE listid='$listid' AND email = '$unsub_row->email' ORDER BY id DESC LIMIT 1";
			//echo $sub_query."<br>";
			$sub_result = mysql_query($sub_query);
			$sub_count = mysql_num_rows($sub_result);
			echo mysql_error();
			while ($sub_row = mysql_fetch_object($sub_result)) {
				$sub_date = substr($sub_row->dateTime,0,10);
			}
			
			$days_diff = round((strtotime($unsub_date) - strtotime($sub_date)) / 86400);
			if ($days_diff < 0) { $days_diff *= -1; }
	
			if ($days_diff >= 0 && $days_diff <=30) { $z0_30++;$total0_30++;array_push($m1, $unsub_row->email); }
			if ($days_diff >= 31 && $days_diff <=60) { $z31_60++;$total31_60++;array_push($m2, $unsub_row->email); }
			if ($days_diff >= 61 && $days_diff <=90) { $z61_90++;$total61_90++;array_push($m3, $unsub_row->email); }
			if ($days_diff >= 91 && $days_diff <=120) { $z91_120++;$total91_120++;array_push($m4, $unsub_row->email); }
			if ($days_diff >= 121 && $days_diff <=150) { $z121_150++;$total121_150++;array_push($m5, $unsub_row->email); }
			if ($days_diff >= 151 && $days_diff <=180) { $z151_180++;$total151_180++;array_push($m6, $unsub_row->email); }
			if ($days_diff >= 181 && $days_diff <=210) { $z181_210++;$total181_210++;array_push($m7, $unsub_row->email); }
			if ($days_diff >= 211 && $days_diff <=240) { $z211_240++;$total211_240++;array_push($m8, $unsub_row->email); }
			if ($days_diff >= 241 && $days_diff <=270) { $z241_270++;$total241_270++;array_push($m9, $unsub_row->email); }
			if ($days_diff >= 271 && $days_diff <=300) { $z271_300++;$total271_300++;array_push($m10, $unsub_row->email); }
			if ($days_diff >= 301 && $days_diff <=330) { $z301_330++;$total301_330++;array_push($m11, $unsub_row->email); }
			if ($days_diff >= 331 && $days_diff <=365) { $z331_365++;$total331_365++;array_push($m12, $unsub_row->email); }
			if ($days_diff >= 366) { $z366_plus++;$total366_plus++;array_push($m13, $unsub_row->email); }
			array_push($m14, $unsub_row->email);
			$total++;
			$total_total++;
		}
		
		$report .= "<td>$z0_30</td><td>$z31_60</td><td>$z61_90</td><td>$z91_120</td>
					<td>$z121_150</td><td>$z151_180</td><td>$z181_210</td>
					<td>$z211_240</td><td>$z241_270</td><td>$z271_300</td>
					<td>$z301_330</td><td>$z331_365</td><td>$z366_plus</td><td><b>$total</b></td></tr>";
	}
	
	$report .= "<tr><td><b>Total</b></td>";
	$report .= "<td><b>$total0_30</b></td><td><b>$total31_60</b></td><td><b>$total61_90</b></td><td><b>$total91_120</b></td>
					<td><b>$total121_150</b></td><td><b>$total151_180</b></td><td><b>$total181_210</b></td>
					<td><b>$total211_240</b></td><td><b>$total241_270</b></td><td><b>$total271_300</b></td>
					<td><b>$total301_330</b></td><td><b>$total331_365</b></td><td><b>$total366_plus</b></td><td><b>$total_total</b></td></tr>";
	
	$report .= "<tr><td><b>Unique Emails</b></td>";
	$report .= "<td><b>".count(array_unique($m1))."</b></td><td><b>".count(array_unique($m2))."</b></td><td><b>".count(array_unique($m3))."</b></td><td><b>".count(array_unique($m4))."</b></td>
					<td><b>".count(array_unique($m5))."</b></td><td><b>".count(array_unique($m6))."</b></td><td><b>".count(array_unique($m7))."</b></td>
					<td><b>".count(array_unique($m8))."</b></td><td><b>".count(array_unique($m9))."</b></td><td><b>".count(array_unique($m10))."</b></td>
					<td><b>".count(array_unique($m11))."</b></td><td><b>".count(array_unique($m12))."</b></td><td><b>".count(array_unique($m13))."</b></td><td><b>".count(array_unique($m14))."</b></td></tr>";
	
	$report .= "</table>";
}

?>
<html>
<head>
<title>Unsubscribe By Age Report</title>
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
	<td align="center"><u><b>Unsubscribe By Age Report</b></u></td>
	<td align="center"><a href="/admin/subctr/sub_report.php">Subscribe Report</a> | <a href="/admin/subctr/unsub_report.php">Unsubscribe Report</a></td>
</tr>
<tr>
	<td align="left"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to 
		<input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font style="font-size:9px;">YYYY-MM-DD</font>
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
<li><?php echo "<b>Time:</b> ".number_format(microtime(true) - $start_time, 2, '.', '')." seconds"; ?></li>
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
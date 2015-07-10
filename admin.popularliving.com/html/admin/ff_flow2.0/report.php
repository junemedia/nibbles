<?php

include_once("include.php");

$data = '';

function ValidateDate($date) {
	if (date('Y-m-d', strtotime($date)) == $date) {
		return true;
	} else {
		return false;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') { $from_date = date('Y-m')."-01"; }

if (ValidateDate($to_date) == false || $to_date == '') { $to_date = date('Y-m-d'); }


if ($submit == 'Submit') {
	$filter = "";
	if ($linkid != '') {
		$filter = " AND linkid='$linkid'";
	}
	$get_links_result = mysql_query("SELECT * FROM links WHERE 1=1 $filter ORDER BY dateTime DESC");
	$total_total_display = 0;
	$total_total_signup = 0;
	while ($link_row = mysql_fetch_object($get_links_result)) {
		$template = $link_row->template;
		$name = $link_row->name;
		$delay = $link_row->delay;
		$opacity = $link_row->opacity;
		$usercontrol = $link_row->usercontrol;
		$source = $link_row->source;
		$campaign = $link_row->campaign;
		
		$report_result = mysql_query("SELECT SUM(display) AS totalDisplay, SUM(signup) AS totalSignup FROM report WHERE linkid='$link_row->linkid' AND dateAdded BETWEEN '$from_date' AND '$to_date'");
		while ($row = mysql_fetch_object($report_result)) {
			if ($sBgcolorClass == "#FAFAFA") {
				$sBgcolorClass = "#FBEFF2";
			} else {
				$sBgcolorClass = "#FAFAFA";
			}
			
			$conversion_rate = sprintf("%.2f%%", (($row->totalSignup / $row->totalDisplay) * 100));
			$display_count = $row->totalDisplay;
			if ($row->totalDisplay == '') { $display_count = 0; }
			$signup_count = $row->totalSignup;
			if ($row->totalSignup == '') { $signup_count = 0; }
			$data .= "<tr bgcolor=$sBgcolorClass>
					<td>$name</td>
					<td>$display_count</td>
					<td>$signup_count</td>
					<td>$conversion_rate</td>
					<td>$template</td>
					<td>$usercontrol</td>
					<td>$delay</td>
					<td>$opacity</td>
					<td>$source</td>
					<td>$campaign</td>
				</tr>";
			
			$total_total_display += $display_count;
			$total_total_signup += $signup_count;
		}
	}

	$total_total_conversion = sprintf("%.2f%%", (($total_total_signup / $total_total_display) * 100));
	$data .= "<tr>
				<td><b>Total:</b></td>
				<td><b>$total_total_display</b></td>
				<td><b>$total_total_signup</b></td>
				<td><b>$total_total_conversion</b></td>
				<td colspan='6'>&nbsp;</td>
			</tr>";
}

$link_options = "<option value=''></option>";
$get_links_result = mysql_query("SELECT * FROM links ORDER BY dateTime DESC");
while ($link_row = mysql_fetch_object($get_links_result)) {
	$selected = '';
	if ($linkid == $link_row->linkid) {
		$selected = 'selected';
	}
	$link_options .= "<option value='$link_row->linkid' $selected>$link_row->name</option>";
}


?>
<html>
<head>
<title>Report</title>
<style>
table{font:12px Arial,Helvetica,sans-serif;line-height:1.25em;color:#4e4e4e;background:#e2ded5;}
</style>
</head>
<body>
<center><h3>FitAndFabLiving</h3></center>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" name='form1'>
<table border="1" align="center" cellpadding="5" cellspacing="5">
	<tr>
		<td colspan="3" align="center"><a href="/admin/ff_flow2.0/">Back to Links Management</a></td>
	</tr>
	<tr>
		<td>Date: <input type="text" name="from_date" value="<?php echo $from_date; ?>" maxlength="10" size="10"> to <input type="text" name="to_date" value="<?php echo $to_date; ?>" maxlength="10" size="10"></td>
		<td>Link: <select name="linkid"><?php echo $link_options; ?></select></td>
		<td><input type="submit" name="submit" value="Submit"></td>
	</tr>
</table>
</form>
<table border="1" align="center" cellpadding="5" cellspacing="5">
<tr>
	<td><b>Link Name</b></td>
	<td><b>Display</b></td>
	<td><b>Signup</b></td>
	<td><b>Conversion Rate</b></td>
	<td><b>Graphic</b></td>
	<td><b>User Control</b></td>
	<td><b>Delay</b></td>
	<td><b>Opacity</b></td>
	<td><b>Source</b></td>
	<td><b>Campaign</b></td>
</tr>
<?php echo $data; ?>
</table>

<br><br><br><br>
<font size="2">
<b>Note:</b> Report data not available before Nov 11th, 2013.
</font>
</body>
</html>
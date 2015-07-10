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

function lookupNameBySubcampId ($subcampid) {
	$find_name = "SELECT notes FROM subcampid WHERE subcampid = '$subcampid' LIMIT 1";
	$find_name_result = mysql_query($find_name);
	echo mysql_error();
	while ($row = mysql_fetch_object($find_name_result)) {
		return $row->notes;
	}
}

if (ValidateDate($from_date) == false || $from_date == '') {
	$from_date = date('Y-m-d', strtotime('-1 days'));
}
if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d', strtotime('-1 days'));
}

$report = "";
$filter = '';
$listing_array = array();
$title_array = array();
$total = 0;
if ($submit == 'Submit') {
	$report = "<tr><td><b>Subcamp ID</b></td><td><b>Subscriptions Count</b></td></tr>";
	
	if ($listid != '') {
		$filter .= " AND listid = '$listid' ";
	}
	if ($subcampid != '') {
		$filter .= " AND subcampid IN ($subcampid) ";
	}
	
	$query = "SELECT subcampid, COUNT(*) AS ct FROM joinEmailSub 
			WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' 
			$filter 
			GROUP BY subcampid 
			ORDER by subcampid DESC";
	$result = mysql_query($query);
	while ($row = mysql_fetch_object($result)) {
		$report .= "<tr><td>$row->subcampid (".lookupNameBySubcampId($row->subcampid).")</td><td>$row->ct</td></tr>";
		$total += $row->ct;
	}
	$report .= "<tr><td><b>Total Gross Subscription: </b></td><td><b>$total</b></td></tr>";
	
	$query2 = "SELECT COUNT(DISTINCT email) AS ct FROM joinEmailSub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'  $filter ";
	$result2 = mysql_query($query2);
	while ($row = mysql_fetch_object($result2)) {
		$report .= "<tr><td><b>Total Gross Unique Emails: </b></td><td><b>$row->ct</b></td></tr>";
	}
}


$list_result = mysql_query("SELECT listid,title FROM joinLists WHERE isActive='Y'");
$listid_options = '<option value=""></option>';
while ($list_row = mysql_fetch_object($list_result)) {
	array_push($listing_array, $list_row->listid);
	array_push($title_array, array("$list_row->listid"=>"$list_row->title"));
	$selected = '';
	if ($list_row->listid == $listid) {
		$selected = ' selected ';
	}
	$listid_options .= "<option value='$list_row->listid' $selected>$list_row->title ($list_row->listid)</option>";
}




// START OF GENERATING SOURCE DROP DOWN MENU

$source_options = '';

$source_options .= "<option value=''></option><option value='' disabled>--------Group of SubcampID-------</option>";
$get_group_result = mysql_query("SELECT * FROM groupSubcampId");
echo mysql_error();
while ($group_row = mysql_fetch_object($get_group_result)) {
	$selected = '';
	if ($group_row->sources == $subcampid) {
		$selected = ' selected ';
	}
	$source_options .= "<option value='$group_row->sources' $selected>$group_row->groupName</option>";
}

$source_options .= "<option value='' disabled>--------SubcampID-------</option>";

$get_subcampid = "SELECT * FROM subcampid";
$get_subcampid_result = mysql_query($get_subcampid);
echo mysql_error();
while ($subcampid_row = mysql_fetch_object($get_subcampid_result)) {
	$selected = '';
	if ($subcampid_row->subcampid == $subcampid) {
		$selected = ' selected ';
	}
	$source_options .= "<option value='$subcampid_row->subcampid' $selected>$subcampid_row->subcampid [$subcampid_row->notes]</option>";
}

// END OF GENERATING SOURCE DROP DOWN MENU

?>
<html>
<head>
<title>Subscribe Report</title>
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
	<td colspan="2" align="center"><u><b>Subscribe Report</b></u></td>
	<td><a href="/admin/subctr/unsub_report.php">Unsubscribe Report</a> | 
	<a href="/admin/subctr/unsub_by_age_report.php">Unsubscribe By Age Report</a></td>
</tr>
<tr>
	<td align="left"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to 
		<input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font style="font-size:9px;">YYYY-MM-DD</font>
	</td>
	<td>
		<select id="listid" name="listid">
			<?php echo $listid_options; ?>
		</select>
	</td>
	<td align="left"><select name="subcampid" id="subcampid"><?php echo $source_options; ?></select></td>
	<td align="right"><input type="submit" name="submit" value="Submit"></td>
</tr>
</table>
</form>

<table border='1' width='50%' align='center' cellspacing='5' cellpadding='5'>
<?php echo $report; ?>
</table>

<br><br><br><br>
<ul>
<li>Try <b>not</b> to run reports for big date range</li>
<li>If you use today's date, you will get real-time data</li>
<li>This report shows gross count. For eg: Gross Total is 10, 2 unsub, 5 dupes, leaving 3 still subscribed</li>
<li><?php echo "<b>Time:</b> ".number_format(microtime(true) - $start_time, 2, '.', '')." seconds"; ?></li>
<li><a href="manage_groups/">Manage Groups</a> &nbsp;&nbsp;
<a href="/admin/subctr/subcampid/index.php?iMenuId=349">Manage SubcampId</a></li>
</ul>
</body>
</html>
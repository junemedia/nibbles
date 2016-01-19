<?php

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
	$from_date = date('Y-m-d', strtotime('-1 days'));
}

if (ValidateDate($to_date) == false || $to_date == '') {
	$to_date = date('Y-m-d', strtotime('-1 days'));
}


$report_array = array();

$joinEmailUnsub_filter = '';
if ($list_filter !='') {
	$joinEmailUnsub_filter .= " AND listid = '$list_filter' ";
}

$joinEmailSub_filter = '';
if ($subcampid_filter !='') {
	$joinEmailSub_filter .= " AND subcampid = '$subcampid_filter' ";
}


$result = mysql_query("DELETE FROM subscriberLife WHERE authUser = '$authUser'");
echo mysql_error();
$query = "SELECT * FROM joinEmailUnsub WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59' $joinEmailUnsub_filter";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$unsubDate = $row->dateTime;
	$listid = $row->listid;
	$email = $row->email;
	$originalUnsubId = $row->id;
	
	$related_data = "SELECT * FROM joinEmailSub WHERE email=\"$email\" AND listid=\"$listid\" $joinEmailSub_filter ORDER BY id DESC LIMIT 1";
	$related_data_result = mysql_query($related_data);
	echo mysql_error();
	while ($sub_data_row = mysql_fetch_object($related_data_result)) {
		$subDate = $sub_data_row->dateTime;
		$subcampid = $sub_data_row->subcampid;
		$source = $sub_data_row->source;
		$originalSubId = $sub_data_row->id;
	}
	
	if (mysql_num_rows($related_data_result) == 1) {
		$sub_life_in_days = floor((strtotime($unsubDate) - strtotime($subDate))/(60*60*24));
		if ($sub_life_in_days < 0) { $sub_life_in_days = 0; }
		
		$insert = "INSERT IGNORE INTO subscriberLife (email,subDateTime,unsubDateTime,subcampid,source,listid,lifeDays,originalSubId,originalUnsubId,authUser) 
				VALUES (\"$email\",\"$subDate\",\"$unsubDate\",\"$subcampid\",\"$source\",\"$listid\",\"$sub_life_in_days\",\"$originalSubId\",\"$originalUnsubId\",\"$authUser\")";
		$insert_result = mysql_query($insert);
		echo mysql_error();
	}
}





$result = mysql_query("SELECT listid,title FROM joinLists WHERE isActive='Y'");
echo mysql_error();
$listing_array = array();
$title_array = array();
while ($row = mysql_fetch_object($result)) {
	array_push($listing_array, "$row->listid:$row->title");
	array_push($title_array, array("$row->listid"=>"$row->title"));
}
function lookupTitleByListId ($val) {
	global $title_array;
	for($x=0;$x<count($title_array);$x++) {
		if ($title_array[$x][$val] != '') {
			return str_replace(',',' ',$title_array[$x][$val]);
		}
	}
}
$list_drop_down_options = "<option value=''></option>";
foreach ($listing_array as $listing) {
	$pieces = explode(":", $listing);
	$list_drop_down_options .= "<option value='".$pieces[0]."'>".$pieces[1]."</option>";
}







$result = mysql_query("SELECT * FROM subcampid ORDER BY subcampid ASC");
echo mysql_error();
$all_subcampid_array = array();
$subcampid_listing_array = array();
while ($row = mysql_fetch_object($result)) {
	array_push($subcampid_listing_array, "$row->subcampid:$row->notes");
	array_push($all_subcampid_array, array("$row->subcampid"=>"$row->notes"));
}
function lookupSubcampId ($val) {
	global $all_subcampid_array;
	for($x=0;$x<count($all_subcampid_array);$x++) {
		if ($all_subcampid_array[$x][$val] != '') {
			return str_replace(',',' ',$all_subcampid_array[$x][$val]);
		}
	}
}
$subcampid_drop_down_options = "<option value=''></option>";
foreach ($subcampid_listing_array as $listing) {
	$pieces = explode(":", $listing);
	$subcampid_drop_down_options .= "<option value='".$pieces[0]."'>".$pieces[0] . " => " .$pieces[1]."</option>";
}


$all_group1 = 0;
$all_group2 = 0;
$all_group3 = 0;
$all_group4 = 0;
$all_group5 = 0;
$all_group6 = 0;
$all_group7 = 0;
$all_group8 = 0;
$all_group9 = 0;


if ($report_by == 'subcampid') {
	$query = "SELECT DISTINCT subcampid FROM subscriberLife WHERE authUser = '$authUser' ORDER BY subcampid ASC";
	$result = mysql_query($query);
	echo mysql_error();
	$subcampid_array = array();
	while ($row = mysql_fetch_object($result)) {
		array_push($subcampid_array, $row->subcampid);
	}
	
	foreach ($subcampid_array as $subcampid) {
		$group1 = 0;
		$group2 = 0;
		$group3 = 0;
		$group4 = 0;
		$group5 = 0;
		$group6 = 0;
		$group7 = 0;
		$group8 = 0;
		$group9 = 0;
		
		$query = "SELECT * FROM subscriberLife WHERE subcampid='$subcampid' AND authUser='$authUser' ORDER BY subcampid ASC";
		$result = mysql_query($query);
		echo mysql_error();
		while ($row = mysql_fetch_object($result)) {
			if ($row->lifeDays >= 0 && $row->lifeDays <= 30) {
				$group1++;
				$all_group1++;
			}
			if ($row->lifeDays >= 31 && $row->lifeDays <= 60) {
				$group2++;
				$all_group2++;
			}
			if ($row->lifeDays >= 61 && $row->lifeDays <= 90) {
				$group3++;
				$all_group3++;
			}
			if ($row->lifeDays >= 91 && $row->lifeDays <= 120) {
				$group4++;
				$all_group4++;
			}
			if ($row->lifeDays >= 121 && $row->lifeDays <= 180) {
				$group5++;
				$all_group5++;
			}
			if ($row->lifeDays >= 181 && $row->lifeDays <= 240) {
				$group6++;
				$all_group6++;
			}
			if ($row->lifeDays >= 241 && $row->lifeDays <= 300) {
				$group7++;
				$all_group7++;
			}
			if ($row->lifeDays >= 301 && $row->lifeDays <= 360) {
				$group8++;
				$all_group8++;
			}
			if ($row->lifeDays >= 361) {
				$group9++;
				$all_group9++;
			}
		}
		
		
		$report_array[] = array($subcampid,'0-30',$group1);
		$report_array[] = array($subcampid,'31-60',$group2);
		$report_array[] = array($subcampid,'61-90',$group3);
		$report_array[] = array($subcampid,'91-120',$group4);
		$report_array[] = array($subcampid,'121-180',$group5);
		$report_array[] = array($subcampid,'181-240',$group6);
		$report_array[] = array($subcampid,'241-300',$group7);
		$report_array[] = array($subcampid,'301-360',$group8);
		$report_array[] = array($subcampid,'361+',$group9);
	}
} else {
	$temp_array = array();
	$query = "SELECT DISTINCT listid FROM subscriberLife WHERE authUser = '$authUser' ORDER BY listid ASC";
	$result = mysql_query($query);
	echo mysql_error();
	$listid_array = array();
	while ($row = mysql_fetch_object($result)) {
		array_push($listid_array, $row->listid);
	}
	
	
	
	foreach ($listid_array as $list) {
		$group1 = 0;
		$group2 = 0;
		$group3 = 0;
		$group4 = 0;
		$group5 = 0;
		$group6 = 0;
		$group7 = 0;
		$group8 = 0;
		$group9 = 0;
		
		$query = "SELECT * FROM subscriberLife WHERE listid='$list' AND authUser='$authUser' ORDER BY listid ASC";
		$result = mysql_query($query);
		echo mysql_error();
		while ($row = mysql_fetch_object($result)) {
			if ($row->lifeDays >= 0 && $row->lifeDays <= 30) {
				$group1++;
				$all_group1++;
			}
			if ($row->lifeDays >= 31 && $row->lifeDays <= 60) {
				$group2++;
				$all_group2++;
			}
			if ($row->lifeDays >= 61 && $row->lifeDays <= 90) {
				$group3++;
				$all_group3++;
			}
			if ($row->lifeDays >= 91 && $row->lifeDays <= 120) {
				$group4++;
				$all_group4++;
			}
			if ($row->lifeDays >= 121 && $row->lifeDays <= 180) {
				$group5++;
				$all_group5++;
			}
			if ($row->lifeDays >= 181 && $row->lifeDays <= 240) {
				$group6++;
				$all_group6++;
			}
			if ($row->lifeDays >= 241 && $row->lifeDays <= 300) {
				$group7++;
				$all_group7++;
			}
			if ($row->lifeDays >= 301 && $row->lifeDays <= 360) {
				$group8++;
				$all_group8++;
			}
			if ($row->lifeDays >= 361) {
				$group9++;
				$all_group9++;
			}
		}
		
		
		$report_array[] = array($list,'0-30',$group1);
		$report_array[] = array($list,'31-60',$group2);
		$report_array[] = array($list,'61-90',$group3);
		$report_array[] = array($list,'91-120',$group4);
		$report_array[] = array($list,'121-180',$group5);
		$report_array[] = array($list,'181-240',$group6);
		$report_array[] = array($list,'241-300',$group7);
		$report_array[] = array($list,'301-360',$group8);
		$report_array[] = array($list,'361+',$group9);
	}
}




?>
<html>
<head>
<title>Subscriber Life Report</title>
<style>
* {
	font-family: verdana;
	font-size: 12px;
}
</style>
<script>
/*function disableItems () {
	if (document.form1.report_by[1].checked == true) {
		document.form1.subcampid_filter.disabled=false;
		document.form1.list_filter.disabled=true;
		document.form1.list_filter.value='';
	} else {
		document.form1.subcampid_filter.disabled=true;
		document.form1.list_filter.disabled=false;
		document.form1.subcampid_filter.value='';
	}
}*/
</script>
</head>
<body>
<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table align="center" border="0" cellpadding="5" cellspacing="5" width="50%">
<tr>
	<td colspan="2" align="center"><u><b>Subscriber Life Report</b></u></td>
</tr>
<tr>
	<td align="left"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to 
		<input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font size="1">e.g. <?php echo date('Y-m-d'); ?></font>
	</td>
	<td align="left"><b>Report By: </b>
		<input type="radio" name="report_by" id="report_by" value="listid" <?php if ($report_by =='' || $report_by == 'listid') { echo ' checked '; }?>> ListID
		<input type="radio" name="report_by" id="report_by" value="subcampid" <?php if ($report_by == 'subcampid') { echo ' checked '; }?>> SubcampID
	</td>
</tr>
<tr>
	<td align="left"><b>List: </b>
		<select name="list_filter" id="list_filter">
			<?php echo $list_drop_down_options; ?>
		</select>
	</td>
	<td align="left"><b>SubcampID: </b>
		<select name="subcampid_filter" id="subcampid_filter">
			<?php echo $subcampid_drop_down_options; ?>
		</select>
	</td>
</tr>
<tr>
	<td align="right" colspan="2"><input type="submit" name="submit" value="Submit"></td>
</tr>
<tr>
	<td align="center" colspan="2"><font style="color:red">
		* Please do NOT run this report for big date range.<br>
		* Never run report for more than 15 days at a time<br>
		* If date range is more than 7 days, please wait upto 5 mins<br>
	</font></td>
</tr>
</table>
</form>

<script>
//disableItems();
</script>

<br><br><br><br><br>



<?php


$export_data = '';



	$rows = array(); // associative array, key index will be row header etc etc..
	$rows[0] = array(); // initial rows would be column names, so the key will just be 0
	foreach ($report_array as $column) { // loop thru the jumbled up mess of information ONE TIME, to calculate the number of columns necessary
		if (!in_array($column[1],$rows[0])) {
			$rows[0][] = $column[1]; // append the column name
		}
	}

	// sort the column titles now
	sort($rows[0],SORT_NUMERIC);
	
	// then get count of this new column title array
	$count = count($rows[0]);
	
	// now just loop thru the $report_array array, and fill in the place values
	foreach ($report_array as $row) {
		$rowTtl = $row[0];
		$rowQty = $row[1];
		$rowVal = $row[2];
		if (!isset($rows[$rowTtl])) $rows[$rowTtl] = array_pad(array(),$count,0);
		$rows[$rowTtl][array_search($rowQty,$rows[0])] = $rowVal;
	}
	
	// display table
?>
<table align='center' border='1' cellspacing='5' cellpadding='5'>
	<?php
		$x = 0;
		foreach ($rows as $rowName => $columns) {
			if (strlen($rowName) == 3) {
				echo "<tr><td><b>".lookupTitleByListId($rowName)." [$rowName]</b></td>";
				$export_data .= lookupTitleByListId($rowName)."[$rowName],";
			} elseif (strlen($rowName) == 4) {
				echo "<tr><td><b>".lookupSubcampId($rowName)." [$rowName]</b></td>";
				$export_data .= lookupSubcampId($rowName)."[$rowName],";
				$export_data .= $cell.",";
			} else {
				echo "<tr><td>&nbsp;</td>";
				$export_data .= ",";
			}
			$y = 0;
			foreach ($columns as $cell) {
				if ($x == 0) {
					echo "<td><b>{$cell}</b></td>";
					$export_data .= $cell.",";
				} else {
					echo "<td>{$cell}</td>";
					$export_data .= $cell.",";
				}
				$y++;
			}
			echo "</tr>";
			$x++;
			$export_data .= "\n";
		}
		
		$file_name = "Subscriber_Life_$authUser.csv";
		if (!$fp = fopen(dirname(__FILE__)."/export/".$file_name, 'w')) {
			echo 'error';
		}
		fwrite($fp, $export_data);
		fclose($fp);
	?>
</table>


<div align="center">
Download: <a href="/admin/subctr/export/<?php echo $file_name; ?>" target="_blank">Download</a>
</div>

</body>
</html>

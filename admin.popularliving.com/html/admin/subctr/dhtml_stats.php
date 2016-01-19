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

if (!ctype_alnum($form_name)) {
	$form_name = '';
}

$where_clause = "";

if ($form_name != '') {
	$where_clause .= " AND form=\"$form_name\" ";
}


if ($from_date != '' && $to_date != '') {
	$where_clause .= " AND dateAdded BETWEEN \"$from_date\" AND \"$to_date\" ";
}

$query = "SELECT * FROM dhtml_stats WHERE 1=1 $where_clause ORDER BY dateAdded DESC";
$result = mysql_query($query);
echo mysql_error();
$content = '';
$display_count = 0;
$signup_count = 0;
while ($row = mysql_fetch_object($result)) {
	if ($sBgcolorClass == "#FCEDFF") {
		$sBgcolorClass = "#FFF8A3";
	} else {
		$sBgcolorClass = "#FCEDFF";
	}
	$content .= "<tr bgcolor=$sBgcolorClass>
				<td>$row->id</td>
				<td>$row->dateAdded</td>
				<td>$row->form</td>
				<td>$row->display</td>
				<td>$row->signup</td>
			</tr>";
	$display_count += $row->display;
	$signup_count += $row->signup;
}


$query = "SELECT DISTINCT form FROM dhtml_stats ORDER BY form ASC";
$result = mysql_query($query);
echo mysql_error();
$options = '';
while ($row = mysql_fetch_object($result)) {
	$selected = '';
	if ($form_name == $row->form) {
		$selected = 'selected';
	}
	$options .= "<option value='$row->form' $selected>$row->form</option>";
}


?>
<html>
<head>
<title>Dhtml Stats</title>
<style>
body {
	font-family: verdana;
}
</style>
</head>
<body>
<table align="center" border="1" cellpadding="5" cellspacing="5" width="50%">
<tr>
	<td colspan="5" align="center"><b>Dhtml Stats</b></td>
</tr>
<tr><form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<td colspan="3" align="center"><b>Date Range: </b>
		<input type="text" name="from_date" size="10" value="<?php echo $from_date; ?>" maxlength="10"> to <input type="text" value="<?php echo $to_date; ?>" name="to_date" size="10" maxlength="10"> <font size="1">e.g. <?php echo date('Y-m-d'); ?></font></td>
	<td aign="center"><select name="form_name"><option value="" <?php if ($form_name == '') { echo 'selected'; } ?>></option><?php echo $options; ?></select></td>
	<td><input type="submit" name="submit" value="Submit"></td>
	</form>
</tr>
<tr>
	<td><b>ID</b></td>
	<td><b>Date</b></td>
	<td><b>Form</b></td>
	<td><b>Display</b> <font size="1">(<?php echo $display_count; ?>)</font></td>
	<td><b>Signup</b> <font size="1">(<?php echo $signup_count; ?>)</font></td>
</tr>
<?php echo $content; ?>
<tr>
	<td><b>&nbsp;</b></td>
	<td><b>&nbsp;</b></td>
	<td><b>&nbsp;</b></td>
	<td><b><?php echo $display_count; ?></b></td>
	<td><b><?php echo $signup_count; ?></b></td>
</tr>
</table>
</body>
</html>

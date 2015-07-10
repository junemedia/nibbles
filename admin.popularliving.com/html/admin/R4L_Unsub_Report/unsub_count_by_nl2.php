<?php

while (list($key,$val) = each($_GET)) {
	$$key = $val;
}
while (list($key,$val) = each($_POST)) {
	$$key = $val;
}

if ($sNLFromDate == '') {
	$sNLFromDate = date('Y').'-'.date('m').'-'.date('d');
}
if ($sNLToDate == '') {
	$sNLToDate = date('Y').'-'.date('m').'-'.date('d');
}

mysql_pconnect ("localhost", "root", "asdf!@#$");
mysql_select_db ("nibbles_temp");

$job_id_filter = '';
if ($job_id !='') {
	$job_id_filter = " AND jobid = '$job_id' ";
}


$listId_filter = '';
if ($listId != '') {
	$listId_filter = " AND listid LIKE '%$listId%' ";
}

$total = 0;
$day_total = 0;
$sReportQuery = "SELECT issue_date, jobid, count(*) as count FROM Recipe4LivingUnsub 
				WHERE issue_date BETWEEN '$sNLFromDate 00:00:00' AND '$sNLToDate 23:59:59'
				$job_id_filter
				$listId_filter
				GROUP BY issue_date, jobid ORDER BY issue_date ASC";
$rReportResult = mysql_query($sReportQuery);
echo mysql_error();
$issue_date = '';
$sReportContent =  "<tr><td><b>Date</b></td><td><b>JobId</b></td><td><b>Count</b></td></tr>";
while ($oReportRow = mysql_fetch_object($rReportResult)) {
	if ($sBgcolorClass=="#E6E6FA") {
		$sBgcolorClass="#FFFACD";
	} else {
		$sBgcolorClass="#E6E6FA";
	}
		
	switch ($oReportRow->jobid) {
		case  392:
			$jobid = 'sc-rsvp';
			break;
		case  393:
			$jobid = 'sc-r4l';
			break;
		case  394:
			$jobid = 'sc-qerecipes';
			break;
		case  395:
			$jobid = 'budgetcooking';
			break;
		case  999:
			$jobid = 'R4L Site';
			break;
		case  396:
			$jobid = 'sc-solo';
			break;
		case  397:
			$jobid = 'sc-coreg';
			break;
		case  998:
			$jobid = 'Fit&Fab Site';
			break;
		case  411:
			$jobid = 'Fit&Fab-nl';
			break;
		case  410:
			$jobid = 'Fit&Fab-solo';
			break;
		default:
			$jobid = '';
	}
	
	if ($issue_date != $oReportRow->issue_date) {
		$issue_date = $oReportRow->issue_date;
	} else {
		$issue_date = '';
	}
	
	$sReportContent .=  "<tr bgcolor=$sBgcolorClass>
								<td><b>$issue_date</b></td>
								<td><b>$jobid</b></td>
								<td><b>$oReportRow->count</b></td></tr>";
	$total += $oReportRow->count;
	$issue_date = $oReportRow->issue_date;
}

?>

<html>
<head>
<title>R4L Unsub Stats Report</title>
<LINK rel="stylesheet" href="http://admin.popularliving.com/admin/styles.css" type="text/css" >
</head>
<body>
<center>
<table width="85%">
<tr>
<td align ="center">
<img src = "http://admin.popularliving.com/admin/nibbles_header.gif">
</td>
</tr>
</table>
</center>
<br>
<center><a href='http://admin.popularliving.com/admin/index.php?SID' class=menulink>Return to Nibbles Main Menu</a><BR><BR></center>
<table align=center width=85%><tr><td align=center class=header></td></tr></table>
<table width=85% align=center><tr><td align=left><a href=JavaScript:history.go(-1);>Back</a></td><Td align=right>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Logged In : spatel</td></tr>
<tr><Td class=message align=center colspan=2></td></tr>
</table>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<table cellpadding=5 cellspacing=0 bgcolor=	#FFE4B5 width=75% align=center>
	<tr>
			<td><b>Date From (NL Issue)</b></td>
			<td><input type="text" maxlength="10" name='sNLFromDate' value="<?php echo $sNLFromDate; ?>" size="10"> (yyyy-mm-dd)</td>
			<td><b>Date To (NL Issue)</b></td>
			<td><input type="text" maxlength="10" name='sNLToDate' value="<?php echo $sNLToDate; ?>" size="10"> (yyyy-mm-dd)</td>
	</tr>
	
	<tr><td colspan=2><b>Filter By JobId:</b> 
	<select name="job_id">
	<option value="" <?php if ($job_id == '') { echo 'selected'; } ?>>All</option>
	<option value="392" <?php if ($job_id == '392') { echo 'selected'; } ?>>sc-rsvp (392)</option>
	<option value="393" <?php if ($job_id == '393') { echo 'selected'; } ?>>sc-r4l (393)</option>
	<option value="394" <?php if ($job_id == '394') { echo 'selected'; } ?>>sc-qerecipes (394)</option>
	<option value="395" <?php if ($job_id == '395') { echo 'selected'; } ?>>budgetcooking (395)</option>
	<option value="396" <?php if ($job_id == '396') { echo 'selected'; } ?>>sc-solo (396)</option>
	<option value="397" <?php if ($job_id == '397') { echo 'selected'; } ?>>sc-coreg (397)</option>
	<option value="999" <?php if ($job_id == '999') { echo 'selected'; } ?>>R4L Site (999)</option>
	
	<option value="998" <?php if ($job_id == '998') { echo 'selected'; } ?>>Fit&Fab Site (998)</option>
	<option value="411" <?php if ($job_id == '411') { echo 'selected'; } ?>>Fit&Fab Newsletter (411)</option>
	<option value="410" <?php if ($job_id == '410') { echo 'selected'; } ?>>Fit&Fab SOLO (410)</option>
	
	<option value="432" <?php if ($job_id == '432') { echo 'selected'; } ?>>Fitness Insider (432)</option>
	<option value="433" <?php if ($job_id == '433') { echo 'selected'; } ?>>Beauty Insider (433)</option>
	</select>
	</td>
		<td colspan=2></td></tr>
		
		
		
	<tr><td colspan=2><b>Filter By ListId:</b> 
	<select name="listId">
	<option value="" <?php if ($listId == '') { echo 'selected'; } ?>>All</option>
	<option value="392" <?php if ($listId == '392') { echo 'selected'; } ?>>sc-rsvp (392)</option>
	<option value="393" <?php if ($listId == '393') { echo 'selected'; } ?>>sc-r4l (393)</option>
	<option value="394" <?php if ($listId == '394') { echo 'selected'; } ?>>sc-qerecipes (394)</option>
	<option value="395" <?php if ($listId == '395') { echo 'selected'; } ?>>budgetcooking (395)</option>
	<option value="396" <?php if ($listId == '396') { echo 'selected'; } ?>>sc-solo (396)</option>
	<option value="397" <?php if ($listId == '397') { echo 'selected'; } ?>>sc-coreg (397)</option>
	<option value="999" <?php if ($job_id == '999') { echo 'selected'; } ?>>R4L Site (999)</option>
	
	<option value="998" <?php if ($job_id == '998') { echo 'selected'; } ?>>Fit&Fab Site (998)</option>
	<option value="411" <?php if ($listId == '411') { echo 'selected'; } ?>>Fit&Fab Newsletter (411)</option>
	<option value="410" <?php if ($listId == '410') { echo 'selected'; } ?>>Fit&Fab SOLO (410)</option>
	
	<option value="432" <?php if ($listId == '432') { echo 'selected'; } ?>>Fitness Insider (432)</option>
	<option value="433" <?php if ($listId == '433') { echo 'selected'; } ?>>Beauty Insider (433)</option>
	</select>
	</td>
		<td colspan=2></td></tr>
	
	<tr><td colspan=2><input type="submit" name=sSubmit value='View Report'></td>
		<td colspan=2>&nbsp;</td></tr>
</table>
</form>
<table cellpadding=5 cellspacing=0 width=75% align=center bgcolor=#FFFFFF border="1">
<tr><td colspan="2">Date Range: <?php echo $sNLFromDate; ?> to <?php echo $sNLToDate; ?></td><td><b>Total: <?php echo $total; ?></b></td></tr>
<?php echo $sReportContent; ?>
</table>
</body>
</html>

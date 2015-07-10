<?php

if ($_GET['sExportExcel'] == 'Y') {
	include("../../includes/paths.php");
	include("$sGblIncludePath/reportInclude.php");
}

while (list($key,$val) = each($_GET)) {
	$$key = $val;
}
while (list($key,$val) = each($_POST)) {
	$$key = $val;
}

mysql_pconnect ("localhost", "root", "asdf!@#$");
mysql_select_db ("nibbles_temp");


$report_notes = "";

$count = 0;
$list_of_emails = '';
if ($sSubmit) {
	
	$listId_filter = '';
	if ($listId != '') {
		$listId_filter = "AND listid LIKE '%$listId%'";
	}
	
	
	$job_id_filter = '';
	if ($job_id !='') {
		$job_id_filter = "AND jobid = '$job_id'";
	}
	
	
	
	$unsub_date_filter = '';
	if ($sFromDate !='' && $sToDate !='') {
		$unsub_date_filter = "AND dateTimeAdded BETWEEN '$sFromDate 00:00:00' AND '$sToDate 23:59:59'";
	}
	
	
	$issue_date_filter = '';
	if ($sNLFromDate !='' && $sNLToDate !='') {
		$issue_date_filter = "AND issue_date BETWEEN '$sNLFromDate 00:00:00' AND '$sNLToDate 23:59:59'";
	}
	
	
	
	$domain_filter = '';
	if ($domain !='') {
		$domain_filter = "AND email LIKE '%$domain'";
	}
	
	
	
	
	$sReportQuery = "SELECT * FROM Recipe4LivingUnsub WHERE 1 = 1 
				$unsub_date_filter 
				$issue_date_filter 
				$listId_filter  
				$job_id_filter  
				$domain_filter  
				$order_report_by ";
	
	echo "<!-- Report Query: ".$sReportQuery." -->";
	
	$report_notes = "Report For (Unsub: $sFromDate - $sToDate) (NL Issue: $sNLFromDate - $sNLToDate)";
	
	$rReportResult = mysql_query($sReportQuery);
	$sReportContent =  "<tr>
								<td><b>Email</b></td>
								<td><b>List</b></td>
								<td><b>IP</b></td>
								<td><b>Date (Unsub)</b></td>
								<td><b>Issue (NL Issue)</b></td>
								<td><b>JobId</b></td>
							</tr>";
	echo mysql_error();
	$sExportData = '';
	$sExportHeader = '';
	while ($oReportRow = mysql_fetch_object($rReportResult)) {
		$count++;
		
		if ($sBgcolorClass=="#E6E6FA") {
			$sBgcolorClass="#FFFACD";
		} else {
			$sBgcolorClass="#E6E6FA";
		}
		
		if ($oReportRow->jobid == '0') {
			$jobid = '';
		} else {
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
		}
		
		
		if ($oReportRow->issue_date == '0000-00-00') {
			$issue_date = '';
		} else {
			$issue_date = $oReportRow->issue_date;
		}
		
		$sReportContent .=  "<tr bgcolor=$sBgcolorClass>
								<td>$oReportRow->email<!-- ID: $oReportRow->id --></td>
								<td>$oReportRow->listid</td>
								<td>$oReportRow->ip</td>
								<td>$oReportRow->dateTimeAdded</td>
								<td>$issue_date</td>
								<td>$jobid</td>
							</tr>";
		$list_of_emails .= "$oReportRow->email
";
		
		if ($sExportExcel) {
			$sExportHeader = "Email\tList\tIP\tDate Unsub\tIssue NL Date\tJobId\t\n";
			$sExportData .= "$oReportRow->email\t$oReportRow->listid\t$oReportRow->ip\t$oReportRow->dateTimeAdded\t$issue_date\t$jobid\t\n";
		}
	}
	
	if ($sExportExcel) {
		$sExportData = $sExportHeader.$sExportData."\n\n\n";
		$unique_value = md5(uniqid(mt_rand(), true));
		$sFileName = "Unsub_".$unique_value.".xls";
		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExportData, strlen($sExportData));
			fclose($rFpFile);
			echo "<script language=JavaScript>
			void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
		  </script>";
		}
	}
	
	
	
	
	
} else {
	if ($sFromDate == '') {
		$sFromDate = date('Y').'-'.date('m').'-'.date('d');
	}
	if ($sToDate == '') {
		$sToDate = date('Y').'-'.date('m').'-'.date('d');
	}
	if ($sNLFromDate == '') {
		$sNLFromDate = date('Y').'-'.date('m').'-'.date('d');
	}
	if ($sNLToDate == '') {
		$sNLToDate = date('Y').'-'.date('m').'-'.date('d');
	}
}

$sExportExcelChecked = '';
if ($sExportExcel) {
	$sExportExcelChecked = "checked";
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
			<td><b>Date From (Unsub)</b></td>
			<td><input type="text" maxlength="10" name='sFromDate' value="<?php echo $sFromDate; ?>" size="10"> (yyyy-mm-dd)</td>
			<td><b>Date To (Unsub)</b></td>
			<td><input type="text" maxlength="10" name='sToDate' value="<?php echo $sToDate; ?>" size="10"> (yyyy-mm-dd)</td>
	</tr>
	
	
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
	
	
	
	<tr><td colspan=2><b>Filter By List:</b> 
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
		<td colspan=2>
		<input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel (<b>Disable Popup Blocker</b>)
		</td></tr>
		
		

		
		
		<tr><td colspan=2><b>Sort Report By:</b> 
	<select name="order_report_by">
	<option value="ORDER BY dateTimeAdded ASC" <?php if ($order_report_by == 'ORDER BY dateTimeAdded ASC') { echo 'selected'; } ?>>Unsub Date Ascending Order</option>
	<option value="ORDER BY dateTimeAdded DESC" <?php if ($order_report_by == '' || $order_report_by == 'ORDER BY dateTimeAdded DESC') { echo 'selected'; } ?>>Unsub Date Descending Order</option>
	<option value="ORDER BY issue_date ASC" <?php if ($order_report_by == 'ORDER BY issue_date ASC') { echo 'selected'; } ?>>Issue Date Ascending Order</option>
	<option value="ORDER BY issue_date DESC" <?php if ($order_report_by == 'ORDER BY issue_date DESC') { echo 'selected'; } ?>>Issue Date Descending Order</option>
	<option value="ORDER BY email ASC" <?php if ($order_report_by == 'ORDER BY email ASC') { echo 'selected'; } ?>>Email Ascending Order</option>
	<option value="ORDER BY email DESC" <?php if ($order_report_by == 'ORDER BY email DESC') { echo 'selected'; } ?>>Email Descending Order</option>
	<option value="ORDER BY ip ASC" <?php if ($order_report_by == 'ORDER BY ip ASC') { echo 'selected'; } ?>>IP Ascending Order</option>
	<option value="ORDER BY ip DESC" <?php if ($order_report_by == 'ORDER BY ip DESC') { echo 'selected'; } ?>>IP Descending Order</option>
	</select>
	</td>
		<td colspan=2></td></tr>
		
		
		
		
		
	
	
	<tr><td colspan=2><b>Domain Filter:</b> 
	<input type="text" name="domain" id="domain" value="<?php echo $domain; ?>"> &nbsp;e.g. gmail.com
	</td>
		<td colspan=2></td></tr>
		
		
	<tr><td colspan=2><input type="submit" name=sSubmit value='View Report'></td>
		<td colspan=2><a href='unsub_count_by_nl2.php' target="_blank">Report By JobId</a></td></tr>
</table>


<table cellpadding=5 cellspacing=0 bgcolor=	#FFE4B5 width=75% align=center>
	<tr>
		<td colspan=4 align="center"><font color="Red" size="3"><b>Total # of Records:
		<?php echo $count; ?><br><br><?php echo $report_notes; ?></b></font>
		</td>
	</tr>
</table>
</form>


<table cellpadding=5 cellspacing=0 width=75% align=center bgcolor=#FFFFFF>
<?php echo $sReportContent; ?>
</table>

<br><br>
<!--
<?php echo $list_of_emails; ?>
-->
<br><br>

</body>
</html>

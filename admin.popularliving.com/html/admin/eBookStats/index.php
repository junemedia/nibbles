<?php

while (list($key,$val) = each($_GET)) {
	$$key = $val;
}
while (list($key,$val) = each($_POST)) {
	$$key = $val;
}
mysql_pconnect ("mydb01.amperemedia.com", "nibbles", "#a!!yu5");
mysql_select_db ('nibbles_r4l');

if ($sFromDate == '') {
	$sFromDate = date('Y').'-'.date('m').'-'.date('d');
}
if ($sToDate == '') {
	$sToDate = date('Y').'-'.date('m').'-'.date('d');
}

$page_filter = '';
if ($page !='') {
	$page_filter = " AND capture_page LIKE '%$page%'";
}

$subid_filter = '';
if ($subid !='') {
	$subid_filter = " AND subid LIKE '%$subid%'";
}

$sReportContent = "";
$sReportQuery = "SELECT * FROM eBook WHERE  dateTimeAdded BETWEEN '$sFromDate 00:00:00' AND '$sToDate 23:59:59' $page_filter $subid_filter ";
$rReportResult = mysql_query($sReportQuery);
$iCount = 0;
while ($oReportRow = mysql_fetch_object($rReportResult)) {
	$temp_page = str_replace('/cookbooks/','',$oReportRow->capture_page);
	$temp_page = str_replace('.php','',$temp_page);
	
	$sReportContent .=  "<tr><td align='left'>$oReportRow->dateTimeAdded</td>
						<td nowrap align='left'>$oReportRow->email</td>
						<td nowrap align='left'>$oReportRow->subid</td>
						<td align='left'>$temp_page</td>
					</tr>";
	$iCount++;
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
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Logged In : <?php echo $_SERVER['PHP_AUTH_USER']; ?></td></tr>
<tr><Td class=message align=center colspan=2></td></tr>
</table>
<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr>
			<td><b>Date From</b></td>
			<td><input type="text" maxlength="10" name='sFromDate' value="<?php echo $sFromDate; ?>" size="10"> (yyyy-mm-dd)</td>
			<td><b>Date To</b></td>
			<td><input type="text" maxlength="10" name='sToDate' value="<?php echo $sToDate; ?>" size="10"> (yyyy-mm-dd)</td>
	</tr>
	<tr>
			<td><b>Page: </b></td>
			<td colspan="3"><input type="text" maxlength="50" name='page' value="<?php echo $page; ?>" size="20"></td>
	</tr>
	<tr>
			<td><b>SubID: </b></td>
			<td colspan="3"><input type="text" maxlength="50" name='subid' value="<?php echo $subid; ?>" size="20"></td>
	</tr>
	<tr><td colspan=2><input type="submit" name=sSubmit value='View Report'></td>
	<td colspan=2></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=4 class=bigHeader align=center><BR>eBook Stats Report<BR>From <?php echo "$sFromDate to $sToDate";?><br>
	<br></td></tr>
	<tr><td class='header' align="left">Date</td>
		<td class='header' align="left">Email</td>
		<td class='header' align="left">SubId</td>
		<td class='header' align="left">Page</td>
	</tr>
	<Tr><td colspan=4><hr color=#000000></td></tR>
	<?php echo $sReportContent;?>
	<Tr><td colspan=4><hr color=#000000></td></tR>
	<Tr><td colspan=4>Total: <?php echo $iCount; ?></td></tR>
	</table></td></tr></table></td></tr>
	</table>
</form>


</body>
</html>

<?php

while (list($key,$val) = each($_GET)) {
	$$key = $val;
}
while (list($key,$val) = each($_POST)) {
	$$key = $val;
}

define('DB_NAME', 'chewonthat');    // The name of the database
define('DB_USER', 'chewonthat');     // Your MySQL username
define('DB_PASSWORD', 'Ch3w0nyeah'); // ...and password
define('DB_HOST', 'mydb01.amperemedia.com');    // 99% chance you won't need to change this value

mysql_connect (DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db (DB_NAME);

if ($sFromDate == '') {
	$sFromDate = date('Y').'-'.date('m').'-'.date('d');
	$sSubmit = true;
}
if ($sToDate == '') {
	$sToDate = date('Y').'-'.date('m').'-'.date('d');
	$sSubmit = true;
}

if ($sSubmit) {
	$sReportQuery = "SELECT * FROM chewonthat.contest WHERE dateTimeAdded BETWEEN '$sFromDate 00:00:00' AND '$sToDate 23:59:59'";
	$rReportResult = mysql_query($sReportQuery);
	$sReportContent =  "<tr>
								<td><b>Email</b></td>
								<td><b>First</b></td>
								<td><b>Last</b></td>
								<td><b>Address</b></td>
								<td><b>Address2</b></td>
								<td><b>City</b></td>
								<td><b>State</b></td>
								<td><b>Zip</b></td>
								<td><b>Phone</b></td>
								<td><b>Date</b></td>
							</tr>";
	echo mysql_error();
	while ($oReportRow = mysql_fetch_object($rReportResult)) {
		if ($sBgcolorClass=="#E6E6FA") {
			$sBgcolorClass="#FFFACD";
		} else {
			$sBgcolorClass="#E6E6FA";
		}
		$sReportContent .=  "<tr bgcolor=$sBgcolorClass>
								<td>$oReportRow->email<!-- IP: $oReportRow->ip --></td>
								<td>$oReportRow->first</td>
								<td>$oReportRow->last</td>
								<td>$oReportRow->address</td>
								<td>$oReportRow->address2</td>
								<td>$oReportRow->city</td>
								<td>$oReportRow->state</td>
								<td>$oReportRow->zip</td>
								<td>$oReportRow->phone</td>
								<td>$oReportRow->dateTimeAdded</td>
							</tr>";
	}
}


?>

<html>
<head>
<title>Chew On That Contest Entries</title>
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
<table cellpadding=5 cellspacing=0 bgcolor=	#FFE4B5 width=95% align=center>
	<tr>
			<td><b>Date From</b></td>
			<td><input type="text" maxlength="10" name='sFromDate' value="<?php echo $sFromDate; ?>" size="10"> (yyyy-mm-dd)</td>
			<td><b>Date To</b></td>
			<td><input type="text" maxlength="10" name='sToDate' value="<?php echo $sToDate; ?>" size="10"> (yyyy-mm-dd)</td>
	</tr>
	<tr><td colspan=2><input type="submit" name=sSubmit value='View Report'></td>
		<td colspan=2></td></tr>
</table>
</form>
<table cellpadding=5 cellspacing=0 width=95% align=center bgcolor=#FFFFFF>
<?php echo $sReportContent; ?>
</table>
</body>
</html>
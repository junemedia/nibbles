<?php

/*
Runs from web browser.

Prompts for id and password.

If match shows report, date wise and filterable for successes and
failures on checks. 
*/


include("config.php");

session_start();

if ($sUserSubmit) {
	$sUserQuery = "SELECT *
					FROM   users
					WHERE  id = '$iUserId'
					AND	   passwd = '$sPasswd' ";
	$rUserResult = mysql_query($sUserQuery);
	echo mysql_error();
	while ($oUserRow = mysql_fetch_object($rUserResult)) {
		$_SESSION['sSesUserId'] = $oUserRow->id;
	}
}

if ($_SESSION['sSesUserId'] != '') {
	
	// show report
	$sPageData = "<form action = '$PHP_SELF'>
					<table>
					<tr><td>Date From </td><td><input type=text name=sDateFrom value='$sDateFrom'></td>
						<td>Date To </td><td><input type=text name=sDateTo value='$sDateTo'></td></tr>
				  <tr><Td></td><td><input type=submit name=sViewReport value='View Report'></td></tr>
				  </table>
				  <table border=1>
					<tr><td><b>Date Time</b></td>
						<td><b>URL</b></td>
						<td><b>Ping</b></td>
						<td><b>Test String</b></td></tr>";
	
	$sReportQuery = "SELECT *
					 FROM   history
					 WHERE  (ping = 'false'
					 OR     testString = 'false')";
	
	if ($sDateFrom == '' || $sDateTo == '') {
		$sReportQuery .= " AND  date_format(dateTimeTested,'%Y-%m') = date_format(CURRENT_DATE,'%Y-%m') ";		
	} else {
		$sReportQuery .= " AND  date_format(dateTimeTested,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo' ";
	}
	
	$sReportQuery .= " ORDER BY dateTimeTested DESC";
	$rReportResult = mysql_query($sReportQuery);
	while ($oReportRow = mysql_fetch_object($rReportResult)) {
	
		$sPageData .= "<tr><td>$oReportRow->dateTimeTested</td>
							<td>$oReportRow->url</td>
							<td>$oReportRow->ping</td>
							<td>$oReportRow->testString</td></tr>";
		
	}
	
	$sPageData .= "</table></form>";
		
} else {
	// show login form
	
	$sPageData = "
<form action='$PHP_SELF'>
<input type=text name=iUserId value='$iUserId'>
<input type=password name=sPasswd value=''>
<input type=submit name=sUserSubmit value = 'Submit'>
</form>";

}

echo $sPageData;
?>

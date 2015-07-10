<?php

include_once("/var/www/html/admin.popularliving.com/html/includes/paths.php");

$first_of_last_month = date('Y-m-d',mktime(0,0,0,date('m')-1,1,date('Y')));
$end_of_last_month = date('Y-m-d',mktime(0,0,0,date('m'),0,date('Y')));

$query = "SELECT * FROM pointsBookStats 
		WHERE dateTimeTaken BETWEEN '$first_of_last_month' AND '$end_of_last_month'
		ORDER BY dateTimeTaken ASC";
$result = mysql_query($query);
echo mysql_error();


$reportData = "<table align='center' width='500px' border='1' style='font-face:verdana;font-size:12px;'>
			<tr><td><b>Date</b></td><td><b>Page View</b></td><td><b>PDF Download</b></td><td><b>Page</b></td></tr>";
$offerTaken = 0;
$pageDisplayed = 0;
while($row = mysql_fetch_object($result)) {
	$reportData .= "<tr>
					<td>$row->dateTimeTaken</td>
					<td>$row->pageDisplayed</td>
					<td>$row->offerTaken</td>
					<td>$row->page</td>
					</tr>";
	$offerTaken += $row->offerTaken;
	$pageDisplayed += $row->pageDisplayed;
}

$reportData .= "<tr><td><b>Total:</b> </td><td><b>$pageDisplayed</b></td><td><b>$offerTaken</b></td><td>&nbsp;</td></tr></table>";

$sEmailTo = '';
$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
$sHeaders .= "cc: ";

$rEmailResult = dbQuery("SELECT * FROM   emailRecipients WHERE  purpose = 'pointsBookStats'");
echo mysql_error();
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}

if (!($sEmailTo)) {
	$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
}


$sCcTo = substr($sRecipients,strlen($sEmailTo));
$sHeaders .= ", $sCcTo";
$sHeaders .= "\r\n";
$sSubject = "Points Book Stats Report - $first_of_last_month to $end_of_last_month";

mail($sEmailTo, $sSubject, $reportData, $sHeaders);


?>

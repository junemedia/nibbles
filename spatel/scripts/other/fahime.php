<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include_once("$sGblLibsPath/dateFunctions.php");

$sLastFriday = DateAdd("d", -7, date('Y')."-".date('m')."-".date('d'));
$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
$sExportTableName = "list_orders_fahime_".date('m').date('d').date('Y');
$sFileName = date('m').date('d').date('Y').'.csv';
$iNumRecords = 0;


$rEmailResult = dbQuery("SELECT * FROM emailRecipients WHERE purpose = 'fahime'");
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}


$rDropTableResult = dbQuery("DROP TABLE IF EXISTS listOrders.$sExportTableName");

$sQuery = " CREATE TABLE listOrders.$sExportTableName AS SELECT n.first, n.last, n.address, n.address2, n.city, n.state, n.zip
			FROM nibbles.userDataHistory AS n				
			WHERE date_format(n.dateTimeAdded,'%Y-%m-%d') BETWEEN '$sLastFriday' AND '$sYesterday'
			LIMIT 0, 15000 ";
$rResult = dbQuery($sQuery);

$rFile = fopen("/home/fahime/$sFileName","w");
if ($rFile) {
	$sExportQuery = "SELECT * FROM listOrders.$sExportTableName";
	$rExportResult =  dbQuery($sExportQuery);
	while ($oExportRow = dbFetchObject($rExportResult)) {
		$sExportData = "\"$oExportRow->first\",\"$oExportRow->last\",\"$oExportRow->address\",";
		$sExportData .= "\"$oExportRow->address2\",\"$oExportRow->city\",\"$oExportRow->state\",\"$oExportRow->zip\"\r\n";
		$sTemp = fwrite($rFile, $sExportData);
		$iNumRecords++;
	}
}


// set up basic connection && login with username and password
$sConnection_Id = ftp_connect('12.110.246.122');
$sLoginResult = ftp_login($sConnection_Id, 'myfree', 'myf-6789');
ftp_pasv($sConnection_Id, false);	// turn off passive mode so active mode will be turned on

// check connection
if (!$sConnection_Id) {
	$sEmailMessage = "FTP connection has failed!\n\nAttempted to connect to 12.110.246.122 for user myfree\n\n";
	mail($sRecipients, 'Fahime FTP Failed', $sEmailMessage , "From: it@amperemedia.com\r\n");
} else {
	// upload a file
	if (ftp_put($sConnection_Id, "$sFileName", "/home/fahime/"."$sFileName", FTP_ASCII)) {
		mail($sRecipients, "Fahime Count - ".date('m').'/'.date('d').'/'.date('Y').' : '.$iNumRecords, '', "From: it@amperemedia.com\r\n");
	} else {
		mail($sRecipients, 'Fahime FTP Failed', "There was a problem while uploading fahime file." , "From: it@amperemedia.com\r\n");
	}
	ftp_close($sConnection_Id);	// close the FTP stream
}

// 00 06 * * 5 php /home/scripts/other/fahime.php

?>

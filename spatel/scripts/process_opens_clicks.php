<?php

include_once("/home/spatel/config.php");



/*

START DATA CLEANUP

Move data older than 3 days from opensClicksRawData to opensClicksRawDataCurrent
START MOVING DATA FROM CURRENT TABLE TO ARCHIVE TABLE

*/


/*
$three_days_ago = date("Y-m-d", strtotime("-1 day"));
$count = 0;
$query = "SELECT * FROM opensClicksRawData WHERE dateAdded < '$three_days_ago';";
$result = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($result)) {
        $insert = "INSERT LOW_PRIORITY IGNORE INTO opensClicksRawDataCurrent (dateAdded,email,custId,adMapId,ipAddr,listId,jobId,vmtaId,target,subcampid,joinDate)
                VALUES(\"$oRow->dateAdded\",\"$oRow->email\",\"$oRow->custId\",\"$oRow->adMapId\",\"$oRow->ipAddr\",\"$oRow->listId\",\"$oRow->jobId\",\"$oRow->vmtaId\",\"$oRow->target\",\"$oRow->subcampid\",\"$oRow->joinDate\")";
        $insert_result = mysql_query($insert);
        echo mysql_error();

        if ($insert_result) {
                $delete = "DELETE FROM opensClicksRawData WHERE id = '$oRow->id'";
                $delete_result = mysql_query($delete);
                echo mysql_error();
        }
        
        if ($count % 500 == 0) {
			sleep(1);
		}
		$count++;
}*/



/*

END OF DATA CLEANUP

*/


$date_file = date("Y-m-d", strtotime("-1 day"));

$success = true;
$error_note = '';
$body = '';

// path to remote file
$remote_file = "/adredirlogs/$date_file.csv";
$local_file = "$date_file.csv";

$ftp_user_name = 'af2964';
$ftp_server = 'www.arcamax.com';
$ftp_user_pass = 'gd5axVtG';

// open some file to write to
$handle = fopen($local_file, 'w');
if (!$handle) {
	$body .= "Unable to create new file on local host to download file from Arcamax FTP server.\n\n\n";
	$success = false;
}

// set up basic connection
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
	$body .= "Cannot connect to FTP server.\n\n\n";
	$success = false;
}

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if (!$login_result) {
	$body .= "Unable to login to Arcamax FTP server.\n\n\n";
	$success = false;
}

ftp_pasv($conn_id, true);

// try to download $remote_file and save it to $handle
if (!ftp_fget($conn_id, $handle, $remote_file, FTP_ASCII, 0)) {
	$body .= "There was a problem while downloading $remote_file to $local_file\n\n\n";
	$success = false;
}

//echo $body;
// close the connection and the file handler
ftp_close($conn_id);
fclose($handle);

$total_count = 0;
// get contents of a file into a string
$handle = fopen($local_file, "r");
if ($handle) {
	while (!feof($handle)) {
		$sLine = fgets($handle, 1024);
		$sLine = str_replace('"','',$sLine);
		$pieces = explode(',', $sLine);
		if (count($pieces) > 1) {
			$Email = trim($pieces[0]);
			$CustId = trim($pieces[1]);
			$AdMapId = trim($pieces[2]);
			$IPAddr = trim($pieces[3]);
			$ListId = trim($pieces[4]);
			$JobId = trim($pieces[5]);
			$VMTAId = trim($pieces[6]);
			$Target = trim($pieces[7]);
			
			$subcampid = '';
			$joinDate = '';
			$get_details = mysql_query("SELECT DATE(dateTime) AS joinDate,subcampid FROM joinEmailSub WHERE email=\"$Email\" AND listid=\"$ListId\" ORDER BY id DESC LIMIT 1");
			while ($details_row = mysql_fetch_object($get_details)) {
				$subcampid = $details_row->subcampid;
				$joinDate = $details_row->joinDate;
			}
			
			if ($subcampid == '') {
				$get_details = mysql_query("SELECT DATE(dateTime) AS joinDate,subcampid FROM joinEmailActive WHERE email=\"$Email\" AND listid=\"$ListId\"");
				while ($details_row = mysql_fetch_object($get_details)) {
					$subcampid = $details_row->subcampid;
					$joinDate = $details_row->joinDate;
				}
			}
			
			
			if ($Email != 'Email') {	// skip header
				$insert = "INSERT LOW_PRIORITY IGNORE INTO opensClicksRawData (dateAdded,email,custId,adMapId,ipAddr,listId,jobId,vmtaId,target,subcampid,joinDate)
							VALUES (\"$date_file\",\"$Email\",\"$CustId\",\"$AdMapId\",\"$IPAddr\",\"$ListId\",\"$JobId\",\"$VMTAId\",\"$Target\",\"$subcampid\",\"$joinDate\");";
				$result = mysql_query($insert);

				if (!$result) {
					$body .= "Insert Query Failed: $insert . Error: ".mysql_error()."\n\n\n";
					$success = false;
				}
				
				$total_count++;
			}
			
			if ($total_count % 100 == 0) {
				sleep(1);
			}
		}
	}
	// delete file once processing is done.
	unlink($local_file);
} else {
	$body .= "Cannot read downloaded file to process opens/clicks.\n\n\n";
	$success = false;
}
fclose($handle);

if ($success == false) {
	$error_note = '***ERROR Processing opens/clicks File *** web02.am.tld - Anytime after 3 hours, please re-run /home/spatel/scripts/process_opens_clicks.php';
}

$body .= "\n\n\n$error_note\n\n\nTotal: $total_count\n\n\n";


$subject = "Opens/Clicks Report ".$date_file;

$header = "From:Subscription Center Admin <admin@myfree.com>";

mail("samirp@junemedia.com,leonz@junemedia.com",$subject,$body,$header);

mysql_close();

?>

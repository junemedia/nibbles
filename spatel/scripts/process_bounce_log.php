<?php

include_once("/home/spatel/config.php");
$process_bounce_log_email = "samirp@junemedia.com,leonz@junemedia.com";

/*

LOOK FOR SAME DAY FILE

*/


$ten_days_ago = date("Y-m-d", strtotime("-10 day"));
$date_file = date('Ymd');
$soft_bounce_count = 0;
$hard_bounce_count = 0;
$total_count = 0;
$success = true;
$error_note = '';
$body = '';

// path to remote file
$remote_file = "/BounceLogs/site04-$date_file.bnclog";
$local_file = "site04-$date_file.txt";

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

// close the connection and the file handler
ftp_close($conn_id);
fclose($handle);


// get contents of a file into a string
$handle = fopen($local_file, "r");
if ($handle) {
	while (!feof($handle)) {
		$sLine = fgets($handle, 1024);
		$pieces = explode('	', $sLine);
		if (count($pieces) > 1) {
			$date_time = trim($pieces[0]);
			$listid = trim($pieces[2]);
			$jobid = trim($pieces[3]);
			$email = trim($pieces[4]);
			
			$split_error = explode(':', trim($pieces[1]));
			$type = $split_error[0];
			$reason = $split_error[1];
			
			$insert = "INSERT INTO bounceLog (dateTimeAdded, bounceDate,type,reason,listid,jobid,email)
						VALUES (NOW(),\"$date_time\",\"$type\",\"$reason\",\"$listid\",\"$jobid\",\"$email\")";
			$result = mysql_query($insert);
			if (!$result) {
				$body .= "Insert Query Failed: $insert . Error: ".mysql_error()."\n\n\n";
				$success = false;
			}
			
			if ($type == 'softbounce') {
				$soft_bounce_count++;
			} elseif ($type == 'hardbounce') {
				$hard_bounce_count++;
			}
			$total_count++;
		}
	}
	// delete file once processing is done.
	unlink($local_file);
} else {
	$body .= "Cannot read download file to process Hard/Soft bounces.\n\n\n";
	$success = false;
}
fclose($handle);

if ($success == false) {
	$error_note = '***ERROR Processing Soft/Hard Bounce File. Have Nazar or Will or Samir re-run this script (anytime after 3 hours) on web02.am.tld:/home/spatel/scripts/process_bounce_log.php *** ';
}

$body .= "\n\n\n$error_note\n\n\nTotal: $total_count\n\n\n
Soft Bounce: $soft_bounce_count\n\n\n
Hard Bounce: $hard_bounce_count\n\n\n";


$subject = "Bounce Log Report ".date('Y-m-d');

$header = "From:Subscription Center Admin <admin@myfree.com>";

mail($process_bounce_log_email,$subject,$body,$header);

echo 'done';

mysql_close();

?>

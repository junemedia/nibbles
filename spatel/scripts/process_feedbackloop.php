<?php

include_once("/home/spatel/config.php");
$feed_back_loop_report = "samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com";

/*

LOOK FOR PREVIOUS DAY FILE

*/

$yesterday = date("Ymd", strtotime("-1 day"));
$yesterday_with_dash = date("Y-m-d", strtotime("-1 day"));
$success = true;
$body = '';

// path to remote file
$remote_file = "/FeedBackLoopLogs/site04-$yesterday.fbllog";
$local_file = "site04-$yesterday.txt";

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
			$email = trim($pieces[1]);
						
			$dt_only = explode(" ", trim($pieces[0]));
			$pieces = explode("/", $dt_only[0]);
			$yyyy = $pieces[2];
			$mm = $pieces[0];
			$dd = $pieces[1];
			
			if (strlen($mm) == 1) { $mm = '0'.$mm; }
			if (strlen($dd) == 1) {	$dd = '0'.$dd; }
			
			$date = $yyyy.'-'.$mm.'-'.$dd;
			
			$insert = "INSERT IGNORE INTO feedBackLoop (dateTimeAdded, feedBackDate, email)
						VALUES (NOW(), \"$date\", \"$email\")";
			$result = mysql_query($insert);
			if (!$result) {
				$body .= "Insert Query Failed: $insert . Error: ".mysql_error()."\n\n\n";
				$success = false;
			}
		}
	}
	unlink($local_file);	// delete file once processing is done.
} else {
	$body .= "Cannot read download file to process feed back loop.\n\n\n";
	$success = false;
}
fclose($handle);

$results = mysql_query("SELECT * FROM feedBackLoop WHERE feedBackDate = '$yesterday_with_dash'");
echo mysql_error();
$total_count = mysql_num_rows($results);
while ($row = mysql_fetch_object($results)) {
	$email = $row->email;
	
	$get_listid_result = mysql_query("SELECT DISTINCT listid FROM joinEmailActive WHERE email = \"$email\"");
	echo mysql_error();
	
	// only process this email if the user is currently subscribed.
	if (mysql_num_rows($get_listid_result) > 0) {
		$listid_array = array();
		while ($get_listid_row = mysql_fetch_object($get_listid_result)) {
			array_push($listid_array, $get_listid_row->listid);
		}
		
		// delete from joinEmailActive - DELETE THIS ONLY AFTER GETTING CURRENT LISTID
		$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\"";
		$delete_query_result = mysql_query($delete_query);
		if (!$delete_query_result) {
			$body .= "Delete Query Failed: $delete_query . Error: ".mysql_error()."\n\n\n";
			$success = false;
		}
		
		foreach ($listid_array as $listid) {
			// insert into joinEmailUnsub
			$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,listid,source,errorCode)
						VALUES (NOW(),\"$email\",\"$listid\",\"FeedBackLoop\",\"FeedBackLoop\")";
			$insert_query_result = mysql_query($insert_query);
			if (!$insert_query_result) {
				$body .= "Insert Query Failed: $insert_query . Error: ".mysql_error()."\n\n\n";
				$success = false;
			}
		}
	}
}


$error_note = '';
if ($success == false) {
	$error_note = '***ERROR Processing Feed Back Loop File. Have Nazar or Will or Samir re-run this script (anytime after 3 hours) on web02.am.tld:/home/spatel/scripts/process_feedbackloop.php *** ';
}

$body .= "$error_note\n\n\nTotal (Unique emails): $total_count\n\n\n";

$subject = "Feed Back Loop Report ".date('Y-m-d');

$header = "From:Subscription Center Admin <admin@myfree.com>";

mail($feed_back_loop_report,$subject,$body,$header);

echo 'done';

mysql_close();

?>

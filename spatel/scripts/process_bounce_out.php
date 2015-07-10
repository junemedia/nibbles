<?php

include_once("/home/spatel/config.php");
$bounce_out_report = "samirp@junemedia.com,leonz@junemedia.com,williamg@junemedia.com";
//$bounce_out_report = "samirp@silvercarrot.com";

/*

LOOK FOR PREVIOUS DAY FILE

*/

$yesterday = date("Ymd", strtotime("-1 day"));
$yesterday_with_dash = date("Y-m-d", strtotime("-1 day"));
$success = true;
$body = '';

// path to remote file	site04-20101112.bouncers
$remote_file = "/BounceOuts/site04-$yesterday.bouncers";
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
			//	11/2/2010	sallyglennmont@charter.net	1000	393:2651,394:2651,396:2651,397:2651
			
			$part = explode("/", trim($pieces[0]));
			$yyyy = $part[2];
			$mm = $part[0];
			$dd = $part[1];
			if (strlen($mm) == 1) { $mm = '0'.$mm; }
			if (strlen($dd) == 1) {	$dd = '0'.$dd; }
			$date = $yyyy.'-'.$mm.'-'.$dd;
			
			
			$email = trim($pieces[1]);
			
			$type = '';
			if (trim($pieces[2]) == '1000' || trim($pieces[2]) == '10000') {
				$type = 'hardbounce';
			}
			if (trim($pieces[2]) == '20') {
				$type = 'softbounce';
			}
			
			// look up reason if it is available in bounce log table.  if not, set reason to blank.
			$reason = '';
			$get_reason = mysql_query("SELECT reason FROM bounceLog WHERE email = \"$email\" ORDER BY id DESC LIMIT 1");
			if (mysql_num_rows($get_reason) > 0) {
				$reason_row = mysql_fetch_object($get_reason);
				$reason = $reason_row->reason;
			}
			
			$insert = "INSERT IGNORE INTO bounceOut (dateTimeAdded, bounceDate, email, type, reason)
						VALUES (NOW(), \"$date\", \"$email\", \"$type\", \"$reason\")";
			$result = mysql_query($insert);
			if (!$result) {
				$body .= "Insert Query Failed: $insert . Error: ".mysql_error()."\n\n\n";
				$success = false;
			}
		}
	}
	unlink($local_file);	// delete file once processing is done.
} else {
	$body .= "Cannot read download file to $local_file.\n\n\n";
	$success = false;
}
fclose($handle);






// DO NOT UNSUBSCRIBE BOUNCED OUT USERS YET.  WE NEED TO WAIT 90 DAYS AND THEN PROCESS UNSUB.
// THIS UNSUB PROCESS WILL BE DONE IN SEPARATE SCRIPT SO KEEP BELOW BLOCK OF CODE COMMENTED OUT

$results = mysql_query("SELECT * FROM bounceOut WHERE bounceDate = '$yesterday_with_dash'");
echo mysql_error();
$total_count = mysql_num_rows($results);
/*
while ($row = mysql_fetch_object($results)) {
	$email = $row->email;
	$errorCode = $row->type.':'.$row->reason;
	
	$get_listid_result = mysql_query("SELECT * FROM joinEmailActive WHERE email = \"$email\"");
	echo mysql_error();
	
	// only process this email if the user is currently subscribed.
	if (mysql_num_rows($get_listid_result) > 0) {
		$listid_array = array();
		while ($get_listid_row = mysql_fetch_object($get_listid_result)) {
			array_push($listid_array, $get_listid_row->listid.':'.$get_listid_row->subcampid);
		}
		
		// delete from joinEmailActive - DELETE THIS ONLY AFTER GETTING CURRENT LISTID
		$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\"";
		$delete_query_result = mysql_query($delete_query);
		if (!$delete_query_result) {
			$body .= "Delete Query Failed: $delete_query . Error: ".mysql_error()."\n\n\n";
			$success = false;
		}
		
		foreach ($listid_array as $each_sub) {
			$temp_listid = explode(":", $each_sub);
			$listid = $temp_listid[0];
			$subcampid = $temp_listid[1];

			// insert into joinEmailUnsub
			$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,listid,subcampid,source,errorCode)
						VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"BounceOut\",\"$errorCode\")";
			$insert_query_result = mysql_query($insert_query);
			if (!$insert_query_result) {
				$body .= "Insert Query Failed: $insert_query . Error: ".mysql_error()."\n\n\n";
				$success = false;
			}
		}
	}
}
*/






$results = mysql_query("SELECT SUBSTRING_INDEX( email, '@', -1 ) AS Domain, count(*) AS Total FROM bounceOut WHERE bounceDate = '$yesterday_with_dash' GROUP BY Domain ORDER BY Total DESC LIMIT 25");
echo mysql_error();
$top_domains = "<br><br><br><table border='1'><tr><td><b>Domain</b></td><td><b>Total</b></td></tr>";
while ($row = mysql_fetch_object($results)) {
	$top_domains .= "<tr><td>$row->Domain</td><td>$row->Total</td></tr>";
}
$top_domains .= '</table><br><br><br>';









$error_note = '';
if ($success == false) {
	$error_note = '***ERROR Processing Bounce Out File. Have Nazar or Will or Samir re-run this script (anytime after 3 hours) on web02.am.tld:/home/spatel/scripts/process_bounce_out.php*** ';
}

$body .= "$error_note<br><br>\n\n\nTotal: $total_count<br><br>\n\n\nThis is the number of emails that were bounced out yesterday.  These people will be unsubscribed after 90 days if at that time their bounce count is still 20+.  
			If they are restored, then they will not be unsub. Below report only includes top 25 domains.<br><br>\n\n\n".$top_domains;

$subject = "Bounced Out Report ".date('Y-m-d');

$header = "From:Subscription Center Admin <admin@myfree.com>";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-type: text/html; charset=iso-8859-1\r\n";

mail($bounce_out_report,$subject,$body,$header);


echo 'done';

mysql_close();

?>

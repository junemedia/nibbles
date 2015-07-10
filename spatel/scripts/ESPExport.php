<?php

exit;

$start_time = microtime(true);

include_once("/home/spatel/config.php");

$email_to = "leonz@junemedia.com";


// LOOK FOR SAME DAY FILE


$body = '';

$date_file = date('Ymd');
$success = true;

$header = "From:Subscription Center Admin <admin@myfree.com>";

// path to remote file
$remote_file = "/ESPExport/site04-$date_file.zip";
$local_file = "ESPExport-$date_file.zip";
$extracted_file_path = "/home/spatel/scripts/extracted/";

$ftp_user_name = 'af2964';
$ftp_server = 'www.arcamax.com';
$ftp_user_pass = 'gd5axVtG';


// open some file to write to
$handle = fopen($local_file, 'w');
if (!$handle) {
	$body .= "Unable to create new file on local host to download file from Arcamax FTP server.\n\n\n";
	mail($email_to,"ESPExport.php ERRORS",$body,$header);exit;
	$success = false;
}

// set up basic connection
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
	$body .= "Cannot connect to FTP server.\n\n\n";
	mail($email_to,"ESPExport.php ERRORS",$body,$header);exit;
	$success = false;
}

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if (!$login_result) {
	$body .= "Unable to login to Arcamax FTP server.\n\n\n";
	mail($email_to,"ESPExport.php ERRORS",$body,$header);exit;
	$success = false;
}

ftp_pasv($conn_id, true);

// try to download $remote_file and save it to $handle
if (!ftp_fget($conn_id, $handle, $remote_file, FTP_BINARY, 0)) {
	$body .= "There was a problem while downloading $remote_file to $local_file\n\n\n";
	mail($email_to,"ESPExport.php ERRORS",$body,$header);exit;
	$success = false;
}

// close the connection and the file handler
ftp_close($conn_id);
fclose($handle);

/*
// must be php 5.2 or higher for this to work
$zip = new ZipArchive;
if ($zip->open($local_file) === TRUE) {
	$zip->extractTo(pathinfo(realpath($local_file), PATHINFO_DIRNAME).'/extracted/');
	$zip->close();
	//echo 'Unzip was successful';
	unlink($local_file);
} else {
	$body .= "There was a problem unzipping $local_file\n\n\n";
	$success = false;
}*/

system("unzip $local_file -d $extracted_file_path");
unlink($local_file);


$extracted_file = $extracted_file_path."site04-$date_file.csv";



$count = 0;

// get contents of a file into a string
$handle = fopen($extracted_file, "r");
if ($handle) {
	$truncate_result = mysql_query("TRUNCATE TABLE joinEmailActiveArcamax");
	while (!feof($handle)) {
		$sLine = fgets($handle, 1024);
		$sLine = trim(str_replace('"','',$sLine));
		
		$columns = explode(',', $sLine);
		if (count($columns) > 2) { // dont process last line since it's total only
			$custid = trim($columns[0]);
			$email = trim($columns[1]);
			
			if ($columns[2] != '') {
				$first_contact_date = date('Y-m-d', strtotime($columns[2]));
			} else {
				$first_contact_date = '0000-00-00';
			}
		
			if ($columns[3] != '') {
				$last_open_click_date = date('Y-m-d', strtotime($columns[3]));
			} else {
				$last_open_click_date = '0000-00-00';
			}
			
			$master_sub_status = trim($columns[4]);		//	0 is FALSE (is Master UNsubscribed)		1 is TRUE (is Master SUBscribed)
			$bounce_count = trim($columns[5]);
			
			if ($columns[6] != '') {
				$last_bounce_date = date('Y-m-d', strtotime($columns[6]));
			} else {
				$last_bounce_date = '0000-00-00';
			}

			$sub_array = explode('|', $columns[7]);
			
			//$bc_result = mysql_query("UPDATE badEmails SET bounce_count=\"$bounce_count\" WHERE email=\"$email\" LIMIT 1");
			
			if (strlen($sub_array[0]) == 0) {
				// since there is no subscription, insert record with no signup info
				//$insert_query = "INSERT LOW_PRIORITY IGNORE INTO joinEmailActiveArcamax (custid,email,first_contact_date,last_open_click_date,master_status,bounce_count,last_bounce_date) VALUES (\"$custid\",\"$email\",\"$first_contact_date\",\"$last_open_click_date\",\"$master_sub_status\",\"$bounce_count\",\"$last_bounce_date\")";
				//$result = mysql_query($insert_query);
			} else {
				foreach ($sub_array as $sub) {
					//	L393S3110D12/17/2012
					$sub = str_replace('L','|',$sub);
					$sub = str_replace('S','|',$sub);
					$sub = str_replace('D','|',$sub);
					$list_array = explode('|', $sub);
					
					$listid = $list_array[1];
					$subcampid = $list_array[2];
					$signup_date = date('Y-m-d', strtotime($list_array[3]));
					
					$insert_query = "INSERT LOW_PRIORITY IGNORE INTO joinEmailActiveArcamax (custid,email,first_contact_date,last_open_click_date,master_status,bounce_count,last_bounce_date,listid,subcampid,signup_date) VALUES 
					(\"$custid\",\"$email\",\"$first_contact_date\",\"$last_open_click_date\",\"$master_sub_status\",\"$bounce_count\",\"$last_bounce_date\",\"$listid\",\"$subcampid\",\"$signup_date\")";
					$result = mysql_query($insert_query);
					
					if ($count % 200 == 0) {
						sleep(1);
					}
					$count++;
				}
			}
		}
	}
	// delete file once processing is done.
	unlink($extracted_file);
} else {
	$body .= "Cannot read download file to process ESP Export.\n\n\n";
	mail($email_to,"ESPExport.php ERRORS",$body,$header);exit;
	$success = false;
}
fclose($handle);




$subject = "ESP Export ".date('Y-m-d');

$stop_time = microtime(true);
$time = $stop_time - $start_time;

mail($email_to,$subject,$body."\n\n\nCount: ".$count."\n\n\n"."Elapsed time was $time seconds.",$header);



/*

START PROCESSING DIFFERENCE

*/

/*
$drop = mysql_query("DROP TABLE IF EXISTS joinEmailActive_TEMP_USE;");
sleep(5);
$create = mysql_query("CREATE TABLE joinEmailActive_TEMP_USE SELECT * FROM joinEmailActive;");
sleep(5);
$alter = mysql_query("ALTER TABLE joinEmailActive_TEMP_USE ADD INDEX (email);");
sleep(5);
$alter = mysql_query("ALTER TABLE joinEmailActive_TEMP_USE ADD INDEX (listid);");
sleep(5);
$get_diff = mysql_query("INSERT LOW_PRIORITY IGNORE INTO diff (signup_date,email,listid,subcampid) SELECT they.signup_date,they.email,they.listid,they.subcampid 
	FROM joinEmailActiveArcamax they LEFT OUTER JOIN joinEmailActive_TEMP_USE us ON us.email=they.email AND us.listid=they.listid WHERE  
	they.master_status='1' AND they.bounce_count<20 AND they.listid NOT IN (0,432,433,397,398) AND us.email IS NULL;");
sleep(5);
$drop = mysql_query("DROP TABLE IF EXISTS joinEmailActive_TEMP_USE;");
sleep(5);
*/

/*
if (date('D') == 'Sun') {
	$empty_diff = mysql_query("TRUNCATE TABLE diff");
	sleep(5);
	
	//$get_diff = mysql_query("INSERT LOW_PRIORITY IGNORE INTO diff (signup_date,email,listid,subcampid) SELECT they.signup_date,they.email,they.listid,they.subcampid 
	//FROM joinEmailActiveArcamax they LEFT OUTER JOIN joinEmailActive us ON us.email=they.email AND us.listid=they.listid WHERE  
	//they.master_status='1' AND they.bounce_count<20 AND they.listid NOT IN (0,432,433,397,398) AND us.email IS NULL;");
	
	sleep(5);
	
	$today_with_dash = date('Y-m-d');
	$delete_diff = mysql_query("DELETE FROM diff USING diff INNER JOIN joinEmailUnsub ON diff.email=joinEmailUnsub.email AND diff.listid=joinEmailUnsub.listid WHERE joinEmailUnsub.dateTime BETWEEN '$today_with_dash 00:00:00' AND '$today_with_dash 23:59:59';");
	
	sleep(5);
	
	$today_without_dash = date('Ymd');
	$insert_active = mysql_query("INSERT LOW_PRIORITY IGNORE INTO joinEmailActive (dateTime,email,listid,subcampid,source,subsource) SELECT signup_date, email, listid, subcampid, 'diff', '$today_without_dash' FROM diff;");
	sleep(5);
	$insert_sub = mysql_query("INSERT LOW_PRIORITY IGNORE INTO joinEmailSub (dateTime,email,listid,subcampid,source,subsource) SELECT signup_date, email, listid, subcampid, 'diff', '$today_without_dash' FROM diff;");
	sleep(5);
	
	$all_diff = mysql_query("SELECT 1 FROM diff");
	$diff_all_rows = mysql_num_rows($all_diff);
	sleep(5);
	
	$unique_diff = mysql_query("SELECT DISTINCT email FROM diff");
	$diff_unique_rows = mysql_num_rows($unique_diff);
	
	$subject = "Arcamax vs Subscription Center Stats ".date('Y-m-d');
	
	$stop_time = microtime(true);
	$time = $stop_time - $start_time;
	
	$body = "Total Subscriptions: $diff_all_rows\n\nUnique Emails: $diff_unique_rows\n\n"."Elapsed time was $time seconds.";
	mail($email_to,$subject,$body,$header);
}
*/


mysql_close();


/*

SELECT they.email,they.listid FROM joinEmailActiveArcamax they LEFT OUTER JOIN joinEmailActive us ON us.email=they.email AND us.listid=they.listid WHERE  they.master_status=1 AND they.bounce_count<20 AND they.listid !=0 AND us.email IS NULL;

		
		id
		custid
		email
		first_contact_date
		last_open_click_date
		master_status
		bounce_count
		last_bounce_date
		listid
		subcampid
		signup_date

		
		Each record contains these fields:
			custid
			email address
			first contact date
			last open date (or last click date)
			master subscribe status (0 or 1)
			bounce count
			last bounce date
			current subscriptions
			
			The current subscriptions is set of subscription descriptors separated by the | (pipe symbol).  A subscription descriptor contains three 
				subfields each preceeded by a letter indicating the type of the field:
			     L: List Id
			     S: Sub Camp Id
			     D: Date of subscription
			For example: "L393S3110D12/17/2012|L396S3110D12/17/2012"
			This means:
			   User subscribed to list 393 using subcamp 3110 on 12/17/2012
			   User subscribed to list 396 using subcamp 3110 on 12/17/2012
*/


?>

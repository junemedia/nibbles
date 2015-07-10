<?php

/*

INSERT IGNORE INTO emailsToRestore (email) 
SELECT DISTINCT email FROM joinEmailUnsub 
WHERE source='BounceOut' 
AND dateTime BETWEEN '2012-12-21 00:00:00' AND '2013-01-03 23:59:59'
AND (email LIKE '%hotmail.com' OR email LIKE '%msn.com' OR email LIKE '%live.com')

*/

include_once("/home/spatel/config.php");

$start_date = "2012-12-21 00:00:00";
$end_date = "2013-01-03 23:59:59";

$get_emails = "SELECT DISTINCT email FROM emailsToRestore ORDER BY email DESC";
$result_email = mysql_query($get_emails);
echo mysql_error();
$counter = 0;
while ($row = mysql_fetch_object($result_email)) {
	$email = $row->email;
	
	if ($counter % 100 == 0) {
		sleep(2);
	}
	
	$counter++;
	
	$get_data = "SELECT * FROM joinEmailUnsub WHERE email=\"$email\" AND source='BounceOut' 
			AND dateTime BETWEEN '$start_date' AND '$end_date'";
	$result_data = mysql_query($get_data);
	echo mysql_error();
	while ($data_row = mysql_fetch_object($result_data)) {
		$dateTime = $data_row->dateTime;
		$listid = $data_row->listid;
		$subcampid = $data_row->subcampid;
		$unsub_id = $data_row->id;
		
		// init so we don't use wrong info from previous loop - very important
		$source = 'RestoreBounceOut';
		$subsource = '';
		$ipaddr = '';
		$afid1 = '';
		$afid2 = '';
		$home = '';
		$io = '';
		$api = '';
		
		//echo "date: $dateTime\n\n";
		
		// must get the last sign up date
		$get_original_date = "SELECT * FROM joinEmailSub WHERE email=\"$email\" AND 
						listid=\"$listid\" ORDER BY id DESC LIMIT 1";
		//echo $get_original_date."\n\n";
		$result_getDate = mysql_query($get_original_date);
		echo mysql_error();
		while ($signup_date_row = mysql_fetch_object($result_getDate)) {
			$dateTime = $signup_date_row->dateTime;
			$ipaddr = $signup_date_row->ipaddr;
			$source = $signup_date_row->source;
			$subsource = $signup_date_row->subsource;
			$afid1 = $signup_date_row->afid1;
			$afid2 = $signup_date_row->afid2;
			$home = $signup_date_row->home;
			$io = $signup_date_row->io;
			$api = $signup_date_row->api;
		}
		
		//echo "date: $dateTime\n\n";
		
		$active_insert = "INSERT IGNORE INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource,afid1,afid2,home,io,api) 
					VALUES (\"$dateTime\",\"$email\",\"$ipaddr\",\"$listid\",\"$subcampid\",\"$source\",\"$subsource\",\"$afid1\",\"$afid2\",\"$home\",\"$io\",\"$api\")";
		//echo $active_insert."\n\n";
		$active_insert_result = mysql_query($active_insert);
		
		$delete_unsub = "DELETE FROM joinEmailUnsub WHERE id='$unsub_id' LIMIT 1";
		//echo $delete_unsub."\n\n";
		$delete_unsub_result = mysql_query($delete_unsub);
	}
	
	
	$delete_restore_email = "DELETE FROM emailsToRestore WHERE email=\"$email\"";
	//echo $delete_restore_email."\n\n";
	$delete_restore_email_result = mysql_query($delete_restore_email);
	
	echo '.';

	//exit;
}



?>

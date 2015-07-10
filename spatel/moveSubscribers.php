<?php

$start_time = microtime(true);

include_once("/home/spatel/config.php");

function HardFlush ($x = 0) {
    echo "\n\n$x--";
    // check that buffer is actually set before flushing
    if (ob_get_length()) {
        @ob_flush();
        @flush();
        @ob_end_flush();
    }
    @ob_start();
}

$x = 0;
$result = mysql_query("SELECT email FROM moveSubscribers");
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$x++;
	
	// START THE UNSUB PROCESS
	
	$get_user_data = "SELECT * FROM joinEmailActive WHERE email=\"$email\"";
	$get_user_data_result = mysql_query($get_user_data);
	while ($user_row = mysql_fetch_object($get_user_data_result)) {
		$user_ip = $user_row->ipaddr;
		$listid = $user_row->listid;
		$subcampid = '2921';
	
		$insert_query = "INSERT IGNORE INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source) VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"MovingToListID579\")";
		$insert_query_result = mysql_query($insert_query);
		echo mysql_error();
	
		$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
		$delete_query_result = mysql_query($delete_query);
		echo mysql_error();
	
		$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid&ipaddr=$user_ip";
		
		
		$insert_log = "INSERT IGNORE INTO moveQuery (post_string) VALUES (\"$post_string\")";
		$insert_log_result = mysql_query($insert_log);
		echo mysql_error();
	}
	
	
	
	// START THE SUB PROCESS
	
	$new_listid = '579';
	$new_subcampid = '3362';
	
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO joinEmailSub (dateTime,email,listid,subcampid,source)
					VALUES (NOW(),\"$email\",\"$new_listid\",\"$new_subcampid\",\"MovedInactivesToNewList\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO joinEmailActive (dateTime,email,listid,subcampid,source)
					VALUES (NOW(),\"$email\",\"$new_listid\",\"$new_subcampid\",\"MovedInactivesToNewList\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	$post_string = "email=$email&sublists=$new_listid&subcampid=$new_subcampid";
	
	$insert_log = "INSERT IGNORE INTO moveQuery (post_string) VALUES (\"$post_string\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();

	// NOW DELETE THE ENTRY SINCE IT'S PROCESSED...
	$delete_query = "DELETE FROM moveSubscribers WHERE email =\"$email\"";
	$delete_query_result = mysql_query($delete_query);
	echo mysql_error();
	
	if ($x % 5 == 0) {
		//sleep(1);
		echo $email;
		HardFlush($x);
	}
}

$stop_time = microtime(true);
$time = $stop_time - $start_time;
echo "Script Run Time: $time Seconds";

?>

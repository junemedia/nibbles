<?php

include_once("subctr_config.php");
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

if ($request_type == 'sub') {
	// insert into joinEmailSub
	$insert_query = "INSERT INTO joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"2921\",\"admin\",\"admin\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT INTO joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"2921\",\"admin\",\"admin\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($listid);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"2921\",\"admin\",\"admin\",'sub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
	

	// call to function to send new subscriber to Arcamax.
	$send_to_arcamax = Arcamax($email,$listid,'2921',$user_ip,'sub'); // sub or unsub

	// record arcamax server response log
	$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$email\",\"$listid\",\"2921\",\"$user_ip\",\"sub\",\"$send_to_arcamax\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	echo $send_to_arcamax;
	exit;
}

if ($request_type == 'unsub') {
	// insert into joinEmailUnsub
	$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
				VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"2921\",\"admin\",\"admin\",\"per request\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	
	// get new listid from old listid
	$new_listid = LookupNewListIdByOldListId($listid);
				
	// insert into campaigner
	$campaigner = "INSERT IGNORE INTO campaigner (dateTime,email,ipaddr,oldListId,newListId,subcampid,source,subsource,type,isProcessed)
					VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$new_listid\",\"2921\",\"admin\",\"admin\",'unsub','N')";
	$campaigner_result = mysql_query($campaigner);
	echo mysql_error();
	

	// delete from joinEmailActive
	$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\" LIMIT 1";
	$delete_query_result = mysql_query($delete_query);
	echo mysql_error();

	// call to function to send unsub to Arcamax
	$send_to_arcamax = Arcamax($email,$listid,'2921',$user_ip,'unsub'); // sub or unsub

	// record arcamax server response log
	$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$email\",\"$listid\",\"2921\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	echo $send_to_arcamax;
	exit;
}

?>

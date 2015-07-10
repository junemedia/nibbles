<?php

function getMicroTime(){
	$mtime = explode(" ", microtime());
	return ($mtime[1]+ $mtime[0]);
}

//the database connection
$connection;

//the global that holds the table name
$session_table;

//The session open handler called by PHP whenever
//a session is initialized. Always returns true.
function sessionOpen($database_name, $table_name){
	global $connection;
	global $hostname;
	global $username;
	global $password; 
	
	if(!($connection = @mysql_pconnect($hostname,
										$username,
										$password)))
		showerror();
		
	if(!mysql_select_db($database_name, $connection))
		showerror();
		
	global $session_table;
	$session_table = $table_name;
	
	return true;
}

function sessionRead($sess_id){
	global $connection;
	
	global $session_table;
	
	$search_query = "SELECT * FROM $session_table WHERE session_id = '$sess_id'";
	if(!($result = @mysql_query($search_query,
								$connection)))
		showerror();
		
	if(mysql_num_rows($result) == 0)
		return "";
	else {
		$row = mysql_fetch_array($result);
		return $row["session_variable"];
	}
}

function sessionWrite($sess_id, $val){
	global $connection;
	global $session_table;
	
	$time_stamp = getMicroTime();
	
	$search_query = "SELET session_id from $session_table WHERE session_id = '$sess_id'";
	
	if(!($result = @ mysql_query($search_query,
								 $connection)))
		showerror();
		
	if(mysql_num_rows($result) == 0){
		$insert_query = "INSERT INTO $session_table (session_id, session_variable, last_accessed) values
						('$sess_id', '$val', $time_stamp)";
		if(!mysql_query($insert_query, 
						$connection))
			showerror();
	} else {
		//existing session found
		$update_query = "UPDATE $session_table SET session_variable = '$val', last_accessed = $time_stamp 
						 WHERE session_id = '$sess_id'";
		if(!mysql_query($update_query,
						$connection))
			showerror();
	}
	return true;
}

function sessionClose($sess_id){
	return true;
}

function sessionDestroy($sess_id){
	global $connection;
	global $session_table;
	
	$delete_query = "DELETE FROM $session_table WHERE session_id = '$sess_id'";
	
	if(!($result = @ mysql_query($delete_query,
								 $connection)))
		showerror();
		
	return true;
}

function sessionGC($max_lifetime){
	global $connection;
	global $session_table;
	
	$time_stamp = getMicroTime();
	
	$delete_query = "DELETE FROM $session_table WHERE last_accessed < ($time_stamp - $max_lifetime)";
	
	if(!($result = @mysql_query($delete_query,
								$connection)))
		showerror();
	
	return true;
}

session_set_save_handler("sessionOpen", "sessionClose","sessionRead", "sessionWrite", "sessionDestroy", "sessionGC");

?>
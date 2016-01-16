<?php

include_once ("../../includes/JSON.php");

while (list($key,$val) = each($_POST)) {
	$$key = $val;
}
while (list($key,$val) = each($_GET)) {
	$$key = $val;
}

mysql_pconnect ("8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com", "jingshi", "kendeji12306!");
mysql_select_db ("arcamax");

$user_ip = trim($_SERVER['REMOTE_ADDR']);


function get_subcampid_notes ($subcampid) {
	$get_subcampid_notes = "SELECT notes FROM subcampid WHERE subcampid = \"$subcampid\" LIMIT 1";
	$get_subcampid_notes_results = mysql_query($get_subcampid_notes);
	echo mysql_error();
	$subcampid_row = mysql_fetch_object($get_subcampid_notes_results);
	return $subcampid_row->notes;
}


function get_listid_name ($listid) {
	$get_listid_name = "SELECT title FROM joinLists WHERE listid = \"$listid\" LIMIT 1";
	$get_listid_name_result = mysql_query($get_listid_name);
	echo mysql_error();
	$listid_name_row = mysql_fetch_object($get_listid_name_result);
	return $listid_name_row->title;
}


function get_active_listid_with_listname () {
	$get_listid_name = "SELECT listid,title FROM joinLists WHERE isActive = \"Y\"";
	$get_listid_name_result = mysql_query($get_listid_name);
	echo mysql_error();
	$list_id_array = array();
	while ($row = mysql_fetch_object($get_listid_name_result)) {
		array_push($list_id_array, $row->listid.' ==> '.$row->title);
	}
	return $list_id_array;
}


?>

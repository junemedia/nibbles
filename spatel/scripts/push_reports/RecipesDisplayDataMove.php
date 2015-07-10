<?php


exit;


mysql_pconnect ('localhost', 'root', "8tre938G");
mysql_select_db ('nibbles_temp');


// START OF DATE MOVE
$result = mysql_query("SELECT * FROM RecipesDisplay WHERE dateAdded < CURRENT_DATE");
echo mysql_error();
while ($select_row = mysql_fetch_object($result)) {
	$id = $select_row->id;
	$title = $select_row->title;
	$url =  $select_row->url;
	$count = $select_row->count;
	$dateAdded = $select_row->dateAdded;
	
	$insert = "INSERT INTO RecipesDisplayHistory (title,url,count,dateAdded) VALUES(\"$title\", \"$url\", \"$count\", \"$dateAdded\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
	
	if ($insert_result) {
		$delete = "DELETE FROM RecipesDisplay WHERE id = '$id' LIMIT 1";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}
// END OF DATA MOVE





// START OF DATE MOVE
$result = mysql_query("SELECT * FROM FullRecipesDisplay WHERE dateAdded < CURRENT_DATE");
echo mysql_error();
while ($select_row = mysql_fetch_object($result)) {
	$id = $select_row->id;
	$title = $select_row->title;
	$count = $select_row->count;
	$dateAdded = $select_row->dateAdded;
	
	$insert = "INSERT INTO FullRecipesDisplayHistory (title,count,dateAdded) VALUES(\"$title\", \"$count\", \"$dateAdded\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
	
	if ($insert_result) {
		$delete = "DELETE FROM FullRecipesDisplay WHERE id = '$id' LIMIT 1";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}
// END OF DATA MOVE




/*
// START OF DATE MOVE
$result = mysql_query("SELECT * FROM StimRecipesDisplay WHERE dateAdded < CURRENT_DATE");
echo mysql_error();
while ($select_row = mysql_fetch_object($result)) {
	$id = $select_row->id;
	$title = $select_row->title;
	$count = $select_row->count;
	$dateAdded = $select_row->dateAdded;
	
	$insert = "INSERT INTO StimRecipesDisplayHistory (title,count,dateAdded) VALUES(\"$title\", \"$count\", \"$dateAdded\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();
	
	if ($insert_result) {
		$delete = "DELETE FROM StimRecipesDisplay WHERE id = '$id' LIMIT 1";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}
// END OF DATA MOVE
*/

?>


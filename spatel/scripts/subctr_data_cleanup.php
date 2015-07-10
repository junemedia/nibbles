<?php

include_once("/home/spatel/config.php");


$ten_days_ago = date("Y-m-d", strtotime("-10 day"));
$sixty_days_ago = date("Y-m-d", strtotime("-60 day"));
$hundred_days_ago = date("Y-m-d", strtotime("-100 day"));


// Move data older than 100 days from bounceOut to bounceOutArchive
// START MOVING DATA FROM CURRENT TABLE TO ARCHIVE TABLE
$query = "SELECT * FROM bounceOut WHERE bounceDate <= '$hundred_days_ago'";
$result = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($result)) {
	$insert = "INSERT INTO bounceOutArchive(dateTimeAdded,bounceDate,email,type,reason)
		VALUES(\"$oRow->dateTimeAdded\", \"$oRow->bounceDate\", \"$oRow->email\", 
		\"$oRow->type\", \"$oRow->reason\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();

	if ($insert_result) {
		$delete = "DELETE FROM bounceOut WHERE id = '$oRow->id'";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}




// START MOVING DATA FROM CURRENT TABLE TO ARCHIVE TABLE
$query = "SELECT * FROM bounceLog WHERE bounceDate <= '$ten_days_ago'";
$result = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($result)) {
	$insert = "INSERT INTO bounceLogArchive(dateTimeAdded,bounceDate,type,reason,listid,jobid,email)
		VALUES(\"$oRow->dateTimeAdded\", \"$oRow->bounceDate\", \"$oRow->type\", 
		\"$oRow->reason\", \"$oRow->listid\", \"$oRow->jobid\", \"$oRow->email\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();

	if ($insert_result) {
		$delete = "DELETE FROM bounceLog WHERE id = '$oRow->id'";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}







// Move data older than 90 days from arcamaxNewLog to arcamaxNewLogArchive
$query = "SELECT * FROM arcamaxNewLog WHERE dateTime <= '$sixty_days_ago'";
$result = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($result)) {
	$response = addslashes($oRow->response);
	$insert = "INSERT INTO arcamaxNewLogArchive(dateTime,email,listid,subcampid,ipaddr,type,response)
		VALUES(\"$oRow->dateTime\",\"$oRow->email\",\"$oRow->listid\",\"$oRow->subcampid\",
		\"$oRow->ipaddr\",\"$oRow->type\",\"$response\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();

	if ($insert_result) {
		$delete = "DELETE FROM arcamaxNewLog WHERE id = '$oRow->id'";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}






// Move data older than 90 days from api to api_Archive
$query = "SELECT * FROM api WHERE dateTimeAdded <= '$sixty_days_ago'";
$result = mysql_query($query);
echo mysql_error();
while ($oRow = mysql_fetch_object($result)) {
	$insert = "INSERT INTO api_Archive(dateTimeAdded,email,listid,subcampid,ipaddr,fname,lname,
		gender,addr1,addr2,city,state,zip,phone_1,phone_2,phone_3,day,month,year,subsource)
		VALUES(\"$oRow->dateTimeAdded\",\"$oRow->email\",\"$oRow->listid\",\"$oRow->subcampid\",
		\"$oRow->ipaddr\",\"$oRow->fname\",\"$oRow->lname\",\"$oRow->gender\",\"$oRow->addr1\",
		\"$oRow->addr2\",\"$oRow->city\",\"$oRow->state\",\"$oRow->zip\",\"$oRow->phone_1\",
		\"$oRow->phone_2\",\"$oRow->phone_3\",\"$oRow->day\",\"$oRow->month\",\"$oRow->year\",\"$oRow->subsource\")";
	$insert_result = mysql_query($insert);
	echo mysql_error();

	if ($insert_result) {
		$delete = "DELETE FROM api WHERE id = '$oRow->id'";
		$delete_result = mysql_query($delete);
		echo mysql_error();
	}
}


$yesterday = date("Y-m-d", strtotime("-1 day"));

$report = "<table><tr><td>Data Move/Cleanup Completed</td></tr></table>";

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:admin@myfree.com\r\n";

//mail('samirp@junemedia.com,leonz@junemedia.com',"Subscription Center (Arcamax Database) Data Move/Cleanup Report - $yesterday",$report,$sHeaders);



?>

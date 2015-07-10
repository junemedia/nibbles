<?php

mysql_pconnect ("localhost", "root", "asdf!@#$");



/*
$query = "SELECT * FROM test.be WHERE partner = '' ORDER BY afid1,afid2 ASC";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$referrer = $row->referrer;
	$afid1 = $row->afid1;
	$afid2 = $row->afid2;
	
	$update = "UPDATE test.gross SET referrer=\"$referrer\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	$update = "UPDATE test.gross SET afid1=\"$afid1\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	$update = "UPDATE test.gross SET afid2=\"$afid2\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
}

*/




/*
$query = "SELECT * FROM test.be WHERE partner !=''";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	$subid = $row->subid;
	$partner = $row->partner;
	
	$update = "UPDATE test.gross SET subid=\"$subid\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	$update = "UPDATE test.gross SET partner=\"$partner\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
}

*/














/*

$query = "SELECT * FROM test.gross";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	
	$source = '';
	$subsource = '';
	
	$check = "SELECT source,subsource FROM arcamax.joinEmailSub WHERE email='$email' AND source !='' AND dateTime BETWEEN '2012-02-11 00:00:00' AND '2012-02-17 23:59:59' ORDER BY dateTime ASC LIMIT 1";
	$check_result = mysql_query($check);
	echo mysql_error();
	$num_rows = mysql_num_rows($check_result);
	while ($check_row = mysql_fetch_object($check_result)) {
		$source = $check_row->source;
		$subsource = $check_row->subsource;
	}
	
	$update = "UPDATE test.gross SET source=\"$source\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	$update = "UPDATE test.gross SET subsource=\"$subsource\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
}


*/













/*
$query = "SELECT * FROM test.gross";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	
	$subcampid = '';
	$check = "SELECT subcampid FROM arcamax.joinEmailSub WHERE email='$email' AND dateTime BETWEEN '2012-02-11 00:00:00' AND '2012-02-17 23:59:59' ORDER BY dateTime ASC LIMIT 1";
	$check_result = mysql_query($check);
	echo mysql_error();
	$num_rows = mysql_num_rows($check_result);
	while ($check_row = mysql_fetch_object($check_result)) {
		$subcampid = $check_row->subcampid;
	}
	$update = "UPDATE test.gross SET subcampid=\"$subcampid\" WHERE email=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	
	$sActiveListId = '';
	$check = "SELECT DISTINCT listid FROM arcamax.joinEmailActive WHERE email='$email' ORDER BY listid ASC";
	$check_result = mysql_query($check);
	echo mysql_error();
	$num_rows = mysql_num_rows($check_result);
	while ($check_row = mysql_fetch_object($check_result)) {
		$sActiveListId .= "$check_row->listid,";
	}
	if ($sActiveListId !='') {
		$sActiveListId = substr($sActiveListId,0,strlen($sActiveListId)-1);
	}
	if ($sActiveListId !='') {
		$update = "UPDATE test.gross SET active=\"$sActiveListId\" WHERE email=\"$email\"";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
	
	
	$gross = '';
	$check = "SELECT DISTINCT listid FROM arcamax.joinEmailSub WHERE email='$email' ORDER BY listid ASC";
	$check_result = mysql_query($check);
	echo mysql_error();
	$num_rows = mysql_num_rows($check_result);
	while ($check_row = mysql_fetch_object($check_result)) {
		$gross .= "$check_row->listid,";
	}
	if ($gross !='') {
		$gross = substr($gross,0,strlen($gross)-1);
	}
	if ($gross !='') {
		$update = "UPDATE test.gross SET sub=\"$gross\" WHERE email=\"$email\"";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}
*/












/*
$firsttime = 0;
$repeatsub = 0;
$query = "SELECT * FROM test.twomonths";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	
	$check_query = "SELECT * FROM arcamax.joinEmailSub WHERE email=\"$email\" AND dateTime <= '2011-12-31 23:59:59'";
	$check_result = mysql_query($check_query);
	echo mysql_error();
	
	if (mysql_num_rows($check_result) == 0) {
		$firsttime++;
		$update = "UPDATE test.twomonths SET firsttime=\"Y\" WHERE email=\"$email\"";
		$update_result = mysql_query($update);
		echo mysql_error();
	} else {
		$repeatsub++;
	}
}

echo "\n\n";
echo "First Time: $firsttime\n\n";
echo "Repeat: $repeatsub\n\n";
echo "\n\n";
*/








?>

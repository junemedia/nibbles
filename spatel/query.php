<?php



mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("samir_test");






$query = "SELECT * FROM log";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$pieces = explode("&", $row->data);
	$data = $row->data;
	$response = $row->response;
	$email = '';
	
	foreach ($pieces as $piece) {
		if (strstr($piece,'email=')) {
			$email = str_replace('email=','',$piece);
			break;
		}
	}

	//echo $email;
	//echo "\n\n\n";
	//var_dump($pieces);
	
	
	$update = "UPDATE leads SET data=\"$data\", response=\"$response\" WHERE e=\"$email\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	//exit;
}













/*
$query = "SELECT * FROM listing";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$domain = $row->domain;
	$exp = $row->exp;
	
	$update = "UPDATE domains SET g='$exp' WHERE d='$domain' LIMIT 1";
	$update_result = mysql_query($update);
	echo mysql_error();
}
*/





/*
$query = "SELECT * FROM domains ORDER BY id DESC";
$result = mysql_query($query);
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$id = $row->id;
	if (strlen($row->g) == 8) {
		//10/08/11
		$exp_yy = '20'.substr($row->g,6,2);
		$exp_mm = substr($row->g,0,2);
		$exp_dd = substr($row->g,3,2);
		echo $id." --> Y ".$exp_yy."\n\n";
		echo $id." --> D ".$exp_dd."\n\n";
		echo $id." --> M ".$exp_mm."\n\n";
		
		$update = "UPDATE domains SET g='$exp_yy-$exp_mm-$exp_dd' WHERE id='$id' LIMIT 1";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}
*/


echo 'done';
exit();


?>

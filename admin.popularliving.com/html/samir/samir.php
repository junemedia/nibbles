<?php


$host = "mydb01.amperemedia.com";
$dbase = "nibbles_temp" ;
$user = "nibbles" ;
$pass = "#a!!yu5" ;

mysql_pconnect ($host, $user, $pass);
mysql_select_db ($dbase);

/*

$row = 1;
$handle = fopen("member_export.csv", "r");
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    //$num = count($data);
    //echo "<p> $num fields in line $row: <br /></p>\n";
    $row++;
    
    $email = $data[2];
    $query = "INSERT IGNORE INTO phil (email) VALUES (\"$email\")";
    
    $result = mysql_query($query);
    echo mysql_error();
    
    //for ($c=0; $c < $num; $c++) {
        //echo $data[2] . "<br />\n";
    //}
    if ($row % 15 == 0) {
		echo ".";
    }
    if ($row % 300 == 0) {
		echo "<br>\n";
    }
}
fclose($handle);
*/





$result = mysql_query("SELECT email FROM phil WHERE ip=''");
echo mysql_error();
$x = 0;
while ($row = mysql_fetch_object($result)) {
	$x++;
	$email = $row->email;
	
	
	
	
	$result3 = mysql_query("SELECT dateTimeAdded,remoteIp FROM nibbles.joinEmailSub WHERE email = \"$email\"");
	echo mysql_error();
	while ($row3 = mysql_fetch_object($result3)) {
		$remoteIp = $row3->remoteIp;
		$dateTimeAdded = $row3->dateTimeAdded;
	}
	
	
	
	$result2 = mysql_query("update phil set ip=\"$remoteIp\", dateTime=\"$dateTimeAdded\" WHERE email=\"$email\"");
	echo mysql_error();
	if ($x % 5 == 0) {
		echo ".";
    }
    if ($x % 300 == 0) {
		echo "<br>\n";
    }
    flush();
    ob_flush();
}
echo 'done';




?>


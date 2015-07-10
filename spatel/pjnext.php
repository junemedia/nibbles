<?php

mysql_pconnect ("localhost", "root", "8tre938G");

$get = "SELECT * FROM samir_test.pjnext";
$get_result = mysql_query($get);
echo mysql_error();
$x = 0;
while ($get_row = mysql_fetch_object($get_result)) {
	$data = $get_row->data;

	$pieces = explode("&", $data);
	$phone = '';
	foreach ($pieces as $piece) {
		if (strstr($piece,'phone')) {
			$phone = strtoupper(trim($piece));
			$phone = str_replace('PHONE=','',$phone);
			break;
		}
	}

	
	$update_query = "UPDATE samir_test.pjnext
				SET data=\"$phone\"
				WHERE id=\"$get_row->id\"";
	if ($x == 0) {
		//echo $update_query;
		//exit;
	}
	
	$update = mysql_query($update_query);
	echo mysql_error();
	
	
	$x++;
}


//firstname=Harry&lastname=Mccoy&address1=1709 Mccoy St&city=Pell City&state=AL&zip=35125&email=tank5244@aol.com
//&phone=2053387984&agreedtoterms=1&productid=1&brandid=26&affid=6&subaffid=1323&subsubaffid=
//&affleadid=&source=http://www.groupdialusavm.com&ipaddr=136.235.108.206&dob=03/14/1949&last4ssn=0000


?>


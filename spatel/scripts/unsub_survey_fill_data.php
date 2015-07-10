<?php

include_once("/home/spatel/config.php");

$result = mysql_query("SELECT DISTINCT email FROM joinEmailUnsubDetails WHERE first_subcampid='0'");
echo mysql_error();
while ($row = mysql_fetch_object($result)) {
	$email = $row->email;
	
	$subcampid = "";
	$details = mysql_query("SELECT * FROM joinEmailSub WHERE email=\"$email\" ORDER BY dateTime ASC LIMIT 1");
	echo mysql_error();
	while ($details_row = mysql_fetch_object($details)) {
		$subcampid = $details_row->subcampid;
	}
	
	$update_result = mysql_query("UPDATE joinEmailUnsubDetails SET first_subcampid=\"$subcampid\" WHERE email=\"$email\" AND first_subcampid='0'");
	echo mysql_error();
}

//mail("samirp@junemedia.com,leonz@junemedia.com", "Unsub Survey Fill Data", "Done", "From:admin@myfree.com");

mysql_close();

?>

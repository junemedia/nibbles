<?php

include_once("/home/spatel/config.php");

/*
// skip this part since it's done by cron script

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
}*/


$from_date = date('Y-m-d', strtotime('last Sunday'));
$to_date = date('Y-m-d', strtotime('last Saturday'));


$output = "";
$sql = mysql_query("SELECT * FROM joinEmailUnsubDetails WHERE dateTime BETWEEN '$from_date 00:00:00' AND '$to_date 23:59:59'");
echo mysql_error();
$columns_total = mysql_num_fields($sql);


for ($i = 0; $i < $columns_total; $i++) {
	$heading = mysql_field_name($sql, $i);
	$output .= '"'.$heading.'",';
}
$output .="\n";


while ($row = mysql_fetch_array($sql)) {
	for ($i = 0; $i < $columns_total; $i++) {
		$output .='"'.$row["$i"].'",';
	}
	$output .="\n";
}

$random_hash = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 25);

$attachment = chunk_split(base64_encode($output));

$headers = "From: admin@myfree.com\r\nReply-To: samirp@junemedia.com,leonz@junemedia.com";
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";

$email_output = "
--PHP-mixed-$random_hash;
Content-Type: multipart/alternative; boundary='PHP-alt-$random_hash'
--PHP-alt-$random_hash
Content-Type: text/plain; charset='iso-8859-1'
Content-Transfer-Encoding: 7bit

--PHP-mixed-$random_hash
Content-Type: text/csv; name=unsub_survey_dump.csv
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$attachment
--PHP-mixed-$random_hash--";


mail("samirp@junemedia.com,leonz@junemedia.com,leonz@junemedia.com,williamg@junemedia.com,patriciad@junemedia.com", "Unsub Survey Dump for $from_date to $to_date", $email_output, $headers);

mysql_close();

?>

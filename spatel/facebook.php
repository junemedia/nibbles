<?php

// setup mysql connection...
mysql_pconnect ("192.168.51.33", "root", "5dsa234Y");


function send_to_arcamax ($email) {
	$post_string = "email=$email&sublists=393&subcampid=2934";
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	$server_response = '';
	if ($rSocketConnection) {
		fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rSocketConnection, "Host: $sHostPart\r\n");
		fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
		fputs($rSocketConnection, $post_string);
		while(!feof($rSocketConnection)) {
			$server_response .= fgets($rSocketConnection, 1024);
		}
		fclose($rSocketConnection);
	}
	return addslashes($post_string."\n\n\n".$server_response);
}


function validate_email ($email) {
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		return false;
	}
	
	list($prefix, $domain) = split("@",$email);
	if (!getmxrr($domain, $mxhosts)) {
		return false;
	}
	
	$check_banned_domain = "SELECT * FROM arcamax.bannedDomains WHERE domain=\"$domain\" LIMIT 1";
	$check_banned_domain_result = mysql_query($check_banned_domain);
	if (mysql_num_rows($check_banned_domain_result) == 1) {
		return false;
	}
	
	$check_banned_email = "SELECT * FROM arcamax.bannedEmails WHERE email=\"$email\" LIMIT 1";
	$check_banned_email_result = mysql_query($check_banned_email);
	if (mysql_num_rows($check_banned_email_result) == 1) {
		return false;
	}
	
	
	$handle = fopen("http://www3.tendollars.com/BriteVerifyForSubscriptionCenter.aspx?email=$email&source=subcenter", "rb");
	$server_response = stream_get_contents($handle);
	fclose($handle);
	
	$server_response = addslashes($server_response);
	$insert_bv_log = "INSERT INTO arcamax.BullseyeBriteVerifyCheck (email,dateTimeAdded,response)
				VALUES (\"$email\", NOW(), \"$server_response\")";
	$insert_bv_log_result = mysql_query($insert_bv_log);
	echo mysql_error();
	
	$return_value = true;
	if (strstr($server_response,'valid') || strstr($server_response,'unknown')) {
		$return_value = true;
	} else {
		$return_value = false;
	}
	
	if (strstr($server_response,'not valid') || strstr($server_response,'invalid')) {
		$return_value = false;
	}
	
	return $return_value;
}

echo '.';
@ob_flush();
@flush();
@ob_end_flush();
@ob_start();

// delete entries that are not for signup
$delete = "DELETE FROM test.facebook WHERE signup != 'TRUE'";
$result = mysql_query($delete);
echo mysql_error();


echo '.';
@ob_flush();
@flush();
@ob_end_flush();
@ob_start();


// delete entries that are already processed in last batches
$delete_dups = "DELETE FROM test.facebook WHERE email IN (SELECT email FROM test.facebook_done)";
$result_dups = mysql_query($delete_dups);
echo mysql_error();


echo '.';
@ob_flush();
@flush();
@ob_end_flush();
@ob_start();


// validate emails in facebook and delete if it's bad.
$get_emails = "SELECT email FROM test.facebook";
$result_email = mysql_query($get_emails);
$total_count = mysql_num_rows($result_email);
$x = 0;
echo mysql_error();
while ($row = mysql_fetch_object($result_email)) {
	$x++;
	echo '.';
	@ob_flush();
	@flush();
	@ob_end_flush();
	@ob_start();

	$email_ck_result = validate_email($row->email);
	
	echo '.';
	@ob_flush();
	@flush();
	@ob_end_flush();
	@ob_start();

	if ($email_ck_result == false) {
		$delete = "DELETE FROM test.facebook WHERE email = \"$row->email\"";
		$result = mysql_query($delete);
		echo mysql_error();
	}
	
	echo $x . " of " . $total_count . "\n";
    @ob_flush();
    @flush();
    @ob_end_flush();
    @ob_start();
}



// email addresses in facebook table are valid so process now....
// get new emails for signup
$get_emails = "SELECT email FROM test.facebook";
$result_email = mysql_query($get_emails);
echo mysql_error();
$total_count = mysql_num_rows($result_email);
$x = 0;
while ($row = mysql_fetch_object($result_email)) {
	$response = send_to_arcamax($row->email);
	
	$x++;
	
	// insert into facebook_done table so we don't process this email again
	$insert_query = "INSERT IGNORE INTO test.facebook_done (email) VALUES (\"$row->email\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// delete from facebook take too...
	$delete = "DELETE FROM test.facebook WHERE email = \"$row->email\"";
	$result = mysql_query($delete);
	echo mysql_error();
	
	
	
	
	// insert into joinEmailSub
	$insert_query = "INSERT IGNORE INTO arcamax.joinEmailSub (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",'',\"393\",\"2934\",\"ContestEntrants\",\"ContestEntrants\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();

	// insert into joinEmailActive
	$insert_query = "INSERT IGNORE INTO arcamax.joinEmailActive (dateTime,email,ipaddr,listid,subcampid,source,subsource)
					VALUES (NOW(),\"$row->email\",'',\"393\",\"2934\",\"ContestEntrants\",\"ContestEntrants\")";
	$insert_query_result = mysql_query($insert_query);
	echo mysql_error();
	
	// record arcamax server response log
	$insert_log = "INSERT IGNORE INTO arcamax.arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
				VALUES (NOW(),\"$row->email\",\"393\",\"2934\",'',\"sub\",\"$response\")";
	$insert_log_result = mysql_query($insert_log);
	echo mysql_error();
	
	
	
	echo $x . " of " . $total_count . "\n";
    @ob_flush();
    @flush();
    @ob_end_flush();
    @ob_start();
}


echo $x;


?>


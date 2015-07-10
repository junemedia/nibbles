<?php

exit;

mysql_pconnect ('localhost', 'root', "8tre938G");
mysql_select_db ('nibbles_r4l');

$r4l = "'/cookbooks/aprilfools.php','/cookbooks/cheesecake.php','/cookbooks/christmas.php','/cookbooks/easter.php','/cookbooks/healthyfish.php','/cookbooks/homemadepizza.php','/cookbooks/r4lphotocontest.php','/cookbooks/stirfry.php','/cookbooks/superbowl.php','/cookbooks/thanksgiving.php','/cookbooks/ultimategrilling.php','/cookbooks/porkchops.php','/cookbooks/dietweightloss.php','/cookbooks/budgetfriendly.php','/cookbooks/valentine.php'";

$result = mysql_query("SELECT id,email,ip FROM eBook WHERE sent = 'N' AND capture_page IN ($r4l)");
echo mysql_error();
while ($select_row = mysql_fetch_object($result)) {
	// START OF ARCAMAX SCRIPT - For all R4L ebooks, set up the datapass to add to list IDs: 393,396 and subcamp ID: 2760
	$post_string = "email=$select_row->email&sublists=393,396&subcampid=2760&ipaddr=$select_row->ip";
	$server_response = '';
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
	
	// separate host part and script path
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
					
	if (strstr($sPostingUrl, "https:")) {
		$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	} else {
		$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	}
		
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
	// END OF ARCAMAX SCRIPT
	
	$update = "UPDATE eBook SET sent='Y' WHERE id='$select_row->id'";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	//echo $post_string."\n\n\n".$server_response;
	//exit;
}

?>

<?php

$host = "localhost" ;

$dbase = "jr" ;

$user = "nibbles" ;

$pass = "#a!!yu5" ;

mysql_pconnect ('localhost', $user, $pass);

mysql_select_db ($dbase);



$sUrl = "http://www.popularliving.com/working/testSubmit.php";


$sFirstVal = '';

while (list($key,$val) = each($_POST)) {
	
	//$$key = $val;
	
	$sNewUrlComponents .= "$key=".urlencode($val)."&";
		
	$sValues .= "'$val',";
	
	if ($sFirstVal == '') {
		$sFirstVal = "$val";
	}

}


if ($sNewUrlComponents != '') {
	$sNewUrlComponents = substr($sNewUrlComponents, 0, strlen($sNewUrlComponents)-1);
}


if ($sValues != '') {
	$sValues = substr($sValues, 0, strlen($sValues)-1);
}



// insert here
$sCheckQuery = "SELECT * 
				FROM	postForwardVars
				WHERE   col1 = '$sFirstVal'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();
if (mysql_num_rows($rCheckResult) == 0 && $sValues != '') {
	$sInsertQuery = "INSERT IGNORE INTO postForwardVars(col1, col2)
					 VALUES($sValues)";
	$rInsertResult = mysql_query($sInsertQuery);
	echo mysql_error();
}
	
	
$aUrlArray = explode("//", $sUrl);
$sUrlPart = $aUrlArray[1];

$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
$sHostPart = ereg_replace("\/","",$sHostPart);
					
					
$sScriptPath = substr($sUrlPart,strlen($sHostPart));
if (strstr($sPostingUrl, "https:")) {
	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
} else {
						
	$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
						
}

$sNewUrlComponents = stripslashes($sNewUrlComponents);

if ($rSocketConnection) {
			
	fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
	fputs($rSocketConnection, "Host: $sHostPart\r\n");
	fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
	fputs($rSocketConnection, "Content-length: " . strlen($sNewUrlComponents) . "\r\n");
	fputs($rSocketConnection, "User-Agent: MSIE\r\n");
	fputs($rSocketConnection, "Connection: close\r\n\r\n");
	fputs($rSocketConnection, $sNewUrlComponents);					
	
	/*
	while(!feof($rSocketConnection)) {
		$sResponse .= fgets($rSocketConnection, 1024);
	}
	echo $sResponse;
	*/
	
	fclose($rSocketConnection);
	
} else {
	echo "Error: $errstr ($errno)<br />\r\n";
}
?>


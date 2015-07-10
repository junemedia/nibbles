<?php

while (list($key,$val) = each($HTTP_GET_VARS)) {		
	$$key = $val;		
	$sQueryString .= "$key=$val&";
	
}

while (list($key,$val) = each($HTTP_POST_VARS)) {
	$$key = $val;				
	$sQueryString .= "$key=$val&";
}

if ($sQueryString != '') {
	$sQueryString = substr($sQueryString,0,strlen($sQueryString)-1);
}


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";

mail("ccalip@amperemedia.com","Form Post Test", $sQueryString, $sHeaders);

?>

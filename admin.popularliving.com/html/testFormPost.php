<?php

//$sQueryString = '';
while (list($key,$val) = each($HTTP_GET_VARS)) {		
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
		$$key = "";		
	} else {*/
		$$key = $val;		
		$sQueryString .= "$key=$val&";
	//}	
	
}

while (list($key,$val) = each($HTTP_POST_VARS)) {
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
		$$key = "";		
	} else {*/
		$$key = $val;				
		$sQueryString .= "$key=$val&";
	//}	
}

if ($sQueryString != '') {
	$sQueryString = substr($sQueryString,0,strlen($sQueryString)-1);
}
//echo $sQueryString;

//mail("josh@amperemedia.com","Form Post Test", $sQueryString);
//$sQueryString = stripslashes($sQueryString);

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
//$sHeaders .= "cc: spatel@amperemedia.com";

mail("leads@amperemedia.com","Form Post Test", $sQueryString, $sHeaders);

// wqui_mrt form post url - http://www.QuickApply.com/in/
?>

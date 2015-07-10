<?php

mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("newsletter_archive");




/*
$result = mysql_query("SELECT * FROM newsletters ORDER BY id DESC");
while ($row = mysql_fetch_object($result)) {
	$html = $row->html;
	
	$link_result = mysql_query("SELECT * FROM replacelinks");
	while ($link_row = mysql_fetch_object($link_result)) {
		$html = str_replace(trim($link_row->find),trim($link_row->replace),$html);
	}
	$html = addslashes($html);
	
	$update = "UPDATE newsletters SET html=\"$html\" WHERE id=\"$row->id\"";
	$update_result = mysql_query($update);
	echo mysql_error();
	echo $row->id."\n";
	@flush();@ob_flush();
}
*/

/*
$result = mysql_query("SELECT * FROM newsletters ORDER BY id ASC");
while ($row = mysql_fetch_object($result)) {
	$html = $row->html;
	
	// Convert from YYYY-MM-DD to MM/DD/YYYY
	$issue_date = substr($row->newsletterDate,5,2).'/'.substr($row->newsletterDate,8,2).'/'.substr($row->newsletterDate,0,4);

	$get_link = "SELECT * FROM arcamaxurl WHERE listid='$row->listid' AND issuedate ='$issue_date'";
	$link_result = mysql_query($get_link);
	while ($link_row = mysql_fetch_object($link_result)) {
		$html = str_replace("http://www.arcamax.com/ard/$link_row->admapid?t=C2000000000L$row->listid",$link_row->url,$html);
	}
	$html = addslashes($html);
	
	$update = "UPDATE newsletters SET html=\"$html\" WHERE id=\"$row->id\"";
	$update_result = mysql_query($update);
	echo mysql_error();
}

*/






/*
function seoUrl($string) {
    //Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
    $string = strtolower($string);
    //Strip any unwanted characters
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    //Convert two -- with only 1 that was already done at top
    $string = str_replace("--", "", $string);
    $string = str_replace('"', '', $string);
    $string = str_replace("'", '', $string);
    $string = str_replace("’", '', $string);
    return $string;
}


$result = mysql_query("SELECT * FROM newsletters ORDER BY id DESC");
while ($row = mysql_fetch_object($result)) {
	$subject = $row->id.'-'.seoUrl($row->subject);

	$update = "UPDATE newsletters SET alias=\"$subject\" WHERE id=\"$row->id\"";
	//echo $update;
	//exit;
	$update_result = mysql_query($update);
	echo mysql_error();
}
*/




/*
foreach ($sub as $get_info) {
	$result = mysql_query("SELECT * FROM import WHERE subject = \"$get_info\" LIMIT 1");
	while ($row = mysql_fetch_object($result)) {
		
		$url = "https://www.arcamax.com/esp/bin/bmq?jobpreview=html&joblist=$row->jobid";
		
		$subject = str_replace('"',"'",$row->subject);
		$subject = addslashes($subject);
		
		$update = "INSERT INTO newsletters (newsletterDate,subject,list,preview) 
		VALUES (\"$row->issuedate\",\"$subject\",\"$row->listid\",\"$url\")";
		//echo $update;
		//exit;
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}
*/













/*
$result = mysql_query("SELECT * FROM import");
while ($row = mysql_fetch_object($result)) {
	$pieces = explode("/", $row->issuedate);
	$mm = $pieces[0];
	$dd = $pieces[1];
	$yy = $pieces[2];

	$final = "$yy-$mm-$dd";
	$update = "UPDATE import SET issuedate=\"$final\" WHERE id=\"$row->id\"";
//	echo $update;
//	exit;
	$update_result = mysql_query($update);
	echo mysql_error();
}
*/



/*
$result = mysql_query("SELECT * FROM newsletters");
while ($row = mysql_fetch_object($result)) {
	$sHttpPostString = str_replace('https://www.arcamax.com/esp/bin/bmq?','',$row->preview);
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/bmq';
	
	$server_response = '';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
		
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
		
	if (strstr($sPostingUrl, "https:")) {
		$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	} else {
		$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	}
		
	$sScriptPath .= "?".$sHttpPostString;
	fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");
	fputs($rSocketConnection, "Host: $sHostPart\r\n");
	fputs($rSocketConnection, "Accept-Language: en\r\n");
	fputs($rSocketConnection, "User-Agent: MSIE\r\n");
	fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.hmarshak:RypeNR7i")."\r\n");
	fputs($rSocketConnection, "Connection: close\r\n\r\n");
			
	while(!feof($rSocketConnection)) {
		$server_response .= fgets($rSocketConnection, 1024);
	}
	fclose($rSocketConnection);
	
	$start_pos = strpos($server_response, "<body bgcolor=\"#FFFFFF\">");
	$server_response = substr($server_response,$start_pos,strlen($server_response));
	$server_response = str_replace("<body bgcolor=\"#FFFFFF\">",'',$server_response);
	$server_response = str_replace("</body>",'',$server_response);
	$server_response = str_replace("</html>",'',$server_response);
	
	$server_response = addslashes($server_response);
	
	$update = "UPDATE newsletters SET html = \"$server_response\" WHERE id='$row->id' LIMIT 1";
	$update_result = mysql_query($update);
	echo mysql_error();
	//exit;
}
*/
?>

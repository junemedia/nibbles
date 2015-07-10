<?php

mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("newsletter_archive");


/*
$result = mysql_query("SELECT * FROM newsletters");
while ($row = mysql_fetch_object($result)) {
	$html = $row->html;
	$update = false;
	if (strstr($html,"Advertisement")) {
		$end_pos = strpos($html, '</tr>', strpos($html, "Advertisement"));
		$part1 = substr($html, 0, $end_pos+5);
		$starting = strrpos($part1,"<tr>");
		$ending = strpos($part1,"</tr>",$starting);
		$find = substr($part1, $starting, $ending);
		$replace = '<!-- FIND FIRST AD TR TAG -->';
		$html = str_replace($find,$replace,$html);
		$update = true;
	}
	if (strstr($html,"Advertisement")) {
		$end_pos = strpos($html, '</tr>', strpos($html, "Advertisement"));
		$part1 = substr($html, 0, $end_pos+5);
		$starting = strrpos($part1,"<tr>");
		$ending = strpos($part1,"</tr>",$starting);
		$find = substr($part1, $starting, $ending);
		$replace = '<!-- FIND SECOND AD TR TAG -->';
		$html = str_replace($find,$replace,$html);
		$update = true;
	}
	
	if ($update == true) {
		$html = addslashes($html);
		$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}

*/




exit;




/*
$result = mysql_query("SELECT * FROM newsletters");
while ($row = mysql_fetch_object($result)) {
	if (strstr($row->html,"usemap=\"#netseer_map_")) {
		$html = $row->html;
	
		$start_pos = strpos($html, "<img border=0 src=\"http://nl.netseer.com/dsatserving2/servlet/BannerServer");	
		$end_pos = strpos($html, '</map>');	
		
		$find = substr($html, $start_pos, $end_pos-$start_pos+6);
		$replace = '<!-- REMOVED IMG MAP TAG -->';
		
		$html = str_replace($find,$replace,$html);
		
		$html = addslashes($html);
	
		$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}
*/


exit;






/*
$result = mysql_query("SELECT * FROM newsletters");
while ($row = mysql_fetch_object($result)) {
	//echo $row->html;
	//exit;
	if (strstr($row->html,'#fcefb1') && strstr($row->html,'Sponsored')) {
		//echo 'hi';exit;
		$html = $row->html;
		//Look for "Sponsored" and find open table tag before that word and end table tag after that word and remove all
		
		$start_pos = strpos($html, "#fcefb1");
		
		$end_pos = strpos($html, '</table>', strpos($html, "Sponsored"));
		$find = substr($html, $start_pos, $end_pos-$start_pos+8);
		$replace = '<!-- REMOVED NETSEER CONTEXTLINKS HTML -->';
		
		$html = str_replace($find,$replace,$html);
		
		$html = addslashes($html);
	
		$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}








*/

exit;

?>

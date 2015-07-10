<?php

mysql_pconnect ("192.168.51.51", "r4ldbuser", "acgnW3FsFSD2");
mysql_select_db ("newsletter_archive");


/*


$get_slots = "SELECT * FROM newsletters WHERE html LIKE '%Q&E_More-Newsletters.jpg%' LIMIT 14";
$get_slots_result = mysql_query($get_slots);
//echo mysql_num_rows($get_slots_result);
//exit;
while ($row = mysql_fetch_object($get_slots_result)) {
	$html = $row->html;
	
	$start_pos = strpos($html, '<area');
	$end_pos = strpos($html, '</map>');
		
	$replace = '<area shape="rect" coords="7,82,171,100" alt="Budget Cooking" href="REDIR:http://www.dailygiftsforinput.com/dispatch2.asp?home=1805-28941A-L1" />
<area shape="rect" coords="11,103,172,120" alt="Party Tips & Recipes" href="REDIR:http://www.supersurveysdaily.com/dispatch2.asp?home=1792-28910I-L3" />
<area shape="rect" coords="5,122,172,140" alt="Quick & Easy Recipes" href="REDIR:http://www.surveysformore.com/dispatch2.asp?home=1804-28926Q-L4" />
<area shape="rect" coords="192,105,331,121" alt="Daily Recipes" href="REDIR:http://www.inputtoday.com/dispatch2.asp?home=1801-28923K-L1" />
<area shape="rect" coords="195,124,331,140" alt="Casserole Cookin\'" href="REDIR:http://www.tellusdaily.com/dispatch2.asp?home=1819-29015U-L2" />';
	
	//echo $start_pos.' - '.$end_pos;
	
	//exit;
	
	$find = substr($html, $start_pos, $end_pos-$start_pos);

		//echo $find;
		//exit;
	$html = str_replace($find,$replace,$html);
		
	$html = addslashes($html);
	
	
	//echo $html;
	$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	//exit;
}




$get_slots = "SELECT * FROM newsletters WHERE html LIKE '%Q&E_More-Newsletters.jpg%' LIMIT 14";
$get_slots_result = mysql_query($get_slots);
//echo mysql_num_rows($get_slots_result);
//exit;
while ($row = mysql_fetch_object($get_slots_result)) {
	$html = $row->html;
	
	$html = str_replace('Q&E_More-Newsletters.jpg','R4L_More-Newsletters-Q&E.gif',$html);

	$html = addslashes($html);
	
	
	//echo $html;
	$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
	$update_result = mysql_query($update);
	echo mysql_error();
	
	//exit;
}

*/

?>

<?php

mysql_pconnect ("localhost", "root", "8tre938G");
mysql_select_db ("newsletter_archive");

/*
include_once("links_array.php");

function str_replace_once($search, $replace, $subject) {
    $firstChar = strpos($subject, $search);
    if($firstChar !== false) {
        $beforeStr = substr($subject,0,$firstChar);
        $afterStr = substr($subject, $firstChar + strlen($search));
        return $beforeStr.$replace.$afterStr;
    } else {
        return $subject;
    }
}

$query = "SELECT * FROM newsletters";
$result = mysql_query($query);
while ($row = mysql_fetch_object($result)) {
	$html = $row->html;
	$update = false;
	foreach ($ad_links as $url) {
		if (strstr($html,$url)) {
			$end_pos = strpos($html, '</a>', strpos($html, $url));
			$part1 = substr($html, 0, $end_pos+4);
			$starting = strrpos($part1,"<a href=\"$url");
			$ending = strpos($part1,"</a>",$starting);
			$find = substr($part1, $starting, $ending);
			$replace = '[R4L_NL_Archiving_XXX_300x250]';
			$html = str_replace($find,$replace,$html);
			$update = true;
		}
	}

	if ($update == true) {
		$html = str_replace_once('[R4L_NL_Archiving_XXX_300x250]','[R4L_NL_Archiving_TOP_300x250]',$html);
		$html = str_replace_once('[R4L_NL_Archiving_XXX_300x250]','[R4L_NL_Archiving_BOTTOM_300x250]',$html);
		
		$html = addslashes($html);
		$update = "UPDATE newsletters SET html=\"$html\" WHERE id='$row->id' LIMIT 1";
		$update_result = mysql_query($update);
		echo mysql_error();
	}
}

exit;

*/










/*
$urlArray = array();

function linkFinder($html) {
      global $urlArray;
      $regex='|<a.*?href="(.*?)"|';
      preg_match_all($regex,$html,$parts);
      $links=$parts[1];
      foreach($links as $link){
          array_push($urlArray, $link);
      }
}

$query = "SELECT html,id FROM newsletters ORDER BY id ASC";
$result = mysql_query($query);
while ($row = mysql_fetch_object($result)) {
	linkFinder($row->html);
}

echo "\n\n\n";
echo count($urlArray);
echo "\n\n\n";
$urlArray = array_unique($urlArray);
asort($urlArray);
echo count($urlArray);
echo "\n\n\n";
$found = fopen("links_found.txt", 'w');
foreach ($urlArray as $url) {
	if (!strstr($url, 'chewonthatblog.com') && !strstr($url, 'popularliving.com') && !strstr($url, 'recipe4living-recipes.com') && !strstr($url, 'recipe4living.com') && !strstr($url, '?home=') && !strstr($url, 'facebook') && !strstr($url, 'twitter')) {
		fwrite($found, $url."\n");
	}
}

fclose($found);
*/

?>

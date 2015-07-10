<?php


/*


$filename = "listing.txt";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

//<id>:video:LwOKpX9gaIk</id>




$parsed = get_all_strings_between($contents, "<id>", "</id>");

echo "<pre>";
echo $parsed;
echo "</pre>";

 
 function get_all_strings_between($string,$start,$end)
{
//Returns an array of all values which are between two tags in a set of data
$strings = '';
$startPos = 0;
$i = 0;
//echo strlen($string)."\n";
while($startPos < strlen($string) && $matched = get_string_between(substr($string,$startPos),$start,$end))
{
if ($matched == null || $matched[1] == null || $matched[1] == '') break;
$startPos = $matched[0]+$startPos+1;
//array_push($strings,$matched[1]);
$strings .= $matched[1].",";
$i++;
}
return $strings;
}

function get_string_between($string, $start, $end){
//$string = " ".$string;
$ini = strpos($string,$start);
if ($ini == 0) return null;
$ini += strlen($start);
$len = strpos($string,$end,$ini) - $ini;
return array($ini+$len,substr($string,$ini,$len));
}*/

?>

<?php


include_once("/home/spatel/config.php");

/*

Do NOT accept following: Invalid, Seeds, Traps, Moles

code=560020
pwd=SilCar

*/

//	http://post.impressionwise.com/fastfeed.aspx?code=560020&pwd=SilCar&email=testme@impressionwise.com

$start_time = microtime(true);


$email = 'testme@impressionwise.com';

$sPostingUrl = 'http://post.impressionwise.com/fastfeed.aspx';




$post_string = "code=560020&pwd=SilCar&email=$email";


$contents = file_get_contents($sPostingUrl.'?'.$post_string);
//$contents = stream_get_contents($handle);
//fclose($handle);


echo $contents;






$stop_time = microtime(true);
$time = $stop_time - $start_time;

echo "\n\n\nElapsed time was $time seconds.\n\n\n";



?>


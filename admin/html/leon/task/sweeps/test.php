<?php

/*echo $_POST;
echo $_GET;

print_r("Post:\r\n");
print_r($_POST);

print_r("\r\nGet:\r\n");
print_r($_GET);
exit;*/

/*$endTime = time();
$startTime = $endTime - (24*60*60);
$link = "http://win.betterrecipes.com/api/syncUser/".$startTime.'/'.$endTime;
print_r($link);
$dateStart = date('Y-m-d H:i:s', $startTime);
$dateStop = date('Y-m-d H:i:s', $endTime);
echo "<br>\r\n";
echo "Start time: ";print_r($dateStart);
echo "<br>\r\n";
echo "Stop time: ";print_r($dateStop);*/

//echo date("Y-m-d H:i:s", time(true));


$tst =  array(array('12'=>'456', 19=> "I'm a reobot"),array('14'=>'4522d6', 19=> "I'm a dd reobot"));
$ff= json_encode($tst);
$aa = json_decode($ff);
print_r($ff);echo "<br>\r\n";
print_r($aa);

?>

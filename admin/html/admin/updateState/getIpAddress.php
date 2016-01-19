<?php
include_once("config.php");
include_once("functions.php");

$query = "SELECT * FROM lcc_temp";
$result1 = timeQuery($query);
$total = mysql_num_rows($result1);
echo mysql_error();
$count = 1;
while ($item = mysql_fetch_object($result1)) 
{
	$email = $item->Email;
	
	$query = "SELECT * FROM joinEmailSub WHERE email = '".$email."'";
	$result2 = timeQuery($query);
	echo mysql_error();
	
	while($sItem = mysql_fetch_object($result2))
	{
		$query1 = "INSERT INTO lcc_email_ip (email,ipaddr,listid,subcampid,source) VALUES ('".$email."','".$sItem->ipaddr."','".$sItem->listid."','".$sItem->subcampid."','".$sItem->source."')";
		$result3 = timeQuery($query1);
		echo mysql_error();
	}	
	
	echo "<------------------------------------------------ Split ------------------------------------------------->\n";	
	$count++;		
}

function timeQuery($sql){
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start1 = $time;
    
    $return = mysql_query($sql);
    
    
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start2 = $time;
    
    $pasted = $start2 - $start1;
    $pasted = round($pasted, 5);
    
    echo "-->Sql:$sql\n";
    echo "----> Query Time [$pasted]\n";
    return $return;    
}
?>
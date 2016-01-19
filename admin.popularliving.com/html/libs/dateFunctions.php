<?php


// script contains date functions 


// Pass Date in format yyyy-mm-dd returns yyyy-mm-dd
function DateAdd($intervalType,$interval,$date) {
	$year = substr($date,0,4);
	$month = substr($date,5,2);
	$day = substr($date,8,2);
	switch($intervalType) {
		case "y":
		$time = mktime(0,0,0, $month, $day, $year+$interval);
		break;
		case "m":
		$time = mktime(0,0,0,$month+$interval, $day, $year);
		break;
		case "d":
		$time = mktime(0,0,0,$month, $day+$interval, $year);
	}
	
	$date = getdate($time);
	if ($date["mon"] <10)
		$month = "0".$date["mon"];
	else
		$month = $date["mon"];
	if ($date["mday"] <10)
		$day = "0".$date["mday"];
	else
		$day = $date["mday"];
	return $date["year"]."-".$month."-".$day;
	
}


//************************ Date Difference function ************************/

Function DateDiff ($interval, $date1,$date2) {
	
	// get the number of seconds between the two dates
	/*$timedifference =  $date2 - $date1;
	
	switch ($interval) {
		case "w":
		$retval  = bcdiv($timedifference ,604800);
		break;
		case "d":
		$retval  = bcdiv( $timedifference,86400);
		break;
		case "h":
		$retval = bcdiv ($timedifference,3600);
		break;
		case "n":
		$retval  = bcdiv( $timedifference,60);
		break;
		case "s":
		$retval  = $timedifference;
		break;
		
	}*/
	/*
	if ($interval == 'd') {
		$sQuery = "SELECT DATEDIFF($date1, $date2) as dateDifference";
		$rResult = dbQuery($sQuery);
		while ($oRow = dbFetchObject($rResult)) {
			$iDateDiff = $oRow->dateDifference;
		}
	}*/
	
	
 $diff = $date2-$date1;
 $seconds = 0;
 $hours   = 0;
 $minutes = 0;

 if($diff % 86400 <= 0)  //there are 86,400 seconds in a day
    {$days = $diff / 86400;}

 if($diff % 86400 > 0)
   {   $rest = ($diff % 86400);
       $days = ($diff - $rest) / 86400;
       if( $rest % 3600 > 0 )
       {   $rest1 = ($rest % 3600);
           $hours = ($rest - $rest1) / 3600;
           if( $rest1 % 60 > 0 )
           {   $rest2 = ($rest1 % 60);
               $minutes = ($rest1 - $rest2) / 60;
               $seconds = $rest2;
           }else
               $minutes = $rest1 / 60;
       }else
           $hours = $rest / 3600;
   }
   
   
   switch ($interval) {
		case "w":
		$retval  = bcdiv($diff ,604800);
		break;
		case "d":
		$retval  = $days;
		break;
		case "h":
		$retval = $hours;
		break;
		case "n":
		$retval  = $minutes;
		break;
		case "s":
		$retval  = $seconds;
		break;
		
	}

	return $retval;
	
}
//************************ Date Difference function ************************/


/****************** function to record time to use in script execution time calculation  **************/

function getMicroTime(){
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

/***************************************/



?>
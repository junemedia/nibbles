<?php

include_once("config.php");
include_once("functions.php");

$targetTime = time()+3600;
$updateNum = 1;
echo "Start Time: ".time()."\n";
$round = 1;
while($updateNum)
{
	$updateNum = updateRecordInfo(9000); 
	print_r("Round ".$round.' Finish Time:'.time());echo "\n";
	echo "Update Num: ".$updateNum."\n";
	$round++;
	if($updateNum)
	{
		if(time()>=$targetTime)
		{
			//next round
			$targetTime = time()+3600;
		}
		else
		{			
			//until 1 hour, start next round
			time_sleep_until($targetTime);
			$targetTime = time()+3600;
		}
		echo "Target Time: ".$targetTime."\n";
	}
	else
	{
		break;
	}
}

print_r('Total Finished');


function updateRecordInfo($limit)
{
	$query = "SELECT * FROM LeonCampaignContact where isUpdated = 0 LIMIT 0,".$limit;
	$result1 = timeQuery($query);
	$total = mysql_num_rows($result1);
	echo mysql_error();
	$count = 1;
	while ($item = mysql_fetch_object($result1)) 
	{
        $Contactid = $item->Contactid;
		$email = $item->Email;
		$status = $item->Status;	
		
		$query = "SELECT * FROM joinEmailSub WHERE ipaddr != '' AND email = '".$email."' LIMIT 1";
		$result2 = timeQuery($query);
		echo mysql_error();
		
		$row = mysql_fetch_object($result2);
		if(!empty($row))
		{
			$id = $row->id;
			$email = $row->email;
			$subcampid='';//if(!$subcampid){$subcampid = $row->subcampid;}
			$signup_date = $row->dateTime;
			$ipaddr = $row->ipaddr;
			$new_listid = $row->listid;
			$source = '';//getSubcampIdDescriptiveName($subcampid);
			$subsource = $row->source;
			$type = 'sub';//$row->type;
			$state = '';
			$zipcode = '';
			$ipDetail = array();
				
			$sub_array = array();
			$unsub_array = array();
				
			if ($type == 'sub') {
				$sub_array = array($new_listid);
			} else {
				$unsub_array = array($new_listid);
			}
			echo $count."\n";
			echo "email:".$item->Email."\n";			
			
			$ipDetail = getLocationByIp($ipaddr);
			if(!empty($ipDetail))
			{
				if(isset($ipDetail['region']))
				{
					$state = $ipDetail['region'];
				}
				
				if(isset($ipDetail['zipcode']))
				{
					$zipcode = $ipDetail['zipcode'];
				}
			}			

			echo "state: ".$state."\n";
			echo "zipcode: ".$zipcode."\n";
			
			$data_array = array('email' => $email, 'first' => '', 'last' => '',
								'phone' => '', 'fax' => '', 'status' => ucfirst($status), 'format' => 'Both',
								'ipaddr' => $ipaddr, 'signup_date' => $signup_date, 'age_group' => '',
								'oldlistid' => '', 'subcampid' => $subcampid, 'source' => $source,
								'subsource' => $subsource, 'address1' => '', 'address2' => '',
								'city' => '', 'state' => $state, 'zipcode' => $zipcode,
								'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
									'sub_array' => $sub_array, 'unsub_array' => $unsub_array);
			$send_result = sendToCampaigner($data_array);
			
			/*$result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
			$contactId = trim(getXmlValueByTag($send_result,'ContactId'));
			$email = trim(getXmlValueByTag($send_result,'ContactUniqueIdentifier'));*/
				
			$send_result = addslashes($send_result);
				
			echo "result: ".$send_result."\n";
		
			if(!empty($ipDetail))
			{
				if(empty($state))
				{
					$query1 = "UPDATE LeonCampaignContact SET isUpdated=3 WHERE ContactUniqueIdentifier = '".$email."'"; //3:doesn't get state info
					$result3 = timeQuery($query1);
					echo mysql_error();
				}
				else
				{
					$query1 = "UPDATE LeonCampaignContact SET isUpdated=1 WHERE ContactUniqueIdentifier = '".$email."'"; //1:is updated
					$result3 = timeQuery($query1);
					echo mysql_error();
				}
			}
			else
			{
				$query1 = "UPDATE LeonCampaignContact SET isUpdated=4 WHERE ContactUniqueIdentifier = '".$email."'"; //4:email ip address is empty
				$result3 = timeQuery($query1);
				echo mysql_error();
			}
		}
		else
		{
			$query1 = "UPDATE LeonCampaignContact SET isUpdated=2 WHERE ContactUniqueIdentifier = '".$email."'"; //2:not found in table
			$result3 = timeQuery($query1);
			echo mysql_error();
		}
		echo "<------------------------------------------------ Split ------------------------------------------------->\n";	
		$count++;		
	}
	
	return $total;
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

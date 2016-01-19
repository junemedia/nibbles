<?php
exit;
include_once("config.php");
/*echo $_POST;
echo $_GET;

print_r("Post:\r\n");
print_r($_POST);

print_r("\r\nGet:\r\n");
print_r($_GET);
exit;*/

/*$endTime = time();
$startTime = $endTime - (24*60*60);
$link = "http://win.betterrecipes.com/api/syncUser/".$startTime.'/'.$endTime;*/
//$link="http://win.betterrecipes.com/api/syncUser/1428739907/1428815607";
$link='';
$recieve = file_get_contents($link);
$items = json_decode($recieve);
$items='';
/*echo "<pre>";
print_r($recieve);
echo "</pre>";exit();*/

//$items = array(array('email'=>'howewangme@gmail.com','ip'=>'66.54.186.254','site_id'=>2,'firstname'=>'Howe','lastname'=>'Wang','city'=>'Chicago','zip'=>'60606','state'=>'IL','date_registered'=>'2014-12-12'));

$total = 0;
$successTotal = 0;
$failedTotal = 0;
$failedEmails = '';
if(!empty($items))
{
foreach($items as $item) {
	$email = $item->email;
	$subcampid = '';
	$save_subcampid = '';
	$signup_date = '';
	$ipaddr = long2ip($item->ip);
	$old_listid = '';
	$new_listid = '';
	$subsource = '';
	$type = 'sub';
	$fromSite = '';
	$firstName = $item->firstname;
	$lastName = $item->lastname;
	$city = $item->city;
	$zipcode = $item->zip;
	$state = $item->state;
	$signup_date = $item->date_registered;
	$alreadyExist = false;	
	
	if($item->site_id==1)
	{
		$fromSite = 'BR';
		$subcampid = 4347; //Sweeps Registration BR 0615
		$source = 'SweepsRegistrationBR0615';
		$save_subcampid = $subcampid;
		$old_listid = 506; //506:old list id for br sweeps
	}

	if($item->site_id==2)
	{
		$fromSite = 'R4L';
		$subcampid = 4348 ; //Sweeps Registration R4L 0615
		$source = 'SweepsRegistrationR4L0615';
		$save_subcampid = $subcampid;
		$old_listid= 507;   //507:old list id for r4l sweeps
	}
	
	//$source = getSubcampIdDescriptiveName($subcampid);
	$new_listid = LookupNewListIdByOldListId($old_listid);	
		
	$sub_array = array();
	$unsub_array = array();
		
	if ($type == 'sub') {
		$sub_array = array($new_listid);
	} else {
		$unsub_array = array($new_listid);
	}	    
	
	//Check if the email is already in campaigner
	$query = "SELECT l.3818568 as email,l.3834288 as subcampid  FROM LeonCampaignContactJoin as l WHERE l.3818568 = '".$email."' limit 1";
	$result2 = mysql_query($query);
	echo mysql_error();
	$row = mysql_fetch_object($result2);
	if(!empty($row))
	{
		$alreadyExist = true;
		if(!empty($row->subcampid))
		{
			$subcampid = ''; //don't override the exist subcampid
		}
	}
    
	$data_array = array('email' => $email, 'first' => $firstName, 'last' => $lastName,
						'phone' => '', 'fax' => '', 'status' => 'Subscribed', 'format' => 'Both',
						'ipaddr' => $ipaddr, 'signup_date' => $signup_date, 'age_group' => '',
						'oldlistid' => '', 'subcampid' => $subcampid, 'source' => $source,
						'subsource' => $subsource, 'address1' => '', 'address2' => '',
						'city' => $city, 'state' => $state, 'zipcode' => $zipcode,
						'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
						'sub_array' => $sub_array, 'unsub_array' => $unsub_array, 'alreadyExist'=>$alreadyExist    
                            
                            );
	$send_result = sendSweepsToCampaigner($data_array);
	$result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
	$send_result = addslashes($send_result);	
	if(strtolower($result_code) != 'success')
	{
		$failedEmails[] = $email."(".$result_code.")\r\n";		
		$failedTotal++;
	}else
	{
		$successTotal++;
	}
	echo $email."(".$result_code.")\r\n";
	// insert into sweeps_log
	$sweeps_log = "UPDATE sweeps_user_boolean_log SET status=\"$result_code\",reponse=\"$send_result\" WHERE email=\"$email\"";
	$sweeps_log_result = mysql_query($sweeps_log);
	echo mysql_error();
}
}

$total = $successTotal+$failedTotal;
	
// Send out results mail
date_default_timezone_set('America/Chicago');
$email = "leonz@junemedia.com";
// Send the mail notification
$to      = $email . ',howew@junemedia.com';
$to='howew@junemedia.com';
$subject = 'Daily Report - Push Sweeps Register Users Into Campaigner';
$failedMsg = $failedTotal>0?"Failed Emails:\r\n".implode(",", $failedEmails)."\r\n":"";

$message = "Done! Total Upload [$total] emails.\r\n".
		   "Successed: $successTotal emails.\r\n".
		   "Failed: $failedTotal emails.\r\n".$failedMsg;
$headers = 'From: leon.subctr. <leonz@junemedia.com>' . "\r\n" .
	'Reply-To: Re-Upload <leonz@junemedia.com>' . "\r\n" .
	'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

?>

<?php

include_once("/home/spatel/config.php");
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

	$iCount = 0;
	$query = "SELECT * FROM iw_unsub";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		$email = $row->email;
		
		$data_array = array('email' => $email, 'first' => '', 'last' => '',
								'phone' => '', 'fax' => '', 'status' => 'Subscribed', 'format' => 'Both',
								'ipaddr' => '', 'signup_date' => '', 'age_group' => '',
								'oldlistid' => '', 'subcampid' => '', 'source' => 'iw_unsub',
								'subsource' => '', 'address1' => '', 'address2' => '',
								'city' => '', 'state' => '', 'zipcode' => '',
								'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
								'sub_array' => array(), 'unsub_array' => array(3844903,3844893,3844883,3844873,3844863,3844853,3844843,3844833,3844823,3844813,3844803,3844793,3844783,3844768));
		$send_result = sendToCampaigner($data_array);
	
		$result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
		$contactId = trim(getXmlValueByTag($send_result,'ContactId'));
		//$email = trim(getXmlValueByTag($send_result,'ContactUniqueIdentifier'));
		//var_dump($send_result);
		echo "\n\n$iCount => $result_code => $email => $contactId\n\n";
		
		$iCount++;
	}


?>

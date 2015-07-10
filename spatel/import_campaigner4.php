<?php

include_once("/home/spatel/config.php");
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

for ($x=0;$x<=50;$x++) {

//$process_list = array(583,553,448,411,554,394,574,539,511,395,558,410,393,396);
$process_list = array(396);
foreach ($process_list as $each_list) {
	$iCount = 0;
	$new_listid = LookupNewListIdByOldListId($each_list);
	$query = "SELECT * FROM joinEmailActiveArcamax WHERE processed = 'N' AND listid = '$each_list' ORDER BY id DESC LIMIT 10000";
	$result = mysql_query($query);
	echo mysql_error();
	while ($row = mysql_fetch_object($result)) {
		$id = $row->id;
		$email = $row->email;
		$subcampid = $row->subcampid;
		$signup_date = $row->signup_date;
		
		$source = getSubcampIdDescriptiveName($subcampid);
		
		$subsource = "";
		$ipaddr = '';
		$active_data = "SELECT * FROM joinEmailActive WHERE email='$email' AND listid='$each_list'";
		$active_result = mysql_query($active_data);
		echo mysql_error();
		while ($more_row = mysql_fetch_object($active_result)) {
			$ipaddr = $more_row->ipaddr;
			$subsource = $more_row->source;
		}
		
		$data_array = array('email' => $email, 'first' => '', 'last' => '',
								'phone' => '', 'fax' => '', 'status' => 'Subscribed', 'format' => 'Both',
								'ipaddr' => $ipaddr, 'signup_date' => $signup_date, 'age_group' => '',
								'oldlistid' => '', 'subcampid' => $subcampid, 'source' => $source,
								'subsource' => $subsource, 'address1' => '', 'address2' => '',
								'city' => '', 'state' => '', 'zipcode' => '',
								'country' => 'US', 'gender' => '', 'birth_date' => '', 'contactId' => 0, 
								'sub_array' => array($new_listid), 'unsub_array' => array());
		$send_result = sendToCampaigner($data_array);
	
		$result_code = trim(getXmlValueByTag($send_result,'ResultCode'));
		$contactId = trim(getXmlValueByTag($send_result,'ContactId'));
		$email = trim(getXmlValueByTag($send_result,'ContactUniqueIdentifier'));
			
		// Record ID and email only if it's success
		if ($email !='' && ctype_digit($contactId) && $contactId !='') {
			$campaignerContacts = "INSERT IGNORE INTO campaignerContacts (id, email) VALUES (\"$contactId\",\"$email\")";
			$campaignerContacts_result = mysql_query($campaignerContacts);
			echo mysql_error();
		}
		
		$send_result = addslashes($send_result);
		
		$update = "UPDATE joinEmailActiveArcamax SET processed='Y', campaigner_results = \"$send_result\" WHERE id='$id'";
		$update_result = mysql_query($update);
		echo mysql_error();
		
		$iCount++;
	}
	mail("samirp@junemedia.com,leonz@junemedia.com","$x List $each_list completed: $iCount","List $each_list completed: $iCount","From:samirp@silvercarrot.com");
}

}



?>

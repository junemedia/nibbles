<?php

include_once("JSON.php");

function CreateUpdateCampaign ($data_array) {
    $opts = array('ssl' => array('ciphers'=>'DHE-RSA-AES256-SHA:DHE-DSS-AES256-SHA:AES256-SHA:KRB5-DES-CBC3-MD5:KRB5-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:EDH-DSS-DES-CBC3-SHA:DES-CBC3-SHA:DES-CBC3-MD5:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA:AES128-SHA:RC2-CBC-MD5:KRB5-RC4-MD5:KRB5-RC4-SHA:RC4-SHA:RC4-MD5:RC4-MD5:KRB5-DES-CBC-MD5:KRB5-DES-CBC-SHA:EDH-RSA-DES-CBC-SHA:EDH-DSS-DES-CBC-SHA:DES-CBC-SHA:DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5:EXP-KRB5-DES-CBC-MD5:EXP-KRB5-RC2-CBC-SHA:EXP-KRB5-DES-CBC-SHA:EXP-EDH-RSA-DES-CBC-SHA:EXP-EDH-DSS-DES-CBC-SHA:EXP-DES-CBC-SHA:EXP-RC2-CBC-MD5:EXP-RC2-CBC-MD5:EXP-KRB5-RC4-MD5:EXP-KRB5-RC4-SHA:EXP-RC4-MD5:EXP-RC4-MD5'));
    $client = new SoapClient('https://ws.campaigner.com/2013/01/campaignmanagement.asmx?WSDL',  array("encoding"=>"ISO-8859-1",'soap_version'=> 'SOAP_1_1',
                               'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                               'stream_context' => stream_context_create($opts),
                               'trace' => 1,'exceptions' => 0,'connection_timeout' => 300)); 

	foreach ($data_array as $key => $value) { $$key = $value; }

	if ($campaign_id == 0) { $campaign_id = NULL; }
	
	$response = $client->CreateUpdateCampaign(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'campaignData' => Array(
	        'Id' => $campaign_id,
			'CampaignName' => $campaign_name,
			'CampaignSubject' => $subject_line,
			'CampaignFormat' => 'HTML',
			'Status' => 'Complete',
			'CampaignType' => 'None',
			'HtmlContent' => $html_code,
			'FromName'=> $from_name,
			'FromEmailId' => $from_email_id,
			'ReplyEmailId' => $from_email_id,
			'TrackReplies' => false,
			'AutoReplyMessageId'=> '0',
			'ProjectId'=>0,
			'IsWelcomeCampaign'=>false,
			'DateModified'=>date(DATE_ATOM),
			'Encoding'=>'UTF_8'
			)
	));
	return $client->__getLastResponse();
}




function ListMediaFilesCampaigner() {
	$client = new SoapClient('https://ws.campaigner.com/2013/01/contentmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	$response = $client->ListMediaFiles(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    ));
	return $client->__getLastResponse();
}



function UploadMediaFileCampaigner($image) {
	if (strlen(basename($image)) >= 45) {
		$image_file_name = substr(md5(uniqid(rand(), true)),0,10).substr(basename($image),-40);
	} else {
		$image_file_name = substr(md5(uniqid(rand(), true)),0,5).basename($image);
	}
	
	
	$image_file_name = str_replace(' ', '_', $image_file_name);

	$client = new SoapClient('https://ws.campaigner.com/2013/01/contentmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	
	$response = $client->UploadMediaFile(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'fileName' => basename($image_file_name),
	    'fileContentBase64' => base64_encode(file_get_contents($image)),
	    ));
	return $client->__getLastResponse();
}

function getLocationByIp($ipaddr)
{
	$result = array();
	if(!empty($ipaddr))
	{
		$url = "http://freegeoip.net/json/$ipaddr";
		$content = file_get_contents($url);
		$ipInfo = json_decode($content, true);
		if(isset($ipInfo['region_name']))
		{
			$result['region'] = $ipInfo['region_name'];
		}
		if(isset($ipInfo['zipcode']))
		{
			$result['zipcode'] = $ipInfo['zipcode'];
		}
	}
	
	return $result;
}

function sendToCampaigner ($data_array) {
    return true;
	$client = new SoapClient('https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL',  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
	
	$email = $data_array['email'];			$first = $data_array['first'];			$last = $data_array['last'];
	$phone = $data_array['phone'];			$fax = $data_array['fax'];
	$status = $data_array['status'];	// Subscribed, Unsubscribed, HardBounce, SoftBounce, Pending
	$format = $data_array['format'];	// Text, HTML, Both
	if ($format == '') { $format = 'Both'; }
	
	$ipaddr = $data_array['ipaddr'];
	if ($ipaddr == '') { $ipaddr = trim($_SERVER['REMOTE_ADDR']); }
	$signup_date = $data_array['signup_date'];	// yyyy-mm-dd
	if ($signup_date == '') { $signup_date = date('Y-m-d'); }
	
	$age_group = $data_array['age_group'];		$oldlistid = $data_array['oldlistid'];		$subcampid = $data_array['subcampid'];
	$source = $data_array['source'];			$subsource = $data_array['subsource'];		$address1 = $data_array['address1'];
	$address2 = $data_array['address2'];		$city = $data_array['city'];				$state = $data_array['state'];
	$zipcode = $data_array['zipcode'];			$country = $data_array['country'];	// country code US
	$gender = $data_array['gender'];	// M or F
	$birth_date = $data_array['birth_date'];	// yyyy-mm-dd
	
	
	$contactId = $data_array['contactId'];		if ($contactId == '') { $contactId = 0; }
	
	$sub_array = $data_array['sub_array'];		$unsub_array = $data_array['unsub_array'];
	
	$response = $client->ImmediateUpload(Array(
	    'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
	    'UpdateExistingContacts' => true,
	    'TriggerWorkflow' => false,
	    'contacts' => Array(
	        'ContactData' => Array(
	            Array(   
	                'IsTestContact' => false,	// if set to 'true', then specified email will receive test email
	                'ContactKey' => Array(
	                	'ContactId' => $contactId,	// provide contact id for existing subscriber
	                    'ContactUniqueIdentifier' => $email,
	                ),
	                'EmailAddress' => $email,'FirstName' => $first,'LastName' => $last,'PhoneNumber' => $phone,'Fax' => $fax,'Status' => $status,'MailFormat' => $format,
	                'CustomAttributes' => array(0 =>                		
	                		(($state !='') ? array("_" => $state, "Id" => 3834448) : array("_" => "", "Id" => 3834448)),	// state
							(($zipcode !='') ? array("_" => $zipcode, "Id" => 3833693) : array("_" => "", "Id" => 3833693)) // zipcode
	            )
	        )
	    )
	)));
	
	return $client->__getLastResponse();
	
	/*
	Custom Fields IDs
	oldlistid	3834333				IsDailyInsider	3844903
	subcampid	3834288				IsFitFabLivingSOLO	3844893
	signup_datetime	3834363			IsDailyRecipes	3844883
	ipaddress	3834378				IsRecipe4LivingSOLO	3844873
	source	3834388					IsBudgetCooking	3844863
	subsource	3834408				IsQuickEasyRecipes	3844853
	address1	3834418				IsDietInsider	3844843
	address2	3834428				IsCrockpotCreations	3844833
	city	3834438					IsCasseroleCooking	3844823
	state	3834448					IsCopycatClassics	3844813
	zipcode	3833693					IsMakingItWork	3844803
	country	3834458					IsWorkItMomSOLO	3844793
	gender	3834468					IsDiabeticFriendlyDishes	3844783
	birth_date	3834483				IsTheFeedBySavvyFork	3844768
	age_group	3834493				
	*/
}





function getSubcampIdDescriptiveName ($subcampid) {
	$get_id = "SELECT notes FROM subcampid WHERE subcampid='$subcampid' LIMIT 1";
	$result = mysql_query($get_id);
	while ($id_row = mysql_fetch_object($result)) {
		return trim($id_row->notes);
	}
}


function LookupNewListIdByOldListId ($old_list_id) {
	if (strlen($old_list_id) == 3 && ctype_digit($old_list_id)) {
		$get_id = "SELECT newListId FROM joinLists WHERE listid='$old_list_id' LIMIT 1";
		$result = mysql_query($get_id);
		while ($id_row = mysql_fetch_object($result)) {
			return trim($id_row->newListId);
		}
	} else {
		return false;
	}
}



function getXmlValueByTag($inXmlset,$needle) {
	$resource    =    xml_parser_create(); //Create an XML parser
	xml_parse_into_struct($resource, $inXmlset, $outArray); // Parse XML data into an array structure
	xml_parser_free($resource); //Free an XML parser
	for($i=0;$i<count($outArray);$i++) {
		if($outArray[$i]['tag']==strtoupper($needle)){
			$tagValue    =    $outArray[$i]['value'];
		}
	}
	return $tagValue;
}



function LookupImpressionWise($email_addr) {
	$isValid = true;
	$isValid_msg = 'Y';
	$sPostingUrl = "http://post.impressionwise.com/fastfeed.aspx?code=560020&pwd=SilCar&email=$email_addr";
	$response = strtolower(file_get_contents($sPostingUrl));
	
	//	code=560020&pwd=SilCar&email=testme@impressionwise.com&result=Key&NPD=NA&TTP=0.16
	$pieces = explode("&", $response);
	foreach ($pieces as $pair) {
		$data = explode("=", $pair);
		$$data[0] = $data[1];
	}
	
	if (in_array($result, array("invalid", "seed", "trap", "mole"))) {
		$isValid = false;
		$isValid_msg = 'N';
	}
	
	if ($result == 'retry') {
		mail('samirp@silvercarrot.com','Impression Wise RETRY',$sPostingUrl."\n\n\n".$response);
	}
	
	
	$log_iw = "INSERT IGNORE INTO impression_wise (dateTime,email,isValid,npd,result,ttp,response) 
				VALUES (NOW(),\"$email_addr\",\"$isValid_msg\",\"$npd\",\"$result\",\"$ttp\",\"$response\")";
	$result = mysql_query($log_iw);
	
	return $isValid;
}




function getBounceCountFromArcamax($email) {
	$post_string = "email=$email&encoding=JSON";
	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	$server_response = '';
	if ($rSocketConnection) {
		fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rSocketConnection, "Host: $sHostPart\r\n");
		fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
		fputs($rSocketConnection, $post_string);
		while(!feof($rSocketConnection)) {
			$server_response .= fgets($rSocketConnection, 1024);
		}
		fclose($rSocketConnection);
	}
	$obj = json_decode(substr($server_response,strpos($server_response, '{'),strlen($server_response)));
	return $_SESSION['bouncecount'] = trim($obj->{'bouncecount'});
}




function Arcamax($email,$listid,$subcampid,$user_ip,$type) {
	$server_response = '';
	
	$extra = '';
	if ($_SESSION['fname'] !='') { $extra .= "&csi_fname=".$_SESSION['fname']; }
	if ($_SESSION['lname'] !='') { $extra .= "&csi_lname=".$_SESSION['lname']; }
	if ($_SESSION['addr1'] !='') { $extra .= "&csi_addr1=".$_SESSION['addr1']; }
	if ($_SESSION['addr2'] !='') { $extra .= "&csi_addr2=".$_SESSION['addr2']; }
	if ($_SESSION['city'] !='') { $extra .= "&csi_city=".$_SESSION['city']; }
	if ($_SESSION['state'] !='') { $extra .= "&csi_state=".$_SESSION['state']; }
	if ($_SESSION['zip'] !='') { $extra .= "&csi_zip=".$_SESSION['zip']; }
	if ($_SESSION['gender'] !='') { $extra .= "&csi_gender=".$_SESSION['gender']; }
	if ($_SESSION['phone_1'] !='') { $extra .= "&csi_phone_1=".$_SESSION['phone_1']; }
	if ($_SESSION['phone_2'] !='') { $extra .= "&csi_phone_2=".$_SESSION['phone_2']; }
	if ($_SESSION['phone_3'] !='') { $extra .= "&csi_phone_3=".$_SESSION['phone_3']; }
	if ($_SESSION['day'] !='') { $extra .= "&csi_day=".$_SESSION['day']; }
	if ($_SESSION['month'] !='') { $extra .= "&csi_month=".$_SESSION['month']; }
	if ($_SESSION['year'] !='') { $extra .= "&csi_year=".$_SESSION['year']; }
	if ($_SESSION['country'] !='') { $extra .= "&csi_country=".$_SESSION['country']; }
	
	if ($type == 'sub') {
		$post_string = "email=$email&sublists=$listid&subcampid=$subcampid&ipaddr=$user_ip".$extra;
	} else {
		$post_string = "email=$email&unsublists=$listid&subcampid=$subcampid&ipaddr=$user_ip".$extra;
	}
	
	
	$temp_post_data = addslashes($post_string);
	$insert_post_data = "INSERT IGNORE INTO querystring (dateTimeAdded,postdata) VALUES (NOW(), \"$temp_post_data\")";
	$insert_post_data_result = mysql_query($insert_post_data);
	

	$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
	$aUrlArray = explode("//", $sPostingUrl);
	$sUrlPart = $aUrlArray[1];

	// separate host part and script path
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
			
	if (strstr($sPostingUrl, "https:")) {
		$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	} else {
		$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	}
			
	if ($rSocketConnection) {
		fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
		fputs($rSocketConnection, "Host: $sHostPart\r\n");
		fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
		fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
		fputs($rSocketConnection, "User-Agent: MSIE\r\n");
		fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
		fputs($rSocketConnection, "Connection: close\r\n\r\n");
		fputs($rSocketConnection, $post_string);
				
		while(!feof($rSocketConnection)) {
			$server_response .= fgets($rSocketConnection, 1024);
		}
		fclose($rSocketConnection);
			
		/*if (strstr($server_response,"error")) {
			$message = "Error: $server_response";
		} else {
			$message = "Success: Unsub Successful!";
		}*/
	} else {
		$server_response = "$errstr ($errno)<br />\r\n";
	}
	
	return addslashes($server_response);
}









/*function BullseyeBriteVerifyCheck ($email) {
	$handle = fopen("http://www3.tendollars.com/BriteVerifyForSubscriptionCenter.aspx?email=$email&source=subcenter", "rb");
	$server_response = stream_get_contents($handle);
	fclose($handle);
	
	if (strstr($server_response,'valid') || strstr($server_response,'unknown')) {
		$return_value = true;
	} else {
		$return_value = false;
	}
	
	if (strstr($server_response,'not valid') || strstr($server_response,'invalid')) {
		$return_value = false;
	}
	
	$server_response = addslashes($server_response);
	$user_ip = trim($_SERVER['REMOTE_ADDR']);
	$insert_bv_log = "INSERT INTO BullseyeBriteVerifyCheck (email,dateTimeAdded,ip,response)
				VALUES (\"$email\", NOW(), \"$user_ip\",\"$server_response\")";
	$insert_bv_log_result = mysql_query($insert_bv_log);
	
	return $return_value;
}*/

function BullseyeBriteVerifyCheck ($email) {
	$emailInfo = array();
	if(!empty($email))
	{
		$result = mysql_query("SELECT * FROM email_validation WHERE date(dateAdded) >= date_sub(curdate(),interval 1 day) and email = \"$email\"");
		$emailInfo = mysql_fetch_array($result,MYSQL_ASSOC);
		if (empty($emailInfo)) {
			$url = "https://bpi.briteverify.com/emails.json?address=$email&apikey=ad6d5755-ff3e-4a0b-8d63-c61bcffd57b1";
			$content = file_get_contents($url);
			$emailInfo = json_decode($content, true);
			
			$ipaddress = $_SERVER['REMOTE_ADDR'];
			
			if(!empty($emailInfo))
			{
				//Cache the new email address
				$sql = 'INSERT IGNORE INTO email_validation (email,status,error_code,error,dateAdded,ipaddress) VALUES ("'.$emailInfo["address"].'","'.$emailInfo["status"].'","'.$emailInfo["error_code"].'", "'.$emailInfo["error"].'", NOW(),"'.$ipaddress.'")';
				$result = mysql_query($sql);
			}
		} 
	}
	
	if(!empty($emailInfo) && ($emailInfo["status"]=="valid" || $emailInfo["status"]=="unknown" || $emailInfo["status"]=="accept all" || $emailInfo["status"]=="accept_all"))
	{
		return true;
	}
	else
	{
		return false;
	}
}








function BriteVerify ($email) {
	$data = "email[address]=$email&apikey=ad6d5755-ff3e-4a0b-8d63-c61bcffd57b1";
	$fp = fsockopen('ssl://api.briteverify.com', 443);

	fputs($fp, "POST /emails/verify.xml HTTP/1.1\r\n");
	fputs($fp, "Host: api.briteverify.com\r\n");
	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-length: ". strlen($data) ."\r\n");
	fputs($fp, "Connection: close\r\n\r\n");
	fputs($fp, $data);

	$result = '';
	while(!feof($fp)) { $result .= fgets($fp, 128); }
	fclose($fp);

	$result = explode("\r\n\r\n", $result, 2);	// split the result header from the content
	$content = isset($result[1]) ? $result[1] : '';//$header = isset($result[0]) ? $result[0] : '';

	return $content;	//return array($header, $content);
}

?>

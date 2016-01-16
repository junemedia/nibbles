<?php

include_once("JSON.php");
function sendSweepsToCampaigner ($data_array) {
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
	
	if(empty($state) || empty($zipcode))
	{	
		$ipDetail = getLocationByIp($ipaddr);
		if(!empty($ipDetail))
		{
			if(empty($state) && isset($ipDetail['region']))
			{
				$state = $ipDetail['region'];
			}
			
			if(empty($zipcode) && isset($ipDetail['zipcode']))
			{
				$zipcode = $ipDetail['zipcode'];
			}
		}			
	}
	
	$contactId = $data_array['contactId'];		if ($contactId == '') { $contactId = 0; }
	
	$sub_array = $data_array['sub_array'];		$unsub_array = $data_array['unsub_array'];
	$alreadyExist = $data_array['alreadyExist'];
	$updateArray = '';
	
	if($alreadyExist)
	{
		//if the email already exists in campaigner, but the subcampid is empty.
		//Fill in the subcampid
		if(!empty($subcampid))
		{
                    $updateArray = array(0 =>
                        (($ipaddr !='') ? array("_" => $ipaddr, "Id" => 3834378) : array("_" => "", "Id" => 3834378)),	// ipaddr
                                        (($subcampid !='') ? array("_" => $subcampid, "Id" => 3834288) : array("_" => "", "Id" => 3834288)),	// subcampid
                        (($source !='') ? array("_" => $source, "Id" => 3834388) : array("_" => "", "Id" => 3834388)),	// source
                        (($subsource !='') ? array("_" => $subsource, "Id" => 3834408) : array("_" => "", "Id" => 3834408)),	// subsource
                        (($city !='') ? array("_" => $city, "Id" => 3834438) : array("_" => "", "Id" => 3834438)),	// city
                        (($state !='') ? array("_" => $state, "Id" => 3834448) : array("_" => "", "Id" => 3834448)),	//state
                        (($zipcode !='') ? array("_" => $zipcode, "Id" => 3833693) : array("_" => "", "Id" => 3833693)),	// zipcode
                        (($country !='') ? array("_" => $country, "Id" => 3834458) : array("_" => "", "Id" => 3834458)),	// country

                        // PROCESS SUBSCRIBERS
                        (in_array(3844883, $sub_array) ? array("_" => "true", "Id" => 3844883) : array("_" => "", "Id" => 3844883)),    // IsDailyRecipes
                        (in_array(3844873, $sub_array) ? array("_" => "true", "Id" => 3844873) : array("_" => "", "Id" => 3844873)),    // IsRecipe4LivingSOLO
                        (in_array(4195798, $sub_array) ? array("_" => "true", "Id" => 4195798) : array("_" => "", "Id" => 4195798)),    // IsEditorsChoice
                        (in_array(4195808, $sub_array) ? array("_" => "true", "Id" => 4195808) : array("_" => "", "Id" => 4195808)),    // IsR4LSeasonal
                        (in_array(4195818, $sub_array) ? array("_" => "true", "Id" => 4195818) : array("_" => "", "Id" => 4195818)),    // IsMoreWeLove

                        (in_array(4240263, $sub_array) ? array("_" => "true", "Id" => 4240263) : array("_" => "", "Id" => 4240263)),    // IsBetterRecipesDaily
                        (in_array(4240273, $sub_array) ? array("_" => "true", "Id" => 4240273) : array("_" => "", "Id" => 4240273)),    // IsBetterRecipesSOLO                            
                        (in_array(4362328, $sub_array) ? array("_" => "true", "Id" => 4362328) : array("_" => "", "Id" => 4362328)),    // IsBetterRecipesSweeps
                        (in_array(4362338, $sub_array) ? array("_" => "true", "Id" => 4362338) : array("_" => "", "Id" => 4362338)),    // IsRecipe4LivingSweeps
                    );
		}else
		{
                    //if the email already exists in campaigner and has the subcampid.
                    //Don't override the subcampid
                    $updateArray = array(0 =>
                        (($ipaddr !='') ? array("_" => $ipaddr, "Id" => 3834378) : array("_" => "", "Id" => 3834378)),	// ipaddr
                        (($city !='') ? array("_" => $city, "Id" => 3834438) : array("_" => "", "Id" => 3834438)),	// city
                        (($state !='') ? array("_" => $state, "Id" => 3834448) : array("_" => "", "Id" => 3834448)),	//state
                        (($zipcode !='') ? array("_" => $zipcode, "Id" => 3833693) : array("_" => "", "Id" => 3833693)),	// zipcode
                        (($country !='') ? array("_" => $country, "Id" => 3834458) : array("_" => "", "Id" => 3834458)),	// country

                        // PROCESS SUBSCRIBERS
                        (in_array(3844883, $sub_array) ? array("_" => "true", "Id" => 3844883) : array("_" => "", "Id" => 3844883)),    // IsDailyRecipes
                        (in_array(3844873, $sub_array) ? array("_" => "true", "Id" => 3844873) : array("_" => "", "Id" => 3844873)),    // IsRecipe4LivingSOLO
                        (in_array(4195798, $sub_array) ? array("_" => "true", "Id" => 4195798) : array("_" => "", "Id" => 4195798)),    // IsEditorsChoice
                        (in_array(4195808, $sub_array) ? array("_" => "true", "Id" => 4195808) : array("_" => "", "Id" => 4195808)),    // IsR4LSeasonal
                        (in_array(4195818, $sub_array) ? array("_" => "true", "Id" => 4195818) : array("_" => "", "Id" => 4195818)),    // IsMoreWeLove

                        (in_array(4240263, $sub_array) ? array("_" => "true", "Id" => 4240263) : array("_" => "", "Id" => 4240263)),    // IsBetterRecipesDaily
                        (in_array(4240273, $sub_array) ? array("_" => "true", "Id" => 4240273) : array("_" => "", "Id" => 4240273)),    // IsBetterRecipesSOLO                            
                        (in_array(4362328, $sub_array) ? array("_" => "true", "Id" => 4362328) : array("_" => "", "Id" => 4362328)),    // IsBetterRecipesSweeps
                        (in_array(4362338, $sub_array) ? array("_" => "true", "Id" => 4362338) : array("_" => "", "Id" => 4362338)),    // IsRecipe4LivingSweeps
                    );
		}
	}else
	{	
            //New email in campaigner
            $updateArray= array(0 =>
                (($ipaddr !='') ? array("_" => $ipaddr, "Id" => 3834378) : array("_" => "", "Id" => 3834378)),	// ipaddr
                (($oldlistid !='') ? array("_" => $oldlistid, "Id" => 3834333) : array("_" => "", "Id" => 3834333)),	// oldlistid
                (($subcampid !='') ? array("_" => $subcampid, "Id" => 3834288) : array("_" => "", "Id" => 3834288)),	// subcampid
                (($signup_date !='') ? array("_" => $signup_date, "Id" => 3834363) : array("_" => "", "Id" => 3834363)),	//signup_datetime
                (($source !='') ? array("_" => $source, "Id" => 3834388) : array("_" => "", "Id" => 3834388)),	// source
                (($subsource !='') ? array("_" => $subsource, "Id" => 3834408) : array("_" => "", "Id" => 3834408)),	// subsource
                (($address1 !='') ? array("_" => $address1, "Id" => 3834418) : array("_" => "", "Id" => 3834418)),	// address1
                (($address2 !='') ? array("_" => $address2, "Id" => 3834428) : array("_" => "", "Id" => 3834428)),	// address2
                (($city !='') ? array("_" => $city, "Id" => 3834438) : array("_" => "", "Id" => 3834438)),	// city
                (($state !='') ? array("_" => $state, "Id" => 3834448) : array("_" => "", "Id" => 3834448)),	//state
                (($zipcode !='') ? array("_" => $zipcode, "Id" => 3833693) : array("_" => "", "Id" => 3833693)),	// zipcode
                (($country !='') ? array("_" => $country, "Id" => 3834458) : array("_" => "", "Id" => 3834458)),	// country
                (($gender !='') ? array("_" => $gender, "Id" => 3834468) : array("_" => "", "Id" => 3834468)),	// gender
                (($birth_date !='') ? array("_" => $birth_date, "Id" => 3834483) : array("_" => "", "Id" => 3834483)),	// birth_date
                (($age_group !='') ? array("_" => $age_group, "Id" => 3834493) : array("_" => "", "Id" => 3834493)),	// age_group


                // PROCESS SUBSCRIBERS
                (in_array(3844883, $sub_array) ? array("_" => "true", "Id" => 3844883) : array("_" => "", "Id" => 3844883)),    // IsDailyRecipes
                (in_array(3844873, $sub_array) ? array("_" => "true", "Id" => 3844873) : array("_" => "", "Id" => 3844873)),    // IsRecipe4LivingSOLO
                (in_array(4195798, $sub_array) ? array("_" => "true", "Id" => 4195798) : array("_" => "", "Id" => 4195798)),    // IsEditorsChoice
                (in_array(4195808, $sub_array) ? array("_" => "true", "Id" => 4195808) : array("_" => "", "Id" => 4195808)),    // IsR4LSeasonal
                (in_array(4195818, $sub_array) ? array("_" => "true", "Id" => 4195818) : array("_" => "", "Id" => 4195818)),    // IsMoreWeLove

                (in_array(4240263, $sub_array) ? array("_" => "true", "Id" => 4240263) : array("_" => "", "Id" => 4240263)),    // IsBetterRecipesDaily
                (in_array(4240273, $sub_array) ? array("_" => "true", "Id" => 4240273) : array("_" => "", "Id" => 4240273)),    // IsBetterRecipesSOLO                            
                (in_array(4362328, $sub_array) ? array("_" => "true", "Id" => 4362328) : array("_" => "", "Id" => 4362328)),    // IsBetterRecipesSweeps
                (in_array(4362338, $sub_array) ? array("_" => "true", "Id" => 4362338) : array("_" => "", "Id" => 4362338)),    // IsRecipe4LivingSweeps
            );
	}
        /*
        print_r(Array(   
	                'IsTestContact' => false,	// if set to 'true', then specified email will receive test email
	                'ContactKey' => Array(
	                	'ContactId' => $contactId,	// provide contact id for existing subscriber
	                    'ContactUniqueIdentifier' => $email,
	                ),
	                'EmailAddress' => $email,'FirstName' => $first,'LastName' => $last,'PhoneNumber' => $phone,'Fax' => $fax,'Status' => $status,'MailFormat' => $format,
	                'CustomAttributes' => $updateArray
					));
         */
        
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
	                'CustomAttributes' => $updateArray
					)))));
	
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

if(!function_exists('tryMail'))
{
	function tryMail($to, $subject, $message, $headers, $times = 3){
		for($i=0; $i<$times; $i++){
			if(mail($to, $subject, $message, $headers)){
				// Sending mail success, exit
			   break; 
			}else{
				$body = $to."\n------------------\n".$subject."\n------------------\n".$message."\n------------------\n".$headers;
				mail('leonz@junemedia.com', 'Mail Error', $body);
			}
		}
	}
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




?>

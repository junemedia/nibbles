<?php

//ini_set('default_socket_timeout', 60);
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(-1);

$time_start = microtime(true);

$url = 'https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL';
			
$client = new SoapClient($url,  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
						   'soap_version'=> 'SOAP_1_1',
						   'trace' => true,
						   'connection_timeout' => 300));

$authentication = array("Username"=>'api@junemedia.dom',"Password"=>'v$k}^4]zJ8!!');

/*
$url = 'https://ws.campaigner.com/2013/01/listmanagement.asmx?WSDL';
$response = $client->ListContactGroups(Array('authentication' => $authentication));
echo "<pre>"; 
var_dump($response);
echo "</pre>";
exit;
*/
	
	
$response = $client->ImmediateUpload(Array(
    'authentication' => $authentication,
    'UpdateExistingContacts' => true,
    'TriggerWorkflow' => false,
    'contacts' => Array(
        'ContactData' => Array(
            Array(   
                'IsTestContact' => false,	// if set to 'true', then specified email will receive test email
                'ContactKey' => Array(
                	'ContactId' => 0,	// provide contact id for existing subscriber
                    'ContactUniqueIdentifier' => 'samirp@junemedia.com,leonz@junemedia.com',
                ),
                'EmailAddress' => 'samirp@junemedia.com,leonz@junemedia.com',	// email address that we are adding/updating
                'FirstName' => "Samir",
                'LastName' => "Patel",
                'PhoneNumber' => "312-724-9440",
                'Fax' => "847-205-9340",
                'Status' => 'Subscribed',	// Subscribed, Unsubscribed, HardBounce, SoftBounce, Pending
                'MailFormat' => 'Both',	// Text, HTML, Both
                'AddToGroup' => array(2695460,2695470),	// listid to subscribe
                'RemoveFromGroup' => array(2695439), // listid to unsub
                'CustomAttributes' => array("subcampid" => 1234, "zipcode" => "60606"),
            )
        )
    )
));

echo "<pre>";
//print_r($client->__getLastResponse());
//print_r($client->__getLastResponseHeaders()."\n\n");
//print_r($client->__getLastRequestHeaders());
//print_r($client->__getLastRequest());
	
var_dump($response);
echo "</pre>";

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<b>Total Execution Time:</b> '.$execution_time.' Seconds';

?>

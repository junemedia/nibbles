<?php

ini_set('default_socket_timeout', 60);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

$time_start = microtime(true);

$url = 'https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL';
			
$client = new SoapClient($url,  array('exceptions' => false,
						   'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
						   'soap_version'=> 'SOAP_1_1',
						   'trace' => true,
						   'connection_timeout' => 300));

$authentication = array("Username"=>'api@junemedia.dom',"Password"=>'v$k}^4]zJ8!!');

$response = $client->ImmediateUpload(Array(
    'authentication' => $authentication,
    'UpdateExistingContacts' => true,
    'TriggerWorkflow' => false,
    'contacts' => Array(
        'ContactData' => Array(
            Array(   
                'IsTestContact' => false,
                'ContactKey' => Array(
                	'ContactId' => 0,
                    'ContactUniqueIdentifier' => 'samirp@silvercarrot.com',
                ),
                'EmailAddress' => 'samirp@silvercarrot.com',
            )
        )
    )
));

echo "<pre>";
var_dump($response);
echo "</pre>";

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo '<b>Total Execution Time:</b> '.$execution_time.' Seconds';

?>

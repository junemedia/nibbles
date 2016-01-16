<?php

require_once(dirname(__FILE__) . '/../config.inc.php');

require_once(CAMPAIGNER_LEON_ROOT . 'lib/campaigner.php');

function CampaignListContactGroups(){
    $client = new SoapClient('https://ws.campaigner.com/2013/01/listmanagement .asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
    $response = $client->ListContactGroups(
                                        Array(
                                            'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0')
                                        )
                                );
    return $client->__getLastResponse();
    //return $response;      
}

var_dump(CampaignListContactGroups());
?>

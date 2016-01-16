<?php



function CampaignListAttributes(){
    $client = new SoapClient('https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
    $response = $client->ListAttributes(
                            Array(
                                'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
                                'filter'=>array(
                                    'IncludeAllDefaultAttributes' => true,
                                    'IncludeAllCustomAttributes' => true,
                                    'IncludeAllSystemAttributes' => true
                               )
                            ));
    return $response;
    //return $client->__getLastResponse();    
}

//echo CampaignListAttributes();

function CampaignDownloadListAttributes(){
    
    // Get the latest attributeXML
    echo "Getting Attribute from Campaigner \n\r";
    $attributeXML = CampaignListAttributes();
    
    //Let's truncate the table for insert:
    echo "Truncate Table LeonCampaignContactAttribute \n\r";
    mysql_query("Truncate table LeonCampaignContactAttribute");
    
    foreach($attributeXML->ListAttributesResult->AttributeDescription as $row){
        $query = "INSERT INTO `LeonCampaignContactAttribute` (
        `Id`, `StaticAttributeId`, `IsKey`, `AttributeType`, `Name` , `DefaultValue` ,  `DataType` ,  `LastModifiedDate` )
        VALUES (
        '$row->Id', '$row->StaticAttributeId', '$row->IsKey', '$row->AttributeType', '$row->Name', '$row->DefaultValue', '$row->DataType', '$row->LastModifiedDate')";
        
        $r = mysql_query($query);
        if($r){
            echo "==>Insert Attribute Record: $row->Id \t [$row->Name] \n\r";
        }else{
            echo "==>==>Failed Insert Attribute Record: $row->Id \t [$row->Name] \r\r";
        }
        
    }

}
//CampaignDownloadListAttributes();


function UploadRow($row){
    $id = $row["Contactid"];
    $email = $row["ContactUniqueIdentifier"];
        
    $sub_array = array();
    $unsub_array = array();

    // Add the openx ad sequence
    $openx_base = 5;
    $openx_ads_sequence = (int)($id);   //Open X Unique Sequence
    $openx_ads_tag_1 = (int)($openx_ads_sequence . "1");  // Open X Ad Tag 1
    $openx_ads_tag_2 = (int)($openx_ads_sequence . "2");  // Open X Ad Tag 2
    $openx_ads_tag_3 = (int)($openx_ads_sequence . "3");  // Open X Ad Tag 3
    $openx_ads_tag_4 = (int)($openx_ads_sequence . "4");  // Open X Ad Tag 4
    $openx_ads_tag_5 = (int)($openx_ads_sequence . "5");  // Open X Ad Tag 5
    
    
    $data_array = array('ContactId'=> $row["Contactid"], 'email' =>$email, 'first' => $row["FirstName"], 'last' => $row["LastName"],
                        'phone' =>'', 'fax'=>'', 'status' => 'Subscribed', 'format' => 'Both',
                        'openx_ads_sequence' => $openx_ads_sequence,
                        'openx_ads_tag_1' => $openx_ads_tag_1,
                        'openx_ads_tag_2' => $openx_ads_tag_2,
                        'openx_ads_tag_3' => $openx_ads_tag_3,
                        'openx_ads_tag_4' => $openx_ads_tag_4,
                        'openx_ads_tag_5' => $openx_ads_tag_5     
                            
                            );
    $send_result = updateCampaignerOpenX($data_array);
    return $openx_ads_sequence;
    
    //print_r($send_result);
            
}



function updateCampaignerOpenX ($data_array) {
    $client = new SoapClient('https://ws.campaigner.com/2013/01/contactmanagement.asmx?WSDL',  array('exceptions' => false,
                           'compression'=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,'soap_version'=> 'SOAP_1_1','trace' => true,'connection_timeout' => 300));
    
    $email = $data_array['email'];
    $first = $data_array['first'];
    $last = $data_array['last'];
    $phone = $data_array['phone'];
    $fax = $data_array['fax'];
    $status = $data_array['status'];    // Subscribed, Unsubscribed, HardBounce, SoftBounce, Pending
    $format = $data_array['format'];    // Text, HTML, Both

    
    // Add the openx ad sequence
    $openx_ads_sequence = $data_array["openx_ads_sequence"];      // Open X Unique Sequence
    $openx_ads_tag_1 = $data_array["openx_ads_tag_1"];            // Open X Ad Tag 1
    $openx_ads_tag_2 = $data_array["openx_ads_tag_2"];            // Open X Ad Tag 2
    $openx_ads_tag_3 = $data_array["openx_ads_tag_3"];            // Open X Ad Tag 3
    $openx_ads_tag_4 = $data_array["openx_ads_tag_4"];            // Open X Ad Tag 4
    $openx_ads_tag_5 = $data_array["openx_ads_tag_5"];            // Open X Ad Tag 5    
    
    
    $contactId = $data_array['ContactId'];        if ($contactId == '') { $contactId = 0; }
    
    $response = $client->ImmediateUpload(Array(
        'authentication' => array("Username"=>'api@junemedia.dom',"Password"=>'zhijiage209H@0'),
        'UpdateExistingContacts' => true,
        'TriggerWorkflow' => false,
        'contacts' => Array(
            'ContactData' => Array(
                Array(   
                    'IsTestContact' => false,    // if set to 'true', then specified email will receive test email
                    'ContactKey' => Array(
                        'ContactId' => $contactId,    // provide contact id for existing subscriber
                        'ContactUniqueIdentifier' => $email,
                    ),
                    'EmailAddress' => $email,'FirstName' => $first,'LastName' => $last,'PhoneNumber' => $phone,'Fax' => $fax,'Status' => $status,'MailFormat' => $format,
                    'CustomAttributes' => array(0 =>
                            
                            //OpenX ads Unit
                            (($openx_ads_sequence !='') ? array("_" => $openx_ads_sequence, "Id" => 4173563) : array("_" => "", "Id" => 4173563)),      //Open X Unique Sequence
                            (($openx_ads_tag_1 !='') ? array("_" => $openx_ads_tag_1, "Id" => 4173573) : array("_" => "", "Id" => 4173573)),            // Open X Ad Tag 1
                            (($openx_ads_tag_2 !='') ? array("_" => $openx_ads_tag_2, "Id" => 4173658) : array("_" => "", "Id" => 4173658)),            // Open X Ad Tag 2
                            (($openx_ads_tag_3 !='') ? array("_" => $openx_ads_tag_3, "Id" => 4173668) : array("_" => "", "Id" => 4173668)),            // Open X Ad Tag 3
                            (($openx_ads_tag_4 !='') ? array("_" => $openx_ads_tag_4, "Id" => 4173678) : array("_" => "", "Id" => 4173678)),            // Open X Ad Tag 4
                            (($openx_ads_tag_5 !='') ? array("_" => $openx_ads_tag_5, "Id" => 4173688) : array("_" => "", "Id" => 4173688)),            // Open X Ad Tag 5
                            
                )
            )
        )
    )));
    
    return $client->__getLastResponse();
    
    /*
    Custom Fields IDs
    oldlistid    3834333                IsDailyInsider    3844903
    subcampid    3834288                IsFitFabLivingSOLO    3844893
    signup_datetime    3834363            IsDailyRecipes    3844883
    ipaddress    3834378                IsRecipe4LivingSOLO    3844873
    source    3834388                    IsBudgetCooking    3844863
    subsource    3834408                IsQuickEasyRecipes    3844853
    address1    3834418                IsDietInsider    3844843
    address2    3834428                IsCrockpotCreations    3844833
    city    3834438                    IsCasseroleCooking    3844823
    state    3834448                    IsCopycatClassics    3844813
    zipcode    3833693                    IsMakingItWork    3844803
    country    3834458                    IsWorkItMomSOLO    3844793
    gender    3834468                    IsDiabeticFriendlyDishes    3844783
    birth_date    3834483                IsTheFeedBySavvyFork    3844768
    age_group    3834493                
    */
}


function formatBytes($size, $precision = 2) { 

    $base = log($size) / log(1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];

}

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

function LeonSendEmail($email,$titre,$corps,$lien,$attach = '') {
    require_once("mail.function.php");
    $delim="\r\n";
    CreateEmail("$email","$titre", "$corps".$delim.$delim."Thanks,".$delim.$delim."Leon.");
    if ($attach<>'') AddAttachment($attach); // full file path
    CloseAndSend();
} // SendEmail procedure


function printMemoryInfo(){
    if(phpversion() >= '5.3.1') {echo " - Memory usage [" . formatBytes(memory_get_peak_usage(),2).' bytes' . "]\n\r";}else{echo " \n\r";}
}

function convertUTCtime($time){
    $str = strtotime($time);
    date_default_timezone_set('America/Chicago');
    return date("Y-m-d H:i:s",$str);
}

function getAttrNameByListId($listId){
    // Also defined in subctr.popularliving.com/subctr/functions.php
    //$sql = "SELECT lcca.Id,lcca.Name,jl.listid FROM `LeonCampaignContactAttribute` as lcca left join joinLists as jl on lcca.Id=jl.newListId where lcca.DataType='Boolean'";

    $listArray = array(
        "504" => "IsBetterRecipes Daily",
        "505" => "IsBetterRecipes SOLO",
        "506" => "IsBetterRecipesSweeps",
        "395" => "IsBudgetCooking",
        "539" => "IsCasseroleCooking",
        "554" => "IsCopycatClassics",
        "511" => "IsCrockpotCreations",
        "411" => "IsDailyInsider",
        "393" => "IsDailyRecipes",
        "574" => "IsDiabeticFriendlyDishes",
        "448" => "IsDietInsider",
        "501" => "IsEditorsChoice",
        "410" => "IsFitFabLivingSOLO",
        //"NULL" => "IsLegacySweeps"
        "553" => "IsMakingItWork",
        "503" => "IsMoreWeLove",
        "394" => "IsQuickEasyRecipes",
        "502" => "IsR4LSeasonal",
        "396" => "IsRecipe4LivingSOLO",
        "507" => "IsRecipe4LivingSweeps",
        "508" => "IsSavvyforkSOLO",
        "500" => "IsSecondHelping",
        "583" => "IsTheFeedBySavvyFork",
        "558" => "IsWorkItMomSOLO"
    );
    if(array_key_exists($listId,$listArray)){
        return $listArray[$listId];
    }else{
        return false;
    }
}

?>

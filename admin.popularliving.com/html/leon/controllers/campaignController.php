<?php

require_once(dirname(__FILE__) . '/../config.inc.php');

// Change to another database
//global $db_link;
//mysql_select_db("campaigner", $db_link);

require_once(CAMPAIGNER_LEON_ROOT . 'lib/campaign.class.php');




/**
    * Report Types
    * rpt_Detailed_Contact_Results_by_Campaign (50,000) 
    * rpt_Summary_Contact_Results_by_Campaign (25,000) 
    * rpt_Summary_Campaign_Results (10,000) 
    * rpt_Summary_Campaign_Results_by_Domain (10,000) 
    * rpt_Contact_Attributes (100,000) 
    * rpt_Contact_Details (25,000) 
    * rpt_Contact_Group_Membership (150,000) 
    * rpt_Groups (1,000) 
    * rpt_Tracked_Links (25,000)
    */


function updateCampaignContactResult(){
    $creport = new Contact();
    $xmlQuery = '<contactssearchcriteria>
                                                  <version major="2" minor="0" build="0" revision="0" />
                                                            <accountid>439960</accountid>
                                                            <set>Partial</set>
                                                            <evaluatedefault>True</evaluatedefault>
                                                            <group>
                                                                <filter>
                                                                    <filtertype>SearchAttributeValue</filtertype>
                                                                    <contactattributeid>3844843</contactattributeid>
                                                                    <action>
                                                                        <type>Boolean</type>
                                                                        <operator>EqualTo</operator>
                                                                        <value>1</value>
                                                                    </action>
                                                                </filter>
                                                            </group>
                                                        </contactssearchcriteria>';
    $result =  $creport->getReport($xmlQuery, 1, 3, 'rpt_Detailed_Contact_Results_by_Campaign');
    print_r($creport->getResponseStacks());   
}

//updateCampaignContactResult();

function updateCampaignResult($fromDate, $toDate){
    //$campaignFilter = array('CampaignNames'=>array("FFDiet051714"));
    $i = 0;
    $campaignFilter = false;
    
    echo "-->Get campaignRunResult from [$fromDate] to [$toDate]\n";
    
    $creport = new Campaign();
    $reportXMLObj = $creport->getCampaignResult($campaignFilter, $fromDate, $toDate);
    //var_dump( $reportXML->GetCampaignRunsSummaryReportResult->Campaign->Id);
    foreach($reportXMLObj->GetCampaignRunsSummaryReportResult->Campaign as $row){
        $i++;
        saveCampaign($row);
        saveCampaignRuns($row->CampaignRuns, $row->Id);
    }
    
    if(SOAP_RESPONSE_TRACK){
        print_r($creport->getResponseStacks());
    }
    
    return $i;
   //print_r($creport->getResponseStacks());
   //print_r($reportXMLObj);
}

function saveCampaign($campaign){
        $sql = "REPLACE INTO LeonCampaign (`Id` ,`Name` ,`Status` ,`Type` ,`Subject` ,`FromName` ,`FromEmail` , `CreationDate` ,`ProjectId` ,`SentToAllContacts`, `SentToContactGroupIds`) VALUES (
                        '" . $campaign->Id . "','" . addslashes($campaign->Name) . "','" . $campaign->Status . "','" . $campaign->Type . "','" . addslashes($campaign->Subject) . "','" . addslashes($campaign->FromName) . "','". $campaign->FromEmail . "','" . $campaign->CreationDate . "','" . $campaign->ProjectId . "', '" . $campaign->SentToAllContacts . "', '" . json_encode($campaign->SentToContactGroupIds) . "')";        
        if($r = mysql_query($sql)){
            echo "\t==>Save Campaign: " . $campaign->Id . " - [" . $campaign->Name . "] ... Success\n\r";
        }else{
            echo "\t====>Save Campaign: " . $campaign->Id . " ... Failed\n\r";
            echo "\t====>Save Campaign Mysql Error: " . mysql_error() . "\n\r";
        }
}

function saveCampaignRuns($campaignRuns,$campaignId){
    foreach($campaignRuns as $row){
        $sql = "REPLACE INTO LeonCampaignRun (`Id` , `CampaignId` , `ScheduledDate` ,`RunDate` ,`ContactCount` ,`Status` ,`Sent` ,`Delivered` , `HardBounces` ,`SoftBounces` ,`SpamBounces`, `Opens`, `Clicks`, `Replies`, `Unsubscribes`, `SpamComplaints`) VALUES (
                        '" . $row->Id . "','$campaignId','" . $row->ScheduledDate . "','" . $row->RunDate . "','" . $row->ContactCount . "','" . $row->Status . "','" . $row->Domains->Domain->DeliveryResults->Sent . "','". $row->Domains->Domain->DeliveryResults->Delivered . "','" . $row->Domains->Domain->DeliveryResults->HardBounces . "','" . $row->Domains->Domain->DeliveryResults->SoftBounces . "', '" . $row->Domains->Domain->DeliveryResults->SpamBounces . "', '" . $row->Domains->Domain->ActivityResults->Opens . "', '" . $row->Domains->Domain->ActivityResults->Clicks . "', '" . $row->Domains->Domain->ActivityResults->Replies . "', '" . $row->Domains->Domain->ActivityResults->Unsubscribes . "', '" . $row->Domains->Domain->ActivityResults->SpamComplaints . "')";        
        if($r = mysql_query($sql)){
            echo "\t==>Save CampaignRun: $campaignId Success \n\r";
        }else{
            echo "\t====>Save CampaignRun: $campaignId Failed \n\r";
            echo "\t====>Save CampaignRun Mysql Error: " . mysql_error() . "\n\r";
        }
    }    
}


?>
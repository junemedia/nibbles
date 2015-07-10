<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

function saveContactGeneralDaily($tableName, $downloadReport){
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
        foreach($downloadReport->ReportResult as $i=>$row){
            $sql = "REPLACE INTO $tableName (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                            `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact`)
                            VALUES (
                            '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . $row->attributes()->FirstName . "', '" . $row->attributes()->LastName . "', '" . $row->attributes()->Email . "', '" . $row->attributes()->Phone . "', 
                            '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "')";        
                            $r = mysql_query($sql);
            if($r){
                //echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
            }
        }     
}

$creport = new Contact();
// Get the subscribed only and soft bounced
global $allContactQuery;

echo "-->TRUNCATE Table LeonCampaignContact ...";
if(mysql_query("TRUNCATE Table LeonCampaignContact")){echo " - [Done!]\n\r";}else{echo " - [Failed!]\n\r";}
$result =  $creport->saveReport($allContactQuery, 'rpt_Contact_Details',24000,'LeonCampaignContact','saveContactGeneralDaily');    

?>
<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');
define("DEBUG_PRINT", true);


function saveContactDetailsDaily($tableName, $downloadReport){
    foreach($downloadReport->ReportResult as $index => $report){
        $email = trim(strtolower($report->attributes()->ContactUniqueIdentifier));
//        echo "-------------------------row--------------------------------\n";
//        foreach($row->ReportResult as $report){
//            print_r($row);
//            echo "ContactId: " . $report->attributes()->ContactId . "\n";
//            echo "ContactUniqueIdentifier: " . $report->attributes()->ContactUniqueIdentifier . "\n";
//            echo "Id: " . $report->Attribute->attributes()->Id . "\n";
//            echo "Type: " . $report->Attribute->attributes()->Type . "\n";
//            echo "Attribute: " . $report->Attribute . "\n";
//            echo "=========Split============\n";
//        }

         //print_r($report);
            //Save sql
            $email_attributeId = $email . "_" . $report->Attribute->attributes()->Id;   // This must be unique
            $sql = "REPLACE INTO `$tableName` 
                    (`email_attributeid`, `ContactId`, `email`, `attributeId`, `attributeType`, `attributeValue`) VALUES 
                    ('$email_attributeId', '" . $report->attributes()->ContactId . "', '$email', '" . $report->Attribute->attributes()->Id . "', '" . $report->Attribute->attributes()->Type . "', '" . $report->Attribute . "')";
            $sr = mysql_query($sql);
            
            // Save the JoinEmail
            echo "Updating the user details information ... ";
            saveContactAllDaily($email, $report->Attribute->attributes()->Id, $report->Attribute);
            echo "Done\n";
    }
    unset($downloadReport);
    return true;     
}

function saveContactGeneralDaily($tableName, $downloadReport){
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
        foreach($downloadReport->ReportResult as $i=>$row){
            $sql = "REPLACE INTO $tableName (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                            `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact` ,`emailHash`)
                            VALUES (
                            '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . addslashes($row->attributes()->FirstName) . "', '" . addslashes($row->attributes()->LastName) . "', '" . $row->attributes()->Email . "', '" . $row->attributes()->Phone . "', 
                            '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "' ,'" . md5(strtolower($row->attributes()->ContactUniqueIdentifier)) . "')";
                            $r = mysql_query($sql);
            if($r){
                //echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
                //echo "====>Sql Query : " . $sql . "\n\r";
            }
        }     
}


function saveContactAllDaily($email,$attributeId,$attributeValue){
    $flag = "success";    
    if(!checkJoinEmailExist($email)){
        // Let's create a new one first
        $inssql = "INSERT INTO `arcamax`.`LeonCampaignContactJoin` (`3818568`, `3818583`, `3818578`, `3818558`, `3818563`, `3818573`, `1`, `2`, `3`, `4`, `9`, `10`, `11`, `5`, `6`, `7`, `3834418`, `3834428`, `3834493`, `3834483`, `3834438`, `3834458`, `3834468`, `3834378`, `3844863`, `3844823`, `3844813`, `3844833`, `3844903`, `3844883`, `3844783`, `3844843`, `4195798`, `3844893`, `3844803`, `4195818`, `3844853`, `4195808`, `3844873`, `4195828`, `3844768`, `3844793`, `3834333`, `4173573`, `4173658`, `4173668`, `4173678`, `4173688`, `4173563`, `3845658`, `3845663`, `3845668`, `3834363`, `3834388`, `3834448`, `3834288`, `3834408`, `3833693`) VALUES ('$email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
        $insertResult = mysql_query($inssql); 
        if(!$insertResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
        //$iflag = $insertResult?"Done":"Failed";if(DEBUG_PRINT) echo "Insert [$email] ... $iflag\n";
    }
    
    // Alright, let's do the update
    $upsql = "UPDATE `arcamax`.`LeonCampaignContactJoin` SET `$attributeId` = '$attributeValue' WHERE `LeonCampaignContactJoin`.`3818568` = '$email'";
    $updateResult = mysql_query($upsql); 
    if(!$updateResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
    //$uflag = $updateResult?"Done":"Failed";if(DEBUG_PRINT)echo "Update [$email] - $attributeId - [$attributeValue] ... $uflag\n";
    //if($i == 3)break;            
}

function checkJoinEmailExist($email){
    $ssql = "SELECT count(*) FROM `LeonCampaignContactJoin` WHERE `3818568` LIKE '$email'";
    $selectResult = mysql_query($ssql);
    if(!$selectResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";    
    $totalRows = mysql_fetch_array($selectResult);
    $tr = $totalRows[0];    
    if($tr > 0){
        return true;
    }else{
        return false;
    }
}

$creport = new Contact();
global $allContactQuery;   


    $singleQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                        <group>
                            <filter>
                                <relation>And</relation>
                                <filtertype>SearchAttributeValue</filtertype>
                                <staticattributeid>3</staticattributeid>
                                <action>
                                    <type>Text</type>
                                    <operator>Containing</operator>
                                    <value>@junemedia.com</value>
                                </action>
                            </filter>
                        </group>
                    </contactssearchcriteria>';

                
// Truncate the data first
//mysql_query("TRUNCATE TABLE LeonCampaignContactDetails");                
//$r = $creport->saveReport($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetails"); 

//$r = $creport->saveReportContinue($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetails", "A00A7228-95F5-4BBD-A7BA-05CB6CC7733B", 1859043, 1392501);
//print_r($r);         



// save details attributes
//$r = $creport->saveReport($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetailsDaily");
$r = $creport->saveReportContinue($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetailsDaily", "FF375C0C-D76F-4A25-8718-03FEF6278D9B", 1795925, 880001);
//print_r($r);         


// save general information
$result =  $creport->saveReport($allContactQuery, 'rpt_Contact_Details',24000,'LeonCampaignContact','saveContactGeneralDaily');    





// Send results mail to Leon
date_default_timezone_set('America/Chicago');
$email = "";
// Send the mail notification
$to      = $email . ',leonz@junemedia.com';
$subject = 'Daily Report - Download Campaign Contact Result';
$message = "Done! Save/Update [$result] emails";
$headers = 'From: leonz@junemedia.com' . "\r\n" .
    'Reply-To: leonz@junemedia.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

?>
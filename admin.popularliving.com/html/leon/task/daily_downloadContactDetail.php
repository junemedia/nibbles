<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

$downloadReportDays = 1;

function saveContactDetailsDaily($tableName, $downloadReport){
    foreach($downloadReport->ReportResult as $index => $report){
        $email = trim(strtolower($report->attributes()->ContactUniqueIdentifier));
        //echo "-------------------------row--------------------------------\n";
        //foreach($row->ReportResult as $report){
            //print_r($row);
            //echo "ContactId: " . $report->attributes()->ContactId . "\n";
            //echo "ContactUniqueIdentifier: " . $report->attributes()->ContactUniqueIdentifier . "\n";
            //echo "Id: " . $report->Attribute->attributes()->Id . "\n";
            //echo "Type: " . $report->Attribute->attributes()->Type . "\n";
            //echo "Attribute: " . $report->Attribute . "\n";
            //echo "=========Split============\n";
            
            //Save sql
            $email_attributeId = $email . "_" . $report->Attribute->attributes()->Id;   // This must be unique
            $sql = "REPLACE INTO `$tableName` 
                    (`email_attributeid`, `ContactId`, `email`, `attributeId`, `attributeType`, `attributeValue`) VALUES 
                    ('$email_attributeId', '" . $report->attributes()->ContactId . "', '$email', '" . $report->Attribute->attributes()->Id . "', '" . $report->Attribute->attributes()->Type . "', '" . $report->Attribute . "')";
            $sr = mysql_query($sql);
    }
    unset($downloadReport);
    return true;     
}

function saveContactGeneralDaily($tableName, $downloadReport){
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
        foreach($downloadReport->ReportResult as $i=>$row){
            $sql = "REPLACE INTO $tableName (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                            `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact`)
                            VALUES (
                            '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . addslashes($row->attributes()->FirstName) . "', '" . addslashes($row->attributes()->LastName) . "', '" . $row->attributes()->Email . "', '" . $row->attributes()->Phone . "', 
                            '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "')";
                            $r = mysql_query($sql);
            if($r){
                //echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
				echo "====>Sql Query : " . $sql . "\n\r";
            }
        }     
}

$creport = new Contact();
$lastDayContacts = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                    <group>
                        <filter>
                            <filtertype>SearchAttributeValue</filtertype>
                            <systemattributeid>2</systemattributeid>
                                <action>
                                    <type>DDMMYY</type>
                                    <operator>WithinLastNDays</operator>
                                    <value>'.$downloadReportDays.'</value>
                                </action>
                        </filter>
                        <filter>
                            <relation>Or</relation>
                            <filtertype>SearchAttributeValue</filtertype>
                            <systemattributeid>3</systemattributeid>
                            <action>
                                <type>DDMMYY</type>
                                <operator>WithinLastNDays</operator>
                                <value>'.$downloadReportDays.'</value>
                            </action>
                        </filter>
                    </group>
                </contactssearchcriteria>';             

                
// save details attributes
$r = $creport->saveReport($lastDayContacts, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetailsDaily");
//print_r($r);         


// save general information
$result =  $creport->saveReport($lastDayContacts, 'rpt_Contact_Details',24000,'LeonCampaignContact','saveContactGeneralDaily');    


// Send results mail to Leon
date_default_timezone_set('America/Chicago');
$email = "williamg@junemedia.com";
// Send the mail notification
$to      = $email . ',leonz@junemedia.com';
$subject = 'Daily Report - Download Campaign Contact Result';
$message = "Done! Save/Update [$result] emails";
$headers = 'From: leonz@junemedia.com' . "\r\n" .
    'Reply-To: leonz@junemedia.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

tryMail($to, $subject, $message, $headers);

?>

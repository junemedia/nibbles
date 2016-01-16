<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');


function saveContactDetails($tableName, $downloadReport){
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

$creport = new Contact();
global $allContactQuery;   

/*
    $stateQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                        <group>
                            <filter>
                                <relation>And</relation>
                                <filtertype>SearchAttributeValue</filtertype>
                                <contactattributeid>3834448</contactattributeid>
                                <action>
                                    <type>Text</type>
                                    <operator>EqualTo</operator>
                                    <value>Alaska</value>
                                </action>
                            </filter>
                        </group>
                    </contactssearchcriteria>';
*/
                
// Truncate the data first
mysql_query("TRUNCATE TABLE LeonCampaignContactDetails");                
$r = $creport->saveReport($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetails"); 

//$r = $creport->saveReportContinue($allContactQuery, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetails", "A00A7228-95F5-4BBD-A7BA-05CB6CC7733B", 1859043, 1392501);
//print_r($r);         


?>
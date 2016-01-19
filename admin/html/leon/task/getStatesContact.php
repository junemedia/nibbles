<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');
//getStatesContact($states = "Wyoming");


function saveStateReport($tableName, $downloadReport){
    foreach($downloadReport->ReportResult as $index => $row){
        $email = trim(strtolower($row->attributes()->Email));
        $sql = "REPLACE INTO $tableName (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                        `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact`, `emailHash`)
                        VALUES (
                        '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . $row->attributes()->FirstName . "', '" . $row->attributes()->LastName . "', '" . $email . "', '" . $row->attributes()->Phone . "', 
                        '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "' , '" . md5($email) . "')";        
        $r = mysql_query($sql);
        if($r){
            //echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
        }else{
            //echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            echo "====>Mysql Error: " . mysql_error() . "\n\r";
        }
        unset($email);unset($sql);unset($index);unset($row);unset($r);
    }
    unset($downloadReport);
    return true;    
}

global $allContactQuery; 
$stateQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                    <group>
                        <filter>
                            <relation>And</relation>
                            <filtertype>SearchAttributeValue</filtertype>
                            <contactattributeid>3834448</contactattributeid>
                            <action>
                                <type>Text</type>
                                <operator>EqualTo</operator>
                                <value>Wyoming</value>
                            </action>
                        </filter>
                    </group>
                </contactssearchcriteria>';
                


$creport = new Contact();                
$stateResult =  $creport->saveReport($stateQuery, 'rpt_Contact_Details',24000,"LeonCampaignContact", "saveStateReport");




?>
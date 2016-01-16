<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

function saveDB($tableName, $downloadReport){
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

function saveR4LSOLOContact($tableName){
    $creport = new Contact();
    // Get the subscribed only and soft bounced
    $filterSub = "<filter>
                    <relation>And</relation>
                    <filtertype>SearchAttributeValue</filtertype>
                    <systemattributeid>1</systemattributeid>
                    <action>
                        <type>Numeric</type>
                        <operator>EqualTo</operator>
                        <value>2</value>
                    </action>
                </filter>
                <filter>
                    <relation>Or</relation>
                    <filtertype>SearchAttributeValue</filtertype>
                    <systemattributeid>1</systemattributeid>
                    <action>
                        <type>Numeric</type>
                        <operator>EqualTo</operator>
                        <value>4</value>
                    </action>
                </filter>";
    
    $xmlQuery = '<contactssearchcriteria>
                  <version major="2" minor="0" build="0" revision="0" />
                            <accountid>439960</accountid>
                            <set>Partial</set>
                            <evaluatedefault>True</evaluatedefault>
                            <group>
                                <filter>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844793</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>' . $filterSub . '
                            </group>
                        </contactssearchcriteria>';
    $result =  $creport->saveReport($xmlQuery, 'rpt_Contact_Details',24000,$tableName,"saveDB");    
}

saveR4LSOLOContact("LeonCampaignContact");


// Now we need to save the export csv file for the export
$sql = "select emailHash from LeonCampaignContact";
$r = mysql_query($sql);
$filename = CAMPAIGNER_LEON_ROOT . "export/isWIMSolo.csv";

// Truncate the file first
echo "Truncate file $filename ... ";
exec("cat /dev/null > $filename");
echo " Done\r\n";

// Let's make sure the file exists and is writable first.
if (is_writable($filename)) {
    if (!$handle = fopen($filename, 'w')) {
         echo "Cannot open file ($filename)";
         exit;
    }
    
    while($row = mysql_fetch_array($r)){
        $somecontent = $row["emailHash"] . "\n"; 
        // Write $somecontent to our opened file.
        if (fwrite($handle, $somecontent) === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
        }        
    }
    fclose($handle);
} else {
    echo "The file $filename is not writable";
}

//Zip the file
echo "Zipping the export file - File size is [" . formatBytes(filesize($filename) ,2).' bytes'. "]\n\r"; 
echo exec("zip " . CAMPAIGNER_LEON_ROOT . "export/isWIMSOLO." . date("YmdHms") . ".csv.zip " . CAMPAIGNER_LEON_ROOT . "export/isWIMSolo.csv") . "\n\r";

if(MAIL_RESULT){
    $email = "leonz@junemedia.com,williamg@junemedia.com";
    $title = "IsWIMSOLO email dump for " . date("Y-m-d H:m:s");
    $context = "Leon WIMSOLO export file";
    LeonSendEmail($email,$title,$context,$lien=false,$attach = $filename);
}

?>
<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');
require_once(CAMPAIGNER_LEON_ROOT . 'lib/howe.contact.class.php');

function saveCampaignSendEmails($tableName, $downloadReport){
	$tableName='CampainSendEmails';
    foreach($downloadReport->ReportResult as $index => $row){
        $email = trim(strtolower($row->Contact->attributes()->Email));
		$campaignId = $row->attributes()->CampaignId;
		$campaignRunId = $row->attributes()->CampaignRunId;
		$contactId = $row->Contact->attributes()->Id;
		$delivered = '';
		$openClick = '';
		
		if($row->Contact->DeliveryResult == 'Delivered')
		{
			$delivered = True;
		}
		
		if(!checkSendEmailExist($campaignId,$campaignRunId,$contactId))
		{
			$inssql = "INSERT INTO $tableName (`campaignId` ,`campaignRunId`,`contactId` ,`deliveredStatus` ,`opens` ,`clicks`)
                        VALUES (
                        '" . $campaignId . "', '" . $campaignRunId . "', '". $contactId . "', " . $delivered . ", '', '')";        
			$insertResult = mysql_query($inssql); 
			if(!$insertResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
		}
		
		$fieldName = '';
		if($row->Contact->Action->attributes()->Type=='Opened')
		{
			$openClick = $row->Contact->Action->attributes()->Count;
			$fieldName = 'opens';
		}
		
		if($row->Contact->Action->attributes()->Type=='Clicked')
		{
			$openClick = $row->Contact->Action->attributes()->Count;
			$fieldName = 'clicks';
		}
		
		// Alright, let's do the update
		$upsql = "UPDATE `CampainSendEmails` SET `$fieldName` = $openClick WHERE `campaignId` = $campaignId AND campaignRunId=$campaignRunId AND contactId=".$contactId;
		$updateResult = mysql_query($upsql); 
		if(!$updateResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";		
		
        unset($email);unset($upsql);unset($contactId);unset($row);unset($updateResult);
    }
    unset($downloadReport);
    return true; 
}

function checkSendEmailExist($campaignId,$campaignRunId,$contactId){
    $ssql = "SELECT count(*) FROM `CampainSendEmails` WHERE `contactId` = ".$contactId ." AND campaignRunId=".$campaignRunId." AND campaignId=".$campaignId;
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

function getCampaignSendContacts($tableName,$campaignRunId){
    $creport = new HoweContact();
    
    $xmlQuery = '<contactssearchcriteria>
                  <version major="2" minor="0" build="0" revision="0" />
                            <accountid>439960</accountid>
                            <set>Partial</set>
                            <evaluatedefault>True</evaluatedefault>
                            <group>
                                <filter>
                                    <filtertype>EmailAction</filtertype>
                                    <campaign>
										<campaignrunid>'.$campaignRunId.'</campaignrunid>
									</campaign>
                                    <action>
                                        <status>Do</status>
                                        <operator>Sent</operator>
                                    </action>
                                </filter>								
                            </group>
                        </contactssearchcriteria>';
    $result =  $creport->saveReport($xmlQuery, 'rpt_Summary_Contact_Results_by_Campaign',24000,$tableName,"saveCampaignSendEmails");return $result;    
}
$result = getCampaignSendContacts("CampainSendEmails",12823924);
/*$sql = "select id as campaignRunId,campaignId from LeonCampaignRun limit 1";
$r = mysql_query($sql);
while($row = mysql_fetch_object($r))
{
	if($row->campaignRunId !=0)
	{
		$result = getCampaignSendContacts("CampainSendEmails",$row->campaignRunId);
	}
}*/

function saveInFile($tableName, $downloadReport)
{
	foreach($downloadReport->ReportResult as $index => $row){
        $email = trim(strtolower($row->Contact->attributes()->Email));
		$campaignId = $row->attributes()->CampaignId;
		$campaignRunId = $row->attributes()->CampaignRunId;
		$contactId = $row->Contact->attributes()->Id;
		
		$filename = CAMPAIGNER_LEON_ROOT . "export/campaignSendEmails".$campaignId.".csv";

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
			
			$somecontent = $campaignId.','.$campaignRunId.','.$email. "\n"; 
			// Write $somecontent to our opened file.
			if (fwrite($handle, $somecontent) === FALSE) {
				echo "Cannot write to file ($filename)";
				exit;
			}  
			
			fclose($handle);
		} else {
			echo "The file $filename is not writable";
		}

		//Zip the file
		echo "Zipping the export file - File size is [" . formatBytes(filesize($filename) ,2).' bytes'. "]\n\r"; 
		echo exec("zip " . CAMPAIGNER_LEON_ROOT . "export/isR4LSOLO." . date("YmdHms") . ".csv.zip " . CAMPAIGNER_LEON_ROOT . "export/isR4LSolo.csv") . "\n\r";
    }
	

	if(MAIL_RESULT){
		$email = "leonz@junemedia.com,williamg@junemedia.com";
		$title = "IsR4LSOLO email dump for " . date("Y-m-d H:m:s");
		$context = "Leon R4LSOLO export file";
		LeonSendEmail($email,$title,$context,$lien=false,$attach = $filename);
	}
}
?>
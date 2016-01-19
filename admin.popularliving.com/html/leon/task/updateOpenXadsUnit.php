<?php

// Load Config
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

// Get isCopyCatClassic Contacts
$query = "SELECT * FROM `LeonCampaignContactDetails` WHERE `email_attributeid` LIKE '%_3844813' AND `attributeValue` = 'true'";

//$query = "SELECT * FROM `LeonCampaignContactDetails` WHERE `email_attributeid` LIKE 'leonz@junemedia.com_3844813'";
$result = mysql_query($query);
echo mysql_error();

$totalRow = mysql_affected_rows($db_link);
echo "Total [" . $totalRow . "] rows\n";
$i = 0;
while($unitEmail = mysql_fetch_array($result)){
    $i++;
    $addDetailsSql = "SELECT * from LeonCampaignContact as lcc left join campaigner as c on lcc.ContactUniqueIdentifier = c.email where lcc.ContactUniqueIdentifier = '" . $unitEmail["email"] . "' limit 0,1";
    $addDetailsResult = mysql_query($addDetailsSql);
    //echo "Total [" . mysql_affected_rows($db_link) . "] rows\n"; exit;
    while($row = mysql_fetch_array($addDetailsResult)){
        //print_r($row); exit;
        $sequenceId = UploadRow($row);
        echo "[$i/$totalRow]" . $row["ContactUniqueIdentifier"] . " - [$sequenceId]\n";
    }
    
}

echo "All set\n"; 
exit;


?>
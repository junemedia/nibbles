<?php

require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/listController.php');
define("DEBUG_PRINT", true);


$r = new ListContact();
$t = $r->listContactGroups();    
$totalRows = count($t);
echo "Download Finished. Processing Saving to Database ... \n Total [$totalRows] rows\n";

saveContactGroups($t);


function saveContactGroups($report){
    foreach($report as $row){
        $sql = "REPLACE INTO `LeonContactGroup` (`Id`, `Type`, `Name`, `Status`, `LastModifiedDate`, `xmlContactQuery`, `ProjectId`) VALUES "
                . "(".$row->Id.", '".$row->Type."', '".addslashes($row->Name)."', '".$row->Status."', '".$row->LastModifiedDate."', '".$row->xmlContactQuery."',".$row->ProjectId." )";
        $iResult = mysql_query($sql);
        if($iResult){
            echo "[".$row->Id.' - '.$row->Name."] ... Done\n";
        }else{
            echo "[".$row->Id.' - '.$row->Name."] ... failed\n";
            echo "\n==================================\n";
            echo "====>Mysql Error: " . mysql_error() . "\n";
            echo "\n==================================\n";
        }
    }
}


if(SERVER_RUN){
    // This is run on the server, send the email result to Leon
    $email = "";

    // Send the mail notification
    $to      = $email . ',leonz@junemedia.com';
    $subject = 'Daily Report - Download Campaign Contact Groups Download Result';
    $message = "Done! Save/Update [$totalRows] Campaign Contact Groups";
    $headers = 'From: leonz@junemedia.com' . "\r\n" .
        'Reply-To: leonz@junemedia.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    tryMail($to, $subject, $message, $headers);
}



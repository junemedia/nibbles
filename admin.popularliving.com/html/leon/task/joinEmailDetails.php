<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

define("DEBUG_PRINT", true);

echo "-->Get all rows from LeonCampaignContactDetails ... ";
//$getAllSql = "select email,attributeId,attributeValue from LeonCampaignContactDetails where email_attributeId like '07nitrorose@att.net%'";
$getAllSql = "select email,attributeId,attributeValue from LeonCampaignContactDetails";
$r = mysql_query($getAllSql);
$total = mysql_num_rows($r);
echo "Done - Total [$total] rows\n";

$i = 0;
while($e = mysql_fetch_array($r)){
    $i++;
    $flag = "success";
    $email = $e["email"];
    $attributeId = $e["attributeId"];
    $attributeValue = $e["attributeValue"];
    $process = round((($i/$total) * 100) , 2);
    
    if(!checkEmail($email)){
        // Let's create a new one first
        $inssql = "INSERT INTO `arcamax`.`LeonCampaignContactJoin` (`3818568`, `3818583`, `3818578`, `3818558`, `3818563`, `3818573`, `1`, `2`, `3`, `4`, `9`, `10`, `11`, `5`, `6`, `7`, `3834418`, `3834428`, `3834493`, `3834483`, `3834438`, `3834458`, `3834468`, `3834378`, `3844863`, `3844823`, `3844813`, `3844833`, `3844903`, `3844883`, `3844783`, `3844843`, `4195798`, `3844893`, `3844803`, `4195818`, `3844853`, `4195808`, `3844873`, `4195828`, `3844768`, `3844793`, `3834333`, `4173573`, `4173658`, `4173668`, `4173678`, `4173688`, `4173563`, `3845658`, `3845663`, `3845668`, `3834363`, `3834388`, `3834448`, `3834288`, `3834408`, `3833693`) VALUES ('$email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
        $insertResult = mysql_query($inssql); if(!$insertResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
        $iflag = $insertResult?"Done":"Failed";
        if(DEBUG_PRINT) echo "$i/$total - $process% - Insert [$email] ... $iflag\n";
    }
    
    // Alright, let's do the update
    $upsql = "UPDATE `arcamax`.`LeonCampaignContactJoin` SET `$attributeId` = '$attributeValue' WHERE `LeonCampaignContactJoin`.`3818568` = '$email'";  if($attributeId == 1) {echo $upsql; exit;}
    $updateResult = mysql_query($upsql); if(!$updateResult)echo ">>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
    $uflag = $updateResult?"Done":"Failed";
    if(DEBUG_PRINT)echo "$i/$total - $process% - Update [$email] - $attributeId - [$attributeValue] ... $uflag\n";
    //if($i == 3)break;            
}



function checkEmail($email){
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



printMemoryInfo();

?>
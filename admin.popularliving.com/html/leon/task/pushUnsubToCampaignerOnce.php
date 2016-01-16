<?php

require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');
require_once(dirname(__FILE__) . '/../lib/functions.php');

/*
$unsubEmailsSql = "SELECT joinEmailUnsub.*,count(*) FROM `joinEmailUnsub` WHERE `dateTime` >= '2015-06-15 00:00:00' group BY email,listid ORDER BY dateTime";
$unsubResult = mysql_query($unsubEmailsSql);

mysql_query("TRUNCATE TABLE LeonCampaignPushOnce");
while($row = mysql_fetch_array($unsubResult)){

    if($attrName = getAttrNameByListId($row['listid'])){
        $attrs = json_encode(array($attrName =>"False"));
        $updateAttrSql = "INSERT INTO `LeonCampaignPushOnce` (`id`, `email`, `attrs`, `IsProcessed`, `date_add`,`notes`) VALUES "
                        . "(NULL, '". $row['email'] . "', '$attrs', 'N', NOW(),'')";
	//echo $updateAttrSql . "\n";
        mysql_query($updateAttrSql);
    }
    
}
*/

//exit;

$creport = new Contact(); 

echo "\nNow let's start to push these users to campaigner ... \n";

$perPage = 50;




echo "Loading data from LeonCampaignPushOnce ... ";
$userAll = "SELECT * FROM `LeonCampaignPushOnce`";
$reUserAll = mysql_query($userAll);
while($row = mysql_fetch_array($reUserAll)){
    $users[$row['email']] = json_decode($row['attrs'],TRUE);
}
echo "Done\n";

//print_r($users);exit;

$rowCount = count($users);

if($rowCount > $perPage){
    // We need to use pagination to get the result
    $pages = ceil($rowCount/$perPage);

    // Ok, let's get the result step by step
    for($i = 1; $i<= $pages; $i++){ 
        //if($i == 3) break;
        if($i == 1){
            // This is the first page
            $fromRow = 1;
            $toRow = $i * $perPage;
        }elseif($i == $pages){
            // This is the last page
            $fromRow = (($i - 1) * $perPage) + 1;
            $toRow = $rowCount;    
        }else{
            $fromRow = (($i - 1) * $perPage) + 1; 
            $toRow = $i * $perPage; 
        }
        echo "-->Pushing Rows [$fromRow] - [$toRow] - Total [$rowCount] Rows ... \n";
        $length = $toRow - $fromRow;
        $currentRowArray = array_slice($users, $fromRow, $length, true);
        $runResult = $creport->pushCampaigner($currentRowArray);
        //var_dump($runResult);exit;
        saveResultLogs($runResult);
    }
}



function prepareUserIntoDB($email,$array_values){
    $attrs = json_encode($array_values);
    $update507Sql = "INSERT INTO `LeonCampaignPushOnce` (`id`, `email`, `attrs`, `IsProcessed`, `date_add`, `notes`) VALUES "
                . "(NULL, '$email', '$attrs', 'N', NOW(),'')";
    mysql_query($update507Sql);
    echo mysql_error();
}


function saveResultLogs($result){
    if(is_array($result->ImmediateUploadResult->UploadResultData)){
        // There are a few of them
        foreach($result->ImmediateUploadResult->UploadResultData as $row){
            if($row->ResultCode == 'Success'){
                //unset($users[$row->ContactKey->ContactUniqueIdentifier]);
                $status = 'Y';
            }else{
                $status = 'F';
            }
            //$changeStatusSql = "UPDATE `LeonCampaignPushOnce` SET `IsProcessed` = '$status',`notes` = '".$row->ResultCode."' WHERE `LeonCampaignPush`.`email` = '".$row->ContactKey->ContactUniqueIdentifier."' And IsProcessed='N'";
            $update507Sql ="Update `LeonCampaignPushOnce` set `IsProcessed`='$status', `notes`='".$row->ResultCode."' where email='".$row->ContactKey->ContactUniqueIdentifier."'";
            //echo $changeStatusSql;
            echo mysql_error();
            mysql_query($update507Sql);
        }
    }else{
        $row = $result->ImmediateUploadResult->UploadResultData;
        //print_r($row);
        if($row->ResultCode == 'Success'){
            unset($users[$row->ContactKey->ContactUniqueIdentifier]);
            $status = 'Y';
        }else{
            $status = 'F';
        }
            //$changeStatusSql = "UPDATE `LeonCampaignPushOnce` SET `IsProcessed` = '$status',`notes` = '".$row->ResultCode."' WHERE `LeonCampaignPush`.`email` = '".$row->ContactKey->ContactUniqueIdentifier."' And IsProcessed='N'";
            $update507Sql ="Update `LeonCampaignPushOnce` set `IsProcessed`='$status', `notes`='".$row->ResultCode."' where email='".$row->ContactKey->ContactUniqueIdentifier."'";
            //echo $changeStatusSql;
            echo mysql_error();
            mysql_query($update507Sql);
    }
}


function saveContactGeneralDailyOnce($tableName, $downloadReport){
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
    
    // Sub IsDailyRecipes IsRecipe4LivingSOLO IsEditorsChoice IsR4LSeasonal IsMoreWeLove
    $booleanArray = array("IsLegacySweeps"=>"False");
    echo "-->Prepare the Boolean arrays ... \n\t<<<<< ". json_encode($booleanArray)." >>>>> ... \n";
    
        echo "-->Truncate report table -- LeonCampaignPushOnce ... ";
        mysql_query("TRUNCATE TABLE LeonCampaignPushOnce");
        echo "Done\n";
        echo "-->Preparing the users into the tmp table LeonCampaignPushOnce ... ";
        $users = array();
        $ti = 0;
        foreach($downloadReport->ReportResult as $i=>$row){
            $ti++;
            $email = addslashes($row->attributes()->ContactUniqueIdentifier);
            $Contactid = $row->attributes()->Contactid;
            $users[$email] = $booleanArray;
            //echo $email . "\n";
            prepareUserIntoDB($email, $booleanArray);
        }
        echo "Done, saved [$ti] rows\n";
}


<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');


$upUsersSql = "SELECT * FROM `LeonCampaignPush` where IsProcessed='N'";
$upResult = mysql_query($upUsersSql);

if(mysql_num_rows($upResult) == 0){
    echo "No email contact need to be updated... Exit!\n";
    exit();
}

$r = new Contact();

$users = array();
while($row = mysql_fetch_assoc($upResult)){
    $email = $row['email'];
    $attr = json_decode($row['attrs'], TRUE);
    //downloadUserFromCampaignerByEmail($email);
    if($r->getContactIdByEmail($email) != false){
        if(isset($users[$email])){
            // Exists already, let's merge
        }else{
            // Nope, it is new
            $users[$email] = $attr;
        }
    }else{
        $changeStatusSql = "UPDATE `LeonCampaignPush` SET `IsProcessed` = 'NF',`notes` = 'User not found in campaigner' WHERE `LeonCampaignPush`.`email` = '$email' And IsProcessed='N'";
        //echo $changeStatusSql;
        mysql_query($changeStatusSql);
    }
}





//echo json_encode(array('IsRecipe4LivingSweeps' => 'False','IsR4LSeasonal' => 'False',));


//$users = array(
//    'leonz@junemedia.com' => array(
//                                "IsRecipe4LivingSweeps"=>"True"
//                            )
//);

//print_r($users);


$result = $r->pushCampaigner($users);

//print_r($result);

echo "-->Updating IsProcessed to Yes ... ";
//print_r($result);

if(is_array($result->ImmediateUploadResult->UploadResultData)){
    // There are a few of them
    foreach($result->ImmediateUploadResult->UploadResultData as $row){
        if($row->ResultCode == 'Success'){
            unset($users[$row->ContactKey->ContactUniqueIdentifier]);
            $status = 'Y';
        }else{
            $status = 'F';
        }
        $changeStatusSql = "UPDATE `LeonCampaignPush` SET `IsProcessed` = '$status',`notes` = '".$row->ResultCode."' WHERE `LeonCampaignPush`.`email` = '".$row->ContactKey->ContactUniqueIdentifier."' And IsProcessed='N'";
        //echo $changeStatusSql;
        mysql_query($changeStatusSql);
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
    $changeStatusSql = "UPDATE `LeonCampaignPush` SET `IsProcessed` = '$status',`notes` = '".$row->ResultCode."' WHERE `LeonCampaignPush`.`email` = '".$row->ContactKey->ContactUniqueIdentifier."' And IsProcessed='N'";
    //echo $changeStatusSql;
    mysql_query($changeStatusSql);
}
echo "Done\n";




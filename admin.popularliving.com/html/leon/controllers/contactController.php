<?php

require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'lib/contact.class.php');

//$reportXMLObj = CampaignGetCampaignRunsSummaryReport();
//var_dump( $reportXML->GetCampaignRunsSummaryReportResult->Campaign->Id);

//print_r($reportXMLObj);

//print_r($reportArray);




/**
* Download the Contact Report by campaign
* 
*/
function updateContactInfo(){
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
                                    <contactattributeid>3844873</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>' . $filterSub . '
                            </group>
                        </contactssearchcriteria>';
    $result =  $creport->getReport($xmlQuery, 'rpt_Contact_Details',10000,1,100);
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
    foreach($result as $pages){
        foreach($pages->ReportResult as $i=>$row){
            $sql = "REPLACE INTO LeonCampaignContact (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                            `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact`)
                            VALUES (
                            '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . $row->attributes()->FirstName . "', '" . $row->attributes()->LastName . "', '" . $row->attributes()->Email . "', '" . $row->attributes()->Phone . "', 
                            '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "')";        
                            $r = mysql_query($sql);
            if($r){
                echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
            }
        }
    }    
}



/**
    * Report Types
    * rpt_Detailed_Contact_Results_by_Campaign (50,000) 
    * rpt_Summary_Contact_Results_by_Campaign (25,000) 
    * rpt_Summary_Campaign_Results (10,000) 
    * rpt_Summary_Campaign_Results_by_Domain (10,000) 
    * rpt_Contact_Attributes (100,000) 
    * rpt_Contact_Details (25,000) 
    * rpt_Contact_Group_Membership (150,000) 
    * rpt_Groups (1,000) 
    * rpt_Tracked_Links (25,000)
    */

 
    
function getContactbounce(){
    $xmlQuery = '<contactssearchcriteria>
                            <version major="2" minor="0" build="0" revision="0" />
                            <set>Partial</set>
                            <evaluatedefault>True</evaluatedefault>
                                <group>
                                    <filter>
                                        <filtertype>EmailAction</filtertype>
                                        <campaign>
                                            <campaignrunid>11120387</campaignrunid>
                                        </campaign>
                                        <action>
                                            <status>Do</status>
                                            <operator>Sent</operator>
                                        </action>
                                    </filter>
                                </group>
                            </contactssearchcriteria>';
    $creport = new Contact();
    $result =  $creport->getReport($xmlQuery, 'rpt_Detailed_Contact_Results_by_Campaign', 10000);
    
    // Let's start to save the result
    foreach($result as $pages){
        foreach($pages->ReportResult as $i=>$row){
            $hashCode = $row->attributes()->ContactId .  $row->attributes()->ContactUniqueIdentifier . $row->attributes()->CampaignId  . $row->attributes()->CampaignRunId . $row->Action->attributes()->Type .  $row->Action;
            $ActionUniqueIdentifier = md5($hashCode);
            $sql = "REPLACE INTO CampaignContactResult (`ContactId` ,`ContactUniqueIdentifier` ,`CampaignId` ,`CampaignRunId` ,`ActionType` ,`ActionDate`, `ActionUniqueIdentifier`)
                            VALUES (
                            '" . $row->attributes()->ContactId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . $row->attributes()->CampaignId . "', '" . $row->attributes()->CampaignRunId . "', '" . $row->Action->attributes()->Type . "', '" . $row->Action . "', '$ActionUniqueIdentifier')";        
                            $r = mysql_query($sql);
            if($r){
                echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
            }
        }
    }
    //print_r($result);
    //print_r($creport->getResponseStacks());     
}    

// getContactbounce();
/**
* Delivered
Click
Open
Softbounce
Hardbounce
SpamComplaint
Unsubscribe
*/



//saveR4LSOLOContact("LeonCampaignContact");
//updateContactInfo();








function saveContactAttributes($tableName){
    $creport = new Contact();
    // Get the subscribed only and soft bounced
    
    $xmlQuery = '<contactssearchcriteria>
                  <version major="2" minor="0" build="0" revision="0" />
                            <accountid>439960</accountid>
                            <set>Partial</set>
                            <evaluatedefault>True</evaluatedefault>
                            <group>
                                <filter>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <systemattributeid>1</systemattributeid>
                                    <action>
                                        <type>Numeric</type>
                                        <operator>EqualTo</operator>
                                        <value>4</value>
                                    </action>
                                </filter>
                            </group>
                        </contactssearchcriteria>';
    $result =  $creport->getReport($xmlQuery, "rpt_Contact_Details",11000, 1, 10);
    
    //$result = $creport->getDownloadReport(1,10,'rpt_Contact_Details');
    
    print_r($result);    
}

global $attributes;
function getContactAttributesIdByName($name){
    global $attributes;
    if(isset($attributes) && count($attributes) > 0){
        // Do nothing. We already have it.   
    }else{
        $query = "SELECT * FROM LeonCampaignContactAttribute";
        $r = mysql_query($query);
        while($row = mysql_fetch_array($r)){
            $attributes[$row["Name"]] = $row["Id"];
        }
    }
    return $attributes[$name];
}


function setupStateFilter($state, $relationship = "And"){
    $xmlQuery = "<filter>
                    <relation>$relationship</relation>
                    <filtertype>SearchAttributeValue</filtertype>
                    <contactattributeid>3834448</contactattributeid>
                    <action>
                        <type>Text</type>
                        <operator>EqualTo</operator>
                        <value>$state</value>
                    </action>
                </filter>";
    return $xmlQuery;    
}

function setupQueryFilter($queryName,$relationship = "And"){
    $id = getContactAttributesIdByName($queryName);
    $xmlQuery = "<filter>
                    <relation>$relationship</relation>
                    <filtertype>SearchAttributeValue</filtertype>
                    <contactattributeid>$id</contactattributeid>
                    <action>
                        <type>Boolean</type>
                        <operator>EqualTo</operator>
                        <value>1</value>
                    </action>
                </filter>";
    return $xmlQuery;
}


global $allContactQuery;
$allContactQuery = '<contactssearchcriteria>
                  <version major="2" minor="0" build="0" revision="0" />
                            <accountid>439960</accountid>
                            <set>Partial</set>
                            <evaluatedefault>True</evaluatedefault>
                            <group>
                                <filter>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844823</contactattributeid>
                                    <action>                                             
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844863</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844813</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844833</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844903</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844883</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844783</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844843</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844893</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844803</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844853</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844873</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844768</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>Or</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <contactattributeid>3844793</contactattributeid>
                                    <action>
                                        <type>Boolean</type>
                                        <operator>EqualTo</operator>
                                        <value>1</value>
                                    </action>
                                </filter>
                                <filter>
                                    <relation>And</relation>
                                    <filtertype>SearchAttributeValue</filtertype>
                                    <systemattributeid>1</systemattributeid>
                                    <action>
                                        <type>Numeric</type>
                                        <operator>EqualTo</operator>
                                        <value>2</value>
                                    </action>
                                </filter>
                            </group>
                        </contactssearchcriteria>';
                        
                        
global $stateArray;
$stateArray = array(
    'Mississippi',
    'Alabama',
    'Louisiana',
    'South Carolina',
    'Utah',
    'Tennessee',
    'Arkansas',
    'North Carolina',
    'Georgia',
    'Texas',
    'North Dakota',
    'Oklahoma',
    'Kentucky',
    'South Dakota',
    'Kansas',
    'Iowa',
    'Nebraska',
    'Indiana',
    'Minnesota',
    'Missouri',
    'Virginia',
    'New Mexico',
    'Illinois',
    'Pennsylvania',
    'West Virginia',
    'Idaho',
    'Ohio',
    'Florida',
    'Maryland',
    'Michigan',
    'Wisconsin',
    'Arizona',
    'Delaware',
    'New Jersey',
    'District of Columbia',
    'Montana',
    'California',
    'Colorado',
    'New York',
    'Wyoming',
    'Connecticut',
    'Rhode Island',
    'Washington',
    'Alaska',
    'Hawaii',
    'Oregon',
    'Nevada',
    'Massachusetts',
    'Maine',
    'New Hampshire',
    'Vermont'
);

global $isBooleanArray;
$isBooleanArray = array(
        "3844863"=>"IsBudgetCooking",
        "3844823"=>"IsCasseroleCooking",
        "3844813"=>"IsCopycatClassics",
        "3844833"=>"IsCrockpotCreations",
        "3844903"=>"IsDailyInsider",
        "3844883"=>"IsDailyRecipes",
        "3844783"=>"IsDiabeticFriendlyDishes",
        "3844843"=>"IsDietInsider",
        "3844893"=>"IsFitFabLivingSOLO",
        "3844803"=>"IsMakingItWork",
        "3844853"=>"IsQuickEasyRecipes",
        "3844873"=>"IsRecipe4LivingSOLO",
        "3844768"=>"IsTheFeedBySavvyFork",
        "3844793"=>"IsWorkItMomSOLO"
    );

function saveContactDetailsDaily($tableName, $downloadReport){
    $i=0; $total = count($downloadReport->ReportResult);
    $ten_percent_items = $total/10;
    echo "\tSaving to local DB ... \n\t\t";
    foreach($downloadReport->ReportResult as $index => $report){
        $i++;
        if($i%$ten_percent_items == 0){echo "->" . round($i*100/$total) . "%";}
        
        $email = addslashes(trim(strtolower($report->attributes()->ContactUniqueIdentifier)));
        $ContactId = $report->attributes()->ContactId;
        $attribute = $report->Attribute;
        if($attribute){
//        echo "-------------------------row--------------------------------\n";
//        foreach($row->ReportResult as $report){
//            print_r($row);
//            echo "ContactId: " . $report->attributes()->ContactId . "\n";
//            echo "ContactUniqueIdentifier: " . $report->attributes()->ContactUniqueIdentifier . "\n";
//            echo "Id: " . $report->Attribute->attributes()->Id . "\n";
//            echo "Type: " . $report->Attribute->attributes()->Type . "\n";
//            echo "Attribute: " . $report->Attribute . "\n";
//            echo "=========Split============\n";
//        }

         //print_r($report);
            //Save sql
            $email_attributeId = $email . "_" . $report->Attribute->attributes()->Id;   // This must be unique
            $sql = "REPLACE INTO `$tableName` 
                    (`email_attributeid`, `ContactId`, `email`, `attributeId`, `attributeType`, `attributeValue`) VALUES 
                    ('$email_attributeId', '" . $report->attributes()->ContactId . "', '$email', '" . $report->Attribute->attributes()->Id . "', '" . $report->Attribute->attributes()->Type . "', '" . $report->Attribute . "')";
            $sr = mysql_query($sql);
            
            // Save the JoinEmail
            //echo "[$i/$total]\tUpdating the user details information [$email] - [" . $report->Attribute->attributes()->Id . "] - [" . $report->Attribute . "] ... ";
            saveContactAllDaily($email, $report->Attribute->attributes()->Id, $report->Attribute);
            //echo "Done\n";
        }else{
                // Skip it. It's false
                echo "[$i/$total]\t====>Skip NULL for $email - " . $report->Attribute->attributes()->Id . " [" . $report->Attribute . "]\n";
        }     
    }
    echo "\n";
    unset($downloadReport);
    return true;
}

function saveContactGeneralDaily($tableName, $downloadReport){
    //echo  $result->ReportResult[0]->attributes()->ContactUniqueIdentifier;
        foreach($downloadReport->ReportResult as $i=>$row){
            $sql = "REPLACE INTO $tableName (`Contactid` ,`AccountId` ,`ContactUniqueIdentifier` ,`FirstName` ,`LastName` ,`Email` ,`Phone` ,
                            `Fax` ,`Status` ,`creationMethod` ,`EmailFormat` ,`DateCreatedUTC` ,`DateModifiedUTC` ,`hbOnUpload` ,`IsTestContact` ,`emailHash`, `crc32`)
                            VALUES (
                            '" . $row->attributes()->Contactid . "', '" . $row->attributes()->AccountId . "', '" . $row->attributes()->ContactUniqueIdentifier . "', '" . addslashes($row->attributes()->FirstName) . "', '" . addslashes($row->attributes()->LastName) . "', '" . $row->attributes()->Email . "', '" . $row->attributes()->Phone . "', 
                            '" . $row->attributes()->Fax . "', '" . $row->attributes()->Status . "', '" . $row->attributes()->creationMethod . "', '" . $row->attributes()->EmailFormat . "', '" . $row->attributes()->DateCreatedUTC . "' , '" . $row->attributes()->DateModifiedUTC . "' ,'" . $row->attributes()->hbOnUpload . "' ,'" . $row->attributes()->IsTestContact . "' ,'" . md5(strtolower($row->attributes()->ContactUniqueIdentifier)) . "','" . crc32(strtolower($row->attributes()->ContactUniqueIdentifier)) . "')";
                            $r = mysql_query($sql);
            if($r){
                //echo "==>Success: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
            }else{
                echo "====>Failed: " . $row->attributes()->ContactUniqueIdentifier . " \n\r";
                echo "====>Mysql Error: " . mysql_error() . "\n\r";
				//echo "====>Sql Query : " . $sql . "\n\r";
            }
        }     
}


function saveContactAllDaily($email,$attributeId,$attributeValue){
    $flag = "success";    
    if(!checkJoinEmailExist($email)){
        // Let's create a new one first
        $inssql = "INSERT INTO `arcamax`.`LeonCampaignContactJoin` (`3818568`, `3818583`, `3818578`, `3818558`, `3818563`, `3818573`, `1`, `2`, `3`, `4`, `9`, `10`, `11`, `5`, `6`, `7`, `3834418`, `3834428`, `3834493`, `3834483`, `3834438`, `3834458`, `3834468`, `3834378`, `4240263`, `4240273`,`4362328`, `3844863`, `3844823`, `3844813`, `3844833`, `3844903`, `3844883`, `3844783`, `3844843`, `4195798`, `3844893`, `3844803`, `4195818`, `3844853`, `4195808`, `3844873`,`4362338`,`4369063`, `4195828`, `3844768`, `3844793`, `3834333`, `4173573`, `4173658`, `4173668`, `4173678`, `4173688`, `4173563`, `3845658`, `3845663`, `3845668`, `3834363`, `3834388`, `3834448`, `3834288`, `3834408`, `3833693`) VALUES ('$email', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
        $insertResult = mysql_query($inssql); 
        if(!$insertResult)echo "\n>>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
        //$iflag = $insertResult?"Done":"Failed";
        //if(DEBUG_PRINT) echo "$i/$total - $process% - Insert [$email] ... $iflag\n";
    }
    
    // Alright, let's do the update
    $upsql = "UPDATE `arcamax`.`LeonCampaignContactJoin` SET `$attributeId` = '$attributeValue' WHERE `LeonCampaignContactJoin`.`3818568` = '$email'";
    $updateResult = mysql_query($upsql); 
    if(!$updateResult)echo "\n>>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";
    //$uflag = $updateResult?"Done":"Failed";
    //if(DEBUG_PRINT)echo "$i/$total - $process% - Update [$email] - $attributeId - [$attributeValue] ... $uflag\n";
    //if($i == 3)break;            
}

function checkJoinEmailExist($email){
    $ssql = "SELECT count(*) FROM `LeonCampaignContactJoin` WHERE `3818568` LIKE '$email'";
    $selectResult = mysql_query($ssql);
    if(!$selectResult)echo "\n>>>>>>>>>>>>>>>>>" . mysql_error() . "<<<<<<<<<<<<<<<<<<<<<<<<<\n";    
    $totalRows = mysql_fetch_array($selectResult);
    $tr = $totalRows[0];    
    if($tr > 0){
        return true;
    }else{
        return false;
    }
}

function downloadUserFromCampaignerByEmail($email){
    $lastDayContacts = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                        <group>
                            <filter>
                                <filtertype>SearchAttributeValue</filtertype>
                                <staticattributeid>3</staticattributeid>
                                    <action>
                                        <type>Text</type>
                                        <operator>EqualTo</operator>
                                        <value>'.$email.'</value>
                                    </action>
                            </filter>
                        </group>
                    </contactssearchcriteria>';
    //echo $lastDayContacts;

    $contactReportor = new Contact();
    
    // save details attributes
    $r = $contactReportor->saveReport($lastDayContacts, "rpt_Contact_Attributes",2500, "LeonCampaignContactDetails", "saveContactDetailsDaily");
    //print_r($r);         


    // save general information
    $result =  $contactReportor->saveReport($lastDayContacts, 'rpt_Contact_Details',24000,'LeonCampaignContact','saveContactGeneralDaily');   
}
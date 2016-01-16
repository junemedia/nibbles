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

?>
<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

// Get the general and the detail report
function getIsBooleanReport($isBooleanName = "IsBudgetCooking"){
    $isBooleanReportArray = array();
    $isBooleanReportArray["name"] = $isBooleanName;
    $creport = new Contact();
    // Get the subscribed only and soft bounced
    
    global $stateArray;
    
    $filterIsBoolean = setupQueryFilter($isBooleanName);
    $TotalQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                        <group>
                            ' . $filterIsBoolean . '
                        </group>
                    </contactssearchcriteria>';

    // Let's get the total number first
    echo "==>Report for[$isBooleanName] --> ";
    $generalResult =  $creport->getGeneralReport($TotalQuery);
    echo $generalResult->RunReportResult->RowCount . "\n";
    $isBooleanReportArray["total"] = $generalResult->RunReportResult->RowCount;
    
    echo "\t==>Details Report for [$isBooleanName]\n";
    
    foreach($stateArray as $i=>$state){
       echo "\t\t$i) State [$state] --> ";
       $filterState = setupStateFilter($state);
       $stateQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                            <group>
                                ' . $filterIsBoolean . $filterState . '
                            </group>
                        </contactssearchcriteria>';
       $stateResult =  $creport->getGeneralReport($stateQuery);
       echo $stateResult->RunReportResult->RowCount . "\n";
       
       // Try more time if failed
       if(!isset($stateResult->RunReportResult->RowCount)) {
           $stateResult =  $creport->getGeneralReport($stateQuery);
           echo $stateResult->RunReportResult->RowCount . "\n";
       }
       
       $isBooleanReportArray["state"][$state] = $stateResult->RunReportResult->RowCount; 
    }
    
    //print_r($isBooleanReportArray);exit;
    $creport->destroy();
    
    return $isBooleanReportArray;    
}

//echo getIsBooleanReport("IsDailyRecipes");exit;


function saveStateDb($stateArray){
    echo "\t==>Saving into DB for[" . $stateArray["name"] . "] ... ";
    foreach($stateArray["state"] as $state=>$counts){
        $sql = "INSERT INTO `arcamax`.`LeonCampaignStatesReport` 
                (`id` ,`states` ,`attribute` ,`counts`) VALUES (
                NULL , '$state', '" . $stateArray["name"] . "', $counts
                )";
        mysql_query($sql);        
    }
    echo "Done\n";    
}

// Truncate table first
echo "\tTruncate table LeonCampaignStatesReport ... ";
mysql_query("TRUNCATE table LeonCampaignStatesReport");
echo "Done\n";

global $isBooleanArray;
// Now we are ready and cook everything
foreach($isBooleanArray as $value){
    $stateReportArray = getIsBooleanReport($value);
    saveStateDb($stateReportArray);
    $isArray[] = $stateReportArray;
}

/*
$isArray = array(
              "0" => array
                    (
                        "name" => "IsBudgetCooking",
                        "total" => "65792",
                        "state" => array
                            (
                                "Mississippi" => 361,
                                "Alabama" => 701,
                                "Louisiana" => 594,
                                "South Carolina" => 666,
                                "Utah" => 218,
                                "Tennessee" => 912
                            )
                    )
        );
*/
        
$export = "";
foreach($isArray as $item){
    $export .= $item["name"] . "," . $item["total"] . "\n";
    foreach($item["state"] as $state=>$num){
        $export .= "," . $state . "," . $num . "\n";
    }
}
     
$file = dirname(__FILE__) . "export.csv";
$handle = fopen($file, 'w'); 
fwrite($handle, $export);
fclose($handle);     
?>
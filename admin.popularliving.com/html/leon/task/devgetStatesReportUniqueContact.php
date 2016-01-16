<?php
require_once(dirname(__FILE__) . '/../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');
//getStatesContact($states = "Wyoming");

// Get the general and the detail report
function getStateCount($stateName = "Vermont", $creport){
$IsBooleanQuery = "";        
    // Get the subscribed only and soft bounced    
    $TotalQuery = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault>
                        <group>                        
                            <filter>
                                <relation>And</relation>
                                <filtertype>SearchAttributeValue</filtertype>
                                <contactattributeid>3834448</contactattributeid>
                                <action>
                                    <type>Text</type>
                                    <operator>EqualTo</operator>
                                    <value>'.$stateName.'</value>
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

    // Let's get the total number first
    echo "\t[$stateName] --> ";
    $generalResult =  $creport->getGeneralReport($TotalQuery);
    echo $generalResult->RunReportResult->RowCount . "\n";
    $result = $generalResult->RunReportResult->RowCount;   
    //print_r($isBooleanReportArray);exit;
    
    return $result;    
}

$creport = new Contact();
global $stateArray;
$printArray = array();
foreach($stateArray as $state){
    $printArray[$state] = getStateCount($state, $creport);
}
$creport->destroy();

print_r($printArray);

echo "Saving to file ";

$export = "";
foreach($printArray as $key=>$value){
    $export .= $key . "," . $value . "\n";
}

     
$file = dirname(__FILE__) . "/export.csv";
echo "[$file] ... ";
$handle = fopen($file, 'w'); 
fwrite($handle, $export);
fclose($handle);
echo " Done\n";

?>
<?php

require(dirname(__FILE__) . '/../../config.inc.php');
require_once(CAMPAIGNER_LEON_ROOT . 'controllers/contactController.php');

$attributeList = "SELECT * FROM `LeonCampaignContactAttribute` ";
$attr_result = mysql_query($attributeList);
while($row = mysql_fetch_array($attr_result)){
    $attr_array[$row['Id']] = $row;
}

$user_status = array(1=>'Unsubscribed',2=>'Subscribed',3=>'HardBounce',4=>'SoftBounce',5=>'Pending');

$attributeType = array("Custom"=>"contactattributeid", ""=>"",""=>"");

?>


<form id="form_query" name="form_query" method="post" accept-charset="utf8" enctype="multipart/form-data" action="report.php">
    <div id="form_id">
        <table>
            <tr><td>Booleans</td><td><select name="attr_id"><?php foreach($attr_array as $key=>$row){ echo "<option value='".$row['Id']."'>".$row['AttributeType'].' - ['.$row['Name'].'] - ('.$row['Id'].")</option>";} ?></select></td></tr>
            <tr><td>User status</td><td><select name="user_status"><?php foreach($user_status as $key=>$values){ echo "<option value='$key'>$values</option>";} ?></select></td></tr>
            <tr><td colspan="2"><button type="submit" name="submit" value="submit">Submit</button></td></tr>
        </table>
    </div>
</form>


<?php

if($_POST['submit']){
    
    
    var_dump($_POST);
    
    $queryStringHeader = '<contactssearchcriteria><version major="2" minor="0" build="0" revision="0" /><accountid>439960</accountid><set>Partial</set><evaluatedefault>True</evaluatedefault><group>';               
    $queryStringEnder = '</group></contactssearchcriteria>';

    
    
    
    $attr = $attr_array[$_POST['attr_id']];
    var_dump($attr);
    
    $filterString = "<filter>
                        <filtertype>SearchAttributeValue</filtertype>
                        <contactattributeid>".$attr['Id']."</contactattributeid>
                        <action>
                            <type>".$attr['DataType']."</type>
                            <operator>EqualTo</operator>
                            <value>1</value>
                        </action>
                    </filter>";
    
   
    echo '<textarea style="width:1010px; height:500px;">';
    $xmlQuery = $queryStringHeader.$filterString.$queryStringEnder;
    echo $xmlQuery;
    //$creport = new Contact();
    //$creport->getTicketByQuery($xmlQuery);
    echo '</textarea>';
    
    // Process the result jobs   
}

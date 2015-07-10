<?php

ini_set('max_execution_time', 500000);
$sNewTime = date('Y-m-d H:i:s');
$sOldTime = '';
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$StormPostSOAPUrl = "https://adl6.login.skylist.net/services/SoapRequestProcessor?wsdl";
$StormPostUsername = "tomr@silvercarrot.com";
$StormPostPassword = "silverc";
require_once("/home/scripts/includes/nusoap.php");
$client = new nusoapclient($StormPostSOAPUrl, false);
$authentication_header = "<ns1:username SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns1=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostUsername</ns1:username><ns2:password SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns2=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostPassword</ns2:password>";
$client->setHeaders($authentication_header);


$rFile = fopen("/home/scripts/unsub/r4l2_log.txt","r");
if ($rFile) {
     $sOldTime = fread($rFile, 19);
}

$rFile = fopen("/home/scripts/unsub/r4l2_log.txt","w");
if ($rFile) {
    $sTemp = fwrite($rFile, $sNewTime);
}

if ($sOldTime !='' && $sNewTime !='') {
        $sSubData = "INSERT INTO nibbles_temp.tempProcess2 (email,dateTimeAdded,type,joinListId)
                                        SELECT email, dateTimeAdded, 'sub', joinListId FROM nibbles.joinEmailSub
                                        WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (185,186)";
        $rSubResult = mysql_query($sSubData);
        echo mysql_error();

        $sUnSubData = "INSERT INTO nibbles_temp.tempProcess2 (email,dateTimeAdded,type,joinListId)
                                        SELECT email, dateTimeAdded, 'unsub', joinListId FROM nibbles.joinEmailUnsub
                                        WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (185,186)";
        $rUnSubResult = mysql_query($sUnSubData);
        echo mysql_error();

        for ($i=0; $i<=100; $i++) {
                $rResult = mysql_query("SELECT * FROM nibbles_temp.tempProcess2 ORDER BY dateTimeAdded ASC LIMIT 100");
                echo mysql_error();
                if (mysql_num_rows($rResult) == 0) { break; }
                while ($sRow = mysql_fetch_object($rResult)) {
                	if ($sRow->joinListId == 185) {
                		// RSVP	30
                		$soap_parameters = array('importID' =>30, 'Data' => $sRow->email);
                	} else {
                		// Q&E	29
                		$soap_parameters = array('importID' =>29, 'Data' => $sRow->email);
                	}
                	

            		$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);


			// import IDs for the welcome message templates
			if ($sRow->joinListId == 185) {
                                // RSVP 32 for welcome templates
                                $soap_parameters = array('importID' =>32, 'Data' => $sRow->email);
                        } else {
                                // Q&E  31 for welcome templates
                                $soap_parameters = array('importID' =>31, 'Data' => $sRow->email);
                        }


                        $result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);



			echo $result."\n\n";
                        $rDel = mysql_query("DELETE FROM nibbles_temp.tempProcess2 WHERE id='$sRow->id'");
                }
        }
}

?>



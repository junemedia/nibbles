
<?php

ini_set('max_execution_time', 500000);
$sNewTime = date('Y-m-d H:i:s');
$sOldTime = '';
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$StormPostSOAPUrl = "https://login3.stormpost.datranmedia.com/services/SoapRequestProcessor";
$StormPostUsername = "soap@myfree-newsletter.com";
$StormPostPassword = "storm123";
require_once("/home/scripts/includes/nusoap.php");
$client = new nusoapclient($StormPostSOAPUrl, false);
$authentication_header = "<ns1:username SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns1=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostUsername</ns1:username><ns2:password SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns2=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostPassword</ns2:password>";
$client->setHeaders($authentication_header);

$rFile = fopen("/home/scripts/unsub/skylist_log.txt","r");
if ($rFile) {
        $sOldTime = fread($rFile, 19);
}

$rFile = fopen("/home/scripts/unsub/skylist_log.txt","w");
if ($rFile) {
        $sTemp = fwrite($rFile, $sNewTime);
}

if ($sOldTime !='' && $sNewTime !='') {
        // get all new subscribers from main table and insert them into temp table
        $sSubData = "INSERT INTO nibbles_temp.processSkylist (email,dateTimeAdded,type,joinListId,ip)
                     SELECT email, dateTimeAdded, 'sub', joinListId, remoteIp FROM nibbles.joinEmailSub
                     WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (104,45,161,213,81,124,173,166,216,160,162,46,10)";
        $rSubResult = mysql_query($sSubData);
        echo mysql_error();

        // get all new unsubscribers from main table and insert them into temp table
        $sUnSubData = "INSERT INTO nibbles_temp.processSkylist (email,dateTimeAdded,type,joinListId,ip)
                       SELECT email, dateTimeAdded, 'unsub', joinListId, remoteIp FROM nibbles.joinEmailUnsub
                       WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (104,45,161,213,81,124,173,166,216,160,162,46,10)";
        $rUnSubResult = mysql_query($sUnSubData);
        echo mysql_error();

        for ($i=0; $i<=100; $i++) {
                // process 100 records at a time to avoid time out.
                $rResult = mysql_query("SELECT * FROM nibbles_temp.processSkylist ORDER BY dateTimeAdded ASC LIMIT 100");
                echo mysql_error();
                if (mysql_num_rows($rResult) == 0) { break; }
                while ($sRow = mysql_fetch_object($rResult)) {
                        $s_104 = '';$s_45 = '';$s_161 = '';$s_216 = '';$s_81 = '';$s_124 = '';
                        $s_173 = '';$s_166 = '';$s_213 = '';$s_160 = '';$s_162 = '';$s_46 = '';$s_10 = '';

                        switch ($sRow->joinListId) {
                                case 10:
                                        if ($sRow->type == 'sub') { $s_10 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_10 = '0'; }
                                        break;
                                case 46:
                                        if ($sRow->type == 'sub') { $s_46 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_46 = '0'; }
                                        break;
                                case 162:
                                        if ($sRow->type == 'sub') { $s_162 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_162 = '0'; }
                                        break;
                                case 160:
                                        if ($sRow->type == 'sub') { $s_160 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_160 = '0'; }
                                        break;
                                case 213:
                                        if ($sRow->type == 'sub') { $s_213 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_213 = '0'; }
                                        break;
                                case 166:
                                        if ($sRow->type == 'sub') { $s_166 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_166 = '0'; }
                                        break;
                                case 104:
                                        if ($sRow->type == 'sub') { $s_104 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_104 = '0'; }
                                        break;
                                case 45:
                                        if ($sRow->type == 'sub') { $s_45 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_45 = '0'; }
                                        break;
                                case 161:
                                        if ($sRow->type == 'sub') { $s_161 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_161 = '0'; }
                                        break;
                                case 216:
                                        if ($sRow->type == 'sub') { $s_216 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_216 = '0'; }
                                        break;
                                case 81:
                                        if ($sRow->type == 'sub') { $s_81 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_81 = '0'; }
                                        break;
                                case 124:
                                        if ($sRow->type == 'sub') { $s_124 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_124 = '0'; }
                                        break;
                                case 173:
                                        if ($sRow->type == 'sub') { $s_173 = '1'; }
                                        if ($sRow->type == 'unsub') { $s_173 = '0'; }
                                        break;
                        }
                        
$Data="$sRow->email,$s_104,$s_45,$s_161,$s_216,$s_81,$s_124,$s_173,$s_166,$s_213,$s_160,$s_162,$s_46,$s_10,$sRow->ip";              

if ($sRow->type == 'sub') {     
	$par = array('importID' =>210, 'sendID' =>83, 'Data' => $Data);
        $result = $client->call('doImportAndSendFromTemplate',$par,'http://services.web.stormpost.skylist.com','',false);
        echo "SUB: Data: $Data\nResult: ".var_dump($result)."\n\n\n\n\n"; // this must return success
} else {
        $par = array('importID' =>301, 'Data' => $Data);
        $result = $client->call('doImportFromTemplate',$par,'http://services.web.stormpost.skylist.com','',false);
        echo "UNSUB: Data: $Data\nResult: ".var_dump($result)."\n\n\n\n\n"; // this must return success
}


                 	$rDel = mysql_query("DELETE FROM nibbles_temp.processSkylist WHERE id='$sRow->id' LIMIT 1");
                }
        }
}

?>




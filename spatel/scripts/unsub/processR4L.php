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


$rFile = fopen("/home/scripts/unsub/r4l_log.txt","r");
if ($rFile) {
	$sOldTime = fread($rFile, 19);
}

$rFile = fopen("/home/scripts/unsub/r4l_log.txt","w");
if ($rFile) {
	$sTemp = fwrite($rFile, $sNewTime);
}

if ($sOldTime !='' && $sNewTime !='') {
	$sSubData = "INSERT INTO nibbles_temp.tempProcessR4l (email,dateTimeAdded,type)
					SELECT email, dateTimeAdded, 'sub' FROM nibbles.joinEmailConfirm
					WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (224)";
	$rSubResult = mysql_query($sSubData);
	echo mysql_error();
	
	$sUnSubData = "INSERT INTO nibbles_temp.tempProcessR4l (email,dateTimeAdded,type)
					SELECT email, dateTimeAdded, 'unsub' FROM nibbles.joinEmailUnsub
					WHERE dateTimeAdded BETWEEN '$sOldTime' AND '$sNewTime' AND joinListId IN (224)";
	$rUnSubResult = mysql_query($sUnSubData);
	echo mysql_error();
	
	for ($i=0; $i<=100; $i++) {
		$rResult = mysql_query("SELECT * FROM nibbles_temp.tempProcessR4l ORDER BY dateTimeAdded ASC LIMIT 100");
		echo mysql_error();
		if (mysql_num_rows($rResult) == 0) { break; }
		while ($sRow = mysql_fetch_object($rResult)) {
			if ($sRow->type == 'sub') {
				$soap_parameters = array('importID' =>3, 'Data' => $sRow->email);
			} else {
				$soap_parameters = array('importID' =>2, 'Data' => $sRow->email);
			}
		
			$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);
			//echo "$result\n\n";

			//if ($result != 'Request successfully processed') {
			//	var_dump($result);
				//mail('spatel@amperemedia.com','error in script: processR4L.php',$result);
			//}
			$rDel = mysql_query("DELETE FROM nibbles_temp.tempProcessR4l WHERE id='$sRow->id'");
		}
	}
}

?>

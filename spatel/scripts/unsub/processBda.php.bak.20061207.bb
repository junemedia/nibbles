<?php

	ini_set('max_execution_time', 500000000);
	$iniConfig = parse_ini_file( "/home/scripts/includes/mysqlServer.conf" );
	$sNotify = $iniConfig['recipNotify'];
	include( "/home/scripts/includes/cssLogFunctions.php" );
	$iScriptId = cssLogStart( "processBda.php" );
	$StormPostSOAPUrl = "https://silvercarrot2.login.skylist.net/services/SoapRequestProcessor";
	$StormPostUsername = "jr@myfree.com";
	$StormPostPassword = "bestjohn";
	require_once("/home/scripts/includes/nusoap.php");
	$client = new nusoapclient($StormPostSOAPUrl, false);
	$authentication_header = "<ns1:username SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns1=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostUsername</ns1:username><ns2:password SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns2=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostPassword</ns2:password>";
	$client->setHeaders($authentication_header);
	mysql_connect($iniConfig['mysqlMASTERIP'],$iniConfig['mysqlNibblesUSER'],$iniConfig['mysqlNibblesPASS']);
	mysql_select_db($iniConfig['mysqlDatabaseNibbles']);
	$sNewDataTimeAdded = "";
	$iCount = 0;
	
	
	$rFile = fopen("/home/scripts/unsub/log.txt","r");
	if ($rFile) {
		$sDateTimeAdded = fread($rFile, 19);
	}
	echo "Start DateTime: $sDateTimeAdded\n\n";

	$sGetSubData = "SELECT email, dateTimeAdded
			FROM joinEmailConfirm
			WHERE dateTimeAdded > '$sDateTimeAdded'
			AND joinListId = '215'
			ORDER BY dateTimeAdded ASC";
	$rGetSubDataResult = mysql_query($sGetSubData);
	$iTotal = mysql_num_rows($rGetSubDataResult);

	while ($sSubData = mysql_fetch_object($rGetSubDataResult)) {
		$soap_parameters = array('importID' =>3, 'Data' => $sSubData->email);
		$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);
		$sNewDataTimeAdded = $sSubData->dateTimeAdded;
		$iCount++;
		
		if ($iCount % 50 == 0) {
			echo "$iCount / $iTotal \n";
			sleep(1);
		}
	}

	if ($sNewDataTimeAdded != "") {
		$rFile = fopen("/home/scripts/unsub/log.txt","w");
		if ($rFile) {
			$sTemp = fwrite($rFile, $sNewDataTimeAdded);
		}
		echo "End DateTime: $sNewDataTimeAdded\n\n";
	}
	

	$sGetUnSubData = "SELECT email FROM joinEmailUnsub
			WHERE dateTimeAdded > '$sDateTimeAdded'	AND joinListId = '215'";
	$rGetUnSubDataResult = mysql_query($sGetUnSubData);
	if (!($rGetUnSubDataResult)) {
		$rGetUnSubDataResult = mysql_query($sGetUnSubData);
	}
	$iTotal = mysql_num_rows($rGetUnSubDataResult);
	$iCount = 0;
	
	while ($sUnSubData = mysql_fetch_object($rGetUnSubDataResult)) {
		$soap_parameters = array('importID' =>2, 'Data' => $sUnSubData->email);
		$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);
		$iCount++;
		
		if ($iCount % 50 == 0) {
			echo "$iCount / $iTotal \n";
			sleep(1);
		}
	}
	

	cssLogFinish( $iScriptId );
?>

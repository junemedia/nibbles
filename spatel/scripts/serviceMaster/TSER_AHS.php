<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

$sQuery = "SELECT * FROM nibbles.otData WHERE offerCode = 'TSER_AHS' AND howSent='rte'";
$sOtResult = dbQuery($sQuery);
while ($sOtRow = mysql_fetch_object($sOtResult)) {
	$sXmlContent = "<?xml version='1.0' encoding='utf-8' standalone='no' ?>
		<!DOCTYPE wshRequest SYSTEM 'http://www.servicemaster.com/DTD/wshRequest.dtd'>
		<wshRequest>  
		<type>Order</type> 
		<proxyUserId>xmlAmpere</proxyUserId>
		<partner>
		  <name>Ampere</name> 
		  <uniqueId>15D3574C-0BCE-48FD-8BC6-9AD21AD33135</uniqueId> 
		  <password>meij34uH99</password> 
		  </partner>
		<customer>
		  <firstName>[first]</firstName> 
		  <lastName>[last]</lastName> 
		<email>[email]</email>
		   </customer>
		<serviceAddress>
		  <addressLine1>[address]</addressLine1> 
		  <city>[city]</city> 
		  <state>[state]</state> 
		  <zip>[zip]</zip> 
		  <firstName>[first]</firstName> 
		  <lastName>[last]</lastName> 
		  <phone1area>[phone_areaCode]</phone1area> 
		  <phone1exch>[phone_exchange]</phone1exch> 
		  <phone1suffix>[phone_number]</phone1suffix> 
		  <phone2area>[phone_areaCode]</phone2area> 
		  <phone2exch>[phone_exchange]</phone2exch> 
		  <phone2suffix>[phone_number]</phone2suffix> 
		  </serviceAddress>
		<order>
		 <item>
		  <productName>Home Warranty Lead</productName>
		  <property>
		   <name>adName</name>
		   <value>Ampere_20070123</value>
		  </property>
		  <property>
		   <name>oiPropLeadPlacementBrand</name>
		   <value>AH</value>
		  </property>
		  <property>
		   <name>oiPropResponseVehicle</name>
		   <value>online</value>
		  </property>
		  <property>
		   <name>oiPropResultsEntity</name>
		   <value>NCD</value>
		  </property>
		</item>
		</order>
		</wshRequest>";
	
	$sUserData = "SELECT * FROM nibbles.userData WHERE email = \"$sOtRow->email\"";
	$sUserResult = dbQuery($sUserData);
	while ($sUserRow = mysql_fetch_object($sUserResult)) {
		$sXmlContent = str_replace("[email]",$sUserRow->email, $sXmlContent);
		$sXmlContent = str_replace("[first]",$sUserRow->first, $sXmlContent);
		$sXmlContent = str_replace("[last]",$sUserRow->last, $sXmlContent);
		$sXmlContent = str_replace("[address]",$sUserRow->address, $sXmlContent);
		$sXmlContent = str_replace("[city]",$sUserRow->city, $sXmlContent);
		$sXmlContent = str_replace("[state]",$sUserRow->state, $sXmlContent);
		$sXmlContent = str_replace("[zip]",$sUserRow->zip, $sXmlContent);
		$sXmlContent = str_replace("[phone_areaCode]", substr($sUserRow->phoneNo,0,3), $sXmlContent);
		$sXmlContent = str_replace("[phone_exchange]", substr($sUserRow->phoneNo,4,3), $sXmlContent);
		$sXmlContent = str_replace("[phone_number]", substr($sUserRow->phoneNo,8,4), $sXmlContent);
	}

	$alternate_opts = array('http'=>array('method'=>"POST",'header'=>"Content-type: application/x-www-form-urlencoded\r\n" .
				"Content-length: " . strlen("$sXmlContent"),'content'=>"$sXmlContent"));
	$context = stream_context_create($alternate_opts);
	$fp = fopen('https://www.servicemaster.com/partnerInterface/wshRequest.jsp', 'r', false, $context);
	$buffer='';
	//while(!feof($fp)) {
	//	$buffer = fread($fp, 8192);
	//}
	fclose($fp);

	
	$sUpdateOtData = "UPDATE nibbles.otData
				SET howSent = 'rtfpp',
				realTimeResponse = \"$buffer\"
			WHERE id = '$sOtRow->id' LIMIT 1";
	$sUpdateOtDataResult = dbQuery($sUpdateOtData);
}



?>


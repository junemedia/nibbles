<?php

ini_set('max_execution_time', 5000000);

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");



/*
$StormPostSOAPUrl = "https://silvercarrot2.login.skylist.net/services/SoapRequestProcessor";
$StormPostUsername = "jr@myfree.com";
$StormPostPassword = "bestjohn";

$head1 = new SoapHeader('http://services.web.stormpost.skylist.com',
                        'username',
                        $StormPostUsername,
                        0,'http://schemas.xmlsoap.org/soap/actor/next');

$head2 = new SoapHeader('http://services.web.stormpost.skylist.com',
                        'password',
                        $StormPostPassword,
                        0,'http://schemas.xmlsoap.org/soap/actor/next');

*/


$StormPostSOAPUrl = "https://silvercarrot2.login.skylist.net/services/SoapRequestProcessor";
$StormPostUsername = "jr@myfree.com";
$StormPostPassword = "bestjohn";
require_once("/home/scripts/includes/nusoap.php");
$client = new nusoapclient($StormPostSOAPUrl, false);
$authentication_header = "<ns1:username SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns1=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostUsername</ns1:username><ns2:password SOAP-ENV:actor=\"http://schemas.xmlsoap.org/soap/actor/next\" SOAP-ENV:mustUnderstand=\"0\" xsi:type=\"SOAP-ENC:string\" xmlns:ns2=\"http://services.web.stormpost.skylist.com\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\">$StormPostPassword</ns2:password>";
$client->setHeaders($authentication_header);


$sMailTo = 'spatel@amperemedia.com';




		$res = mysql_query("SELECT count(*) as count FROM nibbles_temp.tempGrid");

		$count = mysql_fetch_object($res);

		// Start sending data
		for($i=0;$i<$count->count;$i+= 1000){
	
			$sLastRunDateTime = date('Y-m-d H:i:s');

			$rResult = mysql_query("SELECT * FROM nibbles_temp.tempGrid LIMIT $i, 1000");
			echo mysql_error();
			echo "\n\n";
			$sGridResponse = '';
			$sRealTimeResponse = '';
			$iCount = 0;
			while ($sRow = mysql_fetch_object($rResult)) {
				/*$client = new SoapClient(NULL,
				                        array(  'location' => "https://silvercarrot2.login.skylist.net/services/SoapRequestProcessor",
			        	                        'uri'=>"http://services.web.stormpost.skylist.com")
				                        );*/
				$iCount++;
				echo ".";
				
				if ($iCount % 500 == 0) {
					sleep(3);
				}
				
				$sLogInsert = "INSERT INTO nibbles_datafeed.dataFeedLog (email,partner,dateTime)
								VALUES (\"$sRow->email\",'grid', NOW())";
				$rLogResult = mysql_query($sLogInsert);
				if (!($rLogResult)) {
					echo mysql_error();
					//mail($sMailTo, 'query failed'.__LINE__, $sLogInsert."\n\n\n".mysql_error());
				}
				
				
				
				//Start Process Grid Part
				$soap_parameters = array('importID' =>1, 'Data' => $sRow->email.",".$sRow->dateTimeAdded);
				$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);
				$sGridResponse .= "\n$result";
				//End Process Grid Part
				
				echo $result;
				
				
				/*
				//Start Process Grid Part
				$soap_parameters = array('importID' =>1, 'Data' => $sRow->email.",".$sRow->dateTimeAdded);
				//$result = $client->call('doImportFromTemplate',$soap_parameters,'http://services.web.stormpost.skylist.com','',false);
				$result = $client->__SoapCall('doImportFromTemplate',
                                				$soap_parameters,
                                				NULL,
                                				array($head1, $head2)
                                				);
				$sGridResponse .= "\n$result";
			
				//echo $sGridResponse;
				// if the server response is not okay, then send out alert email to IT.
				if ($result != 'Request successfully processed') {
					mail('spatel@amperemedia.com',"Grid returned negative server response   ".__LINE__ ,$result."\n\nsoap_parameters:".print_r($soap+parameters,true));
				} else {
				//End Process Grid Part
				*/
				

		
		

					$sDelete = "DELETE FROM nibbles_temp.tempGrid WHERE email = \"$sRow->email\" LIMIT 1";
					$rDeleteResult1 = mysql_query($sDelete);
				
					if (!($rDeleteResult1)) {
						$rDeleteResult1 = mysql_query($sDelete);
					}
				
				
					if (!($rDeleteResult1)) {
						echo mysql_error();
						//mail($sMailTo, 'query failed'.__LINE__, $sDelete."\n\n\n".mysql_error());
					}
				//}

				//$client = NULL;
			}
			
			//echo "\n\n\nNet: $iCount\n";
			sleep(2);
			$sCurrentDateTime = date('Y-m-d H:i:s');
						
			$sUpdateLog1 = "UPDATE nibbles_datafeed.dataFeedCountLog
								SET dupes = '0',
								tld = '0',
								email = '0',
								domain = '0',
								src = '0',
								net = '$iCount'
							WHERE startDate = '$sLastRunDateTime'
							AND endDate = '$sCurrentDateTime'
							AND partner = 'grid'";
			$rUpdateLogResult1 = mysql_query($sUpdateLog1);
			if (!($rUpdateLogResult1)) {
				echo mysql_error();
				//mail($sMailTo, 'query failed'.__LINE__, $sUpdateLog1."\n\n\n".mysql_error());
			}
			
			
			$sToday = date('Y-m-d');

			$sCheckQuery = "SELECT * FROM nibbles_datafeed.dataSentStats WHERE date = '$sToday' AND script='grid'";
			$rCheckResult = mysql_query($sCheckQuery);
			$rCheckResult = mysql_query($sCheckQuery);
			echo mysql_error();
			
			if (!($rCheckResult)) {
				//mail($sMailTo, 'query failed'.__LINE__, $sCheckQuery."\n\n\n".mysql_error());
				$rCheckResult = mysql_query($sCheckQuery);
				echo mysql_error();
			}
			if (mysql_num_rows($rCheckResult) == 0) {
				$asdf = "INSERT INTO nibbles_datafeed.dataSentStats (count,date,script) VALUES('$iCount','$sToday','grid')";
				$rInsert = mysql_query($asdf);
				echo mysql_error();
				if (!($rInsert)) {
					//mail($sMailTo, 'query failed'.__LINE__, $asdf."\n\n\n".mysql_error());
				}
		
				echo mysql_error();
			} else {
				$asdf = "UPDATE nibbles_datafeed.dataSentStats
							SET 	count = count + $iCount
							WHERE date = '$sToday'
							AND script='grid'";
				$rUpdateResult = mysql_query($asdf);
				echo mysql_error();
				if (!($rUpdateResult)) {
					//mail($sMailTo, 'query failed'.__LINE__, $asdf."\n\n\n".mysql_error());
				}
			}
		echo "Net: $count->count\n";
		}



?>
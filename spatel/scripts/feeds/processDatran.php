<?php

ini_set('max_execution_time', 5000000);

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sMailTo = 'spatel@amperemedia.com';

$rCheckScript = mysql_query("SELECT endDateTime FROM cronScriptStatus WHERE scriptName='processDatran.php' ORDER BY startDateTime DESC LIMIT 1");

while ($sValueRow = mysql_fetch_object($rCheckScript)) {
	if ($sValueRow->endDateTime == NULL || $sValueRow->endDateTime == '0000-00-00 00:00:00' || $sValueRow->endDateTime == '') {
		$sMsg = "w0:/home/scripts/feeds/processDatran.php\n\n";
		$sMsg .= "Can't run another copy of processDatran.php because previous copy is still\n\n";
		mail($sMailTo,'datran script still running', $sMsg);
		echo $sMsg;
		exit;
	}
}



$rCronStatusResult1 = mysql_query("INSERT INTO nibbles.cronScriptStatus(scriptName, startDateTime)  VALUES('processDatran.php', now())");
$iScriptId = mysql_insert_id();





$sDateTimeQuery = "SELECT max(dateTime) AS lastRun
				FROM nibbles_datafeed.dataFeedLastRun
				WHERE partner = 'datran' LIMIT 1";
$rGetDataTime = mysql_query($sDateTimeQuery);
if (!($rGetDataTime)) {
	echo mysql_error();
	//mail($sMailTo, 'query failed'.__LINE__, $sDateTimeQuery."\n\n\n".mysql_error());
}

if (mysql_num_rows($rGetDataTime) > 0) {
	while ($sDateRow = mysql_fetch_object($rGetDataTime)) {
		$sLastRunDateTime = $sDateRow->lastRun;
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sToday = substr($sLastRunDateTime,0,10);
		if (substr($sLastRunDateTime,0,10) != substr($sCurrentDateTime,0,10)) {
			$sCurrentDateTime = substr($sCurrentDateTime,0,10)." 00:00:00";
		}
	}
	
	$sUpdateDateTime = "UPDATE nibbles_datafeed.dataFeedLastRun
						SET dateTime = '$sCurrentDateTime'
						WHERE partner = 'datran'";
	$rUpdateResult = mysql_query($sUpdateDateTime);
	if (!($rUpdateResult)) {
		echo mysql_error();
		//mail($sMailTo, 'query failed'.__LINE__, $sUpdateDateTime."\n\n\n".mysql_error());
	}
	
	if ($rUpdateResult) {
		$sCurrData = "INSERT IGNORE INTO nibbles_temp.tempDatran 
					(email,first,last,address,address2,city,state,phoneNo,zip,
					gender,level,remoteIp,dateTimeAdded,sourceCode)
				SELECT otData.email,first,last,address,address2,city,state,phoneNo,zip,gender,'Complete',
						otData.remoteIp,otData.dateTimeAdded,sourceCode
				FROM userData, otData 
				WHERE otData.dateTimeAdded BETWEEN '$sLastRunDateTime' AND '$sCurrentDateTime'
				AND userData.email = otData.email
				AND otData.pageId != '238'";

		$rResult = mysql_query($sCurrData);
		if (!($rResult)) {
			echo mysql_error();
			//mail($sMailTo, 'query failed'.__LINE__, $sCurrData."\n\n\n".mysql_error());
		}
		
		
		$sJoinQuery = "SELECT * FROM joinEmailSub 
					WHERE dateTimeAdded BETWEEN '$sLastRunDateTime' AND '$sCurrentDateTime'";
		$rJoinEmailData = mysql_query($sJoinQuery);
		if (!($rJoinEmailData)) {
			echo mysql_error();
			//mail($sMailTo, 'query failed'.__LINE__, $sJoinQuery."\n\n\n".mysql_error());
		}
		if (mysql_num_rows($rJoinEmailData) > 0) {
			while ($sJoinEmail = mysql_fetch_object($rJoinEmailData)) {
				$rUserDataResult = mysql_query("SELECT * FROM userData WHERE email = \"$sJoinEmail->email\" LIMIT 1");
				echo mysql_error();
				if (mysql_num_rows($rUserDataResult) > 0) {
					while ($sRow1 = mysql_fetch_object($rUserDataResult)) {
						$sInsert = "INSERT IGNORE INTO nibbles_temp.tempDatran (email,first,last,address,address2,city,state,
								phoneNo,zip,gender,level,remoteIp,dateTimeAdded,sourceCode)
								VALUES (\"$sRow1->email\",\"$sRow1->first\",\"$sRow1->last\",\"$sRow1->address\",
								\"$sRow1->address2\",\"$sRow1->city\",\"$sRow1->state\",\"$sRow1->phoneNo\",
								\"$sRow1->zip\",\"$sRow1->gender\",'Complete',\"$sRow1->remoteIp\",\"$sJoinEmail->dateTimeAdded\",\"$sJoinEmail->sourceCode\")";
						$rInsertResult = mysql_query($sInsert);
						if (!($rInsertResult)) {
							echo mysql_error();
							//mail($sMailTo, 'query failed'.__LINE__, $sInsert."\n\n\n".mysql_error());
						}
					}
				} else {
					$sInsert = "INSERT IGNORE INTO nibbles_temp.tempDatran (email,level,remoteIp,dateTimeAdded,sourceCode)
									VALUES (\"$sJoinEmail->email\",'Incomplete',\"$sJoinEmail->remoteIp\",
									\"$sJoinEmail->dateTimeAdded\",\"$sJoinEmail->sourceCode\")";
					$rInsertResult = mysql_query($sInsert);
					if (!($rInsertResult)) {
						echo mysql_error();
						//mail($sMailTo, 'query failed'.__LINE__, $sInsert."\n\n\n".mysql_error());
					}
				}
			}
		}
		
		
		
		$rCountResult1 = mysql_query("SELECT email FROM nibbles_temp.tempDatran");
		$iGross = mysql_num_rows($rCountResult1);

		$sInsertLog1 = "INSERT INTO nibbles_datafeed.dataFeedCountLog
					(startDate,endDate,partner,dateAdded,gross) 
					VALUES ('$sLastRunDateTime','$sCurrentDateTime','datran','$sToday','$iGross')";
		$rLogResult1 = mysql_query($sInsertLog1);
		if (!($rLogResult1)) {
			echo mysql_error();
			//mail($sMailTo, 'query failed'.__LINE__, $sInsertLog1."\n\n\n".mysql_error());
		}
		
		echo "\n\nGross: $iGross\n";
		
		
		
		
		$iDupes = 0;
		// dedup the entries.
		$rDupResult = mysql_query("SELECT DISTINCT email FROM nibbles_temp.tempDatran");
		echo mysql_error();
		if (mysql_num_rows($rDupResult) > 0) {
			while ($sDupRow = mysql_fetch_object($rDupResult)) {
				$sCheckQuery = "SELECT * FROM nibbles_datafeed.dataFeedLog
								WHERE email = \"$sDupRow->email\"
								AND dateTime BETWEEN '$sToday 00:00:00' AND '$sToday 23:59:59'
								AND partner = 'datran'";
				$rCheckResult = mysql_query($sCheckQuery);
				if (!($rCheckResult)) {
					echo mysql_error();
					//mail($sMailTo, 'query failed'.__LINE__, $sCheckQuery."\n\n\n".mysql_error());
				}
				if (mysql_num_rows($rCheckResult) > 0) {
					$sDeleteQuery = "DELETE FROM nibbles_temp.tempDatran
							WHERE email = \"$sDupRow->email\" LIMIT 1";
					$rDeleteResult = mysql_query($sDeleteQuery);
					$iDupes += mysql_affected_rows();
					if (!($rDeleteResult)) {
						echo mysql_error();
						//mail($sMailTo, 'query failed'.__LINE__, $sDeleteQuery."\n\n\n".mysql_error());
					}
				}
			}
		}
		
		echo "\nDupes: $iDupes\n";
		

		// start delete
		$iDeletedTld = 0;
		$rGetResult = mysql_query("SELECT TLDs FROM excludeTLDsDataSales");
		if (!($rGetResult)) {
			echo mysql_error();
			//mail($sMailTo, 'query failed'.__LINE__, $rGetResult."\n\n\n".mysql_error());
		}
		while ($rRow = mysql_fetch_object($rGetResult)) {
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempDatran WHERE email LIKE \"%$rRow->TLDs\"");
			$iDeletedTld += mysql_affected_rows();
			echo mysql_error();
		}
		
		echo "\nTLDs: $iDeletedTld\n";

		
		$iDeletedDomain = 0;
		$rGetResult = mysql_query("SELECT domain FROM excludeDomainsDataSales");
		if (!($rGetResult)) {
			echo mysql_error();
			mail($sMailTo, 'query failed'.__LINE__, $rGetResult."\n\n\n".mysql_error());
		}
		while ($rRow = mysql_fetch_object($rGetResult)) {
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempDatran WHERE email LIKE \"%$rRow->domain\"");
			$iDeletedDomain += mysql_affected_rows();
			echo mysql_error();
		}
		
		echo "\nDomains: $iDeletedDomain\n";

		
		$iDeletedEmail = 0;
		$rGetResult = mysql_query("SELECT email FROM excludeEmailDataSales");
		if (!($rGetResult)) {
			echo mysql_error();
			mail($sMailTo, 'query failed'.__LINE__, $rGetResult."\n\n\n".mysql_error());
		}
		while ($rRow = mysql_fetch_object($rGetResult)) {
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempDatran WHERE email = \"$rRow->email\"");
			$iDeletedEmail += mysql_affected_rows();
			echo mysql_error();
		}
		
		echo "\nEmails: $iDeletedEmail\n";

		
		
		$iDeletedSrc = 0;
		$rGetResult = mysql_query("SELECT DISTINCT sourceCode FROM links, partnerCompanies
					WHERE links.partnerId = partnerCompanies.id AND excludeDataSale = '1'");
		if (!($rGetResult)) {
			echo mysql_error();
			mail($sMailTo, 'query failed'.__LINE__, $rGetResult."\n\n\n".mysql_error());
		}
		while ($rRow = mysql_fetch_object($rGetResult)) {
			if ($rRow->sourceCode !='') {
				$rDelete = mysql_query("DELETE FROM nibbles_temp.tempDatran WHERE sourceCode = \"$rRow->sourceCode\"");
				$iDeletedSrc += mysql_affected_rows();
				echo mysql_error();
			}
		}
		echo "\nSrc: $iDeletedSrc\n";
		
		// end delete
		
		
		
		
		// Start sending data
		$rResult = mysql_query("SELECT * FROM nibbles_temp.tempDatran LIMIT 5000");
		echo mysql_error();
		if (mysql_num_rows($rResult) > 0) {
			echo "\n\n";
			$iCount = 0;
			while ($sRow = mysql_fetch_object($rResult)) {
				$iCount++;
				echo ".";
				
				if ($iCount % 500 == 0) {
					sleep(5);
				}
				
				$sLogInsert = "INSERT INTO nibbles_datafeed.dataFeedLog (email,partner,dateTime)
								VALUES (\"$sRow->email\",'datran', NOW())";
				$rLogResult = mysql_query($sLogInsert);
				if (!($rLogResult)) {
					echo mysql_error();
					//mail($sMailTo, 'query failed'.__LINE__, $sLogInsert."\n\n\n".mysql_error());
				}
				
		
				//Start - Process Datran Part
				$sFirst = urlencode($sRow->first);
				$sLast = urlencode($sRow->last);
				$sEmail = urlencode($sRow->email);
				$sAddress = urlencode($sRow->address);
				$sAddress2 = urlencode($sRow->address2);
				$sPhone = urlencode($sRow->phoneNo);
				$sCity = urlencode($sRow->city);
				$sState = urlencode($sRow->state);
				$sZip = urlencode($sRow->zip);
				$sGender = urlencode($sRow->gender);
				$sLevel = urlencode($sRow->level);
				$iIp = urlencode($sRow->remoteIp);
				$sUrlPart = "ampmed.superautoresponders.com/c.aspx";
				$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
				$sHostPart = ereg_replace("\/","",$sHostPart);
				$sScriptPath = substr($sUrlPart,strlen($sHostPart));
				$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
				$sHttpPostString = "p=AMPMED&f=$sFirst&l=$sLast&e=$sEmail&a=$sAddress&h=$sAddress2&t=$sPhone&c=$sCity&s=$sState&z=$sZip&x=US&b=&g=$sGender&r=$sLevel&IP=$iIp&d=www.popularliving.com";
				$sTempResponse = '';
				if ($rSocketConnection) {
					fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
					fputs($rSocketConnection, "Host: $sHostPart\r\n");
					fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
					fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
					fputs($rSocketConnection, "User-Agent: MSIE\r\n");
					fputs($rSocketConnection, "Connection: close\r\n\r\n");
					fputs($rSocketConnection, $sHttpPostString);
				//	while(!feof($rSocketConnection)) {
				//		$sTempResponse .= fgets($rSocketConnection, 1024);
				//	}
					fclose($rSocketConnection);
				}
				//End - Process Datran Part

				
				// if the server response is not okay, then send out alert email to IT.
				//if (substr($sTempResponse,0,15) != 'HTTP/1.1 200 OK') {
				//	mail($sMailTo,"Datran returned negative server response   ".__LINE__."   ".__FILE__,$sTempResponse."\n\n$sHttpPostString\n");
				//}


				$sDelete = "DELETE FROM nibbles_temp.tempDatran WHERE email = \"$sRow->email\" LIMIT 1";
				$rDeleteResult1 = mysql_query($sDelete);
				
				if (!($rDeleteResult1)) {
					$rDeleteResult1 = mysql_query($sDelete);
				}
				
				
				if (!($rDeleteResult1)) {
					echo mysql_error();
					//mail($sMailTo, 'query failed'.__LINE__, $sDelete."\n\n\n".mysql_error());
				}
			}
			echo "\n\n\nNet: $iCount\n";
			
			
			$sUpdateLog1 = "UPDATE nibbles_datafeed.dataFeedCountLog
								SET dupes = '$iDupes',
								tld = '$iDeletedTld',
								email = '$iDeletedEmail',
								domain = '$iDeletedDomain',
								src = '$iDeletedSrc',
								net = '$iCount'
							WHERE startDate = '$sLastRunDateTime'
							AND endDate = '$sCurrentDateTime'
							AND partner = 'datran'";
			$rUpdateLogResult1 = mysql_query($sUpdateLog1);
			if (!($rUpdateLogResult1)) {
				echo mysql_error();
				//mail($sMailTo, 'query failed'.__LINE__, $sUpdateLog1."\n\n\n".mysql_error());
			}
			
			
			

			$sCheckQuery = "SELECT * FROM nibbles_datafeed.dataSentStats WHERE date = '$sToday' AND script='datran'";
			$rCheckResult = mysql_query($sCheckQuery);
			$rCheckResult = mysql_query($sCheckQuery);
			echo mysql_error();
			
			if (!($rCheckResult)) {
				//mail($sMailTo, 'query failed'.__LINE__, $sCheckQuery."\n\n\n".mysql_error());
				$rCheckResult = mysql_query($sCheckQuery);
				echo mysql_error();
			}
			if (mysql_num_rows($rCheckResult) == 0) {
				$asdf = "INSERT INTO nibbles_datafeed.dataSentStats (count,date,script) VALUES('$iCount', '$sToday', 'datran')";
				$rInsert = mysql_query($asdf);
				if (!($rInsert)) {
					//mail($sMailTo, 'query failed'.__LINE__, $asdf."\n\n\n".mysql_error());
				}
				echo mysql_error();
			} else {
				$asdf = "UPDATE nibbles_datafeed.dataSentStats
							SET 	count = count + $iCount
							WHERE date = '$sToday'
							AND script = 'datran'";
				$rUpdateResult = mysql_query($asdf);
				echo mysql_error();
				if (!($rUpdateResult)) {
					//mail($sMailTo, 'query failed'.__LINE__, $asdf."\n\n\n".mysql_error());
				}
			}
		}
	}
}


$rCronStatusResult2 = mysql_query("UPDATE nibbles.cronScriptStatus SET    endDateTime = now() WHERE  id = '$iScriptId'");

?>
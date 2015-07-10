<?php

ini_set('max_execution_time', 5000000);


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


$rCheckScript = mysql_query("SELECT endDateTime FROM cronScriptStatus WHERE scriptName='processGridIn.php' ORDER BY startDateTime DESC LIMIT 1");
while ($sValueRow = mysql_fetch_object($rCheckScript)) {
	if ($sValueRow->endDateTime == NULL || $sValueRow->endDateTime == '0000-00-00 00:00:00' || $sValueRow->endDateTime == '') {
		$sMsg = "w0:/home/scripts/feeds/processGridIn.php\n\n";
		$sMsg .= "Can't run another copy of processGridIn.php because previous copy is still\n\n";
		mail('spatel@amperemedia.com','processGridIn script still running', $sMsg);
		echo $sMsg;
		exit;
	}
}



$rCronStatusResult1 = mysql_query("INSERT INTO nibbles.cronScriptStatus(scriptName, startDateTime)  VALUES('processGridIn.php', now())");
$iScriptId = mysql_insert_id();



$sMailTo = 'spatel@amperemedia.com';


$sDateTimeQuery = "SELECT max(dateTime) AS lastRun
				FROM nibbles_datafeed.dataFeedLastRun
				WHERE partner = 'grid' LIMIT 1";
$rGetDataTime = mysql_query($sDateTimeQuery);
if (!($rGetDataTime)) {
	echo mysql_error();
	mail($sMailTo, 'query failed'.__LINE__, $sDateTimeQuery."\n\n\n".mysql_error());
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
						WHERE partner = 'grid'";
	$rUpdateResult = mysql_query($sUpdateDateTime);
	if (!($rUpdateResult)) {
		echo mysql_error();
		mail($sMailTo, 'query failed'.__LINE__, $sUpdateDateTime."\n\n\n".mysql_error());
	}
	
	if ($rUpdateResult) {
		$sCurrData = "INSERT IGNORE INTO nibbles_temp.tempTempGrid 
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
			mail($sMailTo, 'query failed'.__LINE__, $sCurrData."\n\n\n".mysql_error());
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
						$sInsert = "INSERT IGNORE INTO nibbles_temp.tempTempGrid (email,first,last,address,address2,city,state,
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
					$sInsert = "INSERT IGNORE INTO nibbles_temp.tempTempGrid (email,level,remoteIp,dateTimeAdded,sourceCode)
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
		
		
		
		$rCountResult1 = mysql_query("SELECT email FROM nibbles_temp.tempTempGrid");
		$iGross = mysql_num_rows($rCountResult1);

		$sInsertLog1 = "INSERT INTO nibbles_datafeed.dataFeedCountLog
					(startDate,endDate,partner,dateAdded,gross) 
					VALUES ('$sLastRunDateTime','$sCurrentDateTime','grid','$sToday','$iGross')";
		$rLogResult1 = mysql_query($sInsertLog1);
		if (!($rLogResult1)) {
			echo mysql_error();
			//mail($sMailTo, 'query failed'.__LINE__, $sInsertLog1."\n\n\n".mysql_error());
		}
		
		echo "\n\nGross: $iGross\n";
		
		
		
		
		$iDupes = 0;
		// dedup the entries.
		$rDupResult = mysql_query("SELECT DISTINCT email FROM nibbles_temp.tempTempGrid");
		echo mysql_error();
		if (mysql_num_rows($rDupResult) > 0) {
			while ($sDupRow = mysql_fetch_object($rDupResult)) {
				$sCheckQuery = "SELECT email FROM nibbles_datafeed.dataFeedLog
								WHERE email = \"$sDupRow->email\"
								AND dateTime BETWEEN '$sToday 00:00:00' AND '$sToday 23:59:59'
								AND ( partner = 'grid' OR partner = 'datranGrid' )
						UNION
						SELECT email FROM nibbles_temp.tempGrid
								WHERE email = \"$sDupRow->email\"";
				$rCheckResult = mysql_query($sCheckQuery);
				if (!($rCheckResult)) {
					echo mysql_error();
					//mail($sMailTo, 'query failed'.__LINE__, $sCheckQuery."\n\n\n".mysql_error());
				}
				if (mysql_num_rows($rCheckResult) > 0) {
					$sDeleteQuery = "DELETE FROM nibbles_temp.tempTempGrid
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
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempTempGrid WHERE email LIKE \"%$rRow->TLDs\"");
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
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempTempGrid WHERE email LIKE \"%$rRow->domain\"");
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
			$rDelete = mysql_query("DELETE FROM nibbles_temp.tempTempGrid WHERE email = \"$rRow->email\"");
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
				$rDelete = mysql_query("DELETE FROM nibbles_temp.tempTempGrid WHERE sourceCode = \"$rRow->sourceCode\"");
				$iDeletedSrc += mysql_affected_rows();
				echo mysql_error();
			}
		}
		echo "\nSrc: $iDeletedSrc\n";
		
		// end delete
		
		$sImportTempSQL = "INSERT INTO nibbles_temp.tempGrid SELECT * FROM nibbles_temp.tempTempGrid";
		mysql_query($sImportTempSQL);

		$sClearTempSQL = "TRUNCATE TABLE nibbles_temp.tempTempGrid";
		mysql_query($sClearTempSQL);
		
		
	}
}




$rCronStatusResult2 = mysql_query("UPDATE nibbles.cronScriptStatus SET    endDateTime = now() WHERE  id = '$iScriptId'");



?>

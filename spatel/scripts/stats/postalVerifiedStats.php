<?php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
		$sGrossLeadsQuery = "SELECT count(id) as grossLeads
						FROM otDataHistory
						WHERE dateTimeAdded BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY) AND DATE_ADD(CURRENT_DATE, INTERVAL -1 SECOND)";
		$rGrossLeadsResult = dbQuery($sGrossLeadsQuery);
		echo dbError();
		$oReportRow = dbFetchObject($rGrossLeadsResult);
		$sTodayGrossLeads = $oReportRow->grossLeads;
		
		$sGrossLeadsQuery = "SELECT count(id) as leadsSent
						FROM otDataHistory
						WHERE dateTimeAdded BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY) AND DATE_ADD(CURRENT_DATE, INTERVAL -1 SECOND)
						AND sendStatus = 'S'";
		$rGrossLeadsResult = dbQuery($sGrossLeadsQuery);
		echo dbError();
		$oReportRow = dbFetchObject($rGrossLeadsResult);
		$sTodayLeadsSent = $oReportRow->leadsSent;
		
		$sGrossLeadsQuery = "SELECT count(id) as postalVerified
						FROM otDataHistory
						WHERE dateTimeAdded BETWEEN DATE_ADD(CURRENT_DATE, INTERVAL -1 DAY) AND DATE_ADD(CURRENT_DATE, INTERVAL -1 SECOND)
						AND postalVerified = 'V'";
		$rGrossLeadsResult = dbQuery($sGrossLeadsQuery);
		echo dbError();
		$oReportRow = dbFetchObject($rGrossLeadsResult);
		$sTodayPV = $oReportRow->postalVerified;
			
		$iPVPercent = number_format((($sTodayPV/$sTodayGrossLeads)*100), 2, '.', "");
	
		$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
		
		$sInsertLeadsCountQuery = "INSERT INTO postalVerified(date, grossLeads, leadsSent, postalVerified, percentPostalVerified)
								VALUES ('$sYesterday', '$sTodayGrossLeads', '$sTodayLeadsSent', '$sTodayPV', '$iPVPercent')";
		$rInsertLeadsCountQueryResult = dbQuery($sInsertLeadsCountQuery);
		
		
?>

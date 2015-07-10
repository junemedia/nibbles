<?php

/*********

Script to send join stats email

**********/

$sViewReport = '';
$sSourceCodeFilter = '';


$sPageTitle = "Join System Statistics Report";
	
// make entry into cron script status table

include("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {

		$iYearFrom = substr($sYesterday,0,4);
		$iMonthFrom = substr($sYesterday,5,2);
		$iDayFrom = substr($sYesterday,8,2);

		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;
	}

	$sHeadingRow = "<tr><td><font face=verdana size=1><b>ListId</b></font></td>
						<td><font face=verdana size=1><b>List Name</b></font></td>
						<td align=right><font face=verdana size=1><b>List Count</b></font></td>
						<td align=right><font face=verdana size=1><b>Subscription Count</b></font></td>
						<td align=right><font face=verdana size=1><b>Confirmed Count</b></font></td>
						<td align=right><font face=verdana size=1><b>Purge Count</b></font></td>
						<td align=right><font face=verdana size=1><b>Unsubscription Count</b></font></td>
						<td align=right><font face=verdana size=1><b>Held Count</b></font></td></tr>";

	$sReportContent = "<html><body><table width=80% align=center border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>";
	$sReportContent .= $sHeadingRow;
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
	$sDateTimeFrom = "$iYearFrom-$iMonthFrom-$iDayFrom"." 00:00:00";
	$sDateTimeTo = "$iYearTo-$iMonthTo-$iDayTo"." 23:59:59";
	
	
	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {

			$sReportQuery1 = "SELECT min(joinEmailActive.joinListId) as joinListId, count(joinEmailActive.id) as counts, joinLists.title
	   						  FROM   joinEmailActive, joinLists
	   						  WHERE	 joinEmailActive.joinListId=joinLists.id
							  GROUP BY joinLists.title";		
			//echo $sReportQuery1;
			$rReportResult1 = dbQuery($sReportQuery1);
			echo dbError();
			$i=0;
			while ($oReportRow = dbFetchObject($rReportResult1)) {
				$iJoinListId = $oReportRow->joinListId;
				$iListCount = $oReportRow->counts;
				
				$aReportArray['listCount'][$iJoinListId] = $iListCount;

			}
			//print_r($aReportArray);
			$sPurgeQuery = "SELECT min(joinEmailUnsub.joinListId) as joinListId, count(joinEmailUnsub.id) AS purgeCount, joinLists.title
							FROM   joinEmailUnsub, joinLists
							WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							AND	   isPurge = '1'
							AND    joinEmailUnsub.joinListId=joinLists.id
							GROUP BY joinLists.title";
			$rPurgeResult = dbQuery($sPurgeQuery);
			
			echo  dbError();
			while ($oPurgeRow = dbFetchObject($rPurgeResult)) {
				$iJoinListId = $oPurgeRow->joinListId;
				$iPurgeCount = $oPurgeRow->purgeCount;
				$aReportArray['purgeCount'][$iJoinListId] = $iPurgeCount;
			}							
		
			$sUnsubQuery = "SELECT min(joinEmailUnsub.joinListId) as joinListId, count(joinEmailUnsub.id) AS unsubCount, joinLists.title
							   FROM	  joinEmailUnsub, joinLists
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							   AND	  isPurge = '' 
							   AND    joinEmailUnsub.joinListId=joinLists.id
							   GROUP BY joinLists.title";
			$rUnsubResult = dbQuery($sUnsubQuery);
			
			echo  dbError();
			while ($oUnsubRow = dbFetchObject($rUnsubResult)) {
				$iJoinListId = $oUnsubRow->joinListId;
				$iUnsubCount = $oUnsubRow->unsubCount;
				
				$aReportArray['unsubCount'][$iJoinListId] = $iUnsubCount;
			}							
		
			$sSubQuery = "SELECT min(joinEmailSub.joinListId) as joinListId,  count(distinct joinEmailSub.email) AS subCount, joinLists.title
							   FROM	  joinEmailSub, joinLists
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							   AND    joinEmailSub.joinListId=joinLists.id
							   GROUP BY joinLists.title";
			$rSubResult = dbQuery($sSubQuery);			
			echo  dbError();
			
			while ($oSubRow = dbFetchObject($rSubResult)) {
				$iJoinListId = $oSubRow->joinListId;
				$iSubCount = $oSubRow->subCount;
				
				$aReportArray['subCount'][$iJoinListId] = $iSubCount;
			}
			
			$sConfirmQuery = "SELECT min(joinEmailConfirm.joinListId) as joinListId,  count(distinct joinEmailConfirm.email) AS confirmCount, joinLists.title
							   FROM	  joinEmailConfirm, joinLists
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							   $sSourceCodeFilter
							   AND    joinEmailConfirm.joinListId=joinLists.id
							   GROUP BY joinLists.title";
			$rConfirmResult = dbQuery($sConfirmQuery);			
			echo  dbError();
			
			while ($oConfirmRow = dbFetchObject($rConfirmResult)) {
				$iJoinListId = $oConfirmRow->joinListId;
				$iConfirmCount = $oConfirmRow->confirmCount;
				$aReportArray['confirmCount'][$iJoinListId] = $iConfirmCount;
			}
			
			
			
			
			$sHeldQuery = "SELECT min(joinEmailHeldJournal.joinListId) as joinListId,  count(joinEmailHeldJournal.id) AS heldCount, joinLists.title
							   FROM	  joinEmailHeldJournal, joinLists
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							   AND    joinEmailHeldJournal.joinListId=joinLists.id
							   GROUP BY joinLists.title";
			$rHeldResult = dbQuery($sHeldQuery);			
			echo  dbError();
			
			while ($oHeldRow = dbFetchObject($rHeldResult)) {
				$iJoinListId = $oHeldRow->joinListId;
				$iHeldCount = $oHeldRow->heldCount;
				$aReportArray['heldCount'][$iJoinListId] = $iHeldCount;
			}
			
			
			$sListQuery = "SELECT min(id) as id, title
					  FROM   joinLists
					  GROUP BY title
					  ORDER BY title";
		
		$rListResult = dbQuery($sListQuery);
		
		while ($oListRow = dbFetchObject($rListResult)) {
			$iJoinListId = $oListRow->id;
			$sTitle = $oListRow->title;
			
			$iActiveCount = 0;
			$iInactiveCount = 0;
			$aReportArray['joinListId'][$iJoinListId] = $iJoinListId;
			$aReportArray['title'][$iJoinListId] = $sTitle;
						
				
			$sReportContent .= "\n<tr bgcolor=white><td><font face=verdana size=1>&nbsp;".$aReportArray['joinListId'][$iJoinListId]."</font></td>
										<td><font face=verdana size=1>&nbsp;".$aReportArray['title'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['listCount'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['subCount'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['confirmCount'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['purgeCount'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['unsubCount'][$iJoinListId]."</font></td>
										<td align=right><font face=verdana size=1>&nbsp;".$aReportArray['heldCount'][$iJoinListId]."</font></td>
									</tr>";

				$iPageTotalListCount += $aReportArray['listCount'][$iJoinListId];
				$iPageTotalSubCount += $aReportArray['subCount'][$iJoinListId];
				$iPageTotalConfirmCount += $aReportArray['confirmCount'][$iJoinListId];
				$iPageTotalPurgeCount += $aReportArray['purgeCount'][$iJoinListId];
				$iPageTotalUnsubCount += $aReportArray['unsubCount'][$iJoinListId];
				$iPageTotalHeldCount += $aReportArray['heldCount'][$iJoinListId];
				
		}

		$sReportContent .= "\n<tr bgcolor=white><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
								<tr><td>&nbsp;</td>
								<td class=header><font face=verdana size=1>Page Total Counts</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalListCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalSubCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalConfirmCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalPurgeCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalUnsubCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iPageTotalHeldCount</font></td>
							</tr>";
				
		$sUniqueQuery = "SELECT *
						 FROM   joinEmailStats
						 WHERE  dateAdded = '$sDateFrom'";
		$rUniqueResult = dbQuery($sUniqueQuery);
		while ($oUniqueRow = dbFetchObject($rUniqueResult)) {
			$iUniqueListCount = $oUniqueRow->uniqueListCount;
			$iUniqueSubCount = $oUniqueRow->uniqueSubCount;
			$iUniqueConfirmCount = $oUniqueRow->uniqueConfirmCount;
			$iUniquePurgeCount = $oUniqueRow->uniquePurgeCount;
			$iUniqueUnsubCount = $oUniqueRow->uniqueUnsubCount;
			$iUniqueHeldCount = $oUniqueRow->uniqueHeldCount;
		}

		$sReportContent .= "\n<tr bgcolor=white><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
								<tr><td>&nbsp;</td>
								<td class=header><font face=verdana size=1>Page Total Unique Counts</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniqueListCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniqueSubCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniqueConfirmCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniquePurgeCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniqueUnsubCount</font></td>
								<td class=header align=right><font face=verdana size=1>$iUniqueHeldCount</font></td>
							</tr>";
		
	}
		
	
	$sReportContent .= "</table><BR><BR><table width=80% align=center><tr><Td><font face=verdana size=1><b>Notes:</b> 
	Sub / un-sub / confirm counts are for last 24 hour period ending 11:59 pm yesterday. List count is as of the time this report ran. Counts are for confirmed subscribers only except for lists which do not require confirmation.
	<BR>Held column represents email addresses inserted into the held journal for the period between 12:01 am to 11:59 pm of the previous day.</font></td></tr></table>";
	
	
$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
$sHeaders .= "cc: ";

$sEmailQuery = "SELECT *
			   FROM   emailRecipients
			   WHERE  purpose = 'join system stats'";
$rEmailResult = dbQuery($sEmailQuery);
echo dbError();
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}

if (!($sEmailTo)) {
	$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
} 
	
$sCcTo = substr($sRecipients,strlen($sEmailTo));
	
$sHeaders .= ", $sCcTo";
	
$sHeaders .= "\r\n";

$sSubject = "Join Stats Report - $sRunDateAndTime";
mail($sEmailTo, $sSubject, $sReportContent, $sHeaders);


?>


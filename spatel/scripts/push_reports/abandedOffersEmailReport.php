<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");


$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');
$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');
$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
$sSevenDaysAgo = DateAdd("d", -8, date('Y')."-".date('m')."-".date('d'));

$iYearTo = substr( $sYesterday, 0, 4);
$iMonthTo = substr( $sYesterday, 5, 2);
$iDayTo = substr( $sYesterday, 8, 2);
$iYearFrom = substr( $sSevenDaysAgo, 0, 4);
$iMonthFrom = substr( $sSevenDaysAgo, 5, 2);
$iDayFrom = substr( $sSevenDaysAgo, 8, 2);

$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

$aReportArray = array();
				
$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempAbandedReport";
$rDeleteResult = mysql_query($sDeleteQuery);

$sAbandedQuery = "SELECT offerCode, count(offerCode) as abandedCount 
	FROM abandedOffersHistory
	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	GROUP BY offerCode";
$rAbandedQuery = dbQuery($sAbandedQuery);
while ($oAbandedRow = dbFetchObject($rAbandedQuery)) {
	$sOfferName = '';
	$sGetOfferNameQuery = "SELECT name, offerCompanies.repDesignated FROM offers left join offerCompanies on offerCompanies.id = offers.companyId WHERE offerCode='$oAbandedRow->offerCode'";
	$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
	if (dbNumRows($rGetOfferNameResult)>0) {
		$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
		$sOfferName = $sOfferNameRow->name;
		
		$sUserQuery = "SELECT concat(nbUsers.firstName, ' ', nbUsers.lastName) as name FROM nbUsers where id in (".$sOfferNameRow->repDesignated.")";
		$res = dbQuery($sUserQuery);
		$oUser = dbFetchObject($res);
		$sOfferExec = $oUser->name;
	}

	$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName,acctExec)
	VALUES (\"$oAbandedRow->offerCode\", \"0\", \"$oAbandedRow->abandedCount\",\"0\",\"$sOfferName\",\"$sOfferExec\")";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();
}
				

				
				
$sTakenQuery = "SELECT offerCode, count(offerCode) as takenCount 
	FROM otDataHistory
	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	GROUP BY offerCode";
$rTakenQuery = dbQuery($sTakenQuery);
while ($oTakenRow = dbFetchObject($rTakenQuery)) {
	$sOfferName = '';
	$sGetOfferNameQuery = "SELECT name, offerCompanies.repDesignated FROM offers left join offerCompanies on offerCompanies.id = offers.companyId  WHERE offerCode='$oTakenRow->offerCode'";
	$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
	if (dbNumRows($rGetOfferNameResult)>0) {
		$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
		$sOfferName = $sOfferNameRow->name;

		$sUserQuery = "SELECT concat(nbUsers.firstName, ' ', nbUsers.lastName) as name FROM nbUsers where id in (".$sOfferNameRow->repDesignated.")";
                $res = dbQuery($sUserQuery);
                $oUser = dbFetchObject($res);
                $sOfferExec = $oUser->name;
	}
				
	$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName,acctExec)
	VALUES (\"$oTakenRow->offerCode\", \"$oTakenRow->takenCount\", \"0\", \"0\",\"$sOfferName\",\"$sOfferExec\")";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();
}


$sXOutQuery = "SELECT offerCode, count(offerCode) as xOutCount 
	FROM xOutDataHistory
	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	GROUP BY offerCode";
$rXOutResult = dbQuery($sXOutQuery);
echo dbError();
while ($xOutRow = dbFetchObject($rXOutResult)) {
	$sOfferName = '';
	$sGetOfferNameQuery = "SELECT name,offerCompanies.repDesignated  FROM offers left join offerCompanies on offerCompanies.id = offers.companyId  WHERE offerCode='$xOutRow->offerCode'";
	$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
	if (dbNumRows($rGetOfferNameResult)>0) {
		$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
		$sOfferName = $sOfferNameRow->name;

		$sUserQuery = "SELECT concat(nbUsers.firstName, ' ', nbUsers.lastName) as name FROM nbUsers where id in (".$sOfferNameRow->repDesignated.")";
                $res = dbQuery($sUserQuery);
                $oUser = dbFetchObject($res);
                $sOfferExec = $oUser->name;
	}

	$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName,acctExec)
		VALUES (\"$xOutRow->offerCode\", \"0\", \"0\", \"$xOutRow->xOutCount\",\"$sOfferName\",\"$sOfferExec\")";
	$rInsertResult = dbQuery($sInsertQuery);
	echo dbError();
}
				

$sSelectQuery = "SELECT distinct offerCode FROM nibbles_temp.tempAbandedReport";
$rSelectResult = dbQuery($sSelectQuery);
while ($sTempRow = dbFetchObject($rSelectResult)) {
	if (strlen($sTempRow->offerCode)<=1) {
		$sDeleteQuery = "DELETE FROM nibbles_temp.tempAbandedReport WHERE offerCode='$sTempRow->offerCode'";
		$rDeleteResult = dbQuery($sDeleteQuery);
	}
}

$sGetDataQuery = "SELECT offerCode, offerName, acctExec, sum(taken) as takenCount, sum(abanded) as abandedCount, 
		sum(xOutCount) as xOutCount	FROM nibbles_temp.tempAbandedReport GROUP BY offerCode";
$rGetDataResult = dbQuery($sGetDataQuery);
echo dbError();
$i = 0;
while ($sData = dbFetchObject($rGetDataResult)) {
	$iGrossCount = $sData->xOutCount;
	$iTempXoutCount = $sData->xOutCount - $sData->takenCount - $sData->abandedCount;
	$aReportArray['offerCode'][$i] = $sData->offerCode;
	$aReportArray['offerName'][$i] = $sData->offerName;
	$aReportArray['acctExec'][$i] = ($sData->acctExec ? $sData->acctExec : '&nbsp;');
	//echo ($sData->acctExec ? $sData->acctExec : '&nbsp;');
	$aReportArray['grossCount'][$i] = $iGrossCount;
	$aReportArray['abandedCount'][$i] = $sData->abandedCount;
			
	if ($iGrossCount < 1) { $iGrossCount=0.001; }
	if ($iTempXoutCount < 1) { $iTempXoutCount=0; }
			
	$aReportArray['abandedPercent'][$i] = number_format((($sData->abandedCount/$iGrossCount)*100),1);
	$aReportArray['xOutCount'][$i] = $iTempXoutCount;
	$aReportArray['xOutPercent'][$i] = number_format((($iTempXoutCount/$iGrossCount)*100),1);
	$aReportArray['netCount'][$i] = $sData->takenCount;
		
	$iGoodPercentTemp = 100 - $aReportArray['abandedPercent'][$i] - $aReportArray['xOutPercent'][$i];
	if ($iGoodPercentTemp < 0) { $iGoodPercentTemp = 0; }
	$aReportArray['goodPercent'][$i] = $iGoodPercentTemp;
			
	$i++;
}

$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempAbandedReport";
$rDeleteResult = mysql_query($sDeleteQuery);

$iCount = 0;
$sPageLoop = 0;
$sPageGrossTotal = 0;
$sAbandedTotal = 0;
$sXOutTotal = 0;
$sNetTotal = 0;
		
$sReportContent = "<html><head>
<style =\"text/css\">
TD.small { 
	FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 9px; COLOR: #000000;
	}
TD.big { 
	FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 10px; COLOR: #000000;
	}
TD.bigbig { 
	FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 12px; COLOR: #000000;
	}
TD.bigHeader {
	FONT-WEIGHT: bold; FONT-SIZE: 12pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
		
TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 8pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
}
</style>
</head><body><table cellpadding=0 cellspacing=0 bgcolor=#FFFFFF width=95% align=center border=1 bordercolor=#000000>
<tr><td>
<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
<tr><td>
<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
<tr><td class=bigHeader align=center><br>Abandon Offers Report<br>From $sDateFrom to $sDateTo<br><br></td></tr>
<tr><td class=header>Run Date / Time: $sRunDateAndTime</td></tr>
</table></td></tr></table></td></tr>
<tr><td>
<table width=100% align=center border=1 bgcolor=#FFFFFF cellpaddiing=3 cellspacing=0 bordercolorlight=#000000>
	<tr>
	<td class=small>Offer<br>Code</td>
	<td class=small>Offer<br>Name</td>
	<td class=small>Acct.<br>Exec.</td>
	<td class=small width=10%>Gross<br>Count</td><td class=small width=10%>Don't Want This<br>Offer Checked<br>Count</td>
	<td class=small align=right>Abanded<br>Percent</td><td class=small align=right>Didn't Complete<br>X Out Count</td>
	<td class=small align=right>X Out Percent</td><td class=small align=right>Net<br>Count</td>
	<td class=small align=right>% Good</td></tr>";

				
for( $iLoop=0; $iLoop<count($aReportArray['offerCode']); $iLoop++ ) {
	$sReportContent .= "<tr>
			<td class=big>".$aReportArray['offerCode'][$iLoop]."</td>
			<td class=big>".$aReportArray['offerName'][$iLoop]."</td>
			<td class=big>".$aReportArray['acctExec'][$iLoop]."</td>
			<td class=big>".$aReportArray['grossCount'][$iLoop]."</td>
			<td class=big>".$aReportArray['abandedCount'][$iLoop]."</td>
			<td class=big>".$aReportArray['abandedPercent'][$iLoop]."</td>
			<td class=big>".$aReportArray['xOutCount'][$iLoop]."</td>
			<td class=big>".$aReportArray['xOutPercent'][$iLoop]."</td>
			<td class=big>".$aReportArray['netCount'][$iLoop]."</td>
			<td class=big>".$aReportArray['goodPercent'][$iLoop]."</td></tr>";					

	$sPageGrossTotal += $aReportArray['grossCount'][$iLoop];
	$sAbandedTotal += $aReportArray['abandedCount'][$iLoop];
	$sXOutTotal += $aReportArray['xOutCount'][$iLoop];
	$sNetTotal += $aReportArray['netCount'][$iLoop];
	$iCount++;
}


$sPageAbandedPercent = number_format((($sAbandedTotal / $sPageGrossTotal)*100),1);
$sPageXOutPercent = number_format((($sXOutTotal / $sPageGrossTotal)*100),1);


$sPageGoodPercent = (100 - $sPageAbandedPercent - $sPageXOutPercent);
$sReportContent .= "<tr><td colspan=8></td></tr>
	<tr><td class=bigbig><b>Total: </b></td>
	<td class=bigbig><b>&nbsp;</b></td>
	<td class=bigbig><b>&nbsp;</b></td>
	<td class=bigbig><b>$sPageGrossTotal</b></td>
	<td class=bigbig><b>$sAbandedTotal</b></td>
	<td class=bigbig><b>$sPageAbandedPercent</b></td><td class=bigbig><b>$sXOutTotal</b></td>
	<td class=bigbig><b>$sPageXOutPercent</b></td><td class=bigbig><b>$sNetTotal</b></td>
	<td class=bigbig><b>$sPageGoodPercent</b></td></tr>";


$sReportContent .= "<br><br><table><tr><td  class=big colspan=9><br><b>Notes - </b><br>
	Today's data is not included on this report.<br>
	Total:  This is the total for entire report.<br>
	OfferCode: This is offerCode.  Clicking on actual offerCode will display all email addresses associated with this offerCode and date.  
		Click on email address to get detailed information from userDataHistory and otDataHistory tables.<br>
	Gross Count: Number of times the offer was checked on the first page.<br>
	Abanded Count: Number of offers abanded (I Don't Want This Offer Is Checked) for this offerCode within this date range.<br>
	Abanded Percent: The ratio of Abanded Count versus Gross Count.<br>
	X Out Count: Number of leads selected on the first page, but the page was closed before the page 2 information submitted. 
				Result of Gross Count minus Abanded Count minus Net Count.<br>
	X Out Percent: The ratio of X Out Count versus Gross Count.<br>
	Net Count:  Number of leads collected.  Count of leads collected on otDataHistory table.<br>
	Good Percent: 100 Percent minus Abanded Percent minus X Out Percent.
	</td></tr></table>";


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
$sTestHeaders = $sHeaders;

$sHeaders .= "cc: ";
$sEmailQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'abanded offers report'";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}

if (!($sEmailTo)) {
	$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
} 
	
$sCcTo = substr($sRecipients,strlen($sEmailTo));
$sHeaders .= ", $sCcTo";
$sHeaders .= "\r\n";
$sSubject = "Abanded Offers Report - $sRunDateAndTime";

mail($sEmailTo, $sSubject, $sReportContent, $sHeaders);


?>



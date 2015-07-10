<?php

// script to recalculate production list and send email of containing production list

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "prodListRecalculate.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

include("$sGblLibsPath/dateFunctions.php");


$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sToday = date('Y')."-".date('m')."-".date('d');
$sTomorrow = DateAdd("d", 1, date('Y')."-".date('m')."-".date('d'));

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";


//passthru("php $sGblWebRoot/admin/prodListMgmnt/recalculate.php", $sOutput);

/*****************   Start Recalculating   ***************/
$sToday = date('Y')."-".date('m')."-".date('d');


// delete old production list assumption dates 

$sDeleteQuery = "DELETE FROM productionListAssumptions
				 WHERE  workDate < CURRENT_DATE";
$rDeleteResult = dbQuery($sDeleteQuery);
echo dbError();
			 

$sWorkingHoursQuery = "SELECT *
					   FROM   vars
					   WHERE  system = 'productionList'";
$rWorkingHoursResult = dbQuery($sWorkingHoursQuery);
while ($oWorkingHoursRow = dbFetchObject($rWorkingHoursResult)) {

	$sVarName = $oWorkingHoursRow->varName;
	switch ($sVarName) {
		case "newCoBrandWorkHours":
		$iCoBrandWorkHours = $oWorkingHoursRow->varValue;
		break;
		case "newOfferWorkHours":
		$iNewOffersWorkHours = $oWorkingHoursRow->varValue;
		break;
		case "changesToExistingOfferWorkHours";
		$iChangesToExistingOffersWorkHours = $oWorkingHoursRow->varValue;
		break;
		case "changesToExistingCoBrandWorkHours";
		$iChangesToExistingCoBrandWorkHours = $oWorkingHoursRow->varValue;
		break;
		case "otherWorkHours";
		$iOtherWorkHours = $oWorkingHoursRow->varValue;
		break;
		case "dailyWorkHours":
		$iDefaultDailyWorkHours = $oWorkingHoursRow->varValue;
		break;
	}
}

// set today's working hours if not set
if (!(isset($iTodaysHours))) {
	$iTodaysHours = $iDefaultDailyWorkHours;
}


$sSelectQuery = "SELECT *
				 FROM   productionList
				 WHERE  requestType IN ('New Co-Brand', 'New Offer', 'Changes To Existing Co-Brand', 'Changes To Existing Offer', 'Other')
				 AND    status = 'scheduled'
				 ORDER BY priority";
$rSelectResult = dbQuery($sSelectQuery);
echo dbError();
$iPrecedingDays = 0;
$iPrecedingHours = 0;
while ($oSelectRow = dbFetchObject($rSelectResult)) {
	
	$iTempId = $oSelectRow->id;
	
	$sTempRequestType = $oSelectRow->requestType;
	
	if ($sPrevEstimateDate == '') {
		$sPrevEstimateDate = date('Y')."-".date('m')."-".date('d');
	}
		
	$iDailyWorkHours = $iDefaultDailyWorkHours;
	
	// set today's remaining hours to work in calculation
	if ($sPrevEstimateDate == $sToday) {
		$iDailyWorkHours = $iTodaysHours;
	}		
	
	// get current day's work hours	
	$sWorkHoursQuery = "SELECT *
						FROM   productionListAssumptions
						WHERE  workDate = '$sPrevEstimateDate'";
	$rWorkHoursResult = dbQuery($sWorkHoursQuery);
	while ($oWorkHoursRow = dbFetchObject($rWorkHoursResult)) {
		$iDailyWorkHours = $oWorkHoursRow->workHours;	
	}	
	
	
	switch ($sTempRequestType) {
		
		case "New Co-Brand":
		$iPrecedingHours += $iCoBrandWorkHours;
		break;
		case "New Offer":
		$iPrecedingHours += $iNewOffersWorkHours;
		break;
		case "Changes To Existing Co-Brand";
		$iPrecedingHours += $iChangesToExistingCoBrandWorkHours;
		break;
		case "Changes To Existing Offer";
		$iPrecedingHours += $iChangesToExistingOffersWorkHours;
		break;
		case "Other";
		$iPrecedingHours += $iOtherWorkHours;
		break;
	}
	
	if ($iPrecedingHours > $iDailyWorkHours) {
		$iPrecedingDays++;
		$iPrecedingHours -= $iDailyWorkHours;
	}
	
	// get next date
	$sEstimateDate = '';
	$sEstimateDay = '';
	$sDateQuery = "SELECT date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY) estimateDate,
									  date_format(date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY),'%a') estimateDay";
	$rDateResult= dbQuery($sDateQuery);
	while ($oDateRow = dbFetchObject($rDateResult)) {
		$sEstimateDate = $oDateRow->estimateDate;
		$sEstimateDay = strtolower($oDateRow->estimateDay);
	}
	
	/*********** check current day's work hours and recalculate if the date has not default work hours  ****/
	$sWorkHoursQuery = "SELECT *
						FROM   productionListAssumptions
						WHERE  workDate = '$sEstimateDate'";
	$rWorkHoursResult = dbQuery($sWorkHoursQuery);
	while ($oWorkHoursRow = dbFetchObject($rWorkHoursResult)) {
		$iDailyWorkHours = $oWorkHoursRow->workHours;	
	}	
	
	if ($iPrecedingHours > $iDailyWorkHours) {
		$iPrecedingDays++;
		$iPrecedingHours -= $iDailyWorkHours;
	}
	
	$sDateQuery = "SELECT date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY) estimateDate,
									  date_format(date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY),'%a') estimateDay";
	$rDateResult= dbQuery($sDateQuery);
//	echo $sDateQuery.dbError();
	while ($oDateRow = dbFetchObject($rDateResult)) {
		$sEstimateDate = $oDateRow->estimateDate;
		
		$sEstimateDay = strtolower($oDateRow->estimateDay);
	}
	/*********************/
	
	
	//echo $iPrecedingDays;
	
	if ($sEstimateDay =='sat'  || $sEstimateDay == 'sun') {
		if ($sEstimateDay =='sat' ) {
			$sDateQuery2 = "SELECT date_add('".$sEstimateDate."', INTERVAL 2 DAY) as estimateDate";
			$iPrecedingDays += 2;
		} else if ($sEstimateDay =='sun' ) {
			$sDateQuery2 = "SELECT date_add('".$sEstimateDate."', INTERVAL 1 DAY) as estimateDate";
			$iPrecedingDays = $iPrecedingDays + 1;
		}
		$rDateResult2= dbQuery($sDateQuery2);
		echo dbError();
		while ($oDateRow2 = dbFetchObject($rDateResult2)) {
			$sEstimateDate = $oDateRow2->estimateDate;
			
		}
	}
	
	
	$sCurrentEstimateDate = '';
	$sTempQuery2 = "SELECT *
					FROM   productionList
					WHERE  id = '$iTempId'";
	$rTempResult2 = dbQuery($sTempQuery2);
	echo dbError();
	while ($oTempRow2 = dbFetchObject($rTempResult2)) {
		$sCurrentEstimateDate = $oTempRow2->estimateDate;
	}
	
	//echo "<BR>".$sTempOfferType ." - ".$iPrecedingHours." - ".$iPrecedingDays. " - ".$sEstimateDate." - ".$sCurrentEstimateDate;
	
	if ($sCurrentEstimateDate != $sEstimateDate) {				
		
		// set oldEstimateDate value first before setting estimateDate in following query
		$sTempUpdateQuery = "UPDATE productionList
							 SET    oldEstimateDate = concat(oldEstimateDate, estimateDate, ',<BR>'),
		 						    estimateDate = '$sEstimateDate' ";							 
		
		$sPriorityChanged = '';
		// if offer came in or out of today or tomorrow, mark it as priority changed
		if (($sCurrentEstimateDate == $sToday || $sCurrentEstimateDate == $sTomorrow)
			&& ($sEstimateDate != $sToday && $sEstimateDate != $sTomorrow)) {
				$sPriorityChanged = "Down";
		} else if (($sEstimateDate == $sToday || $sEstimateDate == $sTomorrow)
			&& ($sCurrentEstimateDate != $sToday && $sCurrentEstimateDate != $sTomorrow)) {
				$sPriorityChanged = "Up";
		}
			
		if ($sPriorityChanged != '') {
			$sTempUpdateQuery .= " , priorityChanged = '$sPriorityChanged' ";	
		}
		
		$sTempUpdateQuery .= " WHERE  id = '$iTempId'";
							 
		$rTempUpdateResult = dbQuery($sTempUpdateQuery);
		echo dbError();
	}
	
	$sPrevEstimateDate = $sEstimateDate;
	//echo "<BR>$sTempOfferType $iPrecedingDays $iPrecedingHours ".$sTempUpdateQuery. dbError();
	
}

/***********    End of Recalculate   *********/

$sHeadingRow = "<tr><td><font face=verdana size=1><b>Priority</b></font></td>
						<td><font face=verdana size=1><b>Request</b></font></td>
						<td><font face=verdana size=1><b>Date Entered</b></font></td>
						<td><font face=verdana size=1><b>Owner</b></font></td>
						<td><font face=verdana size=1><b>Request Type</b></font></td>
						<td><font face=verdana size=1><b>Offer Page</b></font></td>
						<td><font face=verdana size=1><b>Comments</b></font></td>
						<td><font face=verdana size=1><b>Estimate Date</b></font></td>
						<td><font face=verdana size=1><b>Old Estimate Date</b></font></td></tr>";

$sReportContent = "<html><body><table width=80% align=center border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>";
$sReportContent .= $sHeadingRow;



// Query to get the list
$sSelectQuery = "SELECT *
					FROM   productionList
					WHERE  (status = 'scheduled' || status = 'unknownSchedule')
					ORDER BY priority";

$rResult = dbQuery($sSelectQuery);
echo dbError();

if ($rResult) {
	
	while ($oRow = dbFetchObject($rResult)) {
		
		$iPriority = $oRow->priority;
		$sMarkRequest = '&nbsp;';
		if ($oRow->priorityChanged == 'Up' ||
		($oRow->priorityChanged == 'Down' && ($oRow->estimateDate == $sToday || $oRow->estimateDate == $sTomorrow) )) {
			$sMarkRequest = "*";
			
		}
		$sReportContent .= "<tr bgcolor=white><td><font face=verdana size=1>&nbsp;$sMarkRequest $oRow->priority</font></td>
					<td><font face=verdana size=1>&nbsp;$oRow->request</font></td>
					<td><font face=verdana size=1>&nbsp;$oRow->dateEntered</font></td>
					<td><font face=verdana size=1>&nbsp;$oRow->owner</font></td>					
					<td><font face=verdana size=1>&nbsp;$oRow->requestType</font></td>
					<td><font face=verdana size=1>&nbsp;$oRow->offerPage</font></td>
					<td><font face=verdana size=1>&nbsp;$oRow->comments</font></td>";
		if ($oRow->status == 'unknownSchedule') {
			$sTempEstimateDate = "?";
		} else {
			$sTempEstimateDate = $oRow->estimateDate;
		}
		$sReportContent .= "
					<td align=center nowrap><font face=verdana size=1>&nbsp;$sTempEstimateDate</font></td>
					<td nowrap><font face=verdana size=1>&nbsp;$oRow->oldEstimateDate</font></td>					
					</tr>";
	}
	
}


$sReportContent .= "</table>";


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";
//$sHeaders .= "cc: ";

$sEmailQuery = "SELECT *
			   FROM   emailRecipients
			   WHERE  purpose = 'production sheet update'";
$rEmailResult = dbQuery($sEmailQuery);
echo dbError();
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sEmailTo = $oEmailRow->emailRecipients;
}

$sSubject = "Production List - $sRunDateAndTime";
mail($sEmailTo, $sSubject, $sReportContent, $sHeaders);



cssLogFinish( $iScriptId );

?>

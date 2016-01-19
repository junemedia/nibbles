<?php

/*

Caution: script also called from /crons/prodListRecalculate.php 

*/
include("../../includes/paths.php");

$sToday = date('Y')."-".date('m')."-".date('d');


// start of track users' activity in nibbles 
$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Recalculate\")"; 
$rLogResult = dbQuery($sLogAddQuery); 
echo  dbError(); 
// end of track users' activity in nibbles		



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
				 WHERE  requestType IN ('New Co-Brand', 'New Offer', 'Changes To Existing Co-Brand', 'New Campaign', 'Changes To Existing Offer', 'Other')
				 AND    status = 'scheduled'
				 ORDER BY priority";
$rSelectResult = dbQuery($sSelectQuery);
echo dbError();
$iPrecedingDays = 0;
$iPrecedingHours = 0;
while ($oSelectRow = dbFetchObject($rSelectResult)) {
	
	$sGetHoursQuery = "select * from productionList WHERE id='$oSelectRow->id'";
	$rGetHoursResult = dbQuery($sGetHoursQuery);
	while ($oHoursRow = dbFetchObject($rGetHoursResult)) {
		$iPrecedingHours += $oHoursRow->hours;
	}
	
	
	
	
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
	
}

echo "<script language=JavaScript>
							history.go(-1);
							</script>";		
?>
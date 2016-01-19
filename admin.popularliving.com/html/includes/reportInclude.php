<?php

/*

1. If the loads on the servers are above a certain level reporting will be unavailable and you will receive a message to this effect if you try to run a report.

2. If we are processing leads the reporting system will be unavailable.

3. You will be unable to run multiple reports at once. I.E., if you click to start one report, you will be unable to click away and start a second report until the first one has completed.

The partners reporting has been modified as follows:

1. If the load on the servers is above a certain level, they will get a message saying reporting is unavailable and to try again shortly.

2. They are unable to start multiple reports running at once via multiple clicks on the submit button.

*/

// allow report if load is below maximum allowed load average
$fGblMaxLoadForReports = "8.0";
$bGblReportsDisabled = false;

// if reporting is from slave server, then there should
// be no lock out.  so increase this load to 100 so it won't
// lock the reporting due to load.
// per jr request on 8/22/06 5:15pm
// change it so there is no lock out if reporting server is slave 
// and the lock out is in effect if reporting server is cory
if ($reportingHost == "64.132.70.15") {
	$fGblMaxLoadForReports = "99.0";
}


$sAllowReport = 'Y';

if( $_SESSION['reportsRunning'][$_SERVER['PHP_AUTH_USER']] == "1" ) {
	//echo "Val=1, Set Message Already Running, allowReport=NO.<br>";
	$sMessage .= "You must wait for the previous report to complete before running another.";
	$sAllowReport = "N";
} else {
	//echo "Val != 1, Set Val=1, no message.<br>";
	$_SESSION['reportsRunning'][$_SERVER['PHP_AUTH_USER']] = "1";
}

// Check global flag, "Reports Disabled"
if( $bGblReportsDisabled == true ) {
	$sAllowReport = 'N';
	$sMessage .= "\nREPORTING TEMPORARILY DISABLED by IT Department.";
}

// get load average
if(file_exists("/proc/loadavg")) {
	$aLoadAvgTemp = file("/proc/loadavg");
	$aLoadAvg = explode(" ", $aLoadAvgTemp[0]);
	$fOneMinLoadAvg = $aLoadAvg[0];
	$fFiveMinLoadAvg = $aLoadAvg[1];
	$fFifteenMinLoadAvg = $aLoadavg[2];
}

//check if leads are being processed
$sCheckQuery = "SELECT *
				FROM   vars
				WHERE  varName = 'leadProcessingInProgress'";
$rCheckResult = dbQuery($sCheckQuery);
while ($oCheckRow = dbFetchObject($rCheckResult)) {
	$sLeadProcessingInProgress = $oCheckRow->varValue;
}

// report conditions
if ($sLeadProcessingInProgress == 'Y' || $fOneMinLoadAvg > $fGblMaxLoadForReports) {
	$sAllowReport = 'N';
}


$sReportJavaScript = "
<script language=JavaScript>

function funcReportClicked(btnClicked) {
	if (btnClicked == 'history') {
		document.form1.sViewReport.value = \"History Report\";
	} else if (btnClicked == 'today') {
		document.form1.sViewReport.value = \"Today's Report\";
	} else if (btnClicked == 'report') {
		document.form1.sViewReport.value = \"View Report\";
	} else if (btnClicked == 'export') {
		document.form1.sViewReport.value = \"Export Report\";
	} else {
		document.form1.sViewReport.value = btnClicked;
	}
	
	var repClicked = document.form1.reportClicked.value;
	if (repClicked == '') {
		document.form1.reportClicked.value = 'Y';
		
		document.form1.submit();
	} else {
		alert('Report is running... Please Wait');
	}
}

</script>";
$sAllowReport = 'Y';
?>

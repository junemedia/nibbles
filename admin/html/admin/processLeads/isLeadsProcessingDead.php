<?php

include("../../includes/paths.php");

/********************************
	Syntax for this script shoudl be as follows:
	
	[root@whereever root]# php /home/scripts/isLeadsProcessingDead.php scriptName
	
	where 'scriptName' is the name that gets reported to the cronScriptStatus
	table. Automated leads processing works on a series of php scripts called
	by cron jobs on two different machines: web1, and mail.  

	 00:30 am -- (mail) overnightDataMove.php - moves data out of otData, 
			into otDataHistory, and from userData, into userDataHistory
	 02:31 am -- (mail) dedupScript.php - looks for dupes in otDataHistory
	~02:40 am -- (mail) customProcessing.php (included from dedupScript.php)
			- for special processing needs.
	~02:50 am -- (mail) otDataUserDataWorking.php (included from dedup) - 
			populates smaller tables with the last 40 days of leads and 
			users.
	 06:00 am -- (web1) processLeads.php - processes leads in the working
	 		tables, moves batch leads into files.
	~06:20 am -- (web1) sendLeads.php (included from processLeads.php) - 
			sends batch flies, count reports

	This script should be set up on cron jobs for each of the stages of the 
	processing task. Some of these use both cronScriptStatus entries and 
	vars entries, some use only cron script status. Where appropriate, the
	script checks both.

*********************************/

$vars = array('overnightDataMove.php' => 'overnightDataMove',
				'dedupScript.php' => 'dedupScriptRunning',
				'otDataUserDataWorking.php' => 'otDataUserDataWorking',
				'customProcessing.php' => '',
				'processLeads.php' => 'processLeadsRunning',
				'sendLeads.php' => 'sendLeadsRunning');

//where the mail gets sent
$liveAddresses = "it@amperemedia.com,7739344565@tmomail.com,6306700018@messaging.sprintpcs.com";
$testAddresses = "bbevis@amperemedia.com";
$mailToMe = $liveAddresses;
//$mailToMe = $testAddresses;

//where all of the output goes.
$out = '';

//get the argument
$scriptNameToLookFor = $argv[1];
//print_r($argv);
echo $scriptNameToLookFor."\n";

//make sure that the arg is a good one
if(!in_array($scriptNameToLookFor, array_keys($vars))){
	mail($mailToMe, 'bad script argument.', $scriptNameToLookFor." isn't a proper script name.");
	exit();
}

//check vars (if we've got them)
if($vars[$scriptNameToLookFor]){
	$varsSQL = "SELECT * FROM vars WHERE varName = '".$vars[$scriptNameToLookFor]."'";
	$rVarsResult = dbQuery($varsSQL);
	$oVars = dbFetchObject($rVarsResult);
	if($oVars->varValue != '0'){
		$out .= "\n\n-There was a problem with the var for $scriptNameToLookFor (".$vars[$scriptNameToLookFor].")\n\tVar Value for ".$vars[$scriptNameToLookFor]." was ".$oVars->varValue;
	}
}

//check cronScriptStatus 
$crons = array();
$cronScriptSQL = "SELECT * FROM cronScriptStatus WHERE scriptName like '$scriptNameToLookFor%' AND startDateTime like '".date('Y-m-d')."%'";
//echo "$cronScriptSQL\n\n";
$rCronScript = dbQuery($cronScriptSQL);
while($oCronScript = dbFetchObject($rCronScript)){
	array_push($crons,$oCronScript);
}

if(count($crons) > 1){
	/*
	print_r($crons);
	echo "\n\n";
	foreach($crons as $c){
		print_r($c);
	}
	*/
	$out .= "\n\n-There was more than one run of $scriptNameToLookFor.";
}

foreach($crons as $c){
	if($c->endDateTime == '0000-00-00 00:00:00'){
		$out .= "\n\n-$scriptNameToLookFor is either still running, or died without updating its cronScriptStatus.\n\t(cronScriptStatus id $c->id, $c->startDateTime, $c->endDateTime).";
	}
}

if($out){
	mail($mailToMe, "$scriptNameToLookFor may have problems (overnight)",$out);
}

?>
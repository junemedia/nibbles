<?php

/*
This script will grab all leads from otDataHistory table and insert them into otDataHistoryWorking table.
The reason why we do that because it make leads processing run much faster since we are not using big table like otDataHistory.

The script will only grab leads that were not processed or sent and insert them into working table so it will be sent by processing scripts.

For each unique email we have in working table, we grab user info from userDataHistory table and insert them in userDataHistoryWorking table.

We don't want to use otDataHistory beacuse it has more than 1 million rows.
We also don't want to use userDataHistory because it has more than 7 million rows.

So that's why we only grab data we need from history table and insert them into working table, so when we run processing leads script, it uses working 
tables instead of big otDataHistory and userDataHistory table.
*/


ini_set('max_execution_time', 5000);
include_once( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "otDataUserDataWorking.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$dbase = 'nibbles';
mysql_select_db ($dbase);

// system = 'cron'
// varName = 'otDataUserDataWorking'

$sVarUpdateQuery = "UPDATE vars
                    SET    varValue = varValue+1
                    WHERE  id='35'";
$rVarUpdateResult = dbQuery($sVarUpdateQuery);
echo dbError();


// Default max age of leads if below query fails
$sMaxAgeOfLeads = 45;

$sMailToCell = "6306700018@messaging.sprintpcs.com";
$sMailMsgTo = "it@amperemedia.com";



$sGetMaxAgeOfLeads = "SELECT maxAgeOfLeads FROM offerLeadSpec order by maxAgeOfLeads desc limit 1";
//$rGetMaxAgeOfLeads = dbQuery($sGetMaxAgeOfLeads);
$rGetMaxAgeOfLeads = mysql_query($sGetMaxAgeOfLeads);
echo dbError();
if ($rGetMaxAgeOfLeads) {
	$oGetMaxAgeOfLeadsRow = mysql_fetch_object($rGetMaxAgeOfLeads);
	$sMaxAgeOfLeads = $oGetMaxAgeOfLeadsRow->maxAgeOfLeads + 1;
}


//$sOldDate = DateAdd("d", -$sMaxAgeOfLeads, date('Y')."-".date('m')."-".date('d'));
//echo date('Y-m-d', strtotime("-40 day"));

$sOldDate = date('Y-m-d', strtotime("-$sMaxAgeOfLeads days"));
$sTooOldDate = date('Y-m-d', strtotime("-".($sMaxAgeOfLeads + 1)." days"));
$sYesterday = date('Y-m-d', strtotime("-1 days"));

//truncates
$rOtDataHistoryResult = mysql_query("TRUNCATE TABLE process_leads.otDataHistoryWorking");
$rOtDataHistoryResult = mysql_query("TRUNCATE TABLE process_leads.userDataHistoryWorking");

//echo "'$sOldDate 00:00:00' is old date\n'$sTooOldDate 00:00:00' is too old date\n'$sYesterday 00:00:00' is yesterday";

//INSERT INTO otDataHistoryWorking 
$sOtDataHistoryQuery = "INSERT IGNORE INTO process_leads.otDataHistoryWorking
					SELECT * FROM nibbles.otDataHistory
					WHERE (dateTimeAdded > '$sYesterday 00:00:00' AND processStatus !='R')
					OR (dateTimeAdded > '$sOldDate 00:00:00' AND (processStatus = '' OR processStatus IS NULL OR (processStatus = 'P' AND sendStatus is null)))";
//$rOtDataHistoryResult = dbQuery($sOtDataHistoryQuery);
$rOtDataHistoryResult = mysql_query($sOtDataHistoryQuery);
echo dbError();
if (!($rOtDataHistoryResult)) {
	$sMsg = "Query Failed: $sOtDataHistoryQuery"."\n\nError: ".dbError();
	
	mail($sMailMsgTo,__LINE__,$sMsg);
	mail($sMailToCell,__LINE__,"See email");
}





echo "\nDone Inserting Data Into otDataHistoryWorking: ".__LINE__;


echo "\nStart Inserting Into userDataHistoryWorking ";

//$iCountChunks = count($aEmailChunks);
//for($i=0;$i<$iCountChunks; $i++){
	//INSERT IGNORE INTO userDataHistoryWorking
	$sInsertQuery = "INSERT IGNORE INTO process_leads.userDataHistoryWorking
					SELECT nibbles.userDataHistory.* FROM nibbles.userDataHistory, process_leads.otDataHistoryWorking
					WHERE nibbles.userDataHistory.email = process_leads.otDataHistoryWorking.email";
	//$rUserDataInsertResult = dbQuery($sInsertQuery);
	$rUserDataInsertResult = mysql_query($sInsertQuery);
	if (!($rUserDataInsertResult)) {
		$sMsg = "Query Failed: $sInsertQuery"."\n\nError: ".dbError();
		
		mail($sMailMsgTo,__LINE__,$sMsg);
	}
//}


echo "\nDone Inserting Into userDataHistoryWorking: ".__LINE__;


// system = 'cron'
// varName = 'otDataUserDataWorking'

$sVarUpdateQuery = "UPDATE vars
                    SET    varValue = varValue-1
                    WHERE  id='35'";
$rVarUpdateResult = dbQuery($sVarUpdateQuery);
echo dbError();

cssLogFinish( $iScriptId );
?>

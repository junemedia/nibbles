<?php 

include('/home/sites/admin.popularliving.com/html/includes/paths.php');
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);


//get offer codes for each category
$aCategoryIds = array();
$sGetCategoryIdsSQL = "SELECT id, title FROM categories";
$rGetCategoryIds = dbQuery($sGetCategoryIdsSQL);
while($oCategoryId = dbFetchObject($rGetCategoryIds)){
	if($oCategoryId->id != '11'){
		$aCategoryIds[$oCategoryId->id] = array();
		$aCategoryNames[$oCategoryId->id] = $oCategoryId->title;
						
		$sGetOfferCodesSQL = "SELECT offers.offerCode as offerCode FROM offers, categoryMap WHERE categoryMap.categoryId = '$oCategoryId->id' AND categoryMap.offerCode = offers.offerCode AND offers.offerType = 'CR'";
		$rGetOfferCodes = dbQuery($sGetOfferCodesSQL);
		//echo "$sGetOfferCodesSQL<br>";
		while($oOfferCode = dbFetchObject($rGetOfferCodes)){
			array_push($aCategoryIds[$oCategoryId->id],$oOfferCode->offerCode);
		}
	}
}


//update nibbles_reporting.leadsCategoriesCompleted
$aCategoryCompletedResults = array();

//find yesterday's numbers
//find 31-days-ago's numbers
//find 91-days-ago's numbers

$keys = array_keys($aCategoryIds);
$count = count($keys);
for($i=0; $i<$count; $i++){
	$sLCCyesterdaySQL = "SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')";
	//echo "$sLCC30SQL\n\n";
	
	$sLCC31SQL = "SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 23:59:59')";
	//echo "$sLCC90SQL\n\n";
	
	
	$sLCC91SQL = "SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 23:59:59')";
	//echo "$sLCC90SQL\n\n";
		
	$rCatyesterdayRes = dbQuery($sLCCyesterdaySQL);
	$oCatyesterday = dbFetchObject($rCatyesterdayRes);
	echo (dbError() ? __line__.dbError() : '');
	
	$rLCC31Res = dbQuery($sLCC31SQL);
	$oLCC31 = dbFetchObject($rLCC31Res);
	echo (dbError() ? __line__.dbError() : '');
	
	$rLCC91Res = dbQuery($sLCC91SQL);
	$oLCC91 = dbFetchObject($rLCC91Res);
	echo (dbError() ? __line__.dbError() : '');
	//'".$aCategoryNames[$keys[$i]]."', 
	$aCategoryCompletedResults[$i] = "UPDATE leadsCategoriesCompleted SET 
										30Days = (30Days + ".($oCatyesterday->count ? $oCatyesterday->count : '0').") - ".($oLCC31->count?$oLCC31->count:'0').", 
										90Days = (90Days + ".($oCatyesterday->count ? $oCatyesterday->count : '0').") - ".($oLCC91->count?$oLCC91->count:'0').", 
										Total = Total + ".($oCatyesterday->count ? $oCatyesterday->count : '0')." 
										WHERE category = '".$aCategoryNames[$keys[$i]]."'";
	
}

//subtract 31-days-ago from 30 days
//subtract 91-days-ago from 90 days
//add yesterday to all 3

foreach($aCategoryCompletedResults as $query){
	//echo "$query\n\n";
	$rCompletedLeads = dbQuery($query);
	//echo (dbError() ? __line__.dbError() : '');
}

//populate nibbles_reporting.leadsCategoriesAbandoned
$aCategoryAbandonedResults = array();



//find yesterday's numbers
//find 31-days-ago's numbers
//find 91-days-ago's numbers
$count = count(array_keys($aCategoryIds));
for($i=0; $i<$count; $i++){
	$sLACyesterdaySQL = "SELECT count(abandedOffersHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND abandedOffersHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')";
	//echo "$sLACyesterdaySQL\n\n";
	
	$sLAC31SQL = "SELECT count(abandedOffersHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND abandedOffersHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 23:59:59')";
	//echo "$sLAC31SQL\n\n";
	
	$sLAC91SQL = "SELECT count(abandedOffersHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('".join("','",$aCategoryIds[$keys[$i]])."')
	AND abandedOffersHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 23:59:59')";
	//echo "$sLAC91SQL\n\n";
		
	$rLACyesterdayRes = dbQuery($sLACyesterdaySQL);
	$oLACyesterday = dbFetchObject($rLACyesterdayRes);
	echo (dbError() ? __line__.dbError() : '');
	
	$rLAC31Res = dbQuery($sLAC31SQL);
	$oLAC31 = dbFetchObject($rLAC31Res);
	echo (dbError() ? __line__.dbError() : '');
	
	$rLAC91Res = dbQuery($sLAC91SQL);
	$oLAC91 = dbFetchObject($rLAC91Res);
	echo (dbError() ? __line__.dbError() : '');
	
	
	$aCategoryAbandonedResults[$i] = "UPDATE leadsCategoriesAbandoned SET 
										30Days = (30Days + ".($oLACyesterday->count ? $oLACyesterday->count : '0').") - ".($oLAC31->count?$oLAC31->count:'0').", 
										90Days = (90Days + ".($oLACyesterday->count ? $oLACyesterday->count : '0').") - ".($oLAC91->count?$oLAC91->count:'0').", 
										Total = Total + ".($oLACyesterday->count ? $oLACyesterday->count : '0')."
										WHERE category = '".$aCategoryNames[$keys[$i]]."'";
	//$aCategoryAbandonedResults[$i] = "('".$aCategoryNames[$keys[$i]]."', '$oCat30Res->count', '$oCat90Res->count', '$oCatTotalRes->count')";
	
}

//subtract 31-days-ago from 30 days
//subtract 91-days-ago from 90 days
//add yesterday to all 3

foreach($aCategoryAbandonedResults as $query){
	//echo "$query\n\n";
	$rAbandonedLeads = dbQuery($query);
	echo (dbError() ? __line__.dbError() : '');
}

//populate nibbles_reporting.leadSources
$aSourceCounts = array();


//find yesterday's numbers
//find 31-days-ago's numbers
//find 91-days-ago's numbers
$sLeadSourcesSQL = "SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN 
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
GROUP BY sourceCode";
$rLeadsRes = dbQuery($sLeadSourcesSQL);
while($oLeads = dbFetchObject($rLeadsRes)){
	if(!is_array($aSourceCounts[$oLeads->sourceCode])){
		$aSourceCounts[$oLeads->sourceCode] = array();
	}
	$aSourceCounts[$oLeads->sourceCode]['yesterday'] = $oLeads->count;
}

$sLeadSourcesSQL = "SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN 
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 23:59:59')
GROUP BY sourceCode";
$rLeadsRes = dbQuery($sLeadSourcesSQL);
while($oLeads = dbFetchObject($rLeadsRes)){
	if(!is_array($aSourceCounts[$oLeads->sourceCode])){
		$aSourceCounts[$oLeads->sourceCode] = array();
	}
	$aSourceCounts[$oLeads->sourceCode]['31'] = $oLeads->count;
}

$sLeadSourcesSQL = "SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN 
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 23:59:59')
GROUP BY sourceCode";
$rLeadsRes = dbQuery($sLeadSourcesSQL);
while($oLeads = dbFetchObject($rLeadsRes)){
	if(!is_array($aSourceCounts[$oLeads->sourceCode])) {
		$aSourceCounts[$oLeads->sourceCode] = array();
	}
	$aSourceCounts[$oLeads->sourceCode]['91'] = $oLeads->count;
}

//subtract 31-days-ago from 30 days
//subtract 91-days-ago from 90 days
//add yesterday to all 3

foreach($aSourceCounts as $src => $counts){
	$query = "SELECT count(*) as count FROM leadSources WHERE sourceCode = '$src'";
	$rLeadSources = dbQuery($query);
	$oCount = dbFetchObject($rLeadSources);
	if($oCount->count == 0){
		//then do an insert
		$query = "INSERT INTO leadSources (sourceCode, 30Days, 90Days, Total) values ('$src','".$counts['yesterday']."','".$counts['yesterday']."','".$counts['yesterday']."')";
	} else {
	
		$query = "UPDATE leadSources SET
				30Days = (30Days + ".($counts['yesterday'] ? $counts['yesterday'] : '0').") - ".($counts['31'] ? $counts['31'] : '0').",
				90Days = (90Days + ".($counts['yesterday'] ? $counts['yesterday'] : '0').") - ".($counts['91'] ? $counts['91'] : '0').",
				Total = Total + ".($counts['yesterday'] ? $counts['yesterday'] : '0')."
				WHERE sourceCode = '$src'";
	}
	//echo "$query\n\n";
	$rLeadSources = dbQuery($query);
	echo (dbError() ? __line__.dbError() : '');
}

//populate nibbles_reporting.emailDomains

//find yesterday's numbers
//find 31-days-ago's numbers
//find 91-days-ago's numbers
$aDomainCounts = array();


$sEmailDomainsSQL = "SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
GROUP BY domain";
$rDomainRes = dbQuery($sEmailDomainsSQL);
while($oDomain = dbFetchObject($rDomainRes)){
	if(!is_array($aDomainCounts[$oDomain->domain])){
		$aDomainCounts[$oDomain->domain] = array();
	}
	$aDomainCounts[$oDomain->domain]['yesterday'] = $oDomain->count;
}

$sEmailDomainsSQL = "SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 23:59:59')
GROUP BY domain";
$rDomainRes = dbQuery($sEmailDomainsSQL);
while($oDomain = dbFetchObject($rDomainRes)){
	if(!is_array($aDomainCounts[$oDomain->domain])){
		$aDomainCounts[$oDomain->domain] = array();
	}
	$aDomainCounts[$oDomain->domain]['31'] = $oDomain->count;
}

$sEmailDomainsSQL = "SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
WHERE otDataHistory.dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 00:00:00') AND
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 23:59:59')
GROUP BY domain";
$rDomainRes = dbQuery($sEmailDomainsSQL);
while($oDomain = dbFetchObject($rDomainRes)){
	if(!is_array($aDomainCounts[$oDomain->domain])){
		$aDomainCounts[$oDomain->domain] = array();
	}
	$aDomainCounts[$oDomain->domain]['91'] = $oDomain->count;
}


//subtract 31-days-ago from 30 days
//subtract 91-days-ago from 90 days
//add yesterday to all 3
$aEMailRows = array();
foreach($aDomainCounts as $domain => $counts){
	$query = "SELECT count(*) as count FROM emailDomains WHERE domain = '$domain'";
	$rLeadSources = dbQuery($query);
	$oCount = dbFetchObject($rLeadSources);
	if($oCount->count == 0){
		//then do an insert
		$query = "INSERT INTO emailDomains (domain, 30Days, 90Days, Total) values ('$domain','".$counts['yesterday']."','".$counts['yesterday']."','".$counts['yesterday']."')";
	} else {
		
		$query = "UPDATE emailDomains SET
				30Days = (30Days + ".($counts['yesterday'] ? $counts['yesterday'] : '0').") - ".($counts['31'] ? $counts['31'] : '0').",
				90Days = (90Days + ".($counts['yesterday'] ? $counts['yesterday'] : '0').") - ".($counts['91'] ? $counts['91'] : '0').",
				Total = Total + ".($counts['yesterday'] ? $counts['yesterday'] : '0')."
				WHERE domain = '$domain'";
	}
	
	//echo "$query\n\n";
	$rLeadSources = dbQuery($query);
	echo (dbError() ? __line__.dbError() : '');

}

//populate nibbles_reporting.puertoRicanUsers
$aPRCounts = array();

//find yesterday's numbers
//find 31-days-ago's numbers
//find 91-days-ago's numbers
$sPRUsersSQL = "SELECT count(*) as count FROM userDataHistory 
WHERE dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 00:00:00')AND
concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
AND userDataHistory.state = 'PR'";
$rPRRes = dbQuery($sPRUsersSQL);
$oPR = dbFetchObject($rPRRes);
$aPRCounts['yesterday'] = $oPR->count;

$sPRUsersSQL = "SELECT count(*) as count FROM userDataHistory 
WHERE dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 00:00:00')AND
concat(date_add(CURRENT_DATE, INTERVAL -31 DAY), ' 23:59:59')
AND userDataHistory.state = 'PR'";
$rPRRes = dbQuery($sPRUsersSQL);
$oPR = dbFetchObject($rPRRes);
$aPRCounts['31'] = $oPR->count;


$sPRUsersSQL = "SELECT count(*) as count FROM userDataHistory 
WHERE dateTimeAdded BETWEEN
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 00:00:00')AND
concat(date_add(CURRENT_DATE, INTERVAL -91 DAY), ' 23:59:59')
AND userDataHistory.state = 'PR'";
$rPRRes = dbQuery($sPRUsersSQL);
$oPR = dbFetchObject($rPRRes);
$aPRCounts['91'] = $oPR->count;

//subtract 31-days-ago from 30 days
//subtract 91-days-ago from 90 days
//add yesterday to all 3
$sPRUsersSQL = "INSERT INTO puertoRicanUsers (30Days, 90Days, Total, dateTime) values ('".$aPRCounts['30']."','".$aPRCounts['90']."','".$aPRCounts['Total']."',CURRENT_TIMESTAMP)";

//echo "$sPRUsersSQL\n\n";
	$rPRUsers = dbQuery($sPRUsersSQL);
?>

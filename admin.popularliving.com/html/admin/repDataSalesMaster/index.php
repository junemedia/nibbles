<?php
/*********
Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.
**********/
session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$iScriptStartTime = getMicroTime();
set_time_limit(10000);

$sPageTitle = "Data Sales Master Report";

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ('nibbles');


//echo "asdf";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	
	$aCategoryNames = array();
	
	if ($sViewReport != "") {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				//echo "qwerty";
				//first, get lists of offerCodes by categoryId
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
				
				$totalsQuery = "SELECT count(*) as count FROM otDataHistory WHERE dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -30 DAY)";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$o30DayCount = dbFetchObject($rTotals);		
				$totalsQuery = "SELECT count(*) as count FROM abandedOffersHistory WHERE dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -30 DAY)";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$o30DayAbandonedCount = dbFetchObject($rTotals);	
				$ThirtyDayCount = $o30DayCount->count + $o30DayAbandonedCount->count;
				
				$totalsQuery = "SELECT count(*) as count FROM otDataHistory WHERE dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -90 DAY)";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$o90DayCount = dbFetchObject($rTotals);
				$totalsQuery = "SELECT count(*) as count FROM abandedOffersHistory WHERE dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -90 DAY)";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$o90DayAbandonedCount = dbFetchObject($rTotals);	
				$NinetyDayCount = $o90DayCount->count + $o90DayAbandonedCount->count;
				
				$totalsQuery = "SELECT count(*) as count FROM otDataHistory";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$oTotalCount = dbFetchObject($rTotals);		
				$totalsQuery = "SELECT count(*) as count FROM abandedOffersHistory";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$oTotalAbandonedCount = dbFetchObject($rTotals);	
				$TotalCount = $oTotalCount->count + $oTotalAbandonedCount->count;
				
				$totalsQuery = "SELECT count(*) as count FROM otDataHistoryArchive";
				$rTotals = dbQuery($totalsQuery);
				echo dbError();
				$oArchiveCount = dbFetchObject($rTotals);	
				$TotalCount += $oArchiveCount->count;
				
				mysql_select_db ('nibbles_reporting');
				
				//then the 30 day completed offers
				$aCategories = array();
				$query = "SELECT category as category, 30Days as ThirtyDays, 90Days as NinetyDays, Total as Total FROM leadsCategoriesCompleted";
				$rLeadsCompleted = dbQuery($query);
				echo dbError();
				while($oLeadsCompleted = dbFetchObject($rLeadsCompleted)){
					$aCategories[$oLeadsCompleted->category] = array('completed' => array('30' => $oLeadsCompleted->ThirtyDays, '90' => $oLeadsCompleted->NinetyDays, 'Total' => $oLeadsCompleted->Total),
																	 'abandoned' => array());
				}
				
				
				$query = "SELECT category as category, 30Days as ThirtyDays, 90Days as NinetyDays, Total as Total FROM leadsCategoriesAbandoned";
				$rLeadsCompleted = dbQuery($query);
				echo dbError();
				while($oLeadsCompleted = dbFetchObject($rLeadsCompleted)){
					$aCategories[$oLeadsCompleted->category]['abandoned'] = array('30' => $oLeadsCompleted->ThirtyDays, '90' => $oLeadsCompleted->NinetyDays, 'Total' => $oLeadsCompleted->Total);
				}
				
				//print_r($aCategories);
				//then, do the source codes
				
				$aSourceCodes = array('30' => array(), '90' => array(), 'Total' => array());
				$s30DaysSourceCodes = "SELECT sourceCode as sourceCode, 30Days as ThirtyDays FROM leadSources order by 30Days desc limit 10";
				$r30DaysSourceCodes = dbQuery($s30DaysSourceCodes);
				echo dbError();
				while($o30DaysSourceCodes = dbFetchObject($r30DaysSourceCodes)){
					array_push($aSourceCodes['30'],$o30DaysSourceCodes->sourceCode."</td><td>".$o30DaysSourceCodes->ThirtyDays." (".sprintf("%01.2f", (($o30DaysSourceCodes->ThirtyDays/$ThirtyDayCount)*100))."%)");
				}
				
				$s30DaysSourceCodes = "SELECT sourceCode as sourceCode, 90Days as NinetyDays FROM leadSources order by 90Days desc limit 10";
				$r30DaysSourceCodes = dbQuery($s30DaysSourceCodes);
				echo dbError();
				while($o30DaysSourceCodes = dbFetchObject($r30DaysSourceCodes)){
					array_push($aSourceCodes['90'],$o30DaysSourceCodes->sourceCode."</td><td>".$o30DaysSourceCodes->NinetyDays." (".sprintf("%01.2f", (($o30DaysSourceCodes->NinetyDays/$NinetyDayCount)*100))."%)");
				}
				
				$s30DaysSourceCodes = "SELECT sourceCode as sourceCode, Total as Total FROM leadSources order by Total desc limit 10";
				$r30DaysSourceCodes = dbQuery($s30DaysSourceCodes);
				echo dbError();
				while($o30DaysSourceCodes = dbFetchObject($r30DaysSourceCodes)){
					array_push($aSourceCodes['Total'],$o30DaysSourceCodes->sourceCode."</td><td>".$o30DaysSourceCodes->Total." (".sprintf("%01.2f", (($o30DaysSourceCodes->Total/$TotalCount)*100))."%)");
				}
				//print_r($aSourceCodes);
				
				//then, leads counts by email domain
				
				$aDomains = array('30' => array(), '90' => array(), 'Total' => array());
				$s30DaysDomains = "SELECT domain as domain, 30Days as ThirtyDays FROM emailDomains order by 30Days desc limit 10";
				$r30DaysDomains = dbQuery($s30DaysDomains);
				echo dbError();
				while($o30DaysDomains = dbFetchObject($r30DaysDomains)){
					array_push($aDomains['30'],$o30DaysDomains->domain."</td><td>".$o30DaysDomains->ThirtyDays." (".sprintf("%01.2f", (($o30DaysDomains->ThirtyDays/$ThirtyDayCount)*100))."%)");
				}
				
				$s30DaysDomains = "SELECT domain as domain, 90Days as NinetyDays FROM emailDomains order by 90Days desc limit 10";
				$r30DaysDomains = dbQuery($s30DaysDomains);
				echo dbError();
				while($o30DaysDomains = dbFetchObject($r30DaysDomains)){
					array_push($aDomains['90'],$o30DaysDomains->domain."</td><td>".$o30DaysDomains->NinetyDays." (".sprintf("%01.2f", (($o30DaysDomains->NinetyDays/$NinetyDayCount)*100))."%)");
				}
				
				$s30DaysDomains = "SELECT domain as domain, Total as Total FROM emailDomains order by Total desc limit 10";
				$r30DaysDomains = dbQuery($s30DaysDomains);
				echo dbError();
				while($o30DaysDomains = dbFetchObject($r30DaysDomains)){
					array_push($aDomains['Total'],$o30DaysDomains->domain."</td><td>".$o30DaysDomains->Total." (".sprintf("%01.2f", (($o30DaysDomains->Total/$TotalCount)*100))."%)");
				}
				//print_r($aDomains);
																				
				//puertoricans
				$aPuertoRicans = array('30' => array(), '90' => array(), 'total' => array());
				$s30DaysDomains = "SELECT 30Days as ThirtyDays, 90Days as NinetyDays, Total as Total FROM puertoRicanUsers order by dateGenerated DESC limit 1";
				$r30DaysDomains = dbQuery($s30DaysDomains);
				echo dbError();
				while($o30DaysDomains = dbFetchObject($r30DaysDomains)){
					$aPuertoRicans['30'] = $o30DaysDomains->ThirtyDays;
					$aPuertoRicans['90'] = $o30DaysDomains->NinetyDays;
					$aPuertoRicans['Total'] = $o30DaysDomains->Total;
				}
				//print_r($aPuertoRicans);
							
				//turn back on logging before going live!
				/*
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $nibblesReportWhere\")"; 
				$rResult = dbQuery($sAddQuery); 
				echo  dbError(); 
				*/				
				
				//print_r($aCategories);
				$sReportContent = '';
				foreach($aCategories as $category => $data){
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sReportContent .= "<tr class=$sBgcolorClass>
										<td>$category. ".$aCategoryNames[$category]."</td>
										<td>".$data['completed']['30']." (".sprintf("%01.2f", (($data['completed']['30']/$ThirtyDayCount)*100))."%)</td><td>".$data['abandoned']['30']." (".sprintf("%01.2f", (($data['abandoned']['30']/$ThirtyDayCount)*100))."%)</td>
										<td>".$data['completed']['90']." (".sprintf("%01.2f", (($data['completed']['90']/$NinetyDayCount)*100))."%)</td><td>".$data['abandoned']['90']." (".sprintf("%01.2f", (($data['abandoned']['90']/$NinetyDayCount)*100))."%)</td>
										<td>".$data['completed']['Total']." (".sprintf("%01.2f", (($data['completed']['Total']/$TotalCount)*100))."%)</td><td>".$data['abandoned']['Total']." (".sprintf("%01.2f", (($data['abandoned']['Total']/$TotalCount)*100))."%)</td>
										</tr>";
				}
				
				$sTopSources = '';
				for($i=0; $i<10; $i++){
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sTopSources .= "<tr class=$sBgcolorClass>
										<td>".($i + 1).".</td>
										<td>".$aSourceCodes['30'][$i]."</td>
										<td>".$aSourceCodes['90'][$i]."</td>
										<td>".$aSourceCodes['Total'][$i]."</td>
										</tr>";
					
				}
				
				$sTopDomains = '';
				for($i=0; $i<10; $i++){
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sTopDomains .= "<tr class=$sBgcolorClass>
										<td>".($i + 1).".</td>
										<td>".$aDomains['30'][$i]."</td>
										<td>".$aDomains['90'][$i]."</td>
										<td>".$aDomains['Total'][$i]."</td>
										</tr>";
					
				}
				
					
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
					
				$sPuertoRicans = "<tr class=$sBgcolorClass>
									<td></td>
									<td>".$aPuertoRicans['30']."</td>
									<td>".$aPuertoRicans['90']."</td>
									<td>".$aPuertoRicans['Total']."</td>
									</tr>";
					
				
			}
	}

		
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>
<script language='javascript'>
function toggleQueries(){
	div = document.getElementById('QueriesDiv');
	if(div.style.display == 'none'){
		div.style.display = 'block';
	} else {
		div.style.display = 'none';
	}
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>


<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
&nbsp;&nbsp;
</td>
	<td colspan=2></td>
</tr>

</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=7 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR></td></tr>
	<tr><td colspan=7 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td><b>Category #</b></td><td colspan=2><b>Last 30 Days</b></td><td colspan=2><b>Last 90 Days</b></td><td colspan=2><b>Total</b></td></tr>
	<tr><td></td><td><b>Completed</b></td><td><b>Abandoned</b></td><td><b>Completed</b></td><td><b>Abandoned</b></td><td><b>Completed</b></td><td><b>Abandoned</b></td></tr>
<?php echo $sReportContent;?>
<tr><td colspan=7 align=left><hr color=#000000></td></tr>	
<tr><td><b>Top 10 Source Codes</b></td><td colspan=2><b>Last 30 Days</b></td><td colspan=2><b>Last 90 Days</b></td><td colspan=2><b>Total</b></td></tr>	
<?php echo $sTopSources;?>
<tr><td colspan=7 align=left><hr color=#000000></td></tr>	
<tr><td><b>Top 10 Domain Sources</b></td><td colspan=2><b>Last 30 Days</b></td><td colspan=2><b>Last 90 Days</b></td><td colspan=2><b>Total</b></td></tr>	
<?php echo $sTopDomains;?>
<!--<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
<tr><td><b>Puertorican Users</b></td><td><b>Last 30 Days</b></b></td><td><b>Last 90 Days</b></td><td><b>Total</b></td></tr>	
<?php //echo $sPuertoRicans;?>-->
<tr><td colspan=7 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=7 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=7>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Count:  Number of leads collected with this session id.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		</td></tr>
	<tr><td colspan=7><BR><BR></td></tr>
	<tr>
	<td colspan=7>
	<a href='javascript:void(toggleQueries());'>Queries </a><br>
	<div id='QueriesDiv' style='display:none;'>
	
	<b>Categories :</b><br>
	<b>Get names and IDs for each offer Category - </b><br>
	SELECT offers.offerCode as offerCode FROM offers, categoryMap 
	WHERE categoryMap.categoryId = '$oCategoryId->id' 
	AND categoryMap.offerCode = offers.offerCode<br><br>
	
	<b>The example category will be "IT Tech", ID 8.</b><br>
	
	<b>Get each Offer Code for each Category - </b><br>
	SELECT offers.offerCode as offerCode FROM offers, categoryMap 
	WHERE categoryMap.categoryId = '8' 
	AND categoryMap.offerCode = offers.offerCode<br><br>
	
	<b>30 Day Complted Count - </b><br>
	SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -30 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')<br><br>
	
	<b>90 Day Completed Count - </b><br>
	SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -90 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')<br><br>
	
	<b>Total Completed Count - </b><br>
	SELECT count(otDataHistory.id) as count FROM otDataHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistoryArchive.dateTimeAdded < CURRENT_DATE<br>
	SELECT count(otDataHistoryArchive.id) as count FROM otDataHistoryArchive
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistoryArchive.dateTimeAdded < CURRENT_DATE<br><br>
	
	
	<b>30 Day Abandoned Count - </b><br>
	SELECT count(otDataHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -30 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')<br><br>
	
	<b>90 Day Abandoned Count - </b><br>
	SELECT count(otDataHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -90 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')<br><br>
	
	<b>Total Abandoned Count - </b><br>
	SELECT count(abandedOffersHistory.id) as count FROM abandedOffersHistory 
	WHERE offerCode in ('ACSC_BESTB','ACSC_PICS','ACSC_VONG','DSC_DAD','JTMP1','MS_NetPanel','RITT_TECH','test_pdCOREG','test_pdCWH','WMUR_INS')
	AND abandedOffersHistory.dateTimeAdded < CURRENT_DATE<br><br>
	
	<b>Source Codes :</b><br>
	<b>30 Day Count - </b><br>
	SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded BETWEEN 
	concat(date_add(CURRENT_DATE, INTERVAL -30 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
	GROUP BY sourceCode<br><br>
	
	<b>90 Day Count - </b><br>
	SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded BETWEEN 
	concat(date_add(CURRENT_DATE, INTERVAL -90 DAY), ' 00:00:00') AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
	GROUP BY sourceCode<br><br>
	
	<b>Total Count - </b><br>
	SELECT sourceCode, count(sourceCode) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded < CURRENT_DATE
	GROUP BY sourceCode<br>
	SELECT sourceCode, count(sourceCode) as count FROM otDataHistoryArchive
	WHERE otDataHistoryArchive.dateTimeAdded < CURRENT_DATE
	GROUP BY sourceCode<br><br>
	
	<b>Email Domains:</b><br>
	<b>30 Day Count - </b><br>
	SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -30 DAY), ' 00:00:00')AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
	GROUP BY domain<br><br>
	
	<b>90 Day Count - </b><br>
	SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded BETWEEN
	concat(date_add(CURRENT_DATE, INTERVAL -90 DAY), ' 00:00:00')AND
	concat(date_add(CURRENT_DATE, INTERVAL -1 DAY), ' 23:59:59')
	GROUP BY domain<br><br>
	
	<b>Total Count - </b><br>
	SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistory
	WHERE otDataHistory.dateTimeAdded < CURRENT_DATE
	GROUP BY domain<br>
	SELECT substring(email, (locate('@',email)+1)) as domain, count(id) as count FROM otDataHistoryArchive
	WHERE otDataHistoryArchive.dateTimeAdded < CURRENT_DATE
	GROUP BY domain<br><br>
	</div>
	</td>
	</tr>
		</table></td></tr></table></td></tr>
	</table>
</td></tr>
</table>
</form>
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

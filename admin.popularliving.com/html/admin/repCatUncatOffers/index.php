<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offer / Category Assignment By Offer";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {
		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles			


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	


	$sOffersQuery = "SELECT offerCode, revPerLead, mode, isLive
					 FROM   offers
					 WHERE  mode = 'A'					 
					 ORDER BY offerCode";
	$rOffersResult = dbQuery($sOffersQuery);
		
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sOfferCode = $oOffersRow->offerCode;
		$fRevPerLead = $oOffersRow->revPerLead;
		$sMode = $oOffersRow->mode;
		$iIsLive = $oOffersRow->isLive;
		
		if ($sMode == 'A' && $iIsLive) {
			$sLiveAndActive = "Y";
		} else {
			$sLiveAndActive = "";
		}

		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN_WHITE";
		} else {
			$bgcolorClass = "ODD";
		}
		
		$sReportContent .= "<tr class=$bgcolorClass><td>$oOffersRow->offerCode</td><td align=right>";
		
		$sOfferCategories = '';
		$iNoOfCategories = 0;
		$iTodaysLeads = 0;
		
		$sCatQuery = "SELECT   categories.id AS catId, title
						 FROM    categories, categoryMap
						 WHERE   categories.id = categoryMap.categoryId
						 AND 	 offerCode = '$sOfferCode'
						 ORDER BY title";

		$rCatResult = dbQuery($sCatQuery);	
		while ($oCatRow = dbFetchObject($rCatResult)) {
			$sOfferCategories .= $oCatRow->title.", ";
			$iNoOfCategories++;
		}
		if ($sOfferCategories != '') {
			$sOfferCategories = substr($sOfferCategories, 0, strlen($sOfferCategories)-2);
		}
		
		$sTodaysLeadsQuery = "SELECT count(otData.email) AS todaysLeads
							  FROM	 otData, userData
							  WHERE	 otData.email = userData.email
							  AND	 address NOT LIKE \"3401 Dundee%\"
							  AND	 offerCode = '$sOfferCode'";
		
		$rTodaysLeadsResult = dbQuery($sTodaysLeadsQuery);
		while ($oTodaysLeadsRow = dbFetchObject($rTodaysLeadsResult)) {
			$iTodaysLeads = $oTodaysLeadsRow->todaysLeads;
		}
								
		$sReportContent .= "$fRevPerLead</td><td align=right>$iNoOfCategories</td><td>$sLiveAndActive</td><td align=right>$iTodaysLeads</td><td>$sOfferCategories</td></tr>";
	}				 
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
			
	$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=6 class=bigHeader align=center><BR>$sPageTitle<BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: $sRunDateAndTime</td></tr>
	<tr><td class=header>Offer</td>
		<td class=header align=right>Rev Per Lead</td>
		<td class=header align=right>No Of Categories Under</td>
		<td class=header>Live And Active</td>
		<td class=header align=right>Leads Today</td>
		<td class=header>Categories</td></tr>
	
	$sReportContent
	<tr><td colspan=6 align=center><hr color=#000000></td></tr>	
	<tr><td colspan=6><BR><BR></td></tr>
	<tr><td colspan=6 class=header><BR>Notes -</td></tr>
	<tr><td colspan=6>Report only reflects active offers.
					<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads.
			</td></tr>	
	<tr><td colspan=6>Approximate time ro run this report - $iScriptExecutionTime second(s)</td></tr>	
		</td></tr></table></td></tr></table></td></tr>
	</table>";		
	
	include("../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
	
			<?php echo $sReportContent;?>
			
</td></tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
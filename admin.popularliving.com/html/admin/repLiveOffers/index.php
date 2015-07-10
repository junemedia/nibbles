<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offers Live And Collecting Leads Report";

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


// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	

	
	
	$sLiveOffersQuery = "SELECT offers.offerCode, name
								FROM   offers, offerCompanies
								WHERE  offers.companyId = offerCompanies.id
								AND	   mode = 'A'
							    AND    isLive = '1'
								AND    creditStatus = 'ok'
								ORDER BY offerCode";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sLiveOffersQuery\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles	
	
	
	$rLiveOffersResult = dbQuery($sLiveOffersQuery);
	
	
	echo dbError();
	
	while ($oLiveOffersRow = dbFetchObject($rLiveOffersResult)) {
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN_WHITE";
		} else {
			$bgcolorClass = "ODD";
		}
		
		$sReportContent .= "<tr class=$bgcolorClass><td>$oLiveOffersRow->offerCode</td><td>$oLiveOffersRow->name</td><td align=right>$oUndeliveredLeadsRow->undeliveredLeads</td></tr>";
		
	}
								 
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
	$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=2 class=bigHeader align=center><BR>$sPageTitle<BR><BR><BR></td></tr>
	<tr><td colspan=2 class=header>Run Date / Time: $sRunDateAndTime</td></tr>
	<tr><td  class=header>Offer Code</td><td class=header>Offer Name</td></tr>
	
	$sReportContent
	<tr><td colspan=2 align=center><hr color=#000000></td></tr>	
	<tr><td colspan=2 class=header><BR>Notes -</td></tr>
	<tr><td colspan=2>- Approximate time to run this report - $iScriptExecutionTime second(s)</td></tr>	
	<tr><td colspan=2><BR><BR></td></tr>
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
<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offers Paying Less Than $1 By Page";

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

		
	$sPagesQuery = "SELECT   id AS pageId, pageName
						 FROM     otPages
						 ORDER BY pageName";
	$rPagesResult = dbQuery($sPagesQuery);	
	
	echo dbError();

	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sPagesQuery\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		
	
	
	while ($oPagesRow = dbFetchObject($rPagesResult)) {
		$iPageId = $oPagesRow->pageId;
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN_WHITE";
		} else {
			$bgcolorClass = "ODD";
		}
		
		$sReportContent .= "<tr class=$bgcolorClass><td>$oPagesRow->pageName</td><td>";
		
		$sPageOffers = '';
		
		$sOffersQuery = "SELECT offers.offerCode
						 FROM   offers, pageMap
						 WHERE  offers.offerCode = pageMap.offerCode
						 AND    pageMap.pageId = '$iPageId' 
						 AND    offers.mode = 'A'
						 AND    offers.revPerLead < 1
						 ORDER BY offerCode";
		$rOffersResult = dbQuery($sOffersQuery);
		
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$sPageOffers .= $oOffersRow->offerCode.", ";
		}
		if ($sPageOffers != '') {
			$sPageOffers = substr($sPageOffers, 0, strlen($sPageOffers)-2);
		}
		
		$sReportContent .= "$sPageOffers</td></tr>";
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
	<tr><td  class=header>Page</td><td class=header>Offers Paying Less Than $1</td></tr>
	
	$sReportContent
	<tr><td colspan=2 align=center><hr color=#000000></td></tr>
	
	<tr><td colspan=2><BR><BR></td></tr>
	<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	<tr><td colspan=5>Report only reflects active offers.</td></tr>	
	<tr><td colspan=5>Approximate time to run this report - $iScriptExecutionTime second(s)</td></tr>
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
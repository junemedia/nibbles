<?php
include_once("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


$sPageTitle = "Min / Max Offers on OtPages";

if (hasAccessRight($iMenuId) || isAdmin()) {

	if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
				case "offersCount" :
				$sCurrOrder = $sOffersCountOrder;
				$sOffersCountOrder = ($sOffersCountOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "otMinNoOfOffers" :
				$sCurrOrder = $sMinNoOfOffersOrder;
				$sMinNoOfOffersOrder = ($sMinNoOfOffersOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "otMaxNoOfOffers" :
				$sCurrOrder = $sMaxNoOfOffersOrder;
				$sMaxNoOfOffersOrder = ($sMaxNoOfOffersOrder != "DESC" ? "DESC" : "ASC");
				break;
				default:
				$sOrderColumn="otPageName";
				$sCurrOrder = $sPageNameOrder;
				$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
			}
	}
	
	if ($sCurrOrder != 'DESC') {
			$sCurrOrder = 'DESC';
		} else {
			$sCurrOrder = 'ASC';
	}
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";

		$sOffersCountQuery = "SELECT otPages.pageName as otPageName, otPages.minNoOfOffers as otMinNoOfOffers, otPages.maxNoOfOffers as otMaxNoOfOffers, count(pageMap.id) as offersCount
						  FROM	 otPages, pageMap, offers, offerCompanies
						  WHERE	 otPages.id = pageMap.pageId
						  AND    pageMap.offerCode = offers.offerCode
						  AND    offers.companyId = offerCompanies.id
						  AND    offers.mode = 'A'
						  AND	 offers.isLive = '1'
						  AND    offerCompanies.creditStatus = 'ok'
		 				  AND    pageName NOT LIKE 'test%'						 
						  GROUP BY pageMap.pageId
						  ORDER BY $sOrderColumn $sCurrOrder";
	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sOffersCountQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		

	$rOffersCountResult = dbQuery($sOffersCountQuery);

	
	
		while ($oOffersCountRow = dbFetchObject($rOffersCountResult)) {
			$sPageName = $oOffersCountRow->otPageName;
			$iMinNoOfOffers = $oOffersCountRow->otMinNoOfOffers;
			$iMaxNoOfOffers = $oOffersCountRow->otMaxNoOfOffers;
			$iOffersCount = $oOffersCountRow->offersCount;
			
			if ($iOffersCount < $iMinNoOfOffers || $iOffersCount > $iMaxNoOfOffers) {
					if ($bgcolor == "#DDDDDD") {
						$bgcolor = "white";
					} else {
						$bgcolor = "#DDDDDD";
					}
				
				$sReportContent .= "<tr bgcolor=$bgcolor><td>$sPageName</td>
				  <td>$iOffersCount</td>
				  <td>$iMinNoOfOffers</td>
				 <td>$iMaxNoOfOffers</td></tr>";
			}
		}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	
	
include("../../includes/adminHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=75% align=center>
	<tr bgcolor=white><td colspan=4>&nbsp;</td></tr><tr>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=otPageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=offersCount&sOffersCountOrder=<?php echo $sOffersCountOrder;?>" class=header>Offers on page</a></td>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=otMinNoOfOffers&sMinNoOfOffersOrder=<?php echo $sMinNoOfOffersOrder;?>" class=header>Minimum offers on page</a></td>
	<td><a href="<?php echo $sSortLink;?>&sOrderColumn=otMaxNoOfOffers&sMaxNoOfOffersOrder=<?php echo $sMaxNoOfOffersOrder;?>" class=header>Maximum offers on page</a></td>
</tr>

<?php echo $sReportContent;?>

<tr bgcolor=white><td colspan=4>&nbsp;</td></tr>
<tr bgcolor=white><td colspan=4>Notes - <BR>Offers reported for page are live and active.<br>
		All pages are reported pages can't be set as active or inactive.<BR></td>
</tr>

</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Single Page Offers";

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

if (!($sViewReport)) {
	$iShowLiveOnly = "1";
}
	if ($iShowLiveOnly ) {
		$sFilter = " AND  mode = 'A' AND isLive = '1' AND creditStatus = 'ok' ";
	}
	
	
	if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
	} else {
	
		
	$sOffersQuery = "SELECT offers.offerCode, offers.revPerLead, offers.mode, offers.isLive, 
							companyId, creditStatus, companyName, repDesignated
					 FROM   offers, offerCompanies
					 WHERE  offers.companyId = offerCompanies.id
					 AND	page2Info != '1'
					 $sFilter
					 ORDER BY offerCode";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sOffersQuery\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		


	
	
	$rOffersResult = dbQuery($sOffersQuery);
		
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sOfferCode = $oOffersRow->offerCode;
		$sRepDesignated = $oOffersRow->repDesignated;
		$fRevPerLead = $oOffersRow->revPerLead;
		$sMode = $oOffersRow->mode;
		$iIsLive = $oOffersRow->isLive;
		$sCompanyName = $oOffersRow->companyName;
		$sCreditStatus = $oOffersRow->creditStatus;
		
		if ($sMode == 'A' && $iIsLive && $sCreditStatus == 'ok') {
			$sLiveAndActive = "Y";
		} else {
			$sLiveAndActive = "";
		}
		
		$iPageId = $oPagesRow->pageId;
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN_WHITE";
		} else {
			$bgcolorClass = "ODD";
		}
		
		$sReportContent .= "<tr class=$bgcolorClass><td>$oOffersRow->offerCode</td>";
		$sExpReportContent .= "$oOffersRow->offerCode";
		
		$sOfferPages = '';
		$iNoOfPages = 0;
		$iTodaysLeads = 0;
		
		$sPagesQuery = "SELECT   otPages.id AS pageId, pageName
						 FROM    otPages, pageMap
						 WHERE   otPages.id = pageMap.pageId
						 AND 	 offerCode = '$sOfferCode'
						 ORDER BY pageName";
		$rPagesResult = dbQuery($sPagesQuery);	
		while ($oPagesRow = dbFetchObject($rPagesResult)) {
			$sOfferPages .= $oPagesRow->pageName.", ";
			$iNoOfPages++;
		}
		if ($sOfferPages != '') {
			$sOfferPages = substr($sOfferPages, 0, strlen($sOfferPages)-2);
		}
		
		$sOfferRep = '';
		if ($sRepDesignated != '') {
			$sRepQuery = "SELECT *
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
			$rRepResult = dbQuery($sRepQuery);
			echo dbError();
			
			while ($oRepRow = dbFetchObject($rRepResult)) {
				
				$sOfferRep .= $oRepRow->userName.", ";
				
			}
			if ($sOfferRep != '') {
				$sOfferRep = substr($sOfferRep, 0, strlen($sOfferRep) -2);
			}
		}
		$sReportContent .= "<td>$sOfferRep</td><td>$sCompanyName</td><td>$sLiveAndActive</td><td>$sOfferPages</td></tr>";
		$sExpReportContent .= "\t$sOfferRep\t$sCompanyName\t$sLiveAndActive\t$sOfferPages\n";
	}				 

	}
	
	
	
	if ($sExportExcel) {
		
		$sExpReportContent = "Offer\tSales Rep.\tOffer Company\tLive And Active\tPages"."\n".$sExpReportContent;
		if ($iShowLiveOnly) {
			$sExpReportContent .= "\n\nReport For: Live And Active Offers Only";
		} else {
			$sExpReportContent .= "\n\nReport For: All Offers";
		}
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "sopOffers_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExpReportContent, strlen($sExpReportContent));
			fclose($rFpFile);

			echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	}
	
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	if ($iShowLiveOnly) {
		$sShowLiveOnlyChecked = "checked";
	}
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
		
	
	include("../../includes/adminHeader.php");	

// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td width=20%></td><td><input type=checkbox name=iShowLiveOnly value='1' <?php echo $sShowLiveOnlyChecked;?>> Show Live And Active Offers Only</td></tr>
<tr><td></td><td><input type=submit name='sSubmit' value='View Report' onClick="funcReportClicked('report');">
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>
	<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>Offer</td>
		<td class=header>Sales Rep.</td>
		<td class=header>Offer Company</td>
		<td class=header>Live And Active</td>		
		<td class=header>Pages</td></tr>
			<?php echo $sReportContent;?>
			<tr><td colspan=5 align=center><hr color=#000000></td></tr>	
	<tr><td colspan=5><BR><BR></td></tr>
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
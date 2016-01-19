<?php

/*********

Script to Display 

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offer / Page Assignment By Page";

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

	$sRepQuery = "SELECT nbUsers.id, firstName, userName
				  FROM   nbUsers				  
				  ORDER BY userName";
	
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	if ($iRepDesignated == 'all') {
		$sSelected = "selected";
	}
	$sRepOptions = "<option value='all' $sSelected>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ($oRepRow->id == $iRepDesignated) {
			$sSelected = "selected";
			$sRepUserName = $oRepRow->userName;
		} else {
			$sSelected = '';
		}		
		$sRepOptions .= "<option value='".$oRepRow->id."' $sSelected>$oRepRow->userName";
	}
		
	if ($iRepDesignated != '' && $iRepDesignated != 'all') {
		$sTempRepDesignated = "'".$iRepDesignated."'";
	}

	
	if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
	} else {
	
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
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report $sTempRepDesignated\")"; 
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
		$sExpReportContent .= "$oPagesRow->pageName\t";
		
		$sPageOffers = '';
		$iTotalLeads = 0;
		$iCount = 0;
		$sOffersQuery = "SELECT offers.offerCode
						 FROM   offers, pageMap, offerCompanies
						 WHERE  offers.companyId = offerCompanies.id
						 AND	offers.offerCode = pageMap.offerCode						 
						 AND	pageMap.pageId = '$iPageId'";
		
		if ($iRepDesignated != '' && $iRepDesignated !='all') {
			$sOffersQuery .= " AND FIND_IN_SET(\"$sTempRepDesignated\", offerCompanies.repDesignated) > 0 ";
		}
	
		$sOffersQuery .= "	 GROUP BY offerCode
							 ORDER BY offerCode";

		$rOffersResult = dbQuery($sOffersQuery);
		echo dbError();
		$iCount = dbNumRows($rOffersResult);
		
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$sPageOffers .= $oOffersRow->offerCode.", ";
			
		}
		if ($sPageOffers != '') {
			$sPageOffers = substr($sPageOffers, 0, strlen($sPageOffers)-2);
		}
		
		$sActiveAndLiveOffersQuery = "SELECT offers.offerCode
						 FROM   offers, pageMap, offerCompanies
						 WHERE  offers.companyId = offerCompanies.id
						 AND	offers.offerCode = pageMap.offerCode						 
						 AND	pageMap.pageId = '$iPageId' 
						 AND    mode = 'A'
						 AND    isLive = '1'";
		
		if ($iRepDesignated != '' && $iRepDesignated !='all') {
			$sActiveAndLiveOffersQuery .= " AND FIND_IN_SET(\"$sTempRepDesignated\", offerCompanies.repDesignated) > 0 ";
		}
	
		
		$sActiveAndLiveOffersQuery .= " GROUP BY offerCode
						 				ORDER BY offerCode";
		$rActiveAndLiveOffersResult = dbQuery($sActiveAndLiveOffersQuery);
		echo dbError();
		$iActiveAndLiveCount = dbNumRows($rActiveAndLiveOffersResult);
		
		
		// current leads count query
		$sLeadCountQuery = "SELECT count(otData.email) AS totalLeads
							FROM	otData, userData
							WHERE   otData.email = userData.email
						    AND     address NOT LIKE \"3401 Dundee%\"
							AND	    otData.pageId = '$iPageId'";
		
		$rLeadCountResult = dbQuery($sLeadCountQuery);
		while ($oLeadCountRow = dbFetchObject($rLeadCountResult)) {
			$iTotalLeads = $oLeadCountRow->totalLeads;
		}
				
		$sReportContent .= "$sPageOffers</td><td>$iCount</td><td>$iActiveAndLiveCount</td><td>$iTotalLeads</td></tr>";
		$sExpReportContent .= "$sPageOffers\t$iCount\t$iActiveAndLiveCount\t$iTotalLeads\n";
	}

	} // end of report condition
	 
	
		
	if ($sExportExcel) {
		
		$sExpReportContent = "Page\tOffers\tTotal Offers\tLive And Active Offers\tToday's Gross Leads"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport For Rep: $sRepUserName";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nReport only reflects active offers.";
		$sExpReportContent .= "\nReport omits any leads having address starting with '3401 Dundee' considering those as test leads.\n";

		$sFileName = "offerPageByPage_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
<tr><td width=15%>Rep. Designated</td>
	<td><select name='iRepDesignated'>
	<?php echo $sRepOptions;?>
	</select></td>
</tr>
<tr><td></td><td><input type=submit name='sSubmit' value='View Report' onClick="funcReportClicked('report');">
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
</td></tr>
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
	
	<tr><td  class=header>Page</td>
		<td class=header>Offers</td>
		<td class=header>Total Offers</td>
		<td class=header>Live And Active Offers</td>
		<td class=header>Today's Gross Leads</td>
	</tr>
	
			<?php echo $sReportContent;?>
			<tr><td colspan=5 align=center><hr color=#000000></td></tr>	
	
	<tr><td colspan=5><BR><BR></td></tr>
	
	<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	
	<tr><td colspan=5>Report only reflects active offers.
				<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads.</td></tr>	
	<tr><td colspan=5>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
		</td></tr></table></td></tr></table></td></tr>
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
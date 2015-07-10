<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offer / Page Assignment By Offer";

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

	//if ($sViewReport) {
	if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
	} else {
	
			
	$sOffersQuery = "SELECT offerCode, revPerLead, mode, isLive
					 FROM   offers, offerCompanies
					 WHERE  offers.companyId = offerCompanies.id
					 AND	mode = 'A'";
	
	if ($iRepDesignated != '' && $iRepDesignated !='all') {
		$sOffersQuery .= " AND FIND_IN_SET(\"$sTempRepDesignated\", offerCompanies.repDesignated) > 0 ";
	}
	
	$sOffersQuery .= " ORDER BY offerCode";

	
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
		echo dbError();
	$i = 0;
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
		
		$iPageId = $oPagesRow->pageId;
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN_WHITE";
		} else {
			$bgcolorClass = "ODD";
		}
		
		$sReportContent .= "<tr class=$bgcolorClass><td>$oOffersRow->offerCode</td><td align=right>";
		
		$sExpReportContent .= "$oOffersRow->offerCode\t";
		
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
		
		$sTodaysLeadsQuery = "SELECT count(otData.email) AS todaysLeads
							  FROM	 otData, userData
							  WHERE	 otData.email = userData.email
							  AND	 address NOT LIKE \"3401 Dundee%\"
							  AND	 offerCode = '$sOfferCode'";
		
		$rTodaysLeadsResult = dbQuery($sTodaysLeadsQuery);
		while ($oTodaysLeadsRow = dbFetchObject($rTodaysLeadsResult)) {
			$iTodaysLeads = $oTodaysLeadsRow->todaysLeads;
		}
								
		$sReportContent .= "$fRevPerLead</td><td align=right>$iNoOfPages</td><td>$sLiveAndActive</td><td align=right>$iTodaysLeads</td><td>$sOfferPages</td></tr>";
		
		$sExpReportContent .= "$fRevPerLead\t$iNoOfPages\t$sLiveAndActive\t$iTodaysLeads\t$sOfferPages\n";
		
	}				 
	
		}
	//} // end of view report condition
	
	
		
	if ($sExportExcel) {
		
		$sExpReportContent = "Offer\tRev. Per Lead\tNo Of PagesOn\tLive And Active\tLeads Today\tPages"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport For Rep: $sRepUserName";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nReport only reflects active offers.";
		$sExpReportContent .= "\nReport omits any leads having address starting with '3401 Dundee' considering those as test leads.\n";

		$sFileName = "offerPageByOffer_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
		
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
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
	<tr><td colspan=6 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><BR><BR></td></tr>
	<tr><td colspan=6 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header>Offer</td>
		<td class=header align=right>Rev Per Lead</td>
		<td class=header align=right>No Of Pages On</td>
		<td class=header>Live And Active</td>
		<td class=header align=right>Leads Today</td>
		<td class=header>Pages</td></tr>
			<?php echo $sReportContent;?>
			<tr><td colspan=6 align=center><hr color=#000000></td></tr>	
	<tr><td colspan=6><BR><BR></td></tr>
	<tr><td colspan=6 class=header><BR>Notes -</td></tr>
	<tr><td colspan=6>Report only reflects active offers.
					<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads.
			</td></tr>	
	<tr><td colspan=6>Approximate time ro run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>	
		</td></tr></table></td></tr></table></td></tr>
	</table>
</td></tr>
<tr><td></td></tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
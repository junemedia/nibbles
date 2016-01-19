<?php

/*********

Script to Display IO Report
$Author: bbevis $
$Id: repIO.php,v 1.4 2006/03/13 09:07:00 bbevis Exp $

**********/

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


set_time_limit(5000);
$iScriptStartTime = getMicroTime();

$sPageTitle = "IO Report";

	$bugOut = "$sDateFrom == sDateFrom<br>
	$sDateTo == sDateTo<br>
	$sType == sType<br>
	$sTemplate == sTemplate<br>
	$iAccountRep == iAccountRep<br>
	$sPartner == sPartner<br>
	$bPartnerNameExact == bPartnerNameExact<br>
	$sSigned == sSigned<br>";

if (hasAccessRight($iMenuId) || isAdmin()) {
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sCampaignsLink = "<a href='$sGblAdminSiteRoot/ioManagement/index.php?iMenuId=$iMenuId'>IO Management</a>";

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = date('m')."-".date('d')."-".date('Y');

	$sViewReport = stripslashes($sViewReport);

	/*
	if ($sAllowReport == 'N') {
		$sMessage .= "Server Load Is High. Please check back soon...";
	} else {
*/
		
		if ($sViewReport == "Today's Report") {


			$iYearFrom = date('Y');
			$iMonthFrom = date('m');
			$iDayFrom = date('d');

			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";

			$iMonthTo = $iMonthFrom;
			$iDayTo = $iDayFrom;
			$iYearTo = $iYearFrom;

			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";

		}


		if ($sDateFrom && $sDateTo) {

			$sTempDateFromArray = explode("-", $sDateFrom);
			$sTempDateToArray = explode("-", $sDateTo);

			// tempdates are the dates in mysql format

			$sTempDateFrom = $sTempDateFromArray[2]."-".$sTempDateFromArray[0]."-".$sTempDateFromArray[1];
			$sTempDateTo = $sTempDateToArray[2]."-".$sTempDateToArray[0]."-".$sTempDateToArray[1];
			$sTempDateTimeFrom = $sTempDateFromArray[2]."-".$sTempDateFromArray[0]."-".$sTempDateFromArray[1]." 00:00:00";
			$sTempDateTimeTo = $sTempDateToArray[2]."-".$sTempDateToArray[0]."-".$sTempDateToArray[1]." 23:59:59";

			$sTempToday = date('Y')."-".date('m')."-".date('d');
			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
		
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sViewReport, BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo', account rep: $iAccountRep\")"; 
			$rResult = dbQuery($sAddQuery); 
			echo  dbError(); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		
			
		}

		
	
		
		// Set Default order column
		if (!($sOrderColumn)) {
				$sOrderColumn = "dateGenerated";
				$sCurrOrder = SORT_ASC;
		}
		$bugOut .= "orderCOlumn === ".$sOrderColumn."<br>";
		// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
		if (!($sCurrOrder)) {
			//echo "Made it here...";
			switch ($sOrderColumn) {
				case "ioId":
				$sCurrOrder = "id";
				if($sOldOrder == "ioId"){
					$sCurrOrderOrder = ($sCurrOrderOrder != "DESC" ? "DESC" : "ASC");
				} else {
					$sCurrOrderOrder = "ASC";
				}
				break;
				case "dateGenerated":
				$sCurrOrder = "dateGenerated";
				if($sOldOrder == "dateGenerated"){
					$sCurrOrderOrder = ($sCurrOrderOrder != "DESC" ? "DESC" : "ASC");
				} else {
					$sCurrOrderOrder = "ASC";
				}
				break;
				case "partnerName":
				$sCurrOrder = "partnerName";
				if($sOldOrder == "partnerName"){
					$sCurrOrderOrder = ($sCurrOrderOrder != "DESC" ? "DESC" : "ASC");
				} else {
					$sCurrOrderOrder = "ASC";
				}
				break;
				case "repId":
				$sCurrOrder = "repId";
				if($sOldOrder == "repId"){
					$sCurrOrderOrder = ($sCurrOrderOrder != "DESC" ? "DESC" : "ASC");
				} else {
					$sCurrOrderOrder = "ASC";
				}
				break;
				case "signed":
				$sCurrOrder = "signed";
				if($sOldOrder == "signed"){
					$sCurrOrderOrder = ($sCurrOrderOrder != "DESC" ? "DESC" : "ASC");
				} else {
					$sCurrOrderOrder = "ASC";
				}
				break;
			}
		} else {
			$sCurrOrder = "dateGenerated";
			$sCurrOrderOrder = "DESC";
		}
		
		$bugOut .= "<br>currorder = $sCurrOrder , orderorder = $sCurrOrderOrder<br>";
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sDateFrom=$sDateFrom&sDateTo=$sDateTo&iRepId=$iRepId&sViewReport=1";

		if ($sViewReport) {

			$sIOReportQuery = "SELECT I.*, N.userName, N.firstName, N.lastName, C.companyName
								FROM io I, nbUsers N, partnerCompanies C 
								WHERE N.id = I.repId AND C.id = I.partnerId ";
			
			if($sDateFrom && $sDateTo){
				
				$aDateTo = explode('-',$sDateTo);
				$aDateFrom = explode('-',$sDateFrom);				
				
				$sDateToSQL = "$aDateTo[2]-$aDateTo[0]-$aDateTo[1]";//strftime('%Y-%m-%d', strtotime($sDateTo));
				$sDateFromSQL = "$aDateFrom[2]-$aDateFrom[0]-$aDateFrom[1]";//strftime('%Y-%m-%d', strtotime($sDateFrom));
				
				$sIOReportQuery .= "AND I.dateGenerated between '$sDateFromSQL' AND '$sDateToSQL' ";
			} else {
				if($sDateFrom){	
					$sDateFromSQL = strftime('%Y-%m-%d', strtotime($sDateFrom));
					$sIOReportQuery .= "AND I.dateGenerated >= '$sDateFromSQL' ";
				}
				if($sDateTo){			
					$sDateToSQL = strftime('%Y-%m-%d', strtotime($sDateTo));
					$sIOReportQuery .= "AND I.dateGenerated < '$sDateToSQL' ";	
				}
			}
			
			if($sType){
				$sIOReportQuery .= "AND I.type = '$sType' ";
			}
			
			if($sTemplate){
				$sIOReportQuery .= "AND I.template = '$sTemplate' ";
			}
			
			if($iAccountRep){
				$sIOReportQuery .= "AND I.repId = '$iAccountRep' ";
			}
			
			if($sPartner){
				$sIOReportQuery .= "AND C.companyName LIKE '".($bPartnerNameExact ? '' : '%')."$sPartner" . ($bPartnerNameExact ? '' : '%') . "' ";
			}
			
				
		
		
		//sorting here
		if($sCurrOrder == 'repId'){
			$sIOReportQuery .= "ORDER BY N.lastName $sCurrOrderOrder ";
		} else if($sCurrOrder == 'partnerName'){
			$sIOReportQuery .= "ORDER BY C.companyName $sCurrOrderOrder ";
		} else if($sCurrOrder != ''){
			$sIOReportQuery .= "ORDER BY I.$sCurrOrder $sCurrOrderOrder ";
		}

		//echo $sIOReportQuery."<br>";
		
		$aReportArray = array();
		$aReportArray['dateGenerated'] = array();
		$aReportArray['type'] = array();
		$aReportArray['template'] = array();
		$aReportArray['userName'] = array();
		$aReportArray['firstName'] = array();
		$aReportArray['LastName'] = array();
		$aReportArray['repId'] = array();
		$aReportArray['ioId'] = array();		
		
		$sReportContent = '';	
		
		$rIOReport = dbQuery($sIOReportQuery);
		while($oRow = dbFetchObject($rIOReport)){
			
			$d = substr($oRow->dateGenerated,6,2);
			$m = substr($oRow->dateGenerated,4,2);
			$y = substr($oRow->dateGenerated,0,4);
			
			
			$aReportArray['dateGenerated'][count($aReportArray['dateGenerated'])] = "$m/$d/$y";
			$aReportArray['type'][count($aReportArray['type'])] = $oRow->type;
			$aReportArray['template'][count($aReportArray['template'])] = $oRow->template;
			$aReportArray['userName'][count($aReportArray['userName'])] = $oRow->userName;
			$aReportArray['repId'][count($aReportArray['repId'])] = $oRow->repId;
			$aReportArray['companyName'][count($aReportArray['companyName'])] = $oRow->companyName;
			$aReportArray['ioId'][count($aReportArray['ioId'])] = $oRow->id;
			
			$sReportContent .= "<tr>
			<td color='#000000'>$oRow->id</td>
			<td color='#000000'>$m/$d/$y</td>
			<td color='#000000'>$oRow->companyName</td>
			<td color='#000000'>$oRow->firstName $oRow->lastName ($oRow->userName)</td></tr>\n";		
			
		}


		//}//reporting turned off, end
	}

	$sRepQuery = "SELECT id, firstName, userName
				 FROM   nbUsers
				 ORDER BY userName";

	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	$sAccountRepOptions = "<option value=''>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ($iAccountRep == $oRepRow->id) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}

		$sAccountRepOptions .= "<option value='$oRepRow->id' $sSelected>$oRepRow->firstName $oRepRow->lastName ($oRepRow->userName)";
	}


	//if ($sShowQueries == 'Y') {

		$sQueries = "<b>Queries Used To Prepare This Report:</b>
					 <BR><BR><b>Report Query:</b><BR>".$sIOReportQuery;
	//}


	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);


//echo $bugOut;

?>
<script language=JavaScript>

function funcAllSource() {
	if (document.form1.sAllSourceCodes.checked) {
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
	} else {
		document.form1.sSourceCode.disabled = false;
	}
}

function funcAllPartner() {
	if (document.form1.sAllPartnerCodes.checked) {
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
	} else {
		document.form1.sPartnerCode.disabled = false;
	}
	
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td><?php echo $sCampaignsLink;?></td></tr>
	<tr><td>Date From</td><td><input type=textbox name=sDateFrom Value='<?php echo $sDateFrom;?>' onChange='document.form1.submit();'></td></tr>
	<tr><td>Date To</td><td><input type=textbox name=sDateTo Value='<?php echo $sDateTo;?>' onChange='document.form1.submit();'></td></tr>	
	<tr><td>Type</td><td><select name=sType>
			<option value='' <?php echo ($sType == '' ? 'selected' : '');?>>All
			<option value='AMP' <?php echo ($sType == 'AMP' ? 'selected' : '');?>>Ampere Media
			<option value='SC' <?php echo ($sType == 'SC' ? 'selected' : '');?>>Siver Carrot
			</select></td></tr>
			
	<tr><td>Template Used</td>
		<td><select name='sTemplate'>
			<option value='' <?php echo ($sTemplate == '' ? 'selected' : '');?>>All</option>
			<option value='AMP_API.txt' <?php echo ($sTemplate == 'AMP_API.txt' ? 'selected' : '');?>>Ampere Media - API</option>
			<option value='AMP_emailJoin.txt' <?php echo ($sTemplate == 'AMP_emailJoin.txt' ? 'selected' : '');?>>Ampere Media - Email Join</option>
			<option value='AMP_emailOT.txt' <?php echo ($sTemplate == 'AMP_emailOT.txt' ? 'selected' : '');?>>Ampere Media - Email OT</option>
			<option value='AMP_join.txt' <?php echo ($sTemplate == 'AMP_join.txt' ? 'selected' : '');?>>Ampere Media - Join</option>
			<option value='AMP_OT.txt' <?php echo ($sTemplate == 'AMP_OT.txt' ? 'selected' : '');?>>Ampere Media - One Time</option>
			<option value='AMP_revshareemail.txt' <?php echo ($sTemplate == 'AMP_revshareemail.txt' ? 'selected' : '');?>>Ampere Media - Rev. Share Email</option>
			<option value='AMP_tieredPrivateLabel.txt' <?php echo ($sTemplate == 'AMP_tieredPrivateLabel.txt' ? 'selected' : '');?>>Ampere Media - Tiered Private Label</option>
			<option value='SC_IO.txt' <?php echo ($sTemplate == 'SC_IO.txt' ? 'selected' : '');?>>Silver Carrot</option>
			</select>
		</td>
	</tr>
	<tr><Td>Account Executive</td><td><select name=iAccountRep><?php echo $sAccountRepOptions;?></select> </td></tr>
	<tr><td>Partner Name</td>
		<td><input name='sPartner' value='<?php echo $sPartner;?>'>&nbsp; <input name='bPartnerNameExact' type='checkbox' <?php echo ($bPartnerNameExact ? 'checked' : ''); ?>>Exact Match</td>
	</td></tr>
	<tr><td colspan=2><input type=submit name=sViewReport value='View Report'></td></tr>
	<!--<input type=submit name=sPrintReport value='Print This Report'></td></tr>-->
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
	<tr><td colspan=12 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=12 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
	
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=ioId&sOldOrder=<?php echo ($sCurrOrder == 'id'? 'ioId': $sCurrOrder);?>&sCurrOrderOrder=<?php echo $sCurrOrderOrder;?>" class=header>#</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=dateGenerated&sOldOrder=<?php echo ($sCurrOrder == 'id'? 'ioId': $sCurrOrder);?>&sCurrOrderOrder=<?php echo $sCurrOrderOrder;?>" class=header>Date Generated</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=partnerName&sOldOrder=<?php echo ($sCurrOrder == 'id'? 'ioId': $sCurrOrder);?>&sCurrOrderOrder=<?php echo $sCurrOrderOrder;?>" class=header>Partner Name</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=repId&sOldOrder=<?php echo ($sCurrOrder == 'id'? 'ioId': $sCurrOrder);?>&sCurrOrderOrder=<?php echo $sCurrOrderOrder;?>" class=header>Account Executive</a></td>
	</tr>
	
	<?php
		echo $sReportContent;
	?>

	<!--
	<tr><td colspan=12 class=header><BR>Notes -</td></tr>
	<tr><td colspan=12>Counts will change as postal verification status changes.
				<BR><BR>If you change the date range, you need to click the button once again to view the report.
				<BR><BR>e1 Captures are the gross number of emails submitted through an e1 form that pass front end bounds checks.
				<BR><BR>Gross Unique Users: This is the count of distinct email from otData/otDataHistory group by sourceCode.
				<BR><BR>Total Offers Taken: This is the count of email from otData/otDataHistory group by sourceCode.
				<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads if "Include 3401 Test Leads" is not checked. 
						Test leads can be included only in today's report and deleted next day.
				<BR><BR>For history report, counts only reflects records where PV attempted. For today's report, report reflects gross counts.</td></tr>	
	<tr><td colspan=10>Gross Unique Users in Source Analysis Report may be higher than gross unique users in Campaign Analysis Report
					because in Source Analysis Report user will be unique for a source code and same user may be unique user 
					for another source code also if he came up in our site through different source codes resulting the total unique user count higher than 
					Campaign Analysis Report.<BR><BR>				
					-->	
	<tr><td colspan=10>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<!--<tr><td colspan=12><BR><BR></td></tr>-->
	<tr><td colspan=12><?php echo $sQueries; ?></td></tr>
	
	<tr><td colspan=12><BR><BR></td></tr>
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

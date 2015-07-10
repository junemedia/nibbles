<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Popups Mgmnt";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM popups WHERE  id = $iId";
		$rResult = dbQuery($sDeleteQuery);
		$iId = '';
				
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
	
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "popupType";
		$sPopUpTypeOrder = "SORT_ASC";
	}

	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "popupType" :
			$sCurrOrder = $sPopUpTypeOrder;
			$sPopUpTypeOrder = ($sPopUpTypeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "popName" :
			$sCurrOrder = $sPopNameOrder;
			$sPopNameOrder = ($sPopNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "upUnder" :
			$sCurrOrder = $sUpUnderOrder;
			$sUpUnderOrder = ($sUpUnderOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "flowName" :
			$sCurrOrder = $sFlowNameOrder;
			$sFlowNameOrder = ($sFlowNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "pageNo" :
			$sCurrOrder = $sPageNoOrder;
			$sPageNoOrder = ($sPageNoOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "startDate" :
			$sCurrOrder = $sStartOrder;
			$sStartOrder = ($sStartOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "endDate" :
			$sCurrOrder = $sEndDateOrder;
			$sEndDateOrder = ($sEndDateOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "userName" :
			$sCurrOrder = $sUserNameOrder;
			$sUserNameOrder = ($sUserNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "dateTimeAdded" :
			$sCurrOrder = $sDateTimeAddedOrder;
			$sDateTimeAddedOrder = ($sDateTimeAddedOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
		}
	}

	if ($sCurrOrder == 'SORT_DESC') {
		$sCurrOrder = SORT_DESC;
	} else {
		$sCurrOrder = SORT_ASC;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
	
	$sSelectQuery = "SELECT * FROM popups 
					WHERE popType !=''
					ORDER BY popType ASC";
	$rSelectResult = dbQuery($sSelectQuery);
	$i = 0;
	while ($oRow = dbFetchObject($rSelectResult)) {
		$sDateTimeAdded = $oRow->dateTimeAdded;
		$sUserName = $oRow->userName;
		
		if ($oRow->popType=='S') {
			$sPopupType = 'Standard';
		} elseif ($oRow->popType=='E') {
			$sPopupType = 'Exit';
		} elseif ($oRow->popType=='A') {
			$sPopupType = 'Abandoned';
		} elseif ($oRow->popType=='W') {
			$sPopupType = 'Window Manager';
		}
		
		$sStartDate = $oRow->startDate;
		$sEndDate = $oRow->endDate;

		if ($oRow->startDate == '0000-00-00') {
			$sStartDate = '';
		} elseif ($oRow->endDate == '0000-00-00') {
			$sEndDate = '';
		}
		
		$sFlowName = '';
		$iPageNo = '';
		if ($oRow->triggerPop != '') {
			$sTriggerPop = explode(',',$oRow->triggerPop);
			$iPageNo = $sTriggerPop[1];

			$sGetFlowName = "SELECT flowName from flows WHERE id='".$sTriggerPop[0]."'";
			$rFlowResult = dbQuery($sGetFlowName);
			while ($oRow1 = dbFetchObject($rFlowResult)) {
				$sFlowName = $oRow1->flowName;
			}
		}
		
		$aReportArray['sPopupType'][$i] = $sPopupType;
		$aReportArray['sPopName'][$i] = $oRow->popName;
		$aReportArray['sPopUpUnder'][$i] = $oRow->popUpUnder;
		$aReportArray['sFlowName'][$i] = $sFlowName;
		$aReportArray['iPageNo'][$i] = $iPageNo;
		$aReportArray['sStartDate'][$i] = $sStartDate;
		$aReportArray['sEndDate'][$i] = $sEndDate;
		$aReportArray['sUserName'][$i] = $sUserName;
		$aReportArray['sDateTimeAdded'][$i] = $sDateTimeAdded;
		
		$aReportArray['sLink'][$i] = $oRow->id;
		$i++;
	}
	
	if (count($aReportArray['sPopupType']) > 0) {
		switch ($sOrderColumn) {
			case "popupType" :
			array_multisort($aReportArray['sPopupType'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "popName" :
			array_multisort($aReportArray['sPopName'], $sCurrOrder, $aReportArray['sPopupType'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "upUnder" :
			array_multisort($aReportArray['sPopUpUnder'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopupType'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "flowName" :
			array_multisort($aReportArray['sFlowName'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sPopupType'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "pageNo" :
			array_multisort($aReportArray['iPageNo'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['sPopupType'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "startDate" :
			array_multisort($aReportArray['sStartDate'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sPopupType'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "endDate" :
			array_multisort($aReportArray['sEndDate'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sPopupType'], $aReportArray['sUserName'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "userName" :
			array_multisort($aReportArray['sUserName'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sPopupType'], $aReportArray['sDateTimeAdded'], $aReportArray['sLink']);
			break;
			case "dateTimeAdded" :
			array_multisort($aReportArray['sDateTimeAdded'], $sCurrOrder, $aReportArray['sPopName'], $aReportArray['sPopUpUnder'], $aReportArray['sFlowName'], $aReportArray['iPageNo'], $aReportArray['sStartDate'], $aReportArray['sEndDate'], $aReportArray['sUserName'], $aReportArray['sPopupType'], $aReportArray['sLink']);
		}
	}
	
	
	
	$sList = '';
	for ($i=0; $i<count($aReportArray['sPopupType']); $i++) {
		$iTempId = $aReportArray['sLink'][$i];
		if ($sBgcolorClass == 'ODD') {
			$sBgcolorClass = 'EVEN';
		} else {
			$sBgcolorClass = 'ODD';
		}
		
		$sList .= "<tr class=$sBgcolorClass><td>".$aReportArray['sPopupType'][$i]."</td>
				<td>".$aReportArray['sPopName'][$i]."</td>
				<td>".$aReportArray['sPopUpUnder'][$i]."</td>
				<td>".$aReportArray['sFlowName'][$i]."</td>
				<td>".$aReportArray['iPageNo'][$i]."</td>
				<td>".$aReportArray['sStartDate'][$i]."</td>
				<td>".$aReportArray['sEndDate'][$i]."</td>
				<td>".$aReportArray['sUserName'][$i]."</td>
				<td>".$aReportArray['sDateTimeAdded'][$i]."</td>
				<td><a href='JavaScript:void(window.open(\"addPop.php?iMenuId=$iMenuId&id=".$iTempId."\", \"AddContent\", \"height=400, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$iTempId);' >Delete</a></td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addPop.php?iMenuId=$iMenuId\", \"\", \"height=400, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>";
	include("../../includes/adminHeader.php");
	
	$sPopupsExclusionlink = "<a href='/admin/popupsMgmnt/linkPopUpExclude.php?iMenuId=251'>Manage Popups Exclusion</a>";

	?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();								
		}
	}						
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=9 align=left><?php echo $sAddButton;?><div align=right><?php echo $sPopupsExclusionlink; ?></div></td></tr>
<tr><td colspan=9>&nbsp;</td></tr>
<tr>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=popupType&sPopUpTypeOrder=<?php echo $sPopUpTypeOrder;?>" class=header>Popup Type</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=popName&sPopNameOrder=<?php echo $sPopNameOrder;?>" class=header>Pop Name</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=upUnder&sUpUnderOrder=<?php echo $sUpUnderOrder;?>" class=header>Pop Up/Under</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=flowName&sFlowNameOrder=<?php echo $sFlowNameOrder;?>" class=header>Flow Name</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=pageNo&sPageNoOrder=<?php echo $sPageNoOrder;?>" class=header>Page No.</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=startDate&sStartOrder=<?php echo $sStartOrder;?>" class=header>Start Date</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=endDate&sEndDateOrder=<?php echo $sEndDateOrder;?>" class=header>End Date</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=userName&sUserNameOrder=<?php echo $sUserNameOrder;?>" class=header>User Name</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=dateTimeAdded&sDateTimeAddedOrder=<?php echo $sDateTimeAddedOrder;?>" class=header>Date/Time Added</a></td>
</tr>
<?php echo $sList;?>
<tr><td colspan=9 align=left><?php echo $sAddButton;?></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
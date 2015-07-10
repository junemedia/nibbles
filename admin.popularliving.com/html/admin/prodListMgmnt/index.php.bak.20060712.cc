<?php

/*********
Script to List/Delete OT Pages
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sToday = date('Y')."-".date('m')."-".date('d');
$sTomorrow = DateAdd("d", 1, date('Y')."-".date('m')."-".date('d'));
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$iCharPerLineComments = 50;
$iCharPerLineOfferPage = 20;

$sPageTitle = "Nibbles Production Sheet - List/Delete Request";

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		// if record deleted
		
		$sSelectQuery = "SELECT *
						 FROM   productionList
						 WHERE  id = '$iId'";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			//offerType IN ('Co-Brands', 'New Offers', 'Changes To Existing Offers')
			$sRequestType = $oSelectRow->requestType;
		}
		
		$sDeleteQuery = "DELETE FROM productionList
	 			   		WHERE  id = '$iId'"; 

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {			
			echo dbError();
		}
		$iId = '';
	}
	

	
	include("../../includes/adminHeader.php");
		
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "priority";
		$sPriorityOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		case "request" :
		$sCurrOrder = $sRequestOrder;
		$sRequestOrder = ($sRequestOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateEntered" :
		$sCurrOrder = $sDateEnteredOrder;
		$sDateEnteredOrder = ($sDateEnteredOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "owner" :
		$sCurrOrder = $sOwnerOrder;
		$sOwnerOrder = ($sOwnerOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "requestType" :
		$sCurrOrder = $sRequestTypeOrder;
		$sRequestTypeOrder = ($sRequestTypeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "offerPage" :
		$sCurrOrder = $sOfferPageOrder;
		$sOfferPageOrder = ($sOfferPageOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "comments" :
		$sCurrOrder = $sCommentsOrder;
		$sCommentsOrder = ($sCommentsOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "hours" :
		$sCurrOrder = $sHoursOrder;
		$sHoursOrder = ($sHoursOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "estimateDate" :
		$sCurrOrder = $sEstimateDateOrder;
		$sEstimateDateOrder = ($sEstimateDateOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$sCurrOrder = $sPriorityOrder;
		$sPriorityOrder = ($sPriorityOrder != "DESC" ? "DESC" : "ASC");
	}
	
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	// Query to get the list
	$sSelectQuery = "SELECT *
					FROM   productionList
					WHERE  (status = 'scheduled' || status = 'unknownSchedule')
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rResult = dbQuery($sSelectQuery);
	echo dbError();
	
	if ($rResult) {
		while ($oRow = dbFetchObject($rResult)) {
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
					
			$iPriority = $oRow->priority;
			$sMarkRequest = '&nbsp;';
			if ($oRow->priorityChanged == 'Up' || 
				($oRow->priorityChanged == 'Down' && ($oRow->estimateDate == $sToday || $oRow->estimateDate == $sTomorrow) )) {
					$sMarkRequest = "*";
			}
			
			$sComments = '';
			$sComments = wordwrap($oRow->comments,$iCharPerLineComments,"<br>",1);
			
			$sOfferPage = '';
			$sOfferPage = wordwrap($oRow->offerPage,$iCharPerLineOfferPage,"<br>",1);

			$sProductionList .= "<tr class=$sBgcolorClass><td>$sMarkRequest $oRow->priority</td>
					<td>$oRow->request</td>
					<td>$oRow->dateEntered</td>
					<td>$oRow->owner</td>					
					<td>$oRow->requestType</td>
					<td>$sOfferPage</td>
					<td>$sComments</td>
					<td>$oRow->hours</td>";
			if ($oRow->status == 'unknownSchedule') {
				$sTempEstimateDate = "?";	
			} else {
				$sTempEstimateDate = $oRow->estimateDate;
			}
			
			$sProductionList .= "<td align=center nowrap>$sTempEstimateDate</td><td nowrap>";
			$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
			$sProductionList .= "</td></tr>";
		}
	}
	
	// Query to get awaiting approval list
	$sSelectQuery2 = "SELECT *
					FROM   productionList
					WHERE  status = 'awaitingApproval'
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rResult2 = dbQuery($sSelectQuery2);

	$sProductionList .= "<tr><td colspan=7 class=header><BR>Requests Awaiting Approval</td></tr>";
	
	while ($oRow2 = dbFetchObject($rResult2)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sComments = '';
		$sComments = wordwrap($oRow2->comments,$iCharPerLineComments,"<br>",1);
		
		$sOfferPage = '';
		$sOfferPage = wordwrap($oRow2->offerPage,$iCharPerLineOfferPage,"<br>",1);
				
		$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow2->priority</td>
			<td>$oRow2->request</td>
			<td nowrap>$oRow2->dateEntered</td>
			<td>$oRow2->owner</td>					
			<td>$oRow2->requestType</td>					
			<td>$sOfferPage</td>
			<td>$sComments</td>
			<td>$oRow2->hours</td>
			<td nowrap>$oRow2->estimateDate</td>
			<td nowrap>";
		$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow2->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
		$sProductionList .= "</td></tr>";					
	}
		
	// display new request only if creditStatus is "ok"
	// Query to get new requests list
	$sSelectQuery3 = "SELECT productionList.*, offerCompanies.creditStatus
					FROM   productionList 
					LEFT JOIN offers ON (offers.offerCode = productionList.request)
					LEFT JOIN offerCompanies ON (offers.companyId = offerCompanies.id)
					WHERE  status = 'newRequest'
					AND  (requestType = 'New Offer'
					OR requestType='Changes To Existing Offer')
					AND offerCompanies.creditStatus = 'ok'
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rResult3 = dbQuery($sSelectQuery3);

	$sProductionList .= "<tr><td colspan=7 class=header><BR>New Production Requests - Credit Approved</td></tr>";
	
	while ($oRow3 = dbFetchObject($rResult3)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sComments = '';
		$sComments = wordwrap($oRow3->comments,$iCharPerLineComments,"<br>",1);
		
		$sOfferPage = '';
		$sOfferPage = wordwrap($oRow3->offerPage,$iCharPerLineOfferPage,"<br>",1);
				
		$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow3->priority</td>
			<td>$oRow3->request</td>
			<td nowrap>$oRow3->dateEntered</td>
			<td>$oRow3->owner</td>					
			<td>$oRow3->requestType</td>					
			<td>$sOfferPage</td>
			<td>$sComments</td>
			<td>$oRow3->hours</td>
			<td nowrap>$oRow3->estimateDate</td>
			<td nowrap>";

		$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow3->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";		
		$sProductionList .= "</td></tr>";					
	}
	
	
	// get new campaign and changes to current campaign request.
	$sSelectQuery13 = "SELECT * FROM   productionList
					WHERE  status = 'newRequest'
					AND requestType IN ('New Campaign','Changes To Existing Campaign')
					ORDER BY $sOrderColumn $sCurrOrder ";
	$rResult13 = dbQuery($sSelectQuery13);
	while ($oRow13 = dbFetchObject($rResult13)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sComments = '';
		$sComments = wordwrap($oRow13->comments,$iCharPerLineComments,"<br>",1);
		
		$sOfferPage = '';
		$sOfferPage = wordwrap($oRow13->offerPage,$iCharPerLineOfferPage,"<br>",1);
				
		$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow13->priority</td>
			<td>$oRow13->request</td>
			<td nowrap>$oRow13->dateEntered</td>
			<td>$oRow13->owner</td>					
			<td>$oRow13->requestType</td>					
			<td>$sOfferPage</td>
			<td>$sComments</td>
			<td>$oRow13->hours</td>
			<td nowrap>$oRow13->estimateDate</td>
			<td nowrap>";

		$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow13->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";		
		$sProductionList .= "</td></tr>";					
	}
	
	
	
	// display new request only if creditStatus is "hold" or "" (blank)
	// Query to get new requests list
	$sSelectQuery5 = "SELECT productionList.*, offerCompanies.creditStatus
					FROM   productionList 
					LEFT JOIN offers ON (offers.offerCode = productionList.request)
					LEFT JOIN offerCompanies ON (offers.companyId = offerCompanies.id)
					WHERE  status = 'newRequest'
					AND  (requestType = 'New Offer'
					OR requestType='Changes To Existing Offer')
					AND offerCompanies.creditStatus != 'ok'
					ORDER BY $sOrderColumn $sCurrOrder";

	$rResult5 = dbQuery($sSelectQuery5);

	$sProductionList .= "<tr><td colspan=7 class=header><BR>New Production Requests - Credit Not Approved</td></tr>";
	while ($oRow5 = dbFetchObject($rResult5)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sComments = '';
		$sComments = wordwrap($oRow5->comments,$iCharPerLineComments,"<br>",1);
		
		$sOfferPage = '';
		$sOfferPage = wordwrap($oRow5->offerPage,$iCharPerLineOfferPage,"<br>",1);
		
		$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow5->priority</td>
			<td>$oRow5->request</td>
			<td nowrap>$oRow5->dateEntered</td>
			<td>$oRow5->owner</td>					
			<td>$oRow5->requestType</td>					
			<td>$sOfferPage</td>
			<td>$sComments</td>
			<td>$oRow5->hours</td>
			<td nowrap>$oRow5->estimateDate</td>
			<td nowrap>";
		$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow5->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";		
		$sProductionList .= "</td></tr>";
	}
	
	
	// display new co-brand request
	// Query to get new co-brand requests list
	$sSelectQuery6 = "SELECT *
					FROM   productionList
					WHERE  status = 'newRequest'
					AND (requestType = 'New Co-Brand'
					OR requestType= 'Changes To Existing Co-Brand'
					OR requestType= 'Other')
					ORDER BY $sOrderColumn $sCurrOrder";
	$rResult6 = dbQuery($sSelectQuery6);
	$sProductionList .= "<tr><td colspan=7 class=header><BR>New Co-Brand Requests</td></tr>";
	while ($oRow6 = dbFetchObject($rResult6)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sComments = '';
		$sComments = wordwrap($oRow6->comments,$iCharPerLineComments,"<br>",1);
		
		$sOfferPage = '';
		$sOfferPage = wordwrap($oRow6->offerPage,$iCharPerLineOfferPage,"<br>",1);
		
		$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow6->priority</td>
			<td>$oRow6->request</td>
			<td nowrap>$oRow6->dateEntered</td>
			<td>$oRow6->owner</td>					
			<td>$oRow6->requestType</td>					
			<td>$sOfferPage</td>
			<td>$sComments</td>
			<td>$oRow6->hours</td>
			<td nowrap>$oRow6->estimateDate</td>
			<td nowrap>";
		$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow6->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";		
		$sProductionList .= "</td></tr>";
	}
	
	
	// Query to get deleted offers list
	if ($sShowDeleted == 'Y') {
		$sSelectQuery4 = "SELECT *
						FROM   productionList
						WHERE  status = 'deleted'
						ORDER BY $sOrderColumn $sCurrOrder";
		$rResult4 = dbQuery($sSelectQuery4);
		$sProductionList .= "<tr><td colspan=7 class=header><BR>Deleted Requests</td></tr>";
		while ($oRow4 = dbFetchObject($rResult4)) {
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sComments = '';
			$sComments = wordwrap($oRow4->comments,$iCharPerLineComments,"<br>",1);
			
			$sOfferPage = '';
			$sOfferPage = wordwrap($oRow4->offerPage,$iCharPerLineOfferPage,"<br>",1);
					
			$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow4->priority</td>
				<td>$oRow4->request</td>
				<td nowrap>$oRow4->dateEntered</td>
				<td>$oRow4->owner</td>					
				<td>$oRow4->requestType</td>					
				<td>$sOfferPage</td>
				<td>$sComments</td>
				<td nowrap>$oRow4->estimateDate</td>
				<td nowrap>";
			$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow4->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
			$sProductionList .= "</td></tr>";
		}
	}
	
	// Query to get completed offers list
	if ($sShowCompleted == 'Y') {
		$sSelectQuery4 = "SELECT *
						FROM   productionList
						WHERE  status = 'completed'
						ORDER BY $sOrderColumn $sCurrOrder";
		$rResult4 = dbQuery($sSelectQuery4);
		$sProductionList .= "<tr><td colspan=7 class=header><BR>Completed Requests</td></tr>";
		while ($oRow4 = dbFetchObject($rResult4)) {
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sComments = '';
			$sComments = wordwrap($oRow4->comments,$iCharPerLineComments,"<br>",1);
			
			$sOfferPage = '';
			$sOfferPage = wordwrap($oRow4->offerPage,$iCharPerLineOfferPage,"<br>",1);
					
			$sProductionList .= "<tr class=$sBgcolorClass><td>$oRow4->priority</td>
				<td>$oRow4->request</td>
				<td nowrap>$oRow4->dateEntered</td>
				<td>$oRow4->owner</td>					
				<td>$oRow4->requestType</td>					
				<td>$sOfferPage</td>
				<td>$sComments</td>
				<td nowrap>$oRow4->estimateDate</td>
				<td nowrap>";
				$sProductionList .= "<a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oRow4->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
				$sProductionList .= "</td></tr>";
		}
	}
	
	
	// Display Add Button
	// DO NOT ALLOW SALES DEPT HERE
	if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart') {
		$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		$sRecalculate = "<tr><td colspan=7>Today's remaining hours to work &nbsp; <input type=text name=iTodaysHours size=2 maxlength=2 value='6'> &nbsp; &nbsp; &nbsp;
				 <input type=submit name=sCalculate value='Recalculate' ></td></tr>";				
	}  else {
		$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"request.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";		
	}

	if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart') {
		// default assumptions
		$sAssumptions = "<tr><td colspan=9 class=header>Default Assumptions</td></tr>
						<tr><td colspan=9>Daily Working Hours - ".getVar('dailyWorkHours'). "</td></tr>
						<tr><td colspan=9>New Co-Brand - ".getVar('newCoBrandWorkHours'). " hour(s)</td></tr>
						<tr><td colspan=9>New Request - ".getVar('newOfferWorkHours'). " hour(s)</td></tr>
						<tr><td colspan=9>Changes to Existing Co-Brand - ".getVar('changesToExistingCoBrandWorkHours'). " hour(s)</td></tr>
						<tr><td colspan=9>Changes To Existing Offer - ".getVar('changesToExistingOfferWorkHours'). " hour(s)</td></tr>
						<tr><td colspan=9>Other - ".getVar('otherWorkHours'). " hour(s)</td></tr>
						<tr><td colspan=9></td></tr>
						";
			
		$sAssumptions .= "<tr><td colspan=9 class=header>Datewise Assumptions</td></tr>
						<tr><td colspan=2 class=header>Date</td>
									<td class=header>Work Hours</td>
									<td colspan=6 class=header>Description</td></tr>";
		
		$sAssumptionQuery = "SELECT *
							 FROM	productionListAssumptions
							 ORDER BY workDate";
		$rAssumptionResult = dbQuery($sAssumptionQuery);
		while ($oAssumptionRow = dbFetchObject($rAssumptionResult)) {
			$sAssumptions .= "<tr><td colspan=2>$oAssumptionRow->workDate</td>
							<td>$oAssumptionRow->workHours</td>
							<td colspan=6>$oAssumptionRow->description</td></tr>";
		}
	}
		
	if ($sShowDeleted == '') {
		$sDeletedRequestsLink = "<a href='$sSortLink&sShowDeleted=Y'>Show Deleted Requests</a>";
	} else {
		$sDeletedRequestsLink = "<a href='$sSortLink'>Hide Deleted Requests</a>";
	}
	
	if ($sShowCompleted == '') {
		$sCompletedRequestsLink = "<a href='$sSortLink&sShowCompleted=Y'>Show Completed Requests</a>";
	} else {
		$sCompletedRequestsLink = "<a href='$sSortLink'>Hide Completed Requests</a>";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
		
	?>

		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=9><?php echo $sAddButton;?> &nbsp; &nbsp; &nbsp; <?php echo $sDeletedRequestsLink;?>&nbsp; &nbsp; &nbsp; <?php echo $sCompletedRequestsLink;?></td>	
</tr>

<tr>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=priority&sPriorityOrder=<?php echo $sPriorityOrder;?>" class=header>Priority</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=request&sRequestOrder=<?php echo $sRequestOrder;?>" class=header>Request</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=dateEntered&sDateEnteredOrder=<?php echo $sDateEnteredOrder;?>" class=header>Date Entered</a></td>	
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=owner&sOwnerOrder=<?php echo $sOwnerOrder;?>" class=header>Owner</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=requestType&sRequestTypeOrder=<?php echo $sRequestTypeOrder;?>" class=header>Request Type</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=offerPage&sOfferPageOrder=<?php echo $sOfferPageOrder;?>" class=header>Offer Page</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=comments&sCommentsOrder=<?php echo $sCommentsOrder;?>" class=header>Comments</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=hours&sHoursOrder=<?php echo $sHoursOrder;?>" class=header>Hours</a></td>
	<td align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=estimateDate&sEstimateDateOrder=<?php echo $sEstimateDateOrder;?>" class=header>Estimate Date</a></td>
<!--	<td align=left class=header>Old Estimate Date</td>-->
	<td>&nbsp; </td>
</tr>
<?php echo $sProductionList;?>
<tr><td colspan=9><?php echo $sAddButton;?></td></tr>
</form>
<form name=form2 action='recalculate.php'>
<?php echo $sRecalculate; ?>
</form>
<?php echo $sAssumptions;?>
<!--<BR>Estimate date will not be recalculated automatically. IT will recalculate estimate date whenever necessary.-->
<tr><td colspan=9 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=9>Generally speaking we take things in the following order: changes to existing offers, co-brands, and then new offers.
					<BR>Generally speaking offers, cobrands, etc., already in progress are not bumped.
					<BR>The above are general guidelines and are varied as neccessary with approval from CTO or President of company.
					<BR>* shows request's estimate date moved in or moved out from last two days.
					<BR>Possible request status are - New Request, Scheduled, Unknown Schedule, Awaiting Approval, Completed and Deleted. 
					<BR>Estimate dates will be recalculated only when Recalculate button clicked.
					<BR>Datewise assumptions for old dates will be deleted when Recalculate button clicked.
	<tr><td colspan=9><BR><BR></td></tr>
	
</table>


<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
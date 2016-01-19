<?php

/*********

Script to Display 

**********/

    
include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Quick Lead LookUp";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	

	
		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

$sViewReport = stripslashes($sViewReport);

if (! $sViewReport) {
	$sViewReport = "Today's Report";
}

if ($sViewReport != "Today's Report") {
	
	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		}
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = "01";
		}
		
	
	
} else  {
	$iYearFrom = date('Y');
	$iMonthFrom = date('m');
	$iDayFrom = date('d');
		
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
	$iYearTo = $iYearFrom;
	
}


// check the delete rights
	if ( $sDelete ) {
		if (hasAccessRight('62') || isAdmin()) {
			if ($sViewReport != "Today's Report") {
			
				$sDeleteQuery = "DELETE FROM otDataHistory
								 WHERE  id = '$iId'";
			
			} else {
				
				$sDeleteQuery = "DELETE FROM otData
								 WHERE  id = '$iId'";
			
			}
			
			$rDeleteResult = dbQuery($sDeleteQuery); 
			if ($rDeleteResult) {
				$sMessage = "Lead Record Deleted...";
			}
			
		} else {
			
			$sMessage = "You Are Not Authorized To Delete The Data...";
		}
		
	}
	
	

	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
			
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}
		
	
if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {			
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";	
	
	
	// Set Default order column
	if (!($sOrderColumn)) {
	if (!($sOrderColumn)) {
		$sOrderColumn = "o.dateTimeAdded";
		$sDateTimeAddedOrder = "DESC";
	}
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
	switch ($sOrderColumn) {
		case "offerCode" :
		$sCurrOrder = $sOfferCodeOrder;
		$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sourceCode" :
		$sCurrOrder = $sSourceCodeOrder;
		$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
		break;	
		case "repDesignated" :
		$sCurrOrder = $sRepDesignatedOrder;
		$sRepDesignatedOrder = ($sRepDesignatedOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "o.email" :
		$sCurrOrder = $sEmailOrder;
		$sEmailOrder = ($sEmailOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "first" :
		$sCurrOrder = $sFirstOrder;
		$sFirstOrder = ($sFirstOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "last" :
		$sCurrOrder = $sLastOrder;
		$sLastOrder = ($sLastOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "address" :
		$sCurrOrder = $sAddressOrder;
		$sAddressOrder = ($sAddressOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "city" :
		$sCurrOrder = $sCityOrder;
		$sCityOrder = ($sCityOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "state" :
		$sCurrOrder = $sStateOrder;
		$sStateOrder = ($sStateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "zip" :
		$sCurrOrder = $sZipOrder;
		$sZipOrder = ($sZipOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "phoneNo" :
		$sCurrOrder = $sPhoneNoOrder;
		$sPhoneNoOrder = ($sPhoneNoOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "page2Data" :
		$sCurrOrder = $sPage2DataOrder;
		$sPage2DataOrder = ($sPage2DataOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "remoteIp" :
		$sCurrOrder = $sRemoteIpOrder;
		$sRemoteIpOrder = ($sRemoteIpOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "processStatus" :
		$sCurrOrder = $sProcessStatusOrder;
		$sProcessStatusOrder = ($sProcessStatusOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "reasonCode" :
		$sCurrOrder = $sReasonCodeOrder;
		$sReasonCodeOrder = ($sReasonCodeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateTimeProcessed" :
		$sCurrOrder = $sDateTimeProcessedOrder;
		$sDateTimeProcessedOrder = ($sDateTimeProcessedOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sendStatus" :
		$sCurrOrder = $sSendStatusOrder;
		$sSendStatusOrder = ($sSendStatusOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateTimeSent" :
		$sCurrOrder = $sDateTimeSentOrder;
		$sDateTimeSentOrder = ($sDateTimeSentOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "howSent" :
		$sCurrOrder = $sHowSentOrder;
		$sHowSentOrder = ($sHowSentOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "realTimeResponse" :
		$sCurrOrder = $sRealTimeResponseOrder;
		$sRealTimeResponseOrder = ($sRealTimeResponseOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sDateTimeAddedOrder;
		$sDateTimeAddedOrder = ($sDateTimeAddedOrder != "DESC" ? "DESC" : "ASC");
	}
	}
	
	// Prepare filter part of the query if filter/exclude specified...
	
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		// offercode, email, phone, address, city, state, zip, first, last
		switch ($sSearchIn) {
			case "offerCode" :
			$sFilterPart .= ($iExactMatch) ? "o.offerCode = '$sFilter'" : "o.offerCode like '%$sFilter%'";
			break;
			case "sourceCode" :
			$sFilterPart .= ($iExactMatch) ? "sourceCode = '$sFilter'" : "sourceCode like '%$sFilter%'";
			break;
			case "repDesignated" :
			$sFilterPart .= ($iExactMatch) ? "repDesignated = '$sFilter'" : "repDesignated like '%$sFilter%'";
			break;
			case "email" :
			$sFilterPart .= ($iExactMatch) ? "o.email = '$sFilter'" : "o.email like '%$sFilter%'";
			break;
			case "first" :
			$sFilterPart .= ($iExactMatch) ? "first = '$sFilter'" : "first like '%$sFilter%'";
			break;
			case "last" :
			$sFilterPart .= ($iExactMatch) ? "last = '$sFilter'" : "last like '%$sFilter%'";
			break;
			case "phoneNo" :
			$sFilterPart .= ($iExactMatch) ? "phoneNo = '$sFilter'" : "phoneNo like '%$sFilter%'";
			break;
			case "address" :
			$sFilterPart .= ($iExactMatch) ? "address = '$sFilter'" : "address like '%$sFilter%'";
			break;
			case "city" :
			$sFilterPart .= ($iExactMatch) ? "city = '$sFilter'" : "city like '%$sFilter%'";
			break;
			case "state" :
			$sFilterPart .= ($iExactMatch) ? "state = '$sFilter'" : "state like '%$sFilter%'";
			break;
			case "zip" :
			$sFilterPart .= ($iExactMatch) ? "zip = '$sFilter'" : "zip like '%$sFilter%'";
			break;
			
			//	case "dateLastUpdated" :
			//$sFilterPart .= ($sExactMatch == 'Y') ? "dateLastUpdated = '$sFilter'" : "dateLsatUpdated like '%$sFilter%'";
			//break;
			default:
			$sFilterPart .= ($iExactMatch) ? "o.offerCode = '$sFilter' || sourceCode = '$sFilter' || repDesignated = '$sFilter' || o.email = '$sFilter' || first = '$sFilter' || 
							last = '$sFilter' || phoneNo = '$sFilter' || address = '$sFilter' || city = '$sFilter'
							|| state = '$sFilter' || zip = '$sFilter'" : " o.offerCode like '%$sFilter%' || sourceCode like '%$sFilter%' || repDesignated like '%$sFilter%' || o.email LIKE '%$sFilter%'
							 || first like '%$sFilter%' || last like '%$sFilter%' || phoneNo like '%$sFilter%'
							|| address like '%$sFilter%' || city like '%$sFilter%' || state like '%$sFilter%'
							|| zip like '%$sFilter%'";
		}
		
		$sFilterPart .= ") ";
	}
		
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sSearchIn) {
			case "offerCode" :
			$sFilterPart .= " o.offerCode NOT LIKE '%$sExclude%'";
			break;
			case "sourceCode" :
			$sFilterPart .= " sourceCode NOT LIKE '%$sExclude%'";
			break;
			case "repDesignated" :
			$sFilterPart .= " repDesignated NOT LIKE '%$sExclude%'";
			break;
			case "email" :
			$sFilterPart .= " o.email NOT LIKE '%$sExclude%'";
			break;
			case "first" :
			$sFilterPart .= " first NOT LIKE '%$sExclude%'";
			break;
			case "last" :
			$sFilterPart .= " last NOT LIKE '%$sExclude%'";
			break;
			case "phoneNo" :
			$sFilterPart .= " phoneNo NOT LIKE '%$sExclude%'";
			break;
			case "address" :
			$sFilterPart .= " address NOT LIKE '%$sExclude%'";
			break;
			case "city" :
			$sFilterPart .= " city NOT LIKE '%$sExclude%'";
			break;
			case "state" :
			$sFilterPart .= "state NOT LIKE '%$sExclude%'";
			break;
			case "zip" :
			$sFilterPart .= "zip NOT LIKE '%$sExclude%'";
			break;
			
			//	case "dateLastUpdated" :
			//	$sFilterPart .= "dateLastUpdated NOT LIKE '%$sExclude%'";
			//	break;
			default:
			$sFilterPart .= "o.offerCode NOT LIKE '%$sExclude%' && sourceCode NOT LIKE '%$sExclude%' 
							&& repDesinated NOT LIKE '%$sExclude%' && o.email NOT LIKE '%$sExclude%' && first NOT LIKE '%$sExclude%' 
							&& last NOT LIKE '%$sExclude%' && phoneNo NOT LIKE '%$sExclude%' 
							&& address NOT LIKE '%$sExclude%' && city NOT LIKE '%$sExclude%' 
							&& state NOT LIKE '%$sExclude%' && zip NOT LIKE '%$sExclude%'" ;
		}
		$sFilterPart .= " ) ";
		
	}
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 50;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage";
	
	
	
	// delete from temporary table
	$sDeleteQuery = "DELETE FROM tempQuickLeadLook";
	$rDeleteResult = dbQuery($sDeleteQuery);
	
	if ($sViewReport != 'History Report') {
		//$sLeadQuery = "SELECT *
    		//				  FROM    tempQuickLeadLook o
    			//			  WHERE   date_format(dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sDateFrom' AND '$sDateTo'
			//				  $sFilterPart 	";

		$sLeadQuery = "SELECT u.salutation, u.first, u.last, u.address, u.address2, u.city, u.state, u.zip, phoneNo,
								u.postalVerified, u.postalErrors, o.* , repDesignated
						FROM   otData o, userData u, offers, offerCompanies
						WHERE  o.email = u.email
						AND	   o.offerCode = offers.offerCode
						AND	   offers.companyId = offerCompanies.id
						AND   date_format(o.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sDateFrom' AND '$sDateTo'
							  $sFilterPart ";
		//$rLeadResult1 = dbQuery($sLeadQuery1);
		//echo $sLeadQuery1;
		echo dbError();
		
		// following lines are commented because we are allowing duplicate user entry in userData table
		// so there will be always corresponding user entry for a record in otData table,
		// no need to relate with userDataHistory table
		
		/*
		$sLeadQuery2 = "INSERT IGNORE INTO tempQuickLeadLook 
						SELECT u.salutation, u.first, u.last, u.address, u.address2, u.city, u.state, u.zip, phoneNo,
								u.postalVerified, u.postalErrors, o.*, repDesignated 
						FROM   otData o, userDataHistory u, offers, offerCompanies
						WHERE  o.email = u.email
						AND	   o.offerCode = offers.offerCode
						AND	   offers.companyId = offerCompanies.id";
		$rLeadResult2 = mysql_query($sLeadQuery2);
		echo mysql_error();*/						
		
		} else {
			
			$sLeadQuery = "SELECT userDataHistory.*, o.*, repDesignated
	    						  FROM    userDataHistory, otDataHistory AS o, 
										  offers LEFT JOIN offerCompanies ON offers.companyId = offerCompanies.id
    							  WHERE   o.offerCode = offers.offerCode
								  AND     o.email = userDataHistory.email							  
								  AND     date_format(o.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sDateFrom' AND '$sDateTo'
								$sFilterPart 	";
			
		}
	
	$sLeadQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	//echo $sLeadQuery;
	$rLeadResult = dbQuery($sLeadQuery);
	echo dbError();
	$iNumRecords = dbNumRows($rLeadResult);
	//echo "<RB> Total Records ".mysql_num_rows($rLeadResult);
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Total Records - $iNumRecords &nbsp; &nbsp;  Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sLeadQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rLeadResult = dbQuery($sLeadQuery);
	
	
	echo dbError();
	
		
	if ( dbNumRows($rLeadResult) >0) {
	
	if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
			}
			if ($iPage != 1) {
				$iPrevPage = $iPage-1;
				$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
				$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
			}
			
	while ($oLeadRow = dbFetchObject($rLeadResult)) {	
						
			
		if ($sBgColorClass == "ODD") {
			$sBgColorClass = "EVEN_WHITE";
		} else {
			$sBgColorClass = "ODD";
		}
			
		$sReportContent .= "<tr class=$sBgColorClass><td><a href='JavaScript:confirmDelete(this,".$oLeadRow->id.");'>Delete</a></td><Td>$oLeadRow->offerCode</td><Td>$oLeadRow->sourceCode</td><td>$oLeadRow->repDesignated</td>
							<td nowrap>$oLeadRow->dateTimeAdded</td><td>$oLeadRow->email</td><td>$oLeadRow->first</td><td>$oLeadRow->last</td>
							 <td>$oLeadRow->address<BR>$oLeadRow->address2</td><td>$oLeadRow->city</td>
							 <td>$oLeadRow->state</td><td>$oLeadRow->zip</td><td>$oLeadRow->phoneNo</td>
							 <td>$oLeadRow->remoteIp</td><td>$oLeadRow->page2Data</td><td>$oLeadRow->processStatus</td><td>$oLeadRow->reasonCode</td>
							 <td>$oLeadRow->dateTimeProcessed</td><td>$oLeadRow->sendStatus</td>
							 <td>$oLeadRow->dateTimeSent</td><td>$oLeadRow->howSent</td>
							 <td>".htmlentities($oLeadRow->realTimeResponse)."</td>
			
						</tr>";
	}
	}
	
	// delete from temporary table
	$sDeleteQuery = "DELETE FROM tempQuickLeadLook";
	$rDeleteResult = dbQuery($sDeleteQuery);

	if ($rLeadResult) {
		dbFreeResult($rLeadResult);
	}	
}
			
	
switch ($sSearchIn) {
		case 'offerCode':
		$sOfferCodeSelected = "selected";
		break;
		case 'sourceCode':
		$sSourceCodeSelected = "selected";
		break;		
		case 'repDesignated':
		$sRepDesinatedSelected = "selected";
		break;
		case 'email':
		$sEmailSelected = "selected";
		break;
		case 'first':
		$sFirstSelected = "selected";
		break;
		case 'last':
		$sLastSelected = "selected";
		break;
		case 'address':
		$sAddressSelected = "selected";
		break;
		case 'city':
		$sCitySelected = "selected";
		break;
		case 'state':
		$sStateSelected = "selected";
		break;
		case 'zip':
		$sZipSelected = "selected";
		break;
		case 'phoneNo':
		$sPhoneNoSelected = "selected";
		break;
		default:
		$sAllFieldsSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='' $sAllFieldsSelected>All Fields
						<option value='offerCode' $sOfferCodeSelected>offerCode
						<option value='sourceCode' $sSourceCodeSelected>sourceCode
						<option value='repDesignated' $sRepDesignatedSelected>Rep Designated
						<option value='email' $sEmailSelected>Email
						<option value='first' $sFirstSelected>First
						<option value='last' $sLastSelected>Last
						<option value='address' $sAddressSelected>Address
						<option value='city' $sCitySelected>City
						<option value='state' $sStateSelected>State
						<option value='zip' $sZipSelected>Zip
						<option value='phone' $sPhoneNoSelected>PhoneNo
						";
	
/*	$sRepQuery = "SELECT nbUsers.id, firstName
						 FROM    offerCompanies LEFT JOIN nbUsers ON
						 WHERE 	 FIND_IN_SET(nbUsers.id,repDesignated) > 0
						 order by nbUsers.id";
	*/
	$sRepQuery = "SELECT id, firstName
						 FROM     nbUsers						 
						 order by nbUsers.id";
	
			$rRepResult = dbQuery($sRepQuery);
			echo dbError();
			//echo mysql_num_rows($rRepResult);
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sRepList .= " $oRepRow->id - $oRepRow->firstName, ";
			}
			$sRepList = substr($sRepList, 0 , strlen($sRepList) -2);
		
			
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	
	$sReportContent = "<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=100% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=22 class=bigHeader align=center><BR>$sPageTitle<BR>From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo<BR><BR><BR></td></tr>
	<tr><td colspan=22 ><b>Rep Designated:</b> $sRepList</td></tr>
	<tr><td colspan=22 class=header>Run Date / Time: $sRunDateAndTime</td></tr>
	<tr><td></td><td class=header><a href=\"$sSortLink&sOrderColumn=offerCode&sOfferCodeOrder=$sOfferCodeOrder\" class=header>Offer Code</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder\" class=header>Source Code</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=repDesignated&sRepDesignatedOrder=$sRepDesignatedOrder\" class=header>Rep Designated</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=o.dateTimeAdded&sDateTimeAddedOrder=$sDateTimeAddedOrder\" class=header>Date Time Added</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=o.email&sEmailOrder=$sEmailOrder\" class=header>eMail</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=first&sFirstOrder=$sFirstOrder\" class=header>First</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=last&sLastOrder=$sLastOrder\" class=header>Last<a/></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=address&sAddressOrder=$sAddressOrder\" class=header>Address</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=city&sCityOrder=$sCityOrder\" class=header>City</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=state&sStateOrder=$sStateOrder\" class=header>State</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=zip&sZipOrder=$sZipOrder\" class=header>Zip</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=phoneNo&sPhoneNoOrder=$sPhoneNoOrder\" class=header>Phone No</A></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=remoteIp&sRemoteIpOrder=$sRemoteIpOrder\" class=header>IP Address</A></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=page2Data&sPage2DataOrder=$sPage2DataOrder\" class=header>Page2 Data</A></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=processStatus&sProcessStatusOrder=$sProcessStatusOrder\" class=header>Process Status</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=reasonCode&sReasonCodeOrder=$sReasonCodeOrder\" class=header>Reason Code</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=dateTimeProcessed&sDateTimeProcessedOrder=$sDateTimeProcessedOrder\" class=header>Date Time Processed</a></td>	
		<td class=header><a href=\"$sSortLink&sOrderColumn=sendStatus&sSendStatusOrder=$sSendStatusOrder\" class=header>Send Status</A></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=dateTimeSent&sDateTimeSentOrder=$sDateTimeSentOrder\" class=header>Date Time Sent</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=howSent&sHowSentOrder=$sHowSentOrder\" class=header>How Sent</a></td>
		<td class=header><a href=\"$sSortLink&sOrderColumn=realTimeResponse&sRealTimeResponseOrder=$sRealTimeResponseOrder\" class=header>Real Time Response</a></td>
	</tr>
	
	$sReportContent
	<tr><td colspan=22 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=22 class=header><BR>Notes -</td></tr>
	<tr><td colspan=22><BR>Approximate time to run this report - $iScriptExecutionTime second(s)</td></tr>
		</td></tr></table></td></tr></table></td></tr>
	</table>";	
	
	
	include("../../includes/adminHeader.php");	

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

<tr><td>Date From</td>
	<td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td>
	<td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td>
</tr>		
	
<tr><td>Filter By</td>
	<td colspan=3><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td>
	</tr>	

<tr><td>Exclude</td>
	<td colspan=3><input type=text name=sExclude value='<?php echo $sExclude;?>'></td>
</tr>
<tr><td>Search In</td><td colspan=3><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select>
</tr>

<tr><td colspan=2><input type=submit name=sViewReport value='History Report'>  &nbsp; &nbsp; 
	<input type=submit name=sViewReport value="Today's Report">  &nbsp; &nbsp; 
	</td>
	<td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td>
</tr>
<tr>
	<td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?>
	</td>
</tr>

</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>

<?php echo $sReportContent;?>

</td></tr>
</table>
</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>
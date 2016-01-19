<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$sPageTitle = "Payment Voucher Management";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	//$iCurrDay = date('d');
	
	//$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if ($sDelete) {
		// if record deleted
		$sSelectQuery = "SELECT voucherNo
						FROM paymentVouchers
						WHERE id = $iId";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oRow = dbFetchObject($rSelectResult)) {
			$iVoucherNo = $oRow->voucherNo;
		}
		
		$sDeleteQuery = "DELETE FROM paymentVouchers
	 			   		 WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase); 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if ($rResult) {
			$sDeleteQuery = "DELETE FROM paymentVoucherTransactions
	 			   		 WHERE  voucherNo = '$iVoucherNo'"; 
			$rResult = dbQuery($sDeleteQuery);
			echo dbError();
			$sMessage = "Voucher $iVoucherNo deleted";
			
		} else {
			echo dbError();
		}
	}
	
	if (! ($iYear || $iMonth)) {
		$iYear = $iCurrYear;
		if ($iCurrMonth > 1) {
			$iMonth = $iCurrMonth - 1;
		} else {
			$iMonth = "12";
			$iYear = $iCurrYear - 1;
		}
	}
	$iMonth = str_pad($iMonth, 2, "0",STR_PAD_LEFT);
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
				
		$iValue = $i+1;
		
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		
		if ($iValue == $iMonth) {
			$sMonthSel = "selected";
		} else {
			$sMonthSel = "";
		}

		$sMonthOptions .= "<option value='$iValue' $sMonthSel>$aGblMonthsArray[$i]";
	}
	
		
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYear) {
			$sYearSel = "selected";
		} else {
			$sYearSel ="";
		}
				
		$sYearOptions .= "<option value='$i' $sYearSel>$i";		
	}	
				
	
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
		
	

$sTempRepDesignated = "'".$iRepDesignated."'";
// Prepare partner options for Partner Selection box
$sPartnerQuery = "SELECT id, companyName, code
				  FROM   partnerCompanies";
if ($iRepDesignated != 'all') {
$sPartnerQuery .= " WHERE FIND_IN_SET(\"$sTempRepDesignated\", partnerCompanies.repDesignated) > 0 ";
}

$sPartnerQuery .= " ORDER BY companyName";

$rPartnerResult = dbQuery($sPartnerQuery);
echo dbError();

if ($iPartnerId == 'all') {
	$sSelected = "selected";
}
$sPartnerOptions = "<option value='all' $sSelected>All";
	
while ( $oPartnerRow = dbFetchObject($rPartnerResult)) {

	if ($oPartnerRow->id == $iPartnerId) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sPartnerOptions .="<option value='".$oPartnerRow->id."' $sSelected>".$oPartnerRow->companyName;
}	
	

// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "voucherNo";
		$sVoucherNoOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {		
		case "companyName" :
		$sCurrOrder = $sCompanyNameOrder;
		$sCompanyNameOrder = ($sPartnerNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "voucherMonthYear" :
		$sCurrOrder = $sVoucherMonthYearOrder;
		$sVoucherMonthYearOrder = ($sVoucherMonthYearOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "totalAmountDue" :
		$sCurrOrder = $sTotalAmountDueOrder;
		$sTotalAmountDueOrder = ($sTotalAmountDueOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dueDate" :
		$sCurrOrder = $sDueDateOrder;
		$sDueDateOrder = ($sDueDateOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "dateTimeCreated" :
		$sCurrOrder = $sDateTimeCreatedOrder;
		$sDateTimeCreatedOrder = ($sDateTimeCreatedOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "createdBy" :
		$sCurrOrder = $sCreatedByOrder;
		$sCreatedByOrder = ($sCreatedByOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$sCurrOrder = $sVoucherNoOrder;
		$sVoucherNoOrder = ($sVoucherNoOrder != "DESC" ? "DESC" : "ASC");
	}
	
	
$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYear=$iYear&iMonth=$iMonth&iRepDesignated=$iRepDesignated&iPartnerId=$iPartnerId&iRecPerPage=$iRecPerPage";
// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}

$sTempMonthYear = "$iYear-$iMonth";
$sVoucherQuery = "SELECT paymentVouchers.*, companyName
				  FROM   paymentVouchers, partnerCompanies
				  WHERE  paymentVouchers.partnerId = partnerCompanies.id
				  AND	 voucherMonthYear = '$sTempMonthYear'
				  AND totalAmountDue > 0";

if ($iRepDesignated != '' && $iRepDesignated !='all') {
	$sVoucherQuery .= " AND FIND_IN_SET(\"$sTempRepDesignated\", partnerCompanies.repDesignated) > 0 ";
}

$sVoucherQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";

$rResult = dbQuery($sVoucherQuery);
echo dbError();
	
	$iNumRecords = dbNumRows($rResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sVoucherQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rVoucherResult = dbQuery($sVoucherQuery);
	
//echo "Ddf".dbNumRows($rVoucherResult);
if ($rVoucherResult) {
	if (dbNumRows($rResult) > 0) {
			// Prepare Next/Prev/First/Last links
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Last</a>";
			}
			if ($iPage != 1) {
				$iPrevPage = $iPage-1;
				$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
				$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
			}
			
			
while ($oVoucherRow = dbFetchObject($rVoucherResult)) {
	if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
				} else {
					$sBgcolorClass = "ODD";
				}
	$iTempVoucherNo = str_pad($oVoucherRow->voucherNo, 6, '0', STR_PAD_LEFT);
	$sVoucherList .= "<tr class=$sBgcolorClass><td>$iTempVoucherNo</td>
							<td>$oVoucherRow->companyName</td>
							<td>$oVoucherRow->voucherMonthYear</td>
							<td>$oVoucherRow->totalAmountDue</td>
							<td>$oVoucherRow->dueDate</td>
							<td>$oVoucherRow->dateTimeCreated</td>
							<td>$oVoucherRow->createdBy</td>
							<td><a href='voucherPdf.php?iMenuId=$iMenuId&iMonth=$iMonth&iYear=$iYear&iVoucherNo=$oVoucherRow->voucherNo' target=_NEW>Get Voucher</a>
							&nbsp; <a href='voucherPdf.php?iMenuId=$iMenuId&sViewReport=calculate&iMonth=$iMonth&iYear=$iYear&iVoucherNo=$oVoucherRow->voucherNo' target=_NEW>Recalculate</a>
							&nbsp; <a href='JavaScript:confirmDelete(this,".$oVoucherRow->id.");'>Delete</a></td>
						</tr>";
}

	}
}
	include("../../includes/adminHeader.php");
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	

	//<BR><a href='JavaScript:void(window.open("selectPages.php?iMenuId=<?php echo $iMenuId;>","page","scrollbars=yes, status=yes"));'>Select Pages</a>	

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

<script language=JavaScript>

self.name = 'voucher';

function repChanged() {
	var repSelectedIndex = document.form1.iRepDesignated.selectedIndex;
	var rep = document.form1.iRepDesignated.options[repSelectedIndex].value;
	var mnu = document.form1.iMenuId.value;
	window.location.href = "<?php echo $PHP_SELF."?".SID;?>" + "&iMenuId=" + mnu + "&iRepDesignated=" + rep;
	
}

function confirmDelete(form1,id)
{
	if(confirm('Are you sure to delete this record ?'))
	{
		document.form1.elements['sDelete'].value='Delete';
		document.form1.elements['iId'].value=id;
		viewVoucherList();
	}
}

function funcRecPerPage(form1) {	
	viewVoucherList();
}		

function viewVoucherList() {
	document.form1.reportClicked.value='';
	var repIndex = document.form1.iRepDesignated.selectedIndex;
	var rep = document.form1.iRepDesignated.options[repIndex].value;
	var partnerIndex = document.form1.iPartnerId.selectedIndex;
	var partner = document.form1.iPartnerId.options[partnerIndex].value;
	var yrIndex = document.form1.iYear.selectedIndex;
	var yr = document.form1.iYear.options[yrIndex].value;
	var mnIndex = document.form1.iMonth.selectedIndex;
	var mn = document.form1.iMonth.options[mnIndex].value;
	var rec = document.form1.iRecPerPage.value;
	var del = document.form1.sDelete.value;
	var id = document.form1.iId.value;
	var mnu = document.form1.iMenuId.value;
	newUrl = "<?php echo $PHP_SELF."?".SID;?>" +"&iMenuId=" + mnu + "&iRepDesignated="+rep + "&iPartnerId=" + partner;
	newUrl += "&iYear=" + yr + "&iMonth=" + mn + "&iRecPerPage="+rec;
	if (del != '') {
		newUrl += "&sDelete="+del+"&iId="+id;
	}
	
	window.location.href = newUrl;
}
</script>

<form name=form1 action='voucherPdf.php' target=_BLANK>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td width=15%>Voucher Month</td>
	<td><select name=iMonth><?php echo $sMonthOptions;?>	
		</select> &nbsp;<select name=iYear><?php echo $sYearOptions;?>
		</select>
	</td>
</tr>		
<tr><td>Rep. Designated</td>
	<td><select name='iRepDesignated' onChange='repChanged()'>
	<?php echo $sRepOptions;?>
	</select></td>
</tr>
<tr><td>Select Partner</td>
	<td><select name='iPartnerId'>
	<?php echo $sPartnerOptions;?>
	</select></td>
</tr>
		
<tr><td colspan=2>
<input type=button name=sSubmit value='View Voucher List' onClick='viewVoucherList();'>
&nbsp; &nbsp; &nbsp; <input type=button name=sSubmit value='Get Voucher/s' onClick="funcReportClicked('view');">
&nbsp; &nbsp; &nbsp; <input type=button name=sSubmit value='Prepare/Recalculate Voucher/s' onClick="funcReportClicked('calculate');"></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=8 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=voucherNo&sVoucherNoOrder=<?php echo $sVoucherNoOrder;?>" class=header>Voucher No</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Company Name</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=voucherMonthYear&sVoucherMonthYearOrder=<?php echo $sVoucherMonthYearOrder;?>" class=header>Month-Year</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=totalAmountDue&sTotalAmountDueOrder=<?php echo $sTotalAmountDueOrder;?>" class=header>Amount</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=dueDate&sDueDateOrder=<?php echo $sDueDateOrder;?>" class=header>Due Date</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=dateTimeCreated&sDateTimeCreatedOrder=<?php echo $sDateTimeCreatedOrder;?>" class=header>Date Time Created</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=createdBy&sCreatedByOrder=<?php echo $sCreatedByOrder;?>" class=header>Created By</a></th>
	<td></td>
			
</tr>
<?php echo $sVoucherList;?>
<tr><td colspan=8 align=right class=header>Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>

</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td class=header colspan=4>Notes:</td></tr>
<tr><td colspan=4>Payment amount will change as postal verification status changes.</td></tr>
<tr><td colspan=4>Payment amount is calculated as total unique postal verified users multiplied by the rate for the campaign.</td></tr>

</table>

</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to List/Delete Offer Company Contacts

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Partner Company Contacts - List/Delete Offer Company Contacts";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($SDelete) {
$sDeleteQuery = "DELETE FROM offerCompanyContacts
		 			 WHERE id = '$iId'"; 
	$rResult = dbQuery($sDeleteQuery);
	if (!($rResult)) {
		echo dbError();
	}
	//reset $id to null
	$iId = '';
}

// Set Default order column
if (!($sOrderColumn)) {
	$sOrderColumn = "contact";
	$sContactOrder = "ASC";
}

// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($sOrderColumn) {
	case "phoneNo" :
	$sCurrOrder = $sPhoneNoOrder;
	$sPhoneNoOrder = ($sPhoneNoOrder != "DESC" ? "DESC" : "ASC");
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
	default:
	$sCurrOrder = $sContactOrder;
	$sContactOrder = ($sContactOrder != "DESC" ? "DESC" : "ASC");
}

// Query to get the list of Partner Contacts
$sSelectQuery = "SELECT *
				FROM offerCompanyContacts
				WHERE companyId = '$iCompanyId'
				ORDER BY ".$sOrderColumn." $sCurrOrder";

$rResult = dbQuery($sSelectQuery);
//echo mysql_error();
if ($rResult) {
	
	$iNumRecords = dbNumRows($rResult);
	if ($iNumRecords > 0) {
		
		while ($oRow = dbFetchObject($rResult)) {
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			if ($oRow->defaultContact  == 'Y') {
				$sDefault = " * ";
			} else {
				$sDefault = "";
			}
			$sContactList .= "<tr class=$sBgcolorClass>
					<td>$sDefault<a href='JavaScript:void(window.open(\"emailCompany.php?iMenuId=$iMenuId&iId=".$oRow->id."&sSesEmail=$sSesEmail\",\"Email\",\"width=600, height=400, scrollbars=yes\"));'>$oRow->contact</a></td>
					<td>$oRow->phoneNo</td>
					<td>$oRow->address<br>$oRow->address2</td>	
					<td>$oRow->city</td><td>$oRow->state</td>
					<td>$oRow->zip</td><td>";
			$sContactList .= "<a href='JavaScript:void(window.open(\"addContact.php?iMenuId=$iMenuId&iCompanyId=$iCompanyId&iId=".$oRow->id."\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
			$sContactList .= "&nbsp; <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a></td></tr>";
			$sContactList .= "</td>";
		}
	} else {
		$sMessage = "No records exist...";
	}
	dbFreeResult($rResult);
	
} else {
	echo dbError();
}

if (!($sAdd)){
	// Display Add button, if Add button isn't already clicked	
	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addContact.php?iMenuId=$iMenuId&iCompanyId=$iCompanyId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>			
			<input type=hidden name=iPartnerId value='$iPartnerId'>";

$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sSesEmail=$sSesEmail&iCompanyId=$iCompanyId";

include("../../includes/adminAddHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}						
</script>
	
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<Tr><td><?php echo $sAddButton;?></td><td></td><td colspan=4> * &nbsp;  Shows Default Contact</td></tr>
<tr>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=contact&sContactOrder=<?php echo $sContactOrder;?>" class=header>Contact</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=phoneNo&sPhoneNoOrder=<?php echo $sPhoneNoOrder;?>" class=header>Phone No</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=address&sAddressOrder=<?php echo $sAddressOrder;?>" class=header>Address</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=city&sCityOrder=<?php echo $sCityOrder;?>" class=header>City</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=state&sStateOrder=<?php echo $sStateOrder;?>" class=header>State</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=zip&sZipOrder=<?php echo $sZipOrder;?>" class=header>Zip</a></th>	
	<th>&nbsp; </th>
</tr>
<?php echo $sContactList;?>
</table>
<BR><BR><center>
<input type=button name=sClose value=Close onClick="JavaScript:self.close();">
</center>
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
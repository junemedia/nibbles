<?php

/*********

Script to Add/Edit Field To Map

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offer's Page2 Field Mappings - Add/Edit Field";

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($sDelete) {
	$sDeleteQuery = "DELETE FROM page2Map
		 			 WHERE id = '$iId'"; 
	
	$rResult = dbQuery($sDeleteQuery);
	if (!($rResult)) {
		echo dbError();
	} else {
		
		// rearrange the storage order when a field deleted
		// ONLY IF no leads collected yet
		// check if any leads are there
		$sCheckQuery = "SELECT *
						FROM   otData
						WHERE  offerCode = '$sOfferCode'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult) == 0 ) {
			
			$sUpdateQuery = "UPDATE page2Map
						 SET    storageOrder = storageOrder-1
						 WHERE  offerCode = '$sOfferCode'
						 AND    id > '$iId'";
			$rUpdateResult = dbQuery($sUpdateQuery);
		}
	}
	//reset $id to null
	$iId = '';
}


// Query to get the list of Partner Contacts
$sSelectQuery = "SELECT *
				FROM page2Map
				WHERE offerCode = '$sOfferCode'
				ORDER BY fieldName";
$rResult = dbQuery($sSelectQuery);

if ($rResult) {
	
	$iNumRecords = dbNumRows($rResult);
	
	if ($iNumRecords > 0) {
		
		while ($oRow = dbFetchObject($rResult)) {
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass = "ODD";
			}
			
			$sActualFieldName = $sOfferCode."_".$oRow->fieldName;
			$sFieldsList .= "<tr class=$sBgcolorClass>
					<td>$oRow->fieldName</td>
					<td>$oRow->actualFieldName</td>
					<td>$oRow->isRequired</td>
					<td>$oRow->encryptData</td>
					<td>$oRow->storageOrder</td>
					<td>";
			$sFieldsList .= "<a href='JavaScript:void(window.open(\"addMapField.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode&iId=".$oRow->id."\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>";
			//$sFieldsList .= "&nbsp; <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a></td></tr>";
			$sFieldsList .= "</td></tr>";
			//<td>$oRow->validation</td>
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
	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addMapField.php?iMenuId=$iMenuId&sOfferCode=$sOfferCode\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>";

include("../../includes/adminAddHeader.php");

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
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
	
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<Tr><td><?php echo $sAddButton;?></td></tr>
<tr>
	<td class=header>Field Name</td>
	<td class=header>Actual Field Name</td>
	<td class=header>Is Required</td>
	<td class=header>Encrypt Data</td>
	<td class=header>Storage Order</td>
	<!--<td class=header>Validation</td>-->
	<td>&nbsp; </td>
</tr>
<?php echo $sFieldsList;?>
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
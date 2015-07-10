<?php

/*********

Script to Add/Edit Field To Map

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offer's Page2 Field Mappings - Add/Edit Field";

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($sSaveClose || $sSaveNew || $sSaveReplace) {
	// When New Record Submitted
	$sMessage = "";
	if (!ereg("^[A-Za-z0-9_]+$", $sFieldName)) {
		$sMessage .= "Field Name can contain only AlphaNumeric characters or _ ...<BR>";
		$bKeepValues = true;
	} 

	
	if ($bKeepValues != true) {
		
		$sActualFieldName = $sOfferCode."_".$sFieldName;
		
		/*if ($sValidation == 'phoneAreaCode') {
			//|| $sValidation == 'phoneExchange' || $sValidation == 'phoneNumber'
			$sActualFieldName = $sActualFieldName."_areaCode";
		} else if ($sValidation == 'phoneExchange') {
			$sActualFieldName = $sActualFieldName."_exchange";
		} else if ($sValidation == 'phoneNumber') {
			$sActualFieldName = $sActualFieldName."_number";
		}*/
		//echo "<BR>$sValidation $sActualFieldName";
		if (!($iId)) {
			// check if field name exists
			$sCheckQuery = "SELECT *
						FROM   page2Map
						WHERE  offerCode = '$sOfferCode'
						AND    fieldName = '$sFieldName'
						AND    actualFieldName = '$sActualFieldName'
						AND sopOnChangeCall = '$sChangeCall'";
						//AND    validation NOT IN ('phoneAreaCode','phoneExchange', 'phoneNumber')";
			$rCheckResult = dbQuery($sCheckQuery);
			
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Field Name already exists for this offer...";
				$bKeepValues = true;
			} else {
				
				// Insert record if everything is fine
				// get next storage order for this field
				$iMaxStorageOrder = 0;
				$sTempQuery = "SELECT max(storageOrder) AS maxStorageOrder
							   FROM   page2Map
							   WHERE  offerCode = '$sOfferCode'";
				$rTempResult = dbQuery($sTempQuery);
				while ($oTempRow = dbFetchObject($rTempResult)) {
					$iMaxStorageOrder = $oTempRow->maxStorageOrder;
				}
				$iStorageOrder = $iMaxStorageOrder + 1;
								
				$sAddQuery = "INSERT INTO page2Map(offerCode, fieldName, actualFieldName, sopOnChangeCall, isRequired, encryptData, storageOrder)
						 	  VALUES('$sOfferCode', '$sFieldName', '$sActualFieldName', '$sChangeCall', '$iIsRequired', '$iEncryptData', '$iStorageOrder')";
				
				$rResult = dbQuery($sAddQuery);
				
				if ( $rResult ) {
					// below line is commented out because we are not using dbInsertId anywhere on the page.
					// line 108, we are overwriting dbInsertId with iId
					//$iPageId = dbInsertId();
				} else {
					echo dbError();
				}				
			}
		} else if ($iId) {
			
			// When Record Edited
			// Check if code already exists...
			$sCheckQuery =  "SELECT *
					  		 FROM   page2Map
					  		 WHERE  fieldName = '$sFieldName'
					  		 AND sopOnChangeCall = '$sChangeCall'
							 AND    actualFieldName = '$sActualFieldName'
					  		 AND    id != '$iId'";
					  		 //AND    validation NOT IN ('phoneAreaCode','phoneExchange', 'phoneNumber')"; 
			
			$rCheckResult = dbQuery($sCheckQuery);
			
			if (dbNumRows($rCheckResult) == 0) {
				
				$sEditQuery = "UPDATE   page2Map
					   SET 		fieldName = \"$sFieldName\",
					   			sopOnChangeCall = \"$sChangeCall\",
								actualFieldName = '$sActualFieldName',
								isRequired = '$iIsRequired',
								encryptData = '$iEncryptData'								
		 			   WHERE    id = '$iId'";
				
				$rResult = dbQuery($sEditQuery);
				
				echo dbError();
				
				$iPageId = $iId;
			} else {
				$sMessage = "Field Name already exists for this offer...";
				$bKeepValues = true;
			}
		}
			
		//echo htmlspecialchars($sPage2Template);
		if ($bKeepValues != true) {
			$sFieldName = '';	
			$sChangeCall='';
			$sActualFieldName = '';
			$iIsRequired = '';
			$iEncryptData = '';
			$iStorageOrder = '';
			//$sValidation = '';			
		}
		
		if ($sSaveClose || $sSaveReplace) {
			
			echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";
			// exit from this script
			exit();
			
		} else if ($sSaveNew) {

			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";	
		}
	}
}

if ($iId && !($sSaveClose || $sSaveNew || $sSaveReplace)) {
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT *
					 FROM   page2Map
			  		 WHERE  id = '$iId'";
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			$sFieldName = $oRow->fieldName;	
			$sChangeCall = $oRow->sopOnChangeCall;
			$sActualFieldName = $oRow->actualFieldName;
			$iIsRequired = $oRow->isRequired;
			$iEncryptData = $oRow->encryptData;
			$iStorageOrder = $oRow->storageOrder;
		//	$sValidation = $oRow->validation;
		}
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


// if isRequired checked
$sIsRequiredChecked = "";
if ($iIsRequired) {
	$sIsRequiredChecked = "checked";
}

// if Encrypt Data checked
$sEncryptDataChecked = "";
if ($iEncryptData) {
	$sEncryptDataChecked = "checked";
}

// prepare validation options
$sValPhoneSelected = "";
$sValEmailSelected = "";
switch ($sValidation) {
	case "email":
	$sValEmailSelected = "Selected";
	break;
	case "phone":
	$sValPhoneSelected = "selected";
	break;	
	case "phoneAreaCode":
	$sValPhoneAreaCodeSelected = "Selected";
	break;
	case "phoneExchange":
	$sValPhoneExchangeSelected = "Selected";
	break;
	case "phoneNumber":
	$sValPhoneNumberSelected = "Selected";
	break;
}
/*
$sValidationOptions = "<option value=''>
						<option value='email' $sValEmailSelected>Email
					   <option value='phone' $sValPhoneSelected>Phone
						<option value='phoneAreaCode' $sValPhoneAreaCodeSelected>Phone - Area Code
						<option value='phoneExchange' $sValPhoneExchangeSelected>Phone - Exchange
						<option value='phoneNumber' $sValPhoneNumberSelected>Phone - Number";
*/
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>";

include("../../includes/adminAddHeader.php");

?>
<script language=JavaScript>
function confirmReplace() {
if(confirm("Are You Sure To Replace Existing Template For This Offer ?")) {
document.form1.sSaveReplace.value='Y';
document.form1.submit();
}
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<input type=hidden name=sSaveReplace>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>OfferCode</td>
		<td><?php echo $sOfferCode;?></td>
	</tr>	
	<tr><td>Field Name</td>
		<td><input type=text name='sFieldName' value='<?php echo $sFieldName;?>'><br>
			Field Name must contain AlphaNumeric characters only.</td>
	</tr>			
	<tr><td>Actual Field Name</td>
		<td><?php echo $sActualFieldName;?></td>
	</tr>			
	<tr><td>On Change Call</td>
		<td><input type=text name='sChangeCall' value='<?php echo $sChangeCall;?>'><br></td>
	</tr>
	<tr><td>Is Required</td>
		<td><input type=checkbox name='iIsRequired' value='1' <?php echo $sIsRequiredChecked;?>></td>
	</tr>			
	<tr><td>Encrypt Data</td>
		<td><input type=checkbox name='iEncryptData' value='1' <?php echo $sEncryptDataChecked;?>><BR>
		If Field Data should be encrypted before saving in database.</td>
	</tr>					
	<!--<tr><td>Validation</td>
		<td><select name='sValidation'>
		<php echo $sValidationOptions;?>
		</select></td>
	</tr>		-->
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveClose value='Save & Close'> &nbsp; &nbsp; 
		<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();" >
		<?php echo $sNewEntryButtons;?></td><td></td>
	</tr>		
	</table>
	<form>
</body>

</html>
<?php
} else {
	echo "You are not authorized to access this page...";
}
?>
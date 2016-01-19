<?php

/*********

Script to Display Add/Edit Partner Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Partner Companies - Add/Edit Partner Company";
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// When New Record Submitted
	
	// If more than one rep. then combine them with comma delimiter
	for ($i = 0; $i < count($sRepDesignated); $i++) {
		$sRepDesignatedAll .= "'".$sRepDesignated[$i]."',";				
		
	}
	
	$sRepDesignatedAll = substr($sRepDesignatedAll, 0, strlen($sRepDesignatedAll)-1);
	$sRepDesignated = $sRepDesignatedAll;
	
	
	if (strlen(trim($sCode)) != 3) {
		$sMessage = "Please Enter Exactly 3 Letter Code...";
		$bKeepValues = true;
	} else if (!(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid eMail Address...";
		$bKeepValues = "true";
	} else {				
				
		// Check if code already exists...
		$sCheckQuery = "SELECT *
					   FROM   partnerCompanies
					   WHERE  code = LOWER('$sCode')"; 
		$rCheckResult = dbQuery($sCheckQuery);
		
		if (dbNumRows($rCheckResult) == 0) {
			// Insert record if everything is fine
			$sAddQuery = "INSERT INTO partnerCompanies(companyName, code, repDesignated,  paymentTerms, faxNo, taxId, canExportEmails, canViewE1Counts, acceptCc, excludeDataSale)
					 VALUES('$sCompanyName', LOWER('$sCode'), \"$sRepDesignated\", '$sPaymentTerms', '$sFaxNo', '$sTaxId', '$iCanExportEmails', '$iCanViewE1Counts', '$sAcceptCc', '$iExcludeDataSale')";
			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		

			$rResult = dbQuery($sAddQuery);
			
			
			if ( $rResult ) {
				
				
				$sCheckQuery = "SELECT id
	   					FROM   partnerCompanies
	   					WHERE  code = LOWER('$sCode')"; 
				$rCheckResult = dbQuery($sCheckQuery);
				$sRow = dbFetchObject($rCheckResult);

				// Insert records into Partner Contact table
				$iPartnerId = $sRow->id;
				
				// If Assign password is checked, create random password
				if ($sAssignPasswd == 'Y') {
					$sPasswd = uniqid("con");
				}
		
				$sContactQuery = "INSERT INTO partnerContacts(partnerId, contact, email, passwd, phoneNo, address1, address2, city, state, zip, defaultContact, accountingContact)
								VALUES('$iPartnerId', '$sContact', '$sEmail', '$sPasswd', '$sPhoneNo', '$sAddress1', '$sAddress2', '$sCity', '$sState', '$sZip', 'Y', '$sAccountingContact')";
				$rContactResult = dbQuery($sContactQuery);
			} else {
				echo dbError();
			}
		} else {
			$sMessage = "Code Already Exists...";
			$bKeepValues = "true";
		}
		
	}
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// When Record Edited
	
	// If more than one rep. then combine them with comma
	for ($i = 0; $i < count($sRepDesignated); $i++) {
		$sRepDesignatedAll .= "'".$sRepDesignated[$i]."',";
	}
	
	$sRepDesignatedAll = substr($sRepDesignatedAll, 0, strlen($sRepDesignatedAll)-1);
	$sRepDesignated = $sRepDesignatedAll;
	
	if (!(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid eMail Address...";
		$bKeepValues = "true";
	} else {
		$sEditQuery = "UPDATE partnerCompanies
					  SET companyName='$sCompanyName',					 					 
					  paymentTerms = '$sPaymentTerms',
					  faxNo = '$sFaxNo',
					  taxId = '$sTaxId',
					  canExportEmails = '$iCanExportEmails',
					  canViewE1Counts = '$iCanViewE1Counts',
					  excludeDataSale = '$iExcludeDataSale',
					  acceptCc = '$sAcceptCc',
					  repDesignated = \"$sRepDesignated\" ";
		
		$sNbUserIdQuoted = "'".$sSesUserId."'";
		$sPrevRepDesignated = stripslashes($sPrevRepDesignated);
		
		if(stristr($sPrevRepDesignated, $sNbUserIdQuoted) || isAdmin()) {
			$sEditQuery .= ", repDesignated = \"$sRepDesignatedAll\"";
		}
	}
	
	// If everything is fine, update the record
	if ($sEditQuery != '') {
		$sEditQuery .= " WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		
		echo dbError();
		
		if ($sAssignPasswd == 'Y') {
			$sPasswd = uniqid("con");
		}
		
		
		$sEditContactQuery = "UPDATE partnerContacts
							SET contact = '$sContact',
					  email = '$sEmail',
					  passwd = '$sPasswd',
					  phoneNo = '$sPhoneNo',
					  address1 = '$sAddress1',
					  address2 = '$sAddress2',
					  city = '$sCity',
					  state = '$sState',
					  zip = '$sZip',
					  accountingContact = '$sAccountingContact'
					WHERE partnerId = '$iId'
					AND   defaultContact = 'Y'";
		$rEditContactResult = dbQuery($sEditContactQuery);
		
		echo dbError();
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
	//	exit();
	}
} else if ($sSaveNew) {
		
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$sCompanyName = '';	
		$sPaymentTerms = '';
		$sFaxNo = '';
		$sTaxId = '';
		$iCanExportEmails = '';
		$iCanViewE1Counts = '';
		$iExcludeDataSale = '';
		$sCode = '';
		$sContact = '';
		$sEmail = '';
		$sPasswd = '';
		$sPhoneNo = '';
		$sAddress1 = '';
		$sAddress2 = '';
		$sCity = '';
		$sState = '';
		$sZip = '';
		$sRepDesignated = '';
		$sCodeField = '';
		$sAccountingContact = '';
	}
}

if ($iId && !($bKeepValues)) {
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT P.*,  C.*
						FROM   partnerCompanies P, partnerContacts C										 
			  			WHERE  P.id = '$iId'
						AND    P.id = C.partnerId
						AND    C.defaultContact = 'Y'";
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			$sCompanyName = ascii_encode($oRow->companyName);
			$sPaymentTerms = $oRow->paymentTerms;
			$sFaxNo = $oRow->faxNo;
			$sTaxId = $oRow->taxId;
			$sAcceptCc = $oRow->acceptCc;
			$iCanExportEmails = $oRow->canExportEmails;		
			$iCanViewE1Counts = $oRow->canViewE1Counts;
			$iExcludeDataSale = $oRow->excludeDataSale;
			$sContact = $oRow->contact;
			$sEmail = $oRow->email;
			$sPasswd = $oRow->passwd;
			$sPhoneNo = $oRow->phoneNo;
			$sAddress1 = $oRow->address1;
			$sAddress2 = $oRow->address2;
			$sCity = $oRow->city;
			$sState = $oRow->state;
			$sZip = $oRow->zip;
			$sAccountingContact = $oRow->accountingContact;
			$sRepDesignated = $oRow->repDesignated;
			if ($sRepDesignated == '')
			$sRepDesignated = '0';
			// if user is not authorised to change rep designated,
			//Get the name of rep designated, just to display
			/*$sRepQuery = "SELECT firstName
						 FROM   nbUsers
						 WHERE  id IN (".$sRepDesignated.")";
			$rRepResult = dbQuery($sRepQuery);
			
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sRepDesignatedField .= $oRepRow->firstName . "<br>";
			}
			*/
			// Don't allow to change the code if it's entered before
			$sCodeField = $oRow->code;
		}
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
	
	if ($sPasswd != '') {
		$sPasswdField = "<tr><td>Password</td><td><input type=text name=sPasswd value='$sPasswd'></td></tr>";
	} else {
		// If No password is stored for this contact, display checkbox if want to assign password
		$sPasswdField = "<tr><td>Assign Password</td>
						<td><input type=checkbox name='sAssignPasswd' value='Y'></td></tr>";
	}
	
	
} else {
	$sCompanyName = ascii_encode(stripslashes($sCompanyName));
	$sCodeField = "<input type=text name='sCode' value=$sCode> &nbsp; Must be 3 Chars";
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// If new record is going to be added, display Assign Aassword checkbox
	$sPasswdField = "<tr><td>Assign Password</td>
						<td><input type=checkbox name='sAssignPasswd' value='Y'></td></tr>";
	
}

// If user has right to change the rep. designated for this record
// User has right only if his level is 9 or he himself is a rep. designated for this record
//if ($sSesLevel == 'admin' || stristr($sRepDesignated,"'".$sSesUserId."'") || $iId=='' || $sRepDesignated == 0) {
	// Prepare Rep. designated options for selection box
	
	$sRepQuery = "SELECT id, userName
				 FROM   nbUsers
				 ORDER BY userName";
	
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	
	$sRepDesignatedField = "<select name ='sRepDesignated[]' multiple size=3>";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if (stristr($sRepDesignated, "'".$oRepRow->id."'")) {			
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		
		$sRepDesignatedField .= "<option value=$oRepRow->id $sSelected>$oRepRow->userName";
	}
	$sRepDesignatedField .= "</select>";
//}

// prepare State Options for selection box
$sStateQuery = "SELECT *
			   FROM   states
			   ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value='' selected>";
while ( $oStateRow = dbFetchObject($rStateResult)) {
	if ($oStateRow->stateId == $sState) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value='".$oStateRow->stateId."' $sSelected>$oStateRow->state";
}


// canadian provinces query
$sStateQuery = "SELECT *
			   FROM   canadianProvinces
			   ORDER BY province";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions .= "<option value=''> -- Canadian Provinces --";
while ( $oStateRow = dbFetchObject($rStateResult)) {
	if ($oStateRow->provinceId == $sState) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value='".$oStateRow->provinceId."' $sSelected>$oStateRow->province";
}



if ($iCanExportEmails) {
	$sCanExportEmailsChecked = "checked";
}


if ($iCanViewE1Counts) {
	$sCanViewE1CountsChecked = "checked";
}

if ($iExcludeDataSale) {
	$sExcludeDataSaleChecked = "checked";
}

if ($sAccountingContact) {
	$sAccountingContactChecked = "checked";
}


$sDefaultSelected = '';
$sYesSelected = '';
$sNoSelected = '';
$sYesViaPayPalSelected = '';

switch($sAcceptCc) {
	case "Yes":
		$sYesSelected = "selected";
		break;
	case "No":
		$sNoSelected = "selected";
		break;
	case "Yes Via PayPal":
		$sYesViaPayPalSelected = "selected";
		break;
	default:
		$sDefaultSelected = "selected";
}

$sAcceptCcOptions = "<option value='' $sDefaultSelected>
					 <option value='Yes' $sYesSelected>Yes
					 <option value='No' $sNoSelected>No
					 <option value='Yes Via PayPal' $sYesViaPayPalSelected>Yes via Paypal";


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Company Name</td>
		<td><input type=text name='sCompanyName' value='<?php echo $sCompanyName;?>'></td>
	</tr>
	<tr><td>Code</td>
		<td><?php echo $sCodeField;?></td>
	</tr>	
		
	<tr><td>Payment Terms</td>
		<td><input type=text name='sPaymentTerms' value='<?php echo $sPaymentTerms;?>'></td>
	</tr>	
	
	<tr><td>Fax No.</td>
		<td><input type=text name='sFaxNo' value='<?php echo $sFaxNo;?>'></td>
	</tr>
	<tr><td>Tax I.D.#</td>
		<td><input type=text name='sTaxId' value='<?php echo $sTaxId;?>'></td>
	</tr>
	<tr><td>Accept Credit Card ?</td>
		<td><select name='sAcceptCc'>
			<?php echo $sAcceptCcOptions;?>
		</select></td>
	</tr>
	<tr><td>Can Export Emails</td>
		<td><input type=checkbox name='iCanExportEmails' value='1' <?php echo $sCanExportEmailsChecked;?>></td>
	</tr>
	<tr><td>Can View e1 Counts</td>
		<td><input type=checkbox name='iCanViewE1Counts' value='1' <?php echo $sCanViewE1CountsChecked;?>></td>
	</tr>
	
	<tr><td>Exclude From Data Sales</td>
		<td><input type=checkbox name='iExcludeDataSale' value='1' <?php echo $sExcludeDataSaleChecked;?>></td>
	</tr>
	
	<tr><td colspan=2 class=header><BR>Contact Details</td></tr>
	<tr><td>Contact Person</td>
		<td><input type=text name='sContact' value='<?php echo $sContact;?>'></td>
	</tr>
	<tr><td>eMail</td>
		<td><input type=text name='sEmail' value='<?php echo $sEmail;?>'></td>
	</tr>
	<?php echo $sPasswdField;?>
	<tr><td>Phone No</td>
		<td><input type=text name='sPhoneNo' value='<?php echo $sPhoneNo;?>'></td>
	</tr>
	<tr><td>Address 1</td>
		<td><input type=text name='sAddress1' value='<?php echo $sAddress1;?>'></td>
	</tr>	
	<tr><td>Address 2</td>
		<td><input type=text name='sAddress2' value='<?php echo $sAddress2;?>'></td>		
	</tr>
	<tr><td>City</td>
		<td><input type=text name='sCity' value='<?php echo $sCity;?>'></td>
	</tr>
	<tr><td>State</td>
		<td><select name='sState'><?php echo $sStateOptions;?></select></td>
	</tr>
	<tr><td>Zip</td>
		<td><input type=text name='sZip' value='<?php echo $sZip;?>'></td>
	</tr>
	<tr><td>Accounting Contact</td>
		<td><input type=checkbox name='sAccountingContact' value='Y' <?php echo $sAccountingContactChecked;?>></td>
	</tr>
	<tr><td colspan=2><BR><BR></td></tr>
	<tr><td>Rep.</td>
		<td><?php echo $sRepDesignatedField;?></td>
	</tr>
		
	<tr><td colspan="2"><b>Note:</b><br>
		If "Exclude From Data Sales" is checked, any data that comes in from sourceCode associated
		with partner will be excluded from data sales.</td>
	</tr>
</table>

<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
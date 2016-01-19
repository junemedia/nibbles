<?php

/*********

Script to Display Add/Edit Offer Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Offer Companies - Add/Edit Offer Company";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// When New Record Submitted
	
	// If more than one rep. then combine them with comma delimiter
	for ($i = 0; $i < count($sRepDesignated); $i++) {
		$sRepDesignatedAll .= "'".$sRepDesignated[$i]."',";
		
	}
	$sRepDesignatedAll = substr($sRepDesignatedAll, 0, strlen($sRepDesignatedAll)-1);
	$sRepDesignated = $sRepDesignatedAll;	
	
	if (strlen(trim($sCode)) != 5) {
		$sMessage = "Please Enter Exactly 5 Letter Code...";
		$bKeepValues = true;
	} else if ($sEmail !='' && !(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid Contact eMail Address...";
		$bKeepValues = "true";
	} else if ($sTechEmail !='' && !(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sTechEmail)))  {
		$sMessage = "Please Enter Valid Tech Contact eMail Address...";
		$bKeepValues = "true";
	} else {
		// Check if code already exists...
		$sCheckQuery = "SELECT *
					   FROM   offerCompanies
					   WHERE  code = LOWER('$sCode')"; 
		$rCheckResult = dbQuery($sCheckQuery);

		if (dbNumRows($rCheckResult) == 0) {
			// Insert record if everything is fine
			$sAddQuery = "INSERT INTO offerCompanies(companyName, code, repDesignated, paymentTermId, techContact,
								techEmail, techPhoneNo, techAddress, techAddress2, techCity, techState, techZip, notes)
					 	  VALUES('$sCompanyName', LOWER('$sCode'), \"$sRepDesignated\", '$iPaymentTermId', \"$sTechContact\",
							 	\"$sTechEmail\", \"$sTechPhoneNo\", \"$sTechAddress\", \"$sTechAddress2\", \"$sTechCity\",
							    \"$sTechState\", \"$sTechZip\",\"$sNotes\")";

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
					   FROM   offerCompanies
					   WHERE  code = LOWER('$sCode')"; 
				$rCheckResult = dbQuery($sCheckQuery);
				$sRow = dbFetchObject($rCheckResult);
		
				// Insert records into offer company Contact table
				$iCompanyId = $sRow->id;
				$sContactQuery = "INSERT INTO offerCompanyContacts(companyId, contact, email,  phoneNo, address, address2, city, state, zip, defaultContact, faxNo)
							VALUES('$iCompanyId', '$sContact', '$sEmail', '$sPhoneNo', '$sAddress', '$sAddress2', '$sCity', '$sState', '$sZip', 'Y', '$sFaxNo')";
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
		
	if ($sEmail !='' && !(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid Contact eMail Address...";
		$bKeepValues = "true";
	} else if ($sTechEmail !='' && !(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sTechEmail)))  {
		$sMessage = "Please Enter Valid Tech eMail Address...";
		$bKeepValues = "true";
	} else {
		$sEditQuery = "UPDATE offerCompanies
					  SET companyName='$sCompanyName',						 					
						  paymentTermId = '$iPaymentTermId',
						  repDesignated = \"$sRepDesignatedAll\",
						  techContact = \"$sTechContact\",
						  techEmail = \"$sTechEmail\",
						  techPhoneNo = \"$sTechPhoneNo\",
						  techAddress = \"$sTechAddress\",
						  techAddress2 = \"$sTechAddress2\",
						  techCity = \"$sTechCity\",
						  techState = \"$sTechState\",
						  techZip = \"$sTechZip\",
						  notes = \"$sNotes\"
	 				  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		
		$sEditContactQuery = "UPDATE offerCompanyContacts
							SET contact = '$sContact',
					  email = '$sEmail',
					  phoneNo = '$sPhoneNo',
					  faxNo = '$sFaxNo',
					  address = '$sAddress',
					  address2 = '$sAddress2',
					  city = '$sCity',
					  state = '$sState',
					  zip = '$sZip'
					WHERE companyId = '$iId'
					AND   defaultContact = 'Y'";
		
		$rEditContactResult = dbQuery($sEditContactQuery);
		
		echo dbError();
		
	}
	
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		if ($sReturnTo == 'list') {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		exit();
		} else {
			
			echo "<script language=JavaScript>
				var companyValue=new String('$iCompanyId');
				var companyText=new String('$sCompanyName');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var companyOpt;

					companyOpt              = window.opener.document.createElement('option');
					companyOpt.value        = companyValue;
					companyOpt.text         = companyText;				

					window.opener.document.form1.$sReturnTo.options.add(companyOpt);
				
				} else {
					//if browser is not IE			
					var companyOpt=new Option(companyText, companyValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=companyOpt\");
				}		
			self.close();
			</script>";
			
		}
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {	
	
	if ($bKeepValues != true) {
		if ($sReturnTo == 'list') {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		} else {
			
			echo "<script language=JavaScript>
			    var companyValue=new String('$sCompanyName');
				var companyText=new String('$iCompanyId');
				var v2 = window.opener.document.form1.$sReturnTo;
				var i = window.opener.document.form1.$sReturnTo.length;
				if (agt.indexOf(\"msie\") != -1) {						
				var companyOpt;

				companyOpt              = window.opener.document.createElement('option');
				companyOpt.value        = companyValue;
				companyOpt.text         = companyText;				

				window.opener.document.form1.$sReturnTo.options.add(companyOpt);
				
			} else {
				//if browser is not IE			
				var companyOpt=new Option(companyText, companyValue);
				eval(\"window.opener.document.form1.$sReturnTo.options[i]=companyOpt\");
			}
			
			</script>";
			
		}
		
		$iCompanyId = '';
		$sCompanyName = '';		
		$iPaymentTermId = '';
		$sTechContact = '';
		$sTechEmail = '';
		$sTechPhoneNo = '';
		$sTechAddress = '';
		$sTechAddress2 = '';
		$sTechCity = '';
		$sTechState = '';
		$sTechZip = '';
		$sCode = '';
		$sContact = '';
		$sEmail = '';
		$sPhoneNo = '';
		$sAddress = '';
		$sAddress2 = '';
		$sCity = '';
		$sState = '';
		$sZip = '';
		$sRepDesignated = '';
		$sCodeField = '';
		$sNotes = '';
	}
}

if ($iId) {
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT O.*, C.*
						FROM   offerCompanies O, offerCompanyContacts C
			  			WHERE  O.id = '$iId'
						AND    O.id = C.companyId
						AND    C.defaultContact = 'Y'";
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			$sCompanyName = ascii_encode($oRow->companyName);			
			$iPaymentTermId = $oRow->paymentTermId;	
			$sTechContact = $oRow->techContact;
			$sTechEmail = $oRow->techEmail;
			$sTechPhoneNo = $oRow->techPhoneNo;
			$sTechAddress = $oRow->techAddress;
			$sTechAddress2 = $oRow->techAddress2;
			$sTechCity = $oRow->techCity;
			$sTechState = $oRow->techState;
			$sTechZip = $oRow->techZip;
			$sContact = $oRow->contact;
			$sEmail = $oRow->email;
			$sPhoneNo = $oRow->phoneNo;
			$sFaxNo = $oRow->faxNo;
			$sAddress = $oRow->address;
			$sAddress2 = $oRow->address2;
			$sCity = $oRow->city;
			$sState = $oRow->state;
			$sZip = $oRow->zip;
			$sNotes = ascii_encode($oRow->notes);
			$sRepDesignated = $oRow->repDesignated;
			if ($sRepDesignated == '') {
				$sRepDesignated = '0';
			}
			// if user is not authorised to change rep designated,
			//Get the name of rep designated, just to display
			$sRepQuery = "SELECT firstName
						 FROM   nbUsers
						 WHERE  id IN (".$sRepDesignated.")";
			$rRepResult = dbQuery($sRepQuery);
			
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sRepDesignatedField .= $oRepRow->firstName . "<br>";
			}
			
			// Don't allow to change the code if it's entered before
			$sCodeField = $oRow->code;
		}
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
} else {
	$sCompanyName = ascii_encode(stripslashes($sCompanyName));
	$sNotes = ascii_encode(stripslashes($sNotes));
	$sCodeField = "<input type=text name='sCode' value=$sCode> &nbsp; Must be 5 Chars";
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Prepare Payment Term options for selection box
$sPaymentTermQuery = "SELECT *
			 	 	  FROM   paymentTerms";

$rPaymentTermResult = dbQuery($sPaymentTermQuery);
$sPaymentTermOptions = "<option value=''>";
while ($oPaymentTermRow = dbFetchObject($rPaymentTermResult)) {
	if ($oPaymentTermRow->id == $iPaymentTermId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	
	$sPaymentTermOptions .= "<option value=$oPaymentTermRow->id $sSelected>$oPaymentTermRow->paymentTerm";	
}


// If user has right to change the rep. designated for this record
// User has right only if his level is 9 or he himself is a rep. designated for this record
if ($iId == '' || $sRepDesignated == 0) {
	// Prepare Rep. designated options for selection box
	
	$sRepQuery = "SELECT id, firstName,userName
				 FROM   nbUsers
				 ORDER BY firstName";
	
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
	//  <a href='JavaScript:void(window.open(\"$sGblAdminSiteRoot/nbUsers/addUser.php?iMenuId=11&sReturnTo=sRepDesignated\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Add Rep</a>";
}

// prepare State Options for selection box
$sStateQuery = "SELECT *
			   FROM   states
			   ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value='' selected>";
$sTechStateOptions = "<option value='' selected>";
while ( $oStateRow = dbFetchObject($rStateResult)) {
	if ($oStateRow->stateId == $sState) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value='".$oStateRow->stateId."' $sSelected>$oStateRow->state";
	
	if ($oStateRow->stateId == $sTechState) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sTechStateOptions .= "<option value='".$oStateRow->stateId."' $sSelected>$oStateRow->state";
	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sReturnTo value='$sReturnTo'>
			<input type=hidden name=sSesUserId value='$sSesUserId'>
			<input type=hidden name=sSesLevel value='$sSesLevel'>
			<input type=hidden name=sPrevRepDesignated value=\"$sRepDesignated\">";

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
		<td><select name='iPaymentTermId'>
		<?php echo $sPaymentTermOptions;?>
		</select> <a href='JavaScript:void(window.open("<?php echo $sGblAdminSiteRoot;?>/paymentTerms/addTerm.php?iMenuId=11&sReturnTo=iPaymentTermId", "", "height=450, width=600, scrollbars=yes, resizable=yes, status=yes"));'>Add Payment Term</a></td>
	</tr>
	
	<tr><td colspan=2 class=header><BR>Tech Contact Details</td></tr>
	<tr><td>Tech Contact Person</td>
		<td><input type=text name='sTechContact' value='<?php echo $sTechContact;?>'></td>
	</tr>
	<tr><td>Tech eMail</td>
		<td><input type=text name='sTechEmail' value='<?php echo $sTechEmail;?>'></td>
	</tr>
	<tr><td>Tech Phone No</td>
		<td><input type=text name='sTechPhoneNo' value='<?php echo $sTechPhoneNo;?>'></td>
	</tr>
	<tr><td>Tech Address </td>
		<td><input type=text name='sTechAddress' value='<?php echo $sTechAddress;?>'></td>
	</tr>	
	<tr><td>Tech Address 2</td>
		<td><input type=text name='sTechAddress2' value='<?php echo $sTechAddress2;?>'></td>		
	</tr>
	<tr><td>Tech City</td>
		<td><input type=text name='sTechCity' value='<?php echo $sTechCity;?>'></td>
	</tr>
	<tr><td>Tech State</td>
		<td><select name='sTechState'><?php echo $sTechStateOptions;?></select></td>
	</tr>
	<tr><td>Tech Zip</td>
		<td><input type=text name='sTechZip' value='<?php echo $sTechZip;?>'></td>
	</tr>
	
	
	<tr><td colspan=2 class=header><BR>Contact Details</td></tr>
	<tr><td>Contact Person</td>
		<td><input type=text name='sContact' value='<?php echo $sContact;?>'></td>
	</tr>
	<tr><td>eMail</td>
		<td><input type=text name='sEmail' value='<?php echo $sEmail;?>'></td>
	</tr>
	<tr><td>Phone No</td>
		<td><input type=text name='sPhoneNo' value='<?php echo $sPhoneNo;?>'></td>
	</tr>
	<tr><td>Fax No</td>
		<td><input type=text name='sFaxNo' value='<?php echo $sFaxNo;?>'></td>
	</tr>
	<tr><td>Address </td>
		<td><input type=text name='sAddress' value='<?php echo $sAddress;?>'></td>
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
	<tr><td>Rep.</td>
		<td><?php echo $sRepDesignatedField;?></td>
	</tr>
	<tr><td>Notes</td>
		<td><textarea rows=5 cols=45 name=sNotes><?php echo $sNotes;?></textarea></td>
	</tr>
	
</table>

<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
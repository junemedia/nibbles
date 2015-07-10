<?php

/*********

Script to Add/Edit Partner Company Contacts

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Partner Company Contacts - Add/Edit Partner Company Contact";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	
	// if new data submitted
	if (!(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid eMail Address...";
		$bKeepValues = true;
		
	}
	else {
		/* If Assign password is checked, create random password
		if ($sAssignPasswd == 'Y') {
			$iUniqId = uniqid("con");
		}*/
		// Insert record into contacts table
		$sAddQuery = "INSERT INTO offerCompanyContacts(companyId, contact, email, phoneNo, address,
					 address2, city, state, zip, defaultContact) 
					 VALUES('$iCompanyId', '$sContact', '$sEmail', '$sPhoneNo', '$sAddress', '$sAddress2', '$sCity', '$sState', '$sZip', '$sDefaultContact')";

		
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		echo dbError();
		if ($rResult) {
			
			$sCheckQuery = "SELECT id
	   				FROM   offerCompanyContacts
	  				WHERE  companyId = '$iCompanyId'
	  				AND email = '$sEmail'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			$sRow = dbFetchObject($rCheckResult);
			
			// If this is default contact, make other contacts of this company as secondary
			$iContactId = $sRow->id;
			if ($sDefaultContact == 'Y')
			{
				$sUpdateQuery = "UPDATE offerCompanyContacts
								SET    defaultContact = ''
								WHERE  companyId = '$iCompanyId'
								AND    id != '$iContactId'";
		
				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sUpdateQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$sUpdateResult = dbQuery($sUpdateQuery);
			}			
		} else {
			echo dbError();
			$bKeepValues = true;			
		} 		}
} elseif (($sSaveClose || $sSaveNew) && ($iId)) {
	
	if (!(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $sEmail)))  {
		$sMessage = "Please Enter Valid eMail Address...";
		$bKeepValues = true;

	} else {
		// If passwd value was null before, so if checked assign passwd checkbox
		/* then generate the password.
		if ($sAssignPasswd == 'Y') {
			$passwd = uniqid("con");			
		}*/
		// Update the record if everything is fine
		$sEditQuery = "UPDATE offerCompanyContacts
					  SET contact = '$sContact',
					  email = '$sEmail',					  
					  phoneNo = '$sPhoneNo',
					  address = '$sAddress',
					  address2 = '$sAddress2',
					  city = '$sCity',
					  state = '$sState',
					  zip = '$sZip',
					  defaultContact = '$sDefaultContact'
					WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);
		if ( $rResult && $sDefaultContact == 'Y')
		{
			// If this is default contact, make other contacts of this company as secondary
			$sUpdateQuery = "UPDATE offerCompanyContacts
								SET    defaultContact = ''
								WHERE  companyId = '$iCompanyId'
								AND    id != '$iId'";
			$updateResult = dbQuery($sUpdateQuery);
		}
		$id = '';
	}
}
echo dbError();
if ($sSaveClose) {
	// Close the window if record inserted correctly
	if($bKeepValues != true) {
		echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";		
		//exit from this script
		exit();
	}
} else if ($sSaveNew || $sAbandonNew) {
	// Reset textboxes for new record if not any error in saving current record
	if($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$sContact = '';
		$sEmail = '';
		$sPasswd = '';
		$sPhoneNo = '';
		$sAddress = '';
		$sAddress2 = '';
		$sCity = '';
		$sState = '';
		$sZip = '';
		$sDefaultContact = '';
	}
}

// If Clicked on Edit, display values in fields 
if ($iId != '') {
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT *
						FROM   offerCompanyContacts
			  			WHERE  id = '$iId'";
	$rResult = dbQuery($sSelectQuery);
	
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {
			$sContact = $oRow->contact;
			$sEmail = $oRow->email;
			//$sPasswd = $oRow->passwd;
			$sPhoneNo = $oRow->phoneNo;
			$sAddress = $oRow->address;
			$sAddress2 = $oRow->address2;
			$sCity = $oRow->city;
			$sState = $oRow->state;
			$sZip = $oRow->zip;
			$sDefaultContact = $oRow->defaultContact;
		}
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
	/* Display passwd field, if there is a password stored, generate passwd while adding the records
	if ($passwd != '') {
		$passwdField = "<tr><td>Password</td><td><input type=text name=passwd value='$passwd'></td></tr>";
	} else {
		// If No password is stored for this contact, display checkbox if want to assign password
		$passwdField = "<tr><td>Assign Password</td>
						<td><input type=checkbox name='assignPasswd' value='Y'></td></tr>";
	}*/
} else {
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}
/*else if ($add) {
	// If new record is going to be added, display Assign Aassword checkbox
	$passwdField = "<tr><td>Assign Password</td>
						<td><input type=checkbox name='assignPasswd' value='Y'></td></tr>";
}*/

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


if ($sDefaultContact == 'Y') {
	$sDefaultContactChecked = "checked";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=sMenuFolder value='$sMenuFolder'>
			<input type=hidden name=iCompanyId value='$iCompanyId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Contact</td>
		<td><input type=text name='sContact' value='<?php echo $sContact;?>'></td>
	</tr>
	<tr><td>eMail</td>
		<td><input type=text name='sEmail' value='<?php echo $sEmail;?>'></td>
	</tr>
		
	<tr><td>Phone No</td>
		<td><input type=text name='sPhoneNo' value='<?php echo $sPhoneNo;?>'></td>
	</tr>
	<tr><td>Address</td>
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
	<tr><td>Default Contact</td>
		<td><input type=checkbox name='sDefaultContact' value='Y' <?php echo $sDefaultContactChecked;?>></td>
	</tr>
</table>

<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

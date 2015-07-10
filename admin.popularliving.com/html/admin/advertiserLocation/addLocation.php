<?php

include("../../includes/paths.php");
session_start();
$sPageTitle = "Nibbles Advertisers Location - List/Delete Advertisers Location";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		$sMessage = '';
		
		if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sEmail)) {
			$sMessage = "Email Address Contains Invalid Characters.";
			$bKeepValues = true;
		} elseif (!(ereg("^[0-9]{10,}$", $sPhoneNo))) {
			$sMessage = "Phone Number Must Be 10 Digit Numeric.";
			$bKeepValues = true;
		} elseif ($sFax !='' && !(ereg("^[0-9]{10,}$", $sFax))) {
			$sMessage = "Fax Number Must Be 10 Digit Numeric.";
			$bKeepValues = true;
		} elseif ($sPhoneExt !='' && !(ereg("^[0-9]+$", $sPhoneExt))) {
			$sMessage = "Phone Extension Must Be Numeric.";
			$bKeepValues = true;
		} elseif (!(ereg("^[0-9]{5,}$", $sZip))) {
			$sMessage = "Zip Code Must Be 5 Digit Numeric.";
			$bKeepValues = true;
		} elseif ($sState == '') {
			$sMessage = "State Is Required.";
			$bKeepValues = true;
		} elseif ($sAddress1 == '') {
			$sMessage = "Address Is Required.";
			$bKeepValues = true;
		} elseif ($sCity == '') {
			$sMessage = "City Is Required.";
			$bKeepValues = true;
		} elseif ($sContactName == '') {
			$sMessage = "Contact Name Is Required.";
			$bKeepValues = true;
		} elseif ($sCompanyName == '') {
			$sMessage = "Company Name Is Required.";
			$bKeepValues = true;
		}

		if ($sMessage == '') {
			if (!($iId)) {
				$sAddEditQuery = "INSERT IGNORE INTO advertisersLocation (companyName,contactName,address,address2,
							city,state,zip,phone,phoneExt,fax,email) 
						 VALUES(\"$sCompanyName\", \"$sContactName\", \"$sAddress1\", \"$sAddress2\",
						  \"$sCity\", \"$sState\", \"$sZip\", \"$sPhoneNo\", \"$sPhoneExt\", \"$sFax\", \"$sEmail\")";
			} elseif ($iId) {
				$sAddEditQuery = "UPDATE advertisersLocation
							SET companyName = \"$sCompanyName\",
							contactName = \"$sContactName\",
						    address = \"$sAddress1\",
						    address2 = \"$sAddress2\",
						    city = \"$sCity\",
						    state = \"$sState\",
						    zip = \"$sZip\",
					    	phone = \"$sPhoneNo\",
					        phoneExt = \"$sPhoneExt\",
					        fax = \"$sFax\",
					        email = \"$sEmail\"
						WHERE id = '$iId'";
			}
			
			$rResult = dbQuery($sAddEditQuery);
			echo  dbError();
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"".addslashes($sAddEditQuery)."\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
				window.opener.location.reload();
				</script>";	
			$iId = '';
			$sCompanyName = '';
			$sContactName = '';
			$sAddress1 = '';
			$sAddress2 = '';
			$sCity = '';
			$sState = '';
			$sZip = '';
			$sPhoneNo = '';
			$sPhoneExt = '';
			$sFax = '';
			$sEmail = '';
		}
	}
	
	if ($iId) {
		// If Clicked to edit, get the data to display in fields
		$sSelectQuery = "SELECT * FROM advertisersLocation
					    WHERE  id = '$iId'";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$sCompanyName = $oSelectRow->companyName;
			$sContactName = $oSelectRow->contactName;
			$sAddress1 = $oSelectRow->address;
			$sAddress2 = $oSelectRow->address2;
			$sCity = $oSelectRow->city;
			$sState = $oSelectRow->state;
			$sZip = $oSelectRow->zip;
			$sPhoneNo = $oSelectRow->phone;
			$sPhoneExt = $oSelectRow->phoneExt;
			$sFax = $oSelectRow->fax;
			$sEmail = $oSelectRow->email;
			if ($sPhoneExt == 0) { $sPhoneExt = ''; }
		}
	} else {
		// If add button is clicked, display another two buttons
		$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
							<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}
	
	
	// prepare State Options for selection box
	$rStateResult = dbQuery("SELECT * FROM states ORDER BY state");
	$sStateOptions = "<option value=''>";
	while ( $oStateRow = dbFetchObject($rStateResult)) {
		$sSelected = '';
		if ($oStateRow->stateId == $sState) {
			$sSelected = "selected";
		}
		$sStateOptions .= "<option value='$oStateRow->stateId' $sSelected>$oStateRow->state";
	}
	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminAddHeader.php");
?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	
		<tr><td>Company Name:</td>
			<td><input type=text name='sCompanyName' size="30" value='<?php echo $sCompanyName;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>
		<tr><td>Contact Name:</td>
			<td><input type=text name='sContactName' size="30" value='<?php echo $sContactName;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>
		<tr><td>Address 1</td>
			<td><input type=text name='sAddress1' size="30" value='<?php echo $sAddress1;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>	
		<tr><td>Address 2</td>
			<td><input type=text name='sAddress2' size="30" value='<?php echo $sAddress2;?>'></td>		
		</tr>
		<tr><td>City</td>
			<td><input type=text name='sCity' size="30" value='<?php echo $sCity;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>
		<tr><td>State</td>
			<td><select name='sState'><?php echo $sStateOptions;?></select>
			&nbsp;&nbsp;Required
			</td>
		</tr>
		<tr><td>Zip</td>
			<td><input type=text name='sZip' maxlength="5" size="6" value='<?php echo $sZip;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>
		<tr><td>E-Mail</td>
			<td><input type=text name='sEmail' maxlength="100" size="30" value='<?php echo $sEmail;?>'>
			&nbsp;&nbsp;Required</td>
		</tr>
		<tr><td>Phone No</td>
			<td><input type=text name='sPhoneNo' maxlength="10" size="11" value='<?php echo $sPhoneNo;?>'>
			&nbsp;x&nbsp;<input type=text name='sPhoneExt' size=5 maxlength="5" value='<?php echo $sPhoneExt;?>'>
			&nbsp;&nbsp;For Example: 8472059320 x 208
			</td>
		</tr>
		<tr><td>Fax</td>
			<td><input type=text name='sFax' maxlength="10" size="11" value='<?php echo $sFax;?>'>
			&nbsp;&nbsp;For Example: 8472059340
			</td>
		</tr>
	</table>
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
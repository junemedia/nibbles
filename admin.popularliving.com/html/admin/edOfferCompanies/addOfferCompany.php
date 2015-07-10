<?php

/*********

Script to Display List/Add/Edit/Delete Offer Companies information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Editorial Offer Company Management - Add/Edit Offer Company";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	if (strlen(trim($code)) != 3) {
		$sMessage = "Please Enter Exactly 3 Letter Code...";
		$keepValues = true;
	}  else {
		// Check if code already exists...
		$checkQuery = "SELECT code
						   FROM   edOfferCompanies
						   WHERE code = '$code'";	
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) == 0) {
			$addQuery = "INSERT INTO edOfferCompanies(companyName, code, paymentMethod, paymentAmount,
							 	paymentTerms, affiliateMgmntCompany, repDesignated, notes) 
						  VALUES('$companyName', LOWER('$code'), '$paymentMethod', '$paymentAmount', '$paymentTerms', '$affiliateMgmntCompany', '$repDesignated', '$notes')";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$result = mysql_query($addQuery);
			if (! $result) {
				echo mysql_error();
			}
		} else {
			$sMessage = "Company Code Already Exists...";
			$keepValues = true;
		}
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	// If record edited
	
	$editQuery = "UPDATE edOfferCompanies
				  SET companyName='$companyName',
				  	  paymentMethod = '$paymentMethod',
					  paymentAmount = '$paymentAmount',
					  paymentTerms = '$paymentTerms',
					  affiliateMgmntCompany = '$affiliateMgmntCompany',
					  notes = '$notes',
	 				  repDesignated = '$repDesignated'";			
	if ($code != '') {
		//check if it's exact 3 characters
		if (strlen(trim($code)) == 3) {
			// check if code exists
			$checkQuery = "SELECT code
						   FROM   edOfferCompanies
						   WHERE  code='$code'";
			$checkResult=mysql_query($checkQuery);
			if (mysql_num_rows($checkResult) == 0) {
				$editQuery .= ", code = LOWER('$code') ";
			} else {
				$sMessage = "Company Code Already Exists...";
				$editQuery ='';
			}
		} else {
			$sMessage = "Please Enter 3 Letter Code Only...";
			$editQuery = '';
		}
	}
	if ($editQuery != '') {
		$editQuery .= " WHERE id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($editQuery);
	}
	if (! $result) {
		echo mysql_error();
	}
}

if ($sSaveClose) {
	if ($keepValues != true) {
		echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$companyName = '';
		$notes = '';
		$repDesignated = '';
		$paymentMethod = '';
		$paymentAmount = '';
		$paymentTerms = '';
		$affiliateMgmntCompany = '';
		$code = '';
	}
}

if ($id != '' && $keepValues != true) {
	// If Clicked Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edOfferCompanies										 
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$companyName = ascii_encode($row->companyName);
			$notes = ascii_encode($row->notes);
			$repDesignated = $row->repDesignated;
			$paymentMethod = $row->paymentMethod;
			$paymentAmount = $row->paymentAmount;
			$paymentTerms = $row->paymentTerms;
			$affiliateMgmntCompany = $row->affiliateMgmntCompany;
			
			if (trim($row->code) == '') {
				// Allow code to enter if it's not entered before, i.e. it's null
				$codeField = "<input type=text name='code' value=$code>";
			} else {
				// Don't allow to change the code if it's entered before
				$codeField = $row->code;
			}
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
} else {
	$companyName = ascii_encode(stripslashes($companyName));
	$notes = ascii_encode(stripslashes($notes));
	$codeField = "<input type=text name='code' value=$code>";
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Prepare Rep. designated options for selection box
$repDesignatedOptions = "<option value=''>";
$repQuery = "SELECT id, firstName
			 FROM   nbUsers";

$repResult = mysql_query($repQuery);

while ($repRow = mysql_fetch_object($repResult)) {
	if ($repRow->id == $repDesignated) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$repDesignatedOptions .= "<option value=$repRow->id $selected>$repRow->firstName";
}

// Prepare Payment Method options for selection box
$paymentQuery = "SELECT *
			 	 FROM   edOfferPaymentMethods";

$paymentResult = mysql_query($paymentQuery);

while ($paymentRow = mysql_fetch_object($paymentResult)) {
	if ($paymentRow->id == $paymentMethod) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$paymentMethodOptions .= "<option value=$paymentRow->id $selected>$paymentRow->paymentMethod";
}

// Prepare Affiliate Management Companies options for selection box
$affiliateQuery = "SELECT *
			 	   FROM   edAffiliateMgmntCompanies
				   ORDER BY companyName";

$affiliateResult = mysql_query($affiliateQuery);

while ($affiliateRow = mysql_fetch_object($affiliateResult)) {
	if ($affiliateRow->id == $affiliateMgmntCompany) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$affiliateCompanyOptions .= "<option value=$affiliateRow->id $selected>$affiliateRow->companyName";
}

	//$addButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addOfferCompany.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=40%>Company Name</td>
		<td><input type=text name='companyName' value='<?php echo $companyName;?>'></td>
	</tr>
	<tr><td>Code</td>
		<td><?php echo $codeField;?></td>
	</tr>		
	<tr><td>Notes</td>
		<td><textarea name='notes' rows=3 cols=40><?php echo $notes;?></textarea>
		</td>
	</tr>
	<tr><td>Payment Method</td>
		<td><select name='paymentMethod'>
		<?php echo $paymentMethodOptions;?>
		</select></td>
	</tr>
	<tr><td>Payment Amount</td>
		<td><input type=text name='paymentAmount' value='<?php echo $paymentAmount;?>'></td>
	</tr>
	<tr><td>Payment Terms</td>
		<td><input type=text name='paymentTerms' value='<?php echo $paymentTerms;?>'></td>
	</tr>
	<tr><td>Affiliate Management Company</td>
		<td><select name='affiliateMgmntCompany'>
		<?php echo $affiliateCompanyOptions;?>
		</select></td>
	</tr>		
	<tr><td>Rep.</td>
		<td><select name ='repDesignated'>
				<?php echo $repDesignatedOptions;?>
			</select></td>
	</tr>
	
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
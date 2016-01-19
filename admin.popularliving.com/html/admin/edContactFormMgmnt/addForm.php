<?php

/*********

Script to Display Add/Edit Contact Forms

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Editor's Submission Forms Management - Add/Edit Contact Form";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {


if ($delete) {
	// If a contact email address is deleted/removed
	for ($i = 0; $i < count($emailOption); $i++) {
		// Keep emailOptions except the emailNo sent as delete
		if ($i != $delete)
		$contactEmails .= $emailOption[$i].",";
	}
	$contactEmails = substr($contactEmails, 0, strlen($contactEmails)-1);
	
	$editQuery = "UPDATE edContactForms
				  SET 	 contactEmail = '$contactEmails'
				  WHERE  id = '$id'";	
	$result = mysql_query($editQuery);
}

if (($sSaveClose || $sSaveNew) && !($id)) {
	
	// If new contact form added
	
	
	if ( !ereg( "^[a-z]+$", $shortName) ) {		
		$sMessage = "Short name should contain lower case alpha characters only";
		$keepValues = true;
	} else {
		
		for ($i = 0; $i < count($emailOption); $i++) {
			if ($emailOption[$i] != '')
			$contactEmails .= $emailOption[$i].",";
		}
		$contactEmails = substr($contactEmails, 0, strlen($contactEmails)-1);
		
		$displayDate = $year."-".$month."-".$day;
		$addQuery = "INSERT INTO edContactForms(shortName, formName, formHeading, reqGraphic, contactEmail)
				 VALUES(\"$shortName\", \"$formName\", \"$formHeading\", '$reqGraphic', '$contactEmails')";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add Entry: $shortName\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($addQuery);
		if (! $result) {
			echo mysql_error();
		}
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	
	if ( !ereg(  "^[a-z]+$", $shortName) ) {
		$sMessage = "Short name should contain lower case alpha characters only";
		$keepValues = true;
	} else {
		// If contact form record edited
		for ($i = 0; $i < count($emailOption); $i++) {
			if ($emailOption[$i] != '') {
				$contactEmails .= $emailOption[$i].",";
			}
		}
		$contactEmails = substr($contactEmails, 0, strlen($contactEmails)-1);
		
		$editQuery = "UPDATE edContactForms
				  SET 	 shortName = \"$shortName\",
						 formName = \"$formName\",
						 formHeading = \"$formHeading\",
						 reqGraphic = '$reqGraphic',
						 contactEmail = '$contactEmails'
				  WHERE  id = '$id'";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: edContactForms.id=$id\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($editQuery);
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
		$shortName = '';
		$formName = '';
		$formHeading = '';
		$reqGraphic = '';
		$contactEmail = '';
		$contactEmails = '';
	}
}

$emailNo = 0;
$emailQuery = "SELECT *
				FROM   edContactForms
				WHERE  id = '$id'";
$emailResult = mysql_query($emailQuery);

if (mysql_num_rows($emailResult) > 0) {
	while ($emailRow = mysql_fetch_object($emailResult)) {
		$shortName = $emailRow->shortName;
		$formName = ascii_encode($emailRow->formName);
		$formHeading = ascii_encode($emailRow->formHeading);
		$reqGraphic = $emailRow->reqGraphic;
		$contactEmails = $emailRow->contactEmail;
	}
	$emailArray = explode(",", $contactEmails);
	// Display existing contact emails rowwise with delete links
	for ($i = 0; $i < count($emailArray); $i++) {
		$emailOptions .= "<tr><td>eMail $emailNo</td><Td><input type=text name='emailOption[".$emailNo."]' value='".$emailArray[$i]."' size=30> &nbsp; <a href='JavaScript:delEmail($emailNo);'>Delete</a></td></tr>";
		$emailNo++;
	}
}

// Display contact emails if currently added while adding/editing record
// Don't give delete option to currently added emails, except the last added eMail
for ($i = $emailNo; $i < $addEmail; $i++) {
	$emailOptions .= "<tr><td>eMail $i </td><Td><input type=text name='emailOption[".$emailNo."]' value='".$emailOption[$i]."' size=30></td></tr>";
	$emailNo++;
}

// Display last contact email from currently added, while adding/editing record
// Allow Delete for the currently last added eMail
if (isset($addEmail) && $addEmail >= count($emailArray)) {
	$emailOptions .= "<tr><td>Email $emailNo</td><Td><input type=text name='emailOption[".$addEmail."]' value='".$addEmail."' size=30> &nbsp; <a href='JavaScript:addEmailFunc(".($addEmail-1).");'>Delete</a></td></tr>";
	$emailNo++;
}

if (!($id)) {
	// If add button is clicked, display another two buttons
	$formName = ascii_encode(stripslashes($formName));
	$formHeading = ascii_encode(stripslashes($formHeading));
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

if ($reqGraphic) {
	$reqGraphicChecked = "checked";
} else {
	$reqGraphicChecked = "";
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>
			<input type=hidden name=addEmail>
			<input type=hidden name=delete>";

// set add New Contact eMail link
$addEmailLink = "<a href='JavaScript:addEmailFunc(".$emailNo.");'>Add New Contact Email</a>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<script language=JavaScript>
	function addEmailFunc(emailNo) {
		document.forms[0].elements['addEmail'].value=emailNo;
		document.forms[0].submit();
	}
	
	function delEmail(emailNo) {
		document.forms[0].elements['delete'].value=emailNo;
		document.forms[0].submit();
	}
</script>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Short Name</td>
		<td><input type=text name='shortName' value='<?php echo $shortName;?>'><BR>Letters only, All lower case.</td>
	</tr>
	<tr><td>Contact Form</td>
		<td><input type=text name='formName' value='<?php echo $formName;?>'></td>
	</tr>
	<tr><td>Form Heading</td>
		<td><input type=text name='formHeading' value='<?php echo $formHeading;?>' size=35></td>
	</tr>	
	<tr><td>Allow Graphic Upload</td>
		<td><input type=checkbox name='reqGraphic' value='1' <?php echo $reqGraphicChecked;?>></td>
	</tr>
	<tr><td>Contact eMail(s)</td>	
	</tr>
	<?php echo $emailOptions;?>	
	<tr><td><?php echo $addEmailLink;?></td></tr>
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display Add/Edit Offer Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Offer Companies - Send Email To Offer Company";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	//Get the email address to be displayed in 'Email To' Field
	if ($iId) {
		
			$emailQuery = "SELECT email FROM offerCompanyContacts
							WHERE id = $iId";
		
		$emailResult = dbQuery($emailQuery);
		while ($row = dbFetchObject($emailResult)) {
			$sEmailTo=$row->email;
		}
	}
	
	// If form submitted to send the eMail
	if ($sSubmit) {

		$sHeaders = "From: $sEmailFrom\r\n";
		mail($sEmailTo, $sSubject, $sMessage, $sHeaders );
		echo "<script language=JavaScript>
		alert(\"Your mail has been sent\");				
		self.close();
		</script>";	
	}

		
?>

<!-- Template to send an email to the partner -->
<html>

<head>
	<title>Send eMail to partner</title>
	<LINK rel="stylesheet" href="../styles.css" type="text/css" >
</head>

<body>

<table cellpadding=5 cellspacing=0 width=95% align=center>

<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $sHidden;?>
	<tr><TD>From</td><td><input type=text name=sEmailFrom value='<?php echo $sEmailFrom;?>' size=30></td></tr>
	<tr><TD>To</td><td><input type=text name=sEmailTo value='<?php echo $sEmailTo;?>' size=30 ></td></tr>
	<tr><TD>Subject</td><td><input type=text name=sSubject size=50></td></tr>
	<tr><TD>Message</td><td><textarea name=sMessage rows=15 cols=50></textarea></td></tr>
	<tr><td></td><TD><input type=submit name=sSubmit value="Send"></td></tr>

</table>
</form>


</body>

</html>

<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
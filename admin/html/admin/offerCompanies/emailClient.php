<?php

/***********

Script to send an eMail to the partner

**********/

//Get the email address to be displayed in 'Email To' Field

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($iId) {
	$sEmailQuery = "SELECT email FROM offerCompanyContacts
					WHERE id = $iId";
	$rEmailResult = dbQuery($sEmailQuery);
	while ($oRow = dbFetchObject($rEmailResult)) {
		$sEmailTo = $oRow->email;
	}
}

// If form submitted to send the eMail
if ($sSend) {
	
	$sHeaders = "From: $sEmailFrom\r\n";
	mail($sEmailTo, $sSubject, $sMessage, $sHeaders );
	echo "<script language=JavaScript>
		alert(\"Your mail has been sent\");				
		self.close();
		</script>";	
}

// set user's email address here, who is logged in
$sEmailFrom = $sMarsEmail;

// Hidden variable to be passed with form submit
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	

include("../../includes/adminAddHeader.php");
?>

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
	<tr><td></td><TD><input type=submit name=sSend value="Send"></td></tr>

</table>
</form>

</body>

</html>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
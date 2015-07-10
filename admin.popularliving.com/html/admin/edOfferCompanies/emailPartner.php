<?php

/***********

Script to send an eMail to the partner

**********/

include("../../includes/paths.php");

	//Get the email address to be displayed in 'Email To' Field
	if ($id) {
		if($affiliate) {
				$emailQuery = "SELECT email FROM edAffiliateMgmntCompanies
						WHERE id = $id";
		} else {
			$emailQuery = "SELECT email FROM edOfferCompanies
						WHERE id = $id";
		}
		$emailResult = mysql_query($emailQuery);
		while ($row = mysql_fetch_object($emailResult)) {
			$emailTo=$row->email;
		}
	}
	
	// If form submitted to send the eMail
	if ($submit) {

		$headers = "From: $emailFrom\r\n";
		mail($emailTo, $subject, $message, $headers );
		echo "<script language=JavaScript>
		alert(\"Your mail has been sent\");				
		self.close();
		</script>";	
	}

	
include("../../includes/adminAddHeader.php");
?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 width=95% align=center>
	<tr><TD>From</td><td><input type=text name=emailFrom value='<?php echo $emailFrom;?>' size=30></td></tr>
	<tr><TD>To</td><td><input type=text name=emailTo value='<?php echo $emailTo;?>' size=30 ></td></tr>
	<tr><TD>Subject</td><td><input type=text name=subject size=50></td></tr>
	<tr><TD>Message</td><td><textarea name=message rows=15 cols=50></textarea></td></tr>
	<tr><td></td><TD><input type=submit name=submit value="Send"></td></tr>

</table>
</form>

</body>

</html>
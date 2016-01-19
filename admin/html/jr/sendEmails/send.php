<?php

/*
Specify config info at top.

Form to enter subject and body and select text or html.

Enter email to send in form.

When click send it says are you sure and says how many people it will send to.

Ability to send test message.

Sends to every one in table specified, getting address from email column. Dedupes.
So if someone in table twice they only get email once.

Every 100 emails sent updates the browser display so no timeouts

At end displays number of emails sent.

Set from address and other headers at top of script.
*/



$host = "localhost" ;

$dbase = "testJr" ;

$user = "root" ;

$pass = "092363jr" ;

mysql_pconnect ('localhost', $user, $pass);

mysql_select_db ($dbase);


$sGblEmailFrom = "nibbles@amperemedia.com";
$sTableEmails = "users";

if ($sSubmit) {
	
	$sHeaders  = "MIME-Version: 1.0\r\n";
	
	if ($sEmailType == 'html') {
		$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	} else {
		$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
	}
	
	$sHeaders .= "From:$sGblEmailFrom\r\n";
	
	if ($sTestEmailTo != '') {
		mail($sTestEmailTo, $sEmailSub, $sEmailMessage, $sHeaders);
		echo "Test email sent...";
		
	} else {
		$sSelectQuery = "SELECT distinct email
						 FROM   $sTableEmails";
		$rSelectResult = mysql_query($sSelectQuery);
		echo mysql_error();
		$i=0;
		while ($oSelectRow = mysql_fetch_object($rSelectResult)) {
			$sEmailTo = $oSelectRow->email;	
			mail($sEmailTo, $sEmailSub, $sEmailMessage, $sHeaders);
			$i++;
			if ($i%100 == 0 && $i > 0) {
				echo "<BR>$i emails sent...";
			}
		}
		
		echo "<BR>$i emails sent...";
	}
} 
	
if ($sEmailType == 'html') {
	$sHtmlSelected = "selected";
} else {
	$sTextSelected = "selected";
}
	
?>

<html>
<body>
<form action ='<?php echo $PHP_SELF;?>'>
<table border=0>
<tr><td>Send Test Email To</td><td><input type=text name=sTestEmailTo value='<?php echo $sTestEmailTo;?>'></td></tr>
<tr><td>Email Subject</td><td><input type=text name=sEmailSub value='<?php echo $sEmailSub;?>'></td></tr>
<tr><td>Email Message</td><td><textarea name=sEmailMessage><?php echo $sEmailMessage;?></textarea></td></tr>
<tr><td>Email Type</td><td><select name=sEmailType>
						<option value='text' <?php echo $sTextSelected;?>>Text
						<option value='html' <?php echo $sHtmlSelected;?>>Html</td></tr>
						<tr><td></td><td><input type=submit name=sSubmit value='Send'></td></tr>
</table>
</form>
</body>
</html>

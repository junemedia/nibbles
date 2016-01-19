<?php
include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "BDA Mailing Post";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($mode == "submitted") {
	
	$sEmailHeaders = "Content-Type: text/plain; charset=iso-8859-1\r\n";
	$sEmailHeaders .= "From: $fromAddress\r\n";
	$sEmailHeaders .= "Reply-To: $fromAddress\r\n";
	$sEmailHeaders .= "Return-Path: $fromAddress\r\n";
	$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
	
	mail($toAddress, $subject, stripslashes($body), $sEmailHeaders, "-t -i -F $fromAddress -f $fromAddress");
	$sMessage .= "Message Sent";

	// start of track users' activity in nibbles 
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Sent email: $sEmailHeaders\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles

}

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminHeader.php");
?>
<FORM name="form1" method=post ACTION="<?php echo $PHP_SELF; ?>">
<?php echo $sHidden; ?>
<input name="mode" type="hidden" value="submitted">
<table border="0" align="center">
<tr>
	<td>From:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<select name="fromAddress">
		<option value="bdaadmin@amperemedia.com">BDA Admin</option>
		<option value="clibbe@amperemedia.com">Carole Libbe</option>
		<option value="jr@amperemedia.com">John Rudnick</option>
		<option value="spatel@amperemedia.com">Samir Patel</option>
		<option value="sderby@amperemedia.com">Susan Derby</option>
		</select>
	</td>
</tr>

<tr>
	<td>To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<select name="toAddress">
		<option value="blast2-bdatest@blast2.amperemedia.com" selected>blast2-bdatest@blast2.amperemedia.com</option>
		<option value="blast2-bda@blast2.amperemedia.com">blast2-bda@blast2.amperemedia.com</option>
		</select>
	</td>
</tr>

<tr>
	<td>Subject:&nbsp;&nbsp;
		<input type="text" name="subject" value="" size="115">
	</td>
</tr>

<tr>
	<td>
		<textarea name="body" rows="10" cols="100"></textarea>
	</td>
</tr>

<tr>
	<td align="center">
		<INPUT TYPE=SUBMIT VALUE="Submit">
	</td>
</tr>
</table>
</FORM>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
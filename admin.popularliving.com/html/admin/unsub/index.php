<?php

include("../../includes/paths.php");
session_start();
$sMessage = '';
if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSave) {
		$sEmail = str_replace(' ','',$sEmail);
		if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sEmail)) {
			// bad email
			$sMessage = 'Invalid Email Address';
		} else {
			// valid email so unsub user from BDA
			$rResult = dbQuery("DELETE FROM joinEmailInactive WHERE email = \"$sEmail\" AND joinListId = '215' LIMIT 1");
			$rResult = dbQuery("INSERT INTO joinEmailInactive(email, joinListId, dateTimeAdded) VALUES (\"$sEmail\", '215', now())");
			$rResult = dbQuery("INSERT INTO joinEmailUnsub(email, joinListId, dateTimeAdded, isPurge) VALUES (\"$sEmail\", '215', now(), '1')");
			$rResult = dbQuery("DELETE FROM joinEmailActive WHERE  email = \"$sEmail\" AND joinListId = '215'");
			$sMessage = "$sEmail added successfully and will be unsubscribed from BDA in next 10 mins.";
			$sEmail = '';
		}
	}
	include("../../includes/adminHeader.php");
?>


<form name=form1 action='<?php echo $_SERVER['PHP_SELF'];?>'>
<input type=hidden name=iMenuId value='<?php echo $iMenuId; ?>'>
<table bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr>
	<tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr>
	<tr>
		<td>&nbsp;</td>
		<td><b>Unsubcribe Email From BDA:</b> <input type=text name=sEmail value='<?php echo $sEmail;?>' size="100"> <br><br><br>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type=submit name=sSave value='Submit'></td>
	</tr>
	<tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr>
	<tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr><tr><td colspan="2"></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
<script>
document.form1.sEmail.focus();
</script>

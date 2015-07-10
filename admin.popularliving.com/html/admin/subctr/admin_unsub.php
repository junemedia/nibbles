<?php

exit;


include_once("subctr_config.php");
include_once("/var/www/html/subctr.popularliving.com/subctr/functions.php");

$message = '';

if ($_POST['submit'] == 'Unsubscribe From ALL') {
	$email = trim($_POST['email']);
	
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$message = "The email address you provided is not valid. Please try again.";
	}
	
	// Check DNS records corresponding to a given domain
	// Get MX records corresponding to a given domain.
	list($prefix, $domain) = split("@",$email);
	if (!getmxrr($domain, $mxhosts)) {
		$message = "The email address you provided is not valid. Please try again.";
	}
	
	
	
	
	
	
	
	
	
	/*if ($message == '') {
		$get_ids = "SELECT listid,subcampid FROM joinEmailActive WHERE email=\"$email\"";
		$get_ids_results = mysql_query($get_ids);
		echo mysql_error();
		$xx = 0;
		while ($row = mysql_fetch_object($get_ids_results)) {
			$subcampid = '2921';
			$listid = $row->listid;
			
			// insert into joinEmailUnsub
			$insert_query = "INSERT INTO joinEmailUnsub (dateTime,email,ipaddr,listid,subcampid,source,subsource,errorCode)
						VALUES (NOW(),\"$email\",\"$user_ip\",\"$listid\",\"$subcampid\",\"admin\",\"admin\",\"admin\")";
			$insert_query_result = mysql_query($insert_query);
			echo mysql_error();
			
			// delete from joinEmailActive
			$delete_query = "DELETE FROM joinEmailActive WHERE email =\"$email\" AND listid=\"$listid\"";
			$delete_query_result = mysql_query($delete_query);
			echo mysql_error();
			
			// call to function to send unsub to Arcamax
			$send_to_arcamax = Arcamax($email,$listid,$subcampid,$user_ip,'unsub'); // sub or unsub
			
			// record arcamax server response log
			$insert_log = "INSERT INTO arcamaxNewLog (dateTime,email,listid,subcampid,ipaddr,type,response)
						VALUES (NOW(),\"$email\",\"$listid\",\"$subcampid\",\"$user_ip\",\"unsub\",\"$send_to_arcamax\")";
			$insert_log_result = mysql_query($insert_log);
			echo mysql_error();
			$xx++;
		}
		$message = "<b>$email unsubscribed from all [count: $xx]</b>";
		$email = '';
	}*/
}

?>
<html>
<head>
<title>Admin Unsubscribe</title>
<style>
table {
	font-family: verdana;
	font-size:75%;
}
</style>
<script language="JavaScript">
function check_fields() {
	document.form1.email.style.backgroundColor="";
	var str = '';
	
	if (document.form1.email.value == '') {
		str += "* Please enter your email address.";
		document.form1.email.style.backgroundColor="yellow";
	}
	
	if (str == '') {
		return true;
	} else {
		alert (str);
		return false;
	}
}
</script>
</head>
<body>
<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return check_fields();">
<table border="0" align="center" width="400px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="2" align="center">
			<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2"><?php echo $message; ?></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td>Email: </td>
		<td><input type="text" name="email" size="40" maxlength="100" value="<?php echo $email; ?>"></td>
	</tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr><td colspan="2" align="center">
		<input type="submit" name="submit" value="Unsubscribe From ALL">
	</td></tr>
</table>
</form>
</body>
</html>




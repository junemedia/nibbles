<?php


echo 'blocked by samir';
exit;

// this is test script created by samir.  now it is being blocked.



if ($_GET['username'] != '') {
	$username = trim($_GET['username']);
} else {
	$username = trim($_POST['username']);
}


if ($_GET['password'] != '') {
        $password = trim($_GET['password']);
} else {
        $password = trim($_POST['password']);
}


if ($_GET['submit'] != '') {
        $submit = trim($_GET['submit']);
} else {
        $submit = trim($_POST['submit']);
}


$message = '';
$show_form_again = true;
if ($submit) {
	if ($username == 'stuart' && $password == 'stuart200') {
		echo "Login Successful!";exit;
	}
	
	echo "Sorry, you have provided an invalid security code.\n\n";
	exit;
}


?>
<html>
<head>
<title>Bots Test</title>
</head>
<body>
<FORM action='<?php echo $_SERVER['PHP_SELF']; ?>' name='form1' method=post>
<table width="50%" align="center" bgcolor="Yellow">
<tr>
	<td colspan="2"><font size="3" color="Red"><?php echo $message; ?></font></td>
</tr>
<?php if ($show_form_again) { ?>
<tr>
	<td>Username:</td>
	<td><input type="text" size="25" maxlength="15" name="username" value="<?php echo $username; ?>"></td>
</tr>
<tr>
	<td>Password:</td>
	<td><input type="password" size="25" maxlength="15" name="password" value="<?php echo $password; ?>"></td>
</tr>
<tr>
	<td>Security Code:</td>
	<td><input name="security_code" type="text" maxlength="6" size="6"><img src="../../libs/captcha.php"></td>
</tr>
<tr>
	<td colspan="2" align="center"><input type="submit" value="submit" name="submit"></td>
</tr>
<?php } ?>
</table>
</FORM>
</body>
</html>

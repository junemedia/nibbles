<?php

include_once("subctr_config.php");

$subcampid = trim($_POST['subcampid']);
$listid = trim($_POST['listid']);
$email = trim($_POST['email']);
$remote_ip = trim($_SERVER['REMOTE_ADDR']);

$listid = str_replace(" ", "", $listid);
$subcampid = str_replace(" ", "", $subcampid);

$message = '';
$server_response = '';

if (trim($_POST['submit']) == 'submit') {
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$message .= '* Please provide a valid email address.';
	}
	
	if (!strstr($email, 'junemedia.com')) {
		$message .= '* Your email address must be junemedia.com.';
	}
	
	if ($message == '') {
			$post_string = "email=$email&sublists=$listid&subcampid=$subcampid&ipaddr=$remote_ip";
			$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
			$aUrlArray = explode("//", $sPostingUrl);
			$sUrlPart = $aUrlArray[1];
			
			// separate host part and script path
			$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
			$sHostPart = ereg_replace("\/","",$sHostPart);
			$sScriptPath = substr($sUrlPart,strlen($sHostPart));
			
			if (strstr($sPostingUrl, "https:")) {
				$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
			} else {
				$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
			}
			
			if ($rSocketConnection) {
				fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
				fputs($rSocketConnection, "Host: $sHostPart\r\n");
				fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
				fputs($rSocketConnection, "Content-length: " . strlen($post_string) . "\r\n");
				fputs($rSocketConnection, "User-Agent: MSIE\r\n");
				fputs($rSocketConnection, "Authorization: Basic ".base64_encode("sc.datapass:jAyRwBU8")."\r\n");
				fputs($rSocketConnection, "Connection: close\r\n\r\n");
				fputs($rSocketConnection, $post_string);
				
				while(!feof($rSocketConnection)) {
					$server_response .= fgets($rSocketConnection, 1024);
				}
				fclose($rSocketConnection);
				
			}
			
			$server_response = $post_string."<br><br>".$server_response."<br><br><br><br>";
	} else {
		$server_response = $message;
	}
}


?>

<html>
<head>
<title>Test Signup Email</title>
<style>
body, table {
  font-family: Arial;
  font-style: normal;
  font-size: 12px;
  font-weight: normal;
  text-decoration: none;
}
</style>
</head>
<body>
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="2" align="center">
			<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
</table>
<form name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table width="50%" border="0" align="center">
<tr>
	<td style="color:red;">
	*** <b>NOTE</b>: USE THIS FORM ONLY TO TEST YOUR SIGNUP.  SIGNING UP FROM THIS FORM WILL <u><b>NOT</b></u> UPDATE NIBBLES SYSTEM/SUBSCRIPTION CENTER SYSTEM.  IT WILL SEND SIGN UP REQUEST DIRECTLY TO ARCAMAX.COM SO YOU CAN TEST WELCOME EMAIL ***
	<br><br><br><br>
	*** MAKE SURE YOU UNSUBSCRIBE YOURSELF BEFORE TRYING TO SIGNUP AGAIN OR USE NEW EMAIL ADDRESS ***  IF YOU ARE ALREADY SIGNED UP FOR THE LIST, ARCAMAX WILL COUNT IT AS DUP AND IT WILL NOT SEND YOU TEST WELCOME EMAIL.
	<br><br><br><br>
	*** EMAIL ADDRESS MUST BE JUNEMEDIA.COM
	<br><br><br><br>
	</td>
</tr>
<tr>
	<td><b>Email</b>: <input type="text" maxlength="100" size="50" name="email" id="email" value="<?php echo $email; ?>"> - must be junemedia.com email address</td>
</tr>
<tr>
	<td><b>SubcampID</b>: <input type="text" maxlength="4" size="25" name="subcampid" id="subcampid" value="<?php echo $subcampid; ?>"> - eg: 2762</td>
</tr>
<tr>
	<td><b>ListID</b>: <input type="text" maxlength="7" size="25" name="listid" id="listid" value="<?php echo $listid; ?>"> - eg: 396  OR  396,393</td>
</tr>
<tr>
	<td><input type="submit" name="submit" value="submit"></td>
</tr>
<tr>
	<td style="color:red;">
	<br><br><br><br>
	<?php echo $server_response; ?>
	<br><br><br><br>
	</td>
</tr>

<tr>
	<td style="color:red;">
	<br><br><br><br><pre>
	<?php echo print_r(get_active_listid_with_listname()); ?></pre>
	<br><br><br><br>
	</td>
</tr>




</table>
</form>
</body>
</html>

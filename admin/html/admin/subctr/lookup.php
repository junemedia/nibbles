<?php

include_once("subctr_config.php");

$message = '';
$sub_history_list = '';
$unsub_history_list = '';
$active_list = '';
$listing = '';
$user_data = '';
$email_change_list = '';
$submit = false;
$arcamax_records = '';

// this is linked in find_user/index.php.  also used in reset_bounce_count.php script
if ($_GET['GET'] == 'Y') {
	$_POST['submit'] = 'Lookup';
	$_POST['email'] = $_GET['email'];
	$_POST['userData'] = 'Y';
	$_POST['unsubHistory'] = 'Y';
	$_POST['subHistory'] = 'Y';
}

if ($_POST['submit'] == 'Lookup') {
	$email = trim($_POST['email']);
	$userData = trim($_POST['userData']);
	$unsubHistory = trim($_POST['unsubHistory']);
	$subHistory = trim($_POST['subHistory']);
	
	if (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $email)) {
		$message = "The email address you provided is not valid. Please try again.";
	}
	
	// Check DNS records corresponding to a given domain
	// Get MX records corresponding to a given domain.
	list($prefix, $domain) = split("@",$email);
	/*if (!getmxrr($domain, $mxhosts)) {
		$message = "The email address you provided is not valid. Please try again.";
	}*/
	
	
	
	if ($message == '') {
		$active_array = array();
		$get_active = "SELECT * FROM joinEmailActive WHERE email=\"$email\" ORDER BY dateTime DESC";
		$get_active_results = mysql_query($get_active);
		echo mysql_error();
		while ($active_row = mysql_fetch_object($get_active_results)) {
			if ($sBgcolorClass=="#E6E6FA") {
				$sBgcolorClass="#FFFACD";
			} else {
				$sBgcolorClass="#E6E6FA";
			}
			array_push($active_array, $active_row->listid);
			
			$subcampid_note = get_subcampid_notes ($active_row->subcampid);
			$listid_notes = get_listid_name ($active_row->listid);
			
			
			$active_list .= "<tr bgcolor=$sBgcolorClass>
							<td>$active_row->dateTime</td>
							<td>$active_row->email</td>
							<td>$active_row->ipaddr</td>
							<td>$active_row->listid <font size='1'><br>($listid_notes)</font></td>
							<td>$active_row->subcampid <font size='1'><br>($subcampid_note)</font></td>
							<td>$active_row->source</td>
							<td>$active_row->subsource</td>
						</tr>";
		}
		
		$get_list = "SELECT * FROM joinLists";
		$get_list_results = mysql_query($get_list);
		echo mysql_error();
		while ($list_row = mysql_fetch_object($get_list_results)) {
			if ($sBgcolorClass=="#E6E6FA") {
				$sBgcolorClass="#FFFACD";
			} else {
				$sBgcolorClass="#E6E6FA";
			}
			$checked = '';
			if (in_array($list_row->listid, $active_array)) {
				$checked = 'checked';
			}
			
			$discontinued = '';
			if ($list_row->isActive == 'N') {
				$discontinued = ' disabled ';
			}
			
			$listing .= "<tr bgcolor=$sBgcolorClass>
						<td><input type='checkbox' $discontinued value='$list_row->listid' onchange='process_sub_unsub(this.checked,$list_row->listid);' $checked> $list_row->title (<b>$list_row->listid => $list_row->frequency</b>) <div id='$list_row->listid'></div></td>
						</tr>";
		}
		
		
		
		
		if ($subHistory == 'Y') {
			$get_history = "SELECT * FROM joinEmailSub WHERE email=\"$email\" ORDER BY dateTime DESC";
			$get_history_result = mysql_query($get_history);
			echo mysql_error();
			while ($history_row = mysql_fetch_object($get_history_result)) {
				if ($sBgcolorClass=="#E6E6FA") {
					$sBgcolorClass="#FFFACD";
				} else {
					$sBgcolorClass="#E6E6FA";
				}
				
				$subcampid_note = get_subcampid_notes ($history_row->subcampid);
				$listid_notes = get_listid_name ($history_row->listid);
				
				$sub_history_list .= "<tr bgcolor=$sBgcolorClass>
								<td>$history_row->dateTime</td>
								<td>$history_row->email</td>
								<td>$history_row->ipaddr</td>
								<td>$history_row->listid <font size='1'><br>($listid_notes)</font></td>
								<td>$history_row->subcampid <font size='1'><br>($subcampid_note)</font></td>
								<td>$history_row->source</td>
								<td>$history_row->subsource</td>
							</tr>";
			}
		}
		
		
		
		
		if ($unsubHistory == 'Y') {
			$get_history = "SELECT * FROM joinEmailUnsub WHERE email=\"$email\" ORDER BY dateTime DESC";
			$get_history_result = mysql_query($get_history);
			echo mysql_error();
			while ($history_row = mysql_fetch_object($get_history_result)) {
				if ($sBgcolorClass=="#E6E6FA") {
					$sBgcolorClass="#FFFACD";
				} else {
					$sBgcolorClass="#E6E6FA";
				}
				
				$subcampid_note = get_subcampid_notes ($history_row->subcampid);
				$listid_notes = get_listid_name ($history_row->listid);
				
				$unsub_history_list .= "<tr bgcolor=$sBgcolorClass>
								<td>$history_row->dateTime</td>
								<td>$history_row->email</td>
								<td>$history_row->ipaddr</td>
								<td>$history_row->listid <font size='1'><br>($listid_notes)</font></td>
								<td>$history_row->subcampid <font size='1'><br>($subcampid_note)</font></td>
								<td>$history_row->source</td>
								<td>$history_row->subsource</td>
								<td>$history_row->errorCode</td>
							</tr>";
			}
		}
		
		
		if ($userData == 'Y') {
			$get_user_data = "SELECT * FROM userData WHERE email=\"$email\" LIMIT 1";
			$get_user_data_result = mysql_query($get_user_data);
			echo mysql_error();
			while ($user_row = mysql_fetch_object($get_user_data_result)) {
				$user_data .= "<tr>
								<td><b>First/Last:</b> $user_row->fname $user_row->lname</td>
								</tr><tr>
								<td><b>Addr1/Addr2:</b> $user_row->addr1, $user_row->addr2</td>
								</tr><tr>
								<td><b>City/State/Zip:</b> $user_row->city, $user_row->state $user_row->zip</td>
								</tr><tr>
								<td><b>Country:</b> $user_row->country</td>
								</tr><tr>
								<td><b>Gender:</b> $user_row->gender</td>
								</tr><tr>
								<td><b>Phone:</b> $user_row->phone_1-$user_row->phone_2-$user_row->phone_3</td>
								</tr><tr>
								<td><b>DOB:</b> $user_row->year/$user_row->month/$user_row->day</td>
							</tr>";
			}
		}
		
		
		$get_change_data = "SELECT * FROM emailChange WHERE (old_email=\"$email\" OR new_email=\"$email\") ORDER BY dateTimeAdded DESC";
		$get_change_data_result = mysql_query($get_change_data);
		echo mysql_error();
		while ($change_row = mysql_fetch_object($get_change_data_result)) {
			$email_change_list .= "<tr>
							<td>$change_row->dateTimeAdded</td>
							<td>$change_row->old_email</td>
							<td>$change_row->new_email</td>
							<td>$change_row->ip</td>
						</tr>";
		}
		
		
		
		
		$submit = true;
		
		/*
		$post_string = "email=$email&encoding=JSON";
		$sPostingUrl = 'https://www.arcamax.com/esp/bin/espsub';
		$aUrlArray = explode("//", $sPostingUrl);
		$sUrlPart = $aUrlArray[1];
		$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
		$sHostPart = ereg_replace("\/","",$sHostPart);
		$sScriptPath = substr($sUrlPart,strlen($sHostPart));
		$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
		$server_response = '';
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
		$obj = json_decode(substr($server_response,strpos($server_response, '{'),strlen($server_response)));
		$arcamax_records .= "<b>Bounce Count</b>: ".$obj->{'bouncecount'}."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='reset_bounce_count.php?email=$email'>Reset Bounce Count to Zero</a><br>";
		$arcamax_records .= "<b>Bounce Date</b>: ".$obj->{'bouncedate'}."<br>";
		$arcamax_records .= "<b>Last Open Date</b>: ".$obj->{'lodate'}."<br>";
		$arcamax_records .= "<b>Master Subscribed (0 unsub / 1 sub)</b>: ".$obj->{'msub'}."<br>";
		*/
		
		
		
		$impression_wise = file_get_contents("http://post.impressionwise.com/fastfeed.aspx?code=560020&pwd=SilCar&email=$email");
		$impression_wise = str_replace("code=560020&pwd=SilCar&email=$email&",'',$impression_wise);
	}
}

?>
<html>
<head>
<title>Lookup, Subscribe, and Unsubscribe</title>
<style>
table {
	font-family: verdana;
	font-size:75%;
}
</style>

<SCRIPT LANGUAGE=JavaScript SRC="http://r4l.popularliving.com/subctr/js/ajax.js" TYPE=text/javascript></script>
<script language="JavaScript">
function process_sub_unsub(request_type,listid) {
	if (request_type == true) {
		// checked - meaning process subscribe
		div = getObject(listid);
		txt = div.innerHTML;
		div.innerHTML = "<img src='http://r4l.popularliving.com/subctr/images/r4l_loader.gif' border='0'>";
		response=coRegPopup.send('process_sub_unsub.php?listid='+listid+'&request_type=sub&email=<?php echo $email; ?>','');
		//alert(response);
		div.innerHTML = "<font size='1' color='Green'><b>Added</b></font>";
	} else {
		// unchecked - meaning process unsubscribe
		div = getObject(listid);
		txt = div.innerHTML;
		div.innerHTML = "<img src='http://r4l.popularliving.com/subctr/images/r4l_loader.gif' border='0'>";
		response=coRegPopup.send('process_sub_unsub.php?listid='+listid+'&request_type=unsub&email=<?php echo $email; ?>','');
		//alert(response);
		div.innerHTML = "<font size='1' color='Red'><b>Removed</b></font>";
	}
	return true;
}
</script>
</head>
<body>
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="2" align="center">
			<a href="http://admin.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2" style="color:red;"><?php echo $message; ?></td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
</table>

<form name='form1' method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table border="0" align="center" width="600px" cellpadding="2" cellspacing="2">
	<tr>
		<td>Email: </td>
		<td><input type="text" name="email" size="40" maxlength="100" value="<?php echo $email; ?>">
		&nbsp;&nbsp;&nbsp;
		<input type="submit" name="submit" value="Lookup">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" name="userData" value="Y" checked>User Data
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="unsubHistory" value="Y" checked>Unsubscribe History
			&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="subHistory" value="Y" checked>Subscribe History
		</td>
	</tr>
</table>
</form>






<?php if ($submit == true) { ?>


	<table border="0" align="center" cellpadding="5" cellspacing="5" width="700px" cellpadding="2" cellspacing="2">
	<tr><td><b>Subscriptions</b></td></tr>
	<?php echo $listing; ?>
	</table>
	
	
	
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="700px" cellpadding="2" cellspacing="2">
	<tr><td><b><a href="ImpressionWise_Instructions.html" target="_blank">Impression Wise Lookup</a>: </b> <?php echo $impression_wise; ?>
	</td></tr>
	</table>
	
	
	
	
	<?php if ($email_change_list != '') { ?>
	<br><br><br><br><br><br>
		<table border="0" align="center" cellpadding="5" cellspacing="5" width="700px" cellpadding="2" cellspacing="2">
		<tr><td colspan="4" style="color:red;"><b>Email Change History</b></td></tr>
		<tr><td><b>Date/Time</b></td><td><b>Old Email</b></td><td><b>New Email</b></td><td><b>IP</b></td></tr>
		<?php echo $email_change_list; ?>
		</table>
	<?php } ?>
	
	
	
	
	
	
	
	
	
	<?php if ($active_list != '') { ?>
	<br><br><br><br><br><br>
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="800px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="7" align="left" style="color:red;"><b>Active List</b></td>
	</tr>
	<tr>
		<td><b>Date/Time</b></td>
		<td><b>Email</b></td>
		<td><b>IP</b></td>
		<td><b>ListId</b></td>
		<td><b>SubcampId</b></td>
		<td><b>Source</b></td>
		<td><b>SubSource</b></td>
	</tr>
	<?php echo $active_list; ?>
	</table>
	<?php } ?>
	
	
	
	
	
	
	<?php if ($sub_history_list != '') { ?>
	<br><br><br><br><br><br>
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="800px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="7" align="left" style="color:red;"><b>Subscribe History</b></td>
	</tr>
	<tr>
		<td><b>Date/Time</b></td>
		<td><b>Email</b></td>
		<td><b>IP</b></td>
		<td><b>ListId</b></td>
		<td><b>SubcampId</b></td>
		<td><b>Source</b></td>
		<td><b>SubSource</b></td>
	</tr>
	<?php echo $sub_history_list; ?>
	</table>
	<?php } ?>
	
	
	
	
	
	
	<?php if ($unsub_history_list != '') { ?>
	<br><br><br><br><br><br>
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="900px" cellpadding="2" cellspacing="2">
	<tr>
		<td colspan="8" align="left" style="color:red;"><b>Unsubscribe History</b></td>
	</tr>
	<tr>
		<td><b>Date/Time</b></td>
		<td><b>Email</b></td>
		<td><b>IP</b></td>
		<td><b>ListId</b></td>
		<td><b>SubcampId</b></td>
		<td><b>Source</b></td>
		<td><b>SubSource</b></td>
		<td><b>errorCode</b></td>
	</tr>
	<?php echo $unsub_history_list; ?>
	</table>
	<?php } ?>
	
	
	
	
	
	
	
	
	<?php if ($user_data != '') { ?>
	<br><br><br><br><br><br>
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="900px" cellpadding="2" cellspacing="2">
	<td align="left" style="color:red;"><b>User Data</b></td>
	<?php echo $user_data; ?>
	</table>
	<?php } ?>
	
	
	<br><br><br><br><br><br>
	<table border="0" align="center" cellpadding="5" cellspacing="5" width="900px" cellpadding="2" cellspacing="2">
	<td align="left" style="color:red;"><b>Arcamax Info</b></td>
	<td align="left"><?php echo $arcamax_records; ?></td>
	</table>



<?php } ?>


</body>
</html>


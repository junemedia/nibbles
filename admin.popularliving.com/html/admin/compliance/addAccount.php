<?php

/*******

Script to Display List/Delete  Seed Email Accounts information

*********/

include("../../includes/paths.php");

$sPageTitle = "Seed Email Accounts - Add/Edit Account";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	// Check if ISPCode is not duplicated
	$checkQuery = "SELECT *
				   FROM   seedEmailAccounts
				   WHERE  ISPCode = '$ISPCode'";
	$checkResult = mysql_query($checkQuery)  ;
	if (mysql_num_rows($checkResult) == 0) {
		
		$addQuery = "INSERT INTO seedEmailAccounts(ISPName, ISPType, ISPCode, userName, passwd, mailServer)
					 VALUES('$ISPName', '$ISPType', UPPER('$ISPCode'), '$userName', '$passwd', '$mailServer')";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add Entry: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($addQuery);
		if (! $result) {
			echo mysql_error();
		}
	} else {
		$message = "ISPCode Duplicated...";
		$keepValues = true;
	}
	
}  elseif (($sSaveClose || $sSaveNew) && ($id)) {
	
	// If record edited
	$editQuery = "UPDATE seedEmailAccounts
				  SET ISPName = '$ISPName',
					  ISPType = '$ISPType',	
					  ISPCode = UPPER('$ISPCode'),
				  userName = '$userName',
				  passwd = '$passwd',
				  mailServer = '$mailServer'
	 			  WHERE id = '$id'";	
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: $editQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	
	$result = mysql_query($editQuery);
	if (! $result) {
		echo mysql_error();
	}
}

if ($sSaveClose) {
	if ($keepValues != true) {
		echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";					
		//exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$ISPName ='';
		$ISPType = '';
		$userName = '';
		$passwd = '';
		$mailServer = '';
	}
}

if ($id != '') {
	// If Clicked to edit, get the data to display in fields and
	// buttons to edit it...
	
	$selectQuery = "SELECT *
					FROM   seedEmailAccounts
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		while ($row = mysql_fetch_object($result)) {
			$ISPName = $row->ISPName;
			$ISPType = $row->ISPType;
			$ISPCode = $row->ISPCode;
			$userName = $row->userName;
			$passwd = $row->passwd;
			$mailServer = $row->mailServer;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Prepare ISP Type options
if ($ISPType =='POP3') {
	$pop3Selected = "selected";
} else if ($ISPType == 'WebMail') {
	$webMailSelected = "selected";
}

$ispOptions = "<option value='POP3' $pop3Selected>POP3
				<option value='WebMail' $webMailSelected>Web Mail";

// Hidden variable to be passed with Form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value=$id>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>ISP Name</td>
		<td><input type=text name='ISPName' value='<?php echo $ISPName;?>'></td>
	</tr>
	<tr><td>ISP Type</td>
		<td><select name='ISPType'>
		<?php echo $ispOptions;?>
		</select></td>
	</tr>	
	<tr><td>ISP Code</td>
		<td><input type=text name='ISPCode' value='<?php echo $ISPCode;?>'></td>
	</tr>
	<tr><td>User Name</td>
		<td><input type=text name='userName' value='<?php echo $userName;?>'></td>
	</tr>
	<tr><td>Password</td>
		<td><input type=text name='passwd' value='<?php echo $passwd;?>'></td>
	</tr>		
	<tr><td>Mail Server</td>
		<td><input type=text name='mailServer' value='<?php echo $mailServer;?>'></td>
	</tr>			
	
</table>


<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

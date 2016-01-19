<?php

/*********

Script to Display Add/Edit/ Polls

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Polls Management - Add/Edit Poll";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($delete) {
	// if a poll option deleted
	// Poll will not be deleted from this script (It's from index.php in the same folder)
	
	$deleteQuery = "DELETE FROM edPollOptions
				    WHERE  id = '$delete'";	

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = mysql_query($deleteQuery);
	
	if (!($result)) {
		echo mysql_error();
	}
}

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	$question = addslashes($question);
	$addQuery = "INSERT INTO edPolls(question, isActive)
					 VALUES('$question', '$isActive')";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = mysql_query($addQuery);
	if ($result) {
		
		$sCheckQuery = "SELECT id
		   FROM   edPolls
		   WHERE  question = '$question'
		   AND isActive = '$isActive'"; 
		$rCheckResult = dbQuery($sCheckQuery);
		$sRow = dbFetchObject($rCheckResult);
		
		
		
		$pollId = $sRow->id;
		// Set other polls inactive if this poll is set as active
		if ($isActive == 'Y') {
			$updateQuery = "UPDATE edPolls
							SET    isActive = ''
							WHERE  id != '$pollId'";
			$updateResult = mysql_query($updateQuery);
		}
		
		for ($i = 0; $i < count($newPollOption); $i++) {
			$addOptQuery = "INSERT INTO edPollOptions(pollId, optionValue, votes)
								VALUES('$pollId', '".$newPollOption[$i]."', 0)";

			$result = mysql_query($addOptQuery);
		}
	} else {
		echo mysql_error();
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	$question = addslashes($question);
	$editQuery = "UPDATE edPolls
				  	  SET 	 question = '$question',
							 isActive = '$isActive'		
				  	  WHERE  id = '$id'";	

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = mysql_query($editQuery);
	if ($result) {
		// Set other polls inactive if this poll is set as active
		if ($isActive == 'Y') {
			$updateQuery = "UPDATE edPolls
							SET    isActive = ''
							WHERE  id != '$id'";
			$updateResult = mysql_query($updateQuery);
		}
		
		// Traverse through all existing poll options and edit them
		while (list($key, $value) = each($pollOption)) {
			$editOptQuery = "UPDATE edPollOptions
								 SET optionValue = '$value'
								 WHERE id = '$key'";
			$result = mysql_query($editOptQuery);
		}
		
		// Insert all newly added poll options
		while (list($key, $value) = each($newPollOption)) {
			$addOptQuery = "INSERT INTO edPollOptions(pollId, optionValue, votes)
								VALUES('$id', '".$value."', 0)";
			$result = mysql_query($addOptQuery);
		}
	}
}

if ($sSaveClose) {
	
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	// exit from this script
	exit();
	
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	$question = '';
	$isActive = '';
	
}

$optionNo = 0;
$optionQuery = "SELECT *
				FROM   edPollOptions
				WHERE  pollId = '$id'";
$optionResult = mysql_query($optionQuery);

while ($optionRow = mysql_fetch_object($optionResult)) {
	// display new option value if changed after adding new options
	if ($pollOption[$optionRow->id]) {
		$optionValue = ascii_encode($pollOption[$optionRow->id]);
	} else {
		$optionValue = ascii_encode($optionRow->optionValue);
	}
	// Display existing poll options with delete link for each of them
	$pollOptions .="<tr><td>Option $optionNo</td><Td><input type=text name='pollOption[".$optionRow->id."]' value='".$optionValue."'> &nbsp; <a href='JavaScript:delOpt(".$optionRow->id.");'>Delete</a></td></tr>";
	$optionNo++;
}

// Display currently added new poll options except last one, without delete link
for ($i = $optionNo; $i < $addOption; $i++) {
	
	$pollOptions .= "<tr><td>Option $i a</td><Td><input type=text name='newPollOption[".$i."]' value='".ascii_encode(stripslashes($newPollOption[$i]))."'></td></tr>";
	$optionNo++;
}

// Display the last currently added new poll option with delete link for it
if (isset($addOption) && $addOption >= $optionNo) {
	$pollOptions .= "<tr><td>Option $optionNo b</td><Td><input type=text name='newPollOption[".$addOption."]' value='".ascii_encode(stripslashes($newPollOption[$addOption]))."'> &nbsp; <a href='JavaScript:addOpt(".($optionNo-1).");'>Delete</a></td></tr>";
	$optionNo++;
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edPolls
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$question = $row->question;			
			//$question = ascii_encode($row->question);			
			$isActive = $row->isActive;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
}  else {
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

if ($isActive == 'Y') {
	$isActiveChecked = "checked";
} else {
	$isActiveChecked = '';
}

$question = ascii_encode(stripslashes($question));

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>
			<input type=hidden name=addOption value=''>
			<input type=hidden name=delete>";

$addOptionLink = "<a href='JavaScript:addOpt(".$optionNo.");'>Add Option</a>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<script language=JavaScript>
	function addOpt(addOption) {
		document.forms[0].elements['addOption'].value=addOption;
		document.forms[0].submit();
	}
	function delOpt(optNo) {
		document.forms[0].elements['delete'].value=optNo;
		document.forms[0].submit();
	}
</script>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Question</td>
		<td><input type=text name='question' value="<?php echo $question;?>" size=60></td>
	</tr>
	<tr><td>Is Active</td>
		<td><input type=checkbox name='isActive' value='Y' <?php echo $isActiveChecked;?>></td>
	</tr>
	
	
	<?php echo $pollOptions;?>
	<tr><td><?php echo $addOptionLink;?></td></tr>
			
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
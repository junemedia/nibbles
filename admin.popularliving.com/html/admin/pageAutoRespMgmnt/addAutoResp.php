<?php

/*********

Script to Display Add/Edit Auto Responder

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Auto Responders - Add/Edit Auto Responders";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new approved word added

	$sAddQuery = "INSERT INTO pageAutoResponders(pageId, emailFormat, emailFromAddr, emailSub, emailText, replyTo)
				 VALUES('$iPageId', '$sEmailFormat', '$sEmailFromAddr', \"$sEmailSub\", \"".addslashes($sEmailText)."\", \"$sReplyTo\")";

	// start of track users' activity in nibbles 
	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")";
	$rLogResult = dbQuery($sLogAddQuery); 
	// end of track users' activity in nibbles		
	
	$rResult = dbQuery($sAddQuery);
	if (!($rResult))
		$sMessage = dbError();
		
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
		$sEditQuery = "UPDATE pageAutoResponders
				  		SET   pageId = '$iPageId',
							  emailFormat = '$sEmailFormat',
						      emailFromAddr = \"$sEmailFromAddr\",
							  emailSub = \"$sEmailSub\",
							  emailText = \"".addslashes($sEmailText)."\",
							  replyTo = \"$sReplyTo\"
					  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		// end of track users' activity in nibbles		
		
		$rResult = dbQuery($sEditQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}	
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != '') {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	

		$sEmailFormat = '';
		$iPageId = '';
		$sEmailFromAddr = '';
		$sEmailSub = '';
		$sEmailText = '';
		$sReplyTo = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM pageAutoResponders
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$iPageId = $oSelectRow->pageId;
		$sEmailFormat = $oSelectRow->emailFormat;
		$sEmailFromAddr = $oSelectRow->emailFromAddr;
		$sEmailSub = ascii_encode($oSelectRow->emailSub);
		$sEmailText = ascii_encode($oSelectRow->emailText);
		$sReplyTo = $oSelectRow->replyTo;
	}
} else {
	
	if ($sEmailFormat == '') {
		$sEmailFormat = 'text';
	}
	$sEmailSub = ascii_encode(stripslashes($sEmailSub));
	$sEmailText = ascii_encode(stripslashes($sEmailText));
		
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}



// Prepare Page layout options for selection box
$sPageQuery = "SELECT *
			   FROM   otPages
			   ORDER BY pageName";

$rPageResult = dbQuery($sPageQuery);

while ($oPageRow = dbFetchObject($rPageResult)) {
	if ($oPageRow->id == $iPageId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sPageOptions .= "<option value=$oPageRow->id $sSelected>$oPageRow->pageName";
}

$sEmailFormatHtmlChecked = '';
$sEmailFormatTextChecked = '';
if ($sEmailFormat == 'html') {
	$sEmailFormatHtmlChecked = "checked";
} else {
	$sEmailFormatTextChecked = "checked";
}
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";


include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td>OT Page</td>
			<td><select name=iPageId>
				<?php echo $sPageOptions;?>
				</select></td>
		</tr>
		<tr><TD>EmailFormat</td>
		<td><input type=radio name=sEmailFormat value='text' <?php echo $sEmailFormatTextChecked;?>> Text 
		<input type=radio name=sEmailFormat value='html' <?php echo $sEmailFormatHtmlChecked;?>> Html</td></tr>		
		<tr><td>Email From Address</td>
			<td><input type=text name=sEmailFromAddr value='<?php echo $sEmailFromAddr;?>' size=40></td>
		</tr>
		<tr><td>Email Subject</td>
			<td><input type=text name=sEmailSub value='<?php echo $sEmailSub;?>' size=40></td>
		</tr>
		<tr><td valign=top>Email Text</td>
			<td><textarea name=sEmailText rows=20 cols=65><?php echo $sEmailText;?></textarea></td>
		</tr>
		<tr><td>Reply To</td>
			<td><input type=text name=sReplyTo value='<?php echo $sReplyTo;?>' size=40></td>
		</tr>
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
<?php

/*********

Script to Display Add/Edit Payment Method

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Email Contents - Add/Edit Email Content";
if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew)) {

	// Prepare comma-separated Join List if record added or edited
	
	$sListQuery = "SELECT id
					FROM   joinLists
					ORDER BY title";
	
	$rListResult = dbQuery($sListQuery);
	$i = 0;
	while ($oListRow = dbFetchObject($rListResult)) {

		// prepare Categories of this offer
		$sCheckboxName = "list_".$oListRow->id;

		$iCheckboxValue = $$sCheckboxName;

		if ($iCheckboxValue != '') {
			$aListArray[$i] = $iCheckboxValue;
			$sListString .= $iCheckboxValue.",";
			$i++;
		}
	}

	 if (!($iId)) {
		// if new email content added
	
		$sAddQuery = "INSERT INTO dbMails(triggerDays, triggerLookBackDays, trigg, emailFormat, emailFrom, emailSub, emailBody, isActive)
					 VALUES('$iTriggerDays', '$iTriggerLookBackDays', '$sTrigger', '$sEmailFormat', \"$sEmailFrom\", \"$sEmailSub\", \"$sEmailBody\", '$iIsActive')";

		
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Inserted: " . addslashes($sAddQuery) . "\")"; 
		$rLogResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();	
	
	
	} else if ($iId) {
	
		$sEditQuery = "UPDATE dbMails
					  SET triggerDays = '$iTriggerDays',
						  triggerLookBackDays = '$iTriggerLookBackDays',
						  trigg = '$sTrigger',
						  emailFormat = '$sEmailFormat',
						  emailFrom = \"$sEmailFrom\", 
						  emailSub = \"$sEmailSub\",
						  emailBody = \"$sEmailBody\",
						  isActive = '$iIsActive'
					  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Updated: " . addslashes($sEditQuery) . "\")"; 
		$rLogResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
	
		if (!($rResult)) {
			$sMessage = dbError();
		}
	
	}	
	
	
	// Delete records from dbMailsMap with the ListId which are not checked
					
					// remove last comma from the join list
					
					$sListString = substr($sListString, 0, strlen($sListString)-1);
					// Delete if any page unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM dbMailsMap
									 WHERE  dbMailId = '$iId'";
					if ($sListString != '') {
						$sDeleteQuery .= " AND joinListId NOT IN (".$sListString.")";
					}

		
					// start of track users' activity in nibbles 
					$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			
					$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Deleted: $sDeleteQuery\")"; 
					$rLogResult = dbQuery($sAddLogQuery); 
					echo  dbError(); 
					// end of track users' activity in nibbles		
				

					$rDeleteResult = dbQuery($sDeleteQuery);
					
					if (count($aListArray) > 0) {
						for ($i = 0; $i<count($aListArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   dbMailsMap
							   WHERE  joinListId = ".$aListArray[$i]."
							   AND    dbMailId = '$iId'";
							$rCheckResult = dbQuery($sCheckQuery);
							
							if (dbNumRows($rCheckResult) == 0) {
								// INSERT OfferCategoryRel record
								
								$sInsertQuery = "INSERT INTO dbMailsMap (joinListId, dbMailId)
												VALUES('".$aListArray[$i]."', '$iId')";

								// start of track users' activity in nibbles 
								$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
						
								$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
								  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Inserted: $sInsertQuery\")"; 
								$rLogResult = dbQuery($sAddLogQuery); 
								echo  dbError(); 
								// end of track users' activity in nibbles		
								
								
								$rInsertResult = dbQuery($sInsertQuery);
								echo dbError();
							} 
						}
					}
					
					echo dbError();
					

					
					
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
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
								window.opener.location.reload();
								</script>";
			//$iJoinListId = '';
			$iTriggerDays = '';
			$iTriggerLookBackDays = '';
			$sTrigger = '';
			$sEmailFormat = '';
			$sEmailFrom = '';
			$sEmailSub = '';
			$sEmailBody = '';
			$iIsActive = '';
		}
	}
}


if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	

	$sSelectQuery = "SELECT * FROM dbMails
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		//$iJoinListId = $oSelectRow->joinListId;
		$iTriggerDays = $oSelectRow->triggerDays;
		$iTriggerLookBackDays = $oSelectRow->triggerLookBackDays;
		$sTrigger = $oSelectRow->trigg;
		$sEmailFormat = $oSelectRow->emailFormat;
		$sEmailFrom = $oSelectRow->emailFrom;
		$sEmailSub = ascii_encode($oSelectRow->emailSub);
		$sEmailBody = ascii_encode($oSelectRow->emailBody);
		$iIsActive = $oSelectRow->isActive;
	}
	
} else {
	
		$sEmailFrom = "Support@MyFree.com";
		$sEmailSub = ascii_encode(stripslashes($sEmailSub));
		$sMessageBody = ascii_encode(stripslashes($sMessageBody));
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// create join list options

$sAnySelected = '';
if ($iJoinListId <0) {
	$sAnySelected = "selected";
}


// Prepare checkboxes for Join Lists
$sListQuery = "SELECT *
			    FROM  joinLists
				ORDER BY title";
$rListResult = dbQuery($sListQuery);

$j = 0;
$sListCheckboxes = "<tr>";
while ($oListRow = dbFetchObject($rListResult)) {
	$iTempJoinListId = $oListRow->id;
	$sListTitle = $oListRow->title;
	
	
	$sMailMapQuery = "SELECT *
				   FROM   dbMailsMap
				   WHERE  joinListId = '$iTempJoinListId'
				   AND    dbMailId = '$iId'";
	
	$rMailMapResult = dbQuery($sMailMapQuery);
	
	if (dbNumRows($rMailMapResult) > 0) {
		$sListChecked  = "checked";
	} else {
		$sListChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sListCheckboxes .= "</tr>";
		}
		$sListCheckboxes .= "<tr>";
	}
		
	/*if ($sCategory != $sOldCategory || $sOldCategory == '') {
		$sMenuCheckboxes .= "</tr><tr><td></td><td colspan=5><b>$sCategory</b></td></tr><tr>";
		$j=0;
		
	}*/
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sListCheckboxes .= "</tr>";
		}
		$sListCheckboxes .= "<tr>";
	}
	
	$sListCheckboxes .= "<td width=5% valign=top><input type=checkbox name='list_".$oListRow->id."' value='".$oListRow->id."' $sListChecked></td><td  width=28%>$sListTitle</td>";
	$j++;	
}
$sListCheckboxes .= "</tr>";
$sCheckAllLink = "<tr><td colspan=6><a href = 'JavaScript:checkAll();'>Check All</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href = 'JavaScript:uncheckAll();'>Uncheck All</a></td></tr>";

$sCheckAllJavaScript = "
			<script language=JavaScript>
			function checkAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
				var eleName = document.form1.elements[i].name;

        	    if (elm.type == 'checkbox' && eleName != 'iIsActive') {
                    	elm.checked = true;            	   
            	}
					
            }
			}

		function uncheckAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
				var eleName = document.form1.elements[i].name;
        	    if (elm.type == 'checkbox' && eleName != 'iIsActive') {            	   
                    	elm.checked = false;            	   
            	}
					
            }
			}
				</script>
				";







// prepare trigger options

$sConfirmSelected = '';
$sUnsubSelected = '';
$sSubSelected = '';

switch ($sTrigger) {
	case "C":
		$sConfirmSelected = "selected";
		break;
	case "U":
		$sUnsubSelected = "selected";
		break;
	default:
		$sSubSelected = "Selected";
		break;
}

$sTriggerOptions = "<option value='S' $sSubSelected>Subscribe
					<option value='C' $sConfirmSelected>Confirm
					<option value='U' $sUnsubSelected>Unsubscribe";

// prepare email format options
$sTextChecked = '';
$sHtmlChecked = '';

if (strtolower($sEmailFormat) == 'text') {
	$sTextChecked = "checked";
} 

if (strtolower($sEmailFormat) == 'html') {
	$sHtmlChecked = "checked";
}


$sIsActiveChecked = '';

if ($iIsActive) {
	$sIsActiveChecked = "checked";
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";
	
include("../../includes/adminAddHeader.php");	

?>
<?php echo $sCheckAllJavaScript;?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>	    
		<tr><TD>Trigger Days</td><td><input type=text name=iTriggerDays value='<?php echo $iTriggerDays;?>' size=8></td></tr>
		<tr><TD>Trigger Look Back Days</td><td><input type=text name=iTriggerLookBackDays value='<?php echo $iTriggerLookBackDays;?>' size=8></td></tr>
		<tr><TD>Trigger</td><td><select name=sTrigger>
				<?php echo $sTriggerOptions;?></select></td></tr>
		<tr><TD>Email Format</td><td><input type=radio name=sEmailFormat value='text' <?php echo $sTextChecked;?>> Text
		&nbsp; &nbsp; <input type=radio name=sEmailFormat value='html' <?php echo $sHtmlChecked;?>> Html</td></tr>
		<tr><TD>Email From</td><td><input type=text name=sEmailFrom value='<?php echo $sEmailFrom;?>' size=35></td></tr>
		<tr><TD>Email Subject</td><td><input type=text name=sEmailSub value='<?php echo $sEmailSub;?>' size=50></td></tr>				
		<tr><TD>Message Body</td><td><textarea name=sEmailBody rows=10 cols=50><?php echo $sEmailBody;?></textarea></td></tr>
		<tr><TD>Is Active</td><td><input type=checkbox name=iIsActive value='1' <?php echo $sIsActiveChecked;?>></td></tr>
		
	</table>	
		<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<?php echo $sCheckAllLink;?>
	<?php echo $sListCheckboxes;?>
		
</table>	
<?php

	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

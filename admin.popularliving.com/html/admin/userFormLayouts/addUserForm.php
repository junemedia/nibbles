<?php

/*********

Script to Display Add/Edit OT Page Layout

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles OT Page Layouts - Add/Edit User Form Layout";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new layout method added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   userFormLayouts
					WHERE  layout = '$sLayout'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "User Form  Layout already exists...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO userFormLayouts(layout, content)
					 VALUES('$sLayout', '$sContent')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if ($rResult) {
			
			$sCheckQuery = "SELECT id
			   FROM   userFormLayouts
			   WHERE  layout = '$sLayout'
			   AND content = '$sContent'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			$sRow = dbFetchObject($rCheckResult);
			
			
			
			
			$iId = $sRow->id;
		} else {
		
		$sMessage = dbError();
		}
		
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   userFormLayouts
					WHERE  layout = '$sLayout'
					AND    id != '$iId'";
	echo $sCheckQuery;
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "User Form already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE userFormLayouts
					   SET    layout = '$sLayout', 
							  content = '$sContent'
					   WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		if ($sReturnTo == 'list') {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		} else {
						
			
		echo "<script language=JavaScript>
				var usreFormValue=new String('$iId');
				var userFormText=new String('$sLayout');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var userFormOpt;

					userFormOpt              = window.opener.document.createElement('option');
					userFormOpt.value        = userFormValue;
					userFormOpt.text         = userFormText;				

					window.opener.document.form1.$sReturnTo.options.add(useFormOpt);
				
				} else {
					//if browser is not IE			
					var userFormOpt=new Option(userFormText, userFormValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=userFormOpt\");
				}		
			self.close();
			</script>";
			
		}
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		if ($sReturnTo == 'list') {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		} else {
			
			echo "<script language=JavaScript>
				var usreFormValue=new String('$iId');
				var userFormText=new String('$sLayout');
				var v2 = window.opener.document.form1.$sReturnTo.value;
				var i = window.opener.document.form1.$sReturnTo.length;
				var agt=navigator.userAgent.toLowerCase();
				if (agt.indexOf(\"msie\") != -1) {						
					var userFormOpt;

					userFormOpt              = window.opener.document.createElement('option');
					userFormOpt.value        = userFormValue;
					userFormOpt.text         = userFormText;				

					window.opener.document.form1.$sReturnTo.options.add(useFormOpt);
				
				} else {
					//if browser is not IE			
					var userFormOpt=new Option(userFormText, userFormValue);
					eval(\"window.opener.document.form1.$sReturnTo.options[i]=userFormOpt\");
				}		
			self.close();
			</script>";			
			
		}
		$iId = '';
		$sFormName = '';
		$sContent = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM userFormLayouts
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sLayout = $oSelectRow->layout;
		$sContent = $oSelectRow->content;
		
	}
} else {	
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sReturnTo value='$sReturnTo'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2>
		<BR>[START_FONT_COLOR] / [END_FONT_COLOR] will be replaced with the font opening/closing tags to apply font color of the page.						  
		<BR>[SALUTATION_OPTIONS] will be replaced with options list for salutation.	
		<BR>[FIRST] will be replaced with the value of First Name in case of prepopulation or error in the data entered by user.
		<BR>[LAST] will be replaced with the value of Last Name in case of prepopulation or error in the data entered by user.
		<BR>[ADDRESS] will be replaced with the value of Address in case of prepopulation or error in the data entered by user.
		<BR>[ADDRESS2] will be replaced with the value of Address2 in case of prepopulation or error in the data entered by user.
		<BR>[CITY] will be replaced with the value of City in case of prepopulation or error in the data entered by user.		
		<BR>[STATE_OPTIONS] will be replaced with options list for state.				
		<BR>[ZIP] will be replaced with the value of Zip in case of prepopulation or error in the data entered by user.
		<BR>[PHONE] will be replaced with the value of PhoneNo in case of prepopulation or error in the data entered by user.
		<BR>[EMAIL] will be replaced with the value of Email in case of prepopulation or error in the data entered by user.
		
		<BR>[SALUTATION_ASTERISK] will be replaced with * if error in salutation value entered by user.	
		<BR>[FIRST_ASTERISK] will be replaced with * if error in First Name entered by user.
		<BR>[LAST_ASTERISK] will be replaced with * if error in Last Name entered by user.
		<BR>[ADDRESS_ASTERISK] will be replaced with * if error in Address entered by user.
		<BR>[ADDRESS2_ASTERISK] will be replaced with * if error in Address2 entered by user.
		<BR>[CITY_ASTERISK] will be replaced with * if error in City entered by user.
		<BR>[STATE_OPTIONS_ASTERISK] will be replaced with * if error in State entered by user.
		<BR>[ZIP_ASTERISK] will be replaced with * if error in Zip entered by user.
		<BR>[PHONE_ASTERISK] will be replaced with * if error in PhoneNo entered by user.
		<BR>[EMAIL_ASTERISK] will be replaced with * if error in eMail entered by user.
		
	</td></tr>	
		<tr><TD colspan=2>Please look at www.popularliving.com/docs/userFormRules.txt to look for strict rules to follow in designing User Form Layout.</td></tr>		
		
		<tr><td colspan=2 class=message>Any changes to the user form layout requires all the pages using this form to be regenerated.</td></tr>
		
		<tr><td>User Form Name</td><td><input type=text name='sLayout' size=40 value="<?php echo $sLayout;?>"></td></tr>
		<tr><TD valign=top>User Form Layout Content</td><td><textarea name=sContent rows=20 cols=60><?php echo $sContent;?></textarea></td></tr>
	</table>	
	
<?php
	include("../../includes/adminAddFooter.php");
	} else {
	echo "You are not authorized to access this page...";
}
?>
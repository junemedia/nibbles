<?php

/*********

Script to Display/Edit Terms and Conditions Contents for MyFree.com

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Edit Terms and Conditions";

session_start();

$sSave = $_GET['sSave'];
if( $sSave == "" ) {
	$sSave = $_POST['sSave'];
}

$sTermsContent = $_GET['sTermsContent'];
if( $sTermsContent == "" ) {
	$sTermsContent = $_POST['sTermsContent'];
}

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
			
	if ($sSave) {
		
		$sTermsUpdate = "UPDATE vars SET varValue='".addslashes( $sTermsContent )."'
						WHERE system='myFree' and varName='termsAndConditions'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sTermsUpdate\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rTermsUpdate = dbQuery( $sTermsUpdate );
		echo dbError();
		
		$sMessage = "Changes Saved In Database";		
	} 

	$sTermsQuery = "select * from vars where system='myFree' and varName='termsAndConditions'";
	$rTermsResult = dbQuery( $sTermsQuery );
	echo dbError();
	
	$oRowTerms = dbFetchObject( $rTermsResult );
	$sTermsContent = $oRowTerms->varValue;
	
	$sTermsContent = stripslashes( $sTermsContent );
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
		
	include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>

<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><Td align=left valign=top>Terms and Conditions:</a></th>

<Td align=left valign=top><textarea  name=sTermsContent rows=15 cols=50><?php echo $sTermsContent;?></textarea></td>

</tr>
<tr><td></tD><td><input type=submit name='sSave' value='Save'></td></tr>
<tr><td></tD><td>This will change the Terms and Conditions entry located in the "nibbles.vars" table.</td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
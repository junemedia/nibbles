<?php 

include('../../includes/paths.php');

$sPageTitle = "Nibbles Offer Preview";
session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Index: Preview\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	include("../../includes/adminHeader.php");
?>

<form name=form1 action='http://www.popularliving.com/partners/preview/preview.php' target=_BLANK>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Offer Code</td><td><input type=textbox name=sOfferCode Value='<?php echo $sOfferCode;?>'></td></tr>
	<tr><td>Preview</td><td><input type=radio name=iPage value='1' <?php echo $sPage1Checked;?>> Page1 
	&nbsp; &nbsp; <input type=radio name=iPage value='2' <?php echo $sPage2Checked;?>> Page2 </td></tr>
	<tr><td colspan=2><input type=submit name=sPreview value='Preview'></td></tr>
	</table>
<form>

<?php

include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}

?>
						
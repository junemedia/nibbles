<?php

/***********

Script to display Redirects Report

************/

include("../../includes/paths.php");
//include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Generate New Pixel";
//$marsNLPixelTrackingPath = "http://ed.myfree.com/pixels/nlPixelTracking.php";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSave) {
		$sPixelsTrackingPath = $sGblNlPixelsTrackingPath."?src=".$newsletterCode.$month.$day.$year;
		$pixelTrackingString = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b>Pixel Tracking:</b> &nbsp; ".htmlspecialchars("<IMG src=\"".$sPixelsTrackingPath."\"  width=\"2\" height=\"3\">")."</font></center>";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Generate pixel: " . addslashes($pixelTrackingString) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
	
	
	}
	
	$year = date('Y');
	$month = date('m');
	$day = date('d');
	
	// prepare month options
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$value = $i+1;
		if ($value < 10) {
			$value ="0".($value);
		} 
		
		if ($value == $month) {
			$monthSel = "selected";
		} else {
			$monthSel = "";
		}
		
		$monthOptions .= "<option value='$value' $monthSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $day) {
			$daySel = "selected";
		} else {
			$daySel = "";
		}
		
		$dayOptions .= "<option value='$value' $daySel>$i";
	}
	
	// prepare year options
	for ($i = $year; $i <= $year+1; $i++) {
		
		if ($i == $year) {
			$yearSel = "selected";
		} else {
			$yearSel ="";
		}
		$yearVal = substr($year, 2,2);
		$yearOptions .= "<option value='$yearVal' $yearSel>$i";
	}
	
	$publicationQuery = "SELECT *
						 FROM   publications
						 ORDER BY publicationName";
	$publicationResult = mysql_query($publicationQuery);
	while ($publicationRow = mysql_fetch_object($publicationResult)) {
		if ($row->id = '$id') {
			$selected = "selected";
		} else {
			$selected = "selected";
		}
		
		$newsletterOptions .= "<option value='".$publicationRow->publicationCode."'>$publicationRow->publicationName";
	}

	$managePixelsLink = "<a href='index.php?iMenuId=$iMenuId'>Back to Manage NL Pixels<a>";
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
		
	include("../../includes/adminHeader.php");	
	
	?>
	
	<?php echo $pixelTrackingString;?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>
<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $managePixelsLink;?></td></tr>
<tr><Td>Newsletter</td><td><select name=newsletterCode><?php echo $newsletterOptions;?></select></td></tr>
<tr>
	<td>Newsletter Date</td><td><select name=month><?php echo $monthOptions;?>
	</select> &nbsp;<select name=day><?php echo $dayOptions;?>
	</select> &nbsp;<select name=year><?php echo $yearOptions;?>
	</select></td></tr>	
	<tr><td colspan=2 align=center><br><BR><input type=submit name=sSave value='Generate New Pixel'></td></tr>
</table>
</form>			

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>	
<?php

/*********

Script to Sort Offers In A Category

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles - Sort Myfree Footer Menu";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {


if ($sSaveClose || $sSaveNew) {
	// Change the sort orders
	$sTempQuery = '';
	
	if (is_array($frontPageSortOrder)) {
		while (list($key, $val) = each($frontPageSortOrder)) {
			$editQuery = "UPDATE edFooterLinks
						  SET    frontPageDisplay = '$val'
						  WHERE  id = '$key'";
			$sTempQuery .= "\n$editQuery";
			$editResult = mysql_query($editQuery);
		}
	}
	
	if (is_array($offerPageSortOrder)) {
		while (list($key, $val) = each($offerPageSortOrder)) {
			$editQuery = "UPDATE edFooterLinks
						  SET    offerPageDisplay = '$val'
						  WHERE  id = '$key'";
			$sTempQuery .= "\n$editQuery";
			$editResult = mysql_query($editQuery);
		}
	}	
	
	if (is_array($removeFromFrontPage)) {		
		while (list($key, $val) = each($removeFromFrontPage)) {
			$deleteQuery = "UPDATE edFooterLinks
							SET frontPageDisplay = ''
							WHERE  id = '$key'";
			$sTempQuery .= "\n$deleteQuery";
			$deleteResult = mysql_query($deleteQuery);
			$sMessage = '';
		}
	}
	
	if (is_array($removeFromOfferPage)) {
		
		while (list($key, $val) = each($removeFromOfferPage)) {
			$deleteQuery = "UPDATE edFooterLinks
							SET offerPageDisplay = ''
							WHERE  id = '$key'";
			$sTempQuery .= "\n$deleteQuery";
			$deleteResult = mysql_query($deleteQuery);
			$sMessage = '';
		}
	}
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sTempQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
	// exit from this script
	exit();	
}

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "linkText";
	$linkTextOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	
	case "frontPageDisplay" :
	$currOrder = $frontPageDisplayOrder;
	$frontPageDisplayOrder = ($frontPageDisplayOrder != "DESC" ? "DESC" : "ASC");
	break;
	case "offerPageDisplay" :
	$currOrder = $offerPageDisplayOrder;
	$offerPageDisplayOrder = ($offerPageDisplayOrder != "DESC" ? "DESC" : "ASC");
	break;
	default:
	$currOrder = $linkTextOrder;
	$linkTextOrder = ($linkTextOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$selectQuery = "SELECT *
				FROM   edFooterLinks
				ORDER BY ";
// Convert column to the number for proper order by result
// Column is string type to avoid 0 default value
 if ($orderColumn == "frontPageDisplay" || $orderColumn == "offerPageDisplay") {
	 	$selectQuery .= " 0x41 + ";
	 }
	 $selectQuery .= " $orderColumn $currOrder";

$selectResult = mysql_query($selectQuery);

while ($row = mysql_fetch_object($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass=="ODD") {
		$bgcolorClass="EVEN";
	} else {
		$bgcolorClass="ODD";
	}
	$footerList .= "<tr class=$bgcolorClass><TD><b>$row->linkText</b></td>
						<TD><input type=text name=frontPageSortOrder[".$row->id."] value='$row->frontPageDisplay' size=5></td><td>";
	if ($row->frontPageDisplay != '') {
		$footerList .= "<input type=checkbox name=removeFromFrontPage[".$row->id."]>";
	}
	$footerList .= "</td><TD><input type=text name=offerPageSortOrder[".$row->id."] value='$row->offerPageDisplay' size=5></td><td>";
	if ($row->offerPageDisplay != '') {
		$footerList .= "<input type=checkbox name=removeFromOfferPage[".$row->id."]>";
	}	
	$footerList .= "</td></tr>";
}
if (mysql_num_rows($selectResult) == 0) {
	$sMessage = "No offers in this category...";
}

// Hidden fields to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";

$sortLink = $PHP_SELF."?iMenuId=$iMenuId&id=$id";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=linkText&linkTextOrder=<?php echo $linkTextOrder;?>" class=header>Link Text</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=frontPageDisplay&frontPageDisplayOrder=<?php echo $frontPageDisplayOrder;?>" class=header>Front Page Sort Order</td>
	<TD class=header>Remove From Front Page</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=offerPageDisplay&offerPageDisplayOrder=<?php echo $offerPageDisplayOrder;?>" class=header>Offer Page Sort Order</td>
	<TD class=header>Remove From Offer Page</td>	
</tr>
<?php echo $footerList;?>

</table>
<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
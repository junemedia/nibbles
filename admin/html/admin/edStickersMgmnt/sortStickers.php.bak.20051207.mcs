<?php

/*********

Script to Sort Stickers and Slogans in myfree site

**********/

function getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
    } 

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Sort Stickers And Slogans In MyFree Stickers And Slogans Page";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if ($sSaveClose || $sSaveNew) {
	// Change the sort orders
	if(is_array($sortOrder)) {
		while (list($key, $val) = each($sortOrder)) {
			$editQuery = "UPDATE edStickersAndSlogans
						  SET    sortOrder = '$val'
						  WHERE  id = '$key'";
			$editResult = mysql_query($editQuery);
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
}

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "sortOrder";
	$sortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	
	case "headline" :
	$currOrder = $headlineOrder;
	$headlineOrder = ($headlineOrder != "DESC" ? "DESC" : "ASC");	
	break;
	case "description" :
	$currOrder = $descriptionOrder;
	$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
	break;	
	default:
	$currOrder = $sortOrderOrder;
	$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
	
}

// Select Query to display list of data
$selectQuery = "SELECT *
			   FROM   edStickersAndSlogans
			   WHERE  CURRENT_DATE >= activeDate AND CURRENT_DATE < inactiveDate"; 

$selectQuery .= " ORDER BY $orderColumn $currOrder";

$selectResult = mysql_query($selectQuery);

while ($row = mysql_fetch_object($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	$dispHeadline = ascii_encode($row->headline);
	$dispDescription = ascii_encode(substr($row->description,0,50));
	$stickersList .= "<tr class=$bgcolorClass><TD>$dispHeadline</td>
						<td>$dispDescription...</td>
						<TD><input type=text name=sortOrder[".$row->id."] value='$row->sortOrder' size=5></td>
						</tr>";
}
if (mysql_num_rows($selectResult) == 0) {
	$sMessage = "No Stickers Or Slogans are active...";
}


// Hidden fields to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

$sortLink = $PHP_SELF."?iMenuId=$iMenuId";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr bgcolor=#FFFFFF><td class=header colspan=4 align=center><?php echo $sPageTitle;?><BR></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=headline&headlineOrder=<?php echo $headlineOrder;?>" class=header>Headline</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>" class=header>Description</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=sortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Sort Order</td>		
</tr>
<?php echo $stickersList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->

</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>

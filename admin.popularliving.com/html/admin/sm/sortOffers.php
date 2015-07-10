<?php

/*********

Script to Sort Offers In A Category

**********/

include("../../library.php");
include("../../includes/template.php");

$pageTitle = "Sort Offers In Show Me Display Page";

// Create the template object
$t = new Template($marsWebRoot,"comment");

$t->set_file(array("main" => "addMain.phtml",
"content" => "$menuFolder/sortOffers.phtml"));

if ($saveClose || $saveNew) {
	// Change the sort orders
	if(is_array($sortOrder)) {
		while (list($key, $val) = each($sortOrder)) {
			$editQuery = "UPDATE ShowMeOffers
						  SET    sortOrder = '$val'
						  WHERE  id = '$key'";
			$editResult = mysql_query($editQuery);
		}
	}
		
	if (is_array($removeFromShowMe)) {
		
		while (list($key, $val) = each($removeFromShowMe)) {
			$updateQuery = "UPDATE ShowMeOffers
							SET sortOrder = ''
							WHERE  id = '$key'";
			$updateResult = mysql_query($updateQuery);
			$message = '';
		}
	}		
}

if ($saveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
}

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "title";
	$titleOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {	
	case "sortOrder" :
	$currOrder = $sortOrderOrder;
	$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
	break;	
	default:
	$currOrder = $titleOrder;
	$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$selectQuery = "SELECT *
				FROM   ShowMeOffers
				ORDER BY ";

 if ($orderColumn == "sortOrder") {
	 	$selectQuery .= " 0x41 + ";
	 }
	 $selectQuery .= " $orderColumn $currOrder";

$selectResult = mysql_query($selectQuery);
echo mysql_error();
while ($row = mysql_fetch_object($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass=="ODD") {
		$bgcolorClass="EVEN";
	} else {
		$bgcolorClass="ODD";
	}
	$offerList .= "<tr class=$bgcolorClass><TD><b>$row->title</b></td>
						<TD><input type=text name=sortOrder[".$row->id."] value='$row->sortOrder' size=5></td><td>";
	if ($row->sortOrder != '') {
		$offerList .= "<input type=checkbox name=removeFromShowMe[".$row->id."]>";
	}		
	$offerList .= "</td></tr>";
}
if (mysql_num_rows($selectResult) == 0) {
	$message = "No offers in this category...";
}

// Hidden fields to be passed with form submission
$hidden = "<input type=hidden name=menuId value='$menuId'>
			<input type=hidden name=menuFolder value='$menuFolder'>
			<input type=hidden name=id value='$id'>";

$sortLink = $PHP_SELF."?menuId=$menuId&menuFolder=$menuFolder&id=$id";

$newEntryButtons = "<BR><BR><input type=submit name=saveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	

// Parse variables to the template
$t->set_var(array(  "ACTION" => $PHP_SELF,
"HIDDEN" => "$hidden",
"SORT_LINK" => "$sortLink",
"TITLE_ORDER" => "$titleOrder",
"SORT_ORDER_ORDER" => "$sortOrderOrder",
"OFFER_LIST" => "$offerList",
"NEW_ENTRY_BUTTONS" => "$newEntryButtons"
));


// Parse common variables and common steps to display the template

include("../mainParse.php");

?>
<?php

/*******

Script to Add/Edit Publications

*******/

include("../../library.php");
include("../../includes/template.php");

$pageTitle = "Show Me Management - Add/Edit Offers";

// Create the template object
$t = new Template($marsWebRoot,"comment");

$t->set_file(array("main" => "addMain.phtml",
"content" => "$menuFolder/addOffer.phtml"));

if (($saveClose || $saveNew) && !($id)) {
	// if new data submitted
	$publicationName = ucfirst($publicationName);
	$addQuery = "INSERT INTO ShowMeOffers(title, url, popHeight, popWidth, textContent, htmlContent, emailType, sortOrder)
				 VALUES('$title', '$url', '$popHeight', '$popWidth', '$textContent', '$htmlContent', '$emailType', '$sortOrder')";
	
	$result = mysql_query($addQuery);

	if (! $result) {		
		echo mysql_error();
	}
	
} else if ( ($saveClose || $saveNew) && ($id)) {
		
	// If record edited
	$editQuery = "UPDATE ShowMeOffers
				  SET 	 title = '$title',
				  		 url = '$url',
				  		 popHeight = '$popHeight',
				  		 popWidth = '$popWidth',
						 textContent = '$textContent',
						 htmlContent = '$htmlContent',
						 emailType = '$emailType',
						 sortOrder = '$sortOrder'
				  WHERE  id = '$id'";
	
	$result = mysql_query($editQuery);	
	if (! $result) {		
		echo mysql_error();
	}
}

if ($saveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	// exit from this script
	exit();			
} else if ($saveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	$title = '';
	$url = '';
	$popHeight = '';
	$popWidth = '';
	$textContent = '';
	$htmlContent = '';
	$emailType = '';
	$sortOrder = '';
}

if ($id != '') {
	// If Clicked Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   ShowMeOffers
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {		
		while ($row = mysql_fetch_object($result)) {
			$title = ascii_encode($row->title);
			$url = $row->url;
			$popHeight = $row->popHeight;
			$popWidth = $row->popWidth;
			$textContent = ascii_encode($row->textContent);
			$htmlContent = ascii_encode($row->htmlContent);			
			$emailType = $row->emailType;
			$sortOrder = $row->sortOrder;			
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}	
} else {	
	// If add button is clicked, display another two buttons
	$newEntryButtons = "<BR><BR><input type=submit name=saveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

$textTypeChecked = "";
$htmlTypeChecked = "";
$noEmailChecked = "";

switch ($emailType) {
	case "text":
		$textTypeChecked = "checked";
		break;
	case "html":
		$htmlTypeChecked = "checked";
		break;		
	default:
		$noEmailChecked = "checked";
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=menuId value='$menuId'>
			<input type=hidden name=menuFolder value='$menuFolder'>
			<input type=hidden name=id value='$id'>";

// Parse variables in Template
$t->set_var(array(  "ACTION" => $PHP_SELF,
"HIDDEN" => "$hidden",
"TITLE" => "$title",
"URL" => "$url",
"NO_EMAIL_CHECKED" => "$noEmailChecked",
"TEXT_TYPE_CHECKED" => "$textTypeChecked",
"HTML_TYPE_CHECKED" => "$htmlTypeChecked",
"POP_HEIGHT" => "$popHeight",
"POP_WIDTH" => "$popWidth",
"TEXT_CONTENT" => "$textContent",
"HTML_CONTENT" => "$htmlContent",
"SORT_ORDER" => "$sortOrder",
"NEW_ENTRY_BUTTONS" => "$newEntryButtons",
"RELOAD_WINDOW_OPENER" => "$reloadWindowOpener",
));

// Include common steps to parse common variables
// and steps to display the final template
include("../mainParse.php");

?>
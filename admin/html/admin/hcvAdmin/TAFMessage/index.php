<?php

/*********

Script to You Won eMail Content

**********/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village TAF eMail Content";


session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	if ($delete) {
		// if record deleted
		$deleteQuery = "DELETE FROM emailMessages
	 			   		WHERE  id = $id"; 
		$result = dbQuery($deleteQuery);
		if (!($result)) {
			$sMessage = dbError();
		}
		$id='';
	}
	
		
	if (!($add || $id && $marsPermissions[$menuId]['perAdd']=='Y')) { //
	// display Add button
	$addButton ="<input type=submit name=add value=Add>";
	}
	else {
		$resetValue="Reset";
		
		if ($add) {
			$submitValue ="Add";
		} else {
			$submitValue ="Edit";
		}
	}
	
	
	// Select Query to display list of data
	
	$selectQuery = "SELECT *
				  FROM emailMessages";
	//$selectQuery .= " ORDER BY $orderColumn $currOrder";
	
	$selectResult = dbQuery($selectQuery);
	
	while ($row = dbFetchObject($selectResult)) {
		
		// For alternate background color
		if ($bgcolorClass=="ODD") {
			$bgcolorClass="EVEN";
		} else {
			$bgcolorClass="ODD";
		}
		$emailList .= "<tr class=$bgcolorClass>
						<TD>$row->subject</td>
						<TD>".htmlentities($row->message)."</td><td>
						<a href='JavaScript:void(window.open(\"addMessage.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&id=".$row->id."\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));' >Edit</a>
						</td></tr>";
	}
	
	if (dbNumRows($selectResult)==0) {
		$sMessage = "No records exist...";
	}
	
	// Hidden fields to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=id value='$id'>";
		
	
	include("$sGblIncludePath/adminHeader.php");	

	echo $hidden;
?>
			
		
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<Td align=left valign=top class=header>Subject</td>

<Td align=left valign=top class=header>Message Body</td>
<Td></Td>
</tr>

<?php echo $emailList;?>

</table>


	
<?php
// include footer

include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>
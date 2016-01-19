<?php

/*********

Script toFun Page TAF Message

**********/

include("../../../includes/paths.php");

$sPageTitle = "Fun Page TAF eMail Content";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {

	if ($delete) {
		// if record deleted
		$deleteQuery = "DELETE FROM fpEmailMessages
	 			   		WHERE  id = '$id'"; 
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($deleteQuery);
		if (!($result)) {
			$message = mysql_error();
		}
		$id='';
	}
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addMessage.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

	if ($id) {
		
		// If Clicked to edit, get the data to display in fields and
		// buttons to edit it...
		
		
		$messageQuery = "SELECT *
				  		 FROM 	fpEmailMessages";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display Content: $messageQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$messageResult = mysql_query($messageQuery);
		while ($messageRow = mysql_fetch_object($messageResult)) {
			$id = $messageRow->id;
		//	$emailPurpose = $messageRow->purpose;
			$subject = $messageRow->subject;
			$messageBody = $messageRow->message;
		}
		
	}
	// Select Query to display list of data
	
	$selectQuery = "SELECT *
				 	FROM   fpEmailMessages";
	//$selectQuery .= " ORDER BY $orderColumn $currOrder";
	
	$selectResult = mysql_query($selectQuery);
	
	while ($row = mysql_fetch_object($selectResult)) {
		
		// For alternate background color
		if ($bgcolorClass=="ODD") {
			$bgcolorClass="EVEN";
		} else {
			$bgcolorClass="ODD";
		}
		$emailList .= "<tr class=$bgcolorClass>
						<TD>$row->subject</td>
						<TD>".htmlspecialchars($row->message)."</td><td>
						<a href='JavaScript:void(window.open(\"addMessage.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&id=".$row->id."\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));' >Edit</a></td></tr>";
	}
	
	if (mysql_num_rows($selectResult)==0) {
		$message = "No records exist...";
	}
	
	// Hidden fields to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
		
	include("$sGblIncludePath/adminHeader.php");	
	
	?>
	
	
<script language=JavaScript>

	function confirmDelete(form1,id)

	{

		if(confirm('Are you sure to delete this record ?'))

		{							

			document.form1.elements['delete'].value='Delete';

			document.form1.elements['id'].value=id;

			document.form1.submit();								

		}

	}						

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>

<input type=hidden name=delete>
		
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
<Td align=left valign=top class=header>Subject</td>

<Td align=left valign=top class=header>Message Body</td>
<TH></TH>
</tr>

<?php echo $emailList;?>

</table>


</form>


<?php
include("../../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
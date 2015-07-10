<?php

/*********

Script to Display List/Delete Publication Information

*********/

include("../config.php");
session_start();

$pageTitle = "Show Me Management";

if ($login) {
	if ($passwd == $adminPasswd) {
		session_register("showMeAdmin");
		$showMeAdmin = "admin";
	}
	
}
// Check if user is permitted to view this page
//if (session_is_registered("anlUserId")) {
if (session_is_registered("showMeAdmin")) {
	//&& $marsPermissions[$menuId]['perView']=='Y'
	
	if ($delete) {
					
		$deleteQuery = "DELETE FROM ShowMeOffers
			   			WHERE id = '$id'"; 
		$result = mysql_query($deleteQuery);
		if ( $result) {
			// delete from Tracking tables
			$delete1Query = "DELETE FROM ShowMeTracking
							 WHERE  offerId = '$id'";
			$delete1Result = mysql_query($delete1Query);
			
		} else {
			echo mysql_error();
		}
		
		//reset $id to null
		//$id = '';
	}
	// set default order by column
	if (!($orderColumn)) {
		$orderColumn = "title";
		$titleOrder = "ASC";
	}
	
	switch ($orderColumn) {
		
		case "content" :
		$currOrder = $contentOrder;
		$contentOrder = ($contentOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "url" :
		$currOrder = $urlOrder;
		$urlOrder = ($urlOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default :
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of Show Me offers
	$selectQuery = "SELECT *
					FROM ShowMeOffers
	 				ORDER BY $orderColumn $currOrder";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		$numRecords = mysql_num_rows($result);
		
		if ($numRecords > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$title = ascii_encode($row->title);
				$textContent = ascii_encode(substr($row->textContent,0,25));
				$htmlContent = ascii_encode(substr($row->htmlContent,0,25));
				
				$emailType = $row->emailType;
				
				if ($row->emailType == '')
					$emailType = "No eMail";
				//<td>$row->popHeight</td>
					//<td>$row->popWidth</td>
					
				$offerList .= "<tr class=$bgcolorClass>
					<td>$title</td>
					<td>$row->url</td>
					<td>$emailType</td>
					<td>$textContent</td>
					<td>$htmlContent</td>
					<td><a href='JavaScript:void(window.open(\"addOffer.php?id=".$row->id."\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$message = "No records exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	// Display Add Button 
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addOffer.php\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	$sortLink = $PHP_SELF;
	?>
	
	<html>

<head>
<title><?php echo "$pageTitle";?></title>
<LINK rel="stylesheet" href="<?php echo "$showMeSiteRoot";?>/styles.css" type="text/css" >
</head>

<body>
<br>
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
		
<form name=form1 action='{ACTION}'>

<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td><?php echo "$addButton";?></td><td><a href='reportByDate.php'>Report By Date</a> &nbsp; &nbsp; <a href='reportBySrc.php'>Report By SourceCode</a></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>?orderColumn=title&titleOrder=<?php echo "$titleOrder";?>" class=header>Title</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>?orderColumn=url&urlOrder=<?php echo "$urlOrder";?>" class=header>URL</a></td>	
	<TD class=header><a href="<?php echo $sortLink;?>?orderColumn=emailType&emailTypeOrder=<?php echo "$emailTypeOrder";?>" class=header>eMail Type</a></td>		
	<TD class=header><a href="<?php echo $sortLink;?>?orderColumn=textContent&textContentOrder=<?php echo "$textContentOrder";?>" class=header>Text Content</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>?orderColumn=htmlContent&htmlContentOrder=<?php echo "$htmlContentOrder";?>" class=header>HTML Content</a></td>
	<!--<td class=header>PopUp Height</td>	
	<td class=header>PopUp Width</td>	-->
	<th>&nbsp; </th>
</tr>
<?php echo "$offerList";?>
<tr><Td><?php echo "$addButton";?></td></tr>
</table>
</form>
<br>

</body>

</html>

<?php

} else { ?>
	<form name=form1 action='<?php echo $PHP_SELF;?>'>
		<table align=center width=30%>
		<tr><td>Please Enter the passwd...</tD></tr>
		<tr><td><input type=password name=passwd></tD></tR>
		<tr><td><input type=submit name=login value='Login'></tD></tr>
		</table>
<?php
}
?>
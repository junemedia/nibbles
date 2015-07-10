<?php

/*******

Script to Add/Edit Publications

*******/

include("../config.php");

$pageTitle = "Show Me Management - Add/Edit Offers";

if (($saveClose || $saveNew) && !($id)) {
	// if new data submitted
	$publicationName = ucfirst($publicationName);
	$addQuery = "INSERT INTO ShowMeOffers(title, url, popHeight, popWidth, textContent, htmlContent, emailType)
				 VALUES('$title', '$url', '$popHeight', '$popWidth', '$textContent', '$htmlContent', '$emailType')";
	
	$result = mysql_query($addQuery);
	echo mysql_error();
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
						 emailType = '$emailType'
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
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
	
} else {	
	$newEntryButtons = "<BR><BR><input type=submit name=saveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

$textTypeChecked = "";
$htmlTypeChecked = "";
$noEmailChecked = "";
switch($emailType) {
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
$hidden = "<input type=hidden name=id value='$id'>";
?>

<html>
<head>
<title><?php echo "$pageTitle";?></title>
<LINK rel="stylesheet" href="<?php echo "$showMeSiteRoot";?>/styles.css" type="text/css" >
</head>

<body>
<br>
<form action="<?php echo $PHP_SELF;?>" method=post>
<?php echo "$hidden";?>
<table width=95% align=center><tr><TD class=message align=center><?php echo "$message";?></td></tr></table>
<?php echo "$reloadWindowOpener";?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr><td>Offer Title</td>
		<td><input type=text name='title' value='<?php echo "$title";?>' size=50></td>
	</tr>
	<tr><td>URL</td>
		<td><input type=text name='url' value='<?php echo "$url";?>'></td>
	</tr>
	<tr><td>eMail Type</td>
		<td><input type=radio name='emailType' value='' <?php echo "$noEmailChecked";?>> No eMail
			<input type=radio name='emailType' value='text' <?php echo "$textTypeChecked";?>> Text			
			<input type=radio name='emailType' value='html' <?php echo "$htmlTypeChecked";?>> HTML
		</td>
	</tr>
	<tr><td>PopUp Height</td>
		<td><input type=text name='popHeight' value='<?php echo "$popHeight";?>'></td>
	</tr>				
	<tr><td>PopUp Width</td>
		<td><input type=text name='popWidth' value='<?php echo "$popWidth";?>'></td>
	</tr>					
	<tr><td>Text Content</td>
		<td><textarea name=textContent rows=5 cols=40><?php echo $textContent;?></textarea></td>
	</tr>					
	<tr><td>HTML Content</td>
		<td><textarea name=htmlContent rows=5 cols=40><?php echo $htmlContent;?></textarea></td>
	</tr>						

</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=saveClose value='Save & Close'> &nbsp; &nbsp; 
		<input type=button name=abandonClose value='Abandon & Close' onclick="self.close();" >
		<?php echo "$newEntryButtons";?></td><td></td>
	</tr>	
	</table>
</form>
</body>
</html>

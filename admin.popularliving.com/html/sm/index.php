<?php

/*********

Script to Display List/Delete Publication Information

*********/

include("config.php");

$pageTitle = "Show Me Management";

$prevQueryString = urldecode($prevQueryString);
$queryStringArray = explode("&", $prevQueryString);
for ($i=0;$i<count($queryStringArray); $i++) {
	$urlPart = explode("=",$queryStringArray[$i]);
	if($urlPart[0] == "src")
	$src = $urlPart[1];
}


if ($save) {
	// When clicked on proceed
	
	$selectQuery = "SELECT *
						FROM   ShowMeOffers";
	$selectResult = mysql_query($selectQuery);
	while ($selectRow = mysql_fetch_object($selectResult)) {
		$id = $selectRow->id;
		$temp = "offer_".$id;
		if ($$temp == "Y")
		{
			// update tracking table
			$updateQuery = "UPDATE ShowMeTracking
								SET    pickCounts = pickCounts + 1
								WHERE  dateShown = CURRENT_DATE
								AND    sourceCode = '$src'
								AND    offerId = '$id'";
			$updateResult = mysql_query($updateQuery);
			echo mysql_error();
			if ($selectRow->emailType != '') {
				$headers = "From: $emailFrom\r\n";
				if ($selectRow->emailType == "html") {
					$headers .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
					$emailContent = $selectRow->htmlContent;
				} else {
					$emailContent = $selectRow->textContent;
				}
				mail($e, $selectRow->title, $emailContent, $headers);
			}
		}
	}
	header("Location:http://www.kids.com?".$prevQueryString);
} else {
	// Insert entry in Stat tables
	// Check if record exists
	$checkQuery = "SELECT *
				   FROM   ShowMePageCounts
				   WHERE dateShown=CURRENT_DATE
				   AND   sourceCode = '$src'  ";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult)==0) {
		//insert record
		$insertQuery = "INSERT INTO ShowMePageCounts(dateShown, sourceCode, counts)
						VALUES(CURRENT_DATE, '$src', 1)";
		$insertResult = mysql_query($insertQuery);
	} else {
		//update record
		$updateQuery = "UPDATE ShowMePageCounts
						SET    counts = counts+1
						WHERE  dateShown=CURRENT_DATE
						AND    sourceCode = '$src'";
		$updateResult = mysql_query($updateQuery);
	}
}


// Query to get the list of Show Me offers
$selectQuery = "SELECT *
				FROM ShowMeOffers
	 			ORDER BY title";
$result = mysql_query($selectQuery);

if ($result) {
	$numRecords = mysql_num_rows($result);
	
	if ($numRecords > 0) {
		
		while ($row = mysql_fetch_object($result)) {
			$offerId = $row->id;
			$title = ascii_encode($row->title);
			
			$offerList .= "<tr class=$bgcolorClass>
					<td><input type=radio name=offer_".$row->id." value='Y' ";
			if ($displayMode == "popAndEmail") {
				$offerList .="onClick='showme(\"".$row->id."\",\"".$row->url."\",".$row->popHeight.",".$row->popWidth.");'";
			}
			$offerList .= "> <b>Show Me!</b> &nbsp;
						&nbsp;<input type=radio name=offer_".$row->id." value='N'> No___$title</td>					
					</tr>";			
			
			// Prepare offers to be used in stat query
			//$offers .= $row->id.",";
			// Insert/update record into Tracking table
			if(!($save)) {
			$checkQuery = "SELECT *
						   FROM   ShowMeTracking
						   WHERE  dateShown=CURRENT_DATE
						   AND    sourceCode = '$src'
						   AND    offerId='$offerId'";
			
			$checkResult = mysql_query($checkQuery);
			if (mysql_num_rows($checkResult) == 0) {
				// insert query
				$insertQuery = "INSERT INTO ShowMeTracking(dateShown, sourceCode, offerId, displayCounts, pickCounts)
								VALUES(CURRENT_DATE, '$src', '$offerId', '1', '0')";
				$insertResult = mysql_query($insertQuery);
			} else {
				//update query
				$updateQuery = "UPDATE ShowMeTracking
								SET    displayCounts = displayCounts + 1
								WHERE  dateShown = CURRENT_DATE
								AND    sourceCode = '$src'
								AND    offerId = '$offerId'";
				$udpateResult = mysql_query($updateQuery);
				echo mysql_error();
			}
			}
		}
	} else {
		$message = "No records exist...";
	}
	mysql_free_result($result);
	/*	// insert/update record in stat table
	if ($offers != '') {
	$offers = substr($offers, 0, strlen($offers)-1);
	// Insert using IGNORE INTO if not exists
	$insertQuery = "INSERT IGNORE INTO ShowMeTracking(dateShown, sourceCode, offerId, displayCounts, pickCounts)
	VALUES()";
	
	}*/
	
} else {
	echo mysql_error();
}

?>
	
<html>

<head>
<title><?php echo "$pageTitle";?></title>
<LINK rel="stylesheet" href="<?php echo "$showMeSiteRoot";?>/styles.css" type="text/css" >
</head>
<script language=JavaScript>
	function showme(id, url, popH, popW) {
		var winTitle = "show"+id;		
		window.open(url, winTitle, "width="+popW+", height="+popH+", left=0, top=0, scrollbars=yes, resizable=yes ");		
		return true;
	}
	
	function funcProceed() {
		if (document.forms['form1'].elements['forceShowMe'].value=='YES') {
			var ele=document.forms['form1'].elements.length;	
			var sub = false;
			var temp='',j='', oldElement='';
			for (i=0; i < ele; i++) {
				temp = document.forms['form1'].elements[i].name;
				if (temp.substring(0,5) == 'offer') {
					
					if (oldElement != temp && oldElement != '' && sub == false) {
						break;
					} else if (oldElement == temp && sub==true) {
						continue;
					}		
					sub = document.form1.elements[i].checked;					
					oldElement = document.forms['form1'].elements[i].name;
				} 				
			}
			if (sub == true) {
				document.forms['form1'].elements['save'].value='Save';
				document.forms['form1'].submit();
			} else {
				alert("Please check all the offers");
				return false;
			}
		} else {
			document.forms['form1'].elements['save'].value='Save';
			document.forms['form1'].submit();

		}
	}
		
</script>
<body>
<center>
<?php
if ($showMeHeader != '') {
	include($showMeHeader);
}
?>
</center>
<br>
	
<form name=form1 action='<?php echo $action; ?>'>
<input type=hidden name=save value=''>
<input type=hidden name=forceShowMe value='<?php echo $forceShowMe;?>'>
<input type=hidden name=prevQueryString value='<?php echo urlencode($QUERY_STRING);?>'>

<table cellpadding=5 cellspacing=0 width=95% align=center>
<tr><td colspan = 2>Click on "<b>Show Me</b>" on ALL offers that are in interest to you! 
	Go ahead and sign up for any of the. A new window will show for that offer.</td></tr>
<?php echo "$offerList";?>
<tr><td align=center><br><br><input type=button name=proceed value="Proceed After Your Selections" onClick="funcProceed();"></td></tr>
</table>
</form>
<br>
<center>
<?php
if ($showMeFooter != '') {
	include($showMeFooter);
}
?>
</center>
</body>

</html>

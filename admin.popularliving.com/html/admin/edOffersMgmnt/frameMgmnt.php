<?php

/***********

Script for Redirs Frames Management

*************/

include("../../includes/paths.php");

$sPageTitle = "Nibbles Editorial Offers Frame Management";

if (hasAccessRight($iMenuId) || isAdmin()) {


if ($sSaveClose) {
	
	// Insert/Update separate records for all the frame contents
	
	// check if record exists for top frame content
	$checkQuery = "SELECT *
				   FROM   vars
				   WHERE  varName = 'offerTopFrameHtml'";	 
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$insertQuery = "INSERT INTO vars(varName, varValue)
						VALUES('offerTopFrameHtml', '$offerTopFrameHtml')";
		$insertResult = mysql_query($insertQuery);
	} else {
		$editQuery = "UPDATE vars
					  SET 	 varValue = '$offerTopFrameHtml'
					  WHERE  varName = 'offerTopFrameHtml'";
		$editResult = mysql_query($editQuery);
	}
	
	// check if record exists for left frame content
	$checkQuery = "SELECT *
				   FROM   vars
				   WHERE  varName = 'offerLeftFrameHtml'";	 
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$insertQuery = "INSERT INTO vars(varName, varValue)
						VALUES('offerLeftFrameHtml', '$offerLeftFrameHtml')";
		$insertResult = mysql_query($insertQuery);
	} else {
		$editQuery = "UPDATE vars
					  SET    varValue = '$offerLeftFrameHtml'
		 	 		  WHERE  varName = 'offerLeftFrameHtml'";
		$editResult = mysql_query($editQuery);
	}
	
	// check if record exists for right frame content
	$checkQuery = "SELECT *
				   FROM   vars
				   WHERE  varName = 'offerRightFrameHtml'";	 
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$insertQuery = "INSERT INTO vars(varName, varValue)
						VALUES('offerRightFrameHtml', '$offerRightFrameHtml')";
		$insertResult = mysql_query($insertQuery);
	} else {
		$editQuery = "UPDATE vars
					  SET  	 varValue = '$offerRightFrameHtml'
					  WHERE  varName = 'offerRightFrameHtml'";
		$editResult = mysql_query($editQuery);
	}
	
	// check if record exists for bootom frame content
	$checkQuery = "SELECT *
				   FROM   vars
				   WHERE  varName = 'offerBottomFrameHtml'";	 
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		$insertQuery = "INSERT INTO vars(varName, varValue)
						VALUES('offerBottomFrameHtml', '$offerBottomFrameHtml')";
		$insertResult = mysql_query($insertQuery);
	} else {
		$editQuery = "UPDATE vars
					  SET  	 varValue = '$offerBottomFrameHtml'
					  WHERE  varName = 'offerBottomFrameHtml'";	
		$editResult = mysql_query($editQuery);
	}
		// create the files 
		// because in a frameset, you must use filename and not the content of the file
		
		$offerTopFrameHtml = stripslashes($offerTopFrameHtml);
		$offerLeftFrameHtml = stripslashes($offerLeftFrameHtml);
		$offerRightFrameHtml = stripslashes($offerRightFrameHtml);
		$offerBottomFrameHtml = stripslashes($offerBottomFrameHtml);
		
	$changeDirToR = chdir("$sGblPopularlivingWebRoot/r");
	if ($changeDirToR) {
		$fpTop = fopen("offerTopFrameHtml.html", "w");
		
		if ($fpTop) {
			fputs($fpTop, $offerTopFrameHtml);
			fclose($fpTop);
		}
		
		$fpLeft = fopen("offerLeftFrameHtml.html", "w");
		
		if ($fpLeft) {
			fputs($fpLeft, $offerLeftFrameHtml);
			fclose($fpLeft);
		}
		
		$fpRight = fopen("offerRightFrameHtml.html", "w");
		
		if ($fpRight) {
			fputs($fpRight, $offerRightFrameHtml);
			fclose($fpRight);
		}
		
		$fpBottom = fopen("offerBottomFrameHtml.html", "w");
		
		if ($fpBottom) {
			fputs($fpBottom, $offerBottomFrameHtml);
		}
		fclose($fpBottom);
	}
	
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	// exit from this script
	exit();
}

// get content of Frames
$selectQuery = "SELECT varName, varValue
				FROM   vars
				WHERE  varName like 'offer%'";

$selectResult = mysql_query($selectQuery);
while ($row = mysql_fetch_object($selectResult)) {
	if ($row->varName == 'offerTopFrameHtml') {
		$offerTopFrameHtml = $row->varValue;
	}
	if ($row->varName == 'offerLeftFrameHtml'){
		$offerLeftFrameHtml = $row->varValue;
	}
	if ($row->varName == 'offerRightFrameHtml') {
		$offerRightFrameHtml = $row->varValue;
	}
	if ($row->varName == 'offerBottomFrameHtml') {
		$offerBottomFrameHtml = $row->varValue;
	}
}

echo mysql_error();

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Top Frame</td>
		<td><textarea name=offerTopFrameHtml rows=5 cols=40><?php echo $offerTopFrameHtml;?></textarea></td>				
	</tr>
	
	<tr><td>Left Frame</td>
		<td><textarea name=offerLeftFrameHtml rows=5 cols=40><?php echo $offerLeftFrameHtml;?></textarea></td>				
	</tr>

	<tr><td>Right Frame</td>
		<td><textarea name=offerRightFrameHtml rows=5 cols=40><?php echo $offerRightFrameHtml;?></textarea></td>				
	</tr>

	<tr><td>Bottom Frame</td>
		<td><textarea name=offerBottomFrameHtml rows=5 cols=40><?php echo $offerBottomFrameHtml;?></textarea></td>				
	</tr>	
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>

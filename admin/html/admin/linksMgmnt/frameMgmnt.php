<?php

/*********

Script to Display Add/Edit Campaign Frame

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Campaign Frames - Add/Edit Campaign Frame";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose) {
	$sCheckQuery = "SELECT *
				    FROM   campaignFrames
				    WHERE  frameName = 'topFrame'";	 
	$rCheckResult = dbQuery($sCheckQuery);
	
	if (dbNumRows($rCheckResult) == 0) {
		$sInsertQuery = "INSERT INTO campaignFrames(frameName, content)
						 VALUES('topFrame', '$sTopFrame')";
		$rInsertResult = dbQuery($sInsertQuery);
	} else {
		$sEditQuery = "UPDATE campaignFrames
					   SET  	 content = '$sTopFrame'
					   WHERE  frameName = 'topFrame'";
		$rEditResult = dbQuery($sEditQuery);
	}
	
	$sCheckQuery = "SELECT *
				    FROM   campaignFrames
				    WHERE  frameName = 'leftFrame'";	 
	$rCheckResult = dbQuery($sCheckQuery);
	//echo dbNumRows($rCheckResult);
	if (dbNumRows($rCheckResult) == 0) {
		$sInsertQuery = "INSERT INTO campaignFrames(frameName, content)
						 VALUES('leftFrame', '$sLeftFrame')";
		$rInsertResult = dbQuery($sInsertQuery);
	} else {
		$sEditQuery = "UPDATE campaignFrames
					   SET    content = '$sLeftFrame'
		 			   WHERE  frameName = leftFrame'";
		$rEditResult = dbQuery($sEditQuery);
	}
	
	$sCheckQuery = "SELECT *
				    FROM   campaignFrames
				    WHERE  frameName = 'rightFrame'";	 
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult) == 0) {
		$sInsertQuery = "INSERT INTO campaignFrames(frameName, content)
						 VALUES('rightFrame', '$sRightFrame')";
		$rInsertResult = dbQuery($sInsertQuery);
	} else {
		$sEditQuery = "UPDATE campaignFrames
					  SET  	 content = '$sRightFrame'
					  WHERE  frameName = 'rightFrame'";
		$rEditResult = dbQuery($sEditQuery);
	}
	$sCheckQuery = "SELECT *
				   FROM   campaignFrames
				   WHERE  frameName = 'bottomFrame'";	 
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult) == 0) {
		$sInsertQuery = "INSERT INTO campaignFrames(frameName, content)
						VALUES('bottomFrame', '$sBottomFrame')";
		$rInsertResult = dbQuery($sInsertQuery);
	} else {
		$sEditQuery = "UPDATE campaignFrames
					  SET  	 content = '$sBottomFrame'
					  WHERE  frameName = 'bottomFrame'";	
		$rEditResult = dbQuery($sEditQuery);
	}
	
	// update the files and put the updated data inside, 
	// file will be used in frameset to display the frame content from it
		
	$bChangeDirToR = chdir($sGblCampaignFrameRoot);
	if ($bChangeDirToR) {
		$rFpTop = fopen("campaignTopFrameHtml.html", "w");
		
		if ($rFpTop) {
			fputs($rFpTop, $sTopFrame);
			fclose($rFpTop);
		}
		
		$rFpLeft = fopen("campaignLeftFrameHtml.html", "w");
		
		if ($rFpLeft) {
			fputs($rFpLeft, $sLeftFrame);
			fclose($rFpLeft);
		}
		
		$rFpRight = fopen("campaignRightFrameHtml.html", "w");
		
		if ($rFpRight) {
			fputs($rFpRight, $sRightFrame);
			fclose($rFpRight);
		}
		
		$rFpBottom = fopen("campaignBottomFrameHtml.html", "w");
		
		if ($rFpBottom) {
			fputs($rFpBottom, $sBottomFrame);
			fclose($rFpBottom);
		}
		
	}
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	// exit from this script
	exit();
}

//get the frame contents to be displayed in the form
$sSelectQuery = "SELECT *
				 FROM   campaignFrames";

$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	if ($oRow->frameName == 'topFrame') {
		$sTopFrame = $oRow->content;
	}
	if ($oRow->frameName == 'leftFrame') {
		$sLeftFrame = $oRow->content;
	}
	if ($oRow->frameName == 'rightFrame') {
		$sRightFrame = $oRow->content;
	}
	if ($oRow->frameName == 'bottomFrame') {
		$sBottomFrame = $oRow->content;
	}
}


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sCampaignCode value='$sCampaignCode'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Top Frame</td>
		<td><textarea name=sTopFrame rows=5 cols=40><?php echo $sTopFrame;?></textarea></td>				
	</tr>
	
	<tr><td>Left Frame</td>
		<td><textarea name=sLeftFrame rows=5 cols=40><?php echo $sLeftFrame;?></textarea></td>				
	</tr>

	<tr><td>Right Frame</td>
		<td><textarea name=sRightFrame rows=5 cols=40><?php echo $sRightFrame;?></textarea></td>				
	</tr>

	<tr><td>Bottom Frame</td>
		<td><textarea name=sBottomFrame rows=5 cols=40><?php echo $sBottomFrame;?></textarea></td>				
	</tr>	
</table>

<?php

include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";	
}
?>
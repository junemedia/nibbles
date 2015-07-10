<?php

include("../includes/paths.php");

session_start();

/******  Check first if $src is a sourceCode, otherwise check if it's offerCode  *******/
$popupQuery = "SELECT popOption, popupUrl, vSize, hSize, bustFrames, displayInFrame
			   FROM   links
			   WHERE  sourceCode = '$src'";

$result = dbQuery($popupQuery);
if (dbNumRows($result) >0 ) {
	// It's sourceCode
	$redirect = "true";
	while($row = dbFetchObject($result)) {
		$popOption = $row->popOption;
		$popupUrl = $row->popupUrl;
		$vSize = $row->vSize;
		$hSize = $row->hSize;
		$iBustFrames = $row->bustFrames;
		$sDisplayInFrame = $row->displayInFrame;
	}
}


if (dbNumRows($result) <= 0 ) {
	// if it's offerCode
	$tempQuery = "SELECT popOption, popupId
				  FROM   edOffers
				  WHERE  offerCode = '$src'";
	$tempResult = dbQuery($tempQuery);
	while ($tempRow= dbFetchObject($tempResult)) {
		$popOption = $tempRow->popOption;
		$popupIdList = $tempRow->popupId;
		if ($popOption != '') {
			$popQuery = "SELECT *
				 	 FROM   edOfferPopUps
				 	 WHERE	id IN (".$popupIdList.")";
			$popResult = dbQuery($popQuery);
			$i = 0;
			while ($popRow = dbFetchObject($popResult)) {
				$sPopup = $popRow->popupName;
				$popupUrlArray[$i] = $popRow->url;
				$popupHSizeArray[$i] = $popRow->hSize;
				$popupVSizeArray[$i] = $popRow->vSize;

				$sCheckQuery = "SELECT *
								FROM	edOfferPopupDisplayStats
								WHERE   offerId = '$sPopup'
								AND		displayDate = CURRENT_DATE";

				$rCheckResult = dbQuery($sCheckQuery);

				echo dbError();
				if ( dbNumRows($rCheckResult) == 0 ) {
					$sStatsInsertQuery = "INSERT INTO edOfferPopupDisplayStats(offerId, displayDate,  counts)
					 					  VALUES('$sPopup', CURRENT_DATE, 1)";
					$rStatsInsertResult = dbQuery($sStatsInsertQuery);
					echo dbError();

				} else {
					$sStatsUpdateQuery = "UPDATE edOfferPopupDisplayStats
						  				  SET	 counts = counts+1
										  WHERE  offerId = '$sPopup'
						  				  AND	 displayDate = CURRENT_DATE";
					$rStatsUpdateResult = dbQuery($sStatsUpdateQuery);
					echo dbError();
				}
				
				$i++;
			}
		}
	}
}
/***********  End checking if src is sourceCode or offerCode and get details  *********/


/*********  Prepare popup display content  **********/
$popupContent = "<script language=JavaScript>";

if ($redirect == "true" && $popOption != '') {
	
	$popUpContent .= "popupWindow = window.open('".$popupUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
	if ($popOption =="popup") {
		$popUpContent .= "popupWindow.focus();
			//				//window.blur();";
	} else {
		$popUpContent .= "popupWindow.blur();\n
							//window.focus();";
	}
}

if ($popupUrlArray != '') {	
	for ($i=0; $i<count($popupUrlArray); $i++) {
		$popupUrl = $popupUrlArray[$i];
		$hSize = $popupHSizeArray[$i];
		$vSize = $popupVSizeArray[$i];
		
		$popUpContent .= "popupWindow = window.open('".$popupUrl."','','width=".$hSize.",height=".$vSize.", scrollbars=yes, resizable=yes');";
		if ($popOption =="popup") {
		 $popUpContent .= "popupWindow.focus();
		 				  // window.blur();";
		} else {
		$popUpContent .= "popupWindow.blur();
						//window.focus()\n";
		}
	}
}
/**********  End preparing popup display content  **********/


/*********  Code to bust frames  *********/
if ($iBustFrames && $sDisplayInFrame == '') {
	
	
	$sBustFramesCode = "<script language='JavaScript' type='text/javascript'>

		if (parent.frames.length > 0) 
		{
    		parent.location.href = self.document.location;
		} 

		</script>";
}
/**********  End of code to bust frames  ***********/


/**********  Attach sessionId  **********/
if (strstr($newUrl, "popularliving.com")) { 
	if (strstr($newUrl,"?")) {
		$newUrl = $newUrl."&PHPSESSID=".session_id();
	} else {
		$newUrl = $newUrl."?PHPSESSID=".session_id();
	}
}
/********  End attaching sessionId  **********/

?>

<html>

<head>
<title><?php print "$src"; ?></title>
<script language="JavaScript">

<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->


<?php echo $popUpContent;?>

</script>
<?php echo $sBustFramesCode; 

?>
<!-- hide this from tired old Browsers  if (window.location != top.location)
{top.location.href=window.location} // -->

</head>

<body>
<?php
//if () 
echo "<meta http-equiv=\"refresh\" content=\"1;URL='$newUrl'\">";

?>

</body>

</html>
